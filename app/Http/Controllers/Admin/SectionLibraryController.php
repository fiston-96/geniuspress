<?php

namespace App\Http\Controllers\Admin;

use App\Builder\ContentSanitizer;
use App\Builder\DesignTokens;
use App\Builder\Renderer;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Templates\SectionLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Bibliothèque de sections du builder : liste, aperçus rendus, insertion (photos copiées dans la médiathèque). */
class SectionLibraryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'categories' => SectionLibrary::categoryLabels(),
            'sections' => SectionLibrary::index($this->locale($request)),
            // Change avec la bibliothèque et les couleurs du site : les aperçus en cache sont renouvelés.
            'version' => SectionLibrary::version().'-'.substr(md5(json_encode(Setting::publicValues())), 0, 8),
        ]);
    }

    /** Aperçu d'une section aux couleurs et polices du site (affiché en vignette dans le builder). */
    public function preview(Request $request, string $section)
    {
        $found = SectionLibrary::find($section, $this->locale($request));
        abort_unless($found, 404);
        $rendered = app(Renderer::class)->render(['version' => 2, 'elements' => [SectionLibrary::previewNode($found['node'])]]);
        // Photos pas encore copiées dans la médiathèque : servies par la bibliothèque.
        $photo = fn (string $html) => preg_replace_callback('#/storage/templates/([a-z0-9-]{2,80})\.(?:jpg|svg)#', fn ($m) => route('admin.builder.sections.photo', $m[1]), $html);
        $settings = Setting::publicValues();

        return response()->view('admin.section-preview', [
            'html' => $photo($rendered['html']),
            'css' => $photo($rendered['css']),
            'designCss' => DesignTokens::css($settings),
            'fontsUrl' => DesignTokens::fontsUrl($settings, $rendered['fonts']),
        ])->header('Cache-Control', 'private, max-age=600');
    }

    public function photo(string $slot)
    {
        abort_unless(preg_match('/^[a-z0-9-]{2,80}$/', $slot), 404);
        $photo = SectionLibrary::previewPhoto($slot);

        return response($photo['data'], 200, ['Content-Type' => $photo['type'], 'Cache-Control' => 'private, max-age=86400', 'X-Content-Type-Options' => 'nosniff']);
    }

    /** Section prête à insérer, comme tout contenu entrant : repassée par le sanitizer. */
    public function insert(Request $request, string $section, ContentSanitizer $sanitizer): JsonResponse
    {
        $found = SectionLibrary::find($section, $this->locale($request));
        abort_unless($found, 404);
        $node = SectionLibrary::insert($found, $request->user()->id);
        $clean = $sanitizer->sanitize(['version' => 2, 'elements' => [$node]])['elements'];
        abort_if(count($clean) !== 1, 422, __('Cette section ne peut pas être insérée.'));

        return response()->json(['node' => $clean[0]]);
    }

    private function locale(Request $request): string
    {
        return $request->query('locale') === 'fr' ? 'fr' : 'en';
    }
}
