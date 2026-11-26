<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;

/** Achat payé introuvable chez le fournisseur après le délai de traitement : à vérifier dans l'espace LWS. */
class SupplierPurchaseMissing extends Notice
{
    public function __construct(private string $name) {}

    public function title(): array
    {
        return ['Achat introuvable chez LWS : :name', ['name' => $this->name]];
    }

    public function body(): ?array
    {
        return ['Payé par le client depuis plus de deux jours, il n’apparaît pas sur le compte LWS. Vérifiez l’historique des achats et le solde prépayé.', []];
    }

    public function url(): ?string
    {
        return route('console.domains.index');
    }

    public function icon(): string
    {
        return 'bell';
    }

    public function mail(object $notifiable): bool
    {
        return true;
    }
}
