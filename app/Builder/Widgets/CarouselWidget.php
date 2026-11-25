<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Carrousel de cartes illustrées (projets, produits, partenaires) : plusieurs à la fois, défilement aimanté. */
class CarouselWidget extends Widget
{
    public function type(): string { return 'carousel'; }

    public function label(): string { return __('Carrousel'); }

    public function rules(): array
    {
        return [
            'items' => 'list:30:url=url;media_id=int:0:2147483647;title=string:200;text=string:400;link=url',
            'per_view' => 'int:1:6@r',
            'gap' => 'int:0:60',
            'ratio' => 'enum:1:1,4:3,3:2,16:9,3:4',
            'card' => 'bool',
            'autoplay' => 'bool',
            'interval' => 'int:2:20',
            'arrows' => 'bool',
            'dots' => 'bool',
            'radius' => 'int:0:40',
            'title_color' => 'color',
        ];
    }

    public function translatable(): array { return ['items.*.title', 'items.*.text']; }

    public function aiExcluded(): array { return ['items.*.url', 'items.*.link']; }

    public function aiHint(): ?string
    {
        return 'Carousel of illustrated cards ("items": [{title, text}]). For projects, products, partners or a photo strip.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        self::slidesCss($ctx, $id, $s, 3);
        $ctx->rule($id, ['aspect-ratio' => str_replace(':', ' / ', $s['ratio'] ?? '4:3'), 'border-radius' => isset($s['radius']) ? $s['radius'].'px' : null], false, ' .gp-card__img');
        $ctx->rule($id, ['color' => $s['title_color'] ?? null], false, ' .gp-card__title');

        $locale = $ctx->site?->locale;
        $html = '<div class="gp-slider" data-gp-slider'.(! empty($s['autoplay']) ? ' data-autoplay="'.($s['interval'] ?? 5).'"' : '').' aria-roledescription="carousel"><div class="gp-slides">';
        foreach ($items as $item) {
            $img = $ctx->media($item['media_id'] ?? null)?->url ?? ($item['url'] ?? null);
            $tag = ! empty($item['link']) ? 'a' : 'div';
            $html .= "<{$tag} class=\"gp-card".(($s['card'] ?? true) ? ' gp-card--boxed' : '').'"'.($tag === 'a' ? ' href="'.e($item['link']).'"' : '').'>'
                .($img ? '<img class="gp-card__img" src="'.e($img).'" alt="" loading="lazy">' : '')
                .((! empty($item['title']) || ! empty($item['text'])) ? '<span class="gp-card__body">'
                    .(! empty($item['title']) ? '<span class="gp-card__title">'.e($item['title']).'</span>' : '')
                    .(! empty($item['text']) ? '<span class="gp-card__text">'.e($item['text']).'</span>' : '').'</span>' : '')
                ."</{$tag}>";
        }

        return $html.'</div>'.SliderWidget::controls($s, count($items), $locale).'</div>';
    }

    /** Nombre d'éléments visibles et espacement, par appareil (variables CSS). */
    public static function slidesCss(RenderContext $ctx, string $id, array $s, int $default): void
    {
        $ctx->rule($id, ['--gp-per' => (string) ($s['per_view'] ?? $default), '--gp-gap' => isset($s['gap']) ? $s['gap'].'px' : null], false, ' .gp-slides');
        $ctx->rule($id, ['--gp-per' => (string) ($s['per_view_tablet'] ?? min(2, $s['per_view'] ?? $default))], 'tablet', ' .gp-slides');
        $ctx->rule($id, ['--gp-per' => (string) ($s['per_view_mobile'] ?? 1)], 'mobile', ' .gp-slides');
    }
}
