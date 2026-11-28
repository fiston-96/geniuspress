<?php

namespace App\Console\Commands;

use App\Backup\Backups;
use Illuminate\Console\Command;

/**
 * Sauvegarde complète du site (base de données et fichiers envoyés).
 *
 *   php artisan geniuspress:backup          → sauvegarde maintenant
 *   php artisan geniuspress:backup --auto   → seulement si la sauvegarde automatique est due (planificateur)
 */
class BackupCommand extends Command
{
    protected $signature = 'geniuspress:backup {--auto : Seulement si la sauvegarde automatique est due (réglage du site)}';

    protected $description = 'Crée une sauvegarde complète du site';

    public function handle(Backups $backups): int
    {
        $name = $this->option('auto') ? $backups->runScheduled() : $backups->create();
        $this->info($name ? __('Sauvegarde créée : :name', ['name' => $name]) : __('Aucune sauvegarde automatique due.'));

        return self::SUCCESS;
    }
}
