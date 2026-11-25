<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Site\Permalinks;
use App\Site\SiteContext;

/** Fil d'Ariane : Accueil › Blog › Catégorie › Article. */
class BreadcrumbsWidget extends ThemeWidget
{
    public function type(): string { return 'breadcrumbs'; }

    public function label(): string { return __('Fil d’Ariane'); }

    public function rules(): array
    {
        return [
            'separator' => 'string:4',
            'align' => 'enum:left,center,right@r',
            'color' => 'color',
            'link_color' => 'color',
            'font_size' => 'int:8:40@r',
        ];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $site = $ctx->site;
        if (! $site || $site->isHome()) {
            return '';
        }
        $locale = $site->locale;
        $crumbs = [[__('Accueil', [], $locale), Permalinks::home($locale)]];
        if ($site->kind === SiteContext::POST || $site->term) {
            $crumbs[] = [Permalinks::blogTitle($locale), Permalinks::blog($locale)];
        }
        if ($site->kind === SiteContext::POST && ($category = $site->post->categories->first())) {
            $crumbs[] = [$category->name, $category->url()];
        }
        $crumbs[] = [$site->title(), null];

        $ctx->rule($id, ['color' => $s['color'] ?? null], false, ' .gp-crumbs');
        $ctx->rule($id, ['color' => $s['link_color'] ?? null], false, ' .gp-crumbs a');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$v]], ' .gp-crumbs ol');
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-crumbs');
        $sep = '<li class="gp-crumbs__sep" aria-hidden="true">'.e($s['separator'] ?? '›').'</li>';

        $items = array_map(fn ($c) => '<li>'.($c[1] ? '<a href="'.e($c[1]).'">'.e($c[0]).'</a>' : '<span aria-current="page">'.e($c[0]).'</span>').'</li>', $crumbs);

        return '<nav class="gp-crumbs" aria-label="'.e(__('Fil d’Ariane', [], $locale)).'"><ol>'.implode($sep, $items).'</ol></nav>';
    }
}
