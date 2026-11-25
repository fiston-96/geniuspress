<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Bouton « retour en haut », affiché quand le visiteur a fait défiler la page. */
class BackToTopWidget extends Widget
{
    public function type(): string { return 'back-to-top'; }

    public function label(): string { return __('Retour en haut'); }

    public function rules(): array
    {
        return ['position' => 'enum:right,left', 'bg_color' => 'color', 'color' => 'color', 'shape' => 'enum:circle,rounded'];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['background' => $s['bg_color'] ?? null, 'color' => $s['color'] ?? null], false, ' .gp-totop');

        return '<a class="gp-totop gp-totop--'.e($s['position'] ?? 'right').' gp-totop--'.e($s['shape'] ?? 'circle').'" href="#" data-gp-totop aria-label="'.e(__('Retour en haut de la page', [], $ctx->site?->locale)).'">'.Icons::svg('lucide:arrow-up-right').'</a>';
    }
}
