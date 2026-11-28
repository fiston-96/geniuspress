<?php

namespace App\Site;

use App\Builder\ContentSanitizer;
use App\Builder\Renderer;
use App\Models\Layout;
use App\Models\Page;
use Illuminate\Http\Request;

/**
 * Retrouve le widget d'un formulaire envoyé (contact, formulaire avancé, newsletter) dans
 * le document publié qui l'affiche : une page (« page:12 ») ou un gabarit du thème, popup
 * comprise (« layout:3 »). Le champ « page » des anciens formulaires reste accepté.
 * Seul le contenu publié compte : un widget de brouillon ne reçoit rien.
 */
final class FormOwner
{
    /**
     * @param  string[]  $types  types de widgets acceptés
     * @return array{node: array, page: ?Page, layout: ?Layout}|null
     */
    public static function locate(Request $request, array $types): ?array
    {
        $widgetId = (string) $request->input('widget');
        [$kind, $id] = array_pad(explode(':', (string) $request->input('owner', ''), 2), 2, null);
        if ($kind === null || $kind === '' || $id === null) {
            [$kind, $id] = ['page', $request->input('page')];
        }

        $page = $layout = null;
        if ($kind === 'page') {
            $page = Page::query()->published()->find((int) $id);
            $content = $page?->content;
        } elseif ($kind === 'layout') {
            // Gabarit actif, ou gabarit affiché par un widget (méga-menu, section globale).
            $layout = Layout::query()->where(fn ($q) => $q->where('status', Layout::STATUS_PUBLISHED)->orWhereIn('type', Layout::EMBEDDED))->find((int) $id);
            $content = $layout?->content;
        } else {
            return null;
        }
        if (! $content) {
            return null;
        }

        $found = null;
        Renderer::walk(app(ContentSanitizer::class)->sanitize($content)['elements'], function ($node) use ($widgetId, $types, &$found) {
            if (($node['id'] ?? '') === $widgetId && in_array($node['type'] ?? '', $types, true)) {
                $found = $node;
            }
        });

        return $found ? ['node' => $found, 'page' => $page, 'layout' => $layout] : null;
    }
}
