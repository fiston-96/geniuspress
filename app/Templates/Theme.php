<?php

namespace App\Templates;

/**
 * Identité visuelle d'un modèle : couleurs, polices, formes et variantes des
 * blocs. Les blocs n'utilisent que des couleurs globales (« global:primary »,
 * « global:clair »…) : changer une couleur dans les réglages met tout le site à jour.
 */
final class Theme
{
    /**
     * @param  array{primary: string, secondary: string, accent: string, light: string, text: string, muted: string}  $colors
     * @param  string  $hero  split | center | image | gradient
     * @param  string  $cards  shadow | border | flat | tinted
     * @param  string  $buttons  pill | rounded | square
     */
    public function __construct(
        public readonly array $colors,
        public readonly string $font = 'Inter',
        public readonly ?string $headingFont = null,
        public readonly string $hero = 'split',
        public readonly string $cards = 'shadow',
        public readonly string $buttons = 'rounded',
        public readonly int $radius = 14,
        public readonly int $space = 96,
    ) {}

    /** Réglages du site installés avec le modèle. */
    public function settings(): array
    {
        return [
            'primary_color' => $this->colors['primary'],
            'secondary_color' => $this->colors['secondary'],
            'accent_color' => $this->colors['accent'],
            'font_family' => $this->font,
            'heading_font' => $this->headingFont,
            'custom_colors' => [
                ['id' => 'clair', 'name' => 'Fond clair', 'value' => $this->colors['light']],
                ['id' => 'texte', 'name' => 'Texte', 'value' => $this->colors['text']],
                ['id' => 'doux', 'name' => 'Texte secondaire', 'value' => $this->colors['muted']],
            ],
        ];
    }

    public function buttonRadius(): int
    {
        return match ($this->buttons) {
            'pill' => 999,
            'square' => 2,
            default => max(6, intdiv($this->radius, 2)),
        };
    }

    /** Style commun d'une carte (fond, arrondi, bordure ou ombre, marges intérieures). */
    public function card(array $extra = []): array
    {
        $r = $this->radius;
        $base = [
            'padding' => ['t' => 32, 'r' => 28, 'b' => 32, 'l' => 28],
            'border_radius' => ['t' => $r, 'r' => $r, 'b' => $r, 'l' => $r],
            'background_color' => '#ffffff',
        ];
        $style = match ($this->cards) {
            'border' => ['border_style' => 'solid', 'border_width' => ['t' => 1, 'r' => 1, 'b' => 1, 'l' => 1], 'border_color' => '#e2e8f0', 'border_color_hover' => 'global:primary', 'transition' => 250],
            'flat' => ['background_color' => 'global:clair'],
            'tinted' => ['background_color' => 'global:clair', 'border_style' => 'solid', 'border_width' => ['t' => 0, 'r' => 0, 'b' => 4, 'l' => 0], 'border_color' => 'global:primary'],
            default => ['box_shadow' => ['x' => 0, 'y' => 10, 'blur' => 30, 'spread' => -12, 'color' => 'rgba(15,23,42,0.18)'], 'box_shadow_hover' => ['x' => 0, 'y' => 22, 'blur' => 44, 'spread' => -14, 'color' => 'rgba(15,23,42,0.28)'], 'transition' => 250],
        };

        return $extra + $style + $base;
    }
}
