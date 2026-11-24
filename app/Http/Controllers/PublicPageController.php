<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Site\Permalinks;
use App\Site\SiteContext;
use App\Site\SiteResponder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Site public : pages, articles, archives (blog, catégories, étiquettes),
 * recherche (?s=), flux RSS du blog et page introuvable.
 * Voir App\Site\Permalinks pour les adresses.
 */
class PublicPageController extends Controller
{
    public function __invoke(Request $request, SiteResponder $responder)
    {
        $locales = (array) Setting::read('locales', ['fr']);
        $default = $locales[0] ?? 'fr';

        $segments = array_values(array_filter(explode('/', trim($request->path() === '/' ? '' : $request->path(), '/'))));
        $locale = $default;
        if ($segments && $segments[0] !== $default && in_array($segments[0], $locales, true)) {
            $locale = array_shift($segments);
        }
        $loggedIn = (bool) $request->user();

        // Recherche : /?s=mot (ou /en?s=mot).
        if ($segments === [] && $request->filled('s')) {
            $q = mb_substr(trim((string) $request->query('s')), 0, 100);

            return $responder->respond($this->archive(new SiteContext(SiteContext::SEARCH, $locale, search: $q), $request));
        }

        // Blog : /blog, /blog/rss, /blog/categorie/x, /blog/etiquette/x, /blog/article.
        if ($segments && $segments[0] === Permalinks::blogBase($locale)) {
            $rest = array_slice($segments, 1);
            if ($rest === []) {
                // Une page qui porte l'adresse du blog l'emporte (elle peut contenir une grille d'articles).
                $page = Page::query()->where('locale', $locale)->where('slug', $segments[0])->first();
                if ($page && ($page->isPublished() || $loggedIn)) {
                    $site = new SiteContext(SiteContext::PAGE, $locale, page: $page, preview: ! $page->isPublished());
                    $site->postsUsing(fn () => $this->paginate(Post::query()->where('locale', $locale), $request));

                    return $responder->respond($site);
                }

                return $responder->respond($this->archive(new SiteContext(SiteContext::BLOG, $locale), $request));
            }
            if ($rest === ['rss']) {
                return $this->feed($locale);
            }
            foreach ([Term::CATEGORY => SiteContext::CATEGORY, Term::TAG => SiteContext::TAG] as $taxonomy => $kind) {
                if (count($rest) === 2 && $rest[0] === Permalinks::word($locale, $taxonomy)) {
                    $term = Term::query()->where(['taxonomy' => $taxonomy, 'locale' => $locale, 'slug' => $rest[1]])->first();

                    return $term
                        ? $responder->respond($this->archive(new SiteContext($kind, $locale, term: $term), $request))
                        : $this->notFound($responder, $locale);
                }
            }
            if (count($rest) === 1) {
                $post = Post::query()->with(['categories', 'tags', 'author', 'featuredMedia'])->where('locale', $locale)->where('slug', $rest[0])->first();
                if ($post && ($post->isPublished() || $loggedIn)) {
                    return $responder->respond(new SiteContext(SiteContext::POST, $locale, post: $post, preview: ! $post->isPublished()));
                }
            }
        }

        $slug = implode('/', $segments);
        $query = Page::query()->where('locale', $locale);
        $page = $slug === '' ? $query->where('is_home', true)->first() : $query->where('slug', $slug)->first();

        // Les brouillons ne sont visibles que par les personnes connectées.
        if (! $page || (! $page->isPublished() && ! $loggedIn)) {
            return $this->notFound($responder, $locale);
        }

        $site = SiteContext::forPage($page, ! $page->isPublished());
        // Une grille d'articles posée sur une page affiche les derniers articles, paginés.
        $site->postsUsing(fn () => $this->paginate(Post::query()->where('locale', $locale), $request));

        return $responder->respond($site);
    }

    /** Ajoute la liste paginée des articles de l'archive (blog, catégorie, étiquette, recherche). */
    private function archive(SiteContext $site, Request $request): SiteContext
    {
        $query = Post::query()->where('locale', $site->locale);
        if ($site->term) {
            $query->whereHas('terms', fn ($q) => $q->whereKey($site->term->id));
        }
        if ($site->search !== null) {
            $words = array_filter(preg_split('/\s+/u', $site->search));
            foreach (array_slice($words, 0, 6) as $word) {
                $like = '%'.addcslashes($word, '%_\\').'%';
                $query->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('content', 'like', $like));
            }
        }
        $site->postsUsing(fn () => $this->paginate($query, $request));

        return $site;
    }

    private function paginate(Builder $query, Request $request)
    {
        return $query->published()->with(['categories', 'featuredMedia', 'author'])
            ->orderByDesc('published_at')->orderByDesc('id')
            ->paginate(Permalinks::perPage())->withQueryString();
    }

    private function notFound(SiteResponder $responder, string $locale)
    {
        return $responder->respond(new SiteContext(SiteContext::NOT_FOUND, $locale), 404);
    }

    /** Flux RSS des 20 derniers articles de la langue. */
    private function feed(string $locale)
    {
        $posts = Post::query()->published()->where('locale', $locale)->orderByDesc('published_at')->limit(20)->get();

        return response()->view('public.feed', [
            'locale' => $locale,
            'posts' => $posts,
            'title' => Setting::read('site_name').' · '.Permalinks::blogTitle($locale),
            'link' => Permalinks::blog($locale),
            'self' => Permalinks::feed($locale),
        ])->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
