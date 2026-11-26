<?php

namespace App\Hosting;

/**
 * Le site hébergé servi par cette requête, vu de l'intérieur (quotas, état,
 * bandeau d'essai). Sur la plateforme ou une installation ordinaire : aucun.
 */
class CurrentSite
{
    public function __construct(private ?Manifest $manifest) {}

    public static function fromEnvironment(): self
    {
        $path = getenv('GP_HOSTED_SITE');

        return new self(is_string($path) && $path !== '' ? Manifest::load($path) : null);
    }

    public function isHosted(): bool
    {
        return $this->manifest !== null;
    }

    public function manifest(): ?Manifest
    {
        return $this->manifest;
    }

    public function status(): ?string
    {
        return $this->manifest?->status();
    }

    public function limit(string $key): ?int
    {
        return $this->manifest?->limit($key);
    }
}
