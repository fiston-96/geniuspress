<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Renderer;
use App\Models\Layout;

/**
 * Méga-menu : chaque entrée est un lien, ou ouvre un panneau. Le panneau est un gabarit
 * « Méga-menu » conçu dans le builder (colonnes, images, boutons…), ou une simple liste
 * de liens (« Libellé | /adresse », un par ligne). Au survol ou au clic sur ordinateur,
 * en accordéon derrière le bouton du menu sur mobile. Utilisable sans JavaScript
 * (survol et focus) ; resources/js/site.js gère le clic, le clavier et la largeur.
 */
class MegaMenuWidget extends ThemeWidget
{
    /** Panneaux en cours de rendu : un panneau qui contient son propre méga-menu n'est pas rendu deux fois. */
    private static array $rendering = [];

    public function type(): string { return 'mega-menu'; }

    public function label(): string { return __('Méga-menu'); }

    public function rules(): array
    {
        return [
            'items' => 'list:12:label=string:60;url=url;mega=int:0:99999999;links=string:3000',
            'open_on' => 'enum:hover,click',
            'panel_width' => 'enum:auto,content,full',
            'collapse' => 'enum:mobile,tablet,never',
            'align' => 'enum:left,center,right',
            'color' => 'color@h',
            'font_size' => 'int:8:40',
            'gap' => 'int:0:80',
            'panel_bg' => 'color',
        ];
    }

    public function translatable(): array
    {
        return ['items.*.label', 'items.*.links'];
    }

    /** Liens d'un panneau simple : « Libellé | /adresse » par ligne. @return list<array{0: string, 1: string}> */
    public static function links(?string $text): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
            [$label, $url] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
            $url = \App\Builder\ContentSanitizer::url($url);
            if ($label !== '' && $url) {
                $out[] = [$label, $url];
            }
        }

        return array_slice($out, 0, 40);
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $items = (array) ($s['items'] ?? []);
        if (! $items) {
            return '';
        }
        $locale = $this->locale($ctx);
        $ctx->rule($id, ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$s['align'] ?? 'right']], false, ' .gp-mega__list');
        $ctx->rule($id, ['gap' => isset($s['gap']) ? $s['gap'].'px' : null], false, ' .gp-mega__list');
        $ctx->rule($id, ['color' => $s['color'] ?? null, 'font-size' => isset($s['font_size']) ? $s['font_size'].'px' : null], false, ' .gp-mega__top');
        $ctx->rule($id, ['color' => $s['color_hover'] ?? null], false, ' .gp-mega__item:hover > .gp-mega__top');
        $ctx->rule($id, ['color' => $s['color'] ?? null], false, ' .gp-mega__burger');
        $ctx->rule($id, ['background-color' => $s['panel_bg'] ?? null], false, ' .gp-mega__panel');

        // Panneaux conçus dans le builder, chargés en une requête.
        $ids = array_values(array_filter(array_map(fn ($i) => (int) ($i['mega'] ?? 0), $items)));
        $layouts = $ids ? Layout::query()->where('type', 'mega')->whereIn('id', $ids)->get()->keyBy('id') : collect();

        $current = rtrim('/'.trim(request()->path(), '/'), '/');
        $html = '<nav class="gp-mega gp-mega--collapse-'.e($s['collapse'] ?? 'mobile').' gp-mega--'.e($s['open_on'] ?? 'hover').' gp-mega--w-'.e($s['panel_width'] ?? 'content').'" aria-label="'.e(__('Menu principal', [], $locale)).'" data-gp-mega>'
            .'<input type="checkbox" id="mm-'.e($id).'" class="gp-mega__toggle">'
            .'<label for="mm-'.e($id).'" class="gp-mega__burger" aria-label="'.e(__('Ouvrir le menu', [], $locale)).'"><span></span></label>'
            .'<ul class="gp-mega__list">';
        foreach ($items as $n => $item) {
            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $url = $item['url'] ?? null;
            $panel = $this->panel($item, $layouts, $ctx);
            $here = $url && ! str_contains($url, '://') && ! str_starts_with($url, '#') && rtrim(parse_url($url, PHP_URL_PATH) ?: '/', '/') === $current;
            $top = match (true) {
                (bool) $url => '<a class="gp-mega__top" href="'.e($url).'"'.($here ? ' aria-current="page"' : '').'>'.e($label).'</a>',
                $panel !== '' => '<button type="button" class="gp-mega__top" data-gp-mega-open aria-expanded="false" aria-controls="gp-'.e($id).'-p'.$n.'">'.e($label).'</button>',
                default => '<span class="gp-mega__top">'.e($label).'</span>',
            };
            if ($panel === '') {
                $html .= '<li class="gp-mega__item">'.$top.'</li>';

                continue;
            }
            $html .= '<li class="gp-mega__item has-panel">'.$top
                .($url ? '<button type="button" class="gp-mega__caret" data-gp-mega-open aria-expanded="false" aria-controls="gp-'.e($id).'-p'.$n.'" aria-label="'.e(__('Afficher : :label', ['label' => $label], $locale)).'">' : '<span class="gp-mega__caret" aria-hidden="true">')
                .'<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>'
                .($url ? '</button>' : '</span>')
                .'<div class="gp-mega__panel" id="gp-'.e($id).'-p'.$n.'"><div class="gp-mega__inner">'.$panel.'</div></div></li>';
        }

        return $html.'</ul></nav>';
    }

    /** Contenu du panneau d'une entrée : gabarit « Méga-menu », sinon liste de liens, sinon rien. */
    private function panel(array $item, $layouts, RenderContext $ctx): string
    {
        $layout = $layouts->get((int) ($item['mega'] ?? 0));
        if ($layout && ! isset(self::$rendering[$layout->id])) {
            self::$rendering[$layout->id] = true;
            try {
                $rendered = app(Renderer::class)->render($layout->content, null, $ctx->site, 'layout:'.$layout->id);
            } finally {
                unset(self::$rendering[$layout->id]);
            }
            $ctx->embed($rendered);

            return $rendered['html'];
        }
        $links = self::links($item['links'] ?? null);
        if (! $links) {
            return '';
        }

        return '<ul class="gp-mega__links">'.implode('', array_map(fn ($l) => '<li><a href="'.e($l[1]).'">'.e($l[0]).'</a></li>', $links)).'</ul>';
    }
}
