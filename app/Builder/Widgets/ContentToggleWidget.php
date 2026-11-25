<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

/** Bascule entre deux contenus (mensuel / annuel, particuliers / entreprises…), sans JavaScript. */
class ContentToggleWidget extends Widget
{
    public function type(): string { return 'content-toggle'; }

    public function label(): string { return __('Bascule de contenu'); }

    public function rules(): array
    {
        return [
            'label_a' => 'string:60',
            'label_b' => 'string:60',
            'html_a' => 'html',
            'html_b' => 'html',
            'align' => 'enum:left,center,right',
            'accent' => 'color',
        ];
    }

    public function translatable(): array { return ['label_a', 'label_b', 'html_a', 'html_b']; }

    public function aiHint(): ?string
    {
        return 'Switch between two contents ("label_a", "html_a", "label_b", "html_b"), e.g. individuals / businesses, monthly / yearly.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['--gp-toggle' => $s['accent'] ?? null, '--gp-toggle-align' => isset($s['align']) ? ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$s['align']] : null], false, ' .gp-ctog');
        $name = 't-'.e($id);

        return '<div class="gp-ctog">'
            .'<input type="radio" name="'.$name.'" id="'.$name.'-a" class="gp-ctog__radio gp-ctog__radio--a" checked>'
            .'<input type="radio" name="'.$name.'" id="'.$name.'-b" class="gp-ctog__radio gp-ctog__radio--b">'
            .'<div class="gp-ctog__switch"><label for="'.$name.'-a">'.e($s['label_a'] ?? 'A').'</label><label for="'.$name.'-b">'.e($s['label_b'] ?? 'B').'</label></div>'
            .'<div class="gp-ctog__panel gp-ctog__panel--a">'.($s['html_a'] ?? '').'</div>'
            .'<div class="gp-ctog__panel gp-ctog__panel--b">'.($s['html_b'] ?? '').'</div>'
            .'</div>';
    }
}
