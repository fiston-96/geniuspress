<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Icône + titre + texte (+ lien) : services, avantages, valeurs, coordonnées. */
class IconBoxWidget extends Widget
{
    public function type(): string { return 'icon-box'; }

    public function label(): string { return __('Boîte d’icône'); }

    public function rules(): array
    {
        return [
            'icon' => 'icon',
            'title' => 'string:200',
            'text' => 'html',
            'link' => 'url',
            'link_text' => 'string:80',
            'title_tag' => 'enum:h2,h3,h4,h5,p',
            'position' => 'enum:top,left',
            'align' => 'enum:left,center,right@r',
            'icon_size' => 'int:8:200@r',
            'icon_color' => 'color@h',
            'icon_bg_color' => 'color@h',
            'icon_shape' => 'enum:none,circle,square,rounded',
            'icon_box' => 'int:0:200',
            'title_color' => 'color',
            'title_size' => 'int:10:80@r',
            'text_color' => 'color',
        ];
    }

    public function translatable(): array { return ['title', 'text', 'link_text']; }

    public function aiHint(): ?string
    {
        return 'Card with an icon ("icon": "lucide:<name>"), a short title and 1–2 sentences ("text" as simple HTML). Best for services, programs, values or key benefits, 3–4 side by side.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        IconWidget::iconCss($ctx, $id, $s, '', 'icon_', 40);
        $top = ($s['position'] ?? 'top') === 'top';
        $ctx->rule($id, ['text-align' => $s['align'] ?? ($top ? 'center' : null)]);
        $ctx->responsive($id, array_diff_key($s, ['align' => 1]), 'align', fn ($v) => ['text-align' => $v]);
        // Icône au-dessus du texte : elle suit l'alignement sans s'étirer sur toute la largeur.
        if ($top) {
            $self = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];
            $ctx->rule($id, ['align-self' => $self[$s['align'] ?? 'center']], false, ' .gp-ibox__icon');
            foreach (['tablet', 'mobile'] as $device) {
                if (isset($s['align_'.$device])) {
                    $ctx->rule($id, ['align-self' => $self[$s['align_'.$device]]], $device, ' .gp-ibox__icon');
                }
            }
        }
        $ctx->rule($id, ['color' => $s['title_color'] ?? null], false, ' .gp-ibox__title');
        $ctx->responsive($id, $s, 'title_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-ibox__title');
        $ctx->rule($id, ['color' => $s['text_color'] ?? null], false, ' .gp-ibox__text');

        $tag = $s['title_tag'] ?? 'h3';
        $html = '<div class="gp-ibox gp-ibox--'.e($s['position'] ?? 'top').'">';
        if (isset($s['icon'])) {
            $html .= '<span class="gp-ibox__icon gp-icon-wrap gp-icon--'.e($s['icon_shape'] ?? 'none').'">'.Icons::svg($s['icon']).'</span>';
        }
        $html .= '<div class="gp-ibox__body">';
        if (isset($s['title'])) {
            $html .= "<{$tag} class=\"gp-ibox__title\">".e($s['title'])."</{$tag}>";
        }
        if (isset($s['text'])) {
            $html .= '<div class="gp-ibox__text">'.$s['text'].'</div>';
        }
        if (isset($s['link'])) {
            $html .= '<a class="gp-ibox__link" href="'.e($s['link']).'">'.e($s['link_text'] ?? __('En savoir plus')).' <span aria-hidden="true">→</span></a>';
        }

        return $html.'</div></div>';
    }
}
