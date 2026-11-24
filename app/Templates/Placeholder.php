<?php

namespace App\Templates;

use App\Builder\Icons;

/**
 * Illustration d'attente pour un emplacement de photo : dégradé aux couleurs
 * du thème, formes douces et grande icône du sujet. Sans texte ni script :
 * le fichier SVG passe les contrôles de l'import.
 */
final class Placeholder
{
    public static function svg(Theme $theme, string $icon, string $seed, int $width = 1600, int $height = 1000): string
    {
        $c = $theme->colors;
        $hash = crc32($seed);
        $angle = $hash % 360;
        [$from, $to] = $hash % 2 ? [$c['primary'], $c['secondary']] : [$c['secondary'], $c['primary']];
        $accent = $c['accent'];

        $glyph = Icons::exists('lucide:'.$icon) ? Icons::set()['lucide'][$icon] : Icons::set()['lucide']['image'];
        $size = (int) round(min($width, $height) * 0.34);
        $x = (int) round(($width - $size) / 2);
        $y = (int) round(($height - $size) / 2);

        // Cercles décoratifs placés de façon stable selon l'emplacement.
        $shapes = '';
        for ($i = 0; $i < 4; $i++) {
            $cx = (($hash >> ($i * 5)) % 100) / 100 * $width;
            $cy = (($hash >> ($i * 7 + 3)) % 100) / 100 * $height;
            $r = (0.18 + (($hash >> ($i * 3)) % 20) / 100) * $height;
            $fill = $i === 0 ? $accent : '#ffffff';
            $opacity = $i === 0 ? 0.35 : 0.08;
            $shapes .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s" fill-opacity="%s"/>', $cx, $cy, $r, $fill, $opacity);
        }

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}" width="{$width}" height="{$height}">
        <defs><linearGradient id="g" gradientTransform="rotate({$angle} .5 .5)"><stop offset="0" stop-color="{$from}"/><stop offset="1" stop-color="{$to}"/></linearGradient></defs>
        <rect width="{$width}" height="{$height}" fill="url(#g)"/>
        {$shapes}
        <svg x="{$x}" y="{$y}" width="{$size}" height="{$size}" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-opacity="0.92" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round">{$glyph}</svg>
        </svg>
        SVG;
    }
}
