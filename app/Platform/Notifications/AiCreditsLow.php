<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Money;

/** Solde de crédits IA passé sous le seuil d'alerte (réglage platform_low_credit_threshold). */
class AiCreditsLow extends Notice
{
    public function __construct(private int $balance) {}

    public function title(): array
    {
        return ['Vos crédits IA sont presque épuisés', []];
    }

    public function body(): ?array
    {
        return ['Il vous reste :balance. Rechargez pour continuer à utiliser l’assistant IA.', ['balance' => Money::micros(max(0, $this->balance))]];
    }

    public function url(): ?string
    {
        return route('platform.customer.plans').'#ai_credits';
    }

    public function actionText(): string
    {
        return 'Recharger mes crédits';
    }

    public function icon(): string
    {
        return 'sparkles';
    }

    public function mail(object $notifiable): bool
    {
        return true;
    }
}
