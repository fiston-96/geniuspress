<?php

namespace App\Install;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Sait si GeniusPress est installé (fichier verrou) et prépare l'application
 * avant l'installation : fichier .env, clé de chiffrement, sessions en fichiers
 * (la base de données n'existe pas encore).
 */
class InstallState
{
    private ?bool $installed = null;

    public function __construct(private string $lockPath, private EnvironmentFile $env, private ?string $pendingPath = null) {}

    public function isInstalled(): bool
    {
        if ($this->installed !== null) {
            return $this->installed;
        }
        if (is_file($this->lockPath)) {
            return $this->installed = true;
        }

        // Installation faite avant l'existence du verrou : la base contient déjà un compte.
        try {
            if (Schema::hasTable('users') && User::query()->exists()) {
                $this->markInstalled();

                return $this->installed = true;
            }
        } catch (Throwable) {
            // Base pas encore configurée.
        }

        return $this->installed = false;
    }

    public function markInstalled(): void
    {
        @mkdir(dirname($this->lockPath), 0775, true);
        file_put_contents($this->lockPath, json_encode([
            'installed_at' => now()->toIso8601String(),
            'version' => config('geniuspress.version'),
        ], JSON_PRETTY_PRINT));
        $this->installed = true;
    }

    /** Appelé au démarrage tant que l'installation n'est pas terminée. */
    public function prepare(): void
    {
        if (is_file($this->lockPath)) {
            return;
        }

        // 1. Fichier .env et clé de chiffrement (nécessaires aux sessions et cookies).
        try {
            if (! $this->env->exists()) {
                $this->env->createFrom(base_path('.env.example'));
                // Aucune base tant que l'utilisateur ne l'a pas choisie à l'étape 2 : sinon
                // les valeurs d'exemple pourraient pointer vers une base existante d'un autre site.
                // Exception : base fournie par l'environnement du serveur (Docker, hébergeur).
                if (getenv('DB_CONNECTION') === false) {
                    $this->env->set(['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => config('geniuspress.sqlite_path')]);
                    config([
                        'database.default' => 'sqlite',
                        'database.connections.sqlite.database' => config('geniuspress.sqlite_path'),
                    ]);
                }
            }
            if (! $this->env->get('APP_KEY')) {
                $this->env->set(['APP_KEY' => 'base64:'.base64_encode(random_bytes(32))]);
            }
            $key = $this->env->get('APP_KEY');
        } catch (Throwable) {
            $key = null; // .env non modifiable : signalé par la vérification du serveur.
        }
        config(['app.key' => config('app.key') ?: ($key ?: 'base64:'.base64_encode(random_bytes(32)))]);

        // 2. Base choisie à l'étape 2 mais pas encore écrite dans .env.
        if ($pending = $this->pendingDatabase()) {
            DatabaseConfig::use($pending);
        }

        // 3. Tant que la base n'est pas prête, sessions et cache passent par des fichiers.
        //    Dès que les tables existent (étape 3), on utilise la base : la connexion
        //    du compte créé à la fin reste valable après l'installation.
        if (! $this->databaseIsReady()) {
            config([
                'session.driver' => 'file',
                'cache.default' => 'file',
                'queue.default' => 'sync',
            ]);
        }
    }

    /** Paramètres de base enregistrés à l'étape 2, en attendant l'écriture du .env. */
    public function savePendingDatabase(array $db): void
    {
        $path = $this->pendingPath();
        @mkdir(dirname($path), 0775, true);
        file_put_contents($path, json_encode($db));
        @chmod($path, 0600);
    }

    public function pendingDatabase(): ?array
    {
        $path = $this->pendingPath();
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return is_array($data) ? $data : null;
    }

    public function clearPendingDatabase(): void
    {
        @unlink($this->pendingPath());
    }

    private function pendingPath(): string
    {
        return $this->pendingPath ?? dirname($this->lockPath).DIRECTORY_SEPARATOR.'install-database.json';
    }

    public function databaseIsReady(): bool
    {
        try {
            return Schema::hasTable('migrations') && Schema::hasTable('users') && Schema::hasTable('sessions');
        } catch (Throwable) {
            return false;
        }
    }
}
