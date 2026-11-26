<?php

namespace App\Newsletter;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Envoi des campagnes en cours, au rythme du réglage newsletter_per_minute. Lancé juste après
 * le clic sur « Envoyer » (après la réponse, ou par la file d'attente si elle existe) et
 * pendant 15 minutes au plus ; la commande planifiée geniuspress:newsletter-send reprend le reste.
 */
class SendNewsletter implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 960;

    public function handle(Newsletter $newsletter): void
    {
        @set_time_limit(0);
        $deadline = time() + 15 * 60;
        while (true) {
            $started = time();
            $newsletter->sendBatch();
            if ($newsletter->pending() === 0 || time() + 60 > $deadline) {
                return;
            }
            sleep(max(1, 60 - (time() - $started)));
        }
    }
}
