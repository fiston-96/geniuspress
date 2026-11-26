<?php

namespace App\Platform\Client;

use App\Ai\AiException;
use App\Ai\AiRefusedException;
use App\Ai\LlmGateway;
use Illuminate\Http\Client\ConnectionException;
use Throwable;

/**
 * Côté site d'un client : l'IA passe par le relais de la plateforme GeniusPress
 * (crédits IA du compte client) au lieu d'appeler Anthropic directement.
 * Utilisée dès qu'une clé de licence est configurée (voir PlatformServiceProvider).
 */
class PlatformAiGateway implements LlmGateway
{
    public function __construct(private PlatformClient $client) {}

    public function isConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    public function json(string $system, array $content, array $schema, string $effort = 'medium', int $maxTokens = 16000): array
    {
        if (! $this->isConfigured()) {
            throw new AiException(__('L’assistant IA n’est pas configuré : ajoutez une clé API Anthropic dans Réglages.'));
        }

        try {
            $response = $this->client->request(300)->post('ai/messages', [
                'model' => (string) config('geniuspress.ai.model'),
                'system' => $system,
                'content' => $content,
                'schema' => $schema,
                'effort' => $effort,
                'max_tokens' => min($maxTokens, 32000),
                'action' => request()?->route()?->getName(),
            ]);
        } catch (ConnectionException) {
            throw new AiException(__('Impossible de joindre la plateforme GeniusPress. Vérifiez la connexion Internet du serveur.'));
        } catch (Throwable $e) {
            report($e);
            throw new AiException(__('L’assistant IA a rencontré une erreur. Réessayez.'));
        }

        $body = (array) $response->json();
        if ($response->successful() && is_array($body['data'] ?? null)) {
            return [
                'data' => $body['data'],
                'usage' => ['input' => (int) ($body['usage']['input'] ?? 0), 'output' => (int) ($body['usage']['output'] ?? 0)],
                'model' => (string) ($body['model'] ?? ''),
            ];
        }

        $code = (string) ($body['error'] ?? '');
        if ($code === 'refused') {
            throw new AiRefusedException(__('L’IA a refusé cette demande. Reformulez-la.'),
                ['input' => (int) ($body['usage']['input'] ?? 0), 'output' => (int) ($body['usage']['output'] ?? 0)], (string) ($body['model'] ?? ''));
        }

        // Messages du site lui-même (traduits), selon le code renvoyé par la plateforme.
        throw new AiException(match ($code) {
            'credits_exhausted' => __('Vos crédits IA sont épuisés. Rechargez-les dans votre espace client GeniusPress.'),
            'monthly_cap' => __('Le plafond mensuel de dépenses IA de ce site est atteint.'),
            'invalid_license' => __('La clé de licence GeniusPress est invalide ou a été révoquée.'),
            'suspended' => __('Ce compte est suspendu. Contactez-nous.'),
            'model_unavailable' => __('Le modèle d’IA :model n’est pas proposé par la plateforme. Modèles proposés : :models.', [
                'model' => (string) config('geniuspress.ai.model'),
                'models' => implode(', ', array_map('strval', (array) ($body['models'] ?? []))) ?: '—',
            ]),
            'too_large' => __('Cette demande est trop volumineuse pour l’IA.'),
            default => match (true) {
                $response->status() === 429 => __('Trop de demandes envoyées à l’IA. Réessayez dans une minute.'),
                default => __('Le service IA a renvoyé une erreur. Réessayez dans un instant.'),
            },
        });
    }
}
