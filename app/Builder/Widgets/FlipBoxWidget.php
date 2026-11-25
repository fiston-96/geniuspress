<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Carte qui se retourne au survol (ou au toucher) : un recto, un verso avec un bouton. */
class FlipBoxWidget extends Widget
{
    public function type(): string { return 'flip-box'; }

    public function label(): string { return __('Carte qui se retourne'); }

    public function rules(): array
    {
        return [
            'icon' => 'icon',
            'front_title' => 'string:200',
            'front_text' => 'string:600',
            'back_title' => 'string:200',
            'back_text' => 'string:600',
            'button_text' => 'string:80',
            'button_url' => 'url',
            'direction' => 'enum:horizontal,vertical',
            'height' => 'int:120:900@r',
            'radius' => 'int:0:80',
            'front_bg' => 'color',
            'front_color' => 'color',
            'back_bg' => 'color',
            'back_color' => 'color',
        ];
    }

    public function translatable(): array { return ['front_title', 'front_text', 'back_title', 'back_text', 'button_text']; }

    public function aiHint(): ?string
    {
        return 'Card that flips on hover: front (icon, front_title, front_text) and back (back_title, back_text, button_text, button_url). Good for services or values.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->responsive($id, $s, 'height', fn ($v) => ['height' => $v.'px'], ' .gp-flip');
        $ctx->rule($id, ['border-radius' => isset($s['radius']) ? $s['radius'].'px' : null], false, ' .gp-flip__face');
        $ctx->rule($id, ['background' => $s['front_bg'] ?? null, 'color' => $s['front_color'] ?? null], false, ' .gp-flip__front');
        $ctx->rule($id, ['background' => $s['back_bg'] ?? null, 'color' => $s['back_color'] ?? null], false, ' .gp-flip__back');

        $button = ! empty($s['button_text']) ? '<a class="gp-btn gp-btn-sm gp-flip__btn" href="'.e($s['button_url'] ?? '#').'">'.e($s['button_text']).'</a>' : '';

        return '<div class="gp-flip gp-flip--'.e($s['direction'] ?? 'horizontal').'" tabindex="0"><div class="gp-flip__inner">'
            .'<div class="gp-flip__face gp-flip__front">'.(isset($s['icon']) ? Icons::svg($s['icon']) : '')
            .'<p class="gp-flip__title">'.e($s['front_title'] ?? '').'</p>'.(! empty($s['front_text']) ? '<p class="gp-flip__text">'.e($s['front_text']).'</p>' : '').'</div>'
            .'<div class="gp-flip__face gp-flip__back"><p class="gp-flip__title">'.e($s['back_title'] ?? '').'</p>'
            .(! empty($s['back_text']) ? '<p class="gp-flip__text">'.e($s['back_text']).'</p>' : '').$button.'</div>'
            .'</div></div>';
    }
}
