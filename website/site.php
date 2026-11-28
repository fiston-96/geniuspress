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
    'hosting' => ['fr' => 'hebergement', 'en' => 'hosting'],
    'privacy' => ['fr' => 'confidentialite', 'en' => 'privacy'],
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
                'Un seul fichier zip, prêt à l’emploi : décompressez-le, ouvrez votre navigateur, l’installation se fait à l’écran. Aucune ligne de commande.',
                'A single zip file, ready to use: unzip it, open your browser and install on screen. No command line.'
            ), [
                K::btn($t('Télécharger GeniusPress (zip)', 'Download GeniusPress (zip)'), '/telecharger/geniuspress.zip', ['size' => 'lg', 'align' => 'center']),
                K::space(12),
                K::p('<p>'.$t(
                    'Version 0.1 · environ 15 Mo · PHP 8.3 ou plus · <strong>préversion</strong> : idéale pour essayer et contribuer, pas encore pour un site critique.',
                    'Version 0.1 · about 15 MB · PHP 8.3 or newer · <strong>preview</strong>: ideal to try out and contribute, not yet for a critical website.'
                ).'</p>', ['align' => 'center', 'color' => '#78716c', 'font_size' => 15]),
            ]),
            K::section([
                $card($t('Sur votre ordinateur', 'On your computer'), $t(
                    'Avec WAMP, XAMPP, Laragon ou MAMP : décompressez le zip dans le dossier <code>www</code> (ou <code>htdocs</code>), puis ouvrez <code>http://localhost/geniuspress</code>. Choisissez « SQLite » : aucune base de données à créer, tout tient dans un fichier. Parfait pour travailler hors ligne.',
                    'With WAMP, XAMPP, Laragon or MAMP: unzip into the <code>www</code> (or <code>htdocs</code>) folder, then open <code>http://localhost/geniuspress</code>. Choose “SQLite”: no database to create, everything fits in one file. Perfect for working offline.'
                ), $sable),
                $card($t('Chez un hébergeur mutualisé', 'On shared hosting'), $t(
                    'Envoyez le dossier par FTP ou avec le gestionnaire de fichiers de votre hébergeur, puis ouvrez l’adresse de votre site. Le fichier <code>.htaccess</code> fourni fait le reste. Sauvegardes et tâches planifiées fonctionnent sans cron.',
                    'Upload the folder by FTP or with your host’s file manager, then open your site’s address. The included <code>.htaccess</code> file does the rest. Backups and scheduled tasks work without cron.'
                ), $sable),
            ], ['padding_top' => 64, 'padding_bottom' => 24, 'gap' => 24]),
            K::section([
                $card('Docker', $t(
                    'GeniusPress et MariaDB en une commande : <code>docker compose up -d</code>, puis ouvrez <code>http://localhost:8080</code>.',
                    'GeniusPress and MariaDB in one command: <code>docker compose up -d</code>, then open <code>http://localhost:8080</code>.'
                ), $sable),
                $card($t('Hébergé par GeniusPress', 'Hosted by GeniusPress'), $t(
                    'Rien à installer : créez votre site en ligne sur <a href="/compte/inscription">geniuspress.site</a>, avec son adresse .geniuspress.site, puis votre propre nom de domaine.',
                    'Nothing to install: create your site online at <a href="/compte/inscription">geniuspress.site</a>, with its .geniuspress.site address, then your own domain name.'
                ), $sable),
            ], ['padding_top' => 0, 'padding_bottom' => 24, 'gap' => 24]),
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
                    '<h2>Première version · terminée</h2><p>Éditeur visuel, assistant IA, administration, médiathèque, site multilingue.</p>'
                    .'<h2>Phase 1 · fondations open source · presque terminée</h2><ul><li>Système de plugins et registre de widgets.</li><li>Interface traduite (français, anglais).</li><li>Installateur depuis zéro, paquet zip, Docker.</li><li>Export et import de site.</li><li>Tests automatiques à chaque proposition sur GitHub : à venir.</li></ul>'
                    .'<h2>Phase 2 · éditeur avancé et modèles · terminée</h2><ul><li>Conteneurs imbriqués, réglages par appareil et au survol.</li><li>'.count(\App\Providers\AppServiceProvider::CORE_WIDGETS).' widgets, dont le formulaire de contact, les graphiques, les carrousels et les titres animés.</li><li>'.count(require __DIR__.'/../templates/catalog.php').' modèles dans '.count(array_unique(array_column(require __DIR__.'/../templates/catalog.php', 'sector'))).' secteurs, dont les médias, avec démonstrations.</li><li>Page Modèles, assistant IA et nouvelle vitrine.</li></ul>'
                    .'<h2>Phase 3 · articles et constructeur de thème · terminée</h2><ul><li>Articles, catégories, étiquettes, flux RSS et recherche.</li><li>Gabarits d’en-tête, de pied de page, d’article et d’archive, avec conditions d’affichage.</li><li>Widgets dynamiques et en-tête collant.</li><li>Méga-menu : à venir.</li></ul>'
                    .'<h2>Phase 4 · boutique (plugin Commerce officiel)</h2><ul><li>Produits à variantes, panier, livraison, plusieurs devises.</li><li>Paiements par carte, Mobile Money, à la livraison et par WhatsApp.</li><li>Commandes, gabarits et widgets de boutique.</li><li>Liste de souhaits, comparaison de produits et dons.</li></ul>'
                    .'<h2>Phase 5 · éditeur enrichi · en grande partie fait</h2><ul><li>Animations d’apparition, effets au survol et au défilement, parallaxe, transitions entre les pages (fait).</li><li>Fenêtres surgissantes, CSS personnalisé et blocs réutilisables : à venir.</li></ul>'
                    .'<h2>Phase 6 · modèles nouvelle génération · en cours</h2><ul><li>Blog et gabarits du thème dans chaque modèle (fait).</li><li>Plus de 120 modèles, dont des boutiques : en cours.</li></ul>'
                    .'<h2>Phase 7 · hébergement et plateforme · en cours</h2><ul><li>Site et démonstrations en ligne sur un serveur, en HTTPS (fait).</li><li>Essai hébergé, offres, noms de domaine et crédits IA : en cours.</li><li>Sauvegardes automatiques et mises à jour en un clic.</li></ul>'
                    .'<h2>Phase 8 · lancement de la version 1.0 · juin 2027</h2><p>Documentation complète en français et en anglais, lancement public.</p>',
                    '<h2>First version · done</h2><p>Visual editor, AI assistant, admin, media library, multilingual site.</p>'
                    .'<h2>Phase 1 · open source foundations · almost done</h2><ul><li>Plugin system and widget registry.</li><li>Translated interface (French, English).</li><li>Installer from scratch, zip package, Docker.</li><li>Site export and import.</li><li>Automated tests on every GitHub contribution: coming.</li></ul>'
                    .'<h2>Phase 2 · advanced editor and templates · done</h2><ul><li>Nested containers, per-device and hover settings.</li><li>'.count(\App\Providers\AppServiceProvider::CORE_WIDGETS).' widgets, including the contact form, charts, carousels and animated headings.</li><li>'.count(require __DIR__.'/../templates/catalog.php').' templates in '.count(array_unique(array_column(require __DIR__.'/../templates/catalog.php', 'sector'))).' sectors, including media, with demos.</li><li>Templates page, AI assistant and new showcase.</li></ul>'
                    .'<h2>Phase 3 · posts and theme builder · done</h2><ul><li>Posts, categories, tags, RSS feed and search.</li><li>Header, footer, single post and archive templates, with display conditions.</li><li>Dynamic widgets and sticky header.</li><li>Mega menu: coming.</li></ul>'
                    .'<h2>Phase 4 · online store (official Commerce plugin)</h2><ul><li>Products with variants, cart, shipping, multiple currencies.</li><li>Payments by card, Mobile Money, cash on delivery and WhatsApp.</li><li>Orders, store templates and widgets.</li><li>Wishlist, product comparison and donations.</li></ul>'
                    .'<h2>Phase 5 · richer editor · largely done</h2><ul><li>Entrance animations, hover and scroll effects, parallax, page transitions (done).</li><li>Popups, custom CSS and reusable blocks: coming.</li></ul>'
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
                    'Un formulaire, une galerie, un moyen de paiement : <a href="'.$u('doc-plugins').'">le guide des plugins</a> explique tout, avec une commande qui crée un plugin d’exemple.',
                    'A form, a gallery, a payment method: <a href="'.$u('doc-plugins').'">the plugin guide</a> explains everything, with a command that creates a sample plugin.'
                ), $sable),
                $card($t('Améliorer le code', 'Improve the code'), $t(
                    'Corrigez un bogue ou proposez une fonctionnalité sur <a href="'.$repo.'">GitHub</a>. Chaque proposition est relue et doit passer les tests automatiques du projet.',
                    'Fix a bug or suggest a feature on <a href="'.$repo.'">GitHub</a>. Every contribution is reviewed and must pass the project’s automated tests.'
                ), $sable),
            ], ['padding_top' => 64, 'padding_bottom' => 24, 'gap' => 24]),
            K::section([
                $card($t('Documenter', 'Document'), $t(
                    'La documentation vit dans le dossier <code>docs/</code> du dépôt. Une explication plus claire fait gagner du temps à chaque utilisateur.',
                    'The documentation lives in the <code>docs/</code> folder of the repository. A clearer explanation saves every user time.'
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

    // ─── Confidentialité (données des comptes et des commandes, prestataires) ───
    $pages[] = $page('privacy', $t('Confidentialité', 'Privacy'),
        $t('Politique de confidentialité · GeniusPress', 'Privacy policy · GeniusPress'),
        $t('Quelles données GeniusPress garde sur geniuspress.site, pourquoi, avec qui elles sont partagées et comment exercer vos droits.',
            'What data GeniusPress keeps on geniuspress.site, why, who it is shared with and how to exercise your rights.'), [
            $hero($t('Politique de confidentialité', 'Privacy policy'), $t(
                'Ce que nous gardons sur geniuspress.site, pourquoi, et avec qui c’est partagé.',
                'What we keep on geniuspress.site, why, and who it is shared with.'
            )),
            K::section([K::col([K::p($t(
                '<h2>Données gardées</h2><ul><li><strong>Votre compte</strong> : nom, adresse e-mail, mot de passe chiffré (ou identifiant Google), langue, téléphone, pays et organisation si vous les indiquez.</li><li><strong>Vos commandes</strong> : offres achetées, montants, état des paiements. Nous ne voyons jamais votre numéro de carte ni le code secret de votre compte de paiement.</li><li><strong>Titulaire des noms de domaine</strong> : adresse, ville, pays et téléphone, exigés par les registres pour enregistrer un domaine à votre nom.</li><li><strong>Vos sites hébergés</strong> : leur contenu vous appartient ; nous n’y accédons que pour les faire fonctionner ou à votre demande.</li><li><strong>Newsletter</strong> : votre adresse e-mail, seulement après votre confirmation ; un lien de désinscription figure dans chaque envoi.</li></ul>'
                .'<h2>Prestataires techniques</h2><p>Pour livrer certains services, des données strictement nécessaires sont transmises à nos prestataires :</p><ul><li><strong>LWS (Ligne Web Services, France)</strong> : enregistrement des noms de domaine, hébergement web, mail pro et stockage en ligne. Les coordonnées du titulaire d’un domaine lui sont transmises, comme l’exigent les registres.</li><li><strong>Le prestataire de paiement</strong> : montant, référence de commande et coordonnées de paiement, pour encaisser le paiement.</li><li><strong>Google</strong>, si vous choisissez « Continuer avec Google » : il nous confirme votre nom et votre adresse e-mail.</li><li><strong>Anthropic</strong>, quand vous utilisez l’assistant IA : le texte de vos demandes, pour produire la réponse.</li></ul><p>Nous ne vendons jamais vos données et ne les utilisons pas pour de la publicité.</p>'
                .'<h2>Durée de conservation</h2><p>Les données du compte sont gardées tant que le compte existe. Les commandes et paiements sont gardés le temps exigé par les obligations comptables.</p>'
                .'<h2>Vos droits</h2><p>Vous pouvez demander à consulter, corriger ou supprimer vos données, et récupérer le contenu de vos sites (export depuis leur administration). Un nom de domaine vous appartient : vous pouvez demander son code de transfert depuis votre espace client à tout moment.</p><p>Pour toute demande, écrivez-nous depuis l’adresse de votre compte.</p>',
                '<h2>Data we keep</h2><ul><li><strong>Your account</strong>: name, email address, encrypted password (or Google identifier), language, and phone, country and organization if you provide them.</li><li><strong>Your orders</strong>: offers purchased, amounts, payment status. We never see your card number or the PIN of your payment account.</li><li><strong>Domain name holder</strong>: address, city, country and phone, required by registries to register a domain in your name.</li><li><strong>Your hosted websites</strong>: their content belongs to you; we only access it to keep them running or at your request.</li><li><strong>Newsletter</strong>: your email address, only after you confirm it; every mailing includes an unsubscribe link.</li></ul>'
                .'<h2>Technical providers</h2><p>To deliver some services, strictly necessary data is shared with our providers:</p><ul><li><strong>LWS (Ligne Web Services, France)</strong>: domain name registration, web hosting, business email and online storage. The domain holder’s details are passed on to it, as registries require.</li><li><strong>The payment provider</strong>: amount, order reference and payment details, to collect the payment.</li><li><strong>Google</strong>, if you choose “Continue with Google”: it confirms your name and email address.</li><li><strong>Anthropic</strong>, when you use the AI assistant: the text of your requests, to produce the answer.</li></ul><p>We never sell your data and do not use it for advertising.</p>'
                .'<h2>Retention</h2><p>Account data is kept as long as the account exists. Orders and payments are kept for as long as accounting obligations require.</p>'
                .'<h2>Your rights</h2><p>You can ask to access, correct or delete your data, and retrieve the content of your websites (export from their admin). A domain name belongs to you: you can request its transfer code from your account at any time.</p><p>For any request, write to us from your account’s email address.</p>'
            ), ['font_size' => 16])])], ['padding_top' => 56, 'padding_bottom' => 88, 'max_width' => 820]),
        ]);

    // ─── Menus de cette langue ───────────────────────────────────────────
    $navigation = [
        'menu' => [
            ['label' => $t('Modèles', 'Templates'), 'url' => $u('templates')],
            ['label' => $t('Hébergement', 'Hosting'), 'url' => $u('hosting')],
            ['label' => $t('Fonctionnalités', 'Features'), 'url' => $u('features')],
            ['label' => 'Documentation', 'url' => $u('docs')],
            // Espace client de la plateforme (comptes, site hébergé, offres) ; « Contribuer » et la feuille de route sont dans le pied de page.
            ['label' => $t('Espace client', 'My account'), 'url' => '/compte'],
        ],
        'cta_label' => $t('Télécharger', 'Download'),
        'cta_url' => $u('download'),
        'footer_text' => $t(
            'GeniusPress est un logiciel libre (AGPL-3.0) pour créer des sites soignés, avec un éditeur visuel et un assistant IA, pour les entreprises, les ONG et les institutions du monde entier.',
            'GeniusPress is free software (AGPL-3.0) for building polished websites, with a visual editor and an AI assistant, for businesses, NGOs and institutions around the world.'
        ),
        'footer_links' => [
            ['label' => $t('Modèles', 'Templates'), 'url' => $u('templates')],
            ['label' => $t('Hébergement', 'Hosting'), 'url' => $u('hosting')],
            ['label' => $t('Télécharger', 'Download'), 'url' => $u('download')],
            ['label' => 'Documentation', 'url' => $u('docs')],
            ['label' => $t('Espace client', 'My account'), 'url' => '/compte'],
            ['label' => $t('Contribuer', 'Contribute'), 'url' => $u('contribute')],
            ['label' => $t('Feuille de route', 'Roadmap'), 'url' => $u('roadmap')],
            ['label' => $t('Créer un plugin', 'Creating a plugin'), 'url' => $u('doc-plugins')],
            ['label' => $t('Marque et logo', 'Brand and logo'), 'url' => $u('doc-brand')],
            ['label' => $t('Code source', 'Source code'), 'url' => $repo],
            ['label' => $t('Licence', 'License'), 'url' => $repo.'/blob/main/LICENSE'],
            ['label' => $t('Confidentialité', 'Privacy'), 'url' => $u('privacy')],
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
        'organization_description' => 'GeniusPress est un CMS open source (AGPL-3.0) avec un page builder visuel et un assistant IA, pour les entreprises, les ONG et les institutions du monde entier. Public : responsables de communication, développeurs, contributeurs open source. Ton : clair, chaleureux, précis, sans jargon.',
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
