<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Chiffre clé animé : bénéficiaires, années d'expérience, élèves, lits… */
class CounterWidget extends Widget
{
    public function type(): string { return 'counter'; }

    public function label(): string { return __('Compteur'); }

    public function rules(): array
    {
        return [
            'number' => 'int:-999999999:999999999',
            'start' => 'int:-999999999:999999999',
            'prefix' => 'string:12',
            'suffix' => 'string:12',
            'label' => 'string:160',
            'duration' => 'int:0:10000',
            'separator' => 'enum:space,comma,dot,none',
            'align' => 'enum:left,center,right@r',
            'number_color' => 'color',
            'number_size' => 'int:12:160@r',
            'label_color' => 'color',
        ];
    }

    public function translatable(): array { return ['label', 'suffix', 'prefix']; }

    public function aiHint(): ?string
    {
        return 'Key figure with a label (e.g. 1200 + "children supported"). Never invent figures: use 0 and a label such as "[à compléter] bénéficiaires" when the brief does not give the number.';
    }

    public static function format(int $n, string $separator): string
    {
        $sep = ['space' => "\u{202F}", 'comma' => ',', 'dot' => '.', 'none' => ''][$separator] ?? "\u{202F}";

        return number_format($n, 0, '', $sep);
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['text-align' => $s['align'] ?? 'center']);
        $ctx->responsive($id, array_diff_key($s, ['align' => 1]), 'align', fn ($v) => ['text-align' => $v]);
        $ctx->rule($id, ['color' => $s['number_color'] ?? null], false, ' .gp-counter__num');
        $ctx->responsive($id, $s, 'number_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-counter__num');
        $ctx->rule($id, ['color' => $s['label_color'] ?? null], false, ' .gp-counter__label');

        $n = (int) ($s['number'] ?? 0);
        $sep = $s['separator'] ?? 'space';

        // Le chiffre final est écrit dans la page : l'animation (site.js) n'est qu'un bonus.
        return '<div class="gp-counter__box"><span class="gp-counter__num">'.e($s['prefix'] ?? '')
            .'<span data-gp-counter="'.$n.'" data-start="'.(int) ($s['start'] ?? 0).'" data-duration="'.(int) ($s['duration'] ?? 2000).'" data-separator="'.e($sep).'">'.e(self::format($n, $sep)).'</span>'
            .e($s['suffix'] ?? '').'</span>'
            .(isset($s['label']) ? '<span class="gp-counter__label">'.e($s['label']).'</span>' : '').'</div>';
    }
}
