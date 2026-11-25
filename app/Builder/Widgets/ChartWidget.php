<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/**
 * Graphique (barres, barres horizontales, courbe, aire, camembert, anneau) dessiné
 * en SVG par le serveur : rien à charger, lisible sans JavaScript, avec un tableau
 * des données pour les lecteurs d'écran. Le builder dessine le même SVG (widgets/chart.js).
 */
class ChartWidget extends Widget
{
    public const W = 640;
    public const HEIGHTS = ['2:1' => 320, '16:9' => 360, '4:3' => 480, '1:1' => 640];

    public function type(): string { return 'chart'; }

    public function label(): string { return __('Graphique'); }

    public function rules(): array
    {
        return [
            'kind' => 'enum:bar,hbar,line,area,pie,doughnut',
            'data' => 'string:5000',
            'series' => 'string:200',
            'title' => 'string:160',
            'ratio' => 'enum:2:1,16:9,4:3,1:1',
            'show_values' => 'bool',
            'show_grid' => 'bool',
            'show_legend' => 'bool',
            'prefix' => 'string:12',
            'suffix' => 'string:12',
            'color_1' => 'color',
            'color_2' => 'color',
            'color_3' => 'color',
            'color_4' => 'color',
            'text_color' => 'color',
            'grid_color' => 'color',
            'max_width' => 'int:120:1200@r',
        ];
    }

    public function translatable(): array { return ['data', 'series', 'title', 'prefix', 'suffix']; }

    public function aiHint(): ?string
    {
        return 'Chart drawn from text data: one line per item "Label | value" (up to 4 values per line for several series, named in "series" separated by "|"). kind: bar, hbar, line, area, pie or doughnut. Use it for key figures (budget, results, growth).';
    }

    /** Lignes « Libellé | valeur | valeur… » : au plus 60 lignes et 4 séries, valeurs négatives ramenées à 0. */
    public static function parse(string $data): array
    {
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($data)) as $line) {
            if (trim($line) === '' || count($rows) >= 60) {
                continue;
            }
            $cells = array_map('trim', explode('|', $line));
            $values = array_map(function ($c) {
                $n = str_replace([' ', "\u{00A0}", "\u{202F}", ','], ['', '', '', '.'], $c);

                return is_numeric($n) ? max(0, (float) $n) : 0.0;
            }, array_slice($cells, 1, 4));
            $rows[] = ['label' => $cells[0], 'values' => $values ?: [0.0]];
        }

        return $rows;
    }

    /** Graduation « ronde » de l'axe : [pas, maximum]. */
    public static function scale(float $max): array
    {
        if ($max <= 0) {
            return [1, 4];
        }
        $raw = $max / 4;
        $mag = 10 ** floor(log10($raw));
        $norm = $raw / $mag;
        $step = ($norm <= 1 ? 1 : ($norm <= 2 ? 2 : ($norm <= 2.5 ? 2.5 : ($norm <= 5 ? 5 : 10)))) * $mag;

        return [$step, ceil($max / $step - 1e-9) * $step];
    }

    /** Nombre lisible : « 1 250,5 » en français, « 1,250.5 » sinon. */
    public static function number(float $v, string $locale): string
    {
        $v = round($v, 2);
        $decimals = $v == floor($v) ? 0 : (round($v, 1) == $v ? 1 : 2);

        return $locale === 'fr' ? number_format($v, $decimals, ',', ' ') : number_format($v, $decimals, '.', ',');
    }

    /**
     * Valeur avec ses unités, séparées par une espace insécable quand l'usage le veut :
     * « FC 500 », « $500 » ; « 45 % » et « 500 $ » en français, « 45% » sinon, « 3 k ».
     */
    public static function unit(float $v, string $prefix, string $suffix, string $locale): string
    {
        $before = $prefix !== '' && preg_match('/\p{L}$/u', $prefix) ? $prefix."\u{00A0}" : $prefix;
        $after = $suffix !== '' && ($locale === 'fr' || preg_match('/^\p{L}/u', $suffix)) ? "\u{00A0}".$suffix : $suffix;

        return $before.self::number($v, $locale).$after;
    }

    private static function r(float $v): string
    {
        return (string) round($v, 1);
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $rows = self::parse((string) ($s['data'] ?? ''));
        if (! $rows) {
            return '';
        }
        $locale = $ctx->site?->locale ?? \App\Site\Permalinks::defaultLocale();
        $ctx->rule($id, [
            '--gp-c1' => $s['color_1'] ?? null,
            '--gp-c2' => $s['color_2'] ?? null,
            '--gp-c3' => $s['color_3'] ?? null,
            '--gp-c4' => $s['color_4'] ?? null,
            '--gp-chart-grid' => $s['grid_color'] ?? null,
            'color' => $s['text_color'] ?? null,
        ], false, ' .gp-chart');
        $ctx->responsive($id, $s, 'max_width', fn ($v) => ['max-width' => $v.'px'], ' .gp-chart');

        $kind = $s['kind'] ?? 'bar';
        $title = $s['title'] ?? '';
        $fmt = fn (float $v) => self::unit($v, $s['prefix'] ?? '', $s['suffix'] ?? '', $locale);
        $series = array_values(array_filter(array_map('trim', explode('|', (string) ($s['series'] ?? ''))), fn ($n) => $n !== ''));
        $count = in_array($kind, ['pie', 'doughnut'], true) ? 1 : min(4, max(array_map(fn ($r) => count($r['values']), $rows)));

        $svg = in_array($kind, ['pie', 'doughnut'], true)
            ? self::slices($rows, $kind)
            : ($kind === 'hbar' ? self::hbars($rows, $count, $s, $fmt) : self::axes($rows, $count, $kind, $s, $fmt));

        // Légende : les parts (camembert, anneau) ou les séries nommées.
        $legend = '';
        if ($s['show_legend'] ?? true) {
            if (in_array($kind, ['pie', 'doughnut'], true)) {
                $total = array_sum(array_map(fn ($r) => $r['values'][0], $rows)) ?: 1;
                foreach ($rows as $i => $r) {
                    $legend .= '<li><span class="gp-chart__swatch gp-chart__c'.($i % 6 + 1).'"></span>'.e($r['label'])
                        .(($s['show_values'] ?? true) ? ' <span class="gp-chart__legend-value">'.e($fmt($r['values'][0])).' · '.self::unit($r['values'][0] / $total * 100, '', '%', $locale).'</span>' : '').'</li>';
                }
            } elseif ($series) {
                foreach (array_slice($series, 0, $count) as $k => $name) {
                    $legend .= '<li><span class="gp-chart__swatch gp-chart__c'.($k + 1).'"></span>'.e($name).'</li>';
                }
            }
        }

        // Les données en tableau, pour les lecteurs d'écran.
        $table = '<table class="gp-sr-only">'.($series ? '<thead><tr><td></td>'.implode('', array_map(fn ($n) => '<th scope="col">'.e($n).'</th>', array_slice($series, 0, $count))).'</tr></thead>' : '').'<tbody>';
        foreach ($rows as $r) {
            $table .= '<tr><th scope="row">'.e($r['label']).'</th>'.implode('', array_map(fn ($v) => '<td>'.e($fmt($v)).'</td>', array_slice($r['values'], 0, $count))).'</tr>';
        }
        $table .= '</tbody></table>';

        $height = in_array($kind, ['pie', 'doughnut'], true) ? 320 : self::HEIGHTS[$s['ratio'] ?? '16:9'];
        $width = in_array($kind, ['pie', 'doughnut'], true) ? 320 : self::W;

        return '<figure class="gp-chart gp-chart--'.e($kind).'">'
            .($title !== '' ? '<figcaption class="gp-chart__title">'.e($title).'</figcaption>' : '')
            .'<svg class="gp-chart__svg" viewBox="0 0 '.$width.' '.$height.'" aria-hidden="true" focusable="false">'.$svg.'</svg>'
            .($legend !== '' ? '<ul class="gp-chart__legend">'.$legend.'</ul>' : '')
            .$table.'</figure>';
    }

    /** Barres verticales, courbes et aires, avec un axe gradué. */
    private static function axes(array $rows, int $count, string $kind, array $s, \Closure $fmt): string
    {
        $h = self::HEIGHTS[$s['ratio'] ?? '16:9'];
        [$left, $right, $top, $bottom] = [56, self::W - 12, 22, $h - 34];
        $max = max(array_map(fn ($r) => max(array_slice($r['values'], 0, $count)), $rows));
        [$step, $scaleTop] = self::scale($max);
        $y = fn (float $v) => $bottom - $v / $scaleTop * ($bottom - $top);
        $out = '';

        for ($v = 0, $i = 0; $v <= $scaleTop + 1e-9 && $i < 12; $v += $step, $i++) {
            $out .= (($s['show_grid'] ?? true) || $v == 0 ? '<line class="gp-chart__grid" x1="'.$left.'" x2="'.$right.'" y1="'.self::r($y($v)).'" y2="'.self::r($y($v)).'"/>' : '')
                .'<text class="gp-chart__axis" x="'.($left - 8).'" y="'.self::r($y($v) + 4).'" text-anchor="end">'.e($fmt($v)).'</text>';
        }

        $n = count($rows);
        $band = ($right - $left) / $n;
        foreach ($rows as $i => $r) {
            $out .= '<text class="gp-chart__axis" x="'.self::r($left + $band * ($i + 0.5)).'" y="'.($bottom + 22).'" text-anchor="middle">'.e($r['label']).'</text>';
        }

        if ($kind === 'bar') {
            $inner = $band * 0.72;
            $bw = $inner / $count;
            foreach ($rows as $i => $r) {
                for ($k = 0; $k < $count; $k++) {
                    $v = $r['values'][$k] ?? 0.0;
                    $x = $left + $band * $i + ($band - $inner) / 2 + $k * $bw;
                    $out .= '<rect class="gp-chart__c'.($k + 1).'" x="'.self::r($x + ($count > 1 ? 1 : 0)).'" y="'.self::r($y($v)).'" width="'.self::r($bw - ($count > 1 ? 2 : 0)).'" height="'.self::r($bottom - $y($v)).'" rx="3" fill="currentColor"/>';
                    if ($s['show_values'] ?? true) {
                        $out .= '<text class="gp-chart__value" x="'.self::r($x + $bw / 2).'" y="'.self::r($y($v) - 6).'" text-anchor="middle">'.e($fmt($v)).'</text>';
                    }
                }
            }

            return $out;
        }

        for ($k = 0; $k < $count; $k++) {
            $points = [];
            foreach ($rows as $i => $r) {
                $points[] = [self::r($left + $band * ($i + 0.5)), self::r($y($r['values'][$k] ?? 0.0)), $r['values'][$k] ?? 0.0];
            }
            $line = 'M'.implode(' L', array_map(fn ($p) => $p[0].' '.$p[1], $points));
            if ($kind === 'area') {
                $out .= '<path class="gp-chart__c'.($k + 1).'" d="'.$line.' L'.$points[array_key_last($points)][0].' '.$bottom.' L'.$points[0][0].' '.$bottom.' Z" fill="currentColor" fill-opacity=".14"/>';
            }
            $out .= '<path class="gp-chart__c'.($k + 1).'" d="'.$line.'" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>';
            foreach ($points as [$px, $py, $v]) {
                $out .= '<circle class="gp-chart__c'.($k + 1).'" cx="'.$px.'" cy="'.$py.'" r="4.5" fill="currentColor"/>'
                    .(($s['show_values'] ?? true) ? '<text class="gp-chart__value" x="'.$px.'" y="'.self::r($py - 10).'" text-anchor="middle">'.e($fmt($v)).'</text>' : '');
            }
        }

        return $out;
    }

    /** Barres horizontales : libellés à gauche, valeurs au bout des barres. */
    private static function hbars(array $rows, int $count, array $s, \Closure $fmt): string
    {
        $h = self::HEIGHTS[$s['ratio'] ?? '16:9'];
        [$left, $right, $top, $bottom] = [150, self::W - 70, 6, $h - 6];
        $max = max(array_map(fn ($r) => max(array_slice($r['values'], 0, $count)), $rows));
        [, $scaleTop] = self::scale($max);
        $band = ($bottom - $top) / count($rows);
        $inner = min($band * 0.7, 26 * $count);
        $bh = $inner / $count;
        $out = '<line class="gp-chart__grid" x1="'.$left.'" x2="'.$left.'" y1="'.$top.'" y2="'.$bottom.'"/>';
        foreach ($rows as $i => $r) {
            $out .= '<text class="gp-chart__axis" x="'.($left - 10).'" y="'.self::r($top + $band * ($i + 0.5) + 4).'" text-anchor="end">'.e(mb_strimwidth($r['label'], 0, 22, '…')).'</text>';
            for ($k = 0; $k < $count; $k++) {
                $v = $r['values'][$k] ?? 0.0;
                $y = $top + $band * $i + ($band - $inner) / 2 + $k * $bh;
                $w = $v / $scaleTop * ($right - $left);
                $out .= '<rect class="gp-chart__c'.($k + 1).'" x="'.$left.'" y="'.self::r($y + ($count > 1 ? 1 : 0)).'" width="'.self::r($w).'" height="'.self::r($bh - ($count > 1 ? 2 : 0)).'" rx="3" fill="currentColor"/>';
                if ($s['show_values'] ?? true) {
                    $out .= '<text class="gp-chart__value" x="'.self::r($left + $w + 6).'" y="'.self::r($y + $bh / 2 + 4).'">'.e($fmt($v)).'</text>';
                }
            }
        }

        return $out;
    }

    /** Camembert et anneau : une part par ligne, de midi dans le sens des aiguilles d'une montre. */
    private static function slices(array $rows, string $kind): string
    {
        $total = array_sum(array_map(fn ($r) => $r['values'][0], $rows));
        if ($total <= 0) {
            return '<circle class="gp-chart__grid" cx="160" cy="160" r="150" fill="none"/>';
        }
        [$outer, $inner] = [150, $kind === 'doughnut' ? 90 : 0];
        $point = fn (float $turn, int $radius) => self::r(160 + $radius * sin(2 * M_PI * $turn)).' '.self::r(160 - $radius * cos(2 * M_PI * $turn));
        $out = '';
        $start = 0.0;
        foreach ($rows as $i => $r) {
            $part = $r['values'][0] / $total;
            if ($part <= 0) {
                continue;
            }
            if ($part >= 0.9999) {
                // Une seule part : le disque (ou l'anneau) entier.
                $d = 'M10 160 A150 150 0 1 1 310 160 A150 150 0 1 1 10 160 Z'.($inner ? ' M70 160 A90 90 0 1 0 250 160 A90 90 0 1 0 70 160 Z' : '');
            } else {
                $large = $part > 0.5 ? 1 : 0;
                $end = $start + $part;
                $d = $inner
                    ? 'M'.$point($start, $outer).' A'.$outer.' '.$outer.' 0 '.$large.' 1 '.$point($end, $outer).' L'.$point($end, $inner).' A'.$inner.' '.$inner.' 0 '.$large.' 0 '.$point($start, $inner).' Z'
                    : 'M160 160 L'.$point($start, $outer).' A'.$outer.' '.$outer.' 0 '.$large.' 1 '.$point($end, $outer).' Z';
            }
            $out .= '<path class="gp-chart__slice gp-chart__c'.($i % 6 + 1).'" d="'.$d.'" fill="currentColor" fill-rule="evenodd"/>';
            $start += $part;
        }

        return $out;
    }
}
