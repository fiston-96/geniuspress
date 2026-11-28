<?php

namespace App\Http\Middleware;

use App\Install\InstallState;
use App\Scheduling\WebCron;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tâches planifiées sans cron : après l'envoi de chaque page (terminate), le planificateur
 * peut être lancé (au plus une fois par minute, voir App\Scheduling\WebCron).
 */
class RunScheduleOnVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (app()->runningUnitTests() || app()->runningInConsole() || ! $request->isMethod('GET')
            || $request->routeIs('cron.run') || ! app(InstallState::class)->isInstalled()) {
            return;
        }
        WebCron::onVisit();
    }
}
