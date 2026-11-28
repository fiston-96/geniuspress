<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Platform\Checkout;
use App\Platform\Models\Order;
use App\Platform\Money;
use App\Platform\Payments\MonerailsGateway;
use App\Platform\Payments\PaymentManager;
use App\Platform\PlatformException;
use Illuminate\Http\Request;

/** Commande : récapitulatif, paiement (Mobile Money ou carte) et suivi. */
class OrderController extends CustomerController
{
    /** Toutes les commandes du client, de la plus récente à la plus ancienne. */
    public function index(Request $request)
    {
        $customer = $this->customer($request);

        return view('platform.customer.orders', [
            'orders' => $customer->orders()->with('items')->latest('id')->paginate(20),
        ]);
    }

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
            'countries' => $gateway instanceof MonerailsGateway && $order->status === 'pending' ? $this->countries($order, $gateway) : [],
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
            // « CD » ou « CD|USD » : pays du compte Mobile Money et devise choisie.
            'country' => ['nullable', 'string', 'regex:/^[A-Za-z]{2}(\|[A-Z]{3})?$/'],
            'operator' => ['required_if:method,mobile_money', 'nullable', 'string', 'max:30'],
            'phone' => ['required_if:method,mobile_money', 'nullable', 'string', 'max:30', 'regex:/^\+?[0-9 .-]{6,20}$/'],
        ]);
        if (! empty($data['country']) && str_contains($data['country'], '|')) {
            [$data['country'], $data['currency']] = explode('|', $data['country'], 2);
        }
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

    /** Code de paiement reçu par SMS (ou obtenu par code USSD), pour les opérateurs qui en demandent un. */
    public function authorizePayment(Request $request, Order $order, Checkout $checkout)
    {
        $this->own($request, $order);
        $otp = $request->validate(['otp' => ['required', 'string', 'regex:/^[0-9A-Za-z]{4,12}$/']])['otp'];
        $payment = $order->payments()->where('status', 'pending')->where('awaiting', 'otp')->latest('id')->first();
        if (! $payment) {
            return back()->withErrors(['payment' => __('Aucun code n’est attendu pour ce paiement.')]);
        }
        try {
            $checkout->authorize($payment, $otp);
            $checkout->confirm($payment);
        } catch (PlatformException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        }

        return redirect()->route('platform.customer.orders.show', $order)
            ->with('status', $payment->fresh()->status === 'succeeded' ? __('Paiement confirmé.') : __('Code transmis : le paiement est en cours de validation.'));
    }

    /**
     * Pays où payer par Mobile Money : montant dans la devise du pays et opérateurs ouverts.
     * Pays sans taux de change pour la devise de la commande : absents.
     *
     * @return array<string, array{country: string, name: string, currency: string, amount: string, operators: array<string, array{name: string, otp: bool}>, note: ?string}>
     */
    private function countries(Order $order, MonerailsGateway $gateway): array
    {
        // Clé « CD » ou « CD|USD » (pays et devise de paiement, quand le pays en a plusieurs).
        $out = [];
        foreach (array_keys(MonerailsGateway::COUNTRIES) as $code) {
            $operators = null;
            foreach (MonerailsGateway::currenciesFor($code) as $i => $currency) {
                $amount = Checkout::localAmount($order, $currency);
                if ($amount === null || ($currency === $order->currency && $amount !== $order->total)) {
                    continue;
                }
                // Opérateurs en direct : une requête par devise, gardée 10 minutes. Pays sans opérateur ouvert : retiré.
                $operators ??= $gateway->operatorsFor($code);
                if (! $operators) {
                    break;
                }
                $name = class_exists(\Locale::class) ? \Locale::getDisplayRegion('-'.$code, app()->getLocale()) : $code;
                $out[$i === 0 ? $code : $code.'|'.$currency] = [
                    'country' => $code, 'name' => $name ?: $code, 'currency' => $currency,
                    'amount' => Money::format($amount, $currency), 'operators' => $operators,
                    // L'opérateur prélève le solde de cette devise seulement, jamais un solde dans une autre devise.
                    'note' => match (true) {
                        $currency === 'USD' => __('Le paiement est prélevé sur votre solde en dollars (USD).'),
                        count(MonerailsGateway::currenciesFor($code)) > 1 => __('Le paiement est prélevé sur votre solde en :currency. Si votre argent est en dollars, choisissez « :country (USD) ».', ['currency' => $currency, 'country' => $name ?: $code]),
                        default => __('Le paiement est prélevé sur votre solde en :currency. Si votre argent est dans une autre devise, convertissez d’abord le montant en :currency dans l’application ou le menu de votre opérateur Mobile Money.', ['currency' => $currency]),
                    },
                ];
            }
        }
        uasort($out, fn ($a, $b) => strcoll($a['name'], $b['name']));

        return $out;
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
