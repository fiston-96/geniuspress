<?php

/*
 * Pages vitrines de geniuspress.site au format 2 (conteneurs et widgets) :
 * accueil, fonctionnalités et catalogue des modèles. Elles utilisent les blocs
 * des modèles de sites (App\Templates\Blocks) avec un thème aux couleurs de
 * GeniusPress, et les aperçus de modèles de App\Website\Showcase.
 *
 * Renvoie fn (string $lang, callable $u, string $repo) => [clé => [title, meta_title, meta_description, elements]].
 */

use App\Providers\AppServiceProvider;
use App\Templates\Blocks as B;
use App\Templates\Kit as K;
use App\Templates\Theme;
use App\Website\Showcase as S;

return function (string $lang, callable $u, string $repo, Theme $theme): array {
    $t = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
    $catalog = S::catalog();
    $sectors = S::sectors();
    $widgets = count(AppServiceProvider::CORE_WIDGETS);
    $templates = count($catalog);
    $pagesPerLang = $templates * 8;
    $pick = fn (array $ids) => array_map(fn ($id) => S::card($id, $catalog[$id], $lang), $ids);

    // Palette des widgets du builder (icône, nom).
    $palette = [
        ['heading', $t('Titre', 'Heading')], ['file-text', $t('Texte', 'Text')], ['image', 'Image'], ['play', $t('Vidéo', 'Video')],
        ['arrow-right', $t('Bouton', 'Button')], ['star', $t('Icône', 'Icon')], ['layout-grid', $t('Boîte d’icône', 'Icon box')], ['frame', $t('Boîte d’image', 'Image box')],
        ['list-checks', $t('Liste d’icônes', 'Icon list')], ['list', $t('Liste de liens', 'Link list')], ['quote', $t('Témoignage', 'Testimonial')], ['trending-up', $t('Compteur', 'Counter')],
        ['chart-column', $t('Progression', 'Progress bar')], ['chevron-down', $t('Accordéon', 'Accordion')], ['layers', $t('Onglets', 'Tabs')], ['camera', $t('Galerie', 'Gallery')],
        ['tag', $t('Tarifs', 'Pricing')], ['user-round', $t('Membre d’équipe', 'Team member')], ['share-2', $t('Réseaux sociaux', 'Social icons')], ['map-pin', $t('Carte', 'Map')],
        ['timer', $t('Compte à rebours', 'Countdown')], ['mail', $t('Formulaire de contact', 'Contact form')], ['move-vertical', $t('Espace', 'Spacer')], ['minus', $t('Séparateur', 'Divider')],
    ];
    $palette = array_map(fn ($w) => $w[0] === 'heading' ? ['pencil', $w[1]] : ($w[0] === 'move-vertical' ? ['sliders-horizontal', $w[1]] : $w), $palette);
    $tiles = fn (int $n = 0) => array_map(fn ($w) => S::widgetTile($w[0], $w[1]), $n ? array_slice($palette, 0, $n) : $palette);

    $pages = [];

    // ─── Accueil ─────────────────────────────────────────────────────────
    $mosaic = K::grid(array_map(fn ($id) => S::miniHero($id, $catalog[$id], $lang, 190) , ['ong-espoir', 'tech-nova', 'restaurant-lac', 'eglise-grace']), 2, ['gap' => 14, 'gap_mobile' => 12]);
    // Aperçus arrondis, avec une ombre.
    foreach ($mosaic['children'] as &$tile) {
        $tile['settings'] += ['border_radius' => ['t' => 14, 'r' => 14, 'b' => 14, 'l' => 14], 'overflow' => 'hidden', 'box_shadow' => ['x' => 0, 'y' => 24, 'blur' => 50, 'spread' => -18, 'color' => 'rgba(0,0,0,0.55)']];
        $tile['settings']['width_tablet'] = '50%';
    }
    unset($tile);

    $pages['home'] = [
        'title' => $t('Accueil', 'Home'),
        'meta_title' => $t('GeniusPress · Le CMS open source avec page builder, modèles et assistant IA', 'GeniusPress · The open source CMS with a page builder, templates and an AI assistant'),
        'meta_description' => $t(
            'Construisez le site de votre organisation sans coder : page builder visuel, '.$templates.' modèles prêts, assistant IA, multilingue. Libre et installable partout.',
            'Build your organization’s website without code: visual page builder, '.$templates.' ready-made templates, AI assistant, multilingual. Free and installable anywhere.'
        ),
        'elements' => [
            K::section($theme, [K::row([
                K::col([
                    K::eyebrow($t('Libre · open source · AGPL-3.0', 'Free · open source · AGPL-3.0'), ['color' => 'global:accent']),
                    K::h($t('Construisez le site de votre organisation, sans coder.', 'Build your organization’s website, without code.'), 'h1', ['font_size' => 54, 'font_size_tablet' => 44, 'font_size_mobile' => 34, 'font_weight' => '800', 'line_height' => '1.08', 'letter_spacing' => '-0.02em', 'color' => '#ffffff']),
                    K::p($t(
                        '<p>Un <strong>page builder visuel</strong>, <strong>'.$templates.' modèles</strong> prêts pour votre secteur et un <strong>assistant IA</strong> qui rédige à votre place. Chez un hébergeur, sur un serveur ou sur un ordinateur sans Internet : vos données restent à vous.</p>',
                        '<p>A <strong>visual page builder</strong>, <strong>'.$templates.' templates</strong> ready for your sector and an <strong>AI assistant</strong> that writes for you. On shared hosting, on a server or on a computer without Internet: your data stays yours.</p>'
                    ), ['font_size' => 19, 'font_size_mobile' => 17, 'line_height' => '1.65', 'color' => 'rgba(255,255,255,0.8)']),
                    K::buttons([
                        K::btn($theme, $t('Voir les '.$templates.' modèles', 'See the '.$templates.' templates'), $u('templates'), 'accent'),
                        K::btn($theme, $t('Télécharger', 'Download'), $u('download'), 'outline-light'),
                    ]),
                    K::p($t('Version 0.1 · préversion · français et anglais', 'Version 0.1 · preview · French and English'), ['font_size' => 14, 'color' => 'rgba(255,255,255,0.55)']),
                ], '50%', ['gap' => 22, 'justify' => 'center']),
                K::col([$mosaic], '50%'),
            ], ['gap' => 56, 'gap_tablet' => 40, 'align' => 'center'])], [
                'background_type' => 'gradient', 'gradient_from' => '#0f172a', 'gradient_to' => '#312e81', 'gradient_angle' => 145, 'text_color' => '#ffffff',
                'padding' => ['t' => 104, 'r' => 20, 'b' => 104, 'l' => 20], 'padding_mobile' => ['t' => 64, 'b' => 64],
            ]),

            B::stats($theme, ['bg' => 'primary', 'items' => [
                [$templates, '', $t('modèles de sites', 'website templates')],
                [$pagesPerLang, '', $t('pages prêtes, par langue', 'ready pages, per language')],
                [$widgets, '', $t('widgets dans le builder', 'widgets in the builder')],
                [count($sectors), '', $t('secteurs d’activité', 'business sectors')],
            ]]),

            B::features($theme, [
                'eyebrow' => $t('Tout en un', 'All in one'),
                'title' => $t('Tout ce qu’il faut pour un vrai site', 'Everything a real website needs'),
                'intro' => $t('Les outils d’un grand page builder, sans abonnement ni dépendance à une plateforme.', 'The tools of a major page builder, with no subscription and no platform lock-in.'),
                'items' => [
                    ['layout-grid', $t('Builder visuel', 'Visual builder'), $t('Conteneurs imbriqués, '.$widgets.' widgets, réglages par appareil et au survol, aperçu mobile au rendu réel.', 'Nested containers, '.$widgets.' widgets, per-device and hover settings, mobile preview with the real rendering.'), $u('features'), $t('Découvrir le builder', 'Explore the builder')],
                    ['sparkles', $t('Assistant IA', 'AI assistant'), $t('Il vous pose quelques questions, propose des modèles adaptés et rédige les textes de vos pages.', 'It asks you a few questions, suggests matching templates and writes the text of your pages.'), $u('features', 'ia'), $t('Voir comment', 'See how')],
                    ['frame', $t($templates.' modèles prêts', $templates.' ready-made templates'), $t('Églises, écoles, ONG, hôpitaux, commerces… 8 pages chacun, en français et en anglais.', 'Churches, schools, NGOs, hospitals, shops… 8 pages each, in French and English.'), $u('templates'), $t('Parcourir les modèles', 'Browse the templates')],
                    ['languages', $t('Multilingue', 'Multilingual'), $t('Une version de chaque page par langue, liées entre elles, et une administration traduite.', 'One version of each page per language, linked together, and a translated admin.')],
                    ['workflow', 'Plugins', $t('Ajoutez des widgets et des fonctionnalités. Un plugin qui plante est désactivé tout seul.', 'Add widgets and features. A plugin that crashes is switched off automatically.')],
                    ['shield-check', $t('Vos données vous appartiennent', 'Your data belongs to you'), $t('Exportez tout le site dans un seul fichier et importez-le ailleurs. Jamais prisonnier.', 'Export the whole site to a single file and import it elsewhere. Never locked in.')],
                ],
            ]),

            K::section($theme, [
                K::box([
                    K::eyebrow($t('Modèles de sites', 'Website templates'), ['align' => 'center']),
                    K::h($t('Partez d’un site déjà conçu pour votre secteur', 'Start from a website already designed for your sector'), 'h2', ['align' => 'center', 'font_size' => 40, 'font_size_mobile' => 28, 'font_weight' => '800', 'line_height' => '1.15']),
                    K::p($t(
                        'Chaque modèle compte 8 pages en français et en anglais, avec de vraies photos. Ouvrez la démonstration : rien à installer.',
                        'Each template has 8 pages in French and English, with real photos. Open the demo: nothing to install.'
                    ), ['align' => 'center', 'font_size' => 18, 'color' => 'global:doux']),
                ], ['gap' => 14, 'content_width' => 'boxed', 'max_width' => 760, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]),
                K::grid($pick(['ong-horizon', 'ecole-excellence', 'hopital-soin', 'commerce-quartier', 'art-deco-wax', 'cabinet-conseil']), 3),
                K::buttons([K::btn($theme, $t('Voir les '.$templates.' modèles', 'See all '.$templates.' templates'), $u('templates'))], 'center'),
            ], ['background_color' => 'global:clair']),

            B::steps($theme, [
                'eyebrow' => $t('Assistant IA', 'AI assistant'),
                'title' => $t('Votre site en quatre étapes, avec l’IA ou à la main', 'Your website in four steps, with AI or by hand'),
                'intro' => $t('L’assistant écrit à partir de ce que vous lui dites. Il n’invente ni chiffres ni contacts : il laisse des espaces à compléter.', 'The assistant writes from what you tell it. It never makes up figures or contact details: it leaves placeholders for you.'),
                'items' => [
                    [$t('Décrivez votre organisation', 'Describe your organization'), $t('Quelques questions : votre activité, votre public, vos langues, votre ton.', 'A few questions: your activity, your audience, your languages, your tone.')],
                    [$t('Choisissez un modèle', 'Pick a template'), $t('L’IA propose les modèles qui vous correspondent, et explique pourquoi.', 'The AI suggests the templates that fit you, and explains why.')],
                    [$t('L’IA rédige vos textes', 'The AI writes your text'), $t('Titres, présentations, services : chaque page est adaptée à votre organisation.', 'Headings, introductions, services: every page is tailored to your organization.')],
                    [$t('Ajustez et publiez', 'Adjust and publish'), $t('Tout reste modifiable dans le builder. Rien n’est en ligne avant votre publication.', 'Everything stays editable in the builder. Nothing goes live until you publish.')],
                ],
            ]),

            K::section($theme, [K::row([
                K::col([
                    K::eyebrow($t('Le builder', 'The builder')),
                    K::h($t('La liberté d’un page builder professionnel', 'The freedom of a professional page builder'), 'h2', ['font_size' => 38, 'font_size_mobile' => 28, 'font_weight' => '800', 'line_height' => '1.15']),
                    K::p($t(
                        'Glissez des conteneurs, imbriquez-les, posez des widgets : vous réglez tout, sans une ligne de code.',
                        'Drag containers, nest them, drop widgets in: you control everything, without a line of code.'
                    ), ['font_size' => 17, 'color' => 'global:doux', 'line_height' => '1.7']),
                    K::w('icon-list', ['items' => array_map(fn ($b) => ['icon' => 'lucide:circle-check', 'text' => $b], [
                        $t('Conteneurs en ligne ou en colonne, retour à la ligne, alignements', 'Row or column containers, wrapping, alignment'),
                        $t('Réglages pour ordinateur, tablette et mobile, et au survol', 'Desktop, tablet, mobile and hover settings'),
                        $t('Couleurs et polices globales : un changement, tout le site suit', 'Global colors and fonts: change once, the whole site follows'),
                        $t('Dégradés, ombres, bordures, arrondis, superpositions', 'Gradients, shadows, borders, radius, overlays'),
                        $t('Annuler, rétablir, historique des versions, brouillons', 'Undo, redo, version history, drafts'),
                    ]), 'icon_color' => 'global:primary', 'gap' => 12]),
                    K::buttons([K::btn($theme, $t('Toutes les fonctionnalités', 'All features'), $u('features'))]),
                ], '46%', ['gap' => 18, 'justify' => 'center']),
                K::col([K::grid($tiles(12), 4, ['gap' => 12, 'gap_mobile' => 10])], '54%', [
                    'background_color' => 'global:clair', 'padding' => ['t' => 28, 'r' => 28, 'b' => 28, 'l' => 28], 'border_radius' => ['t' => 24, 'r' => 24, 'b' => 24, 'l' => 24],
                ]),
            ], ['gap' => 56, 'gap_tablet' => 32, 'align' => 'center'])]),

            B::features($theme, [
                'eyebrow' => $t('Pour qui ?', 'Who is it for?'),
                'title' => $t('Pensé pour les organisations africaines', 'Designed for African organizations'),
                'intro' => $t(
                    'Né en RDC pour une réalité simple : les ONG, les écoles, les églises et les entreprises ont besoin d’un site professionnel, en plusieurs langues, sans budget d’agence.',
                    'Born in the DRC for a simple need: NGOs, schools, churches and businesses need a professional website, in several languages, without an agency budget.'
                ),
                'bg' => 'light', 'cols' => 5,
                'items' => array_map(fn ($key, $s) => [$s['icon'], $s['name'][$lang], $t(count(S::bySector($key)).' modèles', count(S::bySector($key)).' templates'), $u('templates', $key), $t('Voir', 'View')], array_keys($sectors), $sectors),
            ]),

            B::faq($theme, [
                'eyebrow' => 'FAQ',
                'title' => $t('Questions fréquentes', 'Frequently asked questions'),
                'intro' => $t('Une autre question ? La documentation et le dépôt GitHub sont ouverts à tous.', 'Another question? The documentation and the GitHub repository are open to everyone.'),
                'buttons' => [[$t('Lire la documentation', 'Read the documentation'), $u('docs')]],
                'items' => [
                    [$t('GeniusPress est-il vraiment gratuit ?', 'Is GeniusPress really free?'), $t('Oui. Il est publié sous licence AGPL-3.0 : vous pouvez l’installer, l’utiliser et le modifier librement. Seul l’assistant IA demande une clé API Claude, payée à l’usage.', 'Yes. It is released under the AGPL-3.0 license: you can install, use and modify it freely. Only the AI assistant needs a Claude API key, paid per use.')],
                    [$t('Faut-il savoir coder ?', 'Do I need to know how to code?'), $t('Non. Choisissez un modèle, laissez l’assistant rédiger, puis ajustez dans le builder visuel.', 'No. Pick a template, let the assistant write, then adjust in the visual builder.')],
                    [$t('Puis-je utiliser les modèles et leurs photos ?', 'Can I use the templates and their photos?'), $t('Oui. Les photos viennent de Pixabay, sous une licence qui autorise l’usage commercial. Remplacez-les par les vôtres quand vous voulez.', 'Yes. The photos come from Pixabay, under a license that allows commercial use. Replace them with your own whenever you like.')],
                    [$t('Où mon site est-il hébergé ?', 'Where is my website hosted?'), $t('Où vous voulez : hébergement mutualisé, VPS, Docker ou ordinateur local. Vous pouvez tout exporter et déménager à tout moment.', 'Wherever you want: shared hosting, VPS, Docker or a local computer. You can export everything and move at any time.')],
                    [$t('Mon site peut-il être en plusieurs langues ?', 'Can my site be in several languages?'), $t('Oui : chaque page a une version par langue, et l’assistant peut traduire une page en gardant sa mise en page.', 'Yes: each page has one version per language, and the assistant can translate a page while keeping its layout.')],
                ],
            ]),

            B::cta($theme, [
                'tone' => 'dark',
                'title' => $t('Libre, ouvert, construit avec vous', 'Free, open, built with you'),
                'text' => $t('Le code, la documentation et la feuille de route sont publics. Proposez une amélioration, traduisez l’interface ou créez un plugin.', 'The code, the documentation and the roadmap are public. Suggest an improvement, translate the interface or build a plugin.'),
                'buttons' => [[$t('Contribuer', 'Contribute'), $u('contribute')], [$t('Voir le code sur GitHub', 'View the code on GitHub'), $repo]],
            ]),
        ],
    ];

    // ─── Fonctionnalités ─────────────────────────────────────────────────
    $li = fn (array $items) => '<ul>'.implode('', array_map(fn ($i) => '<li>'.$i.'</li>', $items)).'</ul>';
    $pages['features'] = [
        'title' => $t('Fonctionnalités', 'Features'),
        'meta_title' => $t('Fonctionnalités · GeniusPress', 'Features · GeniusPress'),
        'meta_description' => $t(
            'Page builder visuel, '.$widgets.' widgets, '.$templates.' modèles, assistant IA, multilingue, plugins, export complet : tout ce que fait GeniusPress.',
            'Visual page builder, '.$widgets.' widgets, '.$templates.' templates, AI assistant, multilingual, plugins, full export: everything GeniusPress does.'
        ),
        'elements' => [
            B::pageHeader(new Theme($theme->colors, $theme->font, $theme->headingFont, hero: 'gradient'), [
                'eyebrow' => $t('Version 0.1', 'Version 0.1'),
                'title' => $t('Fonctionnalités', 'Features'),
                'intro' => $t('Ce que GeniusPress sait déjà faire, et ce qui arrive ensuite.', 'What GeniusPress already does, and what comes next.'),
            ]),

            K::section($theme, [
                K::box([
                    K::h($t('En un coup d’œil', 'At a glance'), 'h2', ['align' => 'center', 'font_size' => 36, 'font_size_mobile' => 28, 'font_weight' => '800']),
                ], ['content_width' => 'boxed', 'max_width' => 760, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]),
                K::w('tabs', ['style' => 'pills', 'accent' => 'global:primary', 'items' => [
                    ['title' => 'Builder', 'html' => $li([
                        $t('Conteneurs imbriqués en ligne ou en colonne, avec retour à la ligne, alignements et espacements.', 'Nested row or column containers, with wrapping, alignment and spacing.'),
                        $t('Réglages par appareil (ordinateur, tablette, mobile) et au survol.', 'Per-device settings (desktop, tablet, mobile) and hover settings.'),
                        $t('Fonds en couleur, en dégradé ou en image avec superposition ; bordures, arrondis, ombres.', 'Color, gradient or image backgrounds with overlay; borders, radius, shadows.'),
                        $t('Navigateur de structure, annuler et rétablir, raccourcis clavier, historique des versions.', 'Structure navigator, undo and redo, keyboard shortcuts, version history.'),
                        $t('Brouillon séparé de la version en ligne : rien ne change avant la publication.', 'Drafts kept apart from the live version: nothing changes until you publish.'),
                    ])],
                    ['title' => 'Widgets', 'html' => '<p>'.$t($widgets.' widgets dans le cœur, et d’autres avec les plugins :', $widgets.' widgets in the core, and more with plugins:').'</p>'.$li([
                        $t('Contenu : titre, texte, image, vidéo, bouton, icône, liste de liens, espace, séparateur.', 'Content: heading, text, image, video, button, icon, link list, spacer, divider.'),
                        $t('Mise en valeur : boîtes d’icône et d’image, liste d’icônes, compteur, barre de progression, témoignage.', 'Highlights: icon and image boxes, icon list, counter, progress bar, testimonial.'),
                        $t('Organisation : accordéon (avec données structurées FAQ), onglets, galerie avec visionneuse.', 'Organization: accordion (with FAQ structured data), tabs, gallery with lightbox.'),
                        $t('Activité : tarifs, membre d’équipe, réseaux sociaux, carte, compte à rebours, formulaire de contact.', 'Business: pricing, team member, social icons, map, countdown, contact form.'),
                    ])],
                    ['title' => $t('Assistant IA', 'AI assistant'), 'html' => $li([
                        $t('Questionnaire sur votre organisation, puis propositions de modèles adaptés.', 'A questionnaire about your organization, then template suggestions that fit.'),
                        $t('Rédaction des pages d’un modèle à partir de votre présentation.', 'Writes a template’s pages from your own description.'),
                        $t('Création d’une page à partir d’une description ; réécriture, correction, changement de ton.', 'Creates a page from a description; rewrites, proofreads, changes the tone.'),
                        $t('Traduction d’une page en gardant la mise en page ; audit SEO et accessibilité.', 'Translates a page while keeping the layout; SEO and accessibility audit.'),
                        $t('Basé sur Claude ; la clé API reste chiffrée sur votre serveur.', 'Powered by Claude; the API key stays encrypted on your server.'),
                    ])],
                    ['title' => $t('Modèles', 'Templates'), 'html' => $li([
                        $t($templates.' modèles dans '.count($sectors).' secteurs, 8 pages chacun, en français et en anglais.', $templates.' templates in '.count($sectors).' sectors, 8 pages each, in French and English.'),
                        $t('Démonstration en direct, sans installation.', 'Live demo, nothing to install.'),
                        $t('Installation en un clic : pages, menus, couleurs et polices du modèle.', 'One-click install: the template’s pages, menus, colors and fonts.'),
                        $t('Couleurs globales : changez une couleur, tout le site suit.', 'Global colors: change one color, the whole site follows.'),
                    ])],
                    ['title' => $t('Site et données', 'Site and data'), 'html' => $li([
                        $t('Pages, médiathèque, référencement par page, messages du formulaire de contact.', 'Pages, media library, per-page SEO, contact form messages.'),
                        $t('En-tête, menus et pied de page par langue ; administration en français et en anglais.', 'Header, menus and footer per language; admin in French and English.'),
                        $t('Export et import complets du site dans un seul fichier.', 'Full site export and import in a single file.'),
                        $t('Plugins : widgets, routes, hooks, traductions ; un plugin défaillant est désactivé tout seul.', 'Plugins: widgets, routes, hooks, translations; a faulty plugin is switched off automatically.'),
                        $t('Installation par navigateur, ligne de commande, paquet zip ou Docker.', 'Install from the browser, the command line, a zip package or Docker.'),
                    ])],
                ]]),
            ], ['gap' => 32]),

            K::section($theme, [
                K::box([
                    K::eyebrow($t('Palette du builder', 'Builder palette'), ['align' => 'center']),
                    K::h($t($widgets.' widgets pour composer vos pages', $widgets.' widgets to compose your pages'), 'h2', ['align' => 'center', 'font_size' => 38, 'font_size_mobile' => 28, 'font_weight' => '800']),
                    K::p($t('Chaque widget a ses réglages de contenu et de style, pour chaque appareil.', 'Each widget has its own content and style settings, for each device.'), ['align' => 'center', 'font_size' => 18, 'color' => 'global:doux']),
                ], ['gap' => 14, 'content_width' => 'boxed', 'max_width' => 760, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]),
                (function () use ($tiles) {
                    $g = K::grid($tiles(), 6, ['gap' => 14, 'gap_mobile' => 10]);
                    foreach ($g['children'] as &$c) {
                        $c['settings']['width_tablet'] = '25%';
                        $c['settings']['width_mobile'] = '50%';
                    }
                    $g['settings']['direction_mobile'] = 'row';

                    return $g;
                })(),
            ], ['background_color' => 'global:clair']),

            K::section($theme, [K::row([
                K::col([
                    K::eyebrow($t('Assistant IA', 'AI assistant')),
                    K::h($t('Un assistant qui connaît votre secteur', 'An assistant that knows your sector'), 'h2', ['font_size' => 38, 'font_size_mobile' => 28, 'font_weight' => '800', 'line_height' => '1.15']),
                    K::p($t(
                        'L’assistant s’appuie sur une base de connaissances par secteur : ce qu’une église, une école ou un hôpital doit montrer sur son site, et comment le dire. Il rédige dans le ton de votre organisation, en français ou en anglais.',
                        'The assistant relies on a knowledge base for each sector: what a church, a school or a hospital should show on its website, and how to say it. It writes in your organization’s tone, in French or English.'
                    ), ['font_size' => 17, 'color' => 'global:doux', 'line_height' => '1.7']),
                    K::w('icon-list', ['items' => array_map(fn ($b) => ['icon' => 'lucide:sparkles', 'text' => $b], [
                        $t('Jamais de chiffres, de noms ni de contacts inventés', 'Never makes up figures, names or contact details'),
                        $t('Une version de la page est gardée avant chaque rédaction', 'A version of the page is kept before each rewrite'),
                        $t('Vous gardez la main : tout reste modifiable', 'You stay in control: everything remains editable'),
                    ]), 'icon_color' => 'global:accent', 'gap' => 12]),
                ], '55%', ['gap' => 18, 'justify' => 'center']),
                K::col([
                    K::w('counter', ['number' => count($sectors), 'label' => $t('secteurs connus de l’assistant', 'sectors known to the assistant'), 'number_color' => 'global:primary', 'number_size' => 64, 'align' => 'center']),
                    K::w('counter', ['number' => $pagesPerLang * 2, 'label' => $t('pages de modèles qu’il peut rédiger pour vous', 'template pages it can write for you'), 'number_color' => 'global:primary', 'number_size' => 64, 'align' => 'center', 'separator' => 'space']),
                ], '45%', $theme->card(['gap' => 28, 'justify' => 'center', 'padding' => ['t' => 48, 'r' => 32, 'b' => 48, 'l' => 32]])),
            ], ['gap' => 56, 'gap_tablet' => 32, 'align' => 'center'])], ['css_id' => 'ia']),

            B::features($theme, [
                'eyebrow' => $t('Partout', 'Anywhere'),
                'title' => $t('S’installe là où vous êtes', 'Runs where you are'),
                'bg' => 'light', 'cols' => 4,
                'items' => [
                    ['server', $t('Hébergement mutualisé', 'Shared hosting'), $t('Un paquet zip, sans Composer ni Node.js.', 'A zip package, no Composer or Node.js needed.')],
                    ['cloud', 'VPS & Docker', $t('GeniusPress et MariaDB en une commande.', 'GeniusPress and MariaDB in one command.')],
                    ['laptop', $t('Hors ligne', 'Offline'), $t('Sur un ordinateur, avec SQLite, sans Internet.', 'On a computer, with SQLite, without Internet.')],
                    ['download', $t('Export complet', 'Full export'), $t('Votre site entier dans un fichier, à importer ailleurs.', 'Your whole site in one file, to import elsewhere.')],
                ],
            ]),

            B::split($theme, [
                'eyebrow' => $t('Pour les ONG', 'For NGOs'),
                'title' => $t('Racontez votre impact, dans toutes vos langues', 'Tell your impact story, in all your languages'),
                'html' => $t(
                    '<p>Pages de projets, chiffres d’impact, appels aux dons, contenus en plusieurs langues pour vos partenaires et vos bénéficiaires. Six modèles sont conçus pour les ONG et associations.</p><p>Le plugin officiel de dons avec Mobile Money et carte bancaire est prévu dans une prochaine phase.</p>',
                    '<p>Project pages, impact figures, calls for donations, content in several languages for your partners and the people you serve. Six templates are designed for NGOs and nonprofits.</p><p>The official donations plugin, with Mobile Money and card payments, is planned for a later phase.</p>'
                ),
                'buttons' => [[$t('Modèles pour ONG', 'NGO templates'), $u('templates', 'ong')]],
                'photo' => ['ong-equipe-terrain', 'users', $t('Équipe de terrain avec des habitants', 'Field team with residents')],
                'reverse' => true,
            ]) + [],

            B::cta($theme, [
                'title' => $t('Essayez un modèle maintenant', 'Try a template now'),
                'text' => $t('Ouvrez une démonstration : pas d’inscription, rien à installer.', 'Open a demo: no sign-up, nothing to install.'),
                'buttons' => [[$t('Voir les modèles', 'See the templates'), $u('templates')], [$t('Télécharger', 'Download'), $u('download')]],
            ]),
        ],
    ];
    // Ancre conservée pour les anciens liens (#ngo).
    $pages['features']['elements'][5]['settings']['css_id'] = 'ngo';

    // ─── Modèles ─────────────────────────────────────────────────────────
    $sectorNav = K::box(array_map(fn ($key, $s) => K::w('button', [
        'text' => $s['name'][$lang], 'url' => '#'.$key, 'size' => 'sm', 'variant' => 'outline', 'bg_color' => 'global:primary', 'bg_color_hover' => 'global:primary', 'text_color_hover' => '#ffffff', 'border_radius' => 999,
    ]), array_keys($sectors), $sectors), ['direction' => 'row', 'wrap' => 'wrap', 'justify' => 'center', 'gap' => 8, 'content_width' => 'boxed', 'max_width' => 980, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]);

    $sectorSections = [];
    $n = 0;
    foreach ($sectors as $key => $s) {
        $sectorSections[] = K::section($theme, [
            K::box([
                K::w('icon', ['icon' => 'lucide:'.$s['icon'], 'size' => 30, 'color' => 'global:primary', 'align' => 'left']),
                K::h($s['name'][$lang], 'h2', ['font_size' => 32, 'font_size_mobile' => 26, 'font_weight' => '800']),
                K::p($t(count(S::bySector($key)).' modèles · 8 pages chacun · français et anglais', count(S::bySector($key)).' templates · 8 pages each · French and English'), ['color' => 'global:doux']),
            ], ['gap' => 8, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]),
            K::grid(array_map(fn ($id) => S::card($id, $catalog[$id], $lang), array_keys(S::bySector($key))), 3),
        ], ['css_id' => $key, 'gap' => 32, 'padding' => ['t' => 72, 'r' => 20, 'b' => 72, 'l' => 20], 'padding_mobile' => ['t' => 48, 'b' => 48]] + ($n++ % 2 ? ['background_color' => 'global:clair'] : []));
    }

    $pages['templates'] = [
        'title' => $t('Modèles', 'Templates'),
        'meta_title' => $t($templates.' modèles de sites gratuits · GeniusPress', $templates.' free website templates · GeniusPress'),
        'meta_description' => $t(
            $templates.' modèles de sites gratuits pour églises, écoles, ONG, hôpitaux, commerces, restaurants et plus : 8 pages chacun, en français et en anglais, avec démonstration en direct.',
            $templates.' free website templates for churches, schools, NGOs, hospitals, shops, restaurants and more: 8 pages each, in French and English, with a live demo.'
        ),
        'elements' => [
            K::section($theme, [
                K::box([
                    K::eyebrow($t($pagesPerLang.' pages prêtes, par langue', $pagesPerLang.' ready pages, per language'), ['align' => 'center', 'color' => 'global:accent']),
                    K::h($t($templates.' modèles de sites, prêts à l’emploi', $templates.' website templates, ready to use'), 'h1', ['align' => 'center', 'font_size' => 52, 'font_size_mobile' => 34, 'font_weight' => '800', 'line_height' => '1.1', 'color' => '#ffffff']),
                    K::p($t(
                        'Choisissez votre secteur, ouvrez la démonstration, puis installez le modèle en un clic depuis votre administration. L’assistant IA peut ensuite rédiger tous les textes pour votre organisation.',
                        'Choose your sector, open the demo, then install the template in one click from your admin. The AI assistant can then write all the text for your organization.'
                    ), ['align' => 'center', 'font_size' => 19, 'color' => 'rgba(255,255,255,0.8)']),
                ], ['gap' => 16, 'content_width' => 'boxed', 'max_width' => 820, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]),
            ], ['background_type' => 'gradient', 'gradient_from' => '#0f172a', 'gradient_to' => '#312e81', 'gradient_angle' => 145, 'text_color' => '#ffffff', 'padding' => ['t' => 96, 'r' => 20, 'b' => 88, 'l' => 20], 'padding_mobile' => ['t' => 56, 'b' => 52]]),

            K::section($theme, [$sectorNav], ['padding' => ['t' => 32, 'r' => 20, 'b' => 8, 'l' => 20]]),
            ...$sectorSections,

            B::steps($theme, [
                'eyebrow' => $t('Mode d’emploi', 'How it works'),
                'title' => $t('Utiliser un modèle', 'Using a template'),
                'bg' => 'light',
                'items' => [
                    [$t('Installez GeniusPress', 'Install GeniusPress'), $t('Chez un hébergeur, sur un serveur ou en local.', 'On shared hosting, a server or locally.')],
                    [$t('Ouvrez « Modèles »', 'Open “Templates”'), $t('Dans l’administration, répondez au questionnaire ou parcourez les modèles.', 'In the admin, answer the questionnaire or browse the templates.')],
                    [$t('Installez en un clic', 'Install in one click'), $t('Choisissez les langues ; pages, menus, couleurs et polices sont posés.', 'Choose the languages; pages, menus, colors and fonts are set up.')],
                    [$t('Personnalisez', 'Customize'), $t('Laissez l’IA rédiger, ou modifiez tout vous-même dans le builder.', 'Let the AI write, or edit everything yourself in the builder.')],
                ],
            ]),
            K::section($theme, [
                K::p($t(
                    '<p>Photos : <a href="https://pixabay.com/">Pixabay</a>, sous la <a href="https://pixabay.com/service/license-summary/">licence Pixabay</a> (usage libre, y compris commercial). Les noms des photographes figurent dans chaque modèle.</p>',
                    '<p>Photos: <a href="https://pixabay.com/">Pixabay</a>, under the <a href="https://pixabay.com/service/license-summary/">Pixabay license</a> (free to use, including commercially). Photographers are credited in each template.</p>'
                ), ['align' => 'center', 'font_size' => 14, 'color' => 'global:doux']),
            ], ['padding' => ['t' => 8, 'r' => 20, 'b' => 8, 'l' => 20]]),
            B::cta($theme, [
                'title' => $t('Prêt à construire votre site ?', 'Ready to build your website?'),
                'text' => $t('Installez GeniusPress, choisissez un modèle et publiez dès aujourd’hui.', 'Install GeniusPress, pick a template and publish today.'),
                'buttons' => [[$t('Télécharger GeniusPress', 'Download GeniusPress'), $u('download')], [$t('Lire la documentation', 'Read the documentation'), $u('docs')]],
            ]),
        ],
    ];

    return $pages;
};
