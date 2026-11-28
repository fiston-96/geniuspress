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
        $middleware->web(append: [\App\Http\Middleware\SetLocale::class, \App\Http\Middleware\EnsureInstalled::class, \App\Platform\Http\Middleware\CaptureReferral::class,
            // Tâches planifiées sans cron (hébergement mutualisé) : lancées après l'envoi des pages.
            \App\Http\Middleware\RunScheduleOnVisit::class]);
        // L'espace client de la plateforme commerciale (/compte) a sa propre connexion.
        // Trois espaces, trois connexions : CMS (/admin), espace client (/compte), console d'exploitation (/console).
        $middleware->redirectGuestsTo(fn (Request $request) => match (true) {
            ! config('geniuspress.platform.enabled') => route('login'),
            $request->is('console', 'console/*') => route('console.login'),
            $request->is('compte', 'compte/*', 'essayer', 'essayer/*') => route('platform.login'),
            default => route('login'),
        });
        $middleware->redirectUsersTo(fn (Request $request) => match (true) {
            $request->is('console', 'console/*') => route('console.dashboard'),
            $request->is('compte', 'compte/*') => route('platform.customer.dashboard'),
            default => route('admin.dashboard'),
        });
        // Désinscription en un clic depuis la messagerie (POST sans jeton CSRF ; le jeton secret de l'abonné suffit).
        $middleware->validateCsrfTokens(except: ['newsletter/desinscription/*']);
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
