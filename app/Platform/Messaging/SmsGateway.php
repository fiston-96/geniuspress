<?php

namespace App\Platform\Messaging;

/**
 * Prestataire SMS. Africa's Talking couvre une vingtaine de pays africains ; d'autres
 * prestataires (Afrique centrale, RD Congo) s'ajoutent dans Sms::GATEWAYS. Le premier
 * prestataire configuré qui couvre le numéro l'envoie.
 */
interface SmsGateway
{
    public function name(): string;

    /** Clés réglées (console → Messagerie). */
    public function configured(): bool;

    /** Le numéro (E.164, « +221770000000 ») est-il dans un pays couvert ? */
    public function covers(string $phone): bool;

    /** @throws \App\Platform\PlatformException refus ou erreur du prestataire (message en français) */
    public function send(string $phone, string $text): SmsResult;
}
