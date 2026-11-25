<?php

namespace Tests\Feature\Platform;

use App\Ai\AiException;
use App\Ai\AiRefusedException;
use App\Ai\AnthropicGateway;
use App\Ai\LlmGateway;
use App\Models\Setting;
use App\Models\User;
use App\Platform\Client\PlatformAiGateway;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/** Site d'un client (installé chez lui) : clé de licence, IA par le relais, modèles payants. */
class LicensedSiteTest extends PlatformTestCase
{
    private const KEY = 'gpl_abcdefghijkl_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMN';

    protected function setUp(): void
    {
        parent::setUp();
        // Ce site n'est pas la plateforme : il l'appelle.
        config(['geniuspress.platform.enabled' => false, 'geniuspress.license.platform_url' => 'https://plateforme.test']);
    }

    public function test_license_key_switches_the_ai_to_the_platform_relay(): void
    {
        $this->assertInstanceOf(AnthropicGateway::class, app(LlmGateway::class));

        config(['geniuspress.license.key' => self::KEY]);
        $this->assertInstanceOf(PlatformAiGateway::class, app(LlmGateway::class));

        // Sur la plateforme elle-même, jamais de relais vers soi.
        config(['geniuspress.platform.enabled' => true]);
        $this->assertInstanceOf(AnthropicGateway::class, app(LlmGateway::class));
    }

    public function test_license_key_is_saved_encrypted_from_the_settings(): void
    {
        $admin = User::create(['name' => 'Amani', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        $form = ['site_name' => 'ONG', 'primary_color' => '#2563eb', 'secondary_color' => '#0f172a', 'accent_color' => '#f59e0b',
            'font_family' => 'Inter', 'locales' => ['fr'], 'default_locale' => 'fr'];

        $this->actingAs($admin)->put(route('admin.settings.update'), $form + ['gp_license_key' => 'sk-ant-pas-une-licence'])->assertSessionHasErrors('gp_license_key');
        $this->put(route('admin.settings.update'), $form + ['gp_license_key' => self::KEY])->assertSessionHasNoErrors();

        $stored = Setting::read('gp_license_key');
        $this->assertNotSame(self::KEY, $stored);
        $this->assertSame(self::KEY, Crypt::decryptString($stored));
        $this->assertArrayNotHasKey('gp_license_key', Setting::publicValues());
        $this->assertInstanceOf(PlatformAiGateway::class, app(LlmGateway::class));
        $this->get(route('admin.settings.edit'))->assertOk()->assertDontSee(self::KEY)->assertSee('…KLMN');

        $this->put(route('admin.settings.update'), $form + ['remove_license_key' => '1']);
        $this->assertNull(Setting::read('gp_license_key'));
    }

    public function test_relay_gateway_success_and_french_errors(): void
    {
        config(['geniuspress.license.key' => self::KEY, 'geniuspress.ai.model' => 'claude-choisi']);
        $gateway = app(PlatformAiGateway::class);

        Http::fake(['plateforme.test/api/platform/v1/ai/messages' => Http::response(['data' => ['text' => 'Bonjour'], 'usage' => ['input' => 12, 'output' => 3], 'model' => 'claude-choisi', 'billed' => 90, 'balance' => 10])]);
        $result = $gateway->json('système', [['type' => 'text', 'text' => 'Salut']], ['type' => 'object'], 'low', 99999);
        $this->assertSame(['data' => ['text' => 'Bonjour'], 'usage' => ['input' => 12, 'output' => 3], 'model' => 'claude-choisi'], $result);
        Http::assertSent(fn (HttpRequest $r) => $r->hasHeader('Authorization', 'Bearer '.self::KEY)
            && $r['model'] === 'claude-choisi' && $r['max_tokens'] === 32000 && $r['system'] === 'système');

        $cases = [
            [['error' => 'credits_exhausted', 'message' => 'texte distant ignoré'], 402, 'Vos crédits IA sont épuisés. Rechargez-les dans votre espace client GeniusPress.'],
            [['error' => 'invalid_license'], 401, 'La clé de licence GeniusPress est invalide ou a été révoquée.'],
            [['error' => 'model_unavailable', 'models' => ['claude-a', 'claude-b']], 422, 'Le modèle d’IA claude-choisi n’est pas proposé par la plateforme. Modèles proposés : claude-a, claude-b.'],
            [['message' => 'Too Many Attempts.'], 429, 'Trop de demandes envoyées à l’IA. Réessayez dans une minute.'],
            [[], 500, 'Le service IA a renvoyé une erreur. Réessayez dans un instant.'],
        ];
        foreach ($cases as [$body, $status, $message]) {
            Http::swap(new \Illuminate\Http\Client\Factory);
            Http::fake(['*' => Http::response($body, $status)]);
            try {
                $gateway->json('s', [['type' => 'text', 'text' => 't']], []);
                $this->fail('AiException attendue');
            } catch (AiException $e) {
                $this->assertSame($message, $e->getMessage());
            }
        }

        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['*' => Http::response(['error' => 'refused', 'usage' => ['input' => 10, 'output' => 0], 'model' => 'claude-choisi'], 422)]);
        try {
            $gateway->json('s', [['type' => 'text', 'text' => 't']], []);
            $this->fail('AiRefusedException attendue');
        } catch (AiRefusedException $e) {
            $this->assertSame(['input' => 10, 'output' => 0], $e->usage);
        }
    }

    public function test_end_to_end_the_assistant_uses_the_relay_and_blocks_cleanly(): void
    {
        $admin = User::create(['name' => 'Amani', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        config(['geniuspress.license.key' => self::KEY]);
        Http::fake(['plateforme.test/*' => Http::response(['error' => 'credits_exhausted'], 402)]);

        $this->actingAs($admin)->postJson(route('admin.ai.text'), ['action' => 'improve', 'format' => 'plain', 'text' => 'Bonjour'])
            ->assertStatus(422)->assertJson(['message' => 'Vos crédits IA sont épuisés. Rechargez-les dans votre espace client GeniusPress.']);
    }

    public function test_paid_templates_are_downloaded_through_the_platform(): void
    {
        $catalog = app(\App\Templates\Catalog::class);
        config(['geniuspress.catalog.local' => false, 'geniuspress.catalog.url' => 'https://catalogue.test/v1']);
        $zip = 'contenu-du-modele';
        Http::fake([
            'catalogue.test/v1/index.json' => Http::response(['format' => 'geniuspress-catalog', 'templates' => [
                ['id' => 'ong-horizon', 'file' => 'ong-horizon.zip', 'sha256' => hash('sha256', $zip)],
            ]]),
            'plateforme.test/api/platform/v1/catalog/prices' => Http::response(['templates' => ['ong-horizon' => ['price' => 2500, 'currency' => 'USD']]]),
            'plateforme.test/api/platform/v1/templates/ong-horizon/download' => Http::response($zip),
        ]);

        $this->assertSame(['price' => 2500, 'currency' => 'USD'], $catalog->price('ong-horizon'));

        // Sans licence : message clair, rien n'est téléchargé.
        try {
            $catalog->download('ong-horizon');
            $this->fail('RuntimeException attendue');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Ce modèle est payant', $e->getMessage());
        }

        config(['geniuspress.license.key' => self::KEY]);
        $file = $catalog->download('ong-horizon');
        $this->assertSame($zip, file_get_contents($file));
        @unlink($file);
        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/templates/ong-horizon/download') && $r->hasHeader('Authorization', 'Bearer '.self::KEY));
        Http::assertNotSent(fn (HttpRequest $r) => str_ends_with($r->url(), 'catalogue.test/v1/ong-horizon.zip'));
    }
}
