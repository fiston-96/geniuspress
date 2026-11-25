<?php

namespace App\Templates;

use App\Templates\Blocks as B;
use App\Templates\Kit as K;

/**
 * Blog des modèles : contenu des articles d'exemple et gabarits du thème
 * (« Article » et « Archive »), aux couleurs et dans le style de chaque modèle.
 * Comme les blocs, ils n'utilisent que les couleurs globales.
 */
final class BlogKit
{
    /** Contenu d'un article : paragraphes, intertitre et citation facultatifs. */
    public static function body(array $paragraphs, ?string $heading = null, ?string $quote = null): array
    {
        $items = [];
        foreach (array_values($paragraphs) as $i => $text) {
            if ($heading && $i === 1) {
                $items[] = K::h($heading, 'h2', ['font_size' => 28, 'font_size_mobile' => 23, 'font_weight' => '700', 'margin' => ['t' => 12, 'r' => 0, 'b' => 0, 'l' => 0]]);
            }
            $items[] = K::p($text, ['font_size' => 18, 'line_height' => '1.75', 'color' => 'global:texte']);
            if ($quote && $i === 0) {
                $items[] = K::box([K::h($quote, 'p', ['font_size' => 22, 'font_weight' => '600', 'line_height' => '1.45', 'font_family' => 'global:heading', 'color' => 'global:secondary'])], [
                    'padding' => ['t' => 4, 'r' => 0, 'b' => 4, 'l' => 24], 'border_style' => 'solid',
                    'border_width' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 4], 'border_color' => 'global:accent',
                ]);
            }
        }

        return ['version' => 2, 'elements' => [K::box($items, ['gap' => 20, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]])]];
    }

    /** Fond et couleur du haut des gabarits, comme l'en-tête des pages du modèle. */
    private static function header(Theme $t): array
    {
        return match ($t->hero) {
            'split' => [['background_color' => 'global:clair'], false],
            'gradient' => [['background_type' => 'gradient', 'gradient_from' => 'global:primary', 'gradient_to' => 'global:secondary', 'gradient_angle' => 135, 'text_color' => '#ffffff'], true],
            default => [['background_color' => 'global:secondary', 'text_color' => '#ffffff'], true],
        };
    }

    /**
     * Gabarits du blog dans une langue : « Article » et « Archive ».
     * Options (sites de médias) : share — barre de progression de lecture et boutons de partage
     * sur l'article ; terms — rubriques (catégories) sous la recherche de l'archive et étiquettes
     * populaires en bas de l'article.
     *
     * @return list<array{name: string, type: string, content: array}>
     */
    public static function layouts(Theme $t, callable $T, array $options = []): array
    {
        [$bg, $dark] = self::header($t);
        $muted = $dark ? 'rgba(255,255,255,0.78)' : 'global:doux';
        $zero = ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0];
        $narrow = fn (array $children, array $s = []) => K::box($children, $s + ['gap' => 18, 'content_width' => 'boxed', 'max_width' => 820, 'padding' => $zero]);

        $share = ! empty($options['share']);
        $topics = ! empty($options['terms']);

        $single = [
            K::section($t, [$share ? K::w('reading-progress', ['color' => 'global:accent', 'height' => 4, 'position' => 'top']) : null, $narrow([
                K::w('breadcrumbs', ['align' => 'center', 'color' => $muted, 'link_color' => $dark ? '#ffffff' : 'global:primary', 'font_size' => 14]),
                K::w('post-title', ['tag' => 'h1', 'align' => 'center', 'font_size' => 48, 'font_size_tablet' => 40, 'font_size_mobile' => 32, 'font_weight' => '800', 'line_height' => '1.12', 'color' => $dark ? '#ffffff' : null]),
                K::w('post-meta', ['align' => 'center', 'color' => $muted, 'link_color' => $dark ? '#ffffff' : 'global:primary', 'show_date' => true, 'show_author' => true, 'show_category' => true, 'show_reading' => true, 'icons' => true]),
            ])], $bg + ['padding' => ['t' => 88, 'r' => 20, 'b' => 80, 'l' => 20], 'padding_mobile' => ['t' => 56, 'b' => 48]]),
            K::section($t, [$narrow([
                K::w('post-image', ['ratio' => '16:9', 'radius' => $t->radius]),
                K::w('post-content', []),
                K::w('post-meta', ['show_date' => false, 'show_author' => false, 'show_category' => false, 'show_reading' => false, 'show_tags' => true, 'icons' => true, 'link_color' => 'global:primary']),
                $share ? K::w('share-buttons', ['label' => $T('Partager cet article', 'Share this article'), 'style' => 'brand', 'shape' => $t->buttons === 'square' ? 'square' : 'circle', 'size' => 40, 'align' => 'left']) : null,
                K::w('divider', ['color' => '#e2e8f0']),
                K::w('post-nav', ['show_titles' => true]),
            ], ['gap' => 28])], ['padding' => ['t' => 56, 'r' => 20, 'b' => 72, 'l' => 20], 'padding_mobile' => ['t' => 32, 'b' => 48]]),
            B::posts($t, ['title' => $T('À lire aussi', 'Keep reading'), 'source' => 'latest', 'count' => 3, 'bg' => 'light', 'more' => $T('Lire la suite', 'Read more')]),
            $topics ? B::terms($t, ['title' => $T('Sujets populaires', 'Popular topics'), 'taxonomy' => 'tag', 'layout' => 'cloud', 'count' => false]) : null,
        ];

        $archive = [
            K::section($t, [$narrow([
                K::w('archive-title', ['tag' => 'h1', 'align' => 'center', 'show_prefix' => ! $dark, 'show_description' => true, 'font_size' => 48, 'font_size_tablet' => 40, 'font_size_mobile' => 32, 'font_weight' => '800', 'color' => $dark ? '#ffffff' : null]),
                K::w('search-form', ['placeholder' => $T('Rechercher un article…', 'Search posts…'), 'button_text' => $T('Rechercher', 'Search'), 'button_color' => $dark ? 'global:accent' : 'global:primary', 'radius' => min(12, $t->buttonRadius())]),
                $topics ? K::w('terms-list', ['taxonomy' => 'category', 'layout' => 'inline', 'show_count' => false, 'align' => 'center', 'font_size' => 15, 'color' => $dark ? '#ffffff' : null, 'accent' => $dark ? 'global:accent' : 'global:primary']) : null,
            ], ['gap' => 22, 'max_width' => 680])], $bg + ['padding' => ['t' => 88, 'r' => 20, 'b' => 80, 'l' => 20], 'padding_mobile' => ['t' => 56, 'b' => 48]]),
            B::posts($t, ['source' => 'current', 'cols' => 3, 'more' => $T('Lire la suite', 'Read more')]),
        ];

        return [
            ['name' => $T('Article', 'Post'), 'type' => 'single', 'content' => ['version' => 2, 'elements' => array_values(array_filter($single))]],
            ['name' => $T('Archive du blog', 'Blog archive'), 'type' => 'archive', 'content' => ['version' => 2, 'elements' => $archive]],
        ];
    }
}
