<?php

namespace App\Hosting;

use App\Ai\LlmGateway;

/**
 * Modèle de langage limité par les crédits IA du site hébergé (vérifiés avant
 * chaque appel ; la consommation est enregistrée dans ai_requests par l'assistant).
 * Hors d'un site hébergé, simple relais.
 */
class MeteredLlmGateway implements LlmGateway
{
    public function __construct(private LlmGateway $inner, private Quotas $quotas) {}

    public function json(string $system, array $content, array $schema, string $effort = 'medium', int $maxTokens = 16000): array
    {
        $this->quotas->checkAi();

        return $this->inner->json($system, $content, $schema, $effort, $maxTokens);
    }

    public function isConfigured(): bool
    {
        return $this->inner->isConfigured();
    }
}
