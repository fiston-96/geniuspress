<?php

namespace App\Templates;

use App\Templates\Blocks as B;
use App\Templates\Kit as K;

/**
 * Blocs propres aux sites de médias (journaux, magazines, radios, télévisions,
 * podcasts, journalistes) : flash info, la une, direct, replays, grille des
 * programmes, audience, rubriques, parcours, plateformes d'écoute ; grille
 * d'articles avec options (carrousel) ; gabarits du blog enrichis (partage,
 * barre de lecture, rubriques). Comme les blocs communs (Blocks), ils
 * n'utilisent que les couleurs globales.
 *
 * Fichier à part de Blocks et BlogKit : les animations des modèles y sont
 * ajoutées par ailleurs ; les sections produites ici passent par Kit::section
 * et Kit::grid et en héritent.
 */
final class MediaBlocks
{
    // ─── Éléments communs (mêmes règles que Blocks) ─────────────────────

    private static function title(array $d, string $align = 'center', bool $dark = false): ?array
    {
        if (empty($d['title'])) {
            return null;
        }

        return K::box([
            ! empty($d['eyebrow']) ? K::eyebrow($d['eyebrow'], ['align' => $align, 'color' => $dark ? 'global:accent' : 'global:primary']) : null,
            K::h($d['title'], $d['tag'] ?? 'h2', ['align' => $align, 'font_size' => 40, 'font_size_tablet' => 34, 'font_size_mobile' => 28, 'font_weight' => '800', 'line_height' => '1.15']),
            ! empty($d['intro']) ? K::p($d['intro'], ['align' => $align, 'font_size' => 18, 'color' => $dark ? 'rgba(255,255,255,0.78)' : 'global:doux', 'line_height' => '1.65']) : null,
        ], ['gap' => 14, 'content_width' => 'boxed', 'max_width' => $align === 'center' ? 760 : 1140, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]);
    }

    private static function photo(?array $p, int $radius = 0, bool $shadow = false): ?array
    {
        if (! $p) {
            return null;
        }

        return K::box([K::photo($p[0], $p[1], $p[2])], array_filter([
            'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0],
            'border_radius' => $radius ? self::corners($radius) : null,
            'overflow' => $radius ? 'hidden' : null,
            'box_shadow' => $shadow ? ['x' => 0, 'y' => 30, 'blur' => 60, 'spread' => -20, 'color' => 'rgba(15,23,42,0.35)'] : null,
        ]));
    }

    private static function buttons(Theme $t, array $buttons, string $align = 'left', bool $dark = false): ?array
    {
        if (! $buttons) {
            return null;
        }
        $out = [];
        foreach (array_values($buttons) as $i => $b) {
            $variant = $b[2] ?? ($i === 0 ? ($dark ? 'accent' : 'primary') : ($dark ? 'outline-light' : 'outline'));
            $out[] = K::btn($t, $b[0], $b[1], $variant);
        }

        return K::buttons($out, $align);
    }

    private static function bg(string $tone): array
    {
        return match ($tone) {
            'light' => ['background_color' => 'global:clair'],
            'dark' => ['background_color' => 'global:secondary', 'text_color' => '#ffffff'],
            'primary' => ['background_color' => 'global:primary', 'text_color' => '#ffffff'],
            default => [],
        };
    }

    private static function corners(int $r): array
    {
        return ['t' => $r, 'r' => $r, 'b' => $r, 'l' => $r];
    }

    // ─── Articles ───────────────────────────────────────────────────────

    /**
     * Grille d'articles du blog (Blocks::posts) avec options : layout (grid, list, carousel),
     * excerpt, more_link, ratio, autoplay.
     */
    public static function posts(Theme $t, array $d): array
    {
        $section = B::posts($t, $d);
        $carousel = ($d['layout'] ?? null) === 'carousel';
        $options = array_filter([
            'layout' => $d['layout'] ?? null,
            'show_excerpt' => $d['excerpt'] ?? null,
            'show_more' => $d['more_link'] ?? null,
            'ratio' => $d['ratio'] ?? null,
            'autoplay' => $carousel ? ($d['autoplay'] ?? true) : null,
            'interval' => $carousel ? 6 : null,
            'arrows' => $carousel ? true : null,
            'dots' => $carousel ? true : null,
        ], fn ($v) => $v !== null);

        return self::edit($section, 'posts', fn (array $w) => ['settings' => $options + $w['settings']] + $w);
    }

    // ─── Blocs des médias ───────────────────────────────────────────────

    /** Bandeau « Flash info » qui défile : label, items [[texte, lien?]]. */
    public static function ticker(Theme $t, array $d): array
    {
        return K::section($t, [K::w('ticker', [
            'label' => $d['label'],
            'items' => array_map(fn ($i) => array_filter(['text' => $i[0], 'link' => $i[1] ?? null]), $d['items']),
            'speed' => $d['speed'] ?? 45,
            'bg_color' => 'global:secondary', 'color' => '#ffffff', 'label_bg' => 'global:accent', 'label_color' => 'global:secondary',
        ])], ['padding' => ['t' => 14, 'r' => 20, 'b' => 14, 'l' => 20], 'padding_mobile' => ['t' => 10, 'b' => 10]] + self::bg($d['bg'] ?? 'white'));
    }

    /**
     * La une : grand diaporama des articles phares et, à côté, les derniers articles en liste.
     * $d : slides [[slot, icône, titre, texte, lien, sur-titre?]], read (texte du bouton), side (titre de la colonne),
     * count, button [texte, lien].
     */
    public static function headlines(Theme $t, array $d): array
    {
        $slider = K::w('slider', [
            // Le titre de la diapositive sert de texte alternatif : pas de champ « alt » pour ce widget.
            'slides' => array_map(fn ($s) => array_diff_key(K::photoFields($s[0], $s[1], $s[2]), ['alt' => 1]) + array_filter([
                'eyebrow' => $s[5] ?? null, 'title' => $s[2], 'text' => $s[3], 'button_text' => $d['read'] ?? null, 'button_url' => $s[4],
            ]), $d['slides']),
            'height' => 540, 'height_tablet' => 460, 'height_mobile' => 420, 'align' => 'left',
            'overlay' => 'global:secondary', 'overlay_opacity' => 58, 'color' => '#ffffff',
            'autoplay' => true, 'interval' => 7, 'arrows' => true, 'dots' => true,
            'radius' => $t->radius, 'title_size' => 38, 'title_size_mobile' => 26,
        ]);
        $side = K::col([
            K::h($d['side'], 'h2', ['font_size' => 22, 'font_weight' => '800', 'text_transform' => 'uppercase', 'letter_spacing' => '0.06em', 'color' => 'global:primary',
                'padding' => ['t' => 0, 'r' => 0, 'b' => 10, 'l' => 0], 'border_style' => 'solid', 'border_width' => ['t' => 0, 'r' => 0, 'b' => 3, 'l' => 0], 'border_color' => 'global:accent']),
            K::w('posts', [
                'source' => 'latest', 'count' => $d['count'] ?? 3, 'layout' => 'list', 'style' => 'flat', 'ratio' => '4:3', 'gap' => 14,
                'show_excerpt' => false, 'show_more' => false, 'title_size' => 17, 'accent' => 'global:primary',
            ]),
            ! empty($d['button']) ? self::buttons($t, [[$d['button'][0], $d['button'][1], 'outline']]) : null,
        ], '36%', ['gap' => 18]);

        return K::section($t, [K::row([K::col([$slider], '64%'), $side], ['gap' => 36, 'gap_tablet' => 28])],
            ['padding' => ['t' => 40, 'r' => 20, 'b' => 72, 'l' => 20], 'padding_mobile' => ['t' => 24, 'b' => 48]]);
    }

    /**
     * Direct d'une radio ou d'une télévision, sur fond foncé : pastille (badge), titre, texte,
     * infos pratiques (items [[icône, texte]]), boutons (un lien externe s'ouvre dans un nouvel onglet) ;
     * à côté, la vidéo du direct (video : lien YouTube ou Vimeo) ou une photo (photo).
     */
    public static function live(Theme $t, array $d): array
    {
        $buttons = [];
        foreach (array_values($d['buttons'] ?? []) as $i => $b) {
            $buttons[] = K::btn($t, $b[0], $b[1], $b[2] ?? ($i === 0 ? 'accent' : 'outline-light'), str_starts_with($b[1], 'http') ? ['new_tab' => true] : []);
        }
        $text = K::col([
            K::eyebrow('● '.$d['badge'], ['color' => 'global:accent', 'font_size' => 15]),
            K::h($d['title'], $d['tag'] ?? 'h2', ['font_size' => 44, 'font_size_tablet' => 38, 'font_size_mobile' => 30, 'font_weight' => '800', 'line_height' => '1.12']),
            K::p($d['text'], ['font_size' => 18, 'line_height' => '1.65', 'color' => 'rgba(255,255,255,0.82)']),
            ! empty($d['items']) ? K::w('icon-list', ['items' => array_map(fn ($i) => ['icon' => 'lucide:'.$i[0], 'text' => $i[1]], $d['items']), 'icon_color' => 'global:accent', 'text_color' => '#ffffff', 'gap' => 12]) : null,
            $buttons ? K::buttons($buttons) : null,
        ], '48%', ['gap' => 18, 'justify' => 'center']);
        $media = ! empty($d['video'])
            ? K::box([K::w('video', ['url' => $d['video'], 'aspect' => '16:9'])], ['padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0], 'border_radius' => self::corners($t->radius), 'overflow' => 'hidden'])
            : self::photo($d['photo'] ?? null, $t->radius * 2, true);

        return K::section($t, [K::row([$text, K::col([$media], '52%')], ['gap' => 56, 'gap_tablet' => 32, 'align' => 'center'])],
            self::bg('dark') + ['css_id' => $d['anchor'] ?? 'direct']);
    }

    /** Vidéos (replays, émissions, reportages) : lecteur et liste, items [[titre, lien YouTube ou Vimeo, durée?]]. */
    public static function videos(Theme $t, array $d): array
    {
        return K::section($t, [self::title($d), K::w('video-playlist', [
            'items' => array_map(fn ($i) => array_filter(['title' => $i[0], 'url' => $i[1], 'duration' => $i[2] ?? null]), $d['items']),
            // La liste est écrite en clair : elle garde un fond foncé (couleur secondaire du site).
            'layout' => $d['layout'] ?? 'side', 'accent' => 'global:primary', 'list_bg' => 'global:secondary',
        ])], self::bg($d['bg'] ?? 'white'));
    }

    /** Tableau (grille des programmes, tarifs détaillés) : rows [[cellules]], la première ligne sert d'en-tête ; note facultative. */
    public static function table(Theme $t, array $d): array
    {
        return K::section($t, [
            self::title($d),
            K::w('data-table', [
                'data' => implode("\n", array_map(fn ($r) => implode(' | ', $r), $d['rows'])),
                'header' => true, 'striped' => true, 'caption' => $d['caption'] ?? null,
                'header_bg' => 'global:primary', 'header_color' => '#ffffff', 'border_color' => '#e2e8f0', 'font_size' => 16, 'font_size_mobile' => 14,
            ]),
            ! empty($d['note']) ? K::p($d['note'], ['align' => 'center', 'color' => 'global:doux', 'font_size' => 15]) : null,
        ], self::bg($d['bg'] ?? 'white') + ['gap' => 32]);
    }

    /**
     * Audience ou chiffres clés en graphique : texte et puces d'un côté, graphique dans une carte de l'autre.
     * $d : eyebrow, title, html, bullets, buttons, reverse, chart [kind, rows [[libellé, valeur…]], series?, title?, suffix?].
     */
    public static function chart(Theme $t, array $d): array
    {
        $c = $d['chart'];
        $text = K::col([
            ! empty($d['eyebrow']) ? K::eyebrow($d['eyebrow']) : null,
            K::h($d['title'], 'h2', ['font_size' => 38, 'font_size_mobile' => 28, 'font_weight' => '800', 'line_height' => '1.15']),
            K::p($d['html'], ['font_size' => 17, 'line_height' => '1.7', 'color' => 'global:doux']),
            ! empty($d['bullets']) ? K::w('icon-list', ['items' => array_map(fn ($b) => ['icon' => 'lucide:circle-check', 'text' => $b], $d['bullets']), 'icon_color' => 'global:primary', 'gap' => 12]) : null,
            self::buttons($t, $d['buttons'] ?? []),
        ], '45%', ['gap' => 18, 'justify' => 'center']);
        $chart = K::col([K::w('chart', [
            'kind' => $c['kind'] ?? 'bar',
            'data' => implode("\n", array_map(fn ($r) => implode(' | ', $r), $c['rows'])),
            'series' => isset($c['series']) ? implode(' | ', $c['series']) : null,
            'title' => $c['title'] ?? null, 'suffix' => $c['suffix'] ?? null,
            'ratio' => '4:3', 'show_values' => $c['values'] ?? true, 'show_grid' => true, 'show_legend' => true,
            'color_1' => 'global:primary', 'color_2' => 'global:accent', 'color_3' => 'global:secondary', 'color_4' => 'global:doux',
        ])], '55%', $t->card(['padding' => ['t' => 28, 'r' => 24, 'b' => 24, 'l' => 24]]));

        return K::section($t, [K::row(! empty($d['reverse']) ? [$chart, $text] : [$text, $chart], ['gap' => 56, 'gap_tablet' => 32, 'align' => 'center'])], self::bg($d['bg'] ?? 'white'));
    }

    /** Rubriques (catégories) ou étiquettes du blog, avec leur nombre d'articles : taxonomy, layout (inline, cloud, list). */
    public static function terms(Theme $t, array $d): array
    {
        return K::section($t, [
            self::title($d),
            K::w('terms-list', ['taxonomy' => $d['taxonomy'] ?? 'category', 'layout' => $d['layout'] ?? 'inline', 'show_count' => $d['count'] ?? true, 'align' => 'center', 'font_size' => 16, 'accent' => 'global:primary']),
        ], self::bg($d['bg'] ?? 'white') + ['gap' => 24, 'padding' => ['t' => 48, 'r' => 20, 'b' => 48, 'l' => 20], 'padding_mobile' => ['t' => 32, 'b' => 32]]);
    }

    /** Parcours ou histoire en frise : items [[date, titre, texte]]. */
    public static function timeline(Theme $t, array $d): array
    {
        return K::section($t, [self::title($d), K::box([K::w('timeline', [
            'items' => array_map(fn ($i) => ['date' => $i[0], 'title' => $i[1], 'text' => $i[2]], $d['items']),
            'layout' => 'alternate', 'dot_color' => 'global:primary', 'date_color' => 'global:primary', 'card' => true,
        ])], ['content_width' => 'boxed', 'max_width' => 980, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]])], self::bg($d['bg'] ?? 'white'));
    }

    /** Plateformes d'écoute ou réseaux, en grandes icônes de marque centrées : items [[marque, libellé]]. */
    public static function platforms(Theme $t, array $d): array
    {
        return K::section($t, [
            self::title($d),
            K::w('social-icons', ['items' => array_map(fn ($i) => ['icon' => 'brand:'.$i[0], 'url' => $i[2] ?? 'https://', 'label' => $i[1]], $d['items']), 'size' => 26, 'shape' => 'circle', 'gap' => 18, 'align' => 'center']),
        ], self::bg($d['bg'] ?? 'light') + ['gap' => 28, 'padding' => ['t' => 64, 'r' => 20, 'b' => 64, 'l' => 20], 'padding_mobile' => ['t' => 44, 'b' => 44]]);
    }

    // ─── Gabarits du blog ───────────────────────────────────────────────

    /**
     * Gabarits du blog (BlogKit::layouts) enrichis pour un média. Options : share — barre de
     * progression de lecture et boutons de partage sur l'article ; terms — rubriques sous la
     * recherche de l'archive et sujets populaires en bas de l'article.
     *
     * @param  list<array{name: string, type: string, content: array}>  $layouts
     */
    public static function blogLayouts(array $layouts, Theme $t, callable $T, array $options = []): array
    {
        $share = ! empty($options['share']);
        $topics = ! empty($options['terms']);
        $dark = $t->hero !== 'split';
        if (! $share && ! $topics) {
            return $layouts;
        }

        foreach ($layouts as &$layout) {
            $elements = $layout['content']['elements'];
            if ($layout['type'] === 'single') {
                if ($share) {
                    array_unshift($elements[0]['children'], K::w('reading-progress', ['color' => 'global:accent', 'height' => 4, 'position' => 'top']));
                    $elements = self::insertAfter($elements, fn ($w) => $w['type'] === 'post-meta' && ! empty($w['settings']['show_tags']),
                        K::w('share-buttons', ['label' => $T('Partager cet article', 'Share this article'), 'style' => 'brand', 'shape' => $t->buttons === 'square' ? 'square' : 'circle', 'size' => 40, 'align' => 'left']));
                }
                if ($topics) {
                    $elements[] = self::terms($t, ['title' => $T('Sujets populaires', 'Popular topics'), 'taxonomy' => 'tag', 'layout' => 'cloud', 'count' => false]);
                }
            } elseif ($layout['type'] === 'archive' && $topics) {
                $elements = self::insertAfter($elements, fn ($w) => $w['type'] === 'search-form',
                    K::w('terms-list', ['taxonomy' => 'category', 'layout' => 'inline', 'show_count' => false, 'align' => 'center', 'font_size' => 15, 'color' => $dark ? '#ffffff' : null, 'accent' => $dark ? 'global:accent' : 'global:primary']));
            }
            $layout['content']['elements'] = $elements;
        }

        return $layouts;
    }

    // ─── Outils ─────────────────────────────────────────────────────────

    /** Modifie le premier widget du type donné dans un arbre d'éléments. */
    private static function edit(array $node, string $type, callable $change): array
    {
        if (($node['type'] ?? null) === $type) {
            return $change($node);
        }
        foreach ($node['children'] ?? [] as $i => $child) {
            $node['children'][$i] = self::edit($child, $type, $change);
        }

        return $node;
    }

    /** Insère un élément juste après le premier widget qui satisfait $match (dans le même conteneur). */
    private static function insertAfter(array $nodes, callable $match, array $new): array
    {
        $done = false;
        $walk = function (array $nodes) use (&$walk, &$done, $match, $new) {
            $out = [];
            foreach ($nodes as $node) {
                if (! $done && ($node['type'] ?? null) === 'container') {
                    $node['children'] = $walk($node['children'] ?? []);
                }
                $out[] = $node;
                if (! $done && ($node['type'] ?? null) !== 'container' && $match($node)) {
                    $out[] = $new;
                    $done = true;
                }
            }

            return $out;
        };

        return $walk($nodes);
    }
}
