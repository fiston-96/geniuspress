<?php

namespace App\Providers;

use App\Ai\AnthropicGateway;
use App\Ai\LlmGateway;
use App\Builder\WidgetRegistry;
use App\Builder\Widgets;
use App\Install\EnvironmentFile;
use App\Install\InstallState;
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
        Widgets\LinksWidget::class,
        Widgets\IconWidget::class,
        Widgets\IconBoxWidget::class,
        Widgets\ImageBoxWidget::class,
        Widgets\IconListWidget::class,
        Widgets\TestimonialWidget::class,
        Widgets\CounterWidget::class,
        Widgets\ProgressWidget::class,
        Widgets\AccordionWidget::class,
        Widgets\TabsWidget::class,
        Widgets\GalleryWidget::class,
        Widgets\PricingWidget::class,
        Widgets\TeamMemberWidget::class,
        Widgets\SocialIconsWidget::class,
        Widgets\MapWidget::class,
        Widgets\CountdownWidget::class,
        Widgets\ContactFormWidget::class,
        // Widgets avancés
        Widgets\FlipBoxWidget::class,
        Widgets\PromoBoxWidget::class,
        Widgets\DualHeadingWidget::class,
        Widgets\PriceListWidget::class,
        Widgets\BusinessHoursWidget::class,
        Widgets\DataTableWidget::class,
        Widgets\TimelineWidget::class,
        Widgets\StarRatingWidget::class,
        Widgets\AlertWidget::class,
        Widgets\HotspotsWidget::class,
        Widgets\ImageAccordionWidget::class,
        Widgets\DualButtonWidget::class,
        Widgets\UnfoldWidget::class,
        Widgets\TickerWidget::class,
        Widgets\ImageScrollWidget::class,
        // Widgets dynamiques du thème (articles, archives, en-tête)
        Widgets\PostTitleWidget::class,
        Widgets\PostContentWidget::class,
        Widgets\PostImageWidget::class,
        Widgets\PostMetaWidget::class,
        Widgets\PostExcerptWidget::class,
        Widgets\PostNavigationWidget::class,
        Widgets\ArchiveTitleWidget::class,
        Widgets\PostsWidget::class,
        Widgets\BreadcrumbsWidget::class,
        Widgets\SearchFormWidget::class,
        Widgets\SiteLogoWidget::class,
        Widgets\NavMenuWidget::class,
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

        $this->app->singleton(EnvironmentFile::class, fn () => new EnvironmentFile(config('geniuspress.env_path') ?? $this->app->environmentFilePath()));
        $this->app->singleton(InstallState::class, fn ($app) => new InstallState(config('geniuspress.install_lock'), $app->make(EnvironmentFile::class)));

        // Gabarits du thème : chargés une fois par requête.
        $this->app->scoped(\App\Site\ThemeLayouts::class);
        $this->app->singleton(PluginManager::class, fn () => new PluginManager(config('geniuspress.plugins_path')));
    }

    public function boot(): void
    {
        // Avant l'installation : .env, clé de chiffrement et sessions en fichiers.
        if (! $this->app->runningUnitTests()) {
            $this->app->make(InstallState::class)->prepare();
        }

        Gate::define('admin', fn (User $user) => $user->isAdmin());

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        // Formulaires de contact : 5 envois par minute et 30 par jour par visiteur.
        RateLimiter::for('forms', fn (Request $request) => [
            Limit::perMinute(5)->by('forms|'.$request->ip()),
            Limit::perDay(30)->by('forms-day|'.$request->ip()),
        ]);

        RateLimiter::for('ai', function (Request $request) {
            return Limit::perMinute((int) config('geniuspress.ai.per_minute'))->by('ai|'.$request->user()?->id)
                ->response(fn () => response()->json(['message' => __('Trop de demandes à l’assistant IA. Patientez une minute.')], 429));
        });

        $this->app->make(PluginManager::class)->bootActive();
    }
}
