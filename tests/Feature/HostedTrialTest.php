<?php

namespace Tests\Feature;

use App\Ai\LlmGateway;
use App\Builder\DefaultContent;
use App\Hosting\CurrentSite;
use App\Hosting\Events\SiteDeleted;
use App\Hosting\Events\TrialExpired;
use App\Hosting\HostedBootstrap;
use App\Hosting\LoginLink;
use App\Hosting\Manifest;
use App\Hosting\QuotaExceeded;
use App\Hosting\Quotas;
use App\Hosting\Site;
use App\Models\AiRequest;
use App\Models\Media;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PDO;
use Tests\Fakes\FakeLlm;
use Tests\TestCase;

/** Essai hébergé : provisionnement, règles, commandes, et comportement d'un site d'essai. */
class HostedTrialTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config([
            'hosting.enabled' => true,
            'hosting.path' => $this->sandbox.DIRECTORY_SEPARATOR.'hosting',
            'hosting.domain' => 'localhost',
            'hosting.scheme' => 'http',
            'hosting.port' => 8000,
            'hosting.database.driver' => 'sqlite',
        ]);
        $this->user = User::create(['name' => 'Coopérative Amani', 'email' => 'amani@test.local', 'password' => 'mot-de-passe-2026', 'role' => 'admin', 'locale' => 'fr']);
    }

    // ─── Plateforme ──────────────────────────────────────────────────────

    public function test_a_platform_account_gets_a_working_trial_site_in_one_click(): void
    {
        $this->actingAs($this->user)->get(route('hosting.trial'))
            ->assertOk()->assertSee('Créer mon site d’essai')->assertSee('cooperative-amani');

        $this->post(route('hosting.trial.store'), ['name' => 'Coopérative Amani', 'slug' => 'cooperative'])
            ->assertRedirect(route('hosting.trial'));

        // Création juste après la réponse : base, fichiers, compte administrateur.
        $site = Site::query()->sole();
        $this->assertSame(Site::STATUS_ACTIVE, $site->status, (string) $site->error);
        $this->assertSame('cooperative.localhost', $site->host);
        $this->assertSame('http://cooperative.localhost:8000', $site->url());
        $this->assertEqualsWithDelta(14, now()->diffInDays($site->expires_at), 0.01);

        $dir = $site->directory();
        $this->assertFileExists($dir.'/.env');
        $this->assertFileExists($dir.'/storage/app/installed.lock');
        $this->assertFileDoesNotExist($dir.'/setup.json');
        $this->assertSame('cooperative', trim(file_get_contents(config('hosting.path').'/hosts/cooperative.localhost')));
        $this->assertSame('cooperative', HostedBootstrap::resolve(config('hosting.path'), false, 'Cooperative.localhost:8000'));
        $this->assertSame(40, $site->manifest()->limit('pages'));
        $this->assertSame(Manifest::STATUS_ACTIVE, $site->manifest()->status());
        $this->assertSame(14, $site->manifest()->daysLeft());

        // La base du site contient le propriétaire (même mot de passe) et sa page d'accueil.
        $pdo = new PDO('sqlite:'.$dir.'/database.sqlite');
        $owner = $pdo->query('select * from users')->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('amani@test.local', $owner['email']);
        $this->assertSame('admin', $owner['role']);
        $this->assertSame($this->user->getAuthPassword(), $owner['password']);
        $this->assertSame(1, (int) $pdo->query('select count(*) from pages where is_home = 1')->fetchColumn());
        $this->assertSame('Coopérative Amani', json_decode((string) $pdo->query("select value from settings where key = 'site_name'")->fetchColumn()));
        $this->assertSame(0, (int) $pdo->query("select count(*) from sqlite_master where name = 'hosting_sites'")->fetchColumn(), 'La table de la plateforme ne va pas dans les sites.');
        $owner = $pdo = null; // Windows ne supprime pas un fichier SQLite encore ouvert.

        // La plateforme ne met jamais de valeurs de test ou de la plateforme dans le .env du site.
        $env = file_get_contents($dir.'/.env');
        $this->assertStringContainsString('APP_URL=http://cooperative.localhost:8000', $env);
        $this->assertStringNotContainsString(':memory:', $env);

        $this->get(route('hosting.trial'))->assertOk()->assertSee('Ouvrir mon site')->assertSee('14 jours restants');
        $this->getJson(route('hosting.trial.status'))->assertJson(['status' => 'active']);

        // Un clic : lien signé vers l'administration du site.
        $location = $this->get(route('hosting.trial.open'))->assertRedirect()->headers->get('Location');
        $this->assertStringStartsWith('http://cooperative.localhost:8000'.LoginLink::PATH.'?', $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $q);
        $this->assertTrue(LoginLink::verify($site->manifest(), $q['expires'], $q['nonce'], $q['signature']));
        $this->assertFalse(LoginLink::verify($site->manifest(), $q['expires'], $q['nonce'], str_repeat('0', 64)));

        // Exploitant : prolonger, suspendre, rétablir, mettre à jour, supprimer.
        $end = $site->expires_at;
        $this->artisan('geniuspress:trial', ['action' => 'extend', 'site' => 'cooperative', '--days' => 7])->assertSuccessful();
        $this->assertTrue($site->fresh()->expires_at->equalTo($end->copy()->addDays(7)));
        $this->assertSame(21, $site->fresh()->manifest()->daysLeft());

        $this->artisan('geniuspress:trial', ['action' => 'suspend', 'site' => 'cooperative', '--reason' => 'Contenu frauduleux'])->assertSuccessful();
        $this->assertSame(Manifest::STATUS_SUSPENDED, $site->fresh()->manifest()->status());
        $this->artisan('geniuspress:trial', ['action' => 'resume', 'site' => 'cooperative'])->assertSuccessful();
        $this->assertSame(Manifest::STATUS_ACTIVE, $site->fresh()->manifest()->status());

        $this->artisan('geniuspress:trial', ['action' => 'migrate'])->assertSuccessful()->expectsOutputToContain('cooperative');
        $this->artisan('geniuspress:trial', ['action' => 'list'])->assertSuccessful()->expectsOutputToContain('amani@test.local');

        Event::fake([SiteDeleted::class]);
        $this->artisan('geniuspress:trial', ['action' => 'delete', 'site' => 'cooperative', '--force' => true])->assertSuccessful();
        $site->refresh();
        $this->assertSame(Site::STATUS_DELETED, $site->status);
        $this->assertNull($site->slug);
        $this->assertDirectoryDoesNotExist($dir);
        $this->assertFileDoesNotExist(config('hosting.path').'/hosts/cooperative.localhost');
        Event::assertDispatched(SiteDeleted::class);

        $this->get(route('hosting.trial'))->assertOk()->assertSee('ses données ont été supprimées');
    }

    public function test_trial_rules(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->actingAs($this->user);
        foreach (['ab', 'avec espace', '-tiret', 'www', 'deux--tirets', 'accentué'] as $bad) {
            $this->post(route('hosting.trial.store'), ['name' => 'Essai', 'slug' => $bad])->assertSessionHasErrors('slug');
        }

        // Un seul essai par compte ; adresse déjà prise.
        Site::create(['slug' => 'prise', 'name' => 'Autre', 'account_id' => 'user:999', 'kind' => 'trial', 'status' => Site::STATUS_ACTIVE, 'host' => 'prise.localhost']);
        $this->post(route('hosting.trial.store'), ['name' => 'Essai', 'slug' => 'prise'])->assertSessionHasErrors('slug');
        Site::create(['slug' => 'ancien', 'name' => 'Ancien', 'account_id' => 'user:'.$this->user->id, 'kind' => 'trial', 'status' => Site::STATUS_DELETED]);
        $this->post(route('hosting.trial.store'), ['name' => 'Essai', 'slug' => 'nouveau'])->assertSessionHas('warnings', ['Vous avez déjà utilisé votre essai gratuit.']);

        // Plafond du serveur.
        $other = User::create(['name' => 'Bintou', 'email' => 'bintou@test.local', 'password' => 'mot-de-passe-2026', 'role' => 'editor']);
        config(['hosting.max_sites' => 1]);
        $this->actingAs($other)->get(route('hosting.trial'))->assertOk()->assertSee('Tous nos sites d’essai sont occupés');
        $this->assertSame(1, Site::query()->where('status', '!=', Site::STATUS_DELETED)->count());
    }

    public function test_with_one_certificate_per_site_the_trial_opens_once_its_certificate_is_ready(): void
    {
        config(['hosting.certificates' => 'per-site', 'hosting.domain' => '78-24-248-103.sslip.io', 'hosting.scheme' => 'https', 'hosting.port' => null]);
        $site = Site::create(['slug' => 'cooperative', 'name' => 'Coopérative', 'account_id' => 'user:'.$this->user->id, 'kind' => 'trial', 'status' => Site::STATUS_ACTIVE, 'host' => 'cooperative.78-24-248-103.sslip.io', 'expires_at' => now()->addDays(14)]);
        File::ensureDirectoryExists($site->directory());
        app(\App\Hosting\LocalSiteProvisioner::class)->writeManifest($site);
        $this->actingAs($this->user);

        $this->get(route('hosting.trial'))->assertOk()->assertSee('sécurisation de son adresse')->assertDontSee('Ouvrir mon site');
        $this->getJson(route('hosting.trial.status'))->assertJson(['status' => 'securing']);
        $this->get(route('hosting.trial.open'))->assertRedirect(route('hosting.trial'));

        // Témoin déposé par scripts/hosting/certificats-essais.sh.
        File::ensureDirectoryExists(config('hosting.path').'/certs');
        touch(config('hosting.path').'/certs/cooperative.78-24-248-103.sslip.io');
        $this->getJson(route('hosting.trial.status'))->assertJson(['status' => 'active']);
        $this->get(route('hosting.trial.open'))->assertRedirect();
        $this->assertStringStartsWith('https://cooperative.78-24-248-103.sslip.io/_essai/connexion?', $this->get(route('hosting.trial.open'))->headers->get('Location'));
    }

    public function test_trial_pages_are_hidden_unless_hosting_is_enabled(): void
    {
        config(['hosting.enabled' => false]);
        $this->actingAs($this->user)->get('/essayer')->assertNotFound();
        $this->post('/essayer', ['name' => 'Essai', 'slug' => 'essai-x'])->assertNotFound();
        $this->assertSame(0, Site::query()->count());
    }

    public function test_unknown_trial_addresses_do_not_show_the_platform(): void
    {
        $this->get('http://inconnu.localhost/')->assertNotFound()->assertSee('Site introuvable');
    }

    public function test_bootstrap_only_resolves_registered_sites(): void
    {
        $root = config('hosting.path');
        File::ensureDirectoryExists($root.'/sites/cooperative');
        File::ensureDirectoryExists($root.'/hosts');
        file_put_contents($root.'/sites/cooperative/.env', "APP_KEY=x\n");
        file_put_contents($root.'/hosts/cooperative.localhost', 'cooperative');
        file_put_contents($root.'/hosts/piege.localhost', '../../etc');

        $this->assertSame('cooperative', HostedBootstrap::resolve($root, false, 'cooperative.localhost'));
        $this->assertSame('cooperative', HostedBootstrap::resolve($root, 'cooperative', null));
        $this->assertNull(HostedBootstrap::resolve($root, false, 'autre.localhost'));
        $this->assertNull(HostedBootstrap::resolve($root, false, 'piege.localhost'));
        $this->assertNull(HostedBootstrap::resolve($root, false, '../hosts/cooperative.localhost'));
        $this->assertNull(HostedBootstrap::resolve($root, 'inconnu', null));
        $this->assertNull(HostedBootstrap::resolve($root, false, null));
    }

    public function test_expire_task_closes_trials_then_deletes_them_after_the_grace_period(): void
    {
        Event::fake([TrialExpired::class, SiteDeleted::class]);
        $make = function (string $slug, array $attrs) {
            $site = Site::create($attrs + ['slug' => $slug, 'name' => $slug, 'account_id' => 'user:'.$slug, 'kind' => 'trial', 'host' => $slug.'.localhost']);
            File::ensureDirectoryExists($site->directory());

            return $site;
        };
        $ending = $make('fin-essai', ['status' => Site::STATUS_ACTIVE, 'expires_at' => now()->subHour()]);
        $running = $make('en-cours', ['status' => Site::STATUS_ACTIVE, 'expires_at' => now()->addDay()]);
        $old = $make('ancien', ['status' => Site::STATUS_EXPIRED, 'expires_at' => now()->subDays(40), 'expired_at' => now()->subDays(31)]);
        $paid = $make('payant', ['status' => Site::STATUS_ACTIVE, 'kind' => 'paid', 'expires_at' => now()->subDay()]);

        $this->artisan('geniuspress:trial', ['action' => 'expire'])->assertSuccessful();

        $this->assertSame(Site::STATUS_EXPIRED, $ending->fresh()->status);
        $this->assertSame(Manifest::STATUS_EXPIRED, $ending->fresh()->manifest()->status());
        $this->assertSame(Site::STATUS_ACTIVE, $running->fresh()->status);
        $this->assertSame(Site::STATUS_DELETED, $old->fresh()->status);
        $this->assertDirectoryDoesNotExist($old->directory());
        $this->assertSame(Site::STATUS_ACTIVE, $paid->fresh()->status, 'Un site payant ne suit pas le cycle de l’essai.');
        Event::assertDispatched(TrialExpired::class, 1);
        Event::assertDispatched(SiteDeleted::class, 1);
    }

    // ─── Dans un site d'essai ────────────────────────────────────────────

    private function hosted(array $values = []): Manifest
    {
        $manifest = new Manifest(array_replace([
            'slug' => 'cooperative',
            'kind' => 'trial',
            'status' => Manifest::STATUS_ACTIVE,
            'expires_at' => now()->addDays(5)->toIso8601String(),
            'limits' => ['pages' => 3, 'posts' => 3, 'media_mb' => 1, 'ai_requests' => 2, 'ai_tokens' => 1000],
            'login_secret' => str_repeat('s', 64),
        ], $values));
        $this->app->instance(CurrentSite::class, new CurrentSite($manifest));
        $this->app->forgetInstance(Quotas::class);

        return $manifest;
    }

    private function homePage(): Page
    {
        return Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => Page::STATUS_PUBLISHED, 'published_at' => now(), 'author_id' => $this->user->id, 'content' => DefaultContent::home('Coopérative')]);
    }

    public function test_quotas_of_a_trial_site(): void
    {
        $this->hosted();
        $this->homePage();
        $this->actingAs($this->user);

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Essai gratuit : 5 jours restants.')->assertSee('Pages : 1 / 3');

        $this->post(route('admin.pages.store'), ['title' => 'Deux', 'slug' => 'deux', 'locale' => 'fr'])->assertRedirect();
        $this->post(route('admin.pages.store'), ['title' => 'Trois', 'slug' => 'trois', 'locale' => 'fr'])->assertRedirect();
        $this->post(route('admin.pages.store'), ['title' => 'Quatre', 'slug' => 'quatre', 'locale' => 'fr'])
            ->assertSessionHas('warnings', ['Votre essai est limité à 3 pages. Supprimez une page ou passez à une offre payante.']);
        $this->assertSame(3, Page::query()->count());

        // Médias : 1 Mo au total ; le fichier refusé n'est pas gardé.
        Storage::fake('public');
        $this->postJson(route('admin.media.store'), ['file' => UploadedFile::fake()->image('a.jpg')->size(700)])->assertCreated();
        $this->postJson(route('admin.media.store'), ['file' => UploadedFile::fake()->image('b.jpg')->size(700)])
            ->assertStatus(422)->assertJson(['message' => 'Votre essai est limité à 1 Mo de médias. Supprimez des fichiers ou passez à une offre payante.']);
        $this->assertSame(1, Media::query()->count());
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_ai_credits_of_a_trial_site(): void
    {
        $this->hosted();
        $fake = new FakeLlm([['text' => 'Bonjour'], ['text' => 'Encore']]);
        $this->app->bind(LlmGateway::class, fn () => $fake);
        $this->actingAs($this->user);

        $this->postJson(route('admin.ai.text'), ['action' => 'improve', 'format' => 'plain', 'text' => 'Salut'])->assertOk();
        $this->postJson(route('admin.ai.text'), ['action' => 'improve', 'format' => 'plain', 'text' => 'Salut'])->assertOk();
        $this->assertCount(2, $fake->calls);

        // 2 demandes au plus : la troisième n'atteint pas l'IA.
        $this->postJson(route('admin.ai.text'), ['action' => 'improve', 'format' => 'plain', 'text' => 'Salut'])
            ->assertStatus(422)->assertJson(['message' => 'Les crédits IA de votre essai sont épuisés. Passez à une offre payante pour continuer à utiliser l’assistant.']);
        $this->assertCount(2, $fake->calls);

        // Jetons : 1000 au plus.
        AiRequest::query()->delete();
        AiRequest::create(['user_id' => $this->user->id, 'action' => 'text_improve', 'model' => 'm', 'input_tokens' => 900, 'output_tokens' => 200, 'status' => 'ok']);
        $this->expectException(QuotaExceeded::class);
        app(LlmGateway::class)->json('s', [], []);
    }

    public function test_an_expired_trial_closes_the_public_site_and_keeps_a_read_only_admin(): void
    {
        $this->homePage();
        $this->get('/')->assertOk();

        $this->hosted(['expires_at' => now()->subMinute()->toIso8601String()]);
        $this->get('/')->assertForbidden()->assertSee('Ce site d’essai est terminé');
        $this->get(route('login'))->assertOk();

        $this->actingAs($this->user);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Essai terminé : administration en lecture seule');
        $this->get(route('admin.transfer.export'))->assertOk();
        $this->from(route('admin.settings.edit'))->put(route('admin.settings.update'), ['site_name' => 'Modifié'])
            ->assertRedirect(route('admin.settings.edit'))->assertSessionHas('warnings');
        $this->assertNotSame('Modifié', Setting::read('site_name'));
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_a_suspended_site_is_closed(): void
    {
        $this->homePage();
        $this->hosted(['status' => Manifest::STATUS_SUSPENDED]);
        $this->get('/')->assertStatus(503)->assertSee('Ce site est suspendu');
        $this->actingAs($this->user)->get(route('admin.dashboard'))->assertStatus(503);
    }

    public function test_one_click_login_link_works_once(): void
    {
        $manifest = $this->hosted();
        $site = new Site(['slug' => 'cooperative', 'host' => 'localhost']);
        config(['hosting.port' => null]);
        $link = LoginLink::for($site, $manifest);
        $path = parse_url($link, PHP_URL_PATH).'?'.parse_url($link, PHP_URL_QUERY);

        $this->get($path)->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->user);

        auth()->logout();
        $this->get($path)->assertRedirect(route('login'));
        $this->assertGuest();

        // Hors d'un site hébergé, l'adresse n'existe pas.
        $this->app->instance(CurrentSite::class, new CurrentSite(null));
        $this->get($path)->assertNotFound();
    }
}
