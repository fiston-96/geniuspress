<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Accordéon d'images : des bandes verticales qui s'ouvrent au survol, avec titre et lien. */
class ImageAccordionWidget extends Widget
{
    public function type(): string { return 'image-accordion'; }

    public function label(): string { return __('Accordéon d’images'); }

    public function rules(): array
    {
        return [
            'items' => 'list:8:url=url;media_id=int:0:2147483647;title=string:120;text=string:300;link=url',
            'height' => 'int:160:900@r',
            'radius' => 'int:0:60',
            'gap' => 'int:0:40',
            'overlay' => 'color',
        ];
    }

    public function translatable(): array { return ['items.*.title', 'items.*.text']; }

    public function aiExcluded(): array { return ['items.*.url', 'items.*.link']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        $ctx->responsive($id, $s, 'height', fn ($v) => ['height' => $v.'px'], ' .gp-iacc');
        $ctx->rule($id, ['gap' => isset($s['gap']) ? $s['gap'].'px' : null], false, ' .gp-iacc');
        $ctx->rule($id, ['border-radius' => isset($s['radius']) ? $s['radius'].'px' : null], false, ' .gp-iacc__item');
        $ctx->rule($id, ['background' => $s['overlay'] ?? null], false, ' .gp-iacc__shade');

        $html = '<div class="gp-iacc">';
        foreach ($items as $item) {
            $img = $ctx->media($item['media_id'] ?? null)?->url ?? ($item['url'] ?? null);
            $tag = ! empty($item['link']) ? 'a' : 'div';
            $html .= "<{$tag} class=\"gp-iacc__item\"".($tag === 'a' ? ' href="'.e($item['link']).'"' : ' tabindex="0"').'>'
                .($img ? '<img src="'.e($img).'" alt="" loading="lazy">' : '')
                .'<span class="gp-iacc__shade"></span><span class="gp-iacc__body"><span class="gp-iacc__title">'.e($item['title'] ?? '').'</span>'
                .(! empty($item['text']) ? '<span class="gp-iacc__text">'.e($item['text']).'</span>' : '')."</span></{$tag}>";
        }

        return $html.'</div>';
    }
}
