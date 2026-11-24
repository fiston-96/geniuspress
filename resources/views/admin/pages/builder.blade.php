@include('admin.builder', ['boot' => \App\Builder\BuilderBoot::make('page',
    $page->only(['id', 'title', 'slug', 'locale', 'status']) + [
        // Validé et converti au format actuel (les anciennes pages sont mises à jour ici).
        'content' => app(\App\Builder\ContentSanitizer::class)->sanitize($page->editableContent()),
        'has_draft' => $page->hasUnpublishedChanges(),
    ],
    $page->revisions->map->only(['id', 'note', 'created_at', 'user']),
    [
        'back' => route('admin.pages.index'),
        'settings' => route('admin.pages.edit', $page),
        'content' => route('admin.pages.content', $page),
        'preview' => route('admin.pages.preview', $page),
        'restore' => route('admin.pages.revisions.restore', [$page, '__ID__']),
        'aiGenerate' => route('admin.ai.generate', $page),
        'aiTranslate' => route('admin.ai.translate', $page),
        'aiAudit' => route('admin.ai.audit', $page),
        'meta' => route('admin.pages.meta', $page),
    ],
    $settings,
    [
        'meta' => $page->only(['meta_title', 'meta_description']),
        'translations' => $page->translations()->get(['id', 'locale', 'title', 'status'])
            ->map(fn ($t) => $t->only(['id', 'locale', 'title', 'status']) + ['builder_url' => route('admin.pages.builder', $t)]),
    ],
)])
