<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

class DividerWidget extends Widget
{
    public function type(): string { return 'divider'; }

    public function label(): string { return 'Séparateur'; }

    public function rules(): array
    {
        return [
            'color' => 'color',
            'thickness' => 'int:1:20',
            'width' => 'int:5:100',
            'style' => 'enum:solid,dashed,dotted',
            'align' => 'enum:left,center,right',
        ];
    }

    public function aiHint(): ?string { return 'Horizontal line.'; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $margin = match ($s['align'] ?? 'center') {
            'left' => '0 auto 0 0', 'right' => '0 0 0 auto', default => '0 auto',
        };
        $ctx->rule($id, [
            'border-top' => ($s['thickness'] ?? 1).'px '.($s['style'] ?? 'solid').' '.($s['color'] ?? '#e2e8f0'),
            'width' => ($s['width'] ?? 100).'%',
            'margin' => $margin,
        ], false, ' hr');

        return '<hr>';
    }
}
