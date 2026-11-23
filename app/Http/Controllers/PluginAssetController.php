<?php

namespace App\Http\Controllers;

use App\Plugins\PluginManager;

/** Sert les fichiers du dossier public/ d'un plugin (scripts du builder, CSS, images). */
class PluginAssetController extends Controller
{
    private const TYPES = [
        'js' => 'text/javascript', 'mjs' => 'text/javascript', 'css' => 'text/css', 'json' => 'application/json',
        'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif',
        'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'woff2' => 'font/woff2', 'woff' => 'font/woff',
    ];

    public function __invoke(PluginManager $plugins, string $slug, string $path)
    {
        $manifest = $plugins->find($slug);
        abort_unless($manifest && $plugins->isActive($slug), 404);

        $root = realpath($manifest->path.DIRECTORY_SEPARATOR.'public');
        $file = $root ? realpath($root.DIRECTORY_SEPARATOR.$path) : false;
        // Le fichier doit se trouver à l'intérieur du dossier public/ du plugin.
        abort_unless($file && is_file($file) && str_starts_with($file, $root.DIRECTORY_SEPARATOR), 404);

        $type = self::TYPES[strtolower(pathinfo($file, PATHINFO_EXTENSION))] ?? null;
        abort_unless($type, 404);

        return response()->file($file, [
            'Content-Type' => $type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
