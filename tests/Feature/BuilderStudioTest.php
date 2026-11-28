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
