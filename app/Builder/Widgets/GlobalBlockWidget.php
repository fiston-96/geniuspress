<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Builder\Renderer;
use App\Models\Layout;

/**
 * Section globale : affiche un gabarit de type « block » conçu une fois dans le builder
 * (bandeau d'appel, coordonnées, logos de partenaires…). Le modifier met à jour toutes
 * les pages qui l'utilisent. Une section qui s'inclut elle-même n'est pas rendue deux fois.
 */
class GlobalBlockWidget extends Widget
{
    /** @var array<int, true> sections globales en cours de rendu */
    private static array $rendering = [];

    public function type(): string { return 'global-block'; }

    public function label(): string { return __('Section globale'); }

    public function rules(): array
    {
        return ['layout' => 'int:1:99999999'];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $layoutId = (int) ($s['layout'] ?? 0);
        if (! $layoutId || isset(self::$rendering[$layoutId])) {
            return '';
        }
        $layout = Layout::query()->where('type', 'block')->find($layoutId);
        if (! $layout) {
            return '';
        }
        self::$rendering[$layoutId] = true;
        try {
            $rendered = app(Renderer::class)->render($layout->content, $ctx->pageId, $ctx->site, 'layout:'.$layout->id);
        } finally {
            unset(self::$rendering[$layoutId]);
        }
        $ctx->embed($rendered);

        return $rendered['html'];
    }
}
