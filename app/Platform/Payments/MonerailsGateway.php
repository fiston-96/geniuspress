<?php

namespace App\Platform\Payments;

use App\Platform\Models\Payment;
use App\Platform\PlatformException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Monerails (https://docs.monerails.com) : encaissement Mobile Money dans 22 pays
 * africains, dont la RDC (Airtel Money, M-Pesa, Orange Money, Afrimoney, Vodacom).
 * Monerails n'encaisse pas les cartes : seul le Mobile Money est proposé.
 *
 * Clés dans le .env uniquement : GP_MONERAILS_KEY (« mr_test_… » ou « mr_live_… »)
 * et GP_MONERAILS_WEBHOOK_SECRET (« whsec_… », secret du point de notification).
 */
class MonerailsGateway implements PaymentGateway
{
    /** Devises ouvertes à l'encaissement par l'API (documentation, version 2026-10-30). */
    public const CURRENCIES = ['XAF', 'XOF', 'GNF', 'CDF', 'GMD', 'UGX', 'TZS', 'RWF'];

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

    /**
     * Codes d'opérateurs Monerails (ceux de la RDC d'abord, d'après la page « Coverage »).
     * À confirmer avant la mise en service avec GET /v1/operators?currency=CDF&country=CD.
     */
    public function operators(): array
    {
        return [
            'AIRTEL' => 'Airtel Money',
            'MPESA' => 'M-Pesa',
            'ORANGE' => 'Orange Money',
            'AFRIMONEY' => 'Afrimoney',
            'VODACOM' => 'Vodacom',
            'MTN' => 'MTN MoMo',
            'WAVE' => 'Wave',
        ];
    }

    /** Clé de test refusée en production (elle « encaisserait » sans argent réel), sauf GP_PLATFORM_SANDBOX=true. */
    public static function usable(): bool
    {
        $key = (string) config('geniuspress.platform.monerails.key');
        if ($key === '') {
            return false;
        }

        return ! str_starts_with($key, 'mr_test_') || PaymentManager::sandboxAllowed();
    }

    public function start(Payment $payment, array $details): PaymentStart
    {
        if (! in_array($payment->currency, self::CURRENCIES, true)) {
            throw new PlatformException(__('Le paiement Mobile Money n’est pas disponible dans la devise :currency. Choisissez une offre en francs (CDF, XAF, XOF…).', ['currency' => $payment->currency]));
        }
        // Francs congolais : centimes, par multiples de 100 (des francs entiers).
        if ($payment->currency === 'CDF' && $payment->amount % 100 !== 0) {
            throw new PlatformException(__('Le montant doit être un nombre entier de francs congolais.'));
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
                'country' => $order->customer->country ?: null,
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
            // Validation par code SMS : pas encore prise en charge par l'espace client.
            throw new PlatformException(__('Cet opérateur demande un code reçu par SMS, ce qui n’est pas encore pris en charge. Choisissez un autre opérateur.'));
        }
        if (($action['type'] ?? null) === 'redirect' && is_string($action['url'] ?? null) && str_starts_with($action['url'], 'https://')) {
            return new PaymentStart(redirectUrl: $action['url']);
        }

        return new PaymentStart(message: ($action['ussd_code'] ?? null)
            ? __('Composez :code sur votre téléphone pour valider le paiement, puis cliquez sur « J’ai payé : vérifier ».', ['code' => $action['ussd_code']])
            : __('Une demande de paiement a été envoyée au :phone. Validez-la sur votre téléphone.', ['phone' => $payment->phone]));
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
            // Le montant et la devise encaissés doivent être ceux de la commande.
            'succeeded' => (int) ($collection['amount'] ?? -1) === $payment->amount && ($collection['currency'] ?? null) === $payment->currency ? 'succeeded' : 'failed',
            'failed', 'expired' => 'failed',
            default => 'pending',
        };
    }

    /**
     * En-tête « Monerail-Signature: t=<secondes>,v1=<hex>[,v1=<hex>] » :
     * HMAC-SHA256 de « t.corps brut » avec le secret complet (« whsec_… »).
     */
    public function webhookReference(Request $request): ?string
    {
        $secret = (string) config('geniuspress.platform.monerails.webhook_secret');
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
            ->withToken((string) config('geniuspress.platform.monerails.key'))
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
