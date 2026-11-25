<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Barre de progression de la lecture, en haut de l'écran (articles longs). */
class ReadingProgressWidget extends Widget
{
    public function type(): string { return 'reading-progress'; }

    public function label(): string { return __('Progression de lecture'); }

    public function rules(): array
    {
        return ['color' => 'color', 'height' => 'int:2:12', 'position' => 'enum:top,bottom'];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['background' => $s['color'] ?? null, 'height' => isset($s['height']) ? $s['height'].'px' : null], false, ' .gp-progress-read');

        return '<div class="gp-progress-read gp-progress-read--'.e($s['position'] ?? 'top').'" data-gp-reading hidden aria-hidden="true"></div>';
    }
}
