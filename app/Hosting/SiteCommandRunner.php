<?php

namespace App\Hosting;

use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Lance une commande artisan dans le contexte d'un site hébergé (GP_SITE=…),
 * dans un processus séparé : le site y démarre avec son propre .env, sa base et
 * son storage, exactement comme pour une requête web.
 */
class SiteCommandRunner
{
    /**
     * Variables à ne jamais transmettre au processus du site : elles l'emporteraient sur
     * son .env (environnement de la plateforme, de PHPUnit, de Docker…).
     */
    private const MASKED = [
        'APP_ENV', 'APP_KEY', 'APP_URL', 'APP_DEBUG', 'APP_NAME', 'APP_CONFIG_CACHE', 'APP_MAINTENANCE_DRIVER',
        'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_URL',
        'SESSION_DRIVER', 'CACHE_STORE', 'QUEUE_CONNECTION', 'MAIL_MAILER', 'BROADCAST_CONNECTION', 'BCRYPT_ROUNDS',
        'GP_ENV_DIR', 'GP_INSTALL_LOCK', 'GP_HOSTED_SITE', 'GP_HOSTING_ENABLED', 'ANTHROPIC_API_KEY',
        'PULSE_ENABLED', 'TELESCOPE_ENABLED', 'NIGHTWATCH_ENABLED',
    ];

    /** @param list<string> $arguments */
    public function run(string $slug, array $arguments, int $timeout = 600): string
    {
        // Tout l'environnement du serveur, explicitement : sous un serveur web, Symfony Process
        // n'en transmettrait qu'une partie (sans SYSTEMROOT, Windows ne peut plus ouvrir de
        // connexion réseau ; sans PATH, PHP-FPM ne trouve plus ses outils).
        $env = array_filter((array) getenv(), 'is_string');
        foreach (self::MASKED as $key) {
            $env[$key] = false;
        }
        foreach ($this->envKeys($slug) as $key) {
            $env[$key] = false;
        }
        $env['GP_SITE'] = $slug;
        $env['GP_HOSTING_PATH'] = (string) config('hosting.path');

        $process = new Process([$this->php(), base_path('artisan'), ...$arguments, '--no-interaction'], base_path(), $env, null, $timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()."\n".$process->getOutput()) ?: 'Code de sortie '.$process->getExitCode());
        }

        return $process->getOutput();
    }

    private function php(): string
    {
        return config('hosting.php') ?: ((new PhpExecutableFinder)->find(false) ?: 'php');
    }

    /** Variables définies dans le .env du site. */
    private function envKeys(string $slug): array
    {
        $path = HostedBootstrap::siteDir((string) config('hosting.path'), $slug).DIRECTORY_SEPARATOR.'.env';
        preg_match_all('/^([A-Z][A-Z0-9_]*)=/m', is_file($path) ? (string) file_get_contents($path) : '', $m);

        return $m[1];
    }
}
