<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Schema;

/**
 * Catalogue public des modèles (/catalogue/v1/…), consulté par les sites GeniusPress installés
 * ailleurs (config geniuspress.catalog.url) : index.json et le zip de chaque modèle, construits
 * par « php artisan geniuspress:templates » dans config geniuspress.catalog.path.
 *
 * Les zips des modèles payants ne sont jamais servis ici : la plateforme les fournit à qui les a
 * achetés, avec sa clé de licence (/api/platform/v1/templates/{modèle}/download).
 */
class CatalogFileController extends Controller
{
    public function __invoke(string $file)
    {
        $path = rtrim((string) config('geniuspress.catalog.path'), '/\\').DIRECTORY_SEPARATOR.$file;
        abort_unless(is_file($path), 404);

        if ($file === 'index.json') {
            return response()->file($path, ['Content-Type' => 'application/json; charset=utf-8', 'Cache-Control' => 'public, max-age=600']);
        }

        abort_if($this->isPaid(basename($file, '.zip')), 404);

        return response()->file($path, ['Content-Type' => 'application/zip', 'Cache-Control' => 'public, max-age=86400']);
    }

    /** Modèle vendu par la plateforme (prix actif) : jamais en téléchargement libre. */
    private function isPaid(string $template): bool
    {
        if (! config('geniuspress.platform.enabled') || ! Schema::hasTable('platform_template_prices')) {
            return false;
        }

        return \App\Platform\Models\TemplatePrice::query()->where('template_id', $template)->where('active', true)->exists();
    }
}
