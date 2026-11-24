<?php

namespace App\Templates;

use App\Transfer\SiteExporter;
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
        }

        // Illustrations d'attente pour chaque emplacement de photo utilisé.
        $media = [];
        $files = [];
        foreach (Kit::slots() as $slot => $info) {
            $media[] = ['id' => $info['id'], 'file' => 'media/'.$slot.'.svg', 'path' => 'templates/'.$slot.'.svg', 'original_name' => $slot.'.svg', 'alt' => $info['alt']];
            $files['media/'.$slot.'.svg'] = Placeholder::svg($theme, $info['icon'], $id.'/'.$slot);
        }

        $settings = $theme->settings() + ['navigation' => $navigation, 'locales' => self::LOCALES];

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
            'photos' => count($media),
            'file' => $id.'.zip',
            'size' => filesize($path),
            'sha256' => hash_file('sha256', $path),
        ];
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
