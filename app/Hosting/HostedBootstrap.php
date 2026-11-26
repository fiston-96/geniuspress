<?php

namespace App\Hosting;

use Illuminate\Foundation\Application;

/**
 * Choisit, avant le chargement du .env, le site hébergé que sert la requête.
 *
 * Appelé dans bootstrap/app.php. Le site est trouvé par son nom d'hôte (index
 * storage/hosting/hosts/) ou, en ligne de commande, par GP_SITE=… ; Laravel utilise
 * alors le .env, le storage et le cache de configuration de ce site. Le code est
 * partagé : un site hébergé est une installation GeniusPress ordinaire.
 *
 * Aucune dépendance au conteneur ni aux façades : rien n'est encore démarré.
 */
final class HostedBootstrap
{
    public const SLUG = '/^[a-z0-9](?:[a-z0-9-]{1,38}[a-z0-9])$/';

    /** Dossier des sites hébergés (variable d'environnement du serveur, pas du .env). */
    public static function root(string $basePath): string
    {
        $path = getenv('GP_HOSTING_PATH');

        return rtrim($path !== false && $path !== '' ? $path : $basePath.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'hosting', '/\\');
    }

    /** Applique le site hébergé trouvé, s'il y en a un. Renvoie son identifiant. */
    public static function apply(Application $app): ?string
    {
        $root = self::root($app->basePath());
        $slug = self::resolve($root, PHP_SAPI === 'cli' ? getenv('GP_SITE') : false, $_SERVER['HTTP_HOST'] ?? null);
        if ($slug === null) {
            return null;
        }

        // Garde-fou : des DB_* dans l'environnement du serveur (PHP-FPM, crontab) l'emporteraient sur
        // le .env du site, qui lirait alors la base de la plateforme. Mieux vaut refuser de servir.
        foreach (['DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_HOST'] as $key) {
            if (getenv($key) !== false) {
                throw new \RuntimeException("Site hébergé refusé : {$key} est défini dans l'environnement du serveur (PHP-FPM ou crontab). Retirez-le : chaque site lit sa base dans son propre .env.");
            }
        }

        $dir = self::siteDir($root, $slug);
        $app->useEnvironmentPath($dir);
        $app->useStoragePath($dir.DIRECTORY_SEPARATOR.'storage');

        // Valeurs propres au site, prioritaires sur tout le reste.
        self::setEnv('GP_HOSTED_SITE', $dir.DIRECTORY_SEPARATOR.Manifest::FILE);
        self::setEnv('APP_CONFIG_CACHE', $dir.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'config.php');
        self::setEnv('GP_INSTALL_LOCK', $dir.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'installed.lock');

        return $slug;
    }

    /** Identifiant du site pour cette commande (GP_SITE) ou ce nom d'hôte, ou null. */
    public static function resolve(string $root, string|false|null $cliSite, ?string $host): ?string
    {
        if (is_string($cliSite) && $cliSite !== '') {
            $slug = strtolower($cliSite);
        } else {
            $host = self::normalizeHost((string) $host);
            if ($host === null) {
                return null;
            }
            $index = $root.DIRECTORY_SEPARATOR.'hosts'.DIRECTORY_SEPARATOR.$host;
            if (! is_file($index)) {
                return null;
            }
            $slug = trim((string) file_get_contents($index));
        }

        if (! preg_match(self::SLUG, $slug) || ! is_file(self::siteDir($root, $slug).DIRECTORY_SEPARATOR.'.env')) {
            return null;
        }

        return $slug;
    }

    public static function siteDir(string $root, string $slug): string
    {
        return $root.DIRECTORY_SEPARATOR.'sites'.DIRECTORY_SEPARATOR.$slug;
    }

    /** « Cooperative.Essai.Exemple.org:8000 » → « cooperative.essai.exemple.org », ou null si invalide. */
    public static function normalizeHost(string $host): ?string
    {
        $host = strtolower(trim($host));
        $host = preg_replace('/:\d+$/', '', $host);
        if ($host === '' || strlen($host) > 253 || ! preg_match('/^[a-z0-9](?:[a-z0-9.-]*[a-z0-9])?$/', $host) || str_contains($host, '..')) {
            return null;
        }

        return $host;
    }

    private static function setEnv(string $key, string $value): void
    {
        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
