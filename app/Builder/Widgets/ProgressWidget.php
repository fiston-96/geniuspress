<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Barre de progression : collecte de dons, objectifs, compétences. */
class ProgressWidget extends Widget
{
    public function type(): string { return 'progress'; }

    public function label(): string { return __('Barre de progression'); }

    public function rules(): array
    {
        return [
            'label' => 'string:160',
            'percent' => 'int:0:100',
            'show_percent' => 'bool',
            'bar_color' => 'color',
            'track_color' => 'color',
            'height' => 'int:2:60',
            'radius' => 'int:0:30',
            'label_color' => 'color',
        ];
    }

    public function translatable(): array { return ['label']; }

    public function aiHint(): ?string
    {
        return 'Progress bar with a label and a percentage (fundraising goal, project progress). Use 0 when the real value is unknown.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $percent = (int) ($s['percent'] ?? 0);
        $ctx->rule($id, ['background' => $s['track_color'] ?? null, 'height' => isset($s['height']) ? $s['height'].'px' : null, 'border-radius' => isset($s['radius']) ? $s['radius'].'px' : null], false, ' .gp-progress__track');
        $ctx->rule($id, ['background' => $s['bar_color'] ?? null, 'width' => $percent.'%'], false, ' .gp-progress__bar');
        $ctx->rule($id, ['color' => $s['label_color'] ?? null], false, ' .gp-progress__head');

        $head = isset($s['label']) || ($s['show_percent'] ?? true)
            ? '<div class="gp-progress__head"><span>'.e($s['label'] ?? '').'</span>'.(($s['show_percent'] ?? true) ? '<span>'.$percent.'&nbsp;%</span>' : '').'</div>'
            : '';

        return '<div class="gp-progress">'.$head.'<div class="gp-progress__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="'.$percent.'"'
            .(isset($s['label']) ? ' aria-label="'.e($s['label']).'"' : '').'><div class="gp-progress__bar"></div></div></div>';
    }
}
