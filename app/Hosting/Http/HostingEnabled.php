<?php

namespace App\Hosting\Http;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Pages de l'essai hébergé introuvables tant que l'hébergement n'est pas activé (GP_HOSTING_ENABLED). */
class HostingEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('hosting.enabled'), 404);

        return $next($request);
    }
}
