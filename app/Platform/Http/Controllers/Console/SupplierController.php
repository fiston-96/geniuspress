<?php

namespace App\Platform\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Platform\Models\Plan;
use App\Platform\Models\Service;
use App\Platform\Models\TldPrice;
use App\Platform\Money;
use App\Platform\PlatformException;
use App\Platform\Suppliers\Lws\LwsCatalog;
use App\Platform\Suppliers\Lws\LwsClient;
use App\Platform\Suppliers\ServiceDesk;
use App\Platform\Suppliers\SupplierPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

/**
 * Fournisseurs (LWS) : connexion à l'API (clé chiffrée, mode test), solde prépayé,
 * marge et taux de change, coûts et marges des extensions et des offres partenaires,
 * file de livraison des services à créer ou renouveler à la main.
 */
class SupplierController extends Controller
{
    public function index(LwsClient $client, SupplierPricing $pricing)
    {
        return view('platform.admin.suppliers', [
            'client' => $client,
            'keyHint' => Setting::read('platform_lws_key_hint'),
            'registrar' => \App\Platform\Domains\DomainService::active(),
            'registrarOpen' => $this->registrarOpen(),
            'lowBalance' => (string) Setting::read('platform_lws_low_balance', '20'),
            'pricing' => $pricing,
            'currencies' => array_values(array_diff(Money::CURRENCIES, ['EUR'])),
            'tlds' => TldPrice::query()->orderBy('tld')->get(),
            'plans' => Plan::query()->whereIn('kind', Plan::SUPPLIER_KINDS)->orderBy('kind')->orderBy('sort')->get(),
            'queue' => Service::query()->with(['customer', 'plan', 'order'])->whereNotNull('pending_action')->orderBy('updated_at')->get(),
            'recent' => Service::query()->with(['customer', 'plan'])->whereNull('pending_action')->latest('updated_at')->limit(20)->get(),
        ]);
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'lws_login' => ['nullable', 'string', 'regex:/^\d{1,12}$/'],
            'lws_key' => ['nullable', 'string', 'max:200'],
            'lws_key_remove' => ['sometimes', 'boolean'],
            'lws_test' => ['sometimes', 'boolean'],
            'low_balance' => ['required', 'string', 'regex:/^\d{1,6}([.,]\d{1,2})?$/'],
            'markup' => ['required', 'string', 'regex:/^\d{1,3}([.,]\d{1,2})?$/'],
            'rates' => ['array'],
            'rates.*' => ['nullable', 'string', 'regex:/^\d{1,9}([.,]\d{1,6})?$/'],
            'registrar' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Platform\Domains\DomainService::REGISTRARS))],
        ]);
        $values = [
            'platform_domain_registrar' => $data['registrar'] ?? \App\Platform\Domains\DomainService::active(),
            'platform_lws_login' => $data['lws_login'] ?? null,
            'platform_lws_test' => $request->boolean('lws_test'),
            'platform_lws_low_balance' => str_replace(',', '.', $data['low_balance']),
            'platform_supplier_markup' => str_replace(',', '.', $data['markup']),
            'platform_supplier_rates' => collect($data['rates'] ?? [])
                ->filter(fn ($rate, $currency) => in_array($currency, Money::CURRENCIES, true) && $rate !== null && $rate !== '')
                ->map(fn ($rate) => str_replace(',', '.', $rate))->all(),
        ];
        // Clé jamais réaffichée : un champ vide garde la clé enregistrée.
        $key = trim((string) ($data['lws_key'] ?? ''));
        if ($key !== '') {
            $values['platform_lws_key'] = Crypt::encryptString($key);
            $values['platform_lws_key_hint'] = '…'.substr($key, -4);
        } elseif ($request->boolean('lws_key_remove')) {
            $values['platform_lws_key'] = null;
            $values['platform_lws_key_hint'] = null;
        }
        Setting::write($values);

        return back()->with('status', __('Réglages des fournisseurs enregistrés.'));
    }

    /** Essai de connexion : lecture du solde prépayé. */
    public function test(LwsCatalog $catalog)
    {
        try {
            $balance = $catalog->balance();
        } catch (PlatformException $e) {
            return back()->withErrors(['lws' => $e->getMessage()]);
        }

        return back()->with('status', __('Connexion à LWS réussie. Solde prépayé : :amount.', ['amount' => Money::format($balance, 'EUR')]));
    }

    public function sync(LwsCatalog $catalog)
    {
        try {
            $done = $catalog->sync();
        } catch (PlatformException $e) {
            return back()->withErrors(['lws' => $e->getMessage()]);
        }

        return back()->with('status', __('Coûts LWS mis à jour : :tlds extension(s), :plans offre(s).', $done));
    }

    /** Disponibilité d'un domaine chez LWS (lecture seule, fonctionne aussi en mode test). */
    public function checkDomain(Request $request, \App\Platform\Suppliers\Lws\LwsRegistrar $lws)
    {
        $domain = strtolower(trim((string) $request->validate(['domain' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9-]+(\.[a-z0-9-]+)+$/i']])['domain']));
        try {
            $free = $lws->check([$domain])[$domain] ?? false;
        } catch (PlatformException $e) {
            return back()->withErrors(['lws' => $e->getMessage()]);
        }

        return back()->with('status', $free ? __('LWS : :domain est disponible.', ['domain' => $domain]) : __('LWS : :domain n’est pas disponible.', ['domain' => $domain]));
    }

    /** Échéances réelles des domaines et hébergements LWS (sinon fait chaque nuit). */
    public function syncDomains(\App\Platform\Suppliers\Lws\LwsAccounts $accounts)
    {
        try {
            $done = $accounts->syncAll();
        } catch (PlatformException $e) {
            return back()->withErrors(['lws' => $e->getMessage()]);
        }

        return back()->with('status', __('Suivi LWS : :synced élément(s) à jour, :missing achat(s) introuvable(s).', $done));
    }

    /** La vente de domaines est-elle ouverte aux clients avec le registraire choisi ? Sinon, pourquoi. */
    private function registrarOpen(): ?string
    {
        try {
            app(\App\Platform\Domains\DomainService::class)->registrar();

            return null;
        } catch (PlatformException) {
            return \App\Platform\Domains\DomainService::active() === 'lws'
                ? __('Fermée : connexion LWS absente, ou mode test (les achats seraient simulés alors que le client paie).')
                : __('Fermée : choisissez LWS pour vendre des domaines.');
        }
    }

    /** Prix conseillés (coût + marge) appliqués aux extensions dont le coût est connu. */
    public function applyTldPrices(SupplierPricing $pricing)
    {
        $count = 0;
        foreach (TldPrice::query()->whereNotNull('cost_register')->get() as $tld) {
            $register = $pricing->suggested($tld->cost_register, $tld->cost_currency, $tld->currency);
            $renew = $pricing->suggested($tld->cost_renew, $tld->cost_currency, $tld->currency);
            if ($register !== null && $renew !== null) {
                $tld->update(['register_price' => $register, 'renew_price' => $renew]);
                $count++;
            }
        }

        return back()->with('status', trans_choice('{0} Aucune extension mise à jour : réglez les taux de change.|{1} Prix conseillé appliqué à 1 extension.|[2,*] Prix conseillés appliqués à :count extensions.', $count));
    }

    public function done(Request $request, Service $service, ServiceDesk $desk)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date'],
        ]);
        $desk->markDone($service, ($data['note'] ?? '') !== '' ? $data['note'] : null, isset($data['expires_at']) ? Carbon::parse($data['expires_at'])->endOfDay() : null);

        return back()->with('status', __('Service marqué comme livré : le client est prévenu.'));
    }

    public function fail(Request $request, Service $service, ServiceDesk $desk)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        $desk->markFailed($service, $data['reason']);

        return back()->with('status', __('Service marqué en échec : le client est prévenu. Pensez au remboursement.'));
    }
}
