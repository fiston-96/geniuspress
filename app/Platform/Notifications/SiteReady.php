<?php

namespace App\Platform\Notifications;

use App\Hosting\Site;
use App\Notifications\Notice;

/** Le site hébergé du client est prêt. */
class SiteReady extends Notice
{
    public function __construct(private Site $site) {}

    public function title(): array
    {
        return ['Votre site « :name » est prêt', ['name' => $this->site->name]];
    }

    public function body(): ?array
    {
        return ['Ouvrez-le depuis votre espace pour commencer à le construire : :url', ['url' => $this->site->url()]];
    }

    public function url(): ?string
    {
        return route('platform.customer.dashboard');
    }

    public function actionText(): string
    {
        return 'Ouvrir mon espace';
    }

    public function icon(): string
    {
        return 'site';
    }

    public function mail(object $notifiable): bool
    {
        return true;
    }
}
