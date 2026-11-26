<?php

namespace App\Hosting;

/**
 * Offre appliquée à un site hébergé : durée (null = sans date de fin) et quotas
 * (null = illimité). L'offre d'essai vient de config/hosting.php ; les offres
 * payantes viennent de la plateforme commerciale.
 */
final class Plan
{
    public const LIMITS = ['pages', 'posts', 'media_mb', 'ai_requests', 'ai_tokens'];

    /** @param array<string, int|null> $limits */
    public function __construct(
        public readonly string $code,
        public readonly string $kind,
        public readonly ?int $days,
        public readonly array $limits,
    ) {}

    public static function trial(?array $overrides = null): self
    {
        $config = array_replace((array) config('hosting.trial'), $overrides ?? []);
        $limits = [];
        foreach (self::LIMITS as $key) {
            $limits[$key] = isset($config[$key]) ? (int) $config[$key] : null;
        }

        return new self('trial', 'trial', (int) ($config['days'] ?? 14), $limits);
    }
}
