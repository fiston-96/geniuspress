<?php

namespace Tests\Feature\Platform;

use App\Models\Setting;
use App\Platform\LicenseKeys;
use App\Platform\PlatformAccounts;
use App\Platform\PlatformException;

/** Interface utilisée par l'essai hébergé (app/Hosting) : comptes, essai, offre en cours, licence. */
class PlatformAccountsTest extends PlatformTestCase
{
    public function test_trial_lifecycle(): void
    {
        $accounts = app(PlatformAccounts::class);
        $customer = $accounts->register('Amani', ' Amani@Test.local ', 'secret-password-1', '+243810000000', 'cd');
        $this->assertSame('amani@test.local', $customer->email);
        $this->assertTrue($accounts->findByEmail('AMANI@test.local')->is($customer));
        $this->expectExceptionObject(new PlatformException('Un compte existe déjà avec cette adresse e-mail.'));
        $accounts->register('Autre', 'amani@test.local', 'secret-password-1');
    }

    public function test_trial_can_be_started_once_and_becomes_a_paid_subscription(): void
    {
        $accounts = app(PlatformAccounts::class);
        $customer = $this->customer();

        // Sans offre d'essai : pas d'essai possible.
        $this->assertNull($accounts->trialPlan());
        $this->assertFalse($accounts->canStartTrial($customer));

        $this->plan(['slug' => 'sans-essai', 'trial_days' => 0]);
        $plan = $this->plan(['slug' => 'essentiel', 'trial_days' => 14]);
        Setting::write(['platform_trial_credits' => 500_000]);
        $this->assertTrue($accounts->trialPlan()->is($plan));
        $this->assertTrue($accounts->canStartTrial($customer));

        $subscription = $accounts->startTrial($customer, 'site-42');
        $this->assertTrue($subscription->onTrial());
        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->endsAt()->between(now()->addDays(14)->subMinute(), now()->addDays(14)->addMinute()));
        $this->assertSame('site-42', $subscription->site_reference);
        $this->assertSame(500_000, $customer->fresh()->ai_balance);
        $this->assertFalse($accounts->canStartTrial($customer->fresh()));
        $this->assertTrue($accounts->activeSubscription($customer)->is($subscription));

        try {
            $accounts->startTrial($customer->fresh());
            $this->fail('Un seul essai par client.');
        } catch (PlatformException $e) {
            $this->assertSame('Vous avez déjà utilisé votre essai gratuit.', $e->getMessage());
        }

        // Payée pendant l'essai : la période commence à la fin de l'essai, même abonnement.
        $accounts->activatePlan($customer, $plan);
        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->current_period_end->gt(now()->addDays(14)->addMonth()->subMinute()));
        $this->assertSame(1, $customer->subscriptions()->count());

        $accounts->attachSite($subscription, 'site-43');
        $this->assertSame('site-43', $subscription->fresh()->site_reference);

        // Résiliation : reste active jusqu'à l'échéance.
        $accounts->cancel($subscription);
        $this->assertTrue($subscription->fresh()->isActive());
        $this->travelTo(now()->addMonths(3));
        $this->assertNull($accounts->activeSubscription($customer));
    }

    public function test_trial_is_refused_for_suspended_accounts_and_expires(): void
    {
        $accounts = app(PlatformAccounts::class);
        $this->plan(['trial_days' => 7]);
        $suspended = $this->customer(['status' => 'suspended']);
        $this->assertFalse($accounts->canStartTrial($suspended));
        $this->expectException(PlatformException::class);
        $accounts->startTrial($suspended);
    }

    public function test_trial_expiry_and_license_issue(): void
    {
        $accounts = app(PlatformAccounts::class);
        $this->plan(['trial_days' => 7]);
        $customer = $this->customer();
        $subscription = $accounts->startTrial($customer);

        ['license' => $license, 'key' => $key] = $accounts->issueLicense($customer, 'Site d’essai', 'https://essai.example');
        $this->assertTrue(app(LicenseKeys::class)->find($key)->is($license));
        $this->assertSame('https://essai.example', $license->site_url);

        $this->travelTo(now()->addDays(8));
        $this->assertFalse($subscription->fresh()->isActive());
        $this->assertNull($accounts->activeSubscription($customer));
    }
}
