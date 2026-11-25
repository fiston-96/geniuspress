<?php

namespace Tests\Feature;

use App\Builder\ContentSanitizer;
use App\Builder\Renderer;
use App\Builder\WidgetRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Widgets avancés : rendu, validation des réglages et sécurité. */
class AdvancedWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private function render(string $type, array $settings): string
    {
        return app(Renderer::class)->render(['version' => 2, 'elements' => [['id' => 'w000000001', 'type' => $type, 'settings' => $settings]]])['html'];
    }

    public function test_every_advanced_widget_is_registered_and_keeps_its_settings(): void
    {
        $samples = [
            'flip-box' => ['icon' => 'lucide:heart', 'front_title' => 'Recto', 'back_title' => 'Verso', 'button_text' => 'Voir', 'button_url' => '/x', 'direction' => 'vertical', 'height' => 300, 'height_mobile' => 240, 'radius' => 20, 'front_bg' => 'global:clair', 'back_bg' => '#123456'],
            'promo-box' => ['url' => '/a.jpg', 'eyebrow' => 'Nouveau', 'title' => 'Offre', 'text' => 'Texte', 'button_text' => 'Go', 'button_url' => '/go', 'align' => 'center', 'valign' => 'top', 'height' => 400, 'overlay' => '#000000', 'overlay_opacity' => 50, 'zoom' => true],
            'dual-heading' => ['before' => 'Construisons', 'highlight' => 'ensemble', 'after' => 'demain', 'tag' => 'h1', 'style' => 'marker', 'highlight_color_2' => 'global:accent', 'font_size' => 48],
            'price-list' => ['items' => [['title' => 'Moambe', 'price' => '12 $', 'description' => 'Riz', 'badge' => 'Chef']], 'leader' => true, 'image_size' => 64],
            'business-hours' => ['items' => [['day' => 'Lundi', 'hours' => '8 h – 17 h', 'highlight' => true]], 'divider' => false],
            'data-table' => ['data' => "Niveau | Frais\nPrimaire | 100", 'header' => true, 'striped' => false, 'caption' => 'Frais'],
            'timeline' => ['items' => [['date' => '2010', 'title' => 'Création', 'text' => 'Début', 'icon' => 'lucide:flag']], 'layout' => 'left', 'card' => false],
            'star-rating' => ['rating' => 9, 'text' => 'Très bien', 'size' => 30],
            'alert' => ['kind' => 'success', 'title' => 'Bravo', 'text' => '<p>Ok</p>', 'icon' => false],
            'hotspots' => ['url' => '/a.jpg', 'alt' => 'Plan', 'spots' => [['x' => 80, 'y' => 20, 'title' => 'Entrée', 'text' => 'Ici', 'link' => '/contact']]],
            'image-accordion' => ['items' => [['url' => '/a.jpg', 'title' => 'A', 'text' => 'B', 'link' => '/a']], 'height' => 380],
            'dual-button' => ['text_1' => 'Don', 'url_1' => '/don', 'text_2' => 'Aider', 'url_2' => '/aider', 'middle' => 'ou', 'radius' => 8],
            'unfold' => ['html' => '<p>Long</p>', 'height' => 120, 'more' => 'Plus', 'less' => 'Moins'],
            'ticker' => ['label' => 'Infos', 'items' => [['text' => 'Inscriptions', 'link' => '/inscriptions']], 'speed' => 20],
            'image-scroll' => ['url' => '/long.jpg', 'alt' => 'Capture', 'height' => 400, 'duration' => 8, 'frame' => true, 'link' => '/site'],
        ];
        $registry = app(WidgetRegistry::class);
        $sanitizer = app(ContentSanitizer::class);
        foreach ($samples as $type => $settings) {
            $this->assertTrue($registry->has($type), $type);
            $clean = $sanitizer->sanitize(['version' => 2, 'elements' => [['id' => 'w000000001', 'type' => $type, 'settings' => $settings]]])['elements'][0]['settings'];
            $this->assertSame([], array_values(array_diff(array_keys($settings), array_keys($clean))), "{$type} : réglages refusés");
            $this->assertNotSame('', $this->render($type, $settings), "{$type} : rendu vide");
        }
    }

    public function test_markup_of_the_main_widgets(): void
    {
        $this->assertStringContainsString('<h2 class="gp-dh">Construisons <span class="gp-dh__hl gp-dh__hl--color">ensemble</span></h2>', $this->render('dual-heading', ['before' => 'Construisons', 'highlight' => 'ensemble']));

        $table = $this->render('data-table', ['data' => "A | B | C\n1 | 2\n<b>x</b> | y"]);
        $this->assertStringContainsString('<thead><tr><th>A</th><th>B</th><th>C</th></tr></thead>', $table);
        $this->assertStringContainsString('<tr><td>1</td><td>2</td><td></td></tr>', $table, 'Cellules manquantes complétées.');
        $this->assertStringContainsString('&lt;b&gt;x&lt;/b&gt;', $table, 'Contenu échappé.');

        $this->assertStringContainsString('aria-label="4,5 sur 5"', $this->render('star-rating', ['rating' => 9]));
        $this->assertStringContainsString('style="--gp-rating:90%"', $this->render('star-rating', ['rating' => 9]));

        $spots = $this->render('hotspots', ['url' => '/a.jpg', 'spots' => [['x' => 80, 'y' => 30, 'title' => 'A'], ['x' => 10, 'y' => 10]]]);
        $this->assertStringContainsString('class="gp-spot gp-spot--left" style="left:80%;top:30%"', $spots);
        $this->assertStringContainsString('<span class="gp-spot__dot">2</span>', $spots);

        $ticker = $this->render('ticker', ['items' => [['text' => 'Hello']]]);
        $this->assertSame(2, substr_count($ticker, '<li>Hello</li>'));
        $this->assertStringContainsString('<ul aria-hidden="true">', $ticker);

        $unfold = $this->render('unfold', ['html' => '<p>Long</p><script>alert(1)</script>']);
        $this->assertStringContainsString('id="u-w000000001"', $unfold);
        $this->assertStringContainsString('for="u-w000000001"', $unfold);
        $this->assertStringNotContainsString('<script', $unfold);

        $this->assertStringNotContainsString('<script', $this->render('alert', ['text' => '<p>Ok</p><script>alert(1)</script>']));
        $this->assertStringContainsString('gp-alert--info', $this->render('alert', []));

        // Sans contenu, rien n'est affiché.
        foreach (['price-list' => ['items' => []], 'timeline' => [], 'hotspots' => [], 'image-scroll' => [], 'data-table' => ['data' => ' ']] as $type => $settings) {
            $this->assertStringNotContainsString('<ul', $this->render($type, $settings), $type);
        }
    }

    public function test_hotspot_positions_are_bounded_and_links_are_safe(): void
    {
        $clean = app(ContentSanitizer::class)->sanitize(['version' => 2, 'elements' => [['id' => 'w000000001', 'type' => 'hotspots', 'settings' => [
            'url' => '/a.jpg', 'spots' => [['x' => 150, 'y' => -5, 'title' => 'A', 'link' => 'javascript:alert(1)']],
        ]]]])['elements'][0]['settings'];

        $spot = $clean['spots'][0];
        $this->assertArrayNotHasKey('link', $spot);
        $this->assertTrue(! isset($spot['x']) || ($spot['x'] >= 0 && $spot['x'] <= 100));
        $this->assertTrue(! isset($spot['y']) || ($spot['y'] >= 0 && $spot['y'] <= 100));
    }
}
