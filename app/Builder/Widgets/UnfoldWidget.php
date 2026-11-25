<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Texte dépliable : un long texte replié, avec un bouton « Lire la suite ». */
class UnfoldWidget extends Widget
{
    public function type(): string { return 'unfold'; }

    public function label(): string { return __('Texte dépliable'); }

    public function rules(): array
    {
        return [
            'html' => 'html',
            'height' => 'int:40:800',
            'more' => 'string:60',
            'less' => 'string:60',
            'color' => 'color',
            'button_color' => 'color',
            'fade' => 'color',
        ];
    }

    public function translatable(): array { return ['html', 'more', 'less']; }

    public function aiHint(): ?string
    {
        return 'Long text shown folded with a "Read more" button ("html"). For long presentations or terms.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['--gp-unfold-h' => isset($s['height']) ? $s['height'].'px' : null, '--gp-unfold-fade' => $s['fade'] ?? null, 'color' => $s['color'] ?? null], false, ' .gp-unfold');
        $ctx->rule($id, ['color' => $s['button_color'] ?? null], false, ' .gp-unfold__btn');
        $locale = $ctx->site?->locale;

        return '<div class="gp-unfold"><input type="checkbox" id="u-'.e($id).'" class="gp-unfold__toggle">'
            .'<div class="gp-unfold__content">'.($s['html'] ?? '').'</div>'
            .'<label for="u-'.e($id).'" class="gp-unfold__btn"><span class="gp-unfold__more">'.e($s['more'] ?? __('Lire la suite', [], $locale)).'</span>'
            .'<span class="gp-unfold__less">'.e($s['less'] ?? __('Réduire', [], $locale)).'</span></label></div>';
    }
}
