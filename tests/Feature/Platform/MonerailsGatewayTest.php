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

        // Validation par code SMS : pas encore prise en charge, message clair.
        Http::fake(['monerails.test/v1/collections' => Http::response(['id' => 'col_2', 'status' => 'requires_otp', 'next_action' => ['type' => 'otp']], 201)]);
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'ORANGE', 'phone' => '+243810000000'])
            ->assertSessionHasErrors('payment');

        // Offre en USD : Monerails n'encaisse pas cette devise en RDC, aucun appel.
        Http::fake();
        $usd = app(\App\Platform\Checkout::class)->forPlan($order->customer, $this->plan(['slug' => 'usd', 'price' => 1000, 'currency' => 'USD']));
        $this->post(route('platform.customer.orders.pay', $usd), ['method' => 'mobile_money', 'operator' => 'MPESA', 'phone' => '+243810000000'])
            ->assertSessionHasErrors('payment');
        // Francs congolais : centimes par multiples de 100.
        $odd = app(\App\Platform\Checkout::class)->forPlan($order->customer, $this->plan(['slug' => 'cdf-impair', 'price' => 150050, 'currency' => 'CDF']));
        $this->post(route('platform.customer.orders.pay', $odd), ['method' => 'mobile_money', 'operator' => 'MPESA', 'phone' => '+243810000000'])
            ->assertSessionHasErrors(['payment' => 'Le montant doit être un nombre entier de francs congolais.']);
        Http::assertNothingSent();
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
