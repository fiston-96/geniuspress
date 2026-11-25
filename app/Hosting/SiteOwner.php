<?php

namespace App\Hosting;

/**
 * Propriétaire d'un site hébergé, tel que la plateforme le connaît. Son compte
 * administrateur sur le site reprend ces informations (mot de passe déjà chiffré).
 */
final class SiteOwner
{
    public function __construct(
        public readonly string $accountId,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $passwordHash = null,
        public readonly ?string $locale = null,
    ) {}
}
