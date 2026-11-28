<?php

namespace App\Http\Controllers\Admin;

use App\Builder\BuilderBoot;
use App\Builder\ContentSanitizer;
use App\Builder\DocumentSaver;
use App\Http\Controllers\Controller;
use App\Models\ContentRevision;
use App\Models\Layout;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Site\Popups;
use App\Site\SiteContext;
use App\Site\SiteResponder;
use App\Site\ThemeLayouts;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Constructeur de thème : gabarits (en-tête, pied de page, article, archive, recherche, 404, popup) et leurs conditions. */
class LayoutController extends Controller
{
    public function index()
    {
        $layouts = Layout::query()->withoutContent()->orderByDesc('status')->orderByDesc('priority')->orderBy('name')->get()->groupBy('type');

        return view('admin.layouts.index', ['layouts' => $layouts, 'types' => Layout::typeLabels(), 'locales' => $this->locales()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(Layout::TYPES)],
            'name' => ['required', 'string', 'max:120'],
        ]);
        $layout = Layout::create($data + ['status' => Layout::STATUS_DRAFT]);

        return redirect()->route('admin.layouts.builder', $layout);
    }

    public function edit(Layout $layout)
    {
        return view('admin.layouts.form', [
            'layout' => $layout,
            'types' => Layout::typeLabels(),
            'locales' => $this->locales(),
            'targets' => $this->targets(),
            'popup' => $layout->type === 'popup' ? Popups::sanitize($layout->options) : null,
        ]);
    }

    public function update(Request $request, Layout $layout)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'locale' => ['nullable', Rule::in($this->locales())],
            'status' => ['required', Rule::in([Layout::STATUS_DRAFT, Layout::STATUS_PUBLISHED])],
            'priority' => ['required', 'integer', 'min:-99', 'max:99'],
            'rules' => ['nullable', 'array', 'max:30'],
            'rules.*.mode' => ['nullable', 'in:include,exclude'],
            'rules.*.what' => ['nullable', 'string', 'max:40'],
            'options' => ['nullable', 'array'],
        ]);
        // « what » : « all », « page », « page:12 », « in_category:3 »…
        $conditions = ThemeLayouts::sanitizeConditions(array_map(function ($row) {
            [$target, $id] = array_pad(explode(':', (string) ($row['what'] ?? ''), 2), 2, null);

            return ['mode' => $row['mode'] ?? 'include', 'target' => $target, 'id' => $id];
        }, $data['rules'] ?? []));

        $layout->update([
            'name' => $data['name'], 'locale' => $data['locale'] ?: null, 'status' => $data['status'],
            'priority' => $data['priority'], 'conditions' => $conditions,
            // Popups : déclencheur, fréquence, apparence (valeurs validées par Popups::sanitize).
            'options' => $layout->type === 'popup' ? Popups::sanitize($data['options'] ?? []) : $layout->options,
        ]);

        return back()->with('status', $conditions || $layout->status !== Layout::STATUS_PUBLISHED
            ? __('Gabarit enregistré.')
            : __('Gabarit enregistré, mais sans condition d’affichage : il ne s’affiche nulle part.'));
    }

    public function destroy(Layout $layout)
    {
        $layout->delete();

        return redirect()->route('admin.layouts.index')->with('status', __('Gabarit mis à la corbeille.'));
    }

    public function builder(Layout $layout)
    {
        return view('admin.builder', ['boot' => BuilderBoot::make('layout',
            ['id' => $layout->id, 'title' => $layout->name, 'slug' => null, 'locale' => $layout->locale ?? $this->locales()[0], 'status' => $layout->status, 'type' => $layout->type,
                'content' => app(ContentSanitizer::class)->sanitize($layout->editableContent()),
                'has_draft' => $layout->hasUnpublishedChanges(),
            ],
            DocumentSaver::revisions($layout),
            [
                'back' => route('admin.layouts.index'),
                'settings' => route('admin.layouts.edit', $layout),
                'content' => route('admin.layouts.content', $layout),
                'preview' => route('admin.layouts.preview', $layout),
                'restore' => route('admin.layouts.revisions.restore', [$layout, '__ID__']),
            ],
            Setting::publicValues(),
        )]);
    }

    public function saveContent(Request $request, Layout $layout, DocumentSaver $saver)
    {
        $request->validate(['content' => ['required', 'array'], 'publish' => ['sometimes', 'boolean']]);

        return response()->json($saver->save($layout, $request->input('content'), $request->boolean('publish'), $request->user()));
    }

    /**
     * Aperçu du gabarit en cours d'édition, dans un contexte qui lui correspond :
     * l'accueil pour un en-tête ou un pied de page, le dernier article pour un gabarit
     * d'article, le blog pour une archive…
     */
    public function preview(Request $request, Layout $layout, SiteResponder $responder, ThemeLayouts $layouts)
    {
        $content = $request->input('content');
        $content = is_string($content) ? json_decode($content, true) : $content;
        abort_unless(is_array($content), 422, __('Contenu invalide.'));
        $layout->content = $content;
        $layouts->force($layout);

        $locale = $layout->locale ?? $this->locales()[0];
        $posts = fn () => Post::query()->published()->where('locale', $locale)->with(['categories', 'featuredMedia', 'author'])->latest('published_at')->paginate(9);
        $site = match ($layout->type) {
            'single' => ($post = Post::query()->published()->where('locale', $locale)->with(['categories', 'tags', 'author', 'featuredMedia'])->latest('published_at')->first())
                ? new SiteContext(SiteContext::POST, $locale, post: $post, preview: true)
                : new SiteContext(SiteContext::POST, $locale, post: $this->samplePost($locale), preview: true),
            'archive' => new SiteContext(SiteContext::BLOG, $locale, preview: true),
            'search' => new SiteContext(SiteContext::SEARCH, $locale, search: '…', preview: true),
            'not_found' => new SiteContext(SiteContext::NOT_FOUND, $locale, preview: true),
            default => ($home = Page::query()->where('locale', $locale)->where('is_home', true)->first())
                ? SiteContext::forPage($home, true)
                : new SiteContext(SiteContext::NOT_FOUND, $locale, preview: true),
        };
        $site->postsUsing($posts);

        return $responder->respond($site);
    }

    public function restoreRevision(Layout $layout, ContentRevision $revision, DocumentSaver $saver)
    {
        return response()->json($saver->restore($layout, $revision));
    }

    /** Article d'exemple (non enregistré) pour l'aperçu d'un gabarit d'article sur un site sans article. */
    private function samplePost(string $locale): Post
    {
        $post = new Post(['title' => __('Titre de l’article'), 'slug' => 'exemple', 'locale' => $locale, 'status' => Post::STATUS_PUBLISHED,
            'excerpt' => __('Le résumé de l’article apparaît ici, en deux ou trois lignes, pour donner envie de lire la suite.'),
            'content' => ['version' => 2, 'elements' => [['id' => 'wexemple01', 'type' => 'text', 'settings' => ['html' => '<p>'.e(__('Le contenu de l’article, écrit dans le builder, s’affiche ici.')).'</p>']]]]]);
        $post->published_at = now();
        $post->setRelation('categories', collect())->setRelation('tags', collect())->setRelation('author', auth()->user())->setRelation('featuredMedia', null);

        return $post;
    }

    /** Choix de l'éditeur de conditions : « all », « front », « page:12 »… avec leurs libellés. */
    private function targets(): array
    {
        $groups = [
            __('Général') => [
                'all' => __('Tout le site'),
                'front' => __('Page d’accueil'),
                'page' => __('Toutes les pages'),
                'post' => __('Tous les articles'),
                'blog' => __('Liste des articles (blog)'),
                'category' => __('Toutes les catégories'),
                'tag' => __('Toutes les étiquettes'),
                'search' => __('Résultats de recherche'),
                'not_found' => __('Page introuvable (404)'),
            ],
        ];
        $groups[__('Pages')] = Page::query()->withoutContent()->orderBy('locale')->orderBy('title')->limit(300)->get()
            ->mapWithKeys(fn ($p) => ['page:'.$p->id => $p->title.' ('.strtoupper($p->locale).')'])->all();
        $categories = Term::query()->where('taxonomy', Term::CATEGORY)->orderBy('name')->get();
        $groups[__('Articles d’une catégorie')] = $categories->mapWithKeys(fn ($t) => ['in_category:'.$t->id => $t->name.' ('.strtoupper($t->locale).')'])->all();
        $groups[__('Archive d’une catégorie')] = $categories->mapWithKeys(fn ($t) => ['category:'.$t->id => $t->name.' ('.strtoupper($t->locale).')'])->all();
        $groups[__('Archive d’une étiquette')] = Term::query()->where('taxonomy', Term::TAG)->orderBy('name')->limit(300)->get()
            ->mapWithKeys(fn ($t) => ['tag:'.$t->id => $t->name.' ('.strtoupper($t->locale).')'])->all();
        $groups[__('Un article')] = Post::query()->latest('id')->limit(300)->get()
            ->mapWithKeys(fn ($p) => ['post:'.$p->id => $p->title.' ('.strtoupper($p->locale).')'])->all();

        return array_filter($groups);
    }

    private function locales(): array
    {
        return (array) Setting::read('locales', ['fr']);
    }
}
