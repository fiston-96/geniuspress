<?php

namespace App\Platform\Http\Middleware;

use App\Platform\Referrals\Referrals;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lien de parrainage (…?ref=CODE, sur n'importe quelle page du site de la plateforme) : le code
 * est retenu 30 jours dans un cookie, pour l'inscription qui suivra. Le premier lien suivi l'emporte.
 */
class CaptureReferral
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = config('geniuspress.platform.enabled') ? Referrals::validCode($request->query('ref')) : null;
        if ($code && ! $request->cookie(Referrals::COOKIE)) {
            Cookie::queue(Referrals::COOKIE, $code, Referrals::COOKIE_DAYS * 24 * 60, null, null, $request->isSecure(), true, false, 'lax');
        }

        return $next($request);
    }
}
