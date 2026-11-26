<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;
use App\Platform\Models\Domain;
use App\Platform\Models\Service;

/** Domaine ou service bientôt expiré : rappel au client (cloche, e-mail et SMS) avec le lien pour renouveler. */
class ExpiringSoon extends Notice
{
    public function __construct(private Domain|Service $item, private int $days) {}

    private function label(): string
    {
        return $this->item instanceof Domain ? $this->item->name : $this->item->label();
    }

    public function title(): array
    {
        return $this->days <= 1
            ? [':item expire demain', ['item' => $this->label()]]
            : [':item expire dans :days jours', ['item' => $this->label(), 'days' => $this->days]];
    }

    public function body(): ?array
    {
        return $this->item instanceof Domain
            ? ['Renouvelez-le depuis votre espace pour garder votre adresse : après l’échéance, le site et les e-mails à ce nom cessent de fonctionner.', []]
            : ['Renouvelez-le depuis votre espace pour éviter l’interruption du service.', []];
    }

    public function url(): ?string
    {
        return $this->item instanceof Domain ? route('platform.customer.domains') : route('platform.customer.plans').'#services';
    }

    public function icon(): string
    {
        return 'clock';
    }

    public function mail(object $notifiable): bool
    {
        return true;
    }

    public function sms(object $notifiable): ?string
    {
        return $this->smsFromTitle();
    }
}
