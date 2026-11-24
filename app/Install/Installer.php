<?php

namespace App\Install;

use App\Builder\DefaultContent;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDOException;
use Throwable;

/**
 * Étapes de l'installation, partagées par l'assistant web (/install) et la
 * commande « php artisan geniuspress:install ».
 */
class Installer
{
    public function __construct(private EnvironmentFile $env, private InstallState $state) {}

    /**
     * Teste une connexion. Renvoie null si tout va bien, sinon un message clair.
     *
     * @param  array{driver: string, host?: string, port?: int|string, database: string, username?: string, password?: string, create?: bool}  $db
     */
    public function testDatabase(array $db): ?string
    {
        try {
            if ($db['driver'] === 'sqlite') {
                $this->prepareSqliteFile($db['database']);
            } elseif (! empty($db['create'])) {
                try {
                    $this->createMysqlDatabase($db);
                } catch (Throwable $e) {
                    // Pas le droit de créer une base (fréquent chez les hébergeurs) : la base existe
                    // peut-être déjà. L'erreur ne compte que si la connexion échoue aussi.
                    if ($e->getMessage() === 'invalid-name') {
                        throw $e;
                    }
                }
            }
            // Connexion d'essai séparée : la connexion principale n'est pas modifiée.
            DatabaseConfig::define('gp_install_check', $db);
            DB::connection('gp_install_check')->getPdo();

            return null;
        } catch (Throwable $e) {
            return $this->explain($e, $db);
        } finally {
            DB::purge('gp_install_check');
        }
    }

    /**
     * Crée les tables avec cette connexion. Le .env n'est pas encore modifié :
     * il ne sera écrit qu'une fois, à la fin (« php artisan serve » redémarre
     * à chaque modification du .env et couperait la requête en cours).
     */
    public function configureDatabase(array $db): void
    {
        DatabaseConfig::use($db);
        Artisan::call('migrate', ['--force' => true]);
        $this->state->savePendingDatabase($db);
    }

    /**
     * Crée le site et le compte administrateur, puis verrouille l'installation.
     *
     * @param  array{site_name: string, locale: string, name: string, email: string, password: string, start: string, url?: string|null}  $site
     */
    public function createSite(array $site): User
    {
        $user = DB::transaction(function () use ($site) {
            $user = User::create([
                'name' => $site['name'],
                'email' => $site['email'],
                'password' => $site['password'],
                'role' => User::ROLE_ADMIN,
                'locale' => array_key_exists(app()->getLocale(), config('geniuspress.ui_locales')) ? app()->getLocale() : null,
            ]);

            Setting::write(['site_name' => $site['site_name'], 'locales' => [$site['locale']]]);

            Page::create([
                'title' => __('Accueil'),
                'slug' => $site['locale'] === 'fr' ? 'accueil' : 'home',
                'locale' => $site['locale'],
                'is_home' => true,
                'status' => Page::STATUS_PUBLISHED,
                'published_at' => now(),
                'author_id' => $user->id,
                'content' => $site['start'] === 'blank' ? Page::emptyContent() : DefaultContent::home($site['site_name']),
            ]);

            return $user;
        });

        $this->finish($site['url'] ?? null);

        return $user;
    }

    /** Derniers réglages, écrits en une seule fois : base de données, adresse du site, mode, verrou. */
    private function finish(?string $url): void
    {
        $values = [];
        if ($pending = $this->state->pendingDatabase()) {
            $values = DatabaseConfig::envValues($pending) + [
                'SESSION_DRIVER' => 'database',
                'CACHE_STORE' => 'database',
                'QUEUE_CONNECTION' => 'database',
            ];
        }
        if ($url) {
            $host = parse_url($url, PHP_URL_HOST) ?: '';
            $local = in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.test') || str_ends_with($host, '.localhost');
            $values += [
                'APP_URL' => rtrim($url, '/'),
                // Sur un vrai serveur, jamais de messages d'erreur détaillés visibles par les visiteurs.
                'APP_ENV' => $local ? 'local' : 'production',
                'APP_DEBUG' => $local,
            ];
        }
        try {
            if (! file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
        } catch (Throwable $e) {
            report($e); // Pas bloquant : les images envoyées ne s'afficheront pas tant que le lien manque.
        }

        // En dernier : la base est prête et le site créé ; un redémarrage du serveur ne casse plus rien.
        $this->state->markInstalled();
        if ($values) {
            $this->env->set($values);
        }
        $this->state->clearPendingDatabase();
        $this->clearCachedConfig();
    }

    private function createMysqlDatabase(array $db): void
    {
        if (! preg_match('/^[A-Za-z0-9_]{1,64}$/', $db['database'])) {
            throw new \InvalidArgumentException('invalid-name');
        }
        $pdo = new \PDO("mysql:host={$db['host']};port={$db['port']}", $db['username'], $db['password'] ?? '', [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 5]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    private function prepareSqliteFile(string $path): void
    {
        if (! is_file($path)) {
            @mkdir(dirname($path), 0775, true);
            if (@touch($path) === false) {
                throw new \RuntimeException('sqlite-not-writable');
            }
        }
    }

    private function explain(Throwable $e, array $db): string
    {
        $code = $e instanceof PDOException ? (string) ($e->errorInfo[1] ?? $e->getCode()) : '';
        $message = $e->getMessage();

        return match (true) {
            $message === 'invalid-name' => __('Le nom de la base ne peut contenir que des lettres, des chiffres et _.'),
            $message === 'sqlite-not-writable' => __('Impossible de créer le fichier :path.', ['path' => $db['database']]),
            $code === '1045' => __('Utilisateur ou mot de passe refusé par MySQL.'),
            $code === '1049' => __('La base « :name » n’existe pas. Cochez « Créer la base » ou créez-la dans votre hébergement.', ['name' => $db['database']]),
            $code === '1044' => __('Cet utilisateur n’a pas le droit d’utiliser la base « :name ».', ['name' => $db['database']]),
            $code === '2002' || str_contains($message, 'refused') || str_contains($message, 'getaddrinfo') => __('Impossible de joindre le serveur MySQL :host (port :port). Vérifiez qu’il est démarré.', ['host' => $db['host'] ?? '', 'port' => $db['port'] ?? '']),
            default => __('Connexion impossible : :error', ['error' => mb_substr($message, 0, 200)]),
        };
    }

    private function clearCachedConfig(): void
    {
        try {
            Artisan::call('config:clear');
        } catch (Throwable) {
        }
    }
}
