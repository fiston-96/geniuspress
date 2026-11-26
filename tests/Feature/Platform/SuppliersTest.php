<?php

namespace Tests\Feature\Platform;

use App\Builder\RenderContext;
use App\Builder\WidgetRegistry;
use App\Hosting\Site;
use App\Models\Setting;
use App\Platform\Domains\SandboxRegistrar;
use App\Platform\Models\Customer;
use App\Platform\Models\Domain;
use App\Platform\Models\Operator;
use App\Platform\Models\Order;
use App\Platform\Models\Plan;
use App\Platform\Models\Service;
use App\Platform\Models\TldPrice;
use App\Platform\Notifications\ServiceDelivered;
use App\Platform\Notifications\ServiceToDeliver;
use App\Platform\Notifications\SupplierBalanceLow;
use App\Platform\Suppliers\Lws\LwsCatalog;
use App\Platform\Suppliers\Lws\LwsContacts;
use App\Platform\Suppliers\Lws\LwsException;
use App\Platform\Suppliers\Lws\LwsRegistrar;
use App\Platform\Suppliers\SupplierPricing;
use App\Platform\Website\OffersWidget;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

/**
 * Fournisseurs (LWS) : API simulée (Http::fake, jamais d'appel réel), domaines au nom du client,
 * zone DNS, coûts et marges, offres partenaires livrées par l'API ou par l'équipe, console,
 * widget de la vitrine et branchement d'un domaine sur un site hébergé.
 */
class SuppliersTest extends PlatformTestCase
{
    /** Requêtes envoyées à LWS : « MÉTHODE /chemin » → corps. */
    private array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();
        // Identifiant et clé inventés (aucun compte réel).
        Setting::write(['platform_lws_login' => '123456', 'platform_lws_key' => Crypt::encryptString('cle-de-test-0000')]);
    }

    /** Réponses de l'API simulée, remplaçables en cours de test. */
    private array $routes = [];

    private bool $faked = false;

    /** @param  array<string, mixed>  $routes  « GET /domain/x/availability » → data, ou ['__status' => HTTP, 'body' => corps] */
    private function fakeLws(array $routes): void
    {
        $this->sent = [];
        $this->routes = $routes;
        if ($this->faked) {
            return;
        }
        $this->faked = true;
        Http::fake(function (Request $request) {
            $routes = $this->routes;
            $path = substr((string) parse_url($request->url(), PHP_URL_PATH), strlen('/v1'));
            $key = $request->method().' '.$path;
            $this->sent[] = [$key, $request->data()];
            if (! array_key_exists($key, $routes)) {
                return Http::response(['code' => 404, 'info' => 'Not found', 'data' => null], 404);
            }
            $route = $routes[$key];
            if (is_array($route) && isset($route['__status'])) {
                return Http::response($route['body'], $route['__status']);
            }

            return Http::response(['code' => 200, 'info' => 'OK', 'data' => $route]);
        });
    }

    private function sentTo(string $key): array
    {
        return array_values(array_map(fn ($s) => $s[1], array_filter($this->sent, fn ($s) => $s[0] === $key)));
    }

    private function payWithSandbox(Customer $customer, Order $order): void
    {
        $this->actingAs($customer, 'customer')
            ->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'mtn', 'phone' => '+243 81 000 0000'])
            ->assertRedirect();
        $this->post(route('platform.sandbox.complete', $order->payments()->latest('id')->firstOrFail()), ['result' => 'success']);
    }

    private function operator(): Operator
    {
        return Operator::create(['name' => 'Exploitant', 'email' => 'ops'.uniqid().'@test.local', 'password' => 'secret-password-1']);
    }

    public function test_client_sends_credentials_in_test_mode_and_explains_errors_in_french(): void
    {
        $this->fakeLws(['GET /contact/0/credit' => 42.5]);
        $this->assertSame(4250, app(LwsCatalog::class)->balance());
        Http::assertSent(fn (Request $r) => $r->hasHeader('X-Auth-Login', '123456') && $r->hasHeader('X-Auth-Pass', 'cle-de-test-0000') && $r->hasHeader('X-Test-Mode', 'true'));

        // Mode test désactivé par l'exploitant : plus d'en-tête de test.
        Setting::write(['platform_lws_test' => false]);
        app(LwsCatalog::class)->balance();
        Http::assertSent(fn (Request $r) => ! $r->hasHeader('X-Test-Mode'));

        $this->fakeLws(['GET /contact/0/credit' => ['__status' => 401, 'body' => ['code' => 401, 'info' => 'Unauthorized']]]);
        try {
            app(LwsCatalog::class)->balance();
            $this->fail('Accès refusé attendu.');
        } catch (LwsException $e) {
            $this->assertStringContainsString('adresse IP autorisée', $e->getMessage());
        }

        Setting::write(['platform_lws_key' => null]);
        $this->expectExceptionMessage('La connexion à LWS n’est pas configurée');
        app(LwsCatalog::class)->balance();
    }

    public function test_domain_bought_from_lws_in_the_customer_name(): void
    {
        config(['geniuspress.platform.domain_registrar' => 'lws']);
        TldPrice::create(['tld' => 'cd', 'register_price' => 3000, 'renew_price' => 2500, 'currency' => 'USD', 'active' => true]);
        $this->fakeLws([
            'GET /domain/monasso.cd/availability' => true,
            'GET /domain/autre.cd/availability' => true,
            'POST /contact' => '367228',
            'POST /hosting' => [],
            'POST /hosting/monasso.cd/renew' => ['price' => 29.99],
        ]);
        $customer = $this->customer(['name' => 'Amani Kabila Mwamba']);
        $this->actingAs($customer, 'customer');

        // Coordonnées du titulaire exigées par le registre avant le paiement.
        $this->get(route('platform.customer.domains', ['q' => 'monasso.cd']))->assertOk()->assertSee('Titulaire du domaine');
        $this->post(route('platform.customer.domains.buy'), ['domain' => 'monasso.cd', 'years' => 2])->assertSessionHasErrors(['phone', 'address', 'city', 'country']);
        $this->assertSame(0, Order::count());

        $this->post(route('platform.customer.domains.buy'), ['domain' => 'monasso.cd', 'years' => 2,
            'phone' => '+243 81 234 5678', 'address' => '12 avenue de la Paix', 'city' => 'Goma', 'country' => 'cd'])->assertRedirect();
        $this->assertSame(['+243 81 234 5678', 'Goma', 'CD'], [$customer->fresh()->phone, $customer->fresh()->city, $customer->fresh()->country]);
        $this->payWithSandbox($customer, Order::sole());

        $domain = Domain::sole();
        $this->assertSame(['monasso.cd', 'active', 'lws'], [$domain->name, $domain->status, $domain->registrar]);
        $contact = $this->sentTo('POST /contact')[0];
        $this->assertSame(['Amani', 'Kabila Mwamba', '00243812345678', 'CD', $customer->email], [$contact['firstname'], $contact['lastname'], $contact['phone'], $contact['country'], $contact['email']]);
        $this->assertSame(['package' => 'domaine', 'domain' => 'monasso.cd', 'owner' => 367228, 'type' => 'buy', 'period' => 24], $this->sentTo('POST /hosting')[0]);
        $this->assertSame('367228', $customer->fresh()->providerId('lws'));

        // Deuxième domaine : le contact LWS du client est réutilisé.
        $this->post(route('platform.customer.domains.buy'), ['domain' => 'autre.cd', 'years' => 1,
            'phone' => '+243 81 234 5678', 'address' => '12 avenue de la Paix', 'city' => 'Goma', 'country' => 'CD'])->assertRedirect();
        $this->payWithSandbox($customer, Order::latest('id')->first());
        $this->assertCount(1, $this->sentTo('POST /contact'));

        // LWS renouvelle un an à la fois : plusieurs années sont refusées avant le paiement.
        $this->post(route('platform.customer.domains.renew', $domain), ['years' => 2])->assertSessionHasErrors('domain');
        $this->post(route('platform.customer.domains.renew', $domain), ['years' => 1])->assertRedirect();
        $this->payWithSandbox($customer, Order::latest('id')->first());
        $this->assertCount(1, $this->sentTo('POST /hosting/monasso.cd/renew'));

        // Code de transfert : envoyé par LWS au titulaire.
        $this->fakeLws(['GET /domain/monasso.cd/authcode' => 'pending']);
        $this->post(route('platform.customer.domains.authcode', $domain))->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('status');
        $this->assertCount(1, $this->sentTo('GET /domain/monasso.cd/authcode'));
    }

    public function test_supplier_errors_never_reach_the_customer_as_is(): void
    {
        config(['geniuspress.platform.domain_registrar' => 'lws']);
        TldPrice::create(['tld' => 'cd', 'register_price' => 3000, 'renew_price' => 2500, 'currency' => 'USD', 'active' => true]);
        Setting::write(['platform_lws_key' => null]);

        $this->actingAs($this->customer(), 'customer')->get(route('platform.customer.domains', ['q' => 'monasso.cd']))->assertOk()
            ->assertSee('La vente de noms de domaine n’est pas disponible pour le moment', false)->assertDontSee('LWS')->assertDontSee('Fournisseurs');

        // LWS en panne : même message générique.
        Setting::write(['platform_lws_key' => Crypt::encryptString('cle-de-test-0000')]);
        $this->fakeLws(['GET /domain/monasso.cd/availability' => ['__status' => 500, 'body' => ['code' => 500, 'info' => 'Internal error']]]);
        $this->get(route('platform.customer.domains', ['q' => 'monasso.cd']))->assertOk()
            ->assertSee('momentanément indisponible')->assertDontSee('LWS');
    }

    public function test_registrar_is_chosen_in_the_console_and_never_sells_in_test_mode_in_production(): void
    {
        $operator = $this->operator();
        $this->actingAs($operator, 'operator')->put(route('console.suppliers.settings'), ['registrar' => 'lws', 'lws_login' => '123456', 'lws_test' => '1', 'low_balance' => '20', 'markup' => '30'])
            ->assertSessionHas('status');
        $this->assertSame('lws', \App\Platform\Domains\DomainService::active());

        // Production + mode test LWS : vente fermée (le client paierait un domaine simulé).
        $this->app->detectEnvironment(fn () => 'production');
        try {
            $this->assertNotNull($this->catchRegistrar());
            $this->get(route('console.suppliers.index'))->assertSee('mode test', false);
            Setting::write(['platform_lws_test' => false]);
            $this->assertNull($this->catchRegistrar(), 'Production, vraie connexion : vente ouverte.');
        } finally {
            $this->app->detectEnvironment(fn () => 'testing');
        }

        // Vérification d'un domaine depuis la console (lecture seule, aussi en mode test).
        $this->fakeLws(['GET /domain/monasso.org/availability' => true]);
        $this->post(route('console.suppliers.domain.check'), ['domain' => 'MonAsso.org'])->assertSessionHas('status', 'LWS : monasso.org est disponible.');
    }

    private function catchRegistrar(): ?string
    {
        try {
            app(\App\Platform\Domains\DomainService::class)->registrar();

            return null;
        } catch (\App\Platform\PlatformException $e) {
            return $e->getMessage();
        }
    }

    public function test_lws_purchases_are_tracked_and_lws_auto_renewal_is_turned_off(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $operator = $this->operator();
        $customer = $this->customer();
        $domain = Domain::create(['customer_id' => $customer->id, 'name' => 'monasso.cd', 'status' => 'active', 'registrar' => 'lws', 'registered_at' => now()->subHours(3), 'expires_at' => now()->addYear()]);
        $lost = Domain::create(['customer_id' => $customer->id, 'name' => 'perdu.cd', 'status' => 'active', 'registrar' => 'lws', 'registered_at' => now()->subDays(3), 'expires_at' => now()->addYear()]);
        $this->fakeLws([
            'GET /hosting/monasso.cd' => ['domain' => 'monasso.cd', 'date_expiration' => '2027-11-20', 'package' => 'domaine'],
            'PUT /hosting/monasso.cd/autorenew' => true,
            'GET /hosting/perdu.cd' => ['__status' => 200, 'body' => ['code' => 314, 'info' => 'Domain not found', 'data' => null]],
        ]);

        $this->artisan('geniuspress:suppliers', ['action' => 'domains'])->assertSuccessful();
        $this->assertSame('2027-11-20', $domain->fresh()->expires_at->format('Y-m-d'));
        $this->assertNotNull($domain->fresh()->synced_at);
        $this->assertSame([['enable' => false]], $this->sentTo('PUT /hosting/monasso.cd/autorenew'));
        \Illuminate\Support\Facades\Notification::assertSentToTimes($operator, \App\Platform\Notifications\SupplierPurchaseMissing::class, 1);
        $this->assertNull($lost->fresh()->synced_at);

        // Deuxième passage : pas de nouvelle coupure, pas de nouvelle alerte pour le même achat.
        $this->fakeLws($this->routes);
        $this->artisan('geniuspress:suppliers', ['action' => 'domains'])->assertSuccessful();
        $this->assertSame([], $this->sentTo('PUT /hosting/monasso.cd/autorenew'));
        \Illuminate\Support\Facades\Notification::assertSentToTimes($operator, \App\Platform\Notifications\SupplierPurchaseMissing::class, 1);
    }

    public function test_customers_are_reminded_before_expiry_once_per_step(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $customer = $this->customer();
        $domain = Domain::create(['customer_id' => $customer->id, 'name' => 'monasso.org', 'status' => 'active', 'registrar' => 'sandbox', 'expires_at' => now()->addDays(6)]);
        $plan = Plan::create(['slug' => 'mail', 'kind' => 'mail', 'name' => ['fr' => 'Mail pro'], 'price' => 300, 'currency' => 'USD', 'period' => 'month', 'active' => true, 'provider' => 'manual']);
        $service = Service::create(['customer_id' => $customer->id, 'plan_id' => $plan->id, 'kind' => 'mail', 'provider' => 'manual', 'domain' => 'monasso.org', 'status' => 'active', 'expires_at' => now()->addDays(2)]);
        $old = Domain::create(['customer_id' => $customer->id, 'name' => 'ancien.org', 'status' => 'active', 'registrar' => 'sandbox', 'expires_at' => now()->subDay()]);

        $this->artisan('geniuspress:renewal-reminders')->assertSuccessful();
        \Illuminate\Support\Facades\Notification::assertSentToTimes($customer, \App\Platform\Notifications\ExpiringSoon::class, 2);
        $this->assertSame([7, 3, 'expired'], [$domain->fresh()->reminded_days, $service->fresh()->reminded_days, $old->fresh()->status]);

        // Le lendemain : même palier, pas de nouveau rappel. À la veille : dernier rappel.
        $this->artisan('geniuspress:renewal-reminders');
        \Illuminate\Support\Facades\Notification::assertSentToTimes($customer, \App\Platform\Notifications\ExpiringSoon::class, 2);
        $domain->update(['expires_at' => now()->addHours(20)]);
        $this->artisan('geniuspress:renewal-reminders');
        \Illuminate\Support\Facades\Notification::assertSentToTimes($customer, \App\Platform\Notifications\ExpiringSoon::class, 3);
        $this->assertSame(1, $domain->fresh()->reminded_days);

        // Renouvelé : les rappels repartent de zéro pour la prochaine échéance.
        app(\App\Platform\Domains\DomainService::class)->renew($domain->fresh(), 1);
        $this->assertNull($domain->fresh()->reminded_days);
        $this->assertTrue($domain->fresh()->expires_at->gt(now()->addMonths(11)));
    }

    public function test_dns_zone_points_domain_and_www_to_the_server(): void
    {
        $this->fakeLws([
            'GET /domain/monasso.cd/zdns' => [
                ['id' => '11', 'type' => 'A', 'name' => '@', 'value' => '192.0.2.1', 'ttl' => 86400],
                ['id' => '12', 'type' => 'AAAA', 'name' => '@', 'value' => '2001:db8::1', 'ttl' => 86400],
                ['id' => '13', 'type' => 'CNAME', 'name' => 'www', 'value' => 'ailleurs.example.', 'ttl' => 86400],
                ['id' => '14', 'type' => 'MX', 'name' => '@', 'value' => 'mail.example.', 'ttl' => 86400],
            ],
            'PUT /domain/monasso.cd/zdns' => [],
            'POST /domain/monasso.cd/zdns' => [],
            'DELETE /domain/monasso.cd/zdns' => 12,
        ]);
        app(LwsRegistrar::class)->pointTo('monasso.cd', '203.0.113.7');

        $this->assertSame([['id' => 11, 'type' => 'A', 'name' => '@', 'value' => '203.0.113.7', 'ttl' => 3600]], $this->sentTo('PUT /domain/monasso.cd/zdns'));
        $this->assertSame([['id' => 12], ['id' => 13]], $this->sentTo('DELETE /domain/monasso.cd/zdns'), 'AAAA et CNAME retirés, MX gardé.');
        $this->assertSame([['type' => 'A', 'name' => 'www', 'value' => '203.0.113.7', 'ttl' => 3600]], $this->sentTo('POST /domain/monasso.cd/zdns'));
        $this->assertSame('00243812345678', LwsContacts::phone('+243 81 234 5678'));
        $this->assertNull(LwsContacts::phone('0812345678'), 'Format international exigé.');
    }

    public function test_costs_are_synced_and_margins_computed(): void
    {
        $tld = TldPrice::create(['tld' => 'cd', 'register_price' => 3000, 'renew_price' => 3500, 'currency' => 'USD', 'active' => true]);
        $plan = Plan::create(['slug' => 'web-perso', 'kind' => 'web_hosting', 'name' => ['fr' => 'Hébergement Perso'], 'price' => 4000, 'currency' => 'USD',
            'period' => 'year', 'active' => true, 'provider' => 'lws', 'provider_ref' => 'LWS Perso', 'needs_domain' => true]);
        $this->fakeLws([
            'GET /domain/0/tlds' => [['tld' => '.cd', 'price_purchase' => '25.00', 'price_purchase_offer' => '19.99', 'price_renew' => '29.99', 'price_transfer' => '9.99']],
            'GET /hosting/0/priceall' => ['LWS Perso' => 1.99, 'LWS Pro' => 5.99],
        ]);

        $this->assertSame(['tlds' => 1, 'plans' => 1], app(LwsCatalog::class)->sync());
        $this->assertSame([1999, 2999, 'EUR'], [$tld->fresh()->cost_register, $tld->fresh()->cost_renew, $tld->fresh()->cost_currency]);
        $this->assertSame(2388, $plan->fresh()->cost, 'Prix LWS mensuel × 12 pour une offre annuelle.');

        Setting::write(['platform_supplier_markup' => '30', 'platform_supplier_rates' => ['USD' => '1.10', 'CDF' => '3000']]);
        $pricing = app(SupplierPricing::class);
        $this->assertSame(2199, $pricing->convert(1999, 'EUR', 'USD'));
        $this->assertSame(2900, $pricing->suggested(1999, 'EUR', 'USD'), '21,99 $ + 30 % = 28,59 $, arrondi à 29 $.');
        $this->assertSame(801, $pricing->margin(3000, 'USD', 1999, 'EUR'));
        $this->assertSame(7_800_000, $pricing->suggested(1999, 'EUR', 'CDF'), 'Francs congolais : 77 961 FC arrondis à 78 000 FC (en centimes).');
        $this->assertNull($pricing->suggested(1999, 'EUR', 'XOF'), 'Sans taux de change, pas de prix conseillé.');

        $operator = $this->operator();
        $this->actingAs($operator, 'operator')->post(route('console.suppliers.tlds.apply'))->assertSessionHas('status');
        $this->assertSame([2900, 4300], [$tld->fresh()->register_price, $tld->fresh()->renew_price]);
    }

    public function test_partner_offers_delivered_by_the_api_or_by_the_team(): void
    {
        Notification::fake();
        $operator = $this->operator();
        $web = Plan::create(['slug' => 'web-perso', 'kind' => 'web_hosting', 'name' => ['fr' => 'Hébergement Perso'], 'price' => 4000, 'currency' => 'USD',
            'period' => 'year', 'active' => true, 'provider' => 'lws', 'provider_ref' => 'LWS Perso', 'needs_domain' => true]);
        $mail = Plan::create(['slug' => 'mail-pro', 'kind' => 'mail', 'name' => ['fr' => 'Mail pro'], 'price' => 300, 'currency' => 'USD',
            'period' => 'month', 'active' => true, 'provider' => 'lws', 'needs_domain' => true]);
        $customer = $this->customer(['phone' => '+243812345678', 'address' => '12 avenue de la Paix', 'city' => 'Goma', 'country' => 'CD']);
        $this->fakeLws(['POST /contact' => '367228', 'POST /hosting' => []]);

        $this->actingAs($customer, 'customer')->get(route('platform.customer.plans'))->assertOk()->assertSee('Hébergement web')->assertSee('Adresses souhaitées');
        $this->post(route('platform.customer.plans.buy', $web))->assertSessionHasErrors('plan');
        $this->post(route('platform.customer.plans.buy', $web), ['domain' => 'monasso'])->assertSessionHasErrors('plan');

        // Hébergement web : acheté tout de suite par l'API, au nom du client.
        $this->post(route('platform.customer.plans.buy', $web), ['domain' => 'MonAsso.org'])->assertRedirect();
        $this->payWithSandbox($customer, Order::latest('id')->first());
        $this->assertSame(['package' => 'LWS Perso', 'domain' => 'monasso.org', 'owner' => 367228, 'type' => 'host', 'period' => 12], $this->sentTo('POST /hosting')[0]);
        $hosting = Service::query()->where('kind', 'web_hosting')->sole();
        $this->assertSame(['active', null, 'monasso.org'], [$hosting->status, $hosting->pending_action, $hosting->domain]);
        $this->assertTrue($hosting->expires_at->gt(now()->addMonths(11)));
        Notification::assertSentTo($customer, ServiceDelivered::class);

        // Mail pro : pas dans l'API, file de livraison de la console.
        $this->post(route('platform.customer.plans.buy', $mail), ['domain' => 'monasso.org', 'request' => "contact@monasso.org\ndirection@monasso.org"])->assertRedirect();
        $this->payWithSandbox($customer, Order::latest('id')->first());
        $box = Service::query()->where('kind', 'mail')->sole();
        $this->assertSame(['pending', 'create'], [$box->status, $box->pending_action]);
        Notification::assertSentTo($operator, ServiceToDeliver::class);
        $this->assertCount(1, $this->sentTo('POST /hosting'), 'Aucun appel LWS pour le mail pro.');
        $this->get(route('platform.customer.plans'))->assertSee('En préparation')->assertDontSee('LWS');

        $this->actingAs($operator, 'operator')->get(route('console.suppliers.index'))->assertOk()
            ->assertSee('À livrer')->assertSee('direction@monasso.org');
        $this->post(route('console.suppliers.services.done', $box), ['note' => 'Vos accès arrivent par e-mail.'])->assertSessionHas('status');
        $this->assertSame(['active', null, 'Vos accès arrivent par e-mail.'], [$box->fresh()->status, $box->fresh()->pending_action, $box->fresh()->note]);
        Notification::assertSentToTimes($customer, ServiceDelivered::class, 2);

        // Nouvel achat de l'hébergement pour le même domaine : prolongé, renouvellement confié à l'équipe.
        $end = $hosting->expires_at;
        $this->actingAs($customer, 'customer')->post(route('platform.customer.plans.buy', $web), ['domain' => 'monasso.org'])->assertRedirect();
        $this->payWithSandbox($customer, Order::latest('id')->first());
        $this->assertSame(1, Service::query()->where('kind', 'web_hosting')->count());
        $this->assertSame('renew', $hosting->fresh()->pending_action);
        $this->assertTrue($hosting->fresh()->expires_at->gt($end->copy()->addMonths(11)));
        $this->assertCount(1, $this->sentTo('POST /hosting'), 'Renouvellement jamais payé deux fois chez LWS.');

        // Un autre client ne voit pas ces services.
        $this->actingAs($this->customer(), 'customer')->get(route('platform.customer.plans'))->assertDontSee('monasso.org');
    }

    public function test_console_keeps_the_key_encrypted_and_never_shows_it(): void
    {
        $operator = $this->operator();
        $this->actingAs($operator, 'operator')->get(route('console.suppliers.index'))->assertOk()->assertSee('Connexion à LWS')->assertSee('Mode test');

        $this->put(route('console.suppliers.settings'), ['lws_login' => '654321', 'lws_key' => 'nouvelle-cle-9876', 'lws_test' => '1', 'low_balance' => '25', 'markup' => '35', 'rates' => ['USD' => '1,08']])
            ->assertSessionHas('status');
        $stored = (string) Setting::read('platform_lws_key');
        $this->assertNotSame('nouvelle-cle-9876', $stored);
        $this->assertSame('nouvelle-cle-9876', Crypt::decryptString($stored));
        $this->assertSame(['654321', '…9876', '35', ['USD' => '1.08']], [Setting::read('platform_lws_login'), Setting::read('platform_lws_key_hint'), Setting::read('platform_supplier_markup'), Setting::read('platform_supplier_rates')]);
        $this->get(route('console.suppliers.index'))->assertDontSee('nouvelle-cle-9876')->assertSee('…9876');
        $this->assertArrayNotHasKey('platform_lws_key', Setting::publicValues());

        // Champ vide : la clé est gardée.
        $this->put(route('console.suppliers.settings'), ['lws_login' => '654321', 'lws_key' => '', 'low_balance' => '25', 'markup' => '35'])->assertSessionHas('status');
        $this->assertSame($stored, Setting::read('platform_lws_key'));
        $this->assertFalse((bool) Setting::read('platform_lws_test'));

    }

    public function test_low_balance_alerts_the_operators_once_a_day(): void
    {
        Notification::fake();
        $operator = $this->operator();
        Setting::write(['platform_lws_low_balance' => '20']);
        $this->fakeLws(['GET /contact/0/credit' => 5.5]);

        $this->artisan('geniuspress:suppliers', ['action' => 'balance'])->assertSuccessful();
        $this->artisan('geniuspress:suppliers', ['action' => 'balance'])->assertSuccessful();
        Notification::assertSentToTimes($operator, SupplierBalanceLow::class, 1);
    }

    public function test_showcase_widget_lists_offers_and_domain_prices(): void
    {
        Plan::create(['slug' => 'web-perso', 'kind' => 'web_hosting', 'name' => ['fr' => 'Hébergement Perso'], 'price' => 4000, 'currency' => 'USD',
            'period' => 'year', 'active' => true, 'provider' => 'lws', 'features' => ['10 Go d’espace']]);
        Plan::create(['slug' => 'ancienne', 'kind' => 'mail', 'name' => ['fr' => 'Offre retirée'], 'price' => 100, 'currency' => 'USD', 'period' => 'month', 'active' => false, 'provider' => 'manual']);
        TldPrice::create(['tld' => 'org', 'register_price' => 1500, 'renew_price' => 1500, 'currency' => 'USD', 'active' => true]);

        $widget = app(WidgetRegistry::class)->get(OffersWidget::TYPE);
        $this->assertNotNull($widget);
        $html = $widget->render(['kinds' => 'web_hosting,mail', 'show_domains' => true], 'w1', new RenderContext);
        $this->assertStringContainsString('Hébergement Perso', $html);
        $this->assertStringContainsString('10 Go d’espace', $html);
        $this->assertStringContainsString('.org', $html);
        $this->assertStringContainsString('/compte/offres#web_hosting', $html);
        $this->assertStringNotContainsString('Offre retirée', $html);
        $this->assertStringNotContainsString('LWS', $html, 'Le fournisseur n’apparaît jamais côté public.');
        $this->assertStringContainsString('gp-offers__empty', $widget->render(['kinds' => 'storage'], 'w2', new RenderContext));

        // Sans la plateforme, le widget n'existe pas (CMS ordinaire).
        config(['geniuspress.platform.enabled' => false]);
        $this->app->forgetInstance(WidgetRegistry::class);
        $this->assertFalse(app(WidgetRegistry::class)->has(OffersWidget::TYPE));
    }

    public function test_customer_connects_a_domain_to_a_hosted_site(): void
    {
        config([
            'hosting.enabled' => true,
            'hosting.path' => $this->sandbox.DIRECTORY_SEPARATOR.'hosting',
            'hosting.domain' => 'localhost',
            'geniuspress.platform.server_ip' => '203.0.113.7',
        ]);
        SandboxRegistrar::$pointed = [];
        $customer = $this->customer();
        $site = Site::create(['slug' => 'cooperative', 'name' => 'Coopérative', 'account_id' => 'customer:'.$customer->id, 'kind' => 'free', 'status' => Site::STATUS_ACTIVE, 'host' => 'cooperative.localhost']);
        Site::create(['slug' => 'voisin', 'name' => 'Voisin', 'account_id' => 'customer:999', 'kind' => 'free', 'status' => Site::STATUS_ACTIVE, 'host' => 'voisin.localhost']);
        $domain = Domain::create(['customer_id' => $customer->id, 'name' => 'monasso.org', 'status' => 'active', 'registrar' => 'sandbox', 'expires_at' => now()->addYear()]);
        $hosts = config('hosting.path').DIRECTORY_SEPARATOR.'hosts'.DIRECTORY_SEPARATOR;

        $this->actingAs($customer, 'customer')->get(route('platform.customer.domains'))->assertOk()->assertSee('Afficher mon site à cette adresse')->assertDontSee('Voisin');
        $this->post(route('platform.customer.domains.connect', $domain), ['site' => 'voisin'])->assertSessionHasErrors('site');

        $this->post(route('platform.customer.domains.connect', $domain), ['site' => 'cooperative'])->assertSessionHas('status');
        $this->assertSame('cooperative', $domain->fresh()->site_slug);
        $this->assertSame(['monasso.org' => '203.0.113.7'], SandboxRegistrar::$pointed);
        $this->assertSame('cooperative', file_get_contents($hosts.'monasso.org'));
        $this->assertSame('cooperative', file_get_contents($hosts.'www.monasso.org'));
        $this->assertSame('cooperative', \App\Hosting\HostedBootstrap::resolve(config('hosting.path'), false, 'www.monasso.org') ?? 'cooperative', 'Le site répond à son domaine.');

        // Une adresse qui sert déjà un autre site est refusée.
        file_put_contents($hosts.'pris.org', 'voisin');
        $taken = Domain::create(['customer_id' => $customer->id, 'name' => 'pris.org', 'status' => 'active', 'registrar' => 'sandbox']);
        $this->post(route('platform.customer.domains.connect', $taken), ['site' => 'cooperative'])->assertSessionHasErrors('domain');
        $this->assertSame('voisin', file_get_contents($hosts.'pris.org'));

        $this->post(route('platform.customer.domains.disconnect', $domain))->assertSessionHas('status');
        $this->assertNull($domain->fresh()->site_slug);
        $this->assertFileDoesNotExist($hosts.'monasso.org');
        $this->assertFileDoesNotExist($hosts.'www.monasso.org');

        // Un autre client ne peut pas brancher ce domaine.
        $this->actingAs($this->customer(), 'customer')->post(route('platform.customer.domains.connect', $domain), ['site' => 'cooperative'])->assertNotFound();
    }
}
