<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Schema;

/** Résumé de l'article (son extrait, ou le début de son texte). */
class PostExcerptWidget extends ThemeWidget
{
    public function type(): string { return 'post-excerpt'; }

    public function label(): string { return __('Résumé de l’article'); }

    public function rules(): array
    {
        return [
            'length' => 'int:40:600',
            'align' => 'enum:left,center,right@r',
            'color' => 'color',
            'font_size' => 'int:8:60@r',
        ] + Schema::TYPOGRAPHY;
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $post = $this->post($ctx);
        if (! $post) {
            return '';
        }
        $this->text($ctx, $id, $s, ' p', false);

        return '<p>'.e($post->summary($s['length'] ?? 220)).'</p>';
    }
}
