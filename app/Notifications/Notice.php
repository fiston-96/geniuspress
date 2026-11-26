<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification de GeniusPress : toujours dans la cloche (canal « database »), et par e-mail
 * quand mail() le demande. Les textes sont gardés en français avec leurs paramètres et traduits
 * à l'affichage : chacun lit ses notifications dans la langue de son interface.
 *
 * Une classe fille donne title(), body() ([texte source, paramètres]), url(), icon().
 */
abstract class Notice extends Notification
{
    use Queueable;

    public const ICONS = [
        'bell' => 'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-4-5.7V5a2 2 0 10-4 0v.3A6 6 0 006 11v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0',
        'mail' => 'M3 8l9 6 9-6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'shield' => 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z',
        'site' => 'M3 5h18v14H3zM3 9h18M7 7h.01',
        'payment' => 'M3 7h18v10H3zM3 11h18M7 15h3',
        'clock' => 'M12 7v5l3 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'sparkles' => 'M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8zM19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0',
        'newsletter' => 'M4 5h16v14H4zM8 9h8M8 13h8M8 17h4',
    ];

    /** @return array{0: string, 1: array<string, mixed>} */
    abstract public function title(): array;

    /** @return array{0: string, 1: array<string, mixed>}|null */
    public function body(): ?array
    {
        return null;
    }

    public function url(): ?string
    {
        return null;
    }

    public function icon(): string
    {
        return 'bell';
    }

    /** Envoyer aussi par e-mail ? (actions importantes : paiement, sécurité, fin d'essai…) */
    public function mail(object $notifiable): bool
    {
        return false;
    }

    /** Libellé du bouton de l'e-mail. */
    public function actionText(): string
    {
        return 'Voir le détail';
    }

    /**
     * Texte du SMS (clients de la plateforme, messages importants seulement), ou null.
     * Écrit dans la langue du client : Laravel envoie la notification dans sa langue préférée.
     */
    public function sms(object $notifiable): ?string
    {
        return null;
    }

    /** SMS court : « GeniusPress : » suivi du titre, dans la langue du client. */
    protected function smsFromTitle(): string
    {
        [$title, $params] = $this->title();

        return __('GeniusPress : :message', ['message' => __($title, $params)]);
    }

    public function via(object $notifiable): array
    {
        $channels = $this->mail($notifiable) && filled($notifiable->email ?? null) ? ['database', 'mail'] : ['database'];
        // SMS en dernier : une erreur du prestataire ne bloque ni la cloche ni l'e-mail.
        if (config('geniuspress.platform.enabled') && $notifiable instanceof \App\Platform\Models\Customer
            && \App\Platform\Messaging\Sms::enabled() && $this->sms($notifiable) !== null) {
            $channels[] = \App\Platform\Messaging\SmsChannel::class;
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        [$title, $titleParams] = $this->title();
        [$body, $bodyParams] = $this->body() ?? [null, []];

        return [
            'title' => $title, 'title_params' => $titleParams,
            'body' => $body, 'body_params' => $bodyParams,
            'url' => $this->url(), 'icon' => $this->icon(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        [$title, $titleParams] = $this->title();
        $mail = (new MailMessage)->subject(__($title, $titleParams))->greeting(__('Bonjour :name,', ['name' => $notifiable->name]));
        if ($body = $this->body()) {
            $mail->line(__($body[0], $body[1]));
        } else {
            $mail->line(__($title, $titleParams));
        }
        if ($url = $this->url()) {
            $mail->action(__($this->actionText()), $url);
        }

        return $mail;
    }

    // ─── Affichage (cloche et liste) ────────────────────────────────────

    public static function titleOf(DatabaseNotification $n): string
    {
        return __((string) ($n->data['title'] ?? ''), (array) ($n->data['title_params'] ?? []));
    }

    public static function bodyOf(DatabaseNotification $n): ?string
    {
        return isset($n->data['body']) ? __((string) $n->data['body'], (array) ($n->data['body_params'] ?? [])) : null;
    }

    public static function iconOf(DatabaseNotification $n): string
    {
        return self::ICONS[$n->data['icon'] ?? 'bell'] ?? self::ICONS['bell'];
    }
}
