<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Site\Permalinks;

/** Formulaire de recherche dans les articles du site. */
class SearchFormWidget extends ThemeWidget
{
    public function type(): string { return 'search-form'; }

    public function label(): string { return __('Recherche'); }

    public function rules(): array
    {
        return [
            'placeholder' => 'string:80',
            'button_text' => 'string:40',
            'button_color' => 'color',
            'radius' => 'int:0:40',
        ];
    }

    public function translatable(): array { return ['placeholder', 'button_text']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $locale = $this->locale($ctx);
        $ctx->rule($id, ['background' => $s['button_color'] ?? null], false, ' button');
        if (isset($s['radius'])) {
            $ctx->rule($id, ['border-radius' => $s['radius'].'px'], false, ' input');
            $ctx->rule($id, ['border-radius' => $s['radius'].'px'], false, ' button');
        }
        $label = __('Rechercher', [], $locale);

        return '<form class="gp-search-form" role="search" action="'.e(Permalinks::search($locale)).'" method="get">'
            .'<label class="gp-sr-only" for="s-'.e($id).'">'.e($label).'</label>'
            .'<input id="s-'.e($id).'" type="search" name="s" value="'.e($ctx->site?->search ?? '').'" placeholder="'.e($s['placeholder'] ?? __('Rechercher dans le site…', [], $locale)).'" maxlength="100" required>'
            .'<button type="submit">'.e($s['button_text'] ?? $label).'</button></form>';
    }
}
