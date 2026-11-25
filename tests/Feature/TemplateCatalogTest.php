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

    /** Le catalogue (72 modèles) n'est construit qu'une fois pour toute la classe. */
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
            // Pages, articles du blog et gabarits du thème : chaque réglage doit être accepté.
            $docs = array_merge(
                array_map(fn ($p) => ["{$p['locale']}/{$p['slug']}", $p['content']], $this->zipJson($id, 'pages.json')),
                array_map(fn ($p) => ["article {$p['locale']}/{$p['slug']}", $p['content']], $this->zipJson($id, 'posts.json')),
                array_map(fn ($l) => ["gabarit {$l['locale']}/{$l['type']}", $l['content']], $this->zipJson($id, 'layouts.json')),
            );
            foreach ($docs as [$name, $content]) {
                $before = $flat($content['elements']);
                $after = $flat($sanitizer->sanitize($content)['elements']);
                $this->assertSame(array_keys($before), array_keys($after), "{$id}/{$name} : éléments perdus");
                foreach ($before as $nodeId => $settings) {
                    $lost = array_diff(array_keys($settings), array_keys($after[$nodeId]));
                    $this->assertSame([], array_values($lost), "{$id}/{$name} : réglages refusés sur {$nodeId}");
                }
            }
            $this->assertNotEmpty($this->zipJson($id, 'posts.json'), "{$id} : articles d’exemple");
            $this->assertCount(4, $this->zipJson($id, 'layouts.json'), "{$id} : gabarits Article et Archive, en français et en anglais");
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

        // Blog du modèle : articles, catégories, gabarits et adresse du blog.
        $this->assertSame([6, 2], [$report['posts'], $report['layouts'] / 2]);
        $this->assertSame('actualites', Setting::read('blog')['locales']['fr']['base']);
        $post = \App\Models\Post::where('slug', 'rapport-annuel')->sole();
        $this->assertNotNull($post->featured_media_id);
        $this->assertSame('Rapports', $post->categories->first()->name);
        $this->get('/actualites')->assertOk()->assertSee('Rapport annuel : une année de résultats');
        $this->get('/actualites/rapport-annuel')->assertOk()->assertSee('<h1>Rapport annuel : une année de résultats</h1>', false)->assertSee('À lire aussi');
        $this->get('/en/news/annual-report')->assertOk()->assertSee('Keep reading');
    }

    public function test_every_demo_home_page_renders_and_every_menu_link_is_an_address(): void
    {
        config(['geniuspress.catalog.path' => $this->catalog, 'geniuspress.catalog.demos' => true]);

        foreach (array_keys(require base_path('templates/catalog.php')) as $id) {
            $navigation = $this->zipJson($id, 'settings.json')['navigation'] ?? [];
            foreach ($navigation['locales'] ?? [] as $locale => $nav) {
                foreach ([...($nav['menu'] ?? []), ...($nav['footer_links'] ?? [])] as $link) {
                    $this->assertIsString($link['url'] ?? null, "{$id} ({$locale}) : adresse du lien « ".json_encode($link['label'] ?? null, JSON_UNESCAPED_UNICODE).' »');
                    $this->assertIsString($link['label'] ?? null, "{$id} ({$locale}) : libellé d’un lien");
                }
            }
            $this->get('/catalogue/'.$id)->assertOk();
        }
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

        // Blog de la démonstration : liste, article, catégorie et recherche, lus dans l'archive.
        $this->get('/catalogue/ong-horizon/actualites')->assertOk()->assertSee('Journée portes ouvertes')
            ->assertSee('href="'.url('/catalogue/ong-horizon/actualites/journee-portes-ouvertes').'"', false);
        $this->get('/catalogue/ong-horizon/actualites/rapport-annuel')->assertOk()
            ->assertSee('<h1>Rapport annuel : une année de résultats</h1>', false)
            ->assertSee(url('/catalogue/ong-horizon/media/ong-actu-1.svg'))
            ->assertSee('href="'.url('/catalogue/ong-horizon/actualites/categorie/rapports').'"', false);
        $this->get('/catalogue/ong-horizon/actualites/categorie/projets')->assertOk()->assertSee('Un nouveau partenariat pour l’eau')->assertDontSee('Journée portes ouvertes');
        $this->get('/catalogue/ong-horizon?s=village')->assertOk()->assertSee('Un nouveau partenariat pour l’eau');
        $this->get('/catalogue/ong-horizon/en/news/annual-report')->assertOk()->assertSee('href="'.url('/catalogue/ong-horizon/en/news').'"', false);
        $this->get('/catalogue/ong-horizon/actualites/absent')->assertNotFound();

        // Flux RSS annoncé dans l'en-tête des pages : les articles de la démonstration, en français et en anglais.
        $feed = $this->get('/catalogue/ong-horizon/actualites/rss')->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')->getContent();
        $this->assertStringContainsString('<link>'.url('/catalogue/ong-horizon/actualites/rapport-annuel').'</link>', $feed);
        $this->assertStringContainsString('Annual report', $this->get('/catalogue/ong-horizon/en/news/rss')->assertOk()->getContent());
        $this->assertSame(0, \App\Models\Post::count(), 'Rien n’est installé.');

        // Les réglages du site ne sont pas modifiés par une démonstration.
        $this->assertNotSame('#0f766e', Setting::read('primary_color'));
        $this->assertSame(url('/blog/x'), url('/blog/x'));
        $this->assertStringStartsWith(url('/blog'), \App\Site\Permalinks::blog('fr'), 'Adresses du site rétablies après la démonstration.');
    }

    public function test_media_templates_use_their_news_widgets(): void
    {
        $types = function (string $id, string $file) {
            $out = [];
            $data = $this->zipJson($id, $file);
            array_walk_recursive($data, function ($v, $k) use (&$out) {
                if ($k === 'type') {
                    $out[$v] = true;
                }
            });

            return array_keys($out);
        };
        $index = json_decode(file_get_contents($this->catalog.'/index.json'), true);
        $this->assertSame('Médias et presse', $index['sectors']['medias']['name']['fr']);
        $this->assertGreaterThanOrEqual(6, count(array_filter($index['templates'], fn ($t) => $t['sector'] === 'medias')));

        foreach (['ticker', 'slider', 'posts', 'terms-list', 'chart', 'pricing'] as $type) {
            $this->assertContains($type, $types('medias-journal-quotidien', 'pages.json'), "journal : {$type}");
        }
        foreach (['ticker', 'video-playlist', 'data-table', 'chart', 'social-icons'] as $type) {
            $this->assertContains($type, $types('medias-radio-onde', 'pages.json'), "radio : {$type}");
        }
        foreach (['video', 'video-playlist', 'data-table', 'timeline'] as $type) {
            $this->assertContains($type, $types('medias-tv-ecran', 'pages.json'), "télévision : {$type}");
        }
        // Les articles des médias se partagent, avec une barre de progression de lecture.
        foreach (['share-buttons', 'reading-progress', 'terms-list'] as $type) {
            $this->assertContains($type, $types('medias-podcast-echo', 'layouts.json'), "gabarits : {$type}");
        }

        config(['geniuspress.catalog.path' => $this->catalog, 'geniuspress.catalog.demos' => true]);
        $this->get('/catalogue/medias-radio-onde')->assertOk()->assertSee('gp-ticker', false)->assertSee('Écouter en direct');
        $this->get('/catalogue/medias-tv-ecran/en/live')->assertOk()->assertSee('Watch the channel live')->assertSee('youtube-nocookie.com/embed/', false);
        $this->get('/catalogue/medias-journal-quotidien/actualites/categorie/economie')->assertOk()->assertSee('Le marché central rouvre après six mois de travaux');
        $this->get('/catalogue/medias-journal-quotidien/actualites/marche-central-rouvre')->assertOk()->assertSee('gp-share', false);
    }

    public function test_demos_are_off_in_production_unless_enabled(): void
    {
        config(['geniuspress.catalog.path' => $this->catalog, 'geniuspress.catalog.demos' => false]);
        $this->get('/catalogue/ong-horizon')->assertNotFound();
    }
}
