<?php

/**
 * Génère les icônes PNG et le favicon de GeniusPress à partir des formes du
 * logo (mêmes coordonnées que public/brand/geniuspress-mark*.svg).
 *
 *   php scripts/brand/generate-icons.php
 *
 * Nécessite l'extension GD. Le dessin est fait à 8 fois la taille puis réduit,
 * pour des bords lissés.
 */

if (! extension_loaded('gd')) {
    fwrite(STDERR, "L'extension PHP gd est nécessaire.\n");
    exit(1);
}

ini_set('memory_limit', '512M');
$public = dirname(__DIR__, 2).'/public';

// [x, y, largeur, hauteur, rayon, couleur, opacité] dans une grille de 64.
$full = [
    [5, 25, 25, 33, 5, '#a5b4fc', 1],
    [17, 16, 25, 34, 5, '#4f46e5', 1],
    [30, 6, 28, 37, 5, '#f59e0b', 1],
    [35, 11, 18, 6, 2, '#ffffff', 1],
    [35, 20, 8, 10, 2, '#ffffff', 1],
    [45, 20, 8, 10, 2, '#ffffff', 0.6],
];
$small = [
    [5, 25, 25, 33, 5, '#a5b4fc', 1],
    [17, 16, 25, 34, 5, '#4f46e5', 1],
    [30, 6, 28, 37, 5, '#f59e0b', 1],
    [35, 12, 18, 8, 3, '#ffffff', 1],
];

/** Dessine le symbole dans un carré de $size px, avec une marge et un fond facultatif. */
function render(array $shapes, int $size, float $padding = 0.0, ?string $background = null, int $radius = 0): GdImage
{
    $scale = $size >= 128 ? 4 : 8; // Assez pour lisser sans exploser la mémoire.
    $big = $size * $scale;
    $img = imagecreatetruecolor($big, $big);
    imagesavealpha($img, true);
    imagealphablending($img, false);
    imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
    imagealphablending($img, true);

    if ($background) {
        rounded($img, 0, 0, $big, $big, $radius * $scale, color($img, $background, 1));
    }

    $inner = $big * (1 - 2 * $padding);
    $offset = $big * $padding;
    $unit = $inner / 64;
    foreach ($shapes as [$x, $y, $w, $h, $r, $hex, $alpha]) {
        rounded($img, $offset + $x * $unit, $offset + $y * $unit, $w * $unit, $h * $unit, $r * $unit, color($img, $hex, $alpha));
    }

    $out = imagecreatetruecolor($size, $size);
    imagesavealpha($out, true);
    imagealphablending($out, false);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
    imagecopyresampled($out, $img, 0, 0, 0, 0, $size, $size, $big, $big);
    imagedestroy($img);

    return $out;
}

function color(GdImage $img, string $hex, float $opacity): int
{
    [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

    return imagecolorallocatealpha($img, $r, $g, $b, (int) round(127 * (1 - $opacity)));
}

function rounded(GdImage $img, float $x, float $y, float $w, float $h, float $r, int $color): void
{
    [$x, $y, $w, $h, $r] = array_map(fn ($v) => (int) round($v), [$x, $y, $w, $h, $r]);
    $r = min($r, intdiv($w, 2), intdiv($h, 2));
    imagefilledrectangle($img, $x + $r, $y, $x + $w - $r - 1, $y + $h - 1, $color);
    imagefilledrectangle($img, $x, $y + $r, $x + $r - 1, $y + $h - $r - 1, $color);
    imagefilledrectangle($img, $x + $w - $r, $y + $r, $x + $w - 1, $y + $h - $r - 1, $color);
    foreach ([[$x + $r, $y + $r, 180], [$x + $w - $r - 1, $y + $r, 270], [$x + $r, $y + $h - $r - 1, 90], [$x + $w - $r - 1, $y + $h - $r - 1, 0]] as [$cx, $cy, $start]) {
        imagefilledarc($img, $cx, $cy, 2 * $r, 2 * $r, $start, $start + 90, $color, IMG_ARC_PIE);
    }
}

function png(GdImage $img): string
{
    ob_start();
    imagepng($img, null, 9);

    return (string) ob_get_clean();
}

// Icônes d'application et d'onglet
$files = [
    'brand/geniuspress-icon-512.png' => render($full, 512, 0.08),
    'brand/geniuspress-icon-192.png' => render($full, 192, 0.08),
    // iOS ajoute ses propres coins arrondis : fond blanc plein, marge confortable.
    'apple-touch-icon.png' => render($full, 180, 0.14, '#ffffff'),
];
foreach ($files as $path => $img) {
    file_put_contents("{$public}/{$path}", png($img));
    echo "créé : public/{$path}\n";
}

// favicon.ico : PNG 16, 32 et 48 px dans un seul fichier (format ICO à images PNG).
$sizes = [16 => $small, 32 => $small, 48 => $full];
$images = [];
foreach ($sizes as $size => $shapes) {
    $images[$size] = png(render($shapes, $size, 0.02));
}
$ico = pack('vvv', 0, 1, count($images));
$offset = 6 + 16 * count($images);
foreach ($images as $size => $data) {
    $ico .= pack('CCCCvvVV', $size, $size, 0, 0, 1, 32, strlen($data), $offset);
    $offset += strlen($data);
}
$ico .= implode('', $images);
file_put_contents("{$public}/favicon.ico", $ico);
echo "créé : public/favicon.ico (16, 32, 48 px)\n";
