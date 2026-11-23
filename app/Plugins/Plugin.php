<?php

namespace App\Plugins;

use App\Builder\Widgets\Widget;
use App\Builder\WidgetRegistry;
use Closure;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

/**
 * Classe de base des plugins GeniusPress.
 *
 *   class MonPlugin extends \App\Plugins\Plugin
 *   {
 *       public function boot(): void
 *       {
 *           $this->widget(new CitationWidget);
 *           $this->filter('page.head', fn ($html) => $html.'<meta …>');
 *       }
 *   }
 */
abstract class Plugin
{
    public function __construct(public readonly PluginManifest $manifest) {}

    /** Appelé au chargement du plugin, à chaque requête, quand il est actif. */
    public function boot(): void {}

    /** Appelé une fois, quand un administrateur active le plugin (après ses migrations). */
    public function activate(): void {}

    /** Appelé une fois, quand un administrateur désactive le plugin. Ne supprimez pas de données ici. */
    public function deactivate(): void {}

    // ─── Outils pour les plugins ───────────────────────────────────────────

    /** Déclare un widget dans le builder. */
    protected function widget(Widget $widget): void
    {
        app(WidgetRegistry::class)->register($widget);
    }

    /** Réagit à un événement (ex. « page.published »). */
    protected function on(string $event, callable $callback, int $priority = 10): void
    {
        Hooks::listen($event, $callback, $priority);
    }

    /** Modifie une valeur (ex. « page.head », « admin.menu »). */
    protected function filter(string $name, callable $callback, int $priority = 10): void
    {
        Hooks::addFilter($name, $callback, $priority);
    }

    /**
     * Déclare des routes. Elles sont préfixées par /plugins/{slug} et reçoivent
     * le middleware « web » (session, CSRF). Ajoutez ->middleware('auth') pour l'admin.
     */
    protected function routes(Closure $routes): void
    {
        Route::middleware('web')->prefix('plugins/'.$this->manifest->slug)
            ->name('plugins.'.$this->manifest->slug.'.')->group($routes);
    }

    /** Rend les vues de resources/views accessibles sous « {slug}::nom ». */
    protected function views(): void
    {
        View::addNamespace($this->manifest->slug, $this->path('resources/views'));
    }

    /** Chemin absolu d'un fichier du plugin. */
    protected function path(string $relative = ''): string
    {
        return rtrim($this->manifest->path.DIRECTORY_SEPARATOR.ltrim($relative, '/\\'), DIRECTORY_SEPARATOR);
    }

    /** Adresse publique d'un fichier du dossier public/ du plugin. */
    protected function asset(string $relative): string
    {
        return route('plugin.asset', [$this->manifest->slug, ltrim($relative, '/')]);
    }
}
