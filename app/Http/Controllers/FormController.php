<?php

namespace App\Http\Controllers;

use App\Builder\ContentSanitizer;
use App\Builder\Renderer;
use App\Models\FormSubmission;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Plugins\Hooks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

/** Réception des formulaires de contact du site public (widget « contact-form »). */
class FormController extends Controller
{
    /** Délai minimal entre l'affichage et l'envoi : en dessous, c'est un robot. */
    private const MIN_SECONDS = 3;

    public function submit(Request $request)
    {
        $page = Page::query()->published()->find((int) $request->input('page'));
        $widgetId = (string) $request->input('widget');
        $widget = null;
        if ($page) {
            Renderer::walk(app(ContentSanitizer::class)->sanitize($page->content)['elements'], function ($node) use ($widgetId, &$widget) {
                if ($node['id'] === $widgetId && $node['type'] === 'contact-form') {
                    $widget = $node;
                }
            });
        }
        abort_unless($widget, 404);
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
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => [! empty($s['show_phone']) ? 'nullable' : 'prohibited', 'string', 'max:40'],
            'subject' => [! empty($s['show_subject']) ? 'nullable' : 'prohibited', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ], [], [
            'name' => mb_strtolower($s['name_label'] ?? __('Nom')),
            'email' => mb_strtolower($s['email_label'] ?? __('E-mail')),
            'message' => mb_strtolower($s['message_label'] ?? __('Message')),
        ]);
        if ($validator->fails()) {
            return $back()->withErrors($validator, 'gp_form_'.$widgetId)->withInput($request->only(['name', 'email', 'phone', 'subject', 'message']));
        }

        $data = array_filter($validator->validated(), fn ($v) => $v !== null && $v !== '');
        $submission = FormSubmission::create([
            'page_id' => $page->id,
            'widget_id' => $widgetId,
            'data' => $data,
            'ip_hash' => hash('sha256', $request->ip().'|'.config('app.key')),
        ]);
        Hooks::dispatch('form.submitted', $submission);
        $this->notify($submission, $page);

        return $back()->with('gp_form_sent', $widgetId);
    }

    /** Envoie le message par e-mail ; un problème d'envoi n'empêche jamais l'enregistrement. */
    private function notify(FormSubmission $submission, Page $page): void
    {
        $to = Setting::read('forms_email') ?: User::query()->where('role', User::ROLE_ADMIN)->orderBy('id')->value('email');
        if (! $to) {
            return;
        }
        $d = $submission->data;
        $lines = [__('Nouveau message depuis la page « :page » :', ['page' => $page->title]), ''];
        foreach (['name' => __('Nom'), 'email' => __('E-mail'), 'phone' => __('Téléphone'), 'subject' => __('Sujet')] as $key => $label) {
            if (isset($d[$key])) {
                $lines[] = $label.' : '.$d[$key];
            }
        }
        $lines[] = '';
        $lines[] = $d['message'] ?? '';
        try {
            Mail::raw(implode("\n", $lines), function ($mail) use ($to, $d) {
                $mail->to($to)->replyTo($d['email'], $d['name'] ?? null)
                    ->subject(__('[:site] Nouveau message de :name', ['site' => Setting::read('site_name'), 'name' => $d['name'] ?? '']));
            });
        } catch (Throwable $e) {
            report($e);
        }
    }
}
