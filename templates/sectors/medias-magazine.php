<?php

/*
 * Pack « Médias et presse » — magazine (culture, société, mode, entrepreneuriat) :
 * 8 pages, en français et en anglais. Articles en carrousel, rubriques, le numéro
 * du mois et ses couvertures, abonnements, équipe et contributeurs, publicité
 * (lectorat en graphique) et contact. Textes et articles d'exemple à personnaliser.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Médias et presse', 'en' => 'Media and press'],
        'icon' => 'newspaper',
        'keywords' => ['média', 'medias', 'presse', 'magazine', 'revue', 'mensuel', 'journal', 'radio', 'télévision', 'podcast', 'journaliste', 'media', 'press', 'magazine', 'journalist'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $articles = '/articles';
        $sections = $u('/rubriques', '/sections');
        $issue = $u('/le-magazine', '/the-magazine');
        $subscribe = $u('/abonnement', '/subscribe');
        $team = $u('/equipe', '/team');
        $advertise = $u('/publicite', '/advertise');
        $contact = '/contact';
        $cat = fn (string $fr, string $en) => $articles.$u('/categorie/', '/category/').$T($fr, $en);

        $rubriques = [
            ['user-round', 'Portraits', $T('Celles et ceux qui inventent l’Afrique de demain, racontés en longueur.', 'The women and men inventing tomorrow’s Africa, told at length.'), $cat('portraits', 'profiles'), $T('Lire', 'Read')],
            ['palette', 'Culture', $T('Musique, cinéma, littérature et arts visuels : la création contemporaine.', 'Music, film, literature and visual arts: contemporary creation.'), $cat('culture', 'culture'), $T('Lire', 'Read')],
            ['shirt', $T('Mode et beauté', 'Fashion and beauty'), $T('Créateurs, tendances et savoir-faire, du wax au prêt-à-porter.', 'Designers, trends and craftsmanship, from wax prints to ready-to-wear.'), $cat('mode-et-beaute', 'fashion-and-beauty'), $T('Lire', 'Read')],
            ['rocket', $T('Entreprendre', 'Business'), $T('Start-up, PME, financement : les idées qui marchent et ceux qui les portent.', 'Start-ups, SMEs, funding: the ideas that work and the people behind them.'), $cat('entreprendre', 'business'), $T('Lire', 'Read')],
            ['plane', $T('Voyages', 'Travel'), $T('Escapades, carnets de route et adresses, du littoral aux montagnes.', 'Getaways, travel diaries and addresses, from the coast to the mountains.'), $cat('voyages', 'travel'), $T('Lire', 'Read')],
            ['utensils', $T('Saveurs', 'Food'), $T('Chefs, recettes et produits du terroir revisités.', 'Chefs, recipes and local produce revisited.'), $cat('saveurs', 'food'), $T('Lire', 'Read')],
        ];
        $cta = ['title' => $T('Abonnez-vous au magazine', 'Subscribe to the magazine'), 'text' => $T('Le numéro papier chez vous chaque mois, l’accès à tous les articles en ligne et aux archives.', 'The print issue at home every month, plus access to every article online and the archives.'),
            'buttons' => [[$T('Je m’abonne', 'Subscribe'), $subscribe, 'accent'], [$T('Feuilleter le numéro', 'Browse the issue'), $issue, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Le magazine de la culture, de la création et de l’entrepreneuriat africains : portraits, tendances, voyages.', 'The magazine of African culture, creativity and entrepreneurship: profiles, trends, travel.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Le numéro de ce mois', 'This month’s issue'),
                    'title' => $T('Ceux qui créent l’Afrique de demain', 'The people creating tomorrow’s Africa'),
                    'text' => $T('Portraits, culture, mode, entrepreneuriat et voyages : un magazine qui prend le temps de raconter.', 'Profiles, culture, fashion, business and travel: a magazine that takes the time to tell stories.'),
                    'buttons' => [[$T('Lire le magazine', 'Read the magazine'), $articles], [$T('S’abonner', 'Subscribe'), $subscribe]],
                    'photo' => ['medias-magazine-accueil', 'sparkles', $T('Créatrice de mode dans son atelier', 'Fashion designer in her studio')],
                ]),
                B::posts($theme, ['eyebrow' => $T('À la une', 'Featured'), 'title' => $T('Les histoires du moment', 'Stories of the moment'), 'count' => 6, 'layout' => 'carousel', 'more' => $T('Lire l’article', 'Read the story'), 'ratio' => '4:3']),
                B::features($theme, ['eyebrow' => $T('Rubriques', 'Sections'), 'title' => $T('Six univers à explorer', 'Six worlds to explore'), 'items' => $rubriques, 'bg' => 'light']),
                B::split($theme, [
                    'eyebrow' => $T('Le magazine papier', 'The print magazine'),
                    'title' => $T('Le numéro du mois : la nouvelle génération', 'This month: the new generation'),
                    'html' => '<p>'.e($T('Cent vingt pages consacrées aux moins de 30 ans qui bousculent la musique, la mode, la tech et l’agriculture. En kiosque et en version numérique.', 'A hundred and twenty pages devoted to under-30s shaking up music, fashion, tech and farming. On newsstands and in digital form.')).'</p>',
                    'bullets' => [$T('12 portraits exclusifs', '12 exclusive profiles'), $T('Un cahier mode de 20 pages', 'A 20-page fashion section'), $T('Le guide des festivals de l’été', 'The summer festival guide')],
                    'buttons' => [[$T('Découvrir le numéro', 'Discover the issue'), $issue], [$T('S’abonner', 'Subscribe'), $subscribe, 'outline']],
                    'photo' => ['medias-magazine-numero', 'book-open', $T('Magazine ouvert sur une table', 'Open magazine on a table')],
                    'reverse' => true,
                ]),
                B::posts($theme, ['eyebrow' => $T('Derniers articles', 'Latest stories'), 'title' => $T('Fraîchement publiés', 'Freshly published'), 'count' => 3, 'more' => $T('Lire la suite', 'Read more')]),
                B::testimonials($theme, ['eyebrow' => $T('Lecteurs', 'Readers'), 'title' => $T('Ils ne manquent aucun numéro', 'They never miss an issue'), 'items' => [
                    [$T('Un magazine beau et exigeant, qui montre une Afrique créative et ambitieuse.', 'A beautiful, demanding magazine that shows a creative, ambitious Africa.'), '[Prénom Nom]', $T('Abonnée depuis 3 ans', 'Subscriber for 3 years')],
                    [$T('Les portraits d’entrepreneurs m’ont donné le courage de lancer mon activité.', 'The entrepreneur profiles gave me the courage to start my business.'), '[Prénom Nom]', $T('Créateur d’entreprise', 'Business founder')],
                    [$T('Je garde chaque numéro : c’est un objet autant qu’un magazine.', 'I keep every issue: it is a keepsake as much as a magazine.'), '[Prénom Nom]', $T('Lectrice', 'Reader')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'articles', 'slug' => 'articles', 'title' => 'Articles',
            'description' => $T('Tous les articles du magazine : portraits, culture, mode, entrepreneuriat, voyages et saveurs.', 'Every story from the magazine: profiles, culture, fashion, business, travel and food.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Articles', 'title' => $T('Le magazine en ligne', 'The magazine online'), 'intro' => $T('Nos histoires, nos rencontres et nos coups de cœur.', 'Our stories, encounters and favorites.')]),
                B::terms($theme, ['bg' => 'light']),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more'), 'ratio' => '4:3']),
                B::terms($theme, ['title' => $T('Thèmes', 'Topics'), 'taxonomy' => 'tag', 'layout' => 'cloud', 'count' => false, 'bg' => 'light']),
            ]];

        $pages[] = ['key' => 'sections', 'slug' => $u('rubriques', 'sections'), 'title' => $T('Rubriques', 'Sections'),
            'description' => $T('Portraits, culture, mode et beauté, entreprendre, voyages et saveurs.', 'Profiles, culture, fashion and beauty, business, travel and food.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Rubriques', 'Sections'), 'title' => $T('Explorez le magazine', 'Explore the magazine'), 'intro' => $T('Six rubriques, une même curiosité.', 'Six sections, one curiosity.')]),
                B::features($theme, ['items' => $rubriques]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('Nos séries photo', 'Our photo stories'), 'bg' => 'light', 'photos' => [
                    ['medias-magazine-galerie-1', 'camera', $T('Séance photo de mode en extérieur', 'Outdoor fashion shoot')],
                    ['medias-magazine-galerie-2', 'camera', $T('Artiste peintre dans son atelier', 'Painter in her studio')],
                    ['medias-magazine-galerie-3', 'camera', $T('Plat traditionnel revisité', 'Traditional dish, revisited')],
                    ['medias-magazine-galerie-4', 'camera', $T('Paysage de bord de mer', 'Seaside landscape')],
                    ['medias-magazine-galerie-5', 'camera', $T('Musicien en concert', 'Musician in concert')],
                    ['medias-magazine-galerie-6', 'camera', $T('Jeune entrepreneure dans sa boutique', 'Young entrepreneur in her shop')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'issue', 'slug' => $u('le-magazine', 'the-magazine'), 'title' => $T('Le magazine', 'The magazine'),
            'description' => $T('Le numéro du mois, son sommaire et les numéros précédents.', 'This month’s issue, its contents and past issues.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Le magazine', 'The magazine'), 'title' => $T('Le numéro du mois', 'This month’s issue'), 'intro' => $T('En kiosque, en librairie et en version numérique.', 'On newsstands, in bookshops and in digital form.')]),
                B::split($theme, [
                    'eyebrow' => $T('N° 48', 'No. 48'),
                    'title' => $T('La nouvelle génération', 'The new generation'),
                    'html' => '<p>'.e($T('Ils ont moins de 30 ans et réinventent la musique, la mode, la tech et l’agriculture. Ce numéro leur donne la parole, en 120 pages et 12 portraits exclusifs.', 'They are under 30 and reinventing music, fashion, tech and farming. This issue gives them the floor, in 120 pages and 12 exclusive profiles.')).'</p>',
                    'bullets' => [$T('120 pages, papier recyclé', '120 pages, recycled paper'), $T('Version numérique incluse pour les abonnés', 'Digital edition included for subscribers'), $T('En kiosque le 1er de chaque mois', 'On newsstands on the 1st of every month')],
                    'buttons' => [[$T('Recevoir ce numéro', 'Get this issue'), $subscribe]],
                    'photo' => ['medias-magazine-couverture', 'book-open', $T('Couverture du magazine', 'Magazine cover')],
                ]),
                B::features($theme, ['eyebrow' => $T('Au sommaire', 'In this issue'), 'title' => $T('Dans ce numéro', 'Inside this issue'), 'cols' => 4, 'bg' => 'light', 'items' => [
                    ['user-round', $T('Le grand portrait', 'The big profile'), $T('Une productrice qui fait danser tout le continent.', 'A producer who gets the whole continent dancing.')],
                    ['shirt', $T('Cahier mode', 'Fashion section'), $T('20 pages de créations de jeunes stylistes.', '20 pages of young designers’ work.')],
                    ['sprout', $T('Reportage', 'Report'), $T('Les jeunes agriculteurs qui misent sur le bio.', 'The young farmers betting on organic.')],
                    ['plane', $T('Évasion', 'Escape'), $T('Trois jours sur la route des lacs.', 'Three days on the lakes road.')],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('Archives', 'Archives'), 'title' => $T('Les numéros précédents', 'Past issues'), 'intro' => $T('Commandez les anciens numéros ou lisez-les en ligne avec votre abonnement.', 'Order back issues or read them online with your subscription.'), 'cols' => 4, 'photos' => [
                    ['medias-magazine-archive-1', 'book-open', $T('Numéro 47 : les femmes qui bâtissent', 'Issue 47: women who build')],
                    ['medias-magazine-archive-2', 'book-open', $T('Numéro 46 : spécial musique', 'Issue 46: music special')],
                    ['medias-magazine-archive-3', 'book-open', $T('Numéro 45 : villes de demain', 'Issue 45: cities of tomorrow')],
                    ['medias-magazine-archive-4', 'book-open', $T('Numéro 44 : la table africaine', 'Issue 44: the African table')],
                ]]),
            ]];

        $pages[] = ['key' => 'subscribe', 'slug' => $u('abonnement', 'subscribe'), 'title' => $T('Abonnement', 'Subscribe'),
            'description' => $T('Abonnez-vous au magazine : numérique, papier ou les deux. Offres et questions fréquentes.', 'Subscribe to the magazine: digital, print or both. Offers and FAQ.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Abonnement', 'Subscribe'), 'title' => $T('Choisissez votre formule', 'Choose your plan'), 'intro' => $T('Sans engagement pour le numérique ; livraison incluse pour le papier.', 'No commitment for digital; delivery included for print.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Numérique', 'Digital'), '3', '$', $T('/ mois', '/ month'), $T('Tous les articles en ligne et le magazine en PDF.', 'Every article online and the magazine as a PDF.'), [$T('Accès illimité au site', 'Unlimited website access'), $T('Le numéro en PDF', 'The issue as a PDF'), $T('Lettre d’information', 'Newsletter'), '-'.$T('Magazine papier', 'Print magazine')], $T('Je m’abonne', 'Subscribe')],
                    [$T('Duo', 'Duo'), '6', '$', $T('/ mois', '/ month'), $T('Le magazine papier chez vous et tout le numérique.', 'The print magazine at home and everything digital.'), [$T('Magazine papier livré', 'Print magazine delivered'), $T('Accès illimité au site', 'Unlimited website access'), $T('Archives complètes', 'Full archives'), $T('Invitations aux événements', 'Event invitations')], $T('Je m’abonne', 'Subscribe'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Papier', 'Print'), '45', '$', $T('/ an', '/ year'), $T('Douze numéros livrés à domicile ou au bureau.', 'Twelve issues delivered to your home or office.'), [$T('12 numéros par an', '12 issues a year'), $T('Livraison incluse', 'Delivery included'), $T('Numéros hors-série', 'Special editions'), '-'.$T('Accès au site', 'Website access')], $T('Je m’abonne', 'Subscribe')],
                ]]),
                B::pillars($theme, ['title' => $T('Pourquoi s’abonner ?', 'Why subscribe?'), 'bg' => 'light', 'items' => [
                    ['heart', $T('Soutenir un média indépendant', 'Support independent media'), $T('Vos abonnements financent nos reportages.', 'Your subscription funds our reporting.')],
                    ['book-open', $T('Lire sans limite', 'Read without limits'), $T('Tous les articles et douze ans d’archives.', 'Every story and twelve years of archives.')],
                    ['ticket', $T('Vivre le magazine', 'Live the magazine'), $T('Invitations à nos rencontres et lancements.', 'Invitations to our talks and launches.')],
                ]]),
                B::faq($theme, ['title' => $T('Questions fréquentes', 'Frequently asked questions'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'outline']], 'items' => [
                    [$T('Comment payer ?', 'How do I pay?'), $T('Par Mobile Money, carte bancaire ou virement. Écrivez-nous pour recevoir les coordonnées.', 'By Mobile Money, card or bank transfer. Write to us for the details.')],
                    [$T('Livrez-vous à l’étranger ?', 'Do you deliver abroad?'), $T('Oui, avec un supplément selon le pays. L’abonnement numérique est accessible partout.', 'Yes, with a surcharge depending on the country. The digital subscription works everywhere.')],
                    [$T('Puis-je offrir un abonnement ?', 'Can I give a subscription as a gift?'), $T('Oui : indiquez le nom et l’adresse de la personne, nous joignons une carte à votre nom.', 'Yes: give us the person’s name and address and we will include a card from you.')],
                    [$T('Comment résilier ?', 'How do I cancel?'), $T('Le numérique est sans engagement : un simple message suffit.', 'Digital has no commitment: a simple message is enough.')],
                ]]),
            ]];

        $pages[] = ['key' => 'team', 'slug' => $u('equipe', 'team'), 'title' => $T('L’équipe', 'Team'),
            'description' => $T('La rédaction du magazine, ses photographes et ses contributeurs.', 'The magazine’s editors, photographers and contributors.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’équipe', 'Team'), 'title' => $T('Ceux qui font le magazine', 'The people behind the magazine'), 'intro' => $T('Une rédaction resserrée et un réseau de contributeurs sur tout le continent.', 'A small newsroom and a network of contributors across the continent.')]),
                B::team($theme, ['items' => [
                    ['[Prénom Nom]', $T('Directrice de la publication', 'Publisher'), 'medias-magazine-equipe-1'],
                    ['[Prénom Nom]', $T('Rédacteur en chef', 'Editor-in-chief'), 'medias-magazine-equipe-2'],
                    ['[Prénom Nom]', $T('Directrice artistique', 'Art director'), 'medias-magazine-equipe-3'],
                    ['[Prénom Nom]', $T('Journaliste culture', 'Culture writer'), 'medias-magazine-equipe-4'],
                    ['[Prénom Nom]', $T('Journaliste mode', 'Fashion editor'), 'medias-magazine-equipe-5'],
                    ['[Prénom Nom]', $T('Journaliste économie', 'Business writer'), 'medias-magazine-equipe-6'],
                    ['[Prénom Nom]', $T('Photographe', 'Photographer'), 'medias-magazine-equipe-7'],
                    ['[Prénom Nom]', $T('Secrétaire de rédaction', 'Copy editor'), 'medias-magazine-equipe-8'],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Contributeurs', 'Contributors'),
                    'title' => $T('Écrivez pour nous', 'Write for us'),
                    'html' => '<p>'.e($T('Journalistes, photographes, illustrateurs : nous publions chaque mois des contributeurs de tout le continent. Envoyez-nous une proposition de sujet et trois exemples de vos travaux.', 'Journalists, photographers, illustrators: every month we publish contributors from across the continent. Send us a story idea and three samples of your work.')).'</p>',
                    'bullets' => [$T('Sujets rémunérés à la pige', 'Paid freelance commissions'), $T('Accompagnement éditorial', 'Editorial support'), $T('Réponse sous deux semaines', 'Reply within two weeks')],
                    'buttons' => [[$T('Proposer un sujet', 'Pitch a story'), $contact, 'outline']],
                    'photo' => ['medias-magazine-redaction', 'pen-tool', $T('Réunion de rédaction autour de maquettes', 'Editorial meeting around page layouts')],
                    'bg' => 'light',
                ]),
            ]];

        $pages[] = ['key' => 'advertise', 'slug' => $u('publicite', 'advertise'), 'title' => $T('Publicité', 'Advertise'),
            'description' => $T('Annoncez dans le magazine : pages, partenariats éditoriaux et campagnes numériques. Lectorat et tarifs.', 'Advertise in the magazine: pages, editorial partnerships and digital campaigns. Readership and rates.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Annonceurs', 'Advertisers'), 'title' => $T('Une audience qui aime les belles marques', 'An audience that loves great brands'), 'intro' => $T('Un lectorat jeune, urbain et influent, sur papier et en ligne.', 'A young, urban and influential readership, in print and online.')]),
                B::stats($theme, ['items' => [
                    [35, ' k', $T('exemplaires par mois', 'copies a month')],
                    [180, ' k', $T('lecteurs en ligne par mois', 'monthly online readers')],
                    [90, ' k', $T('abonnés Instagram', 'Instagram followers')],
                    [12, '', $T('pays de diffusion', 'countries of distribution')],
                ], 'bg' => 'primary']),
                B::chart($theme, [
                    'eyebrow' => $T('Lectorat', 'Readership'),
                    'title' => $T('Qui lit le magazine ?', 'Who reads the magazine?'),
                    'html' => '<p>'.e($T('Répartition de nos lecteurs par âge (enquête lecteurs, [année]). Un lectorat jeune et actif, à 58 % féminin.', 'Our readers by age group (reader survey, [year]). A young, active readership, 58% women.')).'</p>',
                    'bullets' => [$T('72 % de cadres et entrepreneurs', '72% professionals and business owners'), $T('Un numéro lu par 4 personnes en moyenne', 'Each issue read by 4 people on average'), $T('Conservé plus de 6 mois', 'Kept for over 6 months')],
                    'chart' => ['kind' => 'pie', 'title' => $T('Lecteurs par âge', 'Readers by age'), 'suffix' => ' %', 'rows' => [
                        [$T('18-24 ans', '18–24'), 18], [$T('25-34 ans', '25–34'), 41], [$T('35-44 ans', '35–44'), 27], [$T('45 ans et plus', '45 and over'), 14],
                    ]],
                    'reverse' => true,
                ]),
                B::pricing($theme, ['eyebrow' => $T('Formats', 'Formats'), 'title' => $T('Nos offres', 'Our offers'), 'intro' => $T('Tarifs indicatifs hors taxes par parution.', 'Indicative rates before tax, per issue.'), 'url' => $contact, 'bg' => 'light', 'items' => [
                    [$T('Pleine page', 'Full page'), '900', '$', $T('/ numéro', '/ issue'), $T('Une page en quadrichromie dans le magazine papier.', 'A full-color page in the print magazine.'), [$T('Emplacement au choix selon disponibilité', 'Placement of your choice, subject to availability'), $T('Version PDF incluse', 'PDF edition included'), $T('Bon à tirer validé avec vous', 'Proof approved with you'), '-'.$T('Contenu rédigé', 'Written content')], $T('Réserver', 'Book')],
                    [$T('Partenariat éditorial', 'Editorial partnership'), '1800', '$', $T('/ opération', '/ campaign'), $T('Un cahier de 4 pages et sa déclinaison en ligne.', 'A 4-page section and its online version.'), [$T('Conception par notre studio', 'Designed by our studio'), $T('Mention « en partenariat avec »', '“In partnership with” label'), $T('Relais sur nos réseaux', 'Shared on our networks'), $T('Bilan de lecture', 'Readership report')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus complet', 'Most complete')],
                    [$T('Campagne numérique', 'Digital campaign'), '500', '$', $T('/ mois', '/ month'), $T('Bannières, lettre d’information et Instagram.', 'Banners, newsletter and Instagram.'), [$T('Bannières sur le site', 'Website banners'), $T('2 lettres d’information', '2 newsletters'), $T('1 publication sponsorisée', '1 sponsored post'), $T('Statistiques détaillées', 'Detailed statistics')], $T('Réserver', 'Book')],
                ]]),
                B::cta($theme, ['title' => $T('Recevez notre kit média', 'Get our media kit'), 'text' => $T('Calendrier éditorial, lectorat détaillé et tarifs : nous vous répondons sous 24 heures.', 'Editorial calendar, detailed readership and rates: we reply within 24 hours.'), 'buttons' => [[$T('Demander le kit média', 'Request the media kit'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez la rédaction, le service abonnements ou la publicité.', 'Contact the editors, subscriptions or advertising.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Écrivez-nous', 'Write to us'), 'intro' => $T('Une idée de sujet, une question sur votre abonnement, un projet de partenariat ?', 'A story idea, a question about your subscription, a partnership project?')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['pen-tool', $T('Rédaction', 'Editorial'), $T('Propositions de sujets, communiqués et invitations presse.', 'Story ideas, press releases and press invitations.')],
                    ['book-open', $T('Abonnements', 'Subscriptions'), $T('Livraison, changement d’adresse, offre cadeau.', 'Delivery, change of address, gift offers.'), $subscribe, $T('Voir les offres', 'See the offers')],
                    ['megaphone', $T('Publicité', 'Advertising'), $T('Pages, partenariats et campagnes numériques.', 'Pages, partnerships and digital campaigns.'), $advertise, $T('Voir les formats', 'See the formats')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Le magazine', 'The magazine'), 'intro' => $T('Rédaction ouverte du lundi au vendredi, de 9 h à 18 h.', 'Office open Monday to Friday, 9 am to 6 pm.'),
                    'items' => [
                        ['map-pin', $T('Rue des Almadies, Dakar, Sénégal', 'Rue des Almadies, Dakar, Senegal')],
                        ['phone', '+221 00 000 00 00', 'tel:+221000000000'],
                        ['mail', 'redaction@exemple.org', 'mailto:redaction@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 9 h – 18 h', 'Monday – Friday, 9 am – 6 pm')],
                    ],
                    'social' => ['instagram', 'facebook', 'tiktok', 'pinterest'],
                    'subject' => true,
                    'map' => 'Dakar, Almadies',
                ]),
            ]];

        $blog = [
            'base' => 'articles',
            'title' => $T('Le magazine', 'The magazine'),
            'layout' => ['share' => true, 'terms' => true],
            'interval' => 4,
            'categories' => ['portraits' => $T('Portraits', 'Profiles'), 'culture' => 'Culture', 'mode' => $T('Mode et beauté', 'Fashion and beauty'), 'entreprendre' => $T('Entreprendre', 'Business'), 'voyages' => $T('Voyages', 'Travel'), 'saveurs' => $T('Saveurs', 'Food')],
            'posts' => [
                ['key' => 'styliste', 'slug' => $u('la-styliste-qui-reinvente-le-wax', 'the-designer-reinventing-wax'), 'category' => 'mode', 'tags' => ['Wax', $T('Créateurs', 'Designers')],
                    'title' => $T('La styliste qui réinvente le wax', 'The designer reinventing wax prints'),
                    'excerpt' => $T('Dans son atelier, [Prénom Nom] marie tissus traditionnels et coupes contemporaines. Ses collections s’exportent.', 'In her studio, [First Last] blends traditional fabrics with contemporary cuts. Her collections now sell abroad.'),
                    'photo' => ['medias-magazine-accueil', 'shirt', $T('Créatrice de mode dans son atelier', 'Fashion designer in her studio')],
                    'heading' => $T('Un atelier qui forme', 'A studio that trains'),
                    'quote' => $T('« Le wax raconte des histoires : je lui en fais raconter de nouvelles. »', '“Wax prints tell stories: I make them tell new ones.”'),
                    'body' => [
                        $T('Rien ne la destinait à la mode : diplômée en comptabilité, elle a appris la couture auprès de sa grand-mère. Dix ans plus tard, sa marque habille des clientes à Dakar, Paris et Montréal.', 'Nothing destined her for fashion: a trained accountant, she learned to sew from her grandmother. Ten years later, her label dresses clients in Dakar, Paris and Montreal.'),
                        $T('Son atelier emploie aujourd’hui douze couturières, qu’elle forme elle-même. Chaque pièce est coupée et cousue sur place, en petites séries.', 'Her studio now employs twelve seamstresses, whom she trains herself. Every piece is cut and sewn on site, in small runs.'),
                        $T('Sa prochaine collection, présentée le mois prochain, sera à découvrir dans notre cahier mode.', 'Her next collection, shown next month, will feature in our fashion section.'),
                    ]],
                ['key' => 'productrice', 'slug' => $u('portrait-productrice-musique', 'profile-music-producer'), 'category' => 'portraits', 'tags' => [$T('Musique', 'Music'), $T('Nouvelle génération', 'New generation')],
                    'title' => $T('La productrice qui fait danser le continent', 'The producer who makes the continent dance'),
                    'excerpt' => $T('Depuis son studio, elle signe les tubes de l’année. Rencontre avec une perfectionniste.', 'From her studio, she produces the hits of the year. Meet a perfectionist.'),
                    'photo' => ['medias-magazine-actu-musique', 'music', $T('Productrice de musique devant sa console', 'Music producer at her mixing console')],
                    'body' => [
                        $T('Elle a commencé avec un ordinateur d’occasion et un casque emprunté. Aujourd’hui, ses productions cumulent des millions d’écoutes.', 'She started with a second-hand computer and borrowed headphones. Today her productions have millions of streams.'),
                        $T('Sa méthode : partir des rythmes traditionnels de sa région, enregistrés sur place, et les marier aux sons électroniques.', 'Her method: start from her region’s traditional rhythms, recorded on location, and blend them with electronic sounds.'),
                        $T('Elle ouvre cette année une école pour former les jeunes producteurs, et en particulier les jeunes femmes.', 'This year she is opening a school to train young producers, especially young women.'),
                    ]],
                ['key' => 'agriculture', 'slug' => $u('jeunes-agriculteurs-bio', 'young-organic-farmers'), 'category' => 'entreprendre', 'tags' => [$T('Agriculture', 'Farming'), $T('Nouvelle génération', 'New generation')],
                    'title' => $T('Ces jeunes qui misent sur l’agriculture bio', 'The young people betting on organic farming'),
                    'excerpt' => $T('Diplômés en ville, ils sont retournés à la terre. Leurs fermes livrent les restaurants de la capitale.', 'City graduates, they went back to the land. Their farms supply the capital’s restaurants.'),
                    'photo' => ['medias-magazine-actu-agriculture', 'sprout', $T('Jeune agricultrice dans un champ', 'Young woman farmer in a field')],
                    'heading' => $T('Un modèle qui essaime', 'A model that spreads'),
                    'body' => [
                        $T('Ils sont ingénieurs, commerciaux ou informaticiens, et ont choisi de cultiver la terre. Leur pari : une agriculture sans produits chimiques, vendue en circuit court.', 'They are engineers, salespeople or IT specialists who chose to farm. Their bet: chemical-free agriculture, sold directly.'),
                        $T('Paniers livrés à domicile, contrats avec les restaurants, réseaux sociaux : ils ont réinventé la vente des légumes.', 'Home-delivered baskets, contracts with restaurants, social media: they have reinvented how vegetables are sold.'),
                        $T('Plusieurs coopératives se sont créées sur leur modèle. Notre reportage complet est à lire dans le numéro du mois.', 'Several cooperatives have formed on their model. Our full report is in this month’s issue.'),
                    ]],
                ['key' => 'lacs', 'slug' => $u('trois-jours-sur-la-route-des-lacs', 'three-days-on-the-lakes-road'), 'category' => 'voyages', 'tags' => [$T('Évasion', 'Getaway'), $T('Nature', 'Nature')],
                    'title' => $T('Trois jours sur la route des lacs', 'Three days on the lakes road'),
                    'excerpt' => $T('Plages de sable volcanique, villages de pêcheurs et couchers de soleil : notre itinéraire.', 'Volcanic sand beaches, fishing villages and sunsets: our itinerary.'),
                    'photo' => ['medias-magazine-actu-voyage', 'plane', $T('Lac bordé de collines au coucher du soleil', 'Lake surrounded by hills at sunset')],
                    'body' => [
                        $T('Premier jour : départ à l’aube, cap sur les rives du lac. On s’arrête au marché aux poissons du village, avant un déjeuner les pieds dans l’eau.', 'Day one: leave at dawn, heading for the lakeshore. Stop at the village fish market before lunch with your feet in the water.'),
                        $T('Deuxième jour : randonnée dans les collines, rencontre avec des producteurs de café et nuit dans un écolodge.', 'Day two: a hike in the hills, meeting coffee growers and a night in an eco-lodge.'),
                        $T('Troisième jour : traversée en bateau et retour par la route des plantations. Nos adresses sont à retrouver dans le magazine.', 'Day three: a boat crossing and the drive back along the plantation road. Our addresses are in the magazine.'),
                    ]],
                ['key' => 'cheffe', 'slug' => $u('la-cheffe-qui-revisite-le-terroir', 'the-chef-revisiting-local-produce'), 'category' => 'saveurs', 'tags' => [$T('Gastronomie', 'Gastronomy')],
                    'title' => $T('La cheffe qui revisite le terroir', 'The chef revisiting local produce'),
                    'excerpt' => $T('Fonio, bissap, poisson fumé : une cuisine d’ici, servie comme dans les grandes tables.', 'Fonio, hibiscus, smoked fish: local cuisine served like fine dining.'),
                    'photo' => ['medias-magazine-actu-cuisine', 'chef-hat', $T('Plat raffiné dressé à l’assiette', 'Refined plated dish')],
                    'body' => [
                        $T('Dans son restaurant de vingt couverts, la cheffe ne travaille que des produits locaux, achetés chaque matin au marché.', 'In her twenty-seat restaurant, the chef uses only local produce, bought at the market every morning.'),
                        $T('Sa carte change chaque semaine, au rythme des saisons et des arrivages des pêcheurs.', 'Her menu changes every week, following the seasons and the fishermen’s catch.'),
                        $T('Elle nous a confié la recette de son fonio aux légumes grillés, à retrouver dans le numéro du mois.', 'She shared her fonio with grilled vegetables recipe with us, in this month’s issue.'),
                    ]],
                ['key' => 'cinema', 'slug' => $u('le-cinema-qui-vient', 'the-cinema-to-come'), 'category' => 'culture', 'tags' => [$T('Cinéma', 'Film'), $T('Nouvelle génération', 'New generation')],
                    'title' => $T('Le cinéma qui vient', 'The cinema to come'),
                    'excerpt' => $T('Tournés au téléphone ou en équipe réduite, les films d’une nouvelle génération de cinéastes s’imposent dans les festivals.', 'Shot on phones or with small crews, a new generation of filmmakers is winning at festivals.'),
                    'photo' => ['medias-magazine-actu-cinema', 'film', $T('Tournage de film en extérieur', 'Outdoor film shoot')],
                    'body' => [
                        $T('Ils ont moins de 30 ans et tournent avec des budgets minuscules. Leurs courts métrages circulent pourtant dans les festivals du monde entier.', 'They are under 30 and shoot on tiny budgets. Yet their short films travel to festivals around the world.'),
                        $T('Nous avons rencontré cinq jeunes cinéastes qui racontent leur ville, leur famille et leurs rêves, avec humour et sans détour.', 'We met five young filmmakers who tell the story of their city, their families and their dreams, with humor and candor.'),
                        $T('Leurs films seront projetés lors de notre soirée cinéma, le [date]. Entrée libre pour les abonnés.', 'Their films will be screened at our cinema night on [date]. Free for subscribers.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => 'Articles', 'url' => $articles],
                    ['label' => $T('Rubriques', 'Sections'), 'url' => $sections],
                    ['label' => $T('Le magazine', 'The magazine'), 'url' => $issue],
                    ['label' => $T('L’équipe', 'Team'), 'url' => $team],
                    ['label' => $T('Publicité', 'Advertise'), 'url' => $advertise],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('S’abonner', 'Subscribe'),
                'cta_url' => $subscribe,
                'footer_text' => $T('Magazine indépendant de la culture, de la création et de l’entrepreneuriat africains.', 'Independent magazine of African culture, creativity and entrepreneurship.'),
                'footer_links' => [
                    ['label' => $T('S’abonner', 'Subscribe'), 'url' => $subscribe],
                    ['label' => $T('Le magazine', 'The magazine'), 'url' => $issue],
                    ['label' => $T('Publicité', 'Advertise'), 'url' => $advertise],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
