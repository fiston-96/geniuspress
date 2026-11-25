<?php

namespace App\Site;

use App\Builder\ContentSanitizer;
use App\Models\Setting;

/**
 * Référencement d'une page publique : adresse canonique, langue par défaut (x-default),
 * aperçu de partage (Open Graph, X/Twitter), vérification Google et Bing, données structurées
 * (site, organisation, article, fil d'Ariane). Utilisé par resources/views/public/page.blade.php.
 */
class Seo
{
    /** Langue du contenu → locale Open Graph. */
    private const OG_LOCALES = ['fr' => 'fr_FR', 'en' => 'en_US', 'pt' => 'pt_PT', 'es' => 'es_ES', 'ar' => 'ar_AR', 'sw' => 'sw_KE', 'ln' => 'ln_CD'];

    /** Le site accepte-t-il les moteurs de recherche (Réglages → Référencement) ? */
    public static function indexable(?array $settings = null): bool
    {
        return (bool) (($settings ?? Setting::publicValues())['search_engines'] ?? true);
    }

    /**
     * Tout ce qu'il faut pour l'en-tête de la page.
     *
     * @param  array  $languages  Navigation::forContext($site)['languages']
     * @return array{noindex: bool, canonical: ?string, x_default: ?string, og: array<string, string>, twitter: array<string, string>, verify: array<string, string>, jsonld: array<int, array>}
     */
    public static function for(SiteContext $site, array $settings, array $languages, string $title, ?string $description, bool $preview = false): array
    {
        $noindex = $preview || ! self::indexable($settings) || in_array($site->kind, [SiteContext::SEARCH, SiteContext::NOT_FOUND], true);
        $canonical = $preview || $site->kind === SiteContext::NOT_FOUND ? null : self::canonical();
        $image = self::image($site, $settings);

        // Langue par défaut : la version dans la première langue du site, quand elle existe.
        $default = ((array) ($settings['locales'] ?? ['fr']))[0] ?? 'fr';
        $xDefault = collect($languages)->first(fn ($l) => $l['code'] === $default && $l['translated'])['url'] ?? null;
        $translated = collect($languages)->where('translated', true)->count() > 1;

        $og = array_filter([
            'og:type' => $site->kind === SiteContext::POST ? 'article' : 'website',
            'og:site_name' => $settings['site_name'] ?? null,
            'og:title' => $title,
            'og:description' => $description,
            'og:url' => $canonical,
            'og:locale' => self::OG_LOCALES[$site->locale] ?? $site->locale,
            'og:image' => $image,
            'article:published_time' => $site->kind === SiteContext::POST ? $site->post->published_at?->toIso8601String() : null,
        ]);
        $twitter = array_filter([
            'twitter:card' => $image ? 'summary_large_image' : 'summary',
            'twitter:title' => $title,
            'twitter:description' => $description,
            'twitter:image' => $image,
        ]);
        $verify = array_filter([
            'google-site-verification' => $settings['google_site_verification'] ?? null,
            'msvalidate.01' => $settings['bing_site_verification'] ?? null,
        ]);

        return [
            'noindex' => $noindex,
            'canonical' => $canonical,
            'x_default' => $translated ? $xDefault : null,
            'og' => $og,
            'twitter' => $twitter,
            'verify' => $verify,
            'jsonld' => $noindex ? [] : self::structuredData($site, $settings, $title, $description, $image, $canonical),
        ];
    }

    /** Adresse officielle de la page : sans paramètres, sauf le numéro de page d'une liste. */
    public static function canonical(): string
    {
        $page = (int) request()->query('page', 1);

        return request()->url().($page > 1 ? '?page='.$page : '');
    }

    /**
     * Image de partage : celle de l'article, sinon la première image de la page, sinon l'image
     * par défaut choisie dans les réglages. Adresse complète ; jamais un SVG (mal accepté par les réseaux).
     */
    public static function image(SiteContext $site, array $settings): ?string
    {
        $candidates = [
            $site->post?->featuredMedia?->url,
            $site->page ? self::firstImage($site->page->content['elements'] ?? []) : null,
            $settings['og_image'] ?? null,
        ];
        foreach ($candidates as $url) {
            if (is_string($url) && $url !== '' && ContentSanitizer::url($url) && ! str_ends_with(strtolower(parse_url($url, PHP_URL_PATH) ?? ''), '.svg')) {
                return url($url);
            }
        }

        return null;
    }

    /** Première image du contenu (widget Image ou image de fond d'un conteneur), dans l'ordre de la page. */
    private static function firstImage(array $nodes): ?string
    {
        foreach ($nodes as $node) {
            $s = $node['settings'] ?? [];
            $url = ($node['type'] ?? '') === 'image' ? ($s['url'] ?? null) : ($s['background_image'] ?? null);
            if (is_string($url) && $url !== '' && ! str_ends_with(strtolower($url), '.svg')) {
                return $url;
            }
            if ($found = self::firstImage($node['children'] ?? [])) {
                return $found;
            }
        }

        return null;
    }

    /** Données structurées schema.org : site et organisation sur l'accueil, article et fil d'Ariane sur un article. */
    private static function structuredData(SiteContext $site, array $settings, string $title, ?string $description, ?string $image, ?string $url): array
    {
        $name = $settings['site_name'] ?? 'GeniusPress';
        $home = url(rtrim(Permalinks::prefix($site->locale), '/') ?: '/');
        $logo = ! empty($settings['logo_url']) ? url($settings['logo_url']) : null;
        $organization = array_filter(['@type' => 'Organization', 'name' => $name, 'url' => $home, 'logo' => $logo]);
        $out = [];

        if ($site->isHome()) {
            $out[] = ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => $name, 'url' => $home, 'inLanguage' => $site->locale,
                'potentialAction' => ['@type' => 'SearchAction', 'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $home.'?s={search_term_string}'], 'query-input' => 'required name=search_term_string']];
            $out[] = ['@context' => 'https://schema.org'] + $organization;
        }

        if ($site->kind === SiteContext::POST) {
            $post = $site->post;
            $out[] = array_filter([
                '@context' => 'https://schema.org', '@type' => 'BlogPosting',
                'headline' => mb_substr($post->title, 0, 110), 'description' => $description, 'image' => $image, 'inLanguage' => $site->locale,
                'datePublished' => $post->published_at?->toIso8601String(), 'dateModified' => $post->updated_at?->toIso8601String(),
                'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : null,
                'publisher' => $organization, 'mainEntityOfPage' => $url,
            ]);
            $crumbs = [[$name, $home], [Permalinks::blogTitle($site->locale), Permalinks::blog($site->locale)]];
            if ($category = $post->categories->first()) {
                $crumbs[] = [$category->name, $category->url()];
            }
            $crumbs[] = [$post->title, $url];
            $out[] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => array_map(
                fn ($c, $i) => array_filter(['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c[0], 'item' => $c[1]]), $crumbs, array_keys($crumbs),
            )];
        }

        return $out;
    }
}
