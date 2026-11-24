<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Templates\PhotoLibrary;
use App\Transfer\SiteImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/** Photos libres de droits des modèles (Pexels simulé : aucun vrai téléchargement). */
class TemplatePhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['geniuspress.catalog.photos_path' => $this->sandbox.'/photos', 'geniuspress.catalog.pexels_key' => 'cle-de-test']);
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

                return Http::response(['photos' => [
                    ['id' => 1000 + $n * 10, 'photographer' => 'Amina K.', 'photographer_url' => 'https://www.pexels.com/@amina', 'url' => 'https://www.pexels.com/photo/'.(1000 + $n * 10), 'alt' => 'Photo', 'src' => ['medium' => 'https://images.pexels.com/m.jpg', 'large2x' => 'https://images.pexels.com/photos/a.jpg']],
                    ['id' => 1001 + $n * 10, 'photographer' => 'Kofi M.', 'photographer_url' => 'https://www.pexels.com/@kofi', 'url' => 'https://www.pexels.com/photo/'.(1001 + $n * 10), 'alt' => 'Photo', 'src' => ['medium' => 'https://images.pexels.com/m.jpg', 'large2x' => 'https://images.pexels.com/photos/b.jpg']],
                ]], 200, ['X-Ratelimit-Remaining' => (string) $remaining]);
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
            ->expectsOutputToContain('PEXELS_API_KEY')->assertFailed();
        Http::assertNothingSent();
    }
}
