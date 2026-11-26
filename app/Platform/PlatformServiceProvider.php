<?php

namespace App\Platform;

use App\Hosting\Events\SiteProvisioned;
use App\Hosting\HostingAccounts;
use App\Hosting\LocalHostingAccounts;
use App\Platform\Ai\CreditAccount;
use App\Platform\Ai\Pricing;
use App\Platform\Domains\DomainService;
use App\Platform\Payments\PaymentManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Plateforme commerciale : services, routes (/compte, /console, /api/platform/v1)
 * et limites. Les routes répondent 404 tant que GP_PLATFORM n'est pas activé.
 */
class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach ([PaymentManager::class, DomainService::class, Pricing::class, CreditAccount::class, LicenseKeys::class] as $service) {
            $this->app->singleton($service);
        }

        // Sur la plateforme, l'essai hébergé se lance depuis l'espace client : ses comptes sont
        // les clients (garde « customer »), pas les utilisateurs de l'administration.
        // Choix fait à chaque résolution : la plateforme peut être activée après le démarrage (tests).
        $this->app->bind(HostingAccounts::class, fn ($app) => config('geniuspress.platform.enabled')
            ? $app->make(PlatformHostingAccounts::class)
            : $app->make(LocalHostingAccounts::class));

        // Vitrine de la plateforme : widget des offres en direct, seulement quand la plateforme est activée.
        $this->callAfterResolving(\App\Builder\WidgetRegistry::class, function (\App\Builder\WidgetRegistry $registry) {
            if (config('geniuspress.platform.enabled') && ! $registry->has(Website\OffersWidget::TYPE)) {
                $registry->register(new Website\OffersWidget);
            }
        });
    }

    public function boot(): void
    {
        RateLimiter::for('platform-relay', fn (Request $request) => [
            Limit::perMinute((int) config('geniuspress.platform.relay_per_minute'))
                ->by('relay|'.substr(hash('sha256', (string) $request->bearerToken()), 0, 32)),
            Limit::perMinute(120)->by('relay-ip|'.$request->ip()),
        ]);
        // Code de transfert d'un domaine (e-mail envoyé par le registraire) : 3 demandes par heure et par domaine.
        RateLimiter::for('platform-authcode', fn (Request $request) => Limit::perHour(3)
            // Le limiteur passe avant la liaison du modèle : « domain » est encore l'identifiant brut.
            ->by('platform-authcode|'.$request->user('customer')?->getAuthIdentifier().'|'.(string) $request->route()?->originalParameter('domain')));
        // Code de paiement Mobile Money : 10 essais par 10 minutes et par commande.
        RateLimiter::for('platform-otp', fn (Request $request) => Limit::perMinutes(10, 10)
            ->by('platform-otp|'.$request->user('customer')?->getAuthIdentifier().'|'.(string) $request->route()?->originalParameter('order')));
        RateLimiter::for('platform-auth', fn (Request $request) => Limit::perMinute(5)
            ->by('platform-auth|'.strtolower((string) $request->input('email')).'|'.$request->ip()));

        // Site d'essai prêt : l'abonnement d'essai est ouvert côté plateforme (essai marqué
        // utilisé, crédits IA d'essai offerts, site rattaché à l'abonnement).
        Event::listen(SiteProvisioned::class, function (SiteProvisioned $event) {
            $site = $event->site;
            $customer = PlatformHostingAccounts::customer($site->account_id);
            if (! config('geniuspress.platform.enabled') || ! $customer) {
                return;
            }
            PlatformNotifier::send($customer, new Notifications\SiteReady($site));
            // Abonnement d'essai seulement pour un essai (pas pour un site gratuit sans limite).
            if ($site->kind !== 'trial') {
                return;
            }
            $accounts = $this->app->make(PlatformAccounts::class);
            if ($accounts->activeSubscription($customer)) {
                return;
            }
            try {
                $accounts->startTrial($customer, $site->slug);
            } catch (PlatformException $e) {
                report($e);
            }
        });

        // Rappels de fin d'essai, chaque jour à 9 h.
        if ($this->app->runningInConsole()) {
            $this->commands([Console\TrialRemindersCommand::class, Console\OperatorCommand::class, Console\ReferralsReleaseCommand::class, Console\SuppliersCommand::class, Console\RenewalRemindersCommand::class]);
        }
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            if (config('geniuspress.platform.enabled')) {
                $schedule->command('geniuspress:referrals-release')->hourly()->withoutOverlapping();
                // Fournisseurs (sans effet tant que LWS n'est pas configuré) : solde chaque heure, coûts chaque nuit.
                $schedule->command('geniuspress:suppliers balance')->hourly()->withoutOverlapping();
                $schedule->command('geniuspress:suppliers sync')->dailyAt('04:00')->withoutOverlapping();
                $schedule->command('geniuspress:suppliers domains')->dailyAt('05:00')->withoutOverlapping();
                // Rappels d'échéance des domaines et services, chaque matin.
                $schedule->command('geniuspress:renewal-reminders')->dailyAt('08:00')->withoutOverlapping();
            }
            if (config('geniuspress.platform.enabled') && config('hosting.enabled')) {
                $schedule->command('geniuspress:trial-reminders')->dailyAt('09:00')->withoutOverlapping();
            }
        });

        if (! $this->app->routesAreCached()) {
            Route::middleware('web')->group(base_path('routes/platform.php'));
            Route::prefix('api/platform/v1')->middleware('api')->group(base_path('routes/platform-api.php'));
        }
    }
}
