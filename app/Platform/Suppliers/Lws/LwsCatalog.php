<?php

namespace App\Platform\Suppliers\Lws;

use App\Platform\Models\Plan;
use App\Platform\Models\TldPrice;

/**
 * Prix d'achat chez LWS (tarifs publics en euros HT) et solde du compte prépayé.
 * La synchronisation met à jour les coûts des extensions et des offres « lws » déjà
 * créées dans la console : les prix de vente restent ceux de l'exploitant.
 */
class LwsCatalog
{
    public function __construct(private LwsClient $client) {}

    /** Solde prépayé, en centimes d'euro. */
    public function balance(): int
    {
        return self::cents($this->client->get('/contact/0/credit'));
    }

    /**
     * Extensions vendues par LWS.
     *
     * @return array<string, array{register: int, renew: int, transfer: int}> « fr » → coûts en centimes d'euro
     */
    public function tlds(): array
    {
        $out = [];
        foreach ((array) $this->client->get('/domain/0/tlds') as $row) {
            $tld = ltrim(strtolower((string) ($row['tld'] ?? '')), '.');
            if ($tld === '') {
                continue;
            }
            $offer = (float) ($row['price_purchase_offer'] ?? 0);
            $out[$tld] = [
                'register' => self::cents($offer > 0 ? $offer : ($row['price_purchase'] ?? 0)),
                'renew' => self::cents($row['price_renew'] ?? 0),
                'transfer' => self::cents($row['price_transfer'] ?? 0),
            ];
        }
        ksort($out);

        return $out;
    }

    /**
     * Offres d'hébergement LWS et leur prix mensuel.
     *
     * @return array<string, int> « LWS Perso » → centimes d'euro par mois
     */
    public function packages(): array
    {
        return array_map(fn ($price) => self::cents($price), (array) $this->client->get('/hosting/0/priceall'));
    }

    /**
     * Coûts mis à jour dans les extensions et les offres « lws ».
     *
     * @return array{tlds: int, plans: int}
     */
    public function sync(): array
    {
        $tlds = $this->tlds();
        $updatedTlds = 0;
        foreach (TldPrice::query()->get() as $price) {
            if (isset($tlds[$price->tld])) {
                $price->update(['cost_register' => $tlds[$price->tld]['register'], 'cost_renew' => $tlds[$price->tld]['renew'], 'cost_currency' => 'EUR']);
                $updatedTlds++;
            }
        }

        $packages = $this->packages();
        $updatedPlans = 0;
        foreach (Plan::query()->where('provider', 'lws')->whereNotNull('provider_ref')->get() as $plan) {
            if (isset($packages[$plan->provider_ref])) {
                // Prix LWS mensuel ; une offre annuelle coûte douze mois.
                $plan->update(['cost' => $packages[$plan->provider_ref] * ($plan->period === 'year' ? 12 : 1), 'cost_currency' => 'EUR']);
                $updatedPlans++;
            }
        }

        return ['tlds' => $updatedTlds, 'plans' => $updatedPlans];
    }

    public static function cents(mixed $euros): int
    {
        return (int) round((float) $euros * 100);
    }
}
