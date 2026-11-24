<?php

namespace App\Site;

use App\Builder\ContentSanitizer;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use Illuminate\Support\Str;

/**
 * En-tête, menus et pied de page communs à tout le site public.
 * Les libellés et les liens existent pour chaque langue du site.
 *
 * Réglage « navigation » :
 *   header: { enabled, sticky, style: light|dark, cta_url (ancien réglage commun, utilisé en repli) }
 *   locales: { fr: { menu: [{label, url}], cta_label, cta_url, footer_text, footer_links: [{label, url}] }, … }
 *   footer: { enabled, credit }
 */
class Navigation
{
    public const MAX_LINKS = 12;

    public static function defaults(): array
    {
        return [
            'header' => ['enabled' => true, 'sticky' => true, 'style' => 'light', 'cta_url' => null],
            'footer' => ['enabled' => true, 'credit' => true],
            'locales' => [],
        ];
    }

    public static function all(): array
    {
        $stored = Setting::read('navigation');

        return is_array($stored) ? array_replace_recursive(self::defaults(), $stored) : self::defaults();
    }

    /** Tout ce qu'il faut pour afficher l'en-tête et le pied de page d'une page. */
    public static function forPage(Page $page): array
    {
        return self::forContext(SiteContext::forPage($page));
    }

    /** En-tête et pied de page pour ce que regarde le visiteur (page, article, archive…). */
    public static function forContext(SiteContext $site): array
    {
        $nav = self::all();
        $l = $nav['locales'][$site->locale] ?? [];

        return [
            'header' => $nav['header'],
            'footer' => $nav['footer'],
            'menu' => $l['menu'] ?? [],
            'cta_label' => $l['cta_label'] ?? null,
            'cta_url' => $l['cta_url'] ?? $nav['header']['cta_url'],
            'footer_text' => $l['footer_text'] ?? null,
            'footer_links' => $l['footer_links'] ?? [],
            'home_url' => self::homeUrl($site->locale),
            'languages' => self::languages($site),
        ];
    }

    /** Valide et enregistre les réglages envoyés par le formulaire « Apparence ». */
    public static function save(array $input, array $locales): void
    {
        $text = fn ($v, $max) => is_string($v) && trim($v) !== '' ? Str::limit(trim(strip_tags($v)), $max, '') : null;
        $links = function ($rows) use ($text) {
            $out = [];
            foreach (array_slice((array) $rows, 0, self::MAX_LINKS) as $row) {
                $label = $text($row['label'] ?? null, 60);
                $url = ContentSanitizer::url($row['url'] ?? null);
                if ($label && $url) {
                    $out[] = ['label' => $label, 'url' => $url];
                }
            }

            return $out;
        };

        $nav = [
            'header' => [
                'enabled' => (bool) ($input['header']['enabled'] ?? false),
                'sticky' => (bool) ($input['header']['sticky'] ?? false),
                'style' => ($input['header']['style'] ?? 'light') === 'dark' ? 'dark' : 'light',
                'cta_url' => ContentSanitizer::url($input['header']['cta_url'] ?? null),
            ],
            'footer' => [
                'enabled' => (bool) ($input['footer']['enabled'] ?? false),
                'credit' => (bool) ($input['footer']['credit'] ?? false),
            ],
            'locales' => [],
        ];
        foreach ($locales as $locale) {
            $l = (array) ($input['locales'][$locale] ?? []);
            $nav['locales'][$locale] = [
                'menu' => $links($l['menu'] ?? []),
                'cta_label' => $text($l['cta_label'] ?? null, 40),
                'cta_url' => ContentSanitizer::url($l['cta_url'] ?? null),
                'footer_text' => $text($l['footer_text'] ?? null, 500),
                'footer_links' => $links($l['footer_links'] ?? []),
            ];
        }

        Setting::write(['navigation' => $nav]);
    }

    private static function homeUrl(string $locale): string
    {
        $default = ((array) Setting::read('locales', ['fr']))[0] ?? 'fr';

        return url($locale === $default ? '/' : $locale);
    }

    /** Liens vers le même contenu dans les autres langues (ou leur page d'accueil). */
    private static function languages(SiteContext $site): array
    {
        $locales = (array) Setting::read('locales', ['fr']);
        if (count($locales) < 2) {
            return [];
        }
        // Traductions du contenu affiché : même groupe de traduction (page, article, catégorie).
        $translations = match (true) {
            $site->page !== null => Page::query()->published()->where('translation_group', $site->page->translation_group)->get()->keyBy('locale'),
            $site->post !== null => Post::query()->published()->where('translation_group', $site->post->translation_group)->get()->keyBy('locale'),
            $site->term !== null => Term::query()->where('translation_group', $site->term->translation_group)->get()->keyBy('locale'),
            default => collect(),
        };
        $names = \App\Http\Controllers\Admin\SettingController::AVAILABLE_LOCALES;

        $out = [];
        foreach ($locales as $locale) {
            $target = $translations[$locale] ?? null;
            $url = match (true) {
                $target !== null => $target->url(),
                $site->kind === SiteContext::BLOG => Permalinks::blog($locale),
                default => self::homeUrl($locale),
            };
            $out[] = [
                'code' => $locale,
                'name' => $names[$locale] ?? $locale,
                'url' => $url,
                'current' => $locale === $site->locale,
                'translated' => $target !== null || $site->kind === SiteContext::BLOG,
            ];
        }

        return $out;
    }
}
