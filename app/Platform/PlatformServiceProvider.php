<?php

namespace App\Platform;

use App\Platform\Ai\CreditAccount;
use App\Platform\Ai\Pricing;
use App\Platform\Domains\DomainService;
use App\Platform\Payments\PaymentManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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

        if (! $this->app->routesAreCached()) {
            Route::middleware('web')->group(base_path('routes/platform.php'));
            Route::prefix('api/platform/v1')->middleware('api')->group(base_path('routes/platform-api.php'));
        }
    }
}
