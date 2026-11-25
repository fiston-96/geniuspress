<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Platform\Checkout;
use App\Platform\Domains\DomainService;
use App\Platform\Models\Domain;
use App\Platform\PlatformException;
use Illuminate\Http\Request;

/** Recherche, achat et renouvellement de noms de domaine. */
class DomainController extends CustomerController
{
    public function index(Request $request, DomainService $domains)
    {
        $query = trim((string) $request->query('q', ''));
        $results = null;
        $error = null;
        if ($query !== '') {
            try {
                $results = $domains->search(mb_substr($query, 0, 120));
            } catch (PlatformException $e) {
                $error = $e->getMessage();
            }
        }

        return view('platform.customer.domains', [
            'query' => $query,
            'results' => $results,
            'error' => $error,
            'domains' => $this->customer($request)->domains()->orderBy('name')->get(),
        ]);
    }

    public function buy(Request $request, Checkout $checkout)
    {
        $data = $request->validate([
            'domain' => ['required', 'string', 'max:190'],
            'years' => ['required', 'integer', 'min:1', 'max:10'],
        ]);
        try {
            $order = $checkout->forDomain($this->customer($request), $data['domain'], (int) $data['years']);
        } catch (PlatformException $e) {
            return back()->withErrors(['domain' => $e->getMessage()]);
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
}
