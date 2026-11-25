<?php

/*
 * Pack « Médias et presse » — journal d'information en ligne : 8 pages, en français
 * et en anglais. La une (diaporama des articles phares et dernières infos), un flash
 * info qui défile, des rubriques reliées aux catégories du blog, des enquêtes, la
 * rédaction, la publicité (audience en graphique) et le contact.
 * Les textes et les articles sont des exemples à personnaliser : aucun fait réel.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Médias et presse', 'en' => 'Media and press'],
        'icon' => 'newspaper',
        'keywords' => ['média', 'medias', 'presse', 'journal', 'quotidien', 'hebdomadaire', 'magazine', 'radio', 'télévision', 'tv', 'chaîne', 'podcast', 'journaliste', 'rédaction', 'information', 'actualité', 'agence de presse', 'media', 'press', 'newspaper', 'news', 'television', 'broadcaster', 'journalist', 'newsroom'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $news = $u('/actualites', '/news');
        $sections = $u('/rubriques', '/sections');
        $investigations = $u('/enquetes', '/investigations');
        $newsroom = $u('/redaction', '/newsroom');
        $about = $u('/qui-sommes-nous', '/about');
        $advertise = $u('/publicite', '/advertise');
        $contact = '/contact';
        // Archives des rubriques (catégories du blog) et articles d'exemple.
        $cat = fn (string $fr, string $en) => $news.$u('/categorie/', '/category/').$T($fr, $en);
        $post = fn (string $fr, string $en) => $news.'/'.$T($fr, $en);

        $rubriques = [
            ['landmark', $T('Politique', 'Politics'), $T('Institutions, élections, décisions publiques : ce qui se décide et ce que cela change pour vous.', 'Institutions, elections, public decisions: what is decided and what it changes for you.'), $cat('politique', 'politics'), $T('Lire la rubrique', 'Read the section')],
            ['trending-up', $T('Économie', 'Economy'), $T('Prix, emploi, entreprises et marchés : l’économie expliquée simplement, avec des chiffres vérifiés.', 'Prices, jobs, businesses and markets: the economy explained simply, with checked figures.'), $cat('economie', 'economy'), $T('Lire la rubrique', 'Read the section')],
            ['users', $T('Société', 'Society'), $T('Éducation, santé, quartiers et familles : la vie quotidienne racontée au plus près des habitants.', 'Education, health, neighborhoods and families: daily life told up close.'), $cat('societe', 'society'), $T('Lire la rubrique', 'Read the section')],
            ['drama', $T('Culture', 'Culture'), $T('Musique, cinéma, livres et patrimoine : les artistes et les événements qui font vibrer la ville.', 'Music, film, books and heritage: the artists and events that make the city vibrate.'), $cat('culture', 'culture'), $T('Lire la rubrique', 'Read the section')],
            ['trophy', $T('Sport', 'Sports'), $T('Football, basket, athlétisme : résultats, portraits et coulisses du sport local et national.', 'Football, basketball, athletics: results, profiles and behind the scenes of local and national sport.'), $cat('sport', 'sports'), $T('Lire la rubrique', 'Read the section')],
            ['search', $T('Enquêtes', 'Investigations'), $T('Des mois de travail pour révéler ce qui ne se voit pas : nos enquêtes au long cours.', 'Months of work to reveal what cannot be seen: our in-depth investigations.'), $investigations, $T('Voir les enquêtes', 'See investigations')],
        ];
        $audience = ['title' => $T('Une audience fidèle et engagée', 'A loyal and engaged audience'), 'items' => [
            [250, ' k', $T('lecteurs par mois', 'monthly readers')],
            [48, ' k', $T('abonnés à la lettre du matin', 'morning newsletter subscribers')],
            [120, ' k', $T('abonnés sur les réseaux', 'social media followers')],
            [12, '', $T('ans d’information', 'years of reporting')],
        ]];
        $cta = ['title' => $T('L’essentiel de l’actualité, chaque matin', 'The news that matters, every morning'), 'text' => $T('Recevez notre lettre gratuite à 7 h : les informations du jour, vérifiées et expliquées en cinq minutes de lecture.', 'Get our free newsletter at 7 am: the day’s news, checked and explained in a five-minute read.'),
            'buttons' => [[$T('Je m’abonne à la lettre', 'Subscribe to the newsletter'), $contact, 'accent'], [$T('Envoyer une information', 'Send us a tip'), $contact, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('L’actualité locale et nationale vérifiée : politique, économie, société, culture, sport et enquêtes.', 'Checked local and national news: politics, economy, society, culture, sports and investigations.'),
            'sections' => [
                B::ticker($theme, ['label' => $T('Flash info', 'Breaking'), 'items' => [
                    [$T('Le marché central rouvre ses portes après six mois de travaux', 'Central market reopens after six months of works'), $post('marche-central-rouvre', 'central-market-reopens')],
                    [$T('Budget communal : les grandes lignes votées hier soir', 'City budget: main lines voted last night'), $post('budget-communal-ce-qui-change', 'city-budget-what-changes')],
                    [$T('Football : les cadets qualifiés pour la finale régionale', 'Football: under-17s reach the regional final'), $post('cadets-qualifies-finale', 'under-17-reach-regional-final')],
                    [$T('Météo : fortes pluies attendues ce week-end dans la région', 'Weather: heavy rain expected in the region this weekend')],
                    [$T('Festival des musiques urbaines : la programmation dévoilée', 'Urban music festival: line-up revealed'), $post('festival-musiques-urbaines', 'urban-music-festival-returns')],
                ]]),
                B::headlines($theme, [
                    'side' => $T('Dernières infos', 'Latest news'), 'read' => $T('Lire l’article', 'Read the story'), 'count' => 3,
                    'button' => [$T('Toute l’actualité', 'All the news'), $news],
                    'slides' => [
                        ['medias-journal-accueil', 'newspaper', $T('Enquête : où va l’eau du réseau public ?', 'Investigation: where does the public network’s water go?'), $T('Fuites, branchements illégaux, factures impayées : trois mois d’enquête dans les quartiers.', 'Leaks, illegal connections, unpaid bills: a three-month investigation across neighborhoods.'), $post('enquete-eau-reseau-public', 'investigation-public-water-network'), $T('Enquête', 'Investigation')],
                        ['medias-journal-une-2', 'store', $T('Le marché central rouvre après six mois de travaux', 'Central market reopens after six months of works'), $T('Nouvelles allées, toiture refaite, 1 200 commerçants réinstallés : visite guidée.', 'New aisles, a new roof, 1,200 traders back in place: a guided tour.'), $post('marche-central-rouvre', 'central-market-reopens'), $T('Économie', 'Economy')],
                        ['medias-journal-une-3', 'music', $T('Le festival des musiques urbaines revient en juillet', 'The urban music festival returns in July'), $T('Trois jours de concerts gratuits et une scène ouverte aux jeunes talents.', 'Three days of free concerts and an open stage for young talent.'), $post('festival-musiques-urbaines', 'urban-music-festival-returns'), 'Culture'],
                    ],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos rubriques', 'Our sections'), 'title' => $T('Toute l’actualité, rubrique par rubrique', 'All the news, section by section'), 'intro' => $T('Une rédaction présente sur le terrain chaque jour, des articles vérifiés avant publication.', 'A newsroom on the ground every day, stories checked before publication.'), 'items' => $rubriques, 'bg' => 'light']),
                B::posts($theme, ['eyebrow' => $T('À ne pas manquer', 'Don’t miss'), 'title' => $T('Les articles les plus lus', 'Most read stories'), 'count' => 6, 'layout' => 'carousel', 'excerpt' => false, 'more' => $T('Lire', 'Read')]),
                B::split($theme, [
                    'eyebrow' => $T('Enquêtes', 'Investigations'),
                    'title' => $T('Le journalisme qui prend le temps', 'Journalism that takes its time'),
                    'html' => '<p>'.e($T('Chaque trimestre, notre cellule d’enquête consacre des semaines à un sujet qui touche votre quotidien. Documents, témoignages, chiffres : tout est vérifié, et chaque personne mise en cause peut répondre.', 'Every quarter, our investigations team spends weeks on a subject that affects your daily life. Documents, testimonies, figures: everything is checked, and everyone involved can respond.')).'</p>',
                    'bullets' => [$T('Sources protégées', 'Protected sources'), $T('Documents publiés quand c’est possible', 'Documents published whenever possible'), $T('Droit de réponse systématique', 'Right of reply, always')],
                    'buttons' => [[$T('Lire nos enquêtes', 'Read our investigations'), $investigations]],
                    'photo' => ['medias-journal-enquete', 'search', $T('Journaliste examinant des documents', 'Journalist examining documents')],
                    'reverse' => true,
                ]),
                B::stats($theme, $audience),
                B::posts($theme, ['eyebrow' => $T('En continu', 'Live updates'), 'title' => $T('Les dernières nouvelles', 'The latest news'), 'count' => 6, 'more' => $T('Lire la suite', 'Read more'), 'bg' => 'light']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites', 'news'), 'title' => $T('Actualités', 'News'),
            'description' => $T('Tous nos articles : politique, économie, société, culture, sport et enquêtes.', 'All our stories: politics, economy, society, culture, sports and investigations.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Toute l’actualité', 'All the news'), 'intro' => $T('Des informations vérifiées, publiées chaque jour par notre rédaction.', 'Checked news, published every day by our newsroom.')]),
                B::terms($theme, ['bg' => 'light']),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::terms($theme, ['title' => $T('Sujets du moment', 'Trending topics'), 'taxonomy' => 'tag', 'layout' => 'cloud', 'count' => false, 'bg' => 'light']),
            ]];

        $pages[] = ['key' => 'sections', 'slug' => $u('rubriques', 'sections'), 'title' => $T('Rubriques', 'Sections'),
            'description' => $T('Politique, économie, société, culture, sport et enquêtes : retrouvez nos rubriques et nos dossiers.', 'Politics, economy, society, culture, sports and investigations: find our sections and special reports.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Rubriques', 'Sections'), 'title' => $T('Choisissez vos sujets', 'Pick your topics'), 'intro' => $T('Six rubriques, une même exigence : des faits vérifiés et expliqués.', 'Six sections, one standard: facts checked and explained.')]),
                B::features($theme, ['items' => $rubriques]),
                B::cards($theme, ['eyebrow' => $T('Dossiers', 'Special reports'), 'title' => $T('Nos dossiers spéciaux', 'Our special reports'), 'intro' => $T('Pour comprendre en profondeur les grands sujets de l’année.', 'To understand the big stories of the year in depth.'), 'more' => $T('Lire le dossier', 'Read the report'), 'bg' => 'light', 'items' => [
                    ['medias-journal-dossier-1', 'vote', $T('Élections : le guide complet', 'Elections: the complete guide'), $T('Calendrier, candidats, programmes et règles du vote expliqués pas à pas.', 'Calendar, candidates, platforms and voting rules explained step by step.'), $investigations],
                    ['medias-journal-dossier-2', 'banknote', $T('La vie chère, au marché', 'The cost of living, at the market'), $T('Nous suivons chaque mois le prix de dix produits de base.', 'Every month we track the price of ten staple products.'), $investigations],
                    ['medias-journal-dossier-3', 'leaf', $T('Climat : la ville face aux pluies', 'Climate: the city and the rains'), $T('Inondations, érosion, drainage : les solutions qui fonctionnent.', 'Floods, erosion, drainage: the solutions that work.'), $investigations],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'investigations', 'slug' => $u('enquetes', 'investigations'), 'title' => $T('Enquêtes', 'Investigations'),
            'description' => $T('Nos enquêtes au long cours, notre méthode et comment nous transmettre une information en toute confidentialité.', 'Our in-depth investigations, our method and how to send us information confidentially.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Enquêtes', 'Investigations'), 'title' => $T('Révéler ce qui ne se voit pas', 'Revealing what cannot be seen'), 'intro' => $T('Des semaines de travail, des faits documentés, une seule boussole : l’intérêt public.', 'Weeks of work, documented facts, one compass: the public interest.')]),
                B::cards($theme, ['more' => $T('Lire l’enquête', 'Read the investigation'), 'items' => [
                    ['medias-journal-enquete-1', 'droplet', $T('Où va l’eau du réseau public ?', 'Where does the public network’s water go?'), $T('Trois mois dans les quartiers pour comprendre les coupures à répétition.', 'Three months in the neighborhoods to understand repeated cuts.'), $post('enquete-eau-reseau-public', 'investigation-public-water-network')],
                    ['medias-journal-enquete-2', 'truck', $T('Le prix caché du transport', 'The hidden price of transport'), $T('Des barrières routières aux tarifs des taxis-motos : qui paie quoi ?', 'From roadblocks to motorbike taxi fares: who pays what?')],
                    ['medias-journal-enquete-3', 'school', $T('Écoles : les frais qui ne disent pas leur nom', 'Schools: fees by another name'), $T('Nous avons comparé les frais demandés aux parents dans 40 écoles.', 'We compared the fees charged to parents in 40 schools.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Notre méthode', 'Our method'), 'title' => $T('Comment nous enquêtons', 'How we investigate'), 'bg' => 'light', 'items' => [
                    [$T('Signalement', 'Tip-off'), $T('Une information arrive : d’un lecteur, d’un document, d’un constat sur le terrain.', 'Information comes in: from a reader, a document, an observation in the field.')],
                    [$T('Vérification', 'Verification'), $T('Chaque fait est recoupé auprès d’au moins deux sources indépendantes.', 'Every fact is cross-checked with at least two independent sources.')],
                    [$T('Droit de réponse', 'Right of reply'), $T('Les personnes mises en cause sont contactées avant publication.', 'People involved are contacted before publication.')],
                    [$T('Publication', 'Publication'), $T('Article, documents et suivi : l’enquête continue après sa parution.', 'Story, documents and follow-up: the investigation continues after it runs.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Vous avez une information ?', 'Do you have a tip?'),
                    'title' => $T('Écrivez-nous en toute confidentialité', 'Write to us confidentially'),
                    'html' => '<p>'.e($T('Un document, un témoignage, une situation anormale ? Contactez la cellule d’enquête. Nous protégeons nos sources : votre identité n’est jamais révélée sans votre accord.', 'A document, a testimony, something wrong? Contact the investigations team. We protect our sources: your identity is never revealed without your consent.')).'</p>',
                    'bullets' => [$T('Messagerie chiffrée sur demande', 'Encrypted messaging on request'), $T('Rendez-vous discret possible', 'Discreet meetings possible'), $T('Aucune information publiée sans vérification', 'Nothing published without verification')],
                    'buttons' => [[$T('Contacter la cellule d’enquête', 'Contact the investigations team'), $contact]],
                    'photo' => ['medias-journal-source', 'lock', $T('Rendez-vous discret entre une journaliste et une source', 'Discreet meeting between a journalist and a source')],
                ]),
                B::quote($theme, ['text' => $T('Notre travail n’est pas de plaire, mais d’informer : avec exactitude, équité et indépendance.', 'Our job is not to please but to inform: accurately, fairly and independently.'), 'source' => $T('— Charte de la rédaction', '— Newsroom charter'), 'bg' => 'light']),
            ]];

        $pages[] = ['key' => 'newsroom', 'slug' => $u('redaction', 'newsroom'), 'title' => $T('La rédaction', 'Newsroom'),
            'description' => $T('Les journalistes, photographes et éditeurs qui fabriquent le journal chaque jour.', 'The journalists, photographers and editors who make the paper every day.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('La rédaction', 'Newsroom'), 'title' => $T('Les visages de l’information', 'The faces behind the news'), 'intro' => $T('Une équipe de journalistes professionnels, de correspondants et de photographes.', 'A team of professional journalists, correspondents and photographers.')]),
                B::team($theme, ['items' => [
                    ['[Prénom Nom]', $T('Directeur de la publication', 'Publisher'), 'medias-journal-equipe-1'],
                    ['[Prénom Nom]', $T('Rédactrice en chef', 'Editor-in-chief'), 'medias-journal-equipe-2'],
                    ['[Prénom Nom]', $T('Chef d’édition', 'Managing editor'), 'medias-journal-equipe-3'],
                    ['[Prénom Nom]', $T('Journaliste politique', 'Political reporter'), 'medias-journal-equipe-4'],
                    ['[Prénom Nom]', $T('Journaliste économie', 'Business reporter'), 'medias-journal-equipe-5'],
                    ['[Prénom Nom]', $T('Reporter société', 'Society reporter'), 'medias-journal-equipe-6'],
                    ['[Prénom Nom]', $T('Journaliste sport', 'Sports reporter'), 'medias-journal-equipe-7'],
                    ['[Prénom Nom]', $T('Photographe', 'Photographer'), 'medias-journal-equipe-8'],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Ce que nous promettons à nos lecteurs', 'What we promise our readers'), 'bg' => 'light', 'items' => [
                    ['shield-check', $T('Vérifier', 'Verify'), $T('Aucune information publiée sans avoir été recoupée.', 'Nothing is published without being cross-checked.')],
                    ['scale', $T('Équilibrer', 'Balance'), $T('Toutes les parties concernées peuvent s’exprimer.', 'Every party involved gets to speak.')],
                    ['pencil', $T('Corriger', 'Correct'), $T('Une erreur ? Nous la corrigeons publiquement, en le signalant.', 'A mistake? We correct it publicly and say so.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Rejoindre la rédaction', 'Join the newsroom'),
                    'title' => $T('Correspondants, stagiaires, pigistes', 'Correspondents, interns, freelancers'),
                    'html' => '<p>'.e($T('Nous cherchons régulièrement des correspondants dans les quartiers et les villes de l’intérieur, ainsi que des stagiaires en journalisme. Envoyez-nous votre CV et trois articles ou reportages.', 'We regularly look for correspondents in neighborhoods and inland towns, as well as journalism interns. Send us your résumé and three stories or reports.')).'</p>',
                    'buttons' => [[$T('Proposer ma candidature', 'Apply'), $contact, 'outline']],
                    'photo' => ['medias-journal-conference', 'users', $T('Conférence de rédaction du matin', 'Morning editorial meeting')],
                    'reverse' => true,
                ]),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('qui-sommes-nous', 'about'), 'title' => $T('Qui sommes-nous', 'About us'),
            'description' => $T('Notre histoire, notre charte, notre indépendance et notre financement.', 'Our history, our charter, our independence and how we are funded.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Qui sommes-nous', 'About us'), 'title' => $T('Un journal libre, au service de ses lecteurs', 'A free newspaper, serving its readers'), 'intro' => $T('Indépendant, vérifié, proche des gens : c’est notre promesse depuis le premier numéro.', 'Independent, checked, close to people: our promise since the first issue.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('D’une feuille hebdomadaire à un média en ligne', 'From a weekly sheet to an online news outlet'),
                    'html' => '<p>'.e($T('Tout a commencé avec quatre journalistes et une feuille de huit pages vendue au marché. Aujourd’hui, notre rédaction publie chaque jour en ligne, envoie sa lettre du matin à des dizaines de milliers d’abonnés et informe sur tous les réseaux.', 'It all started with four journalists and an eight-page sheet sold at the market. Today our newsroom publishes online every day, sends its morning newsletter to tens of thousands of subscribers and reports across every network.')).'</p><p>'.e($T('Notre ligne n’a pas changé : informer juste, expliquer clairement, donner la parole à tous.', 'Our line hasn’t changed: report accurately, explain clearly, give everyone a voice.')).'</p>',
                    'photo' => ['medias-journal-histoire', 'newspaper', $T('Pile de journaux imprimés', 'Stack of printed newspapers')],
                ]),
                B::timeline($theme, ['eyebrow' => $T('Repères', 'Milestones'), 'title' => $T('Douze ans d’information', 'Twelve years of news'), 'bg' => 'light', 'items' => [
                    ['2013', $T('Premier numéro', 'First issue'), $T('Un hebdomadaire de huit pages, imprimé à 2 000 exemplaires.', 'An eight-page weekly, 2,000 copies printed.')],
                    ['2016', $T('Lancement du site', 'Website launch'), $T('L’actualité publiée chaque jour en ligne, gratuitement.', 'News published online every day, free of charge.')],
                    ['2019', $T('La lettre du matin', 'The morning newsletter'), $T('L’essentiel de l’actualité par e-mail et sur WhatsApp à 7 h.', 'The day’s essentials by email and WhatsApp at 7 am.')],
                    ['2022', $T('Cellule de vérification', 'Fact-checking desk'), $T('Une équipe dédiée à la vérification des rumeurs et des chiffres.', 'A team dedicated to checking rumors and figures.')],
                    ['2025', $T('Cellule d’enquête', 'Investigations team'), $T('Des enquêtes au long cours sur les sujets d’intérêt public.', 'In-depth investigations on matters of public interest.')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Notre charte', 'Our charter'), 'title' => $T('Trois principes, aucune exception', 'Three principles, no exceptions'), 'items' => [
                    ['shield', $T('Indépendance', 'Independence'), $T('Aucun parti, aucun annonceur ne décide de ce que nous publions.', 'No party and no advertiser decides what we publish.')],
                    ['eye', $T('Transparence', 'Transparency'), $T('Nos sources de financement et notre charte sont publiques.', 'Our funding sources and charter are public.')],
                    ['heart-handshake', $T('Proximité', 'Closeness'), $T('Des correspondants dans les quartiers et les villes de l’intérieur.', 'Correspondents in neighborhoods and inland towns.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur le journal', 'Your questions about the paper'), 'intro' => $T('Une autre question ? La rédaction vous répond.', 'Another question? The newsroom will answer.'), 'buttons' => [[$T('Écrire à la rédaction', 'Write to the newsroom'), $contact, 'outline']], 'bg' => 'light', 'items' => [
                    [$T('Comment le journal est-il financé ?', 'How is the paper funded?'), $T('Par la publicité, clairement signalée, par les abonnements de soutien et par des partenariats publiés chaque année.', 'Through clearly labeled advertising, supporter subscriptions and partnerships published every year.')],
                    [$T('Comment demander une correction ?', 'How do I request a correction?'), $T('Écrivez-nous en précisant l’article et l’erreur. Les corrections sont signalées en bas de l’article.', 'Write to us naming the story and the error. Corrections are noted at the bottom of the story.')],
                    [$T('Qu’est-ce que le droit de réponse ?', 'What is the right of reply?'), $T('Toute personne mise en cause dans un article peut faire publier sa réponse, dans les conditions prévues par la loi.', 'Anyone named in a story can have their reply published, under the conditions set by law.')],
                    [$T('Les articles sponsorisés sont-ils signalés ?', 'Are sponsored stories labeled?'), $T('Toujours : ils portent la mention « Contenu sponsorisé » et ne sont jamais écrits par la rédaction.', 'Always: they carry a “Sponsored content” label and are never written by the newsroom.')],
                ]]),
            ]];

        $pages[] = ['key' => 'advertise', 'slug' => $u('publicite', 'advertise'), 'title' => $T('Publicité', 'Advertise'),
            'description' => $T('Annoncez auprès de nos lecteurs : bannières, contenus sponsorisés et lettre d’information. Audience et tarifs.', 'Advertise to our readers: banners, sponsored content and newsletter. Audience and rates.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Annonceurs', 'Advertisers'), 'title' => $T('Touchez un public qui nous fait confiance', 'Reach an audience that trusts us'), 'intro' => $T('Des formats clairs, une audience mesurée, une publicité toujours séparée de l’information.', 'Clear formats, a measured audience, advertising always kept apart from the news.')]),
                B::stats($theme, $audience + ['bg' => 'primary']),
                B::chart($theme, [
                    'eyebrow' => $T('Audience', 'Audience'),
                    'title' => $T('Une audience qui grandit chaque mois', 'An audience that grows every month'),
                    'html' => '<p>'.e($T('Lecteurs uniques par mois sur le site et la lettre d’information, mesurés par notre outil de statistiques. Le rapport détaillé est inclus dans notre kit média.', 'Unique monthly readers on the website and newsletter, measured by our analytics tool. The detailed report is included in our media kit.')).'</p>',
                    'bullets' => [$T('62 % de lecteurs de 18 à 34 ans', '62% of readers aged 18 to 34'), $T('78 % de lecture sur téléphone', '78% of reading on mobile'), $T('Lettre ouverte par 1 abonné sur 2', 'Newsletter opened by 1 subscriber in 2')],
                    'chart' => ['kind' => 'area', 'title' => $T('Lecteurs uniques (milliers)', 'Unique readers (thousands)'), 'series' => [$T('Site', 'Website'), $T('Lettre', 'Newsletter')], 'rows' => [
                        [$T('Jan', 'Jan'), 168, 38], [$T('Fév', 'Feb'), 182, 40], [$T('Mar', 'Mar'), 196, 42], [$T('Avr', 'Apr'), 210, 44], [$T('Mai', 'May'), 231, 46], [$T('Juin', 'Jun'), 250, 48],
                    ]],
                ]),
                B::pricing($theme, ['eyebrow' => $T('Formats', 'Formats'), 'title' => $T('Nos offres publicitaires', 'Our advertising offers'), 'intro' => $T('Tarifs indicatifs hors taxes ; remises pour les campagnes longues et les associations.', 'Indicative rates before tax; discounts for long campaigns and nonprofits.'), 'url' => $contact, 'items' => [
                    [$T('Bannière', 'Banner'), '150', '$', $T('/ semaine', '/ week'), $T('Votre bannière sur toutes les pages du site.', 'Your banner on every page of the site.'), [$T('Ordinateur et téléphone', 'Desktop and mobile'), $T('Lien vers votre site', 'Link to your website'), $T('Statistiques de clics', 'Click statistics'), '-'.$T('Article rédigé', 'Written article')], $T('Réserver', 'Book')],
                    [$T('Contenu sponsorisé', 'Sponsored content'), '400', '$', $T('/ article', '/ article'), $T('Un article signalé « sponsorisé », mis en avant une semaine.', 'A story labeled “sponsored”, featured for a week.'), [$T('Rédaction par notre studio', 'Written by our studio'), $T('Partage sur nos réseaux', 'Shared on our networks'), $T('Photos incluses', 'Photos included'), $T('Bilan d’audience', 'Audience report')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus demandé', 'Most requested')],
                    [$T('Lettre du matin', 'Morning newsletter'), '250', '$', $T('/ envoi', '/ issue'), $T('Votre message en tête de la lettre envoyée à 7 h.', 'Your message at the top of the 7 am newsletter.'), [$T('48 000 abonnés', '48,000 subscribers'), $T('Image et lien', 'Image and link'), $T('Taux d’ouverture communiqué', 'Open rate shared'), '-'.$T('Article rédigé', 'Written article')], $T('Réserver', 'Book')],
                ]]),
                B::cta($theme, ['title' => $T('Recevez notre kit média', 'Get our media kit'), 'text' => $T('Audience détaillée, formats, calendrier éditorial et tarifs : notre équipe commerciale vous répond sous 24 heures.', 'Detailed audience, formats, editorial calendar and rates: our sales team replies within 24 hours.'), 'buttons' => [[$T('Demander le kit média', 'Request the media kit'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez la rédaction : information, droit de réponse, abonnement ou publicité.', 'Contact the newsroom: tips, right of reply, subscriptions or advertising.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Écrivez à la rédaction', 'Write to the newsroom'), 'intro' => $T('Une information, une correction, un projet publicitaire : nous vous répondons rapidement.', 'A tip, a correction, an advertising project: we reply quickly.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['megaphone', $T('Envoyer une information', 'Send a tip'), $T('Témoin d’un événement ? Envoyez-nous photos et détails, la rédaction vérifie.', 'Witnessed something? Send us photos and details; the newsroom will check.')],
                    ['scale', $T('Droit de réponse', 'Right of reply'), $T('Précisez l’article concerné et votre réponse : nous revenons vers vous sous 48 heures.', 'Name the story and your reply: we get back to you within 48 hours.')],
                    ['briefcase', $T('Publicité', 'Advertising'), $T('Formats, tarifs et disponibilités : notre équipe commerciale vous conseille.', 'Formats, rates and availability: our sales team will advise you.'), $advertise, $T('Voir les offres', 'See the offers')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('La rédaction est joignable 7 jours sur 7.', 'The newsroom can be reached 7 days a week.'),
                    'items' => [
                        ['map-pin', $T('Boulevard du 30 Juin, Kinshasa, RDC', 'Boulevard du 30 Juin, Kinshasa, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'redaction@exemple.org', 'mailto:redaction@exemple.org'],
                        ['clock', $T('Rédaction : 7 j/7, 7 h – 22 h', 'Newsroom: 7 days a week, 7 am – 10 pm')],
                    ],
                    'social' => ['facebook', 'x', 'whatsapp', 'youtube'],
                    'subject' => true,
                    'button' => $T('Envoyer à la rédaction', 'Send to the newsroom'),
                    'map' => 'Kinshasa, Gombe',
                ]),
            ]];

        $blog = [
            'base' => $u('actualites', 'news'),
            'title' => $T('Actualités', 'News'),
            'layout' => ['share' => true, 'terms' => true],
            'interval' => 1,
            'categories' => ['politique' => $T('Politique', 'Politics'), 'economie' => $T('Économie', 'Economy'), 'societe' => $T('Société', 'Society'), 'culture' => 'Culture', 'sport' => $T('Sport', 'Sports')],
            'posts' => [
                ['key' => 'marche', 'slug' => $u('marche-central-rouvre', 'central-market-reopens'), 'category' => 'economie', 'tags' => [$T('Commerce', 'Trade'), $T('Ville', 'City')],
                    'title' => $T('Le marché central rouvre après six mois de travaux', 'Central market reopens after six months of works'),
                    'excerpt' => $T('Nouvelles allées, toiture refaite, 1 200 commerçants réinstallés : ce qui change pour les clients et les vendeurs.', 'New aisles, a new roof, 1,200 traders back in place: what changes for shoppers and vendors.'),
                    'photo' => ['medias-journal-une-2', 'store', $T('Allées animées d’un grand marché', 'Busy aisles of a large market')],
                    'heading' => $T('Des loyers encore en discussion', 'Rents still under discussion'),
                    'quote' => $T('« On a retrouvé nos clients dès le premier jour. » — [Prénom Nom], vendeuse de tissus', '“Our customers were back on day one.” — [First Last], fabric seller'),
                    'body' => [
                        $T('Après six mois de chantier, le marché central a rouvert ses portes lundi. Les allées ont été élargies, la toiture entièrement refaite et un réseau d’évacuation des eaux installé pour la saison des pluies.', 'After six months of works, the central market reopened on Monday. Aisles have been widened, the roof fully rebuilt and a drainage system installed for the rainy season.'),
                        $T('Les commerçants réinstallés saluent des conditions de travail plus sûres, mais s’inquiètent du nouveau montant des emplacements, que la commune doit fixer d’ici [date].', 'Traders back in place welcome safer working conditions but worry about the new stall fees, which the city must set by [date].'),
                        $T('Notre rédaction suivra les négociations et publiera les tarifs dès leur adoption. Vous êtes commerçant ? Racontez-nous votre retour au marché.', 'Our newsroom will follow the talks and publish the fees as soon as they are adopted. Are you a trader? Tell us about your return to the market.'),
                    ]],
                ['key' => 'budget', 'slug' => $u('budget-communal-ce-qui-change', 'city-budget-what-changes'), 'category' => 'politique', 'tags' => [$T('Budget', 'Budget'), $T('Ville', 'City')],
                    'title' => $T('Budget communal : ce qui change pour les habitants', 'City budget: what changes for residents'),
                    'excerpt' => $T('Routes, écoles, éclairage public : les grandes lignes du budget voté par le conseil, poste par poste.', 'Roads, schools, street lighting: the main lines of the budget voted by the council, item by item.'),
                    'photo' => ['medias-journal-actu-budget', 'landmark', $T('Séance du conseil municipal', 'City council session')],
                    'heading' => $T('Les investissements prioritaires', 'Priority investments'),
                    'body' => [
                        $T('Le conseil communal a adopté hier soir le budget de l’année, en hausse de [x] % par rapport à l’an dernier. Nous l’avons lu ligne par ligne pour vous.', 'The city council adopted the year’s budget last night, up [x]% on last year. We read it line by line for you.'),
                        $T('Les routes et le drainage représentent la plus grosse part des investissements, suivis de la rénovation de [nombre] écoles et de l’extension de l’éclairage public dans [nombre] quartiers.', 'Roads and drainage are the largest share of investment, followed by the renovation of [number] schools and street lighting in [number] neighborhoods.'),
                        $T('L’opposition a voté contre, dénonçant des recettes surestimées. Le maire répond que la collecte des taxes a progressé. Nous suivrons l’exécution de ce budget tout au long de l’année.', 'The opposition voted against, criticizing overestimated revenue. The mayor replies that tax collection has improved. We will follow how this budget is spent throughout the year.'),
                    ]],
                ['key' => 'rentree', 'slug' => $u('rentree-scolaire-cout-fournitures', 'back-to-school-supply-costs'), 'category' => 'societe', 'tags' => [$T('Éducation', 'Education'), $T('Familles', 'Families')],
                    'title' => $T('Rentrée scolaire : les familles face au coût des fournitures', 'Back to school: families face the cost of supplies'),
                    'excerpt' => $T('Cahiers, uniformes, frais d’inscription : nous avons comparé les prix dans cinq marchés de la ville.', 'Notebooks, uniforms, enrollment fees: we compared prices in five city markets.'),
                    'photo' => ['medias-journal-actu-ecole', 'school', $T('Écoliers en uniforme', 'Schoolchildren in uniform')],
                    'heading' => $T('Nos conseils pour réduire la facture', 'Our tips to cut the bill'),
                    'body' => [
                        $T('À deux semaines de la rentrée, les parents font leurs comptes. Selon notre relevé dans cinq marchés, le kit de base d’un élève du primaire coûte entre [montant] et [montant].', 'Two weeks before school starts, parents are doing the math. According to our survey of five markets, a primary pupil’s basic kit costs between [amount] and [amount].'),
                        $T('Les prix varient du simple au double d’un marché à l’autre. Acheter en groupe entre voisins ou se tourner vers les uniformes d’occasion permet d’économiser sensiblement.', 'Prices vary twofold from one market to another. Buying in groups with neighbors or choosing second-hand uniforms saves a lot.'),
                        $T('Plusieurs associations organisent aussi des distributions gratuites : nous publions la liste mise à jour chaque jour jusqu’à la rentrée.', 'Several associations also hold free distributions: we publish the updated list every day until school starts.'),
                    ]],
                ['key' => 'cadets', 'slug' => $u('cadets-qualifies-finale', 'under-17-reach-regional-final'), 'category' => 'sport', 'tags' => ['Football', $T('Jeunesse', 'Youth')],
                    'title' => $T('Football : les cadets qualifiés pour la finale régionale', 'Football: under-17s reach the regional final'),
                    'excerpt' => $T('Une victoire 2-1 arrachée dans les dernières minutes, devant un stade comble.', 'A 2–1 win snatched in the final minutes, in front of a packed stadium.'),
                    'photo' => ['medias-journal-actu-sport', 'trophy', $T('Jeunes footballeurs pendant un match', 'Young footballers during a match')],
                    'body' => [
                        $T('Menés à la pause, les cadets du club ont renversé la situation en seconde période pour s’imposer 2-1 et décrocher leur place en finale régionale, une première depuis dix ans.', 'Trailing at half-time, the club’s under-17s turned the game around in the second half to win 2–1 and reach the regional final, a first in ten years.'),
                        $T('L’entraîneur salue « un groupe soudé, qui a travaillé toute l’année ». La finale se jouera le [date] au stade municipal.', 'The coach praised “a close-knit group that worked hard all year”. The final will be played on [date] at the municipal stadium.'),
                        $T('Notre rédaction sportive suivra la rencontre en direct sur notre site et nos réseaux.', 'Our sports desk will cover the match live on our website and social networks.'),
                    ]],
                ['key' => 'festival', 'slug' => $u('festival-musiques-urbaines', 'urban-music-festival-returns'), 'category' => 'culture', 'tags' => [$T('Musique', 'Music'), 'Festival'],
                    'title' => $T('Le festival des musiques urbaines revient en juillet', 'The urban music festival returns in July'),
                    'excerpt' => $T('Trois jours de concerts gratuits, une scène ouverte aux jeunes talents et des ateliers pour les enfants.', 'Three days of free concerts, an open stage for young talent and workshops for children.'),
                    'photo' => ['medias-journal-une-3', 'music', $T('Foule devant une scène de concert', 'Crowd in front of a concert stage')],
                    'heading' => $T('Une scène pour les jeunes talents', 'A stage for young talent'),
                    'body' => [
                        $T('Le festival des musiques urbaines revient pour sa [nombre]ᵉ édition, du [date] au [date], sur l’esplanade du bord du fleuve. Les organisateurs annoncent une trentaine d’artistes.', 'The urban music festival returns for its [number]th edition, from [date] to [date], on the riverside esplanade. Organizers announce some thirty artists.'),
                        $T('Nouveauté cette année : une scène ouverte permettra à dix jeunes artistes sélectionnés sur maquette de se produire devant le public. Les candidatures sont ouvertes jusqu’au [date].', 'New this year: an open stage will let ten young artists, selected from demos, perform for the crowd. Applications are open until [date].'),
                        $T('L’entrée reste gratuite. Programme complet, horaires et plan d’accès sont à retrouver sur notre site dans les prochains jours.', 'Admission remains free. The full program, times and access map will be on our site in the coming days.'),
                    ]],
                ['key' => 'eau', 'slug' => $u('enquete-eau-reseau-public', 'investigation-public-water-network'), 'category' => 'societe', 'tags' => [$T('Enquête', 'Investigation'), $T('Eau', 'Water')],
                    'title' => $T('Enquête : où va l’eau du réseau public ?', 'Investigation: where does the public network’s water go?'),
                    'excerpt' => $T('Fuites, branchements illégaux, factures impayées : trois mois d’enquête pour comprendre les coupures à répétition.', 'Leaks, illegal connections, unpaid bills: a three-month investigation into repeated water cuts.'),
                    'photo' => ['medias-journal-accueil', 'droplet', $T('Habitants remplissant des bidons à une borne-fontaine', 'Residents filling jerrycans at a standpipe')],
                    'heading' => $T('Ce que disent les documents', 'What the documents say'),
                    'quote' => $T('« Certains jours, nous n’avons de l’eau qu’entre 2 h et 5 h du matin. » — [Prénom Nom], habitante', '“Some days we only have water between 2 and 5 am.” — [First Last], resident'),
                    'body' => [
                        $T('Pendant trois mois, notre cellule d’enquête a suivi le parcours de l’eau, de l’usine de traitement jusqu’aux robinets de [nombre] quartiers. Constat : une part importante de l’eau produite n’arrive jamais aux abonnés.', 'For three months, our investigations team followed the water from the treatment plant to the taps of [number] neighborhoods. Finding: a large share of the water produced never reaches subscribers.'),
                        $T('Les rapports techniques que nous avons consultés évoquent des canalisations vieilles de plusieurs décennies et des branchements non déclarés. La société des eaux, contactée, reconnaît « un réseau à moderniser » et annonce un plan de travaux.', 'The technical reports we consulted point to pipes several decades old and undeclared connections. The water company, when contacted, acknowledged “a network in need of modernization” and announced a works plan.'),
                        $T('Dans un second volet, nous verrons comment d’autres villes ont réduit leurs pertes. Vous vivez des coupures ? Témoignez auprès de la rédaction.', 'In a second part, we will look at how other cities have cut their losses. Do you live with water cuts? Tell the newsroom.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => $T('Rubriques', 'Sections'), 'url' => $sections],
                    ['label' => $T('Enquêtes', 'Investigations'), 'url' => $investigations],
                    ['label' => $T('La rédaction', 'Newsroom'), 'url' => $newsroom],
                    ['label' => $T('Qui sommes-nous', 'About'), 'url' => $about],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Publicité', 'Advertise'),
                'cta_url' => $advertise,
                'footer_text' => $T('Journal d’information indépendant : l’actualité locale et nationale, vérifiée et expliquée chaque jour.', 'Independent news outlet: local and national news, checked and explained every day.'),
                'footer_links' => [
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => $T('Enquêtes', 'Investigations'), 'url' => $investigations],
                    ['label' => $T('Publicité', 'Advertise'), 'url' => $advertise],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
