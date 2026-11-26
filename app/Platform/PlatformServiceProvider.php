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
 * Plateforme commerciale : services, routes (/compte, /admin/platform, /api/platform/v1)
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
    }

    public function boot(): void
    {
        RateLimiter::for('platform-relay', fn (Request $request) => [
            Limit::perMinute((int) config('geniuspress.platform.relay_per_minute'))
                ->by('relay|'.substr(hash('sha256', (string) $request->bearerToken()), 0, 32)),
            Limit::perMinute(120)->by('relay-ip|'.$request->ip()),
        ]);
        RateLimiter::for('platform-auth', fn (Request $request) => Limit::perMinute(5)
            ->by('platform-auth|'.strtolower((string) $request->input('email')).'|'.$request->ip()));

        // Site d'essai prêt : l'abonnement d'essai est ouvert côté plateforme (essai marqué
        // utilisé, crédits IA d'essai offerts, site rattaché à l'abonnement).
        Event::listen(SiteProvisioned::class, function (SiteProvisioned $event) {
            $site = $event->site;
            $customer = PlatformHostingAccounts::customer($site->account_id);
            if (! config('geniuspress.platform.enabled') || $site->kind !== 'trial' || ! $customer) {
                return;
            }
            PlatformNotifier::send($customer, new Notifications\SiteReady($site));
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
            $this->commands([Console\TrialRemindersCommand::class]);
        }
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
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
