<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Fabrique le paquet téléchargeable pour l'hébergement mutualisé :
 * dist/geniuspress-{version}.zip, prêt à envoyer chez l'hébergeur (sans Composer ni Node.js).
 *
 *   npm run build && php artisan geniuspress:package
 */
class PackageCommand extends Command
{
    protected $signature = 'geniuspress:package {--output= : Dossier de sortie (défaut : dist/)} {--keep : Garder le dossier de travail}';

    protected $description = 'Crée le paquet zip de GeniusPress pour l’hébergement mutualisé';

    /** Jamais dans le paquet : outils de développement, données locales, secrets. */
    private const EXCLUDE_DIRS = ['.git', '.github', '.claude', 'node_modules', 'vendor', 'tests', 'dist', 'storage', 'public/storage', 'public/hot',
        // Préparation du catalogue et de la vitrine geniuspress.site : inutiles à un site installé.
        'templates/photos', 'website', 'scripts/hosting'];

    private const EXCLUDE_FILES = ['.env', '.phpunit.result.cache', 'phpunit.xml', '.editorconfig', 'AGENTS.md', 'CLAUDE.md', '*.stackdump', '*.log', '.env.testing'];

    public function handle(): int
    {
        if (! class_exists(ZipArchive::class)) {
            $this->error('L’extension PHP zip est nécessaire.');

            return self::FAILURE;
        }
        if (! is_file(public_path('build/manifest.json'))) {
            $this->error('Le builder n’est pas compilé : lancez d’abord « npm run build ».');

            return self::FAILURE;
        }

        $version = (string) config('geniuspress.version');
        $outDir = $this->option('output') ?: base_path('dist');
        $work = $outDir.DIRECTORY_SEPARATOR.'work'.DIRECTORY_SEPARATOR.'geniuspress';
        $zipPath = $outDir.DIRECTORY_SEPARATOR."geniuspress-{$version}.zip";

        File::deleteDirectory(dirname($work));
        File::ensureDirectoryExists($work);

        // 1. Fichiers du projet
        $this->line('Copie des fichiers…');
        $count = 0;
        $finder = Finder::create()->in(base_path())->files()->ignoreDotFiles(false)->ignoreVCS(true)
            ->exclude(self::EXCLUDE_DIRS)->notName(self::EXCLUDE_FILES)
            ->notPath('#^bootstrap/cache/.+\.php$#')->notPath('#^database/.+\.sqlite#');
        foreach ($finder as $file) {
            $target = $work.DIRECTORY_SEPARATOR.$file->getRelativePathname();
            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getRealPath(), $target);
            $count++;
        }

        // 2. Arborescence vide de storage (avec ses .gitignore)
        foreach (['app/public', 'app/private', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
            File::ensureDirectoryExists($work.'/storage/'.$dir);
        }
        foreach (Finder::create()->in(storage_path())->files()->ignoreDotFiles(false)->name('.gitignore') as $file) {
            $target = $work.'/storage/'.$file->getRelativePathname();
            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getRealPath(), $target);
        }

        // 3. Dépendances PHP de production
        $this->line('Installation des dépendances PHP (sans les outils de développement)…');
        $composer = new Process(['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress', '--no-scripts', '--prefer-dist'], $work, null, null, 900);
        $composer->run(fn ($type, $out) => $this->output->isVerbose() ? $this->output->write($out) : null);
        if (! $composer->isSuccessful()) {
            $this->error('composer install a échoué : '.trim($composer->getErrorOutput()));

            return self::FAILURE;
        }

        // 4. Archive zip
        $this->line('Création de l’archive…');
        File::delete($zipPath);
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            $this->error("Impossible de créer {$zipPath}");

            return self::FAILURE;
        }
        $root = dirname($work);
        foreach (Finder::create()->in($work)->ignoreDotFiles(false)->ignoreVCS(true) as $item) {
            $name = 'geniuspress/'.str_replace('\\', '/', $item->getRelativePathname());
            $item->isDir() ? $zip->addEmptyDir($name) : $zip->addFile($item->getRealPath(), $name);
        }
        $zip->close();

        if (! $this->option('keep')) {
            File::deleteDirectory($root);
        }

        $this->info(sprintf('Paquet prêt : %s (%s Mo, %d fichiers du projet)', $zipPath, number_format(filesize($zipPath) / 1048576, 1, ',', ' '), $count));

        return self::SUCCESS;
    }
}
