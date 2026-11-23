<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class InterfaceLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function admin(array $attrs = []): User
    {
        return User::create($attrs + ['name' => 'Amani', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
    }

    public function test_guests_get_the_browser_language(): void
    {
        $this->admin();

        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/admin/login')
            ->assertOk()->assertSee('Sign in')->assertSee('lang="en"', false);
        $this->withHeader('Accept-Language', 'fr-CD,fr;q=0.9')->get('/admin/login')
            ->assertOk()->assertSee('Se connecter');
        // Langue non disponible : français par défaut.
        $this->withHeader('Accept-Language', 'sw')->get('/admin/login')->assertSee('Se connecter');
    }

    public function test_users_choose_their_interface_language(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/locale', ['locale' => 'en'])->assertSessionHas('status', 'Interface language changed.');
        $this->assertSame('en', $admin->fresh()->locale);

        // Le choix de l'utilisateur l'emporte sur la langue du navigateur.
        $this->actingAs($admin->fresh())->get('/admin')->assertOk()->assertSee('Hello Amani')->assertSee('Dashboard');
        $this->actingAs($admin)->put('/admin/locale', ['locale' => 'xx'])->assertSessionHasErrors('locale');
    }

    public function test_validation_messages_follow_the_interface_language(): void
    {
        $this->admin();

        $this->from('/admin/login')->post('/admin/login', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email' => 'Le champ adresse e-mail est obligatoire.']);

        $this->withHeader('Accept-Language', 'en')->from('/admin/login')->post('/admin/login', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors(['email' => 'The email address field is required.']);
    }

    public function test_builder_receives_the_dictionary_and_content_language_stays_the_site_language(): void
    {
        $admin = $this->admin(['locale' => 'en']);

        // Une page créée par un administrateur anglophone reste dans la langue du site.
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'Nos programmes', 'locale' => 'fr']);
        $page = Page::where('slug', 'nos-programmes')->firstOrFail();
        $this->assertSame('fr', $page->locale);

        $html = $this->actingAs($admin)->get("/admin/pages/{$page->id}/builder")->assertOk()->getContent();
        preg_match('#<script type="application/json" id="gp-boot">(.*?)</script>#s', $html, $m);
        $boot = json_decode($m[1], true);
        $this->assertSame('Save', $boot['i18n']['Enregistrer']);
        $this->assertSame('Heading', $boot['widgets']['heading']['label']);
    }

    /** Garde-fou pour les contributeurs : tout texte traduisible doit exister en anglais. */
    public function test_every_interface_text_has_an_english_translation(): void
    {
        $english = json_decode(file_get_contents(lang_path('en.json')), true);
        $string = "'((?:[^'\\\\]|\\\\.)*)'";
        $missing = [];

        $files = Finder::create()->files()->in([app_path(), resource_path('views'), resource_path('js/builder')])
            ->name(['*.php', '*.js', '*.jsx'])
            ->notName('MakePluginCommand.php'); // contient des modèles de plugin, pas des textes de l'interface
        foreach ($files as $file) {
            $pattern = str_ends_with($file->getFilename(), '.php')
                ? "/(?<![A-Za-z_])__\\({$string}/"
                : "/(?<![A-Za-z_.])t\\({$string}/";
            preg_match_all($pattern, $file->getContents(), $matches);
            foreach ($matches[1] as $key) {
                $key = stripcslashes($key);
                if (! array_key_exists($key, $english)) {
                    $missing[] = $key.'  ('.$file->getRelativePathname().')';
                }
            }
        }

        $this->assertSame([], array_values(array_unique($missing)), 'Textes absents de lang/en.json');
    }
}
