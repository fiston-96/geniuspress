<?php

namespace Tests\Feature\Platform;

use App\Platform\Models\Order;

/** Espace client : navigation latérale, « Mon compte », « Mes commandes ». */
class CustomerSpaceTest extends PlatformTestCase
{
    public function test_navigation_account_and_orders_pages(): void
    {
        $customer = $this->customer(['name' => 'Awa Diallo', 'country' => 'SN']);
        $other = $this->customer(['name' => 'Autre']);
        $plan = $this->plan(['trial_days' => 0]);

        $this->actingAs($customer, 'customer')->post(route('platform.customer.plans.buy', $plan))->assertRedirect();
        $this->actingAs($other, 'customer')->post(route('platform.customer.plans.buy', $plan))->assertRedirect();
        $mine = Order::query()->where('customer_id', $customer->id)->sole();

        $html = $this->actingAs($customer, 'customer')->get(route('platform.customer.dashboard'))->assertOk()->getContent();
        foreach (['Mon espace', 'Domaines', 'Offres et hébergement', 'Mes commandes', 'Affiliation', 'Mon compte', 'Aide et documentation'] as $entry) {
            $this->assertStringContainsString($entry, $html, "Menu : {$entry}");
        }
        $this->assertStringContainsString('aria-current="page"', $html);

        $this->get(route('platform.customer.account'))->assertOk()
            ->assertSee('Votre pays : Sénégal')->assertSee('Clés de licence')->assertSee('Awa Diallo');

        $this->get(route('platform.customer.orders'))->assertOk()->assertSee($mine->number)
            ->assertDontSee(Order::query()->where('customer_id', $other->id)->value('number'));

        // Sans connexion : vers la page de connexion des clients.
        \Illuminate\Support\Facades\Auth::guard('customer')->logout();
        $this->get(route('platform.customer.orders'))->assertRedirect(route('platform.login'));
    }
}
