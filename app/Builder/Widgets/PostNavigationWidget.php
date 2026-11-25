<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Site\PostNavigation;

/** Liens vers l'article précédent et l'article suivant. */
class PostNavigationWidget extends ThemeWidget
{
    public function type(): string { return 'post-nav'; }

    public function label(): string { return __('Articles précédent et suivant'); }

    public function rules(): array
    {
        return ['show_titles' => 'bool', 'color' => 'color@h'];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $post = $this->post($ctx);
        if (! $post) {
            return '';
        }
        $nav = PostNavigation::for($post);
        if (! $nav['previous'] && ! $nav['next']) {
            return '';
        }
        $locale = $this->locale($ctx);
        $ctx->rule($id, ['color' => $s['color'] ?? null], false, ' .gp-postnav a');
        $ctx->rule($id, ['color' => $s['color_hover'] ?? null], false, ' .gp-postnav a:hover');
        $link = function ($p, string $label, string $rel) use ($s) {
            if (! $p) {
                return '<span></span>';
            }

            return '<a href="'.e($p->url()).'" rel="'.$rel.'" class="gp-postnav__'.$rel.'"><small>'.e($label).'</small>'.(($s['show_titles'] ?? true) ? '<span>'.e($p->title).'</span>' : '').'</a>';
        };

        return '<nav class="gp-postnav" aria-label="'.e(__('Articles voisins', [], $locale)).'">'
            .$link($nav['previous'], '← '.__('Article précédent', [], $locale), 'prev')
            .$link($nav['next'], __('Article suivant', [], $locale).' →', 'next')
            .'</nav>';
    }
}
