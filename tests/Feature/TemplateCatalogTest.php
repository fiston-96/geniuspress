<?php

namespace Tests\Feature;

use App\Builder\ContentSanitizer;
use App\Models\Media;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Site\Navigation;
use App\Transfer\SiteImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/** Catalogue des modèles : construction, validité des pages, installation, démonstration. */
class TemplateCatalogTest extends TestCase
{
    use RefreshDatabase;

    private string $catalog;

    /** Le catalogue (60 modèles) n'est construit qu'une fois pour toute la classe. */
    private static ?string $built = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
        // Sans les photos téléchargées localement : le résultat ne dépend pas de la machine
        // (les photos sont testées dans TemplatePhotosTest).
        config(['geniuspress.catalog.photos_path' => $this->sandbox.'/sans-photos']);
        User::create(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        if (self::$built === null) {
            self::$built = sys_get_temp_dir().'/gp-catalog-test-'.getmypid();
            $this->artisan('geniuspress:templates', ['--output' => self::$built])->assertSuccessful();
        }
        $this->catalog = self::$built;
    }

    public function test_every_palette_is_readable(): void
    {
        $luminance = function (string $hex) {
            $c = array_map(fn ($x) => hexdec($x) / 255, str_split(ltrim($hex, '#'), 2));
            $c = array_map(fn ($v) => $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4, $c);

            return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
        };
        $ratio = fn ($a, $b) => (max($luminance($a), $luminance($b)) + 0.05) / (min($luminance($a), $luminance($b)) + 0.05);

        foreach (require base_path('templates/catalog.php') as $id => $def) {
            $c = $def['theme']->colors;
            // WCAG AA : texte blanc sur la couleur principale, texte foncé sur l'accent, texte secondaire sur fond clair.
            $this->assertGreaterThanOrEqual(4.5, $ratio($c['primary'], '#ffffff'), "{$id} : texte blanc sur la couleur principale");
            $this->assertGreaterThanOrEqual(4.5, $ratio($c['accent'], $c['secondary']), "{$id} : texte foncé sur l’accent");
            $this->assertGreaterThanOrEqual(4.5, $ratio($c['muted'], $c['light']), "{$id} : texte secondaire sur fond clair");
        }
    }

    private function zipJson(string $id, string $file): array
    {
        $zip = new ZipArchive;
        $zip->open($this->catalog.'/'.$id.'.zip');
        $data = json_decode((string) $zip->getFromName($file), true);
        $zip->close();

        return $data;
    }

    public function test_catalog_index_describes_every_template(): void
    {
        $index = json_decode(file_get_contents($this->catalog.'/index.json'), true);
        $declared = array_keys(require base_path('templates/catalog.php'));

        $this->assertSame('geniuspress-catalog', $index['format']);
        $this->assertSame($declared, array_column($index['templates'], 'id'));
        foreach ($index['templates'] as $t) {
            $this->assertFileExists($this->catalog.'/'.$t['file']);
            $this->assertSame(hash_file('sha256', $this->catalog.'/'.$t['file']), $t['sha256']);
            $this->assertSame(['fr', 'en'], $t['locales']);
            $this->assertCount(8, $t['pages'], $t['id'].' : 8 pages par langue');
            $this->assertArrayHasKey($t['sector'], $index['sectors']);
            foreach ($t['pages'] as $p) {
                $this->assertNotEmpty($p['title']['fr']);
                $this->assertNotEmpty($p['title']['en']);
            }
        }
    }

    public function test_every_setting_of_every_template_page_is_valid(): void
    {
        $sanitizer = app(ContentSanitizer::class);
        $flat = function (array $nodes) use (&$flat) {
            $out = [];
            foreach ($nodes as $n) {
                $out[$n['id']] = $n['settings'];
                if ($n['type'] === 'container') {
                    $out += $flat($n['children']);
                }
            }

            return $out;
        };

        foreach (array_keys(require base_path('templates/catalog.php')) as $id) {
            foreach ($this->zipJson($id, 'pages.json') as $page) {
                $before = $flat($page['content']['elements']);
                $after = $flat($sanitizer->sanitize($page['content'])['elements']);
                $this->assertSame(array_keys($before), array_keys($after), "{$id}/{$page['locale']}/{$page['slug']} : éléments perdus");
                foreach ($before as $nodeId => $settings) {
                    $lost = array_diff(array_keys($settings), array_keys($after[$nodeId]));
                    $this->assertSame([], array_values($lost), "{$id}/{$page['locale']}/{$page['slug']} : réglages refusés sur {$nodeId}");
                }
            }
        }
    }

    public function test_a_template_installs_like_a_site_backup(): void
    {
        $report = app(SiteImporter::class)->import($this->catalog.'/ong-horizon.zip', User::first(), replace: true, withSettings: true);

        $this->assertSame([], $report['warnings'], 'Les illustrations passent les contrôles de l’import.');
        $this->assertSame(16, $report['pages']);
        $this->assertSame(Media::count(), $report['media']);
        $this->assertSame('#0f766e', Setting::read('primary_color'));
        $this->assertSame('Poppins', Setting::read('heading_font'));
        $this->assertSame(['clair', 'texte', 'doux'], array_column(Setting::read('custom_colors'), 'id'));
        $this->assertSame(['fr', 'en'], Setting::read('locales'));
        $this->assertSame('/en/donate', Navigation::all()['locales']['en']['cta_url']);

        $home = Page::where('locale', 'fr')->where('is_home', true)->sole();
        $en = Page::where('locale', 'en')->where('is_home', true)->sole();
        $this->assertSame($home->translation_group, $en->translation_group);

        // Les images pointent vers les fichiers importés dans la médiathèque.
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('/storage/templates/', $html);
        $this->assertStringContainsString(Media::first()->url, $html);
        $this->get('/en/about')->assertOk()->assertSee('Our story and our mission');
    }

    public function test_demos_render_templates_without_installing_them(): void
    {
        config(['geniuspress.catalog.path' => $this->catalog, 'geniuspress.catalog.demos' => true]);

        $html = $this->get('/catalogue/ong-horizon')->assertOk()->assertHeader('X-Robots-Tag', 'noindex')->getContent();
        $this->assertStringContainsString('<title>Accueil · Horizon</title>', $html);
        $this->assertStringContainsString(url('/catalogue/ong-horizon/media/ong-accueil.svg'), $html);
        $this->assertStringContainsString('href="'.url('/catalogue/ong-horizon/faire-un-don').'"', $html, 'Liens internes réécrits vers la démonstration.');
        $this->assertSame(0, Page::count(), 'Rien n’est installé.');

        $this->get('/catalogue/ong-horizon/en/programs')->assertOk()->assertSee('Practical answers to essential needs');
        $this->get('/catalogue/ong-horizon/media/ong-accueil.svg')->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $this->get('/catalogue/ong-horizon/media/..%2F..%2F.env')->assertNotFound();
        $this->get('/catalogue/inconnu')->assertNotFound();
        $this->get('/catalogue/ong-horizon/page-absente')->assertNotFound();

        // Les réglages du site ne sont pas modifiés par une démonstration.
        $this->assertNotSame('#0f766e', Setting::read('primary_color'));
    }

    public function test_demos_are_off_in_production_unless_enabled(): void
    {
        config(['geniuspress.catalog.path' => $this->catalog, 'geniuspress.catalog.demos' => false]);
        $this->get('/catalogue/ong-horizon')->assertNotFound();
    }
}
