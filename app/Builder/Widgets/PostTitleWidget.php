<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Schema;

/** Titre de l'article affiché (gabarit « Article »). */
class PostTitleWidget extends ThemeWidget
{
    public function type(): string { return 'post-title'; }

    public function label(): string { return __('Titre de l’article'); }

    public function rules(): array
    {
        return [
            'tag' => 'enum:h1,h2,h3,h4,h5,h6,p',
            'link' => 'bool',
            'align' => 'enum:left,center,right@r',
            'color' => 'color',
            'font_size' => 'int:8:160@r',
        ] + Schema::TYPOGRAPHY;
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $post = $this->post($ctx);
        if (! $post) {
            return '';
        }
        $this->text($ctx, $id, $s);
        $tag = $s['tag'] ?? 'h1';
        $title = e($post->title);

        return "<{$tag}>".(! empty($s['link']) ? '<a href="'.e($post->url()).'">'.$title.'</a>' : $title)."</{$tag}>";
    }
}
