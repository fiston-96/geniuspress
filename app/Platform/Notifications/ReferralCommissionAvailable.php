<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\ReferralCommission;
use App\Platform\Money;

/** Commission disponible sur le solde d’affiliation. */
class ReferralCommissionAvailable extends Notice
{
    public function __construct(private ReferralCommission $commission) {}

    public function title(): array
    {
        return [':amount sont disponibles sur votre solde d’affiliation', ['amount' => Money::format($this->commission->amount, $this->commission->currency)]];
    }

    public function body(): ?array
    {
        return ['Utilisez-les pour payer vos commandes, ou demandez un retrait par Mobile Money.', []];
    }

    public function url(): ?string
    {
        return route('platform.customer.referrals');
    }

    public function actionText(): string
    {
        return 'Voir mon solde';
    }

    public function icon(): string
    {
        return 'payment';
    }

    public function mail(object $notifiable): bool
    {
        return true;
    }
}
