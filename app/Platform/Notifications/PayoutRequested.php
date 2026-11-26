<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\Payout;
use App\Platform\Money;

/** Demande de retrait à traiter (cloche de la console). */
class PayoutRequested extends Notice
{
    public function __construct(private Payout $payout) {}

    public function title(): array
    {
        return ['Retrait demandé : :amount', ['amount' => Money::format($this->payout->amount, $this->payout->currency)]];
    }

    public function body(): ?array
    {
        return [':name · :operator :phone', ['name' => (string) $this->payout->customer?->name, 'operator' => $this->payout->mobile_operator, 'phone' => $this->payout->phone]];
    }

    public function url(): ?string
    {
        return route('console.referrals.index').'#retraits';
    }

    public function icon(): string
    {
        return 'payment';
    }
}
