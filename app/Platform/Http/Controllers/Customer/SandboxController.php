<?php

namespace App\Platform\Http\Controllers\Customer;

use App\Platform\Checkout;
use App\Platform\Models\Payment;
use App\Platform\Payments\PaymentManager;
use App\Platform\Payments\SandboxPaymentGateway;
use Illuminate\Http\Request;

/** Page du prestataire de test : le client choisit le résultat du paiement simulé. */
class SandboxController extends CustomerController
{
    public function show(Request $request, Payment $payment)
    {
        $this->guard($request, $payment);

        return view('platform.customer.sandbox', ['payment' => $payment->load('order')]);
    }

    public function complete(Request $request, Payment $payment, SandboxPaymentGateway $gateway, Checkout $checkout)
    {
        $this->guard($request, $payment);
        $gateway->simulate($payment, $request->input('result') === 'success');
        $checkout->confirm($payment);

        return redirect()->route('platform.customer.orders.show', $payment->order);
    }

    private function guard(Request $request, Payment $payment): void
    {
        abort_unless($payment->gateway === 'sandbox' && PaymentManager::sandboxAllowed(), 404);
        $this->own($request, $payment->order);
    }
}
