<?php

namespace Tests\Feature;

use App\Ai\LlmGateway;
use App\Models\AiRequest;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\Fakes\FakeLlm;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
    }

    private function fake(array $responses = [], bool $configured = true, bool $refuse = false): FakeLlm
    {
        $fake = new FakeLlm($responses, $configured, $refuse);
        $this->app->instance(LlmGateway::class, $fake);

        return $fake;
    }

    private function page(array $attrs = []): Page
    {
        return Page::create($attrs + ['title' => 'Accueil', 'slug' => 'accueil', 'content' => ['sections' => [[
            'id' => 'section0001', 'columns' => [['id' => 'column0001', 'widgets' => [
                ['id' => 'heading001', 'type' => 'heading', 'settings' => ['text' => 'Bienvenue']],
                ['id' => 'text000001', 'type' => 'text', 'settings' => ['html' => '<p>Notre <strong>mission</strong></p>']],
                ['id' => 'image00001', 'type' => 'image', 'settings' => ['url' => '/storage/x.png']],
            ]]],
        ]]]]);
    }

    public function test_generated_pages_are_sanitized_and_get_ids(): void
    {
        $fake = $this->fake([[
            'meta_title' => 'ONG Espoir',
            'meta_description' => 'Description',
            'sections' => [[
                'settings' => ['background_color' => '#0F172A', 'padding_top' => 9999, 'anchor' => 'Nous Contacter'],
                'columns' => [['settings' => ['width' => null], 'widgets' => [
                    ['type' => 'heading', 'settings' => ['text' => 'Titre', 'tag' => 'h1', 'color' => 'javascript:alert(1)']],
                    ['type' => 'text', 'settings' => ['html' => '<p>Bonjour<script>alert(1)</script><img src=x onerror=y></p>']],
                    ['type' => 'button', 'settings' => ['text' => 'Don', 'url' => 'javascript:alert(1)']],
                ]]],
            ]],
        ]]);
        $page = $this->page();

        $r = $this->actingAs($this->admin)->postJson("/admin/ai/pages/{$page->id}/generate", ['brief' => 'Une page pour notre ONG à Goma'])->assertOk();

        // L'IA décrit des sections et des colonnes : elles arrivent converties en conteneurs.
        $this->assertSame(2, $r->json('content.version'));
        $section = $r->json('content.elements.0');
        $this->assertSame('container', $section['type']);
        $this->assertMatchesRegularExpression('/^[a-z0-9]{10}$/', $section['id']);
        $this->assertSame('#0f172a', $section['settings']['background_color']);
        $this->assertSame(600, $section['settings']['padding']['t']);
        $this->assertSame('nous-contacter', $section['settings']['css_id']);
        $widgets = $this->widgetsOf($r->json('content'));
        $this->assertArrayNotHasKey('color', $widgets[0]['settings']);
        $this->assertSame('<p>Bonjour</p>', $widgets[1]['settings']['html']);
        $this->assertArrayNotHasKey('url', $widgets[2]['settings']);
        $this->assertSame('ONG Espoir', $r->json('meta_title'));

        // Le contenu n'est pas enregistré tant que l'utilisateur ne sauvegarde pas.
        $this->assertSame('Bienvenue', $this->widgetsOf($page->fresh()->content)[0]['settings']['text']);
        $this->assertSame(1, AiRequest::where('action', 'generate_page')->where('status', 'ok')->count());
        $this->assertStringContainsString('Français', $fake->calls[0]['system']);
    }

    public function test_page_schema_is_valid_for_structured_outputs(): void
    {
        $fake = $this->fake([['meta_title' => '', 'meta_description' => '', 'sections' => []]]);
        $page = $this->page();
        $this->actingAs($this->admin)->postJson("/admin/ai/pages/{$page->id}/generate", ['brief' => 'Une page de test'])->assertOk();

        $check = function (array $node, string $path) use (&$check) {
            if (($node['type'] ?? null) === 'object') {
                $this->assertFalse($node['additionalProperties'] ?? true, "additionalProperties manquant : {$path}");
                $this->assertEqualsCanonicalizing(array_keys($node['properties']), $node['required'], "required incomplet : {$path}");
            }
            foreach (['minimum', 'maximum', 'minLength', 'maxLength'] as $forbidden) {
                $this->assertArrayNotHasKey($forbidden, $node, "contrainte non supportée : {$path}");
            }
            foreach ($node['properties'] ?? [] as $k => $child) {
                $check($child, "{$path}.{$k}");
            }
            if (isset($node['items'])) {
                $check($node['items'], "{$path}[]");
            }
            foreach ($node['anyOf'] ?? [] as $i => $child) {
                $check($child, "{$path}|{$i}");
            }
        };
        $check($fake->calls[0]['schema'], 'root');
    }

    public function test_rewrite_returns_clean_html_and_plain_text(): void
    {
        $this->fake([['text' => '<p>Texte <a href="javascript:x()">lien</a><script>x</script></p>'], ['text' => '"Un titre <b>fort</b>"']]);

        $this->actingAs($this->admin)->postJson('/admin/ai/text', ['action' => 'improve', 'format' => 'html', 'text' => '<p>Texte</p>'])
            ->assertOk()->assertJsonPath('text', '<p>Texte <a rel="noopener noreferrer">lien</a></p>');

        $this->actingAs($this->admin)->postJson('/admin/ai/text', ['action' => 'write', 'format' => 'plain', 'instructions' => 'Un titre'])
            ->assertOk()->assertJsonPath('text', 'Un titre fort');
    }

    public function test_translation_creates_a_draft_page_in_the_same_group(): void
    {
        Setting::write(['locales' => ['fr', 'en']]);
        $this->fake([['items' => [
            ['key' => '__title', 'text' => 'Home'],
            ['key' => 'heading001.text', 'text' => 'Welcome'],
            ['key' => 'text000001.html', 'text' => '<p>Our <strong>mission</strong><script>x</script></p>'],
            ['key' => 'unknown.key', 'text' => 'ignored'],
        ]]]);
        $page = $this->page(['status' => 'published']);

        $r = $this->actingAs($this->admin)->postJson("/admin/ai/pages/{$page->id}/translate", ['locale' => 'en'])->assertOk();

        $translation = Page::find($r->json('page.id'));
        $this->assertSame('en', $translation->locale);
        $this->assertSame('draft', $translation->status);
        $this->assertSame('home', $translation->slug);
        $this->assertSame($page->translation_group, $translation->translation_group);
        $widgets = $this->widgetsOf($translation->content);
        $this->assertSame('Welcome', $widgets[0]['settings']['text']);
        $this->assertSame('<p>Our <strong>mission</strong></p>', $widgets[1]['settings']['html']);

        $this->actingAs($this->admin)->postJson("/admin/ai/pages/{$page->id}/translate", ['locale' => 'fr'])->assertUnprocessable();
    }

    public function test_audit_keeps_only_alt_texts_for_real_images(): void
    {
        $this->fake([[
            'score' => 140, 'summary' => 'Bien', 'meta_title' => 'T', 'meta_description' => 'D',
            'issues' => [['severity' => 'high', 'category' => 'seo', 'message' => 'Pas de h1', 'fix' => 'Ajoutez un titre H1']],
            'image_alts' => [['widget_id' => 'image00001', 'alt' => 'Photo'], ['widget_id' => 'nope', 'alt' => 'x']],
        ]]);
        $page = $this->page();

        $r = $this->actingAs($this->admin)->postJson("/admin/ai/pages/{$page->id}/audit", ['content' => $page->content])->assertOk();
        $this->assertSame(100, $r->json('score'));
        // Seules les images de la médiathèque sont envoyées au modèle : ici aucune, donc aucune suggestion retenue.
        $this->assertSame([], $r->json('image_alts'));
        $this->assertCount(1, $r->json('issues'));
    }

    public function test_missing_api_key_gives_a_clear_message(): void
    {
        $page = $this->page();
        $this->fake(configured: false);
        $this->actingAs($this->admin)->postJson("/admin/ai/pages/{$page->id}/generate", ['brief' => 'Une page de test'])
            ->assertUnprocessable()->assertJsonPath('message', fn ($m) => str_contains($m, 'clé API'));
    }

    public function test_refusals_are_reported_in_french_and_logged(): void
    {
        $page = $this->page();
        $this->fake(refuse: true);
        $this->actingAs($this->admin)->postJson("/admin/ai/pages/{$page->id}/generate", ['brief' => 'Une page de test'])
            ->assertUnprocessable()->assertJsonPath('message', 'L’IA a refusé cette demande. Reformulez-la.');
        $this->assertSame(1, AiRequest::where('status', 'refused')->count());
    }

    public function test_api_key_is_encrypted_and_never_sent_to_the_browser(): void
    {
        $this->actingAs($this->admin)->put('/admin/settings', [
            'site_name' => 'ONG', 'primary_color' => '#2563eb', 'secondary_color' => '#0f172a', 'accent_color' => '#f59e0b',
            'font_family' => 'Inter', 'locales' => ['fr'], 'default_locale' => 'fr', 'anthropic_api_key' => 'sk-ant-test123456789',
        ])->assertSessionHasNoErrors();

        $stored = Setting::find('anthropic_api_key')->value;
        $this->assertNotSame('sk-ant-test123456789', $stored);
        $this->assertSame('sk-ant-test123456789', Crypt::decryptString($stored));

        $page = $this->page();
        $html = $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/builder")->assertOk()->getContent();
        $this->assertStringNotContainsString($stored, $html);
        $this->assertStringNotContainsString('anthropic_api_key', $html);
        $this->assertStringNotContainsString('sk-ant-test', $this->actingAs($this->admin)->get('/admin/settings')->getContent());
    }
}
