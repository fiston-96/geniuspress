<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Renderer;

/** Contenu de l'article, tel qu'il a été écrit dans le builder (gabarit « Article »). */
class PostContentWidget extends ThemeWidget
{
    public function type(): string { return 'post-content'; }

    public function label(): string { return __('Contenu de l’article'); }

    public function rules(): array
    {
        return ['color' => 'color', 'font_size' => 'int:8:60@r'];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $post = $this->post($ctx);
        // Le contenu d'un article ne peut pas s'inclure lui-même.
        if (! $post || $ctx->site->insideContent) {
            return '';
        }
        $rendered = app(Renderer::class)->render($post->content, null, $ctx->site->forContent());
        $ctx->embed($rendered);
        $ctx->rule($id, ['color' => $s['color'] ?? null]);
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px']);

        return '<div class="gp-post-content">'.$rendered['html'].'</div>';
    }
}
