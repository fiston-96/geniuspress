<?php

namespace Tests\Feature;

use App\Builder\ContentSanitizer;
use App\Builder\Renderer;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeniusPressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
    }

    private function content(array $widgets): array
    {
        return ['sections' => [['id' => 'section0001', 'settings' => [], 'columns' => [['id' => 'column0001', 'settings' => [], 'widgets' => $widgets]]]]];
    }

    public function test_redirects_to_install_until_an_account_exists(): void
    {
        $this->get('/')->assertRedirect('/install');
        $this->get('/admin/login')->assertRedirect('/install');
    }

    public function test_guests_cannot_reach_admin(): void
    {
        $this->admin();
        $this->get('/admin/pages')->assertRedirect('/admin/login');
        $this->putJson('/admin/pages/1/content', [])->assertUnauthorized();
    }

    public function test_editors_cannot_change_site_settings(): void
    {
        $this->admin();
        $editor = User::create(['name' => 'Ed', 'email' => 'e@test.local', 'password' => 'secret-password-1', 'role' => 'editor']);
        $this->actingAs($editor)->get('/admin/settings')->assertForbidden();
    }

    public function test_sanitizer_removes_dangerous_content(): void
    {
        $clean = app(ContentSanitizer::class)->sanitize($this->content([
            ['type' => 'text', 'settings' => ['html' => '<p onclick="x()">Bonjour<script>alert(1)</script></p>']],
            ['type' => 'button', 'settings' => ['url' => 'javascript:alert(1)', 'bg_color' => 'red;background:url(x)']],
            ['type' => '<script>', 'settings' => []],
            ['type' => 'heading', 'settings' => ['font_size' => 9999, 'tag' => 'script']],
        ]));

        $widgets = $clean['sections'][0]['columns'][0]['widgets'];
        $this->assertCount(3, $widgets);
        $this->assertSame('<p>Bonjour</p>', $widgets[0]['settings']['html']);
        $this->assertArrayNotHasKey('url', $widgets[1]['settings']);
        $this->assertArrayNotHasKey('bg_color', $widgets[1]['settings']);
        $this->assertSame(160, $widgets[2]['settings']['font_size']);
        $this->assertArrayNotHasKey('tag', $widgets[2]['settings']);
    }

    public function test_widgets_of_a_disabled_plugin_are_kept_but_never_rendered(): void
    {
        $content = $this->content([
            ['id' => 'orphan0001', 'type' => 'acme-gallery', 'settings' => ['images' => ['a.jpg'], 'html' => '<script>x</script>']],
            ['id' => 'heading001', 'type' => 'heading', 'settings' => ['text' => 'Visible']],
        ]);

        $clean = app(ContentSanitizer::class)->sanitize($content);
        $orphan = $clean['sections'][0]['columns'][0]['widgets'][0];
        $this->assertTrue($orphan['orphan']);
        $this->assertSame(['a.jpg'], $orphan['settings']['images']);

        $html = app(Renderer::class)->render($content)['html'];
        $this->assertStringContainsString('Visible', $html);
        $this->assertStringNotContainsString('orphan0001', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_renderer_escapes_text_and_only_embeds_known_video_hosts(): void
    {
        $out = app(Renderer::class)->render($this->content([
            ['id' => 'heading001', 'type' => 'heading', 'settings' => ['text' => '<img src=x onerror=alert(1)>', 'color' => '#ff0000']],
            ['id' => 'video00001', 'type' => 'video', 'settings' => ['url' => 'https://evil.example/embed']],
            ['id' => 'video00002', 'type' => 'video', 'settings' => ['url' => 'https://youtu.be/dQw4w9WgXcQ']],
        ]));

        $this->assertStringNotContainsString('<img src=x', $out['html']);
        $this->assertStringContainsString('&lt;img', $out['html']);
        $this->assertStringNotContainsString('evil.example', $out['html']);
        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', $out['html']);
        $this->assertStringContainsString('#gp-heading001 > *{color:#ff0000}', $out['css']);
    }

    public function test_saving_a_published_page_keeps_live_version_until_publish(): void
    {
        $admin = $this->admin();
        $page = Page::create(['title' => 'À propos', 'slug' => 'a-propos', 'status' => 'published', 'content' => $this->content([
            ['id' => 'heading001', 'type' => 'heading', 'settings' => ['text' => 'Version en ligne']],
        ])]);
        $draft = $this->content([['id' => 'heading001', 'type' => 'heading', 'settings' => ['text' => 'Version brouillon']]]);

        $this->actingAs($admin)->putJson("/admin/pages/{$page->id}/content", ['content' => $draft])
            ->assertOk()->assertJson(['has_draft' => true]);

        auth()->logout();
        $this->get('/a-propos')->assertOk()->assertSee('Version en ligne')->assertDontSee('Version brouillon');

        $this->actingAs($admin)->putJson("/admin/pages/{$page->id}/content", ['content' => $draft, 'publish' => true])
            ->assertOk()->assertJson(['has_draft' => false]);
        auth()->logout();
        $this->get('/a-propos')->assertSee('Version brouillon');
        $this->assertSame(2, $page->revisions()->count());
    }

    public function test_draft_pages_are_hidden_from_visitors(): void
    {
        $admin = $this->admin();
        Page::create(['title' => 'Secret', 'slug' => 'secret', 'status' => 'draft']);

        $this->get('/secret')->assertNotFound();
        $this->actingAs($admin)->get('/secret')->assertOk()->assertSee('noindex', false);
    }

    public function test_page_slugs_are_unique_per_language(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'Contact', 'locale' => 'fr'])->assertRedirect();
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'Contact', 'locale' => 'fr'])->assertSessionHasErrors('slug');
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'Admin', 'locale' => 'fr'])->assertSessionHasErrors('slug');
    }
}
