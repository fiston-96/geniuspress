<?php

return [
    'ai' => [
        // Modèle Claude utilisé par l'assistant.
        'model' => env('GP_AI_MODEL', 'claude-opus-5-5'),
        // Adresse de l'API (explicite pour ne pas hériter d'une variable d'environnement locale).
        'base_url' => env('GP_AI_BASE_URL', 'https://api.anthropic.com'),
        // Clé de secours si aucune n'est enregistrée dans les réglages du site.
        'api_key' => env('ANTHROPIC_API_KEY'),
        // Nombre maximal de requêtes IA par utilisateur et par minute.
        'per_minute' => (int) env('GP_AI_PER_MINUTE', 10),
    ],
];
