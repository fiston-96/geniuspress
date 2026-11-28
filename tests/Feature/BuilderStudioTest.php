<?php

namespace Tests\Feature;

use App\Builder\ContentSanitizer;
use App\Builder\CustomCss;
use App\Builder\Renderer;
use App\Models\BuilderBlock;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Outils avancés du builder : CSS personnalisé par élément et « Mes blocs ». */
class BuilderStudioTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_css_is_scoped_to_the_element(): void
    {
        $sel = '#gp-abc123';

        $this->assertSame('#gp-abc123{color: red;}', CustomCss::scope('color: red;', $sel), 'De simples déclarations visent l’élément.');
        $this->assertSame('#gp-abc123:hover{opacity:.8}#gp-abc123 h3,#gp-abc123 .gp-btn{color:global:primary}',
            CustomCss::scope("selector:hover { opacity:.8 }\nh3, selector .gp-btn { color:global:primary }", $sel));
        $this->assertSame('@media (max-width: 767px){#gp-abc123{padding:0}}', CustomCss::scope('@media (max-width: 767px) { selector { padding:0 } }', $sel));
        // Les autres règles @ (keyframes, font-face…) et une classe qui contient le mot ne sont pas touchées.
        $this->assertSame('#gp-abc123 .my-selector{color:red}', CustomCss::scope('@font-face { font-family: x; } .my-selector { color:red }', $sel));
    }

    public function test_dangerous_custom_css_is_refused(): void
    {
        foreach ([
            '@import url(https://exemple.com/x.css);',
            'selector { background: url(javascript:alert(1)) }',
            'selector { width: expression(alert(1)) }',
            '</style><script>alert(1)</script>',
            'selector { content: "\\3c" }',
            'selector { color: red',
            'selector { color: red } }',
            '@media a { @media b { selector { x: y } } }',
            str_repeat('a', CustomCss::MAX + 1),
        ] as $css) {
            $this->assertNull(CustomCss::clean($css), $css);
        }
        $this->assertSame('selector{color:red}', CustomCss::clean('/* commentaire */ selector{color:red}'));
    }

    public function test_custom_css_is_saved_and_rendered_after_the_element_settings(): void
    {
        $content = app(ContentSanitizer::class)->sanitize(['version' => 2, 'elements' => [
            ['id' => 'box0000001', 'type' => 'container', 'settings' => ['css_id' => 'offre', 'background_color' => '#ffffff', 'custom_css' => 'selector { border-radius: 24px } h3 { color: global:primary }'], 'children' => [
                ['id' => 'title00001', 'type' => 'heading', 'settings' => ['text' => 'Titre', 'custom_css' => '@import url(x.css);']],
            ]],
        ]]);

        $this->assertSame('selector { border-radius: 24px } h3 { color: global:primary }', $content['elements'][0]['settings']['custom_css']);
        $this->assertArrayNotHasKey('custom_css', $content['elements'][0]['children'][0]['settings'], 'CSS interdit retiré.');

        $css = app(Renderer::class)->render($content)['css'];
        // L'ancre remplace l'identifiant technique, et les couleurs globales deviennent des variables.
        $this->assertStringContainsString('#offre{background-color:#ffffff}', $css);
        $this->assertStringContainsString('#offre{border-radius: 24px}#offre h3{color: var(--gp-primary)}', $css);
        $this->assertGreaterThan(strpos($css, '#offre{background-color'), strpos($css, '#offre{border-radius'));
    }

    public function test_containers_render_shape_dividers_and_video_backgrounds(): void
    {
        $content = app(ContentSanitizer::class)->sanitize(['version' => 2, 'elements' => [
            ['id' => 'hero000001', 'type' => 'container', 'settings' => [
                'background_video' => '/storage/uploads/fond.mp4', 'background_image' => '/storage/uploads/affiche.jpg', 'overlay_color' => '#000000',
                'shape_bottom' => 'wave', 'shape_bottom_color' => 'global:primary', 'shape_bottom_height' => 120, 'shape_bottom_height_mobile' => 40,
                'shape_bottom_flip' => true, 'shape_top' => 'inconnue', 'shape_top_color' => '#ff0000',
            ], 'children' => []],
            ['id' => 'hero000002', 'type' => 'container', 'settings' => ['background_video' => 'javascript:alert(1)', 'background_video_mobile' => true, 'shape_top' => 'waves', 'shape_top_front' => true], 'children' => []],
        ]]);
        $this->assertArrayNotHasKey('shape_top', $content['elements'][0]['settings'], 'Forme inconnue retirée.');
        $this->assertArrayNotHasKey('background_video', $content['elements'][1]['settings']);

        $out = app(Renderer::class)->render($content);
        $html = $out['html'];
        // Ordre des calques : vidéo, voile, séparateur, puis le contenu.
        $this->assertStringContainsString('<div class="gp-bg-video gp-bg-video--no-mobile" aria-hidden="true"><video src="/storage/uploads/fond.mp4" autoplay muted loop playsinline preload="metadata"></video></div><div class="gp-overlay"></div><div class="gp-shape gp-shape--bottom" aria-hidden="true"><svg viewBox="0 0 1000 100" preserveAspectRatio="none"><path d="M0 0H1000V60', $html);
        $this->assertStringContainsString('</svg></div><div class="gp-con-inner">', $html);
        $this->assertSame(3, substr_count($html, '<path d="M0 0H1000V'.'75') + substr_count($html, '<path d="M0 0H1000V55') + substr_count($html, '<path d="M0 0H1000V35'), 'Vagues superposées : trois tracés.');
        $this->assertStringContainsString('opacity="0.25"', $html);

        $css = $out['css'];
        $this->assertStringContainsString('#gp-hero000001 > .gp-shape--bottom{color:var(--gp-primary)}', $css);
        $this->assertStringContainsString('#gp-hero000001 > .gp-shape--bottom svg{transform:translateX(-50%) rotateY(180deg)}', $css);
        $this->assertStringContainsString('#gp-hero000001 > .gp-shape--bottom svg{height:120px}', $css);
        $this->assertMatchesRegularExpression('/@media \(max-width: 767px\) \{.*#gp-hero000001 > \.gp-shape--bottom svg\{height:40px\}/s', $css);
        $this->assertStringContainsString('#gp-hero000002 > .gp-shape--top{z-index:2}', $css);
    }

    public function test_media_library_accepts_videos(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $video = \Illuminate\Http\UploadedFile::fake()->create('fond.mp4', 12000, 'video/mp4');

        $this->actingAs($admin)->postJson(route('admin.media.store'), ['file' => $video])->assertCreated()->assertJsonPath('mime_type', 'video/mp4');
        // Une image reste limitée à 10 Mo.
        $this->actingAs($admin)->postJson(route('admin.media.store'), ['file' => \Illuminate\Http\UploadedFile::fake()->create('grande.png', 12000, 'image/png')])->assertStatus(422);
    }

    public function test_blocks_are_saved_sanitized_and_listed(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);

        $this->actingAs($editor)->postJson(route('admin.builder.blocks.store'), [
            'name' => 'Bandeau d’appel',
            'node' => ['id' => 'cta0000001', 'type' => 'container', 'settings' => ['background_color' => 'javascript:alert(1)', 'padding' => ['t' => 40, 'u' => 'px']], 'children' => [
                ['id' => 'cta0000002', 'type' => 'text', 'settings' => ['html' => '<p onclick="x()">Bonjour<script>alert(1)</script></p>']],
            ]],
        ])->assertCreated()->assertJsonPath('block.name', 'Bandeau d’appel')->assertJsonPath('block.user', $editor->name);

        $block = BuilderBlock::firstOrFail();
        $this->assertArrayNotHasKey('background_color', $block->node['settings']);
        $this->assertStringNotContainsString('script', $block->node['children'][0]['settings']['html']);
        $this->assertStringNotContainsString('onclick', $block->node['children'][0]['settings']['html']);

        $this->actingAs($editor)->getJson(route('admin.builder.blocks.index'))
            ->assertOk()->assertJsonCount(1, 'blocks')->assertJsonPath('blocks.0.node.type', 'container');

        // Un élément inconnu ou vide n'est pas un bloc.
        $this->actingAs($editor)->postJson(route('admin.builder.blocks.store'), ['name' => 'Vide', 'node' => ['type' => '<b>']])->assertStatus(422);
        $this->assertSame(1, BuilderBlock::count());
    }

    public function test_only_the_author_or_an_admin_can_delete_a_block(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $other = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $make = fn () => BuilderBlock::create(['name' => 'Titre', 'node' => ['id' => 'h000000001', 'type' => 'heading', 'settings' => ['text' => 'Bonjour']], 'user_id' => $author->id]);

        $block = $make();
        $this->actingAs($other)->deleteJson(route('admin.builder.blocks.destroy', $block))->assertForbidden();
        $this->actingAs($author)->deleteJson(route('admin.builder.blocks.destroy', $block))->assertNoContent();

        $block = $make();
        $this->actingAs($admin)->deleteJson(route('admin.builder.blocks.destroy', $block))->assertNoContent();
        $this->assertSame(0, BuilderBlock::count());

        $this->app['auth']->forgetGuards();
        $this->getJson(route('admin.builder.blocks.index'))->assertUnauthorized();
    }

    public function test_builder_receives_the_blocks_addresses(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $page = Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'status' => 'draft', 'content' => ['version' => 2, 'elements' => []]]);

        $this->actingAs($admin)->get(route('admin.pages.builder', $page))
            ->assertOk()->assertSee(str_replace('/', '\/', route('admin.builder.blocks.index')), false);
    }
}
