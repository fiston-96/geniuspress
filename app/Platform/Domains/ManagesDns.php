<?php

namespace App\Platform\Domains;

/** Registraire qui gère aussi la zone DNS et le code de transfert des domaines qu'il enregistre. */
interface ManagesDns
{
    /**
     * Fait pointer le domaine et www vers cette adresse IPv4 (enregistrements A),
     * en retirant ce qui les contredirait (AAAA, CNAME sur les mêmes noms).
     *
     * @throws \App\Platform\PlatformException
     */
    public function pointTo(string $domain, string $ip): void;

    /**
     * Fait envoyer le code de transfert au titulaire, par e-mail.
     *
     * @throws \App\Platform\PlatformException
     */
    public function sendAuthCode(string $domain): void;
}
