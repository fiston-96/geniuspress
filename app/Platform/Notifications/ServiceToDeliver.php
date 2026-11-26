<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\Service;

/** Service partenaire à créer ou à renouveler à la main chez le fournisseur (console, cloche + e-mail). */
class ServiceToDeliver extends Notice
{
    public function __construct(private Service $service) {}

    public function title(): array
    {
        return $this->service->pending_action === 'renew'
            ? ['À renouveler chez le fournisseur : :service', ['service' => $this->service->label()]]
            : ['À livrer : :service', ['service' => $this->service->label()]];
    }

    public function body(): ?array
    {
        return ['Client : :name', ['name' => (string) $this->service->customer?->name]];
    }

    public function url(): ?string
    {
        return route('console.suppliers.index').'#livraisons';
    }

    public function icon(): string
    {
        return 'clock';
    }

    public function mail(object $notifiable): bool
    {
        return true;
    }
}
