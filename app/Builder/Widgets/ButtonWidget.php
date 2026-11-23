<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

class ButtonWidget extends Widget
{
    public function type(): string { return 'button'; }

    public function label(): string { return __('Bouton'); }

    public function rules(): array
    {
        return [
            'text' => 'string:120',
            'url' => 'url',
            'variant' => 'enum:filled,outline',
            'size' => 'enum:sm,md,lg',
            'bg_color' => 'color',
            'text_color' => 'color',
            'align' => 'enum:left,center,right,full',
            'border_radius' => 'int:0:100',
            'new_tab' => 'bool',
        ];
    }

    public function translatable(): array { return ['text']; }

    public function aiHint(): ?string
    {
        return 'Call-to-action button. Link to a page anchor (e.g. "#contact") or a site path (e.g. "/contact").';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $variant = $s['variant'] ?? 'filled';
        $bg = $s['bg_color'] ?? 'var(--gp-primary)';
        $fg = $s['text_color'] ?? ($variant === 'filled' ? '#ffffff' : $bg);
        $align = $s['align'] ?? 'left';

        $ctx->rule($id, ['text-align' => $align === 'full' ? null : $align]);
        $ctx->rule($id, [
            'background' => $variant === 'filled' ? $bg : 'transparent',
            'border-color' => $bg,
            'color' => $fg,
            'border-radius' => RenderContext::px($s['border_radius'] ?? null),
            'display' => $align === 'full' ? 'block' : null,
        ], false, ' .gp-btn');

        $target = ! empty($s['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $size = 'gp-btn-'.($s['size'] ?? 'md');

        return '<a class="gp-btn '.$size.'" href="'.e($s['url'] ?? '#').'"'.$target.'>'.e($s['text'] ?? 'Bouton').'</a>';
    }
}
