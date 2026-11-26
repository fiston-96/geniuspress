<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\Service;

/** Service partenaire prêt (ou en échec) : au client, cloche + e-mail. */
class ServiceDelivered extends Notice
{
    public function __construct(private Service $service) {}

    public function title(): array
    {
        return $this->service->status === 'failed'
            ? ['Livraison impossible : :service', ['service' => $this->service->label()]]
            : ['Votre service est prêt : :service', ['service' => $this->service->label()]];
    }

    public function body(): ?array
    {
        return $this->service->note ? [':note', ['note' => $this->service->note]] : null;
    }

    public function url(): ?string
    {
        return route('platform.customer.plans').'#services';
    }

    public function icon(): string
    {
        return $this->service->status === 'failed' ? 'bell' : 'site';
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
