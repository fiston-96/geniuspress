<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\ReferralCommission;
use App\Platform\Money;

/** Commission gagnée (en attente du délai de remboursement). */
class ReferralCommissionEarned extends Notice
{
    public function __construct(private ReferralCommission $commission) {}

    public function title(): array
    {
        return ['Vous avez gagné :amount grâce à :name', ['amount' => Money::format($this->commission->amount, $this->commission->currency), 'name' => (string) $this->commission->referred?->name]];
    }

    public function body(): ?array
    {
        return ['Commission de parrainage sur sa première commande. Elle sera disponible le :date.', ['date' => $this->commission->available_at->isoFormat('LL')]];
    }

    public function url(): ?string
    {
        return route('platform.customer.referrals');
    }

    public function actionText(): string
    {
        return 'Voir mon parrainage';
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
