<?php

namespace App\Platform\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Platform\Models\Payment;
use App\Platform\Money;
use App\Platform\Payments\MonerailsGateway;
use App\Platform\Payments\PaymentManager;
use App\Platform\PlatformException;
use App\Platform\Suppliers\SupplierPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

/**
 * Paiements : prestataire actif, clés Monerails (chiffrées, jamais réaffichées), adresse de
 * notification à déclarer chez Monerails, essai de connexion (soldes), devises sans taux de change.
 */
class PaymentController extends Controller
{
    public function index(SupplierPricing $pricing)
    {
        $currencies = array_unique([config('geniuspress.platform.currency'), ...\App\Platform\Models\Plan::query()->active()->distinct()->pluck('currency')->all()]);

        return view('platform.admin.payments', [
            'active' => PaymentManager::active(),
            'mode' => MonerailsGateway::mode(),
            'usable' => MonerailsGateway::usable(),
            'keyHint' => Setting::read('platform_monerails_key_hint'),
            'secretHint' => Setting::read('platform_monerails_webhook_secret_hint'),
            'webhookUrl' => route('platform.api.webhook', 'monerails'),
            // Devises de vente sans taux vers les devises Mobile Money : ces commandes ne pourraient pas être payées.
            'missingRates' => array_values(array_filter($currencies, fn ($c) => ! in_array($c, MonerailsGateway::CURRENCIES, true) && $pricing->rate($c) === null)),
            'missingLocal' => array_values(array_filter(MonerailsGateway::CURRENCIES, fn ($c) => $pricing->rate($c) === null)),
            'recent' => Payment::query()->with('order')->latest('id')->limit(15)->get(),
        ]);
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'gateway' => ['required', Rule::in(array_keys(PaymentManager::GATEWAYS))],
            'monerails_key' => ['nullable', 'string', 'max:200', 'regex:/^mr_(test|live)_[A-Za-z0-9_-]{8,190}$/'],
            'monerails_webhook_secret' => ['nullable', 'string', 'max:200', 'regex:/^whsec_[A-Za-z0-9_-]{8,190}$/'],
        ], [
            'monerails_key.regex' => __('La clé Monerails commence par « mr_live_ » (production) ou « mr_test_ » (test).'),
            'monerails_webhook_secret.regex' => __('Le secret de notification commence par « whsec_ ».'),
        ]);
        $values = ['platform_payment_gateway' => $data['gateway']];
        foreach (['monerails_key' => 'platform_monerails_key', 'monerails_webhook_secret' => 'platform_monerails_webhook_secret'] as $field => $setting) {
            $secret = trim((string) ($data[$field] ?? ''));
            if ($secret !== '') {
                $values[$setting] = Crypt::encryptString($secret);
                $values[$setting.'_hint'] = substr($secret, 0, 8).'…'.substr($secret, -4);
            } elseif ($request->boolean($field.'_remove')) {
                $values[$setting] = null;
                $values[$setting.'_hint'] = null;
            }
        }
        Setting::write($values);

        return back()->with('status', __('Réglages des paiements enregistrés.'));
    }

    /** Essai de connexion : lecture des soldes Monerails. */
    public function test(MonerailsGateway $gateway)
    {
        try {
            $balances = $gateway->balance();
        } catch (PlatformException $e) {
            return back()->withErrors(['monerails' => $e->getMessage()]);
        }
        $list = collect($balances)->map(fn ($amount, $currency) => Money::format($amount, $currency))->implode(', ');

        return back()->with('status', __('Connexion à Monerails réussie (:mode). Soldes : :balances.', [
            'mode' => MonerailsGateway::mode() === 'live' ? __('production') : __('test'),
            'balances' => $list !== '' ? $list : __('aucun'),
        ]));
    }
}
