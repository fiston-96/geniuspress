<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Horaires d'ouverture : jours, heures, mise en valeur d'une ligne (aujourd'hui, permanence…). */
class BusinessHoursWidget extends Widget
{
    public function type(): string { return 'business-hours'; }

    public function label(): string { return __('Horaires'); }

    public function rules(): array
    {
        return [
            'items' => 'list:20:day=string:80;hours=string:120;highlight=bool',
            'divider' => 'bool',
            'color' => 'color',
            'hours_color' => 'color',
            'highlight_color' => 'color',
            'font_size' => 'int:8:40@r',
        ];
    }

    public function translatable(): array { return ['items.*.day', 'items.*.hours']; }

    public function aiHint(): ?string
    {
        return 'Opening hours ("items": [{day, hours, highlight}]). Use "[hours]" placeholders when unknown, "Fermé"/"Closed" for closed days.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['color' => $s['color'] ?? null], false, ' .gp-hours');
        $ctx->rule($id, ['color' => $s['hours_color'] ?? null], false, ' .gp-hours__time');
        $ctx->rule($id, ['color' => $s['highlight_color'] ?? null], false, ' .gp-hours__row.is-highlight');
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-hours');

        $html = '<ul class="gp-hours'.(($s['divider'] ?? true) ? ' gp-hours--divided' : '').'">';
        foreach ($items as $item) {
            $html .= '<li class="gp-hours__row'.(! empty($item['highlight']) ? ' is-highlight' : '').'"><span class="gp-hours__day">'.e($item['day'] ?? '').'</span><span class="gp-hours__time">'.e($item['hours'] ?? '').'</span></li>';
        }

        return $html.'</ul>';
    }
}
