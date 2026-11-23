<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;

class TextWidget extends Widget
{
    public function type(): string { return 'text'; }

    public function label(): string { return __('Texte'); }

    public function rules(): array
    {
        return [
            'html' => 'html',
            'align' => 'enum:left,center,right,justify',
            'color' => 'color',
            'font_size' => 'int:8:80',
        ];
    }

    public function translatable(): array { return ['html']; }

    public function aiHint(): ?string
    {
        return 'Rich text. Simple HTML only: <p>, <strong>, <em>, <ul>, <ol>, <li>, <a href>.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, [
            'text-align' => $s['align'] ?? null,
            'color' => $s['color'] ?? null,
            'font-size' => RenderContext::px($s['font_size'] ?? null),
        ]);

        // Déjà nettoyé par ContentSanitizer (liste blanche de balises).
        return $s['html'] ?? '';
    }
}
