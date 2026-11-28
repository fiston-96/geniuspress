<?php

namespace App\Backup;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Sauvegardes complètes d'un site : toute la base de données (sauf les tables techniques) et
 * tous les fichiers envoyés, dans un zip rangé dans storage/app/backups (jamais public).
 * En PHP pur, sans mysqldump : fonctionne chez un hébergeur mutualisé, avec MySQL ou SQLite.
 *
 * Différent de l'export (App\Transfer) : l'export déplace le contenu vers un autre site, sans
 * comptes ni secrets ; la sauvegarde remet CE site exactement dans l'état enregistré.
 *
 * Format : manifest.json, database/{table}.jsonl (une ligne JSON par enregistrement),
 * files/… (copie de storage/app/public).
 */
final class Backups
{
    public const FORMAT = 'geniuspress-backup';

    public const FREQUENCIES = ['off', 'daily', 'weekly'];

    /** Jamais sauvegardées ni restaurées : état passager, ou propre au code installé. */
    private const SKIP_TABLES = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'];

    private const CHUNK = 500;

    public function directory(): string
    {
        return storage_path('app/backups');
    }

    /** Sauvegardes présentes, de la plus récente à la plus ancienne. @return list<array{name: string, size: int, date: \Illuminate\Support\Carbon, kind: string}> */
    public function all(): array
    {
        $files = glob($this->directory().'/geniuspress-sauvegarde-*.zip') ?: [];
        $list = array_map(fn ($f) => [
            'name' => basename($f),
            'size' => filesize($f),
            'date' => \Illuminate\Support\Carbon::createFromTimestamp(filemtime($f)),
            'kind' => preg_match('/-(auto|manuelle|avant-restauration)\.zip$/', $f, $m) ? $m[1] : 'manuelle',
        ], $files);
        usort($list, fn ($a, $b) => $b['date'] <=> $a['date']);

        return $list;
    }

    /** Chemin d'une sauvegarde par son nom (jamais en dehors du dossier des sauvegardes). */
    public function path(string $name): ?string
    {
        if (! preg_match('/^geniuspress-sauvegarde-[0-9]{8}-[0-9]{6}-(auto|manuelle|avant-restauration)\.zip$/', $name)) {
            return null;
        }
        $path = $this->directory().DIRECTORY_SEPARATOR.$name;

        return is_file($path) ? $path : null;
    }

    /** Crée une sauvegarde ; renvoie son nom. */
    public function create(string $kind = 'manuelle'): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException(__('L’extension PHP zip est nécessaire pour les sauvegardes.'));
        }
        @set_time_limit(0);
        File::ensureDirectoryExists($this->directory());
        // Le dossier des sauvegardes n'est jamais servi, même si storage/app était exposé par erreur.
        if (! is_file($this->directory().'/.htaccess')) {
            file_put_contents($this->directory().'/.htaccess', "Require all denied\n");
        }
        $name = 'geniuspress-sauvegarde-'.now()->format('Ymd-His').'-'.$kind.'.zip';
        $path = $this->directory().DIRECTORY_SEPARATOR.$name;

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException(__('Impossible d’écrire la sauvegarde dans :dir.', ['dir' => $this->directory()]));
        }
        $tables = [];
        $temp = [];
        foreach ($this->tables() as $table) {
            // Une table par fichier temporaire (lignes JSON), pour ne jamais tout garder en mémoire.
            $tmp = tempnam(sys_get_temp_dir(), 'gp-bak-');
            $handle = fopen($tmp, 'w');
            $count = 0;
            $query = DB::table($table);
            $order = $this->orderColumn($table);
            $order ? $query->orderBy($order) : null;
            $order
                ? $query->chunk(self::CHUNK, function ($rows) use ($handle, &$count) {
                    foreach ($rows as $row) {
                        fwrite($handle, json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)."\n");
                        $count++;
                    }
                })
                : $query->get()->each(function ($row) use ($handle, &$count) {
                    fwrite($handle, json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)."\n");
                    $count++;
                });
            fclose($handle);
            $zip->addFile($tmp, 'database/'.$table.'.jsonl');
            $temp[] = $tmp;
            $tables[$table] = $count;
        }

        $files = 0;
        $public = storage_path('app/public');
        if (is_dir($public)) {
            foreach (File::allFiles($public) as $file) {
                $zip->addFile($file->getPathname(), 'files/'.str_replace('\\', '/', $file->getRelativePathname()));
                $files++;
            }
        }
        $zip->addFromString('manifest.json', json_encode([
            'format' => self::FORMAT,
            'version' => 1,
            'geniuspress_version' => config('geniuspress.version'),
            'created_at' => now()->toIso8601String(),
            'kind' => $kind,
            'site' => Setting::read('site_name'),
            'url' => config('app.url'),
            'driver' => DB::connection()->getDriverName(),
            'tables' => $tables,
            'files' => $files,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $zip->close();
        array_map('unlink', $temp);

        return $name;
    }

    /**
     * Remet le site dans l'état d'une sauvegarde : une sauvegarde « avant restauration » est
     * faite d'abord ; les tables présentes dans la sauvegarde sont remplacées, les fichiers
     * envoyés aussi ; la base est ensuite mise au niveau de la version installée.
     *
     * @return array{tables: int, rows: int, files: int, safety: string}
     */
    public function restore(string $path): array
    {
        @set_time_limit(0);
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException(__('Ce fichier n’est pas une archive zip lisible.'));
        }
        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true);
        if (($manifest['format'] ?? null) !== self::FORMAT) {
            $zip->close();
            throw new RuntimeException(__('Ce fichier n’est pas une sauvegarde complète de GeniusPress. Pour un export de site, utilisez « Importer un site ».'));
        }

        $safety = $this->create('avant-restauration');
        $existing = array_flip($this->tables());
        $rows = 0;
        $tables = 0;

        Schema::disableForeignKeyConstraints();
        try {
            foreach (array_keys((array) ($manifest['tables'] ?? [])) as $table) {
                if (! is_string($table) || ! isset($existing[$table]) || ! preg_match('/^[A-Za-z0-9_]{1,64}$/', $table)) {
                    continue;
                }
                $stream = $zip->getStream('database/'.$table.'.jsonl');
                if (! $stream) {
                    continue;
                }
                $columns = array_flip(Schema::getColumnListing($table));
                DB::table($table)->delete();
                $batch = [];
                while (($line = fgets($stream)) !== false) {
                    $row = json_decode($line, true);
                    if (! is_array($row)) {
                        continue;
                    }
                    // Seules les colonnes qui existent encore (sauvegarde d'une version plus ancienne).
                    $batch[] = array_intersect_key($row, $columns);
                    if (count($batch) >= self::CHUNK) {
                        DB::table($table)->insert($batch);
                        $rows += count($batch);
                        $batch = [];
                    }
                }
                fclose($stream);
                if ($batch) {
                    DB::table($table)->insert($batch);
                    $rows += count($batch);
                }
                $tables++;
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $files = $this->restoreFiles($zip);
        $zip->close();

        // Sauvegarde d'une version plus ancienne : la base rejoint la version installée.
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('optimize:clear');

        return ['tables' => $tables, 'rows' => $rows, 'files' => $files, 'safety' => $safety];
    }

    /** Remplace storage/app/public par les fichiers de la sauvegarde (chemins contrôlés). */
    private function restoreFiles(ZipArchive $zip): int
    {
        $public = storage_path('app/public');
        File::ensureDirectoryExists($public);
        foreach (File::directories($public) as $dir) {
            File::deleteDirectory($dir);
        }
        foreach (File::files($public, true) as $file) {
            if ($file->getFilename() !== '.gitignore') {
                File::delete($file->getPathname());
            }
        }
        $count = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if (! str_starts_with($name, 'files/') || str_ends_with($name, '/')) {
                continue;
            }
            $relative = substr($name, 6);
            // Jamais en dehors du dossier : ni « .. », ni chemin absolu, ni fichier caché exécutable.
            if ($relative === '' || str_contains($relative, '..') || str_starts_with($relative, '/') || preg_match('/(^|\/)\.(htaccess|user\.ini)$|\.(php\d?|phtml|phar)$/i', $relative)) {
                continue;
            }
            $target = $public.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            File::ensureDirectoryExists(dirname($target));
            $in = $zip->getStream($name);
            $out = fopen($target, 'w');
            stream_copy_to_stream($in, $out);
            fclose($in);
            fclose($out);
            $count++;
        }

        return $count;
    }

    /** Sauvegarde automatique si elle est due (réglage « backup_frequency »), puis ménage. */
    public function runScheduled(): ?string
    {
        $frequency = Setting::read('backup_frequency', 'off');
        if (! in_array($frequency, ['daily', 'weekly'], true)) {
            return null;
        }
        $last = collect($this->all())->firstWhere('kind', 'auto');
        $due = ! $last || $last['date']->lte(now()->sub($frequency === 'daily' ? '23 hours' : '6 days 23 hours'));
        if (! $due) {
            return null;
        }
        $name = $this->create('auto');
        $this->prune();

        return $name;
    }

    /** Garde les N dernières sauvegardes automatiques (réglage « backup_keep », 7 par défaut). */
    public function prune(): int
    {
        $keep = max(1, min(60, (int) Setting::read('backup_keep', 7)));
        $old = array_slice(array_values(array_filter($this->all(), fn ($b) => $b['kind'] === 'auto')), $keep);
        foreach ($old as $b) {
            @unlink($this->directory().DIRECTORY_SEPARATOR.$b['name']);
        }

        return count($old);
    }

    /** @return string[] tables sauvegardées */
    private function tables(): array
    {
        $prefix = DB::connection()->getTablePrefix();

        return collect(Schema::getTables())
            ->map(fn ($t) => Str::after($t['name'], $prefix))
            ->reject(fn ($t) => in_array($t, self::SKIP_TABLES, true) || str_starts_with($t, 'sqlite_'))
            ->values()->all();
    }

    /** Colonne d'ordre stable pour lire une table par paquets (id, sinon aucune). */
    private function orderColumn(string $table): ?string
    {
        return Schema::hasColumn($table, 'id') ? 'id' : null;
    }
}
