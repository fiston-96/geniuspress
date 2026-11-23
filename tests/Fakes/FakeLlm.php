<?php

namespace Tests\Fakes;

use App\Ai\AiRefusedException;
use App\Ai\LlmGateway;

/** Faux modèle de langage pour les tests : renvoie des réponses préparées. */
class FakeLlm implements LlmGateway
{
    public array $calls = [];

    public function __construct(public array $responses = [], public bool $configured = true, public bool $refuse = false) {}

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    public function json(string $system, array $content, array $schema, string $effort = 'medium', int $maxTokens = 16000): array
    {
        $this->calls[] = compact('system', 'content', 'schema', 'effort', 'maxTokens');
        if ($this->refuse) {
            throw new AiRefusedException('L’IA a refusé cette demande. Reformulez-la.', ['input' => 10, 'output' => 0], 'fake');
        }

        return ['data' => array_shift($this->responses) ?? [], 'usage' => ['input' => 100, 'output' => 50], 'model' => 'fake-model'];
    }
}
