<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Deux boutons accolés, avec un séparateur facultatif (« ou »). */
class DualButtonWidget extends Widget
{
    public function type(): string { return 'dual-button'; }

    public function label(): string { return __('Double bouton'); }

    public function rules(): array
    {
        return [
            'text_1' => 'string:80',
            'url_1' => 'url',
            'text_2' => 'string:80',
            'url_2' => 'url',
            'middle' => 'string:12',
            'align' => 'enum:left,center,right@r',
            'color_1' => 'color',
            'color_2' => 'color',
            'text_color_1' => 'color',
            'text_color_2' => 'color',
            'radius' => 'int:0:100',
        ];
    }

    public function translatable(): array { return ['text_1', 'text_2', 'middle']; }

    public function aiHint(): ?string
    {
        return 'Two joined buttons ("text_1", "url_1", "text_2", "url_2", optional "middle" word like "ou"/"or"). For two choices: donate or volunteer, call or write.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->responsive($id, $s, 'align', fn ($v) => ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$v]], ' .gp-dbtn');
        $ctx->rule($id, ['background' => $s['color_1'] ?? null, 'color' => $s['text_color_1'] ?? null], false, ' .gp-dbtn__a');
        $ctx->rule($id, ['background' => $s['color_2'] ?? null, 'color' => $s['text_color_2'] ?? null], false, ' .gp-dbtn__b');
        $ctx->rule($id, ['--gp-dbtn-r' => isset($s['radius']) ? $s['radius'].'px' : null], false, ' .gp-dbtn');

        return '<div class="gp-dbtn"><div class="gp-dbtn__group">'
            .'<a class="gp-dbtn__a" href="'.e($s['url_1'] ?? '#').'">'.e($s['text_1'] ?? '').'</a>'
            .(! empty($s['middle']) ? '<span class="gp-dbtn__mid">'.e($s['middle']).'</span>' : '')
            .'<a class="gp-dbtn__b" href="'.e($s['url_2'] ?? '#').'">'.e($s['text_2'] ?? '').'</a>'
            .'</div></div>';
    }
}
