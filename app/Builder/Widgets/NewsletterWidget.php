<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use Illuminate\Support\Facades\Crypt;

/**
 * Inscription à la newsletter du site (App\Newsletter\Newsletter) : adresse e-mail, nom facultatif,
 * double confirmation par e-mail. Mêmes protections que le formulaire de contact (champ piège,
 * délai minimal, limite par visiteur). Aperçu du builder : widgets/library-preview.jsx.
 */
class NewsletterWidget extends Widget
{
    public function type(): string { return 'newsletter'; }

    public function label(): string { return __('Newsletter'); }

    public function rules(): array
    {
        return [
            'layout' => 'enum:inline,stacked',
            'show_name' => 'bool',
            'name_label' => 'string:80',
            'email_label' => 'string:80',
            'button_text' => 'string:80',
            'consent_text' => 'string:300',
            'success_message' => 'string:300',
            'button_color' => 'color',
            'field_radius' => 'int:0:30',
        ];
    }

    public function translatable(): array
    {
        return ['name_label', 'email_label', 'button_text', 'consent_text', 'success_message'];
    }

    public function aiHint(): ?string
    {
        return 'Newsletter sign-up (e-mail, optional name, double opt-in). Put it near the end of the home page or in a news section, with a short heading above.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['background' => $s['button_color'] ?? null, 'border-color' => $s['button_color'] ?? null], false, ' .gp-news .gp-btn');
        $ctx->rule($id, ['border-radius' => isset($s['field_radius']) ? $s['field_radius'].'px' : null], false, ' .gp-news input');

        if (session('gp_news_sent') === $id) {
            return '<div class="gp-form__success" role="status" id="gp-news-'.e($id).'">'
                .e($s['success_message'] ?? __('Merci ! Ouvrez l’e-mail que nous venons de vous envoyer pour confirmer votre inscription.')).'</div>';
        }
        if (! $ctx->pageId) {
            return '<p class="gp-form__note">'.e(__('L’inscription fonctionnera sur la page publiée.')).'</p>';
        }

        $errors = session('errors')?->getBag('gp_news_'.$id);
        $layout = ($s['layout'] ?? 'inline') === 'stacked' ? 'stacked' : 'inline';
        // En ligne, le libellé est masqué (lu par les lecteurs d'écran) et affiché dans le champ.
        $field = function (string $name, string $label, string $type, bool $required, string $autocomplete) use ($errors, $id, $layout) {
            $error = $errors?->first($name);
            $fid = 'gp-'.$id.'-'.$name;

            return '<p class="gp-form__field"><label for="'.$fid.'">'.e($label).'</label>'
                .'<input type="'.$type.'" id="'.$fid.'" name="'.$name.'"'.($required ? ' required' : '').' autocomplete="'.$autocomplete.'" maxlength="190" value="'.e((string) old($name, '')).'"'.($layout === 'inline' ? ' placeholder="'.e($label).'"' : '')
                .($error ? ' aria-invalid="true" aria-describedby="'.$fid.'-err"' : '').'>'
                .($error ? '<span class="gp-form__error" id="'.$fid.'-err">'.e($error).'</span>' : '').'</p>';
        };

        $html = '<form class="gp-form gp-news gp-news--'.$layout.'" id="gp-news-'.e($id).'" method="post" action="'.e(route('newsletter.subscribe')).'" novalidate>'
            .'<input type="hidden" name="_token" value="'.e(csrf_token()).'">'
            .'<input type="hidden" name="page" value="'.$ctx->pageId.'"><input type="hidden" name="widget" value="'.e($id).'">'
            .'<input type="hidden" name="_ts" value="'.e(Crypt::encryptString((string) time())).'">'
            .'<p class="gp-form__hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>'
            .'<div class="gp-news__fields">';
        if (! empty($s['show_name'])) {
            $html .= $field('name', $s['name_label'] ?? __('Prénom'), 'text', false, 'given-name');
        }
        $html .= $field('email', $s['email_label'] ?? __('Votre adresse e-mail'), 'email', true, 'email')
            .'<button type="submit" class="gp-btn gp-btn-md">'.e($s['button_text'] ?? __('S’inscrire')).'</button></div>';
        if (($consent = $s['consent_text'] ?? __('Une ou deux nouvelles par mois. Désinscription en un clic, à tout moment.')) !== '') {
            $html .= '<p class="gp-news__consent">'.e($consent).'</p>';
        }

        return $html.'</form>';
    }
}
