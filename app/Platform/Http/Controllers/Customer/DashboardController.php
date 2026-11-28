<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Hosting\Trials;
use App\Platform\PlatformAccounts;
use Illuminate\Http\Request;

class DashboardController extends CustomerController
{
    public function index(Request $request, PlatformAccounts $accounts)
    {
        $customer = $this->customer($request);

        return view('platform.customer.dashboard', [
            'customer' => $customer,
            'subscriptions' => $customer->subscriptions()->with('plan')->latest('id')->get()->filter->isActive(),
            'canTrial' => $accounts->canStartTrial($customer),
            // Site GeniusPress hébergé du client (essai ou offre payante), au cœur de son espace.
            'hosting' => (bool) config('hosting.enabled'),
            'site' => config('hosting.enabled') ? app(Trials::class)->siteFor($customer) : null,
            // Sites gratuits sans limite : tous les sites du client.
            'sites' => config('hosting.enabled') && config('hosting.unlimited') ? app(Trials::class)->sitesFor($customer) : null,
            'licenses' => $customer->licenses()->latest('id')->get(),
            'orders' => $customer->orders()->with('items')->latest('id')->limit(10)->get(),
            'domains' => $customer->domains()->orderBy('name')->get(),
            'newKey' => $request->session()->get('license_key'),
        ]);
    }

    /** Mon compte : coordonnées, pays et devise, SMS, clés de licence des sites. */
    public function account(Request $request)
    {
        $customer = $this->customer($request);

        return view('platform.customer.account', [
            'customer' => $customer,
            'licenses' => $customer->licenses()->latest('id')->get(),
        ]);
    }

    /** Crédits IA : grand livre et derniers appels. */
    public function credits(Request $request)
    {
        $customer = $this->customer($request);

        return view('platform.customer.credits', [
            'customer' => $customer,
            'transactions' => $customer->creditTransactions()->latest('id')->paginate(30),
            'usages' => $customer->aiUsages()->with('license')->latest('id')->limit(30)->get(),
        ]);
    }

    /** Pays du client : adapte les prix affichés et le paiement. */
    public function country(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'country' => ['required', 'string', 'size:2', fn ($attribute, $value, $fail) => \App\Platform\Countries::valid($value) ? null : $fail(__('Choisissez votre pays dans la liste.'))],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 .()-]{6,25}$/'],
        ]);
        $values = ['country' => strtoupper($data['country'])];
        if ($request->has('phone')) {
            $values['phone'] = ($data['phone'] ?? '') !== '' ? trim($data['phone']) : null;
        }
        $this->customer($request)->update($values);

        return back()->with('status', __('Pays enregistré : :country.', ['country' => \App\Platform\Countries::name($data['country'])]));
    }

    /** Recevoir (ou non) les SMS importants de la plateforme. */
    public function sms(\Illuminate\Http\Request $request)
    {
        $this->customer($request)->update(['sms_opt_in' => $request->boolean('sms_opt_in')]);

        return back()->with('status', $request->boolean('sms_opt_in') ? __('Vous recevrez les messages importants par SMS.') : __('Vous ne recevrez plus de SMS.'));
    }
}
