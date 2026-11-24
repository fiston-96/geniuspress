<?php

namespace App\Http\Controllers\Admin;

use App\Ai\AiException;
use App\Ai\Assistant;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AiController extends Controller
{
    public function __construct(private Assistant $assistant)
    {
        // Les appels au modèle peuvent prendre plus de 30 secondes.
        @set_time_limit(300);
    }

    public function status(): JsonResponse
    {
        return response()->json(['configured' => $this->assistant->isConfigured()]);
    }

    /** Génère une page complète à partir d'une description. Le résultat n'est pas enregistré. */
    public function generate(Request $request, Page $page): JsonResponse
    {
        $data = $request->validate(['brief' => ['required', 'string', 'min:10', 'max:4000']]);

        return $this->run(fn () => $this->assistant->generatePage($page, $data['brief']));
    }

    public function text(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(array_keys(Assistant::TEXT_ACTIONS))],
            'format' => ['required', Rule::in(['plain', 'html'])],
            'text' => ['nullable', 'string', 'max:20000'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'context' => ['nullable', 'string', 'max:6000'],
            'page_id' => ['nullable', 'integer', 'exists:pages,id'],
        ]);

        if ($data['action'] === 'write' && blank($data['instructions'] ?? null) && blank($data['text'] ?? null)) {
            return response()->json(['message' => __('Dites à l’IA ce qu’elle doit écrire.')], 422);
        }

        return $this->run(fn () => ['text' => $this->assistant->rewrite(
            isset($data['page_id']) ? Page::find($data['page_id']) : null,
            $data['action'], $data['format'], (string) ($data['text'] ?? ''),
            (string) ($data['instructions'] ?? ''), (string) ($data['context'] ?? ''),
        )]);
    }

    public function translate(Request $request, Page $page): JsonResponse
    {
        $locales = (array) Setting::read('locales', ['fr']);
        $data = $request->validate([
            'locale' => ['required', Rule::in($locales), Rule::notIn([$page->locale])],
        ], ['locale.in' => __('Ajoutez d’abord cette langue dans les réglages du site.')]);

        return $this->run(function () use ($page, $data, $request) {
            $translation = $this->assistant->translatePage($page, $data['locale'], $request->user()->id);

            return [
                'page' => $translation->only(['id', 'title', 'locale', 'status']),
                'builder_url' => route('admin.pages.builder', $translation),
            ];
        });
    }

    /** Réécrit une page de modèle pour l'organisation (profil saisi sur la page Modèles). */
    public function fill(Request $request, Page $page): JsonResponse
    {
        $profile = (array) Setting::read('site_profile', []);
        if (empty($profile['name'])) {
            return response()->json(['message' => __('Remplissez d’abord le questionnaire de la page Modèles.')], 422);
        }
        $titles = Page::query()->where('locale', $page->locale)->where('id', '!=', $page->id)->orderBy('id')->limit(30)->pluck('title')->all();

        return $this->run(function () use ($page, $profile, $titles, $request) {
            $page = $this->assistant->fillPage($page, $profile, $titles, $request->user()->id);

            return ['page' => $page->only(['id', 'title', 'locale']), 'builder_url' => route('admin.pages.builder', $page)];
        });
    }

    public function audit(Request $request, Page $page): JsonResponse
    {
        $request->validate(['content' => ['required', 'array']]);

        return $this->run(fn () => $this->assistant->audit($page, $request->input('content')));
    }

    /** Applique le titre et la description SEO proposés. */
    public function applyMeta(Request $request, Page $page): JsonResponse
    {
        $page->update($request->validate([
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ]));

        return response()->json($page->only(['meta_title', 'meta_description']));
    }

    private function run(callable $fn): JsonResponse
    {
        try {
            return response()->json($fn());
        } catch (AiException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
