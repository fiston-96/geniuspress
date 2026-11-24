<?php

namespace App\Builder;

/**
 * Convertit le contenu de la version 1 (sections → colonnes → widgets) vers la
 * version 2 (conteneurs imbriqués), en conservant le rendu :
 *
 *  - une section devient un conteneur de premier niveau (balise <section>) ;
 *  - une section à une seule colonne sans réglage propre devient un conteneur
 *    en colonne qui contient directement les widgets ;
 *  - sinon la section devient un conteneur en ligne (en colonne sur mobile)
 *    qui contient un conteneur par colonne, avec sa largeur.
 *
 * L'assistant IA continue de produire des pages au format 1, plus simple à
 * décrire : elles sont converties ici à leur arrivée.
 */
class ContentMigrator
{
    /** Réglages du format 1, utilisés par l'assistant IA pour décrire une page. */
    public const V1_SECTION = [
        'background_color' => 'color',
        'background_image' => 'url',
        'overlay_color' => 'color',
        'overlay_opacity' => 'int:0:100',
        'text_color' => 'color',
        'padding_top' => 'int:0:600',
        'padding_bottom' => 'int:0:600',
        'content_width' => 'enum:boxed,full',
        'max_width' => 'int:320:2400',
        'gap' => 'int:0:200',
        'vertical_align' => 'enum:start,center,end,stretch',
        'min_height' => 'int:0:2000',
        'anchor' => 'slug:60',
    ];

    public const V1_COLUMN = [
        'width' => 'int:5:100',
        'padding' => 'int:0:200',
        'background_color' => 'color',
        'border_radius' => 'int:0:200',
        'vertical_align' => 'enum:start,center,end',
    ];

    public const V1_WIDGET = ['margin_bottom' => 'int:0:400'];

    public static function isV1(mixed $content): bool
    {
        return is_array($content) && ! array_key_exists('elements', $content) && is_array($content['sections'] ?? null);
    }

    public static function toV2(array $content): array
    {
        $elements = [];
        foreach (array_values($content['sections'] ?? []) as $section) {
            if (is_array($section)) {
                $elements[] = self::section($section);
            }
        }

        return ['version' => 2, 'elements' => $elements];
    }

    /**
     * Borne les nombres et vérifie les listes de choix du format 1 avant la conversion
     * (couleurs, adresses et textes sont validés ensuite par ContentSanitizer).
     */
    private static function clean(mixed $settings, array $rules): array
    {
        $o = is_array($settings) ? $settings : [];
        foreach ($rules as $key => $rule) {
            if (! array_key_exists($key, $o)) {
                continue;
            }
            [$type, $args] = array_pad(explode(':', $rule, 2), 2, '');
            if ($type === 'int') {
                [$min, $max] = array_map('intval', explode(':', $args));
                $o[$key] = is_numeric($o[$key]) ? max($min, min($max, (int) round((float) $o[$key]))) : null;
            } elseif ($type === 'enum' && ! in_array((string) $o[$key], explode(',', $args), true)) {
                $o[$key] = null;
            }
            if ($o[$key] === null) {
                unset($o[$key]);
            }
        }

        return $o;
    }

    private static function section(array $s): array
    {
        $o = self::clean($s['settings'] ?? null, self::V1_SECTION + ['padding_top_mobile' => 'int:0:600', 'padding_bottom_mobile' => 'int:0:600']);
        $columns = array_values(array_filter((array) ($s['columns'] ?? []), 'is_array'));

        $settings = array_filter([
            'tag' => 'section',
            'content_width' => $o['content_width'] ?? null,
            'max_width' => $o['max_width'] ?? null,
            'padding' => ['t' => $o['padding_top'] ?? 64, 'r' => 20, 'b' => $o['padding_bottom'] ?? 64, 'l' => 20],
            'padding_mobile' => self::sides(['t' => $o['padding_top_mobile'] ?? null, 'b' => $o['padding_bottom_mobile'] ?? null]),
            'background_color' => $o['background_color'] ?? null,
            'background_image' => $o['background_image'] ?? null,
            'overlay_color' => $o['overlay_color'] ?? null,
            'overlay_opacity' => $o['overlay_opacity'] ?? null,
            'text_color' => $o['text_color'] ?? null,
            'min_height' => isset($o['min_height']) && is_numeric($o['min_height']) ? ((int) $o['min_height']).'px' : null,
            'css_id' => $o['anchor'] ?? null,
        ], fn ($v) => $v !== null);
        if (isset($settings['background_image'])) {
            $settings += ['background_size' => 'cover', 'background_position' => 'center'];
        }

        $single = count($columns) <= 1;
        $column = $columns[0] ?? [];
        $columnSettings = array_diff_key(self::clean($column['settings'] ?? null, self::V1_COLUMN), ['width' => 1]);

        if ($single && $columnSettings === []) {
            // Une seule colonne, sans réglage : les widgets vont directement dans la section.
            return [
                'id' => $s['id'] ?? null,
                'type' => 'container',
                'settings' => $settings + ['direction' => 'column', 'gap' => 0],
                'children' => self::widgets($column['widgets'] ?? []),
            ];
        }

        $count = max(1, count($columns));
        $children = [];
        foreach ($columns as $c) {
            $children[] = self::column($c, $count);
        }

        return [
            'id' => $s['id'] ?? null,
            'type' => 'container',
            'settings' => $settings + [
                'direction' => 'row',
                'direction_mobile' => 'column',
                'gap' => $o['gap'] ?? 24,
                'align' => $o['vertical_align'] ?? 'stretch',
                // Sur mobile, les colonnes empilées prenaient toujours toute la largeur.
                'align_mobile' => 'stretch',
            ],
            'children' => $children,
        ];
    }

    private static function column(array $c, int $count): array
    {
        $o = self::clean($c['settings'] ?? null, self::V1_COLUMN);
        $width = is_numeric($o['width'] ?? null) ? (float) $o['width'] : 100 / $count;
        $padding = is_numeric($o['padding'] ?? null) ? (int) $o['padding'] : 0;
        $radius = is_numeric($o['border_radius'] ?? null) ? (int) $o['border_radius'] : null;

        return [
            'id' => $c['id'] ?? null,
            'type' => 'container',
            'settings' => array_filter([
                'direction' => 'column',
                'gap' => 0,
                'width' => rtrim(rtrim(number_format($width, 3, '.', ''), '0'), '.').'%',
                'padding' => ['t' => $padding, 'r' => $padding, 'b' => $padding, 'l' => $padding],
                'background_color' => $o['background_color'] ?? null,
                'border_radius' => $radius !== null ? ['t' => $radius, 'r' => $radius, 'b' => $radius, 'l' => $radius] : null,
                'justify' => $o['vertical_align'] ?? null,
            ], fn ($v) => $v !== null),
            'children' => self::widgets($c['widgets'] ?? []),
        ];
    }

    private static function widgets(mixed $widgets): array
    {
        $out = [];
        foreach ((array) $widgets as $w) {
            if (! is_array($w)) {
                continue;
            }
            $o = self::clean($w['settings'] ?? null, self::V1_WIDGET);
            if (isset($o['margin_bottom'])) {
                $o['margin'] = ['b' => $o['margin_bottom']];
                unset($o['margin_bottom']);
            }
            // « Masquer sur ordinateur » valait pour tout écran de 768 px et plus.
            if (! empty($o['hide_desktop'])) {
                $o['hide_tablet'] = true;
            }
            $w['settings'] = $o;
            $out[] = $w;
        }

        return $out;
    }

    private static function sides(array $sides): ?array
    {
        $sides = array_filter($sides, fn ($v) => $v !== null);

        return $sides ?: null;
    }
}
