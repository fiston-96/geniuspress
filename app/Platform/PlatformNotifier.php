<?php

namespace App\Platform;

use App\Models\User;
use App\Notifications\Notice;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Envoi des notifications de la plateforme. Une notification qui échoue (serveur d'e-mails
 * indisponible…) est signalée dans le journal mais n'interrompt jamais l'action en cours :
 * un paiement ou une inscription ne doit pas échouer à cause d'un e-mail.
 */
class PlatformNotifier
{
    public static function send(?object $notifiable, Notice $notice): void
    {
        if (! $notifiable) {
            return;
        }
        try {
            $notifiable->notify($notice);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** Administrateurs de la plateforme (utilisateurs « admin » du site de l'exploitant). */
    public static function admins(Notice $notice): void
    {
        if (! config('geniuspress.platform.enabled')) {
            return;
        }
        try {
            Notification::send(User::query()->where('role', User::ROLE_ADMIN)->get(), $notice);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
