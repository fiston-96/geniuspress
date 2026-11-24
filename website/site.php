<?php

/*
 * Site officiel geniuspress.site, construit avec GeniusPress lui-même.
 *
 *   php artisan geniuspress:website            → dist/geniuspress-site.zip
 *   php artisan geniuspress:website --import   → importé dans le site local
 *
 * La documentation est lue directement dans docs/ : modifier un fichier
 * Markdown puis relancer la commande suffit à mettre le site à jour.
 */

use App\Website\PageKit as K;

$repo = 'https://github.com/fiston-96/geniuspress';
$base = dirname(__DIR__);

// Couleurs de la marque (docs/marque.md)
$indigo = '#4f46e5';
$ambre = '#f59e0b';
$nuit = '#0f172a';
$lavande = '#eef2ff';
$gris = '#f8fafc';

// ─── Documentation : pages générées depuis les fichiers Markdown ─────────

$docs = [
    'docs/installation.md' => 'documentation/installation',
    'docs/guide/sauvegarde.md' => 'documentation/sauvegarde',
    'docs/developpeurs/plugins.md' => 'documentation/plugins',
    'docs/marque.md' => 'documentation/marque',
];
$docTitles = [
    'documentation' => 'Vue d’ensemble',
    'documentation/installation' => 'Installer GeniusPress',
    'documentation/sauvegarde' => 'Sauvegarde, export et import',
    'documentation/plugins' => 'Créer un plugin',
    'documentation/marque' => 'Marque et logo',
];
$docNav = K::links('Documentation', array_combine(array_values($docTitles), array_map(fn ($s) => '/'.$s, array_keys($docTitles))), ['style' => 'boxed']);

// Liens relatifs du Markdown : vers la page du site si elle existe, sinon vers GitHub.
$linkFor = function (string $from) use ($docs, $repo) {
    return function (string $href) use ($from, $docs, $repo) {
        [$path, $anchor] = array_pad(explode('#', $href, 2), 2, null);
        $parts = [];
        foreach (explode('/', dirname($from).'/'.$path) as $seg) {
            if ($seg === '..') {
                array_pop($parts);
            } elseif ($seg !== '.' && $seg !== '') {
                $parts[] = $seg;
            }
        }
        $resolved = implode('/', $parts);
        if (isset($docs[$resolved])) {
            return '/'.$docs[$resolved].($anchor ? '#'.$anchor : '');
        }

        return $repo.'/blob/main/'.$resolved.($anchor ? '#'.$anchor : '');
    };
};

$docPage = function (string $slug, string $title, string $html, string $description) use ($docNav) {
    return [
        'slug' => $slug,
        'title' => $title,
        'meta_title' => $title.' · Documentation GeniusPress',
        'meta_description' => $description,
        'content' => ['sections' => [
            K::section([
                K::col([$docNav], ['width' => 26]),
                K::col([
                    K::h($title, 'h1', ['font_size' => 40, 'font_size_mobile' => 30, 'margin_bottom' => 24]),
                    K::p($html, ['font_size' => 16]),
                ], ['width' => 74]),
            ], ['padding_top' => 48, 'padding_bottom' => 72, 'gap' => 48, 'vertical_align' => 'start', 'max_width' => 1180]),
        ]],
    ];
};

$descriptions = [
    'documentation/installation' => 'Installer GeniusPress chez un hébergeur, sur un VPS, avec Docker ou en local hors ligne : prérequis, assistant, ligne de commande.',
    'documentation/sauvegarde' => 'Exporter tout votre site dans un fichier, le restaurer ou le déplacer vers une autre installation de GeniusPress.',
    'documentation/plugins' => 'Créer un plugin GeniusPress : structure, widgets, hooks, traductions et sécurité.',
    'documentation/marque' => 'Le logo GeniusPress, ses couleurs, sa typographie et ses règles d’usage.',
];

$docPages = [];
foreach ($docs as $file => $slug) {
    $md = K::markdown((string) file_get_contents($base.'/'.$file), $linkFor($file));
    $docPages[] = $docPage($slug, $docTitles[$slug] ?? $md['title'], $md['html'], $descriptions[$slug]);
}

// ─── Éléments réutilisés ─────────────────────────────────────────────────

$card = fn (string $title, string $text, string $bg = '#ffffff') => K::col([
    K::h($title, 'h3', ['font_size' => 20, 'margin_bottom' => 8]),
    K::p('<p>'.$text.'</p>', ['color' => '#475569']),
], ['padding' => 28, 'background_color' => $bg, 'border_radius' => 16]);

$hero = fn (string $title, string $intro, array $extra = [], array $settings = []) => K::section([
    K::h($title, 'h1', ['align' => 'center', 'font_size' => 52, 'font_size_mobile' => 34, 'margin_bottom' => 18]),
    K::p('<p>'.$intro.'</p>', ['align' => 'center', 'font_size' => 19, 'color' => '#475569', 'margin_bottom' => $extra ? 28 : 0]),
    ...$extra,
], $settings + ['background_color' => $lavande, 'padding_top' => 88, 'padding_bottom' => 72, 'padding_top_mobile' => 56, 'padding_bottom_mobile' => 48, 'max_width' => 860]);

$ctaBand = K::section([
    K::h('Libre, ouvert, construit avec vous', 'h2', ['align' => 'center', 'color' => '#ffffff', 'margin_bottom' => 12]),
    K::p('<p>GeniusPress est publié sous licence AGPL-3.0. Le code, la documentation et la feuille de route sont publics : proposez une amélioration, traduisez l’interface dans votre langue ou créez un plugin.</p>', ['align' => 'center', 'color' => '#e0e7ff', 'font_size' => 18, 'margin_bottom' => 28]),
    K::btn('Contribuer', '/contribuer', ['align' => 'center', 'size' => 'lg', 'bg_color' => $ambre, 'text_color' => $nuit, 'margin_bottom' => 12]),
    K::btn('Voir le code sur GitHub', $repo, ['align' => 'center', 'variant' => 'outline', 'bg_color' => '#ffffff', 'text_color' => '#ffffff', 'new_tab' => true]),
], ['background_color' => $indigo, 'padding_top' => 80, 'padding_bottom' => 80, 'max_width' => 760]);

// ─── Pages ───────────────────────────────────────────────────────────────

$pages = [];

$pages[] = [
    'slug' => 'accueil',
    'title' => 'Accueil',
    'is_home' => true,
    'meta_title' => 'GeniusPress · Le CMS open source avec page builder et assistant IA',
    'meta_description' => 'Construisez le site de votre organisation sans coder : page builder visuel, assistant IA, multilingue, plugins. Libre et installable partout.',
    'content' => ['sections' => [
        K::section([
            K::h('Construisez le site de votre organisation, sans coder.', 'h1', ['align' => 'center', 'color' => '#ffffff', 'font_size' => 58, 'font_size_mobile' => 36, 'margin_bottom' => 20]),
            K::p('<p>GeniusPress est un CMS libre avec un <strong>page builder visuel</strong> et un <strong>assistant IA</strong>. Il s’installe chez un hébergeur, sur un serveur ou sur un ordinateur sans Internet, et vos données restent à vous.</p>', ['align' => 'center', 'color' => '#cbd5e1', 'font_size' => 20, 'margin_bottom' => 32]),
            K::btn('Télécharger GeniusPress', '/telecharger', ['align' => 'center', 'size' => 'lg', 'bg_color' => $ambre, 'text_color' => $nuit, 'margin_bottom' => 12]),
            K::btn('Lire la documentation', '/documentation', ['align' => 'center', 'variant' => 'outline', 'bg_color' => '#ffffff', 'text_color' => '#ffffff', 'margin_bottom' => 20]),
            K::p('<p>Version 0.1 · préversion · licence AGPL-3.0</p>', ['align' => 'center', 'color' => '#94a3b8', 'font_size' => 14]),
        ], ['background_color' => $nuit, 'text_color' => '#ffffff', 'padding_top' => 120, 'padding_bottom' => 110, 'padding_top_mobile' => 72, 'padding_bottom_mobile' => 64, 'max_width' => 900]),

        K::section([
            K::h('Tout ce qu’il faut pour un vrai site', 'h2', ['align' => 'center', 'margin_bottom' => 12]),
            K::p('<p>Les outils d’un grand page builder, sans abonnement ni dépendance à une plateforme.</p>', ['align' => 'center', 'color' => '#475569', 'font_size' => 18]),
        ], ['padding_top' => 88, 'padding_bottom' => 32, 'max_width' => 760]),
        K::section([
            $card('Builder visuel', 'Glissez des sections, des colonnes et des blocs. Aperçu ordinateur, tablette et mobile, annuler et rétablir, historique des versions.', $gris),
            $card('Assistant IA', 'Décrivez la page voulue : l’assistant la construit. Il rédige, corrige, traduit et vérifie le référencement et l’accessibilité.', $gris),
            $card('Multilingue', 'Français, anglais, swahili, lingala… Une version de chaque page par langue, liées entre elles, et une administration traduite.', $gris),
        ], ['padding_top' => 0, 'padding_bottom' => 24, 'gap' => 24]),
        K::section([
            $card('Plugins', 'Ajoutez des widgets et des fonctionnalités. Un plugin qui plante est désactivé tout seul, sans casser le site.', $gris),
            $card('Vos données vous appartiennent', 'Exportez tout le site dans un seul fichier et importez-le ailleurs. Vous n’êtes jamais prisonnier.', $gris),
            $card('S’installe partout', 'Hébergement mutualisé, VPS, Docker, ou ordinateur hors ligne avec SQLite. L’assistant d’installation fait le reste.', $gris),
        ], ['padding_top' => 0, 'padding_bottom' => 88, 'gap' => 24]),

        K::section([
            K::col([
                K::h('Pensé pour les organisations africaines', 'h2', ['margin_bottom' => 16]),
                K::p('<p>GeniusPress est né en RDC pour répondre à une réalité : les ONG, les entreprises et les écoles ont besoin d’un site professionnel, dans plusieurs langues, sans budget d’agence et parfois avec une connexion Internet instable.</p><p>L’assistant IA écrit à partir de la présentation de votre organisation, n’invente jamais de chiffres ni de contacts, et laisse des espaces à compléter.</p>', ['font_size' => 17, 'color' => '#334155']),
            ], ['width' => 55]),
            K::col([
                K::links('Pour qui ?', [
                    'ONG et associations' => '/fonctionnalites#ong',
                    'PME et commerçants' => '/fonctionnalites',
                    'Écoles et universités' => '/fonctionnalites',
                    'Églises et communautés' => '/fonctionnalites',
                    'Projets et événements' => '/fonctionnalites',
                ], ['style' => 'boxed']),
            ], ['width' => 45]),
        ], ['background_color' => $lavande, 'padding_top' => 88, 'padding_bottom' => 88, 'gap' => 48, 'vertical_align' => 'center']),

        K::section([
            K::h('En ligne en trois étapes', 'h2', ['align' => 'center']),
        ], ['padding_top' => 88, 'padding_bottom' => 24]),
        K::section([
            $card('1. Installez', 'Envoyez les fichiers chez votre hébergeur ou lancez Docker. L’assistant vérifie le serveur et prépare la base de données.'),
            $card('2. Construisez', 'Partez d’une page d’exemple, d’une page vide ou d’une description donnée à l’assistant IA. Ajustez tout dans le builder.'),
            $card('3. Publiez', 'Publiez quand vous êtes prêt : les modifications restent en brouillon tant que vous ne les mettez pas en ligne.'),
        ], ['padding_top' => 0, 'padding_bottom' => 88, 'gap' => 24]),

        $ctaBand,
    ]],
];

$pages[] = [
    'slug' => 'fonctionnalites',
    'title' => 'Fonctionnalités',
    'meta_title' => 'Fonctionnalités · GeniusPress',
    'meta_description' => 'Page builder visuel, assistant IA, site multilingue, plugins, export complet, installation partout : tout ce que fait GeniusPress.',
    'content' => ['sections' => [
        $hero('Fonctionnalités', 'Ce que GeniusPress sait déjà faire dans sa version 0.1, et ce qui arrive ensuite.'),
        K::section([
            K::col([K::h('Builder visuel', 'h2', ['margin_bottom' => 12]), K::p('<ul><li>Glisser-déposer de sections de 1 à 4 colonnes et de widgets : titre, texte, image, bouton, espace, séparateur, vidéo, liste de liens.</li><li>Réglages de style par élément, avec des valeurs spécifiques au mobile.</li><li>Aperçu ordinateur, tablette et mobile au rendu réel.</li><li>Annuler, rétablir, raccourcis clavier, historique des 30 dernières versions.</li><li>Brouillon séparé de la version en ligne : rien ne change pour les visiteurs avant la publication.</li></ul>')], ['width' => 50]),
            K::col([K::h('Assistant IA', 'h2', ['margin_bottom' => 12]), K::p('<ul><li>Création d’une page complète à partir d’une description.</li><li>Rédaction et réécriture : améliorer, raccourcir, développer, corriger, changer de ton.</li><li>Traduction d’une page dans une autre langue, en gardant la mise en page.</li><li>Audit SEO et accessibilité, avec des corrections en langage simple et les textes alternatifs des images.</li><li>Basé sur Claude ; la clé API reste chiffrée sur votre serveur.</li></ul>')], ['width' => 50]),
        ], ['padding_top' => 72, 'padding_bottom' => 40, 'gap' => 48]),
        K::section([
            K::col([K::h('Gestion du site', 'h2', ['margin_bottom' => 12]), K::p('<ul><li>Pages, médiathèque, référencement par page.</li><li>En-tête, menus et pied de page par langue.</li><li>Administration en français et en anglais, langue choisie par chaque utilisateur.</li><li>Rôles administrateur et éditeur.</li></ul>')], ['width' => 50]),
            K::col([K::h('Ouvert et extensible', 'h2', ['margin_bottom' => 12]), K::p('<ul><li>Système de plugins : widgets, routes, hooks, traductions.</li><li>Un plugin défaillant est désactivé automatiquement.</li><li>Export et import complets du site.</li><li>Installation par navigateur, ligne de commande, paquet zip ou Docker.</li></ul>')], ['width' => 50]),
        ], ['padding_top' => 24, 'padding_bottom' => 72, 'gap' => 48]),
        K::section([
            K::h('Pour les ONG', 'h2', ['margin_bottom' => 12]),
            K::p('<p>Pages de projets, chiffres d’impact, appels aux dons, contenus en plusieurs langues pour vos partenaires et vos bénéficiaires. Le plugin officiel de dons avec Mobile Money et carte bancaire est prévu dans la prochaine phase.</p>', ['font_size' => 17]),
        ], ['background_color' => $lavande, 'padding_top' => 64, 'padding_bottom' => 64, 'max_width' => 860, 'anchor' => 'ong']),
        $ctaBand,
    ]],
];

$pages[] = [
    'slug' => 'telecharger',
    'title' => 'Télécharger',
    'meta_title' => 'Télécharger GeniusPress',
    'meta_description' => 'Installez GeniusPress chez un hébergeur mutualisé, sur un VPS avec Docker, en ligne de commande ou en local.',
    'content' => ['sections' => [
        $hero('Télécharger GeniusPress', 'Quatre façons d’installer, de l’hébergement mutualisé à l’ordinateur hors ligne.', [
            K::p('<p><strong>Préversion 0.1.</strong> GeniusPress est en développement actif : utilisez-le pour essayer et contribuer, pas encore pour un site critique.</p>', ['align' => 'center', 'color' => '#92400e', 'font_size' => 15]),
        ]),
        K::section([
            $card('Hébergement mutualisé', 'Le paquet zip contient déjà tout le nécessaire, sans Composer ni Node.js. Il sera publié avec la version 1.0 ; en attendant, fabriquez-le avec <code>php artisan geniuspress:package</code>.', $gris),
            $card('Docker', 'GeniusPress et MariaDB en une commande : <code>docker compose up -d</code>, puis ouvrez <code>http://localhost:8080</code>.', $gris),
        ], ['padding_top' => 64, 'padding_bottom' => 24, 'gap' => 24]),
        K::section([
            $card('Ligne de commande', 'Sur un VPS : <code>php artisan geniuspress:install</code>, avec des questions ou entièrement par options.', $gris),
            $card('Depuis le code source', 'Pour développer : clonez le dépôt, puis <code>composer install</code>, <code>npm install &amp;&amp; npm run build</code> et <code>php artisan serve</code>.', $gris),
        ], ['padding_top' => 0, 'padding_bottom' => 48, 'gap' => 24]),
        K::section([
            K::h('Ce qu’il faut sur le serveur', 'h2', ['margin_bottom' => 12]),
            K::p('<table><thead><tr><th>Élément</th><th>Minimum</th></tr></thead><tbody><tr><td>PHP</td><td>8.3 ou plus récent</td></tr><tr><td>Base de données</td><td>MySQL 8, MariaDB 10.6+ ou SQLite</td></tr><tr><td>Extensions conseillées</td><td><code>intl</code>, <code>curl</code>, <code>gd</code>, <code>zip</code></td></tr></tbody></table><p>Le guide complet est dans la <a href="/documentation/installation">documentation d’installation</a>.</p>'),
            K::space(16),
            K::btn('Code source sur GitHub', $repo, ['size' => 'lg', 'new_tab' => true]),
        ], ['padding_top' => 24, 'padding_bottom' => 88, 'max_width' => 860]),
    ]],
];

$pages[] = $docPage('documentation', 'Documentation', implode('', [
    '<p>Bienvenue dans la documentation de GeniusPress. Elle est écrite en français, puis traduite, et vit dans le dépôt à côté du code : chaque amélioration est relue comme du code.</p>',
    '<h2>Utiliser GeniusPress</h2><ul><li><a href="/documentation/installation">Installer GeniusPress</a> : hébergement mutualisé, VPS, Docker, local hors ligne.</li><li><a href="/documentation/sauvegarde">Sauvegarde, export et import</a> : vos données vous appartiennent.</li></ul>',
    '<h2>Développer</h2><ul><li><a href="/documentation/plugins">Créer un plugin</a> : widgets, hooks, routes, traductions.</li><li><a href="'.$repo.'">Code source et tickets sur GitHub</a>.</li></ul>',
    '<h2>Le projet</h2><ul><li><a href="/documentation/marque">Marque et logo</a>.</li><li><a href="/feuille-de-route">Feuille de route</a>.</li><li><a href="/contribuer">Contribuer</a>.</li></ul>',
]), 'Documentation de GeniusPress : installation, sauvegarde, plugins, marque.');

array_push($pages, ...$docPages);

$pages[] = [
    'slug' => 'feuille-de-route',
    'title' => 'Feuille de route',
    'meta_title' => 'Feuille de route · GeniusPress',
    'meta_description' => 'Ce qui est fait, ce qui est en cours et ce qui arrive dans GeniusPress, jusqu’à la version 1.0.',
    'content' => ['sections' => [
        $hero('Feuille de route', 'Le développement se fait en phases courtes. Chaque phase se termine quand son critère de fin est atteint, pas à une date fixe.'),
        K::section([
            K::p(implode('', [
                '<h2>MVP · terminé</h2><p>Builder visuel, assistant IA, administration, médiathèque, site multilingue.</p>',
                '<h2>Phase 1 · fondations open source · presque terminée</h2><ul><li>Système de plugins et registre de widgets.</li><li>Interface traduite (français, anglais).</li><li>Installateur depuis zéro, paquet zip, Docker.</li><li>Export et import de site.</li><li>Tests automatiques sur GitHub (en attente de validation).</li></ul>',
                '<h2>Phase 2 · un vrai site complet</h2><ul><li>En-tête, menus et pied de page (déjà disponibles).</li><li>Plugins officiels : formulaire de contact, dons et paiements (Mobile Money, carte).</li><li>Nouveaux widgets et modèles de pages.</li><li>Interface en swahili et en lingala.</li></ul>',
                '<h2>Phase 3 · hébergement et plateforme</h2><ul><li>Déploiement sur VPS avec sauvegardes automatiques.</li><li>Plateforme multi-sites avec domaines personnalisés.</li><li>Mises à jour en un clic, export en site statique.</li></ul>',
                '<h2>Phase 4 · version 1.0</h2><p>Documentation complète en français et en anglais, lancement public.</p>',
            ]), ['font_size' => 17]),
        ], ['padding_top' => 64, 'padding_bottom' => 88, 'max_width' => 860]),
    ]],
];

$pages[] = [
    'slug' => 'contribuer',
    'title' => 'Contribuer',
    'meta_title' => 'Contribuer à GeniusPress',
    'meta_description' => 'Code, traductions, plugins, documentation, signalement de failles : toutes les façons de contribuer à GeniusPress.',
    'content' => ['sections' => [
        $hero('Contribuer', 'GeniusPress est un projet libre. Toute aide compte, que vous sachiez coder ou non.'),
        K::section([
            $card('Traduire', 'L’interface est en français et en anglais. Aidez-nous à la traduire en swahili, en lingala, en kinyarwanda… sans écrire de code : un seul fichier de textes par langue.', $gris),
            $card('Créer un plugin', 'Un formulaire, une galerie, une intégration Mobile Money : <a href="/documentation/plugins">le guide des plugins</a> explique tout, avec une commande qui crée un plugin d’exemple.', $gris),
            $card('Améliorer le code', 'Corrigez un bogue ou proposez une fonctionnalité sur <a href="'.$repo.'">GitHub</a>. Chaque proposition est vérifiée par les tests automatiques.', $gris),
        ], ['padding_top' => 64, 'padding_bottom' => 24, 'gap' => 24]),
        K::section([
            $card('Documenter', 'La documentation vit dans le dossier <code>docs/</code> du dépôt. Une explication plus claire aide des centaines d’utilisateurs.', $gris),
            $card('Signaler une faille', 'Vous avez trouvé une faille de sécurité ? Ne la publiez pas : écrivez-nous en privé pour qu’elle soit corrigée avant d’être rendue publique.', $gris),
            $card('En parler', 'Présentez GeniusPress à une ONG, une école ou une entreprise qui a besoin d’un site. C’est la meilleure façon de faire grandir le projet.', $gris),
        ], ['padding_top' => 0, 'padding_bottom' => 88, 'gap' => 24]),
    ]],
];

// ─── Réglages du site ────────────────────────────────────────────────────

return [
    'settings' => [
        'site_name' => 'GeniusPress',
        'site_tagline' => 'Le CMS open source qui construit votre site avec vous',
        'organization_description' => 'GeniusPress est un CMS open source (AGPL-3.0) avec un page builder visuel et un assistant IA, créé en RDC pour les entreprises et les ONG africaines. Public : responsables de communication, développeurs, contributeurs open source. Ton : clair, chaleureux, précis, sans jargon.',
        'primary_color' => $indigo,
        'secondary_color' => $nuit,
        'accent_color' => $ambre,
        'font_family' => 'Inter',
        'locales' => ['fr'],
        'logo_url' => '/brand/geniuspress-mark.svg',
        'favicon_url' => '/favicon.svg',
        'navigation' => [
            'header' => ['enabled' => true, 'sticky' => true, 'style' => 'light', 'cta_url' => '/telecharger'],
            'footer' => ['enabled' => true, 'credit' => false],
            'locales' => [
                'fr' => [
                    'menu' => [
                        ['label' => 'Fonctionnalités', 'url' => '/fonctionnalites'],
                        ['label' => 'Documentation', 'url' => '/documentation'],
                        ['label' => 'Feuille de route', 'url' => '/feuille-de-route'],
                        ['label' => 'Contribuer', 'url' => '/contribuer'],
                    ],
                    'cta_label' => 'Télécharger',
                    'footer_text' => 'GeniusPress est un CMS libre (AGPL-3.0) avec un page builder visuel et un assistant IA, pensé pour les entreprises et les ONG africaines.',
                    'footer_links' => [
                        ['label' => 'Télécharger', 'url' => '/telecharger'],
                        ['label' => 'Documentation', 'url' => '/documentation'],
                        ['label' => 'Créer un plugin', 'url' => '/documentation/plugins'],
                        ['label' => 'Marque et logo', 'url' => '/documentation/marque'],
                        ['label' => 'Code source', 'url' => $repo],
                        ['label' => 'Licence', 'url' => $repo.'/blob/main/LICENSE'],
                    ],
                ],
            ],
        ],
    ],
    'pages' => $pages,
];
