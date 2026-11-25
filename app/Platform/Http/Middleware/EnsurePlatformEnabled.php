<?php

namespace App\Platform\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Les pages de la plateforme n'existent que sur le serveur de l'exploitant (GP_PLATFORM=true). */
class EnsurePlatformEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('geniuspress.platform.enabled'), 404);

        return $next($request);
    }
}
