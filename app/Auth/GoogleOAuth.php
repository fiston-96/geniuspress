<?php

namespace App\Auth;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * « Continuer avec Google » (OAuth 2.0 + OpenID Connect, flux « code d'autorisation »),
 * sans dépendance : redirection vers Google, échange du code contre un jeton côté serveur,
 * lecture de l'identité. Seules les adresses vérifiées par Google sont acceptées.
 */
class GoogleOAuth
{
    public const AUTHORIZE = 'https://accounts.google.com/o/oauth2/v2/auth';
    public const TOKEN = 'https://oauth2.googleapis.com/token';
    public const USERINFO = 'https://openidconnect.googleapis.com/v1/userinfo';

    public static function configured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    /** Adresse de la page de choix du compte Google. */
    public function authorizeUrl(string $redirectUri, string $state): string
    {
        return self::AUTHORIZE.'?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
            'access_type' => 'online',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Code reçu au retour → identité Google.
     *
     * @return array{id: string, email: string, name: string, locale: ?string}
     */
    public function user(string $code, string $redirectUri): array
    {
        $token = Http::asForm()->timeout(15)->post(self::TOKEN, [
            'code' => $code,
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);
        if (! $token->successful() || ! is_string($access = $token->json('access_token'))) {
            throw new RuntimeException(__('Google n’a pas confirmé la connexion. Réessayez.'));
        }

        $info = Http::withToken($access)->timeout(15)->acceptJson()->get(self::USERINFO);
        $sub = $info->json('sub');
        $email = $info->json('email');
        if (! $info->successful() || ! is_string($sub) || ! is_string($email)) {
            throw new RuntimeException(__('Google n’a pas confirmé la connexion. Réessayez.'));
        }
        if ($info->json('email_verified') !== true && $info->json('email_verified') !== 'true') {
            throw new RuntimeException(__('Votre adresse Google n’est pas vérifiée. Vérifiez-la chez Google ou créez un compte avec un mot de passe.'));
        }

        $locale = substr((string) $info->json('locale'), 0, 2);

        return [
            'id' => $sub,
            'email' => strtolower($email),
            'name' => trim((string) ($info->json('name') ?: strstr($email, '@', true))),
            'locale' => $locale !== '' ? $locale : null,
        ];
    }
}
