<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

class ImageWidget extends Widget
{
    public function type(): string { return 'image'; }

    public function label(): string { return __('Image'); }

    public function rules(): array
    {
        return [
            'url' => 'url',
            'media_id' => 'int:0:2147483647',
            'alt' => 'string:300',
            'width' => 'int:5:100',
            'align' => 'enum:left,center,right',
            'border_radius' => 'int:0:500',
            'link' => 'url',
        ];
    }

    public function translatable(): array { return ['alt']; }

    public function aiExcluded(): array { return ['url', 'media_id', 'link']; }

    public function aiHint(): ?string
    {
        return 'Image. You cannot provide files: describe in "alt" exactly which photo the user should add. Use only where a photo really helps.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $media = $ctx->media($s['media_id'] ?? null);
        $src = $media?->url ?? ($s['url'] ?? null);
        if (! $src) {
            return '';
        }
        $ctx->rule($id, ['text-align' => $s['align'] ?? 'center']);
        $ctx->rule($id, [
            'width' => isset($s['width']) ? $s['width'].'%' : null,
            'border-radius' => RenderContext::px($s['border_radius'] ?? null),
        ], false, ' img');

        $alt = e($s['alt'] ?? $media?->alt ?? '');
        $size = $media?->width ? " width=\"{$media->width}\" height=\"{$media->height}\"" : '';
        $img = '<img src="'.e($src).'" alt="'.$alt.'"'.$size.' loading="lazy" decoding="async">';

        return isset($s['link']) ? '<a href="'.e($s['link']).'">'.$img.'</a>' : $img;
    }
}
