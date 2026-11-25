<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Models\Setting;
use App\Site\Permalinks;

/** Logo et nom du site, avec un lien vers l'accueil (en-tête, pied de page). */
class SiteLogoWidget extends ThemeWidget
{
    public function type(): string { return 'site-logo'; }

    public function label(): string { return __('Logo du site'); }

    public function rules(): array
    {
        return [
            'show_logo' => 'bool',
            'show_name' => 'bool',
            'height' => 'int:12:200@r',
            'align' => 'enum:left,center,right@r',
            'color' => 'color',
            'font_size' => 'int:8:80@r',
        ];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $logo = Setting::read('logo_url');
        $name = (string) Setting::read('site_name', 'GeniusPress');
        $showLogo = ($s['show_logo'] ?? true) && $logo;
        $showName = ($s['show_name'] ?? true) || ! $showLogo;

        $ctx->responsive($id, $s, 'height', fn ($v) => ['height' => $v.'px'], ' img');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$v]], ' .gp-logo');
        $ctx->rule($id, ['color' => $s['color'] ?? null], false, ' .gp-logo');
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-logo__name');

        return '<a class="gp-logo" href="'.e(Permalinks::home($this->locale($ctx))).'">'
            .($showLogo ? '<img src="'.e($logo).'" alt="'.($showName ? '' : e($name)).'">' : '')
            .($showName ? '<span class="gp-logo__name">'.e($name).'</span>' : '')
            .'</a>';
    }
}
