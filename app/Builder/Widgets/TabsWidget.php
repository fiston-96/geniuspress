<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/**
 * Onglets. Sans JavaScript, tous les panneaux restent visibles les uns sous les
 * autres ; site.js les transforme en onglets accessibles (flèches du clavier).
 */
class TabsWidget extends Widget
{
    public function type(): string { return 'tabs'; }

    public function label(): string { return __('Onglets'); }

    public function rules(): array
    {
        return [
            'items' => 'list:12:title=string:120;html=html',
            'accent' => 'color',
            'style' => 'enum:line,pills',
        ];
    }

    public function translatable(): array { return ['items.*.title', 'items.*.html']; }

    public function aiHint(): ?string
    {
        return 'Tabs ("items": [{title, html}]) to group related content, e.g. programs per level or services per department. 2 to 5 tabs.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['--gp-tabs-accent' => $s['accent'] ?? null], false, ' .gp-tabs');

        $html = '<div class="gp-tabs gp-tabs--'.e($s['style'] ?? 'line').'" data-gp-tabs>';
        foreach ($items as $i => $item) {
            $html .= '<section class="gp-tabs__panel"><h3 class="gp-tabs__title" id="gp-'.e($id).'-t'.$i.'">'.e($item['title'] ?? '').'</h3><div class="gp-tabs__body">'.($item['html'] ?? '').'</div></section>';
        }

        return $html.'</div>';
    }
}
