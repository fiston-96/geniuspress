<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Encadré d'information : avis, conseil, avertissement, succès. */
class AlertWidget extends Widget
{
    private const ICONS = ['info' => 'info', 'success' => 'circle-check', 'warning' => 'triangle-alert', 'danger' => 'triangle-alert', 'note' => 'lightbulb'];

    public function type(): string { return 'alert'; }

    public function label(): string { return __('Encadré d’information'); }

    public function rules(): array
    {
        return [
            'kind' => 'enum:info,success,warning,danger,note',
            'title' => 'string:200',
            'text' => 'html',
            'icon' => 'bool',
            'bg_color' => 'color',
            'color' => 'color',
            'accent' => 'color',
        ];
    }

    public function translatable(): array { return ['title', 'text']; }

    public function aiHint(): ?string
    {
        return 'Information box ("kind": info|success|warning|danger|note, "title", "text" as simple HTML). For notices, practical tips or warnings.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $kind = $s['kind'] ?? 'info';
        $ctx->rule($id, ['background' => $s['bg_color'] ?? null, 'color' => $s['color'] ?? null, 'border-color' => $s['accent'] ?? null], false, ' .gp-alert');
        $ctx->rule($id, ['color' => $s['accent'] ?? null], false, ' .gp-alert .gp-ico');

        return '<div class="gp-alert gp-alert--'.e($kind).'" role="note">'
            .(($s['icon'] ?? true) ? Icons::svg('lucide:'.self::ICONS[$kind]) : '')
            .'<div class="gp-alert__body">'.(! empty($s['title']) ? '<p class="gp-alert__title">'.e($s['title']).'</p>' : '')
            .'<div class="gp-alert__text">'.($s['text'] ?? '').'</div></div></div>';
    }
}
