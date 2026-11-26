<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Double confirmation de l'inscription à la newsletter (e-mail seulement, l'abonné n'a pas de compte). */
class NewsletterConfirmation extends Notification
{
    use Queueable;

    public function __construct(public readonly string $url) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Confirmez votre inscription à la newsletter'))
            ->greeting(__('Bonjour,'))
            ->line(__('Merci de votre intérêt ! Confirmez votre adresse pour recevoir nos nouvelles.'))
            ->action(__('Confirmer mon inscription'), $this->url)
            ->line(__('Si vous n’avez rien demandé, ignorez ce message : vous ne recevrez rien.'));
    }
}
