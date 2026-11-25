<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Image à la une de l'article (gabarit « Article »). */
class PostImageWidget extends ThemeWidget
{
    public function type(): string { return 'post-image'; }

    public function label(): string { return __('Image à la une'); }

    public function rules(): array
    {
        return [
            'ratio' => 'enum:auto,16:9,4:3,3:2,1:1,21:9',
            'radius' => 'int:0:200',
            'link' => 'bool',
        ];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $media = $this->post($ctx)?->featuredMedia;
        if (! $media) {
            return '';
        }
        $ratio = $s['ratio'] ?? '16:9';
        $ctx->rule($id, [
            'aspect-ratio' => $ratio === 'auto' ? null : str_replace(':', ' / ', $ratio),
            'border-radius' => isset($s['radius']) ? $s['radius'].'px' : null,
        ], false, ' img');
        $img = '<img src="'.e($media->url).'" alt="'.e($media->alt ?? '').'">';

        return ! empty($s['link']) ? '<a href="'.e($this->post($ctx)->url()).'">'.$img.'</a>' : $img;
    }
}
