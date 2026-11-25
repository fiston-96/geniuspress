<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Liste de prix : carte d'un restaurant, prestations, tarifs détaillés. */
class PriceListWidget extends Widget
{
    public function type(): string { return 'price-list'; }

    public function label(): string { return __('Liste de prix'); }

    public function rules(): array
    {
        return [
            'items' => 'list:60:title=string:200;price=string:40;description=string:400;badge=string:40;url=url;media_id=int:0:2147483647',
            'leader' => 'bool',
            'gap' => 'int:0:80',
            'title_color' => 'color',
            'price_color' => 'color',
            'text_color' => 'color',
            'image_size' => 'int:32:200',
        ];
    }

    public function translatable(): array { return ['items.*.title', 'items.*.description', 'items.*.badge']; }

    public function fillable(): array { return [...$this->translatable(), 'items.*.price']; }

    public function aiExcluded(): array { return ['items.*.url']; }

    public function aiHint(): ?string
    {
        return 'List of items with prices ("items": [{title, price, description, badge}]). Ideal for a restaurant menu or a list of services with rates. Use placeholders like "[price]" if unknown.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['gap' => isset($s['gap']) ? $s['gap'].'px' : null], false, ' .gp-pricelist');
        $ctx->rule($id, ['color' => $s['title_color'] ?? null], false, ' .gp-price__title');
        $ctx->rule($id, ['color' => $s['price_color'] ?? null], false, ' .gp-price__price');
        $ctx->rule($id, ['color' => $s['text_color'] ?? null], false, ' .gp-price__desc');
        if (isset($s['image_size'])) {
            $ctx->rule($id, ['width' => $s['image_size'].'px', 'height' => $s['image_size'].'px'], false, ' .gp-price__img');
        }

        $html = '<ul class="gp-pricelist'.(($s['leader'] ?? true) ? ' gp-pricelist--leader' : '').'">';
        foreach ($items as $item) {
            $img = $ctx->media($item['media_id'] ?? null)?->url ?? ($item['url'] ?? null);
            $html .= '<li class="gp-price">'
                .($img ? '<img class="gp-price__img" src="'.e($img).'" alt="" loading="lazy">' : '')
                .'<div class="gp-price__body"><p class="gp-price__head"><span class="gp-price__title">'.e($item['title'] ?? '').'</span>'
                .(! empty($item['badge']) ? '<span class="gp-price__badge">'.e($item['badge']).'</span>' : '')
                .'<span class="gp-price__dots" aria-hidden="true"></span><span class="gp-price__price">'.e($item['price'] ?? '').'</span></p>'
                .(! empty($item['description']) ? '<p class="gp-price__desc">'.e($item['description']).'</p>' : '')
                .'</div></li>';
        }

        return $html.'</ul>';
    }
}
