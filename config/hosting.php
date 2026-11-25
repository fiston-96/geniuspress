<?php

use App\Hosting\HostedBootstrap;

// Essai hébergé : sites GeniusPress créés pour les comptes de la plateforme.
// Conception : docs/developpeurs/essai-heberge.md.
return [
    // Parcours « Essayer GeniusPress » (/essayer), commandes et tâche planifiée.
    // Désactivé par défaut : seule la plateforme héberge des sites.
    'enabled' => (bool) env('GP_HOSTING_ENABLED', false),

    // Dossier des sites hébergés. Variable d'environnement du serveur (GP_HOSTING_PATH),
    // lue avant le .env : voir HostedBootstrap.
    'path' => HostedBootstrap::root(dirname(__DIR__)),

    // Adresse de chaque site : {site}.{domain}. En local : localhost, http, port 8000.
    'domain' => strtolower((string) env('GP_HOSTING_DOMAIN', 'localhost')),
    'scheme' => env('GP_HOSTING_SCHEME', 'https'),
    'port' => env('GP_HOSTING_PORT'),

    // Middleware des pages de la plateforme (garde des comptes plateforme).
    'middleware' => ['web', 'auth'],

    // Base de chaque site : mysql (une base et un utilisateur MySQL par site) ou sqlite.
    // Le compte indiqué doit pouvoir créer des bases et des utilisateurs.
    'database' => [
        'driver' => env('GP_HOSTING_DB', 'mysql'),
        'host' => env('GP_HOSTING_DB_HOST', env('DB_HOST', '127.0.0.1')),
        'port' => env('GP_HOSTING_DB_PORT', env('DB_PORT', '3306')),
        'username' => env('GP_HOSTING_DB_USERNAME', env('DB_USERNAME', 'root')),
        'password' => env('GP_HOSTING_DB_PASSWORD', env('DB_PASSWORD', '')),
        'prefix' => 'gpe_',
        // Hôte des utilisateurs MySQL créés (« % » si PHP et MySQL sont sur des machines différentes).
        'user_host' => env('GP_HOSTING_DB_USER_HOST', 'localhost'),
    ],

    // Clé API Anthropic de la plateforme, copiée dans chaque site (jamais envoyée au navigateur).
    'ai_key' => env('GP_HOSTING_AI_KEY'),

    // Envoi des e-mails des sites hébergés (formulaires de contact).
    'mailer' => env('GP_HOSTING_MAILER', 'log'),

    // File d'attente pour créer les sites (null : juste après la réponse, sans worker).
    'queue' => env('GP_HOSTING_QUEUE'),

    // Exécutable PHP en ligne de commande (null : détecté).
    'php' => env('GP_HOSTING_PHP'),

    // Sites en cours (actifs, terminés, suspendus, en préparation) au plus sur ce serveur.
    'max_sites' => (int) env('GP_HOSTING_MAX_SITES', 100),

    // Essais gratuits par compte.
    'trials_per_account' => 1,

    // Page « choisir une offre » (en attendant la plateforme commerciale).
    'upgrade_url' => env('GP_HOSTING_UPGRADE_URL'),

    // Offre d'essai (null = illimité).
    'trial' => [
        'days' => (int) env('GP_TRIAL_DAYS', 14),
        // Jours en lecture seule après la fin, avant suppression des données.
        'grace_days' => (int) env('GP_TRIAL_GRACE_DAYS', 30),
        'pages' => 40,
        'posts' => 40,
        'media_mb' => 150,
        'ai_requests' => 40,
        'ai_tokens' => 400000,
    ],
];
