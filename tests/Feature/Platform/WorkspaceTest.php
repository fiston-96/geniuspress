<?php

namespace Tests\Feature\Platform;

use App\Hosting\Site;
use App\Models\User;
use App\Platform\Models\Subscription;

/**
 * Espace de travail du client : depuis /compte, il crée son site GeniusPress hébergé
 * (essai), l'ouvre, et la plateforme enregistre l'abonnement d'essai.
 */
class WorkspaceTest extends PlatformTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'hosting.enabled' => true,
            'hosting.path' => $this->sandbox.DIRECTORY_SEPARATOR.'hosting',
            'hosting.domain' => 'localhost',
            'hosting.scheme' => 'http',
            'hosting.port' => 8000,
            'hosting.database.driver' => 'sqlite',
        ]);
    }

    public function test_customer_creates_and_opens_a_hosted_site_from_the_workspace(): void
    {
        $this->plan(['trial_days' => 10]);
        $customer = $this->customer(['name' => 'Coopérative Amani']);

        // Visiteur : envoyé vers la connexion des clients, pas vers celle de l'administration.
        $this->get(route('hosting.trial'))->assertRedirect(route('platform.login'));

        $this->actingAs($customer, 'customer')->get(route('platform.customer.dashboard'))
            ->assertOk()->assertSee('Créez votre site GeniusPress')->assertSee('Créer mon site gratuitement');

        $this->get(route('hosting.trial'))->assertOk()->assertSee('Créer mon site d’essai');
        $this->post(route('hosting.trial.store'), ['name' => 'Coopérative Amani', 'slug' => 'cooperative'])->assertRedirect(route('hosting.trial'));

        $site = Site::query()->sole();
        $this->assertSame(Site::STATUS_ACTIVE, $site->status, (string) $site->error);
        $this->assertSame('customer:'.$customer->id, $site->account_id);
        $this->assertSame($customer->email, $site->owner_email);
        $this->assertEqualsWithDelta(10, now()->diffInDays($site->expires_at), 0.01, 'Durée de l’offre d’essai de la plateforme.');

        // La plateforme a ouvert l'abonnement d'essai et marqué l'essai comme utilisé.
        $subscription = Subscription::query()->sole();
        $this->assertSame('trialing', $subscription->status);
        $this->assertSame('cooperative', $subscription->site_reference);
        $this->assertNotNull($customer->fresh()->trial_used_at);

        $this->get(route('platform.customer.dashboard'))->assertOk()
            ->assertSee('Mon site GeniusPress')->assertSee('cooperative.localhost')->assertSee('Ouvrir mon site et le builder');
        $this->get(route('hosting.trial.open'))->assertRedirect();

        // Un second essai est refusé.
        $this->assertSame('Vous avez déjà utilisé votre essai gratuit.', app(\App\Hosting\HostingAccounts::class)->trialRefusal($customer->fresh()));
    }

    public function test_platform_admins_cannot_start_a_trial_as_customers(): void
    {
        $this->plan();
        $admin = User::create(['name' => 'Exploitant', 'email' => 'admin@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);

        $this->actingAs($admin)->get(route('hosting.trial'))->assertOk()
            ->assertSee('Créez un compte GeniusPress ou connectez-vous pour lancer votre essai.');
        $this->post(route('hosting.trial.store'), ['name' => 'Test', 'slug' => 'test-admin'])
            ->assertSessionHas('warnings', ['Créez un compte GeniusPress ou connectez-vous pour lancer votre essai.']);
        $this->assertSame(0, Site::query()->count());
    }
}
