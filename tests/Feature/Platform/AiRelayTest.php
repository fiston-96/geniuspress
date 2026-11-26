<?php

namespace Tests\Feature\Platform;

use App\Models\Setting;
use App\Platform\Ai\Pricing;
use App\Platform\LicenseKeys;
use App\Platform\Models\AiUsage;
use App\Platform\Models\CreditTransaction;
use App\Platform\Money;

/** Relais IA : clé de licence, tarif par modèle, débit de deux fois le coût réel, blocage. */
class AiRelayTest extends PlatformTestCase
{
    private function payload(array $extra = []): array
    {
        return $extra + [
            'model' => 'fake-model',
            'system' => 'Tu es un assistant.',
            'content' => [['type' => 'text', 'text' => 'Texte secret du client']],
            'schema' => ['type' => 'object'],
            'effort' => 'low',
            'max_tokens' => 1000,
            'action' => 'text_improve',
        ];
    }

    private function relay(string $key, array $payload = [])
    {
        return $this->withToken($key)->postJson('/api/platform/v1/ai/messages', $payload ?: $this->payload());
    }

    public function test_pricing_charges_twice_the_real_cost_rounded_up(): void
    {
        $pricing = app(Pricing::class);
        $rate = $this->rate(input: '3', output: '15');

        // 100 jetons × 3 $/M + 50 jetons × 15 $/M = 300 + 750 = 1 050 µ$
        $this->assertSame(1050, $pricing->cost($rate, 100, 50));
        $this->assertSame(2100, $pricing->billed($rate, 100, 50));

        // Prix fractionnaire : 1 jeton × 0,8 $/M = 0,8 µ$ → arrondi supérieur.
        $cheap = $this->rate('cheap', '0.8', '4');
        $this->assertSame(1, $pricing->cost($cheap, 1, 0));
        $this->assertSame(2, $pricing->billed($cheap, 1, 0)); // 1,6 µ$ → 2

        // Multiplicateur réglé par l'exploitant.
        Setting::write(['platform_ai_multiplier' => '2.5']);
        $this->assertSame(2625, $pricing->billed($rate, 100, 50));
        $this->assertSame(7_500_000, $pricing->billedPerMillion('3'));
        $this->assertSame('2.5', $pricing->multiplier());

        // Valeur absurde : retour à 2.
        Setting::write(['platform_ai_multiplier' => '0.5']);
        $this->assertSame('2', $pricing->multiplier());
    }

    public function test_money_helpers(): void
    {
        $this->assertSame(1250, Money::parse('12,50', 'USD'));
        $this->assertSame(5000, Money::parse('5000', 'XOF'));
        $this->assertSame(2_500_000, Money::usdToMicros('2.5'));
        $this->assertSame(-1_000_000, Money::usdToMicros('-1'));
        $this->assertSame('12.5', Money::input(1250, 2));
        $this->assertSame('0.000001', Money::input(1, 6));
        $this->assertSame("5\u{202F}000\u{00A0}XOF", Money::format(5000, 'XOF'));
        $this->assertSame("12,50\u{00A0}USD", Money::format(1250, 'USD'));
    }

    public function test_successful_call_is_relayed_billed_and_logged_without_content(): void
    {
        [$customer, $key] = $this->licensed(balance: 1_000_000);
        $this->rate();
        $fake = $this->fakeLlm([['text' => 'Bonjour']]);

        $this->relay($key)->assertOk()
            ->assertJson(['data' => ['text' => 'Bonjour'], 'usage' => ['input' => 100, 'output' => 50], 'model' => 'fake-model', 'billed' => 2100, 'balance' => 1_000_000 - 2100]);

        // La demande est transmise telle quelle à Claude, avec le modèle choisi.
        $this->assertSame('Tu es un assistant.', $fake->calls[0]['system']);
        $this->assertSame(1000, $fake->calls[0]['maxTokens']);
        $this->assertSame('fake-model', config('geniuspress.ai.model'));

        $this->assertSame(1_000_000 - 2100, $customer->fresh()->ai_balance);
        $usage = AiUsage::sole();
        $this->assertSame([100, 50, 1050, 2100, 'ok', 'text_improve'], [$usage->input_tokens, $usage->output_tokens, $usage->cost, $usage->billed, $usage->status, $usage->action]);
        $this->assertStringNotContainsString('secret', json_encode(AiUsage::all()->toArray()).json_encode(CreditTransaction::all()->toArray()));
        $this->assertSame(-2100, CreditTransaction::where('kind', 'usage')->sole()->amount);
    }

    public function test_calls_are_blocked_when_credits_run_out(): void
    {
        [$customer, $key] = $this->licensed(balance: 0);
        $this->rate();
        $fake = $this->fakeLlm();

        $this->relay($key)->assertStatus(402)->assertJson(['error' => 'credits_exhausted']);
        $this->assertSame([], $fake->calls, 'Claude n’est pas appelé sans crédits.');
        $this->assertSame('blocked', AiUsage::sole()->status);

        // Le dernier appel peut rendre le solde légèrement négatif, puis tout est bloqué.
        app(\App\Platform\Ai\CreditAccount::class)->credit($customer, 1000, 'purchase', 'Pack');
        $this->relay($key)->assertOk()->assertJson(['balance' => 1000 - 2100]);
        $this->relay($key)->assertStatus(402);
        $this->assertCount(1, $fake->calls);
    }

    public function test_unknown_key_revoked_key_suspended_account_and_unpriced_model(): void
    {
        [$customer, $key] = $this->licensed(balance: 1_000_000);
        $this->fakeLlm();

        $this->relay('gpl_aaaaaaaaaaaa_'.str_repeat('b', 40))->assertStatus(401)->assertJson(['error' => 'invalid_license']);
        $this->postJson('/api/platform/v1/ai/messages', $this->payload())->assertStatus(401);

        // Modèle sans tarif : refusé, avec la liste des modèles proposés.
        $this->rate('claude-test-1');
        $this->relay($key)->assertStatus(422)->assertJson(['error' => 'model_unavailable', 'models' => ['claude-test-1']]);

        $customer->forceFill(['status' => 'suspended'])->save();
        $this->relay($key)->assertStatus(403)->assertJson(['error' => 'suspended']);

        $customer->forceFill(['status' => 'active'])->save();
        app(LicenseKeys::class)->revoke($customer->licenses()->first());
        $this->relay($key)->assertStatus(401);
    }

    public function test_requests_are_validated_and_limited(): void
    {
        [, $key] = $this->licensed(balance: 1_000_000);
        $this->rate();
        $this->fakeLlm();
        config(['geniuspress.platform.relay_per_minute' => 5]);

        $this->relay($key, $this->payload(['max_tokens' => 100000]))->assertStatus(422)->assertJson(['error' => 'invalid_request']);
        $this->relay($key, $this->payload(['content' => [['type' => 'document', 'source' => []]]]))->assertStatus(422);
        $this->relay($key, $this->payload(['content' => [['type' => 'image', 'source' => ['type' => 'url', 'url' => 'http://x']]]]))->assertStatus(422);

        // 5 demandes par minute, refusées comprises.
        $this->relay($key)->assertOk();
        $this->relay($key)->assertOk();
        $this->relay($key)->assertStatus(429);
    }

    public function test_monthly_cap_per_license(): void
    {
        [$customer, $key] = $this->licensed(balance: 1_000_000);
        $this->rate();
        $this->fakeLlm([[], []]);
        $customer->licenses()->first()->update(['monthly_cap' => 3000]);

        $this->relay($key)->assertOk();   // 2 100 µ$ dépensés
        $this->relay($key)->assertOk();   // 4 200 µ$ : plafond dépassé après cet appel
        $this->relay($key)->assertStatus(402)->assertJson(['error' => 'monthly_cap']);
    }

    public function test_refusals_are_billed_and_upstream_errors_are_not(): void
    {
        [$customer, $key] = $this->licensed(balance: 1_000_000);
        $this->rate();
        $this->fakeLlm(refuse: true);

        // FakeLlm : refus après 10 jetons d'entrée → 30 µ$ réels, 60 µ$ débités.
        $this->relay($key)->assertStatus(422)->assertJson(['error' => 'refused', 'billed' => 60]);
        $this->assertSame(1_000_000 - 60, $customer->fresh()->ai_balance);

        $failing = new class implements \App\Ai\LlmGateway
        {
            public function isConfigured(): bool
            {
                return true;
            }

            public function json(string $system, array $content, array $schema, string $effort = 'medium', int $maxTokens = 16000): array
            {
                throw new \App\Ai\AiException('Le service IA a renvoyé une erreur. Réessayez dans un instant.');
            }
        };
        $this->app->instance(\App\Ai\LlmGateway::class, $failing);
        $this->relay($key)->assertStatus(502)->assertJson(['error' => 'upstream']);
        $this->assertSame(1_000_000 - 60, $customer->fresh()->ai_balance);
    }

    public function test_account_endpoint_and_disabled_platform(): void
    {
        [$customer, $key] = $this->licensed(balance: 5_000_000);
        $this->rate(input: '3', output: '15');

        $this->withToken($key)->getJson('/api/platform/v1/account')->assertOk()
            ->assertJson(['customer' => ['name' => 'Amani'], 'ai' => ['balance' => 5_000_000, 'models' => [['model' => 'fake-model', 'input_per_million' => 6_000_000, 'output_per_million' => 30_000_000]]]]);

        config(['geniuspress.platform.enabled' => false]);
        $this->withToken($key)->getJson('/api/platform/v1/account')->assertNotFound();
        $this->get('/compte/connexion')->assertNotFound();
    }

    public function test_license_keys_are_hashed_and_shown_once(): void
    {
        $customer = $this->customer();
        ['license' => $license, 'key' => $key] = app(LicenseKeys::class)->issue($customer, 'Mon site');

        $this->assertMatchesRegularExpression(LicenseKeys::PATTERN, $key);
        $this->assertSame(hash('sha256', $key), $license->key_hash);
        $this->assertStringNotContainsString(substr($key, 17), json_encode($license->fresh()->toArray()));
        $this->assertTrue(app(LicenseKeys::class)->find($key)->is($license));
        $this->assertNull(app(LicenseKeys::class)->find(substr($key, 0, -1).'x'));
    }
}
