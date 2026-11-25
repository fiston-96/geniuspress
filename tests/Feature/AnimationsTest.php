<?php

namespace Tests\Feature;

use App\Builder\ContentSanitizer;
use App\Builder\Renderer;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Animations : apparition à l'écran, effets au survol et courbes de transition, sur tous les éléments. */
class AnimationsTest extends TestCase
{
    use RefreshDatabase;

    private function render(array $elements): array
    {
        return app(Renderer::class)->render(['version' => 2, 'elements' => $elements]);
    }

    public function test_entrance_and_hover_classes_and_css(): void
    {
        $out = $this->render([
            ['id' => 'c000000001', 'type' => 'container', 'settings' => ['entrance' => 'fade-up', 'entrance_duration' => 900, 'entrance_delay' => 200, 'entrance_easing' => 'back', 'entrance_repeat' => true], 'children' => [
                ['id' => 'w000000001', 'type' => 'heading', 'settings' => ['text' => 'Titre', 'hover_effect' => 'grow', 'transition' => 500, 'transition_easing' => 'smooth']],
                ['id' => 'w000000002', 'type' => 'text', 'settings' => ['html' => '<p>Texte</p>']],
            ]],
        ]);

        $this->assertStringContainsString('class="gp-el gp-con gp-anim gp-anim--fade-up gp-anim--repeat"', $out['html']);
        $this->assertStringContainsString('class="gp-el gp-widget gp-heading gp-hv gp-hv--grow"', $out['html']);
        $this->assertStringContainsString('class="gp-el gp-widget gp-text"', $out['html'], 'Sans réglage, aucune classe ajoutée.');

        $this->assertStringContainsString('#gp-c000000001{--gp-anim-dur:900ms;--gp-anim-delay:200ms;--gp-anim-ease:cubic-bezier(.34,1.56,.64,1)}', $out['css']);
        $this->assertStringContainsString('transform 500ms cubic-bezier(.22,1,.36,1)', $out['css'], 'La transformation du survol suit la transition.');
        $this->assertStringContainsString('background-color 500ms cubic-bezier(.22,1,.36,1)', $out['css']);
    }

    public function test_hover_colors_without_effect_keep_the_previous_css(): void
    {
        $out = $this->render([['id' => 'w000000001', 'type' => 'text', 'settings' => ['html' => '<p>x</p>', 'background_color_hover' => '#ff0000']]]);
        $this->assertStringContainsString('{transition:background-color 300ms, color 300ms, border-color 300ms, box-shadow 300ms, opacity 300ms}', $out['css']);
    }

    public function test_unknown_values_are_refused(): void
    {
        $clean = app(ContentSanitizer::class)->sanitize(['version' => 2, 'elements' => [['id' => 'w000000001', 'type' => 'heading', 'settings' => [
            'text' => 'x', 'entrance' => 'fade-up" onmouseover="alert(1)', 'hover_effect' => 'explode', 'entrance_duration' => 999999, 'entrance_easing' => 'cubic-bezier(9,9,9,9)', 'transition_easing' => 'smooth',
        ]]]])['elements'][0]['settings'];

        $this->assertArrayNotHasKey('entrance', $clean);
        $this->assertArrayNotHasKey('hover_effect', $clean);
        $this->assertArrayNotHasKey('entrance_easing', $clean);
        $this->assertTrue(! isset($clean['entrance_duration']) || $clean['entrance_duration'] <= 5000);
        $this->assertSame('smooth', $clean['transition_easing']);
    }

    public function test_public_page_hides_animated_elements_only_with_javascript(): void
    {
        $this->withoutVite();
        \App\Models\User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published', 'content' => ['version' => 2, 'elements' => [
            ['id' => 'w000000001', 'type' => 'heading', 'settings' => ['text' => 'Bonjour', 'entrance' => 'zoom-in']],
        ]]]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString("document.documentElement.classList.add('gp-js')", $html);
        $this->assertStringContainsString('if(!window.gpAnimReady)', $html, 'Sans le script du site, tout redevient visible.');
        $this->assertStringContainsString('gp-anim gp-anim--zoom-in', $html);
    }
}
