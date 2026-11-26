<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Money;

/** Solde prépayé d'un fournisseur sous le seuil réglé : les achats des clients risquent d'échouer. */
class SupplierBalanceLow extends Notice
{
    public function __construct(private string $provider, private int $balance, private string $currency) {}

    public function title(): array
    {
        return ['Solde :provider bas : :amount', ['provider' => strtoupper($this->provider), 'amount' => Money::format($this->balance, $this->currency)]];
    }

    public function body(): ?array
    {
        return ['Rechargez le compte prépayé : sans crédit, les domaines et hébergements payés par les clients ne peuvent pas être achetés.', []];
    }

    public function url(): ?string
    {
        return route('console.suppliers.index');
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
