<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\Payout;
use App\Platform\Money;

/** Retrait traité : envoyé (avec la référence du transfert) ou refusé (montant rendu au solde). */
class PayoutProcessed extends Notice
{
    public function __construct(private Payout $payout) {}

    public function title(): array
    {
        $amount = Money::format($this->payout->amount, $this->payout->currency);

        return $this->payout->status === 'paid'
            ? ['Retrait envoyé : :amount', ['amount' => $amount]]
            : ['Retrait refusé : :amount', ['amount' => $amount]];
    }

    public function body(): ?array
    {
        return $this->payout->status === 'paid'
            ? ['Transfert Mobile Money vers :phone, référence :reference.', ['phone' => $this->payout->phone, 'reference' => (string) $this->payout->transfer_reference]]
            : ['Le montant est revenu sur votre solde. Motif : :note', ['note' => (string) $this->payout->note]];
    }

    public function url(): ?string
    {
        return route('platform.customer.referrals');
    }

    public function icon(): string
    {
        return 'payment';
    }

    public function mail(object $notifiable): bool
    {
        return true;
    }

    /** SMS (message important), en plus de la cloche et de l'e-mail. */
    public function sms(object $notifiable): ?string
    {
        return $this->smsFromTitle();
    }
}
