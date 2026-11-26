<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Platform\Checkout;
use App\Platform\Models\Order;
use App\Platform\Payments\PaymentManager;
use App\Platform\PlatformException;
use Illuminate\Http\Request;

/** Commande : récapitulatif, paiement (Mobile Money ou carte) et suivi. */
class OrderController extends CustomerController
{
    public function show(Request $request, Order $order, PaymentManager $payments)
    {
        $this->own($request, $order);
        try {
            $gateway = $payments->gateway();
        } catch (PlatformException $e) {
            $gateway = null;
        }

        return view('platform.customer.order', [
            'order' => $order->load('items'),
            'payment' => $order->payments()->latest('id')->first(),
            'gateway' => $gateway,
            'walletBalance' => app(\App\Platform\Referrals\Wallet::class)->balance($order->customer, $order->currency),
        ]);
    }

    public function pay(Request $request, Order $order, Checkout $checkout)
    {
        $this->own($request, $order);

        // Solde d’affiliation : pas de prestataire, la commande est payée tout de suite.
        if ($request->input('method') === 'wallet') {
            try {
                $checkout->payWithWallet($order);
            } catch (PlatformException $e) {
                return back()->withErrors(['payment' => $e->getMessage()]);
            }

            return redirect()->route('platform.customer.orders.show', $order)->with('status', __('Commande payée avec votre solde d’affiliation.'));
        }

        $data = $request->validate([
            'method' => ['required', 'in:mobile_money,card'],
            'operator' => ['required_if:method,mobile_money', 'nullable', 'string', 'max:30'],
            'phone' => ['required_if:method,mobile_money', 'nullable', 'string', 'max:30', 'regex:/^\+?[0-9 .-]{6,20}$/'],
        ]);
        if (! empty($data['phone'])) {
            $data['phone'] = preg_replace('/[^0-9+]/', '', $data['phone']);
        }

        try {
            ['start' => $start] = $checkout->pay($order, $data);
        } catch (PlatformException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }
        if ($start->redirectUrl) {
            return redirect()->away($start->redirectUrl)->with('status', $start->message);
        }

        return redirect()->route('platform.customer.orders.show', $order)->with('status', $start->message);
    }

    /** Le client revient du paiement : on redemande l'état au prestataire. */
    public function check(Request $request, Order $order, Checkout $checkout)
    {
        $this->own($request, $order);
        $payment = $order->payments()->where('status', 'pending')->latest('id')->first();
        if ($payment) {
            try {
                $checkout->confirm($payment);
            } catch (PlatformException $e) {
                return back()->withErrors(['payment' => $e->getMessage()]);
            }
        }

        return redirect()->route('platform.customer.orders.show', $order);
    }
}
