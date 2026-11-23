<?php

namespace App\Builder;

use App\Models\Media;
use App\Plugins\Hooks;
use Throwable;

/**
 * Transforme l'arbre JSON d'une page en HTML + CSS.
 * Les sections et colonnes sont rendues ici ; chaque widget est rendu par
 * sa classe, trouvée dans le WidgetRegistry (cœur ou plugin).
 */
class Renderer
{
    public function __construct(private ContentSanitizer $sanitizer, private WidgetRegistry $widgets) {}

    /** @return array{html: string, css: string} */
    public function render(mixed $content): array
    {
        $tree = $this->sanitizer->sanitize($content);
        $tree = Hooks::filter('page.content', $tree);
        $ctx = new RenderContext($this->preloadMedia($tree));

        $html = '';
        foreach ($tree['sections'] as $section) {
            $html .= $this->section($section, $ctx);
        }

        return ['html' => $html, 'css' => $ctx->stylesheet()];
    }

    /** Charge en une requête tous les médias référencés par un réglage « media_id ». */
    private function preloadMedia(array $tree): array
    {
        $ids = [];
        foreach ($tree['sections'] as $s) {
            foreach ($s['columns'] as $c) {
                foreach ($c['widgets'] as $w) {
                    if (! empty($w['settings']['media_id'])) {
                        $ids[] = $w['settings']['media_id'];
                    }
                }
            }
        }

        return $ids ? Media::query()->whereIn('id', array_unique($ids))->get()->keyBy('id')->all() : [];
    }

    private function section(array $s, RenderContext $ctx): string
    {
        $o = $s['settings'];
        $id = $s['id'];
        $px = RenderContext::px(...);

        $bg = isset($o['background_image']) ? 'url("'.$o['background_image'].'") center/cover no-repeat' : null;
        $ctx->rule($id, [
            'background-color' => $o['background_color'] ?? null,
            'background' => $bg ? $bg.(isset($o['background_color']) ? ' '.$o['background_color'] : '') : null,
            'color' => $o['text_color'] ?? null,
            'padding-top' => $px($o['padding_top'] ?? 64),
            'padding-bottom' => $px($o['padding_bottom'] ?? 64),
            'min-height' => $px($o['min_height'] ?? null),
        ]);
        $ctx->rule($id, [
            'padding-top' => $px($o['padding_top_mobile'] ?? null),
            'padding-bottom' => $px($o['padding_bottom_mobile'] ?? null),
        ], true);

        $full = ($o['content_width'] ?? 'boxed') === 'full';
        $ctx->rule($id, [
            'max-width' => $full ? 'none' : (($o['max_width'] ?? 1140).'px'),
            'gap' => $px($o['gap'] ?? 24),
            'align-items' => match ($o['vertical_align'] ?? 'stretch') {
                'start' => 'flex-start', 'center' => 'center', 'end' => 'flex-end', default => 'stretch',
            },
        ], false, ' > .gp-row');

        $overlay = '';
        if (isset($o['overlay_color'])) {
            $ctx->rule($id, [
                'background' => $o['overlay_color'],
                'opacity' => (($o['overlay_opacity'] ?? 50) / 100),
            ], false, ' > .gp-overlay');
            $overlay = '<div class="gp-overlay"></div>';
        }

        $cols = '';
        foreach ($s['columns'] as $c) {
            $cols .= $this->column($c, count($s['columns']), $o['gap'] ?? 24, $ctx);
        }

        // Une ancre (#contact) remplace l'identifiant technique de la section.
        $htmlId = 'gp-'.$id;
        if (isset($o['anchor'])) {
            $htmlId = e($o['anchor']);
            $ctx->renameId($id, $htmlId);
        }

        return "<section id=\"{$htmlId}\" class=\"gp-section\">{$overlay}<div class=\"gp-row\">{$cols}</div></section>\n";
    }

    private function column(array $c, int $count, int $gap, RenderContext $ctx): string
    {
        $o = $c['settings'];
        $id = $c['id'];
        $width = $o['width'] ?? round(100 / max(1, $count), 4);
        $share = round($gap * ($count - 1) / max(1, $count), 2);

        $ctx->rule($id, [
            'flex' => "0 1 calc({$width}% - {$share}px)",
            'padding' => RenderContext::px($o['padding'] ?? null),
            'background-color' => $o['background_color'] ?? null,
            'border-radius' => RenderContext::px($o['border_radius'] ?? null),
            'justify-content' => match ($o['vertical_align'] ?? null) {
                'center' => 'center', 'end' => 'flex-end', 'start' => 'flex-start', default => null,
            },
        ]);

        $widgets = '';
        foreach ($c['widgets'] as $w) {
            $widgets .= $this->widget($w, $ctx);
        }

        return "<div id=\"gp-{$id}\" class=\"gp-column\">{$widgets}</div>";
    }

    private function widget(array $w, RenderContext $ctx): string
    {
        $type = $this->widgets->get($w['type']);
        if (! $type) {
            return ''; // Widget d'un plugin désactivé : on ne l'affiche pas, sans casser la page.
        }

        $o = $w['settings'];
        $id = $w['id'];
        $classes = 'gp-widget gp-'.$w['type'];
        if (! empty($o['hide_desktop'])) {
            $classes .= ' gp-hide-desktop';
        }
        if (! empty($o['hide_mobile'])) {
            $classes .= ' gp-hide-mobile';
        }
        $ctx->rule($id, ['margin-bottom' => RenderContext::px($o['margin_bottom'] ?? null)]);

        try {
            $inner = $type->render($o, $id, $ctx);
        } catch (Throwable $e) {
            // Un widget de plugin défaillant ne doit pas faire tomber toute la page.
            report($e);
            $inner = '';
        }

        return "<div id=\"gp-{$id}\" class=\"{$classes}\">{$inner}</div>";
    }
}
