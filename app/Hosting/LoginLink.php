<?php

namespace App\Hosting;

/**
 * Lien de connexion en un clic, de la plateforme vers l'administration d'un site
 * hébergé : signé avec le secret du site (hosting.json), valable quelques minutes,
 * utilisable une seule fois (le site retient les jetons déjà utilisés).
 */
final class LoginLink
{
    public const PATH = '/_essai/connexion';
    public const TTL = 600;

    public static function for(Site $site, Manifest $manifest): string
    {
        $expires = time() + self::TTL;
        $nonce = bin2hex(random_bytes(16));

        return $site->url(self::PATH).'?'.http_build_query([
            'expires' => $expires,
            'nonce' => $nonce,
            'signature' => self::sign($manifest, $expires, $nonce),
        ]);
    }

    public static function verify(Manifest $manifest, mixed $expires, mixed $nonce, mixed $signature): bool
    {
        if (! is_string($nonce) || ! preg_match('/^[a-f0-9]{32}$/', $nonce) || ! is_string($signature) || ! is_numeric($expires)) {
            return false;
        }
        $expires = (int) $expires;
        if ($expires < time() || $expires > time() + self::TTL || $manifest->loginSecret() === '') {
            return false;
        }

        return hash_equals(self::sign($manifest, $expires, $nonce), $signature);
    }

    private static function sign(Manifest $manifest, int $expires, string $nonce): string
    {
        return hash_hmac('sha256', $manifest->slug().'|'.$expires.'|'.$nonce, $manifest->loginSecret());
    }
}
