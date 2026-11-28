<?php

namespace App\Builder;

/**
 * Styles globaux du site (réglage « typography ») : titres H1 à H6, texte courant, liens et
 * boutons. Le CSS produit a la priorité la plus faible (sélecteurs d'éléments et de classes) :
 * les réglages propres à un élément (#gp-…) l'emportent toujours. Ajouté par
 * DesignTokens::css(), donc identique sur le site, dans le builder et dans les aperçus.
 */
final class GlobalStyles
{
    public const HEADINGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    private const WEIGHTS = ['300', '400', '500', '600', '700', '800', '900'];

    private const TRANSFORMS = ['none', 'uppercase', 'capitalize'];

    /** Valeurs proposées par défaut dans le formulaire (le CSS de base de site.css s'applique sans réglage). */
    public const PLACEHOLDERS = ['h1' => 56, 'h2' => 40, 'h3' => 24, 'h4' => 20, 'h5' => 18, 'h6' => 16];

    /** Réglages validés ; une valeur absente ou invalide est retirée. */
    public static function sanitize(mixed $input): array
    {
        $in = is_array($input) ? $input : [];
        $int = fn (array $a, string $k, int $min, int $max) => isset($a[$k]) && is_numeric($a[$k]) && $a[$k] !== '' ? max($min, min($max, (int) $a[$k])) : null;
        $dec = fn (array $a, string $k, float $min, float $max) => isset($a[$k]) && is_numeric($a[$k]) && $a[$k] !== '' ? round(max($min, min($max, (float) $a[$k])), 3) : null;
        $pick = fn (array $a, string $k, array $allowed) => in_array((string) ($a[$k] ?? ''), $allowed, true) ? (string) $a[$k] : null;
        $clean = fn (array $a) => array_filter($a, fn ($v) => $v !== null);

        $out = [];
        foreach (self::HEADINGS as $h) {
            $a = (array) ($in[$h] ?? []);
            $row = $clean([
                'size' => $int($a, 'size', 10, 160),
                'size_mobile' => $int($a, 'size_mobile', 10, 120),
                'weight' => $pick($a, 'weight', self::WEIGHTS),
                'line_height' => $dec($a, 'line_height', 0.8, 2.5),
                'letter_spacing' => $dec($a, 'letter_spacing', -0.1, 0.5),
                'transform' => $pick($a, 'transform', self::TRANSFORMS),
            ]);
            if ($row) {
                $out[$h] = $row;
            }
        }
        $body = $clean(['size' => $int((array) ($in['body'] ?? []), 'size', 12, 24), 'line_height' => $dec((array) ($in['body'] ?? []), 'line_height', 1, 2.5)]);
        $a = (array) ($in['links'] ?? []);
        $links = $clean([
            'color' => ContentSanitizer::color($a['color'] ?? null),
            'hover' => ContentSanitizer::color($a['hover'] ?? null),
            'underline' => $pick($a, 'underline', ['none', 'hover', 'always']),
        ]);
        $a = (array) ($in['buttons'] ?? []);
        $buttons = $clean([
            'radius' => $int($a, 'radius', 0, 999),
            'weight' => $pick($a, 'weight', self::WEIGHTS),
            'transform' => $pick($a, 'transform', self::TRANSFORMS),
            'size' => $int($a, 'size', 12, 24),
            'padding_y' => $int($a, 'padding_y', 4, 30),
            'padding_x' => $int($a, 'padding_x', 8, 60),
        ]);

        return array_filter(['body' => $body, 'links' => $links, 'buttons' => $buttons] + $out);
    }

    public static function css(array $settings): string
    {
        $t = self::sanitize($settings['typography'] ?? []);
        if (! $t) {
            return '';
        }
        $rules = [];
        $mobile = [];
        $rule = function (string $selector, array $props) use (&$rules) {
            $props = array_filter($props, fn ($v) => $v !== null && $v !== '');
            if ($props) {
                $rules[] = $selector.'{'.implode(';', array_map(fn ($k, $v) => $k.':'.RenderContext::value((string) $v), array_keys($props), $props)).'}';
            }
        };

        foreach (self::HEADINGS as $h) {
            $s = $t[$h] ?? [];
            $size = $s['size'] ?? null;
            $small = $s['size_mobile'] ?? null;
            // Taille fluide entre le mobile et l'ordinateur (comme les titres réglés élément par élément).
            $fontSize = match (true) {
                $size && $small && $small < $size => "clamp({$small}px, calc({$small}px + ".($size - $small).' * (100vw - '.Schema::BREAKPOINTS['mobile'].'px) / '.(RenderContext::FLUID_WIDE - Schema::BREAKPOINTS['mobile'])."), {$size}px)",
                (bool) $size => $size.'px',
                default => null,
            };
            $rule($h, [
                'font-size' => $fontSize,
                'font-weight' => $s['weight'] ?? null,
                'line-height' => $s['line_height'] ?? null,
                'letter-spacing' => isset($s['letter_spacing']) ? $s['letter_spacing'].'em' : null,
                'text-transform' => $s['transform'] ?? null,
            ]);
            if ($small && (! $size || $small >= $size)) {
                $mobile[] = "{$h}{font-size:{$small}px}";
            }
        }
        $rule('body', ['font-size' => isset($t['body']['size']) ? $t['body']['size'].'px' : null, 'line-height' => $t['body']['line_height'] ?? null]);

        $l = $t['links'] ?? [];
        $underline = $l['underline'] ?? null;
        $rule('a', ['color' => $l['color'] ?? null, 'text-decoration' => $underline ? ($underline === 'always' ? 'underline' : 'none') : null]);
        $rule('a:hover', ['color' => $l['hover'] ?? null, 'text-decoration' => $underline === 'hover' ? 'underline' : null]);

        $b = $t['buttons'] ?? [];
        $rule('.gp-btn', [
            'border-radius' => isset($b['radius']) ? $b['radius'].'px' : null,
            'font-weight' => $b['weight'] ?? null,
            'text-transform' => $b['transform'] ?? null,
            'letter-spacing' => ($b['transform'] ?? null) === 'uppercase' ? '.04em' : null,
        ]);
        // Taille moyenne réglée ; petite et grande en proportion.
        if (isset($b['size']) || isset($b['padding_y']) || isset($b['padding_x'])) {
            $size = $b['size'] ?? 16;
            $py = $b['padding_y'] ?? 12;
            $px = $b['padding_x'] ?? 24;
            foreach (['sm' => 0.75, 'md' => 1, 'lg' => 1.25] as $variant => $k) {
                $rule('.gp-btn-'.$variant, ['padding' => (int) round($py * $k).'px '.(int) round($px * $k).'px', 'font-size' => (int) round($size * ($variant === 'md' ? 1 : ($variant === 'sm' ? 0.875 : 1.125))).'px']);
            }
        }

        $css = implode('', $rules);
        if ($mobile) {
            $css .= '@media (max-width: '.Schema::BREAKPOINTS['mobile'].'px){'.implode('', $mobile).'}';
        }

        return $css;
    }
}
