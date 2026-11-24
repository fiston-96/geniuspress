<?php

return [
    // Version du cœur, comparée à « requires.geniuspress » dans plugin.json.
    'version' => '0.1.0',

    // Langues disponibles pour l'interface d'administration et le builder.
    // Pour en ajouter une : créer lang/{code}.json et lang/{code}/*.php.
    'ui_locales' => [
        'fr' => 'Français',
        'en' => 'English',
    ],

    // Fichier verrou écrit à la fin de l'installation, et fichier .env modifié par l'installateur.
    'install_lock' => env('GP_INSTALL_LOCK', storage_path('app/installed.lock')),
    'env_path' => base_path('.env'),
    // Fichier de base de données quand on choisit SQLite à l'installation.
    'sqlite_path' => database_path('geniuspress.sqlite'),

    // Dossier des plugins.
    'plugins_path' => env('GP_PLUGINS_PATH', base_path('plugins')),

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
