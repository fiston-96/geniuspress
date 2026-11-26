<?php

namespace App\Platform\Domains;

/**
 * API d'un registraire de noms de domaine (LWS, OVH, Namecheap, ResellerClub…).
 * Une implémentation par registraire, choisie par GP_DOMAIN_REGISTRAR ; ses clés
 * sont dans le .env. Les prix de vente ne viennent pas du registraire mais de
 * la table platform_tld_prices, réglée par l'exploitant.
 */
interface DomainRegistrar
{
    public function name(): string;

    /**
     * Disponibilité à l'enregistrement.
     *
     * @param  list<string>  $domains  noms complets en minuscules (« exemple.cd »)
     * @return array<string, bool>
     */
    public function check(array $domains): array;

    /**
     * @param  array{name: string, email: string, phone?: ?string, country?: ?string, company?: ?string}  $contact  titulaire
     * @return array{reference: string, expires_at: \DateTimeInterface}
     *
     * @throws \App\Platform\PlatformException
     */
    public function register(string $domain, int $years, array $contact): array;

    /**
     * @return array{expires_at: \DateTimeInterface}
     *
     * @throws \App\Platform\PlatformException
     */
    public function renew(string $domain, int $years, \DateTimeInterface $currentExpiry, ?string $reference): array;
}
