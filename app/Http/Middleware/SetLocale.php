<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Langue de l'interface d'administration : celle choisie par l'utilisateur,
 * sinon celle du navigateur, sinon la langue par défaut de l'application.
 * (La langue du contenu des pages publiques est gérée à part, page par page.)
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = array_keys(config('geniuspress.ui_locales'));
        // Choix de l'utilisateur connecté, sinon choix fait pendant l'installation.
        $locale = $request->user()?->locale ?? ($request->hasSession() ? $request->session()->get('locale') : null);

        if (! in_array($locale, $available, true)) {
            $locale = $request->getPreferredLanguage($available) ?? config('app.locale');
        }

        app()->setLocale(in_array($locale, $available, true) ? $locale : config('app.fallback_locale'));

        return $next($request);
    }
}
