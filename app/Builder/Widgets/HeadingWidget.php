<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

class HeadingWidget extends Widget
{
    public function type(): string { return 'heading'; }

    public function label(): string { return 'Titre'; }

    public function rules(): array
    {
        return [
            'text' => 'string:500',
            'tag' => 'enum:h1,h2,h3,h4,h5,h6,p',
            'align' => 'enum:left,center,right',
            'color' => 'color',
            'font_size' => 'int:8:160',
            'font_size_mobile' => 'int:8:160',
            'font_weight' => 'enum:300,400,500,600,700,800,900',
        ];
    }

    public function translatable(): array { return ['text']; }

    public function aiHint(): ?string
    {
        return 'Heading. One h1 per page (first section), h2 for section titles, h3 for card titles.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $tag = $s['tag'] ?? 'h2';
        $ctx->rule($id, [
            'text-align' => $s['align'] ?? null,
            'color' => $s['color'] ?? null,
            'font-size' => RenderContext::px($s['font_size'] ?? null),
            'font-weight' => $s['font_weight'] ?? null,
        ], false, ' > *');
        $ctx->rule($id, ['font-size' => RenderContext::px($s['font_size_mobile'] ?? null)], true, ' > *');

        return "<{$tag}>".e($s['text'] ?? '')."</{$tag}>";
    }
}
