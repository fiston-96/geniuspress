<?php

namespace App\Install;

use Illuminate\Support\Facades\DB;

/** Traduit les réponses de l'étape « base de données » en configuration Laravel. */
final class DatabaseConfig
{
    /** Déclare une connexion nommée avec ces paramètres (sans toucher à la connexion par défaut). */
    public static function define(string $name, array $db): void
    {
        $driver = $db['driver'] === 'sqlite' ? 'sqlite' : 'mysql';
        $config = $driver === 'sqlite'
            ? ['database' => $db['database']]
            : ['host' => $db['host'], 'port' => $db['port'], 'database' => $db['database'], 'username' => $db['username'], 'password' => $db['password'] ?? ''];

        config(["database.connections.{$name}" => array_merge(config("database.connections.{$driver}"), $config)]);
        DB::purge($name);
    }

    /** Utilise ces paramètres comme connexion principale pour la requête en cours. */
    public static function use(array $db): void
    {
        $name = $db['driver'] === 'sqlite' ? 'sqlite' : 'mysql';
        self::define($name, $db);
        config(['database.default' => $name]);
        DB::setDefaultConnection($name);
    }

    /** Variables à écrire dans .env. */
    public static function envValues(array $db): array
    {
        return $db['driver'] === 'sqlite'
            ? ['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $db['database']]
            : [
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $db['host'],
                'DB_PORT' => (string) $db['port'],
                'DB_DATABASE' => $db['database'],
                'DB_USERNAME' => $db['username'],
                'DB_PASSWORD' => $db['password'] ?? '',
            ];
    }
}
