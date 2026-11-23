<?php

namespace App\Http\Controllers;

use App\Builder\Renderer;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function __invoke(Request $request, Renderer $renderer)
    {
        $path = $request->path() === '/' ? '' : $request->path();
        $locales = (array) Setting::read('locales', ['fr']);
        $default = $locales[0] ?? 'fr';

        $segments = array_values(array_filter(explode('/', trim((string) $path, '/'))));
        $locale = $default;
        if ($segments && $segments[0] !== $default && in_array($segments[0], $locales, true)) {
            $locale = array_shift($segments);
        }
        $slug = implode('/', $segments);

        $query = Page::query()->where('locale', $locale);
        $page = $slug === '' ? $query->where('is_home', true)->first() : $query->where('slug', $slug)->first();

        // Les brouillons ne sont visibles que par les personnes connectées.
        abort_if(! $page || (! $page->isPublished() && ! $request->user()), 404);

        return view('public.page', [
            'page' => $page,
            'rendered' => $renderer->render($page->content),
            'settings' => Setting::publicValues(),
            'preview' => ! $page->isPublished(),
        ]);
    }
}
