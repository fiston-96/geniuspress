<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>Builder · {{ $page->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/builder/main.jsx'])
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
        'csrf' => csrf_token(),
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
        'locales' => collect((array) $settings['locales'])
            ->mapWithKeys(fn ($code) => [$code => \App\Http\Controllers\Admin\SettingController::AVAILABLE_LOCALES[$code] ?? $code]),
        'translations' => $page->translations()->get(['id', 'locale', 'title', 'status'])
            ->map(fn ($t) => $t->only(['id', 'locale', 'title', 'status']) + ['builder_url' => route('admin.pages.builder', $t)]),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
</body>
</html>
