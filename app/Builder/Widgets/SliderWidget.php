<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/**
 * Diaporama plein cadre : diapositives avec image de fond, titre, texte et bouton.
 * Sans JavaScript, les diapositives défilent à la main (défilement horizontal aimanté).
 */
class SliderWidget extends Widget
{
    public function type(): string { return 'slider'; }

    public function label(): string { return __('Diaporama'); }

    public function rules(): array
    {
        return [
            'slides' => 'list:12:url=url;media_id=int:0:2147483647;eyebrow=string:80;title=string:200;text=string:500;button_text=string:80;button_url=url',
            'height' => 'int:200:1000@r',
            'align' => 'enum:left,center,right',
            'overlay' => 'color',
            'overlay_opacity' => 'int:0:100',
            'color' => 'color',
            'autoplay' => 'bool',
            'interval' => 'int:2:20',
            'arrows' => 'bool',
            'dots' => 'bool',
            'radius' => 'int:0:60',
            'title_size' => 'int:16:100@r',
        ];
    }

    public function translatable(): array { return ['slides.*.eyebrow', 'slides.*.title', 'slides.*.text', 'slides.*.button_text']; }

    public function aiExcluded(): array { return ['slides.*.url', 'slides.*.button_url']; }

    public function aiHint(): ?string
    {
        return 'Full-width slideshow ("slides": [{eyebrow, title, text, button_text}]). For a home page hero with several messages.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $slides = $s['slides'] ?? [];
        if (! $slides) {
            return '';
        }
        $ctx->responsive($id, $s, 'height', fn ($v) => ['height' => $v.'px'], ' .gp-slide');
        $ctx->rule($id, ['border-radius' => isset($s['radius']) ? $s['radius'].'px' : null], false, ' .gp-slider');
        $ctx->rule($id, [
            'color' => $s['color'] ?? null,
            'text-align' => $s['align'] ?? null,
            '--gp-slide-align' => isset($s['align']) ? ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$s['align']] : null,
        ], false, ' .gp-slide');
        $ctx->rule($id, ['background' => $s['overlay'] ?? null, 'opacity' => isset($s['overlay_opacity']) ? $s['overlay_opacity'] / 100 : null], false, ' .gp-slide__overlay');
        $ctx->responsive($id, $s, 'title_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-slide__title');

        $locale = $ctx->site?->locale;
        $html = '<div class="gp-slider gp-slider--full" data-gp-slider'.(! empty($s['autoplay']) ? ' data-autoplay="'.($s['interval'] ?? 6).'"' : '')
            .' aria-roledescription="carousel" aria-label="'.e(__('Diaporama', [], $locale)).'"><div class="gp-slides gp-slides--full">';
        foreach ($slides as $i => $slide) {
            $img = $ctx->media($slide['media_id'] ?? null)?->url ?? ($slide['url'] ?? null);
            $html .= '<div class="gp-slide" role="group" aria-roledescription="slide" aria-label="'.($i + 1).' / '.count($slides).'">'
                .($img ? '<img class="gp-slide__img" src="'.e($img).'" alt=""'.($i ? ' loading="lazy"' : '').'>' : '')
                .'<span class="gp-slide__overlay"></span><div class="gp-slide__body">'
                .(! empty($slide['eyebrow']) ? '<p class="gp-slide__eyebrow">'.e($slide['eyebrow']).'</p>' : '')
                .(! empty($slide['title']) ? '<p class="gp-slide__title">'.e($slide['title']).'</p>' : '')
                .(! empty($slide['text']) ? '<p class="gp-slide__text">'.e($slide['text']).'</p>' : '')
                .(! empty($slide['button_text']) ? '<a class="gp-btn gp-slide__btn" href="'.e($slide['button_url'] ?? '#').'">'.e($slide['button_text']).'</a>' : '')
                .'</div></div>';
        }
        $html .= '</div>';

        return $html.self::controls($s, count($slides), $locale).'</div>';
    }

    /** Flèches et points communs aux diaporamas et carrousels (activés par le script du site). */
    public static function controls(array $s, int $count, ?string $locale): string
    {
        $out = '';
        if (($s['arrows'] ?? true) && $count > 1) {
            $out .= '<button type="button" class="gp-slider__arrow gp-slider__prev" data-gp-prev hidden aria-label="'.e(__('Précédent', [], $locale)).'">‹</button>'
                .'<button type="button" class="gp-slider__arrow gp-slider__next" data-gp-next hidden aria-label="'.e(__('Suivant', [], $locale)).'">›</button>';
        }
        if (($s['dots'] ?? true) && $count > 1) {
            $out .= '<div class="gp-slider__dots" data-gp-dots hidden></div>';
        }

        return $out;
    }
}
