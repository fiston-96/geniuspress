<?php

namespace App\Builder;

/**
 * Format du contenu d'une page (version 2) et réglages autorisés.
 *
 * Une page est une liste d'éléments. Un élément est soit un conteneur, qui
 * contient d'autres éléments (imbrication libre), soit un widget :
 *
 *   { "version": 2, "elements": [
 *       { "id": "…", "type": "container", "settings": {…}, "children": [ … ] },
 *       { "id": "…", "type": "heading",   "settings": {…} } ] }
 *
 * Tous les éléments partagent les réglages de style de STYLE (marges, fond,
 * bordure, ombre…). Les conteneurs ajoutent ceux de CONTAINER (disposition),
 * les widgets ceux de leur classe (app/Builder/Widgets).
 *
 * Règles : string:MAX | html | color | int:MIN:MAX | enum:a,b,c | url | bool |
 * slug:MAX | links:MAX | dim | spacing | shadow | font | classes:MAX | css
 *
 * Suffixes : « @r » = réglable par appareil (clé, clé_tablet, clé_mobile),
 * « @h » = réglable au survol (clé_hover), « @rh » = les deux.
 */
class Schema
{
    public const VERSION = 2;

    /** Appareils : suffixe des clés et largeur maximale (null = sans limite). */
    public const DEVICES = ['desktop' => '', 'tablet' => '_tablet', 'mobile' => '_mobile'];

    public const BREAKPOINTS = ['tablet' => 1024, 'mobile' => 767];

    /** Réglages de style communs à tous les éléments. */
    public const STYLE = [
        // Disposition dans le parent
        'margin' => 'spacing@r',
        'padding' => 'spacing@r',
        'width' => 'dim@r',
        'min_height' => 'dim@r',
        'align_self' => 'enum:start,center,end,stretch@r',
        'grow' => 'bool@r',
        'order' => 'int:-20:20@r',
        // Fond
        'background_type' => 'enum:classic,gradient',
        'background_color' => 'color@h',
        'background_image' => 'url',
        'background_position' => 'enum:center,top,bottom,left,right',
        'background_size' => 'enum:cover,contain,auto',
        'background_fixed' => 'bool',
        'background_parallax' => 'int:1:10',
        'gradient_type' => 'enum:linear,radial',
        'gradient_from' => 'color',
        'gradient_to' => 'color',
        'gradient_angle' => 'int:0:360',
        // Bordure et ombre
        'border_style' => 'enum:none,solid,dashed,dotted,double',
        'border_width' => 'spacing',
        'border_color' => 'color@h',
        'border_radius' => 'spacing@r',
        'box_shadow' => 'shadow@h',
        // Divers
        'text_color' => 'color@h',
        'opacity' => 'int:0:100@h',
        'z_index' => 'int:-100:9999',
        'overflow' => 'enum:visible,hidden',
        'transition' => 'int:0:3000',
        // Animations : effet au survol, apparition à l'écran (classes et images clés dans site.css)
        'transition_easing' => 'enum:ease,ease-in,ease-out,ease-in-out,linear,smooth,back',
        'hover_effect' => 'enum:grow,shrink,lift,sink,rotate-left,rotate-right,float,pulse,wobble,buzz',
        'entrance' => 'enum:fade,fade-up,fade-down,fade-left,fade-right,zoom-in,zoom-out,slide-up,slide-down,slide-left,slide-right,flip-up,flip-left,rotate-in,bounce-in,blur-in',
        'entrance_duration' => 'int:100:5000',
        'entrance_delay' => 'int:0:10000',
        'entrance_easing' => 'enum:ease,ease-in,ease-out,ease-in-out,linear,smooth,back',
        'entrance_repeat' => 'bool',
        // Défilement : élément collant (en-tête…), effets liés à la position dans l'écran
        'sticky' => 'enum:top,bottom',
        'sticky_offset' => 'int:0:400',
        'sticky_devices' => 'enum:all,desktop,desktop-tablet',
        'sticky_bg' => 'color',
        'sticky_shadow' => 'bool',
        'scroll_effect' => 'enum:fade,move-up,move-down,move-left,move-right,scale,blur,rotate',
        'scroll_intensity' => 'int:1:10',
        'hide_desktop' => 'bool',
        'hide_tablet' => 'bool',
        'hide_mobile' => 'bool',
        'css_id' => 'slug:60',
        'css_classes' => 'classes:200',
        // CSS personnalisé (« selector » = l'élément), limité à l'élément : App\Builder\CustomCss
        'custom_css' => 'css',
    ];

    /** Disposition d'un conteneur (flexbox). */
    public const CONTAINER = [
        'tag' => 'enum:div,section,header,footer,article,aside,nav,main',
        'content_width' => 'enum:boxed,full',
        'max_width' => 'int:200:2400@r',
        'direction' => 'enum:row,column,row-reverse,column-reverse@r',
        'wrap' => 'enum:nowrap,wrap@r',
        'justify' => 'enum:start,center,end,between,around,evenly@r',
        'align' => 'enum:start,center,end,stretch,baseline@r',
        'gap' => 'int:0:400@r',
        'overlay_color' => 'color',
        'overlay_opacity' => 'int:0:100',
        // Fond vidéo (MP4/WebM, sans son, en boucle) : l'image de fond sert d'affiche et le remplace sur mobile.
        'background_video' => 'url',
        'background_video_mobile' => 'bool',
        // Séparateurs de formes (App\Builder\Shapes), en haut et en bas.
        'shape_top' => 'enum:tilt,triangle,triangle-asym,curve,curve-asym,arrow,split,wave,waves,zigzag,mountains',
        'shape_top_color' => 'color',
        'shape_top_height' => 'int:10:600@r',
        'shape_top_width' => 'int:100:300',
        'shape_top_flip' => 'bool',
        'shape_top_front' => 'bool',
        'shape_bottom' => 'enum:tilt,triangle,triangle-asym,curve,curve-asym,arrow,split,wave,waves,zigzag,mountains',
        'shape_bottom_color' => 'color',
        'shape_bottom_height' => 'int:10:600@r',
        'shape_bottom_width' => 'int:100:300',
        'shape_bottom_flip' => 'bool',
        'shape_bottom_front' => 'bool',
    ];

    /** Typographie, à ajouter aux règles des widgets qui affichent du texte. */
    public const TYPOGRAPHY = [
        'font_family' => 'font',
        'font_weight' => 'enum:300,400,500,600,700,800,900',
        'font_style' => 'enum:normal,italic',
        'text_transform' => 'enum:none,uppercase,lowercase,capitalize',
        'line_height' => 'dim@r',
        'letter_spacing' => 'dim@r',
    ];

    public const MAX_DEPTH = 8;

    public const MAX_ELEMENTS = 3000;

    /** Valeurs par défaut d'un conteneur selon sa profondeur (0 = au premier niveau de la page). */
    public static function containerDefaults(int $depth): array
    {
        return ['content_width' => $depth === 0 ? 'boxed' : 'full', 'max_width' => 1140, 'direction' => 'column', 'gap' => 20];
    }

    /**
     * Développe les suffixes @r / @h : « font_size => int:8:160@r » devient
     * font_size, font_size_tablet et font_size_mobile avec la règle « int:8:160 ».
     *
     * @return array<string, string>
     */
    public static function expand(array $rules): array
    {
        $out = [];
        foreach ($rules as $key => $rule) {
            [$base, $flags] = self::split($rule);
            $out[$key] = $base;
            if (str_contains($flags, 'r')) {
                $out[$key.'_tablet'] = $base;
                $out[$key.'_mobile'] = $base;
            }
            if (str_contains($flags, 'h')) {
                $out[$key.'_hover'] = $base;
            }
        }

        return $out;
    }

    /** @return array{0: string, 1: string} règle sans suffixe, suffixes (« r », « h », « rh ») */
    public static function split(string $rule): array
    {
        return preg_match('/^(.*)@([rh]{1,2})$/', $rule, $m) ? [$m[1], $m[2]] : [$rule, ''];
    }

    /** Règles complètes d'un conteneur. */
    public static function containerRules(): array
    {
        return self::expand(self::CONTAINER + self::STYLE);
    }
}
