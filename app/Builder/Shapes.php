<?php

namespace App\Builder;

/**
 * Séparateurs de formes des conteneurs (vague, courbe, triangle…), en haut ou en bas.
 * Formes dessinées pour GeniusPress dans resources/builder/shapes.json (viewBox 1000×100,
 * remplissage collé au bord haut ; le séparateur du bas est retourné par site.css).
 * Même balisage que shapeSvg() dans resources/js/builder/style.js.
 */
final class Shapes
{
    private static ?array $set = null;

    /** @return array<string, list<array{d: string, opacity?: float}>> */
    public static function all(): array
    {
        return self::$set ??= json_decode((string) file_get_contents(resource_path('builder/shapes.json')), true) ?: [];
    }

    /** @return string[] */
    public static function names(): array
    {
        return array_keys(self::all());
    }

    /** Calque HTML d'un séparateur (« top » ou « bottom »), ou chaîne vide si la forme est inconnue. */
    public static function layer(string $name, string $side): string
    {
        $paths = self::all()[$name] ?? null;
        if (! $paths || ! in_array($side, ['top', 'bottom'], true)) {
            return '';
        }
        $svg = '';
        foreach ($paths as $p) {
            $svg .= '<path d="'.e($p['d']).'"'.(isset($p['opacity']) ? ' opacity="'.$p['opacity'].'"' : '').'/>';
        }

        return "<div class=\"gp-shape gp-shape--{$side}\" aria-hidden=\"true\"><svg viewBox=\"0 0 1000 100\" preserveAspectRatio=\"none\">{$svg}</svg></div>";
    }
}
