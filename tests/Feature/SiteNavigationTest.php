<?php

namespace Tests\Feature;

use App\Builder\ContentSanitizer;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Site\Navigation;
use App\Website\PageKit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

/** En-tête, menus et pied de page, widget « Liste de liens », adresses à plusieurs niveaux, site officiel. */
class SiteNavigationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['site_name' => 'ONG Espoir', 'locales' => ['fr', 'en']]);
    }

    private function page(string $slug, string $locale = 'fr', array $extra = []): Page
    {
        return Page::create($extra + ['title' => ucfirst($slug), 'slug' => $slug, 'locale' => $locale, 'status' => 'published', 'content' => ['sections' => []]]);
    }

    private function saveNavigation(array $overrides = []): void
    {
        Navigation::save(array_replace_recursive([
            'header' => ['enabled' => '1', 'sticky' => '1', 'style' => 'dark', 'cta_url' => '/dons'],
            'footer' => ['enabled' => '1', 'credit' => '1'],
            'locales' => [
                'fr' => [
                    'menu' => [['label' => 'Projets', 'url' => '/projets'], ['label' => 'Piège', 'url' => 'javascript:alert(1)'], ['label' => '', 'url' => '/vide']],
                    'cta_label' => 'Faire un don', 'footer_text' => '<b>Ensemble</b> pour l’éducation',
                    'footer_links' => [['label' => 'Contact', 'url' => '/contact']],
                ],
                'en' => ['menu' => [['label' => 'Projects', 'url' => '/en/projects']], 'cta_label' => 'Donate'],
                'xx' => ['menu' => [['label' => 'Inconnue', 'url' => '/x']]],
            ],
        ], $overrides), ['fr', 'en']);
    }

    public function test_navigation_is_validated_before_being_saved(): void
    {
        $this->saveNavigation();
        $nav = Navigation::all();

        $this->assertSame([['label' => 'Projets', 'url' => '/projets']], $nav['locales']['fr']['menu']);
        $this->assertSame('Ensemble pour l’éducation', $nav['locales']['fr']['footer_text']);
        $this->assertSame('dark', $nav['header']['style']);
        $this->assertArrayNotHasKey('xx', $nav['locales']);

        $links = array_fill(0, 30, ['label' => 'Lien', 'url' => '/a']);
        Navigation::save(['locales' => ['fr' => ['menu' => $links]]], ['fr']);
        $this->assertCount(Navigation::MAX_LINKS, Navigation::all()['locales']['fr']['menu']);
        $this->assertFalse(Navigation::all()['header']['enabled']);
    }

    public function test_public_pages_show_the_header_menu_languages_and_footer(): void
    {
        $this->saveNavigation();
        $fr = $this->page('projets', 'fr', ['translation_group' => 'g1']);
        $this->page('projects', 'en', ['translation_group' => 'g1']);
        $this->page('projets/ecoles');

        $this->get('/projets')->assertOk()
            ->assertSee('gp-site-header--dark', false)
            ->assertSee('<a href="/projets" aria-current="page">Projets</a>', false)
            ->assertSee('Faire un don')
            ->assertSee('href="'.url('/en/projects').'" hreflang="en"', false)
            ->assertSee('Ensemble pour l’éducation')
            ->assertSee('Propulsé par')
            ->assertDontSee('javascript:alert', false);

        // Sous-page : la rubrique du menu est marquée comme contenant la page.
        $this->get('/projets/ecoles')->assertOk()->assertSee('<a href="/projets" aria-current="true">Projets</a>', false);

        // Version anglaise : menu anglais et crédit traduit dans la langue de la page.
        $this->get('/en/projects')->assertOk()->assertSee('Projects')->assertSee('Donate')->assertSee('Powered by');

        // Tout peut être masqué.
        $this->saveNavigation(['header' => ['enabled' => '0'], 'footer' => ['enabled' => '0']]);
        $this->get($fr->url())->assertOk()->assertDontSee('gp-site-header', false)->assertDontSee('gp-site-footer', false);
    }

    public function test_appearance_page_is_reserved_to_administrators(): void
    {
        $this->actingAs($this->admin)->get('/admin/appearance')->assertOk()->assertSee('Apparence du site');
        $this->actingAs($this->admin)->put('/admin/appearance', [
            'header' => ['enabled' => '1', 'style' => 'light'],
            'favicon_url' => 'javascript:alert(1)',
            'locales' => ['fr' => ['menu' => [['label' => 'Accueil', 'url' => '/']]]],
        ])->assertSessionHas('status');
        $this->assertSame('Accueil', Navigation::all()['locales']['fr']['menu'][0]['label']);
        $this->assertNull(Setting::read('favicon_url'));

        $editor = User::create(['name' => 'Ed', 'email' => 'e@test.local', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->actingAs($editor)->get('/admin/appearance')->assertForbidden();
        $this->actingAs($editor)->put('/admin/appearance', [])->assertForbidden();
    }

    public function test_links_widget_is_sanitized_and_rendered(): void
    {
        $content = app(ContentSanitizer::class)->sanitize(['sections' => [['columns' => [['widgets' => [[
            'type' => 'links',
            'settings' => ['title' => 'Sur cette page', 'layout' => 'diagonal', 'style' => 'boxed', 'items' => [
                ['label' => 'Équipe', 'url' => '/equipe'],
                ['label' => 'Piège', 'url' => 'javascript:alert(1)'],
                ['label' => '<b>Dons</b>', 'url' => '#dons'],
                'pas un lien',
            ]],
        ]]]]]]]);
        $settings = $content['sections'][0]['columns'][0]['widgets'][0]['settings'];

        $this->assertSame([['label' => 'Équipe', 'url' => '/equipe'], ['label' => 'Dons', 'url' => '#dons']], $settings['items']);
        $this->assertArrayNotHasKey('layout', $settings);

        $this->page('equipe', 'fr', ['content' => $content]);
        $this->get('/equipe')->assertOk()
            ->assertSee('gp-links gp-links--vertical gp-links--boxed', false)
            ->assertSee('<a href="/equipe" aria-current="page">Équipe</a>', false)
            ->assertSee('<a href="#dons">Dons</a>', false);
    }

    public function test_slugs_can_have_several_levels_but_not_reserved_prefixes(): void
    {
        $this->assertSame('documentation/installation', Page::normalizeSlug('/Documentation/Installation/'));
        $this->assertSame('a/b/c/d', Page::normalizeSlug('a/b/c/d/e/f'));
        $this->assertTrue(Page::isReservedSlug('admin/pages'));
        $this->assertTrue(Page::isReservedSlug('brand'));
        $this->assertFalse(Page::isReservedSlug('administration'));

        $this->actingAs($this->admin)->post('/admin/pages', ['title' => 'Piège', 'slug' => 'admin/settings', 'locale' => 'fr'])
            ->assertSessionHasErrors('slug');
    }

    public function test_markdown_documentation_becomes_safe_html_with_site_links(): void
    {
        $md = PageKit::markdown("# Installer\n\nVoir [la sauvegarde](guide/sauvegarde.md#export) et [le code](https://github.com).\n\n<script>alert(1)</script>\n\n###### Petit titre\n\n| A | B |\n|---|---|\n| 1 | 2 |", fn ($href) => '/doc/'.$href);

        $this->assertSame('Installer', $md['title']);
        $this->assertStringContainsString('href="/doc/guide/sauvegarde.md#export"', $md['html']);
        $this->assertStringContainsString('href="https://github.com"', $md['html']);
        $this->assertStringContainsString('<h4>Petit titre</h4>', $md['html']);
        $this->assertStringContainsString('<table>', $md['html']);
        $this->assertStringNotContainsString('<script', $md['html']);
    }

    public function test_official_website_is_built_and_imported(): void
    {
        $path = $this->sandbox.'/site-officiel.zip';
        $this->artisan('geniuspress:website', ['--output' => $path, '--import' => true])->assertSuccessful();

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        $this->assertSame('geniuspress-site', json_decode($zip->getFromName('manifest.json'), true)['format']);
        $zip->close();

        $this->assertSame('GeniusPress', Setting::read('site_name'));
        $this->assertSame('accueil', Page::where('is_home', true)->sole()->slug);
        $this->assertTrue(Page::where('slug', 'documentation/plugins')->exists());

        $this->get('/')->assertOk()->assertSee('Construisez le site de votre organisation');
        $this->get('/documentation/installation')->assertOk()
            ->assertSee('Installer GeniusPress')
            ->assertSee('<pre>', false)
            ->assertSee('<a href="/documentation" aria-current="true">Documentation</a>', false);
    }
}
