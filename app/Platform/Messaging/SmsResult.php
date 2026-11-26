<?php

namespace App\Platform\Messaging;

/** Résultat d'un envoi accepté par le prestataire (la remise au téléphone n'est pas garantie). */
final class SmsResult
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $cost = null,
    ) {}
}
