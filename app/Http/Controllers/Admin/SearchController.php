<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Layout;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Request;

/** Recherche globale de l'administration (barre du haut) : pages, articles, médias, gabarits du thème. */
class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = trim(mb_substr((string) $request->query('q', ''), 0, 100));
        $like = '%'.addcslashes($q, '%_\\').'%';
        $results = [];
        if (mb_strlen($q) >= 2) {
            $results = [
                'pages' => Page::query()->withoutContent()->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like))->latest('updated_at')->limit(10)->get(),
                'posts' => Post::query()->withoutContent()->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('slug', 'like', $like))->latest('updated_at')->limit(10)->get(),
                'media' => Media::query()->where(fn ($w) => $w->where('original_name', 'like', $like)->orWhere('alt', 'like', $like))->latest()->limit(12)->get(),
                'layouts' => $request->user()->isAdmin() ? Layout::query()->withoutContent()->where('name', 'like', $like)->orderBy('name')->limit(10)->get() : collect(),
            ];
        }

        return view('admin.search', ['q' => $q, 'results' => $results, 'total' => collect($results)->sum(fn ($r) => $r->count())]);
    }
}
