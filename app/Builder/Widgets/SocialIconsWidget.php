<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Liens vers les réseaux sociaux (logos officiels ou couleur unique). */
class SocialIconsWidget extends Widget
{
    public function type(): string { return 'social-icons'; }

    public function label(): string { return __('Réseaux sociaux'); }

    public function rules(): array
    {
        return [
            'items' => 'list:20:icon=icon;url=url;label=string:60',
            'size' => 'int:12:80',
            'color_mode' => 'enum:brand,custom',
            'color' => 'color@h',
            'bg_color' => 'color',
            'shape' => 'enum:none,circle,square,rounded',
            'gap' => 'int:0:40',
            'align' => 'enum:left,center,right@r',
        ];
    }

    public function aiExcluded(): array { return ['items']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = array_filter($s['items'] ?? [], fn ($i) => isset($i['icon'], $i['url']));
        if (! $items) {
            return '';
        }
        $size = $s['size'] ?? 22;
        $shape = $s['shape'] ?? 'circle';
        $ctx->rule($id, ['gap' => ($s['gap'] ?? 10).'px', 'justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$s['align'] ?? 'left']], false, ' .gp-social');
        foreach (['tablet', 'mobile'] as $device) {
            if (isset($s['align_'.$device])) {
                $ctx->rule($id, ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$s['align_'.$device]]], $device, ' .gp-social');
            }
        }
        $ctx->rule($id, ['width' => $size.'px', 'height' => $size.'px'], false, ' .gp-ico');
        $ctx->rule($id, [
            'padding' => $shape === 'none' ? '0' : round($size * 0.45).'px',
            'background' => $shape === 'none' ? null : ($s['bg_color'] ?? (($s['color_mode'] ?? 'brand') === 'brand' ? null : 'rgba(15,23,42,.06)')),
            'color' => ($s['color_mode'] ?? 'brand') === 'custom' ? ($s['color'] ?? null) : null,
        ], false, ' .gp-social a');
        $ctx->rule($id, ['color' => ($s['color_mode'] ?? 'brand') === 'custom' ? ($s['color_hover'] ?? null) : null], false, ' .gp-social a:hover');

        $html = '<ul class="gp-social gp-social--'.e($shape).' gp-social--'.e($s['color_mode'] ?? 'brand').'">';
        foreach ($items as $item) {
            $brand = str_starts_with($item['icon'], 'brand:') ? substr($item['icon'], 6) : null;
            $label = $item['label'] ?? ($brand ? Icons::brandTitle($brand) : null) ?? $item['url'];
            $style = $brand && ($s['color_mode'] ?? 'brand') === 'brand' ? ' style="--gp-brand:'.e(Icons::set()['brands'][$brand]['hex'] ?? '').'"' : '';
            $html .= '<li><a href="'.e($item['url']).'" aria-label="'.e($label).'"'.$style.' rel="noopener" target="_blank">'.Icons::svg($item['icon']).'</a></li>';
        }

        return $html.'</ul>';
    }
}
