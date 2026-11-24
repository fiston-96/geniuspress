<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Templates\PhotoLibrary;
use App\Transfer\SiteImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use Tests\TestCase;
use ZipArchive;

/** Photos libres de droits des modèles (Pixabay et Pexels simulés : aucun vrai téléchargement). */
class TemplatePhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['geniuspress.catalog.photos_path' => $this->sandbox.'/photos', 'geniuspress.catalog.pexels_key' => 'cle-de-test', 'geniuspress.catalog.pixabay_key' => null]);
        Sleep::fake();
    }

    private function jpeg(int $w = 2400, int $h = 1600): string
    {
        $img = imagecreatetruecolor($w, $h);
        imagefill($img, 0, 0, imagecolorallocate($img, 30, 120, 90));
        ob_start();
        imagejpeg($img);

        return (string) ob_get_clean();
    }

    /** Requêtes encore permises dans l'heure (simulé). */
    private int $remaining = 150;

    private function fakePexels(): void
    {
        $n = 0;
        Http::fake([
            'api.pexels.com/v1/search*' => function () use (&$n) {
                $n++;
                $remaining = --$this->remaining;

                return Http::response(['photos' => array_map(fn ($i) => [
                    'id' => 1000 + $n * 10 + $i, 'photographer' => $i % 2 ? 'Kofi M.' : 'Amina K.', 'photographer_url' => 'https://www.pexels.com/@p'.$i,
                    'url' => 'https://www.pexels.com/photo/'.(1000 + $n * 10 + $i), 'alt' => 'Photo', 'src' => ['medium' => 'https://images.pexels.com/m.jpg', 'large2x' => 'https://images.pexels.com/photos/'.$i.'.jpg'],
                ], range(0, 3))], 200, ['X-Ratelimit-Remaining' => (string) $remaining]);
            },
            'images.pexels.com/*' => Http::response($this->jpeg()),
        ]);
    }

    public function test_search_choose_download_and_build(): void
    {
        $this->fakePexels();
        $this->artisan('geniuspress:templates:photos', ['pack' => 'hopital', '--search' => true])->assertSuccessful();

        $candidates = PhotoLibrary::read('candidates');
        $choices = PhotoLibrary::read('choices');
        $this->assertCount(15, $choices, 'Un choix par emplacement du pack hôpitaux.');
        $this->assertStringContainsString('african hospital', $candidates['hopital-accueil']['query']);
        $team = collect($candidates)->first(fn ($c, $slot) => str_contains($slot, 'medecin') || str_contains($slot, 'equipe'));
        $this->assertNotNull($team);
        $this->assertStringContainsString('portrait', $team['query'], 'Les photos d’équipe cherchent des portraits.');
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'cle-de-test') && str_contains($r->url(), 'orientation=landscape'));

        $this->artisan('geniuspress:templates:photos', ['pack' => 'hopital', '--choose' => ['hopital-accueil:2']])->assertSuccessful();
        $this->assertSame($candidates['hopital-accueil']['photos'][1]['id'], PhotoLibrary::read('choices')['hopital-accueil']);

        $this->artisan('geniuspress:templates:photos', ['pack' => 'hopital', '--download' => true])->assertSuccessful();
        $file = PhotoLibrary::file('hopital-accueil');
        $this->assertNotNull($file);
        $this->assertSame(1600, getimagesize($file)[0], 'Réduite à 1600 px de large.');
        $this->assertSame('Kofi M.', PhotoLibrary::read('credits')['hopital-accueil']['photographer']);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'api.pexels.com/v1/photos/'));

        // Le modèle utilise les photos, et s'installe avec elles.
        $catalog = $this->sandbox.'/catalog';
        $this->artisan('geniuspress:templates', ['--output' => $catalog, '--only' => 'hopital-sante'])->assertSuccessful();
        $zip = new ZipArchive;
        $zip->open($catalog.'/hopital-sante.zip');
        $this->assertNotFalse($zip->statName('media/hopital-accueil.jpg'));
        $this->assertStringContainsString('/storage/templates/hopital-accueil.jpg', (string) $zip->getFromName('pages.json'));
        $zip->close();
        $entry = collect(json_decode(file_get_contents($catalog.'/index.json'), true)['templates'])->firstWhere('id', 'hopital-sante');
        $this->assertNotEmpty($entry['credits']);
        $this->assertSame('Pexels License', $entry['credits'][0]['license']);
        $this->assertSame('pexels', $entry['credits'][0]['source']);

        Storage::fake('public');
        $admin = User::create(['name' => 'A', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        $report = app(SiteImporter::class)->import($catalog.'/hopital-sante.zip', $admin, replace: true);
        $this->assertSame([], $report['warnings']);
        $this->assertSame('image/jpeg', Media::where('original_name', 'hopital-accueil.jpg')->sole()->mime_type);
    }

    public function test_search_stops_at_the_hourly_limit_and_resumes(): void
    {
        $this->remaining = 3;
        $this->fakePexels();
        $this->artisan('geniuspress:templates:photos', ['pack' => 'hopital', '--search' => true])
            ->expectsOutputToContain('relancez la même commande dans une heure')->assertSuccessful();
        $this->assertCount(2, PhotoLibrary::read('choices'), 'Progression enregistrée avant l’arrêt.');

        $this->remaining = 150; // une heure plus tard
        $this->artisan('geniuspress:templates:photos', ['pack' => 'hopital', '--search' => true])->assertSuccessful();
        $this->assertCount(15, PhotoLibrary::read('choices'));
    }

    public function test_a_key_is_required_to_search(): void
    {
        config(['geniuspress.catalog.pexels_key' => null]);
        Http::fake();
        $this->artisan('geniuspress:templates:photos', ['pack' => 'tech', '--search' => true])
            ->expectsOutputToContain('PIXABAY_API_KEY')->assertFailed();
        $this->artisan('geniuspress:templates:photos', ['pack' => 'tech', '--search' => true, '--source' => 'pexels'])
            ->expectsOutputToContain('PEXELS_API_KEY')->assertFailed();
        Http::assertNothingSent();
    }

    public function test_pixabay_search_falls_back_to_shorter_queries_and_hides_the_key(): void
    {
        config(['geniuspress.catalog.pixabay_key' => 'cle-pixabay-secrete']);
        $queries = [];
        Http::fake([
            'pixabay.com/api/*' => function ($request) use (&$queries) {
                parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $q);
                $queries[] = $q['q'];
                // La requête complète (longue) ne trouve rien ; la plus courte trouve.
                $hits = str_word_count($q['q']) > 4 ? [] : array_map(fn ($i) => [
                    'id' => 5000 + count($queries) * 10 + $i, 'pageURL' => 'https://pixabay.com/photos/x-'.$i.'/', 'tags' => 'hospital, doctor',
                    'previewURL' => 'https://cdn.pixabay.com/p.jpg', 'webformatURL' => 'https://pixabay.com/get/w.jpg', 'largeImageURL' => 'https://pixabay.com/get/l.jpg',
                    'user' => 'Nadia B', 'user_id' => 42,
                ], range(1, 8));

                return Http::response(['total' => count($hits), 'totalHits' => count($hits), 'hits' => $hits], 200, ['X-RateLimit-Remaining' => '90', 'X-RateLimit-Reset' => '30']);
            },
            'pixabay.com/get/*' => Http::response($this->jpeg(1280, 853)),
        ]);

        PhotoLibrary::write('queries', ['hopital-maternite' => 'african mother baby']);
        $this->artisan('geniuspress:templates:photos', ['pack' => 'hopital', '--search' => true, '--download' => true])->assertSuccessful();

        $this->assertSame('african mother baby', PhotoLibrary::read('candidates')['hopital-maternite']['query'], 'Les mots-clés choisis à la main passent en premier.');
        $c = PhotoLibrary::read('candidates')['hopital-accueil'];
        $this->assertSame('pixabay', $c['source']);
        $this->assertCount(8, $c['photos']);
        $this->assertLessThanOrEqual(4, str_word_count($c['query']), 'La requête retenue est la plus courte qui a trouvé.');
        $this->assertGreaterThan(count(PhotoLibrary::read('choices')), count($queries), 'Des requêtes plus courtes ont été essayées.');
        Http::assertSent(fn ($r) => str_contains($r->url(), 'orientation=horizontal') && str_contains($r->url(), 'safesearch=true'));

        $credit = PhotoLibrary::read('credits')['hopital-accueil'];
        $this->assertSame(['pixabay', 'Nadia B', 'https://pixabay.com/users/Nadia%20B-42/', 'Pixabay Content License'], [$credit['source'], $credit['photographer'], $credit['photographer_url'], $credit['license']]);
        $this->assertSame(1280, getimagesize(PhotoLibrary::file('hopital-accueil'))[0], 'Jamais agrandie.');
        foreach (['candidates', 'choices', 'credits'] as $file) {
            $this->assertStringNotContainsString('cle-pixabay-secrete', (string) file_get_contents(PhotoLibrary::path($file.'.json')), 'La clé n’est jamais enregistrée.');
        }
    }

    public function test_a_photo_is_not_reused_within_a_sector_and_refresh_searches_again(): void
    {
        config(['geniuspress.catalog.pixabay_key' => 'cle-de-test']);
        $calls = 0;
        // Toujours les mêmes 8 photos, quelle que soit la recherche.
        Http::fake(['pixabay.com/api/*' => function () use (&$calls) {
            $calls++;

            return Http::response(['hits' => array_map(fn ($i) => ['id' => 700 + $i, 'pageURL' => 'https://pixabay.com/photos/'.$i, 'tags' => 'x', 'webformatURL' => 'https://pixabay.com/get/w.jpg', 'largeImageURL' => 'https://pixabay.com/get/l.jpg', 'user' => 'U', 'user_id' => 1], range(1, 8))]);
        }]);
        $this->artisan('geniuspress:templates:photos', ['pack' => 'tech', '--search' => true])->assertSuccessful();
        $choices = array_values(PhotoLibrary::read('choices'));
        $this->assertSame(8, count(array_unique($choices)), 'Les huit photos sont toutes utilisées avant qu’une ne serve deux fois.');

        $before = $calls;
        $this->artisan('geniuspress:templates:photos', ['pack' => 'tech', '--search' => true])->assertSuccessful();
        $this->assertSame($before, $calls, 'Sans --refresh, les emplacements choisis ne sont pas recherchés à nouveau.');
        $this->artisan('geniuspress:templates:photos', ['pack' => 'tech', '--search' => true, '--refresh' => true])->assertSuccessful();
        $this->assertGreaterThan($before, $calls);
    }

    public function test_a_rejected_pixabay_key_stops_without_showing_it(): void
    {
        // (Une coupure réseau simulée fait planter PHP 8.3 ZTS sous Windows : on simule la clé refusée.)
        config(['geniuspress.catalog.pixabay_key' => 'cle-pixabay-secrete']);
        Http::fake(['pixabay.com/api/*' => Http::response('[ERROR 400] Invalid or missing API key (cle-pixabay-secrete).', 400)]);
        $this->artisan('geniuspress:templates:photos', ['pack' => 'tech', '--search' => true])
            ->expectsOutputToContain('Clé API Pixabay refusée')
            ->doesntExpectOutputToContain('cle-pixabay-secrete')
            ->assertFailed();
        $this->assertSame([], PhotoLibrary::read('choices'));
    }
}
