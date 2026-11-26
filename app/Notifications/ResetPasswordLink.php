<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Lien « mot de passe oublié » (administration d'un site ou espace client de la plateforme).
 * L'adresse du lien est calculée par le modèle (route de son espace), valable 60 minutes.
 */
class ResetPasswordLink extends Notification
{
    use Queueable;

    public function __construct(public readonly string $url, public readonly int $minutes = 60) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Choisissez un nouveau mot de passe'))
            ->greeting(__('Bonjour :name,', ['name' => $notifiable->name]))
            ->line(__('Vous avez demandé à changer le mot de passe de votre compte. Cliquez sur le bouton ci-dessous pour en choisir un nouveau.'))
            ->action(__('Choisir un nouveau mot de passe'), $this->url)
            ->line(__('Ce lien est valable :minutes minutes et ne sert qu’une fois.', ['minutes' => $this->minutes]))
            ->line(__('Si vous n’avez rien demandé, ignorez ce message : votre mot de passe reste le même.'));
    }
}
