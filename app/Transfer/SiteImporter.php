<?php

namespace App\Transfer;

use App\Builder\ContentSanitizer;
use App\Http\Controllers\Admin\SettingController;
use App\Models\Media;
use App\Models\Page;
use App\Models\Setting;
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

    /** @return array{pages: int, media: int, settings: bool, warnings: list<string>} */
    public function import(string $zipPath, ?User $author = null, bool $replace = false, bool $withSettings = true): array
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

            $report = ['pages' => 0, 'media' => 0, 'settings' => false, 'warnings' => []];
            $pages = array_slice((array) $this->json($zip, 'pages.json'), 0, self::MAX_PAGES);
            $mediaList = array_slice((array) $this->json($zip, 'media.json', []), 0, self::MAX_MEDIA);
            $settings = (array) $this->json($zip, 'settings.json', []);

            // Les fichiers sont copiés avant la transaction ; supprimés si elle échoue.
            [$mediaIds, $urlMap, $created] = $this->importMedia($zip, $mediaList, $manifest, $author, $report);

            try {
                DB::transaction(function () use ($pages, $settings, $withSettings, $replace, $author, $mediaIds, $urlMap, &$report) {
                    if ($withSettings) {
                        $this->importSettings($settings, $replace);
                        $report['settings'] = true;
                    }
                    $this->importPages($pages, $replace, $author, $mediaIds, $urlMap, $report);
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

        if ($favicon = \App\Builder\ContentSanitizer::url($settings['favicon_url'] ?? null)) {
            $values['favicon_url'] = $favicon;
        }

        Setting::write($values);

        // En-tête, menus et pied de page : revalidés comme s'ils venaient du formulaire « Apparence ».
        if (is_array($settings['navigation'] ?? null)) {
            \App\Site\Navigation::save($settings['navigation'], (array) Setting::read('locales', ['fr']));
        }
    }

    private function importPages(array $pages, bool $replace, ?User $author, array $mediaIds, array $urlMap, array &$report): void
    {
        $groups = [];
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
        }
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
