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
}
