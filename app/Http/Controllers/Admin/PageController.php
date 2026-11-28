<?php

namespace App\Http\Controllers\Admin;

use App\Builder\ContentSanitizer;
use App\Builder\Renderer;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    private const MAX_REVISIONS = 30;

    public function index(Request $request)
    {
        $locale = $request->string('locale')->toString();
        $pages = Page::query()->withoutContent()
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($locale, fn ($q, $l) => $q->where('locale', $l))
            ->with('author:id,name')
            ->orderByDesc('is_home')->orderByDesc('updated_at')
            ->paginate(20)->withQueryString();

        // Compteurs des onglets de statut (comme WordPress), dans la langue choisie.
        $counts = Page::query()->when($locale, fn ($q, $l) => $q->where('locale', $l))
            ->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('admin.pages.index', ['pages' => $pages, 'counts' => $counts, 'locales' => $this->locales()]);
    }

    public function create()
    {
        return view('admin.pages.form', ['page' => new Page(['locale' => $this->defaultLocale()]), 'locales' => $this->locales()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['author_id'] = $request->user()->id;
        $data['status'] = Page::STATUS_DRAFT;

        $page = Page::create($data);

        return redirect()->route('admin.pages.builder', $page);
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', ['page' => $page, 'locales' => $this->locales()]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validated($request, $page);
        $status = $request->validate(['status' => ['required', Rule::in([Page::STATUS_DRAFT, Page::STATUS_PUBLISHED])]])['status'];

        DB::transaction(function () use ($page, $data, $status, $request) {
            if ($request->boolean('is_home')) {
                Page::query()->where('locale', $data['locale'])->where('id', '!=', $page->id)->update(['is_home' => false]);
            }
            $page->fill($data + ['status' => $status, 'is_home' => $request->boolean('is_home')]);
            if ($status === Page::STATUS_PUBLISHED && ! $page->published_at) {
                $page->published_at = now();
            }
            $page->save();
        });

        return back()->with('status', __('Page enregistrée.'));
    }

    public function builder(Page $page)
    {
        $page->load(['revisions' => fn ($q) => $q->with('user:id,name')->limit(self::MAX_REVISIONS)]);

        return view('admin.pages.builder', [
            'page' => $page,
            'settings' => Setting::publicValues(),
        ]);
    }

    /** Sauvegarde le contenu du builder (JSON). */
    public function saveContent(Request $request, Page $page, ContentSanitizer $sanitizer)
    {
        $request->validate([
            'content' => ['required', 'array'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        $content = $sanitizer->sanitize($request->input('content'));

        DB::transaction(function () use ($page, $content, $request) {
            if ($request->boolean('publish')) {
                // Publier : le contenu devient visible et le brouillon disparaît.
                $page->content = $content;
                $page->draft_content = null;
                $page->status = Page::STATUS_PUBLISHED;
                $page->published_at ??= now();
            } elseif ($page->isPublished()) {
                // Page en ligne : on garde la version publiée intacte.
                $page->draft_content = $content;
            } else {
                // Page hors ligne : un ancien brouillon (gardé quand elle était en ligne)
                // serait sinon rechargé à la place de ce contenu.
                $page->content = $content;
                $page->draft_content = null;
            }
            $page->save();

            PageRevision::create([
                'page_id' => $page->id,
                'user_id' => $request->user()->id,
                'content' => $content,
                'note' => $request->boolean('publish') ? 'Publication' : 'Enregistrement',
            ]);

            // On ne garde que les N dernières révisions.
            $keep = $page->revisions()->limit(self::MAX_REVISIONS)->pluck('id');
            PageRevision::query()->where('page_id', $page->id)->whereNotIn('id', $keep)->delete();
        });

        \App\Plugins\Hooks::dispatch('page.saved', $page);
        if ($request->boolean('publish')) {
            \App\Plugins\Hooks::dispatch('page.published', $page);
        }

        return response()->json([
            'content' => $content,
            'status' => $page->status,
            'has_draft' => $page->hasUnpublishedChanges(),
            'saved_at' => $page->updated_at->toIso8601String(),
            'revisions' => $page->revisions()->with('user:id,name')->limit(self::MAX_REVISIONS)->get(['id', 'user_id', 'note', 'created_at']),
        ]);
    }

    /** Rendu HTML exact du contenu en cours d'édition (pour l'aperçu). */
    public function preview(Request $request, Page $page, Renderer $renderer)
    {
        // Le formulaire d'aperçu envoie le contenu sous forme de texte JSON.
        $content = $request->input('content');
        if (is_string($content)) {
            $content = json_decode($content, true);
        }
        abort_unless(is_array($content), 422, __('Contenu invalide.'));
        $page->content = $content;

        return view('public.page', [
            'page' => $page,
            'rendered' => $renderer->render($page->content, $page->id),
            'settings' => Setting::publicValues(),
            'preview' => true,
        ]);
    }

    public function restoreRevision(Request $request, Page $page, PageRevision $revision, ContentSanitizer $sanitizer)
    {
        abort_unless($revision->page_id === $page->id, 404);

        // Une ancienne version peut être au format 1 : elle est convertie ici.
        return response()->json(['content' => $sanitizer->sanitize($revision->content)]);
    }

    public function destroy(Page $page)
    {
        abort_if($page->is_home, 422, __('Impossible de supprimer la page d’accueil.'));
        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', __('Page mise à la corbeille.'));
    }

    private function validated(Request $request, ?Page $page = null): array
    {
        $request->merge(['slug' => Page::normalizeSlug((string) ($request->input('slug') ?: $request->input('title')))]);
        $locale = $request->input('locale', $this->defaultLocale());

        return $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => [
                'required', 'string', 'max:190',
                function ($attribute, $value, $fail) {
                    if (Page::isReservedSlug((string) $value)) {
                        $fail(__('Cette adresse est réservée.'));
                    }
                },
                // Les pages à la corbeille bloquent aussi l'adresse (index unique en base).
                Rule::unique('pages')->where('locale', $locale)->ignore($page?->id),
            ],
            'locale' => ['required', Rule::in($this->locales())],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ], [
            'slug.unique' => __('Une autre page utilise déjà cette adresse.'),
            'slug.not_in' => __('Cette adresse est réservée.'),
        ]);
    }

    private function locales(): array
    {
        return (array) Setting::read('locales', ['fr']);
    }

    private function defaultLocale(): string
    {
        return $this->locales()[0] ?? 'fr';
    }
}
