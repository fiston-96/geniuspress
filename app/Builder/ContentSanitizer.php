<?php

namespace App\Builder;

use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Normalise et nettoie l'arbre JSON d'une page : supprime les types et
 * réglages inconnus, borne les valeurs, nettoie le HTML. Tout ce qui sort
 * d'ici peut être rendu sans risque.
 */
class ContentSanitizer
{
    private ?HtmlSanitizer $html = null;

    public function __construct(private WidgetRegistry $widgets) {}

    public function sanitize(mixed $content): array
    {
        $sections = is_array($content) && is_array($content['sections'] ?? null) ? $content['sections'] : [];

        $out = [];
        foreach (array_slice(array_values($sections), 0, Schema::MAX_SECTIONS) as $section) {
            if (is_array($section)) {
                $out[] = $this->section($section);
            }
        }

        return ['version' => 1, 'sections' => $out];
    }

    private function section(array $s): array
    {
        $columns = [];
        foreach (array_slice(array_values((array) ($s['columns'] ?? [])), 0, Schema::MAX_COLUMNS) as $col) {
            if (is_array($col)) {
                $columns[] = $this->column($col);
            }
        }
        if ($columns === []) {
            $columns[] = $this->column([]);
        }

        return [
            'id' => $this->id($s['id'] ?? null),
            'type' => 'section',
            'settings' => $this->settings((array) ($s['settings'] ?? []), Schema::SECTION),
            'columns' => $columns,
        ];
    }

    private function column(array $c): array
    {
        $widgets = [];
        foreach (array_slice(array_values((array) ($c['widgets'] ?? [])), 0, Schema::MAX_WIDGETS_PER_COLUMN) as $w) {
            if (! is_array($w) || ! is_string($w['type'] ?? null)) {
                continue;
            }
            if ($this->widgets->has($w['type'])) {
                $widgets[] = $this->widget($w);
            } elseif ($orphan = $this->orphan($w)) {
                $widgets[] = $orphan;
            }
        }

        return [
            'id' => $this->id($c['id'] ?? null),
            'type' => 'column',
            'settings' => $this->settings((array) ($c['settings'] ?? []), Schema::COLUMN),
            'widgets' => $widgets,
        ];
    }

    private function widget(array $w): array
    {
        $rules = $this->widgets->rules($w['type']);

        return [
            'id' => $this->id($w['id'] ?? null),
            'type' => $w['type'],
            'settings' => $this->settings((array) ($w['settings'] ?? []), $rules),
        ];
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
        return is_string($id) && preg_match('/^[a-zA-Z0-9_-]{4,40}$/', $id) ? $id : Str::lower(Str::random(10));
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
            'color' => is_string($v) && preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', trim($v)) ? strtolower(trim($v)) : null,
            'int' => $this->int($v, $args),
            'enum' => in_array((string) $v, explode(',', $args), true) ? (string) $v : null,
            'url' => self::url($v),
            'bool' => filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            default => null,
        };
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
        return $this->html ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowElement('p')->allowElement('br')->allowElement('strong')->allowElement('b')
                ->allowElement('em')->allowElement('i')->allowElement('u')->allowElement('s')
                ->allowElement('ul')->allowElement('ol')->allowElement('li')
                ->allowElement('blockquote')->allowElement('h2')->allowElement('h3')->allowElement('h4')
                ->allowElement('a', ['href', 'title', 'target'])
                ->allowElement('span')
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
        );
    }
}
