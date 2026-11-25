<?php

namespace Tests\Feature\Platform;

use App\Ai\LlmGateway;
use App\Platform\LicenseKeys;
use App\Platform\Models\AiRate;
use App\Platform\Models\Customer;
use App\Platform\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeLlm;
use Tests\TestCase;

/** Base des tests de la plateforme commerciale : plateforme activée, aucun prestataire réel. */
abstract class PlatformTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'geniuspress.platform.enabled' => true,
            'geniuspress.platform.payment_gateway' => 'sandbox',
            'geniuspress.platform.domain_registrar' => 'sandbox',
            'geniuspress.license.key' => null,
        ]);
        // Site installé (verrou du bac à sable des tests).
        file_put_contents(config('geniuspress.install_lock'), 'test');
    }

    protected function customer(array $attrs = []): Customer
    {
        return Customer::create($attrs + ['name' => 'Amani', 'email' => 'amani'.uniqid().'@test.local', 'password' => 'secret-password-1', 'status' => 'active']);
    }

    /** @return array{0: Customer, 1: string} client et clé de licence en clair */
    protected function licensed(int $balance = 0): array
    {
        $customer = $this->customer();
        if ($balance) {
            app(\App\Platform\Ai\CreditAccount::class)->credit($customer, $balance, 'grant', 'Test');
        }
        ['key' => $key] = app(LicenseKeys::class)->issue($customer, 'Site de test');

        return [$customer, $key];
    }

    /** Tarif de test (prix inventés, sans rapport avec les tarifs réels). */
    protected function rate(string $model = 'fake-model', string $input = '3', string $output = '15'): AiRate
    {
        return AiRate::create(['model' => $model, 'label' => 'Modèle de test', 'input_price' => $input, 'output_price' => $output, 'active' => true]);
    }

    protected function plan(array $attrs = []): Plan
    {
        return Plan::create($attrs + [
            'slug' => 'essentiel', 'kind' => 'hosting', 'name' => ['fr' => 'Essentiel', 'en' => 'Essential'],
            'price' => 1000, 'currency' => 'USD', 'period' => 'month', 'trial_days' => 14, 'active' => true,
        ]);
    }

    protected function fakeLlm(array $responses = [['ok' => true]], bool $refuse = false): FakeLlm
    {
        $fake = new FakeLlm($responses, true, $refuse);
        $this->app->instance(LlmGateway::class, $fake);

        return $fake;
    }
}
