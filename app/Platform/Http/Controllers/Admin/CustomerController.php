<?php

namespace App\Platform\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Platform\Ai\CreditAccount;
use App\Platform\LicenseKeys;
use App\Platform\Models\Customer;
use App\Platform\Models\License;
use App\Platform\Money;
use Illuminate\Http\Request;

/** Comptes clients : fiche, suspension, crédits IA, licences. */
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        return view('platform.admin.customers.index', [
            'search' => $search,
            'customers' => Customer::query()
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->withCount(['orders' => fn ($q) => $q->where('status', 'paid'), 'licenses' => fn ($q) => $q->where('status', 'active')])
                ->latest('id')->paginate(30)->withQueryString(),
        ]);
    }

    public function show(Customer $customer)
    {
        return view('platform.admin.customers.show', [
            'customer' => $customer,
            'subscriptions' => $customer->subscriptions()->with('plan')->latest('id')->get(),
            'orders' => $customer->orders()->latest('id')->limit(30)->get(),
            'licenses' => $customer->licenses()->latest('id')->get(),
            'domains' => $customer->domains()->orderBy('name')->get(),
            'templates' => $customer->templateIds(),
            'transactions' => $customer->creditTransactions()->latest('id')->limit(30)->get(),
        ]);
    }

    public function status(Request $request, Customer $customer)
    {
        $data = $request->validate(['status' => ['required', 'in:active,suspended']]);
        $customer->forceFill(['status' => $data['status']])->save();

        return back()->with('status', $data['status'] === 'active' ? __('Compte réactivé.') : __('Compte suspendu : ses licences ne fonctionnent plus.'));
    }

    /** Ajustement manuel des crédits IA (en dollars, positif ou négatif), avec motif obligatoire. */
    public function credits(Request $request, Customer $customer, CreditAccount $credits)
    {
        $data = $request->validate([
            'amount' => ['required', 'string', 'regex:/^-?\d{1,6}([.,]\d{1,6})?$/'],
            'reason' => ['required', 'string', 'min:3', 'max:200'],
        ]);
        $micros = Money::usdToMicros($data['amount']);
        if ($micros === 0) {
            return back()->withErrors(['amount' => __('Indiquez un montant différent de zéro.')]);
        }
        $credits->adjust($customer, $micros, $data['reason'], $request->user()->id);

        return back()->with('status', __('Crédits IA ajustés de :amount.', ['amount' => Money::micros($micros)]));
    }

    public function revokeLicense(License $license, LicenseKeys $keys)
    {
        $keys->revoke($license);

        return back()->with('status', __('Clé de licence révoquée.'));
    }

    /** Plafond mensuel de dépenses IA d'une licence (vide : sans plafond). */
    public function licenseCap(Request $request, License $license)
    {
        $data = $request->validate(['monthly_cap' => ['nullable', 'string', 'regex:/^\d{1,6}([.,]\d{1,6})?$/']]);
        $license->update(['monthly_cap' => ($data['monthly_cap'] ?? '') === '' ? null : Money::usdToMicros($data['monthly_cap'])]);

        return back()->with('status', __('Plafond enregistré.'));
    }
}
