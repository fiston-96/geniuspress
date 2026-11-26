<?php

namespace App\Platform\Http\Middleware;

use App\Platform\LicenseKeys;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API de la plateforme : la clé de licence arrive dans « Authorization: Bearer ».
 * La licence et son client sont rangés dans les attributs de la requête.
 */
class AuthenticateLicense
{
    public function __construct(private LicenseKeys $keys) {}

    public function handle(Request $request, Closure $next): Response
    {
        $license = $this->keys->find((string) $request->bearerToken());
        if (! $license || ! $license->isActive()) {
            return self::error('invalid_license', __('La clé de licence GeniusPress est invalide ou a été révoquée.'), 401);
        }
        $customer = $license->customer;
        if (! $customer->isActive()) {
            return self::error('suspended', __('Ce compte est suspendu. Contactez-nous.'), 403);
        }

        // Dernière utilisation, au plus une écriture par minute.
        if (! $license->last_used_at || $license->last_used_at->lt(now()->subMinute()) || $license->last_ip !== $request->ip()) {
            $license->forceFill(['last_used_at' => now(), 'last_ip' => $request->ip()])->saveQuietly();
        }

        $request->attributes->set('platform_license', $license);
        $request->attributes->set('platform_customer', $customer);

        return $next($request);
    }

    /** Réponse d'erreur de l'API : un code stable (traduit côté site) et un message. */
    public static function error(string $code, string $message, int $status, array $extra = []): Response
    {
        return response()->json(['error' => $code, 'message' => $message] + $extra, $status);
    }
}
