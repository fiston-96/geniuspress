<?php

namespace Tests\Feature;

use App\Models\Layout;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Site\Popups;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Popups : gabarits du thème de type « popup », déclencheurs, fréquence et apparence. */
class PopupsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Setting::write(['locales' => ['fr']]);
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published']);
        Page::create(['title' => 'Contact', 'slug' => 'contact', 'status' => 'published']);
    }

    private function popup(string $text, array $attributes = []): Layout
    {
        return Layout::create($attributes + [
            'name' => 'Offre '.$text, 'type' => 'popup', 'status' => Layout::STATUS_PUBLISHED,
            'content' => ['version' => 2, 'elements' => [['id' => 'w'.substr(md5($text), 0, 9), 'type' => 'heading', 'settings' => ['text' => $text]]]],
        ]);
    }

    public function test_options_are_sanitized_with_defaults(): void
    {
        $o = Popups::sanitize(['trigger' => 'scroll', 'scroll' => 900, 'delay' => -4, 'position' => 'ailleurs', 'background' => 'red;}', 'overlay' => '0', 'width' => 'abc']);

        $this->assertSame('scroll', $o['trigger']);
        $this->assertSame(100, $o['scroll']);
        $this->assertSame(0, $o['delay']);
        $this->assertSame('center', $o['position']);
        $this->assertSame('#ffffff', $o['background']);
        $this->assertFalse($o['overlay']);
        $this->assertSame(560, $o['width']);
        $this->assertSame(Popups::DEFAULTS, Popups::sanitize(null));
    }

    public function test_matching_popups_are_rendered_hidden_with_their_settings(): void
    {
        $this->popup('PARTOUT', ['options' => ['trigger' => 'exit', 'frequency' => 'once', 'position' => 'bottom-right', 'background' => 'global:primary']]);
        $contact = Page::where('slug', 'contact')->first();
        $this->popup('SAUF CONTACT', ['priority' => 5, 'conditions' => [['mode' => 'include', 'target' => 'all'], ['mode' => 'exclude', 'target' => 'page', 'id' => $contact->id]]]);
        $this->popup('INACTIVE', ['status' => Layout::STATUS_DRAFT]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('PARTOUT', $html);
        $this->assertStringContainsString('SAUF CONTACT', $html);
        $this->assertStringNotContainsString('INACTIVE', $html);
        // La plus prioritaire d'abord ; cachées au chargement, ouvertes par le script du site.
        $this->assertLessThan(strpos($html, '>PARTOUT<'), strpos($html, '>SAUF CONTACT<'));
        $this->assertMatchesRegularExpression('/<div id="popup-\d+" class="gp-popup gp-popup--bottom-right gp-popup--zoom" hidden\s+role="dialog" aria-modal="false"/', $html);
        $this->assertStringContainsString('data-trigger="exit"', $html);
        $this->assertStringContainsString('data-frequency="once"', $html);
        $this->assertStringContainsString('--gp-popup-bg:var(--gp-primary)', $html);
        $this->assertStringContainsString('aria-label="Fermer"', $html);

        $this->get('/contact')->assertOk()->assertSee('PARTOUT')->assertDontSee('SAUF CONTACT');
    }

    public function test_admin_configures_and_previews_a_popup(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('admin.layouts.store'), ['type' => 'popup', 'name' => 'Newsletter'])->assertRedirect();
        $layout = Layout::sole();

        $this->get(route('admin.layouts.edit', $layout))->assertOk()->assertSee('Ouverture de la popup')->assertSee('#popup-'.$layout->id);
        $this->put(route('admin.layouts.update', $layout), ['name' => 'Newsletter', 'status' => 'published', 'priority' => 0, 'locale' => '',
            'rules' => [['mode' => 'include', 'what' => 'all']],
            'options' => ['trigger' => 'delay', 'delay' => 12, 'frequency' => 'session', 'position' => 'left', 'overlay' => '0', 'close_outside' => '1', 'close_button' => '1', 'nimporte' => 'x'],
        ])->assertSessionHasNoErrors();
        $options = $layout->refresh()->options;
        $this->assertSame(12, $options['delay']);
        $this->assertSame('session', $options['frequency']);
        $this->assertFalse($options['overlay']);
        $this->assertArrayNotHasKey('nimporte', $options);

        // Aperçu depuis le builder : la popup s'ouvre tout de suite, à chaque fois.
        $html = $this->post(route('admin.layouts.preview', $layout), ['content' => json_encode(['version' => 2, 'elements' => [['id' => 'wapercu001', 'type' => 'heading', 'settings' => ['text' => 'APERÇU POPUP']]]])])
            ->assertOk()->getContent();
        $this->assertStringContainsString('APERÇU POPUP', $html);
        $this->assertStringContainsString('data-trigger="load"', $html);
        $this->assertStringContainsString('data-frequency="always"', $html);
        $this->assertStringContainsString('data-preview', $html);
        $this->assertSame(1, substr_count($html, 'data-popup="'.$layout->id.'"'), 'La popup en aperçu n’apparaît qu’une fois.');
    }

    public function test_other_layouts_keep_no_popup_options(): void
    {
        $this->actingAs($this->admin);
        $header = Layout::create(['name' => 'En-tête', 'type' => 'header']);
        $this->put(route('admin.layouts.update', $header), ['name' => 'En-tête', 'status' => 'draft', 'priority' => 0, 'locale' => '', 'options' => ['trigger' => 'load']])
            ->assertSessionHasNoErrors();
        $this->assertNull($header->refresh()->options);
        $this->get(route('admin.layouts.edit', $header))->assertOk()->assertDontSee('Ouverture de la popup');
    }
}
