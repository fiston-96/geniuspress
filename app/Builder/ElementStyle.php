<?php

namespace App\Builder;

/**
 * CSS des réglages communs (Schema::STYLE) et de la disposition des conteneurs
 * (Schema::CONTAINER). Miroir exact de resources/js/builder/style.js, qui
 * produit le même CSS dans l'éditeur.
 */
class ElementStyle
{
    private const FLEX = [
        'start' => 'flex-start', 'center' => 'center', 'end' => 'flex-end', 'stretch' => 'stretch', 'baseline' => 'baseline',
        'between' => 'space-between', 'around' => 'space-around', 'evenly' => 'space-evenly',
    ];

    /** Dimensions par défaut d'un enfant de conteneur en colonne : rien à écrire. */
    private const COLUMN_DEFAULT = ['flex' => '0 1 auto', 'width' => 'auto'];

    /** Valeur d'un réglage pour un appareil, en héritant des écrans plus grands. */
    public static function at(array $s, string $key, string $device): mixed
    {
        $value = null;
        foreach (Schema::DEVICES as $d => $sfx) {
            if (isset($s[$key.$sfx])) {
                $value = $s[$key.$sfx];
            }
            if ($d === $device) {
                break;
            }
        }

        return $value;
    }

    /**
     * Réglages communs d'un élément (conteneur ou widget).
     *
     * @param  string[]  $keys  réglages communs gérés ici (ceux qu'un widget redéfinit sont exclus)
     */
    public static function apply(array $node, ?array $parent, array $keys, RenderContext $ctx): void
    {
        $s = $node['settings'];
        $id = $node['id'];
        $has = array_flip($keys);
        $isContainer = $node['type'] === 'container';

        // Réglages par appareil
        $previous = null;
        foreach (Schema::DEVICES as $device => $sfx) {
            $p = [];
            if (isset($has['margin'])) {
                $p += self::sides('margin', $s['margin'.$sfx] ?? null);
            }
            if (isset($has['padding'])) {
                $p += self::sides('padding', $s['padding'.$sfx] ?? null);
            }
            if (isset($has['border_radius'])) {
                $p += self::radius($s['border_radius'.$sfx] ?? null);
            }
            if (isset($has['min_height'], $s['min_height'.$sfx])) {
                $p['min-height'] = $s['min_height'.$sfx];
            }
            if (isset($has['align_self'], $s['align_self'.$sfx])) {
                $p['align-self'] = self::FLEX[$s['align_self'.$sfx]];
            }
            if (isset($has['order'], $s['order'.$sfx])) {
                $p['order'] = (string) $s['order'.$sfx];
            }

            // Place dans le parent : écrite seulement quand elle change d'un appareil à l'autre.
            $sizing = self::sizing($s, $parent, $device, $isContainer, isset($has['width']));
            if ($sizing !== $previous && ! ($device === 'desktop' && $sizing === self::COLUMN_DEFAULT)) {
                $p += $sizing;
            }
            $previous = $sizing;

            $ctx->rule($id, $p, $device);
        }

        // Fond, bordure, ombre, couleur…
        $p = self::background($s, $has);
        if (isset($has['border_style'], $s['border_style'])) {
            $p['border-style'] = $s['border_style'];
            if ($s['border_style'] !== 'none') {
                $p += self::sides('border', $s['border_width'] ?? ['t' => 1, 'r' => 1, 'b' => 1, 'l' => 1], '-width');
                $p['border-color'] = $s['border_color'] ?? null;
            }
        }
        if (isset($has['box_shadow'], $s['box_shadow'])) {
            $p['box-shadow'] = self::shadow($s['box_shadow']);
        }
        if (isset($has['text_color'], $s['text_color'])) {
            $p['color'] = $s['text_color'];
        }
        if (isset($has['opacity'], $s['opacity'])) {
            $p['opacity'] = (string) ($s['opacity'] / 100);
        }
        if (isset($has['z_index'], $s['z_index'])) {
            $p['z-index'] = (string) $s['z_index'];
            $p['position'] = $isContainer ? null : 'relative';
        }
        if (isset($has['overflow'], $s['overflow'])) {
            $p['overflow'] = $s['overflow'];
        }

        // Survol
        $hover = array_filter([
            'background-color' => isset($has['background_color']) ? ($s['background_color_hover'] ?? null) : null,
            'border-color' => isset($has['border_color']) ? ($s['border_color_hover'] ?? null) : null,
            'box-shadow' => isset($has['box_shadow'], $s['box_shadow_hover']) ? self::shadow($s['box_shadow_hover']) : null,
            'color' => isset($has['text_color']) ? ($s['text_color_hover'] ?? null) : null,
            'opacity' => isset($has['opacity'], $s['opacity_hover']) ? (string) ($s['opacity_hover'] / 100) : null,
        ], fn ($v) => $v !== null);
        if ($hover || isset($has['transition'], $s['transition'])) {
            $ms = (int) ($s['transition'] ?? 300);
            $p['transition'] = implode(', ', array_map(fn ($prop) => "{$prop} {$ms}ms", ['background-color', 'color', 'border-color', 'box-shadow', 'opacity']));
        }

        $ctx->rule($id, $p);
        $ctx->rule($id, $hover, false, ':hover');
    }

    /** Disposition flexbox d'un conteneur (appliquée à sa boîte intérieure). */
    public static function container(array $node, int $depth, RenderContext $ctx): void
    {
        $s = $node['settings'];
        $id = $node['id'];
        $boxed = ($s['content_width'] ?? Schema::containerDefaults($depth)['content_width']) === 'boxed';

        $previousWrap = 'nowrap';
        foreach (Schema::DEVICES as $device => $sfx) {
            // Le retour à la ligne ne vaut que pour une disposition en ligne : en colonne, sans
            // hauteur fixe, il n'a pas de sens et fausse le calcul des hauteurs par le navigateur.
            $wrap = str_starts_with((string) (self::at($s, 'direction', $device) ?? 'column'), 'row') ? (self::at($s, 'wrap', $device) ?? 'nowrap') : 'nowrap';
            $p = [
                'flex-direction' => $s['direction'.$sfx] ?? null,
                'flex-wrap' => $wrap !== $previousWrap ? $wrap : null,
                'justify-content' => isset($s['justify'.$sfx]) ? self::FLEX[$s['justify'.$sfx]] : null,
                'align-items' => isset($s['align'.$sfx]) ? self::FLEX[$s['align'.$sfx]] : null,
                'gap' => isset($s['gap'.$sfx]) ? $s['gap'.$sfx].'px' : null,
                'max-width' => $boxed && isset($s['max_width'.$sfx]) ? $s['max_width'.$sfx].'px' : null,
            ];
            if ($device === 'desktop') {
                $p['max-width'] = $boxed ? ($s['max_width'] ?? 1140).'px' : 'none';
            }
            $previousWrap = $wrap;
            $ctx->rule($id, $p, $device, ' > .gp-con-inner');
        }

        if (isset($s['overlay_color'])) {
            $ctx->rule($id, ['background' => $s['overlay_color'], 'opacity' => (string) (($s['overlay_opacity'] ?? 50) / 100)], false, ' > .gp-overlay');
        }
    }

    /**
     * Place d'un élément dans son parent pour un appareil. En ligne, la largeur
     * devient la base flex ; en colonne, c'est la largeur. Une largeur choisie
     * pour une disposition en ligne ne s'applique pas quand le parent passe en
     * colonne (sur mobile par exemple) : l'élément reprend toute la largeur.
     */
    private static function sizing(array $s, ?array $parent, string $device, bool $isContainer, bool $useWidth): array
    {
        $direction = fn ($d) => $parent ? (self::at($parent['settings'], 'direction', $d) ?? 'column') : 'column';
        $row = str_starts_with($direction($device), 'row');

        $width = null;
        if ($useWidth) {
            $devices = array_keys(Schema::DEVICES);
            foreach (array_reverse(array_slice($devices, 0, array_search($device, $devices, true) + 1)) as $d) {
                if (isset($s['width'.Schema::DEVICES[$d]])) {
                    if (str_starts_with($direction($d), 'row') === $row) {
                        $width = $s['width'.Schema::DEVICES[$d]];
                    }
                    break;
                }
            }
        }

        // « Remplir l'espace disponible » : l'élément grandit dans l'axe principal du parent.
        $grow = ! empty(self::at($s, 'grow', $device)) ? '1' : '0';
        if ($row) {
            return ['flex' => $width !== null ? $grow.' 1 '.self::basis($width, $parent, $device) : ($isContainer ? '1 1 0' : $grow.' 1 auto'), 'width' => 'auto'];
        }

        return ['flex' => $grow.' 1 auto', 'width' => $width ?? 'auto'];
    }

    /**
     * Base flex d'une largeur en % : l'espace entre les enfants est réparti à parts
     * égales, pour que 50 % + 50 % (ou 26 % + 74 %) remplissent exactement la ligne.
     */
    private static function basis(string $width, array $parent, string $device): string
    {
        $count = count($parent['children'] ?? []);
        $gap = (int) (self::at($parent['settings'], 'gap', $device) ?? 20);
        if (! str_ends_with($width, '%') || $count < 2 || $gap === 0) {
            return $width;
        }
        // Grille sur plusieurs rangées : l'espace se répartit entre les éléments d'une même rangée.
        if ((self::at($parent['settings'], 'wrap', $device) ?? 'nowrap') === 'wrap') {
            $count = max(1, (int) round(100 / max(1, (float) $width)));
            if ($count < 2) {
                return $width;
            }
        }
        $share = round($gap * ($count - 1) / $count, 2);

        return "calc({$width} - {$share}px)";
    }

    private static function background(array $s, array $has): array
    {
        $p = [];
        if (($s['background_type'] ?? 'classic') === 'gradient' && isset($has['gradient_from'], $s['gradient_from'])) {
            $from = $s['gradient_from'];
            $to = $s['gradient_to'] ?? 'transparent';
            $p['background-image'] = ($s['gradient_type'] ?? 'linear') === 'radial'
                ? "radial-gradient(circle, {$from}, {$to})"
                : 'linear-gradient('.($s['gradient_angle'] ?? 180)."deg, {$from}, {$to})";

            return $p;
        }
        if (isset($has['background_color'], $s['background_color'])) {
            $p['background-color'] = $s['background_color'];
        }
        if (isset($has['background_image'], $s['background_image'])) {
            $p['background-image'] = 'url("'.$s['background_image'].'")';
            $p['background-position'] = $s['background_position'] ?? 'center';
            $p['background-size'] = $s['background_size'] ?? 'cover';
            $p['background-repeat'] = 'no-repeat';
            $p['background-attachment'] = ! empty($s['background_fixed']) ? 'fixed' : null;
        }

        return $p;
    }

    /** { t, r, b, l, u } → padding-top, padding-right… (côtés non renseignés ignorés). */
    private static function sides(string $property, ?array $v, string $postfix = ''): array
    {
        if (! $v) {
            return [];
        }
        $unit = $v['u'] ?? 'px';
        $out = [];
        foreach (['t' => 'top', 'r' => 'right', 'b' => 'bottom', 'l' => 'left'] as $k => $side) {
            if (isset($v[$k])) {
                $out["{$property}-{$side}{$postfix}"] = $v[$k].($v[$k] === 0 ? '' : $unit);
            }
        }

        return $out;
    }

    /** Coins : t = haut gauche, r = haut droit, b = bas droit, l = bas gauche. */
    private static function radius(?array $v): array
    {
        if (! $v) {
            return [];
        }
        $unit = $v['u'] ?? 'px';
        $out = [];
        foreach (['t' => 'top-left', 'r' => 'top-right', 'b' => 'bottom-right', 'l' => 'bottom-left'] as $k => $corner) {
            if (isset($v[$k])) {
                $out["border-{$corner}-radius"] = $v[$k].($v[$k] === 0 ? '' : $unit);
            }
        }

        return $out;
    }

    public static function shadow(array $v): string
    {
        return (! empty($v['inset']) ? 'inset ' : '').($v['x'] ?? 0).'px '.($v['y'] ?? 0).'px '.($v['blur'] ?? 0).'px '.($v['spread'] ?? 0).'px '.$v['color'];
    }
}
