<?php

namespace App\Http\Middleware;

use App\Install\InstallState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** L'assistant d'installation n'existe plus (404) une fois GeniusPress installé. */
class InstallerOpen
{
    public function __construct(private InstallState $state) {}

    public function handle(Request $request, Closure $next): Response
    {
        abort_if($this->state->isInstalled(), 404);

        return $next($request);
    }
}
