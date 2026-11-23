<?php

namespace App\Plugins;

/** Contenu validé du fichier plugin.json d'un plugin. */
final class PluginManifest
{
    public function __construct(
        public readonly string $slug,
        public readonly string $path,
        public readonly string $name,
        public readonly string $version,
        public readonly string $description,
        public readonly string $author,
        public readonly string $license,
        public readonly ?string $homepage,
        public readonly string $requires,
        public readonly ?string $main,
        public readonly array $autoload,
        public readonly array $builderScripts,
        public readonly array $siteStyles,
        public readonly array $siteScripts,
        public readonly array $errors = [],
    ) {}

    public static function load(string $dir): self
    {
        $slug = basename($dir);
        $errors = [];
        $file = $dir.DIRECTORY_SEPARATOR.'plugin.json';
        $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;

        if (! preg_match('/^[a-z][a-z0-9-]{1,40}$/', $slug)) {
            $errors[] = __('Le nom du dossier doit contenir uniquement des minuscules, des chiffres et des tirets.');
        }
        if (! is_array($data)) {
            $errors[] = is_file($file) ? __('Le fichier plugin.json est illisible (JSON invalide).') : __('Le fichier plugin.json est manquant.');
            $data = [];
        }
        foreach (['name', 'version'] as $key) {
            if (empty($data[$key]) || ! is_string($data[$key])) {
                $errors[] = __('Le champ « :field » est obligatoire dans plugin.json.', ['field' => $key]);
            }
        }

        $list = fn ($v) => array_values(array_filter((array) $v, fn ($p) => is_string($p) && self::safeRelative($p)));
        $assets = is_array($data['assets'] ?? null) ? $data['assets'] : [];

        return new self(
            slug: $slug,
            path: $dir,
            name: (string) ($data['name'] ?? $slug),
            version: (string) ($data['version'] ?? '0.0.0'),
            description: (string) ($data['description'] ?? ''),
            author: (string) ($data['author'] ?? ''),
            license: (string) ($data['license'] ?? ''),
            homepage: isset($data['homepage']) && is_string($data['homepage']) ? $data['homepage'] : null,
            requires: (string) ($data['requires']['geniuspress'] ?? '*'),
            main: isset($data['main']) && is_string($data['main']) ? ltrim($data['main'], '\\') : null,
            autoload: array_filter((array) ($data['autoload']['psr-4'] ?? []), fn ($p, $ns) => is_string($p) && is_string($ns) && self::safeRelative($p), ARRAY_FILTER_USE_BOTH),
            builderScripts: $list($assets['builder'] ?? []),
            siteStyles: $list($assets['site_css'] ?? []),
            siteScripts: $list($assets['site_js'] ?? []),
            errors: $errors,
        );
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /** Vérifie la contrainte « requires.geniuspress » (ex. « >=0.1 », « ^1.0 », « * »). */
    public function isCompatibleWith(string $version): bool
    {
        $constraint = trim($this->requires);
        if ($constraint === '' || $constraint === '*') {
            return true;
        }
        if (str_starts_with($constraint, '^')) {
            $min = substr($constraint, 1);
            $major = (int) explode('.', $min)[0];

            return version_compare($version, $min, '>=') && (int) explode('.', $version)[0] === $major;
        }
        if (preg_match('/^(>=|<=|>|<|=|==)?\s*([0-9][0-9.]*)$/', $constraint, $m)) {
            return version_compare($version, $m[2], $m[1] ?: '>=');
        }

        return false;
    }

    /** Chemin relatif sans « .. » ni chemin absolu. */
    private static function safeRelative(string $p): bool
    {
        return $p !== '' && ! str_contains($p, '..') && ! preg_match('#^([/\\\\]|[a-zA-Z]:)#', $p);
    }
}
