<?php

namespace Tests\Feature\Platform;

use App\Models\Setting;
use App\Models\User;
use App\Platform\Models\Operator;
use Illuminate\Support\Facades\Auth;
use App\Platform\Models\AiRate;
use App\Platform\Models\Order;
use App\Platform\Models\Plan;
use App\Platform\Models\TemplatePrice;
use App\Platform\Models\TldPrice;
use App\Platform\OrderFulfiller;

/** Console d'exploitation (/console) : offres, tarifs, clients, commandes, journal IA. */
class PlatformAdminTest extends PlatformTestCase
{
    private Operator $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Operator::create(['name' => 'Exploitant', 'email' => 'ops@test.local', 'password' => 'secret-password-1']);
    }

    public function test_console_is_a_separate_module_with_its_own_accounts(): void
    {
        // Ni un administrateur du CMS ni un client n'entrent dans la console.
        $cmsAdmin = User::create(['name' => 'Admin du site', 'email' => 'site@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        $this->get('/console')->assertRedirect(route('console.login'));
        $this->actingAs($cmsAdmin)->get('/console')->assertRedirect(route('console.login'));
        $this->get('/admin')->assertOk()->assertDontSee('/console', false);
        Auth::guard('web')->logout();
        $this->actingAs($this->customer(), 'customer')->get('/console')->assertRedirect(route('console.login'));
        Auth::guard('customer')->logout();
        $this->assertSame(404, $this->get('/admin/platform')->status(), 'Plus d’exploitation dans le CMS.');

        // Connexion de l'exploitant ; un compte désactivé est refusé.
        $this->get(route('console.login'))->assertOk()->assertSee('Console GeniusPress');
        $this->post(route('console.login'), ['email' => 'site@test.local', 'password' => 'secret-password-1'])->assertSessionHasErrors('email');
        $this->post(route('console.login'), ['email' => 'OPS@test.local', 'password' => 'secret-password-1'])->assertRedirect(route('console.dashboard'));
        $this->assertSame($this->admin->id, Auth::guard('operator')->id());
        $this->assertFalse(Auth::guard('web')->check());
        $this->assertNotNull($this->admin->fresh()->last_login_at);
        $this->get(route('console.dashboard'))->assertOk()->assertSee('Console');
        $this->post(route('console.logout'))->assertRedirect(route('console.login'));

        $this->artisan('geniuspress:operator', ['action' => 'disable', 'email' => 'ops@test.local'])->assertSuccessful();
        $this->post(route('console.login'), ['email' => 'ops@test.local', 'password' => 'secret-password-1'])->assertSessionHasErrors('email');

        // Commande de création (mot de passe écrit dans un fichier, jamais affiché).
        $file = $this->sandbox.DIRECTORY_SEPARATOR.'console.txt';
        $this->artisan('geniuspress:operator', ['action' => 'create', 'email' => 'neema@test.local', '--name' => 'Neema', '--password-file' => $file])->assertSuccessful();
        $password = trim(explode(PHP_EOL, trim(file_get_contents($file)))[2]);
        $this->post(route('console.login'), ['email' => 'neema@test.local', 'password' => $password])->assertRedirect(route('console.dashboard'));

        config(['geniuspress.platform.enabled' => false]);
        $this->get('/console')->assertNotFound();
    }

    public function test_every_page_renders(): void
    {
        [$customer, $key] = $this->licensed(balance: 1_000_000);
        $plan = $this->plan();
        $this->rate();
        TldPrice::create(['tld' => 'cd', 'register_price' => 3000, 'renew_price' => 2500, 'currency' => 'USD', 'active' => true]);
        TemplatePrice::create(['template_id' => 'ong-horizon', 'price' => 2500, 'currency' => 'USD', 'active' => true]);
        $order = app(\App\Platform\Checkout::class)->forPlan($customer, $plan);
        $this->fakeLlm();
        $this->withToken($key)->postJson('/api/platform/v1/ai/messages', ['model' => 'fake-model', 'system' => 's', 'content' => [['type' => 'text', 'text' => 't']], 'schema' => ['type' => 'object']])->assertOk();

        $this->actingAs($this->admin, 'operator');
        $this->get(route('console.dashboard'))->assertOk()->assertSee($order->number);
        $this->get(route('console.customers.index', ['q' => 'Amani']))->assertOk()->assertSee($customer->email);
        $this->get(route('console.customers.show', $customer))->assertOk()->assertSee('Site de test');
        $this->get(route('console.plans.index'))->assertOk()->assertSee('Essentiel');
        $this->get(route('console.plans.create'))->assertOk();
        $this->get(route('console.plans.edit', $plan))->assertOk()->assertSee('essentiel');
        $this->get(route('console.pricing.index'))->assertOk()->assertSee('fake-model')->assertSee('ong-horizon')->assertSee('.cd');
        $this->get(route('console.orders.index', ['status' => 'pending']))->assertOk()->assertSee($order->number);
        $this->get(route('console.orders.show', $order))->assertOk();
        $this->get(route('console.domains.index'))->assertOk();
        $this->get(route('console.usage.index', ['customer' => $customer->id]))->assertOk()->assertSee('fake-model');

        // Pages du client aussi.
        $this->actingAs($customer, 'customer');
        foreach (['dashboard', 'credits', 'plans', 'templates', 'domains'] as $page) {
            $this->get(route('platform.customer.'.$page))->assertOk();
        }
        $this->get(route('platform.customer.orders.show', $order))->assertOk()->assertSee('Mobile Money');
    }

    public function test_plans_are_managed_with_prices_and_periods(): void
    {
        $this->actingAs($this->admin, 'operator');
        $this->post(route('console.plans.store'), ['slug' => 'Pas Valide', 'kind' => 'hosting', 'name_fr' => 'X', 'price' => '10', 'currency' => 'USD', 'period' => 'month'])
            ->assertSessionHasErrors('slug');
        $this->post(route('console.plans.store'), [
            'slug' => 'pro', 'kind' => 'hosting', 'name_fr' => 'Pro', 'name_en' => 'Pro', 'price' => '15000', 'currency' => 'XOF',
            'period' => 'year', 'trial_days' => 30, 'ai_credits' => '2,5', 'features' => "Nom de domaine offert\n\nSauvegardes", 'active' => '1',
        ])->assertRedirect(route('console.plans.index'));
        $plan = Plan::where('slug', 'pro')->sole();
        $this->assertSame([15000, 'XOF', 'year', 30, 2_500_000, ['Nom de domaine offert', 'Sauvegardes'], true],
            [$plan->price, $plan->currency, $plan->period, $plan->trial_days, $plan->ai_credits, $plan->features, $plan->active]);

        // Un pack de crédits est toujours un paiement unique, sans essai.
        $this->put(route('console.plans.update', $plan), ['slug' => 'pro', 'kind' => 'ai_credits', 'name_fr' => 'Pack', 'price' => '9.99', 'currency' => 'USD', 'period' => 'month', 'trial_days' => 10, 'ai_credits' => '10', 'active' => '0'])
            ->assertRedirect();
        $plan->refresh();
        $this->assertSame(['once', 0, 999, false], [$plan->period, $plan->trial_days, $plan->price, $plan->active]);
    }

    public function test_ai_rates_multiplier_and_trial_settings(): void
    {
        $this->actingAs($this->admin, 'operator');
        $this->post(route('console.pricing.rates.store'), ['model' => 'claude-test', 'label' => 'Test', 'input_price' => '3', 'output_price' => '15,5'])->assertRedirect();
        $rate = AiRate::sole();
        $this->assertSame([3_000_000, 15_500_000, true], [\App\Platform\Money::scale((string) $rate->input_price, 6), \App\Platform\Money::scale((string) $rate->output_price, 6), $rate->active]);
        $this->post(route('console.pricing.rates.store'), ['model' => 'claude-test', 'label' => 'Doublon', 'input_price' => '1', 'output_price' => '1'])->assertSessionHasErrors('model');

        $this->put(route('console.pricing.rates.update', $rate), ['model' => 'claude-test', 'label' => 'Test', 'input_price' => '4', 'output_price' => '20', 'active' => '0'])->assertRedirect();
        $this->assertFalse($rate->fresh()->active);

        $this->put(route('console.pricing.settings'), ['multiplier' => '0,5'])->assertSessionHasErrors('multiplier');
        $this->put(route('console.pricing.settings'), ['multiplier' => '2,5', 'trial_credits' => '1'])->assertRedirect();
        $this->assertSame(['2.5', 1_000_000], [Setting::read('platform_ai_multiplier'), Setting::read('platform_trial_credits')]);
        // Réglages privés : jamais envoyés au navigateur.
        $this->assertArrayNotHasKey('platform_ai_multiplier', Setting::publicValues());

        $this->delete(route('console.pricing.rates.destroy', $rate))->assertRedirect();
        $this->assertSame(0, AiRate::count());

        $this->post(route('console.pricing.templates.store'), ['template_id' => 'ong-horizon', 'price' => '25', 'currency' => 'USD'])->assertRedirect();
        $this->assertSame(2500, TemplatePrice::sole()->price);
        $this->post(route('console.pricing.tlds.store'), ['tld' => '.CD', 'register_price' => '30', 'renew_price' => '25', 'currency' => 'USD'])->assertSessionHasErrors('tld');
        $this->post(route('console.pricing.tlds.store'), ['tld' => 'co.za', 'register_price' => '30', 'renew_price' => '25', 'currency' => 'USD'])->assertRedirect();
        $this->assertSame([3000, 2500], [TldPrice::sole()->register_price, TldPrice::sole()->renew_price]);
    }

    public function test_customers_credits_licenses_and_failed_deliveries(): void
    {
        [$customer, $key] = $this->licensed();
        $this->actingAs($this->admin, 'operator');

        $this->post(route('console.customers.credits', $customer), ['amount' => '5', 'reason' => ''])->assertSessionHasErrors('reason');
        $this->post(route('console.customers.credits', $customer), ['amount' => '5', 'reason' => 'Geste commercial'])->assertRedirect();
        $this->post(route('console.customers.credits', $customer), ['amount' => '-1,5', 'reason' => 'Correction'])->assertRedirect();
        $this->assertSame(3_500_000, $customer->fresh()->ai_balance);
        $tx = $customer->creditTransactions()->latest('id')->first();
        $this->assertSame(['adjustment', 'Correction', $this->admin->id, 3_500_000], [$tx->kind, $tx->description, $tx->operator_id, $tx->balance_after]);

        $license = $customer->licenses()->sole();
        $this->put(route('console.licenses.cap', $license), ['monthly_cap' => '10'])->assertRedirect();
        $this->assertSame(10_000_000, $license->fresh()->monthly_cap);
        $this->delete(route('console.licenses.revoke', $license))->assertRedirect();
        $this->withToken($key)->getJson('/api/platform/v1/account')->assertStatus(401);

        $this->put(route('console.customers.status', $customer), ['status' => 'suspended'])->assertRedirect();
        $this->assertFalse($customer->fresh()->isActive());

        // Domaine payé que le registraire a refusé : visible sur le tableau de bord, relancé ensuite.
        $customer->forceFill(['status' => 'active'])->save();
        TldPrice::create(['tld' => 'org', 'register_price' => 1500, 'renew_price' => 1500, 'currency' => 'USD', 'active' => true]);
        $order = app(\App\Platform\Checkout::class)->forDomain($customer, 'echec-asso.org');
        $order->update(['status' => 'paid', 'paid_at' => now()]);
        app(OrderFulfiller::class)->deliver($order);
        $this->get(route('console.dashboard'))->assertOk()->assertSee('Achats payés non livrés')->assertSee($order->number);

        $order->items()->update(['reference' => 'asso-reprise.org']); // le registraire accepte cette fois
        $this->post(route('console.orders.deliver', $order))->assertSessionHas('status', 'Commande livrée.');
        $this->assertNotNull($order->items()->sole()->fulfilled_at);
        $this->assertSame('asso-reprise.org', $customer->domains()->sole()->name);
    }
}
