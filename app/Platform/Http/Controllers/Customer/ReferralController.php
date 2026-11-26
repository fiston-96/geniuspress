<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Platform\Money;
use App\Platform\PlatformException;
use App\Platform\Referrals\Referrals;
use App\Platform\Referrals\Wallet;
use Illuminate\Http\Request;

/** Affiliation dans l'espace client : lien, clients apportés, commissions, solde, retraits. */
class ReferralController extends CustomerController
{
    public function index(Request $request, Referrals $referrals, Wallet $wallet)
    {
        $customer = $this->customer($request);

        return view('platform.customer.referrals', [
            'customer' => $customer,
            'link' => $referrals->link($customer),
            'rate' => Referrals::rate(),
            'holdDays' => Referrals::holdDays(),
            'referrals' => $customer->referrals()->latest('referred_at')->limit(50)->get(['id', 'name', 'referred_at']),
            'commissions' => $customer->referralCommissions()->with('referred:id,name')->latest('id')->limit(50)->get(),
            'balances' => $wallet->balances($customer),
            'transactions' => $customer->walletTransactions()->latest('id')->limit(30)->get(),
            'payouts' => $customer->payouts()->latest('id')->limit(20)->get(),
            'operators' => Referrals::PAYOUT_OPERATORS,
        ]);
    }

    public function payout(Request $request, Referrals $referrals, Wallet $wallet)
    {
        $customer = $this->customer($request);
        $balances = $wallet->balances($customer);
        $data = $request->validate([
            'currency' => ['required', 'in:'.implode(',', array_keys($balances) ?: ['XXX'])],
            'amount' => ['required', 'string', 'max:20', 'regex:/^\d+([.,]\d{1,2})?$/'],
            'mobile_operator' => ['required', 'in:'.implode(',', array_keys(Referrals::PAYOUT_OPERATORS))],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9 .-]{6,20}$/'],
        ], [], ['amount' => __('montant'), 'mobile_operator' => __('opérateur'), 'phone' => __('numéro')]);

        $amount = Money::parse($data['amount'], $data['currency']);
        if ($amount > ($balances[$data['currency']] ?? 0)) {
            return back()->withErrors(['amount' => __('Ce montant dépasse votre solde.')])->withInput();
        }
        try {
            $referrals->requestPayout($customer, $amount, $data['currency'], $data['mobile_operator'], preg_replace('/[^0-9+]/', '', $data['phone']));
        } catch (PlatformException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return back()->with('status', __('Demande de retrait envoyée. Nous faisons le transfert sous quelques jours ouvrés.'));
    }
}
