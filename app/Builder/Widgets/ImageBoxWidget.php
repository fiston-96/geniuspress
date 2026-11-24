<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Image + titre + texte (+ lien) : projets, articles, produits, départements. */
class ImageBoxWidget extends Widget
{
    public function type(): string { return 'image-box'; }

    public function label(): string { return __('Image et texte'); }

    public function rules(): array
    {
        return [
            'url' => 'url',
            'media_id' => 'int:0:2147483647',
            'alt' => 'string:300',
            'title' => 'string:200',
            'text' => 'html',
            'link' => 'url',
            'link_text' => 'string:80',
            'title_tag' => 'enum:h2,h3,h4,h5,p',
            'ratio' => 'enum:auto,1:1,4:3,3:2,16:9,3:4',
            'image_radius' => 'int:0:200',
            'align' => 'enum:left,center,right@r',
            'title_color' => 'color',
            'title_size' => 'int:10:80@r',
            'text_color' => 'color',
        ];
    }

    public function translatable(): array { return ['alt', 'title', 'text', 'link_text']; }

    public function aiExcluded(): array { return ['url', 'media_id', 'link']; }

    public function aiHint(): ?string
    {
        return 'Card with a photo, a title and a short text: projects, news, products, departments. Describe the needed photo in "alt".';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $media = $ctx->media($s['media_id'] ?? null);
        $src = $media?->url ?? ($s['url'] ?? null);
        $ctx->responsive($id, $s, 'align', fn ($v) => ['text-align' => $v]);
        $ctx->rule($id, [
            'aspect-ratio' => ($s['ratio'] ?? 'auto') === 'auto' ? null : str_replace(':', ' / ', $s['ratio']),
            'border-radius' => isset($s['image_radius']) ? $s['image_radius'].'px' : null,
        ], false, ' .gp-imgbox__img');
        $ctx->rule($id, ['color' => $s['title_color'] ?? null], false, ' .gp-imgbox__title');
        $ctx->responsive($id, $s, 'title_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-imgbox__title');
        $ctx->rule($id, ['color' => $s['text_color'] ?? null], false, ' .gp-imgbox__text');

        $tag = $s['title_tag'] ?? 'h3';
        $html = '<div class="gp-imgbox">';
        if ($src) {
            $img = '<img class="gp-imgbox__img" src="'.e($src).'" alt="'.e($s['alt'] ?? $media?->alt ?? '').'" loading="lazy" decoding="async">';
            $html .= isset($s['link']) ? '<a href="'.e($s['link']).'" tabindex="-1">'.$img.'</a>' : $img;
        }
        if (isset($s['title'])) {
            $title = e($s['title']);
            $html .= "<{$tag} class=\"gp-imgbox__title\">".(isset($s['link']) ? '<a href="'.e($s['link']).'">'.$title.'</a>' : $title)."</{$tag}>";
        }
        if (isset($s['text'])) {
            $html .= '<div class="gp-imgbox__text">'.$s['text'].'</div>';
        }
        if (isset($s['link'], $s['link_text'])) {
            $html .= '<a class="gp-imgbox__link" href="'.e($s['link']).'">'.e($s['link_text']).' <span aria-hidden="true">→</span></a>';
        }

        return $html.'</div>';
    }
}
