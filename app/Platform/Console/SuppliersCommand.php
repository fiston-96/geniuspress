<?php

namespace App\Platform\Console;

use App\Models\Setting;
use App\Platform\Money;
use App\Platform\Notifications\SupplierBalanceLow;
use App\Platform\PlatformException;
use App\Platform\PlatformNotifier;
use App\Platform\Suppliers\Lws\LwsCatalog;
use App\Platform\Suppliers\Lws\LwsClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Fournisseurs de la plateforme (LWS) :
 *
 *   php artisan geniuspress:suppliers balance   solde prépayé ; alerte aux exploitants sous le seuil (une fois par jour)
 *   php artisan geniuspress:suppliers sync      coûts d'achat des extensions et des offres « lws »
 */
class SuppliersCommand extends Command
{
    protected $signature = 'geniuspress:suppliers {action : balance ou sync}';

    protected $description = 'Solde et prix d’achat chez les fournisseurs (LWS)';

    public function handle(LwsClient $client, LwsCatalog $catalog): int
    {
        if (! $client->configured()) {
            $this->info(__('La connexion à LWS n’est pas configurée (console → Fournisseurs).'));

            return self::SUCCESS;
        }
        try {
            return match ($this->argument('action')) {
                'balance' => $this->balance($catalog),
                'sync' => $this->sync($catalog),
                default => $this->invalid(),
            };
        } catch (PlatformException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function balance(LwsCatalog $catalog): int
    {
        $balance = $catalog->balance();
        $this->info(__('Solde LWS : :amount', ['amount' => Money::format($balance, 'EUR')]));
        $threshold = Money::parse((string) Setting::read('platform_lws_low_balance', '20'), 'EUR');
        if ($balance < $threshold && Cache::add('platform-lws-low-balance', true, now()->addDay())) {
            PlatformNotifier::admins(new SupplierBalanceLow('lws', $balance, 'EUR'));
        }

        return self::SUCCESS;
    }

    private function sync(LwsCatalog $catalog): int
    {
        $done = $catalog->sync();
        $this->info(__('Coûts LWS mis à jour : :tlds extension(s), :plans offre(s).', $done));

        return self::SUCCESS;
    }

    private function invalid(): int
    {
        $this->error(__('Action inconnue. Actions : balance, sync.'));

        return self::FAILURE;
    }
}
