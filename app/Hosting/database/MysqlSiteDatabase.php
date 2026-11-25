<?php

namespace App\Hosting\Database;

use App\Hosting\Site;
use InvalidArgumentException;
use PDO;

/**
 * Une base MySQL et un utilisateur MySQL par site (« gpe_12_a3f9 »), avec des
 * droits sur cette seule base. Le compte de provisionnement (GP_HOSTING_DB_USERNAME)
 * doit pouvoir créer des bases et des utilisateurs.
 *
 * Le nom contient une partie aléatoire et la base doit ne pas exister : un site ne
 * reprend jamais la base d'un autre, même si la numérotation des sites repart à zéro.
 */
class MysqlSiteDatabase implements SiteDatabase
{
    public function __construct(private array $config) {}

    public function create(Site $site): array
    {
        $name = $this->config['prefix'].$site->id.'_'.bin2hex(random_bytes(2));
        $this->assertName($name);
        $password = bin2hex(random_bytes(16));
        $host = $this->userHost();
        $pdo = $this->pdo();

        $pdo->exec("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        // Dès maintenant : en cas d'échec, drop() sait quoi retirer.
        $site->database = $name;
        $pdo->exec("CREATE USER '{$name}'@'{$host}' IDENTIFIED BY ".$pdo->quote($password));
        $pdo->exec("GRANT ALL PRIVILEGES ON `{$name}`.* TO '{$name}'@'{$host}'");

        return [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => (string) $this->config['host'],
            'DB_PORT' => (string) $this->config['port'],
            'DB_DATABASE' => $name,
            'DB_USERNAME' => $name,
            'DB_PASSWORD' => $password,
        ];
    }

    public function drop(Site $site): void
    {
        $name = (string) $site->database;
        if ($name === '') {
            return;
        }
        $this->assertName($name);
        $host = $this->userHost();
        $pdo = $this->pdo();
        $pdo->exec("DROP DATABASE IF EXISTS `{$name}`");
        $pdo->exec("DROP USER IF EXISTS '{$name}'@'{$host}'");
    }

    /** Nom de base et d'utilisateur : 32 caractères au plus (limite des utilisateurs MySQL). */
    private function assertName(string $name): void
    {
        if (! preg_match('/^[a-z0-9_]{1,32}$/', $name) || ! str_starts_with($name, (string) $this->config['prefix'])) {
            throw new InvalidArgumentException("Nom de base invalide : {$name}");
        }
    }

    private function userHost(): string
    {
        $host = (string) ($this->config['user_host'] ?? 'localhost');
        if (! preg_match('/^[A-Za-z0-9.%_:-]+$/', $host)) {
            throw new InvalidArgumentException("Hôte MySQL invalide : {$host}");
        }

        return $host;
    }

    private function pdo(): PDO
    {
        return new PDO(
            "mysql:host={$this->config['host']};port={$this->config['port']}",
            (string) $this->config['username'],
            (string) ($this->config['password'] ?? ''),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10],
        );
    }
}
