<?php

namespace Tests\Unit;

use App\Builder\DesignTokens;
use PHPUnit\Framework\TestCase;

/** Filets et fonds discrets tirés de la palette du thème (variables --gp-line, --gp-tint…). */
class DesignTokensTest extends TestCase
{
    public function test_mix(): void
    {
        $this->assertSame('#808080', DesignTokens::mix('#000000', '#ffffff', 0.5));
        $this->assertSame('#e1ddd6', DesignTokens::mix('#57534e', '#fbf7f0', 0.16));
        $this->assertSame('#ffffff', DesignTokens::mix('#fff', '#fff', 0.3), 'Forme courte acceptée.');
        $this->assertNull(DesignTokens::mix('rgb(1,2,3)', '#ffffff', 0.5));
    }

    public function test_lines_follow_the_theme_muted_color(): void
    {
        $css = DesignTokens::css(['custom_colors' => [
            ['id' => 'clair', 'name' => 'Fond clair', 'value' => '#fbf7f0'],
            ['id' => 'doux', 'name' => 'Texte secondaire', 'value' => '#57534e'],
        ]]);
        $this->assertStringContainsString('--gp-line:#e1ddd6', $css);
        $this->assertStringContainsString('--gp-tint:', $css);
        $this->assertStringContainsString('--gp-line-strong:', $css);

        $this->assertStringNotContainsString('--gp-line', DesignTokens::css([]), 'Sans palette, site.css garde ses valeurs par défaut.');
    }
}
