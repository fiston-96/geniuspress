<?php

namespace Tests\Feature\Platform;

use App\Platform\Models\Customer;
use App\Platform\Models\Domain;
use App\Platform\Models\Entitlement;
use App\Platform\Models\Order;
use App\Platform\Models\Payment;
use App\Platform\Models\Plan;
use App\Platform\Models\TemplatePrice;
use App\Platform\Models\TldPrice;
use App\Platform\OrderFulfiller;
use App\Platform\Payments\PaymentManager;

/** Espace client : inscription, achat, paiement simulé (Mobile Money, carte), livraison. */
class CheckoutTest extends PlatformTestCase
{
    /** Paie une commande avec le prestataire de test et simule le résultat. */
    private function payWithSandbox(Customer $customer, Order $order, bool $success = true, string $method = 'mobile_money')
    {
        $this->actingAs($customer, 'customer')
            ->post(route('platform.customer.orders.pay', $order), ['method' => $method, 'operator' => 'mtn', 'phone' => '+243 81 000 0000'])
            ->assertRedirect();
        $payment = $order->payments()->latest('id')->firstOrFail();

        return $this->post(route('platform.sandbox.complete', $payment), ['result' => $success ? 'success' : 'failure']);
    }

    public function test_registration_login_and_separate_guard(): void
    {
        $this->get('/compte')->assertRedirect(route('platform.login'));
        $this->post('/compte/inscription', ['name' => 'Amani', 'email' => 'Amani@Test.local', 'password' => 'court', 'password_confirmation' => 'court'])
            ->assertSessionHasErrors('password');
        $this->post('/compte/inscription', ['name' => 'Amani', 'email' => 'Amani@Test.local', 'phone' => '+243810000000', 'country' => 'cd', 'password' => 'mot-de-passe-2026', 'password_confirmation' => 'mot-de-passe-2026'])
            ->assertRedirect(route('platform.customer.dashboard'));
        $customer = Customer::sole();
        $this->assertSame(['amani@test.local', 'CD'], [$customer->email, $customer->country]);
        $this->assertAuthenticatedAs($customer, 'customer');
        $this->assertGuest('web'); // un client n'est pas un utilisateur du site
        $this->get('/compte')->assertOk()->assertSee('Bonjour Amani');
        // Un client n'ouvre pas l'administration (chaque vraie requête repart du garde par défaut).
        \Illuminate\Support\Facades\Auth::shouldUse('web');
        $this->get('/admin')->assertRedirect(route('login'));

        $this->post('/compte/deconnexion');
        $this->post('/compte/connexion', ['email' => 'amani@test.local', 'password' => 'faux'])->assertSessionHasErrors('email');
        $this->post('/compte/connexion', ['email' => 'amani@test.local', 'password' => 'mot-de-passe-2026'])->assertRedirect(route('platform.customer.dashboard'));

        // Compte suspendu : connexion refusée.
        $this->post('/compte/deconnexion');
        $customer->forceFill(['status' => 'suspended'])->save();
        $this->post('/compte/connexion', ['email' => 'amani@test.local', 'password' => 'mot-de-passe-2026'])->assertSessionHasErrors('email');
    }

    public function test_hosting_plan_purchase_with_mobile_money(): void
    {
        $customer = $this->customer();
        $plan = $this->plan(['ai_credits' => 2_000_000]);

        $this->actingAs($customer, 'customer')->get(route('platform.customer.plans'))->assertOk()->assertSee('Essentiel');
        $this->post(route('platform.customer.plans.buy', $plan))->assertRedirect();
        $order = Order::sole();
        $this->assertSame(['pending', 1000, 'USD'], [$order->status, $order->total, $order->currency]);

        // Le paiement ouvre la page du prestataire (ici : la page de test).
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'inconnu', 'phone' => '+243810000000'])
            ->assertSessionHasErrors('payment');
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'orange', 'phone' => '+243 81 000 0000'])
            ->assertRedirect(route('platform.sandbox.show', Payment::latest('id')->first()));
        $payment = Payment::latest('id')->first();
        $this->assertSame(['mobile_money', 'orange', '+243810000000', 'pending'], [$payment->method, $payment->operator, $payment->phone, $payment->status]);
        $this->get(route('platform.sandbox.show', $payment))->assertOk()->assertSee('Simuler un paiement réussi');

        // Retour sans paiement confirmé : rien n'est livré.
        $this->post(route('platform.customer.orders.check', $order))->assertRedirect();
        $this->assertSame('pending', $order->fresh()->status);

        $this->post(route('platform.sandbox.complete', $payment), ['result' => 'success'])->assertRedirect(route('platform.customer.orders.show', $order));
        $this->assertSame('paid', $order->fresh()->status);
        $subscription = $customer->subscriptions()->sole();
        $this->assertTrue($subscription->isActive());
        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->current_period_end->between(now()->addMonth()->subMinute(), now()->addMonth()->addMinute()));
        $this->assertSame(2_000_000, $customer->fresh()->ai_balance);

        // Livraison idempotente : un second « succès » ne double rien.
        app(OrderFulfiller::class)->paid($payment->fresh());
        $this->assertSame(2_000_000, $customer->fresh()->ai_balance);
        $this->assertSame(1, $customer->subscriptions()->count());

        // Renouvellement : la période est prolongée depuis son échéance.
        $this->post(route('platform.customer.plans.buy', $plan));
        $this->payWithSandbox($customer, Order::latest('id')->first(), method: 'card');
        $this->assertTrue($subscription->fresh()->current_period_end->gt(now()->addMonths(2)->subDay()));
        $this->assertSame(1, $customer->subscriptions()->count());
    }

    public function test_failed_payment_and_other_customers_orders(): void
    {
        $customer = $this->customer();
        $pack = $this->plan(['slug' => 'credits-10', 'kind' => 'ai_credits', 'period' => 'once', 'trial_days' => 0, 'ai_credits' => 10_000_000, 'price' => 1000]);
        $this->actingAs($customer, 'customer')->post(route('platform.customer.plans.buy', $pack));
        $order = Order::sole();

        $this->payWithSandbox($customer, $order, success: false);
        $this->assertSame(['pending', 'failed'], [$order->fresh()->status, $order->payments()->sole()->status]);
        $this->assertSame(0, $customer->fresh()->ai_balance);
        $this->get(route('platform.customer.orders.show', $order))->assertOk()->assertSee('Le dernier paiement a échoué');

        $this->payWithSandbox($customer, $order);
        $this->assertSame(10_000_000, $customer->fresh()->ai_balance);
        $this->assertSame('purchase', $customer->creditTransactions()->sole()->kind);
        $this->assertSame(0, $customer->subscriptions()->count(), 'Un pack de crédits n’est pas un abonnement.');

        // Un autre client ne voit ni la commande ni la page de paiement.
        $other = $this->customer();
        $this->actingAs($other, 'customer')->get(route('platform.customer.orders.show', $order))->assertNotFound();
        $this->get(route('platform.sandbox.show', $order->payments()->first()))->assertNotFound();
    }

    public function test_sandbox_payments_are_refused_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        $this->assertFalse(PaymentManager::sandboxAllowed());
        config(['geniuspress.platform.sandbox' => 'true']); // choix explicite de l'exploitant
        $this->assertTrue(PaymentManager::sandboxAllowed());
        $this->app->detectEnvironment(fn () => 'testing');

        // Simulation refusée : aucun paiement ne démarre et la page de test n'existe pas.
        $customer = $this->customer();
        $this->actingAs($customer, 'customer')->post(route('platform.customer.plans.buy', $this->plan()));
        $order = Order::sole();
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'card'])->assertRedirect();
        config(['geniuspress.platform.sandbox' => 'false']);
        $this->get(route('platform.sandbox.show', Payment::sole()))->assertNotFound();
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'card'])->assertSessionHasErrors('payment');
        $this->assertSame(1, Payment::count());
        $this->get(route('platform.customer.orders.show', $order))->assertOk()->assertSee('Aucun moyen de paiement n’est configuré', false);
    }

    public function test_webhook_is_verified_then_status_is_asked_again(): void
    {
        $customer = $this->customer();
        $pack = $this->plan(['slug' => 'credits', 'kind' => 'ai_credits', 'period' => 'once', 'trial_days' => 0, 'ai_credits' => 1_000_000]);
        $this->actingAs($customer, 'customer')->post(route('platform.customer.plans.buy', $pack));
        $order = Order::sole();
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'card']);
        $payment = Payment::sole();

        $body = json_encode(['reference' => $payment->reference]);
        $this->call('POST', '/api/platform/v1/payments/sandbox/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SANDBOX_SIGNATURE' => 'faux'], $body)->assertStatus(400);

        // Notification authentique, mais le prestataire dit « en attente » : rien n'est livré.
        $signature = hash_hmac('sha256', $body, config('app.key'));
        $this->call('POST', '/api/platform/v1/payments/sandbox/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SANDBOX_SIGNATURE' => $signature], $body)
            ->assertOk()->assertJson(['status' => 'pending']);
        $this->assertSame('pending', $order->fresh()->status);

        app(\App\Platform\Payments\SandboxPaymentGateway::class)->simulate($payment, true);
        $this->call('POST', '/api/platform/v1/payments/sandbox/webhook', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_SANDBOX_SIGNATURE' => $signature], $body)
            ->assertOk()->assertJson(['status' => 'succeeded']);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(1_000_000, $customer->fresh()->ai_balance);
    }

    public function test_paid_template_purchase_and_download_through_the_api(): void
    {
        $catalog = $this->sandbox.'/catalog';
        mkdir($catalog);
        file_put_contents($catalog.'/ong-horizon.zip', 'zip-payant');
        file_put_contents($catalog.'/ong-espoir.zip', 'zip-gratuit');
        config(['geniuspress.catalog.path' => $catalog]);
        TemplatePrice::create(['template_id' => 'ong-horizon', 'price' => 2500, 'currency' => 'USD', 'active' => true]);
        [$customer, $key] = $this->licensed();

        $this->getJson('/api/platform/v1/catalog/prices')->assertOk()->assertJson(['templates' => ['ong-horizon' => ['price' => 2500, 'currency' => 'USD']]]);
        $this->withToken($key)->get('/api/platform/v1/templates/ong-horizon/download')->assertStatus(403)->assertJson(['error' => 'not_purchased']);
        $this->withToken($key)->get('/api/platform/v1/templates/ong-espoir/download')->assertOk();
        $this->withToken($key)->get('/api/platform/v1/templates/inconnu/download')->assertNotFound();

        $this->actingAs($customer, 'customer')->get(route('platform.customer.templates'))->assertOk()->assertSee('ong-horizon');
        $this->post(route('platform.customer.templates.buy', 'ong-espoir'))->assertSessionHasErrors('template'); // gratuit
        $this->post(route('platform.customer.templates.buy', 'ong-horizon'))->assertRedirect();
        $this->payWithSandbox($customer, Order::sole());

        $this->assertTrue(Entitlement::where(['customer_id' => $customer->id, 'reference' => 'ong-horizon'])->exists());
        $response = $this->withToken($key)->get('/api/platform/v1/templates/ong-horizon/download')->assertOk();
        $this->assertSame('zip-payant', file_get_contents($response->baseResponse->getFile()->getPathname()));
        $this->withToken($key)->getJson('/api/platform/v1/account')->assertJson(['templates' => ['ong-horizon']]);

        // Déjà acheté : pas de seconde commande.
        $this->actingAs($customer, 'customer')->post(route('platform.customer.templates.buy', 'ong-horizon'))->assertSessionHasErrors('template');
    }

    public function test_domain_search_purchase_renewal_and_registrar_failure(): void
    {
        $customer = $this->customer();
        TldPrice::create(['tld' => 'cd', 'register_price' => 3000, 'renew_price' => 2500, 'currency' => 'USD', 'active' => true]);
        TldPrice::create(['tld' => 'org', 'register_price' => 1500, 'renew_price' => 1500, 'currency' => 'USD', 'active' => true]);
        $this->actingAs($customer, 'customer');

        $this->get(route('platform.customer.domains', ['q' => 'mon asso!']))->assertOk()->assertSee('Nom de domaine invalide');
        $this->get(route('platform.customer.domains', ['q' => 'monasso.net']))->assertOk()->assertSee('n’est pas proposée', false);
        $this->get(route('platform.customer.domains', ['q' => 'https://www.MonAsso.org/']))->assertOk()
            ->assertSeeInOrder(['monasso.org', 'monasso.cd']);
        $this->get(route('platform.customer.domains', ['q' => 'google']))->assertOk()->assertSee('Déjà pris');

        $this->post(route('platform.customer.domains.buy'), ['domain' => 'google.cd', 'years' => 1])->assertSessionHasErrors('domain');
        $this->post(route('platform.customer.domains.buy'), ['domain' => 'monasso.cd', 'years' => 2])->assertRedirect();
        $order = Order::sole();
        $this->assertSame(6000, $order->total);
        $this->payWithSandbox($customer, $order);

        $domain = Domain::sole();
        $this->assertSame(['monasso.cd', 'active', 'sandbox'], [$domain->name, $domain->status, $domain->registrar]);
        $this->assertTrue($domain->expires_at->gt(now()->addYears(2)->subDay()));
        $this->get(route('platform.customer.domains', ['q' => 'monasso.cd']))->assertSee('Déjà pris');

        // Renouvellement : depuis l'échéance actuelle.
        $expiry = $domain->expires_at;
        $this->post(route('platform.customer.domains.renew', $domain), ['years' => 1])->assertRedirect();
        $this->payWithSandbox($customer, Order::latest('id')->first());
        $this->assertTrue($domain->fresh()->expires_at->eq($expiry->copy()->addYear()));

        // Le registraire refuse : la commande est payée, la ligne reste à livrer avec son erreur.
        $this->actingAs($customer, 'customer')->post(route('platform.customer.domains.buy'), ['domain' => 'echec.org', 'years' => 1]);
        $this->payWithSandbox($customer, $failed = Order::latest('id')->first());
        $item = $failed->items()->sole();
        $this->assertSame('paid', $failed->fresh()->status);
        $this->assertNull($item->fulfilled_at);
        $this->assertNotNull($item->error);

        // Un autre client ne peut pas renouveler ce domaine.
        $this->actingAs($this->customer(), 'customer')->post(route('platform.customer.domains.renew', $domain), ['years' => 1])->assertNotFound();
    }

    public function test_license_keys_from_the_customer_space(): void
    {
        $customer = $this->customer();
        $this->actingAs($customer, 'customer');

        $this->post(route('platform.customer.licenses.store'), ['name' => 'Site de l’ONG', 'site_url' => 'https://ong.example'])->assertRedirect(route('platform.customer.dashboard'));
        $key = session('license_key');
        $this->assertMatchesRegularExpression(\App\Platform\LicenseKeys::PATTERN, $key);
        $this->get(route('platform.customer.dashboard'))->assertSee($key);
        $this->get(route('platform.customer.dashboard'))->assertDontSee($key); // une seule fois

        $license = $customer->licenses()->sole();
        $this->actingAs($this->customer(), 'customer')->delete(route('platform.customer.licenses.destroy', $license))->assertNotFound();
        $this->actingAs($customer, 'customer')->delete(route('platform.customer.licenses.destroy', $license))->assertRedirect();
        $this->assertSame('revoked', $license->fresh()->status);
    }
}
