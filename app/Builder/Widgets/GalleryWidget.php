<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Grille de photos, avec agrandissement au clic (site.js). */
class GalleryWidget extends Widget
{
    public function type(): string { return 'gallery'; }

    public function label(): string { return __('Galerie'); }

    public function rules(): array
    {
        return [
            'images' => 'list:60:url=url;media_id=int:0:2147483647;alt=string:300;caption=string:300',
            'columns' => 'int:1:8@r',
            'gap' => 'int:0:80',
            'ratio' => 'enum:auto,1:1,4:3,3:2,16:9,3:4',
            'radius' => 'int:0:100',
            'lightbox' => 'bool',
            'captions' => 'bool',
        ];
    }

    public function translatable(): array { return ['images.*.alt', 'images.*.caption']; }

    public function aiExcluded(): array { return ['images']; }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $images = array_values(array_filter(array_map(function ($img) use ($ctx) {
            $media = $ctx->media($img['media_id'] ?? null);
            $src = $media?->url ?? ($img['url'] ?? null);

            return $src ? ['src' => $src, 'alt' => $img['alt'] ?? $media?->alt ?? '', 'caption' => $img['caption'] ?? null] : null;
        }, $s['images'] ?? [])));
        if (! $images) {
            return '';
        }

        $ctx->rule($id, ['grid-template-columns' => 'repeat('.($s['columns'] ?? 3).', minmax(0, 1fr))', 'gap' => ($s['gap'] ?? 12).'px'], false, ' .gp-gallery__grid');
        foreach (['tablet', 'mobile'] as $device) {
            if (isset($s['columns_'.$device])) {
                $ctx->rule($id, ['grid-template-columns' => 'repeat('.$s['columns_'.$device].', minmax(0, 1fr))'], $device, ' .gp-gallery__grid');
            }
        }
        $ctx->rule($id, [
            // « auto » = format d'origine des photos (annule le carré par défaut).
            'aspect-ratio' => ($s['ratio'] ?? '1:1') === 'auto' ? 'auto' : str_replace(':', ' / ', $s['ratio'] ?? '1:1'),
            'border-radius' => isset($s['radius']) ? $s['radius'].'px' : null,
        ], false, ' .gp-gallery__grid img');

        $lightbox = $s['lightbox'] ?? true;
        $html = '<div class="gp-gallery__grid"'.($lightbox ? ' data-gp-lightbox' : '').'>';
        foreach ($images as $img) {
            $tag = '<img src="'.e($img['src']).'" alt="'.e($img['alt']).'" loading="lazy" decoding="async">';
            $tag = $lightbox ? '<a href="'.e($img['src']).'">'.$tag.'</a>' : $tag;
            $caption = ! empty($s['captions']) && $img['caption'] ? '<figcaption>'.e($img['caption']).'</figcaption>' : '';
            $html .= "<figure>{$tag}{$caption}</figure>";
        }

        return $html.'</div>';
    }
}
