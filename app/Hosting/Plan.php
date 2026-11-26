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

    /**
     * Site gratuit sans limite (config hosting.unlimited) : sans date de fin, pages, articles et
     * médias illimités ; les quotas IA de l'essai restent, chaque demande ayant un coût réel.
     */
    public static function free(): self
    {
        $config = (array) config('hosting.trial');

        return new self('free', 'free', null, [
            'pages' => null, 'posts' => null, 'media_mb' => null,
            'ai_requests' => isset($config['ai_requests']) ? (int) $config['ai_requests'] : null,
            'ai_tokens' => isset($config['ai_tokens']) ? (int) $config['ai_tokens'] : null,
        ]);
    }

    /** Offre des nouveaux sites : gratuite sans limite si la plateforme l'a choisi, sinon l'essai. */
    public static function forNewSite(?array $trialOverrides = null): self
    {
        return config('hosting.unlimited') ? self::free() : self::trial($trialOverrides);
    }
}
