<?php

namespace App\Http\Controllers;

/**
 * Téléchargement du paquet prêt à l'emploi (/telecharger/geniuspress.zip) : la version la plus
 * récente de dist/geniuspress-{version}.zip, fabriquée par « php artisan geniuspress:package ».
 * Décompressé chez un hébergeur ou sous WAMP, XAMPP, Laragon, MAMP, il s'installe depuis le
 * navigateur, sans ligne de commande.
 */
class PackageDownloadController extends Controller
{
    public function __invoke()
    {
        $latest = collect(glob(base_path('dist/geniuspress-*.zip')) ?: [])
            ->filter(fn ($f) => preg_match('/geniuspress-(\d+\.\d+\.\d+)\.zip$/', $f))
            ->sortBy(fn ($f) => preg_replace('/.*geniuspress-(\d+\.\d+\.\d+)\.zip$/', '$1', $f), SORT_NATURAL)
            ->last();
        abort_unless($latest, 404);

        return response()->download($latest, basename($latest), [
            'Content-Type' => 'application/zip',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
