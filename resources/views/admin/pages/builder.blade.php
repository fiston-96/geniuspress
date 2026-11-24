<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ __('Builder') }} · {{ $page->title }}</title>
    @include('partials.brand-head')
    @vite(['resources/css/app.css', 'resources/js/builder/main.jsx'])
    {{-- Scripts des plugins actifs : ils déclarent leurs widgets via window.GeniusPress. --}}
    @foreach (app(\App\Plugins\PluginManager::class)->assets('builder') as $src)
        <script type="module" src="{{ $src }}"></script>
    @endforeach
</head>
<body class="overflow-hidden antialiased">
    <div id="builder"></div>
    <script type="application/json" id="gp-boot">{!! json_encode([
        'page' => $page->only(['id', 'title', 'slug', 'locale', 'status']) + [
            'content' => $page->editableContent(),
            'has_draft' => $page->hasUnpublishedChanges(),
        ],
        'revisions' => $page->revisions->map->only(['id', 'note', 'created_at', 'user']),
        'settings' => $settings,
        'siteCss' => \Illuminate\Support\Facades\Vite::asset('resources/css/site.css'),
        'pluginCss' => app(\App\Plugins\PluginManager::class)->assets('site_css'),
        'csrf' => csrf_token(),
        // Textes de l'interface dans la langue de l'utilisateur (cœur + plugins).
        'i18n' => app('translator')->getLoader()->load(app()->getLocale(), '*', '*'),
        'urls' => [
            'back' => route('admin.pages.index'),
            'settings' => route('admin.pages.edit', $page),
            'content' => route('admin.pages.content', $page),
            'preview' => route('admin.pages.preview', $page),
            'restore' => route('admin.pages.revisions.restore', [$page, '__ID__']),
            'media' => route('admin.media.index'),
            'aiText' => route('admin.ai.text'),
            'aiGenerate' => route('admin.ai.generate', $page),
            'aiTranslate' => route('admin.ai.translate', $page),
            'aiAudit' => route('admin.ai.audit', $page),
            'meta' => route('admin.pages.meta', $page),
            'aiSettings' => auth()->user()->isAdmin() ? route('admin.settings.edit') : null,
        ],
        'ai' => [
            'configured' => app(\App\Ai\LlmGateway::class)->isConfigured(),
        ],
        'meta' => $page->only(['meta_title', 'meta_description']),
        'widgets' => collect(app(\App\Builder\WidgetRegistry::class)->all())
            ->map(fn ($w, $type) => ['label' => $w->label(), 'rules' => $w->rules()]),
        'locales' => collect((array) $settings['locales'])
            ->mapWithKeys(fn ($code) => [$code => \App\Http\Controllers\Admin\SettingController::AVAILABLE_LOCALES[$code] ?? $code]),
        'translations' => $page->translations()->get(['id', 'locale', 'title', 'status'])
            ->map(fn ($t) => $t->only(['id', 'locale', 'title', 'status']) + ['builder_url' => route('admin.pages.builder', $t)]),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
</body>
</html>
