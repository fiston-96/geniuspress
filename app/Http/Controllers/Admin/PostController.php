<?php

namespace App\Http\Controllers\Admin;

use App\Builder\BuilderBoot;
use App\Builder\ContentSanitizer;
use App\Builder\DocumentSaver;
use App\Http\Controllers\Controller;
use App\Models\ContentRevision;
use App\Models\Media;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Plugins\Hooks;
use App\Site\SiteContext;
use App\Site\SiteResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** Articles : liste, réglages (catégories, étiquettes, image à la une, publication), builder. */
class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::query()->withoutContent()
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            ->when($request->string('status')->toString(), fn ($q, $s) => match ($s) {
                'scheduled' => $q->where('status', Post::STATUS_PUBLISHED)->where('published_at', '>', now()),
                'published' => $q->published(),
                default => $q->where('status', $s),
            })
            ->when($request->integer('category'), fn ($q, $id) => $q->whereHas('terms', fn ($t) => $t->whereKey($id)))
            ->with(['author:id,name', 'categories', 'featuredMedia'])
            ->orderByDesc('published_at')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'categories' => Term::query()->where('taxonomy', Term::CATEGORY)->orderBy('locale')->orderBy('name')->get(),
            'blog' => (array) Setting::read('blog'),
            'locales' => $this->locales(),
        ]);
    }

    /** Réglages du blog : titre et adresse par langue, nombre d'articles par page. */
    public function settings(Request $request)
    {
        $data = $request->validate([
            'blog' => ['array'],
            'blog.*.title' => ['nullable', 'string', 'max:60'],
            'blog.*.base' => ['nullable', 'string', 'max:40'],
            'per_page' => ['required', 'integer', 'min:1', 'max:48'],
        ]);
        $blog = ['locales' => [], 'per_page' => (int) $data['per_page']];
        foreach ($this->locales() as $code) {
            $base = Post::normalizeSlug($data['blog'][$code]['base'] ?? '');
            if ($base !== '' && \App\Models\Page::isReservedSlug($base)) {
                return back()->withErrors(['blog' => __('Cette adresse est réservée.')]);
            }
            $blog['locales'][$code] = array_filter([
                'title' => trim(strip_tags((string) ($data['blog'][$code]['title'] ?? ''))),
                'base' => $base,
            ]);
        }
        Setting::write(['blog' => $blog]);

        return back()->with('status', __('Réglages du blog enregistrés.'));
    }

    public function create()
    {
        return $this->form(new Post(['locale' => $this->locales()[0]]));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $post = Post::create($data['post'] + ['author_id' => $request->user()->id, 'status' => Post::STATUS_DRAFT]);
        $this->syncTerms($post, $data);

        return redirect()->route('admin.posts.builder', $post);
    }

    public function edit(Post $post)
    {
        return $this->form($post->load('terms'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validated($request, $post);
        $post->fill($data['post']);
        if ($post->status === Post::STATUS_PUBLISHED && ! $post->published_at) {
            $post->published_at = now();
        }
        $post->save();
        $this->syncTerms($post, $data);
        Hooks::dispatch('post.saved', $post);

        return back()->with('status', $post->isScheduled() ? __('Article programmé.') : __('Article enregistré.'));
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('status', __('Article mis à la corbeille.'));
    }

    public function builder(Post $post)
    {
        return view('admin.builder', ['boot' => BuilderBoot::make('post',
            $post->only(['id', 'title', 'slug', 'locale', 'status']) + [
                'content' => app(ContentSanitizer::class)->sanitize($post->editableContent()),
                'has_draft' => $post->hasUnpublishedChanges(),
            ],
            DocumentSaver::revisions($post),
            [
                'back' => route('admin.posts.index'),
                'settings' => route('admin.posts.edit', $post),
                'content' => route('admin.posts.content', $post),
                'preview' => route('admin.posts.preview', $post),
                'restore' => route('admin.posts.revisions.restore', [$post, '__ID__']),
            ],
            Setting::publicValues(),
            ['translations' => $post->translations()->get(['id', 'locale', 'title', 'status'])
                ->map(fn ($t) => $t->only(['id', 'locale', 'title', 'status']) + ['builder_url' => route('admin.posts.builder', $t)])],
        )]);
    }

    public function saveContent(Request $request, Post $post, DocumentSaver $saver)
    {
        $request->validate(['content' => ['required', 'array'], 'publish' => ['sometimes', 'boolean']]);
        $result = $saver->save($post, $request->input('content'), $request->boolean('publish'), $request->user());
        Hooks::dispatch('post.saved', $post);
        if ($request->boolean('publish')) {
            Hooks::dispatch('post.published', $post);
        }

        return response()->json($result);
    }

    /** Aperçu exact de l'article en cours d'édition, avec les gabarits du thème. */
    public function preview(Request $request, Post $post, SiteResponder $responder)
    {
        $content = $request->input('content');
        $content = is_string($content) ? json_decode($content, true) : $content;
        abort_unless(is_array($content), 422, __('Contenu invalide.'));
        $post->content = $content;
        $post->load(['categories', 'tags', 'author', 'featuredMedia']);

        return $responder->respond(new SiteContext(SiteContext::POST, $post->locale, post: $post, preview: true));
    }

    public function restoreRevision(Post $post, ContentRevision $revision, DocumentSaver $saver)
    {
        return response()->json($saver->restore($post, $revision));
    }

    private function form(Post $post)
    {
        return view('admin.posts.form', [
            'post' => $post,
            'locales' => $this->locales(),
            'categories' => Term::query()->where('taxonomy', Term::CATEGORY)->orderBy('name')->get()->groupBy('locale'),
            'images' => Media::query()->where('mime_type', 'like', 'image/%')->latest('id')->limit(60)->get(),
        ]);
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        $request->merge(['slug' => Post::normalizeSlug((string) ($request->input('slug') ?: $request->input('title')))]);
        $locale = $request->input('locale', $this->locales()[0]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:120', Rule::notIn(Post::RESERVED_SLUGS), Rule::unique('posts')->where('locale', $locale)->ignore($post?->id)],
            'locale' => ['required', Rule::in($this->locales())],
            'status' => [$post ? 'required' : 'sometimes', Rule::in([Post::STATUS_DRAFT, Post::STATUS_PUBLISHED])],
            'published_at' => ['nullable', 'date'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'categories' => ['nullable', 'array', 'max:20'],
            'categories.*' => ['integer', Rule::exists('terms', 'id')->where('taxonomy', Term::CATEGORY)->where('locale', $locale)],
            'tags' => ['nullable', 'string', 'max:500'],
        ], [
            'slug.unique' => __('Un autre article utilise déjà cette adresse.'),
            'slug.not_in' => __('Cette adresse est réservée.'),
            'categories.*.exists' => __('Choisissez des catégories de la langue de l’article.'),
        ]);

        $fields = array_intersect_key($data, array_flip(['title', 'slug', 'locale', 'status', 'excerpt', 'featured_media_id', 'meta_title', 'meta_description']));
        if ($request->has('published_at')) {
            $fields['published_at'] = filled($data['published_at'] ?? null) ? Carbon::parse($data['published_at']) : null;
        }

        return ['post' => $fields, 'categories' => $data['categories'] ?? [], 'tags' => (string) ($data['tags'] ?? ''), 'locale' => $locale];
    }

    /** Catégories cochées et étiquettes saisies (« santé, rentrée ») : les étiquettes manquantes sont créées. */
    private function syncTerms(Post $post, array $data): void
    {
        $tagIds = [];
        foreach (array_slice(array_unique(array_filter(array_map(fn ($t) => trim(strip_tags($t)), explode(',', $data['tags'])))), 0, 20) as $name) {
            $name = mb_substr($name, 0, 60);
            $slug = Post::normalizeSlug($name);
            if ($slug === '') {
                continue;
            }
            $tagIds[] = Term::query()->firstOrCreate(['taxonomy' => Term::TAG, 'locale' => $data['locale'], 'slug' => $slug], ['name' => $name])->id;
        }
        $post->terms()->sync([...$data['categories'], ...$tagIds]);
    }

    private function locales(): array
    {
        return (array) Setting::read('locales', ['fr']);
    }
}
