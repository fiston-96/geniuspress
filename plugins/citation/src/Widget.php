<?php

namespace Plugins\Citation;

use App\Builder\RenderContext;
use App\Builder\Widgets\Widget as BaseWidget;

class Widget extends BaseWidget
{
    public function type(): string
    {
        return 'citation';
    }

    public function label(): string
    {
        return 'Citation';
    }

    /** Réglages autorisés : tout le reste est supprimé avant l'enregistrement. */
    public function rules(): array
    {
        return [
            'text' => 'string:600',
            'author' => 'string:120',
            'accent' => 'color',
            'align' => 'enum:left,center',
        ];
    }

    /** Textes traduits par l'assistant IA. */
    public function translatable(): array
    {
        return ['text', 'author'];
    }

    /** Permet à l'assistant IA d'utiliser ce widget quand il crée une page. */
    public function aiHint(): ?string
    {
        return 'A short quote with its author. Never invent quotes attributed to real people.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['border-color' => $s['accent'] ?? null, 'text-align' => $s['align'] ?? null], false, ' blockquote');

        $author = isset($s['author']) ? '<footer>'.e($s['author']).'</footer>' : '';

        return '<blockquote class="citation"><p>'.e($s['text'] ?? '').'</p>'.$author.'</blockquote>';
    }
}
