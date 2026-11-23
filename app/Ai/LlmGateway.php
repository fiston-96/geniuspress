<?php

namespace App\Ai;

/** Accès au modèle de langage. Permet de remplacer l'API par un faux dans les tests. */
interface LlmGateway
{
    /**
     * Envoie une requête et renvoie la réponse JSON (conforme au schéma) décodée.
     *
     * @param  array  $content  Blocs de contenu du message utilisateur (texte, images…)
     * @param  array  $schema  Schéma JSON de la réponse attendue
     * @return array{data: array, usage: array{input: int, output: int}, model: string}
     */
    public function json(string $system, array $content, array $schema, string $effort = 'medium', int $maxTokens = 16000): array;

    public function isConfigured(): bool;
}
