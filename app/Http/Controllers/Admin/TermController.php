<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Catégories et étiquettes des articles. */
class TermController extends Controller
{
    public function index(Request $request)
    {
        $taxonomy = $request->query('taxonomy') === Term::TAG ? Term::TAG : Term::CATEGORY;
        $terms = Term::query()->where('taxonomy', $taxonomy)->withCount('posts')->with('parent')
            ->orderBy('locale')->orderBy('name')->get();

        return view('admin.terms.index', [
            'taxonomy' => $taxonomy,
            'terms' => $terms,
            'edit' => $request->integer('edit') ? $terms->firstWhere('id', $request->integer('edit')) : null,
            'locales' => (array) Setting::read('locales', ['fr']),
            'parents' => $taxonomy === Term::CATEGORY ? $terms : collect(),
        ]);
    }

    public function store(Request $request)
    {
        Term::create($this->validated($request));

        return back()->with('status', __('Enregistré.'));
    }

    public function update(Request $request, Term $term)
    {
        $term->update($this->validated($request, $term));

        return redirect()->route('admin.terms.index', ['taxonomy' => $term->taxonomy])->with('status', __('Enregistré.'));
    }

    public function destroy(Term $term)
    {
        $taxonomy = $term->taxonomy;
        $term->delete(); // les articles restent, ils perdent seulement ce classement

        return redirect()->route('admin.terms.index', ['taxonomy' => $taxonomy])->with('status', __('Supprimé.'));
    }

    private function validated(Request $request, ?Term $term = null): array
    {
        $taxonomy = $term?->taxonomy ?? ($request->input('taxonomy') === Term::TAG ? Term::TAG : Term::CATEGORY);
        $request->merge([
            'taxonomy' => $taxonomy,
            'slug' => Post::normalizeSlug((string) ($request->input('slug') ?: $request->input('name'))),
        ]);
        $locale = $request->input('locale');

        return $request->validate([
            'taxonomy' => ['required', Rule::in(Term::TAXONOMIES)],
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('terms')->where('taxonomy', $taxonomy)->where('locale', $locale)->ignore($term?->id)],
            'locale' => ['required', Rule::in((array) Setting::read('locales', ['fr']))],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', Rule::exists('terms', 'id')->where('taxonomy', Term::CATEGORY)->where('locale', $locale), Rule::notIn([$term?->id])],
        ], ['slug.unique' => __('Cette adresse est déjà utilisée.')]);
    }
}
