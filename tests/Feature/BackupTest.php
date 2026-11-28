<?php

namespace Tests\Feature;

use App\Backup\Backups;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Scheduling\WebCron;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/** Sauvegardes complètes, restauration, tâches planifiées sans cron, paquet téléchargeable. */
class BackupTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@ong.test', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['locales' => ['fr'], 'site_name' => 'Avant']);
        // Stockage isolé : la restauration remplace storage/app/public, jamais celui du vrai site.
        File::ensureDirectoryExists($this->sandbox.'/storage/app/public');
        $this->app->useStoragePath($this->sandbox.'/storage');
    }

    public function test_backup_and_restore_bring_back_database_and_files(): void
    {
        Page::create(['title' => 'Page gardée', 'slug' => 'gardee', 'status' => 'published', 'locale' => 'fr']);
        File::ensureDirectoryExists(storage_path('app/public/uploads'));
        file_put_contents(storage_path('app/public/uploads/test-sauvegarde.txt'), 'contenu sauvegardé');

        $this->actingAs($this->admin)->post(route('admin.backups.store'))->assertSessionHas('status');
        $backup = app(Backups::class)->all()[0];
        $this->assertSame('manuelle', $backup['kind']);
        $this->assertFileExists(storage_path('app/backups/.htaccess'));

        // Le site change après la sauvegarde.
        Page::query()->delete();
        Page::create(['title' => 'Page ajoutée après', 'slug' => 'apres', 'status' => 'published', 'locale' => 'fr']);
        Setting::write(['site_name' => 'Après']);
        file_put_contents(storage_path('app/public/uploads/test-sauvegarde.txt'), 'modifié');

        $this->post(route('admin.backups.restore', $backup['name']))->assertRedirect(route('login'));
        $this->assertSame(['Page gardée'], Page::query()->pluck('title')->all());
        $this->assertSame('Avant', Setting::read('site_name'));
        $this->assertSame('contenu sauvegardé', file_get_contents(storage_path('app/public/uploads/test-sauvegarde.txt')));
        $this->assertTrue(User::query()->where('email', 'admin@ong.test')->exists(), 'Comptes restaurés.');
        $this->assertSame('avant-restauration', app(Backups::class)->all()[0]['kind'], 'Copie de l’état précédent.');
        $this->assertGuest();
    }

    public function test_only_real_backups_are_accepted_and_names_are_checked(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('admin.backups.download', 'geniuspress-sauvegarde-20260101-000000-manuelle.zip'))->assertNotFound();
        $this->get('/admin/sauvegardes/..%2F..%2F.env')->assertNotFound();

        $zip = new \ZipArchive;
        $tmp = tempnam(sys_get_temp_dir(), 'gp-').'.zip';
        $zip->open($tmp, \ZipArchive::CREATE);
        $zip->addFromString('manifest.json', json_encode(['format' => 'geniuspress-export']));
        $zip->close();
        $this->post(route('admin.backups.upload'), ['backup' => new UploadedFile($tmp, 'export.zip', 'application/zip', null, true)])
            ->assertSessionHasErrors('backup');
        $this->assertSame([], app(Backups::class)->all(), 'Aucune restauration, donc aucune copie de sécurité.');

        // Un éditeur n'a pas accès aux sauvegardes.
        $editor = User::create(['name' => 'E', 'email' => 'e@ong.test', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->actingAs($editor)->post(route('admin.backups.store'))->assertForbidden();
    }

    public function test_automatic_backups_follow_the_setting_and_are_pruned(): void
    {
        $backups = app(Backups::class);
        $this->assertNull($backups->runScheduled(), 'Désactivées par défaut.');

        Setting::write(['backup_frequency' => 'daily', 'backup_keep' => 2]);
        $this->assertNotNull($backups->runScheduled());
        $this->assertNull($backups->runScheduled(), 'Déjà faite aujourd’hui.');

        // Trois anciennes sauvegardes automatiques : seules les 2 plus récentes sont gardées.
        foreach ([3, 2, 1] as $days) {
            $name = 'geniuspress-sauvegarde-'.now()->subDays($days)->format('Ymd-His').'-auto.zip';
            copy($backups->directory().'/'.$backups->all()[0]['name'], $backups->directory().'/'.$name);
            touch($backups->directory().'/'.$name, now()->subDays($days)->timestamp);
        }
        $backups->prune();
        $this->assertCount(2, array_filter($backups->all(), fn ($b) => $b['kind'] === 'auto'));
    }

    public function test_scheduler_runs_from_the_secret_url_and_settings_page(): void
    {
        $this->get('/cron/'.str_repeat('a', 40))->assertNotFound();
        $this->get(WebCron::url())->assertOk()->assertSee('OK');
        $this->assertSame('url', Setting::read('cron_last_source'));
        $this->assertArrayNotHasKey('cron_token', Setting::publicValues(), 'Jeton jamais envoyé au navigateur.');

        $this->actingAs($this->admin)->get(route('admin.transfer.index'))->assertOk()
            ->assertSee('Sauvegardes complètes')->assertSee('Tâches planifiées')->assertSee(WebCron::url(), false);
        $this->put(route('admin.backups.cron'), ['cron_visits' => '0', 'action' => 'save'])->assertSessionHas('status');
        $this->assertFalse((bool) Setting::read('cron_visits'));
        $old = WebCron::token();
        $this->put(route('admin.backups.cron'), ['action' => 'renew']);
        $this->assertNotSame($old, WebCron::token());
    }

    public function test_package_download_serves_the_latest_zip(): void
    {
        $dir = base_path('dist');
        $hadDir = is_dir($dir);
        File::ensureDirectoryExists($dir);
        $existing = glob($dir.'/geniuspress-*.zip') ?: [];
        if ($existing) {
            $this->get(route('package.download'))->assertOk()->assertDownload();

            return;
        }
        file_put_contents($dir.'/geniuspress-9.9.9.zip', 'zip');
        try {
            $this->get(route('package.download'))->assertOk()->assertDownload('geniuspress-9.9.9.zip');
        } finally {
            File::delete($dir.'/geniuspress-9.9.9.zip');
            if (! $hadDir) {
                File::deleteDirectory($dir);
            }
        }
    }
}
