<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Schema;

/** Titre avec un mot mis en valeur : couleur, soulignement, surligneur ou dégradé. */
class DualHeadingWidget extends Widget
{
    public function type(): string { return 'dual-heading'; }

    public function label(): string { return __('Titre bicolore'); }

    public function rules(): array
    {
        return [
            'before' => 'string:300',
            'highlight' => 'string:200',
            'after' => 'string:300',
            'tag' => 'enum:h1,h2,h3,h4,p',
            'style' => 'enum:color,underline,marker,gradient',
            'align' => 'enum:left,center,right@r',
            'color' => 'color',
            'highlight_color' => 'color',
            'highlight_color_2' => 'color',
            'font_size' => 'int:8:160@r',
        ] + Schema::TYPOGRAPHY;
    }

    public function translatable(): array { return ['before', 'highlight', 'after']; }

    public function aiHint(): ?string
    {
        return 'Heading with one highlighted word or phrase: "before", "highlight", "after". Use for strong hero or section titles.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $tag = $s['tag'] ?? 'h2';
        $ctx->rule($id, ['color' => $s['color'] ?? null, 'font-family' => isset($s['font_family']) ? null : 'var(--gp-heading-font)'], false, ' > *');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['text-align' => $v], ' > *');
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' > *');
        $ctx->typography($id, $s, ' > *');
        $ctx->rule($id, ['--gp-hl' => $s['highlight_color'] ?? null, '--gp-hl-2' => $s['highlight_color_2'] ?? null], false, ' .gp-dh__hl');

        $parts = array_filter([
            ($s['before'] ?? '') !== '' ? e($s['before']) : null,
            '<span class="gp-dh__hl gp-dh__hl--'.e($s['style'] ?? 'color').'">'.e($s['highlight'] ?? '').'</span>',
            ($s['after'] ?? '') !== '' ? e($s['after']) : null,
        ]);

        return "<{$tag} class=\"gp-dh\">".implode(' ', $parts)."</{$tag}>";
    }
}
