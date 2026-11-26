<?php

namespace Tests\Feature\Platform;

use App\Platform\Models\Customer;
use App\Platform\Models\Operator;
use App\Platform\Models\Order;
use App\Platform\Models\Payout;
use App\Platform\Models\ReferralCommission;
use App\Platform\Notifications\PayoutRequested;
use App\Platform\Notifications\ReferralCommissionAvailable;
use App\Platform\Notifications\ReferralCommissionEarned;
use App\Platform\Notifications\ReferralSignup;
use App\Platform\Referrals\Referrals;
use App\Platform\Referrals\Wallet;
use Illuminate\Support\Facades\Mail;

/** Affiliation : lien, client apporté, commission sur la première commande, solde, paiement et retrait. */
class ReferralsTest extends PlatformTestCase
{
    private Operator $operator;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->operator = Operator::create(['name' => 'Exploitant', 'email' => 'ops@test.local', 'password' => 'secret-password-1']);
    }

    /** Commande payée par le client apporté (prestataire de test), avec une ligne de domaine en plus si demandé. */
    private function paidOrder(Customer $customer, bool $withDomain = false): Order
    {
        $plan = \App\Platform\Models\Plan::query()->first() ?? $this->plan();
        $order = app(\App\Platform\Checkout::class)->forPlan($customer, $plan);
        if ($withDomain) {
            $order->items()->create(['kind' => 'domain_register', 'reference' => 'amani.cd', 'description' => 'amani.cd', 'quantity' => 1, 'amount' => 3000, 'meta' => ['years' => 1]]);
            $order->update(['total' => $order->total + 3000]);
        }
        $this->actingAs($customer, 'customer')->post(route('platform.customer.orders.pay', $order), ['method' => 'mobile_money', 'operator' => 'orange', 'phone' => '+243810000000']);
        $this->post(route('platform.sandbox.complete', $order->payments()->latest('id')->first()), ['result' => 'success']);

        return $order->fresh();
    }

    private function sponsorAndReferred(): array
    {
        $sponsor = $this->customer(['name' => 'Affilié']);
        $code = app(Referrals::class)->codeFor($sponsor);
        $this->get('/?ref='.strtolower($code))->assertCookie(Referrals::COOKIE);

        $this->withCookie(Referrals::COOKIE, $code)->post('/compte/inscription', ['name' => 'Cliente apportée', 'email' => 'cliente@test.local',
            'password' => 'mot-de-passe-2026', 'password_confirmation' => 'mot-de-passe-2026'])->assertRedirect(route('platform.customer.dashboard'));
        $this->post('/compte/deconnexion');

        return [$sponsor->fresh(), Customer::query()->where('email', 'cliente@test.local')->sole()];
    }

    public function test_referral_link_attaches_the_new_customer(): void
    {
        [$sponsor, $referred] = $this->sponsorAndReferred();

        $this->assertSame($sponsor->id, $referred->referred_by);
        $this->assertNotNull($referred->referred_at);
        $this->assertSame(1, $sponsor->notifications()->where('type', ReferralSignup::class)->count());
        $this->assertStringContainsString('ref='.$sponsor->referral_code, app(Referrals::class)->link($sponsor));
        $this->get(route('platform.register', ['ref' => $sponsor->referral_code]))->assertSee('Vous avez été invité sur GeniusPress');

        // Jamais soi-même, jamais deux affiliés.
        $this->assertNull(app(Referrals::class)->attach($sponsor, $sponsor->referral_code));
        $other = $this->customer();
        $this->assertNull(app(Referrals::class)->attach($referred, app(Referrals::class)->codeFor($other)));
        $this->assertSame($sponsor->id, $referred->fresh()->referred_by);
    }

    public function test_commission_on_the_first_paid_order_only_without_domains(): void
    {
        [$sponsor, $referred] = $this->sponsorAndReferred();
        $order = $this->paidOrder($referred, withDomain: true);
        $this->assertSame('paid', $order->status);

        $commission = ReferralCommission::sole();
        $this->assertSame([$sponsor->id, $referred->id, $order->id], [$commission->sponsor_id, $commission->referred_id, $commission->order_id]);
        $this->assertSame([1000, 250, 'USD', 2500, 'pending'], [$commission->base_amount, $commission->amount, $commission->currency, $commission->rate_bps, $commission->status], '25 % hors domaine.');
        $this->assertEqualsWithDelta(30, now()->diffInDays($commission->available_at), 0.01);
        $this->assertSame(1, $sponsor->notifications()->where('type', ReferralCommissionEarned::class)->count());

        // Deuxième commande : pas de nouvelle commission.
        $this->paidOrder($referred);
        $this->assertSame(1, ReferralCommission::count());

        // Avant l'échéance, rien ; après, le solde de l’affilié est crédité.
        $this->artisan('geniuspress:referrals-release')->assertSuccessful();
        $this->assertSame(0, app(Wallet::class)->balance($sponsor, 'USD'));
        $this->travel(31)->days();
        $this->artisan('geniuspress:referrals-release')->assertSuccessful();
        $this->assertSame('available', $commission->fresh()->status);
        $this->assertSame(250, app(Wallet::class)->balance($sponsor, 'USD'));
        $this->assertSame(1, $sponsor->notifications()->where('type', ReferralCommissionAvailable::class)->count());
        $this->artisan('geniuspress:referrals-release')->assertSuccessful();
        $this->assertSame(250, app(Wallet::class)->balance($sponsor, 'USD'), 'Jamais versée deux fois.');
    }

    public function test_sponsor_pays_an_order_with_the_balance(): void
    {
        $sponsor = $this->customer();
        $plan = $this->plan(['price' => 200]);
        app(Wallet::class)->credit($sponsor, 150, 'USD', 'referral', 'Commission');
        $order = app(\App\Platform\Checkout::class)->forPlan($sponsor, $plan);

        // Solde insuffisant : pas de bouton, et le paiement est refusé.
        $this->actingAs($sponsor, 'customer')->get(route('platform.customer.orders.show', $order))->assertDontSee('Payer avec mon solde');
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'wallet'])->assertSessionHasErrors('payment');
        $this->assertSame('pending', $order->fresh()->status);

        app(Wallet::class)->credit($sponsor, 100, 'USD', 'referral', 'Commission');
        $this->get(route('platform.customer.orders.show', $order))->assertSee('Payer avec mon solde');
        $this->post(route('platform.customer.orders.pay', $order), ['method' => 'wallet'])->assertRedirect(route('platform.customer.orders.show', $order));
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(50, app(Wallet::class)->balance($sponsor, 'USD'));
        $this->assertSame('wallet', $order->payments()->sole()->gateway);
    }

    public function test_payout_requested_then_paid_or_rejected_in_the_console(): void
    {
        $sponsor = $this->customer();
        app(Wallet::class)->credit($sponsor, 5000, 'USD', 'referral', 'Commission');

        $this->actingAs($sponsor, 'customer')->get(route('platform.customer.referrals'))->assertOk()->assertSee('Votre lien d’affiliation')->assertSee('50,00');
        $this->post(route('platform.customer.referrals.payout'), ['currency' => 'USD', 'amount' => '60', 'mobile_operator' => 'airtel', 'phone' => '+243 99 000 0000'])
            ->assertSessionHasErrors('amount');
        $this->post(route('platform.customer.referrals.payout'), ['currency' => 'USD', 'amount' => '30', 'mobile_operator' => 'airtel', 'phone' => '+243 99 000 0000'])
            ->assertSessionHas('status');
        $payout = Payout::sole();
        $this->assertSame([3000, 'requested', '+243990000000'], [$payout->amount, $payout->status, $payout->phone]);
        $this->assertSame(2000, app(Wallet::class)->balance($sponsor, 'USD'), 'Montant réservé dès la demande.');
        $this->assertSame(1, $this->operator->notifications()->where('type', PayoutRequested::class)->count());
        $this->post(route('platform.customer.referrals.payout'), ['currency' => 'USD', 'amount' => '10', 'mobile_operator' => 'airtel', 'phone' => '+243990000000'])
            ->assertSessionHasErrors('amount');

        // Console : refus (le montant revient), puis nouvelle demande envoyée.
        $this->actingAs($this->operator, 'operator')->get(route('console.referrals.index'))->assertOk()->assertSee('Retraits à traiter')->assertSee('+243990000000');
        $this->post(route('console.referrals.payouts.reject', $payout), ['note' => 'Numéro injoignable'])->assertSessionHas('status');
        $this->assertSame(['rejected', $this->operator->id], [$payout->fresh()->status, $payout->fresh()->operator_id]);
        $this->assertSame(5000, app(Wallet::class)->balance($sponsor, 'USD'));

        $second = app(Referrals::class)->requestPayout($sponsor, 5000, 'USD', 'mpesa', '+243810000000');
        $this->post(route('console.referrals.payouts.paid', $second), ['transfer_reference' => 'MP-2026-0042'])->assertSessionHas('status');
        $this->assertSame(['paid', 'MP-2026-0042'], [$second->fresh()->status, $second->fresh()->transfer_reference]);
        $this->assertSame(0, app(Wallet::class)->balance($sponsor, 'USD'));
        $this->assertSame(2, $sponsor->notifications()->where('type', \App\Platform\Notifications\PayoutProcessed::class)->count());
    }

    public function test_operator_cancels_a_commission_already_released(): void
    {
        config(['geniuspress.platform.enabled' => true]);
        \App\Models\Setting::write(['platform_referral_hold_days' => 0]);
        [$sponsor, $referred] = $this->sponsorAndReferred();
        $this->paidOrder($referred);
        $commission = ReferralCommission::sole();
        $this->assertSame('available', $commission->status, 'Délai nul : disponible tout de suite.');
        $this->assertSame(250, app(Wallet::class)->balance($sponsor, 'USD'));

        $this->actingAs($this->operator, 'operator')->post(route('console.referrals.cancel', $commission), ['reason' => 'Commande remboursée'])->assertSessionHas('status');
        $this->assertSame(['canceled', 'Commande remboursée'], [$commission->fresh()->status, $commission->fresh()->cancel_reason]);
        $this->assertSame(0, app(Wallet::class)->balance($sponsor, 'USD'));

        $this->put(route('console.referrals.settings'), ['platform_referral_rate' => 30, 'platform_referral_hold_days' => 14])->assertSessionHas('status');
        $this->assertSame([30, 14], [Referrals::rate(), Referrals::holdDays()]);
    }
}
