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

    public function test_sticky_elements(): void
    {
        $out = $this->render([
            ['id' => 'c000000001', 'type' => 'container', 'settings' => ['sticky' => 'top', 'sticky_offset' => 10, 'sticky_devices' => 'desktop', 'sticky_bg' => '#ffffff', 'sticky_shadow' => true], 'children' => []],
            ['id' => 'w000000001', 'type' => 'button', 'settings' => ['text' => 'Appeler', 'sticky' => 'bottom', 'z_index' => 90, 'sticky_devices' => 'desktop-tablet']],
        ]);

        $this->assertStringContainsString('class="gp-el gp-con gp-sticky gp-sticky--top"', $out['html']);
        $this->assertStringContainsString('position:sticky;top:10px', $out['css']);
        $this->assertStringContainsString('z-index:50', $out['css'], 'Au-dessus du contenu par défaut.');
        $this->assertStringContainsString('#gp-c000000001.is-stuck{background-color:#ffffff;box-shadow:0 10px 30px -12px rgba(15,23,42,.35)}', $out['css']);
        $this->assertStringContainsString('z-index:90;position:sticky;bottom:0px', $out['css']);
        // Ordinateur seulement : l'élément reprend sa place sur tablette ; ordinateur et tablette : sur mobile.
        $this->assertMatchesRegularExpression('~@media \(max-width: 1024px\) \{[^@]*#gp-c000000001\{position:relative;top:auto;bottom:auto\}~', $out['css']);
        $this->assertMatchesRegularExpression('~@media \(max-width: 767px\) \{[^@]*#gp-w000000001\{position:relative;top:auto;bottom:auto\}~', $out['css']);
    }

    public function test_parallax_background_and_scroll_effects(): void
    {
        $out = $this->render([
            ['id' => 'c000000001', 'type' => 'container', 'settings' => ['background_image' => '/fond.jpg', 'background_parallax' => 4, 'background_fixed' => true], 'children' => [
                ['id' => 'w000000001', 'type' => 'heading', 'settings' => ['text' => 'A', 'scroll_effect' => 'fade', 'scroll_intensity' => 7]],
                ['id' => 'w000000002', 'type' => 'heading', 'settings' => ['text' => 'B', 'scroll_effect' => 'move-up']],
            ]],
            ['id' => 'c000000002', 'type' => 'container', 'settings' => ['background_type' => 'gradient', 'gradient_from' => '#000000', 'background_image' => '/x.jpg', 'background_parallax' => 4], 'children' => []],
        ]);

        $this->assertStringContainsString('class="gp-el gp-con gp-parallax"', $out['html']);
        $this->assertStringContainsString('--gp-bg-img:url("/fond.jpg");--gp-plx:0.4', $out['css']);
        $this->assertStringNotContainsString('background-attachment:fixed', $out['css'], 'La parallaxe remplace l’image fixe.');
        $this->assertStringNotContainsString('background-image:url("/fond.jpg")', $out['css'], 'L’image passe dans le calque déplacé.');
        $this->assertStringContainsString('class="gp-el gp-widget gp-heading gp-sc gp-sc--fade"', $out['html']);
        $this->assertStringContainsString('#gp-w000000001{--gp-sc-k:0.7}', $out['css']);
        $this->assertStringContainsString('class="gp-el gp-widget gp-heading gp-sc gp-sc--move-up"', $out['html']);
        $this->assertStringContainsString('class="gp-el gp-con"', $out['html'], 'Pas de parallaxe sur un dégradé.');
    }

    public function test_sticky_element_in_the_theme_header_sticks_to_the_whole_page(): void
    {
        $this->withoutVite();
        \App\Models\User::create(['name' => 'Admin', 'email' => 'a@test.local', 'password' => 'secret-password-1', 'role' => 'admin']);
        Page::create(['title' => 'Accueil', 'slug' => 'accueil', 'is_home' => true, 'status' => 'published', 'content' => ['version' => 2, 'elements' => []]]);
        \App\Models\Layout::create(['name' => 'En-tête', 'type' => 'header', 'status' => 'published', 'content' => ['version' => 2, 'elements' => [
            ['id' => 'c000000001', 'type' => 'container', 'settings' => ['sticky' => 'top'], 'children' => [['id' => 'w000000001', 'type' => 'heading', 'settings' => ['text' => 'Logo']]]],
        ]]]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('~<header class="gp-theme-header">\s*<div id="gp-c000000001" class="gp-el gp-con gp-sticky gp-sticky--top">~', $html);
    }
}
