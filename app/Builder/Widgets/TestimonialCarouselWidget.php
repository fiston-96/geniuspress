<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Carrousel de témoignages : citation, nom, fonction, photo, note. */
class TestimonialCarouselWidget extends Widget
{
    public function type(): string { return 'testimonial-carousel'; }

    public function label(): string { return __('Carrousel de témoignages'); }

    public function rules(): array
    {
        return [
            'items' => 'list:20:text=string:800;name=string:120;role=string:160;url=url;media_id=int:0:2147483647;rating=int:0:5',
            'per_view' => 'int:1:4@r',
            'gap' => 'int:0:60',
            'autoplay' => 'bool',
            'interval' => 'int:2:20',
            'arrows' => 'bool',
            'dots' => 'bool',
            'accent' => 'color',
            'card_bg' => 'color',
        ];
    }

    public function translatable(): array { return ['items.*.text', 'items.*.role']; }

    public function fillable(): array { return [...$this->translatable(), 'items.*.name']; }

    public function aiExcluded(): array { return ['items.*.url']; }

    public function aiHint(): ?string
    {
        return 'Carousel of testimonials ("items": [{text, name, role, rating}]). Never invent real names: use "[Prénom Nom]" placeholders.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = $s['items'] ?? [];
        if (! $items) {
            return '';
        }
        CarouselWidget::slidesCss($ctx, $id, $s, 2);
        $ctx->rule($id, ['--gp-tc-accent' => $s['accent'] ?? null], false, ' .gp-slider');
        $ctx->rule($id, ['background' => $s['card_bg'] ?? null], false, ' .gp-tcard');

        $locale = $ctx->site?->locale;
        $html = '<div class="gp-slider" data-gp-slider'.(! empty($s['autoplay']) ? ' data-autoplay="'.($s['interval'] ?? 7).'"' : '').' aria-roledescription="carousel"><div class="gp-slides">';
        foreach ($items as $item) {
            $img = $ctx->media($item['media_id'] ?? null)?->url ?? ($item['url'] ?? null);
            $rating = $item['rating'] ?? 0;
            $html .= '<figure class="gp-tcard">'
                .($rating ? '<span class="gp-tcard__stars" aria-label="'.e(__(':n sur 5', ['n' => $rating], $locale)).'">'.str_repeat('★', $rating).'</span>' : '')
                .'<blockquote class="gp-tcard__text">'.e($item['text'] ?? '').'</blockquote>'
                .'<figcaption class="gp-tcard__who">'.($img ? '<img src="'.e($img).'" alt="" loading="lazy">' : '')
                .'<span><span class="gp-tcard__name">'.e($item['name'] ?? '').'</span>'.(! empty($item['role']) ? '<span class="gp-tcard__role">'.e($item['role']).'</span>' : '').'</span></figcaption></figure>';
        }

        return $html.'</div>'.SliderWidget::controls($s, count($items), $locale).'</div>';
    }
}
