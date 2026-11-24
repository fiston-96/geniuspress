<?php

namespace Tests\Feature;

use App\Install\EnvironmentFile;
use App\Install\InstallState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Étape « base de données » depuis zéro, avec SQLite dans un dossier
 * temporaire. Pas de RefreshDatabase : la base de départ est vide.
 */
class InstallerDatabaseStepTest extends TestCase
{
    public function test_sqlite_database_is_created_configured_and_migrated(): void
    {
        $this->withoutVite();
        $this->get('/install')->assertOk()->assertSee(route('install.database'), false);
        $this->get('/install/site')->assertRedirect(route('install.database'));

        $this->post('/install/database', ['driver' => 'sqlite'])->assertRedirect(route('install.site'));

        // Tables créées dans le fichier SQLite choisi…
        $this->assertFileExists(config('geniuspress.sqlite_path'));
        $this->assertSame(config('geniuspress.sqlite_path'), DB::connection()->getDatabaseName());
        $this->assertTrue(Schema::hasTable('pages'));
        $this->assertTrue(Schema::hasTable('users'));

        // …mais le .env n'est écrit qu'à la fin (« php artisan serve » redémarre à chaque modification).
        $env = app(EnvironmentFile::class);
        $this->assertNull($env->get('DB_CONNECTION'));
        $this->assertSame('sqlite', app(InstallState::class)->pendingDatabase()['driver']);

        // Étape 3 : tout est écrit en une fois, puis le fichier temporaire disparaît.
        $this->post('/install/site', [
            'site_name' => 'Hors ligne', 'locale' => 'fr', 'name' => 'Amani', 'email' => 'amani@test.local',
            'password' => 'motdepasse123', 'password_confirmation' => 'motdepasse123', 'start' => 'welcome',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertSame('sqlite', $env->get('DB_CONNECTION'));
        $this->assertSame(config('geniuspress.sqlite_path'), $env->get('DB_DATABASE'));
        $this->assertSame('database', $env->get('SESSION_DRIVER'));
        $this->assertNull(app(InstallState::class)->pendingDatabase());
        $this->assertFileExists(config('geniuspress.install_lock'));

        DB::disconnect();
    }
}
