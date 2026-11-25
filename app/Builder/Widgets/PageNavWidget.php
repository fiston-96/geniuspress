<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Navigation de page : des points fixés sur le côté, vers les sections (ancres) d'une longue page. */
class PageNavWidget extends Widget
{
    public function type(): string { return 'page-nav'; }

    public function label(): string { return __('Navigation de page'); }

    public function rules(): array
    {
        return [
            'items' => 'list:12:label=string:60;anchor=slug:60',
            'position' => 'enum:right,left',
            'color' => 'color',
            'active_color' => 'color',
        ];
    }

    public function translatable(): array { return ['items.*.label']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = array_values(array_filter($s['items'] ?? [], fn ($i) => ! empty($i['anchor'])));
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['--gp-pnav' => $s['color'] ?? null, '--gp-pnav-on' => $s['active_color'] ?? null], false, ' .gp-pnav');

        $html = '<nav class="gp-pnav gp-pnav--'.e($s['position'] ?? 'right').'" data-gp-pagenav aria-label="'.e(__('Sections de la page', [], $ctx->site?->locale)).'"><ul>';
        foreach ($items as $item) {
            $html .= '<li><a href="#'.e($item['anchor']).'"><span class="gp-pnav__dot"></span><span class="gp-pnav__label">'.e($item['label'] ?? $item['anchor']).'</span></a></li>';
        }

        return $html.'</ul></nav>';
    }
}
