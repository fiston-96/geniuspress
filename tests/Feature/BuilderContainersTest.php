<?php

namespace Tests\Feature;

use App\Builder\ContentMigrator;
use App\Builder\ContentSanitizer;
use App\Builder\DesignTokens;
use App\Builder\Renderer;
use App\Builder\Schema;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Format 2 : conteneurs imbriqués, style commun, réglages par appareil, couleurs et polices globales. */
class BuilderContainersTest extends TestCase
{
    use RefreshDatabase;

    private function sanitize(mixed $content): array
    {
        return app(ContentSanitizer::class)->sanitize($content);
    }

    private function render(array $content): array
    {
        return app(Renderer::class)->render($content);
    }

    private function v1(): array
    {
        return ['version' => 1, 'sections' => [
            ['id' => 'section0001', 'settings' => ['background_color' => '#0f172a', 'padding_top' => 9999, 'padding_bottom_mobile' => 30, 'anchor' => 'contact', 'gap' => 40, 'vertical_align' => 'center'],
                'columns' => [
                    ['id' => 'column0001', 'settings' => ['width' => 30, 'padding' => 12, 'border_radius' => 8, 'vertical_align' => 'end'], 'widgets' => [
                        ['id' => 'heading001', 'type' => 'heading', 'settings' => ['text' => 'Bonjour', 'margin_bottom' => 16, 'hide_desktop' => true]],
                    ]],
                    ['id' => 'column0002', 'settings' => ['width' => 70], 'widgets' => []],
                ]],
            ['id' => 'section0002', 'settings' => [], 'columns' => [
                ['id' => 'column0003', 'settings' => [], 'widgets' => [['id' => 'text000001', 'type' => 'text', 'settings' => ['html' => '<p>Seul</p>']]]],
            ]],
        ]];
    }

    public function test_old_pages_are_converted_to_containers_with_the_same_layout(): void
    {
        $content = $this->sanitize($this->v1());

        $this->assertSame(Schema::VERSION, $content['version']);
        [$row, $single] = $content['elements'];

        // Section à plusieurs colonnes : conteneur en ligne, en colonne sur mobile, colonnes pleine largeur.
        $this->assertSame('container', $row['type']);
        $this->assertSame('section0001', $row['id']);
        $this->assertSame(['tag' => 'section', 'direction' => 'row', 'direction_mobile' => 'column', 'align' => 'center', 'align_mobile' => 'stretch', 'gap' => 40],
            array_intersect_key($row['settings'], array_flip(['tag', 'direction', 'direction_mobile', 'align', 'align_mobile', 'gap'])));
        $this->assertSame(['t' => 600, 'r' => 20, 'b' => 64, 'l' => 20], $row['settings']['padding'], 'Les valeurs hors limites sont bornées comme avant.');
        $this->assertSame(['b' => 30], $row['settings']['padding_mobile']);
        $this->assertSame('contact', $row['settings']['css_id']);

        [$col1, $col2] = $row['children'];
        $this->assertSame(['30%', '70%'], [$col1['settings']['width'], $col2['settings']['width']]);
        $this->assertSame(['t' => 12, 'r' => 12, 'b' => 12, 'l' => 12], $col1['settings']['padding']);
        $this->assertSame(['t' => 8, 'r' => 8, 'b' => 8, 'l' => 8], $col1['settings']['border_radius']);
        $this->assertSame('end', $col1['settings']['justify']);

        $heading = $col1['children'][0];
        $this->assertSame(['b' => 16], $heading['settings']['margin']);
        $this->assertArrayNotHasKey('margin_bottom', $heading['settings']);
        $this->assertTrue($heading['settings']['hide_tablet'], '« Masquer sur ordinateur » couvrait aussi la tablette.');

        // Section à une seule colonne sans réglage : les widgets sont directement dans le conteneur.
        $this->assertSame('column', $single['settings']['direction']);
        $this->assertSame('text', $single['children'][0]['type']);
    }

    public function test_nested_containers_are_validated(): void
    {
        $deep = ['type' => 'heading', 'settings' => ['text' => 'Fond']];
        for ($i = 0; $i < Schema::MAX_DEPTH + 3; $i++) {
            $deep = ['type' => 'container', 'settings' => [], 'children' => [$deep]];
        }

        $content = $this->sanitize(['elements' => [
            $deep,
            ['id' => 'same000001', 'type' => 'container', 'settings' => [
                'direction' => 'diagonal',
                'gap' => 99999,
                'width' => 'calc(100% - 1px);color:red',
                'min_height' => '80vh',
                'margin' => ['t' => '12', 'b' => -40, 'x' => 5, 'u' => 'em'],
                'padding' => 'a',
                'background_color' => 'red;}body{display:none',
                'text_color' => 'rgba(10, 20, 30, .5)',
                'border_color' => 'global:accent',
                'gradient_from' => 'global:bad id',
                'box_shadow' => ['x' => 2, 'y' => 999, 'blur' => -3, 'color' => '#000', 'evil' => 'x'],
                'css_classes' => 'ok "><script> autre_classe',
                'css_id' => 'Mon Ancre!',
                'background_image' => 'javascript:alert(1)',
                'inconnu' => 'x',
            ], 'children' => [['id' => 'same000001', 'type' => 'heading', 'settings' => ['font_family' => 'Comic Sans', 'line_height' => '1.4', 'font_size_tablet' => 999]]]],
            ['type' => 'plugin-absent', 'settings' => ['a' => 1]],
            ['type' => '<script>', 'settings' => []],
        ]]);

        // Profondeur limitée.
        $depth = 0;
        for ($n = $content['elements'][0]; ($n['type'] ?? null) === 'container'; $n = $n['children'][0] ?? null) {
            $depth++;
        }
        $this->assertSame(Schema::MAX_DEPTH, $depth);

        $box = $content['elements'][1];
        $s = $box['settings'];
        $this->assertEquals(['gap' => 400, 'min_height' => '80vh', 'margin' => ['t' => 12, 'b' => -40, 'u' => 'em'], 'text_color' => 'rgba(10,20,30,0.5)', 'border_color' => 'global:accent'],
            array_intersect_key($s, array_flip(['gap', 'min_height', 'margin', 'text_color', 'border_color'])));
        foreach (['direction', 'width', 'padding', 'background_color', 'gradient_from', 'background_image', 'inconnu'] as $rejected) {
            $this->assertArrayNotHasKey($rejected, $s, $rejected);
        }
        $this->assertSame(['x' => 2, 'y' => 200, 'blur' => 0, 'spread' => 0, 'color' => '#000'], $s['box_shadow']);
        $this->assertSame('ok autre_classe', $s['css_classes']);
        $this->assertSame('mon-ancre', $s['css_id']);

        // Identifiants uniques, typographie validée.
        $heading = $box['children'][0];
        $this->assertNotSame('same000001', $heading['id']);
        $this->assertArrayNotHasKey('font_family', $heading['settings']);
        $this->assertSame('1.4', $heading['settings']['line_height']);
        $this->assertSame(160, $heading['settings']['font_size_tablet']);

        // Widget d'un plugin désactivé : conservé tel quel ; type invalide : ignoré.
        $this->assertCount(3, $content['elements']);
        $this->assertTrue($content['elements'][2]['orphan']);
    }

    public function test_renderer_outputs_nested_html_and_css_for_each_device(): void
    {
        $out = $this->render(['elements' => [[
            'id' => 'hero000001', 'type' => 'container',
            'settings' => ['tag' => 'header', 'css_id' => 'accueil', 'direction' => 'row', 'direction_mobile' => 'column', 'gap' => 30, 'padding_tablet' => ['t' => 10],
                'background_color' => 'global:primary', 'background_color_hover' => '#000000', 'hide_mobile' => true, 'css_classes' => 'hero'],
            'children' => [
                ['id' => 'left000001', 'type' => 'container', 'settings' => ['width' => '25%'], 'children' => [
                    ['id' => 'title00001', 'type' => 'heading', 'settings' => ['text' => 'Titre', 'font_size' => 50, 'font_size_mobile' => 30, 'font_family' => 'Poppins']],
                ]],
                ['id' => 'right00001', 'type' => 'container', 'settings' => ['width' => '75%'], 'children' => []],
            ],
        ]]]);

        $html = $out['html'];
        $this->assertStringContainsString('<header id="accueil" class="gp-el gp-con gp-hide-mobile hero"><div class="gp-con-inner">', $html);
        $this->assertStringContainsString('<div id="gp-left000001" class="gp-el gp-con"><div class="gp-con-inner"><div id="gp-title00001" class="gp-el gp-widget gp-heading"><h2>Titre</h2>', $html);

        $css = $out['css'];
        $this->assertStringContainsString('#accueil{background-color:var(--gp-primary)', $css, 'Couleur globale et ancre.');
        $this->assertStringContainsString('#accueil:hover{background-color:#000000}', $css);
        $this->assertStringContainsString('#accueil > .gp-con-inner{flex-direction:row;gap:30px;max-width:1140px}', $css);
        $this->assertStringContainsString('#gp-left000001{flex:0 1 calc(25% - 15px);width:auto}', $css, 'L’espace est réparti entre les colonnes.');
        $this->assertMatchesRegularExpression('/@media \(max-width: 1024px\) \{\n#accueil\{padding-top:10px\}/', $css);
        $this->assertMatchesRegularExpression('/@media \(max-width: 767px\) \{[^}]*#accueil > \.gp-con-inner\{flex-direction:column\}/', $css);
        // En colonne sur mobile, la largeur choisie pour la ligne ne s'applique plus.
        $this->assertMatchesRegularExpression('/@media \(max-width: 767px\) \{.*#gp-left000001\{flex:0 1 auto;width:auto\}/s', $css);
        $this->assertMatchesRegularExpression('/@media \(max-width: 767px\) \{.*#gp-title00001 > \*\{font-size:30px\}/s', $css);
        $this->assertSame(['Poppins'], $out['fonts']);
    }

    public function test_stored_pages_and_revisions_are_converted_by_the_migration(): void
    {
        $page = Page::create(['title' => 'Ancienne', 'slug' => 'ancienne', 'status' => 'published', 'content' => ['version' => 2, 'elements' => []]]);
        DB::table('pages')->where('id', $page->id)->update(['content' => json_encode($this->v1()), 'draft_content' => json_encode($this->v1())]);
        $user = User::create(['name' => 'A', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        $revision = PageRevision::create(['page_id' => $page->id, 'user_id' => $user->id, 'content' => $this->v1(), 'note' => 'x']);

        (require database_path('migrations/2026_11_25_000100_convert_page_content_to_containers.php'))->up();

        $page->refresh();
        $this->assertSame(2, $page->content['version']);
        $this->assertSame('section0001', $page->content['elements'][0]['id']);
        $this->assertSame(2, $page->draft_content['version']);
        $this->assertSame(2, $revision->fresh()->content['version']);

        // Une version restaurée est toujours renvoyée au format actuel.
        DB::table('page_revisions')->where('id', $revision->id)->update(['content' => json_encode($this->v1())]);
        $this->actingAs($user)->postJson("/admin/pages/{$page->id}/revisions/{$revision->id}/restore")
            ->assertOk()->assertJsonPath('content.version', 2)->assertJsonPath('content.elements.0.type', 'container');
    }

    public function test_builder_receives_current_format_and_site_design(): void
    {
        $user = User::create(['name' => 'A', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['custom_colors' => [['id' => 'sable', 'name' => 'Sable', 'value' => '#f5e6c8']], 'heading_font' => 'Poppins']);
        $page = Page::create(['title' => 'P', 'slug' => 'p', 'content' => ['version' => 2, 'elements' => []]]);
        DB::table('pages')->where('id', $page->id)->update(['content' => json_encode($this->v1())]);

        $html = $this->actingAs($user)->get("/admin/pages/{$page->id}/builder")->assertOk()->getContent();
        preg_match('#<script type="application/json" id="gp-boot">(.*?)</script>#s', $html, $m);
        $boot = json_decode(html_entity_decode($m[1]), true);

        $this->assertSame(2, $boot['page']['content']['version']);
        $this->assertSame(['primary', 'secondary', 'accent', 'sable'], array_column($boot['design']['colors'], 'id'));
        $this->assertStringContainsString('--gp-color-sable:#f5e6c8', $boot['design']['css']);
        $this->assertStringContainsString("--gp-heading-font:'Poppins'", $boot['design']['css']);
        $this->assertStringContainsString('poppins', $boot['design']['fontsUrl']);
    }

    public function test_global_colors_and_fonts_are_saved_and_used_on_public_pages(): void
    {
        $admin = User::create(['name' => 'A', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        $this->actingAs($admin)->put('/admin/settings', [
            'site_name' => 'ONG', 'primary_color' => '#112233', 'secondary_color' => '#0f172a', 'accent_color' => '#f59e0b',
            'font_family' => 'Inter', 'heading_font' => 'Merriweather', 'locales' => ['fr'], 'default_locale' => 'fr',
            'custom_colors' => [
                ['name' => 'Vert forêt', 'value' => '#14532d'],
                ['name' => 'Vert forêt', 'value' => '#166534'],
                ['name' => 'Piège', 'value' => 'red;}body{x'],
                ['name' => '', 'value' => '#ffffff'],
            ],
        ])->assertSessionHasNoErrors();

        $colors = Setting::read('custom_colors');
        $this->assertSame([['id' => 'vert-foret', 'name' => 'Vert forêt', 'value' => '#14532d'], ['id' => 'vert-foret-2', 'name' => 'Vert forêt', 'value' => '#166534']], $colors);

        // Un identifiant existant ne change pas quand on renomme la couleur.
        $this->assertSame('vert-foret', DesignTokens::customColors([['id' => 'vert-foret', 'name' => 'Vert sapin', 'value' => '#14532d']])[0]['id']);

        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'status' => 'published', 'is_home' => true, 'content' => ['elements' => [
            ['id' => 'box0000001', 'type' => 'container', 'settings' => ['background_color' => 'global:vert-foret'], 'children' => [
                ['id' => 'txt0000001', 'type' => 'text', 'settings' => ['html' => '<p>Bonjour</p>', 'font_family' => 'Lato']],
            ]],
        ]]]);

        $this->get('/')->assertOk()
            ->assertSee('--gp-primary:#112233', false)
            ->assertSee('--gp-color-vert-foret:#14532d', false)
            ->assertSee("--gp-heading-font:'Merriweather'", false)
            ->assertSee('#gp-box0000001{background-color:var(--gp-color-vert-foret)}', false)
            ->assertSee('family=inter:400,500,600,700,800|merriweather:400,500,600,700,800|lato:400,500,600,700,800', false);
    }

    public function test_ai_layouts_in_the_old_format_still_work(): void
    {
        $this->assertTrue(ContentMigrator::isV1(['sections' => []]));
        $this->assertFalse(ContentMigrator::isV1(['elements' => [], 'sections' => []]));
        $this->assertSame(['version' => 2, 'elements' => []], $this->sanitize(['sections' => 'nimporte']));
        $this->assertSame(['version' => 2, 'elements' => []], $this->sanitize('pas un tableau'));
    }
}
