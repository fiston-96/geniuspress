<?php

namespace App\Platform\Client;

use App\Models\Setting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Côté site d'un client : accès à la plateforme GeniusPress avec la clé de
 * licence (GP_LICENSE_KEY, ou réglage chiffré « gp_license_key »).
 */
class PlatformClient
{
    /** Clé de licence configurée, ou null. Jamais envoyée au navigateur. */
    public static function licenseKey(): ?string
    {
        $env = config('geniuspress.license.key');
        if (is_string($env) && $env !== '') {
            return $env;
        }
        try {
            $stored = Setting::read('gp_license_key');

            return $stored ? Crypt::decryptString($stored) : null;
        } catch (Throwable) {
            // Base pas encore installée, ou clé illisible (APP_KEY changée).
            return null;
        }
    }

    public function isConfigured(): bool
    {
        return self::licenseKey() !== null;
    }

    public function url(string $path = ''): string
    {
        return rtrim((string) config('geniuspress.license.platform_url'), '/').'/'.ltrim($path, '/');
    }

    public function request(int $timeout = 30): PendingRequest
    {
        return Http::baseUrl($this->url('api/platform/v1'))
            ->withToken((string) self::licenseKey())
            ->acceptJson()
            ->withHeaders(['Accept-Language' => app()->getLocale()])
            ->timeout($timeout);
    }

    /** Compte de la licence (offre, solde, modèles IA proposés, modèles achetés), ou null si injoignable. */
    public function account(): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }
        try {
            $response = $this->request(10)->get('account');

            return $response->successful() ? (array) $response->json() : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Télécharge un modèle payant acheté dans $path (l'empreinte est vérifiée ensuite par le catalogue).
     *
     * @throws \RuntimeException message affichable
     */
    public function downloadTemplate(string $id, string $path, int $maxBytes): void
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException(__('Ce modèle est payant : achetez-le dans votre espace client GeniusPress, puis ajoutez votre clé de licence dans Réglages.'));
        }
        try {
            $response = $this->request(120)->withOptions(['sink' => $path])->get('templates/'.rawurlencode($id).'/download');
        } catch (Throwable) {
            throw new \RuntimeException(__('Le téléchargement du modèle a échoué. Réessayez.'));
        }
        if (in_array($response->status(), [401, 403], true)) {
            throw new \RuntimeException($response->status() === 401
                ? __('La clé de licence GeniusPress est invalide ou a été révoquée.')
                : __('Ce modèle est payant : achetez-le dans votre espace client GeniusPress.'));
        }
        if (! $response->successful() || filesize($path) > $maxBytes) {
            throw new \RuntimeException(__('Le téléchargement du modèle a échoué. Réessayez.'));
        }
    }
}
