<?php

namespace App\Http\Middleware;

use App\Install\InstallState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Redirige vers l'assistant d'installation tant que GeniusPress n'est pas installé. */
class EnsureInstalled
{
    public function __construct(private InstallState $state) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('install', 'install/*', 'up', 'build/*', 'storage/*') || $this->state->isInstalled()) {
            return $next($request);
        }

        return redirect()->route('install');
    }
}
