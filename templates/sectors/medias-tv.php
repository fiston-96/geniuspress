<?php

/*
 * Pack « Médias et presse » — chaîne de télévision : 8 pages, en français et en anglais.
 * La une (programmes phares et dernières infos), le direct (vidéo et bouton « Regarder
 * en direct » vers la chaîne en ligne, à remplacer), la grille des programmes, les
 * émissions et leurs replays, l'info, la chaîne, les annonceurs et le contact.
 * Les vidéos d'exemple sont des films libres de la Blender Foundation (CC BY), à remplacer.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Médias et presse', 'en' => 'Media and press'],
        'icon' => 'newspaper',
        'keywords' => ['média', 'medias', 'presse', 'journal', 'radio', 'télévision', 'tv', 'chaîne', 'podcast', 'journaliste', 'media', 'press', 'television', 'channel', 'journalist'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $live = $u('/direct', '/live');
        $schedule = $u('/programmes', '/schedule');
        $shows = $u('/emissions', '/shows');
        $news = $u('/info', '/news');
        $about = $u('/la-chaine', '/about');
        $advertise = $u('/annonceurs', '/advertise');
        $contact = '/contact';
        $post = fn (string $fr, string $en) => $news.'/'.$T($fr, $en);
        // Chaîne en ligne (YouTube, site de la chaîne…) : lien externe à remplacer.
        $stream = 'https://www.youtube.com/';
        $yt = fn (string $id) => 'https://www.youtube.com/watch?v='.$id;

        $watch = [$T('Regarder en direct', 'Watch live'), $stream];
        $replays = [
            [$T('Le Journal de 20 h — édition du jour', 'The 8 pm News — today’s edition'), $yt('aqz-KE-bpKQ'), '10:34'],
            [$T('Face à Face — le débat de la semaine', 'Face to Face — the week’s debate'), $yt('eRsGyueVLvQ'), '14:48'],
            [$T('Talents d’Afrique — la finale', 'African Talents — the final'), $yt('_cMxraX_5RE'), '10:30'],
            [$T('Terre d’ici — les maraîchers du fleuve', 'Land of Ours — the river market gardeners'), $yt('Y-rmzh0PI3c'), '12:10'],
            [$T('Sport Mag — le résumé du week-end', 'Sport Mag — weekend highlights'), $yt('mN0zPOpADL4'), '3:51'],
        ];
        $showItems = [
            ['medias-tv-emission-journal', 'newspaper', $T('Le Journal de 20 h', 'The 8 pm News'), $T('L’essentiel de l’actualité nationale et internationale, chaque soir en direct.', 'The essential national and international news, live every evening.'), $schedule],
            ['medias-tv-emission-debat', 'message-circle', $T('Face à Face', 'Face to Face'), $T('Le débat politique de la semaine : deux invités, un arbitre, aucune langue de bois.', 'The week’s political debate: two guests, one moderator, no spin.'), $schedule],
            ['medias-tv-emission-talents', 'sparkles', $T('Talents d’Afrique', 'African Talents'), $T('Chant, danse, humour : le concours qui révèle les artistes de demain.', 'Singing, dance, comedy: the contest that reveals tomorrow’s artists.'), $schedule],
            ['medias-tv-emission-terre', 'sprout', $T('Terre d’ici', 'Land of Ours'), $T('Le magazine de l’agriculture et de l’environnement, au plus près des producteurs.', 'The farming and environment magazine, up close with producers.'), $schedule],
            ['medias-tv-emission-sport', 'trophy', $T('Sport Mag', 'Sport Mag'), $T('Résumés, analyses et portraits : tout le sport du week-end.', 'Highlights, analysis and profiles: all the weekend’s sport.'), $schedule],
            ['medias-tv-emission-jeunesse', 'smile', $T('Les P’tits Curieux', 'Little Explorers'), $T('Le rendez-vous éducatif et ludique des 6-12 ans, chaque mercredi.', 'The fun, educational show for 6 to 12 year-olds, every Wednesday.'), $schedule],
        ];
        $audience = ['title' => $T('Une chaîne regardée par tout le pays', 'A channel watched across the country'), 'items' => [
            [4.2, ' M', $T('téléspectateurs par semaine', 'weekly viewers')],
            [26, ' %', $T('de part d’audience à 20 h', 'audience share at 8 pm')],
            [1.5, ' M', $T('abonnés sur nos chaînes en ligne', 'online channel subscribers')],
            [20, '', $T('ans de diffusion', 'years on air')],
        ]];
        $cta = ['title' => $T('Votre marque à l’écran', 'Your brand on screen'), 'text' => $T('Spots, parrainages, placements de produits : touchez des millions de téléspectateurs avec nos équipes de production.', 'Spots, sponsorships, product placement: reach millions of viewers with our production teams.'),
            'buttons' => [[$T('Devenir annonceur', 'Advertise with us'), $advertise, 'accent'], [$T('Nous contacter', 'Contact us'), $contact, 'outline-light']]];
        $tonight = [
            [$T('Heure', 'Time'), $T('Programme', 'Program'), $T('Genre', 'Genre')],
            ['06:30', $T('Bonjour l’Afrique', 'Good Morning Africa'), $T('Matinale', 'Morning show')],
            ['12:30', $T('Le Journal de 13 h', 'The 1 pm News'), $T('Information', 'News')],
            ['14:00', $T('Série : Les Héritiers', 'Series: The Heirs'), $T('Fiction', 'Drama')],
            ['17:30', $T('Les P’tits Curieux', 'Little Explorers'), $T('Jeunesse', 'Kids')],
            ['19:00', $T('Terre d’ici', 'Land of Ours'), $T('Magazine', 'Magazine')],
            ['20:00', $T('Le Journal de 20 h', 'The 8 pm News'), $T('Information', 'News')],
            ['20:45', $T('Talents d’Afrique', 'African Talents'), $T('Divertissement', 'Entertainment')],
            ['22:30', $T('Face à Face', 'Face to Face'), $T('Débat', 'Debate')],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('La chaîne d’information et de divertissement : direct, programmes, replays et actualité.', 'The news and entertainment channel: live, programs, replays and news.'),
            'sections' => [
                B::ticker($theme, ['label' => $T('En ce moment', 'Right now'), 'items' => [
                    [$T('20 h : Le Journal, en direct et en ligne', '8 pm: The News, live on TV and online'), $live],
                    [$T('Talents d’Afrique : la grande finale samedi à 20 h 45', 'African Talents: the grand final on Saturday at 8:45 pm'), $post('finale-talents-afrique', 'african-talents-final')],
                    [$T('Nouvelle série : Les Héritiers, tous les jours à 14 h', 'New series: The Heirs, every day at 2 pm'), $post('nouvelle-serie-les-heritiers', 'new-series-the-heirs')],
                    [$T('Face à Face : posez vos questions aux invités', 'Face to Face: ask the guests your questions'), $contact],
                ]]),
                B::headlines($theme, [
                    'side' => $T('L’info', 'News'), 'read' => $T('Découvrir', 'Find out more'), 'count' => 3,
                    'button' => [$T('Toute l’info', 'All the news'), $news],
                    'slides' => [
                        ['medias-tv-accueil', 'video', $T('Talents d’Afrique : la grande finale', 'African Talents: the grand final'), $T('Six finalistes, un jury de stars et vos votes : samedi à 20 h 45 en direct.', 'Six finalists, a star jury and your votes: Saturday at 8:45 pm, live.'), $post('finale-talents-afrique', 'african-talents-final'), $T('Divertissement', 'Entertainment')],
                        ['medias-tv-une-2', 'film', $T('Les Héritiers, la nouvelle série quotidienne', 'The Heirs, the new daily series'), $T('Une saga familiale tournée dans le pays, tous les jours à 14 h.', 'A family saga filmed in the country, every day at 2 pm.'), $post('nouvelle-serie-les-heritiers', 'new-series-the-heirs'), $T('Série', 'Series')],
                        ['medias-tv-une-3', 'newspaper', $T('Le Journal de 20 h fait peau neuve', 'The 8 pm News gets a new look'), $T('Nouveau plateau, nouveaux reportages en région, plus de place pour vos questions.', 'A new set, more regional reports, more room for your questions.'), $post('nouveau-journal-20h', 'new-8pm-news'), $T('Information', 'News')],
                    ],
                ]),
                B::live($theme, [
                    'badge' => $T('En direct', 'Live now'),
                    'title' => $T('Suivez la chaîne en direct, partout', 'Watch the channel live, anywhere'),
                    'text' => $T('Sur la TNT, le satellite, le câble ou en ligne : nos programmes sont aussi diffusés en direct sur Internet, gratuitement.', 'On digital terrestrial, satellite, cable or online: our programs also stream live on the Internet, for free.'),
                    'items' => [['monitor', $T('TNT canal 23 · satellite canal 112', 'Digital channel 23 · satellite channel 112')], ['smartphone', $T('Sur l’appli et sur notre chaîne en ligne', 'On the app and our online channel')]],
                    'buttons' => [$watch, [$T('Programme du jour', 'Today’s schedule'), $live]],
                    'video' => $yt('aqz-KE-bpKQ'),
                ]),
                B::videos($theme, ['eyebrow' => $T('Replay', 'Replay'), 'title' => $T('Vous les avez manqués ?', 'Missed them?'), 'intro' => $T('Nos émissions en intégralité, disponibles juste après leur diffusion.', 'Our full shows, available right after they air.'), 'items' => $replays]),
                B::cards($theme, ['eyebrow' => $T('Nos émissions', 'Our shows'), 'title' => $T('Les programmes phares', 'Flagship programs'), 'more' => $T('Horaires', 'Times'), 'bg' => 'light', 'items' => array_slice($showItems, 0, 3)]),
                B::posts($theme, ['eyebrow' => $T('L’info', 'News'), 'title' => $T('L’actualité de la chaîne', 'Latest news'), 'count' => 6, 'layout' => 'carousel', 'excerpt' => false, 'more' => $T('Lire', 'Read')]),
                B::stats($theme, $audience),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'live', 'slug' => $u('direct', 'live'), 'title' => $T('Direct', 'Live'),
            'description' => $T('Regardez la chaîne en direct : TNT, satellite, câble et en ligne. Le programme du jour.', 'Watch the channel live: digital terrestrial, satellite, cable and online. Today’s schedule.'),
            'sections' => [
                B::live($theme, [
                    'tag' => 'h1', 'badge' => $T('En direct', 'Live now'),
                    'title' => $T('Regardez la chaîne en direct', 'Watch the channel live'),
                    'text' => $T('Le direct est diffusé sur notre chaîne en ligne : il s’ouvre dans un nouvel onglet. Certains programmes (films, sport) peuvent être indisponibles en ligne pour des raisons de droits.', 'The live stream plays on our online channel and opens in a new tab. Some programs (films, sport) may be unavailable online for rights reasons.'),
                    'items' => [['monitor', $T('TNT canal 23', 'Digital channel 23')], ['globe', $T('Satellite canal 112 · câble canal 8', 'Satellite channel 112 · cable channel 8')]],
                    'buttons' => [$watch],
                    'video' => $yt('aqz-KE-bpKQ'),
                ]),
                B::table($theme, ['eyebrow' => $T('Aujourd’hui', 'Today'), 'title' => $T('Programme du jour', 'Today’s schedule'), 'rows' => $tonight, 'note' => $T('Horaires donnés à titre indicatif, sous réserve de modification.', 'Times are indicative and subject to change.')]),
                B::features($theme, ['eyebrow' => $T('Où nous regarder', 'Where to watch'), 'title' => $T('La chaîne sur tous les écrans', 'The channel on every screen'), 'cols' => 4, 'bg' => 'light', 'items' => [
                    ['monitor', 'TNT', $T('Canal 23, avec un simple décodeur TNT et une antenne.', 'Channel 23, with a simple digital decoder and an antenna.')],
                    ['globe', $T('Satellite', 'Satellite'), $T('Canal 112 sur les principaux bouquets satellite.', 'Channel 112 on the main satellite packages.')],
                    ['layers', $T('Câble', 'Cable'), $T('Canal 8 chez les câblo-opérateurs partenaires.', 'Channel 8 with partner cable operators.')],
                    ['smartphone', $T('En ligne', 'Online'), $T('Direct et replays sur l’appli et sur notre chaîne en ligne.', 'Live and replays on the app and our online channel.')],
                ]]),
                B::faq($theme, ['title' => $T('Questions fréquentes', 'Frequently asked questions'), 'intro' => $T('Un problème de réception ? Notre service technique vous aide.', 'Reception problem? Our technical team can help.'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'outline']], 'items' => [
                    [$T('Je ne reçois plus la chaîne sur la TNT', 'I no longer get the channel on digital TV'), $T('Relancez la recherche des chaînes sur votre décodeur. Si le problème persiste, écrivez-nous avec votre ville.', 'Run a channel scan on your decoder. If the problem persists, write to us with your town.')],
                    [$T('Pourquoi un programme n’est-il pas en ligne ?', 'Why isn’t a program online?'), $T('Certains films et matchs ne peuvent être diffusés qu’à la télévision : les droits ne couvrent pas Internet.', 'Some films and matches can only be shown on TV: the rights do not cover the Internet.')],
                    [$T('Combien de temps les replays restent-ils disponibles ?', 'How long do replays stay available?'), $T('En général 30 jours après la diffusion, parfois plus pour les magazines.', 'Usually 30 days after broadcast, sometimes longer for magazines.')],
                ]]),
            ]];

        $pages[] = ['key' => 'schedule', 'slug' => $u('programmes', 'schedule'), 'title' => $T('Programmes', 'Schedule'),
            'description' => $T('La grille des programmes de la semaine et les soirées spéciales.', 'The weekly program schedule and special evenings.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Programmes', 'Schedule'), 'title' => $T('La grille de la semaine', 'This week’s schedule'), 'intro' => $T('Information, séries, magazines et divertissement : tout ce qui passe à l’antenne.', 'News, series, magazines and entertainment: everything on air.')]),
                B::table($theme, ['rows' => [
                    [$T('Heure', 'Time'), $T('Lundi – vendredi', 'Monday – Friday'), $T('Samedi', 'Saturday'), $T('Dimanche', 'Sunday')],
                    ['06:30', $T('Bonjour l’Afrique', 'Good Morning Africa'), $T('Dessins animés', 'Cartoons'), $T('Dessins animés', 'Cartoons')],
                    ['12:30', $T('Le Journal de 13 h', 'The 1 pm News'), $T('Le Journal de 13 h', 'The 1 pm News'), $T('Le Journal de 13 h', 'The 1 pm News')],
                    ['14:00', $T('Série : Les Héritiers', 'Series: The Heirs'), $T('Sport Mag', 'Sport Mag'), $T('Film de l’après-midi', 'Afternoon movie')],
                    ['17:30', $T('Les P’tits Curieux (mer.)', 'Little Explorers (Wed.)'), $T('Football en direct', 'Live football'), $T('Terre d’ici', 'Land of Ours')],
                    ['20:00', $T('Le Journal de 20 h', 'The 8 pm News'), $T('Le Journal de 20 h', 'The 8 pm News'), $T('Le Journal de 20 h', 'The 8 pm News')],
                    ['20:45', $T('Série ou film', 'Series or movie'), $T('Talents d’Afrique', 'African Talents'), $T('Documentaire', 'Documentary')],
                    ['22:30', $T('Face à Face (jeu.)', 'Face to Face (Thu.)'), $T('Concert', 'Concert'), $T('Le Grand Entretien', 'The Big Interview')],
                ], 'note' => $T('Flashs d’information à 7 h, 9 h, 16 h et 18 h en semaine.', 'News flashes at 7 am, 9 am, 4 pm and 6 pm on weekdays.')]),
                B::events($theme, ['eyebrow' => $T('Soirées spéciales', 'Special evenings'), 'title' => $T('À ne pas manquer', 'Not to be missed'), 'icon' => 'video', 'bg' => 'light', 'items' => [
                    [$T('Finale de Talents d’Afrique', 'African Talents final'), $T('Samedi, 20 h 45', 'Saturday, 8:45 pm'), $T('En direct et en public', 'Live, in front of an audience')],
                    [$T('Soirée électorale', 'Election night'), $T('Dimanche 30, dès 18 h', 'Sunday 30, from 6 pm'), $T('Résultats et analyses en continu', 'Rolling results and analysis')],
                    [$T('Documentaire : le fleuve', 'Documentary: the river'), $T('Dimanche, 20 h 45', 'Sunday, 8:45 pm'), $T('Production de la chaîne', 'Channel production')],
                    [$T('Match de la sélection nationale', 'National team match'), $T('Mardi, 18 h', 'Tuesday, 6 pm'), $T('Avant-match dès 17 h', 'Pre-match from 5 pm')],
                ]]),
            ]];

        $pages[] = ['key' => 'shows', 'slug' => $u('emissions', 'shows'), 'title' => $T('Émissions', 'Shows'),
            'description' => $T('Nos émissions : information, débats, divertissement, magazines, sport et jeunesse. Replays.', 'Our shows: news, debates, entertainment, magazines, sport and kids. Replays.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Émissions', 'Shows'), 'title' => $T('Nos émissions', 'Our shows'), 'intro' => $T('Des programmes produits dans le pays, par des équipes passionnées.', 'Programs produced in the country, by passionate teams.')]),
                B::cards($theme, ['more' => $T('Horaires', 'Times'), 'items' => $showItems]),
                B::videos($theme, ['eyebrow' => $T('Replay', 'Replay'), 'title' => $T('Les derniers replays', 'Latest replays'), 'bg' => 'light', 'items' => $replays]),
                B::split($theme, [
                    'eyebrow' => $T('Participer', 'Take part'),
                    'title' => $T('Venez sur le plateau', 'Come to the studio'),
                    'html' => '<p>'.e($T('Assistez à l’enregistrement d’une émission, posez votre question à un invité ou inscrivez-vous au casting de Talents d’Afrique.', 'Attend a show recording, put your question to a guest or sign up for the African Talents casting.')).'</p>',
                    'bullets' => [$T('Places gratuites sur inscription', 'Free seats on registration'), $T('Castings ouverts toute l’année', 'Castings open all year'), $T('Questions en direct par WhatsApp', 'Live questions by WhatsApp')],
                    'buttons' => [[$T('Je m’inscris', 'Sign me up'), $contact]],
                    'photo' => ['medias-tv-plateau', 'video', $T('Public sur un plateau de télévision', 'Audience in a TV studio')],
                    'reverse' => true,
                ]),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('info', 'news'), 'title' => $T('L’info', 'News'),
            'description' => $T('L’actualité de la chaîne et du pays : information, programmes, sport et culture.', 'News from the channel and the country: current affairs, programs, sport and culture.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’info', 'News'), 'title' => $T('L’actualité de la chaîne', 'Channel news'), 'intro' => $T('Nos reportages, nos programmes et les coulisses de la chaîne.', 'Our reports, our programs and behind the scenes.')]),
                B::terms($theme, ['bg' => 'light']),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('la-chaine', 'about'), 'title' => $T('La chaîne', 'About'),
            'description' => $T('Notre histoire, nos valeurs et les visages de la chaîne.', 'Our history, our values and the faces of the channel.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('La chaîne', 'About'), 'title' => $T('Vingt ans à vos côtés', 'Twenty years by your side'), 'intro' => $T('Une chaîne indépendante, qui produit ses programmes dans le pays.', 'An independent channel that produces its programs in the country.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('D’un petit studio à une chaîne nationale', 'From a small studio to a national channel'),
                    'html' => '<p>'.e($T('La chaîne a commencé par trois heures de programmes par jour, diffusées dans la capitale. Elle émet aujourd’hui 24 heures sur 24 sur tout le territoire et en ligne, avec plus de 200 collaborateurs.', 'The channel started with three hours of programming a day in the capital. Today it broadcasts 24 hours a day across the country and online, with more than 200 staff.')).'</p>',
                    'photo' => ['medias-tv-studio', 'camera', $T('Caméra de télévision sur un plateau', 'TV camera in a studio')],
                ]),
                B::timeline($theme, ['eyebrow' => $T('Repères', 'Milestones'), 'title' => $T('Les grandes étapes', 'Key milestones'), 'bg' => 'light', 'items' => [
                    ['2005', $T('Premières émissions', 'First broadcasts'), $T('Trois heures par jour, dans la capitale.', 'Three hours a day, in the capital.')],
                    ['2011', $T('Diffusion nationale', 'Nationwide coverage'), $T('Le satellite porte la chaîne dans tout le pays.', 'Satellite takes the channel nationwide.')],
                    ['2017', $T('Passage à la TNT et à la HD', 'Digital terrestrial and HD'), $T('Une image plus nette sur le canal 23.', 'A sharper picture on channel 23.')],
                    ['2021', $T('Direct en ligne et replays', 'Online live and replays'), $T('Toute la chaîne sur Internet et sur l’appli.', 'The whole channel on the Internet and the app.')],
                    ['2025', $T('Nouveau plateau d’information', 'New news studio'), $T('Un studio moderne pour le Journal de 20 h.', 'A modern studio for the 8 pm News.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('Les visages de la chaîne', 'The faces of the channel'), 'title' => $T('Nos présentateurs', 'Our presenters'), 'items' => [
                    ['[Prénom Nom]', $T('Le Journal de 20 h', 'The 8 pm News'), 'medias-tv-equipe-1'],
                    ['[Prénom Nom]', $T('Bonjour l’Afrique', 'Good Morning Africa'), 'medias-tv-equipe-2'],
                    ['[Prénom Nom]', $T('Face à Face', 'Face to Face'), 'medias-tv-equipe-3'],
                    ['[Prénom Nom]', $T('Talents d’Afrique', 'African Talents'), 'medias-tv-equipe-4'],
                    ['[Prénom Nom]', $T('Sport Mag', 'Sport Mag'), 'medias-tv-equipe-5'],
                    ['[Prénom Nom]', $T('Terre d’ici', 'Land of Ours'), 'medias-tv-equipe-6'],
                    ['[Prénom Nom]', $T('Directrice de l’information', 'Head of news'), 'medias-tv-equipe-7'],
                    ['[Prénom Nom]', $T('Directeur des programmes', 'Head of programming'), 'medias-tv-equipe-8'],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Informer, divertir, rassembler', 'Inform, entertain, bring together'), 'bg' => 'light', 'items' => [
                    ['shield-check', $T('Information vérifiée', 'Checked news'), $T('Une rédaction indépendante et une charte publique.', 'An independent newsroom and a public charter.')],
                    ['video', $T('Production locale', 'Local production'), $T('Plus de 70 % de nos programmes produits dans le pays.', 'Over 70% of our programs produced in the country.')],
                    ['users', $T('Pour toute la famille', 'For the whole family'), $T('Des programmes adaptés à chaque âge et à chaque heure.', 'Programs suited to every age and every hour.')],
                ]]),
            ]];

        $pages[] = ['key' => 'advertise', 'slug' => $u('annonceurs', 'advertise'), 'title' => $T('Annonceurs', 'Advertise'),
            'description' => $T('Annoncez sur la chaîne : spots, parrainages, placements de produits et publireportages.', 'Advertise on the channel: spots, sponsorships, product placement and advertorials.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Annonceurs', 'Advertisers'), 'title' => $T('Faites-vous voir de tout le pays', 'Get seen across the country'), 'intro' => $T('La télévision reste le média qui rassemble les familles, chaque soir.', 'Television is still the medium that brings families together every evening.')]),
                B::stats($theme, $audience + ['bg' => 'primary']),
                B::chart($theme, [
                    'eyebrow' => $T('Audience', 'Audience'),
                    'title' => $T('Le soir, la famille se retrouve devant la chaîne', 'In the evening, families gather around the channel'),
                    'html' => '<p>'.e($T('Répartition de notre audience par tranche horaire en semaine (mesure d’audience, [année]). Le Journal de 20 h et la soirée concentrent plus de la moitié des téléspectateurs.', 'Our weekday audience by time slot (audience measurement, [year]). The 8 pm News and prime time draw more than half of viewers.')).'</p>',
                    'bullets' => [$T('Spots produits par notre studio', 'Spots produced by our studio'), $T('Diffusion en ligne en complément', 'Online streaming as an extra'), $T('Bilan d’audience après campagne', 'Audience report after each campaign')],
                    'chart' => ['kind' => 'doughnut', 'title' => $T('Audience par tranche horaire', 'Audience by time slot'), 'suffix' => ' %', 'rows' => [
                        [$T('Matin', 'Morning'), 14], [$T('Midi', 'Midday'), 18], [$T('Après-midi', 'Afternoon'), 12], [$T('20 h et soirée', '8 pm and evening'), 56],
                    ]],
                ]),
                B::pricing($theme, ['eyebrow' => $T('Offres', 'Packages'), 'title' => $T('Nos formats publicitaires', 'Our advertising formats'), 'intro' => $T('Tarifs indicatifs hors taxes ; plan média sur mesure pour les campagnes nationales.', 'Indicative rates before tax; tailored media plans for national campaigns.'), 'url' => $contact, 'bg' => 'light', 'items' => [
                    [$T('Spot 30 secondes', '30-second spot'), '180', '$', $T('/ passage', '/ airing'), $T('Votre spot dans l’écran publicitaire de votre choix.', 'Your spot in the ad break of your choice.'), [$T('Choix de l’horaire', 'Choice of time'), $T('Rapport de diffusion', 'Broadcast report'), $T('Diffusion en ligne incluse', 'Online broadcast included'), '-'.$T('Production du spot', 'Spot production')], $T('Réserver', 'Book')],
                    [$T('Parrainage d’émission', 'Show sponsorship'), '2500', '$', $T('/ mois', '/ month'), $T('Votre marque en ouverture et en clôture d’une émission phare.', 'Your brand opening and closing a flagship show.'), [$T('Billboard de 8 secondes', '8-second billboard'), $T('Logo à l’écran', 'On-screen logo'), $T('Visibilité sur nos réseaux', 'Visibility on our networks'), $T('Bilan d’audience mensuel', 'Monthly audience report')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus visible', 'Most visible')],
                    [$T('Publireportage', 'Advertorial'), '1200', '$', $T('/ reportage', '/ report'), $T('Un reportage de 3 minutes réalisé par notre studio.', 'A 3-minute report made by our studio.'), [$T('Tournage et montage', 'Filming and editing'), $T('Deux diffusions', 'Two airings'), $T('Mention « publireportage »', '“Advertorial” label'), $T('Fichier vidéo remis', 'Video file delivered')], $T('Nous contacter', 'Contact us')],
                ]]),
                B::cta($theme, ['title' => $T('Construisons votre plan média', 'Let’s build your media plan'), 'text' => $T('Notre régie vous propose un plan de diffusion et un devis sous 48 heures.', 'Our sales team will send a broadcast plan and quote within 48 hours.'), 'buttons' => [[$T('Contacter la régie', 'Contact sales'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez la chaîne : rédaction, castings, réception, publicité.', 'Contact the channel: newsroom, castings, reception, advertising.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Écrivez à la chaîne', 'Write to the channel'), 'intro' => $T('Rédaction, castings, technique ou publicité : le bon interlocuteur vous répond.', 'Newsroom, castings, technical or advertising: the right person will answer.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['newspaper', $T('Rédaction', 'Newsroom'), $T('Une information, un sujet de reportage ? Écrivez à la rédaction.', 'A tip, a story idea? Write to the newsroom.')],
                    ['sparkles', $T('Castings et public', 'Castings and audience'), $T('Inscrivez-vous aux castings ou pour assister à une émission.', 'Sign up for castings or to attend a show.'), $shows, $T('Voir les émissions', 'See the shows')],
                    ['megaphone', $T('Publicité', 'Advertising'), $T('Spots, parrainages, publireportages : la régie vous conseille.', 'Spots, sponsorships, advertorials: our sales team will advise you.'), $advertise, $T('Voir les offres', 'See the offers')],
                ]]),
                B::contact($theme, [
                    'title' => $T('La chaîne', 'The channel'), 'intro' => $T('Accueil du public du lundi au vendredi, de 8 h à 18 h.', 'Front desk open Monday to Friday, 8 am to 6 pm.'),
                    'items' => [
                        ['map-pin', $T('Boulevard de la République, Abidjan, Côte d’Ivoire', 'Boulevard de la République, Abidjan, Côte d’Ivoire')],
                        ['phone', '+225 00 00 00 00 00', 'tel:+2250000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['monitor', $T('TNT canal 23 · satellite 112', 'Digital channel 23 · satellite 112')],
                    ],
                    'social' => ['facebook', 'youtube', 'tiktok', 'instagram'],
                    'subject' => true,
                    'map' => 'Abidjan, Plateau',
                ]),
            ]];

        $blog = [
            'base' => $u('info', 'news'),
            'title' => $T('L’info', 'News'),
            'layout' => ['share' => true, 'terms' => true],
            'interval' => 2,
            'categories' => ['programmes' => $T('Programmes', 'Programs'), 'actualite' => $T('Actualité', 'Current affairs'), 'sport' => $T('Sport', 'Sports'), 'coulisses' => $T('Coulisses', 'Behind the scenes')],
            'posts' => [
                ['key' => 'finale', 'slug' => $u('finale-talents-afrique', 'african-talents-final'), 'category' => 'programmes', 'tags' => [$T('Divertissement', 'Entertainment'), $T('Musique', 'Music')],
                    'title' => $T('Talents d’Afrique : la grande finale samedi', 'African Talents: the grand final on Saturday'),
                    'excerpt' => $T('Six finalistes, un jury de stars et vos votes en direct : rendez-vous samedi à 20 h 45.', 'Six finalists, a star jury and your live votes: Saturday at 8:45 pm.'),
                    'photo' => ['medias-tv-accueil', 'sparkles', $T('Chanteuse sur une scène éclairée', 'Singer on a lit stage')],
                    'heading' => $T('Comment voter', 'How to vote'),
                    'body' => [
                        $T('Après douze semaines de compétition, six artistes s’affrontent samedi soir pour la finale de Talents d’Afrique, en direct et en public.', 'After twelve weeks of competition, six artists face off on Saturday night in the African Talents final, live and in front of an audience.'),
                        $T('Le vote du public compte pour moitié dans le résultat final. Il est ouvert par SMS et sur l’appli pendant toute l’émission, jusqu’à l’annonce des résultats.', 'The public vote counts for half of the final result. It is open by text and on the app throughout the show, until the results are announced.'),
                        $T('Le ou la gagnante enregistrera son premier album avec un label partenaire. Rendez-vous samedi à 20 h 45.', 'The winner will record their first album with a partner label. See you Saturday at 8:45 pm.'),
                    ]],
                ['key' => 'heritiers', 'slug' => $u('nouvelle-serie-les-heritiers', 'new-series-the-heirs'), 'category' => 'programmes', 'tags' => [$T('Série', 'Series'), $T('Fiction', 'Drama')],
                    'title' => $T('Les Héritiers, la nouvelle série quotidienne', 'The Heirs, the new daily series'),
                    'excerpt' => $T('Une saga familiale entièrement tournée dans le pays, tous les jours à 14 h.', 'A family saga filmed entirely in the country, every day at 2 pm.'),
                    'photo' => ['medias-tv-une-2', 'film', $T('Tournage d’une série télévisée', 'Filming a TV series')],
                    'body' => [
                        $T('À la mort du patriarche, trois frères et sœurs se disputent l’entreprise familiale. Les Héritiers, notre nouvelle série quotidienne, arrive à l’antenne lundi à 14 h.', 'When the patriarch dies, three siblings fight over the family business. The Heirs, our new daily series, starts on Monday at 2 pm.'),
                        $T('Tournée pendant six mois avec une équipe et des comédiens locaux, la série compte 120 épisodes. Les épisodes sont disponibles en replay le soir même.', 'Filmed over six months with a local crew and cast, the series has 120 episodes. Episodes are available on replay the same evening.'),
                        $T('Retrouvez chaque semaine les coulisses du tournage dans notre rubrique « Coulisses ».', 'Find behind-the-scenes stories from the shoot every week in our “Behind the scenes” section.'),
                    ]],
                ['key' => 'journal', 'slug' => $u('nouveau-journal-20h', 'new-8pm-news'), 'category' => 'coulisses', 'tags' => [$T('Information', 'News'), $T('Plateau', 'Studio')],
                    'title' => $T('Le Journal de 20 h fait peau neuve', 'The 8 pm News gets a new look'),
                    'excerpt' => $T('Nouveau plateau, plus de reportages en région et une séquence consacrée à vos questions.', 'A new set, more regional reports and a segment dedicated to your questions.'),
                    'photo' => ['medias-tv-une-3', 'newspaper', $T('Présentatrice sur un plateau de journal télévisé', 'Presenter in a TV news studio')],
                    'heading' => $T('Vos questions à l’antenne', 'Your questions on air'),
                    'quote' => $T('« Nous voulons un journal plus proche des gens, de toutes les régions. » — [Prénom Nom], directrice de l’information', '“We want news that is closer to people, from every region.” — [First Last], head of news'),
                    'body' => [
                        $T('Dès lundi, le Journal de 20 h s’installe dans un nouveau plateau, plus lumineux, équipé d’un grand écran pour les reportages et les directs.', 'From Monday, the 8 pm News moves into a new, brighter set with a large screen for reports and live links.'),
                        $T('Chaque soir, une séquence répondra aux questions des téléspectateurs envoyées sur WhatsApp. Nos correspondants en région auront aussi plus de place.', 'Every evening, a segment will answer viewers’ questions sent on WhatsApp. Our regional correspondents will also get more airtime.'),
                        $T('Le journal reste diffusé en direct à la télévision et en ligne, puis disponible en replay.', 'The news is still broadcast live on TV and online, then available on replay.'),
                    ]],
                ['key' => 'match', 'slug' => $u('selection-nationale-en-direct', 'national-team-live'), 'category' => 'sport', 'tags' => ['Football', $T('En direct', 'Live')],
                    'title' => $T('Le match de la sélection nationale en direct', 'The national team’s match, live'),
                    'excerpt' => $T('Avant-match dès 17 h, coup d’envoi à 18 h, analyses et réactions après la rencontre.', 'Pre-match from 5 pm, kick-off at 6 pm, analysis and reactions after the game.'),
                    'photo' => ['medias-tv-actu-match', 'trophy', $T('Joueurs de football pendant un match', 'Football players during a match')],
                    'body' => [
                        $T('Mardi, la chaîne diffuse en direct le match de la sélection nationale. L’avant-match commence à 17 h avec nos consultants et un duplex depuis le stade.', 'On Tuesday, the channel broadcasts the national team’s match live. The pre-match starts at 5 pm with our pundits and a live link from the stadium.'),
                        $T('Après le coup de sifflet final, Sport Mag prolonge la soirée avec les réactions des joueurs, les temps forts et l’analyse tactique.', 'After the final whistle, Sport Mag continues the evening with player reactions, highlights and tactical analysis.'),
                        $T('En raison des droits, ce match ne sera pas diffusé en ligne : rendez-vous sur la TNT, le satellite et le câble.', 'Due to rights restrictions, this match will not stream online: watch it on digital terrestrial, satellite and cable.'),
                    ]],
                ['key' => 'reportage', 'slug' => $u('reportage-maraichers-fleuve', 'report-river-market-gardeners'), 'category' => 'actualite', 'tags' => [$T('Agriculture', 'Farming'), $T('Reportage', 'Report')],
                    'title' => $T('Terre d’ici chez les maraîchers du fleuve', 'Land of Ours visits the river market gardeners'),
                    'excerpt' => $T('Comment des coopératives de femmes nourrissent la ville avec des légumes cultivés au bord du fleuve.', 'How women’s cooperatives feed the city with vegetables grown by the river.'),
                    'photo' => ['medias-tv-actu-reportage', 'sprout', $T('Maraîchères dans un champ de légumes', 'Women market gardeners in a vegetable field')],
                    'body' => [
                        $T('Pour ce numéro, l’équipe de Terre d’ici a passé une semaine avec trois coopératives de maraîchères installées au bord du fleuve.', 'For this episode, the Land of Ours team spent a week with three women’s market-gardening cooperatives on the riverbank.'),
                        $T('Irrigation, semences, transport jusqu’aux marchés : le reportage montre les solutions qu’elles ont trouvées, et les difficultés qui restent.', 'Irrigation, seeds, transport to the markets: the report shows the solutions they have found, and the challenges that remain.'),
                        $T('Diffusion dimanche à 17 h 30, puis en replay sur l’appli et notre chaîne en ligne.', 'Airing Sunday at 5:30 pm, then on replay on the app and our online channel.'),
                    ]],
                ['key' => 'casting', 'slug' => $u('casting-saison-prochaine', 'casting-next-season'), 'category' => 'coulisses', 'tags' => [$T('Casting', 'Casting'), $T('Divertissement', 'Entertainment')],
                    'title' => $T('Casting : la prochaine saison de Talents d’Afrique', 'Casting: the next season of African Talents'),
                    'excerpt' => $T('Chanteurs, danseurs, humoristes : les inscriptions sont ouvertes dans cinq villes.', 'Singers, dancers, comedians: registration is open in five cities.'),
                    'photo' => ['medias-tv-actu-casting', 'mic', $T('Jeune artiste passant une audition', 'Young performer at an audition')],
                    'body' => [
                        $T('La prochaine saison de Talents d’Afrique se prépare. Les auditions auront lieu dans cinq villes du pays entre le [date] et le [date].', 'The next season of African Talents is in preparation. Auditions will take place in five cities between [date] and [date].'),
                        $T('Les candidats doivent avoir 16 ans au moins. Inscription gratuite sur l’appli ou à l’accueil de la chaîne, avec une courte vidéo de présentation.', 'Candidates must be at least 16. Free registration on the app or at the channel’s front desk, with a short introduction video.'),
                        $T('Les meilleurs talents rejoindront l’aventure à l’antenne dès [mois].', 'The best talent will join the show on air from [month].'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Direct', 'Live'), 'url' => $live],
                    ['label' => $T('Programmes', 'Schedule'), 'url' => $schedule],
                    ['label' => $T('Émissions', 'Shows'), 'url' => $shows],
                    ['label' => $T('L’info', 'News'), 'url' => $news],
                    ['label' => $T('La chaîne', 'About'), 'url' => $about],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Regarder en direct', 'Watch live'),
                'cta_url' => $live,
                'footer_text' => $T('Chaîne de télévision d’information et de divertissement, sur la TNT, le satellite, le câble et en ligne.', 'News and entertainment TV channel, on digital terrestrial, satellite, cable and online.'),
                'footer_links' => [
                    ['label' => $T('Regarder en direct', 'Watch live'), 'url' => $live],
                    ['label' => $T('Programmes', 'Schedule'), 'url' => $schedule],
                    ['label' => $T('Annonceurs', 'Advertise'), 'url' => $advertise],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
