<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Hosting\HostingException;
use App\Hosting\SiteProvisioner;
use App\Hosting\Trials;
use App\Platform\Checkout;
use App\Platform\Domains\DomainService;
use App\Platform\Domains\ManagesDns;
use App\Platform\Models\Domain;
use App\Platform\PlatformException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Recherche, achat et renouvellement de noms de domaine ; branchement sur un site hébergé. */
class DomainController extends CustomerController
{
    public function index(Request $request, DomainService $domains, Trials $trials)
    {
        $query = trim((string) $request->query('q', ''));
        $results = null;
        $error = null;
        $contactFields = [];
        $maxRenew = 10;
        $customer = $this->customer($request);
        if ($query !== '') {
            try {
                $results = $domains->search(mb_substr($query, 0, 120));
            } catch (PlatformException $e) {
                $error = $e->getMessage();
            }
        }
        try {
            $contactFields = $domains->registrar()->contactFields();
            $maxRenew = $domains->registrar()->maxRenewYears();
        } catch (PlatformException) {
            // Vente de domaines indisponible : la recherche affiche déjà le message.
        }

        return view('platform.customer.domains', [
            'query' => $query,
            'results' => $results,
            'error' => $error,
            'customer' => $customer,
            'contactFields' => $contactFields,
            'contactLabels' => DomainService::contactLabels(),
            'maxRenew' => $maxRenew,
            'domains' => $customer->domains()->orderBy('name')->get(),
            'sites' => $this->canConnect($domains) ? $trials->sitesFor($customer)->filter->isLive()->values() : collect(),
        ]);
    }

    public function buy(Request $request, Checkout $checkout, DomainService $domains)
    {
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:190'],
            'years' => ['required', 'integer', 'min:1', 'max:10'],
        ]);
        $customer = $this->customer($request);
        try {
            // Coordonnées du titulaire exigées par le registre : enregistrées sur le compte du client.
            $fields = $domains->registrar()->contactFields();
            if ($fields) {
                $rules = [
                    'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[\d\s().-]{8,25}$/'],
                    'address' => ['required', 'string', 'max:190'],
                    'postal_code' => ['nullable', 'string', 'max:20'],
                    'city' => ['required', 'string', 'max:100'],
                    'country' => ['required', 'string', 'size:2', 'alpha'],
                ];
                $contact = $request->validate(array_intersect_key($rules, array_flip($fields)));
                if (isset($contact['country'])) {
                    $contact['country'] = strtoupper($contact['country']);
                }
                $customer->update($contact);
            }
            $order = $checkout->forDomain($customer, $data['domain'], (int) $data['years']);
        } catch (PlatformException $e) {
            return back()->withInput()->withErrors(['domain' => $e->getMessage()]);
        }

        return redirect()->route('platform.customer.orders.show', $order);
    }

    public function renew(Request $request, Domain $domain, Checkout $checkout)
    {
        $this->own($request, $domain);
        $years = (int) $request->validate(['years' => ['required', 'integer', 'min:1', 'max:10']])['years'];
        try {
            $order = $checkout->forDomainRenewal($this->customer($request), $domain, $years);
        } catch (PlatformException $e) {
            return back()->withErrors(['domain' => $e->getMessage()]);
        }

        return redirect()->route('platform.customer.orders.show', $order);
    }

    /** Branche le domaine sur un site hébergé du client : zone DNS (A vers le serveur) et adresse du site. */
    public function connect(Request $request, Domain $domain, DomainService $domains, Trials $trials, SiteProvisioner $provisioner)
    {
        $this->own($request, $domain);
        $customer = $this->customer($request);
        $slug = $request->validate(['site' => ['required', 'string', Rule::in($trials->sitesFor($customer)->pluck('slug')->all())]])['site'];
        $site = $trials->siteOf($customer, $slug);
        if (! $site || ! $site->isLive() || $domain->status !== 'active' || ! $this->canConnect($domains)) {
            return back()->withErrors(['domain' => __('Ce domaine ne peut pas être branché sur ce site pour le moment.')]);
        }

        try {
            $previous = $domain->site_slug ? $trials->siteOf($customer, $domain->site_slug) : null;
            foreach ([$domain->name, 'www.'.$domain->name] as $host) {
                if ($previous && $previous->id !== $site->id) {
                    $provisioner->removeHost($previous, $host);
                }
                $provisioner->addHost($site, $host);
            }
            /** @var ManagesDns $registrar */
            $registrar = $domains->registrar();
            DomainService::forCustomer(fn () => $registrar->pointTo($domain->name, (string) config('geniuspress.platform.server_ip')));
        } catch (PlatformException|HostingException $e) {
            return back()->withErrors(['domain' => $e->getMessage()]);
        }
        $domain->update(['site_slug' => $site->slug]);

        return back()->with('status', __(':domain est branché sur « :site ». Comptez de quelques minutes à quelques heures pour que l’adresse et son certificat HTTPS soient prêts partout.', ['domain' => $domain->name, 'site' => $site->name]));
    }

    public function disconnect(Request $request, Domain $domain, Trials $trials, SiteProvisioner $provisioner)
    {
        $this->own($request, $domain);
        if ($domain->site_slug && ($site = $trials->siteOf($this->customer($request), $domain->site_slug))) {
            foreach ([$domain->name, 'www.'.$domain->name] as $host) {
                $provisioner->removeHost($site, $host);
            }
        }
        $domain->update(['site_slug' => null]);

        return back()->with('status', __('Le domaine :domain n’est plus branché sur votre site.', ['domain' => $domain->name]));
    }

    /** Code de transfert envoyé par e-mail au titulaire (le domaine appartient au client). */
    public function authCode(Request $request, Domain $domain, DomainService $domains)
    {
        $this->own($request, $domain);
        try {
            $registrar = $domains->registrar();
            if (! $registrar instanceof ManagesDns || $domain->status !== 'active') {
                throw new PlatformException(__('Le code de transfert n’est pas disponible pour ce domaine. Contactez-nous.'));
            }
            DomainService::forCustomer(fn () => $registrar->sendAuthCode($domain->name));
        } catch (PlatformException $e) {
            return back()->withErrors(['domain' => $e->getMessage()]);
        }

        return back()->with('status', __('Le code de transfert de :domain sera envoyé dans quelques minutes à l’adresse e-mail du titulaire.', ['domain' => $domain->name]));
    }

    private function canConnect(DomainService $domains): bool
    {
        if (! config('hosting.enabled') || ! filter_var(config('geniuspress.platform.server_ip'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        try {
            return $domains->registrar() instanceof ManagesDns;
        } catch (PlatformException) {
            return false;
        }
    }
}
