<?php

namespace App\Transfer;

use App\Builder\ContentSanitizer;
use App\Http\Controllers\Admin\SettingController;
use App\Models\Layout;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use App\Site\ThemeLayouts;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Importe une archive créée par SiteExporter.
 *
 * Sécurité : seuls les fichiers attendus sont lus (jamais d'extraction brute),
 * les médias sont contrôlés comme lors d'un envoi normal, et tout contenu de
 * page passe par ContentSanitizer.
 *
 * Modes :
 *  - fusion (par défaut) : les pages importées s'ajoutent ; une adresse déjà
 *    prise reçoit un suffixe (-2, -3…), la page d'accueil existante est gardée ;
 *  - remplacement : une page de même langue et même adresse est écrasée, et la
 *    page d'accueil importée devient celle du site.
 */
class SiteImporter
{
    private const MEDIA_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf'];
    private const MAX_JSON_BYTES = 20 * 1024 * 1024;
    private const MAX_MEDIA_BYTES = 20 * 1024 * 1024;
    private const MAX_PAGES = 5000;
    private const MAX_MEDIA = 10000;

    private const SETTING_RULES = [
        'site_name' => 'string:120',
        'site_tagline' => 'string:255',
        'organization_description' => 'string:3000',
        'primary_color' => 'color',
        'secondary_color' => 'color',
        'accent_color' => 'color',
    ];

    public function __construct(private ContentSanitizer $sanitizer) {}

    /**
     * @param  list<string>|null  $locales  langues à importer (null = toutes) ; sert à installer un modèle dans une seule langue
     * @return array{pages: int, posts: int, terms: int, layouts: int, media: int, settings: bool, warnings: list<string>, page_ids: list<int>}
     */
    public function import(string $zipPath, ?User $author = null, bool $replace = false, bool $withSettings = true, ?array $locales = null): array
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException(__('Ce fichier n’est pas une archive zip valide.'));
        }

        try {
            $manifest = $this->json($zip, 'manifest.json');
            if (($manifest['format'] ?? null) !== SiteExporter::FORMAT) {
                throw new RuntimeException(__('Cette archive n’est pas un export de site GeniusPress.'));
            }
            if ((int) ($manifest['format_version'] ?? 0) > SiteExporter::FORMAT_VERSION) {
                throw new RuntimeException(__('Cette archive vient d’une version plus récente de GeniusPress. Mettez GeniusPress à jour avant de l’importer.'));
            }

            $report = ['pages' => 0, 'posts' => 0, 'terms' => 0, 'layouts' => 0, 'media' => 0, 'settings' => false, 'warnings' => [], 'page_ids' => []];
            $pages = array_slice((array) $this->json($zip, 'pages.json'), 0, self::MAX_PAGES);
            if ($locales !== null) {
                $pages = array_values(array_filter($pages, fn ($p) => is_array($p) && in_array($p['locale'] ?? null, $locales, true)));
            }
            $mediaList = array_slice((array) $this->json($zip, 'media.json', []), 0, self::MAX_MEDIA);
            $settings = (array) $this->json($zip, 'settings.json', []);
            // Articles, catégories et gabarits : absents des archives plus anciennes.
            $inLocales = fn ($rows) => $locales === null ? $rows : array_values(array_filter($rows, fn ($r) => is_array($r) && in_array($r['locale'] ?? null, $locales, true)));
            $terms = $inLocales(array_slice((array) $this->json($zip, 'terms.json', []), 0, self::MAX_PAGES));
            $posts = $inLocales(array_slice((array) $this->json($zip, 'posts.json', []), 0, self::MAX_PAGES));
            $layouts = array_slice((array) $this->json($zip, 'layouts.json', []), 0, 200);
            if ($locales !== null) {
                $layouts = array_values(array_filter($layouts, fn ($l) => is_array($l) && (empty($l['locale']) || in_array($l['locale'], $locales, true))));
            }
            if ($locales !== null && isset($settings['locales'])) {
                $settings['locales'] = array_values(array_intersect((array) $settings['locales'], $locales));
            }

            // Les fichiers sont copiés avant la transaction ; supprimés si elle échoue.
            [$mediaIds, $urlMap, $created] = $this->importMedia($zip, $mediaList, $manifest, $author, $report);

            try {
                DB::transaction(function () use ($pages, $terms, $posts, $layouts, $settings, $withSettings, $replace, $author, $mediaIds, $urlMap, &$report) {
                    if ($withSettings) {
                        $this->importSettings($settings, $replace);
                        $report['settings'] = true;
                    }
                    $pageMap = $this->importPages($pages, $replace, $author, $mediaIds, $urlMap, $report);
                    $termMap = $this->importTerms($terms, $replace, $report);
                    $postMap = $this->importPosts($posts, $replace, $author, $mediaIds, $urlMap, $termMap, $report);
                    $this->importLayouts($layouts, $replace, $mediaIds, $urlMap, ['page' => $pageMap, 'post' => $postMap, 'term' => $termMap], $report);
                });
            } catch (\Throwable $e) {
                foreach ($created as $m) {
                    Storage::disk($m->disk)->delete($m->path);
                }
                throw $e;
            }

            Setting::write([]); // vide le cache des réglages

            return $report;
        } finally {
            $zip->close();
        }
    }

    private function json(ZipArchive $zip, string $name, mixed $default = null): mixed
    {
        $stat = $zip->statName($name);
        if ($stat === false) {
            if ($default !== null) {
                return $default;
            }
            throw new RuntimeException(__('Fichier :name manquant dans l’archive.', ['name' => $name]));
        }
        if ($stat['size'] > self::MAX_JSON_BYTES) {
            throw new RuntimeException(__('Fichier :name trop volumineux.', ['name' => $name]));
        }
        $data = json_decode((string) $zip->getFromName($name), true);
        if (! is_array($data)) {
            throw new RuntimeException(__('Fichier :name illisible dans l’archive.', ['name' => $name]));
        }

        return $data;
    }

    /** @return array{0: array<int,int>, 1: array<string,string>, 2: list<Media>} */
    private function importMedia(ZipArchive $zip, array $list, array $manifest, ?User $author, array &$report): array
    {
        $ids = [];
        $urls = [];
        $created = [];
        $oldStorage = rtrim((string) ($manifest['storage_url'] ?? ''), '/');

        foreach ($list as $item) {
            if (! is_array($item) || ! isset($item['id'], $item['file'], $item['path'])) {
                continue;
            }
            $entry = (string) $item['file'];
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            // Uniquement media/<nom de fichier>, jamais de sous-dossier ni de « .. ».
            if (! preg_match('#^media/[A-Za-z0-9._-]+$#', $entry) || ! in_array($ext, self::MEDIA_TYPES, true)) {
                $report['warnings'][] = __('Média ignoré (nom ou type non autorisé) : :name', ['name' => Str::limit($entry, 80)]);
                continue;
            }
            $stat = $zip->statName($entry);
            if ($stat === false || $stat['size'] > self::MAX_MEDIA_BYTES) {
                $report['warnings'][] = __('Média ignoré (absent ou trop volumineux) : :name', ['name' => $entry]);
                continue;
            }
            $data = (string) $zip->getFromName($entry);
            if ($ext === 'svg' && preg_match('/<script|on\w+\s*=|javascript:/i', $data)) {
                $report['warnings'][] = __('SVG refusé car il contient du code : :name', ['name' => $entry]);
                continue;
            }

            $name = Str::slug(pathinfo((string) ($item['original_name'] ?? 'fichier'), PATHINFO_FILENAME)) ?: 'fichier';
            $path = 'uploads/'.now()->format('Y/m').'/'.$name.'-'.Str::lower(Str::random(6)).'.'.$ext;
            Storage::disk('public')->put($path, $data);
            $size = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? @getimagesizefromstring($data) : false;

            $media = Media::create([
                'user_id' => $author?->id,
                'disk' => 'public',
                'path' => $path,
                'original_name' => Str::limit((string) ($item['original_name'] ?? basename($path)), 250, ''),
                'mime_type' => (new \finfo(FILEINFO_MIME_TYPE))->buffer($data) ?: 'application/octet-stream',
                'size' => strlen($data),
                'width' => $size ? $size[0] : null,
                'height' => $size ? $size[1] : null,
                'alt' => isset($item['alt']) ? Str::limit((string) $item['alt'], 300, '') : null,
            ]);
            $created[] = $media;
            $ids[(int) $item['id']] = $media->id;

            // Adresses de l'ancien fichier telles qu'elles apparaissent dans le contenu des pages.
            $old = ltrim((string) $item['path'], '/');
            if ($oldStorage !== '') {
                $urls[$oldStorage.'/'.$old] = $media->url;
            }
            $urls['/storage/'.$old] = parse_url($media->url, PHP_URL_PATH) ?: $media->url;
            $report['media']++;
        }

        // Les adresses les plus longues d'abord, pour ne jamais remplacer une partie d'adresse.
        uksort($urls, fn ($a, $b) => strlen($b) <=> strlen($a));

        return [$ids, $urls, $created];
    }

    private function importSettings(array $settings, bool $replace): void
    {
        $values = [];
        foreach (self::SETTING_RULES as $key => $rule) {
            if (! isset($settings[$key]) || ! is_string($settings[$key])) {
                continue;
            }
            $v = trim($settings[$key]);
            if ($rule === 'color') {
                if (preg_match('/^#[0-9a-fA-F]{6}$/', $v)) {
                    $values[$key] = strtolower($v);
                }
            } else {
                $values[$key] = Str::limit($v, (int) explode(':', $rule)[1], '');
            }
        }
        if (in_array($settings['font_family'] ?? null, SettingController::FONTS, true)) {
            $values['font_family'] = $settings['font_family'];
        }
        if (in_array($settings['heading_font'] ?? null, SettingController::FONTS, true)) {
            $values['heading_font'] = $settings['heading_font'];
        }
        if (in_array($settings['page_transition'] ?? null, \App\Builder\DesignTokens::PAGE_TRANSITIONS, true)) {
            $values['page_transition'] = $settings['page_transition'];
            $values['page_transition_duration'] = max(150, min(1500, (int) ($settings['page_transition_duration'] ?? 400)));
        }
        // Image de partage par défaut (les codes de vérification restent propres à chaque site).
        if (is_string($settings['og_image'] ?? null) && preg_match('#^(https?://|/(?!/))#', $settings['og_image'])) {
            $values['og_image'] = Str::limit($settings['og_image'], 2000, '');
        }
        if (is_array($settings['custom_colors'] ?? null)) {
            $values['custom_colors'] = \App\Builder\DesignTokens::customColors($settings['custom_colors']);
        }
        if (is_string($settings['logo_url'] ?? null) && preg_match('#^(https?://|/(?!/))#', $settings['logo_url'])) {
            $values['logo_url'] = Str::limit($settings['logo_url'], 2000, '');
        }

        $imported = array_values(array_intersect((array) ($settings['locales'] ?? []), array_keys(SettingController::AVAILABLE_LOCALES)));
        if ($imported) {
            $current = (array) Setting::read('locales', ['fr']);
            $values['locales'] = $replace
                ? array_values(array_unique([...$imported, ...$current]))
                : array_values(array_unique([...$current, ...$imported]));
        }

        // Blog : titre et adresse par langue, articles par page.
        if (is_array($settings['blog'] ?? null)) {
            $blog = ['locales' => [], 'per_page' => max(1, min(48, (int) ($settings['blog']['per_page'] ?? 9)))];
            foreach ((array) ($settings['blog']['locales'] ?? []) as $code => $row) {
                if (! array_key_exists((string) $code, SettingController::AVAILABLE_LOCALES) || ! is_array($row)) {
                    continue;
                }
                $base = Post::normalizeSlug((string) ($row['base'] ?? ''));
                $blog['locales'][$code] = array_filter([
                    'title' => Str::limit(trim(strip_tags((string) ($row['title'] ?? ''))), 60, ''),
                    'base' => $base !== '' && ! Page::isReservedSlug($base) ? $base : '',
                ]);
            }
            $values['blog'] = $blog;
        }

        if ($favicon = \App\Builder\ContentSanitizer::url($settings['favicon_url'] ?? null)) {
            $values['favicon_url'] = $favicon;
        }

        Setting::write($values);

        // En-tête, menus et pied de page : revalidés comme s'ils venaient du formulaire « Apparence ».
        if (is_array($settings['navigation'] ?? null)) {
            \App\Site\Navigation::save($settings['navigation'], (array) Setting::read('locales', ['fr']));
        }
    }

    /** @return array<int, int> anciens identifiants de pages → nouveaux */
    private function importPages(array $pages, bool $replace, ?User $author, array $mediaIds, array $urlMap, array &$report): array
    {
        $groups = [];
        $map = [];
        $locales = (array) Setting::read('locales', ['fr']);

        foreach ($pages as $item) {
            if (! is_array($item) || ! is_string($item['title'] ?? null)) {
                continue;
            }
            $locale = (string) ($item['locale'] ?? '');
            if (! array_key_exists($locale, SettingController::AVAILABLE_LOCALES)) {
                $report['warnings'][] = __('Page ignorée (langue inconnue) : :title', ['title' => Str::limit($item['title'], 60)]);
                continue;
            }
            if (! in_array($locale, $locales, true)) {
                $locales[] = $locale;
                Setting::write(['locales' => $locales]);
            }

            $slug = Page::normalizeSlug((string) ($item['slug'] ?? $item['title'])) ?: 'page';
            if (Page::isReservedSlug($slug)) {
                $slug = 'page-'.$slug;
            }

            $existing = $replace ? Page::withTrashed()->where('locale', $locale)->where('slug', $slug)->first() : null;
            $oldGroup = (string) ($item['translation_group'] ?? Str::uuid());
            $groups[$oldGroup] ??= $existing?->translation_group ?? (string) Str::uuid();

            $page = $existing ?? new Page(['locale' => $locale, 'slug' => $this->freeSlug($slug, $locale)]);
            if ($existing?->trashed()) {
                $existing->restore();
            }

            $isHome = (bool) ($item['is_home'] ?? false);
            if ($isHome) {
                $homeExists = Page::query()->where('locale', $locale)->where('is_home', true)->where('id', '!=', $page->id ?? 0)->exists();
                if ($replace) {
                    Page::query()->where('locale', $locale)->where('id', '!=', $page->id ?? 0)->update(['is_home' => false]);
                } elseif ($homeExists) {
                    $isHome = false; // On garde la page d'accueil actuelle du site.
                }
            }

            $page->fill([
                'title' => Str::limit($item['title'], 190, ''),
                'translation_group' => $groups[$oldGroup],
                'status' => ($item['status'] ?? null) === Page::STATUS_PUBLISHED ? Page::STATUS_PUBLISHED : Page::STATUS_DRAFT,
                'is_home' => $isHome,
                'content' => $this->sanitizer->sanitize($this->remap($item['content'] ?? [], $mediaIds, $urlMap)),
                'draft_content' => isset($item['draft_content']) ? $this->sanitizer->sanitize($this->remap($item['draft_content'], $mediaIds, $urlMap)) : null,
                'meta_title' => isset($item['meta_title']) ? Str::limit((string) $item['meta_title'], 190, '') : null,
                'meta_description' => isset($item['meta_description']) ? Str::limit((string) $item['meta_description'], 320, '') : null,
                'published_at' => ! empty($item['published_at']) ? now()->parse($item['published_at']) : null,
            ]);
            $page->author_id ??= $author?->id;
            $page->save();
            $report['pages']++;
            $report['page_ids'][] = $page->id;
            if (isset($item['id']) && is_numeric($item['id'])) {
                $map[(int) $item['id']] = $page->id;
            }
        }

        return $map;
    }

    /** Langue connue d'un élément importé (ajoutée aux langues du site si besoin), ou null. */
    private function locale(mixed $locale): ?string
    {
        $locale = (string) $locale;
        if (! array_key_exists($locale, SettingController::AVAILABLE_LOCALES)) {
            return null;
        }
        $locales = (array) Setting::read('locales', ['fr']);
        if (! in_array($locale, $locales, true)) {
            Setting::write(['locales' => [...$locales, $locale]]);
        }

        return $locale;
    }

    /** Catégories et étiquettes : une catégorie de même langue et même adresse est réutilisée. @return array<int, int> */
    private function importTerms(array $terms, bool $replace, array &$report): array
    {
        $map = [];
        $parents = [];
        $groups = [];
        foreach ($terms as $item) {
            if (! is_array($item) || ! in_array($item['taxonomy'] ?? null, Term::TAXONOMIES, true) || ! is_string($item['name'] ?? null) || ! ($locale = $this->locale($item['locale'] ?? null))) {
                continue;
            }
            $slug = Post::normalizeSlug((string) ($item['slug'] ?? $item['name'])) ?: 'categorie';
            $oldGroup = (string) ($item['translation_group'] ?? Str::uuid());
            $term = Term::query()->where(['taxonomy' => $item['taxonomy'], 'locale' => $locale, 'slug' => $slug])->first();
            $groups[$oldGroup] ??= $term?->translation_group ?? (string) Str::uuid();
            if (! $term || $replace) {
                $term ??= new Term(['taxonomy' => $item['taxonomy'], 'locale' => $locale, 'slug' => $slug]);
                $term->fill([
                    'name' => Str::limit(strip_tags($item['name']), 120, ''),
                    'description' => isset($item['description']) ? Str::limit(strip_tags((string) $item['description']), 1000, '') : null,
                    'translation_group' => $groups[$oldGroup],
                ])->save();
                $report['terms']++;
            }
            if (isset($item['id']) && is_numeric($item['id'])) {
                $map[(int) $item['id']] = $term->id;
                if (! empty($item['parent_id'])) {
                    $parents[$term->id] = (int) $item['parent_id'];
                }
            }
        }
        foreach ($parents as $id => $oldParent) {
            if (isset($map[$oldParent]) && $map[$oldParent] !== $id) {
                Term::query()->whereKey($id)->update(['parent_id' => $map[$oldParent]]);
            }
        }

        return $map;
    }

    /** Articles : même logique que les pages (remplacement ou adresse libre), catégories et image à la une remappées. @return array<int, int> */
    private function importPosts(array $posts, bool $replace, ?User $author, array $mediaIds, array $urlMap, array $termMap, array &$report): array
    {
        $map = [];
        $groups = [];
        foreach ($posts as $item) {
            if (! is_array($item) || ! is_string($item['title'] ?? null) || ! ($locale = $this->locale($item['locale'] ?? null))) {
                continue;
            }
            $slug = Post::normalizeSlug((string) ($item['slug'] ?? $item['title'])) ?: 'article';
            if (in_array($slug, Post::RESERVED_SLUGS, true)) {
                $slug = 'article-'.$slug;
            }
            $existing = $replace ? Post::withTrashed()->where('locale', $locale)->where('slug', $slug)->first() : null;
            if ($existing?->trashed()) {
                $existing->restore();
            }
            $oldGroup = (string) ($item['translation_group'] ?? Str::uuid());
            $groups[$oldGroup] ??= $existing?->translation_group ?? (string) Str::uuid();
            $post = $existing ?? new Post(['locale' => $locale, 'slug' => $this->freePostSlug($slug, $locale)]);
            $post->fill([
                'title' => Str::limit($item['title'], 190, ''),
                'translation_group' => $groups[$oldGroup],
                'status' => ($item['status'] ?? null) === Post::STATUS_PUBLISHED ? Post::STATUS_PUBLISHED : Post::STATUS_DRAFT,
                'content' => $this->sanitizer->sanitize($this->remap($item['content'] ?? [], $mediaIds, $urlMap)),
                'draft_content' => isset($item['draft_content']) ? $this->sanitizer->sanitize($this->remap($item['draft_content'], $mediaIds, $urlMap)) : null,
                'excerpt' => isset($item['excerpt']) ? Str::limit(strip_tags((string) $item['excerpt']), 500, '') : null,
                'featured_media_id' => isset($item['featured_media_id']) ? ($mediaIds[(int) $item['featured_media_id']] ?? null) : null,
                'meta_title' => isset($item['meta_title']) ? Str::limit((string) $item['meta_title'], 190, '') : null,
                'meta_description' => isset($item['meta_description']) ? Str::limit((string) $item['meta_description'], 320, '') : null,
                'published_at' => ! empty($item['published_at']) ? now()->parse($item['published_at']) : null,
            ]);
            $post->author_id ??= $author?->id;
            $post->save();
            $post->terms()->sync(array_values(array_unique(array_filter(array_map(fn ($id) => $termMap[(int) $id] ?? null, (array) ($item['terms'] ?? []))))));
            $report['posts']++;
            if (isset($item['id']) && is_numeric($item['id'])) {
                $map[(int) $item['id']] = $post->id;
            }
        }

        return $map;
    }

    /**
     * Gabarits du thème. En remplacement, un gabarit de même type et même nom est écrasé.
     * Les conditions qui visent une page, un article ou une catégorie sont remappées ;
     * celles dont la cible n'a pas été importée sont retirées.
     */
    private function importLayouts(array $layouts, bool $replace, array $mediaIds, array $urlMap, array $maps, array &$report): void
    {
        foreach ($layouts as $item) {
            if (! is_array($item) || ! in_array($item['type'] ?? null, Layout::TYPES, true) || ! is_string($item['name'] ?? null)) {
                continue;
            }
            $locale = empty($item['locale']) ? null : $this->locale($item['locale']);
            $conditions = [];
            foreach (ThemeLayouts::sanitizeConditions($item['conditions'] ?? []) as $rule) {
                if (isset($rule['id'])) {
                    $map = $maps[match ($rule['target']) { 'page' => 'page', 'post' => 'post', default => 'term' }];
                    if (! isset($map[$rule['id']])) {
                        continue;
                    }
                    $rule['id'] = $map[$rule['id']];
                }
                $conditions[] = $rule;
            }
            $name = Str::limit(strip_tags($item['name']), 120, '');
            $layout = ($replace ? Layout::query()->where('type', $item['type'])->where('name', $name)->first() : null) ?? new Layout(['type' => $item['type']]);
            $layout->fill([
                'name' => $name,
                'locale' => $locale,
                'status' => ($item['status'] ?? null) === Layout::STATUS_PUBLISHED ? Layout::STATUS_PUBLISHED : Layout::STATUS_DRAFT,
                'priority' => max(-99, min(99, (int) ($item['priority'] ?? 0))),
                'conditions' => $conditions,
                // Réglages d'une popup : seulement les valeurs connues et valides.
                'options' => $item['type'] === 'popup' ? \App\Site\Popups::sanitize($item['options'] ?? []) : null,
                'content' => $this->sanitizer->sanitize($this->remap($item['content'] ?? [], $mediaIds, $urlMap)),
                'draft_content' => isset($item['draft_content']) ? $this->sanitizer->sanitize($this->remap($item['draft_content'], $mediaIds, $urlMap)) : null,
            ])->save();
            $report['layouts']++;
        }
    }

    private function freePostSlug(string $slug, string $locale): string
    {
        $candidate = $slug;
        $i = 2;
        while (Post::withTrashed()->where('locale', $locale)->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$i++;
        }

        return $candidate;
    }

    /** Remplace les anciens identifiants et adresses de médias par les nouveaux, partout dans le contenu. */
    private function remap(mixed $value, array $mediaIds, array $urlMap, ?string $key = null): mixed
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->remap($v, $mediaIds, $urlMap, is_string($k) ? $k : null);
            }

            return $value;
        }
        if ($key === 'media_id' && is_numeric($value)) {
            return $mediaIds[(int) $value] ?? null;
        }
        if (is_string($value) && $urlMap) {
            return strtr($value, $urlMap);
        }

        return $value;
    }

    private function freeSlug(string $slug, string $locale): string
    {
        $candidate = $slug;
        $i = 2;
        while (Page::withTrashed()->where('locale', $locale)->where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$i++;
        }

        return $candidate;
    }
}
