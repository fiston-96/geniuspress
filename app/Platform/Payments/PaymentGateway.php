<?php

namespace App\Platform\Payments;

use App\Platform\Models\Payment;
use Illuminate\Http\Request;

/**
 * Prestataire de paiement (Mobile Money et cartes). Une implémentation par
 * prestataire, choisie par GP_PAYMENT_GATEWAY ; ses clés sont dans le .env.
 *
 * La référence du paiement ($payment->reference) est créée par la plateforme et
 * transmise au prestataire : c'est elle qui relie la notification à la commande.
 */
interface PaymentGateway
{
    /** Identifiant court (« sandbox », « flutterwave »…), enregistré avec chaque paiement. */
    public function name(): string;

    /** Moyens proposés : « mobile_money », « card ». */
    public function methods(): array;

    /** Opérateurs Mobile Money proposés (code => nom affiché). */
    public function operators(): array;

    /**
     * Démarre le paiement auprès du prestataire.
     *
     * @param  array{phone?: string, operator?: string, return_url: string}  $details
     *
     * @throws \App\Platform\PlatformException refus du prestataire (message affichable)
     */
    public function start(Payment $payment, array $details): PaymentStart;

    /**
     * État du paiement redemandé au prestataire : « pending », « succeeded » ou « failed ».
     * Une implémentation réelle compare aussi le montant et la devise encaissés avec
     * $payment->amount et $payment->currency, et renvoie « failed » s'ils diffèrent.
     */
    public function verify(Payment $payment): string;

    /**
     * Référence du paiement concerné par une notification du prestataire, après
     * vérification de sa signature ; null si la notification n'est pas authentique.
     */
    public function webhookReference(Request $request): ?string;
}
