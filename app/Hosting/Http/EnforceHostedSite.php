<?php

namespace App\Hosting\Http;

use App\Hosting\CurrentSite;
use App\Hosting\LoginLink;
use App\Hosting\Manifest;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * État d'un site hébergé, appliqué à chaque requête :
 * - suspendu : tout est fermé ;
 * - essai terminé : site public fermé, administration en lecture seule (consultation,
 *   export du site) le temps de choisir une offre.
 * Sans effet hors d'un site hébergé.
 */
class EnforceHostedSite
{
    public function __construct(private CurrentSite $site) {}

    public function handle(Request $request, Closure $next): Response
    {
        $manifest = $this->site->manifest();
        if (! $manifest || $request->is('up')) {
            return $next($request);
        }

        $status = $manifest->status();
        if ($status === Manifest::STATUS_SUSPENDED) {
            return response()->view('hosting.closed', ['manifest' => $manifest, 'suspended' => true], 503);
        }
        if ($status !== Manifest::STATUS_EXPIRED) {
            return $next($request);
        }

        $admin = $request->is('admin', 'admin/*', ltrim(LoginLink::PATH, '/'), 'build/*');
        if (! $admin) {
            return response()->view('hosting.closed', ['manifest' => $manifest, 'suspended' => false], 403);
        }
        // Lecture seule : seules la connexion et la déconnexion modifient quelque chose.
        if (! $request->isMethodSafe() && ! $request->is('admin/login', 'admin/logout')) {
            $message = __('Votre essai est terminé : l’administration est en lecture seule. Choisissez une offre pour continuer à modifier votre site.');

            return $request->expectsJson()
                ? response()->json(['message' => $message], 403)
                : back()->with('warnings', [$message]);
        }

        return $next($request);
    }
}
