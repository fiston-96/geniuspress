<?php

namespace App\Platform\Payments;

use App\Models\Setting;
use App\Platform\Models\Payment;
use App\Platform\PlatformException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Monerails (https://docs.monerails.com) : encaissement Mobile Money dans les pays ouverts
 * par l'API (Afrique centrale, de l'Ouest et de l'Est), chacun dans sa devise. Monerails
 * n'encaisse pas les cartes et refuse l'USD : une commande en USD est payée dans la devise du
 * pays du client, au taux réglé dans la console (Checkout::pay).
 *
 * Clés : console → Paiements (chiffrées), sinon GP_MONERAILS_KEY (« mr_test_… » ou « mr_live_… »)
 * et GP_MONERAILS_WEBHOOK_SECRET (« whsec_… », secret du point de notification).
 */
class MonerailsGateway implements PaymentGateway
{
    /** Devises ouvertes à l'encaissement par l'API (Couverture, novembre 2026). */
    public const CURRENCIES = ['XAF', 'XOF', 'GNF', 'CDF', 'GMD', 'UGX', 'TZS', 'RWF'];

    /** Pays ouverts aux encaissements par l'API et leur devise. */
    public const COUNTRIES = [
        'CD' => 'CDF',
        'CM' => 'XAF', 'GA' => 'XAF', 'TD' => 'XAF', 'CG' => 'XAF', 'CF' => 'XAF',
        'CI' => 'XOF', 'SN' => 'XOF', 'BJ' => 'XOF', 'TG' => 'XOF', 'BF' => 'XOF', 'ML' => 'XOF', 'NE' => 'XOF', 'GW' => 'XOF',
        'GN' => 'GNF', 'GM' => 'GMD',
        'UG' => 'UGX', 'TZ' => 'TZS', 'RW' => 'RWF',
    ];

    /** Opérateurs par pays si la liste en direct est injoignable (codes de la page Couverture). */
    private const FALLBACK_OPERATORS = [
        'CD' => ['AIRTEL' => 'Airtel Money', 'MPESA' => 'M-Pesa', 'ORANGE' => 'Orange Money', 'AFRIMONEY' => 'Afrimoney', 'VODACOM' => 'Vodacom'],
        'CM' => ['MTN' => 'MTN MoMo', 'ORANGE' => 'Orange Money'],
        'GA' => ['AIRTEL' => 'Airtel Money', 'MOOV' => 'Moov Money'],
        'TD' => ['AIRTEL' => 'Airtel Money', 'MOOV' => 'Moov Money'],
        'CG' => ['MTN' => 'MTN MoMo', 'AIRTEL' => 'Airtel Money'],
        'CF' => ['ORANGE' => 'Orange Money'],
        'CI' => ['ORANGE' => 'Orange Money', 'MTN' => 'MTN MoMo', 'MOOV' => 'Moov Money', 'WAVE' => 'Wave', 'DJAMO' => 'Djamo'],
        'SN' => ['ORANGE' => 'Orange Money', 'WAVE' => 'Wave', 'FREE' => 'Mixx by Yas', 'EMONEY' => 'E-money'],
        'BJ' => ['MTN' => 'MTN MoMo', 'MOOV' => 'Moov Money', 'CELTIIS' => 'Celtiis Cash', 'CORIS' => 'Coris Money'],
        'TG' => ['TMONEY' => 'Mixx by Yas', 'MOOV' => 'Moov Money'],
        'BF' => ['ORANGE' => 'Orange Money', 'MOOV' => 'Moov Money', 'WAVE' => 'Wave'],
        'ML' => ['ORANGE' => 'Orange Money', 'MOOV' => 'Moov Money'],
        'NE' => ['AIRTEL' => 'Airtel Money', 'MOOV' => 'Moov Money', 'AMANATA' => 'Amanata', 'NITA' => 'Nita', 'ZAMANI' => 'Zamani Cash'],
        'GW' => ['ORANGE' => 'Orange Money'],
        'GN' => ['MTN' => 'MTN MoMo', 'ORANGE' => 'Orange Money'],
        'GM' => ['AFRIMONEY' => 'Afrimoney'],
        'UG' => ['MTN' => 'MTN MoMo', 'AIRTEL' => 'Airtel Money'],
        'TZ' => ['VODACOM' => 'Vodacom M-Pesa', 'AIRTEL' => 'Airtel Money', 'TIGO' => 'Mixx by Yas'],
        'RW' => ['MTN' => 'MTN MoMo', 'AIRTEL' => 'Airtel Money'],
    ];

    /** Écart toléré entre l'horodatage d'une notification et notre horloge. */
    public const WEBHOOK_TOLERANCE = 300;

    public function name(): string
    {
        return 'monerails';
    }

    public function methods(): array
    {
        return ['mobile_money'];
    }

    /** Tous les opérateurs connus (sans pays) ; le parcours de paiement utilise operatorsFor(). */
    public function operators(): array
    {
        return array_merge(...array_values(self::FALLBACK_OPERATORS));
    }

    // ─── Clés ────────────────────────────────────────────────────────────

    /** Clé API : console (chiffrée), sinon .env. */
    public static function key(): string
    {
        return self::secretSetting('platform_monerails_key') ?? (string) config('geniuspress.platform.monerails.key');
    }

    public static function webhookSecret(): string
    {
        return self::secretSetting('platform_monerails_webhook_secret') ?? (string) config('geniuspress.platform.monerails.webhook_secret');
    }

    private static function secretSetting(string $name): ?string
    {
        $stored = Setting::read($name);
        if (! $stored) {
            return null;
        }
        try {
            return Crypt::decryptString((string) $stored);
        } catch (Throwable) {
            return null;
        }
    }

    /** Mode de la clé : « live », « test » ou null sans clé. */
    public static function mode(): ?string
    {
        $key = self::key();

        return $key === '' ? null : (str_starts_with($key, 'mr_live_') ? 'live' : 'test');
    }

    /** Clé de test refusée en production (elle « encaisserait » sans argent réel), sauf GP_PLATFORM_SANDBOX=true. */
    public static function usable(): bool
    {
        $key = self::key();
        if ($key === '') {
            return false;
        }

        return ! str_starts_with($key, 'mr_test_') || PaymentManager::sandboxAllowed();
    }

    // ─── Pays et opérateurs ──────────────────────────────────────────────

    /**
     * Opérateurs ouverts dans un pays : liste en direct de Monerails (gardée 10 minutes),
     * ou liste de repli si l'API ne répond pas.
     *
     * @return array<string, array{name: string, otp: bool}>
     */
    public function operatorsFor(string $country): array
    {
        $country = strtoupper($country);
        $currency = self::COUNTRIES[$country] ?? null;
        if ($currency === null) {
            return [];
        }
        $live = $this->liveOperators($currency);
        if ($live !== null) {
            return $live[$country] ?? [];
        }

        return array_map(fn ($name) => ['name' => $name, 'otp' => false], self::FALLBACK_OPERATORS[$country] ?? []);
    }

    /** @return array<string, array<string, array{name: string, otp: bool}>>|null pays → opérateurs, null si injoignable */
    private function liveOperators(string $currency): ?array
    {
        $cacheKey = 'monerails-operators:'.self::mode().':'.$currency;
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }
        try {
            $response = $this->send(fn (PendingRequest $http) => $http->get('operators', ['currency' => $currency]));
        } catch (Throwable) {
            return null;
        }
        if (! $response->successful()) {
            return null;
        }
        $byCountry = [];
        foreach ((array) $response->json('data') as $operator) {
            $code = (string) ($operator['code'] ?? '');
            $country = strtoupper((string) ($operator['country'] ?? ''));
            if (! preg_match('/^[A-Z0-9_]{2,30}$/', $code) || ! isset(self::COUNTRIES[$country])) {
                continue;
            }
            $byCountry[$country][$code] = [
                'name' => mb_substr((string) ($operator['name'] ?? $code), 0, 60),
                'otp' => in_array('OTP', (array) ($operator['authorization'] ?? []), true),
            ];
        }
        Cache::put($cacheKey, $byCountry, now()->addMinutes(10));

        return $byCountry;
    }

    // ─── Encaissement ────────────────────────────────────────────────────

    public function start(Payment $payment, array $details): PaymentStart
    {
        if (! in_array($payment->currency, self::CURRENCIES, true)) {
            throw new PlatformException(__('Le paiement Mobile Money n’est pas disponible dans la devise :currency.', ['currency' => $payment->currency]));
        }
        // CDF, GMD, TZS : centimes, par multiples de 100 (des unités entières).
        if (\App\Platform\Money::decimals($payment->currency) === 2 && $payment->amount % 100 !== 0) {
            throw new PlatformException(__('Le montant doit être un nombre entier dans la devise :currency.', ['currency' => $payment->currency]));
        }

        $order = $payment->order()->with('customer')->first();
        $response = $this->send(fn (PendingRequest $http) => $http
            ->withHeaders(['Idempotency-Key' => $payment->reference])
            ->post('collections', array_filter([
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'phone' => (string) $payment->phone,
                'operator' => (string) $payment->operator,
                'customer' => ['name' => mb_substr(trim($order->customer->name) ?: 'Client', 0, 80)],
                'country' => $payment->country ?: ($order->customer->country ?: null),
                'description' => mb_substr(__('Commande :number', ['number' => $order->number]), 0, 500),
                'client_reference' => $payment->reference,
                'metadata' => ['order' => $order->number],
            ], fn ($v) => $v !== null)));

        if (! $response->successful()) {
            throw new PlatformException($this->errorMessage($response));
        }
        $collection = (array) $response->json();
        $payment->update(['gateway_reference' => mb_substr((string) ($collection['id'] ?? ''), 0, 100) ?: null]);

        $action = (array) ($collection['next_action'] ?? []);
        if (($collection['status'] ?? null) === 'failed' || ($collection['status'] ?? null) === 'expired') {
            throw new PlatformException(__('Paiement refusé ou annulé.'));
        }
        if (($collection['status'] ?? null) === 'requires_otp' || ($action['type'] ?? null) === 'otp') {
            // Code reçu par SMS, ou à obtenir en composant un code USSD (Orange Money en Guinée…).
            $payment->update(['awaiting' => 'otp']);

            return new PaymentStart(message: ($action['ussd_code'] ?? null)
                ? __('Composez :code sur votre téléphone pour obtenir votre code de paiement, puis saisissez-le ci-dessous.', ['code' => $action['ussd_code']])
                : __('Saisissez ci-dessous le code de paiement reçu par SMS au :phone.', ['phone' => $payment->phone]));
        }
        if (($action['type'] ?? null) === 'redirect' && is_string($action['url'] ?? null) && str_starts_with($action['url'], 'https://')) {
            return new PaymentStart(redirectUrl: $action['url']);
        }

        return new PaymentStart(message: ($action['ussd_code'] ?? null)
            ? __('Une demande de paiement a été envoyée au :phone. Validez-la sur votre téléphone (ou composez :code si elle n’arrive pas), puis cliquez sur « J’ai payé : vérifier ».', ['phone' => $payment->phone, 'code' => $action['ussd_code']])
            : __('Une demande de paiement a été envoyée au :phone. Validez-la sur votre téléphone.', ['phone' => $payment->phone]));
    }

    /** Transmet le code de paiement (OTP) saisi par le client. */
    public function authorize(Payment $payment, string $otp): void
    {
        if (! $payment->gateway_reference || $payment->awaiting !== 'otp') {
            throw new PlatformException(__('Aucun code n’est attendu pour ce paiement.'));
        }
        $response = $this->send(fn (PendingRequest $http) => $http
            ->post('collections/'.rawurlencode($payment->gateway_reference).'/authorize', ['otp' => $otp]));
        if (! $response->successful()) {
            $code = (string) ($response->json('error.code') ?? '');
            throw new PlatformException(match ($code) {
                'otp_invalid' => __('Code incorrect. Vérifiez-le et réessayez.'),
                'otp_not_expected' => __('Aucun code n’est attendu pour ce paiement.'),
                default => $this->errorMessage($response),
            });
        }
        $payment->update(['awaiting' => null]);
    }

    public function verify(Payment $payment): string
    {
        if ($payment->gateway_reference) {
            $response = $this->send(fn (PendingRequest $http) => $http->get('collections/'.rawurlencode($payment->gateway_reference)));
            $collection = $response->successful() ? (array) $response->json() : null;
        } else {
            // Réponse perdue au démarrage : on retrouve la collecte par notre référence.
            $response = $this->send(fn (PendingRequest $http) => $http->get('collections', ['client_reference' => $payment->reference, 'limit' => 1]));
            $collection = $response->successful() ? ($response->json('data.0') ?? null) : null;
        }
        if (! is_array($collection) || ($collection['client_reference'] ?? null) !== $payment->reference) {
            return 'pending';
        }
        if (! $payment->gateway_reference && is_string($collection['id'] ?? null)) {
            $payment->update(['gateway_reference' => mb_substr($collection['id'], 0, 100)]);
        }

        return match ($collection['status'] ?? null) {
            // Le montant et la devise encaissés doivent être ceux du paiement demandé.
            'succeeded' => (int) ($collection['amount'] ?? -1) === $payment->amount && ($collection['currency'] ?? null) === $payment->currency ? 'succeeded' : 'failed',
            'failed', 'expired' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Soldes disponibles (console) : devise → montant en unités mineures.
     *
     * @return array<string, int>
     */
    public function balance(): array
    {
        $response = $this->send(fn (PendingRequest $http) => $http->get('balance'));
        if (! $response->successful()) {
            throw new PlatformException($response->status() === 401
                ? __('Monerails a refusé la clé API : vérifiez-la dans le tableau de bord Monerails.')
                : $this->errorMessage($response));
        }
        $balances = [];
        foreach ((array) $response->json('available') as $row) {
            if (is_string($row['currency'] ?? null)) {
                $balances[$row['currency']] = (int) ($row['amount'] ?? 0);
            }
        }

        return $balances;
    }

    /**
     * En-tête « Monerail-Signature: t=<secondes>,v1=<hex>[,v1=<hex>] » :
     * HMAC-SHA256 de « t.corps brut » avec le secret complet (« whsec_… »).
     */
    public function webhookReference(Request $request): ?string
    {
        $secret = self::webhookSecret();
        if ($secret === '') {
            return null;
        }
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', (string) $request->header('Monerail-Signature')) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($k === 't' && ctype_digit($v)) {
                $timestamp = (int) $v;
            } elseif ($k === 'v1' && $v !== '') {
                $signatures[] = $v;
            }
        }
        if ($timestamp === null || abs(time() - $timestamp) > self::WEBHOOK_TOLERANCE || ! $signatures) {
            return null;
        }
        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);
        $valid = false;
        foreach ($signatures as $signature) {
            $valid = hash_equals($expected, strtolower($signature)) || $valid;
        }
        if (! $valid) {
            return null;
        }
        $reference = $request->json('data.object.client_reference');

        return is_string($reference) && str_starts_with((string) $request->json('type'), 'collection.') ? $reference : null;
    }

    private function send(callable $call): Response
    {
        if (! self::usable()) {
            throw new PlatformException(__('Aucun moyen de paiement n’est configuré. Réessayez plus tard.'));
        }
        $http = Http::baseUrl(rtrim((string) config('geniuspress.platform.monerails.base_url'), '/'))
            ->withToken(self::key())
            ->acceptJson()->asJson()
            ->withHeaders(['Accept-Language' => app()->getLocale()])
            ->timeout(30);

        try {
            return $call($http);
        } catch (ConnectionException) {
            throw new PlatformException(__('Le service de paiement est injoignable. Réessayez dans un instant.'));
        }
    }

    /** Message affichable, selon le code d'erreur de Monerails (jamais le texte du prestataire). */
    private function errorMessage(Response $response): string
    {
        // Corps d'erreur : {"error": {"type", "code", "message", "param", "request_id"}} ; le message n'est pas stable.
        $code = (string) ($response->json('error.code') ?? '');

        return match (true) {
            in_array($code, ['phone_invalid', 'test_phone_required'], true) => __('Ce numéro Mobile Money est invalide. Indiquez-le avec l’indicatif du pays.'),
            in_array($code, ['operator_unsupported', 'operator_phone_mismatch'], true) => __('Cet opérateur Mobile Money n’est pas disponible pour ce numéro.'),
            in_array($code, ['currency_unsupported', 'country_unsupported'], true) => __('Le paiement Mobile Money n’est pas encore disponible pour ce pays ou cette devise.'),
            in_array($code, ['amount_too_small', 'amount_over_limit', 'daily_limit_exceeded', 'amount_invalid'], true) => __('Ce montant ne peut pas être payé par Mobile Money (minimum, plafond ou limite du jour de l’opérateur).'),
            $code === 'provider_declined' => __('Paiement refusé ou annulé.'),
            in_array($response->status(), [429, 502, 503], true) => __('Le service de paiement est momentanément indisponible. Réessayez dans un instant.'),
            default => (function () use ($response, $code) {
                report(new PlatformException("Monerails : erreur {$response->status()} {$code}"));

                return __('Le paiement n’a pas pu être lancé. Réessayez plus tard.');
            })(),
        };
    }
}
