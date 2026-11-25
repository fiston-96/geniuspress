<?php

namespace App\Platform\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Platform\Checkout;
use App\Platform\Models\Payment;
use App\Platform\Payments\PaymentManager;
use App\Platform\PlatformException;
use Illuminate\Http\Request;

/**
 * Notification d'un prestataire de paiement. La signature est vérifiée par la
 * passerelle, puis l'état est redemandé au prestataire : le contenu de la
 * notification ne suffit jamais à valider un paiement.
 */
class WebhookController extends Controller
{
    public function __invoke(Request $request, string $gateway, PaymentManager $payments, Checkout $checkout)
    {
        try {
            $driver = $payments->gateway($gateway);
        } catch (PlatformException) {
            abort(404);
        }
        $reference = $driver->webhookReference($request);
        if ($reference === null) {
            return response()->json(['error' => 'invalid_signature'], 400);
        }
        $payment = Payment::query()->where('reference', $reference)->where('gateway', $driver->name())->first();
        if (! $payment) {
            return response()->json(['error' => 'unknown_payment'], 404);
        }
        $checkout->confirm($payment);

        return response()->json(['status' => $payment->fresh()->status]);
    }
}
