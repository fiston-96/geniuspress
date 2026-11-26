<?php

namespace Tests\Feature\Platform;

use App\Models\Setting;
use App\Platform\Countries;

/**
 * Pays du client : la plateforme s'adapte (prix aussi affichés dans sa devise au taux de la
 * console, pays présélectionné à l'inscription d'après le navigateur et au paiement).
 */
class CountryTest extends PlatformTestCase
{
    public function test_currency_and_indicative_price_of_a_country(): void
    {
        $this->assertSame(['CDF', 'XOF', 'EUR', 'CAD', 'USD'], array_map([Countries::class, 'currency'], ['CD', 'SN', 'FR', 'CA', 'US']));
        $this->assertSame('Sénégal', Countries::name('sn'));
        $this->assertFalse(Countries::valid('ZZ'));
        $this->assertSame('CD', Countries::fromBrowser('fr-CD,fr;q=0.9,en;q=0.8'));
        $this->assertNull(Countries::fromBrowser('fr'));

        // Sans taux : rien d'indicatif. Avec taux : arrondi à l'unité.
        $this->assertNull(Countries::approx(1000, 'USD', 'CD'));
        Setting::write(['platform_supplier_rates' => ['USD' => '1.08', 'CDF' => '3100']]);
        $this->assertSame("≈ 28\u{202F}704,00\u{00A0}CDF", Countries::approx(1000, 'USD', 'CD'));
        $this->assertSame("≈ 9,00\u{00A0}EUR", Countries::approx(1000, 'USD', 'FR'));
        $this->assertNull(Countries::approx(1000, 'USD', 'US'), 'Même devise : rien à ajouter.');
    }

    public function test_customer_chooses_a_country_and_prices_adapt(): void
    {
        Setting::write(['platform_supplier_rates' => ['USD' => '1.08', 'XOF' => '655.957']]);
        $this->plan(['slug' => 'pro', 'kind' => 'web_hosting', 'price' => 1000, 'currency' => 'USD', 'period' => 'year', 'trial_days' => 0, 'provider' => 'manual']);
        $customer = $this->customer();

        // Sans pays : invitation à le choisir, prix en USD seulement.
        $this->actingAs($customer, 'customer')->get(route('platform.customer.dashboard'))->assertOk()->assertSee('Indiquez votre pays');
        $this->get(route('platform.customer.plans'))->assertOk()->assertDontSee('≈');

        $this->put(route('platform.customer.country'), ['country' => 'ZZ'])->assertSessionHasErrors('country');
        $this->put(route('platform.customer.country'), ['country' => 'sn'])->assertSessionHas('status');
        $this->assertSame('SN', $customer->fresh()->country);

        $this->get(route('platform.customer.dashboard'))->assertSee('Votre pays : Sénégal')->assertSee('Les prix sont aussi affichés en XOF', false);
        $this->get(route('platform.customer.plans'))->assertSee("≈ 6\u{202F}074\u{00A0}XOF", false);
    }

    public function test_registration_suggests_the_country_of_the_browser(): void
    {
        $this->get('/compte/inscription', ['Accept-Language' => 'fr-CI,fr;q=0.9'])->assertOk()->assertSee('<option value="CI" selected', false);
        $this->post('/compte/inscription', ['name' => 'Kouassi', 'email' => 'kouassi@test.local', 'country' => 'XX', 'password' => 'mot-de-passe-2026', 'password_confirmation' => 'mot-de-passe-2026'])
            ->assertSessionHasErrors('country');
    }
}
