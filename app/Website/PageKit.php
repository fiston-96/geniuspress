<?php

namespace App\Website;

use League\CommonMark\GithubFlavoredMarkdownConverter;

/**
 * Petite boîte à outils pour décrire des pages GeniusPress en PHP
 * (sections → colonnes → widgets), au même format que le builder.
 * Utilisée par le site officiel (website/site.php) et, plus tard, par les
 * modèles de pages.
 */
class PageKit
{
    private static int $seq = 0;

    private static function id(string $prefix): string
    {
        return $prefix.str_pad((string) ++self::$seq, 8, '0', STR_PAD_LEFT);
    }

    /** Section : liste de colonnes (ou une seule liste de widgets). */
    public static function section(array $columns, array $settings = []): array
    {
        $isWidgetList = isset($columns[0]['type']) && $columns[0]['type'] !== 'column';
        $columns = $isWidgetList ? [self::col($columns)] : $columns;

        return ['id' => self::id('s'), 'type' => 'section', 'settings' => $settings, 'columns' => $columns];
    }

    public static function col(array $widgets, array $settings = []): array
    {
        return ['id' => self::id('c'), 'type' => 'column', 'settings' => $settings, 'widgets' => $widgets];
    }

    public static function widget(string $type, array $settings): array
    {
        return ['id' => self::id('w'), 'type' => $type, 'settings' => array_filter($settings, fn ($v) => $v !== null)];
    }

    public static function h(string $text, string $tag = 'h2', array $s = []): array
    {
        return self::widget('heading', ['text' => $text, 'tag' => $tag] + $s);
    }

    public static function p(string $html, array $s = []): array
    {
        return self::widget('text', ['html' => $html] + $s);
    }

    public static function btn(string $text, string $url, array $s = []): array
    {
        return self::widget('button', ['text' => $text, 'url' => $url] + $s);
    }

    public static function space(int $height, ?int $mobile = null): array
    {
        return self::widget('spacer', ['height' => $height, 'height_mobile' => $mobile]);
    }

    public static function links(?string $title, array $items, array $s = []): array
    {
        return self::widget('links', ['title' => $title, 'items' => array_map(fn ($label, $url) => compact('label', 'url'), array_keys($items), $items)] + $s);
    }

    /**
     * Convertit du Markdown en HTML pour un widget texte.
     * Le premier titre « # » est retiré et renvoyé à part (il devient le titre de la page).
     * $link reçoit chaque lien relatif et renvoie son adresse sur le site.
     *
     * @return array{title: ?string, html: string}
     */
    public static function markdown(string $markdown, ?callable $link = null): array
    {
        $title = null;
        if (preg_match('/^# (.+)$/m', $markdown, $m)) {
            $title = trim($m[1]);
            $markdown = preg_replace('/^# .+\R?/m', '', $markdown, 1);
        }

        $html = (string) (new GithubFlavoredMarkdownConverter(['html_input' => 'strip', 'allow_unsafe_links' => false]))->convert($markdown);
        // Le site n'autorise pas les titres h5/h6 dans les textes.
        $html = preg_replace('#<(/?)h[56]>#', '<$1h4>', $html);

        if ($link) {
            $html = preg_replace_callback('#href="([^"]+)"#', function ($m) use ($link) {
                $href = html_entity_decode($m[1]);
                if (preg_match('#^([a-z]+:|/|\#)#i', $href)) {
                    return $m[0];
                }

                return 'href="'.e($link($href)).'"';
            }, $html);
        }

        return ['title' => $title, 'html' => trim($html)];
    }
}
