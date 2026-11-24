<?php

/*
 * Site officiel geniuspress.site, construit avec GeniusPress lui-même,
 * en français (langue principale) et en anglais (/en/…).
 *
 *   php artisan geniuspress:website            → dist/geniuspress-site.zip
 *   php artisan geniuspress:website --import   → importé dans le site local
 *
 * La documentation est lue directement dans docs/ (français) et docs/en/ (anglais) :
 * modifier un fichier Markdown puis relancer la commande suffit à mettre le site à jour.
 *
 * Chaque texte est écrit dans les deux langues côte à côte : $t('français', 'English').
 * Chaque page a une clé « group » commune à ses deux versions, qui les relie entre elles.
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

// Adresse de chaque page dans chaque langue, par clé de groupe.
$slugs = [
    'home' => ['fr' => 'accueil', 'en' => 'home'],
    'features' => ['fr' => 'fonctionnalites', 'en' => 'features'],
    'download' => ['fr' => 'telecharger', 'en' => 'download'],
    'docs' => ['fr' => 'documentation', 'en' => 'documentation'],
    'doc-installation' => ['fr' => 'documentation/installation', 'en' => 'documentation/installation'],
    'doc-backup' => ['fr' => 'documentation/sauvegarde', 'en' => 'documentation/backup'],
    'doc-plugins' => ['fr' => 'documentation/plugins', 'en' => 'documentation/plugins'],
    'doc-brand' => ['fr' => 'documentation/marque', 'en' => 'documentation/brand'],
    'roadmap' => ['fr' => 'feuille-de-route', 'en' => 'roadmap'],
    'contribute' => ['fr' => 'contribuer', 'en' => 'contribute'],
];

// Fichiers Markdown de la documentation, par langue.
$docFiles = [
    'fr' => [
        'doc-installation' => 'docs/installation.md',
        'doc-backup' => 'docs/guide/sauvegarde.md',
        'doc-plugins' => 'docs/developpeurs/plugins.md',
        'doc-brand' => 'docs/marque.md',
    ],
    'en' => [
        'doc-installation' => 'docs/en/installation.md',
        'doc-backup' => 'docs/en/guide/backup.md',
        'doc-plugins' => 'docs/en/developers/plugins.md',
        'doc-brand' => 'docs/en/brand.md',
    ],
];

$build = function (string $lang) use ($repo, $base, $slugs, $docFiles, $indigo, $ambre, $nuit, $lavande, $gris) {
    $t = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
    // Adresse d'une page du site (par clé de groupe), avec le préfixe /en en anglais.
    $u = function (string $key, string $anchor = '') use ($lang, $slugs) {
        $path = $key === 'home' ? '' : $slugs[$key][$lang];
        $url = $lang === 'fr' ? '/'.$path : '/en'.($path === '' ? '' : '/'.$path);

        return $url.($anchor ? '#'.$anchor : '');
    };
    $page = fn (string $key, string $title, string $metaTitle, string $description, array $sections, array $extra = []) => $extra + [
        'group' => $key,
        'locale' => $lang,
        'slug' => $slugs[$key][$lang],
        'title' => $title,
        'meta_title' => $metaTitle,
        'meta_description' => $description,
        'content' => ['sections' => $sections],
    ];

    // ─── Documentation ───────────────────────────────────────────────────
    $docTitles = [
        'docs' => $t('Vue d’ensemble', 'Overview'),
        'doc-installation' => $t('Installer GeniusPress', 'Installing GeniusPress'),
        'doc-backup' => $t('Sauvegarde, export et import', 'Backup, export and import'),
        'doc-plugins' => $t('Créer un plugin', 'Creating a plugin'),
        'doc-brand' => $t('Marque et logo', 'Brand and logo'),
    ];
    $docNavItems = [];
    foreach ($docTitles as $key => $label) {
        $docNavItems[$label] = $u($key);
    }
    $docNav = K::links('Documentation', $docNavItems, ['style' => 'boxed']);

    // Liens relatifs du Markdown : vers la page du site si elle existe, sinon vers GitHub.
    $byFile = array_flip($docFiles[$lang]);
    $linkFor = fn (string $from) => function (string $href) use ($from, $byFile, $repo, $u) {
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
        if (isset($byFile[$resolved])) {
            return $u($byFile[$resolved], (string) $anchor);
        }

        return $repo.'/blob/main/'.$resolved.($anchor ? '#'.$anchor : '');
    };

    $docPage = function (string $key, string $html, string $description) use ($page, $docTitles, $docNav, $t) {
        $title = $key === 'docs' ? 'Documentation' : $docTitles[$key];

        return $page($key, $title, $title.' · '.$t('Documentation GeniusPress', 'GeniusPress documentation'), $description, [
            K::section([
                K::col([$docNav], ['width' => 26]),
                K::col([
                    K::h($title, 'h1', ['font_size' => 40, 'font_size_mobile' => 30, 'margin_bottom' => 24]),
                    K::p($html, ['font_size' => 16]),
                ], ['width' => 74]),
            ], ['padding_top' => 48, 'padding_bottom' => 72, 'gap' => 48, 'vertical_align' => 'start', 'max_width' => 1180]),
        ]);
    };

    $descriptions = [
        'doc-installation' => $t(
            'Installer GeniusPress chez un hébergeur, sur un VPS, avec Docker ou en local hors ligne : prérequis, assistant, ligne de commande.',
            'Install GeniusPress on shared hosting, a VPS, with Docker or offline on your own computer: requirements, setup wizard, command line.'
        ),
        'doc-backup' => $t(
            'Exporter tout votre site dans un fichier, le restaurer ou le déplacer vers une autre installation de GeniusPress.',
            'Export your whole site to a single file, restore it, or move it to another GeniusPress installation.'
        ),
        'doc-plugins' => $t(
            'Créer un plugin GeniusPress : structure, widgets, hooks, traductions et sécurité.',
            'Build a GeniusPress plugin: structure, widgets, hooks, translations and security.'
        ),
        'doc-brand' => $t(
            'Le logo GeniusPress, ses couleurs, sa typographie et ses règles d’usage.',
            'The GeniusPress logo, its colors, typeface and usage rules.'
        ),
    ];

    $docPages = [];
    foreach ($docFiles[$lang] as $key => $file) {
        $md = K::markdown((string) file_get_contents($base.'/'.$file), $linkFor($file));
        $docPages[] = $docPage($key, $md['html'], $descriptions[$key]);
    }

    // ─── Éléments réutilisés ─────────────────────────────────────────────
    $card = fn (string $title, string $text, string $bg = '#ffffff') => K::col([
        K::h($title, 'h3', ['font_size' => 20, 'margin_bottom' => 8]),
        K::p('<p>'.$text.'</p>', ['color' => '#475569']),
    ], ['padding' => 28, 'background_color' => $bg, 'border_radius' => 16]);

    $hero = fn (string $title, string $intro, array $extra = []) => K::section([
        K::h($title, 'h1', ['align' => 'center', 'font_size' => 52, 'font_size_mobile' => 34, 'margin_bottom' => 18]),
        K::p('<p>'.$intro.'</p>', ['align' => 'center', 'font_size' => 19, 'color' => '#475569', 'margin_bottom' => $extra ? 28 : 0]),
        ...$extra,
    ], ['background_color' => $lavande, 'padding_top' => 88, 'padding_bottom' => 72, 'padding_top_mobile' => 56, 'padding_bottom_mobile' => 48, 'max_width' => 860]);

    $ctaBand = K::section([
        K::h($t('Libre, ouvert, construit avec vous', 'Free, open, built with you'), 'h2', ['align' => 'center', 'color' => '#ffffff', 'margin_bottom' => 12]),
        K::p('<p>'.$t(
            'GeniusPress est publié sous licence AGPL-3.0. Le code, la documentation et la feuille de route sont publics : proposez une amélioration, traduisez l’interface dans votre langue ou créez un plugin.',
            'GeniusPress is released under the AGPL-3.0 license. The code, the documentation and the roadmap are public: suggest an improvement, translate the interface into your language or build a plugin.'
        ).'</p>', ['align' => 'center', 'color' => '#e0e7ff', 'font_size' => 18, 'margin_bottom' => 28]),
        K::btn($t('Contribuer', 'Contribute'), $u('contribute'), ['align' => 'center', 'size' => 'lg', 'bg_color' => $ambre, 'text_color' => $nuit, 'margin_bottom' => 12]),
        K::btn($t('Voir le code sur GitHub', 'View the code on GitHub'), $repo, ['align' => 'center', 'variant' => 'outline', 'bg_color' => '#ffffff', 'text_color' => '#ffffff', 'new_tab' => true]),
    ], ['background_color' => $indigo, 'padding_top' => 80, 'padding_bottom' => 80, 'max_width' => 760]);

    $pages = [];

    // ─── Accueil ─────────────────────────────────────────────────────────
    $pages[] = $page('home', $t('Accueil', 'Home'),
        $t('GeniusPress · Le CMS open source avec page builder et assistant IA', 'GeniusPress · The open source CMS with a page builder and an AI assistant'),
        $t(
            'Construisez le site de votre organisation sans coder : page builder visuel, assistant IA, multilingue, plugins. Libre et installable partout.',
            'Build your organization’s website without code: visual page builder, AI assistant, multilingual, plugins. Free and installable anywhere.'
        ), [
            K::section([
                K::h($t('Construisez le site de votre organisation, sans coder.', 'Build your organization’s website, without code.'), 'h1', ['align' => 'center', 'color' => '#ffffff', 'font_size' => 58, 'font_size_mobile' => 36, 'margin_bottom' => 20]),
                K::p('<p>'.$t(
                    'GeniusPress est un CMS libre avec un <strong>page builder visuel</strong> et un <strong>assistant IA</strong>. Il s’installe chez un hébergeur, sur un serveur ou sur un ordinateur sans Internet, et vos données restent à vous.',
                    'GeniusPress is a free CMS with a <strong>visual page builder</strong> and an <strong>AI assistant</strong>. It runs on shared hosting, on a server or on a computer without Internet, and your data stays yours.'
                ).'</p>', ['align' => 'center', 'color' => '#cbd5e1', 'font_size' => 20, 'margin_bottom' => 32]),
                K::btn($t('Télécharger GeniusPress', 'Download GeniusPress'), $u('download'), ['align' => 'center', 'size' => 'lg', 'bg_color' => $ambre, 'text_color' => $nuit, 'margin_bottom' => 12]),
                K::btn($t('Lire la documentation', 'Read the documentation'), $u('docs'), ['align' => 'center', 'variant' => 'outline', 'bg_color' => '#ffffff', 'text_color' => '#ffffff', 'margin_bottom' => 20]),
                K::p('<p>'.$t('Version 0.1 · préversion · licence AGPL-3.0', 'Version 0.1 · preview · AGPL-3.0 license').'</p>', ['align' => 'center', 'color' => '#94a3b8', 'font_size' => 14]),
            ], ['background_color' => $nuit, 'text_color' => '#ffffff', 'padding_top' => 120, 'padding_bottom' => 110, 'padding_top_mobile' => 72, 'padding_bottom_mobile' => 64, 'max_width' => 900]),

            K::section([
                K::h($t('Tout ce qu’il faut pour un vrai site', 'Everything a real website needs'), 'h2', ['align' => 'center', 'margin_bottom' => 12]),
                K::p('<p>'.$t(
                    'Les outils d’un grand page builder, sans abonnement ni dépendance à une plateforme.',
                    'The tools of a major page builder, with no subscription and no platform lock-in.'
                ).'</p>', ['align' => 'center', 'color' => '#475569', 'font_size' => 18]),
            ], ['padding_top' => 88, 'padding_bottom' => 32, 'max_width' => 760]),
            K::section([
                $card($t('Builder visuel', 'Visual builder'), $t(
                    'Glissez des sections, des colonnes et des blocs. Aperçu ordinateur, tablette et mobile, annuler et rétablir, historique des versions.',
                    'Drag sections, columns and blocks. Desktop, tablet and mobile preview, undo and redo, version history.'
                ), $gris),
                $card($t('Assistant IA', 'AI assistant'), $t(
                    'Décrivez la page voulue : l’assistant la construit. Il rédige, corrige, traduit et vérifie le référencement et l’accessibilité.',
                    'Describe the page you want and the assistant builds it. It writes, proofreads, translates and checks SEO and accessibility.'
                ), $gris),
                $card($t('Multilingue', 'Multilingual'), $t(
                    'Français, anglais, swahili, lingala… Une version de chaque page par langue, liées entre elles, et une administration traduite.',
                    'French, English, Swahili, Lingala… One version of each page per language, linked together, and a translated admin.'
                ), $gris),
            ], ['padding_top' => 0, 'padding_bottom' => 24, 'gap' => 24]),
            K::section([
                $card('Plugins', $t(
                    'Ajoutez des widgets et des fonctionnalités. Un plugin qui plante est désactivé tout seul, sans casser le site.',
                    'Add widgets and features. A plugin that crashes is switched off automatically, without breaking the site.'
                ), $gris),
                $card($t('Vos données vous appartiennent', 'Your data belongs to you'), $t(
                    'Exportez tout le site dans un seul fichier et importez-le ailleurs. Vous n’êtes jamais prisonnier.',
                    'Export the whole site to a single file and import it elsewhere. You are never locked in.'
                ), $gris),
                $card($t('S’installe partout', 'Runs anywhere'), $t(
                    'Hébergement mutualisé, VPS, Docker, ou ordinateur hors ligne avec SQLite. L’assistant d’installation fait le reste.',
                    'Shared hosting, VPS, Docker, or an offline computer with SQLite. The setup wizard does the rest.'
                ), $gris),
            ], ['padding_top' => 0, 'padding_bottom' => 88, 'gap' => 24]),

            K::section([
                K::col([
                    K::h($t('Pensé pour les organisations africaines', 'Designed for African organizations'), 'h2', ['margin_bottom' => 16]),
                    K::p($t(
                        '<p>GeniusPress est né en RDC pour répondre à une réalité : les ONG, les entreprises et les écoles ont besoin d’un site professionnel, dans plusieurs langues, sans budget d’agence et parfois avec une connexion Internet instable.</p><p>L’assistant IA écrit à partir de la présentation de votre organisation, n’invente jamais de chiffres ni de contacts, et laisse des espaces à compléter.</p>',
                        '<p>GeniusPress was born in the DRC to meet a simple need: NGOs, businesses and schools need a professional website, in several languages, without an agency budget and sometimes with an unreliable Internet connection.</p><p>The AI assistant writes from your organization’s own description, never makes up figures or contact details, and leaves placeholders for you to fill in.</p>'
                    ), ['font_size' => 17, 'color' => '#334155']),
                ], ['width' => 55]),
                K::col([
                    K::links($t('Pour qui ?', 'Who is it for?'), [
                        $t('ONG et associations', 'NGOs and associations') => $u('features', 'ngo'),
                        $t('PME et commerçants', 'Small businesses and shops') => $u('features'),
                        $t('Écoles et universités', 'Schools and universities') => $u('features'),
                        $t('Églises et communautés', 'Churches and communities') => $u('features'),
                        $t('Projets et événements', 'Projects and events') => $u('features'),
                    ], ['style' => 'boxed']),
                ], ['width' => 45]),
            ], ['background_color' => $lavande, 'padding_top' => 88, 'padding_bottom' => 88, 'gap' => 48, 'vertical_align' => 'center']),

            K::section([
                K::h($t('En ligne en trois étapes', 'Online in three steps'), 'h2', ['align' => 'center']),
            ], ['padding_top' => 88, 'padding_bottom' => 24]),
            K::section([
                $card($t('1. Installez', '1. Install'), $t(
                    'Envoyez les fichiers chez votre hébergeur ou lancez Docker. L’assistant vérifie le serveur et prépare la base de données.',
                    'Upload the files to your host or start Docker. The wizard checks the server and prepares the database.'
                )),
                $card($t('2. Construisez', '2. Build'), $t(
                    'Partez d’une page d’exemple, d’une page vide ou d’une description donnée à l’assistant IA. Ajustez tout dans le builder.',
                    'Start from a sample page, a blank page or a description given to the AI assistant. Fine-tune everything in the builder.'
                )),
                $card($t('3. Publiez', '3. Publish'), $t(
                    'Publiez quand vous êtes prêt : les modifications restent en brouillon tant que vous ne les mettez pas en ligne.',
                    'Publish when you are ready: changes stay in draft until you put them online.'
                )),
            ], ['padding_top' => 0, 'padding_bottom' => 88, 'gap' => 24]),

            $ctaBand,
        ], ['is_home' => true]);

    // ─── Fonctionnalités ─────────────────────────────────────────────────
    $pages[] = $page('features', $t('Fonctionnalités', 'Features'),
        $t('Fonctionnalités · GeniusPress', 'Features · GeniusPress'),
        $t(
            'Page builder visuel, assistant IA, site multilingue, plugins, export complet, installation partout : tout ce que fait GeniusPress.',
            'Visual page builder, AI assistant, multilingual site, plugins, full export, runs anywhere: everything GeniusPress does.'
        ), [
            $hero($t('Fonctionnalités', 'Features'), $t(
                'Ce que GeniusPress sait déjà faire dans sa version 0.1, et ce qui arrive ensuite.',
                'What GeniusPress already does in version 0.1, and what comes next.'
            )),
            K::section([
                K::col([K::h($t('Builder visuel', 'Visual builder'), 'h2', ['margin_bottom' => 12]), K::p($t(
                    '<ul><li>Glisser-déposer de sections de 1 à 4 colonnes et de widgets : titre, texte, image, bouton, espace, séparateur, vidéo, liste de liens.</li><li>Réglages de style par élément, avec des valeurs spécifiques au mobile.</li><li>Aperçu ordinateur, tablette et mobile au rendu réel.</li><li>Annuler, rétablir, raccourcis clavier, historique des 30 dernières versions.</li><li>Brouillon séparé de la version en ligne : rien ne change pour les visiteurs avant la publication.</li></ul>',
                    '<ul><li>Drag and drop sections of 1 to 4 columns and widgets: heading, text, image, button, spacer, divider, video, link list.</li><li>Style settings for each element, with mobile-specific values.</li><li>Desktop, tablet and mobile preview with the real rendering.</li><li>Undo, redo, keyboard shortcuts, history of the last 30 versions.</li><li>Drafts kept apart from the live version: nothing changes for visitors until you publish.</li></ul>'
                ))], ['width' => 50]),
                K::col([K::h($t('Assistant IA', 'AI assistant'), 'h2', ['margin_bottom' => 12]), K::p($t(
                    '<ul><li>Création d’une page complète à partir d’une description.</li><li>Rédaction et réécriture : améliorer, raccourcir, développer, corriger, changer de ton.</li><li>Traduction d’une page dans une autre langue, en gardant la mise en page.</li><li>Audit SEO et accessibilité, avec des corrections en langage simple et les textes alternatifs des images.</li><li>Basé sur Claude ; la clé API reste chiffrée sur votre serveur.</li></ul>',
                    '<ul><li>A complete page created from a description.</li><li>Writing and rewriting: improve, shorten, expand, proofread, change the tone.</li><li>Translation of a page into another language, keeping the layout.</li><li>SEO and accessibility audit, with fixes in plain language and alt text for images.</li><li>Powered by Claude; the API key stays encrypted on your server.</li></ul>'
                ))], ['width' => 50]),
            ], ['padding_top' => 72, 'padding_bottom' => 40, 'gap' => 48]),
            K::section([
                K::col([K::h($t('Gestion du site', 'Site management'), 'h2', ['margin_bottom' => 12]), K::p($t(
                    '<ul><li>Pages, médiathèque, référencement par page.</li><li>En-tête, menus et pied de page par langue.</li><li>Administration en français et en anglais, langue choisie par chaque utilisateur.</li><li>Rôles administrateur et éditeur.</li></ul>',
                    '<ul><li>Pages, media library, SEO settings for each page.</li><li>Header, menus and footer for each language.</li><li>Admin in French and English, chosen by each user.</li><li>Administrator and editor roles.</li></ul>'
                ))], ['width' => 50]),
                K::col([K::h($t('Ouvert et extensible', 'Open and extensible'), 'h2', ['margin_bottom' => 12]), K::p($t(
                    '<ul><li>Système de plugins : widgets, routes, hooks, traductions.</li><li>Un plugin défaillant est désactivé automatiquement.</li><li>Export et import complets du site.</li><li>Installation par navigateur, ligne de commande, paquet zip ou Docker.</li></ul>',
                    '<ul><li>Plugin system: widgets, routes, hooks, translations.</li><li>A faulty plugin is switched off automatically.</li><li>Full site export and import.</li><li>Install from the browser, the command line, a zip package or Docker.</li></ul>'
                ))], ['width' => 50]),
            ], ['padding_top' => 24, 'padding_bottom' => 72, 'gap' => 48]),
            K::section([
                K::h($t('Pour les ONG', 'For NGOs'), 'h2', ['margin_bottom' => 12]),
                K::p('<p>'.$t(
                    'Pages de projets, chiffres d’impact, appels aux dons, contenus en plusieurs langues pour vos partenaires et vos bénéficiaires. Le plugin officiel de dons avec Mobile Money et carte bancaire est prévu dans la prochaine phase.',
                    'Project pages, impact figures, calls for donations, content in several languages for your partners and the people you serve. The official donations plugin, with Mobile Money and card payments, is planned for the next phase.'
                ).'</p>', ['font_size' => 17]),
            ], ['background_color' => $lavande, 'padding_top' => 64, 'padding_bottom' => 64, 'max_width' => 860, 'anchor' => 'ngo']),
            $ctaBand,
        ]);

    // ─── Télécharger ─────────────────────────────────────────────────────
    $pages[] = $page('download', $t('Télécharger', 'Download'),
        $t('Télécharger GeniusPress', 'Download GeniusPress'),
        $t(
            'Installez GeniusPress chez un hébergeur mutualisé, sur un VPS avec Docker, en ligne de commande ou en local.',
            'Install GeniusPress on shared hosting, on a VPS with Docker, from the command line or locally.'
        ), [
            $hero($t('Télécharger GeniusPress', 'Download GeniusPress'), $t(
                'Quatre façons d’installer, de l’hébergement mutualisé à l’ordinateur hors ligne.',
                'Four ways to install, from shared hosting to an offline computer.'
            ), [
                K::p('<p>'.$t(
                    '<strong>Préversion 0.1.</strong> GeniusPress est en développement actif : utilisez-le pour essayer et contribuer, pas encore pour un site critique.',
                    '<strong>Preview 0.1.</strong> GeniusPress is under active development: use it to try it out and contribute, not yet for a critical website.'
                ).'</p>', ['align' => 'center', 'color' => '#92400e', 'font_size' => 15]),
            ]),
            K::section([
                $card($t('Hébergement mutualisé', 'Shared hosting'), $t(
                    'Le paquet zip contient déjà tout le nécessaire, sans Composer ni Node.js. Il sera publié avec la version 1.0 ; en attendant, fabriquez-le avec <code>php artisan geniuspress:package</code>.',
                    'The zip package already contains everything, with no Composer or Node.js needed. It will be published with version 1.0; in the meantime, build it with <code>php artisan geniuspress:package</code>.'
                ), $gris),
                $card('Docker', $t(
                    'GeniusPress et MariaDB en une commande : <code>docker compose up -d</code>, puis ouvrez <code>http://localhost:8080</code>.',
                    'GeniusPress and MariaDB in one command: <code>docker compose up -d</code>, then open <code>http://localhost:8080</code>.'
                ), $gris),
            ], ['padding_top' => 64, 'padding_bottom' => 24, 'gap' => 24]),
            K::section([
                $card($t('Ligne de commande', 'Command line'), $t(
                    'Sur un VPS : <code>php artisan geniuspress:install</code>, avec des questions ou entièrement par options.',
                    'On a VPS: <code>php artisan geniuspress:install</code>, interactively or entirely through options.'
                ), $gris),
                $card($t('Depuis le code source', 'From the source code'), $t(
                    'Pour développer : clonez le dépôt, puis <code>composer install</code>, <code>npm install &amp;&amp; npm run build</code> et <code>php artisan serve</code>.',
                    'For development: clone the repository, then run <code>composer install</code>, <code>npm install &amp;&amp; npm run build</code> and <code>php artisan serve</code>.'
                ), $gris),
            ], ['padding_top' => 0, 'padding_bottom' => 48, 'gap' => 24]),
            K::section([
                K::h($t('Ce qu’il faut sur le serveur', 'Server requirements'), 'h2', ['margin_bottom' => 12]),
                K::p($t(
                    '<table><thead><tr><th>Élément</th><th>Minimum</th></tr></thead><tbody><tr><td>PHP</td><td>8.3 ou plus récent</td></tr><tr><td>Base de données</td><td>MySQL 8, MariaDB 10.6+ ou SQLite</td></tr><tr><td>Extensions conseillées</td><td><code>intl</code>, <code>curl</code>, <code>gd</code>, <code>zip</code></td></tr></tbody></table><p>Le guide complet est dans la <a href="'.$u('doc-installation').'">documentation d’installation</a>.</p>',
                    '<table><thead><tr><th>Item</th><th>Minimum</th></tr></thead><tbody><tr><td>PHP</td><td>8.3 or newer</td></tr><tr><td>Database</td><td>MySQL 8, MariaDB 10.6+ or SQLite</td></tr><tr><td>Recommended extensions</td><td><code>intl</code>, <code>curl</code>, <code>gd</code>, <code>zip</code></td></tr></tbody></table><p>The full guide is in the <a href="'.$u('doc-installation').'">installation documentation</a>.</p>'
                )),
                K::space(16),
                K::btn($t('Code source sur GitHub', 'Source code on GitHub'), $repo, ['size' => 'lg', 'new_tab' => true]),
            ], ['padding_top' => 24, 'padding_bottom' => 88, 'max_width' => 860]),
        ]);

    // ─── Documentation ───────────────────────────────────────────────────
    $pages[] = $docPage('docs', $t(
        '<p>Bienvenue dans la documentation de GeniusPress. Elle est écrite en français, puis traduite, et vit dans le dépôt à côté du code : chaque amélioration est relue comme du code.</p>'
        .'<h2>Utiliser GeniusPress</h2><ul><li><a href="'.$u('doc-installation').'">Installer GeniusPress</a> : hébergement mutualisé, VPS, Docker, local hors ligne.</li><li><a href="'.$u('doc-backup').'">Sauvegarde, export et import</a> : vos données vous appartiennent.</li></ul>'
        .'<h2>Développer</h2><ul><li><a href="'.$u('doc-plugins').'">Créer un plugin</a> : widgets, hooks, routes, traductions.</li><li><a href="'.$repo.'">Code source et tickets sur GitHub</a>.</li></ul>'
        .'<h2>Le projet</h2><ul><li><a href="'.$u('doc-brand').'">Marque et logo</a>.</li><li><a href="'.$u('roadmap').'">Feuille de route</a>.</li><li><a href="'.$u('contribute').'">Contribuer</a>.</li></ul>',
        '<p>Welcome to the GeniusPress documentation. It is written in French, then translated, and lives in the repository next to the code: every improvement is reviewed like code.</p>'
        .'<h2>Using GeniusPress</h2><ul><li><a href="'.$u('doc-installation').'">Installing GeniusPress</a>: shared hosting, VPS, Docker, offline on your own computer.</li><li><a href="'.$u('doc-backup').'">Backup, export and import</a>: your data belongs to you.</li></ul>'
        .'<h2>Developing</h2><ul><li><a href="'.$u('doc-plugins').'">Creating a plugin</a>: widgets, hooks, routes, translations.</li><li><a href="'.$repo.'">Source code and issues on GitHub</a>.</li></ul>'
        .'<h2>The project</h2><ul><li><a href="'.$u('doc-brand').'">Brand and logo</a>.</li><li><a href="'.$u('roadmap').'">Roadmap</a>.</li><li><a href="'.$u('contribute').'">Contribute</a>.</li></ul>'
    ), $t('Documentation de GeniusPress : installation, sauvegarde, plugins, marque.', 'GeniusPress documentation: installation, backup, plugins, brand.'));

    array_push($pages, ...$docPages);

    // ─── Feuille de route ────────────────────────────────────────────────
    $pages[] = $page('roadmap', $t('Feuille de route', 'Roadmap'),
        $t('Feuille de route · GeniusPress', 'Roadmap · GeniusPress'),
        $t(
            'Ce qui est fait, ce qui est en cours et ce qui arrive dans GeniusPress, jusqu’à la version 1.0.',
            'What is done, what is in progress and what is coming in GeniusPress, up to version 1.0.'
        ), [
            $hero($t('Feuille de route', 'Roadmap'), $t(
                'Le développement se fait en phases courtes. Chaque phase se termine quand son critère de fin est atteint, pas à une date fixe.',
                'Development happens in short phases. Each phase ends when its goal is reached, not on a fixed date.'
            )),
            K::section([
                K::p($t(
                    '<h2>MVP · terminé</h2><p>Builder visuel, assistant IA, administration, médiathèque, site multilingue.</p>'
                    .'<h2>Phase 1 · fondations open source · presque terminée</h2><ul><li>Système de plugins et registre de widgets.</li><li>Interface traduite (français, anglais).</li><li>Installateur depuis zéro, paquet zip, Docker.</li><li>Export et import de site.</li><li>Tests automatiques sur GitHub (en attente de validation).</li></ul>'
                    .'<h2>Phase 2 · un vrai site complet</h2><ul><li>En-tête, menus et pied de page (déjà disponibles).</li><li>Plugins officiels : formulaire de contact, dons et paiements (Mobile Money, carte).</li><li>Nouveaux widgets et modèles de pages.</li><li>Interface en swahili et en lingala.</li></ul>'
                    .'<h2>Phase 3 · hébergement et plateforme</h2><ul><li>Déploiement sur VPS avec sauvegardes automatiques.</li><li>Plateforme multi-sites avec domaines personnalisés.</li><li>Mises à jour en un clic, export en site statique.</li></ul>'
                    .'<h2>Phase 4 · version 1.0</h2><p>Documentation complète en français et en anglais, lancement public.</p>',
                    '<h2>MVP · done</h2><p>Visual builder, AI assistant, admin, media library, multilingual site.</p>'
                    .'<h2>Phase 1 · open source foundations · almost done</h2><ul><li>Plugin system and widget registry.</li><li>Translated interface (French, English).</li><li>Installer from scratch, zip package, Docker.</li><li>Site export and import.</li><li>Automated tests on GitHub (awaiting validation).</li></ul>'
                    .'<h2>Phase 2 · a complete website</h2><ul><li>Header, menus and footer (already available).</li><li>Official plugins: contact form, donations and payments (Mobile Money, card).</li><li>New widgets and page templates.</li><li>Interface in Swahili and Lingala.</li></ul>'
                    .'<h2>Phase 3 · hosting and platform</h2><ul><li>VPS deployment with automatic backups.</li><li>Multi-site platform with custom domains.</li><li>One-click updates, static site export.</li></ul>'
                    .'<h2>Phase 4 · version 1.0</h2><p>Full documentation in French and English, public launch.</p>'
                ), ['font_size' => 17]),
            ], ['padding_top' => 64, 'padding_bottom' => 88, 'max_width' => 860]),
        ]);

    // ─── Contribuer ──────────────────────────────────────────────────────
    $pages[] = $page('contribute', $t('Contribuer', 'Contribute'),
        $t('Contribuer à GeniusPress', 'Contribute to GeniusPress'),
        $t(
            'Code, traductions, plugins, documentation, signalement de failles : toutes les façons de contribuer à GeniusPress.',
            'Code, translations, plugins, documentation, security reports: all the ways to contribute to GeniusPress.'
        ), [
            $hero($t('Contribuer', 'Contribute'), $t(
                'GeniusPress est un projet libre. Toute aide compte, que vous sachiez coder ou non.',
                'GeniusPress is a free software project. Every bit of help counts, whether you code or not.'
            )),
            K::section([
                $card($t('Traduire', 'Translate'), $t(
                    'L’interface est en français et en anglais. Aidez-nous à la traduire en swahili, en lingala, en kinyarwanda… sans écrire de code : un seul fichier de textes par langue.',
                    'The interface is in French and English. Help us translate it into Swahili, Lingala, Kinyarwanda… without writing code: one text file per language.'
                ), $gris),
                $card($t('Créer un plugin', 'Build a plugin'), $t(
                    'Un formulaire, une galerie, une intégration Mobile Money : <a href="'.$u('doc-plugins').'">le guide des plugins</a> explique tout, avec une commande qui crée un plugin d’exemple.',
                    'A form, a gallery, a Mobile Money integration: <a href="'.$u('doc-plugins').'">the plugin guide</a> explains everything, with a command that creates a sample plugin.'
                ), $gris),
                $card($t('Améliorer le code', 'Improve the code'), $t(
                    'Corrigez un bogue ou proposez une fonctionnalité sur <a href="'.$repo.'">GitHub</a>. Chaque proposition est vérifiée par les tests automatiques.',
                    'Fix a bug or suggest a feature on <a href="'.$repo.'">GitHub</a>. Every contribution is checked by the automated tests.'
                ), $gris),
            ], ['padding_top' => 64, 'padding_bottom' => 24, 'gap' => 24]),
            K::section([
                $card($t('Documenter', 'Document'), $t(
                    'La documentation vit dans le dossier <code>docs/</code> du dépôt. Une explication plus claire aide des centaines d’utilisateurs.',
                    'The documentation lives in the <code>docs/</code> folder of the repository. A clearer explanation helps hundreds of users.'
                ), $gris),
                $card($t('Signaler une faille', 'Report a vulnerability'), $t(
                    'Vous avez trouvé une faille de sécurité ? Ne la publiez pas : écrivez-nous en privé pour qu’elle soit corrigée avant d’être rendue publique.',
                    'Found a security vulnerability? Please don’t publish it: contact us privately so it can be fixed before it is made public.'
                ), $gris),
                $card($t('En parler', 'Spread the word'), $t(
                    'Présentez GeniusPress à une ONG, une école ou une entreprise qui a besoin d’un site. C’est la meilleure façon de faire grandir le projet.',
                    'Introduce GeniusPress to an NGO, a school or a business that needs a website. It is the best way to help the project grow.'
                ), $gris),
            ], ['padding_top' => 0, 'padding_bottom' => 88, 'gap' => 24]),
        ]);

    // ─── Menus de cette langue ───────────────────────────────────────────
    $navigation = [
        'menu' => [
            ['label' => $t('Fonctionnalités', 'Features'), 'url' => $u('features')],
            ['label' => 'Documentation', 'url' => $u('docs')],
            ['label' => $t('Feuille de route', 'Roadmap'), 'url' => $u('roadmap')],
            ['label' => $t('Contribuer', 'Contribute'), 'url' => $u('contribute')],
        ],
        'cta_label' => $t('Télécharger', 'Download'),
        'cta_url' => $u('download'),
        'footer_text' => $t(
            'GeniusPress est un CMS libre (AGPL-3.0) avec un page builder visuel et un assistant IA, pensé pour les entreprises et les ONG africaines.',
            'GeniusPress is a free CMS (AGPL-3.0) with a visual page builder and an AI assistant, designed for African businesses and NGOs.'
        ),
        'footer_links' => [
            ['label' => $t('Télécharger', 'Download'), 'url' => $u('download')],
            ['label' => 'Documentation', 'url' => $u('docs')],
            ['label' => $t('Créer un plugin', 'Creating a plugin'), 'url' => $u('doc-plugins')],
            ['label' => $t('Marque et logo', 'Brand and logo'), 'url' => $u('doc-brand')],
            ['label' => $t('Code source', 'Source code'), 'url' => $repo],
            ['label' => $t('Licence', 'License'), 'url' => $repo.'/blob/main/LICENSE'],
        ],
    ];

    return [$pages, $navigation];
};

[$pagesFr, $navFr] = $build('fr');
[$pagesEn, $navEn] = $build('en');

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
        'locales' => ['fr', 'en'],
        'logo_url' => '/brand/geniuspress-mark.svg',
        'favicon_url' => '/favicon.svg',
        'navigation' => [
            'header' => ['enabled' => true, 'sticky' => true, 'style' => 'light', 'cta_url' => null],
            'footer' => ['enabled' => true, 'credit' => false],
            'locales' => ['fr' => $navFr, 'en' => $navEn],
        ],
    ],
    'pages' => [...$pagesFr, ...$pagesEn],
];
