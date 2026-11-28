<?php

namespace App\Transfer;

use App\Models\Layout;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\Term;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Exporte le site dans un fichier zip : pages (avec traductions et brouillons),
 * articles, catégories et étiquettes, gabarits du thème, médias et réglages.
 * Ni comptes utilisateurs, ni clé API, ni mots de passe.
 *
 * Contenu de l'archive :
 *   manifest.json   format, version, date, adresse d'origine
 *   settings.json   réglages publics
 *   pages.json      pages (contenu du builder compris)
 *   terms.json      catégories et étiquettes
 *   posts.json      articles (avec leurs catégories et étiquettes)
 *   layouts.json    gabarits du thème et leurs conditions d'affichage
 *   media.json      liste des médias
 *   media/…         fichiers
 */
class SiteExporter
{
    public const FORMAT = 'geniuspress-site';
    public const FORMAT_VERSION = 1;

    /** Crée l'archive et renvoie son chemin (à supprimer par l'appelant). */
    public function export(?string $path = null): string
    {
        $path ??= storage_path('app/private/exports/'.Str::slug(Setting::read('site_name', 'site') ?: 'site').'-'.now()->format('Y-m-d-His').'.zip');
        @mkdir(dirname($path), 0775, true);

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException(__('Impossible de créer le fichier :path.', ['path' => $path]));
        }

        $json = fn ($data) => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $zip->addFromString('manifest.json', $json([
            'format' => self::FORMAT,
            'format_version' => self::FORMAT_VERSION,
            'geniuspress_version' => config('geniuspress.version'),
            'exported_at' => now()->toIso8601String(),
            'site_url' => rtrim(url('/'), '/'),
            'storage_url' => rtrim(Storage::disk('public')->url(''), '/'),
        ]));

        $zip->addFromString('settings.json', $json(Setting::publicValues()));

        $zip->addFromString('pages.json', $json(Page::query()->orderBy('id')->get()->map(fn (Page $p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'locale' => $p->locale,
            'translation_group' => $p->translation_group,
            'status' => $p->status,
            'is_home' => $p->is_home,
            'content' => $p->content,
            'draft_content' => $p->draft_content,
            'meta_title' => $p->meta_title,
            'meta_description' => $p->meta_description,
            'published_at' => $p->published_at?->toIso8601String(),
        ])->all()));

        $zip->addFromString('terms.json', $json(Term::query()->orderBy('id')->get()->map(fn (Term $t) => $t->only([
            'id', 'taxonomy', 'name', 'slug', 'locale', 'translation_group', 'description', 'parent_id',
        ]))->all()));

        $zip->addFromString('posts.json', $json(Post::query()->with('terms')->orderBy('id')->get()->map(fn (Post $p) => $p->only([
            'id', 'title', 'slug', 'locale', 'translation_group', 'status', 'content', 'draft_content', 'excerpt',
            'featured_media_id', 'meta_title', 'meta_description',
        ]) + [
            'published_at' => $p->published_at?->toIso8601String(),
            'terms' => $p->terms->pluck('id')->all(),
        ])->all()));

        $zip->addFromString('layouts.json', $json(Layout::query()->orderBy('id')->get()->map(fn (Layout $l) => $l->only([
            'id', 'name', 'type', 'locale', 'status', 'conditions', 'options', 'priority', 'content', 'draft_content',
        ]))->all()));

        $media = [];
        foreach (Media::query()->orderBy('id')->get() as $m) {
            $disk = Storage::disk($m->disk);
            if (! $disk->exists($m->path)) {
                continue; // Fichier disparu : on n'exporte pas une référence cassée.
            }
            $file = 'media/'.$m->id.'-'.basename($m->path);
            $zip->addFile($disk->path($m->path), $file);
            $media[] = $m->only(['id', 'path', 'original_name', 'mime_type', 'size', 'width', 'height', 'alt']) + ['file' => $file];
        }
        $zip->addFromString('media.json', $json($media));

        $zip->close();

        return $path;
    }
}
