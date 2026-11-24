<?php

namespace Tests\Feature;

use App\Install\EnvironmentFile;
use App\Install\Installer;
use App\Install\InstallState;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function env(): EnvironmentFile
    {
        return app(EnvironmentFile::class);
    }

    private function site(array $overrides = []): array
    {
        return $overrides + [
            'site_name' => 'ONG Espoir', 'locale' => 'fr', 'name' => 'Amani', 'email' => 'amani@test.local',
            'password' => 'motdepasse123', 'password_confirmation' => 'motdepasse123', 'start' => 'welcome',
        ];
    }

    public function test_everything_redirects_to_the_installer_until_it_is_done(): void
    {
        $this->get('/')->assertRedirect('/install');
        $this->get('/admin/login')->assertRedirect('/install');
        $this->get('/install')->assertOk()->assertSee('Vérifions d’abord')->assertSee('PHP 8.3');
    }

    public function test_server_check_continues_to_the_site_step_when_the_database_is_ready(): void
    {
        $this->get('/install')->assertOk()->assertSee(route('install.site'), false);
        $this->get('/install/site')->assertOk()->assertSee('Compte administrateur');
    }

    public function test_site_step_creates_the_site_then_locks_the_installer(): void
    {
        $this->post('/install/site', $this->site(['locale' => 'en', 'start' => 'blank']))
            ->assertRedirect(route('admin.dashboard'));

        $admin = User::sole();
        $this->assertTrue($admin->isAdmin());
        $this->assertAuthenticatedAs($admin);
        $this->assertSame(['en'], Setting::read('locales'));

        $home = Page::sole();
        $this->assertTrue($home->is_home);
        $this->assertSame('en', $home->locale);
        $this->assertSame('home', $home->slug);
        $this->assertSame([], $home->content['elements']);

        // Verrou écrit, adresse du site et mode enregistrés dans .env.
        $this->assertFileExists(config('geniuspress.install_lock'));
        $this->assertSame(rtrim(config('app.url'), '/'), $this->env()->get('APP_URL'));
        $this->assertSame('local', $this->env()->get('APP_ENV'));

        // L'installateur n'est plus accessible, même pour recréer un compte.
        $this->get('/install')->assertNotFound();
        $this->get('/install/database')->assertNotFound();
        $this->post('/install/site', $this->site(['email' => 'pirate@test.local']))->assertNotFound();
        $this->assertSame(1, User::count());
    }

    public function test_a_public_server_is_switched_to_production_mode(): void
    {
        app(Installer::class)->createSite($this->site(['url' => 'https://ong-espoir.org']));

        $this->assertSame('production', $this->env()->get('APP_ENV'));
        $this->assertSame('false', $this->env()->get('APP_DEBUG'));
        $this->assertSame('https://ong-espoir.org', $this->env()->get('APP_URL'));
    }

    public function test_site_step_validates_the_form(): void
    {
        $this->from('/install/site')->post('/install/site', $this->site(['password' => 'court', 'password_confirmation' => 'court', 'locale' => 'xx']))
            ->assertSessionHasErrors(['password', 'locale']);
        $this->assertSame(0, User::count());
    }

    public function test_existing_installations_without_lock_are_detected(): void
    {
        User::create(['name' => 'Ancien', 'email' => 'old@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);

        $this->assertTrue(app(InstallState::class)->isInstalled());
        $this->assertFileExists(config('geniuspress.install_lock'));
        $this->get('/install')->assertNotFound();
    }

    public function test_mysql_errors_are_explained(): void
    {
        $error = app(Installer::class)->testDatabase([
            'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 1, 'database' => 'geniuspress', 'username' => 'root', 'password' => '',
        ]);
        $this->assertStringContainsString('Impossible de joindre le serveur MySQL 127.0.0.1 (port 1)', $error);

        $this->from('/install/database')->post('/install/database', ['driver' => 'mysql', 'host' => 'bad host;dbname=x', 'port' => 3306, 'database' => 'x', 'username' => 'root'])
            ->assertSessionHasErrors('host');
    }

    public function test_the_installer_language_can_be_changed_before_any_account_exists(): void
    {
        $this->from('/install')->get('/install/language/en')->assertRedirect('/install');
        $this->get('/install')->assertSee('Welcome to GeniusPress');
    }

    public function test_command_line_installation(): void
    {
        $this->artisan('geniuspress:install', [
            '--site-name' => 'Coopérative Kivu', '--locale' => 'sw', '--admin-name' => 'Neema',
            '--admin-email' => 'neema@test.local', '--admin-password' => 'motdepasse123', '--url' => 'https://kivu.coop',
        ])->assertSuccessful();

        $this->assertSame('neema@test.local', User::sole()->email);
        $this->assertSame(['sw'], Setting::read('locales'));
        $this->assertFileExists(config('geniuspress.install_lock'));

        $this->artisan('geniuspress:install')->expectsOutputToContain('déjà installé')->assertSuccessful();
    }

    /** Utilisé par Docker : tout vient de l'environnement, aucun mot de passe dans la commande. */
    public function test_command_line_installation_reads_environment_variables(): void
    {
        $vars = ['GP_SITE_NAME' => 'Depuis Docker', 'GP_SITE_LOCALE' => 'en', 'GP_ADMIN_NAME' => 'Docker',
            'GP_ADMIN_EMAIL' => 'docker@test.local', 'GP_ADMIN_PASSWORD' => 'motdepasse123', 'APP_URL' => 'https://docker.test'];
        foreach ($vars as $k => $v) {
            putenv("{$k}={$v}");
        }

        try {
            $this->artisan('geniuspress:install', ['--no-interaction' => true])->assertSuccessful();
        } finally {
            foreach (array_keys($vars) as $k) {
                putenv($k);
            }
        }

        $this->assertSame('docker@test.local', User::sole()->email);
        $this->assertSame(['en'], Setting::read('locales'));
        $this->assertSame('https://docker.test', $this->env()->get('APP_URL'));
    }

    public function test_environment_file_keeps_other_lines_and_escapes_values(): void
    {
        file_put_contents($this->env()->path(), "APP_NAME=GeniusPress\n# DB_HOST=127.0.0.1\nDB_PORT=3306\n");
        $values = [
            'DB_HOST' => 'db.exemple.org',
            'DB_PASSWORD' => 'p@ss w0rd#$HOME\'"\\',
            'DB_USERNAME' => 'user name',
            'APP_DEBUG' => false,
        ];
        $this->env()->set($values);

        $content = file_get_contents($this->env()->path());
        $this->assertStringContainsString("APP_NAME=GeniusPress\n", $content);
        $this->assertStringContainsString("DB_HOST=db.exemple.org\n", $content);
        $this->assertStringNotContainsString('# DB_HOST', $content);
        $this->assertStringContainsString('APP_DEBUG=false', $content);

        // Relecture par le même chargeur que Laravel (phpdotenv) : valeurs intactes.
        $parsed = \Dotenv\Dotenv::parse($content);
        $this->assertSame('p@ss w0rd#$HOME\'"\\', $parsed['DB_PASSWORD']);
        $this->assertSame('user name', $parsed['DB_USERNAME']);
        $this->assertSame('3306', $parsed['DB_PORT']);
    }
}
