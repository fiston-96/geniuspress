<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Encart promotionnel : image de fond, voile, titre, texte et bouton (offre, événement, appel aux dons). */
class PromoBoxWidget extends Widget
{
    public function type(): string { return 'promo-box'; }

    public function label(): string { return __('Encart promotionnel'); }

    public function rules(): array
    {
        return [
            'url' => 'url',
            'media_id' => 'int:0:2147483647',
            'eyebrow' => 'string:80',
            'title' => 'string:200',
            'text' => 'string:600',
            'button_text' => 'string:80',
            'button_url' => 'url',
            'align' => 'enum:left,center,right',
            'valign' => 'enum:top,center,bottom',
            'height' => 'int:120:900@r',
            'radius' => 'int:0:80',
            'overlay' => 'color',
            'overlay_opacity' => 'int:0:100',
            'color' => 'color',
            'zoom' => 'bool',
        ];
    }

    public function translatable(): array { return ['eyebrow', 'title', 'text', 'button_text']; }

    public function aiExcluded(): array { return ['url']; }

    public function aiHint(): ?string
    {
        return 'Promotional box with a background image, a short title, text and a button (offer, event, call for donations).';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $media = $ctx->media($s['media_id'] ?? null);
        $url = $media?->url ?? ($s['url'] ?? null);
        $ctx->responsive($id, $s, 'height', fn ($v) => ['min-height' => $v.'px'], ' .gp-promo');
        $ctx->rule($id, [
            'border-radius' => isset($s['radius']) ? $s['radius'].'px' : null,
            'color' => $s['color'] ?? null,
            'text-align' => $s['align'] ?? null,
            '--gp-promo-align' => isset($s['align']) ? ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$s['align']] : null,
            'justify-content' => ['top' => 'flex-start', 'center' => 'center', 'bottom' => 'flex-end'][$s['valign'] ?? 'bottom'],
        ], false, ' .gp-promo');
        $ctx->rule($id, ['background' => $s['overlay'] ?? null, 'opacity' => isset($s['overlay_opacity']) ? $s['overlay_opacity'] / 100 : null], false, ' .gp-promo__overlay');

        return '<div class="gp-promo'.(! empty($s['zoom']) ? ' gp-promo--zoom' : '').'">'
            .($url ? '<img class="gp-promo__img" src="'.e($url).'" alt="" loading="lazy">' : '')
            .'<span class="gp-promo__overlay"></span><div class="gp-promo__body">'
            .(! empty($s['eyebrow']) ? '<p class="gp-promo__eyebrow">'.e($s['eyebrow']).'</p>' : '')
            .'<p class="gp-promo__title">'.e($s['title'] ?? '').'</p>'
            .(! empty($s['text']) ? '<p class="gp-promo__text">'.e($s['text']).'</p>' : '')
            .(! empty($s['button_text']) ? '<a class="gp-btn gp-promo__btn" href="'.e($s['button_url'] ?? '#').'">'.e($s['button_text']).'</a>' : '')
            .'</div></div>';
    }
}
