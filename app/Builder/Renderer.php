<?php

namespace App\Builder;

use App\Models\Media;
use App\Plugins\Hooks;
use App\Site\SiteContext;
use Throwable;

/**
 * Transforme l'arbre JSON d'une page en HTML + CSS.
 * Les conteneurs et les réglages communs sont rendus ici ; chaque widget est
 * rendu par sa classe, trouvée dans le WidgetRegistry (cœur ou plugin).
 */
class Renderer
{
    public function __construct(private ContentSanitizer $sanitizer, private WidgetRegistry $widgets) {}

    /** @return array{html: string, css: string, fonts: string[]} */
    public function render(mixed $content, ?int $pageId = null, ?SiteContext $site = null): array
    {
        $tree = $this->sanitizer->sanitize($content);
        $tree = Hooks::filter('page.content', $tree);
        $ctx = new RenderContext($this->preloadMedia($tree['elements'] ?? []), $pageId, $site);

        $html = $this->children($tree['elements'] ?? [], null, 0, $ctx);

        return ['html' => $html, 'css' => $ctx->stylesheet(), 'fonts' => $ctx->fonts()];
    }

    /** Parcourt tous les éléments de l'arbre, conteneurs compris. */
    public static function walk(array $elements, callable $fn, ?array $parent = null): void
    {
        foreach ($elements as $node) {
            $fn($node, $parent);
            if (($node['type'] ?? null) === 'container') {
                self::walk($node['children'] ?? [], $fn, $node);
            }
        }
    }

    /** Charge en une requête tous les médias référencés par un réglage « media_id ». */
    private function preloadMedia(array $elements): array
    {
        $ids = [];
        // « media_id » peut aussi se trouver dans une liste (images d'une galerie, photos d'une équipe…).
        $collect = function (array $values) use (&$collect, &$ids) {
            foreach ($values as $key => $value) {
                if ($key === 'media_id' && is_int($value) && $value > 0) {
                    $ids[] = $value;
                } elseif (is_array($value)) {
                    $collect($value);
                }
            }
        };
        self::walk($elements, fn ($node) => $collect($node['settings'] ?? []));

        return $ids ? Media::query()->whereIn('id', array_unique($ids))->get()->keyBy('id')->all() : [];
    }

    private function children(array $nodes, ?array $parent, int $depth, RenderContext $ctx): string
    {
        $html = '';
        foreach ($nodes as $node) {
            $html .= $node['type'] === 'container'
                ? $this->container($node, $parent, $depth, $ctx)
                : $this->widget($node, $parent, $ctx);
        }

        return $html;
    }

    private function container(array $node, ?array $parent, int $depth, RenderContext $ctx): string
    {
        $o = $node['settings'];
        ElementStyle::apply($node, $parent, array_keys(Schema::STYLE), $ctx);
        ElementStyle::container($node, $depth, $ctx);

        $inner = $this->children($node['children'], $node, $depth + 1, $ctx);
        $overlay = isset($o['overlay_color']) ? '<div class="gp-overlay"></div>' : '';
        $tag = $o['tag'] ?? 'div';

        return "<{$tag} id=\"{$this->htmlId($node, $ctx)}\" class=\"{$this->classes($node, 'gp-el gp-con')}\">{$overlay}<div class=\"gp-con-inner\">{$inner}</div></{$tag}>\n";
    }

    private function widget(array $node, ?array $parent, RenderContext $ctx): string
    {
        $type = $this->widgets->get($node['type']);
        if (! $type) {
            return ''; // Widget d'un plugin désactivé : on ne l'affiche pas, sans casser la page.
        }

        ElementStyle::apply($node, $parent, $this->widgets->styleKeys($node['type']), $ctx);

        try {
            $inner = $type->render($node['settings'], $node['id'], $ctx);
        } catch (Throwable $e) {
            // Un widget de plugin défaillant ne doit pas faire tomber toute la page.
            report($e);
            $inner = '';
        }

        return "<div id=\"{$this->htmlId($node, $ctx)}\" class=\"{$this->classes($node, 'gp-el gp-widget gp-'.$node['type'])}\">{$inner}</div>";
    }

    /** Une ancre (#contact) remplace l'identifiant technique de l'élément. */
    private function htmlId(array $node, RenderContext $ctx): string
    {
        if (isset($node['settings']['css_id'])) {
            $ctx->renameId($node['id'], $node['settings']['css_id']);

            return e($node['settings']['css_id']);
        }

        return 'gp-'.$node['id'];
    }

    private function classes(array $node, string $base): string
    {
        $o = $node['settings'];
        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            if (! empty($o['hide_'.$device])) {
                $base .= ' gp-hide-'.$device;
            }
        }
        // Animations (site.css) : apparition à l'écran, déclenchée par le script du site, et effet au survol.
        if (isset($o['entrance'])) {
            $base .= ' gp-anim gp-anim--'.$o['entrance'].(! empty($o['entrance_repeat']) ? ' gp-anim--repeat' : '');
        }
        if (isset($o['hover_effect'])) {
            $base .= ' gp-hv gp-hv--'.$o['hover_effect'];
        }
        // Défilement : élément collant, effet lié à la position, parallaxe de l'image de fond.
        if (isset($o['sticky'])) {
            $base .= ' gp-sticky gp-sticky--'.$o['sticky'];
        }
        if (isset($o['scroll_effect'])) {
            $base .= ' gp-sc gp-sc--'.$o['scroll_effect'];
        }
        if (isset($o['background_parallax'], $o['background_image']) && ($o['background_type'] ?? 'classic') !== 'gradient') {
            $base .= ' gp-parallax';
        }
        if (isset($o['css_classes'])) {
            $base .= ' '.$o['css_classes'];
        }

        return e($base);
    }
}
