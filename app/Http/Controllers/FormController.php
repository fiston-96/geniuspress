<?php

namespace App\Http\Controllers;

use App\Builder\Widgets\FormWidget;
use App\Models\FormSubmission;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\ContactMessageReceived;
use App\Plugins\Hooks;
use App\Site\FormOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Réception des formulaires du site public : formulaire de contact (« contact-form ») et
 * formulaire avancé (« form »), sur une page ou dans un gabarit du thème (popup…).
 */
class FormController extends Controller
{
    /** Délai minimal entre l'affichage et l'envoi : en dessous, c'est un robot. */
    private const MIN_SECONDS = 3;

    public function submit(Request $request)
    {
        $owner = FormOwner::locate($request, ['contact-form', 'form']);
        abort_unless($owner, 404);
        $widget = $owner['node'];
        $widgetId = $widget['id'];
        $back = fn () => redirect()->to(url()->previous().'#gp-form-'.$widgetId);

        // Robots : champ piège rempli ou envoi trop rapide. On fait comme si tout allait bien.
        try {
            $shownAt = (int) Crypt::decryptString((string) $request->input('_ts'));
        } catch (Throwable) {
            $shownAt = time();
        }
        if ($request->filled('website') || time() - $shownAt < self::MIN_SECONDS) {
            return $back()->with('gp_form_sent', $widgetId);
        }

        $s = $widget['settings'];
        [$rules, $attributes, $fields] = $widget['type'] === 'form' ? $this->customRules($s) : $this->contactRules($s);
        $validator = Validator::make($request->all(), $rules, [], $attributes);
        if ($validator->fails()) {
            return $back()->withErrors($validator, 'gp_form_'.$widgetId)->withInput($request->only(array_keys($rules)));
        }

        $data = array_filter($validator->validated(), fn ($v) => $v !== null && $v !== '' && $v !== []);
        if ($fields !== null) {
            // Formulaire avancé : description des champs gardée avec les réponses, sous forme de liste
            // (MySQL réordonne les clés des objets JSON ; une liste garde l'ordre du formulaire).
            $data['_fields'] = array_map(fn ($f) => ['name' => $f['name'], 'label' => $f['label'], 'type' => $f['type']], $fields);
            foreach ($fields as $f) {
                if ($f['type'] === 'acceptance' && isset($data[$f['name']])) {
                    $data[$f['name']] = __('Oui');
                }
            }
        }
        $submission = FormSubmission::create([
            'page_id' => $owner['page']?->id,
            'layout_id' => $owner['layout']?->id,
            'widget_id' => $widgetId,
            'form_name' => $widget['type'] === 'form' ? ($s['form_name'] ?? null) : null,
            'data' => $data,
            'ip_hash' => hash('sha256', $request->ip().'|'.config('app.key')),
        ]);
        Hooks::dispatch('form.submitted', $submission);
        $this->notify($submission, $s);

        if ($widget['type'] === 'form' && ! empty($s['redirect'])) {
            return redirect()->to($s['redirect']);
        }

        return $back()->with('gp_form_sent', $widgetId);
    }

    /** @return array{0: array, 1: array, 2: null} */
    private function contactRules(array $s): array
    {
        return [[
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => [! empty($s['show_phone']) ? 'nullable' : 'prohibited', 'string', 'max:40'],
            'subject' => [! empty($s['show_subject']) ? 'nullable' : 'prohibited', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'name' => mb_strtolower($s['name_label'] ?? __('Nom')),
            'email' => mb_strtolower($s['email_label'] ?? __('E-mail')),
            'message' => mb_strtolower($s['message_label'] ?? __('Message')),
        ], null];
    }

    /** Règles d'un formulaire avancé, champ par champ. @return array{0: array, 1: array, 2: array} */
    private function customRules(array $s): array
    {
        $fields = FormWidget::fields($s);
        $rules = [];
        $attributes = [];
        foreach ($fields as $f) {
            $name = $f['name'];
            $need = $f['required'] ? 'required' : 'nullable';
            $attributes[$name] = mb_strtolower(strip_tags($f['label']));
            $rules[$name] = match ($f['type']) {
                'email' => [$need, 'email', 'max:190'],
                'url' => [$need, 'url:http,https', 'max:500'],
                'number' => [$need, 'numeric', 'max:1000000000000'],
                'date' => [$need, 'date'],
                'tel' => [$need, 'string', 'max:40', 'regex:/^[0-9+().\s-]{4,40}$/'],
                'textarea' => [$need, 'string', 'max:5000'],
                'select', 'radio' => [$need, 'string', Rule::in($f['options'])],
                'checkboxes' => [$need, 'array', 'max:50'],
                'acceptance' => $f['required'] ? ['accepted'] : ['nullable', 'in:1,on,true'],
                'hidden' => ['nullable', 'string', 'max:300'],
                default => [$need, 'string', 'max:300'],
            };
            if ($f['type'] === 'checkboxes') {
                $rules[$name.'.*'] = ['string', Rule::in($f['options'])];
            }
        }

        return [$rules, $attributes, $fields];
    }

    /** Cloche des administrateurs et e-mail ; un problème d'envoi n'empêche jamais l'enregistrement. */
    private function notify(FormSubmission $submission, array $s): void
    {
        try {
            Notification::send(User::query()->where('role', User::ROLE_ADMIN)->get(), new ContactMessageReceived($submission, $submission->source()));
        } catch (Throwable $e) {
            report($e);
        }

        $custom = filter_var($s['email_to'] ?? null, FILTER_VALIDATE_EMAIL) ? $s['email_to'] : null;
        $to = $custom ?: (Setting::read('forms_email') ?: User::query()->where('role', User::ROLE_ADMIN)->orderBy('id')->value('email'));
        if (! $to) {
            return;
        }
        $lines = [__('Nouveau message depuis « :page » :', ['page' => $submission->source()]), ''];
        foreach ($submission->fields() as [$label, $value]) {
            $lines[] = str_contains($value, "\n") ? $label." :\n".$value."\n" : $label.' : '.$value;
        }
        $email = $submission->senderEmail();
        $name = $submission->senderName();
        $subject = ! empty($s['email_subject'])
            ? '['.Setting::read('site_name').'] '.$s['email_subject']
            : __('[:site] Nouveau message de :name', ['site' => Setting::read('site_name'), 'name' => $name ?? $email ?? $submission->form_name ?? '—']);
        try {
            Mail::raw(implode("\n", $lines), function ($mail) use ($to, $email, $name, $subject) {
                $mail->to($to)->subject($subject);
                if ($email) {
                    $mail->replyTo($email, $name);
                }
            });
        } catch (Throwable $e) {
            report($e);
        }
    }
}
