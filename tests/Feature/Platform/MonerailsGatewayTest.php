<?php

namespace Tests\Feature\Platform;

use App\Platform\Models\Order;
use App\Platform\Models\Payment;
use App\Platform\Payments\PaymentManager;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;

/** Monerails (Mobile Money) : réponses simulées par Http::fake, aucun appel réel, clés factices. */
class MonerailsGatewayTest extends PlatformTestCase
{
    private const SECRET = 'whsec_secret_de_test';

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config([
            'geniuspress.platform.payment_gateway' => 'monerails',
            'geniuspress.platform.monerails.base_url' => 'https://monerails.test/v1',
            'geniuspress.platform.monerails.key' => 'mr_test_cle_factice',
            'geniuspress.platform.monerails.webhook_secret' => self::SECRET,
        ]);
    }

    /** Commande d'un pack de crédits en francs congolais (FC 25 000 = 2 500 000 centimes). */
    private function order(int $price = 2_500_000): array
    {
        $customer = $this->customer(['name' => 'Amani Kahindo', 'country' => 'CD']);
        $pack = $this->plan(['slug' => 'credits-cdf', 'kind' => 'ai_credits', 'period' => 'once', 'trial_days' => 0, 'price' => $price, 'currency' => 'CDF', 'ai_credits' => 5_000_000]);
        $this->actingAs($customer, 'customer')->post(route('platform.customer.plans.buy', $pack));

        return [$customer, Order::sole()];
    }

    private function collection(Payment $payment, string $status, array $extra = []): array
    {
        return $extra + [
            'id' => 'col_123', 'object' => 'collection', 'livemode' => false, 'status' => $status,
            'amount' => $payment->amount, 'currency' => $payment->currency, 'operator' => 'MPESA',
            'client_reference' => $payment->reference, 'next_action' => null,
        ];
    }

    private function webhook(array $event, ?string $signature = null, ?int $t = null)
    {
        $body = json_encode($event);
        $t ??= time();
        $signature ??= 't='.$t.',v1='.hash_hmac('sha256', $t.'.'.$body, self::SECRET);

        return $this->call('POST', '/api/platform/v1/payments/monerails/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_MONERAIL_SIGNATURE' => $signature], $body);
    }

    public function test_mobile_money_collection_is_started_then_confirmed(): void
    {
        [$customer, $order] = $this->order();
        Http::fake(['monerails.test/v1/collections' => Http::response([
            'id' => 'col_123', 'status' => 'processing', 'client_reference' => 'x',
            'next_action' => ['type' => 'customer_approval', 'message' => 'Validez sur votre téléphone'],
        ], 202)]);

        // Monerails n'encaisse pas les cartes : seul le Mobile Money est proposé.
        $this->get(route('platform.customer.orders.show', $order))->assertOk()->assertSee('M-Pesa')->assertDontSee('Carte bancaire');
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'card'])->assertSessionHasErrors('payment');

        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'MPESA', 'phone' => '+243 81 234 5678'])
            ->assertRedirect(route('platform.customer.orders.show', $order))
            ->assertSessionHas('status', 'Une demande de paiement a été envoyée au +243812345678. Validez-la sur votre téléphone.');

        $payment = Payment::where('status', 'pending')->sole();
        $this->assertSame(['monerails', 'col_123'], [$payment->gateway, $payment->gateway_reference]);
        Http::assertSent(fn (HttpRequest $r) => $r->method() === 'POST' && $r->url() === 'https://monerails.test/v1/collections'
            && $r->hasHeader('Authorization', 'Bearer mr_test_cle_factice')
            && $r->hasHeader('Idempotency-Key', $payment->reference)
            && $r['amount'] === 2_500_000 && $r['currency'] === 'CDF' && $r['operator'] === 'MPESA'
            && $r['phone'] === '+243812345678' && $r['customer'] === ['name' => 'Amani Kahindo'] && $r['country'] === 'CD'
            && $r['client_reference'] === $payment->reference && $r['metadata'] === ['order' => $order->number]);

        // Toujours en cours chez Monerails : rien n'est livré.
        Http::fake(['monerails.test/v1/collections/col_123' => Http::sequence()
            ->push($this->collection($payment, 'processing'))
            ->push($this->collection($payment, 'succeeded'))]);
        $this->post(route('platform.customer.orders.check', $order));
        $this->assertSame('pending', $order->fresh()->status);

        $this->post(route('platform.customer.orders.check', $order));
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(5_000_000, $customer->fresh()->ai_balance);
    }

    public function test_amount_mismatch_and_failures_are_not_delivered(): void
    {
        [$customer, $order] = $this->order();
        Http::fake(['monerails.test/v1/collections' => Http::response(['id' => 'col_1', 'status' => 'processing', 'next_action' => null], 202)]);
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'AIRTEL', 'phone' => '+243990000000']);
        $payment = Payment::sole();

        // Montant encaissé différent de la commande : refusé.
        Http::fake(['monerails.test/v1/collections/col_1' => Http::response($this->collection($payment, 'succeeded', ['amount' => 100]))]);
        $this->post(route('platform.customer.orders.check', $order));
        $this->assertSame(['pending', 'failed'], [$order->fresh()->status, $payment->fresh()->status]);
        $this->assertSame(0, $customer->fresh()->ai_balance);
    }

    public function test_errors_are_shown_in_french_and_amounts_are_checked(): void
    {
        [, $order] = $this->order();
        Http::fake(['monerails.test/v1/collections' => Http::response(['error' => ['type' => 'invalid_request_error', 'code' => 'phone_invalid', 'message' => 'Invalid phone']], 400)]);
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'ORANGE', 'phone' => '+243810000000'])
            ->assertSessionHasErrors(['payment' => 'Ce numéro Mobile Money est invalide. Indiquez-le avec l’indicatif du pays.']);
        $this->assertSame('failed', Payment::sole()->status);

        // Offre en USD sans taux de change réglé : impossible à convertir, aucun appel.
        Http::fake();
        $usd = app(\App\Platform\Checkout::class)->forPlan($order->customer, $this->plan(['slug' => 'usd', 'price' => 1000, 'currency' => 'USD']));
        $this->post(route('platform.customer.orders.pay', $usd), ['method' => 'mobile_money', 'operator' => 'MPESA', 'phone' => '+243810000000'])
            ->assertSessionHasErrors('payment');
        // Francs congolais : centimes par multiples de 100.
        $odd = app(\App\Platform\Checkout::class)->forPlan($order->customer, $this->plan(['slug' => 'cdf-impair', 'price' => 150050, 'currency' => 'CDF']));
        $this->post(route('platform.customer.orders.pay', $odd), ['method' => 'mobile_money', 'operator' => 'MPESA', 'phone' => '+243810000000'])
            ->assertSessionHasErrors(['payment' => 'Le montant doit être un nombre entier dans la devise CDF.']);
        Http::assertNothingSent();
    }

    public function test_operator_code_is_asked_then_sent_to_monerails(): void
    {
        [$customer, $order] = $this->order();
        Http::fake(['monerails.test/v1/collections' => Http::response(['id' => 'col_2', 'status' => 'requires_otp', 'next_action' => ['type' => 'otp', 'message' => 'Code']], 201)]);
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'ORANGE', 'phone' => '+243810000000'])
            ->assertSessionHasNoErrors()->assertSessionHas('status');
        $payment = Payment::sole();
        $this->assertSame(['pending', 'otp'], [$payment->status, $payment->awaiting]);
        $this->get(route('platform.customer.orders.show', $order))->assertOk()->assertSee('Code de paiement');

        // Mauvais code : message en français, le client peut réessayer ; le bon code est transmis ensuite.
        Http::fake([
            'monerails.test/v1/collections/col_2/authorize' => Http::sequence()
                ->push(['error' => ['code' => 'otp_invalid']], 402)
                ->push(['id' => 'col_2', 'status' => 'processing']),
            'monerails.test/v1/collections/col_2' => Http::response($this->collection($payment, 'succeeded', ['id' => 'col_2'])),
        ]);
        $this->post(route('platform.customer.orders.authorize', $order), ['otp' => '000000'])
            ->assertSessionHasErrors(['payment' => 'Code incorrect. Vérifiez-le et réessayez.']);

        $this->post(route('platform.customer.orders.authorize', $order), ['otp' => '482913'])->assertSessionHas('status', 'Paiement confirmé.');
        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/collections/col_2/authorize') && $r['otp'] === '482913');
        $this->assertSame(['paid', null], [$order->fresh()->status, $payment->fresh()->awaiting]);
        $this->assertSame(5_000_000, $customer->fresh()->ai_balance);
    }

    public function test_usd_order_is_paid_in_the_local_currency_of_the_customer(): void
    {
        // Taux réglés dans la console : 1 € = 1,08 $ = 3 100 FC = 655,957 F CFA.
        \App\Models\Setting::write(['platform_supplier_rates' => ['USD' => '1.08', 'CDF' => '3100', 'XOF' => '655.957']]);
        $customer = $this->customer(['name' => 'Awa Ndiaye', 'country' => 'SN']);
        $plan = $this->plan(['slug' => 'pro-usd', 'kind' => 'ai_credits', 'period' => 'once', 'trial_days' => 0, 'price' => 1000, 'currency' => 'USD', 'ai_credits' => 1_000_000]);
        $order = app(\App\Platform\Checkout::class)->forPlan($customer, $plan);
        Http::fake([
            'monerails.test/v1/operators*' => Http::response(['object' => 'list', 'data' => [
                ['object' => 'operator', 'code' => 'WAVE', 'name' => 'Wave', 'country' => 'SN', 'currency' => 'XOF', 'authorization' => ['PIN']],
                ['object' => 'operator', 'code' => 'ORANGE', 'name' => 'Orange Money', 'country' => 'CI', 'currency' => 'XOF', 'authorization' => ['OTP']],
            ], 'has_more' => false]),
            'monerails.test/v1/collections' => Http::response(['id' => 'col_xof', 'status' => 'processing'], 202),
        ]);

        // Page de paiement : pays du client présélectionné, montant converti, pays sans taux absents.
        $this->actingAs($customer, 'customer')->get(route('platform.customer.orders.show', $order))->assertOk()
            ->assertSee("6\u{202F}074")->assertSee('converti depuis', false)->assertDontSee('(GNF)')
            ->assertSee('Le paiement est prélevé sur votre solde en XOF', false)->assertDontSee('(USD)');

        // Opérateur d'un autre pays : refusé.
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'country' => 'SN', 'operator' => 'ORANGE', 'phone' => '+221770000000'])
            ->assertSessionHasErrors(['payment' => 'Choisissez votre opérateur Mobile Money.']);

        // 10 $ → 9,259 € → 6 073,7 F CFA, arrondis à 6 074 F CFA (unités entières, au-dessus).
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'country' => 'SN', 'operator' => 'WAVE', 'phone' => '+221770000000'])
            ->assertSessionHasNoErrors();
        $payment = Payment::query()->latest('id')->first();
        $this->assertSame([6074, 'XOF', 1000, 'USD', 'SN'], [$payment->amount, $payment->currency, $payment->original_amount, $payment->original_currency, $payment->country]);
        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/collections') && $r['amount'] === 6074 && $r['currency'] === 'XOF' && $r['country'] === 'SN');

        // Encaissé au bon montant : commande en USD payée et livrée.
        Http::fake(['monerails.test/v1/collections/col_xof' => Http::response($this->collection($payment, 'succeeded', ['id' => 'col_xof']))]);
        $this->post(route('platform.customer.orders.check', $order));
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(1_000_000, $customer->fresh()->ai_balance);

        // RDC : 10 $ → 28 703,70 FC, arrondis à 28 704 FC (en centimes, multiple de 100).
        $this->assertSame(2_870_400, \App\Platform\Checkout::localAmount($order, 'CDF'));
        $this->assertNull(\App\Platform\Checkout::localAmount($order, 'GNF'), 'Sans taux : pas de paiement dans cette devise.');
    }

    public function test_dollars_can_be_enabled_country_by_country(): void
    {
        \App\Models\Setting::write(['platform_supplier_rates' => ['USD' => '1.08', 'CDF' => '3100', 'XOF' => '655.957']]);
        $customer = $this->customer(['name' => 'Amani Kahindo', 'country' => 'CD']);
        $plan = $this->plan(['slug' => 'pro-usd', 'kind' => 'ai_credits', 'period' => 'once', 'trial_days' => 0, 'price' => 1000, 'currency' => 'USD', 'ai_credits' => 1_000_000]);
        $order = app(\App\Platform\Checkout::class)->forPlan($customer, $plan);

        // Dollars non activés : la devise du pays, même si le client demande l'USD.
        $this->assertSame(['CDF'], \App\Platform\Payments\MonerailsGateway::currenciesFor('CD'));
        Http::fake(['monerails.test/v1/collections' => Http::response(['id' => 'col_usd', 'status' => 'processing'], 202)]);
        $this->actingAs($customer, 'customer')->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'country' => 'CD|USD', 'operator' => 'MPESA', 'phone' => '+243810000000']);
        $this->assertSame('CDF', Payment::query()->latest('id')->first()->currency);

        // Activés pour la RD Congo et le Sénégal dans la console.
        $operator = \App\Platform\Models\Operator::create(['name' => 'Exploitant', 'email' => 'ops@test.local', 'password' => 'secret-password-1']);
        $this->actingAs($operator, 'operator')->put(route('console.payments.settings'), ['gateway' => 'monerails', 'usd' => ['CD', 'SN']])->assertSessionHas('status');
        $this->assertSame(['CDF', 'USD'], \App\Platform\Payments\MonerailsGateway::currenciesFor('CD'));
        $this->assertSame(['XOF', 'USD'], \App\Platform\Payments\MonerailsGateway::currenciesFor('SN'));
        $this->assertSame(['XAF'], \App\Platform\Payments\MonerailsGateway::currenciesFor('CM'));

        $order = app(\App\Platform\Checkout::class)->forPlan($customer, $plan);
        $this->actingAs($customer, 'customer')->get(route('platform.customer.orders.show', $order))->assertOk()
            ->assertSee('value="CD|USD"', false)->assertSee('value="SN|USD"', false)->assertDontSee('value="CM|USD"', false)
            ->assertSee('Si votre argent est en dollars, choisissez', false);

        // Payé directement en dollars : aucun taux appliqué.
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'country' => 'CD|USD', 'operator' => 'MPESA', 'phone' => '+243810000000'])
            ->assertSessionHasNoErrors();
        $payment = Payment::query()->latest('id')->first();
        $this->assertSame([1000, 'USD', null, 'CD'], [$payment->amount, $payment->currency, $payment->original_amount, $payment->country]);
        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/collections') && $r['currency'] === 'USD' && $r['amount'] === 1000);
    }

    public function test_console_stores_monerails_keys_encrypted_and_tests_the_connection(): void
    {
        config(['geniuspress.platform.monerails.key' => null, 'geniuspress.platform.monerails.webhook_secret' => null, 'geniuspress.platform.payment_gateway' => 'sandbox']);
        $operator = \App\Platform\Models\Operator::create(['name' => 'Exploitant', 'email' => 'ops@test.local', 'password' => 'secret-password-1']);
        $this->actingAs($operator, 'operator')->get(route('console.payments.index'))->assertOk()
            ->assertSee('/api/platform/v1/payments/monerails/webhook')->assertSee('Taux de change manquants');

        $this->put(route('console.payments.settings'), ['gateway' => 'monerails', 'monerails_key' => 'cle-sans-prefixe'])->assertSessionHasErrors('monerails_key');
        $this->put(route('console.payments.settings'), ['gateway' => 'monerails', 'monerails_key' => 'mr_test_cle_console_1234', 'monerails_webhook_secret' => 'whsec_secret_console_5678'])
            ->assertSessionHas('status');
        $this->assertNotSame('mr_test_cle_console_1234', \App\Models\Setting::read('platform_monerails_key'));
        $this->assertSame(['mr_test_cle_console_1234', 'whsec_secret_console_5678', 'monerails', 'test'], [
            \App\Platform\Payments\MonerailsGateway::key(), \App\Platform\Payments\MonerailsGateway::webhookSecret(), PaymentManager::active(), \App\Platform\Payments\MonerailsGateway::mode(),
        ]);
        $this->assertArrayNotHasKey('platform_monerails_key', \App\Models\Setting::publicValues());
        $this->get(route('console.payments.index'))->assertDontSee('mr_test_cle_console_1234')->assertDontSee('whsec_secret_console_5678');

        Http::fake(['monerails.test/v1/balance' => Http::response(['object' => 'balance', 'livemode' => false, 'available' => [['currency' => 'XAF', 'amount' => 150000]], 'cards' => null])]);
        $this->post(route('console.payments.test'))->assertSessionHas('status');
        Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('Authorization', 'Bearer mr_test_cle_console_1234'));
    }

    public function test_webhook_signature_timestamp_and_confirmation(): void
    {
        [$customer, $order] = $this->order();
        Http::fake(['monerails.test/v1/collections' => Http::response(['id' => 'col_9', 'status' => 'processing'], 202)]);
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'MPESA', 'phone' => '+243810000000']);
        $payment = Payment::sole();
        $event = ['id' => 'evt_1', 'object' => 'event', 'type' => 'collection.succeeded', 'data' => ['object' => $this->collection($payment, 'succeeded', ['id' => 'col_9'])]];

        $this->webhook($event, 't='.time().',v1=faux')->assertStatus(400);
        $this->webhook($event, t: time() - 600)->assertStatus(400); // horodatage trop ancien
        $this->assertSame('pending', $order->fresh()->status);

        // Signature valide : l'état est quand même redemandé à Monerails avant de livrer.
        Http::fake(['monerails.test/v1/collections/col_9' => Http::response($this->collection($payment, 'succeeded', ['id' => 'col_9']))]);
        $this->webhook($event)->assertOk()->assertJson(['status' => 'succeeded']);
        Http::assertSent(fn (HttpRequest $r) => $r->method() === 'GET' && str_ends_with($r->url(), '/collections/col_9'));
        $this->assertSame('paid', $order->fresh()->status);

        // Notification reçue deux fois : livrée une seule fois.
        $this->webhook($event)->assertOk();
        $this->assertSame(5_000_000, $customer->fresh()->ai_balance);
    }

    public function test_test_keys_are_refused_in_production_and_missing_keys_disable_payment(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->expectException(\App\Platform\PlatformException::class);
        try {
            app(PaymentManager::class)->gateway();
        } finally {
            config(['geniuspress.platform.monerails.key' => 'mr_live_cle_factice']);
            $this->assertSame('monerails', app(PaymentManager::class)->gateway()->name());
            config(['geniuspress.platform.monerails.key' => null]);
            $this->app->detectEnvironment(fn () => 'testing');
        }
    }
}
