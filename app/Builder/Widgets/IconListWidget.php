<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Liste avec icônes : coordonnées, horaires, points forts, inclus dans une offre. */
class IconListWidget extends Widget
{
    public function type(): string { return 'icon-list'; }

    public function label(): string { return __('Liste d’icônes'); }

    public function rules(): array
    {
        return [
            'items' => 'list:40:icon=icon;text=string:300;url=url',
            'layout' => 'enum:vertical,horizontal',
            'icon_color' => 'color',
            'icon_size' => 'int:8:80',
            'text_color' => 'color@h',
            'gap' => 'int:0:80',
            'align' => 'enum:left,center,right@r',
        ];
    }

    public function translatable(): array { return ['items.*.text']; }

    public function aiHint(): ?string
    {
        return 'List of short lines, each with an icon ("items": [{icon: "lucide:<name>", text}]). Ideal for contact details (phone, e-mail, address, opening hours) or key points.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['gap' => isset($s['gap']) ? $s['gap'].'px' : null], false, ' .gp-ilist');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$v], 'text-align' => $v], ' .gp-ilist');
        $ctx->rule($id, ['color' => $s['icon_color'] ?? null, 'width' => isset($s['icon_size']) ? $s['icon_size'].'px' : null, 'height' => isset($s['icon_size']) ? $s['icon_size'].'px' : null], false, ' .gp-ico');
        $ctx->rule($id, ['color' => $s['text_color'] ?? null], false, ' .gp-ilist__item');
        $ctx->rule($id, ['color' => $s['text_color_hover'] ?? null], false, ' a.gp-ilist__item:hover');

        $html = '<ul class="gp-ilist gp-ilist--'.e($s['layout'] ?? 'vertical').'">';
        foreach ($items as $item) {
            $inner = Icons::svg($item['icon'] ?? 'lucide:check').'<span>'.e($item['text'] ?? '').'</span>';
            $html .= '<li>'.(isset($item['url']) ? '<a class="gp-ilist__item" href="'.e($item['url']).'">'.$inner.'</a>' : '<span class="gp-ilist__item">'.$inner.'</span>').'</li>';
        }

        return $html.'</ul>';
    }
}
