<?php

namespace App\Platform\Suppliers\Lws;

use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * API « Noms de domaine & Emails » de LWS (https://docs-api.lws.net/fr/api-client/).
 *
 * Authentification par en-têtes X-Auth-Login (identifiant LWS, le nombre seul) et
 * X-Auth-Pass (clé API) ; l'adresse IP du serveur doit être autorisée dans l'espace LWS.
 * Mode test (X-Test-Mode: true) : rien n'est créé ni facturé. Réponses JSON
 * { code, info, data } ; les achats sont débités sur le compte prépayé et traités en différé.
 *
 * Identifiant et clé : réglés dans la console (clé chiffrée), sinon GP_LWS_LOGIN / GP_LWS_KEY.
 * Aucun appel réel dans les tests (Http::fake).
 */
class LwsClient
{
    public function configured(): bool
    {
        return $this->login() !== '' && $this->key() !== '';
    }

    /** Mode test : activé tant que l'exploitant ne l'a pas désactivé dans la console. */
    public function testMode(): bool
    {
        return (bool) Setting::read('platform_lws_test', true);
    }

    public function login(): string
    {
        return trim((string) (Setting::read('platform_lws_login') ?: config('geniuspress.platform.lws.login')));
    }

    private function key(): string
    {
        $stored = Setting::read('platform_lws_key');
        if ($stored) {
            try {
                return Crypt::decryptString((string) $stored);
            } catch (Throwable) {
                return '';
            }
        }

        return (string) config('geniuspress.platform.lws.key');
    }

    public function get(string $path, array $query = []): mixed
    {
        return $this->send('GET', $path, $query);
    }

    public function post(string $path, array $body = []): mixed
    {
        return $this->send('POST', $path, $body);
    }

    public function put(string $path, array $body = []): mixed
    {
        return $this->send('PUT', $path, $body);
    }

    public function delete(string $path, array $body = []): mixed
    {
        return $this->send('DELETE', $path, $body);
    }

    /** Envoie la requête et renvoie « data », ou lève une LwsException au message en français. */
    public function send(string $method, string $path, array $payload = []): mixed
    {
        if (! $this->configured()) {
            throw new LwsException(__('La connexion à LWS n’est pas configurée (console → Fournisseurs).'), 0);
        }
        $headers = ['X-Auth-Login' => $this->login(), 'X-Auth-Pass' => $this->key()];
        if ($this->testMode()) {
            $headers['X-Test-Mode'] = 'true';
        }
        $url = rtrim((string) config('geniuspress.platform.lws.url'), '/').'/'.ltrim($path, '/');

        try {
            $request = Http::withHeaders($headers)->acceptJson()->timeout(30);
            $response = $method === 'GET' ? $request->get($url, $payload) : $request->send($method, $url, ['json' => $payload]);
        } catch (ConnectionException) {
            throw new LwsException(__('LWS ne répond pas pour le moment. Réessayez dans quelques minutes.'), 0);
        }

        $json = $response->json();
        $code = is_array($json) && isset($json['code']) ? (int) $json['code'] : $response->status();
        // 201 : déjà fait (enregistrement DNS déjà supprimé, par exemple).
        if ($response->successful() && in_array($code, [200, 201], true)) {
            return is_array($json) ? ($json['data'] ?? null) : null;
        }
        $status = $response->successful() ? $code : $response->status();
        $info = is_array($json) ? trim((string) ($json['info'] ?? '')) : '';

        throw new LwsException(match (true) {
            $status === 401, $status === 403 => __('LWS a refusé l’accès à l’API : vérifiez l’identifiant, la clé et l’adresse IP autorisée.'),
            $status === 429 => __('Trop de demandes envoyées à LWS : réessayez dans une minute.'),
            $status === 314 => __('Ce domaine n’est pas sur le compte LWS de la plateforme.'),
            $status >= 500 => __('LWS rencontre un problème pour le moment. Réessayez plus tard.'),
            default => __('LWS a refusé la demande : :info', ['info' => mb_substr($info !== '' ? $info : (string) $status, 0, 160)]),
        }, $status);
    }
}
