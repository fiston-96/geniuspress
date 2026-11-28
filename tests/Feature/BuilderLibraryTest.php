<?php

namespace Tests\Feature;

use App\Builder\DesignTokens;
use App\Builder\GlobalStyles;
use App\Builder\Renderer;
use App\Models\Layout;
use App\Models\Media;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Templates\SectionLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Lot 4 du builder : bibliothèque de sections, sections globales, styles globaux. */
class BuilderLibraryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $editor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@ong.test', 'password' => 'secret-password-1', 'role' => 'admin']);
        $this->editor = User::create(['name' => 'Éditrice', 'email' => 'edit@ong.test', 'password' => 'secret-password-1', 'role' => 'editor']);
        Setting::write(['locales' => ['fr', 'en'], 'site_name' => 'Mon site']);
    }

    public function test_section_library_lists_categorized_sections_in_both_languages(): void
    {
        $fr = SectionLibrary::all('fr');
        $this->assertGreaterThan(60, count($fr));
        $categories = array_count_values(array_column($fr, 'category'));
        foreach (['hero', 'services', 'pricing', 'faq', 'team', 'contact', 'stats'] as $c) {
            $this->assertArrayHasKey($c, $categories, "Catégorie {$c}");
        }
        $this->assertNotSame(array_column($fr, 'name'), array_column(SectionLibrary::all('en'), 'name'), 'Textes traduits.');
        $this->assertSame(SectionLibrary::all('en'), SectionLibrary::all('es'), 'Autres langues : l’anglais.');

        $this->actingAs($this->editor)->getJson(route('admin.builder.sections.index', ['locale' => 'fr']))
            ->assertOk()->assertJsonPath('categories.faq', 'Questions fréquentes')->assertJsonCount(count($fr), 'sections')
            ->assertJsonMissingPath('sections.0.node');
    }

    public function test_previews_use_library_photos_and_insertion_copies_them_to_the_media_library(): void
    {
        Storage::fake('public');
        $section = collect(SectionLibrary::all('fr'))->first(fn ($s) => $s['slots'] !== []);
        $this->assertNotNull($section, 'Au moins une section avec photos.');
        $slot = array_key_first($section['slots']);

        $html = $this->actingAs($this->editor)->get(route('admin.builder.sections.preview', ['section' => $section['id'], 'locale' => 'fr']))->assertOk()->getContent();
        $this->assertStringContainsString(route('admin.builder.sections.photo', $slot), $html);
        $this->assertStringNotContainsString('/storage/templates/', $html);
        $this->get(route('admin.builder.sections.photo', $slot))->assertOk();
        $this->get(route('admin.builder.sections.photo', '..%2Fenv'))->assertNotFound();

        $node = $this->postJson(route('admin.builder.sections.insert', ['section' => $section['id'], 'locale' => 'fr']))->assertOk()->json('node');
        $media = Media::query()->where('path', 'like', 'templates/'.$slot.'.%')->sole();
        Storage::disk('public')->assertExists($media->path);
        $this->assertStringContainsString($media->url, json_encode($node, JSON_UNESCAPED_SLASHES));
        // Une seconde insertion réutilise la même photo.
        $this->postJson(route('admin.builder.sections.insert', ['section' => $section['id'], 'locale' => 'fr']))->assertOk();
        $this->assertSame(1, Media::query()->where('path', $media->path)->count());

        $this->postJson(route('admin.builder.sections.insert', ['section' => 'inconnue-0', 'locale' => 'fr']))->assertNotFound();
    }

    public function test_global_sections_are_created_by_admins_and_rendered_everywhere(): void
    {
        $node = ['id' => 'cta0000001', 'type' => 'container', 'settings' => ['background_color' => '#123456'], 'children' => [
            ['id' => 'cta0000002', 'type' => 'heading', 'settings' => ['text' => 'APPEL COMMUN']],
        ]];
        $this->actingAs($this->editor)->postJson(route('admin.builder.globals.store'), ['name' => 'Bandeau', 'node' => $node])->assertForbidden();

        $global = $this->actingAs($this->admin)->postJson(route('admin.builder.globals.store'), ['name' => 'Bandeau', 'node' => $node])
            ->assertCreated()->json('global');
        $layout = Layout::findOrFail($global['id']);
        $this->assertSame('block', $layout->type);
        $this->assertSame([], $layout->conditions, 'Pas de conditions : affichée par le widget seulement.');

        $this->actingAs($this->editor)->getJson(route('admin.builder.blocks.index'))->assertOk()->assertJsonPath('globals.0.name', 'Bandeau')->assertJsonPath('canCreateGlobal', false);
        $this->getJson(route('admin.builder.globals.render', $layout))->assertOk()->assertJsonPath('name', 'Bandeau');

        // Deux pages l'utilisent ; une modification de la section les met toutes les deux à jour.
        $widget = fn (string $id) => ['id' => $id, 'type' => 'container', 'settings' => [], 'children' => [['id' => $id.'w', 'type' => 'global-block', 'settings' => ['layout' => $layout->id]]]];
        Page::create(['title' => 'Un', 'slug' => 'un', 'status' => 'published', 'content' => ['version' => 2, 'elements' => [$widget('pageun0001')]]]);
        Page::create(['title' => 'Deux', 'slug' => 'deux', 'status' => 'published', 'content' => ['version' => 2, 'elements' => [$widget('pagedeux01')]]]);
        $this->get('/un')->assertSee('APPEL COMMUN')->assertSee('#gp-cta0000001{background-color:#123456}', false);
        $layout->update(['content' => ['version' => 2, 'elements' => [['id' => 'cta0000001', 'type' => 'heading', 'settings' => ['text' => 'NOUVEL APPEL']]]]]);
        $this->get('/un')->assertSee('NOUVEL APPEL')->assertDontSee('APPEL COMMUN');
        $this->get('/deux')->assertSee('NOUVEL APPEL');

        // Une section qui s'inclut elle-même ne boucle pas.
        $layout->update(['content' => ['version' => 2, 'elements' => [['id' => 'loop000001', 'type' => 'global-block', 'settings' => ['layout' => $layout->id]]]]]);
        $this->assertIsString(app(Renderer::class)->render(['version' => 2, 'elements' => [$widget('boucle0001')]])['html']);
    }

    public function test_global_styles_are_validated_saved_and_applied(): void
    {
        $t = GlobalStyles::sanitize([
            'h1' => ['size' => 64, 'size_mobile' => 36, 'weight' => '800', 'letter_spacing' => '-0.02', 'transform' => 'nimporte'],
            'h3' => ['size' => 'abc', 'size_mobile' => 30],
            'links' => ['color' => 'global:accent', 'underline' => 'hover', 'hover' => 'red;}'],
            'buttons' => ['radius' => 999, 'transform' => 'uppercase', 'size' => 15],
            'h7' => ['size' => 20],
        ]);
        $this->assertSame(['size' => 64, 'size_mobile' => 36, 'weight' => '800', 'letter_spacing' => -0.02], $t['h1']);
        $this->assertSame(['size_mobile' => 30], $t['h3']);
        $this->assertArrayNotHasKey('h7', $t);
        $this->assertArrayNotHasKey('hover', $t['links']);

        $css = GlobalStyles::css(['typography' => $t]);
        $this->assertStringContainsString('h1{font-size:clamp(36px, calc(36px + 28 * (100vw - 767px) / 673), 64px);font-weight:800;letter-spacing:-0.02em}', $css);
        $this->assertStringContainsString('a{color:var(--gp-accent);text-decoration:none}a:hover{text-decoration:underline}', $css);
        $this->assertStringContainsString('.gp-btn{border-radius:999px;text-transform:uppercase;letter-spacing:.04em}', $css);
        $this->assertStringContainsString('.gp-btn-md{padding:12px 24px;font-size:15px}', $css);
        $this->assertStringContainsString('@media (max-width: 767px){h3{font-size:30px}}', $css);
        $this->assertSame('', GlobalStyles::css([]));

        // Enregistrés depuis les réglages, appliqués aux pages et au builder (mêmes variables de design).
        $this->actingAs($this->admin)->put(route('admin.settings.update'), [
            'site_name' => 'Mon site', 'primary_color' => '#2563eb', 'secondary_color' => '#0f172a', 'accent_color' => '#f59e0b', 'font_family' => 'Inter',
            'locales' => ['fr'], 'default_locale' => 'fr', 'typography' => ['h2' => ['size' => 44, 'weight' => '700']],
        ])->assertSessionHasNoErrors();
        $this->assertSame(['h2' => ['size' => 44, 'weight' => '700']], Setting::read('typography'));
        $this->assertStringContainsString('h2{font-size:44px;font-weight:700}', DesignTokens::css(Setting::publicValues()));
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published']);
        $this->get('/')->assertSee('h2{font-size:44px;font-weight:700}', false);
        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('name="typography[h2][size]" value="44"', false);
    }
}
