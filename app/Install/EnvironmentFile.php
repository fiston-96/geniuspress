<?php

namespace App\Install;

use RuntimeException;

/**
 * Lecture et écriture du fichier .env, en conservant les lignes et
 * commentaires existants. Les valeurs sont protégées selon les règles de
 * phpdotenv : pas de guillemets si possible, sinon apostrophes (aucune
 * interprétation), sinon guillemets doubles échappés.
 */
class EnvironmentFile
{
    public function __construct(private string $path) {}

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return is_file($this->path);
    }

    public function isWritable(): bool
    {
        return $this->exists() ? is_writable($this->path) : is_writable(dirname($this->path));
    }

    /** Crée le fichier à partir d'un modèle (.env.example) s'il n'existe pas. */
    public function createFrom(?string $template): void
    {
        if ($this->exists()) {
            return;
        }
        $content = $template && is_file($template) ? (string) file_get_contents($template) : "APP_NAME=GeniusPress\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\n";
        $this->put($content);
    }

    public function get(string $key): ?string
    {
        if (! $this->exists()) {
            return null;
        }
        if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', (string) file_get_contents($this->path), $m)) {
            $value = trim($m[1]);
            if (preg_match('/^([\'"])(.*)\1$/s', $value, $q)) {
                $value = $q[1] === '"' ? stripcslashes($q[2]) : $q[2];
            }

            return $value;
        }

        return null;
    }

    /** @param array<string, string|int|bool|null> $values */
    public function set(array $values): void
    {
        $content = $this->exists() ? (string) file_get_contents($this->path) : '';

        foreach ($values as $key => $value) {
            if (! preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
                throw new RuntimeException("Nom de variable invalide : {$key}");
            }
            $line = $key.'='.self::encode($value);
            $pattern = '/^#?\s*'.preg_quote($key, '/').'=.*$/m';
            $content = preg_match($pattern, $content)
                ? preg_replace($pattern, str_replace(['\\', '$'], ['\\\\', '\\$'], $line), $content, 1)
                : rtrim($content, "\n")."\n".$line."\n";
        }

        $this->put($content);
    }

    public static function encode(string|int|bool|null $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        $value = (string) $value;
        if (str_contains($value, "\n")) {
            throw new RuntimeException('Les valeurs sur plusieurs lignes ne sont pas acceptées.');
        }
        if ($value === '' || preg_match('/^[A-Za-z0-9_.:\/@+\-]+$/', $value)) {
            return $value;
        }
        if (! str_contains($value, "'")) {
            return "'".$value."'";
        }

        return '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
    }

    private function put(string $content): void
    {
        if (@file_put_contents($this->path, $content, LOCK_EX) === false) {
            throw new RuntimeException("Impossible d’écrire le fichier {$this->path}.");
        }
    }
}
