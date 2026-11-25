<?php

namespace App\Templates;

use App\Transfer\SiteExporter;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Construit un modèle du catalogue : une archive au format d'export de site
 * (pages en français et en anglais, réglages de design, menus, illustrations),
 * installable comme n'importe quelle sauvegarde, et sa fiche pour l'index.
 *
 * Un modèle = un pack de secteur (templates/sectors/{pack}.php : les pages et
 * leurs textes) + un thème (couleurs, polices, variantes des blocs).
 */
final class TemplateBuilder
{
    public const LOCALES = ['fr', 'en'];

    public const CATALOG_VERSION = 1;

    public function __construct(private string $sectorsPath) {}

    /** Charge un pack de secteur : ['sector' => […], 'build' => fn (string $lang, Theme $theme, array $options) => […]]. */
    public function pack(string $name): array
    {
        $file = $this->sectorsPath.'/'.$name.'.php';
        if (! preg_match('/^[a-z0-9-]+$/', $name) || ! is_file($file)) {
            throw new RuntimeException("Pack de secteur introuvable : {$name}");
        }

        return require $file;
    }

    /**
     * @param  array{sector: string, pack?: string, theme: Theme, name: array, description: array, tags?: array, options?: array}  $def
     * @return array fiche de l'index du catalogue
     */
    public function build(string $id, array $def, string $outputDir): array
    {
        Kit::reset();
        $pack = $this->pack($def['pack'] ?? $def['sector']);
        $theme = $def['theme'];

        $pages = [];
        $terms = [];
        $posts = [];
        $layouts = [];
        $blog = ['locales' => [], 'per_page' => 9];
        $navigation = ['header' => ['enabled' => true, 'sticky' => true, 'style' => $def['header_style'] ?? 'light', 'cta_url' => null], 'footer' => ['enabled' => true, 'credit' => true], 'locales' => []];
        $summary = [];
        foreach (self::LOCALES as $lang) {
            $site = ($pack['build'])($lang, $theme, $def['options'] ?? []);
            $prefix = $lang === 'fr' ? '' : '/'.$lang;
            foreach ($site['pages'] as $page) {
                $pages[] = [
                    'title' => $page['title'],
                    'slug' => $page['slug'],
                    'locale' => $lang,
                    'status' => 'published',
                    'is_home' => $page['home'] ?? false,
                    'translation_group' => 'tpl-'.$id.'-'.$page['key'],
                    'meta_title' => $page['meta_title'] ?? null,
                    'meta_description' => $page['description'] ?? null,
                    'content' => ['version' => 2, 'elements' => $this->localize($page['sections'], $prefix)],
                ];
                $summary[$page['key']]['title'][$lang] = $page['title'];
            }
            $navigation['locales'][$lang] = $this->localize($site['navigation'], $prefix);
            if (! empty($site['blog'])) {
                $this->blog($id, $lang, $prefix, $theme, $site['blog'], $blog, $terms, $posts, $layouts);
            }
        }

        // Photo libre de droits choisie pour l'emplacement, sinon illustration d'attente.
        $media = [];
        $files = [];
        $credits = [];
        $allCredits = PhotoLibrary::read('credits');
        foreach (Kit::slots() as $slot => $info) {
            $photo = PhotoLibrary::file($slot);
            $name = $slot.($photo ? '.jpg' : '.svg');
            $media[] = ['id' => $info['id'], 'file' => 'media/'.$name, 'path' => 'templates/'.$name, 'original_name' => $name, 'alt' => $info['alt']];
            $files['media/'.$name] = $photo ? (string) file_get_contents($photo) : Placeholder::svg($theme, $info['icon'], $id.'/'.$slot);
            if ($photo && isset($allCredits[$slot])) {
                $credits[] = ['slot' => $slot] + array_intersect_key($allCredits[$slot], array_flip(['source', 'photographer', 'photographer_url', 'url', 'license']));
            }
        }

        $settings = $theme->settings() + ['navigation' => $navigation, 'locales' => self::LOCALES] + ($blog['locales'] ? ['blog' => $blog] : []);

        @mkdir($outputDir, 0775, true);
        $path = $outputDir.'/'.$id.'.zip';
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Impossible d'écrire {$path}");
        }
        $json = fn ($d) => json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $zip->addFromString('manifest.json', $json([
            'format' => SiteExporter::FORMAT,
            'format_version' => SiteExporter::FORMAT_VERSION,
            'geniuspress_version' => config('geniuspress.version'),
            'generator' => 'geniuspress:templates',
            'template' => ['id' => $id, 'sector' => $def['sector'], 'name' => $def['name']],
        ]));
        $zip->addFromString('settings.json', $json($settings));
        $zip->addFromString('pages.json', $json($pages));
        $zip->addFromString('media.json', $json($media));
        $zip->addFromString('terms.json', $json($terms));
        $zip->addFromString('posts.json', $json($posts));
        $zip->addFromString('layouts.json', $json($layouts));
        foreach ($files as $name => $data) {
            $zip->addFromString($name, $data);
        }
        $zip->close();

        return [
            'id' => $id,
            'sector' => $def['sector'],
            'name' => $def['name'],
            'description' => $def['description'],
            'tags' => $def['tags'] ?? [],
            'colors' => array_intersect_key($theme->colors, array_flip(['primary', 'secondary', 'accent', 'light'])),
            'fonts' => array_values(array_unique(array_filter([$theme->font, $theme->headingFont]))),
            'style' => ['hero' => $theme->hero, 'cards' => $theme->cards, 'buttons' => $theme->buttons],
            'locales' => self::LOCALES,
            'pages' => array_map(fn ($key, $p) => ['key' => $key, 'title' => $p['title']], array_keys($summary), $summary),
            'posts' => intdiv(count($posts), count(self::LOCALES)),
            'photos' => count($media),
            // Photos libres de droits (licences Pixabay ou Pexels : usage libre, crédit apprécié) ; le reste est illustré.
            'credits' => $credits,
            'file' => $id.'.zip',
            'size' => filesize($path),
            'sha256' => hash_file('sha256', $path),
        ];
    }

    /**
     * Blog d'un modèle dans une langue : réglage (adresse, titre), catégories,
     * articles d'exemple (photos du pack) et gabarits « Article » et « Archive ».
     * $data : base, title, categories [clé => nom], posts [[key, slug, title, excerpt, category, photo, body, heading?, quote?, tags?]].
     */
    private function blog(string $id, string $lang, string $prefix, Theme $theme, array $data, array &$blog, array &$terms, array &$posts, array &$layouts): void
    {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $blog['locales'][$lang] = ['base' => $data['base'], 'title' => $data['title']];

        $termIds = [];
        foreach ($data['categories'] as $key => $name) {
            $termIds[$key] = count($terms) + 1;
            $terms[] = ['id' => $termIds[$key], 'taxonomy' => 'category', 'name' => $name, 'slug' => Str::slug($name), 'locale' => $lang, 'translation_group' => 'tpl-'.$id.'-cat-'.$key];
        }
        foreach (array_values($data['posts']) as $i => $p) {
            $tags = [];
            foreach ($p['tags'] ?? [] as $tag) {
                $tagId = collect($terms)->first(fn ($t) => $t['taxonomy'] === 'tag' && $t['locale'] === $lang && $t['name'] === $tag)['id'] ?? null;
                if (! $tagId) {
                    $tagId = count($terms) + 1;
                    $terms[] = ['id' => $tagId, 'taxonomy' => 'tag', 'name' => $tag, 'slug' => Str::slug($tag), 'locale' => $lang, 'translation_group' => 'tpl-'.$id.'-tag-'.Str::slug($tag)];
                }
                $tags[] = $tagId;
            }
            $photo = Kit::photoFields(...$p['photo']);
            $posts[] = [
                'id' => count($posts) + 1,
                'title' => $p['title'],
                'slug' => $p['slug'],
                'locale' => $lang,
                'translation_group' => 'tpl-'.$id.'-post-'.$p['key'],
                'status' => 'published',
                'excerpt' => $p['excerpt'],
                'featured_media_id' => $photo['media_id'],
                // Articles récents : le premier il y a 3 jours, puis toutes les deux semaines.
                'published_at' => now()->startOfDay()->subDays(3 + 14 * $i)->setTime(9, 0)->toIso8601String(),
                'content' => $this->localize(BlogKit::body($p['body'], $p['heading'] ?? null, $p['quote'] ?? null), $prefix),
                'terms' => [...(isset($termIds[$p['category']]) ? [$termIds[$p['category']]] : []), ...$tags],
            ];
        }
        foreach (BlogKit::layouts($theme, $T) as $layout) {
            $layouts[] = $layout + ['id' => count($layouts) + 1, 'locale' => $lang, 'status' => 'published', 'priority' => 0, 'conditions' => [['mode' => 'include', 'target' => 'all']]];
        }
    }

    /**
     * Les liens internes des packs sont écrits pour le français (« /contact ») ;
     * en anglais, ils reçoivent le préfixe de langue (« /en/contact »).
     */
    private function localize(array $tree, string $prefix): array
    {
        if ($prefix === '') {
            return $tree;
        }
        array_walk_recursive($tree, function (&$v, $k) use ($prefix) {
            if (in_array($k, ['url', 'link', 'button_url', 'cta_url'], true) && is_string($v) && preg_match('#^/(?!/|storage/)#', $v)) {
                $v = $v === '/' ? $prefix : $prefix.$v;
            }
        });

        return $tree;
    }
}
