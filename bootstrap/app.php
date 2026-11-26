<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [\App\Http\Middleware\SetLocale::class, \App\Http\Middleware\EnsureInstalled::class]);
        // L'espace client de la plateforme commerciale (/compte) a sa propre connexion.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('compte', 'compte/*', 'essayer', 'essayer/*') && config('geniuspress.platform.enabled') ? route('platform.login') : route('login'));
        $middleware->redirectUsersTo(fn (Request $request) => $request->is('compte', 'compte/*') ? route('platform.customer.dashboard') : route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// Dossier du fichier .env (ex. un volume persistant dans Docker : GP_ENV_DIR=/var/www/html/storage).
if ($envDir = getenv('GP_ENV_DIR')) {
    $app->useEnvironmentPath($envDir);
}

// Site hébergé par la plateforme (essai) : son .env, son storage et sa base, même code.
\App\Hosting\HostedBootstrap::apply($app);

return $app;
