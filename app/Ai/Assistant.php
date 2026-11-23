<?php

namespace App\Ai;

use App\Builder\ContentSanitizer;
use App\Builder\Schema;
use App\Builder\WidgetRegistry;
use App\Http\Controllers\Admin\SettingController;
use App\Models\AiRequest;
use App\Models\Media;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Assistant IA de GeniusPress : génération de pages, rédaction, traduction,
 * conseils SEO et accessibilité. Tout contenu produit passe par
 * ContentSanitizer avant d'atteindre le builder ou la base.
 */
class Assistant
{
    public const TEXT_ACTIONS = [
        'write' => 'Write new text for this element from the instructions.',
        'improve' => 'Improve the text: clearer, more engaging, better structured. Keep the meaning and roughly the same length.',
        'shorten' => 'Make the text noticeably shorter and punchier while keeping the key message.',
        'expand' => 'Expand the text with useful, concrete details. Do not invent facts, figures, names or contacts.',
        'fix' => 'Fix spelling, grammar and punctuation only. Change nothing else.',
        'formal' => 'Rewrite in a professional, institutional tone suitable for partners and donors.',
        'friendly' => 'Rewrite in a warm, simple and friendly tone suitable for the general public.',
    ];

    public function __construct(private LlmGateway $llm, private ContentSanitizer $sanitizer, private WidgetRegistry $widgets) {}

    public function isConfigured(): bool
    {
        return $this->llm->isConfigured();
    }

    // ─── Génération de page ──────────────────────────────────────────────

    /** @return array{content: array, meta_title: ?string, meta_description: ?string} */
    public function generatePage(Page $page, string $brief): array
    {
        $system = $this->baseSystem($page->locale).<<<'TXT'

        You design web pages for the GeniusPress page builder. A page is a list of sections; each section has 1 to 4 columns; each column holds widgets.

        Design rules:
        - Build a complete, persuasive page of 4 to 8 sections that follows a clear story: hero (h1 + short pitch + main call to action), then the sections the brief needs (about/mission, services or programs, impact or key figures, testimonials, how to help/donate or pricing, FAQ, contact/final call to action).
        - Exactly one h1 per page, in the first section. Use h2 for section titles and h3 for card titles.
        - Use multi-column sections for cards (2–4 columns), each column with a heading + text (+ optional button).
        - Alternate section backgrounds for rhythm (white, a very light tint, and one or two dark or brand-coloured sections with light text_color). Ensure strong text/background contrast (WCAG AA).
        - Use the site palette given below for buttons and accents. Colours are 6-digit hex codes.
        - Set the section "anchor" setting for sections that buttons link to (for example "contact").
        - Never invent precise facts (figures, names, addresses, phone numbers, emails, dates, testimonials attributed to real people). Use clearly marked placeholders in square brackets, e.g. "[nombre de bénéficiaires]" or "[adresse e-mail]".
        - Set every setting you do not need to null. Prefer sensible defaults over over-styling.

        Also return an SEO meta title (max 60 characters) and meta description (120–160 characters) for the page.
        TXT;
        $system .= "\n\nAvailable widgets:\n".$this->widgetGuide();

        $content = [['type' => 'text', 'text' => "Page title: {$page->title}\n\nBrief from the user:\n".Str::limit($brief, 4000)]];

        $result = $this->call('generate_page', $page, $system, $content, [
            'type' => 'object',
            'properties' => [
                'meta_title' => ['type' => 'string'],
                'meta_description' => ['type' => 'string'],
                'sections' => ['type' => 'array', 'items' => $this->sectionSchema()],
            ],
            'required' => ['meta_title', 'meta_description', 'sections'],
            'additionalProperties' => false,
        ], 'medium', 32000);

        return [
            'content' => $this->sanitizer->sanitize(['sections' => $this->withIds($result['sections'] ?? [])]),
            'meta_title' => Str::limit((string) ($result['meta_title'] ?? ''), 190, '') ?: null,
            'meta_description' => Str::limit((string) ($result['meta_description'] ?? ''), 320, '') ?: null,
        ];
    }

    // ─── Rédaction ───────────────────────────────────────────────────────

    /** Rédige ou retravaille le texte d'un widget. $format : 'plain' (titre, bouton) ou 'html' (bloc texte). */
    public function rewrite(?Page $page, string $action, string $format, string $current, string $instructions = '', string $context = ''): string
    {
        $locale = $page?->locale ?? $this->defaultLocale();
        $task = self::TEXT_ACTIONS[$action] ?? throw new AiException('Action inconnue.');
        $formatRule = $format === 'html'
            ? 'Return simple HTML using only <p>, <strong>, <em>, <ul>, <ol>, <li> and <a href>. No headings, no inline styles.'
            : 'Return plain text only, on a single line, without quotes or markdown. Keep it short (a heading or button label).';

        $system = $this->baseSystem($locale)."\n\nYou are an expert copywriter for websites of businesses and NGOs. {$formatRule} Never invent precise facts (figures, names, contacts, dates); use placeholders in square brackets instead.";

        $user = "Task: {$task}\n";
        if ($instructions !== '') {
            $user .= "Additional instructions from the user: ".Str::limit($instructions, 1000)."\n";
        }
        if ($context !== '') {
            $user .= "\nContext — other text on the page (for consistency, do not repeat it):\n".Str::limit($context, 3000)."\n";
        }
        $user .= "\nCurrent text:\n".($current !== '' ? Str::limit($current, 8000) : '(empty)');

        $result = $this->call('text_'.$action, $page, $system, [['type' => 'text', 'text' => $user]], [
            'type' => 'object',
            'properties' => ['text' => ['type' => 'string']],
            'required' => ['text'],
            'additionalProperties' => false,
        ], 'low', 8000);

        $text = trim((string) ($result['text'] ?? ''));

        return $format === 'html'
            ? $this->sanitizer->cleanHtml($text)
            : Str::limit(trim(strip_tags($text), " \"'«»"), 500, '');
    }

    // ─── Traduction ──────────────────────────────────────────────────────

    /** Crée (ou met à jour) la traduction d'une page dans une autre langue, en brouillon. */
    public function translatePage(Page $page, string $targetLocale, int $userId): Page
    {
        $source = $page->editableContent();
        $strings = $this->extractStrings($source);
        $strings['__title'] = $page->title;
        if ($page->meta_title) {
            $strings['__meta_title'] = $page->meta_title;
        }
        if ($page->meta_description) {
            $strings['__meta_description'] = $page->meta_description;
        }

        $target = $this->localeName($targetLocale);
        $system = $this->baseSystem($targetLocale)."\n\nYou are a professional translator for websites. Translate every item into {$target}. Keep HTML tags and attributes exactly as they are and translate only the visible text. Keep placeholders in square brackets, URLs, e-mail addresses and proper names unchanged. Adapt idioms naturally rather than word for word. Return every key exactly once.";

        $items = [];
        foreach ($strings as $key => $text) {
            $items[] = ['key' => $key, 'text' => $text];
        }

        $result = $this->call('translate', $page, $system, [[
            'type' => 'text',
            'text' => "Translate these items into {$target}:\n".json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ]], [
            'type' => 'object',
            'properties' => ['items' => ['type' => 'array', 'items' => [
                'type' => 'object',
                'properties' => ['key' => ['type' => 'string'], 'text' => ['type' => 'string']],
                'required' => ['key', 'text'],
                'additionalProperties' => false,
            ]]],
            'required' => ['items'],
            'additionalProperties' => false,
        ], 'medium', 32000);

        $translated = [];
        foreach ($result['items'] ?? [] as $item) {
            if (isset($item['key'], $item['text']) && array_key_exists($item['key'], $strings)) {
                $translated[$item['key']] = (string) $item['text'];
            }
        }

        $content = $this->sanitizer->sanitize($this->applyStrings($source, $translated));
        $title = Str::limit($translated['__title'] ?? $page->title, 190, '');

        $existing = Page::query()->where('translation_group', $page->translation_group)->where('locale', $targetLocale)->first();
        $translation = $existing ?? new Page([
            'translation_group' => $page->translation_group,
            'locale' => $targetLocale,
            'author_id' => $userId,
            'status' => Page::STATUS_DRAFT,
            'is_home' => $page->is_home,
            'slug' => $this->uniqueSlug($title, $targetLocale),
        ]);

        $translation->title = $title;
        $translation->meta_title = $translated['__meta_title'] ?? null;
        $translation->meta_description = $translated['__meta_description'] ?? null;
        // Une traduction déjà publiée garde sa version en ligne : la nouvelle version part en brouillon.
        if ($translation->exists && $translation->isPublished()) {
            $translation->draft_content = $content;
        } else {
            $translation->content = $content;
        }
        $translation->save();

        return $translation;
    }

    // ─── SEO et accessibilité ────────────────────────────────────────────

    public function audit(Page $page, array $content): array
    {
        $content = $this->sanitizer->sanitize($content);
        $outline = $this->outline($content);
        $images = $this->imagesForVision($content);

        $system = $this->baseSystem($page->locale).<<<'TXT'

        You audit web pages for SEO and accessibility (WCAG 2.2 AA) for non-technical users of businesses and NGOs.
        - Give a score from 0 to 100 and a two-sentence summary.
        - List concrete issues, most important first (max 12). Each fix must be an action the user can do in a visual page builder, in plain language, no code.
        - Check: one h1, logical heading order, meta title (≤ 60 characters) and description (120–160), clear calls to action, link texts, images without alt text, colour contrast between text_color/color and background colours, text length and readability, missing contact information, placeholders left in square brackets.
        - Propose an improved meta title and meta description.
        - For each image provided, propose a concise, descriptive alt text (max 125 characters) in the page language, keyed by its widget id.
        TXT;

        $blocks = [['type' => 'text', 'text' => sprintf(
            "Page title: %s\nURL path: /%s\nCurrent meta title: %s\nCurrent meta description: %s\n\nPage structure (JSON):\n%s",
            $page->title, $page->slug, $page->meta_title ?: '(none)', $page->meta_description ?: '(none)',
            json_encode($outline, JSON_UNESCAPED_UNICODE),
        )]];
        foreach ($images as $id => $image) {
            $blocks[] = ['type' => 'text', 'text' => "Image widget id: {$id}"];
            $blocks[] = $image;
        }

        $result = $this->call('audit', $page, $system, $blocks, [
            'type' => 'object',
            'properties' => [
                'score' => ['type' => 'integer'],
                'summary' => ['type' => 'string'],
                'meta_title' => ['type' => 'string'],
                'meta_description' => ['type' => 'string'],
                'issues' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => [
                        'severity' => ['type' => 'string', 'enum' => ['high', 'medium', 'low']],
                        'category' => ['type' => 'string', 'enum' => ['seo', 'accessibility', 'content']],
                        'message' => ['type' => 'string'],
                        'fix' => ['type' => 'string'],
                    ],
                    'required' => ['severity', 'category', 'message', 'fix'],
                    'additionalProperties' => false,
                ]],
                'image_alts' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => ['widget_id' => ['type' => 'string'], 'alt' => ['type' => 'string']],
                    'required' => ['widget_id', 'alt'],
                    'additionalProperties' => false,
                ]],
            ],
            'required' => ['score', 'summary', 'meta_title', 'meta_description', 'issues', 'image_alts'],
            'additionalProperties' => false,
        ], 'medium', 16000);

        $alts = [];
        foreach ($result['image_alts'] ?? [] as $a) {
            if (isset($images[$a['widget_id'] ?? null])) {
                $alts[] = ['widget_id' => $a['widget_id'], 'alt' => Str::limit(strip_tags((string) $a['alt']), 300, '')];
            }
        }

        return [
            'score' => max(0, min(100, (int) ($result['score'] ?? 0))),
            'summary' => (string) ($result['summary'] ?? ''),
            'meta_title' => Str::limit((string) ($result['meta_title'] ?? ''), 190, ''),
            'meta_description' => Str::limit((string) ($result['meta_description'] ?? ''), 320, ''),
            'issues' => array_slice(array_values(array_filter($result['issues'] ?? [], 'is_array')), 0, 12),
            'image_alts' => $alts,
        ];
    }

    // ─── Outils internes ─────────────────────────────────────────────────

    private function call(string $action, ?Page $page, string $system, array $content, array $schema, string $effort, int $maxTokens): array
    {
        if (! $this->llm->isConfigured()) {
            throw new AiException('L’assistant IA n’est pas configuré : ajoutez une clé API Anthropic dans Réglages.');
        }

        $started = microtime(true);
        $log =['user_id' => auth()->id(), 'page_id' => $page?->id, 'action' => $action, 'model' => (string) config('geniuspress.ai.model')];

        try {
            $response = $this->llm->json($system, $content, $schema, $effort, $maxTokens);
            AiRequest::create($log + [
                'model' => $response['model'] ?: $log['model'],
                'input_tokens' => $response['usage']['input'],
                'output_tokens' => $response['usage']['output'],
                'status' => 'ok',
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
            ]);

            return $response['data'];
        } catch (AiRefusedException $e) {
            AiRequest::create($log + ['input_tokens' => $e->usage['input'], 'output_tokens' => $e->usage['output'], 'status' => 'refused', 'duration_ms' => (int) ((microtime(true) - $started) * 1000)]);
            throw $e;
        } catch (Throwable $e) {
            AiRequest::create($log + ['status' => 'error', 'error' => Str::limit($e->getMessage(), 490), 'duration_ms' => (int) ((microtime(true) - $started) * 1000)]);
            throw $e instanceof AiException ? $e : new AiException('L’assistant IA a rencontré une erreur. Réessayez.', 0, $e);
        }
    }

    private function baseSystem(string $locale): string
    {
        $s = Setting::allValues();
        $org = trim((string) ($s['organization_description'] ?? ''));
        $lang = $this->localeName($locale);

        return "You are the AI assistant of GeniusPress, a website builder used by businesses and NGOs, especially in Africa.\n"
            ."Always write the website content in {$lang} ({$locale}).\n"
            ."Website name: {$s['site_name']}\n"
            .(! empty($s['site_tagline']) ? "Tagline: {$s['site_tagline']}\n" : '')
            .($org !== '' ? "About the organisation (provided by the site owner):\n".Str::limit($org, 3000)."\n" : '')
            ."Brand palette: primary {$s['primary_color']}, secondary {$s['secondary_color']}, accent {$s['accent_color']}.";
    }

    private function localeName(string $locale): string
    {
        return SettingController::AVAILABLE_LOCALES[$locale] ?? $locale;
    }

    private function defaultLocale(): string
    {
        return ((array) Setting::read('locales', ['fr']))[0] ?? 'fr';
    }

    /** Widgets proposés à l'IA : ceux du cœur et des plugins qui fournissent un conseil d'utilisation. */
    private function aiWidgets(): array
    {
        return array_filter($this->widgets->all(), fn ($w) => $w->aiHint() !== null);
    }

    private function widgetGuide(): string
    {
        $lines = [];
        foreach ($this->aiWidgets() as $type => $w) {
            $lines[] = "- {$type}: ".$w->aiHint();
        }

        return implode("\n", $lines);
    }

    /** Schéma JSON d'une section générée par l'IA, dérivé des règles de validation. */
    private function sectionSchema(): array
    {
        $skip = ['media_id', 'background_image', 'hide_desktop', 'hide_mobile', 'min_height'];

        $widgets = [];
        foreach ($this->aiWidgets() as $type => $widget) {
            $rules = $widget->rules() + ['margin_bottom' => Schema::COMMON_WIDGET['margin_bottom']];
            $rules = array_diff_key($rules, array_flip($widget->aiExcluded()));
            $widgets[] = [
                'type' => 'object',
                'properties' => ['type' => ['type' => 'string', 'enum' => [$type]], 'settings' => $this->settingsSchema($rules, $skip)],
                'required' => ['type', 'settings'],
                'additionalProperties' => false,
            ];
        }

        return [
            'type' => 'object',
            'properties' => [
                'settings' => $this->settingsSchema(Schema::SECTION, $skip),
                'columns' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => [
                        'settings' => $this->settingsSchema(Schema::COLUMN, $skip),
                        'widgets' => ['type' => 'array', 'items' => ['anyOf' => $widgets]],
                    ],
                    'required' => ['settings', 'widgets'],
                    'additionalProperties' => false,
                ]],
            ],
            'required' => ['settings', 'columns'],
            'additionalProperties' => false,
        ];
    }

    private function settingsSchema(array $rules, array $skip): array
    {
        $props = [];
        foreach ($rules as $key => $rule) {
            if (in_array($key, $skip, true)) {
                continue;
            }
            [$type, $args] = array_pad(explode(':', $rule, 2), 2, '');
            $props[$key] = match ($type) {
                'int' => ['type' => ['integer', 'null']],
                'bool' => ['type' => ['boolean', 'null']],
                'enum' => ['anyOf' => [['type' => 'string', 'enum' => explode(',', $args)], ['type' => 'null']]],
                default => ['type' => ['string', 'null']],
            };
        }

        return [
            'type' => 'object',
            'properties' => $props,
            'required' => array_keys($props),
            'additionalProperties' => false,
        ];
    }

    private function withIds(array $sections): array
    {
        $id = fn () => Str::lower(Str::random(10));
        foreach ($sections as &$s) {
            $s['id'] = $id();
            foreach ($s['columns'] ?? [] as &$c) {
                $c['id'] = $id();
                foreach ($c['widgets'] ?? [] as &$w) {
                    $w['id'] = $id();
                }
            }
        }

        return $sections;
    }

    /** Textes traduisibles de l'arbre, indexés par "idWidget.clé". */
    private function extractStrings(array $content): array
    {
        $out = [];
        foreach ($content['sections'] ?? [] as $s) {
            foreach ($s['columns'] as $c) {
                foreach ($c['widgets'] as $w) {
                    foreach ($this->widgets->get($w['type'])?->translatable() ?? [] as $key) {
                        if (! empty($w['settings'][$key])) {
                            $out[$w['id'].'.'.$key] = $w['settings'][$key];
                        }
                    }
                }
            }
        }

        return $out;
    }

    private function applyStrings(array $content, array $translated): array
    {
        foreach ($content['sections'] as &$s) {
            foreach ($s['columns'] as &$c) {
                foreach ($c['widgets'] as &$w) {
                    foreach ($w['settings'] as $key => $value) {
                        if (isset($translated[$w['id'].'.'.$key])) {
                            $w['settings'][$key] = $translated[$w['id'].'.'.$key];
                        }
                    }
                }
            }
        }

        return $content;
    }

    /** Version allégée de la page pour l'audit (structure, textes, couleurs). */
    private function outline(array $content): array
    {
        $out = [];
        foreach ($content['sections'] as $s) {
            $section = ['settings' => array_intersect_key($s['settings'], array_flip(['background_color', 'text_color', 'overlay_color', 'anchor'])), 'columns' => []];
            foreach ($s['columns'] as $c) {
                $col = ['background_color' => $c['settings']['background_color'] ?? null, 'widgets' => []];
                foreach ($c['widgets'] as $w) {
                    $o = $w['settings'];
                    $col['widgets'][] = array_filter([
                        'id' => $w['id'],
                        'type' => $w['type'],
                        'tag' => $o['tag'] ?? null,
                        'text' => isset($o['html']) ? Str::limit(trim(strip_tags($o['html'])), 600) : ($o['text'] ?? null),
                        'color' => $o['color'] ?? $o['bg_color'] ?? null,
                        'url' => $o['url'] ?? $o['link'] ?? null,
                        'alt' => $w['type'] === 'image' ? ($o['alt'] ?? '') : null,
                        'has_image' => $w['type'] === 'image' ? ! empty($o['url']) : null,
                    ], fn ($v) => $v !== null);
                }
                $section['columns'][] = $col;
            }
            $out[] = $section;
        }

        return $out;
    }

    /** Images de la médiathèque envoyées au modèle pour proposer des textes alternatifs (max 8). */
    private function imagesForVision(array $content): array
    {
        $ids = [];
        foreach ($content['sections'] as $s) {
            foreach ($s['columns'] as $c) {
                foreach ($c['widgets'] as $w) {
                    if ($w['type'] === 'image' && ! empty($w['settings']['media_id'])) {
                        $ids[$w['id']] = $w['settings']['media_id'];
                    }
                }
            }
        }

        $media = Media::query()->whereIn('id', array_values($ids))->get()->keyBy('id');
        $out = [];
        foreach (array_slice($ids, 0, 8, true) as $widgetId => $mediaId) {
            $m = $media[$mediaId] ?? null;
            if (! $m || ! in_array($m->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true) || $m->size > 4 * 1024 * 1024) {
                continue;
            }
            $data = Storage::disk($m->disk)->get($m->path);
            if ($data === null) {
                continue;
            }
            $out[$widgetId] = ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $m->mime_type, 'data' => base64_encode($data)]];
        }

        return $out;
    }

    private function uniqueSlug(string $title, string $locale): string
    {
        $base = Str::slug($title) ?: 'page';
        $slug = $base;
        $i = 2;
        while (Page::withTrashed()->where('locale', $locale)->where('slug', $slug)->exists() || in_array($slug, ['admin', 'install', 'storage', 'build', 'up'], true)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
