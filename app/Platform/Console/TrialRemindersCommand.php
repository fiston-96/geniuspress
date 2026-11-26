<?php

namespace App\Platform\Console;

use App\Hosting\Site;
use App\Platform\Notifications\TrialEndingSoon;
use App\Platform\PlatformHostingAccounts;
use App\Platform\PlatformNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Rappels de fin d'essai : 3 jours puis 1 jour avant la fin, une seule fois par étape.
 * Planifié chaque jour quand la plateforme et l'hébergement sont actifs.
 */
class TrialRemindersCommand extends Command
{
    protected $signature = 'geniuspress:trial-reminders';

    protected $description = 'Prévient les clients dont l’essai hébergé se termine bientôt';

    public const DAYS = [3, 1];

    public function handle(): int
    {
        $sent = 0;
        Site::query()->where('kind', 'trial')->where('status', Site::STATUS_ACTIVE)->whereNotNull('expires_at')
            ->where('expires_at', '>', now())->where('expires_at', '<=', now()->addDays(max(self::DAYS)))
            ->each(function (Site $site) use (&$sent) {
                $days = $site->daysLeft();
                $step = collect(self::DAYS)->sort()->first(fn ($d) => $days <= $d);
                $customer = PlatformHostingAccounts::customer($site->account_id);
                if ($step === null || ! $customer || ! Cache::add("trial-reminder:{$site->id}:{$step}", true, now()->addDays(15))) {
                    return;
                }
                PlatformNotifier::send($customer, new TrialEndingSoon($site, $days));
                $sent++;
            });

        $this->info(trans_choice('{0} Aucun rappel envoyé.|{1} 1 rappel envoyé.|[2,*] :count rappels envoyés.', $sent));

        return self::SUCCESS;
    }
}
