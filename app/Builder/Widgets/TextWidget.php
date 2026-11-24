<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Schema;

class TextWidget extends Widget
{
    public function type(): string { return 'text'; }

    public function label(): string { return __('Texte'); }

    public function rules(): array
    {
        return [
            'html' => 'html',
            'align' => 'enum:left,center,right,justify@r',
            'color' => 'color',
            'link_color' => 'color@h',
            'font_size' => 'int:8:80@r',
        ] + Schema::TYPOGRAPHY;
    }

    public function translatable(): array { return ['html']; }

    public function aiHint(): ?string
    {
        return 'Rich text. Simple HTML only: <p>, <strong>, <em>, <ul>, <ol>, <li>, <a href>.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['color' => $s['color'] ?? null]);
        $ctx->responsive($id, $s, 'align', fn ($v) => ['text-align' => $v]);
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px']);
        $ctx->typography($id, $s);
        $ctx->rule($id, ['color' => $s['link_color'] ?? null], false, ' a');
        $ctx->rule($id, ['color' => $s['link_color_hover'] ?? null], false, ' a:hover');

        // Déjà nettoyé par ContentSanitizer (liste blanche de balises).
        return $s['html'] ?? '';
    }
}
