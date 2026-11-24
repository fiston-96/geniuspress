<?php

namespace Tests\Feature;

use App\Builder\WidgetRegistry;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Plugins\Hooks;
use App\Plugins\PluginManager;
use Fixtures\Quote\Plugin as QuotePlugin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PluginSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Hooks::flush();
        $this->usePluginDirectory(base_path('tests/Fixtures/plugins'));
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
    }

    private function usePluginDirectory(string $dir): void
    {
        config(['geniuspress.plugins_path' => $dir]);
        $this->app->forgetInstance(PluginManager::class);
        $this->app->forgetInstance(WidgetRegistry::class);
    }

    private function quotePage(): Page
    {
        return Page::create(['title' => 'Citations', 'slug' => 'citations', 'status' => 'published', 'content' => ['sections' => [[
            'id' => 'section0001', 'columns' => [['id' => 'column0001', 'widgets' => [
                ['id' => 'quote00001', 'type' => 'fixture-quote', 'settings' => ['text' => 'Ensemble <b>!</b>', 'accent' => '#ff0000', 'evil' => 'x']],
            ]]],
        ]]]]);
    }

    public function test_plugins_are_discovered_with_their_problems(): void
    {
        $plugins = app(PluginManager::class)->discover();

        $this->assertSame(['bad-manifest', 'broken-boot', 'fixture-quote', 'from-the-future'], array_keys($plugins));
        $this->assertTrue($plugins['fixture-quote']->isValid());
        $this->assertFalse($plugins['bad-manifest']->isValid());
        $this->assertSame(['builder.js'], $plugins['fixture-quote']->builderScripts, 'Les chemins avec « .. » sont ignorés.');
        $this->assertFalse($plugins['from-the-future']->isCompatibleWith('0.1.0'));

        $this->actingAs($this->admin)->get('/admin/plugins')->assertOk()
            ->assertSee('Fixture Quote')->assertSee('plugin.json est illisible');
    }

    public function test_activated_plugin_adds_widget_hooks_routes_and_assets(): void
    {
        $page = $this->quotePage();

        $this->actingAs($this->admin)->post('/admin/plugins/fixture-quote/activate')->assertSessionHas('status');
        $this->assertSame(['fixture-quote'], Setting::read('active_plugins'));

        // Rendu du widget : réglages validés, texte échappé.
        auth()->logout();
        $html = $this->get('/citations')->assertOk()->getContent();
        $this->assertStringContainsString('<q class="fixture">Ensemble &lt;b&gt;!&lt;/b&gt;</q>', $html);
        $this->assertStringContainsString('#gp-quote00001{color:#ff0000}', $html);
        $this->assertStringContainsString('<meta name="fixture" content="ok">', $html);
        $this->assertStringContainsString('/plugin-assets/fixture-quote/site.css', $html);

        // Route du plugin, préfixée.
        $this->get('/plugins/fixture-quote/hello')->assertOk()->assertSee('bonjour du plugin');

        // Fichiers publics : seulement le dossier public/ et des types connus.
        $this->get('/plugin-assets/fixture-quote/builder.js')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringStartsWith('text/javascript', $this->get('/plugin-assets/fixture-quote/builder.js')->headers->get('Content-Type'));
        $this->get('/plugin-assets/fixture-quote/../plugin.json')->assertNotFound();
        $this->get('/plugin-assets/fixture-quote/..%2Fsecret.txt')->assertNotFound();
        $this->get('/plugin-assets/broken-boot/plugin.json')->assertNotFound();

        // Le builder charge le script du plugin et connaît ses règles.
        $builder = $this->actingAs($this->admin)->get("/admin/pages/{$page->id}/builder")->getContent();
        $this->assertStringContainsString('/plugin-assets/fixture-quote/builder.js', $builder);
        $this->assertStringContainsString('Fixture quote', $builder);

        // Événement de publication.
        QuotePlugin::$published = [];
        $this->putJson("/admin/pages/{$page->id}/content", ['content' => $page->content, 'publish' => true])->assertOk();
        $this->assertSame([$page->id], QuotePlugin::$published);
    }

    public function test_deactivating_keeps_content_but_hides_the_widget(): void
    {
        $page = $this->quotePage();
        $this->actingAs($this->admin)->post('/admin/plugins/fixture-quote/activate');
        $this->actingAs($this->admin)->post('/admin/plugins/fixture-quote/deactivate');
        $this->assertSame([], Setting::read('active_plugins'));

        // Nouvelle requête : le plugin n'est plus chargé.
        $this->app->forgetInstance(PluginManager::class);
        $this->app->forgetInstance(WidgetRegistry::class);
        Hooks::flush();

        $this->putJson("/admin/pages/{$page->id}/content", ['content' => $page->content])->assertOk();
        $this->assertSame('fixture-quote', $this->widgetsOf($page->fresh()->editableContent())[0]['type']);

        auth()->logout();
        $this->get('/citations')->assertOk()->assertDontSee('class="fixture"', false);
    }

    public function test_a_plugin_that_crashes_is_refused_or_disabled_automatically(): void
    {
        $this->actingAs($this->admin)->post('/admin/plugins/broken-boot/activate')
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Erreur volontaire'));
        $this->assertSame([], Setting::read('active_plugins', []));

        // Plugin marqué actif (ex. mis à jour avec un bug) : désactivé au démarrage, le site reste en ligne.
        Setting::write(['active_plugins' => ['broken-boot']]);
        app(PluginManager::class)->bootActive();
        $this->assertSame([], Setting::read('active_plugins'));
        $this->assertStringContainsString('Erreur volontaire', Setting::read('plugin_errors')['broken-boot']);

        $this->actingAs($this->admin)->post('/admin/plugins/from-the-future/activate')
            ->assertSessionHas('error', fn ($m) => str_contains($m, '>=9.0'));
    }

    public function test_only_admins_manage_plugins(): void
    {
        $editor = User::create(['name' => 'Ed', 'email' => 'e@test.local', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->actingAs($editor)->get('/admin/plugins')->assertForbidden();
        $this->actingAs($editor)->post('/admin/plugins/fixture-quote/activate')->assertForbidden();
    }

    public function test_make_plugin_generates_a_plugin_that_works(): void
    {
        $dir = storage_path('framework/testing/plugins-'.uniqid());
        File::ensureDirectoryExists($dir);
        $this->usePluginDirectory($dir);

        try {
            $this->artisan('geniuspress:make-plugin', ['slug' => 'temoignage', '--name' => 'Témoignage'])->assertSuccessful();
            $this->artisan('geniuspress:make-plugin', ['slug' => 'text'])->assertFailed();

            $this->actingAs($this->admin)->post('/admin/plugins/temoignage/activate')->assertSessionHas('status');
            $this->assertTrue(app(WidgetRegistry::class)->has('temoignage'));

            $html = app(\App\Builder\Renderer::class)->render(['sections' => [['columns' => [['widgets' => [
                ['type' => 'temoignage', 'settings' => ['text' => 'Merci !', 'author' => 'Amani']],
            ]]]]]])['html'];
            $this->assertStringContainsString('<blockquote class="temoignage"><p>Merci !</p><footer>Amani</footer></blockquote>', $html);
        } finally {
            File::deleteDirectory($dir);
        }
    }
}
