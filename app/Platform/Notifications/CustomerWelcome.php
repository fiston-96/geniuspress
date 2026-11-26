<?php

namespace App\Platform\Notifications;

use App\Notifications\Notice;

/** Bienvenue (compte client créé, par mot de passe ou avec Google). */
class CustomerWelcome extends Notice
{
    public function title(): array
    {
        return ['Bienvenue sur GeniusPress', []];
    }

    public function body(): ?array
    {
        return ['Créez votre site en un clic depuis votre espace : builder, modèles et assistant IA compris.', []];
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
        return 'sparkles';
    }

    public function mail(object $notifiable): bool
    {
        return true;
    }
}
