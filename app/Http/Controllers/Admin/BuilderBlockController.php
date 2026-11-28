<?php

namespace App\Http\Controllers\Admin;

use App\Builder\ContentSanitizer;
use App\Builder\Renderer;
use App\Models\Layout;
use App\Http\Controllers\Controller;
use App\Models\BuilderBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * « Mes blocs » : éléments enregistrés depuis le builder pour être réutilisés sur
 * toutes les pages du site. Partagés entre les membres de l'équipe ; seuls leur
 * auteur et les administrateurs peuvent les supprimer.
 */
class BuilderBlockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $blocks = BuilderBlock::query()->with('user')->latest()->limit(BuilderBlock::MAX)->get();

        return response()->json([
            'blocks' => $blocks->map->toBuilder()->values(),
            // Sections globales : insérées liées, modifiées une fois pour toutes les pages.
            'globals' => Layout::query()->withoutContent()->where('type', 'block')->orderBy('name')->get()
                ->map(fn (Layout $l) => ['id' => $l->id, 'name' => $l->name, 'edit' => route('admin.layouts.builder', $l)])->values(),
            'canCreateGlobal' => $request->user()->isAdmin(),
        ]);
    }

    /**
     * Transforme un élément en section globale (gabarit « block ») : son contenu est enregistré
     * une fois ; la page le remplace par un widget « Section globale » qui l'affiche.
     */
    public function storeGlobal(Request $request, ContentSanitizer $sanitizer): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'node' => ['required', 'array']]);
        $clean = $sanitizer->sanitize(['version' => 2, 'elements' => [$data['node']]])['elements'];
        abort_if(count($clean) !== 1 || ! empty($clean[0]['orphan']), 422, __('Ce bloc ne contient aucun élément valide.'));
        abort_if(($clean[0]['type'] ?? '') === 'global-block', 422, __('Cet élément est déjà une section globale.'));

        $layout = Layout::create([
            'name' => trim($data['name']), 'type' => 'block', 'status' => Layout::STATUS_PUBLISHED, 'conditions' => [],
            'content' => ['version' => 2, 'elements' => $clean],
        ]);

        return response()->json(['global' => ['id' => $layout->id, 'name' => $layout->name, 'edit' => route('admin.layouts.builder', $layout)]], 201);
    }

    /** Rendu d'une section globale pour l'aperçu du canevas (version publiée). */
    public function renderGlobal(Layout $layout, Renderer $renderer): JsonResponse
    {
        abort_unless($layout->type === 'block', 404);
        $out = $renderer->render($layout->content);

        return response()->json(['name' => $layout->name, 'html' => $out['html'], 'css' => $out['css'], 'fonts' => $out['fonts'], 'edit' => route('admin.layouts.builder', $layout)]);
    }

    public function store(Request $request, ContentSanitizer $sanitizer): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'node' => ['required', 'array'],
        ]);
        abort_if(BuilderBlock::query()->count() >= BuilderBlock::MAX, 422, __('Limite atteinte : supprimez d’anciens blocs avant d’en enregistrer d’autres.'));

        // Comme tout contenu entrant : un seul élément, repassé par le sanitizer.
        $clean = $sanitizer->sanitize(['version' => 2, 'elements' => [$data['node']]])['elements'];
        abort_if(count($clean) !== 1 || ! empty($clean[0]['orphan']), 422, __('Ce bloc ne contient aucun élément valide.'));

        $block = BuilderBlock::create([
            'name' => trim($data['name']),
            'node' => $clean[0],
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['block' => $block->load('user')->toBuilder()], 201);
    }

    public function destroy(Request $request, BuilderBlock $block): JsonResponse
    {
        abort_unless($request->user()->isAdmin() || $block->user_id === $request->user()->id, 403, __('Seul l’auteur du bloc ou un administrateur peut le supprimer.'));
        $block->delete();

        return response()->json(null, 204);
    }
}
