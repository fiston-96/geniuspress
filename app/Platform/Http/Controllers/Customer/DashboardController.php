<?php

namespace App\Platform\Http\Controllers\Customer;

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
