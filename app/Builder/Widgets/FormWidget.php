<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Formulaire avancé : champs composés librement (texte, e-mail, téléphone, adresse web,
 * nombre, date, zone de texte, liste, choix unique, cases à cocher, consentement, champ
 * caché), largeur par champ, envoi par e-mail à l'adresse choisie, redirection possible.
 * Fonctionne sur une page comme dans un gabarit (popup…). Réception : FormController.
 * Mêmes protections que le formulaire de contact (champ piège, délai minimal, limite).
 */
class FormWidget extends Widget
{
    public const TYPES = ['text', 'email', 'tel', 'url', 'number', 'date', 'textarea', 'select', 'radio', 'checkboxes', 'acceptance', 'hidden'];

    public function type(): string { return 'form'; }

    public function label(): string { return __('Formulaire'); }

    public function rules(): array
    {
        return [
            'form_name' => 'string:80',
            'fields' => 'list:30:type=enum:'.implode(',', self::TYPES).';label=string:300;name=slug:40;placeholder=string:120;required=bool;options=string:2000;width=enum:100,50,33;value=string:200',
            'button_text' => 'string:80',
            'success_message' => 'string:300',
            'redirect' => 'url',
            'email_to' => 'string:190',
            'email_subject' => 'string:150',
            'button_color' => 'color',
            'button_align' => 'enum:start,center,end,stretch',
            'field_radius' => 'int:0:30',
            'label_color' => 'color',
        ];
    }

    public function translatable(): array
    {
        return ['button_text', 'success_message', 'form_name', 'fields.*.label', 'fields.*.placeholder', 'fields.*.options'];
    }

    public function aiHint(): ?string
    {
        return 'Custom form with free fields (fields: list of {type: text|email|tel|url|number|date|textarea|select|radio|checkboxes|acceptance|hidden, label, required, options one per line, width 100|50|33}). Use it for quotes, registrations, surveys.';
    }

    /**
     * Champs prêts à l'emploi : nom unique (d'après le libellé si absent), choix en liste.
     *
     * @return list<array{type: string, label: string, name: string, placeholder: ?string, required: bool, options: string[], width: string, value: ?string}>
     */
    public static function fields(array $s): array
    {
        $out = [];
        $used = [];
        foreach ((array) ($s['fields'] ?? []) as $i => $f) {
            $type = in_array($f['type'] ?? 'text', self::TYPES, true) ? ($f['type'] ?? 'text') : 'text';
            $label = trim((string) ($f['label'] ?? ''));
            $base = Str::slug((string) ($f['name'] ?? '')) ?: (Str::slug(Str::limit($label, 30, '')) ?: 'champ');
            $name = $base;
            for ($n = 2; isset($used[$name]) || in_array($name, ['_token', '_ts', 'owner', 'widget', 'website', 'page', '_fields'], true); $n++) {
                $name = $base.'-'.$n;
            }
            $used[$name] = true;
            $options = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($f['options'] ?? ''))), fn ($o) => $o !== ''));
            $out[] = [
                'type' => $type,
                'label' => $label !== '' ? $label : __('Champ :n', ['n' => $i + 1]),
                'name' => $name,
                'placeholder' => $f['placeholder'] ?? null,
                'required' => ! empty($f['required']),
                'options' => array_slice($options, 0, 50),
                'width' => in_array($f['width'] ?? '100', ['100', '50', '33'], true) ? (string) ($f['width'] ?? '100') : '100',
                'value' => $f['value'] ?? null,
            ];
        }

        return $out;
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['background' => $s['button_color'] ?? null, 'border-color' => $s['button_color'] ?? null], false, ' .gp-form .gp-btn');
        $ctx->rule($id, ['align-self' => ['start' => 'flex-start', 'center' => 'center', 'end' => 'flex-end', 'stretch' => 'stretch'][$s['button_align'] ?? 'start']], false, ' .gp-form .gp-btn');
        $ctx->rule($id, ['border-radius' => isset($s['field_radius']) ? $s['field_radius'].'px' : null], false, ' .gp-form :is(input, textarea, select)');
        $ctx->rule($id, ['color' => $s['label_color'] ?? null], false, ' .gp-form :is(label, legend)');

        if (session('gp_form_sent') === $id) {
            return '<div class="gp-form__success" role="status" id="gp-form-'.e($id).'">'.e($s['success_message'] ?? __('Merci ! Votre message a bien été envoyé.')).'</div>';
        }
        $fields = self::fields($s);
        if (! $fields) {
            return '<p class="gp-form__note">'.e(__('Ajoutez des champs au formulaire.')).'</p>';
        }
        if (! $ctx->formOwner()) {
            return '<p class="gp-form__note">'.e(__('Le formulaire fonctionnera sur la page publiée.')).'</p>';
        }

        $errors = session('errors')?->getBag('gp_form_'.$id);
        $html = '<form class="gp-form gp-form--custom" id="gp-form-'.e($id).'" method="post" action="'.e(route('forms.submit')).'" novalidate>'
            .'<input type="hidden" name="_token" value="'.e(csrf_token()).'">'
            .'<input type="hidden" name="owner" value="'.e($ctx->formOwner()).'"><input type="hidden" name="widget" value="'.e($id).'">'
            .'<input type="hidden" name="_ts" value="'.e(Crypt::encryptString((string) time())).'">'
            .'<p class="gp-form__hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>'
            .'<div class="gp-form__grid">';
        foreach ($fields as $f) {
            $html .= self::field($f, $id, $errors?->first($f['name']));
        }

        return $html.'</div><button type="submit" class="gp-btn gp-btn-md">'.e($s['button_text'] ?? __('Envoyer')).'</button></form>';
    }

    /** Un champ ; même balisage que l'aperçu du builder (widgets/studio-preview.jsx). */
    private static function field(array $f, string $id, ?string $error): string
    {
        $fid = 'gp-'.$id.'-'.$f['name'];
        $name = $f['name'];
        $star = $f['required'] ? ' <span aria-hidden="true">*</span>' : '';
        $aria = ($f['required'] ? ' required' : '').($error ? ' aria-invalid="true" aria-describedby="'.$fid.'-err"' : '');
        $err = $error ? '<span class="gp-form__error" id="'.$fid.'-err">'.e($error).'</span>' : '';
        $old = old($name);
        $value = is_string($old) ? $old : '';
        $ph = $f['placeholder'] ? ' placeholder="'.e($f['placeholder']).'"' : '';
        $wrap = fn (string $inner, string $tag = 'p') => "<{$tag} class=\"gp-form__field gp-form__field--{$f['width']}\">{$inner}{$err}</{$tag}>";

        switch ($f['type']) {
            case 'hidden':
                return '<input type="hidden" name="'.e($name).'" value="'.e((string) $f['value']).'">';
            case 'textarea':
                return $wrap('<label for="'.$fid.'">'.e($f['label']).$star.'</label><textarea id="'.$fid.'" name="'.e($name).'" rows="5" maxlength="5000"'.$ph.$aria.'>'.e($value).'</textarea>');
            case 'select':
                $opts = '<option value="">'.e($f['placeholder'] ?: '—').'</option>';
                foreach ($f['options'] as $o) {
                    $opts .= '<option'.($value === $o ? ' selected' : '').'>'.e($o).'</option>';
                }

                return $wrap('<label for="'.$fid.'">'.e($f['label']).$star.'</label><select id="'.$fid.'" name="'.e($name).'"'.$aria.'>'.$opts.'</select>');
            case 'radio':
            case 'checkboxes':
                $multi = $f['type'] === 'checkboxes';
                $checked = $multi ? (array) $old : [$value];
                $items = '';
                foreach ($f['options'] as $i => $o) {
                    $items .= '<label class="gp-form__choice"><input type="'.($multi ? 'checkbox' : 'radio').'" name="'.e($name).($multi ? '[]' : '').'" value="'.e($o).'"'
                        .(in_array($o, $checked, true) ? ' checked' : '').(! $multi && $f['required'] && $i === 0 ? ' required' : '').'> <span>'.e($o).'</span></label>';
                }

                return $wrap('<legend>'.e($f['label']).$star.'</legend><div class="gp-form__choices"'.($error ? ' aria-describedby="'.$fid.'-err"' : '').'>'.$items.'</div>', 'fieldset');
            case 'acceptance':
                return $wrap('<label class="gp-form__choice"><input type="checkbox" id="'.$fid.'" name="'.e($name).'" value="1"'.($old ? ' checked' : '').$aria.'> <span>'.e($f['label']).$star.'</span></label>');
            default:
                $auto = ['email' => 'email', 'tel' => 'tel', 'url' => 'url'][$f['type']] ?? 'off';

                return $wrap('<label for="'.$fid.'">'.e($f['label']).$star.'</label><input type="'.$f['type'].'" id="'.$fid.'" name="'.e($name).'" value="'.e($value).'" autocomplete="'.$auto.'" maxlength="300"'.$ph.$aria.'>');
        }
    }
}
