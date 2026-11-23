<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/** Redirige vers l'installation tant qu'aucun compte administrateur n'existe. */
class EnsureInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('install', 'up', 'build/*', 'storage/*')) {
            return $next($request);
        }

        $installed = Cache::get('gp_installed') ?: (User::query()->exists() && Cache::forever('gp_installed', true));

        if (! $installed) {
            return redirect()->route('install');
        }

        return $next($request);
    }
}
