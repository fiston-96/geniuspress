<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Platform\Checkout;
use App\Platform\Models\Plan;
use App\Platform\Models\Subscription;
use App\Platform\Models\TemplatePrice;
use App\Platform\PlatformAccounts;
use App\Platform\PlatformException;
use App\Templates\Catalog;
use Illuminate\Http\Request;
use Throwable;

/** Offres, packs de crédits IA et modèles de sites payants. */
class ShopController extends CustomerController
{
    public function plans(Request $request)
    {
        $customer = $this->customer($request);

        return view('platform.customer.plans', [
            'plans' => Plan::query()->active()->orderBy('sort')->orderBy('price')->get()->groupBy('kind'),
            'services' => $customer->services()->with('plan')->whereIn('status', ['pending', 'active', 'failed'])->latest('id')->get(),
            'domainNames' => $customer->domains()->where('status', 'active')->orderBy('name')->pluck('name'),
        ]);
    }

    public function buyPlan(Request $request, Plan $plan, Checkout $checkout)
    {
        $options = $request->validate([
            'domain' => ['nullable', 'string', 'max:190'],
            'request' => ['nullable', 'string', 'max:1000'],
        ]);
        try {
            $order = $checkout->forPlan($this->customer($request), $plan, $options);
        } catch (PlatformException $e) {
            return back()->withErrors(['plan' => $e->getMessage()]);
        }

        return redirect()->route('platform.customer.orders.show', $order);
    }

    public function templates(Request $request, Catalog $catalog)
    {
        $names = [];
        try {
            foreach ($catalog->index()['templates'] as $t) {
                $names[$t['id']] = $t + ['demo' => $catalog->demoUrl($t['id'])];
            }
        } catch (Throwable) {
            // Catalogue injoignable : les identifiants suffisent.
        }

        return view('platform.customer.templates', [
            'prices' => TemplatePrice::query()->where('active', true)->orderBy('template_id')->get(),
            'catalog' => $names,
            'owned' => $this->customer($request)->templateIds(),
            'lang' => app()->getLocale() === 'en' ? 'en' : 'fr',
        ]);
    }

    public function buyTemplate(Request $request, string $template, Checkout $checkout, Catalog $catalog)
    {
        $lang = app()->getLocale() === 'en' ? 'en' : 'fr';
        try {
            $name = $catalog->find($template)['name'][$lang] ?? $template;
        } catch (Throwable) {
            $name = $template;
        }
        try {
            $order = $checkout->forTemplate($this->customer($request), $template, $name);
        } catch (PlatformException $e) {
            return back()->withErrors(['template' => $e->getMessage()]);
        }

        return redirect()->route('platform.customer.orders.show', $order);
    }

    public function cancel(Request $request, Subscription $subscription, PlatformAccounts $accounts)
    {
        $this->own($request, $subscription);
        $accounts->cancel($subscription);

        return back()->with('status', __('Abonnement résilié : il reste actif jusqu’à la fin de la période payée.'));
    }
}
