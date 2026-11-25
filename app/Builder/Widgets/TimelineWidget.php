<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Chronologie : histoire de l'organisation, étapes d'un projet, programme d'un événement. */
class TimelineWidget extends Widget
{
    public function type(): string { return 'timeline'; }

    public function label(): string { return __('Chronologie'); }

    public function rules(): array
    {
        return [
            'items' => 'list:40:date=string:80;title=string:200;text=string:800;icon=icon',
            'layout' => 'enum:alternate,left',
            'line_color' => 'color',
            'dot_color' => 'color',
            'date_color' => 'color',
            'card' => 'bool',
        ];
    }

    public function translatable(): array { return ['items.*.date', 'items.*.title', 'items.*.text']; }

    public function aiHint(): ?string
    {
        return 'Timeline ("items": [{date, title, text}]). For an organization’s history, project milestones or an event program. Use "[year]" placeholders for unknown dates.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['--gp-tl-line' => $s['line_color'] ?? null, '--gp-tl-dot' => $s['dot_color'] ?? null], false, ' .gp-tl');
        $ctx->rule($id, ['color' => $s['date_color'] ?? null], false, ' .gp-tl__date');

        $html = '<ol class="gp-tl gp-tl--'.e($s['layout'] ?? 'alternate').(($s['card'] ?? true) ? ' gp-tl--cards' : '').'">';
        foreach ($items as $item) {
            $html .= '<li class="gp-tl__item"><span class="gp-tl__dot">'.(isset($item['icon']) ? Icons::svg($item['icon']) : '').'</span>'
                .'<div class="gp-tl__card">'.(! empty($item['date']) ? '<p class="gp-tl__date">'.e($item['date']).'</p>' : '')
                .'<p class="gp-tl__title">'.e($item['title'] ?? '').'</p>'
                .(! empty($item['text']) ? '<p class="gp-tl__text">'.e($item['text']).'</p>' : '').'</div></li>';
        }

        return $html.'</ol>';
    }
}
