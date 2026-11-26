<?php

namespace App\Builder;

use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerAction;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Normalise et nettoie l'arbre JSON d'une page : supprime les types et
 * réglages inconnus, borne les valeurs, nettoie le HTML. Tout ce qui sort
 * d'ici peut être rendu sans risque. Le contenu au format 1 (sections →
 * colonnes → widgets) est d'abord converti au format 2 (voir Schema).
 */
class ContentSanitizer
{
    private ?HtmlSanitizer $html = null;

    public function __construct(private WidgetRegistry $widgets) {}

    private int $count = 0;

    /** @var array<string, true> identifiants déjà utilisés : chaque élément doit avoir le sien. */
    private array $seen = [];

    /** @var array<string, array<string, string>> règles développées, par type d'élément */
    private array $rules = [];

    public function sanitize(mixed $content): array
    {
        if (ContentMigrator::isV1($content)) {
            $content = ContentMigrator::toV2($content);
        }
        $elements = is_array($content) && is_array($content['elements'] ?? null) ? $content['elements'] : [];
        $this->count = 0;
        $this->seen = [];

        return ['version' => Schema::VERSION, 'elements' => $this->children($elements, 0)];
    }

    private function children(array $items, int $depth): array
    {
        $out = [];
        foreach (array_values($items) as $item) {
            if ($this->count >= Schema::MAX_ELEMENTS) {
                break;
            }
            if (is_array($item) && ($node = $this->node($item, $depth))) {
                $out[] = $node;
            }
        }

        return $out;
    }

    private function node(array $n, int $depth): ?array
    {
        $type = $n['type'] ?? null;
        if (! is_string($type)) {
            return null;
        }
        if ($type === 'container') {
            // Au-delà de la profondeur maximale, les conteneurs sont ignorés.
            if ($depth >= Schema::MAX_DEPTH) {
                return null;
            }
            $this->count++;
            $this->rules['container'] ??= Schema::containerRules();

            return [
                'id' => $this->id($n['id'] ?? null),
                'type' => 'container',
                'settings' => $this->settings((array) ($n['settings'] ?? []), $this->rules['container']),
                'children' => $this->children((array) ($n['children'] ?? []), $depth + 1),
            ];
        }
        if ($this->widgets->has($type)) {
            $this->count++;
            $this->rules[$type] ??= $this->widgets->rules($type);

            return [
                'id' => $this->id($n['id'] ?? null),
                'type' => $type,
                'settings' => $this->settings((array) ($n['settings'] ?? []), $this->rules[$type]),
            ];
        }
        if ($orphan = $this->orphan($n)) {
            $this->count++;

            return $orphan;
        }

        return null;
    }

    /**
     * Widget d'un plugin désactivé ou supprimé : on le conserve tel quel pour ne
     * pas perdre le contenu, mais il n'est jamais rendu tant que son type n'est
     * pas de nouveau déclaré (il sera alors validé normalement).
     */
    private function orphan(array $w): ?array
    {
        if (! preg_match('/^[a-z][a-z0-9-]{1,40}$/', $w['type'])) {
            return null;
        }
        $settings = is_array($w['settings'] ?? null) ? $w['settings'] : [];
        if (strlen((string) json_encode($settings)) > 20000) {
            return null;
        }

        return ['id' => $this->id($w['id'] ?? null), 'type' => $w['type'], 'settings' => $settings, 'orphan' => true];
    }

    private function id(mixed $id): string
    {
        if (! is_string($id) || ! preg_match('/^[a-zA-Z0-9_-]{4,40}$/', $id) || isset($this->seen[$id])) {
            do {
                $id = Str::lower(Str::random(10));
            } while (isset($this->seen[$id]));
        }
        $this->seen[$id] = true;

        return $id;
    }

    private function settings(array $values, array $rules): array
    {
        $out = [];
        foreach ($rules as $key => $rule) {
            if (! array_key_exists($key, $values) || $values[$key] === null || $values[$key] === '') {
                continue;
            }
            $clean = $this->value($values[$key], $rule);
            if ($clean !== null) {
                $out[$key] = $clean;
            }
        }

        return $out;
    }

    private function value(mixed $v, string $rule): mixed
    {
        [$type, $args] = array_pad(explode(':', $rule, 2), 2, '');

        return match ($type) {
            // Texte brut : échappé à l'affichage, donc pas besoin de retirer les balises.
            'string' => is_scalar($v) ? Str::limit(trim((string) $v), (int) $args, '') : null,
            'slug' => is_scalar($v) ? (Str::slug(Str::limit((string) $v, (int) $args, '')) ?: null) : null,
            'html' => is_string($v) ? $this->html()->sanitize(Str::limit($v, 50000, '')) : null,
            'color' => self::color($v),
            'int' => $this->int($v, $args),
            'enum' => in_array((string) $v, explode(',', $args), true) ? (string) $v : null,
            'url' => self::url($v),
            'bool' => filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'links' => $this->links($v, (int) ($args ?: 30)),
            'dim' => self::dim($v),
            'spacing' => $this->spacing($v),
            'shadow' => $this->shadow($v),
            'font' => in_array($v, ['global:heading', 'global:body', ...SettingController::FONTS], true) ? $v : null,
            'classes' => $this->classes($v, (int) ($args ?: 200)),
            'icon' => Icons::exists($v) ? $v : null,
            'datetime' => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $v) && strtotime($v) !== false ? $v : null,
            'list' => $this->listValue($v, $args),
            default => null,
        };
    }

    /**
     * Liste d'éléments : « list:MAX:champ=règle;champ=règle ». Chaque élément
     * est validé champ par champ ; un élément entièrement vide est retiré.
     * Exemple : « list:30:title=string:200;html=html » (questions d'une FAQ).
     */
    private function listValue(mixed $v, string $args): ?array
    {
        if (! is_array($v)) {
            return null;
        }
        $fields = self::listFields($args);
        $max = (int) explode(':', $args, 2)[0] ?: 30;
        $out = [];
        foreach (array_slice(array_values($v), 0, $max) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $clean = $this->settings($item, $fields);
            if ($clean !== []) {
                $out[] = $clean;
            }
        }

        return $out ?: null;
    }

    /** Champs d'une règle « list:MAX:champ=règle;… ». @return array<string, string> */
    public static function listFields(string $args): array
    {
        $fields = [];
        foreach (explode(';', explode(':', $args, 2)[1] ?? '') as $pair) {
            [$name, $rule] = array_pad(explode('=', $pair, 2), 2, '');
            if (preg_match('/^[a-z_]{1,40}$/', $name) && $rule !== '' && ! str_starts_with($rule, 'list')) {
                $fields[$name] = $rule;
            }
        }

        return $fields;
    }

    /**
     * Couleur : #rgb, #rrggbb, #rrggbbaa, rgb()/rgba(), « transparent », ou
     * couleur globale du site (« global:primary », « global:c-…»).
     */
    public static function color(mixed $v): ?string
    {
        if (! is_string($v)) {
            return null;
        }
        $v = strtolower(trim($v));
        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/', $v) || $v === 'transparent' || preg_match('/^global:[a-z0-9-]{1,40}$/', $v)) {
            return $v;
        }
        if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(0|1|0?\.\d+)\s*)?\)$/', $v, $m)) {
            $rgb = array_map(fn ($c) => min(255, (int) $c), [$m[1], $m[2], $m[3]]);

            return isset($m[4]) && $m[4] !== '' ? 'rgba('.implode(',', $rgb).','.(float) $m[4].')' : 'rgb('.implode(',', $rgb).')';
        }

        return null;
    }

    /** Dimension CSS : « 50% », « 320px », « 1.5em », « 1.4 » (sans unité), « auto ». */
    public static function dim(mixed $v): ?string
    {
        if (is_int($v) || is_float($v)) {
            $v = (string) $v;
        }
        if (! is_string($v)) {
            return null;
        }
        $v = strtolower(trim($v));
        if ($v === 'auto') {
            return $v;
        }
        if (! preg_match('/^(-?\d{1,5}(?:\.\d{1,3})?)(px|%|em|rem|vw|vh)?$/', $v, $m)) {
            return null;
        }

        return ((float) $m[1] == (int) $m[1] ? (string) (int) $m[1] : rtrim($m[1], '0')).($m[2] ?? '');
    }

    /** Espacement { t, r, b, l, u } : côtés en nombres entiers, unité px par défaut. */
    private function spacing(mixed $v): ?array
    {
        if (is_numeric($v)) {
            $v = ['t' => $v, 'r' => $v, 'b' => $v, 'l' => $v];
        }
        if (! is_array($v)) {
            return null;
        }
        $out = [];
        foreach (['t', 'r', 'b', 'l'] as $side) {
            if (isset($v[$side]) && is_numeric($v[$side])) {
                $out[$side] = max(-2000, min(2000, (int) round((float) $v[$side])));
            }
        }
        if ($out && in_array($v['u'] ?? 'px', ['%', 'em', 'rem', 'vw', 'vh'], true)) {
            $out['u'] = $v['u'];
        }

        return $out ?: null;
    }

    /** Ombre { x, y, blur, spread, color, inset }. */
    private function shadow(mixed $v): ?array
    {
        if (! is_array($v) || ! ($color = self::color($v['color'] ?? null))) {
            return null;
        }
        $n = fn ($k, $min, $max) => is_numeric($v[$k] ?? null) ? max($min, min($max, (int) $v[$k])) : 0;

        return array_filter([
            'x' => $n('x', -200, 200), 'y' => $n('y', -200, 200), 'blur' => $n('blur', 0, 300), 'spread' => $n('spread', -200, 200),
            'color' => $color, 'inset' => ! empty($v['inset']) ? true : null,
        ], fn ($x) => $x !== null);
    }

    /** Classes CSS supplémentaires, séparées par des espaces. */
    private function classes(mixed $v, int $max): ?string
    {
        if (! is_string($v)) {
            return null;
        }
        $names = array_filter(preg_split('/\s+/', trim($v)), fn ($c) => preg_match('/^[a-zA-Z_][a-zA-Z0-9_-]{0,60}$/', $c));

        return Str::limit(implode(' ', array_slice(array_unique($names), 0, 12)), $max, '') ?: null;
    }

    /** Liste de liens [{label, url}] : libellés en texte brut, adresses vérifiées. */
    private function links(mixed $v, int $max): ?array
    {
        if (! is_array($v)) {
            return null;
        }
        $out = [];
        foreach (array_slice(array_values($v), 0, $max) as $item) {
            $label = is_array($item) && is_scalar($item['label'] ?? null) ? Str::limit(trim(strip_tags((string) $item['label'])), 120, '') : '';
            $url = is_array($item) ? self::url($item['url'] ?? null) : null;
            if ($label !== '' && $url) {
                $out[] = ['label' => $label, 'url' => $url];
            }
        }

        return $out ?: null;
    }

    private function int(mixed $v, string $args): ?int
    {
        if (! is_numeric($v)) {
            return null;
        }
        [$min, $max] = array_map('intval', explode(':', $args));

        return max($min, min($max, (int) round((float) $v)));
    }

    /** N'accepte que http(s), mailto, tel, ancres et chemins relatifs au site. */
    public static function url(mixed $v): ?string
    {
        if (! is_string($v)) {
            return null;
        }
        $v = trim($v);
        if ($v === '' || strlen($v) > 2000 || preg_match('/[\x00-\x1F\x7F\s"<>\\\\]/', $v)) {
            return null;
        }
        if (preg_match('#^(https?://|mailto:|tel:)#i', $v) || preg_match('#^(/(?!/)|\#)#', $v)) {
            return $v;
        }

        return null;
    }

    /** Nettoie un fragment HTML avec la même liste blanche que les blocs texte. */
    public function cleanHtml(string $html): string
    {
        return $this->html()->sanitize(Str::limit($html, 50000, ''));
    }

    private function html(): HtmlSanitizer
    {
        if ($this->html) {
            return $this->html;
        }
        // Liste blanche partagée avec le builder (resources/js/builder/html.js).
        // Une balise non prévue (div collée, h1, figure…) est retirée mais son texte est
        // gardé ; les balises dangereuses partent avec leur contenu.
        $config = (new HtmlSanitizerConfig)
            ->defaultAction(HtmlSanitizerAction::Block)
            // 50 000 caractères (Str::limit) peuvent faire jusqu'à 200 000 octets.
            ->withMaxInputLength(200_000);
        foreach (['script', 'style', 'template', 'noscript', 'iframe', 'object', 'embed', 'svg', 'math', 'head', 'title', 'textarea', 'select', 'button', 'form', 'input', 'img', 'video', 'audio', 'picture', 'canvas'] as $tag) {
            $config = $config->dropElement($tag);
        }

        return $this->html = new HtmlSanitizer(
            $config
                ->allowElement('p')->allowElement('div')->allowElement('br')->allowElement('strong')->allowElement('b')
                ->allowElement('em')->allowElement('i')->allowElement('u')->allowElement('s')
                ->allowElement('ul')->allowElement('ol')->allowElement('li')
                ->allowElement('blockquote')->allowElement('h2')->allowElement('h3')->allowElement('h4')->allowElement('h5')->allowElement('h6')
                ->allowElement('a', ['href', 'title', 'target'])
                ->allowElement('span')
                // Documentation : code, tableaux, séparateurs.
                ->allowElement('code')->allowElement('pre')->allowElement('kbd')->allowElement('hr')
                ->allowElement('table')->allowElement('thead')->allowElement('tbody')
                ->allowElement('tr')->allowElement('th')->allowElement('td')
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
        );
    }
}
