<?php

namespace App\Templates;

/**
 * Boîte à outils pour décrire des pages au format 2 (conteneurs imbriqués),
 * utilisée par les blocs de sections des modèles.
 *
 * Les photos des modèles sont des « emplacements » : Kit::photo('ong-ecole', …)
 * produit une image dont l'adresse est /storage/templates/ong-ecole.svg ; le
 * générateur de catalogue fabrique l'illustration correspondante (ou, plus tard,
 * y place la photo libre de droits choisie pour cet emplacement).
 */
final class Kit
{
    private static int $seq = 0;

    /** @var array<string, array{id: int, icon: string, alt: string}> emplacements de photos utilisés */
    private static array $slots = [];

    public static function reset(): void
    {
        self::$seq = 0;
        self::$slots = [];
    }

    /** @return array<string, array{id: int, icon: string, alt: string}> */
    public static function slots(): array
    {
        return self::$slots;
    }

    public static function id(string $prefix = 'e'): string
    {
        return $prefix.str_pad(base_convert((string) ++self::$seq, 10, 36), 9, '0', STR_PAD_LEFT);
    }

    /** Adresse d'un emplacement : la photo choisie (JPEG) si elle a été téléchargée, sinon l'illustration (SVG). */
    public static function slotUrl(string $slot): string
    {
        return '/storage/templates/'.$slot.(PhotoLibrary::file($slot) ? '.jpg' : '.svg');
    }

    private static function slot(string $slot, string $icon, string $alt): int
    {
        self::$slots[$slot] ??= ['id' => count(self::$slots) + 1, 'icon' => $icon, 'alt' => $alt];

        return self::$slots[$slot]['id'];
    }

    // ─── Structure ──────────────────────────────────────────────────────

    public static function box(array $children, array $s = []): array
    {
        return ['id' => self::id('c'), 'type' => 'container', 'settings' => $s, 'children' => array_values(array_filter($children))];
    }

    /** Section de premier niveau, centrée, avec des marges confortables (réduites sur mobile). */
    public static function section(Theme $theme, array $children, array $s = []): array
    {
        $space = $theme->space;

        return self::box(array_map([self::class, 'reveal'], array_values(array_filter($children))), $s + [
            'tag' => 'section',
            'padding' => ['t' => $space, 'r' => 20, 'b' => $space, 'l' => 20],
            'padding_tablet' => ['t' => (int) round($space * 0.75), 'b' => (int) round($space * 0.75)],
            'padding_mobile' => ['t' => (int) round($space * 0.6), 'b' => (int) round($space * 0.6)],
            'gap' => 48,
            'gap_mobile' => 32,
        ]);
    }

    /**
     * Animation d'apparition du contenu d'une section : fondu vers le haut, et les colonnes
     * d'une ligne l'une après l'autre. Rien n'est ajouté là où un élément est déjà animé
     * (cartes d'une grille, réglages choisis par un bloc).
     */
    public static function reveal(array $node): array
    {
        if (self::animated($node)) {
            return $node;
        }
        $columns = $node['children'] ?? [];
        if (($node['settings']['direction'] ?? null) === 'row' && count($columns) >= 2 && count($columns) <= 4) {
            foreach ($columns as $i => $column) {
                $node['children'][$i]['settings'] += ['entrance' => 'fade-up'] + ($i ? ['entrance_delay' => $i * 150] : []);
            }

            return $node;
        }
        $node['settings'] += ['entrance' => 'fade-up'];

        return $node;
    }

    /** Un élément ou l'un de ses descendants a-t-il déjà une animation d'apparition ? */
    private static function animated(array $node): bool
    {
        if (! empty($node['settings']['entrance'])) {
            return true;
        }
        foreach ($node['children'] ?? [] as $child) {
            if (self::animated($child)) {
                return true;
            }
        }

        return false;
    }

    /** Ligne (colonnes côte à côte), empilée sur mobile. */
    public static function row(array $children, array $s = []): array
    {
        return self::box($children, $s + ['direction' => 'row', 'direction_mobile' => 'column', 'gap' => 32, 'gap_mobile' => 24]);
    }

    /** Colonne d'une ligne, avec sa largeur sur ordinateur. */
    public static function col(array $children, string $width, array $s = []): array
    {
        return self::box($children, $s + ['width' => $width, 'gap' => 16]);
    }

    /**
     * Grille de cartes : $cols par rangée sur ordinateur, 2 sur tablette
     * (si $cols ≥ 3), une seule sur mobile. Les cartes ont la même hauteur.
     */
    public static function grid(array $items, int $cols, array $s = []): array
    {
        $width = rtrim(rtrim(number_format(100 / $cols, 3, '.', ''), '0'), '.').'%';
        // Sur tablette : 2 colonnes à partir de 3, sauf pour exactement 3 éléments (pas d'élément seul sur sa ligne).
        $tablet = count(array_filter($items)) === 3 ? '33.333%' : '50%';
        $items = array_map(function ($item, $i) use ($width, $cols, $tablet) {
            $item['settings']['width'] = $width;
            if ($cols >= 3) {
                $item['settings']['width_tablet'] = $tablet;
            }
            // Les cartes apparaissent en cascade (colonne par colonne) et se soulèvent au survol.
            $item['settings'] += ['entrance' => 'fade-up'] + ($cols > 1 && $i % $cols ? ['entrance_delay' => ($i % $cols) * 120] : []);
            if ($item['type'] === 'container' && (isset($item['settings']['box_shadow']) || isset($item['settings']['background_color']) || isset($item['settings']['border_style']))) {
                $item['settings'] += ['hover_effect' => 'lift'];
            }

            return $item;
        }, $items = array_values(array_filter($items)), array_keys($items));

        return self::box($items, $s + ['direction' => 'row', 'direction_mobile' => 'column', 'wrap' => 'wrap', 'gap' => 28, 'gap_mobile' => 20, 'align' => 'stretch']);
    }

    // ─── Widgets ────────────────────────────────────────────────────────

    public static function w(string $type, array $s): array
    {
        return ['id' => self::id('w'), 'type' => $type, 'settings' => array_filter($s, fn ($v) => $v !== null && $v !== '' && $v !== [])];
    }

    public static function h(string $text, string $tag = 'h2', array $s = []): array
    {
        return self::w('heading', $s + ['text' => $text, 'tag' => $tag]);
    }

    public static function p(string $html, array $s = []): array
    {
        return self::w('text', $s + ['html' => str_starts_with(trim($html), '<') ? $html : '<p>'.e($html).'</p>']);
    }

    /** Petit texte au-dessus d'un titre (« Notre mission »). */
    public static function eyebrow(string $text, array $s = []): array
    {
        return self::h($text, 'p', $s + ['color' => 'global:accent', 'font_size' => 14, 'font_weight' => '700', 'text_transform' => 'uppercase', 'letter_spacing' => '0.12em']);
    }

    public static function btn(Theme $theme, string $text, string $url, string $variant = 'primary', array $s = []): array
    {
        $colors = match ($variant) {
            'accent' => ['bg_color' => 'global:accent', 'text_color' => 'global:secondary', 'variant' => 'filled'],
            'light' => ['bg_color' => '#ffffff', 'text_color' => 'global:secondary', 'variant' => 'filled'],
            'outline-light' => ['bg_color' => '#ffffff', 'text_color' => '#ffffff', 'variant' => 'outline'],
            'outline' => ['bg_color' => 'global:primary', 'variant' => 'outline'],
            default => ['bg_color' => 'global:primary', 'text_color' => '#ffffff', 'variant' => 'filled'],
        };

        return self::w('button', $s + $colors + ['text' => $text, 'url' => $url, 'size' => 'lg', 'border_radius' => $theme->buttonRadius()]);
    }

    /** Boutons côte à côte (centrés si $align = center). */
    public static function buttons(array $buttons, string $align = 'left'): array
    {
        $justify = ['left' => 'start', 'center' => 'center', 'right' => 'end'][$align];

        return self::box($buttons, ['direction' => 'row', 'wrap' => 'wrap', 'gap' => 12, 'justify' => $justify, 'padding' => ['t' => 8, 'r' => 0, 'b' => 0, 'l' => 0]]);
    }

    /** Photo de modèle (emplacement) : illustration d'attente, remplacée plus tard par une photo libre de droits. */
    public static function photo(string $slot, string $icon, string $alt, array $s = []): array
    {
        return self::w('image', $s + ['url' => self::slotUrl($slot), 'media_id' => self::slot($slot, $icon, $alt), 'alt' => $alt, 'width' => 100]);
    }

    /** Emplacement de photo utilisé comme fond de section (adresse seulement). */
    public static function photoUrl(string $slot, string $icon, string $alt): string
    {
        self::slot($slot, $icon, $alt);

        return self::slotUrl($slot);
    }

    /** Champs d'une image de liste (galerie, image et texte) liés à un emplacement. */
    public static function photoFields(string $slot, string $icon, string $alt): array
    {
        return ['url' => self::slotUrl($slot), 'media_id' => self::slot($slot, $icon, $alt), 'alt' => $alt];
    }
}
