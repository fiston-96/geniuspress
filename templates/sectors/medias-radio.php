<?php

/*
 * Pack « Médias et presse » — station de radio : 8 pages, en français et en anglais.
 * Le direct (bouton « Écouter en direct » vers le flux de la station, à remplacer),
 * les émissions, la grille des programmes, les animateurs, l'actualité de la radio,
 * les annonceurs (audience par tranche horaire) et le contact.
 * Les vidéos d'exemple sont des films libres de la Blender Foundation (CC BY), à remplacer.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Médias et presse', 'en' => 'Media and press'],
        'icon' => 'newspaper',
        'keywords' => ['média', 'medias', 'presse', 'journal', 'radio', 'télévision', 'podcast', 'journaliste', 'media', 'press', 'newspaper', 'television', 'journalist'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $live = $u('/direct', '/live');
        $shows = $u('/emissions', '/shows');
        $schedule = $u('/grille', '/schedule');
        $hosts = $u('/animateurs', '/hosts');
        $news = $u('/actualites', '/news');
        $advertise = $u('/annonceurs', '/advertise');
        $contact = '/contact';
        $post = fn (string $fr, string $en) => $news.'/'.$T($fr, $en);
        // Flux de la station (lecteur en ligne, appli…) : lien externe à remplacer.
        $stream = 'https://example.com/direct';
        $yt = fn (string $id) => 'https://www.youtube.com/watch?v='.$id;

        $listen = [$T('Écouter en direct', 'Listen live'), $stream];
        $showItems = [
            ['medias-radio-emission-matinale', 'sun', $T('La Matinale', 'The Morning Show'), $T('Infos, météo, revue de presse et invités : le réveil de la ville, du lundi au vendredi de 6 h à 9 h.', 'News, weather, press review and guests: the city wakes up, Monday to Friday, 6 to 9 am.'), $schedule],
            ['medias-radio-emission-debat', 'message-circle', $T('Le Grand Débat', 'The Big Debate'), $T('Chaque jour à midi, deux invités, une question de société et vos appels en direct.', 'Every day at noon, two guests, one social question and your calls on air.'), $schedule],
            ['medias-radio-emission-musique', 'music', $T('Rythmes d’Afrique', 'African Rhythms'), $T('Rumba, afrobeat, coupé-décalé : les sons qui font danser le continent, chaque soir.', 'Rumba, afrobeat, coupé-décalé: the sounds that make the continent dance, every evening.'), $schedule],
            ['medias-radio-emission-sport', 'trophy', $T('Stade en direct', 'Live from the Stadium'), $T('Les matchs du championnat commentés en direct, avant-match et réactions.', 'League matches commented live, with pre-match and reactions.'), $schedule],
            ['medias-radio-emission-jeunes', 'graduation-cap', $T('Génération Futur', 'Future Generation'), $T('L’émission des 15-25 ans : études, emploi, entrepreneuriat et culture.', 'The show for 15 to 25 year-olds: studies, jobs, entrepreneurship and culture.'), $schedule],
            ['medias-radio-emission-dedicaces', 'heart', $T('Dédicaces du dimanche', 'Sunday Dedications'), $T('Vos messages et vos chansons préférées pour ceux que vous aimez.', 'Your messages and favorite songs for the people you love.'), $schedule],
        ];
        $audience = ['title' => $T('La radio qui rassemble', 'The station that brings people together'), 'items' => [
            [850, ' k', $T('auditeurs chaque semaine', 'weekly listeners')],
            [24, ' h/24', $T('de programmes', 'of programming')],
            [3, '', $T('villes couvertes en FM', 'cities covered on FM')],
            [18, '', $T('ans à l’antenne', 'years on air')],
        ]];
        $cta = ['title' => $T('Faites entendre votre marque', 'Make your brand heard'), 'text' => $T('Spots, parrainages d’émissions, opérations spéciales : touchez des centaines de milliers d’auditeurs chaque semaine.', 'Spots, show sponsorships, special operations: reach hundreds of thousands of listeners every week.'),
            'buttons' => [[$T('Devenir annonceur', 'Advertise with us'), $advertise, 'accent'], [$T('Nous contacter', 'Contact us'), $contact, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Votre radio : infos, musique, débats et sport, en direct sur la FM et en ligne, 24 h/24.', 'Your radio station: news, music, debates and sport, live on FM and online, 24/7.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('98.5 FM · en ligne · sur l’appli', '98.5 FM · online · on the app'),
                    'title' => $T('La voix de votre ville, 24 heures sur 24', 'The voice of your city, 24 hours a day'),
                    'text' => $T('Infos locales, grands débats, musique africaine et sport en direct : écoutez-nous sur la FM, en ligne ou sur votre téléphone.', 'Local news, big debates, African music and live sport: listen on FM, online or on your phone.'),
                    'buttons' => [$listen, [$T('Grille des programmes', 'Program schedule'), $schedule]],
                    'photo' => ['medias-radio-accueil', 'mic', $T('Animatrice au micro dans un studio de radio', 'Host at the microphone in a radio studio')],
                ]),
                B::ticker($theme, ['label' => $T('À l’antenne', 'On air'), 'items' => [
                    [$T('6 h – 9 h : La Matinale, infos et invités', '6–9 am: The Morning Show, news and guests'), $schedule],
                    [$T('12 h : Le Grand Débat, appelez le +243 000 000 000', '12 pm: The Big Debate, call +243 000 000 000'), $schedule],
                    [$T('Samedi 16 h : le derby commenté en direct', 'Saturday 4 pm: the derby, live commentary'), $post('derby-en-direct', 'derby-live-on-air')],
                    [$T('Concours : gagnez vos places pour le festival', 'Contest: win tickets to the festival'), $post('concours-festival', 'festival-contest')],
                ]]),
                B::live($theme, [
                    'badge' => $T('En direct', 'Live now'),
                    'title' => $T('La Matinale, avec [Prénom Nom]', 'The Morning Show, with [First Last]'),
                    'text' => $T('Le journal de 7 h, la revue de presse, la météo et l’invité du jour. Réagissez en direct par téléphone ou sur WhatsApp.', 'The 7 am news, the press review, the weather and today’s guest. React live by phone or on WhatsApp.'),
                    'items' => [['wifi', $T('98.5 FM à Kinshasa · 101.2 FM à Matadi', '98.5 FM in Kinshasa · 101.2 FM in Matadi')], ['smartphone', $T('Sur l’appli et en ligne, partout dans le monde', 'On the app and online, anywhere in the world')], ['phone', $T('Antenne : +243 000 000 000', 'On-air line: +243 000 000 000')]],
                    'buttons' => [$listen, [$T('Voir la grille', 'See the schedule'), $schedule]],
                    'photo' => ['medias-radio-studio', 'mic', $T('Studio de radio en direct', 'Radio studio on air')],
                ]),
                B::cards($theme, ['eyebrow' => $T('Nos émissions', 'Our shows'), 'title' => $T('Les rendez-vous de la semaine', 'This week’s highlights'), 'more' => $T('Horaires', 'Times'), 'items' => array_slice($showItems, 0, 3)]),
                B::platforms($theme, ['title' => $T('Écoutez-nous où vous voulez', 'Listen wherever you are'), 'intro' => $T('Émissions en podcast, extraits vidéo et dédicaces sur nos réseaux.', 'Shows as podcasts, video clips and dedications on our networks.'), 'items' => [
                    ['spotify', 'Spotify'], ['youtube', 'YouTube'], ['soundcloud', 'SoundCloud'], ['whatsapp', 'WhatsApp'], ['facebook', 'Facebook'], ['tiktok', 'TikTok'],
                ]]),
                B::posts($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Les nouvelles de la radio', 'News from the station'), 'count' => 3, 'more' => $T('Lire la suite', 'Read more')]),
                B::testimonials($theme, ['eyebrow' => $T('Auditeurs', 'Listeners'), 'title' => $T('Ils nous écoutent chaque jour', 'They listen every day'), 'items' => [
                    [$T('La Matinale m’accompagne dans le bus tous les matins. Je sais tout avant d’arriver au bureau.', 'The Morning Show keeps me company on the bus every morning. I know everything before I get to the office.'), '[Prénom Nom]', $T('Auditrice à Kinshasa', 'Listener in Kinshasa')],
                    [$T('Le Grand Débat donne vraiment la parole aux gens. J’ai pu poser ma question en direct.', 'The Big Debate really gives people a voice. I got to ask my question live.'), '[Prénom Nom]', $T('Commerçant', 'Shopkeeper')],
                    [$T('Depuis l’étranger, je retrouve la musique et les nouvelles de chez moi grâce à l’appli.', 'From abroad, the app brings me music and news from home.'), '[Prénom Nom]', $T('Auditeur de la diaspora', 'Diaspora listener')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'live', 'slug' => $u('direct', 'live'), 'title' => $T('Direct', 'Live'),
            'description' => $T('Écoutez la radio en direct : FM, en ligne, sur l’appli. Ce qui passe à l’antenne aujourd’hui.', 'Listen live: FM, online, on the app. What’s on air today.'),
            'sections' => [
                B::live($theme, [
                    'tag' => 'h1', 'badge' => $T('En direct', 'Live now'),
                    'title' => $T('Écoutez la radio en direct', 'Listen to the station live'),
                    'text' => $T('Le lecteur s’ouvre dans un nouvel onglet : continuez à naviguer pendant l’écoute. Sur téléphone, l’appli consomme très peu de données.', 'The player opens in a new tab: keep browsing while you listen. On your phone, the app uses very little data.'),
                    'items' => [['wifi', '98.5 FM · 101.2 FM'], ['headphones', $T('Qualité standard et économie de données', 'Standard quality and data saver')], ['clock', $T('24 h/24, 7 j/7', '24/7')]],
                    'buttons' => [$listen, [$T('Télécharger l’appli', 'Get the app'), 'https://example.com/appli', 'outline-light']],
                    'photo' => ['medias-radio-direct', 'headphones', $T('Auditeur avec un casque', 'Listener wearing headphones')],
                ]),
                B::table($theme, ['eyebrow' => $T('Aujourd’hui', 'Today'), 'title' => $T('À l’antenne aujourd’hui', 'On air today'), 'rows' => [
                    [$T('Heure', 'Time'), $T('Émission', 'Show'), $T('Animation', 'Host')],
                    ['06:00', $T('La Matinale', 'The Morning Show'), '[Prénom Nom]'],
                    ['09:00', $T('Bonjour la ville', 'Hello City'), '[Prénom Nom]'],
                    ['12:00', $T('Le Grand Débat', 'The Big Debate'), '[Prénom Nom]'],
                    ['14:00', $T('Après-midi musique', 'Afternoon Music'), '[Prénom Nom]'],
                    ['17:00', $T('Génération Futur', 'Future Generation'), '[Prénom Nom]'],
                    ['19:00', $T('Le journal du soir', 'Evening News'), '[Prénom Nom]'],
                    ['20:00', $T('Rythmes d’Afrique', 'African Rhythms'), '[Prénom Nom]'],
                    ['23:00', $T('La nuit en musique', 'Music Through the Night'), $T('Programmation musicale', 'Music programming')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Comment nous écouter', 'How to listen'), 'title' => $T('Quatre façons de nous retrouver', 'Four ways to tune in'), 'cols' => 4, 'bg' => 'light', 'items' => [
                    ['wifi', 'FM', $T('98.5 à Kinshasa, 101.2 à Matadi, 94.7 à Boma.', '98.5 in Kinshasa, 101.2 in Matadi, 94.7 in Boma.')],
                    ['globe', $T('En ligne', 'Online'), $T('Sur ce site, depuis n’importe quel navigateur.', 'On this site, from any browser.')],
                    ['smartphone', $T('Sur l’appli', 'On the app'), $T('Direct, podcasts et alertes info sur Android et iPhone.', 'Live, podcasts and news alerts on Android and iPhone.')],
                    ['phone', $T('Par téléphone', 'By phone'), $T('Appelez le [numéro court] pour écouter sans Internet.', 'Call [short number] to listen without Internet.')],
                ]]),
                B::faq($theme, ['title' => $T('Un souci d’écoute ?', 'Trouble listening?'), 'intro' => $T('Les réponses aux questions les plus fréquentes.', 'Answers to the most common questions.'), 'buttons' => [[$T('Signaler un problème', 'Report a problem'), $contact, 'outline']], 'items' => [
                    [$T('Le direct ne démarre pas', 'The live stream won’t start'), $T('Vérifiez votre connexion, puis rechargez la page du lecteur. Sur téléphone, essayez l’appli.', 'Check your connection, then reload the player page. On a phone, try the app.')],
                    [$T('Je ne capte pas la FM dans mon quartier', 'I can’t get FM in my area'), $T('Écrivez-nous en précisant votre quartier : nous transmettons les signalements à notre équipe technique.', 'Write to us with your neighborhood: we pass reports on to our technical team.')],
                    [$T('Puis-je réécouter une émission ?', 'Can I listen to a show again?'), $T('Oui, nos émissions sont disponibles en podcast quelques heures après leur diffusion.', 'Yes, our shows are available as podcasts a few hours after they air.')],
                ]]),
            ]];

        $pages[] = ['key' => 'shows', 'slug' => $u('emissions', 'shows'), 'title' => $T('Émissions', 'Shows'),
            'description' => $T('Toutes nos émissions : infos, débats, musique, sport, jeunesse et dédicaces.', 'All our shows: news, debates, music, sport, youth and dedications.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Émissions', 'Shows'), 'title' => $T('Des programmes pour toute la famille', 'Programs for the whole family'), 'intro' => $T('Information, débat, musique et divertissement, du lever au coucher.', 'News, debate, music and entertainment, from sunrise to bedtime.')]),
                B::cards($theme, ['more' => $T('Horaires', 'Times'), 'items' => $showItems]),
                B::videos($theme, ['eyebrow' => $T('En vidéo', 'On video'), 'title' => $T('Nos émissions filmées', 'Our filmed shows'), 'intro' => $T('Le studio en images : retrouvez les moments forts de la semaine.', 'The studio on camera: the week’s highlights.'), 'bg' => 'light', 'items' => [
                    [$T('La Matinale — l’invité du jour', 'The Morning Show — today’s guest'), $yt('eRsGyueVLvQ'), '14:48'],
                    [$T('Le Grand Débat — l’emploi des jeunes', 'The Big Debate — youth jobs'), $yt('R6MlUcmOul8'), '12:14'],
                    [$T('Rythmes d’Afrique — session live', 'African Rhythms — live session'), $yt('WhWc3b3KhnY'), '7:44'],
                    [$T('Génération Futur — portrait d’entrepreneure', 'Future Generation — an entrepreneur’s story'), $yt('PVGeM40dABA'), '3:05'],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'schedule', 'slug' => $u('grille', 'schedule'), 'title' => $T('Grille des programmes', 'Program schedule'),
            'description' => $T('La grille complète de la semaine : heure par heure, du lundi au dimanche.', 'The full weekly schedule: hour by hour, Monday to Sunday.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Programmes', 'Programs'), 'title' => $T('Grille des programmes', 'Program schedule'), 'intro' => $T('Heure par heure, toute la semaine. Les horaires peuvent changer lors des événements en direct.', 'Hour by hour, all week. Times may change during live events.')]),
                B::table($theme, ['rows' => [
                    [$T('Heure', 'Time'), $T('Lundi – vendredi', 'Monday – Friday'), $T('Samedi', 'Saturday'), $T('Dimanche', 'Sunday')],
                    ['06:00', $T('La Matinale', 'The Morning Show'), $T('Le Réveil du week-end', 'Weekend Wake-up'), $T('Louanges et gospel', 'Praise and Gospel')],
                    ['09:00', $T('Bonjour la ville', 'Hello City'), $T('Marché et cuisine', 'Market and Cooking'), $T('Louanges et gospel', 'Praise and Gospel')],
                    ['12:00', $T('Le Grand Débat', 'The Big Debate'), $T('Le journal du midi', 'Midday News'), $T('Dédicaces du dimanche', 'Sunday Dedications')],
                    ['14:00', $T('Après-midi musique', 'Afternoon Music'), $T('Hit-parade', 'Top 40'), $T('Dédicaces du dimanche', 'Sunday Dedications')],
                    ['16:00', $T('Après-midi musique', 'Afternoon Music'), $T('Stade en direct', 'Live from the Stadium'), $T('Stade en direct', 'Live from the Stadium')],
                    ['17:00', $T('Génération Futur', 'Future Generation'), $T('Stade en direct', 'Live from the Stadium'), $T('Stade en direct', 'Live from the Stadium')],
                    ['19:00', $T('Le journal du soir', 'Evening News'), $T('Le journal du soir', 'Evening News'), $T('Le journal du soir', 'Evening News')],
                    ['20:00', $T('Rythmes d’Afrique', 'African Rhythms'), $T('Soirée rumba', 'Rumba Night'), $T('Le magazine de la semaine', 'Weekly Magazine')],
                    ['23:00', $T('La nuit en musique', 'Music Through the Night'), $T('La nuit en musique', 'Music Through the Night'), $T('La nuit en musique', 'Music Through the Night')],
                ], 'note' => $T('Flashs d’information toutes les heures de 6 h à 20 h.', 'News bulletins every hour from 6 am to 8 pm.')]),
                B::events($theme, ['eyebrow' => $T('Rendez-vous spéciaux', 'Special broadcasts'), 'title' => $T('À ne pas manquer ce mois-ci', 'Not to be missed this month'), 'icon' => 'mic', 'bg' => 'light', 'items' => [
                    [$T('Le derby en direct', 'The derby, live'), $T('Samedi 15, 16 h', 'Saturday 15, 4 pm'), $T('Commentaires depuis le stade', 'Commentary from the stadium')],
                    [$T('Concert anniversaire de la radio', 'Station anniversary concert'), $T('Vendredi 21, 19 h', 'Friday 21, 7 pm'), $T('En public et en direct', 'In front of an audience, live')],
                    [$T('Débat des candidats', 'Candidates’ debate'), $T('Mardi 25, 12 h', 'Tuesday 25, 12 pm'), $T('Édition spéciale de deux heures', 'Two-hour special edition')],
                    [$T('Journée de la femme entrepreneure', 'Women entrepreneurs’ day'), $T('Jeudi 27, toute la journée', 'Thursday 27, all day'), $T('Témoignages et conseils', 'Stories and advice')],
                ]]),
            ]];

        $pages[] = ['key' => 'hosts', 'slug' => $u('animateurs', 'hosts'), 'title' => $T('Animateurs', 'Hosts'),
            'description' => $T('Les voix de la radio : animateurs, journalistes et techniciens.', 'The voices of the station: hosts, journalists and technicians.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Les voix que vous aimez', 'The voices you love'), 'intro' => $T('Animateurs, journalistes et techniciens : ils font la radio chaque jour.', 'Hosts, journalists and technicians: they make the station every day.')]),
                B::team($theme, ['shape' => 'circle', 'items' => [
                    ['[Prénom Nom]', $T('La Matinale', 'The Morning Show'), 'medias-radio-equipe-1'],
                    ['[Prénom Nom]', $T('Le Grand Débat', 'The Big Debate'), 'medias-radio-equipe-2'],
                    ['[Prénom Nom]', $T('Rythmes d’Afrique', 'African Rhythms'), 'medias-radio-equipe-3'],
                    ['[Prénom Nom]', $T('Stade en direct', 'Live from the Stadium'), 'medias-radio-equipe-4'],
                    ['[Prénom Nom]', $T('Génération Futur', 'Future Generation'), 'medias-radio-equipe-5'],
                    ['[Prénom Nom]', $T('Rédactrice en chef', 'News editor'), 'medias-radio-equipe-6'],
                    ['[Prénom Nom]', $T('Dédicaces du dimanche', 'Sunday Dedications'), 'medias-radio-equipe-7'],
                    ['[Prénom Nom]', $T('Chef technique', 'Chief engineer'), 'medias-radio-equipe-8'],
                ]]),
                B::quote($theme, ['text' => $T('Une radio, c’est une famille : celle qui parle au micro et celle qui écoute.', 'A radio station is a family: the people at the microphone and the people listening.'), 'source' => $T('— [Prénom Nom], directeur de la station', '— [First Last], station manager'), 'bg' => 'light']),
                B::split($theme, [
                    'eyebrow' => $T('Recrutement', 'Careers'),
                    'title' => $T('Votre voix nous intéresse', 'We want to hear your voice'),
                    'html' => '<p>'.e($T('Nous accueillons des stagiaires en animation, en journalisme et en technique du son, et recherchons des correspondants dans les villes de la région.', 'We welcome interns in presenting, journalism and sound engineering, and look for correspondents in towns across the region.')).'</p>',
                    'bullets' => [$T('Stages de trois à six mois', 'Three- to six-month internships'), $T('Formation au micro et au montage', 'Microphone and editing training'), $T('Envoyez une démo de deux minutes', 'Send a two-minute demo')],
                    'buttons' => [[$T('Postuler', 'Apply'), $contact, 'outline']],
                    'photo' => ['medias-radio-regie', 'sliders-horizontal', $T('Technicien à la table de mixage', 'Engineer at the mixing desk')],
                    'reverse' => true,
                ]),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites', 'news'), 'title' => $T('Actualités', 'News'),
            'description' => $T('Les nouvelles de la radio : infos locales, musique, sport, concours et événements.', 'News from the station: local news, music, sport, contests and events.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Les nouvelles de la radio', 'News from the station'), 'intro' => $T('Infos locales, coulisses, concours et événements.', 'Local news, behind the scenes, contests and events.')]),
                B::terms($theme, ['bg' => 'light']),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
            ]];

        $pages[] = ['key' => 'advertise', 'slug' => $u('annonceurs', 'advertise'), 'title' => $T('Annonceurs', 'Advertise'),
            'description' => $T('Annoncez à la radio : spots, parrainages d’émissions et opérations spéciales. Audience et tarifs.', 'Advertise on the radio: spots, show sponsorships and special operations. Audience and rates.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Annonceurs', 'Advertisers'), 'title' => $T('Votre message, sur toutes les ondes', 'Your message, all over the airwaves'), 'intro' => $T('La radio reste le média le plus écouté : au marché, en voiture, au travail et à la maison.', 'Radio remains the most listened-to medium: at the market, in the car, at work and at home.')]),
                B::stats($theme, $audience + ['bg' => 'primary']),
                B::chart($theme, [
                    'eyebrow' => $T('Audience', 'Audience'),
                    'title' => $T('Choisissez le bon moment', 'Pick the right time slot'),
                    'html' => '<p>'.e($T('Part des auditeurs à l’écoute selon la tranche horaire, en semaine (enquête d’audience, [année]). Le matin et la fin d’après-midi sont les moments forts.', 'Share of listeners tuned in by time slot, on weekdays (audience survey, [year]). Mornings and late afternoons are peak times.')).'</p>',
                    'bullets' => [$T('Ciblage par émission', 'Targeting by show'), $T('Spots produits par notre studio', 'Spots produced by our studio'), $T('Bilan de diffusion après campagne', 'Broadcast report after each campaign')],
                    'chart' => ['kind' => 'bar', 'title' => $T('Auditeurs à l’écoute (%)', 'Listeners tuned in (%)'), 'suffix' => ' %', 'rows' => [
                        ['6 h – 9 h', 38], ['9 h – 12 h', 22], ['12 h – 14 h', 27], ['14 h – 17 h', 18], ['17 h – 20 h', 31], ['20 h – 23 h', 16],
                    ]],
                ]),
                B::pricing($theme, ['eyebrow' => $T('Offres', 'Packages'), 'title' => $T('Nos formules publicitaires', 'Our advertising packages'), 'intro' => $T('Tarifs indicatifs hors taxes, production du spot comprise.', 'Indicative rates before tax, spot production included.'), 'url' => $contact, 'bg' => 'light', 'items' => [
                    [$T('Spot 30 secondes', '30-second spot'), '25', '$', $T('/ passage', '/ airing'), $T('Votre spot diffusé dans la tranche de votre choix.', 'Your spot aired in the slot of your choice.'), [$T('Production du spot incluse', 'Spot production included'), $T('Choix de la tranche horaire', 'Choice of time slot'), $T('Bilan de diffusion', 'Broadcast report'), '-'.$T('Mention à l’antenne', 'On-air mention')], $T('Réserver', 'Book')],
                    [$T('Parrainage d’émission', 'Show sponsorship'), '600', '$', $T('/ mois', '/ month'), $T('Votre marque associée à une émission phare.', 'Your brand tied to a flagship show.'), [$T('« Cette émission vous est offerte par… »', '“This show is brought to you by…”'), $T('2 spots par émission', '2 spots per show'), $T('Logo sur nos réseaux', 'Logo on our networks'), $T('Bilan mensuel', 'Monthly report')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus efficace', 'Most effective')],
                    [$T('Opération spéciale', 'Special operation'), '1500', '$', $T('/ opération', '/ operation'), $T('Direct depuis votre point de vente ou votre événement.', 'Live broadcast from your store or event.'), [$T('Animation en direct sur place', 'Live presenting on site'), $T('Jeux et cadeaux pour les auditeurs', 'Games and prizes for listeners'), $T('Annonces une semaine avant', 'Announcements a week before'), $T('Photos et vidéos sur nos réseaux', 'Photos and videos on our networks')], $T('Nous contacter', 'Contact us')],
                ]]),
                B::cta($theme, ['title' => $T('Parlons de votre campagne', 'Let’s talk about your campaign'), 'text' => $T('Notre régie publicitaire vous conseille et vous envoie un plan de diffusion sous 48 heures.', 'Our sales team will advise you and send a broadcast plan within 48 hours.'), 'buttons' => [[$T('Contacter la régie', 'Contact sales'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez la radio : antenne, dédicaces, rédaction, publicité.', 'Contact the station: on-air line, dedications, newsroom, advertising.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Restons en contact', 'Let’s stay in touch'), 'intro' => $T('Une dédicace, une information, un projet publicitaire ? Écrivez-nous.', 'A dedication, a news tip, an advertising project? Write to us.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['phone', $T('Ligne de l’antenne', 'On-air line'), $T('Pour intervenir en direct pendant les émissions : +243 000 000 000.', 'To speak live during shows: +243 000 000 000.')],
                    ['heart', $T('Dédicaces', 'Dedications'), $T('Envoyez votre message sur WhatsApp avant samedi soir pour le dimanche.', 'Send your message on WhatsApp before Saturday night for Sunday.')],
                    ['megaphone', $T('Publicité', 'Advertising'), $T('Spots, parrainages, opérations spéciales : la régie vous répond.', 'Spots, sponsorships, special operations: our sales team will answer.'), $advertise, $T('Voir les offres', 'See the offers')],
                ]]),
                B::contact($theme, [
                    'title' => $T('La station', 'The station'), 'intro' => $T('Visite des studios sur rendez-vous pour les écoles et les associations.', 'Studio tours by appointment for schools and associations.'),
                    'items' => [
                        ['map-pin', $T('Avenue de la Libération, Kinshasa, RDC', 'Avenue de la Libération, Kinshasa, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['wifi', '98.5 FM · 101.2 FM'],
                    ],
                    'social' => ['facebook', 'whatsapp', 'youtube', 'tiktok'],
                    'subject' => true,
                    'map' => 'Kinshasa, Lingwala',
                ]),
            ]];

        $blog = [
            'base' => $u('actualites', 'news'),
            'title' => $T('Actualités', 'News'),
            'layout' => ['share' => true, 'terms' => true],
            'interval' => 2,
            'categories' => ['infos' => $T('Infos locales', 'Local news'), 'musique' => $T('Musique', 'Music'), 'sport' => $T('Sport', 'Sports'), 'coulisses' => $T('Coulisses', 'Behind the scenes'), 'evenements' => $T('Événements', 'Events')],
            'posts' => [
                ['key' => 'derby', 'slug' => $u('derby-en-direct', 'derby-live-on-air'), 'category' => 'sport', 'tags' => ['Football', $T('En direct', 'Live')],
                    'title' => $T('Le derby commenté en direct samedi', 'The derby, live on air on Saturday'),
                    'excerpt' => $T('Avant-match dès 15 h, commentaires depuis le stade et réactions des joueurs après la rencontre.', 'Pre-match from 3 pm, commentary from the stadium and player reactions after the game.'),
                    'photo' => ['medias-radio-actu-derby', 'trophy', $T('Supporters dans un stade de football', 'Fans in a football stadium')],
                    'body' => [
                        $T('Samedi, l’équipe de Stade en direct s’installe en tribune de presse pour le derby. L’avant-match commence à 15 h avec les compositions et l’avis de nos consultants.', 'On Saturday, the Live from the Stadium team sets up in the press box for the derby. The pre-match starts at 3 pm with the line-ups and our pundits’ views.'),
                        $T('Coup d’envoi à 16 h : suivez chaque action en direct sur la FM, en ligne et sur l’appli. Après le coup de sifflet final, place aux réactions des joueurs et des entraîneurs.', 'Kick-off at 4 pm: follow every move live on FM, online and on the app. After the final whistle, hear reactions from players and coaches.'),
                        $T('Envoyez-nous vos pronostics sur WhatsApp : les meilleurs seront lus à l’antenne.', 'Send us your predictions on WhatsApp: the best ones will be read on air.'),
                    ]],
                ['key' => 'concours', 'slug' => $u('concours-festival', 'festival-contest'), 'category' => 'evenements', 'tags' => [$T('Concours', 'Contest'), 'Festival'],
                    'title' => $T('Concours : gagnez vos places pour le festival', 'Contest: win tickets to the festival'),
                    'excerpt' => $T('Cinquante places à gagner tout au long de la semaine : écoutez La Matinale et répondez à la question du jour.', 'Fifty tickets to win all week: listen to The Morning Show and answer the question of the day.'),
                    'photo' => ['medias-radio-actu-concours', 'gift', $T('Public enthousiaste à un concert', 'Excited crowd at a concert')],
                    'heading' => $T('Comment participer', 'How to enter'),
                    'body' => [
                        $T('Toute la semaine, la radio vous offre des places pour le festival des musiques urbaines. Chaque matin, une question est posée à l’antenne pendant La Matinale.', 'All week, the station is giving away tickets to the urban music festival. Every morning, a question is asked on air during The Morning Show.'),
                        $T('Envoyez votre réponse par SMS ou WhatsApp avec votre nom et votre quartier. Les gagnants sont tirés au sort et annoncés à midi.', 'Send your answer by text or WhatsApp with your name and neighborhood. Winners are drawn at random and announced at noon.'),
                        $T('Règlement complet disponible à l’accueil de la station. Un seul gain par personne.', 'Full rules available at the station’s front desk. One prize per person.'),
                    ]],
                ['key' => 'anniversaire', 'slug' => $u('la-radio-fete-ses-18-ans', 'station-turns-18'), 'category' => 'coulisses', 'tags' => [$T('Anniversaire', 'Anniversary')],
                    'title' => $T('La radio fête ses 18 ans', 'The station turns 18'),
                    'excerpt' => $T('Dix-huit ans d’antenne, des milliers d’émissions et un grand concert gratuit pour dire merci aux auditeurs.', 'Eighteen years on air, thousands of shows and a big free concert to thank our listeners.'),
                    'photo' => ['medias-radio-actu-anniversaire', 'party-popper', $T('Équipe de la radio réunie au studio', 'Station team gathered in the studio')],
                    'heading' => $T('Un concert pour dire merci', 'A concert to say thank you'),
                    'quote' => $T('« Le premier jour, nous étions trois dans un studio de neuf mètres carrés. » — [Prénom Nom], fondateur', '“On day one, there were three of us in a nine-square-meter studio.” — [First Last], founder'),
                    'body' => [
                        $T('Il y a dix-huit ans, la radio émettait pour la première fois depuis un petit studio du centre-ville. Aujourd’hui, elle couvre trois villes en FM et compte des auditeurs dans le monde entier grâce à l’appli.', 'Eighteen years ago, the station went on air for the first time from a small downtown studio. Today it covers three cities on FM and has listeners worldwide thanks to the app.'),
                        $T('Pour fêter cet anniversaire, nous vous invitons à un grand concert gratuit le [date], avec les artistes qui ont marqué l’histoire de la station. Le concert sera diffusé en direct.', 'To celebrate, we invite you to a big free concert on [date], with the artists who shaped the station’s history. The concert will be broadcast live.'),
                        $T('Merci à vous, auditrices et auditeurs, de nous faire confiance chaque jour.', 'Thank you, our listeners, for trusting us every day.'),
                    ]],
                ['key' => 'routes', 'slug' => $u('travaux-routes-quartiers', 'road-works-neighborhoods'), 'category' => 'infos', 'tags' => [$T('Ville', 'City'), $T('Circulation', 'Traffic')],
                    'title' => $T('Travaux : les routes fermées cette semaine', 'Road works: the roads closed this week'),
                    'excerpt' => $T('Trois axes fermés pour réhabilitation : itinéraires conseillés et horaires des travaux.', 'Three roads closed for repairs: suggested routes and work hours.'),
                    'photo' => ['medias-radio-actu-routes', 'construction', $T('Chantier de réfection d’une route', 'Road repair site')],
                    'body' => [
                        $T('La mairie engage cette semaine la réhabilitation de trois axes du centre-ville. Les travaux ont lieu de 7 h à 18 h et devraient durer [durée].', 'The city is starting repairs on three downtown roads this week. Works run from 7 am to 6 pm and should last [duration].'),
                        $T('Des itinéraires de déviation sont signalés. Nos journalistes font le point sur la circulation dans chaque flash info, de 6 h à 20 h.', 'Detours are signposted. Our journalists give traffic updates in every news bulletin, from 6 am to 8 pm.'),
                        $T('Coincé dans un embouteillage ? Signalez-le sur WhatsApp : vos messages aident les autres auditeurs.', 'Stuck in traffic? Report it on WhatsApp: your messages help other listeners.'),
                    ]],
                ['key' => 'talents', 'slug' => $u('nouveaux-talents-rythmes-afrique', 'new-talent-african-rhythms'), 'category' => 'musique', 'tags' => [$T('Nouveaux talents', 'New talent'), 'Rumba'],
                    'title' => $T('Rythmes d’Afrique ouvre son studio aux nouveaux talents', 'African Rhythms opens its studio to new talent'),
                    'excerpt' => $T('Chaque vendredi, un jeune artiste vient jouer en direct dans le studio. Envoyez votre maquette.', 'Every Friday, a young artist plays live in the studio. Send us your demo.'),
                    'photo' => ['medias-radio-actu-talents', 'music', $T('Jeune chanteur en studio d’enregistrement', 'Young singer in a recording studio')],
                    'body' => [
                        $T('À partir de ce mois, Rythmes d’Afrique invite chaque vendredi un jeune artiste à jouer deux titres en direct, suivis d’une interview.', 'From this month, African Rhythms will invite a young artist every Friday to play two songs live, followed by an interview.'),
                        $T('Pour participer, envoyez une maquette de deux titres et une courte présentation. Notre équipe musicale écoute toutes les propositions.', 'To take part, send a two-song demo and a short bio. Our music team listens to every submission.'),
                        $T('Les sessions sont filmées et publiées sur notre chaîne vidéo la semaine suivante.', 'Sessions are filmed and posted on our video channel the following week.'),
                    ]],
                ['key' => 'appli', 'slug' => $u('nouvelle-appli', 'new-app'), 'category' => 'coulisses', 'tags' => [$T('Appli', 'App'), $T('Podcasts', 'Podcasts')],
                    'title' => $T('La nouvelle appli est disponible', 'The new app is available'),
                    'excerpt' => $T('Direct en meilleure qualité, podcasts, alertes info et mode économie de données.', 'Better-quality live stream, podcasts, news alerts and a data saver mode.'),
                    'photo' => ['medias-radio-actu-appli', 'smartphone', $T('Personne écoutant la radio sur son téléphone', 'Person listening to the radio on a phone')],
                    'body' => [
                        $T('Notre nouvelle appli est disponible sur Android et iPhone. Elle permet d’écouter le direct, de réécouter les émissions et de recevoir les alertes info.', 'Our new app is available on Android and iPhone. It lets you listen live, catch up on shows and get news alerts.'),
                        $T('Le mode économie de données réduit fortement la consommation : idéal pour écouter toute la journée sans épuiser son forfait.', 'Data saver mode greatly reduces usage: ideal for listening all day without using up your plan.'),
                        $T('Un souci, une suggestion ? Écrivez-nous depuis la rubrique « Contact » de l’appli.', 'A problem or suggestion? Write to us from the app’s “Contact” section.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Direct', 'Live'), 'url' => $live],
                    ['label' => $T('Émissions', 'Shows'), 'url' => $shows],
                    ['label' => $T('Grille', 'Schedule'), 'url' => $schedule],
                    ['label' => $T('Animateurs', 'Hosts'), 'url' => $hosts],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Écouter en direct', 'Listen live'),
                'cta_url' => $live,
                'footer_text' => $T('Radio locale d’information et de divertissement, en direct sur la FM, en ligne et sur l’appli.', 'Local news and entertainment radio, live on FM, online and on the app.'),
                'footer_links' => [
                    ['label' => $T('Écouter en direct', 'Listen live'), 'url' => $live],
                    ['label' => $T('Grille des programmes', 'Program schedule'), 'url' => $schedule],
                    ['label' => $T('Annonceurs', 'Advertise'), 'url' => $advertise],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
