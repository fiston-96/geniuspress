<?php

namespace App\Http\Controllers;

use App\Builder\Renderer;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use ZipArchive;

/**
 * Démonstration en direct d'un modèle du catalogue, sans l'installer :
 * /catalogue/{modèle} (accueil), /catalogue/{modèle}/a-propos, /catalogue/{modèle}/en/about…
 * Active en local, et sur geniuspress.site avec GP_CATALOG_DEMOS=true.
 */
class CatalogDemoController extends Controller
{
    private const URL_KEYS = ['url', 'link', 'button_url', 'cta_url', 'background_image'];

    public function show(Request $request, Renderer $renderer, string $template, ?string $path = null)
    {
        $data = $this->load($template);
        $base = url('/catalogue/'.$template);

        $segments = array_values(array_filter(explode('/', trim((string) $path, '/'))));
        $locale = ($segments[0] ?? null) === 'en' ? array_shift($segments) : 'fr';
        $slug = implode('/', $segments);
        $item = collect($data['pages'])->first(fn ($p) => $p['locale'] === $locale && ($slug === '' ? ! empty($p['is_home']) : $p['slug'] === $slug));
        abort_unless($item, 404);

        $settings = $data['settings'];
        $settings['site_name'] = $data['manifest']['template']['name'][$locale] ?? $template;
        $settings['locales'] = [$locale];
        $settings['navigation']['locales'] = [$locale => $this->rewrite($settings['navigation']['locales'][$locale] ?? [], $base, $template)];
        $settings['logo_url'] = null;
        Setting::override($settings);

        $page = new Page([
            'title' => $item['title'], 'slug' => $item['slug'], 'locale' => $locale, 'status' => Page::STATUS_PUBLISHED,
            'is_home' => ! empty($item['is_home']), 'meta_title' => $item['meta_title'] ?? null, 'meta_description' => $item['meta_description'] ?? null,
            'translation_group' => $item['translation_group'] ?? null,
        ]);

        try {
            // La vue est rendue tout de suite : les réglages du modèle ne servent qu'à cette réponse.
            return response()->view('public.page', [
                'page' => $page,
                'rendered' => $renderer->render($this->rewrite($item['content'], $base, $template)),
                'settings' => Setting::publicValues(),
                'preview' => false,
            ])->header('X-Robots-Tag', 'noindex');
        } finally {
            Setting::override([]);
        }
    }

    /** Illustrations et photos d'un modèle, lues dans son archive. */
    public function media(string $template, string $file)
    {
        abort_unless(preg_match('/^[a-z0-9-]{1,80}\.(svg|jpg|jpeg|png|webp)$/', $file), 404);
        $zip = new ZipArchive;
        abort_unless($zip->open($this->file($template)) === true, 404);
        $data = $zip->getFromName('media/'.$file);
        $zip->close();
        abort_if($data === false, 404);
        $type = ['svg' => 'image/svg+xml', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][pathinfo($file, PATHINFO_EXTENSION)];

        return response($data, 200, [
            'Content-Type' => $type,
            'Cache-Control' => 'public, max-age=86400',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'",
        ]);
    }

    private function file(string $template): string
    {
        abort_unless(config('geniuspress.catalog.demos') || app()->isLocal(), 404);
        abort_unless(preg_match('/^[a-z0-9-]{2,60}$/', $template), 404);
        $file = config('geniuspress.catalog.path').'/'.$template.'.zip';
        abort_unless(is_file($file), 404);

        return $file;
    }

    /** Contenu de l'archive, gardé en cache tant que le fichier ne change pas. */
    private function load(string $template): array
    {
        $file = $this->file($template);

        return Cache::remember('catalog-demo:'.$template.':'.filemtime($file), 3600, function () use ($file) {
            $zip = new ZipArchive;
            abort_unless($zip->open($file) === true, 404);
            $read = fn ($name) => json_decode((string) $zip->getFromName($name), true) ?: [];
            $data = ['manifest' => $read('manifest.json'), 'settings' => $read('settings.json'), 'pages' => $read('pages.json')];
            $zip->close();

            return $data;
        });
    }

    /** Liens internes et images du modèle → adresses de la démonstration. */
    private function rewrite(array $tree, string $base, string $template): array
    {
        // Les numéros de médias ne valent qu'après installation : ici, seules les adresses comptent.
        $strip = function (array $node) use (&$strip) {
            unset($node['media_id']);
            foreach ($node as $k => $v) {
                if (is_array($v)) {
                    $node[$k] = $strip($v);
                }
            }

            return $node;
        };
        $tree = $strip($tree);

        array_walk_recursive($tree, function (&$v, $k) use ($base, $template) {
            if (! is_string($v)) {
                return;
            }
            if (str_starts_with($v, '/storage/templates/')) {
                $v = url('/catalogue/'.$template.'/media/'.substr($v, strlen('/storage/templates/')));
            } elseif (in_array($k, self::URL_KEYS, true) && preg_match('#^/(?!/)#', $v)) {
                $v = rtrim($base.$v, '/');
            } elseif (str_contains($v, 'href="/')) {
                $v = preg_replace('#href="/(?!/)#', 'href="'.$base.'/', $v);
            }
        });

        return $tree;
    }
}
