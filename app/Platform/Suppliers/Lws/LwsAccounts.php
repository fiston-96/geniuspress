<?php

namespace App\Platform\Suppliers\Lws;

use App\Platform\Models\Domain;
use App\Platform\Models\Service;
use App\Platform\Notifications\SupplierPurchaseMissing;
use App\Platform\PlatformNotifier;
use Illuminate\Support\Carbon;

/**
 * Suivi chez LWS des domaines et hébergements vendus (les achats y sont traités en différé) :
 * échéance réelle lue chez LWS, renouvellement automatique LWS coupé (le client renouvelle et
 * paie dans GeniusPress, sinon LWS débiterait le compte prépayé sans paiement du client),
 * alerte aux exploitants si un achat payé n'apparaît toujours pas sur le compte après deux jours.
 */
class LwsAccounts
{
    /** Délai après lequel un achat absent du compte LWS est signalé. */
    public const MISSING_AFTER_HOURS = 48;

    public function __construct(private LwsClient $client) {}

    /** @return array{synced: int, missing: int} */
    public function syncAll(): array
    {
        $done = ['synced' => 0, 'missing' => 0];
        $domains = Domain::query()->where('registrar', 'lws')->whereIn('status', ['active'])->get();
        foreach ($domains as $domain) {
            $this->track($domain, $domain->name, $domain->registered_at, $done);
        }
        $services = Service::query()->where('provider', 'lws')->where('kind', 'web_hosting')->where('status', 'active')->whereNotNull('domain')->get();
        foreach ($services as $service) {
            $this->track($service, (string) $service->domain, $service->delivered_at, $done);
        }

        return $done;
    }

    /** @param  array{synced: int, missing: int}  $done */
    private function track(Domain|Service $item, string $name, ?Carbon $since, array &$done): void
    {
        try {
            $info = (array) $this->client->get('/hosting/'.rawurlencode($name));
        } catch (LwsException $e) {
            // 314 : pas (encore) sur le compte. Achat différé en cours, ou achat perdu au-delà du délai.
            // Alerte une fois par semaine au plus pour le même achat.
            if ($e->getCode() === 314 && $since && $since->lt(now()->subHours(self::MISSING_AFTER_HOURS)) && ! $item->synced_at
                && \Illuminate\Support\Facades\Cache::add('lws-missing:'.$name, true, now()->addDays(7))) {
                PlatformNotifier::admins(new SupplierPurchaseMissing($name));
                $done['missing']++;
            }

            return;
        }
        $values = ['synced_at' => now()];
        if (is_string($info['date_expiration'] ?? null) && strtotime($info['date_expiration'])) {
            $values['expires_at'] = Carbon::parse($info['date_expiration'])->endOfDay();
        }
        // Première synchronisation : renouvellement automatique coupé chez LWS (hébergement et domaine).
        if (! $item->synced_at) {
            try {
                $this->client->put('/hosting/'.rawurlencode($name).'/autorenew', ['enable' => false]);
            } catch (LwsException $e) {
                report($e);
            }
        }
        $item->update($values);
        $done['synced']++;
    }
}
