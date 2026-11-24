<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Transfer\SiteImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportSiteCommand extends Command
{
    protected $signature = 'geniuspress:import {file : Fichier zip exporté par GeniusPress}
        {--replace : Écraser les pages de même adresse et reprendre la page d’accueil importée}
        {--no-settings : Ne pas importer les réglages (couleurs, police, langues…)}';

    protected $description = 'Importe un site exporté par GeniusPress';

    public function handle(SiteImporter $importer): int
    {
        if (! is_file($this->argument('file'))) {
            $this->error(__('Fichier introuvable : :path', ['path' => $this->argument('file')]));

            return self::FAILURE;
        }

        try {
            $report = $importer->import(
                $this->argument('file'),
                User::query()->where('role', User::ROLE_ADMIN)->orderBy('id')->first(),
                replace: (bool) $this->option('replace'),
                withSettings: ! $this->option('no-settings'),
            );
        } catch (Throwable $e) {
            $this->error(__('Import impossible : :error', ['error' => $e->getMessage()]));

            return self::FAILURE;
        }

        foreach ($report['warnings'] as $warning) {
            $this->warn($warning);
        }
        $this->info(__('Import terminé : :pages pages et :media médias.', ['pages' => $report['pages'], 'media' => $report['media']]));

        return self::SUCCESS;
    }
}
