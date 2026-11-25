<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Note en étoiles (sur 5, demi-étoiles possibles) avec un texte facultatif. */
class StarRatingWidget extends Widget
{
    public function type(): string { return 'star-rating'; }

    public function label(): string { return __('Note en étoiles'); }

    public function rules(): array
    {
        return [
            'rating' => 'int:0:10',
            'text' => 'string:200',
            'align' => 'enum:left,center,right@r',
            'size' => 'int:10:80',
            'color' => 'color',
            'empty_color' => 'color',
            'text_color' => 'color',
        ];
    }

    public function translatable(): array { return ['text']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $rating = $s['rating'] ?? 10; // en demi-étoiles : 10 = 5 étoiles
        $ctx->rule($id, [
            '--gp-star' => $s['color'] ?? null,
            '--gp-star-off' => $s['empty_color'] ?? null,
            'font-size' => isset($s['size']) ? $s['size'].'px' : null,
        ], false, ' .gp-stars');
        $ctx->rule($id, ['color' => $s['text_color'] ?? null], false, ' .gp-rating__text');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$v]], ' .gp-rating');
        $label = __(':n sur 5', ['n' => str_replace('.', ',', (string) ($rating / 2))]);

        return '<div class="gp-rating"><span class="gp-stars" role="img" aria-label="'.e($label).'" style="--gp-rating:'.($rating * 10).'%">★★★★★</span>'
            .(! empty($s['text']) ? '<span class="gp-rating__text">'.e($s['text']).'</span>' : '').'</div>';
    }
}
