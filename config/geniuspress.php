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
    // null = fichier .env de l'application (voir GP_ENV_DIR dans bootstrap/app.php).
    'env_path' => null,
    // Fichier de base de données quand on choisit SQLite à l'installation.
    'sqlite_path' => database_path('geniuspress.sqlite'),

    // Dossier des plugins.
    'plugins_path' => env('GP_PLUGINS_PATH', base_path('plugins')),

    // Catalogue des modèles de sites (voir app/Templates et templates/).
    'catalog' => [
        // Adresse du catalogue en ligne consulté par les sites GeniusPress.
        'url' => env('GP_CATALOG_URL', 'https://geniuspress.site/catalogue/v1'),
        // Dossier où « geniuspress:templates » construit le catalogue, servi par geniuspress.site.
        'path' => env('GP_CATALOG_PATH', base_path('dist/catalog/v1')),
        // Démonstrations en ligne des modèles sur /catalogue/{modèle} (toujours actives en local).
        'demos' => (bool) env('GP_CATALOG_DEMOS', false),
        // Lire le catalogue dans « path » plutôt qu'en ligne (par défaut : en local, s'il a été construit).
        'local' => env('GP_CATALOG_LOCAL'),
        // Adresse des démonstrations quand le catalogue est en ligne.
        'demo_url' => env('GP_CATALOG_DEMO_URL', 'https://geniuspress.site/catalogue'),
        // Taille maximale d'un modèle téléchargé (octets).
        'max_bytes' => 50 * 1024 * 1024,
        // Photos des modèles (Pixabay ou Pexels) : choix et crédits versionnés, fichiers re-téléchargeables.
        'photos_path' => env('GP_TEMPLATE_PHOTOS', base_path('templates/photos')),
        // Clés des API de photos (gratuites), seulement pour préparer le catalogue :
        // Pixabay (https://pixabay.com/api/docs/) ou Pexels (https://www.pexels.com/api/).
        'pixabay_key' => env('PIXABAY_API_KEY'),
        'pexels_key' => env('PEXELS_API_KEY'),
    ],

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

    // Plateforme commerciale (app/Platform, docs/developpeurs/plateforme-commerciale.md).
    'platform' => [
        // Active la plateforme (comptes clients, offres, relais IA) sur ce serveur. Désactivée par défaut.
        'enabled' => (bool) env('GP_PLATFORM', false),
        // Devise par défaut des offres.
        'currency' => env('GP_PLATFORM_CURRENCY', 'USD'),
        // Prestataire de paiement et registraire de domaines (« sandbox » : tests, aucun appel réel).
        'payment_gateway' => env('GP_PAYMENT_GATEWAY', 'sandbox'),
        'domain_registrar' => env('GP_DOMAIN_REGISTRAR', 'sandbox'),
        // Paiements et domaines simulés : jamais en production, sauf GP_PLATFORM_SANDBOX=true.
        'sandbox' => env('GP_PLATFORM_SANDBOX'),
        // Relais IA : demandes par minute et par licence, plafond de jetons de sortie, taille maximale d'une demande.
        'relay_per_minute' => (int) env('GP_RELAY_PER_MINUTE', 30),
        'relay_max_tokens' => 32000,
        'relay_max_bytes' => 8 * 1024 * 1024,
        // Multiplicateur appliqué au coût réel de l'IA, si l'exploitant ne l'a pas réglé.
        'ai_multiplier' => 2,
    ],

    // Côté site d'un client : plateforme consultée et clé de licence (ou réglage « gp_license_key »).
    'license' => [
        'platform_url' => env('GP_PLATFORM_URL', 'https://geniuspress.site'),
        'key' => env('GP_LICENSE_KEY'),
    ],
];
