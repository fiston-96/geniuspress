<?php

namespace App\Platform\Console;

use App\Platform\Models\Domain;
use App\Platform\Models\Service;
use App\Platform\Notifications\ExpiringSoon;
use App\Platform\PlatformNotifier;
use Illuminate\Console\Command;

/**
 * Rappels d'échéance (chaque jour) : domaines à 30, 7 et 1 jour(s), services à 15 et 3 jours.
 * Un seul rappel par palier (reminded_days) ; un renouvellement remet le compteur à zéro.
 * Ce qui a dépassé son échéance passe à l'état « expiré ».
 */
class RenewalRemindersCommand extends Command
{
    protected $signature = 'geniuspress:renewal-reminders';

    protected $description = 'Rappels d’échéance des domaines et services des clients';

    public const DOMAIN_STEPS = [30, 7, 1];

    public const SERVICE_STEPS = [15, 3];

    public function handle(): int
    {
        $sent = 0;
        $domains = Domain::query()->with('customer')->where('status', 'active')->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(max(self::DOMAIN_STEPS)))->get();
        foreach ($domains as $domain) {
            $sent += $this->remind($domain, self::DOMAIN_STEPS);
        }
        $services = Service::query()->with(['customer', 'plan'])->where('status', 'active')->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(max(self::SERVICE_STEPS)))->get();
        foreach ($services as $service) {
            $sent += $this->remind($service, self::SERVICE_STEPS);
        }

        $expired = Domain::query()->where('status', 'active')->where('expires_at', '<', now())->update(['status' => 'expired'])
            + Service::query()->where('status', 'active')->where('expires_at', '<', now())->update(['status' => 'expired']);

        $this->info(__(':sent rappel(s) envoyé(s), :expired échéance(s) dépassée(s).', ['sent' => $sent, 'expired' => $expired]));

        return self::SUCCESS;
    }

    /** @param  list<int>  $steps */
    private function remind(Domain|Service $item, array $steps): int
    {
        if ($item->expires_at->isPast()) {
            return 0;
        }
        $days = (int) ceil(now()->diffInMinutes($item->expires_at) / 1440);
        // Plus petit palier atteint (ex. 7 jours restants → palier 7).
        $step = collect($steps)->sort()->first(fn ($s) => $days <= $s);
        if ($step === null || ($item->reminded_days !== null && $item->reminded_days <= $step)) {
            return 0;
        }
        $item->update(['reminded_days' => $step]);
        PlatformNotifier::send($item->customer, new ExpiringSoon($item, max(1, $days)));

        return 1;
    }
}
