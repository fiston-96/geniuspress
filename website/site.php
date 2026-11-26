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

use App\Templates\Theme;
use App\Website\PageKit as K;

$repo = 'https://github.com/fiston-96/geniuspress';
$base = dirname(__DIR__);

// Palette éditoriale de la vitrine : crème, terracotta, or, nuit indigo (famille du logo, docs/marque.md).
$primaire = '#aa4d26';
$or = '#c9973a';
$nuit = '#1e1b4b';
$creme = '#fbf7f0';
$sable = '#f3ece1';

// Adresse de chaque page dans chaque langue, par clé de groupe.
$slugs = [
    'home' => ['fr' => 'accueil', 'en' => 'home'],
    'features' => ['fr' => 'fonctionnalites', 'en' => 'features'],
    'templates' => ['fr' => 'modeles', 'en' => 'templates'],
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

// Thème des pages vitrines (format 2), aux couleurs de la marque : mêmes blocs que les modèles de sites.
$theme = new Theme(
    colors: ['primary' => $primaire, 'secondary' => $nuit, 'accent' => $or, 'light' => $creme, 'text' => '#1c1917', 'muted' => '#57534e'],
    font: 'DM Sans', headingFont: 'DM Serif Display', hero: 'split', cards: 'border', buttons: 'rounded', radius: 12, space: 112,
);
// Accueil, fonctionnalités et modèles : website/showcase.php.
$showcase = require __DIR__.'/showcase.php';

$build = function (string $lang) use ($repo, $base, $slugs, $docFiles, $primaire, $or, $nuit, $creme, $sable, $theme, $showcase) {
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
        K::p('<p>'.$text.'</p>', ['color' => '#57534e']),
    ], ['padding' => 28, 'background_color' => $bg, 'border_radius' => 16]);

    $hero = fn (string $title, string $intro, array $extra = []) => K::section([
        K::h($title, 'h1', ['align' => 'center', 'font_size' => 52, 'font_size_mobile' => 34, 'margin_bottom' => 18]),
        K::p('<p>'.$intro.'</p>', ['align' => 'center', 'font_size' => 19, 'color' => '#57534e', 'margin_bottom' => $extra ? 28 : 0]),
        ...$extra,
    ], ['background_color' => $creme, 'padding_top' => 88, 'padding_bottom' => 72, 'padding_top_mobile' => 56, 'padding_bottom_mobile' => 48, 'max_width' => 860]);

    $pages = [];

    // ─── Accueil, fonctionnalités, modèles (format 2) ────────────────────
    foreach ($showcase($lang, $u, $repo, $theme) as $key => $p) {
        $pages[] = $page($key, $p['title'], $p['meta_title'], $p['meta_description'], [],
            ['content' => ['version' => 2, 'elements' => $p['elements']]] + ($key === 'home' ? ['is_home' => true] : []));
    }

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
                ), $sable),
                $card('Docker', $t(
                    'GeniusPress et MariaDB en une commande : <code>docker compose up -d</code>, puis ouvrez <code>http://localhost:8080</code>.',
                    'GeniusPress and MariaDB in one command: <code>docker compose up -d</code>, then open <code>http://localhost:8080</code>.'
                ), $sable),
            ], ['padding_top' => 64, 'padding_bottom' => 24, 'gap' => 24]),
            K::section([
                $card($t('Ligne de commande', 'Command line'), $t(
                    'Sur un VPS : <code>php artisan geniuspress:install</code>, avec des questions ou entièrement par options.',
                    'On a VPS: <code>php artisan geniuspress:install</code>, interactively or entirely through options.'
                ), $sable),
                $card($t('Depuis le code source', 'From the source code'), $t(
                    'Pour développer : clonez le dépôt, puis <code>composer install</code>, <code>npm install &amp;&amp; npm run build</code> et <code>php artisan serve</code>.',
                    'For development: clone the repository, then run <code>composer install</code>, <code>npm install &amp;&amp; npm run build</code> and <code>php artisan serve</code>.'
                ), $sable),
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
                'Le développement se fait par phases. Chaque phase se termine quand son critère de fin est atteint ; l’hébergement et la version 1.0 ont une date visée.',
                'Development happens in phases. Each phase ends when its goal is reached; hosting and version 1.0 have a target date.'
            )),
            K::section([
                K::p($t(
                    '<h2>MVP · terminé</h2><p>Builder visuel, assistant IA, administration, médiathèque, site multilingue.</p>'
                    .'<h2>Phase 1 · fondations open source · presque terminée</h2><ul><li>Système de plugins et registre de widgets.</li><li>Interface traduite (français, anglais).</li><li>Installateur depuis zéro, paquet zip, Docker.</li><li>Export et import de site.</li><li>Tests automatiques sur GitHub (en attente de la facturation GitHub).</li></ul>'
                    .'<h2>Phase 2 · builder pro et modèles · terminée</h2><ul><li>Conteneurs imbriqués, réglages par appareil et au survol.</li><li>'.count(\App\Providers\AppServiceProvider::CORE_WIDGETS).' widgets, dont le formulaire de contact, les graphiques, les carrousels et les titres animés.</li><li>'.count(require __DIR__.'/../templates/catalog.php').' modèles dans 11 secteurs, dont les médias, avec démonstrations.</li><li>Page Modèles, assistant IA et nouvelle vitrine.</li></ul>'
                    .'<h2>Phase 3 · articles et constructeur de thème · terminée</h2><ul><li>Articles, catégories, étiquettes, flux RSS et recherche.</li><li>Gabarits d’en-tête, de pied de page, d’article et d’archive, avec conditions d’affichage.</li><li>Widgets dynamiques et en-tête collant.</li><li>Méga-menu : à venir.</li></ul>'
                    .'<h2>Phase 4 · boutique (plugin Commerce officiel)</h2><ul><li>Produits à variantes, panier, livraison, plusieurs devises.</li><li>Paiements par Mobile Money, carte, à la livraison et par WhatsApp.</li><li>Commandes, gabarits et widgets de boutique.</li><li>Liste de souhaits, comparaison de produits et dons.</li></ul>'
                    .'<h2>Phase 5 · builder enrichi · en grande partie faite</h2><ul><li>Animations d’apparition, effets au survol et au défilement, parallaxe, transitions entre les pages (fait).</li><li>Fenêtres surgissantes, CSS personnalisé et blocs réutilisables : à venir.</li></ul>'
                    .'<h2>Phase 6 · modèles nouvelle génération · en cours</h2><ul><li>Blog et gabarits du thème dans chaque modèle (fait).</li><li>Plus de 120 modèles, dont des boutiques : en cours.</li></ul>'
                    .'<h2>Phase 7 · hébergement et plateforme · en cours</h2><ul><li>Site et démonstrations en ligne sur un serveur, en HTTPS (fait).</li><li>Essai hébergé, offres, noms de domaine et crédits IA : en cours.</li><li>Sauvegardes automatiques et mises à jour en un clic.</li></ul>'
                    .'<h2>Phase 8 · lancement de la version 1.0 · juin 2027</h2><p>Documentation complète en français et en anglais, lancement public.</p>',
                    '<h2>MVP · done</h2><p>Visual builder, AI assistant, admin, media library, multilingual site.</p>'
                    .'<h2>Phase 1 · open source foundations · almost done</h2><ul><li>Plugin system and widget registry.</li><li>Translated interface (French, English).</li><li>Installer from scratch, zip package, Docker.</li><li>Site export and import.</li><li>Automated tests on GitHub (waiting on GitHub billing).</li></ul>'
                    .'<h2>Phase 2 · pro builder and templates · done</h2><ul><li>Nested containers, per-device and hover settings.</li><li>'.count(\App\Providers\AppServiceProvider::CORE_WIDGETS).' widgets, including the contact form, charts, carousels and animated headings.</li><li>'.count(require __DIR__.'/../templates/catalog.php').' templates in 11 sectors, including media, with demos.</li><li>Templates page, AI assistant and new showcase.</li></ul>'
                    .'<h2>Phase 3 · posts and theme builder · done</h2><ul><li>Posts, categories, tags, RSS feed and search.</li><li>Header, footer, single post and archive templates, with display conditions.</li><li>Dynamic widgets and sticky header.</li><li>Mega menu: coming.</li></ul>'
                    .'<h2>Phase 4 · online store (official Commerce plugin)</h2><ul><li>Products with variants, cart, shipping, multiple currencies.</li><li>Payments by Mobile Money, card, cash on delivery and WhatsApp.</li><li>Orders, store templates and widgets.</li><li>Wishlist, product comparison and donations.</li></ul>'
                    .'<h2>Phase 5 · richer builder · largely done</h2><ul><li>Entrance animations, hover and scroll effects, parallax, page transitions (done).</li><li>Popups, custom CSS and reusable blocks: coming.</li></ul>'
                    .'<h2>Phase 6 · next-generation templates · in progress</h2><ul><li>Blog and theme templates in every template (done).</li><li>More than 120 templates, including stores: in progress.</li></ul>'
                    .'<h2>Phase 7 · hosting and platform · in progress</h2><ul><li>Website and demos online on a server, over HTTPS (done).</li><li>Hosted trial, plans, domain names and AI credits: in progress.</li><li>Automatic backups and one-click updates.</li></ul>'
                    .'<h2>Phase 8 · version 1.0 launch · June 2027</h2><p>Full documentation in French and English, public launch.</p>'
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
                ), $sable),
                $card($t('Créer un plugin', 'Build a plugin'), $t(
                    'Un formulaire, une galerie, une intégration Mobile Money : <a href="'.$u('doc-plugins').'">le guide des plugins</a> explique tout, avec une commande qui crée un plugin d’exemple.',
                    'A form, a gallery, a Mobile Money integration: <a href="'.$u('doc-plugins').'">the plugin guide</a> explains everything, with a command that creates a sample plugin.'
                ), $sable),
                $card($t('Améliorer le code', 'Improve the code'), $t(
                    'Corrigez un bogue ou proposez une fonctionnalité sur <a href="'.$repo.'">GitHub</a>. Chaque proposition est vérifiée par les tests automatiques.',
                    'Fix a bug or suggest a feature on <a href="'.$repo.'">GitHub</a>. Every contribution is checked by the automated tests.'
                ), $sable),
            ], ['padding_top' => 64, 'padding_bottom' => 24, 'gap' => 24]),
            K::section([
                $card($t('Documenter', 'Document'), $t(
                    'La documentation vit dans le dossier <code>docs/</code> du dépôt. Une explication plus claire aide des centaines d’utilisateurs.',
                    'The documentation lives in the <code>docs/</code> folder of the repository. A clearer explanation helps hundreds of users.'
                ), $sable),
                $card($t('Signaler une faille', 'Report a vulnerability'), $t(
                    'Vous avez trouvé une faille de sécurité ? Ne la publiez pas : écrivez-nous en privé pour qu’elle soit corrigée avant d’être rendue publique.',
                    'Found a security vulnerability? Please don’t publish it: contact us privately so it can be fixed before it is made public.'
                ), $sable),
                $card($t('En parler', 'Spread the word'), $t(
                    'Présentez GeniusPress à une ONG, une école ou une entreprise qui a besoin d’un site. C’est la meilleure façon de faire grandir le projet.',
                    'Introduce GeniusPress to an NGO, a school or a business that needs a website. It is the best way to help the project grow.'
                ), $sable),
            ], ['padding_top' => 0, 'padding_bottom' => 88, 'gap' => 24]),
        ]);

    // ─── Menus de cette langue ───────────────────────────────────────────
    $navigation = [
        'menu' => [
            ['label' => $t('Modèles', 'Templates'), 'url' => $u('templates')],
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
            ['label' => $t('Modèles', 'Templates'), 'url' => $u('templates')],
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
        'organization_description' => 'GeniusPress est un CMS open source (AGPL-3.0) avec un page builder visuel et un assistant IA, pour les entreprises et les ONG africaines. Public : responsables de communication, développeurs, contributeurs open source. Ton : clair, chaleureux, précis, sans jargon.',
        'primary_color' => $primaire,
        'secondary_color' => $nuit,
        'accent_color' => $or,
        'font_family' => $theme->font,
        'heading_font' => $theme->headingFont,
        'custom_colors' => $theme->settings()['custom_colors'],
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
    // Thème des pages vitrines (illustrations d'attente des photos absentes).
    'theme' => $theme,
];
