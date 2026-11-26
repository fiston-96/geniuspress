<?php

namespace App\Platform\Console;

use App\Platform\Referrals\Referrals;
use Illuminate\Console\Command;

/** Verse sur le solde d’affiliation les commissions arrivées à échéance (planifié chaque heure). */
class ReferralsReleaseCommand extends Command
{
    protected $signature = 'geniuspress:referrals-release';

    protected $description = 'Rend disponibles les commissions d’affiliation arrivées à échéance';

    public function handle(Referrals $referrals): int
    {
        $count = $referrals->releaseDue();
        $this->info(trans_choice('{0} Aucune commission libérée.|{1} 1 commission libérée.|[2,*] :count commissions libérées.', $count));

        return self::SUCCESS;
    }
}
