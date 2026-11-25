<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Bandeau défilant : informations, avis, annonces qui défilent en continu. */
class TickerWidget extends Widget
{
    public function type(): string { return 'ticker'; }

    public function label(): string { return __('Bandeau défilant'); }

    public function rules(): array
    {
        return [
            'label' => 'string:60',
            'items' => 'list:20:text=string:200;link=url',
            'speed' => 'int:5:120',
            'bg_color' => 'color',
            'color' => 'color',
            'label_bg' => 'color',
            'label_color' => 'color',
        ];
    }

    public function translatable(): array { return ['label', 'items.*.text']; }

    public function aiHint(): ?string
    {
        return 'Scrolling news ticker ("label" like "Infos", "items": [{text}]). For announcements and urgent notices.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['background' => $s['bg_color'] ?? null, 'color' => $s['color'] ?? null, '--gp-ticker-speed' => isset($s['speed']) ? $s['speed'].'s' : null], false, ' .gp-ticker');
        $ctx->rule($id, ['background' => $s['label_bg'] ?? null, 'color' => $s['label_color'] ?? null], false, ' .gp-ticker__label');

        $list = '';
        foreach ($items as $item) {
            $text = e($item['text'] ?? '');
            $list .= '<li>'.(! empty($item['link']) ? '<a href="'.e($item['link']).'">'.$text.'</a>' : $text).'</li>';
        }

        // La liste est répétée pour un défilement sans coupure ; la copie est masquée aux lecteurs d'écran.
        return '<div class="gp-ticker">'.(! empty($s['label']) ? '<span class="gp-ticker__label">'.e($s['label']).'</span>' : '')
            .'<div class="gp-ticker__viewport"><div class="gp-ticker__track"><ul>'.$list.'</ul><ul aria-hidden="true">'.$list.'</ul></div></div></div>';
    }
}
