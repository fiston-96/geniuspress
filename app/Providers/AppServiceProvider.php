<?php

namespace App\Providers;

use App\Ai\AnthropicGateway;
use App\Ai\LlmGateway;
use App\Builder\WidgetRegistry;
use App\Builder\Widgets;
use App\Models\User;
use App\Plugins\PluginManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Widgets du cœur, déclarés comme ceux des plugins. */
    public const CORE_WIDGETS = [
        Widgets\HeadingWidget::class,
        Widgets\TextWidget::class,
        Widgets\ImageWidget::class,
        Widgets\ButtonWidget::class,
        Widgets\SpacerWidget::class,
        Widgets\DividerWidget::class,
        Widgets\VideoWidget::class,
    ];

    public function register(): void
    {
        $this->app->bind(LlmGateway::class, AnthropicGateway::class);

        $this->app->singleton(WidgetRegistry::class, function () {
            $registry = new WidgetRegistry;
            foreach (self::CORE_WIDGETS as $class) {
                $registry->register(new $class);
            }

            return $registry;
        });

        $this->app->singleton(PluginManager::class, fn () => new PluginManager(config('geniuspress.plugins_path')));
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('ai', function (Request $request) {
            return Limit::perMinute((int) config('geniuspress.ai.per_minute'))->by('ai|'.$request->user()?->id)
                ->response(fn () => response()->json(['message' => __('Trop de demandes à l’assistant IA. Patientez une minute.')], 429));
        });

        $this->app->make(PluginManager::class)->bootActive();
    }
}
