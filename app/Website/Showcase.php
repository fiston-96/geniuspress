<?php

namespace App\Website;

use App\Templates\Kit as K;
use App\Templates\PhotoLibrary;
use App\Templates\TemplateBuilder;
use App\Templates\Theme;

/**
 * Éléments de la vitrine geniuspress.site qui présentent les modèles de sites :
 * aperçus fidèles du haut de la page d'accueil de chaque modèle (même variante,
 * mêmes couleurs, mêmes polices, même photo) et fiches avec lien vers la démonstration.
 *
 * Contrairement aux modèles, la vitrine montre les couleurs propres à chaque
 * modèle : elles sont écrites telles quelles, pas en couleurs globales.
 */
final class Showcase
{
    /** @var array<string, array>|null */
    private static ?array $catalog = null;

    /** Adresse du site affichée dans la barre des miniatures (option --host de geniuspress:website). */
    public static string $host = 'geniuspress.site';

    /** @var array<string, array> métadonnées des secteurs (nom, icône) */
    private static array $sectors = [];

    /** Modèles du catalogue (templates/catalog.php), par identifiant. */
    public static function catalog(): array
    {
        return self::$catalog ??= require base_path('templates/catalog.php');
    }

    /** Secteurs dans l'ordre du catalogue : [secteur => [name => [fr, en], icon]]. */
    public static function sectors(): array
    {
        if (! self::$sectors) {
            $builder = new TemplateBuilder(base_path('templates/sectors'));
            foreach (self::catalog() as $def) {
                self::$sectors[$def['sector']] ??= $builder->pack($def['pack'] ?? $def['sector'])['sector'];
            }
        }

        return self::$sectors;
    }

    /** Modèles d'un secteur. */
    public static function bySector(string $sector): array
    {
        return array_filter(self::catalog(), fn ($d) => $d['sector'] === $sector);
    }

    /** Adresse de la démonstration d'un modèle (sans installation). */
    public static function demoUrl(string $id, string $lang): string
    {
        return '/catalogue/'.$id.($lang === 'fr' ? '' : '/en');
    }

    /**
     * Haut de la page d'accueil d'un modèle, en miniature : la variante du héros
     * (split, center, image, gradient), ses couleurs, ses polices et sa photo.
     */
    public static function miniHero(string $id, array $def, string $lang, int $height = 210): array
    {
        /** @var Theme $th */
        $th = $def['theme'];
        $c = $th->colors;
        $heading = $th->headingFont ?? $th->font;
        $name = $def['name'][$lang];
        $sector = self::sectors()[$def['sector']]['name'][$lang] ?? '';
        $slot = ($def['pack'] ?? $def['sector']).'-accueil';
        $dark = $th->hero !== 'split';
        $radius = $th->buttonRadius();
        $align = in_array($th->hero, ['center', 'gradient'], true) ? 'center' : 'left';

        $text = [
            K::h(mb_strtoupper($sector), 'p', ['align' => $align, 'font_size' => 10, 'font_weight' => '700', 'letter_spacing' => '0.12em', 'color' => $dark ? $c['accent'] : $c['primary'], 'font_family' => $th->font]),
            K::h($name, 'p', ['align' => $align, 'font_size' => $dark ? 26 : 22, 'font_weight' => '800', 'line_height' => '1.1', 'color' => $dark ? '#ffffff' : $c['text'], 'font_family' => $heading]),
            K::box([
                K::w('button', ['text' => $lang === 'fr' ? 'Découvrir' : 'Discover', 'url' => self::demoUrl($id, $lang), 'size' => 'sm', 'font_size' => 11, 'border_radius' => $radius,
                    'bg_color' => $dark ? $c['accent'] : $c['primary'], 'text_color' => $dark ? $c['secondary'] : '#ffffff', 'new_tab' => true]),
                K::w('button', ['text' => 'Contact', 'url' => self::demoUrl($id, $lang), 'size' => 'sm', 'font_size' => 11, 'border_radius' => $radius, 'variant' => 'outline',
                    'bg_color' => $dark ? '#ffffff' : $c['primary'], 'text_color' => $dark ? '#ffffff' : $c['primary'], 'new_tab' => true]),
            ], ['direction' => 'row', 'wrap' => 'wrap', 'gap' => 6, 'justify' => $align === 'center' ? 'center' : 'start', 'padding' => ['t' => 4, 'r' => 0, 'b' => 0, 'l' => 0]]),
        ];
        $base = ['min_height' => $height.'px', 'justify' => 'center', 'gap' => 8, 'padding' => ['t' => 22, 'r' => 22, 'b' => 22, 'l' => 22]];

        return match ($th->hero) {
            'image' => K::box($text, $base + [
                'background_image' => K::photoUrl($slot, 'image', $name), 'background_size' => 'cover',
                'overlay_color' => $c['secondary'], 'overlay_opacity' => 72,
            ]),
            'gradient' => K::box($text, $base + ['background_type' => 'gradient', 'gradient_from' => $c['primary'], 'gradient_to' => $c['secondary'], 'gradient_angle' => 135]),
            'center' => K::box($text, $base + ['background_color' => $c['secondary']]),
            default => K::box([
                K::box($text, ['gap' => 8, 'width' => '55%', 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]),
                // Photo dans un cadre de largeur fixe : le widget Image a sa propre largeur (en %).
                K::box([K::photo($slot, 'image', $name, ['border_radius' => min(12, $th->radius)])], ['width' => '45%', 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]),
            ], $base + ['background_color' => $c['light'], 'direction' => 'row', 'direction_mobile' => 'row', 'align' => 'center', 'gap' => 12]),
        };
    }

    /**
     * Aperçu d'un modèle : la vraie capture d'écran de sa démonstration (dist/website/captures,
     * produite par Chrome sans fenêtre), sinon la miniature dessinée par miniHero().
     */
    public static function shot(string $id, array $def, string $lang): array
    {
        if (! PhotoLibrary::file('capture-'.$id)) {
            return self::miniHero($id, $def, $lang);
        }
        $alt = ($lang === 'fr' ? 'Page d’accueil du modèle ' : 'Home page of the ').$def['name'][$lang].($lang === 'fr' ? '' : ' template');

        return K::photo('capture-'.$id, 'layout-template', $alt, ['link' => self::demoUrl($id, $lang), 'border_radius' => 0]);
    }

    /** Fenêtre de navigateur contenant la capture d'un modèle, ombre douce et coins arrondis. */
    public static function screen(string $id, string $lang, array $s = []): array
    {
        return K::box([self::browserBar($id), self::shot($id, self::catalog()[$id], $lang)], $s + [
            'gap' => 0, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0], 'overflow' => 'hidden', 'background_color' => '#ffffff',
            'border_radius' => ['t' => 12, 'r' => 12, 'b' => 12, 'l' => 12],
            'border_style' => 'solid', 'border_width' => ['t' => 1, 'r' => 1, 'b' => 1, 'l' => 1], 'border_color' => '#e7e0d4',
            'box_shadow' => ['x' => 0, 'y' => 30, 'blur' => 60, 'spread' => -24, 'color' => 'rgba(28,25,23,0.35)'],
        ]);
    }

    /** Barre de navigateur au-dessus d'un aperçu : trois points et l'adresse de la démonstration. */
    private static function browserBar(string $id): array
    {
        $dot = fn (string $color) => K::box([], ['width' => '9px', 'min_height' => '9px', 'background_color' => $color, 'border_radius' => ['t' => 9, 'r' => 9, 'b' => 9, 'l' => 9], 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0], 'grow' => false]);

        return K::box([
            $dot('#f87171'), $dot('#fbbf24'), $dot('#34d399'),
            // Adresse du site seulement (sans le chemin) : elle tient sur une ligne, même sur les petits aperçus.
            K::h(self::$host, 'p', ['font_size' => 12, 'font_family' => 'global:body', 'font_weight' => '400', 'color' => '#6f6862', 'margin' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 8]]),
        ], ['direction' => 'row', 'align' => 'center', 'gap' => 5, 'background_color' => '#f5f0e8', 'padding' => ['t' => 9, 'r' => 12, 'b' => 9, 'l' => 12]]);
    }

    /** Fiche complète d'un modèle : aperçu, palette, nom, description, lien vers la démonstration. */
    public static function card(string $id, array $def, string $lang): array
    {
        $th = $def['theme'];
        $t = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $styles = [
            'split' => $t('photo à côté du titre', 'photo beside the title'),
            'center' => $t('présentation centrée', 'centered layout'),
            'image' => $t('photo plein écran', 'full-screen photo'),
            'gradient' => $t('dégradé', 'gradient'),
        ];
        $swatch = fn (string $color) => K::box([], ['width' => '20px', 'min_height' => '20px', 'background_color' => $color, 'border_radius' => ['t' => 20, 'r' => 20, 'b' => 20, 'l' => 20],
            'border_style' => 'solid', 'border_width' => ['t' => 2, 'r' => 2, 'b' => 2, 'l' => 2], 'border_color' => '#ffffff', 'box_shadow' => ['x' => 0, 'y' => 0, 'blur' => 0, 'spread' => 1, 'color' => 'rgba(15,23,42,0.15)'],
            'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0], 'grow' => false]);
        $fonts = implode(' / ', array_unique(array_filter([$th->headingFont, $th->font])));

        return K::box([
            self::browserBar($id),
            self::shot($id, $def, $lang),
            K::box([
                K::box([
                    K::h($def['name'][$lang], 'h3', ['font_size' => 20, 'font_weight' => '700']),
                    K::box(array_map($swatch, [$th->colors['primary'], $th->colors['secondary'], $th->colors['accent'], $th->colors['light']]),
                        ['direction' => 'row', 'gap' => 4, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0], 'width' => '92px', 'grow' => false]),
                ], ['direction' => 'row', 'justify' => 'between', 'align' => 'center', 'gap' => 8, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]),
                K::p($def['description'][$lang], ['font_size' => 15, 'color' => 'global:doux', 'line_height' => '1.55']),
                K::p(($styles[$th->hero] ?? '').' · '.$fonts.' · '.$t('8 pages, FR + EN', '8 pages, FR + EN'), ['font_size' => 13, 'color' => '#6f6862']),
                K::box([
                    // Un œil seul : « Voir la démo » reste lu par les lecteurs d'écran et s'affiche au survol.
                    K::w('icon', ['icon' => 'lucide:eye', 'link' => self::demoUrl($id, $lang), 'label' => $t('Voir la démo', 'View the demo'), 'new_tab' => true,
                        'shape' => 'rounded', 'size' => 20, 'box' => 11, 'color' => '#ffffff', 'bg_color' => 'global:primary', 'bg_color_hover' => 'global:secondary', 'align' => 'left']),
                ], ['direction' => 'row', 'padding' => ['t' => 6, 'r' => 0, 'b' => 0, 'l' => 0], 'grow' => true, 'justify' => 'start', 'align' => 'end']),
            ], ['gap' => 10, 'grow' => true, 'padding' => ['t' => 18, 'r' => 20, 'b' => 20, 'l' => 20]]),
        ], [
            'gap' => 0, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0], 'overflow' => 'hidden', 'background_color' => '#ffffff',
            'border_radius' => ['t' => 16, 'r' => 16, 'b' => 16, 'l' => 16],
            'border_style' => 'solid', 'border_width' => ['t' => 1, 'r' => 1, 'b' => 1, 'l' => 1], 'border_color' => '#e7e0d4',
            'box_shadow' => ['x' => 0, 'y' => 12, 'blur' => 32, 'spread' => -18, 'color' => 'rgba(28,25,23,0.18)'],
            'box_shadow_hover' => ['x' => 0, 'y' => 26, 'blur' => 50, 'spread' => -18, 'color' => 'rgba(28,25,23,0.28)'], 'transition' => 300,
        ]);
    }

    /** Tuile d'un widget du builder : icône et nom (palette « comme dans l'éditeur »). */
    public static function widgetTile(string $icon, string $name): array
    {
        return K::w('icon-box', [
            'icon' => 'lucide:'.$icon, 'title' => $name, 'position' => 'top', 'align' => 'center', 'icon_size' => 26, 'icon_shape' => 'none',
            'icon_color' => 'global:primary', 'icon_color_hover' => 'global:accent', 'title_size' => 14, 'title_tag' => 'p',
            'background_color' => '#ffffff', 'border_style' => 'solid', 'border_width' => ['t' => 1, 'r' => 1, 'b' => 1, 'l' => 1], 'border_color' => '#e7e0d4', 'border_color_hover' => 'global:primary',
            'border_radius' => ['t' => 12, 'r' => 12, 'b' => 12, 'l' => 12], 'padding' => ['t' => 18, 'r' => 8, 'b' => 14, 'l' => 8], 'transition' => 200,
        ]);
    }
}
