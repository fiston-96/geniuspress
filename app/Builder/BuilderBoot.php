<?php

namespace App\Builder;

use App\Ai\LlmGateway;
use App\Http\Controllers\Admin\SettingController;
use App\Models\Post;
use App\Plugins\PluginManager;
use App\Site\Permalinks;
use Illuminate\Support\Facades\Vite;

/**
 * Données de démarrage du builder (balise #gp-boot), communes aux pages, aux
 * articles et aux gabarits du thème. Chaque document y ajoute son propre état
 * (« page »), ses versions, ses adresses (« urls ») et son type (« document »).
 */
final class BuilderBoot
{
    public static function make(string $document, array $page, $revisions, array $urls, array $settings, array $extra = []): array
    {
        return $extra + [
            'document' => $document,
            'page' => $page,
            'revisions' => $revisions,
            'settings' => $settings,
            // Couleurs et polices globales du site.
            'design' => [
                'colors' => DesignTokens::colors($settings),
                'css' => DesignTokens::css($settings),
                'fontsUrl' => DesignTokens::fontsUrl($settings),
                'fonts' => SettingController::FONTS,
                'headingFont' => DesignTokens::headingFont($settings),
            ],
            'siteCss' => Vite::asset('resources/css/site.css'),
            'pluginCss' => app(PluginManager::class)->assets('site_css'),
            'csrf' => csrf_token(),
            // Textes de l'interface dans la langue de l'utilisateur (cœur + plugins).
            'i18n' => app('translator')->getLoader()->load(app()->getLocale(), '*', '*'),
            'urls' => $urls + [
                'media' => route('admin.media.index'),
                'aiText' => route('admin.ai.text'),
                'aiGenerate' => null,
                'aiTranslate' => null,
                'aiAudit' => null,
                'meta' => null,
                'aiSettings' => auth()->user()->isAdmin() ? route('admin.settings.edit') : null,
            ],
            'ai' => ['configured' => app(LlmGateway::class)->isConfigured()],
            'meta' => [],
            'widgets' => collect(app(WidgetRegistry::class)->all())
                ->map(fn ($w, $type) => ['label' => $w->label(), 'rules' => $w->rules()]),
            'locales' => collect((array) $settings['locales'])
                ->mapWithKeys(fn ($code) => [$code => SettingController::AVAILABLE_LOCALES[$code] ?? $code]),
            'translations' => [],
            'sample' => self::sample($page['locale'] ?? null, $settings),
        ];
    }

    /**
     * Exemples pour l'aperçu des widgets dynamiques dans le builder (titre d'article,
     * grille d'articles, menu…) : les vrais contenus du site quand il y en a.
     */
    public static function sample(?string $locale, array $settings): array
    {
        $locale ??= Permalinks::defaultLocale();
        $posts = Post::query()->published()->where('locale', $locale)->with(['categories', 'featuredMedia', 'author'])
            ->orderByDesc('published_at')->limit(6)->get();
        $card = fn (Post $p) => [
            'title' => $p->title,
            'url' => $p->url(),
            'excerpt' => $p->summary(),
            'date' => $p->published_at?->locale($locale)->translatedFormat('j F Y'),
            'author' => $p->author?->name,
            'category' => $p->categories->first()?->name,
            'image' => $p->featuredMedia?->url,
            'minutes' => $p->readingMinutes(),
        ];
        $examples = [
            ['title' => __('Titre de l’article'), 'excerpt' => __('Le résumé de l’article apparaît ici, en deux ou trois lignes, pour donner envie de lire la suite.'), 'category' => __('Actualités')],
            ['title' => __('Un deuxième article'), 'excerpt' => __('Chaque carte reprend l’image à la une, la catégorie, la date et le résumé.'), 'category' => __('Événements')],
            ['title' => __('Un troisième article'), 'excerpt' => __('La grille s’adapte aux tablettes et aux téléphones.'), 'category' => __('Actualités')],
        ];
        $list = $posts->isNotEmpty() ? $posts->map($card)->all() : array_map(fn ($e) => $e + [
            'url' => '#', 'date' => now()->locale($locale)->translatedFormat('j F Y'), 'author' => auth()->user()?->name, 'image' => null, 'minutes' => 3,
        ], $examples);
        $nav = \App\Site\Navigation::all()['locales'][$locale] ?? [];

        return [
            'locale' => $locale,
            'site_name' => $settings['site_name'] ?? 'GeniusPress',
            'logo_url' => $settings['logo_url'] ?? null,
            'post' => $list[0],
            'posts' => $list,
            'menu' => $nav['menu'] ?? [['label' => __('Accueil'), 'url' => '/'], ['label' => __('À propos'), 'url' => '#'], ['label' => 'Blog', 'url' => '#'], ['label' => __('Contact'), 'url' => '#']],
            'cta_label' => $nav['cta_label'] ?? null,
            'blog_title' => Permalinks::blogTitle($locale),
            // Choix de la grille d'articles (« Les derniers d'une catégorie »).
            'categories' => \App\Models\Term::query()->where('taxonomy', \App\Models\Term::CATEGORY)->orderBy('locale')->orderBy('name')->get()
                ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name.' ('.strtoupper($c->locale).')'])->all(),
            'languages' => array_values((array) $settings['locales']),
            // Aperçu du widget « Catégories et étiquettes ».
            'terms' => collect(\App\Models\Term::TAXONOMIES)->mapWithKeys(fn ($taxonomy) => [$taxonomy => \App\Models\Term::query()
                ->where('taxonomy', $taxonomy)->where('locale', $locale)->withCount(['posts' => fn ($q) => $q->published()])
                ->orderBy('name')->limit(100)->get()->map(fn ($t) => ['name' => $t->name, 'count' => (int) $t->posts_count])->all()])->all(),
        ];
    }
}
