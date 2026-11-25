<?php

namespace App\Platform\Payments;

use App\Platform\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Prestataire de test : aucun appel réseau, aucun argent. Le client est envoyé
 * sur une page de la plateforme où il simule la réussite ou l'échec.
 * Refusé en production sauf GP_PLATFORM_SANDBOX=true (voir PaymentManager).
 */
class SandboxPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'sandbox';
    }

    public function methods(): array
    {
        return ['mobile_money', 'card'];
    }

    public function operators(): array
    {
        return [
            'orange' => 'Orange Money',
            'mtn' => 'MTN MoMo',
            'airtel' => 'Airtel Money',
            'mpesa' => 'M-Pesa',
            'moov' => 'Moov Money',
            'wave' => 'Wave',
        ];
    }

    public function start(Payment $payment, array $details): PaymentStart
    {
        Cache::put($this->key($payment->reference), 'pending', now()->addDay());

        return new PaymentStart(
            redirectUrl: route('platform.sandbox.show', $payment->reference),
            message: $payment->method === 'mobile_money'
                ? __('Une demande de paiement a été envoyée au :phone. Validez-la sur votre téléphone.', ['phone' => $payment->phone])
                : null,
        );
    }

    public function verify(Payment $payment): string
    {
        return Cache::get($this->key($payment->reference), 'pending');
    }

    /** Résultat choisi sur la page de test. */
    public function simulate(Payment $payment, bool $success): void
    {
        Cache::put($this->key($payment->reference), $success ? 'succeeded' : 'failed', now()->addDay());
    }

    /** Notification signée par HMAC-SHA256 du corps avec la clé de l'application (en-tête X-Sandbox-Signature). */
    public function webhookReference(Request $request): ?string
    {
        $expected = hash_hmac('sha256', $request->getContent(), (string) config('app.key'));
        if (! hash_equals($expected, (string) $request->header('X-Sandbox-Signature'))) {
            return null;
        }
        $reference = $request->json('reference');

        return is_string($reference) ? $reference : null;
    }

    private function key(string $reference): string
    {
        return 'platform-sandbox-payment:'.$reference;
    }
}
