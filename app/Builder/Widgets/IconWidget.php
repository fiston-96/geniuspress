<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

class IconWidget extends Widget
{
    public function type(): string { return 'icon'; }

    public function label(): string { return __('Icône'); }

    public function rules(): array
    {
        return [
            'icon' => 'icon',
            'link' => 'url',
            'label' => 'string:120',
            'size' => 'int:8:300@r',
            'color' => 'color@h',
            'bg_color' => 'color@h',
            'shape' => 'enum:none,circle,square,rounded',
            'box' => 'int:0:200',
            'align' => 'enum:left,center,right@r',
        ];
    }

    public function translatable(): array { return ['label']; }

    public function aiHint(): ?string
    {
        return 'A single icon ("icon": "lucide:<name>"), optionally inside a coloured circle ("shape": "circle", "bg_color").';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        if (! isset($s['icon'])) {
            return '';
        }
        self::iconCss($ctx, $id, $s, '');
        $ctx->rule($id, ['text-align' => $s['align'] ?? 'center']);
        $ctx->responsive($id, array_diff_key($s, ['align' => 1]), 'align', fn ($v) => ['text-align' => $v]);

        $icon = '<span class="gp-icon-wrap gp-icon--'.e($s['shape'] ?? 'none').'">'.Icons::svg($s['icon']).'</span>';
        if (isset($s['link'])) {
            return '<a href="'.e($s['link']).'" aria-label="'.e($s['label'] ?? '').'">'.$icon.'</a>';
        }

        return $icon;
    }

    /**
     * CSS d'une icône (taille, couleurs, forme) sous #gp-{id}{scope} .gp-icon-wrap.
     * Partagé par les widgets qui affichent une icône (boîte d'icône…), avec un préfixe de réglages.
     */
    public static function iconCss(RenderContext $ctx, string $id, array $s, string $scope, string $prefix = '', int $defaultSize = 48): void
    {
        $sel = $scope.' .gp-icon-wrap';
        $shape = $s[$prefix.'shape'] ?? 'none';
        $hasBox = $shape !== 'none';
        $ctx->rule($id, [
            'color' => $s[$prefix.'color'] ?? ($hasBox ? '#ffffff' : 'var(--gp-primary)'),
            'background' => $hasBox ? ($s[$prefix.'bg_color'] ?? 'var(--gp-primary)') : ($s[$prefix.'bg_color'] ?? null),
            'padding' => $hasBox ? (($s[$prefix.'box'] ?? 16).'px') : null,
        ], false, $sel);
        $ctx->rule($id, ['color' => $s[$prefix.'color_hover'] ?? null, 'background' => $s[$prefix.'bg_color_hover'] ?? null], false, $sel.':hover');
        $ctx->rule($id, ['width' => ($s[$prefix.'size'] ?? $defaultSize).'px', 'height' => ($s[$prefix.'size'] ?? $defaultSize).'px'], false, $sel.' .gp-ico');
        foreach (['tablet', 'mobile'] as $device) {
            if (isset($s[$prefix.'size_'.$device])) {
                $px = $s[$prefix.'size_'.$device].'px';
                $ctx->rule($id, ['width' => $px, 'height' => $px], $device, $sel.' .gp-ico');
            }
        }
    }
}
