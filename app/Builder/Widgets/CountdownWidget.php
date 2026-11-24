<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use Illuminate\Support\Carbon;

/** Compte à rebours jusqu'à un événement (culte spécial, rentrée, campagne). */
class CountdownWidget extends Widget
{
    public function type(): string { return 'countdown'; }

    public function label(): string { return __('Compte à rebours'); }

    public function rules(): array
    {
        return [
            'date' => 'datetime',
            'days_label' => 'string:30',
            'hours_label' => 'string:30',
            'minutes_label' => 'string:30',
            'seconds_label' => 'string:30',
            'expired_text' => 'string:200',
            'number_color' => 'color',
            'label_color' => 'color',
            'box_color' => 'color',
            'align' => 'enum:left,center,right',
        ];
    }

    public function translatable(): array { return ['days_label', 'hours_label', 'minutes_label', 'seconds_label', 'expired_text']; }

    public function aiExcluded(): array { return ['date']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        if (! isset($s['date'])) {
            return '';
        }
        $ctx->rule($id, ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$s['align'] ?? 'center']], false, ' .gp-countdown__row');
        $ctx->rule($id, ['background' => $s['box_color'] ?? null], false, ' .gp-countdown__box');
        $ctx->rule($id, ['color' => $s['number_color'] ?? null], false, ' .gp-countdown__num');
        $ctx->rule($id, ['color' => $s['label_color'] ?? null], false, ' .gp-countdown__label');

        // Date saisie dans le fuseau horaire du site ; le reste est calculé au moment du rendu.
        $target = Carbon::parse($s['date'], config('app.timezone'));
        $left = max(0, $target->getTimestamp() - time());
        $parts = [
            'd' => [intdiv($left, 86400), $s['days_label'] ?? __('jours')],
            'h' => [intdiv($left % 86400, 3600), $s['hours_label'] ?? __('heures')],
            'm' => [intdiv($left % 3600, 60), $s['minutes_label'] ?? __('minutes')],
            's' => [$left % 60, $s['seconds_label'] ?? __('secondes')],
        ];

        $html = '<div class="gp-countdown__row" data-gp-countdown="'.e($target->toIso8601String()).'" data-expired="'.e($s['expired_text'] ?? '').'" role="timer">';
        foreach ($parts as $key => [$value, $label]) {
            $html .= '<div class="gp-countdown__box"><span class="gp-countdown__num" data-unit="'.$key.'">'.str_pad((string) $value, 2, '0', STR_PAD_LEFT).'</span><span class="gp-countdown__label">'.e($label).'</span></div>';
        }

        return $html.'</div>';
    }
}
