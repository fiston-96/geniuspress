<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Schema;

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
            'bg_color' => 'color@h',
            'text_color' => 'color@h',
            'align' => 'enum:left,center,right,full@r',
            'border_radius' => 'int:0:100',
            'font_size' => 'int:8:80@r',
            'new_tab' => 'bool',
        ] + Schema::TYPOGRAPHY;
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

        $ctx->rule($id, [
            'background' => $variant === 'filled' ? $bg : 'transparent',
            'border-color' => $bg,
            'color' => $fg,
            'border-radius' => RenderContext::px($s['border_radius'] ?? null),
        ], false, ' .gp-btn');
        // Alignement par appareil ; « full » = bouton sur toute la largeur.
        $s += ['align' => 'left'];
        foreach (Schema::DEVICES as $device => $sfx) {
            if (isset($s['align'.$sfx])) {
                $full = $s['align'.$sfx] === 'full';
                $ctx->rule($id, ['text-align' => $full ? 'left' : $s['align'.$sfx]], $device);
                $ctx->rule($id, ['display' => $full ? 'block' : 'inline-block'], $device, ' .gp-btn');
            }
        }
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-btn');
        $ctx->typography($id, $s, ' .gp-btn');
        $hoverBg = $s['bg_color_hover'] ?? null;
        $ctx->rule($id, [
            'background' => $variant === 'filled' ? $hoverBg : null,
            'border-color' => $hoverBg,
            'color' => $s['text_color_hover'] ?? null,
        ], false, ' .gp-btn:hover');

        $target = ! empty($s['new_tab']) ? ' target="_blank" rel="noopener noreferrer"' : '';
        $size = 'gp-btn-'.($s['size'] ?? 'md');

        return '<a class="gp-btn '.$size.'" href="'.e($s['url'] ?? '#').'"'.$target.'>'.e($s['text'] ?? 'Bouton').'</a>';
    }
}
