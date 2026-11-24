<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Transfer\SiteExporter;
use App\Transfer\SiteImporter;
use Illuminate\Console\Command;
use ZipArchive;

/**
 * Construit le site officiel geniuspress.site à partir de website/site.php et
 * de la documentation Markdown du dépôt, sous la forme d'une archive d'import.
 *
 *   php artisan geniuspress:website                 → dist/geniuspress-site.zip
 *   php artisan geniuspress:website --import        → importé directement dans ce site
 *
 * Sur le serveur : installer GeniusPress, puis « Importer un site » avec l'archive.
 */
class WebsiteCommand extends Command
{
    protected $signature = 'geniuspress:website {--output= : Fichier zip à créer} {--import : Importer directement dans ce site (remplace les pages de même adresse)}';

    protected $description = 'Construit le site officiel de GeniusPress (archive d’import)';

    public function handle(SiteImporter $importer): int
    {
        $site = require base_path('website/site.php');

        $path = $this->option('output') ?: base_path('dist/geniuspress-site.zip');
        @mkdir(dirname($path), 0775, true);

        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $json = fn ($d) => json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $zip->addFromString('manifest.json', $json([
            'format' => SiteExporter::FORMAT,
            'format_version' => SiteExporter::FORMAT_VERSION,
            'geniuspress_version' => config('geniuspress.version'),
            'exported_at' => now()->toIso8601String(),
            'generator' => 'geniuspress:website',
        ]));
        $zip->addFromString('settings.json', $json($site['settings']));
        $zip->addFromString('media.json', '[]');
        $zip->addFromString('pages.json', $json(array_map(fn ($p) => $p + [
            'locale' => 'fr',
            'status' => 'published',
            'is_home' => false,
            'translation_group' => 'site-'.md5($p['slug']),
            'published_at' => now()->toIso8601String(),
        ], $site['pages'])));
        $zip->close();

        $this->info(__('Site officiel construit : :count pages → :path', ['count' => count($site['pages']), 'path' => $path]));

        if ($this->option('import')) {
            $admin = User::query()->where('role', User::ROLE_ADMIN)->orderBy('id')->first();
            $report = $importer->import($path, $admin, replace: true, withSettings: true);
            foreach ($report['warnings'] as $warning) {
                $this->warn($warning);
            }
            $this->info(__('Import terminé : :pages pages et :media médias.', ['pages' => $report['pages'], 'media' => $report['media']]));
        }

        return self::SUCCESS;
    }
}
