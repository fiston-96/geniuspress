<?php

namespace App\Console\Commands;

use App\Newsletter\Newsletter;
use Illuminate\Console\Command;

/** Envoie un lot de la newsletter (planifié chaque minute ; reprend un envoi interrompu). */
class NewsletterSendCommand extends Command
{
    protected $signature = 'geniuspress:newsletter-send';

    protected $description = 'Envoie le lot suivant des campagnes de newsletter en cours';

    public function handle(Newsletter $newsletter): int
    {
        $sent = $newsletter->pending() ? $newsletter->sendBatch() : 0;
        $this->info(trans_choice('{0} Aucun e-mail envoyé.|{1} 1 e-mail envoyé.|[2,*] :count e-mails envoyés.', $sent));

        return self::SUCCESS;
    }
}
