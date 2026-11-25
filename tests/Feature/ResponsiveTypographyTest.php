<?php

namespace Tests\Feature;

use App\Builder\Renderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Tailles de texte fluides : elles glissent d'un appareil à l'autre au lieu de sauter aux seuils. */
class ResponsiveTypographyTest extends TestCase
{
    use RefreshDatabase;

    private function css(array $settings): string
    {
        return app(Renderer::class)->render(['version' => 2, 'elements' => [['id' => 'w000000001', 'type' => 'heading', 'settings' => ['text' => 'Titre'] + $settings]]])['css'];
    }

    public function test_font_size_slides_between_devices(): void
    {
        $css = $this->css(['font_size' => 60, 'font_size_tablet' => 44, 'font_size_mobile' => 32]);

        // Ordinateur : de 44 px (1024 px de large) à 60 px (1440 px et plus).
        $this->assertStringContainsString('font-size:clamp(44px, calc(44px + 16 * (100vw - 1024px) / 416), 60px)', $css);
        // Tablette : de 32 px (767 px) à 44 px (1024 px).
        $this->assertStringContainsString('font-size:clamp(32px, calc(32px + 12 * (100vw - 767px) / 257), 44px)', $css);
        // Mobile : taille fixe.
        $this->assertMatchesRegularExpression('~@media \(max-width: 767px\) \{[^@]*font-size:32px~', $css);
    }

    public function test_without_a_tablet_size_it_slides_from_the_mobile_size(): void
    {
        $this->assertStringContainsString('font-size:clamp(30px, calc(30px + 26 * (100vw - 767px) / 673), 56px)', $this->css(['font_size' => 56, 'font_size_mobile' => 30]));
    }

    public function test_fixed_sizes_stay_fixed(): void
    {
        $this->assertStringNotContainsString('clamp(', $this->css(['font_size' => 40]));
        // Taille plus grande sur mobile que sur ordinateur : pas de glissement, valeurs telles quelles.
        $this->assertStringNotContainsString('clamp(', $this->css(['font_size' => 20, 'font_size_mobile' => 24]));
    }
}
