<?php

namespace App\Platform;

use App\Notifications\Notice;
use App\Platform\Models\Operator;
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

    /** Exploitants de la plateforme (cloche de la console /console). */
    public static function admins(Notice $notice): void
    {
        if (! config('geniuspress.platform.enabled')) {
            return;
        }
        try {
            Notification::send(Operator::query()->where('status', 'active')->get(), $notice);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
