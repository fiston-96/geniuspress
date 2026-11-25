<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Models\Post;
use App\Site\Permalinks;

/**
 * Base des widgets dynamiques du thème (titre de l'article, grille d'articles,
 * menu…) : ils affichent ce que regarde le visiteur (RenderContext::$site).
 * Sur une page sans article, les widgets d'article n'affichent rien.
 */
abstract class ThemeWidget extends Widget
{
    protected function post(RenderContext $ctx): ?Post
    {
        return $ctx->site?->post;
    }

    /** Langue du contenu affiché (pour les textes « Lire la suite », dates…). */
    protected function locale(RenderContext $ctx): string
    {
        return $ctx->site?->locale ?? Permalinks::defaultLocale();
    }

    protected function date(Post $post, string $locale): string
    {
        return $post->published_at ? '<time datetime="'.$post->published_at->toDateString().'">'.e($post->published_at->locale($locale)->translatedFormat('j F Y')).'</time>' : '';
    }

    /** Taille de police par appareil, titres (mêmes règles que le widget Titre). */
    protected function text(RenderContext $ctx, string $id, array $s, string $scope = ' > *', bool $heading = true): void
    {
        $ctx->rule($id, ['color' => $s['color'] ?? null, 'font-family' => ($heading && ! isset($s['font_family'])) ? 'var(--gp-heading-font)' : null], false, $scope);
        $ctx->responsive($id, $s, 'align', fn ($v) => ['text-align' => $v], $scope);
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], $scope);
        $ctx->typography($id, $s, $scope);
    }
}
