<?php

namespace App\Hosting;

use App\Ai\LlmGateway;
use App\Hosting\Console\HostedSetupCommand;
use App\Hosting\Console\TrialCommand;
use App\Hosting\Database\MysqlSiteDatabase;
use App\Hosting\Database\SiteDatabase;
use App\Hosting\Database\SqliteSiteDatabase;
use App\Hosting\Http\EnforceHostedSite;
use App\Hosting\Http\HostedSiteController;
use App\Hosting\Http\HostingEnabled;
use App\Hosting\Http\RejectUnknownSiteHost;
use App\Hosting\Http\TrialController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Essai hébergé. Deux rôles, selon la requête :
 * - sur la plateforme : parcours « Essayer GeniusPress », commandes, tâche planifiée ;
 * - dans un site hébergé (voir HostedBootstrap) : état de l'essai, quotas, connexion en un clic.
 */
class HostingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentSite::class, fn () => CurrentSite::fromEnvironment());
        $this->app->singleton(Quotas::class);

        // Remplaçables par la plateforme commerciale.
        $this->app->bindIf(HostingAccounts::class, LocalHostingAccounts::class);
        $this->app->bindIf(SiteProvisioner::class, LocalSiteProvisioner::class);
        $this->app->bindIf(SiteDatabase::class, fn () => config('hosting.database.driver') === 'sqlite'
            ? new SqliteSiteDatabase
            : new MysqlSiteDatabase((array) config('hosting.database')));

        // Crédits IA du site hébergé vérifiés avant chaque appel.
        $this->app->extend(LlmGateway::class, fn (LlmGateway $llm, $app) => new MeteredLlmGateway($llm, $app->make(Quotas::class)));

        // Médias d'un site hébergé : servis par Laravel sur /storage si le serveur web ne
        // les sert pas lui-même (le disque privé « local » libère cette adresse).
        if (getenv('GP_HOSTED_SITE')) {
            config(['filesystems.disks.local.serve' => false, 'filesystems.disks.public.serve' => true]);
        }
    }

    public function boot(): void
    {
        $hosted = $this->app->make(CurrentSite::class)->isHosted();

        Quotas::register();
        // Ajoutés au noyau HTTP (et non au routeur seul) : le noyau recopie ses groupes
        // dans le routeur quand il est créé, ce qui effacerait un ajout fait avant.
        $web = function (HttpKernel $kernel) {
            $kernel->appendMiddlewareToGroup('web', EnforceHostedSite::class);
            $kernel->appendMiddlewareToGroup('web', RejectUnknownSiteHost::class);
        };
        $this->app->resolved(HttpKernel::class) ? $web($this->app->make(HttpKernel::class)) : $this->app->afterResolving(HttpKernel::class, $web);

        // Lien de connexion en un clic (refusé hors d'un site hébergé).
        Route::middleware('web')->get(LoginLink::PATH, [HostedSiteController::class, 'login'])->name('hosting.login');

        if (! $hosted) {
            $this->loadMigrationsFrom(__DIR__.'/database/migrations');

            // Parcours « Essayer GeniusPress » : introuvable tant que l'hébergement n'est pas activé.
            Route::middleware([HostingEnabled::class, ...(array) config('hosting.middleware')])->prefix('essayer')->name('hosting.')->group(function () {
                Route::get('/', [TrialController::class, 'show'])->name('trial');
                Route::post('/', [TrialController::class, 'store'])->middleware('throttle:5,1')->name('trial.store');
                Route::get('/etat', [TrialController::class, 'status'])->name('trial.status');
                Route::post('/ouvrir', [TrialController::class, 'open'])->middleware('throttle:20,1')->name('trial.open');
            });

            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
                if (config('hosting.enabled')) {
                    $schedule->command('geniuspress:trial expire')->hourly()->withoutOverlapping();
                }
            });
        }

        if ($this->app->runningInConsole()) {
            $this->commands([TrialCommand::class, HostedSetupCommand::class]);
        }
    }
}
