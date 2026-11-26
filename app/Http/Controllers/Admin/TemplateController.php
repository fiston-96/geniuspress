<?php

namespace App\Http\Controllers\Admin;

use App\Ai\AiException;
use App\Ai\Assistant;
use App\Ai\LlmGateway;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Setting;
use App\Templates\Catalog;
use App\Transfer\SiteImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Modèles de sites : parcourir le catalogue, se faire conseiller par l'IA,
 * installer un modèle, puis laisser l'IA en rédiger les textes.
 */
class TemplateController extends Controller
{
    public const TONES = ['chaleureux', 'professionnel', 'institutionnel', 'dynamique', 'elegant'];

    public function __construct(private Catalog $catalog) {}

    public function index()
    {
        try {
            $index = $this->catalog->index();
            $error = null;
        } catch (RuntimeException $e) {
            $index = ['sectors' => [], 'templates' => []];
            $error = $e->getMessage();
        }
        $prices = $index['templates'] ? $this->catalog->prices() : [];
        $templates = array_map(fn ($t) => $t + ['demo' => $this->catalog->demoUrl($t['id']), 'price' => $prices[$t['id']] ?? null], $index['templates']);

        return view('admin.templates.index', [
            'sectors' => $index['sectors'],
            'templates' => $templates,
            'byId' => collect($templates)->keyBy('id')->all(),
            'error' => $error,
            'profile' => (array) Setting::read('site_profile', []),
            'recommendations' => session('recommendations'),
            'aiConfigured' => app(LlmGateway::class)->isConfigured(),
            'siteLocales' => (array) Setting::read('locales', ['fr']),
            'lang' => app()->getLocale() === 'en' ? 'en' : 'fr',
        ]);
    }

    /** Questionnaire : enregistre le profil de l'organisation et propose 3 modèles. */
    public function recommend(Request $request, Assistant $assistant)
    {
        $profile = $this->validatedProfile($request);
        Setting::write(['site_profile' => $profile]);

        try {
            $index = $this->catalog->index();
        } catch (RuntimeException $e) {
            return back()->withErrors(['catalog' => $e->getMessage()])->withInput();
        }

        $warning = null;
        $result = null;
        if ($assistant->isConfigured()) {
            try {
                $result = $assistant->recommendTemplates($profile, $index);
            } catch (AiException $e) {
                $warning = $e->getMessage();
            }
        }
        if (! $result || count($result['items']) < 3) {
            $result = $this->suggest($profile, $index, $result['items'] ?? []);
        }

        return redirect()->to(route('admin.templates.index').'#propositions')
            ->with('recommendations', $result)
            ->with('status', $warning ? __('Propositions faites selon votre secteur (assistant IA indisponible : :reason)', ['reason' => $warning]) : null);
    }

    public function install(Request $request, string $template, SiteImporter $importer)
    {
        $data = $request->validate([
            'locales' => ['required', 'array', 'min:1'],
            'locales.*' => ['in:fr,en'],
            'mode' => ['required', 'in:replace,add'],
            'design' => ['sometimes', 'boolean'],
            'fill' => ['sometimes', 'boolean'],
        ]);

        try {
            $file = $this->catalog->download($template);
        } catch (RuntimeException $e) {
            return back()->withErrors(['catalog' => $e->getMessage()]);
        }
        try {
            $report = $importer->import($file, $request->user(), replace: $data['mode'] === 'replace', withSettings: $request->boolean('design'), locales: $data['locales']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['catalog' => $e->getMessage()]);
        } finally {
            @unlink($file);
        }

        $profile = (array) Setting::read('site_profile', []);
        $entry = $this->catalog->find($template);
        $name = $entry['name'][app()->getLocale() === 'en' ? 'en' : 'fr'] ?? $template;
        // Secteur non précisé dans le questionnaire : celui du modèle choisi guide l'assistant.
        if ($profile && empty($profile['sector']) && ! empty($entry['sector'])) {
            $profile['sector'] = $entry['sector'];
            Setting::write(['site_profile' => $profile]);
        }
        $message = __('Modèle « :name » installé : :count pages.', ['name' => $name, 'count' => $report['pages']]);

        if ($request->boolean('fill') && ! empty($profile['name']) && app(LlmGateway::class)->isConfigured() && $report['page_ids']) {
            // Le nom et la présentation de l'organisation servent ensuite à toutes les rédactions.
            Setting::write(array_filter([
                'site_name' => $profile['name'],
                'organization_description' => trim((string) Setting::read('organization_description')) === '' ? Str::limit($profile['description'] ?? '', 3000, '') : null,
            ]));

            return redirect()->route('admin.templates.fill', ['pages' => implode(',', $report['page_ids'])])->with('status', $message);
        }

        return redirect()->route('admin.pages.index')->with('status', $message);
    }

    /** Suivi du remplissage des pages par l'IA, une page après l'autre. */
    public function fill(Request $request)
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $request->query('pages'))));
        $pages = Page::query()->whereIn('id', $ids)->orderBy('locale')->orderBy('id')->get(['id', 'title', 'locale', 'slug']);
        abort_if($pages->isEmpty(), 404);

        return view('admin.templates.fill', ['pages' => $pages]);
    }

    private function validatedProfile(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sector' => ['nullable', 'string', 'max:40'],
            'description' => ['required', 'string', 'max:3000'],
            'offerings' => ['nullable', 'string', 'max:3000'],
            'audience' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'tone' => ['nullable', 'in:'.implode(',', self::TONES)],
            'phone' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:300'],
            'extra' => ['nullable', 'string', 'max:2000'],
        ]);

        return array_map(fn ($v) => is_string($v) ? trim(strip_tags($v)) : $v, array_filter($data, fn ($v) => $v !== null && $v !== ''));
    }

    /**
     * Propositions sans IA : le secteur choisi (ou deviné d'après les mots-clés du
     * catalogue), puis des modèles aux styles variés.
     */
    private function suggest(array $profile, array $index, array $already = []): array
    {
        $sector = isset($index['sectors'][$profile['sector'] ?? '']) ? $profile['sector'] : null;
        if (! $sector) {
            $text = Str::lower(Str::ascii(($profile['description'] ?? '').' '.($profile['offerings'] ?? '').' '.($profile['name'] ?? '')));
            $best = 0;
            foreach ($index['sectors'] as $key => $info) {
                $score = 0;
                foreach ((array) ($info['keywords'] ?? []) as $word) {
                    $score += substr_count($text, Str::lower(Str::ascii($word)));
                }
                if ($score > $best) {
                    [$best, $sector] = [$score, $key];
                }
            }
        }
        $lang = app()->getLocale() === 'en' ? 'en' : 'fr';
        $pool = array_values(array_filter($index['templates'], fn ($t) => ! $sector || $t['sector'] === $sector)) ?: $index['templates'];

        $items = $already;
        $styles = array_map(fn ($i) => collect($index['templates'])->firstWhere('id', $i['id'])['style']['hero'] ?? null, $items);
        foreach ([true, false] as $distinct) {
            foreach ($pool as $t) {
                if (count($items) >= 3) {
                    break 2;
                }
                if (in_array($t['id'], array_column($items, 'id'), true) || ($distinct && in_array($t['style']['hero'] ?? null, $styles, true))) {
                    continue;
                }
                $items[] = ['id' => $t['id'], 'reason' => $t['description'][$lang] ?? ''];
                $styles[] = $t['style']['hero'] ?? null;
            }
        }

        return ['sector' => $sector, 'items' => $items];
    }
}
