<?php

namespace App\Console\Commands;

use App\Transfer\SiteExporter;
use Illuminate\Console\Command;

class ExportSiteCommand extends Command
{
    protected $signature = 'geniuspress:export {path? : Fichier zip à créer (défaut : storage/app/private/exports/…)}';

    protected $description = 'Exporte le site (pages, médias, réglages) dans un fichier zip';

    public function handle(SiteExporter $exporter): int
    {
        $path = $exporter->export($this->argument('path'));
        $this->info(__('Export créé : :path', ['path' => $path]));

        return self::SUCCESS;
    }
}
