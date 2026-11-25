<?php

namespace App\Site;

use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;

/**
 * Adresses publiques des articles et des archives.
 *
 *   /blog                         liste des articles (ou la page dont l'adresse est « blog »)
 *   /blog/mon-article             article
 *   /blog/categorie/actualites    catégorie (« category » en anglais)
 *   /blog/etiquette/sante         étiquette (« tag » en anglais)
 *   /blog/rss                     flux RSS
 *   /?s=mot                       recherche
 *
 * Dans les autres langues que la langue principale, tout est préfixé : /en/blog/…
 * L'adresse du blog (« blog ») se règle par langue dans le réglage « blog ».
 */
final class Permalinks
{
    /** Racine des adresses (démonstration d'un modèle : /catalogue/{modèle}) ; null = le site. */
    private static ?string $root = null;

    public static function useRoot(?string $root): void
    {
        self::$root = $root ? rtrim($root, '/') : null;
    }

    private static function url(string $path): string
    {
        return self::$root !== null ? self::$root.($path === '' || $path === '/' ? '' : '/'.ltrim($path, '/')) : url($path);
    }

    /** Mots des archives, par langue (anglais par défaut). */
    private const WORDS = [
        'fr' => ['category' => 'categorie', 'tag' => 'etiquette'],
        'en' => ['category' => 'category', 'tag' => 'tag'],
    ];

    public static function defaultLocale(): string
    {
        return ((array) Setting::read('locales', ['fr']))[0] ?? 'fr';
    }

    /** « » pour la langue principale, « en/ » pour les autres. */
    public static function prefix(string $locale): string
    {
        return $locale === self::defaultLocale() ? '' : $locale.'/';
    }

    public static function blogBase(string $locale): string
    {
        $base = ((array) Setting::read('blog'))['locales'][$locale]['base'] ?? null;

        return is_string($base) && $base !== '' ? $base : 'blog';
    }

    public static function blogTitle(string $locale): string
    {
        $title = ((array) Setting::read('blog'))['locales'][$locale]['title'] ?? null;

        return is_string($title) && $title !== '' ? $title : 'Blog';
    }

    public static function perPage(): int
    {
        return max(1, min(48, (int) (((array) Setting::read('blog'))['per_page'] ?? 9)));
    }

    /** Mot d'archive : « categorie », « category », « etiquette », « tag ». */
    public static function word(string $locale, string $taxonomy): string
    {
        return (self::WORDS[$locale] ?? self::WORDS['en'])[$taxonomy];
    }

    public static function blog(string $locale): string
    {
        return self::url(self::prefix($locale).self::blogBase($locale));
    }

    public static function post(Post $post): string
    {
        return self::url(self::prefix($post->locale).self::blogBase($post->locale).'/'.$post->slug);
    }

    public static function term(Term $term): string
    {
        return self::url(self::prefix($term->locale).self::blogBase($term->locale).'/'.self::word($term->locale, $term->taxonomy).'/'.$term->slug);
    }

    public static function feed(string $locale): string
    {
        return self::url(self::prefix($locale).self::blogBase($locale).'/rss');
    }

    public static function search(string $locale, ?string $query = null): string
    {
        $home = self::url(rtrim(self::prefix($locale), '/') ?: '/');

        return $query === null ? $home : $home.'?'.http_build_query(['s' => $query]);
    }

    public static function home(string $locale): string
    {
        return self::url(rtrim(self::prefix($locale), '/') ?: '/');
    }
}
