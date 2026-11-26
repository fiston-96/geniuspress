<?php

namespace App\Hosting\Http;

use App\Hosting\CurrentSite;
use App\Hosting\HostedBootstrap;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sur la plateforme : une adresse d'essai qui ne correspond à aucun site
 * (« inconnu.essai.exemple.org ») affiche « site introuvable » au lieu de la plateforme.
 */
class RejectUnknownSiteHost
{
    public function __construct(private CurrentSite $site) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->site->isHosted() && config('hosting.enabled')) {
            $host = HostedBootstrap::normalizeHost($request->getHost());
            $domain = strtolower((string) config('hosting.domain'));
            $platform = parse_url((string) config('app.url'), PHP_URL_HOST);
            if ($host && $domain !== '' && str_ends_with($host, '.'.$domain) && $host !== $platform) {
                return response()->view('hosting.unknown', [], 404);
            }
        }

        return $next($request);
    }
}
