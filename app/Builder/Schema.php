<?php

namespace App\Builder;

/**
 * Réglages autorisés pour les sections, les colonnes et communs à tous les
 * widgets. Les réglages propres à chaque widget sont définis dans sa classe
 * (app/Builder/Widgets) et déclarés dans le WidgetRegistry. Sert à valider tout contenu qui
 * entre dans la base : éditeur, assistant IA, import.
 *
 * Règles : string:MAX | html | color | int:MIN:MAX | enum:a,b,c | url | bool | links:MAX
 */
class Schema
{
    public const COMMON_WIDGET = [
        'margin_bottom' => 'int:0:400',
        'hide_desktop' => 'bool',
        'hide_mobile' => 'bool',
    ];

    public const SECTION = [
        'background_color' => 'color',
        'background_image' => 'url',
        'overlay_color' => 'color',
        'overlay_opacity' => 'int:0:100',
        'text_color' => 'color',
        'padding_top' => 'int:0:600',
        'padding_bottom' => 'int:0:600',
        'padding_top_mobile' => 'int:0:600',
        'padding_bottom_mobile' => 'int:0:600',
        'content_width' => 'enum:boxed,full',
        'max_width' => 'int:320:2400',
        'gap' => 'int:0:200',
        'vertical_align' => 'enum:start,center,end,stretch',
        'min_height' => 'int:0:2000',
        'anchor' => 'slug:60',
    ];

    public const COLUMN = [
        'width' => 'int:5:100',
        'padding' => 'int:0:200',
        'background_color' => 'color',
        'border_radius' => 'int:0:200',
        'vertical_align' => 'enum:start,center,end',
    ];

    public const MAX_SECTIONS = 200;
    public const MAX_COLUMNS = 6;
    public const MAX_WIDGETS_PER_COLUMN = 100;
}
