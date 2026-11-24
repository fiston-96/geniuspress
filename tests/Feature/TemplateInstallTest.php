<?php

namespace Tests\Feature;

use App\Ai\LlmGateway;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Setting;
use App\Models\User;
use App\Site\Navigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Fakes\FakeLlm;
use Tests\TestCase;

/** Page Modèles : catalogue, propositions (avec et sans IA), installation, rédaction par l'IA. */
class TemplateInstallTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private static ?string $built = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        if (self::$built === null) {
            self::$built = sys_get_temp_dir().'/gp-catalog-install-'.getmypid();
            $this->artisan('geniuspress:templates', ['--output' => self::$built])->assertSuccessful();
        }
        config(['geniuspress.catalog.path' => self::$built, 'geniuspress.catalog.local' => true]);
        Setting::write(['locales' => ['fr']]);
    }

    private function fake(array $responses = [], bool $configured = true): FakeLlm
    {
        $fake = new FakeLlm($responses, $configured);
        $this->app->instance(LlmGateway::class, $fake);

        return $fake;
    }

    private function profile(array $extra = []): array
    {
        return $extra + ['name' => 'École Lumière', 'description' => 'École primaire et secondaire à Bukavu depuis 2008.', 'city' => 'Bukavu', 'phone' => '+243 81 111 22 33', 'email' => 'info@lumiere.test', 'tone' => 'chaleureux'];
    }

    public function test_catalog_page_lists_templates_for_administrators_only(): void
    {
        $this->fake(configured: false);
        $this->actingAs($this->admin)->get('/admin/templates')->assertOk()
            ->assertSee('Modèles de sites')->assertSee('Horizon')->assertSee('Lumière')
            ->assertSee(url('/catalogue/ong-horizon'), false)
            ->assertSee('Créer mon site avec l’assistant IA');

        $editor = User::create(['name' => 'Ed', 'email' => 'ed@test.local', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->actingAs($editor)->get('/admin/templates')->assertForbidden();
        $this->actingAs($editor)->post('/admin/templates/ong-horizon/install', ['locales' => ['fr'], 'mode' => 'replace'])->assertForbidden();
    }

    public function test_without_ai_templates_are_suggested_from_the_sector(): void
    {
        $this->fake(configured: false);
        $this->actingAs($this->admin)->post('/admin/templates/recommend', $this->profile(['description' => 'Nous sommes une école : élèves, inscriptions, enseignants.']))
            ->assertRedirect(route('admin.templates.index').'#propositions')
            ->assertSessionHas('recommendations', fn ($r) => $r['sector'] === 'ecole' && count($r['items']) === 3
                && collect($r['items'])->every(fn ($i) => str_starts_with($i['id'], 'ecole-')));

        // Le profil est gardé pour l'IA, mais jamais envoyé au navigateur.
        $this->assertSame('École Lumière', Setting::read('site_profile')['name']);
        $this->assertArrayNotHasKey('site_profile', Setting::publicValues());
    }

    public function test_ai_recommendations_are_validated(): void
    {
        $fake = $this->fake([[
            'sector' => 'ecole',
            'items' => [
                ['id' => 'ecole-eveil', 'reason' => 'Couleurs joyeuses pour une école primaire.'],
                ['id' => 'inexistant', 'reason' => 'x'],
                ['id' => 'ecole-eveil', 'reason' => 'doublon'],
            ],
        ]]);

        $this->actingAs($this->admin)->post('/admin/templates/recommend', $this->profile(['sector' => 'ecole']))
            ->assertSessionHas('recommendations', function ($r) {
                $ids = array_column($r['items'], 'id');

                return $ids[0] === 'ecole-eveil' && count($ids) === 3 && count(array_unique($ids)) === 3 && ! in_array('inexistant', $ids, true);
            });
        $this->assertStringContainsString('École Lumière', $fake->calls[0]['content'][0]['text']);
        $this->assertContains('ecole-savoir', $fake->calls[0]['schema']['properties']['items']['items']['properties']['id']['enum']);
    }

    public function test_installing_a_template_in_one_language(): void
    {
        $this->fake(configured: false);
        Page::create(['title' => 'Ancienne accueil', 'slug' => 'accueil', 'status' => 'published', 'is_home' => true, 'content' => ['elements' => []]]);

        $this->actingAs($this->admin)->post('/admin/templates/ecole-savoir/install', ['locales' => ['fr'], 'mode' => 'replace', 'design' => '1', 'fill' => '0'])
            ->assertRedirect(route('admin.pages.index'))->assertSessionHas('status', fn ($m) => str_contains($m, '8 pages'));

        $this->assertSame(8, Page::count());
        $this->assertSame(0, Page::where('locale', 'en')->count());
        $this->assertSame(['fr'], Setting::read('locales'), 'Le français seul a été installé.');
        $this->assertSame('#2563eb', Setting::read('primary_color'));
        $this->assertNotSame('Ancienne accueil', Page::where('is_home', true)->sole()->title);
        $this->get('/')->assertOk();
    }

    public function test_add_mode_keeps_existing_pages_and_design(): void
    {
        $this->fake(configured: false);
        Setting::write(['primary_color' => '#123456']);
        Page::create(['title' => 'Mon accueil', 'slug' => 'accueil', 'status' => 'published', 'is_home' => true, 'content' => ['elements' => []]]);

        $this->actingAs($this->admin)->post('/admin/templates/ong-racines/install', ['locales' => ['fr'], 'mode' => 'add', 'design' => '0'])->assertRedirect();

        $this->assertSame('Mon accueil', Page::where('is_home', true)->sole()->title);
        $this->assertTrue(Page::where('slug', 'accueil-2')->exists());
        $this->assertSame('#123456', Setting::read('primary_color'));
    }

    public function test_install_then_ai_fills_every_page(): void
    {
        Setting::write(['site_profile' => $this->profile()]);
        $this->fake();

        $response = $this->actingAs($this->admin)->post('/admin/templates/ecole-savoir/install', ['locales' => ['fr'], 'mode' => 'replace', 'design' => '1', 'fill' => '1']);
        $ids = Page::orderBy('id')->pluck('id')->all();
        $response->assertRedirect(route('admin.templates.fill', ['pages' => implode(',', $ids)]));
        $this->assertSame('École Lumière', Setting::read('site_name'));
        $this->actingAs($this->admin)->get(route('admin.templates.fill', ['pages' => implode(',', $ids)]))->assertOk()->assertSee(route('admin.ai.fill', $ids[0]), false);

        // Rédaction de la page d'accueil : l'IA reçoit les textes, le profil et les règles du secteur.
        $home = Page::where('is_home', true)->sole();
        $counterKey = null;
        \App\Builder\Renderer::walk($home->content['elements'], function ($n) use (&$counterKey) {
            if ($n['type'] === 'counter' && ! $counterKey) {
                $counterKey = $n['id'].'.number';
            }
        });
        $this->assertNotNull($counterKey, 'L’accueil du modèle contient des chiffres clés.');
        $fake = $this->fake([['items' => [
            ['key' => '__title', 'text' => 'Bienvenue à l’École Lumière'],
            ['key' => '__footer_text', 'text' => 'École Lumière, Bukavu.'],
            ['key' => $counterKey, 'text' => 'inconnu'],
            ['key' => 'cle.inventee', 'text' => 'ignorée'],
        ]]]);

        $this->actingAs($this->admin)->postJson(route('admin.ai.fill', $home))->assertOk()->assertJsonPath('page.title', 'Bienvenue à l’École Lumière');

        $prompt = $fake->calls[0]['system'];
        $this->assertStringContainsString('École Lumière', $prompt);
        $this->assertStringContainsString('Never invent', $prompt);
        $this->assertStringContainsString('accreditation or approval numbers', $prompt, 'Règles du secteur « écoles » (profil sans secteur : deviné ou générique).');
        $this->assertStringContainsString('__footer_text', $fake->calls[0]['content'][0]['text']);

        $home->refresh();
        $this->assertSame('Bienvenue à l’École Lumière', $home->title);
        $counter = null;
        \App\Builder\Renderer::walk($home->content['elements'], function ($n) use ($counterKey, &$counter) {
            if ($n['id'].'.number' === $counterKey) {
                $counter = $n['settings']['number'];
            }
        });
        $this->assertSame(0, $counter, 'Un chiffre inconnu devient 0, jamais une valeur inventée.');
        $this->assertSame('École Lumière, Bukavu.', Navigation::all()['locales']['fr']['footer_text']);
        $this->assertSame('Avant l’assistant IA', PageRevision::where('page_id', $home->id)->latest('id')->first()->note);
    }

    public function test_fill_requires_the_questionnaire(): void
    {
        $this->fake();
        $page = Page::create(['title' => 'P', 'slug' => 'p', 'content' => ['elements' => []]]);
        $this->actingAs($this->admin)->postJson(route('admin.ai.fill', $page))->assertStatus(422);
    }

    public function test_a_tampered_template_is_refused(): void
    {
        $this->fake(configured: false);
        $copy = sys_get_temp_dir().'/gp-catalog-tampered-'.getmypid();
        @mkdir($copy);
        copy(self::$built.'/index.json', $copy.'/index.json');
        file_put_contents($copy.'/ong-horizon.zip', 'contenu modifié');
        config(['geniuspress.catalog.path' => $copy]);

        $this->actingAs($this->admin)->post('/admin/templates/ong-horizon/install', ['locales' => ['fr'], 'mode' => 'replace'])
            ->assertSessionHasErrors('catalog');
        $this->assertSame(0, Page::count());
    }

    public function test_online_catalog_is_downloaded_and_verified(): void
    {
        $this->fake(configured: false);
        config(['geniuspress.catalog.local' => false, 'geniuspress.catalog.url' => 'https://catalogue.test/v1']);
        Http::fake([
            'catalogue.test/v1/index.json' => Http::response(file_get_contents(self::$built.'/index.json')),
            'catalogue.test/v1/tech-nova.zip' => Http::response(file_get_contents(self::$built.'/tech-nova.zip')),
        ]);

        $this->actingAs($this->admin)->get('/admin/templates')->assertOk()->assertSee('https://geniuspress.site/catalogue/tech-nova', false);
        $this->actingAs($this->admin)->post('/admin/templates/tech-nova/install', ['locales' => ['fr', 'en'], 'mode' => 'replace', 'design' => '1'])->assertRedirect(route('admin.pages.index'));
        $this->assertSame(16, Page::count());
        $this->assertSame(['fr', 'en'], Setting::read('locales'));
    }
}
