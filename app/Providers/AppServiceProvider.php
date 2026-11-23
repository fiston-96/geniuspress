<?php

namespace App\Providers;

use App\Ai\AnthropicGateway;
use App\Ai\LlmGateway;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LlmGateway::class, AnthropicGateway::class);
    }

    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        RateLimiter::for('ai', function (Request $request) {
            return Limit::perMinute((int) config('geniuspress.ai.per_minute'))->by('ai|'.$request->user()?->id)
                ->response(fn () => response()->json(['message' => 'Trop de demandes à l’assistant IA. Patientez une minute.'], 429));
        });
    }
}
