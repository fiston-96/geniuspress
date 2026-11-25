<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Site\Permalinks;

/** Plan du site XML (/sitemap.xml) : pages, articles, blogs et archives publiés, dans toutes les langues. */
class SitemapController extends Controller
{
    public function __invoke()
    {
        $urls = [];
        $pages = Page::query()->withoutContent()->published()->get();
        // Versions d'une même page dans les autres langues (hreflang), reliées par translation_group.
        $groups = $pages->groupBy('translation_group');
        foreach ($pages as $page) {
            $versions = $groups[$page->translation_group] ?? collect();
            $urls[] = ['loc' => $page->url(), 'lastmod' => $page->updated_at,
                'alternates' => $versions->count() > 1 ? $versions->mapWithKeys(fn ($p) => [$p->locale => $p->url()])->all() : []];
        }
        foreach ((array) Setting::read('locales', ['fr']) as $locale) {
            $latest = Post::query()->published()->where('locale', $locale)->max('updated_at');
            if ($latest) {
                $urls[] = ['loc' => Permalinks::blog($locale), 'lastmod' => \Illuminate\Support\Carbon::parse($latest)];
            }
        }
        foreach (Post::query()->published()->get() as $post) {
            $urls[] = ['loc' => $post->url(), 'lastmod' => $post->updated_at];
        }
        foreach (Term::query()->whereHas('posts', fn ($q) => $q->published())->get() as $term) {
            $urls[] = ['loc' => $term->url(), 'lastmod' => null];
        }

        return response()->view('public.sitemap', ['urls' => collect($urls)->unique('loc')])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
