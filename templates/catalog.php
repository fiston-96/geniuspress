<?php

/*
 * Catalogue des modèles de sites. Un modèle = un pack de secteur
 * (templates/sectors/{pack}.php) + un thème. Construire avec :
 *
 *   php artisan geniuspress:templates
 *
 * Couleurs : primary (principale), secondary (foncée), accent (mise en avant),
 * light (fonds clairs), text, muted (texte secondaire).
 */

use App\Templates\Theme;

return [
    'ong-horizon' => [
        'sector' => 'ong',
        'name' => ['fr' => 'Horizon', 'en' => 'Horizon'],
        'description' => [
            'fr' => 'Moderne et lumineux : grande photo d’accueil, cartes aérées, mise en avant des dons.',
            'en' => 'Modern and bright: large welcome photo, airy cards, donations front and center.',
        ],
        'tags' => ['moderne', 'dons', 'programmes'],
        'theme' => new Theme(
            colors: ['primary' => '#0f766e', 'secondary' => '#0b2a33', 'accent' => '#f59e0b', 'light' => '#f0f7f6', 'text' => '#0f172a', 'muted' => '#475569'],
            font: 'Inter', headingFont: 'Poppins', hero: 'split', cards: 'shadow', buttons: 'pill', radius: 16,
        ),
    ],
    'ong-espoir' => [
        'sector' => 'ong',
        'name' => ['fr' => 'Espoir', 'en' => 'Hope'],
        'description' => [
            'fr' => 'Chaleureux et engagé : photo plein écran, couleurs terre, cartes encadrées.',
            'en' => 'Warm and committed: full-screen photo, earthy colors, outlined cards.',
        ],
        'tags' => ['chaleureux', 'humanitaire', 'photo'],
        'theme' => new Theme(
            colors: ['primary' => '#b45309', 'secondary' => '#3b1f0e', 'accent' => '#facc15', 'light' => '#fdf6ec', 'text' => '#1c1917', 'muted' => '#57534e'],
            font: 'Nunito', headingFont: 'Merriweather', hero: 'image', cards: 'border', buttons: 'rounded', radius: 10,
        ),
    ],
];
