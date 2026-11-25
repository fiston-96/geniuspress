<?php

namespace App\Platform\Payments;

/** Suite d'un paiement démarré : page du prestataire, ou attente de confirmation sur le téléphone. */
final class PaymentStart
{
    public function __construct(
        public readonly ?string $redirectUrl = null,
        public readonly ?string $message = null,
    ) {}
}
