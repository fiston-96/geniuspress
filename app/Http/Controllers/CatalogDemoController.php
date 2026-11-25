<?php

namespace App\Http\Controllers;

use App\Models\Layout;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Site\Permalinks;
use App\Site\SiteContext;
use App\Site\SiteResponder;
use App\Site\ThemeLayouts;
use App\Templates\DemoMedia;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use ZipArchive;

/**
 * Démonstration en direct d'un modèle du catalogue, sans l'installer :
 * /catalogue/{modèle} (accueil), /catalogue/{modèle}/a-propos, /catalogue/{modèle}/en/about,
 * et son blog : /catalogue/{modèle}/actualites, …/actualites/{article}, …/actualites/categorie/{catégorie}.
 * Tout est lu dans l'archive du modèle et gardé en mémoire : rien n'est écrit en base.
 * Active en local, et sur geniuspress.site avec GP_CATALOG_DEMOS=true.
 */
class CatalogDemoController extends Controller
{
    private const URL_KEYS = ['url', 'link', 'button_url', 'cta_url', 'background_image'];

    public function show(Request $request, SiteResponder $responder, ThemeLayouts $layouts, string $template, ?string $path = null)
    {
        $data = $this->load($template);
        $base = url('/catalogue/'.$template);

        $segments = array_values(array_filter(explode('/', trim((string) $path, '/'))));
        $locale = ($segments[0] ?? null) === 'en' ? array_shift($segments) : 'fr';

        $settings = $data['settings'];
        $settings['site_name'] = $data['manifest']['template']['name'][$locale] ?? $template;
        $settings['locales'] = [$locale];
        $settings['navigation']['locales'] = [$locale => $this->rewrite($settings['navigation']['locales'][$locale] ?? [], $base, $template)];
        $settings['logo_url'] = null;
        Setting::override($settings);
        // Adresses des articles et des archives : celles de la démonstration.
        Permalinks::useRoot($base.($locale === 'en' ? '/en' : ''));

        try {
            [$posts, $terms] = $this->blog($data, $locale, $base, $template);

            // Flux RSS du blog de la démonstration (annoncé dans l'en-tête de chaque page).
            if ($segments === [Permalinks::blogBase($locale), 'rss']) {
                return response()->view('public.feed', [
                    'locale' => $locale,
                    'posts' => $posts->where('locale', $locale)->sortByDesc(fn ($p) => $p->published_at?->timestamp ?? 0)->take(20)->values(),
                    'title' => $settings['site_name'].' · '.Permalinks::blogTitle($locale),
                    'link' => Permalinks::blog($locale),
                    'self' => Permalinks::feed($locale),
                ])->header('Content-Type', 'application/rss+xml; charset=UTF-8')->header('X-Robots-Tag', 'noindex');
            }
            $layouts->useLayouts(collect($data['layouts'])
                ->filter(fn ($l) => empty($l['locale']) || $l['locale'] === $locale)
                ->map(fn ($l) => $this->model(Layout::class, $l + ['content' => $this->rewrite($l['content'] ?? [], $base, $template)])));

            $site = $this->context($request, $data, $locale, $segments, $posts, $terms, $base, $template);
            if (! $site) {
                $site = new SiteContext(SiteContext::NOT_FOUND, $locale);
                $site->library = $posts;

                return $responder->respond($site, 404)->header('X-Robots-Tag', 'noindex');
            }
            $site->library = $posts;

            // La vue est rendue tout de suite : les réglages du modèle ne servent qu'à cette réponse.
            return $responder->respond($site)->header('X-Robots-Tag', 'noindex');
        } finally {
            Setting::override([]);
            Permalinks::useRoot(null);
        }
    }

    /** Ce que regarde le visiteur de la démonstration (page, blog, article, archive, recherche), ou null. */
    private function context(Request $request, array $data, string $locale, array $segments, Collection $posts, Collection $terms, string $base, string $template): ?SiteContext
    {
        $page = fn (string $slug) => $this->page($data, $locale, $slug, $base, $template);
        $paginate = function (Collection $list) use ($request) {
            $perPage = Permalinks::perPage();
            $current = max(1, (int) $request->query('page', 1));

            return (new LengthAwarePaginator($list->forPage($current, $perPage)->values(), $list->count(), $perPage, $current))
                ->withPath($request->url())->withQueryString();
        };

        if ($segments === [] && $request->filled('s')) {
            $q = mb_strtolower(mb_substr(trim((string) $request->query('s')), 0, 100));
            $site = new SiteContext(SiteContext::SEARCH, $locale, search: $q);

            return $site->postsUsing(fn () => $paginate($posts->filter(fn ($p) => str_contains(mb_strtolower($p->title.' '.$p->excerpt.' '.$p->plainText()), $q))));
        }

        $blogBase = Permalinks::blogBase($locale);
        if ($segments && $segments[0] === $blogBase) {
            $rest = array_slice($segments, 1);
            if ($rest === []) {
                $site = ($p = $page($blogBase)) ? new SiteContext(SiteContext::PAGE, $locale, page: $p) : new SiteContext(SiteContext::BLOG, $locale);

                return $site->postsUsing(fn () => $paginate($posts));
            }
            foreach ([Term::CATEGORY => SiteContext::CATEGORY, Term::TAG => SiteContext::TAG] as $taxonomy => $kind) {
                if (count($rest) === 2 && $rest[0] === Permalinks::word($locale, $taxonomy)) {
                    $term = $terms->first(fn ($t) => $t->taxonomy === $taxonomy && $t->slug === $rest[1]);

                    return $term ? (new SiteContext($kind, $locale, term: $term))->postsUsing(fn () => $paginate($posts->filter(fn ($p) => $p->terms->contains('id', $term->id)))) : null;
                }
            }
            if (count($rest) === 1 && ($post = $posts->firstWhere('slug', $rest[0]))) {
                return new SiteContext(SiteContext::POST, $locale, post: $post);
            }
        }

        $p = $page(implode('/', $segments));

        return $p ? SiteContext::forPage($p)->postsUsing(fn () => $paginate($posts)) : null;
    }

    private function page(array $data, string $locale, string $slug, string $base, string $template): ?Page
    {
        $item = collect($data['pages'])->first(fn ($p) => $p['locale'] === $locale && ($slug === '' ? ! empty($p['is_home']) : $p['slug'] === $slug));
        if (! $item) {
            return null;
        }

        return new Page([
            'title' => $item['title'], 'slug' => $item['slug'], 'locale' => $locale, 'status' => Page::STATUS_PUBLISHED,
            'is_home' => ! empty($item['is_home']), 'meta_title' => $item['meta_title'] ?? null, 'meta_description' => $item['meta_description'] ?? null,
            'translation_group' => $item['translation_group'] ?? null,
            'content' => $this->rewrite($item['content'], $base, $template),
        ]);
    }

    /** Articles et catégories du modèle dans cette langue, en mémoire. @return array{0: Collection<int, Post>, 1: Collection<int, Term>} */
    private function blog(array $data, string $locale, string $base, string $template): array
    {
        $terms = collect($data['terms'])->where('locale', $locale)->map(fn ($t) => $this->model(Term::class, $t))->keyBy('id');
        $media = collect($data['media'])->keyBy('id');
        $posts = collect($data['posts'])->where('locale', $locale)->map(function ($p) use ($terms, $media, $base, $template) {
            $post = $this->model(Post::class, array_diff_key($p, ['terms' => 1, 'published_at' => 1]) + ['content' => $this->rewrite($p['content'] ?? [], $base, $template)]);
            $post->published_at = ! empty($p['published_at']) ? Carbon::parse($p['published_at']) : null;
            $own = collect($p['terms'] ?? [])->map(fn ($id) => $terms->get($id))->filter()->values();
            $file = $media->get($p['featured_media_id'] ?? 0);
            $image = $file ? $this->model(DemoMedia::class, ['alt' => $file['alt'] ?? '', 'demo_url' => url('/catalogue/'.$template.'/media/'.basename($file['file']))]) : null;

            return $post->setRelation('terms', $own)
                ->setRelation('categories', $own->where('taxonomy', Term::CATEGORY)->values())
                ->setRelation('tags', $own->where('taxonomy', Term::TAG)->values())
                ->setRelation('featuredMedia', $image)
                ->setRelation('author', null);
        })->sortByDesc(fn ($p) => $p->published_at?->timestamp ?? 0)->values();

        return [$posts, $terms->values()];
    }

    /** Modèle Eloquent en mémoire (jamais enregistré), avec son identifiant d'origine. */
    private function model(string $class, array $attributes)
    {
        $model = new $class;
        $model->forceFill($attributes);
        $model->exists = false;

        return $model;
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

        return Cache::remember('catalog-demo:v2:'.$template.':'.filemtime($file), 3600, function () use ($file) {
            $zip = new ZipArchive;
            abort_unless($zip->open($file) === true, 404);
            $read = fn ($name) => json_decode((string) $zip->getFromName($name), true) ?: [];
            $data = [
                'manifest' => $read('manifest.json'), 'settings' => $read('settings.json'), 'pages' => $read('pages.json'),
                'posts' => $read('posts.json'), 'terms' => $read('terms.json'), 'layouts' => $read('layouts.json'), 'media' => $read('media.json'),
            ];
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
