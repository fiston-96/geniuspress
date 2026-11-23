<?php

namespace App\Ai;

use Anthropic\Client;
use Anthropic\Core\Exceptions\APIConnectionException;
use Anthropic\Core\Exceptions\APIStatusException;
use Anthropic\Core\Exceptions\AuthenticationException;
use Anthropic\Core\Exceptions\PermissionDeniedException;
use Anthropic\Core\Exceptions\RateLimitException;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class AnthropicGateway implements LlmGateway
{
    public function isConfigured(): bool
    {
        return $this->apiKey() !== null;
    }

    public function json(string $system, array $content, array $schema, string $effort = 'medium', int $maxTokens = 16000): array
    {
        $key = $this->apiKey() ?? throw new AiException(__('L’assistant IA n’est pas configuré : ajoutez une clé API Anthropic dans Réglages.'));
        $model = config('geniuspress.ai.model');
        $client = new Client(apiKey: $key, baseUrl: config('geniuspress.ai.base_url'));

        try {
            $message = $client->beta->messages->create(
                model: $model,
                maxTokens: $maxTokens,
                system: $system,
                messages: [['role' => 'user', 'content' => $content]],
                outputConfig: [
                    'effort' => $effort,
                    'format' => ['type' => 'json_schema', 'schema' => $schema],
                ],
                // En cas de refus par les filtres de sécurité, l'API réessaie sur un modèle de repli.
                fallbacks: 'default',
                betas: ['server-side-fallback-2026-07-01'],
            );
        } catch (AuthenticationException|PermissionDeniedException) {
            throw new AiException(__('La clé API Anthropic est invalide ou n’a pas accès à ce modèle.'));
        } catch (RateLimitException) {
            throw new AiException(__('Trop de demandes envoyées à l’IA. Réessayez dans une minute.'));
        } catch (APIConnectionException) {
            throw new AiException(__('Impossible de joindre le service IA. Vérifiez la connexion Internet du serveur.'));
        } catch (APIStatusException $e) {
            report($e);
            throw new AiException(__('Le service IA a renvoyé une erreur. Réessayez dans un instant.'));
        }

        $stop = self::enumValue($message->stopReason);
        $usage = ['input' => (int) $message->usage->inputTokens, 'output' => (int) $message->usage->outputTokens];

        if ($stop === 'refusal') {
            throw new AiRefusedException(__('L’IA a refusé cette demande. Reformulez-la.'), $usage, (string) $model);
        }
        if ($stop === 'max_tokens') {
            throw new AiException(__('La réponse de l’IA était trop longue. Essayez une demande plus courte.'));
        }

        $text = '';
        foreach ($message->content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        try {
            $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw new AiException(__('La réponse de l’IA est illisible. Réessayez.'));
        }

        return ['data' => (array) $data, 'usage' => $usage, 'model' => (string) self::enumValue($message->model)];
    }

    private function apiKey(): ?string
    {
        $stored = Setting::read('anthropic_api_key');
        if ($stored) {
            try {
                return Crypt::decryptString($stored);
            } catch (Throwable) {
                // Clé illisible (APP_KEY changée) : on ignore et on essaie la variable d'environnement.
            }
        }

        return config('geniuspress.ai.api_key') ?: null;
    }

    private static function enumValue(mixed $v): ?string
    {
        return $v instanceof \BackedEnum ? (string) $v->value : ($v === null ? null : (string) $v);
    }
}
