<?php

namespace App\Templates;

use App\Templates\Kit as K;

/**
 * Blocs de sections réutilisés par tous les modèles. Chaque bloc reçoit le
 * thème et ses textes (déjà dans la langue de la page) et renvoie une section
 * de premier niveau au format 2. Les couleurs sont toujours globales.
 *
 * Photos : ['slot', 'icône lucide', 'texte alternatif'].
 */
final class Blocks
{
    // ─── Éléments communs ───────────────────────────────────────────────

    /** Sur-titre, titre et introduction d'une section. */
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

    /**
     * Photo dans un cadre arrondi (avec ombre si demandé). Le cadre porte l'arrondi
     * et masque les coins : le widget Image a son propre réglage d'arrondi (un nombre).
     */
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

    // ─── En-têtes ───────────────────────────────────────────────────────

    /**
     * Héros de la page d'accueil. $d : eyebrow, title, text, buttons [[texte, lien, variante?]], photo.
     * Variante selon le thème : split | center | image | gradient.
     */
    public static function hero(Theme $t, array $d): array
    {
        $title = fn (string $align, bool $dark) => [
            ! empty($d['eyebrow']) ? K::eyebrow($d['eyebrow'], ['align' => $align, 'color' => $dark ? 'global:accent' : 'global:primary']) : null,
            K::h($d['title'], 'h1', ['align' => $align, 'font_size' => 58, 'font_size_tablet' => 46, 'font_size_mobile' => 36, 'font_weight' => '800', 'line_height' => '1.08', 'letter_spacing' => '-0.02em']),
            K::p($d['text'], ['align' => $align, 'font_size' => 20, 'font_size_mobile' => 17, 'line_height' => '1.6', 'color' => $dark ? 'rgba(255,255,255,0.82)' : 'global:doux']),
            self::buttons($t, $d['buttons'] ?? [], $align, $dark),
        ];

        return match ($t->hero) {
            'center' => K::section($t, [K::box($title('center', true), ['gap' => 20, 'content_width' => 'boxed', 'max_width' => 860, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]])],
                self::bg('dark') + ['padding' => ['t' => 140, 'r' => 20, 'b' => 130, 'l' => 20], 'padding_mobile' => ['t' => 80, 'b' => 72]]),
            'image' => K::section($t, [K::box($title('left', true), ['gap' => 20, 'content_width' => 'boxed', 'max_width' => 1140, 'padding' => ['t' => 0, 'r' => 380, 'b' => 0, 'l' => 0], 'padding_tablet' => ['r' => 80], 'padding_mobile' => ['r' => 0]])], [
                'background_image' => K::photoUrl($d['photo'][0], $d['photo'][1], $d['photo'][2]), 'background_size' => 'cover',
                'overlay_color' => 'global:secondary', 'overlay_opacity' => 72, 'text_color' => '#ffffff', 'min_height' => '620px', 'min_height_mobile' => '480px',
                'justify' => 'center', 'padding' => ['t' => 120, 'r' => 20, 'b' => 120, 'l' => 20], 'padding_mobile' => ['t' => 80, 'b' => 72],
            ]),
            'gradient' => K::section($t, [K::box($title('center', true), ['gap' => 20, 'content_width' => 'boxed', 'max_width' => 880, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]])], [
                'background_type' => 'gradient', 'gradient_from' => 'global:primary', 'gradient_to' => 'global:secondary', 'gradient_angle' => 135, 'text_color' => '#ffffff',
                'padding' => ['t' => 140, 'r' => 20, 'b' => 130, 'l' => 20], 'padding_mobile' => ['t' => 80, 'b' => 72],
            ]),
            default => K::section($t, [K::row([
                K::col($title('left', false), '52%', ['gap' => 20, 'justify' => 'center']),
                K::col([self::photo($d['photo'] ?? null, $t->radius * 2, true)], '48%'),
            ], ['gap' => 64, 'gap_tablet' => 40, 'align' => 'center'])], self::bg('light') + ['padding' => ['t' => 96, 'r' => 20, 'b' => 96, 'l' => 20], 'padding_mobile' => ['t' => 56, 'b' => 56]]),
        };
    }

    /** En-tête des pages intérieures : titre et courte introduction. */
    public static function pageHeader(Theme $t, array $d): array
    {
        $style = $t->hero === 'split' ? self::bg('light') : ($t->hero === 'gradient'
            ? ['background_type' => 'gradient', 'gradient_from' => 'global:primary', 'gradient_to' => 'global:secondary', 'gradient_angle' => 135, 'text_color' => '#ffffff']
            : self::bg('dark'));
        $dark = $t->hero !== 'split';

        return K::section($t, [self::title(['eyebrow' => $d['eyebrow'] ?? null, 'title' => $d['title'], 'intro' => $d['intro'] ?? null, 'tag' => 'h1'], 'center', $dark)],
            $style + ['padding' => ['t' => 88, 'r' => 20, 'b' => 80, 'l' => 20], 'padding_mobile' => ['t' => 56, 'b' => 48]]);
    }

    // ─── Contenu ────────────────────────────────────────────────────────

    /** Grille de services, programmes ou valeurs : items [[icône, titre, texte]], 3 ou 4 colonnes. */
    public static function features(Theme $t, array $d): array
    {
        $cols = $d['cols'] ?? 3;
        $cards = array_map(fn ($i) => K::w('icon-box', $t->card() + [
            'icon' => 'lucide:'.$i[0], 'title' => $i[1], 'text' => '<p>'.e($i[2]).'</p>',
            'link' => $i[3] ?? null, 'link_text' => $i[4] ?? null,
            'position' => 'top', 'align' => 'left', 'icon_shape' => 'rounded', 'icon_size' => 26, 'icon_box' => 14,
            'icon_color' => 'global:primary', 'icon_bg_color' => $t->cards === 'flat' ? '#ffffff' : 'global:clair', 'title_size' => 21, 'text_color' => 'global:doux',
        ]), $d['items']);

        return K::section($t, [self::title($d), K::grid($cards, $cols)], self::bg($d['bg'] ?? 'white'));
    }

    /** Photo et texte côte à côte : html, puces (bullets) et bouton facultatifs ; reverse = photo à droite. */
    public static function split(Theme $t, array $d): array
    {
        $text = K::col([
            ! empty($d['eyebrow']) ? K::eyebrow($d['eyebrow']) : null,
            K::h($d['title'], 'h2', ['font_size' => 38, 'font_size_mobile' => 28, 'font_weight' => '800', 'line_height' => '1.15']),
            K::p($d['html'], ['font_size' => 17, 'line_height' => '1.7', 'color' => 'global:doux']),
            ! empty($d['bullets']) ? K::w('icon-list', ['items' => array_map(fn ($b) => ['icon' => 'lucide:circle-check', 'text' => $b], $d['bullets']), 'icon_color' => 'global:primary', 'gap' => 12]) : null,
            self::buttons($t, $d['buttons'] ?? []),
        ], '50%', ['gap' => 18, 'justify' => 'center']);
        $image = K::col([self::photo($d['photo'], $t->radius * 2)], '50%');

        return K::section($t, [K::row(! empty($d['reverse']) ? [$text, $image] : [$image, $text], ['gap' => 64, 'gap_tablet' => 40, 'align' => 'center', 'direction_mobile' => 'column'])], self::bg($d['bg'] ?? 'white'));
    }

    /** Chiffres clés : items [[nombre, suffixe, libellé]], sur fond foncé ou de couleur. */
    public static function stats(Theme $t, array $d): array
    {
        $counters = array_map(fn ($i) => K::w('counter', [
            'number' => $i[0], 'suffix' => $i[1], 'label' => $i[2], 'number_color' => '#ffffff', 'label_color' => 'rgba(255,255,255,0.8)', 'number_size' => 52, 'number_size_mobile' => 40,
        ]), $d['items']);

        return K::section($t, [self::title($d, 'center', true), K::grid($counters, count($counters), ['gap' => 24])], self::bg($d['bg'] ?? 'dark') + ['padding' => ['t' => 72, 'r' => 20, 'b' => 72, 'l' => 20]]);
    }

    /** Appel à l'action dans un grand cadre arrondi. */
    public static function cta(Theme $t, array $d): array
    {
        $tone = $d['tone'] ?? 'primary';
        $r = $t->radius * 2;
        $box = K::box([
            K::h($d['title'], 'h2', ['align' => 'center', 'font_size' => 40, 'font_size_mobile' => 28, 'font_weight' => '800']),
            K::p($d['text'], ['align' => 'center', 'font_size' => 18, 'color' => 'rgba(255,255,255,0.85)']),
            self::buttons($t, $d['buttons'] ?? [], 'center', true),
        ], self::bg($tone === 'dark' ? 'dark' : 'primary') + [
            'gap' => 18, 'padding' => ['t' => 72, 'r' => 40, 'b' => 72, 'l' => 40], 'padding_mobile' => ['t' => 48, 'r' => 24, 'b' => 48, 'l' => 24],
            'border_radius' => self::corners($r), 'content_width' => 'boxed', 'max_width' => 760,
        ]);

        return K::section($t, [$box], ['padding' => ['t' => 48, 'r' => 20, 'b' => 96, 'l' => 20]]);
    }

    /** Témoignages : items [[texte, nom, fonction]]. */
    public static function testimonials(Theme $t, array $d): array
    {
        $cards = array_map(fn ($i) => K::w('testimonial', [
            'text' => $i[0], 'name' => $i[1], 'role' => $i[2], 'rating' => $d['rating'] ?? 0, 'style' => 'card', 'accent' => 'global:accent',
            'border_radius' => self::corners($t->radius),
        ]), $d['items']);

        return K::section($t, [self::title($d), K::grid($cards, min(3, count($cards)))], self::bg($d['bg'] ?? 'light'));
    }

    /** Équipe : items [[nom, fonction, slot]]. */
    public static function team(Theme $t, array $d): array
    {
        $cards = array_map(fn ($i) => K::w('team-member', [
            'name' => $i[0], 'role' => $i[1], 'image_shape' => $d['shape'] ?? 'rounded', 'style' => 'plain',
            // Le nom sert de texte alternatif à la photo : pas de champ « alt » pour ce widget.
        ] + array_diff_key(K::photoFields($i[2], 'user-round', $i[0]), ['alt' => 1])), $d['items']);

        return K::section($t, [self::title($d), K::grid($cards, min(4, count($cards)))], self::bg($d['bg'] ?? 'white'));
    }

    /** Tarifs : items [[titre, prix, devise, période, description, [avantages], bouton, mis en avant?, étiquette?]]. */
    public static function pricing(Theme $t, array $d): array
    {
        $cards = array_map(fn ($i) => K::w('pricing', [
            'title' => $i[0], 'price' => $i[1], 'currency' => $i[2], 'period' => $i[3], 'description' => $i[4],
            'features' => array_map(fn ($f) => ['text' => ltrim($f, '-'), 'included' => ! str_starts_with($f, '-')], $i[5]),
            'button_text' => $i[6], 'button_url' => $d['url'] ?? '#contact', 'featured' => $i[7] ?? false, 'badge' => $i[8] ?? null, 'accent' => 'global:primary',
        ]), $d['items']);

        return K::section($t, [self::title($d), K::grid($cards, min(3, count($cards)), ['align' => 'stretch'])], self::bg($d['bg'] ?? 'light'));
    }

    /** Questions fréquentes : titre et texte à gauche, accordéon à droite. items [[question, réponse]]. */
    public static function faq(Theme $t, array $d): array
    {
        return K::section($t, [K::row([
            K::col([
                self::title(['eyebrow' => $d['eyebrow'] ?? null, 'title' => $d['title'], 'intro' => $d['intro'] ?? null], 'left'),
                self::buttons($t, $d['buttons'] ?? []),
            ], '38%', ['gap' => 20]),
            K::col([K::w('accordion', [
                'items' => array_map(fn ($i) => ['title' => $i[0], 'html' => '<p>'.e($i[1]).'</p>'], $d['items']),
                'open_first' => true, 'faq_schema' => true, 'accent' => 'global:primary', 'style' => 'boxed',
            ])], '62%'),
        ], ['gap' => 56, 'gap_tablet' => 32])], self::bg($d['bg'] ?? 'white'));
    }

    /** Contact : coordonnées (items [[icône, texte, lien?]]) et formulaire ; carte facultative (map = adresse). */
    public static function contact(Theme $t, array $d): array
    {
        $info = K::col([
            self::title(['eyebrow' => $d['eyebrow'] ?? null, 'title' => $d['title'], 'intro' => $d['intro'] ?? null], 'left'),
            K::w('icon-list', ['items' => array_map(fn ($i) => array_filter(['icon' => 'lucide:'.$i[0], 'text' => $i[1], 'url' => $i[2] ?? null]), $d['items']), 'icon_color' => 'global:primary', 'icon_size' => 22, 'gap' => 16]),
            ! empty($d['social']) ? K::w('social-icons', ['items' => array_map(fn ($s) => ['icon' => 'brand:'.$s, 'url' => 'https://'], $d['social']), 'shape' => 'circle', 'size' => 18]) : null,
        ], '42%', ['gap' => 24]);
        $form = K::col([K::w('contact-form', [
            'show_phone' => true, 'show_subject' => $d['subject'] ?? false, 'button_text' => $d['button'] ?? null, 'button_color' => 'global:primary', 'field_radius' => min(12, $t->radius),
        ])], '58%', $t->card(['padding' => ['t' => 40, 'r' => 36, 'b' => 40, 'l' => 36], 'padding_mobile' => ['t' => 28, 'r' => 20, 'b' => 28, 'l' => 20]]));

        return K::section($t, [
            K::row([$info, $form], ['gap' => 56, 'gap_tablet' => 32]),
            ! empty($d['map']) ? K::w('map', ['address' => $d['map'], 'zoom' => 14, 'height' => 380, 'height_mobile' => 280, 'radius' => $t->radius]) : null,
        ], self::bg($d['bg'] ?? 'light') + ['css_id' => 'contact']);
    }

    /** Galerie de photos : slots [[slot, icône, alt]]. */
    public static function gallery(Theme $t, array $d): array
    {
        return K::section($t, [
            self::title($d),
            K::w('gallery', ['images' => array_map(fn ($p) => K::photoFields($p[0], $p[1], $p[2]), $d['photos']), 'columns' => $d['cols'] ?? 3, 'columns_tablet' => 2, 'columns_mobile' => 1, 'gap' => 16, 'ratio' => '4:3', 'radius' => $t->radius, 'lightbox' => true]),
        ], self::bg($d['bg'] ?? 'white'));
    }

    /** Cartes avec photo (projets, actualités, départements) : items [[slot, icône, titre, texte, lien?]]. */
    public static function cards(Theme $t, array $d): array
    {
        // Carte : photo légèrement en retrait, puis titre et texte.
        $cards = array_map(fn ($i) => K::w('image-box', $t->card(['padding' => ['t' => 14, 'r' => 14, 'b' => 26, 'l' => 14]]) + K::photoFields($i[0], $i[1], $i[2]) + [
            'title' => $i[2], 'text' => '<p>'.e($i[3]).'</p>', 'link' => $i[4] ?? null, 'link_text' => isset($i[4]) ? ($d['more'] ?? null) : null,
            'ratio' => '16:9', 'image_radius' => max(0, $t->radius - 6), 'title_size' => 21, 'text_color' => 'global:doux',
        ]), $d['items']);

        return K::section($t, [self::title($d), K::grid($cards, $d['cols'] ?? 3)], self::bg($d['bg'] ?? 'white'));
    }

    /** Étapes numérotées : items [[titre, texte]]. */
    public static function steps(Theme $t, array $d): array
    {
        $items = [];
        foreach (array_values($d['items']) as $n => $i) {
            $items[] = K::box([
                K::h((string) ($n + 1), 'p', ['font_size' => 44, 'font_weight' => '800', 'color' => 'global:accent', 'line_height' => '1']),
                K::h($i[0], 'h3', ['font_size' => 21]),
                K::p($i[1], ['color' => 'global:doux']),
            ], ['gap' => 10, 'border_style' => 'solid', 'border_width' => ['t' => 3, 'r' => 0, 'b' => 0, 'l' => 0], 'border_color' => 'global:primary', 'padding' => ['t' => 24, 'r' => 4, 'b' => 8, 'l' => 0]]);
        }

        return K::section($t, [self::title($d), K::grid($items, count($items))], self::bg($d['bg'] ?? 'white'));
    }

    /** Collecte de dons : texte, barre de progression, montants, photo. */
    public static function donate(Theme $t, array $d): array
    {
        $amounts = array_map(fn ($a) => K::btn($t, $a, $d['url'] ?? '#contact', 'outline', ['size' => 'md']), $d['amounts'] ?? []);

        return K::section($t, [K::row([
            K::col([
                ! empty($d['eyebrow']) ? K::eyebrow($d['eyebrow']) : null,
                K::h($d['title'], 'h2', ['font_size' => 38, 'font_size_mobile' => 28, 'font_weight' => '800']),
                K::p($d['text'], ['font_size' => 17, 'color' => 'global:doux', 'line_height' => '1.7']),
                K::w('progress', ['label' => $d['goal'], 'percent' => $d['percent'] ?? 0, 'bar_color' => 'global:accent', 'height' => 14]),
                $amounts ? K::buttons($amounts) : null,
                self::buttons($t, [[$d['button'], $d['url'] ?? '#contact', 'primary']]),
            ], '55%', ['gap' => 18, 'justify' => 'center']),
            K::col([self::photo($d['photo'], $t->radius * 2)], '45%'),
        ], ['gap' => 64, 'gap_tablet' => 40, 'align' => 'center'])], self::bg($d['bg'] ?? 'light') + ['css_id' => $d['anchor'] ?? 'dons']);
    }

    /** Agenda : items [[titre, date, lieu]] présentés en cartes avec une icône de calendrier. */
    public static function events(Theme $t, array $d): array
    {
        $cards = array_map(fn ($i) => K::w('icon-box', $t->card() + [
            'icon' => 'lucide:'.($d['icon'] ?? 'calendar-days'), 'title' => $i[0], 'text' => '<p><strong>'.e($i[1]).'</strong><br>'.e($i[2]).'</p>',
            'position' => 'left', 'align' => 'left', 'icon_shape' => 'rounded', 'icon_size' => 24, 'icon_box' => 14, 'icon_color' => '#ffffff', 'icon_bg_color' => 'global:primary', 'title_size' => 20, 'text_color' => 'global:doux',
        ]), $d['items']);

        return K::section($t, [self::title($d), K::grid($cards, $d['cols'] ?? 2)], self::bg($d['bg'] ?? 'white'));
    }

    /** Horaires ou informations pratiques en deux colonnes : groups [[titre, [[icône, ligne]]]]. */
    public static function schedule(Theme $t, array $d): array
    {
        $cols = array_map(fn ($g) => K::box([
            K::h($g[0], 'h3', ['font_size' => 22]),
            K::w('icon-list', ['items' => array_map(fn ($l) => ['icon' => 'lucide:'.$l[0], 'text' => $l[1]], $g[1]), 'icon_color' => 'global:primary', 'gap' => 12]),
        ], $t->card(['gap' => 16])), $d['groups']);

        return K::section($t, [self::title($d), K::grid($cols, count($cols))], self::bg($d['bg'] ?? 'light'));
    }

    /** Grande citation centrée (mission, verset, devise). */
    public static function quote(Theme $t, array $d): array
    {
        return K::section($t, [K::box([
            K::w('icon', ['icon' => 'lucide:quote', 'size' => 44, 'color' => 'global:accent']),
            K::h($d['text'], 'p', ['align' => 'center', 'font_size' => 30, 'font_size_mobile' => 22, 'font_weight' => '600', 'line_height' => '1.4', 'font_family' => 'global:heading']),
            ! empty($d['source']) ? K::p($d['source'], ['align' => 'center', 'color' => 'global:doux', 'font_size' => 16]) : null,
        ], ['gap' => 18, 'content_width' => 'boxed', 'max_width' => 860, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]])], self::bg($d['bg'] ?? 'white'));
    }

    /** Valeurs ou piliers en colonnes simples (sans carte) : items [[icône, titre, texte]]. */
    public static function pillars(Theme $t, array $d): array
    {
        $items = array_map(fn ($i) => K::w('icon-box', [
            'icon' => 'lucide:'.$i[0], 'title' => $i[1], 'text' => '<p>'.e($i[2]).'</p>', 'position' => 'top', 'align' => 'center',
            'icon_shape' => 'circle', 'icon_size' => 28, 'icon_box' => 18, 'icon_color' => '#ffffff', 'icon_bg_color' => 'global:primary', 'title_size' => 22, 'text_color' => ($d['bg'] ?? '') === 'dark' ? null : 'global:doux',
        ]), $d['items']);

        return K::section($t, [self::title($d, 'center', ($d['bg'] ?? '') === 'dark'), K::grid($items, count($items), ['gap' => 40])], self::bg($d['bg'] ?? 'white'));
    }

    private static function corners(int $r): array
    {
        return ['t' => $r, 'r' => $r, 'b' => $r, 'l' => $r];
    }
}
