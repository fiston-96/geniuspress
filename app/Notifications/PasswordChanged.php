<?php

namespace App\Notifications;

/** Mot de passe changé (sécurité) : toujours par e-mail, pour qu'un changement inattendu se voie. */
class PasswordChanged extends Notice
{
    public function __construct(private string $resetUrl) {}

    public function title(): array
    {
        return ['Votre mot de passe a été changé', []];
    }

    public function body(): ?array
    {
        return ['Si ce n’est pas vous, choisissez tout de suite un nouveau mot de passe avec « Mot de passe oublié » et prévenez-nous.', []];
    }

    public function url(): ?string
    {
        return $this->resetUrl;
    }

    public function actionText(): string
    {
        return 'Choisir un nouveau mot de passe';
    }

    public function icon(): string
    {
        return 'shield';
    }

    public function mail(object $notifiable): bool
    {
        return true;
    }
}
