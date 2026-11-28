<?php

namespace App\Http\Controllers\Admin;

use App\Ai\LlmGateway;
use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Models\Layout;
use App\Models\Media;
use App\Models\NewsletterSubscriber;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Site\Navigation;

/** Tableau de bord de l'administration : état du site, « Bien démarrer », contenus et messages récents. */
class DashboardController extends Controller
{
    public function __invoke()
    {
        $isAdmin = auth()->user()->isAdmin();
        $home = Page::query()->withoutContent()->where('is_home', true)->orderByDesc('status')->first();

        // Contenus récents : pages et articles mêlés, du plus récent au plus ancien.
        $recent = Page::query()->withoutContent()->latest('updated_at')->limit(6)->get()->map(fn (Page $p) => [
            'title' => $p->title, 'kind' => __('Page'), 'icon' => 'files', 'published' => $p->isPublished(), 'updated_at' => $p->updated_at,
            'builder' => route('admin.pages.builder', $p), 'url' => $p->url(),
        ])->concat(Post::query()->withoutContent()->latest('updated_at')->limit(6)->get()->map(fn (Post $p) => [
            'title' => $p->title, 'kind' => __('Article'), 'icon' => 'newspaper', 'published' => $p->isPublished(), 'updated_at' => $p->updated_at,
            'builder' => route('admin.posts.builder', $p), 'url' => $p->isPublished() ? $p->url() : null,
        ]))->sortByDesc('updated_at')->take(7)->values();

        return view('admin.dashboard', [
            'stats' => [
                'pages' => Page::count(),
                'published' => Page::published()->count(),
                'drafts' => Page::where('status', Page::STATUS_DRAFT)->count(),
                'posts' => Post::count(),
                'media' => Media::count(),
                'messages' => $isAdmin ? FormSubmission::query()->whereNull('read_at')->count() : null,
                'subscribers' => $isAdmin ? NewsletterSubscriber::query()->subscribed()->count() : null,
            ],
            'home' => $home,
            'recent' => $recent,
            'messages' => $isAdmin ? FormSubmission::query()->with(['page:id,title', 'layout:id,name'])->latest()->limit(4)->get() : collect(),
            'checklist' => $isAdmin ? $this->checklist($home) : [],
        ]);
    }

    /** Étapes « Bien démarrer » : [clé, libellé, aide, fait ?, adresse]. */
    private function checklist(?Page $home): array
    {
        $s = Setting::publicValues();
        $nav = Navigation::all();
        $menus = collect($nav['locales'] ?? [])->contains(fn ($l) => ! empty($l['menu']));

        return [
            ['home', __('Publier la page d’accueil'), __('Le cœur de votre site, conçu dans le builder.'), (bool) $home?->isPublished(), $home ? route('admin.pages.builder', $home) : route('admin.pages.create')],
            ['logo', __('Ajouter votre logo'), __('Il apparaît dans l’en-tête et les onglets du navigateur.'), ! empty($s['logo_url']), route('admin.settings.edit')],
            ['colors', __('Choisir vos couleurs et polices'), __('Toutes les pages suivent : couleurs, titres, boutons.'), ($s['primary_color'] ?? '#2563eb') !== '#2563eb' || ! empty($s['typography']), route('admin.settings.edit')],
            ['menu', __('Composer le menu'), __('Les liens de l’en-tête, dans chaque langue.'), $menus || Layout::query()->where('type', 'header')->where('status', Layout::STATUS_PUBLISHED)->exists(), route('admin.appearance.edit')],
            ['post', __('Écrire un premier article'), __('Actualités, conseils, événements : le blog fait vivre le site.'), Post::query()->published()->exists(), route('admin.posts.create')],
            ['ai', __('Activer l’assistant IA'), __('Il rédige, traduit et vérifie vos pages.'), app(LlmGateway::class)->isConfigured(), route('admin.settings.edit').'#ia'],
        ];
    }
}
