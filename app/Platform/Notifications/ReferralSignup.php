<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\Customer;

/** Un client apporté vient de s'inscrire avec le lien de l’affilié. */
class ReferralSignup extends Notice
{
    public function __construct(private Customer $referred) {}

    public function title(): array
    {
        return ['Nouveau client apporté : :name', ['name' => $this->referred->name]];
    }

    public function body(): ?array
    {
        return ['Vous gagnerez :rate % de sa première commande payée.', ['rate' => \App\Platform\Referrals\Referrals::rate()]];
    }

    public function url(): ?string
    {
        return route('platform.customer.referrals');
    }

    public function icon(): string
    {
        return 'user';
    }
}
