<?php

namespace App\Hosting;

use Carbon\CarbonImmutable;
use RuntimeException;

/**
 * Fiche d'un site hébergé (hosting.json, dans son dossier) : état, date de fin,
 * quotas, propriétaire et secret du lien de connexion. Écrite par la plateforme,
 * lue par le site lui-même à chaque requête.
 */
final class Manifest
{
    public const FILE = 'hosting.json';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUSPENDED = 'suspended';

    /** @param array<string, mixed> $data */
    public function __construct(public readonly array $data) {}

    public static function load(string $path): ?self
    {
        if (! is_file($path)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($path), true);

        return is_array($data) ? new self($data) : null;
    }

    /** Écriture atomique (fichier temporaire puis renommage) : jamais de fiche à moitié écrite. */
    public function save(string $path): void
    {
        $tmp = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
        if (@file_put_contents($tmp, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) === false) {
            throw new RuntimeException("Impossible d’écrire {$path}.");
        }
        @chmod($tmp, 0640);
        if (! @rename($tmp, $path)) {
            // Windows refuse de renommer par-dessus un fichier existant.
            @unlink($path);
            rename($tmp, $path);
        }
    }

    public function with(array $values): self
    {
        return new self(array_replace($this->data, $values));
    }

    public function slug(): string
    {
        return (string) ($this->data['slug'] ?? '');
    }

    public function kind(): string
    {
        return (string) ($this->data['kind'] ?? 'trial');
    }

    public function isTrial(): bool
    {
        return $this->kind() === 'trial';
    }

    public function expiresAt(): ?CarbonImmutable
    {
        return ! empty($this->data['expires_at']) ? CarbonImmutable::parse($this->data['expires_at']) : null;
    }

    /** État effectif : un essai dont la date est passée est terminé, même avant le passage de la tâche planifiée. */
    public function status(): string
    {
        $status = (string) ($this->data['status'] ?? self::STATUS_ACTIVE);
        if ($status === self::STATUS_ACTIVE && ($end = $this->expiresAt()) && $end->isPast()) {
            return self::STATUS_EXPIRED;
        }

        return $status;
    }

    public function daysLeft(): ?int
    {
        $end = $this->expiresAt();

        return $end ? max(0, (int) ceil(CarbonImmutable::now()->diffInSeconds($end, false) / 86400)) : null;
    }

    /** Quota (null = illimité). */
    public function limit(string $key): ?int
    {
        $value = $this->data['limits'][$key] ?? null;

        return $value === null ? null : (int) $value;
    }

    public function loginSecret(): string
    {
        return (string) ($this->data['login_secret'] ?? '');
    }

    public function upgradeUrl(): ?string
    {
        return $this->data['upgrade_url'] ?? null;
    }
}
