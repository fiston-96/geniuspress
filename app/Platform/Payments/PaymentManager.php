<?php

namespace App\Platform\Payments;

use App\Platform\PlatformException;

/** Choisit le prestataire de paiement configuré (GP_PAYMENT_GATEWAY). */
class PaymentManager
{
    /** Prestataires connus : nom => classe. Les prestataires réels s'ajoutent ici. */
    public const GATEWAYS = [
        'sandbox' => SandboxPaymentGateway::class,
    ];

    public function gateway(?string $name = null): PaymentGateway
    {
        $name ??= (string) config('geniuspress.platform.payment_gateway');
        $class = self::GATEWAYS[$name] ?? throw new PlatformException(__('Aucun moyen de paiement n’est configuré. Réessayez plus tard.'));
        if ($name === 'sandbox' && ! self::sandboxAllowed()) {
            throw new PlatformException(__('Aucun moyen de paiement n’est configuré. Réessayez plus tard.'));
        }

        return app($class);
    }

    /** Paiements simulés : jamais en production, sauf choix explicite (GP_PLATFORM_SANDBOX=true). */
    public static function sandboxAllowed(): bool
    {
        $explicit = config('geniuspress.platform.sandbox');

        return $explicit !== null && $explicit !== '' ? filter_var($explicit, FILTER_VALIDATE_BOOLEAN) : ! app()->isProduction();
    }
}
