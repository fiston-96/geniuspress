<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Informations de l'article : date, auteur, catégories, temps de lecture, étiquettes. */
class PostMetaWidget extends ThemeWidget
{
    public function type(): string { return 'post-meta'; }

    public function label(): string { return __('Informations de l’article'); }

    public function rules(): array
    {
        return [
            'show_date' => 'bool',
            'show_author' => 'bool',
            'show_category' => 'bool',
            'show_reading' => 'bool',
            'show_tags' => 'bool',
            'icons' => 'bool',
            'align' => 'enum:left,center,right@r',
            'color' => 'color',
            'link_color' => 'color',
            'font_size' => 'int:8:40@r',
        ];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $post = $this->post($ctx);
        if (! $post) {
            return '';
        }
        $locale = $this->locale($ctx);
        $icon = fn (string $name) => ($s['icons'] ?? true) ? Icons::svg('lucide:'.$name) : '';
        $links = fn ($terms, string $prefix = '') => $terms->map(fn ($t) => '<a href="'.e($t->url()).'">'.$prefix.e($t->name).'</a>')->join(', ');

        $items = [];
        if (($s['show_date'] ?? true) && $post->published_at) {
            $items[] = $icon('calendar').$this->date($post, $locale);
        }
        if (($s['show_author'] ?? true) && $post->author) {
            $items[] = $icon('user-round').'<span>'.e($post->author->name).'</span>';
        }
        if (($s['show_category'] ?? true) && $post->categories->isNotEmpty()) {
            $items[] = $icon('folder').'<span>'.$links($post->categories).'</span>';
        }
        if ($s['show_reading'] ?? true) {
            $items[] = $icon('clock').'<span>'.e(__(':count min de lecture', ['count' => $post->readingMinutes()], $locale)).'</span>';
        }
        if (! empty($s['show_tags']) && $post->tags->isNotEmpty()) {
            $items[] = $icon('tag').'<span>'.$links($post->tags, '#').'</span>';
        }
        if (! $items) {
            return '';
        }
        $ctx->rule($id, ['color' => $s['color'] ?? null], false, ' .gp-pmeta');
        $ctx->rule($id, ['color' => $s['link_color'] ?? null], false, ' .gp-pmeta a');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$v]], ' .gp-pmeta');
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-pmeta');

        return '<div class="gp-pmeta">'.implode('', array_map(fn ($i) => '<span class="gp-pmeta__item">'.$i.'</span>', $items)).'</div>';
    }
}
