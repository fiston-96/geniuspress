<?php

namespace App\Builder;

/**
 * Définition unique des éléments du builder (sections, colonnes, widgets)
 * et des réglages autorisés pour chacun. Sert à valider tout contenu qui
 * entre dans la base : éditeur, assistant IA, import.
 *
 * Règles : string:MAX | html | color | int:MIN:MAX | enum:a,b,c | url | bool
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

    public const WIDGETS = [
        'heading' => [
            'text' => 'string:500',
            'tag' => 'enum:h1,h2,h3,h4,h5,h6,p',
            'align' => 'enum:left,center,right',
            'color' => 'color',
            'font_size' => 'int:8:160',
            'font_size_mobile' => 'int:8:160',
            'font_weight' => 'enum:300,400,500,600,700,800,900',
        ],
        'text' => [
            'html' => 'html',
            'align' => 'enum:left,center,right,justify',
            'color' => 'color',
            'font_size' => 'int:8:80',
        ],
        'image' => [
            'url' => 'url',
            'media_id' => 'int:0:2147483647',
            'alt' => 'string:300',
            'width' => 'int:5:100',
            'align' => 'enum:left,center,right',
            'border_radius' => 'int:0:500',
            'link' => 'url',
        ],
        'button' => [
            'text' => 'string:120',
            'url' => 'url',
            'variant' => 'enum:filled,outline',
            'size' => 'enum:sm,md,lg',
            'bg_color' => 'color',
            'text_color' => 'color',
            'align' => 'enum:left,center,right,full',
            'border_radius' => 'int:0:100',
            'new_tab' => 'bool',
        ],
        'spacer' => [
            'height' => 'int:0:600',
            'height_mobile' => 'int:0:600',
        ],
        'divider' => [
            'color' => 'color',
            'thickness' => 'int:1:20',
            'width' => 'int:5:100',
            'style' => 'enum:solid,dashed,dotted',
            'align' => 'enum:left,center,right',
        ],
        'video' => [
            'url' => 'url',
            'aspect' => 'enum:16:9,4:3,1:1,9:16',
        ],
    ];

    public const MAX_SECTIONS = 200;
    public const MAX_COLUMNS = 6;
    public const MAX_WIDGETS_PER_COLUMN = 100;
}
