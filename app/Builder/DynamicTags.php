<?php

namespace App\Builder;

use App\Models\Setting;
use App\Site\Permalinks;
use App\Site\SiteContext;

/**
 * Balises dynamiques dans les textes des widgets : « {{site.name}} », « {{page.title}} »,
 * « {{date.year}} »… remplacées au rendu par leur valeur (échappée). Une balise inconnue
 * ou sans valeur dans le contexte (« {{post.title}} » hors d'un article) est retirée ;
 * un texte entre accolades qui n'a pas la forme d'une balise reste tel quel.
 *
 * Même liste que TAGS dans resources/js/builder/dynamic-tags.js (aperçu du builder).
 */
final class DynamicTags
{
    public const TAGS = [
        'site.name', 'site.tagline', 'site.url',
        'page.title', 'page.url',
        'post.title', 'post.excerpt', 'post.date', 'post.author', 'post.category',
        'archive.title', 'search.query',
        'date.year', 'date.today',
    ];

    private const PATTERN = '/\{\{\s*([a-z]+\.[a-z_]+)\s*\}\}/';

    public static function has(string $html): bool
    {
        return str_contains($html, '{{') && preg_match(self::PATTERN, $html) === 1;
    }

    public static function replace(string $html, ?SiteContext $site): string
    {
        if (! self::has($html)) {
            return $html;
        }
        $values = null;

        return (string) preg_replace_callback(self::PATTERN, function ($m) use ($site, &$values) {
            if (! in_array($m[1], self::TAGS, true)) {
                return $m[0];
            }
            $values ??= self::values($site);

            return e($values[$m[1]] ?? '');
        }, $html);
    }

    /** @return array<string, string|null> */
    public static function values(?SiteContext $site): array
    {
        $locale = $site?->locale ?? app()->getLocale();
        $post = $site?->kind === SiteContext::POST ? $site->post : null;
        $term = in_array($site?->kind, [SiteContext::CATEGORY, SiteContext::TAG], true) ? $site->term : null;

        return [
            'site.name' => Setting::read('site_name'),
            'site.tagline' => Setting::read('site_tagline'),
            'site.url' => rtrim(url(Permalinks::home($locale)), '/'),
            'page.title' => $site && $site->kind !== SiteContext::NOT_FOUND ? $site->title() : null,
            'page.url' => $site ? request()->url() : null,
            'post.title' => $post?->title,
            'post.excerpt' => $post?->summary(),
            'post.date' => $post?->published_at?->locale($locale)->translatedFormat('j F Y'),
            'post.author' => $post?->author?->name,
            'post.category' => $post?->categories?->first()?->name,
            'archive.title' => $site?->isArchive() ? $site->title() : ($term?->name),
            'search.query' => $site?->search,
            'date.year' => now()->format('Y'),
            'date.today' => now()->locale($locale)->translatedFormat('j F Y'),
        ];
    }
}
