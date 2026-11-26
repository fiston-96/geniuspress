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

    // ─── Accueil (refonte éditoriale : crème, Playfair Display, terracotta, nuit indigo) ───
    $creme = 'global:clair';
    $sable = '#f3ece1';
    $encre = '#1c1917';
    $doux = '#57534e';
    $bord = '#e7e0d4';
    $zero = ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0];
    $corners = fn (int $r) => ['t' => $r, 'r' => $r, 'b' => $r, 'l' => $r];
    // Titre de section : sur-titre, grand titre en Playfair, introduction.
    $intro = fn (string $eyebrow, string $title, ?string $text = null, string $align = 'center', bool $dark = false) => K::box(array_filter([
        K::eyebrow($eyebrow, ['align' => $align, 'color' => $dark ? 'global:accent' : 'global:primary', 'font_size' => 13, 'font_family' => 'global:body']),
        K::h($title, 'h2', ['align' => $align, 'font_size' => 50, 'font_size_tablet' => 42, 'font_size_mobile' => 32, 'font_weight' => '400', 'line_height' => '1.12', 'letter_spacing' => '-0.015em', 'color' => $dark ? '#ffffff' : $encre]),
        $text ? K::p('<p>'.$text.'</p>', ['align' => $align, 'font_size' => 19, 'font_size_mobile' => 17, 'line_height' => '1.7', 'color' => $dark ? 'rgba(255,255,255,0.78)' : $doux]) : null,
    ]), ['gap' => 18, 'content_width' => 'boxed', 'max_width' => $align === 'center' ? 780 : 1140, 'padding' => $zero]);
    // Carte claire, bordure chaude.
    $card = fn (array $children, array $s = []) => K::box($children, $s + [
        'gap' => 12, 'background_color' => '#ffffff', 'border_style' => 'solid', 'border_width' => ['t' => 1, 'r' => 1, 'b' => 1, 'l' => 1], 'border_color' => $bord,
        'border_radius' => $corners(14), 'padding' => ['t' => 32, 'r' => 30, 'b' => 32, 'l' => 30], 'padding_mobile' => ['t' => 26, 'r' => 22, 'b' => 26, 'l' => 22],
    ]);
    $feature = fn (string $icon, string $title, string $text) => $card([
        K::w('icon', ['icon' => 'lucide:'.$icon, 'shape' => 'rounded', 'size' => 22, 'box' => 13, 'color' => 'global:primary', 'bg_color' => '#f6e9e1', 'align' => 'left']),
        K::h($title, 'h3', ['font_size' => 24, 'font_weight' => '400', 'line_height' => '1.25', 'color' => $encre, 'margin' => ['t' => 6, 'r' => 0, 'b' => 0, 'l' => 0]]),
        K::p('<p>'.$text.'</p>', ['font_size' => 16, 'line_height' => '1.65', 'color' => $doux]),
    ]);
    // En-tête des pages intérieures : crème, grand titre en Playfair.
    $pageHead = fn (string $eyebrow, string $title, string $text) => K::section($theme, [K::box([
        K::eyebrow($eyebrow, ['align' => 'center', 'color' => 'global:primary', 'font_size' => 13, 'font_family' => 'global:body']),
        K::h($title, 'h1', ['align' => 'center', 'font_size' => 64, 'font_size_tablet' => 52, 'font_size_mobile' => 38, 'font_weight' => '400', 'line_height' => '1.08', 'letter_spacing' => '-0.02em', 'color' => $encre]),
        K::p('<p>'.$text.'</p>', ['align' => 'center', 'font_size' => 20, 'font_size_mobile' => 17, 'line_height' => '1.7', 'color' => $doux]),
    ], ['gap' => 18, 'content_width' => 'boxed', 'max_width' => 820, 'padding' => $zero])], ['background_color' => $creme, 'padding' => ['t' => 104, 'r' => 20, 'b' => 72, 'l' => 20], 'padding_mobile' => ['t' => 64, 'b' => 48]]);
    $sectorWords = $t("ONG\nécole\nradio\néglise\nentreprise\nmairie\ntélévision\nclinique", "NGO\nschool\nradio station\nchurch\nbusiness\ntown hall\nTV channel\nclinic");

    $pages['home'] = [
        'title' => $t('Accueil', 'Home'),
        'meta_title' => $t('GeniusPress · Le CMS libre qui donne à votre organisation un site élégant', 'GeniusPress · The free CMS that gives your organization an elegant website'),
        'meta_description' => $t(
            'Créez le site de votre ONG, école, église, radio ou entreprise : '.$templates.' modèles prêts en français et en anglais, un éditeur visuel et un assistant IA. Libre et gratuit.',
            'Build the website of your NGO, school, church, radio station or business: '.$templates.' ready-made templates in French and English, a visual editor and an AI assistant. Free and open source.'
        ),
        'elements' => [
            // 1. Ouverture : titre animé, promesse, deux boutons, trois vraies captures de modèles.
            K::section($theme, [
                K::box([
                    K::eyebrow($t('Libre et gratuit · en français et en anglais', 'Free and open source · in French and English'), ['align' => 'center', 'font_size' => 13, 'color' => 'global:primary', 'font_family' => 'global:body']),
                    K::w('animated-heading', [
                        // Mot animé en fin de phrase : aucune place réservée, aucun décalage, quelle que soit la largeur.
                        'before' => $t('Un site élégant pour votre', 'An elegant website for your'), 'words' => $sectorWords, 'after' => '',
                        'effect' => 'typing', 'speed' => 6, 'pause' => 2000, 'tag' => 'h1', 'align' => 'center',
                        'font_size' => 76, 'font_size_tablet' => 58, 'font_size_mobile' => 40, 'font_weight' => '400', 'line_height' => '1.06', 'letter_spacing' => '-0.02em',
                        'color' => $encre, 'words_color' => 'global:primary',
                    ]),
                    K::p($t(
                        '<p>Partez d’un des <strong>'.$templates.' modèles</strong> conçus pour les organisations africaines, laissez l’assistant IA écrire vos textes, puis modifiez tout vous-même. Sans savoir coder.</p>',
                        '<p>Start from one of <strong>'.$templates.' templates</strong> designed for African organizations, let the AI assistant write your text, then edit everything yourself. No coding needed.</p>'
                    ), ['align' => 'center', 'font_size' => 20, 'font_size_mobile' => 17, 'line_height' => '1.7', 'color' => $doux]),
                    K::buttons([
                        K::btn($theme, $t('Voir les '.$templates.' modèles', 'See the '.$templates.' templates'), $u('templates')),
                        K::btn($theme, $t('Télécharger GeniusPress', 'Download GeniusPress'), $u('download'), 'outline'),
                    ], 'center'),
                    K::p($t('Gratuit · sans abonnement · vos données restent à vous', 'Free · no subscription · your data stays yours'), ['align' => 'center', 'font_size' => 14, 'color' => '#6f6862']),
                ], ['gap' => 22, 'content_width' => 'boxed', 'max_width' => 940, 'padding' => $zero]),
                K::row([
                    // Sur tablette et téléphone, seule la capture centrale reste, élargie.
                    K::col([S::screen('medias-journal-quotidien', $lang)], '27%', ['margin' => ['t' => 90, 'r' => 0, 'b' => 0, 'l' => 0], 'hide_tablet' => true, 'hide_mobile' => true]),
                    K::col([S::screen('ong-horizon', $lang)], '46%', ['width_tablet' => '86%']),
                    K::col([S::screen('restaurant-lac', $lang)], '27%', ['margin' => ['t' => 90, 'r' => 0, 'b' => 0, 'l' => 0], 'hide_tablet' => true, 'hide_mobile' => true]),
                ], ['gap' => 24, 'gap_tablet' => 16, 'align' => 'start', 'justify' => 'center', 'content_width' => 'boxed', 'max_width' => 1240]),
            ], ['background_color' => $creme, 'padding' => ['t' => 104, 'r' => 20, 'b' => 56, 'l' => 20], 'padding_mobile' => ['t' => 64, 'b' => 40], 'gap' => 64, 'gap_mobile' => 40]),

            // 3. Manifeste et chiffres.
            K::section($theme, [K::row([
                K::col([
                    K::eyebrow($t('Pourquoi GeniusPress', 'Why GeniusPress'), ['font_size' => 13, 'color' => 'global:primary', 'font_family' => 'global:body']),
                    K::h($t('Un site professionnel ne devrait pas coûter un budget d’agence.', 'A professional website shouldn’t cost an agency budget.'), 'h2', [
                        'font_size' => 46, 'font_size_tablet' => 40, 'font_size_mobile' => 31, 'font_weight' => '400', 'line_height' => '1.14', 'letter_spacing' => '-0.015em', 'color' => $encre]),
                ], '52%', ['gap' => 18]),
                K::col([
                    K::p($t(
                        '<p>Les ONG, les écoles, les églises, les radios et les entreprises ont besoin d’un site soigné, en plusieurs langues, qu’elles peuvent modifier elles-mêmes. GeniusPress réunit tout ce qu’il faut dans un seul logiciel libre : pas d’extension à acheter, pas d’abonnement obligatoire, et vos données restent à vous.</p>',
                        '<p>NGOs, schools, churches, radio stations and businesses need a polished website, in several languages, that they can edit themselves. GeniusPress brings everything together in a single free software: no add-ons to buy, no mandatory subscription, and your data stays yours.</p>'
                    ), ['font_size' => 18, 'line_height' => '1.75', 'color' => $doux]),
                ], '48%', ['justify' => 'center']),
            ], ['gap' => 64, 'gap_tablet' => 40, 'align' => 'center']),
                K::grid(array_map(fn ($n) => K::box([K::w('counter', [
                    'number' => $n[0], 'label' => $n[1], 'separator' => 'space', 'duration' => 1600, 'align' => 'left',
                    'number_color' => 'global:primary', 'number_size' => 60, 'number_size_mobile' => 40, 'label_color' => $doux,
                ])], ['padding' => ['t' => 22, 'r' => 0, 'b' => 0, 'l' => 0], 'border_style' => 'solid', 'border_width' => ['t' => 1, 'r' => 0, 'b' => 0, 'l' => 0], 'border_color' => $bord, 'width_mobile' => '46%']), [
                    [$templates, $t('modèles de sites, 8 pages chacun', 'website templates, 8 pages each')],
                    [$templates * 16, $t('pages prêtes, en français et en anglais', 'ready pages, in French and English')],
                    [$widgets, $t('blocs prêts à l’emploi', 'ready-made building blocks')],
                    [count($sectors), $t('secteurs d’activité', 'business sectors')],
                ]), 4, ['gap' => 32, 'gap_mobile' => 16, 'direction_mobile' => 'row']),
            ], ['background_color' => $creme, 'gap' => 64]),

            // 3 bis. Le builder lui-même : vraie capture de l'éditeur (cette page, en cours de modification).
            K::section($theme, [
                $intro($t('L’éditeur visuel', 'The visual editor'), $t('Vous voyez la page telle qu’elle sera publiée', 'You see the page exactly as it will be published'),
                    $t('Cliquez sur un élément de la page et réglez-le à gauche : textes, couleurs, tailles, marges, animations. Cette page d’accueil a été construite avec ce même éditeur.', 'Click any element on the page and adjust it on the left: text, colors, sizes, spacing, animations. This very home page was built with the same editor.')),
                S::builderShot($lang, ['entrance' => 'fade-up', 'entrance_duration' => 900]),
                K::grid(array_map(fn ($p) => K::box([
                    K::w('icon', ['icon' => 'lucide:'.$p[0], 'shape' => 'rounded', 'size' => 20, 'box' => 11, 'color' => 'global:primary', 'bg_color' => '#ffffff', 'align' => 'left']),
                    K::h($p[1], 'h3', ['font_size' => 21, 'font_weight' => '400', 'line_height' => '1.3', 'color' => $encre, 'margin' => ['t' => 4, 'r' => 0, 'b' => 0, 'l' => 0]]),
                    K::p('<p>'.$p[2].'</p>', ['font_size' => 16, 'line_height' => '1.65', 'color' => $doux]),
                ], ['gap' => 10, 'padding' => $zero]), [
                    ['layers', $t('Cliquez, glissez, déposez', 'Click, drag, drop'), $t($widgets.' blocs (titres, photos, vidéos, formulaires, cartes…) à placer où vous voulez.', $widgets.' blocks (headings, photos, videos, forms, maps…) to place wherever you like.')],
                    ['smartphone', $t('Réglé pour chaque écran', 'Tuned for every screen'), $t('Tailles, marges et visibilité par appareil : ordinateur, tablette et téléphone.', 'Sizes, spacing and visibility per device: desktop, tablet and phone.')],
                    ['history', $t('Rien n’est perdu', 'Nothing gets lost'), $t('Annuler, historique des versions et brouillon séparé de la version en ligne.', 'Undo, version history and a draft kept apart from the live version.')],
                ]), 3, ['gap' => 40, 'gap_mobile' => 28]),
            ], ['background_color' => $sable, 'gap' => 56, 'gap_mobile' => 40, 'padding' => ['t' => 112, 'r' => 20, 'b' => 112, 'l' => 20], 'padding_mobile' => ['t' => 72, 'b' => 72]]),

            // 4. Tout est inclus.
            K::section($theme, [
                $intro($t('Tout est inclus', 'Everything included'), $t('Tout ce qu’un vrai site demande, sans extension payante', 'Everything a real website needs, with no paid add-ons'),
                    $t('Ce que d’autres vendent en options est livré avec GeniusPress, dès l’installation.', 'What others sell as options ships with GeniusPress, from the first install.')),
                K::grid([
                    $feature('search', $t('Bien trouvé sur Google', 'Easy to find on Google'), $t('Adresses claires, plan du site pour les moteurs de recherche et bel aperçu quand on partage un lien sur WhatsApp ou Facebook.', 'Clean addresses, a sitemap for search engines and a neat preview when a link is shared on WhatsApp or Facebook.')),
                    $feature('sparkles', $t('Animations soignées', 'Polished animations'), $t('Des éléments qui apparaissent en douceur, des titres animés, un menu toujours à portée de main et des passages fluides d’une page à l’autre.', 'Elements that fade in smoothly, animated headings, a menu always within reach and smooth transitions from one page to the next.')),
                    $feature('palette', $t('Un style pour tout le site', 'One style for the whole site'), $t('Dessinez une fois l’en-tête, le pied de page et la présentation des articles : toutes les pages suivent.', 'Design the header, the footer and the article layout once: every page follows.')),
                    $feature('newspaper', $t('Blog et actualités', 'Blog and news'), $t('Publiez vos nouvelles, classez-les par thème et laissez vos lecteurs les partager sur WhatsApp en un geste.', 'Publish your news, sort it by topic and let your readers share it on WhatsApp in one tap.')),
                    $feature('languages', $t('Français et anglais', 'French and English'), $t('Chaque page existe dans chaque langue et le visiteur passe de l’une à l’autre en un clic. L’administration aussi est traduite.', 'Each page exists in every language and visitors switch between them in one click. The admin is translated too.')),
                    $feature('package', $t('Vos données vous appartiennent', 'Your data belongs to you'), $t('Exportez tout le site dans un seul fichier et installez-le ailleurs quand vous voulez. Vous n’êtes jamais prisonnier d’une plateforme.', 'Export the whole site into a single file and move it elsewhere whenever you like. You are never locked into a platform.')),
                ], 3, ['gap' => 24, 'gap_mobile' => 16]),
            ], ['background_color' => $creme, 'gap' => 56, 'padding' => ['t' => 112, 'r' => 20, 'b' => 112, 'l' => 20], 'padding_mobile' => ['t' => 72, 'b' => 72]]),

            // 5. Assistant IA, sur fond nuit.
            K::section($theme, [K::row([
                K::col([
                    $intro($t('Assistant IA', 'AI assistant'), $t('Décrivez votre organisation. L’assistant écrit le reste.', 'Describe your organization. The assistant writes the rest.'),
                        $t('Il pose quelques questions, propose les modèles qui vous correspondent et rédige chaque page. Il n’invente ni chiffres ni contacts : il laisse des espaces à compléter.', 'It asks a few questions, suggests the templates that fit you and writes every page. It never makes up figures or contact details: it leaves placeholders for you.'),
                        'left', true),
                    K::w('icon-list', ['items' => array_map(fn ($b) => ['icon' => 'lucide:check', 'text' => $b], [
                        $t('Textes adaptés à votre secteur et à votre public', 'Text tailored to your sector and audience'),
                        $t('Traduction d’une page en gardant sa mise en page', 'Page translation that keeps the layout'),
                        $t('Relecture de vos pages pour Google et pour l’accessibilité', 'Review of your pages for Google and for accessibility'),
                        $t('Tout reste modifiable, rien n’est publié sans vous', 'Everything stays editable, nothing goes live without you'),
                    ]), 'icon_color' => 'global:accent', 'text_color' => 'rgba(255,255,255,0.85)', 'gap' => 12]),
                ], '50%', ['gap' => 28]),
                K::col([
                    // Conversation d'exemple.
                    K::box([K::p($t('<p>Nous sommes une ONG d’éducation. Nous voulons présenter nos écoles et recevoir des dons.</p>', '<p>We are an education NGO. We want to present our schools and receive donations.</p>'), ['font_size' => 16, 'line_height' => '1.6', 'color' => $encre])],
                        ['background_color' => '#ffffff', 'border_radius' => ['t' => 16, 'r' => 16, 'b' => 4, 'l' => 16], 'padding' => ['t' => 16, 'r' => 20, 'b' => 16, 'l' => 20], 'width' => '86%', 'align_self' => 'end']),
                    K::box([
                        K::p($t('<p>Je vous propose <strong>Horizon</strong> : grande photo d’accueil, programmes en cartes et dons mis en avant. J’ai rédigé vos 8 pages, en français et en anglais.</p>', '<p>I suggest <strong>Horizon</strong>: large welcome photo, programs as cards and donations front and center. I’ve written your 8 pages, in French and English.</p>'), ['font_size' => 16, 'line_height' => '1.6', 'color' => '#ffffff']),
                        S::screen('ong-horizon', $lang, ['box_shadow' => null]),
                    ], ['gap' => 14, 'background_color' => 'rgba(255,255,255,0.08)', 'border_style' => 'solid', 'border_width' => ['t' => 1, 'r' => 1, 'b' => 1, 'l' => 1], 'border_color' => 'rgba(255,255,255,0.14)',
                        'border_radius' => ['t' => 16, 'r' => 16, 'b' => 16, 'l' => 4], 'padding' => ['t' => 18, 'r' => 18, 'b' => 18, 'l' => 18], 'width' => '92%']),
                ], '50%', ['gap' => 16]),
            ], ['gap' => 64, 'gap_tablet' => 40, 'align' => 'center'])], ['background_color' => 'global:secondary', 'padding' => ['t' => 112, 'r' => 20, 'b' => 112, 'l' => 20], 'padding_mobile' => ['t' => 72, 'b' => 72]]),

            // 6. Modèles : carrousel de vraies captures.
            K::section($theme, [
                $intro($t($templates.' modèles', $templates.' templates'), $t('Partez d’un site déjà conçu pour votre secteur', 'Start from a website already designed for your sector'),
                    $t('Chaque modèle compte 8 pages en français et en anglais, avec de vraies photos, un blog et des animations. Ouvrez la démonstration : rien à installer.', 'Each template has 8 pages in French and English, with real photos, a blog and animations. Open the demo: nothing to install.')),
                K::w('carousel', [
                    'items' => array_map(fn ($id) => K::photoFields('capture-'.$id, 'layout-template', $catalog[$id]['name'][$lang]) + [
                        'title' => $catalog[$id]['name'][$lang], 'text' => $sectors[$catalog[$id]['sector']]['name'][$lang] ?? '', 'link' => S::demoUrl($id, $lang),
                    ], ['ong-horizon', 'medias-tv-ecran', 'ecole-excellence', 'eglise-grace', 'restaurant-lac', 'medias-radio-onde', 'hopital-soin', 'tech-nova', 'gouvernement-republique', 'commerce-elegance', 'art-deco-wax', 'cabinet-justice']),
                    'per_view' => 3, 'per_view_tablet' => 2, 'per_view_mobile' => 1, 'gap' => 24, 'ratio' => '16:9', 'card' => true, 'autoplay' => true, 'interval' => 5,
                    'arrows' => true, 'dots' => true, 'radius' => 10, 'title_color' => $encre,
                ]),
                K::buttons([K::btn($theme, $t('Voir les '.$templates.' modèles', 'See all '.$templates.' templates'), $u('templates'))], 'center'),
            ], ['background_color' => '#ffffff', 'gap' => 48]),

            // 7. Comparaison.
            K::section($theme, [
                $intro($t('Comparaison', 'Comparison'), $t('Tout en un, plutôt qu’une pile d’extensions', 'All in one, instead of a pile of add-ons'),
                    $t('Avec la plupart des logiciels de site, chaque fonction s’ajoute par une extension, souvent payante. Avec GeniusPress, elle est déjà là.', 'With most website software, every feature comes as an add-on, often a paid one. With GeniusPress, it is already there.')),
                K::box([K::w('data-table', [
                    'data' => implode("\n", [
                        $t(' | GeniusPress | Logiciel classique + extensions', ' | GeniusPress | Classic software + add-ons'),
                        $t('Éditeur visuel | Inclus | Extension, souvent payante', 'Visual editor | Included | Add-on, often paid'),
                        $t('Animations, carrousels, titres animés | Inclus | Version payante de l’éditeur', 'Animations, carousels, animated headings | Included | Paid version of the editor'),
                        $t('En-tête, pied de page et articles sur mesure | Inclus | Version payante de l’éditeur', 'Custom header, footer and article layout | Included | Paid version of the editor'),
                        $t('Site en plusieurs langues | Inclus | Extension payante', 'Multilingual website | Included | Paid add-on'),
                        $t('Assistant IA qui rédige vos pages | Inclus | Extension', 'AI assistant that writes your pages | Included | Add-on'),
                        $t('Modèles pensés pour l’Afrique | '.$templates.', en français et en anglais | Rares', 'Templates designed for Africa | '.$templates.', in French and English | Rare'),
                        $t('Export complet en un fichier | Inclus | Extension', 'Full export in one file | Included | Add-on'),
                    ]),
                    'header' => true, 'striped' => true, 'header_bg' => 'global:secondary', 'header_color' => '#ffffff', 'border_color' => $bord, 'font_size' => 16, 'font_size_mobile' => 14,
                ])], ['content_width' => 'boxed', 'max_width' => 940, 'padding' => $zero]),
            ], ['background_color' => $creme, 'gap' => 48]),

            // 8. Fait pour l'Afrique.
            K::section($theme, [
                $intro($t('Fait pour l’Afrique', 'Made for Africa'), $t('Pensé pour la réalité du terrain', 'Designed for real conditions on the ground')),
                K::grid([
                    $feature('laptop', $t('Même sans Internet', 'Even without Internet'), $t('Installez GeniusPress sur un ordinateur et travaillez hors ligne. Publiez quand la connexion revient.', 'Install GeniusPress on a computer and work offline. Publish when the connection is back.')),
                    $feature('smartphone', $t('Léger sur mobile', 'Light on mobile'), $t('Des sites rapides, lisibles et faciles à toucher sur téléphone, même avec une petite connexion.', 'Fast, readable, easy-to-tap websites on phones, even on a weak connection.')),
                    $feature('message-circle', $t('WhatsApp et appel', 'WhatsApp and calls'), $t('Bouton WhatsApp flottant, appel en un geste, partage des articles sur WhatsApp.', 'Floating WhatsApp button, one-tap calling, article sharing on WhatsApp.')),
                    $feature('wallet', $t('Mobile Money, bientôt', 'Mobile Money, soon'), $t('Le paiement par Mobile Money arrive avec l’offre hébergée : dons, abonnements et achats.', 'Mobile Money payments are coming with the hosted offer: donations, subscriptions and purchases.')),
                ], 4, ['gap' => 20, 'gap_mobile' => 16]),
            ], ['background_color' => $sable, 'gap' => 48]),

            // 9. Questions fréquentes.
            B::faq($theme, [
                'eyebrow' => 'FAQ',
                'title' => $t('Questions fréquentes', 'Frequently asked questions'),
                'intro' => $t('Une autre question ? La documentation et le code sont ouverts à tous.', 'Another question? The documentation and the code are open to everyone.'),
                'buttons' => [[$t('Lire la documentation', 'Read the documentation'), $u('docs')]],
                'items' => [
                    [$t('GeniusPress est-il vraiment gratuit ?', 'Is GeniusPress really free?'), $t('Oui. Il est publié sous licence AGPL-3.0 : vous pouvez l’installer, l’utiliser et le modifier librement. Seul l’assistant IA a un coût : il s’appuie sur un service d’IA payé à l’usage, et tout le reste fonctionne sans lui.', 'Yes. It is released under the AGPL-3.0 license: you can install, use and modify it freely. Only the AI assistant has a cost: it relies on a pay-as-you-go AI service, and everything else works without it.')],
                    [$t('Faut-il savoir coder ?', 'Do I need to know how to code?'), $t('Non. Choisissez un modèle, laissez l’assistant rédiger, puis cliquez sur ce que vous voulez changer dans l’éditeur visuel.', 'No. Pick a template, let the assistant write, then click whatever you want to change in the visual editor.')],
                    [$t('Puis-je utiliser les modèles et leurs photos ?', 'Can I use the templates and their photos?'), $t('Oui. Les photos viennent de Pixabay, sous une licence qui autorise l’usage commercial. Remplacez-les par les vôtres quand vous voulez.', 'Yes. The photos come from Pixabay, under a license that allows commercial use. Replace them with your own whenever you like.')],
                    [$t('Où mon site est-il hébergé ?', 'Where is my website hosted?'), $t('Où vous voulez : chez un hébergeur classique, sur votre propre serveur ou sur un ordinateur du bureau. Vous pouvez tout exporter et déménager à tout moment.', 'Wherever you want: with a regular web host, on your own server or on an office computer. You can export everything and move at any time.')],
                    [$t('Mon site peut-il être en plusieurs langues ?', 'Can my site be in several languages?'), $t('Oui : chaque page a une version par langue, et l’assistant peut traduire une page en gardant sa mise en page.', 'Yes: each page has one version per language, and the assistant can translate a page while keeping its layout.')],
                ],
            ]),

            // 10. Dernier appel, en terracotta.
            K::section($theme, [K::box([
                K::h($t('Votre organisation mérite mieux qu’un site générique.', 'Your organization deserves better than a generic website.'), 'h2', [
                    'align' => 'center', 'font_size' => 52, 'font_size_tablet' => 44, 'font_size_mobile' => 32, 'font_weight' => '400', 'line_height' => '1.12', 'color' => '#ffffff']),
                K::p($t('<p>Choisissez un modèle, laissez l’assistant écrire, publiez. Le reste vous appartient.</p>', '<p>Pick a template, let the assistant write, publish. The rest is yours.</p>'), ['align' => 'center', 'font_size' => 19, 'color' => 'rgba(255,255,255,0.88)']),
                K::buttons([
                    K::btn($theme, $t('Choisir un modèle', 'Choose a template'), $u('templates'), 'light'),
                    K::btn($theme, $t('Télécharger', 'Download'), $u('download'), 'outline-light'),
                ], 'center'),
            ], ['gap' => 22, 'content_width' => 'boxed', 'max_width' => 860, 'padding' => $zero])], ['background_color' => 'global:primary', 'padding' => ['t' => 112, 'r' => 20, 'b' => 112, 'l' => 20], 'padding_mobile' => ['t' => 72, 'b' => 72]]),
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
            $pageHead($t('Version 0.1', 'Version 0.1'), $t('Fonctionnalités', 'Features'),
                $t('Ce que GeniusPress sait déjà faire, et ce qui arrive ensuite.', 'What GeniusPress already does, and what comes next.')),

            K::section($theme, [
                K::box([
                    K::h($t('En un coup d’œil', 'At a glance'), 'h2', ['align' => 'center', 'font_size' => 36, 'font_size_mobile' => 28, 'font_weight' => '800']),
                ], ['content_width' => 'boxed', 'max_width' => 760, 'padding' => ['t' => 0, 'r' => 0, 'b' => 0, 'l' => 0]]),
                K::w('tabs', ['style' => 'pills', 'accent' => 'global:primary', 'items' => [
                    ['title' => $t('Éditeur visuel', 'Visual editor'), 'html' => $li([
                        $t('Conteneurs imbriqués en ligne ou en colonne, avec retour à la ligne, alignements et espacements.', 'Nested row or column containers, with wrapping, alignment and spacing.'),
                        $t('Réglages par appareil (ordinateur, tablette, mobile) et au survol.', 'Per-device settings (desktop, tablet, mobile) and hover settings.'),
                        $t('Fonds en couleur, en dégradé ou en image avec superposition ; bordures, arrondis, ombres.', 'Color, gradient or image backgrounds with overlay; borders, radius, shadows.'),
                        $t('Navigateur de structure, annuler et rétablir, raccourcis clavier, historique des versions.', 'Structure navigator, undo and redo, keyboard shortcuts, version history.'),
                        $t('Brouillon séparé de la version en ligne : rien ne change avant la publication.', 'Drafts kept apart from the live version: nothing changes until you publish.'),
                    ])],
                    ['title' => $t('Blocs', 'Blocks'), 'html' => '<p>'.$t($widgets.' blocs (widgets) livrés avec GeniusPress, et d’autres avec les plugins. Parmi eux :', $widgets.' blocks (widgets) ship with GeniusPress, and more come with plugins. Among them:').'</p>'.$li([
                        $t('Contenu : titre, texte, image, vidéo, bouton, icône, liste de liens, espace, séparateur.', 'Content: heading, text, image, video, button, icon, link list, spacer, divider.'),
                        $t('Mise en valeur : boîtes d’icône et d’image, liste d’icônes, compteur, barre de progression, témoignage.', 'Highlights: icon and image boxes, icon list, counter, progress bar, testimonial.'),
                        $t('Organisation : accordéon (avec données structurées FAQ), onglets, galerie avec visionneuse.', 'Organization: accordion (with FAQ structured data), tabs, gallery with lightbox.'),
                        $t('Activité : tarifs, membre d’équipe, réseaux sociaux, carte, compte à rebours, formulaire de contact.', 'Business: pricing, team member, social icons, map, countdown, contact form.'),
                    ])],
                    ['title' => $t('Assistant IA', 'AI assistant'), 'html' => $li([
                        $t('Questionnaire sur votre organisation, puis propositions de modèles adaptés.', 'A questionnaire about your organization, then template suggestions that fit.'),
                        $t('Rédaction des pages d’un modèle à partir de votre présentation.', 'Writes a template’s pages from your own description.'),
                        $t('Création d’une page à partir d’une description ; réécriture, correction, changement de ton.', 'Creates a page from a description; rewrites, proofreads, changes the tone.'),
                        $t('Traduction d’une page en gardant la mise en page ; relecture pour Google et pour l’accessibilité.', 'Translates a page while keeping the layout; review for Google and for accessibility.'),
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
                    K::eyebrow($t('Les blocs de l’éditeur', 'The editor’s blocks'), ['align' => 'center']),
                    K::h($t($widgets.' blocs pour composer vos pages', $widgets.' blocks to compose your pages'), 'h2', ['align' => 'center', 'font_size' => 38, 'font_size_mobile' => 28, 'font_weight' => '800']),
                    K::p($t('Voici les plus utilisés. Chacun a ses réglages de contenu et de style, pour chaque appareil.', 'Here are the most used. Each has its own content and style settings, for each device.'), ['align' => 'center', 'font_size' => 18, 'color' => 'global:doux']),
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
            $pageHead($t(($templates * 16).' pages prêtes, en français et en anglais', ($templates * 16).' ready pages, in French and English'),
                $t($templates.' modèles de sites, prêts à l’emploi', $templates.' website templates, ready to use'),
                $t('Choisissez votre secteur, ouvrez la démonstration, puis installez le modèle en un clic depuis votre administration. L’assistant IA peut ensuite rédiger tous les textes pour votre organisation.',
                    'Choose your sector, open the demo, then install the template in one click from your admin. The AI assistant can then write all the text for your organization.')),

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
                    [$t('Personnalisez', 'Customize'), $t('Laissez l’IA rédiger, ou modifiez tout vous-même dans l’éditeur visuel.', 'Let the AI write, or edit everything yourself in the visual editor.')],
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
