<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Schema;

class HeadingWidget extends Widget
{
    public function type(): string { return 'heading'; }

    public function label(): string { return __('Titre'); }

    public function rules(): array
    {
        return [
            'text' => 'string:500',
            'tag' => 'enum:h1,h2,h3,h4,h5,h6,p',
            'align' => 'enum:left,center,right,justify@r',
            'color' => 'color',
            'font_size' => 'int:8:160@r',
        ] + Schema::TYPOGRAPHY;
    }

    public function translatable(): array { return ['text']; }

    public function aiHint(): ?string
    {
        return 'Heading. One h1 per page (first section), h2 for section titles, h3 for card titles.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $tag = $s['tag'] ?? 'h2';
        $ctx->rule($id, ['color' => $s['color'] ?? null, 'font-family' => isset($s['font_family']) ? null : 'var(--gp-heading-font)'], false, ' > *');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['text-align' => $v], ' > *');
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' > *');
        $ctx->typography($id, $s, ' > *');

        return "<{$tag}>".e($s['text'] ?? '')."</{$tag}>";
    }
}
