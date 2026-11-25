<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/**
 * Tableau de données : une ligne par ligne du tableau, les cellules séparées
 * par « | ». La première ligne est l'en-tête (si « header » est actif).
 */
class DataTableWidget extends Widget
{
    public const MAX_ROWS = 200;

    public function type(): string { return 'data-table'; }

    public function label(): string { return __('Tableau'); }

    public function rules(): array
    {
        return [
            'data' => 'string:20000',
            'header' => 'bool',
            'striped' => 'bool',
            'caption' => 'string:200',
            'header_bg' => 'color',
            'header_color' => 'color',
            'border_color' => 'color',
            'font_size' => 'int:8:40@r',
        ];
    }

    public function translatable(): array { return ['data', 'caption']; }

    public function aiHint(): ?string
    {
        return 'Table ("data": one line per row, cells separated by " | ", first line is the header). For schedules, comparisons, fees.';
    }

    /** @return list<list<string>> */
    public static function rows(?string $data): array
    {
        $rows = [];
        foreach (array_slice(preg_split('/\r\n|\r|\n/', trim((string) $data)), 0, self::MAX_ROWS) as $line) {
            if (trim($line) !== '') {
                $rows[] = array_map('trim', explode('|', $line));
            }
        }

        return $rows;
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $rows = self::rows($s['data'] ?? '');
        if (! $rows) {
            return '';
        }
        $ctx->rule($id, ['background' => $s['header_bg'] ?? null, 'color' => $s['header_color'] ?? null], false, ' th');
        $ctx->rule($id, ['border-color' => $s['border_color'] ?? null], false, ' td, #gp-'.$id.' th');
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' table');

        $header = ($s['header'] ?? true) ? array_shift($rows) : null;
        $cols = max(array_map('count', $header ? [$header, ...$rows] : $rows));
        $cells = fn (array $r, string $tag) => implode('', array_map(fn ($i) => "<{$tag}>".e($r[$i] ?? '')."</{$tag}>", range(0, $cols - 1)));

        $html = '<div class="gp-table-wrap"><table class="gp-table'.(($s['striped'] ?? true) ? ' gp-table--striped' : '').'">'
            .(! empty($s['caption']) ? '<caption>'.e($s['caption']).'</caption>' : '')
            .($header ? '<thead><tr>'.$cells($header, 'th').'</tr></thead>' : '')
            .'<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>'.$cells($row, 'td').'</tr>';
        }

        return $html.'</tbody></table></div>';
    }
}
