<?php

namespace App\Platform\Notifications;

use App\Hosting\Site;
use App\Notifications\Notice;

/** Essai hébergé bientôt terminé (3 jours puis 1 jour avant). */
class TrialEndingSoon extends Notice
{
    public function __construct(private Site $site, private int $days) {}

    public function title(): array
    {
        return $this->days <= 1
            ? ['Votre essai se termine demain', []]
            : ['Votre essai se termine dans :days jours', ['days' => $this->days]];
    }

    public function body(): ?array
    {
        return ['Choisissez une offre pour garder « :name » en ligne avec toutes vos pages. Sans offre, le site passe en lecture seule à la fin de l’essai.', ['name' => $this->site->name]];
    }

    public function url(): ?string
    {
        return route('platform.customer.plans');
    }

    public function actionText(): string
    {
        return 'Voir les offres';
    }

    public function icon(): string
    {
        return 'clock';
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
