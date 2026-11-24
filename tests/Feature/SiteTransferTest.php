<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Transfer\SiteExporter;
use App\Transfer\SiteImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class SiteTransferTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
    }

    /** Un petit site : accueil FR + traduction EN, une page en brouillon, une image utilisée partout. */
    private function buildSite(): Media
    {
        Setting::write([
            'site_name' => 'ONG Espoir', 'primary_color' => '#123456', 'locales' => ['fr', 'en'],
            'anthropic_api_key' => Crypt::encryptString('sk-ant-secret-ne-pas-exporter'), 'anthropic_api_key_hint' => '…ret',
        ]);

        $file = UploadedFile::fake()->image('enfants.jpg', 40, 30);
        $path = Storage::disk('public')->putFileAs('uploads/2026/11', $file, 'enfants-abc123.jpg');
        $media = Media::create(['disk' => 'public', 'path' => $path, 'original_name' => 'enfants.jpg', 'mime_type' => 'image/jpeg', 'size' => $file->getSize(), 'width' => 40, 'height' => 30, 'alt' => 'Des enfants']);
        $url = $media->url;

        $group = (string) Str::uuid();
        $content = ['sections' => [[
            'id' => 'section0001', 'settings' => ['background_image' => $url],
            'columns' => [['id' => 'column0001', 'widgets' => [
                ['id' => 'image00001', 'type' => 'image', 'settings' => ['media_id' => $media->id, 'url' => $url, 'alt' => 'Enfants']],
                ['id' => 'text000001', 'type' => 'text', 'settings' => ['html' => '<p><a href="'.$url.'">Photo</a></p>']],
            ]]],
        ]]];
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'locale' => 'fr', 'translation_group' => $group, 'status' => 'published', 'is_home' => true, 'content' => $content]);
        Page::create(['title' => 'Home', 'slug' => 'home', 'locale' => 'en', 'translation_group' => $group, 'status' => 'published', 'is_home' => true, 'content' => $content]);
        Page::create(['title' => 'Projets', 'slug' => 'projets', 'locale' => 'fr', 'status' => 'published',
            'content' => ['sections' => []], 'draft_content' => ['sections' => [['id' => 'draftsect1', 'columns' => [['id' => 'draftcol01', 'widgets' => []]]]]]]);

        return $media;
    }

    private function wipeSite(): void
    {
        Page::withTrashed()->forceDelete();
        foreach (Media::all() as $m) {
            Storage::disk('public')->delete($m->path);
            $m->delete();
        }
        Setting::query()->delete();
        Setting::write([]);
    }

    public function test_export_then_import_restores_pages_media_and_settings(): void
    {
        $oldMedia = $this->buildSite();
        $oldUrl = $oldMedia->url;
        $zip = app(SiteExporter::class)->export($this->sandbox.'/export.zip');

        // Jamais de secrets dans l'export.
        $archive = new ZipArchive;
        $archive->open($zip);
        $this->assertStringNotContainsString('anthropic_api_key', $archive->getFromName('settings.json'));
        $this->assertStringNotContainsString('a@test.local', implode('', array_map(fn ($i) => $archive->getFromIndex($i), range(0, $archive->numFiles - 1))));
        $archive->close();

        $this->wipeSite();
        $report = app(SiteImporter::class)->import($zip, $this->admin, replace: true);

        $this->assertSame(3, $report['pages']);
        $this->assertSame(1, $report['media']);
        $this->assertSame([], $report['warnings']);

        // Réglages
        $this->assertSame('ONG Espoir', Setting::read('site_name'));
        $this->assertSame('#123456', Setting::read('primary_color'));
        $this->assertNull(Setting::read('anthropic_api_key'));

        // Médias : nouveau fichier, nouvel identifiant, toutes les références réécrites.
        $media = Media::sole();
        Storage::disk('public')->assertExists($media->path);
        $this->assertNotSame($oldMedia->path, $media->path);
        $this->assertSame('Des enfants', $media->alt);

        $home = Page::where('locale', 'fr')->where('is_home', true)->sole();
        $section = $home->content['elements'][0];
        $widgets = $this->widgetsOf($home->content);
        $this->assertSame($media->url, $section['settings']['background_image']);
        $this->assertSame($media->id, $widgets[0]['settings']['media_id']);
        $this->assertSame($media->url, $widgets[0]['settings']['url']);
        $this->assertStringContainsString($media->url, $widgets[1]['settings']['html']);
        $this->assertStringNotContainsString($oldUrl, json_encode($home->content));

        // Traductions toujours liées entre elles, brouillons conservés.
        $en = Page::where('locale', 'en')->sole();
        $this->assertSame($home->translation_group, $en->translation_group);
        $this->assertTrue($en->is_home);
        $this->assertNotNull(Page::where('slug', 'projets')->sole()->draft_content);

        // Et le site s'affiche.
        $this->get('/')->assertOk()->assertSee($media->url, false);
    }

    public function test_merge_mode_keeps_the_current_home_page_and_renames_conflicts(): void
    {
        $this->buildSite();
        $zip = app(SiteExporter::class)->export($this->sandbox.'/export.zip');
        $before = Page::count();

        app(SiteImporter::class)->import($zip, $this->admin, replace: false, withSettings: false);

        $this->assertSame($before * 2, Page::count());
        $this->assertSame('accueil', Page::where('locale', 'fr')->where('is_home', true)->sole()->slug);
        $copy = Page::where('slug', 'accueil-2')->sole();
        $this->assertFalse($copy->is_home);
        // Une copie forme son propre groupe de traductions, distinct de l'original.
        $this->assertNotSame(Page::where('slug', 'accueil')->sole()->translation_group, $copy->translation_group);
    }

    public function test_malicious_archives_are_neutralised(): void
    {
        $zipPath = $this->sandbox.'/evil.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('manifest.json', json_encode(['format' => 'geniuspress-site', 'format_version' => 1]));
        $zip->addFromString('settings.json', json_encode(['site_name' => 'Pirate', 'primary_color' => 'red;}body{display:none', 'anthropic_api_key' => 'x', 'locales' => ['fr', 'xx']]));
        $zip->addFromString('pages.json', json_encode([
            ['title' => 'Piège', 'slug' => '../admin', 'locale' => 'fr', 'status' => 'published', 'content' => ['sections' => [['columns' => [['widgets' => [
                ['type' => 'text', 'settings' => ['html' => '<p>Ok<script>alert(1)</script></p>']],
                ['type' => 'button', 'settings' => ['text' => 'Clic', 'url' => 'javascript:alert(1)']],
            ]]]]]]],
            ['title' => 'Langue inconnue', 'slug' => 'x', 'locale' => 'klingon', 'content' => []],
        ]));
        $zip->addFromString('media.json', json_encode([
            ['id' => 1, 'path' => 'x.php', 'file' => '../evil.php'],
            ['id' => 2, 'path' => 'shell.php', 'file' => 'media/shell.php'],
            ['id' => 3, 'path' => 'x.svg', 'file' => 'media/x.svg'],
        ]));
        $zip->addFromString('media/shell.php', '<?php echo "pirate";');
        $zip->addFromString('media/x.svg', '<svg onload="alert(1)"></svg>');
        $zip->close();

        $report = app(SiteImporter::class)->import($zipPath, $this->admin);

        $this->assertSame(0, Media::count());
        $this->assertCount(4, $report['warnings']);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $page = Page::sole();
        $this->assertSame('page-admin', $page->slug);
        $widgets = $this->widgetsOf($page->content);
        $this->assertSame('<p>Ok</p>', $widgets[0]['settings']['html']);
        $this->assertArrayNotHasKey('url', $widgets[1]['settings']);

        $this->assertSame('Pirate', Setting::read('site_name'));
        $this->assertNotSame('red;}body{display:none', Setting::read('primary_color'));
        $this->assertNull(Setting::read('anthropic_api_key'));
        $this->assertNotContains('xx', Setting::read('locales'));
    }

    public function test_invalid_files_are_rejected_with_a_clear_message(): void
    {
        file_put_contents($this->sandbox.'/pas-un-zip.zip', 'bonjour');
        $this->expectExceptionMessage('n’est pas une archive zip valide');
        app(SiteImporter::class)->import($this->sandbox.'/pas-un-zip.zip', $this->admin);
    }

    public function test_admin_pages_and_permissions(): void
    {
        $this->buildSite();

        $download = $this->actingAs($this->admin)->get('/admin/transfer/export');
        $download->assertOk()->assertHeader('Content-Type', 'application/zip');

        $editor = User::create(['name' => 'Ed', 'email' => 'e@test.local', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->actingAs($editor)->get('/admin/transfer')->assertForbidden();
        $this->actingAs($editor)->get('/admin/transfer/export')->assertForbidden();

        $zip = app(SiteExporter::class)->export($this->sandbox.'/export.zip');
        $this->actingAs($this->admin)->get('/admin/transfer')->assertOk()->assertSee('Exporter le site');
        $this->actingAs($this->admin)
            ->post('/admin/transfer/import', ['archive' => new UploadedFile($zip, 'export.zip', 'application/zip', null, true), 'mode' => 'merge'])
            ->assertSessionHas('status', fn ($m) => str_contains($m, '3 pages'));
    }

    public function test_a_new_installation_can_start_from_an_exported_site(): void
    {
        $this->buildSite();
        $zip = app(SiteExporter::class)->export($this->sandbox.'/export.zip');
        $this->wipeSite();
        User::query()->delete();

        $this->post('/install/site', [
            'site_name' => 'Nouveau nom', 'locale' => 'fr', 'name' => 'Amani', 'email' => 'amani@test.local',
            'password' => 'motdepasse123', 'password_confirmation' => 'motdepasse123', 'start' => 'import',
            'archive' => new UploadedFile($zip, 'export.zip', 'application/zip', null, true),
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertSame(3, Page::count());
        $this->assertSame('accueil', Page::where('locale', 'fr')->where('is_home', true)->sole()->slug);
        $this->assertSame('Nouveau nom', Setting::read('site_name'));
        $this->assertSame('#123456', Setting::read('primary_color'));
    }

    public function test_command_line_export_and_import(): void
    {
        $this->buildSite();
        $path = $this->sandbox.'/cli.zip';

        $this->artisan('geniuspress:export', ['path' => $path])->assertSuccessful();
        $this->assertFileExists($path);

        $this->wipeSite();
        $this->artisan('geniuspress:import', ['file' => $path, '--replace' => true])
            ->expectsOutputToContain('3 pages')->assertSuccessful();
        $this->assertSame(3, Page::count());

        $this->artisan('geniuspress:import', ['file' => $this->sandbox.'/absent.zip'])->assertFailed();
    }
}
