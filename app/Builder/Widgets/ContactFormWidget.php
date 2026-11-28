<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use Illuminate\Support\Facades\Crypt;

/**
 * Formulaire de contact. Les messages sont enregistrés (Administration →
 * Messages) et envoyés par e-mail à l'adresse choisie dans les réglages.
 * Protégé contre le spam (champ piège, délai minimal, limite par visiteur).
 */
class ContactFormWidget extends Widget
{
    public function type(): string { return 'contact-form'; }

    public function label(): string { return __('Formulaire de contact'); }

    public function rules(): array
    {
        return [
            'show_phone' => 'bool',
            'show_subject' => 'bool',
            'name_label' => 'string:80',
            'email_label' => 'string:80',
            'phone_label' => 'string:80',
            'subject_label' => 'string:80',
            'message_label' => 'string:80',
            'button_text' => 'string:80',
            'success_message' => 'string:300',
            'button_color' => 'color',
            'field_radius' => 'int:0:30',
        ];
    }

    public function translatable(): array
    {
        return ['name_label', 'email_label', 'phone_label', 'subject_label', 'message_label', 'button_text', 'success_message'];
    }

    public function aiHint(): ?string
    {
        return 'Contact form (name, e-mail, message; optional phone and subject). Put it in the contact section next to the contact details.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['background' => $s['button_color'] ?? null, 'border-color' => $s['button_color'] ?? null], false, ' .gp-form .gp-btn');
        $ctx->rule($id, ['border-radius' => isset($s['field_radius']) ? $s['field_radius'].'px' : null], false, ' .gp-form input, #gp-'.$id.' .gp-form textarea');

        if (session('gp_form_sent') === $id) {
            return '<div class="gp-form__success" role="status" id="gp-form-'.e($id).'">'.e($s['success_message'] ?? __('Merci ! Votre message a bien été envoyé.')).'</div>';
        }
        if (! $ctx->formOwner()) {
            return '<p class="gp-form__note">'.e(__('Le formulaire fonctionnera sur la page publiée.')).'</p>';
        }

        $errors = session('errors')?->getBag('gp_form_'.$id);
        $field = function (string $name, string $label, string $type, bool $required, string $autocomplete) use ($errors, $id) {
            $error = $errors?->first($name);
            $fid = 'gp-'.$id.'-'.$name;
            $attrs = ' id="'.$fid.'" name="'.$name.'"'.($required ? ' required' : '').' autocomplete="'.$autocomplete.'"'.($error ? ' aria-invalid="true" aria-describedby="'.$fid.'-err"' : '');
            $value = e((string) old($name, ''));
            $input = $type === 'textarea' ? "<textarea{$attrs} rows=\"5\" maxlength=\"5000\">{$value}</textarea>" : "<input type=\"{$type}\"{$attrs} value=\"{$value}\" maxlength=\"190\">";

            return '<p class="gp-form__field"><label for="'.$fid.'">'.e($label).($required ? ' <span aria-hidden="true">*</span>' : '').'</label>'.$input
                .($error ? '<span class="gp-form__error" id="'.$fid.'-err">'.e($error).'</span>' : '').'</p>';
        };

        $html = '<form class="gp-form" id="gp-form-'.e($id).'" method="post" action="'.e(route('forms.submit')).'" novalidate>'
            .'<input type="hidden" name="_token" value="'.e(csrf_token()).'">'
            .'<input type="hidden" name="owner" value="'.e($ctx->formOwner()).'"><input type="hidden" name="widget" value="'.e($id).'">'
            // Heure d'affichage chiffrée : un envoi trop rapide est celui d'un robot.
            .'<input type="hidden" name="_ts" value="'.e(Crypt::encryptString((string) time())).'">'
            .'<p class="gp-form__hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>';
        $html .= '<div class="gp-form__row">'.$field('name', $s['name_label'] ?? __('Nom'), 'text', true, 'name').$field('email', $s['email_label'] ?? __('E-mail'), 'email', true, 'email').'</div>';
        if (! empty($s['show_phone'])) {
            $html .= $field('phone', $s['phone_label'] ?? __('Téléphone'), 'tel', false, 'tel');
        }
        if (! empty($s['show_subject'])) {
            $html .= $field('subject', $s['subject_label'] ?? __('Sujet'), 'text', false, 'off');
        }
        $html .= $field('message', $s['message_label'] ?? __('Message'), 'textarea', true, 'off');

        return $html.'<button type="submit" class="gp-btn gp-btn-md">'.e($s['button_text'] ?? __('Envoyer')).'</button></form>';
    }
}
