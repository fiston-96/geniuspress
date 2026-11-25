<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Site\Navigation;

/**
 * Menu du site (réglé dans Apparence, pour chaque langue), avec le choix de la
 * langue et le bouton d'action. Sur mobile, il se replie derrière un bouton.
 */
class NavMenuWidget extends ThemeWidget
{
    public function type(): string { return 'nav-menu'; }

    public function label(): string { return __('Menu'); }

    public function rules(): array
    {
        return [
            'show_languages' => 'bool',
            'show_cta' => 'bool',
            'collapse' => 'enum:mobile,tablet,never',
            'align' => 'enum:left,center,right',
            'color' => 'color@h',
            'font_size' => 'int:8:40',
            'gap' => 'int:0:80',
            'cta_color' => 'color',
        ];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $site = $ctx->site;
        if (! $site) {
            return '';
        }
        $nav = Navigation::forContext($site);
        $locale = $site->locale;
        $languages = ($s['show_languages'] ?? true) ? $nav['languages'] : [];
        $cta = ($s['show_cta'] ?? true) && $nav['cta_label'] && $nav['cta_url'];
        if (! $nav['menu'] && ! $languages && ! $cta) {
            return '';
        }

        $ctx->rule($id, ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$s['align'] ?? 'right']], false, ' .gp-menu__panel');
        $ctx->rule($id, ['gap' => isset($s['gap']) ? $s['gap'].'px' : null], false, ' .gp-menu__list');
        $ctx->rule($id, ['color' => $s['color'] ?? null, 'font-size' => isset($s['font_size']) ? $s['font_size'].'px' : null], false, ' .gp-menu__list a');
        $ctx->rule($id, ['color' => $s['color_hover'] ?? null], false, ' .gp-menu__list a:hover');
        $ctx->rule($id, ['color' => $s['color'] ?? null], false, ' .gp-menu__burger');
        $ctx->rule($id, ['background' => $s['cta_color'] ?? null, 'border-color' => $s['cta_color'] ?? null], false, ' .gp-menu__cta');

        $current = rtrim('/'.trim(request()->path(), '/'), '/');
        $html = '<nav class="gp-menu gp-menu--collapse-'.e($s['collapse'] ?? 'mobile').'" aria-label="'.e(__('Menu principal', [], $locale)).'">'
            .'<input type="checkbox" id="m-'.e($id).'" class="gp-menu__toggle">'
            .'<label for="m-'.e($id).'" class="gp-menu__burger" aria-label="'.e(__('Ouvrir le menu', [], $locale)).'"><span></span></label>'
            .'<div class="gp-menu__panel">';
        if ($nav['menu']) {
            $html .= '<ul class="gp-menu__list">';
            foreach ($nav['menu'] as $link) {
                $path = rtrim(parse_url($link['url'], PHP_URL_PATH) ?: '/', '/');
                $here = ! str_contains($link['url'], '://') && ! str_starts_with($link['url'], '#') && $path === $current;
                $html .= '<li><a href="'.e($link['url']).'"'.($here ? ' aria-current="page"' : '').'>'.e($link['label']).'</a></li>';
            }
            $html .= '</ul>';
        }
        if ($languages) {
            $html .= '<ul class="gp-menu__langs" aria-label="'.e(__('Langue', [], $locale)).'">';
            foreach ($languages as $lang) {
                $html .= '<li><a href="'.e($lang['url']).'" hreflang="'.e($lang['code']).'" lang="'.e($lang['code']).'" title="'.e($lang['name']).'"'.($lang['current'] ? ' aria-current="true"' : '').'>'.e(strtoupper($lang['code'])).'</a></li>';
            }
            $html .= '</ul>';
        }
        if ($cta) {
            $html .= '<a class="gp-btn gp-btn-sm gp-menu__cta" href="'.e($nav['cta_url']).'">'.e($nav['cta_label']).'</a>';
        }

        return $html.'</div></nav>';
    }
}
