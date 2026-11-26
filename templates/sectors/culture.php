<?php

/*
 * Pack « Centre culturel et théâtre » (secteur Sport et culture) : 8 pages,
 * en français et en anglais. Théâtre, centre culturel, scène de musiques ou
 * association culturelle : programmation et billetterie, ateliers, expositions,
 * résidences d'artistes, adhésion et mécénat, location de salles.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du lieu). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Sport et culture', 'en' => 'Sports and culture'],
        'icon' => 'trophy',
        'keywords' => ['centre culturel', 'théâtre', 'salle de spectacle', 'association culturelle', 'programmation', 'billetterie', 'spectacle', 'concert', 'exposition', 'atelier', 'résidence d’artistes', 'mécénat', 'arts center', 'theater', 'performing arts', 'venue', 'box office', 'exhibition', 'workshops', 'artist residency', 'cultural center'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $program = $u('/programmation', '/whats-on');
        $classes = $u('/ateliers-et-cours', '/classes-and-workshops');
        $exhibitions = $u('/expositions-residences', '/exhibitions-residencies');
        $support = $u('/adhesion-mecenat', '/membership-giving');
        $hire = $u('/location-de-salles', '/venue-hire');
        $journal = $u('/journal', '/journal');
        $contact = '/contact';
        $tickets = $program.'#billetterie';
        $cur = $T('€', '$');

        $highlights = [
            ['culture-spectacle-theatre', 'drama', $T('« La Traversée » · théâtre', '“The Crossing” · theater'), $T('Une création de la compagnie en résidence, sur l’exil et la mémoire. Dès 12 ans, 1 h 30.', 'A new play by our resident company about exile and memory. Ages 12 and up, 1 h 30.')],
            ['culture-spectacle-concert', 'music', $T('Nuit des musiques du monde', 'World Music Night'), $T('Trois groupes, une scène, et le parterre transformé en piste de danse.', 'Three bands, one stage, and the stalls turned into a dance floor.')],
            ['culture-spectacle-danse', 'sparkles', $T('« Lignes » · danse contemporaine', '“Lines” · contemporary dance'), $T('Six danseurs, une partition électronique jouée en direct. 1 h 10.', 'Six dancers, an electronic score played live. 1 h 10.')],
        ];
        $cta = ['title' => $T('Ne manquez aucun spectacle', 'Never miss a show'), 'text' => $T('Abonnez-vous à la lettre du lieu : programmation, ouvertures de billetterie et invitations aux répétitions publiques.', 'Sign up for our newsletter: new shows, box office openings and invitations to open rehearsals.'),
            'buttons' => [[$T('Réserver mes places', 'Book tickets'), $tickets, 'accent'], [$T('Devenir adhérent', 'Become a member'), $support, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Théâtre et centre culturel : spectacles, concerts, expositions, ateliers pour tous les âges et résidences d’artistes. Programmation et billetterie.', 'Theater and arts center: shows, concerts, exhibitions, classes for all ages and artist residencies. Program and box office.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Saison [année – année]', 'Season [year – year]'),
                    'title' => $T('Un lieu vivant, ouvert à toutes les cultures', 'A lively venue, open to every culture'),
                    'text' => $T('Théâtre, musique, danse, expositions et ateliers : plus de 120 rendez-vous par saison, des artistes en résidence et des tarifs accessibles à tous.', 'Theater, music, dance, exhibitions and workshops: over 120 events a season, artists in residence and prices everyone can afford.'),
                    'buttons' => [[$T('Voir la programmation', 'See what’s on'), $program], [$T('Ateliers et cours', 'Classes and workshops'), $classes]],
                    'photo' => ['culture-accueil', 'drama', $T('Salle de spectacle éclairée avec le public assis face à la scène', 'Lit auditorium with the audience seated facing the stage')],
                ]),
                B::cards($theme, ['eyebrow' => $T('À l’affiche', 'Now showing'), 'title' => $T('Les temps forts du moment', 'Highlights this month'), 'more' => $T('Réserver', 'Book'), 'items' => array_map(fn ($h) => [...$h, $tickets], $highlights)]),
                B::features($theme, ['eyebrow' => $T('Le lieu', 'The venue'), 'title' => $T('Venir voir, venir faire', 'Come to watch, come to create'), 'cols' => 4, 'bg' => 'light', 'items' => [
                    ['ticket', $T('Spectacles', 'Shows'), $T('Théâtre, concerts, danse, cirque et jeune public.', 'Theater, concerts, dance, circus and family shows.'), $program, $T('La programmation', 'What’s on')],
                    ['palette', $T('Ateliers', 'Classes'), $T('Pratiques artistiques pour enfants, ados et adultes.', 'Arts classes for children, teens and adults.'), $classes, $T('Les ateliers', 'See classes')],
                    ['frame', $T('Expositions', 'Exhibitions'), $T('Arts visuels et photographie, entrée libre.', 'Visual arts and photography, free entry.'), $exhibitions, $T('Les expositions', 'Exhibitions')],
                    ['armchair', $T('Location', 'Venue hire'), $T('Salles pour vos conférences, séminaires et soirées.', 'Rooms for your conferences, seminars and events.'), $hire, $T('Louer une salle', 'Hire a room')],
                ]]),
                B::stats($theme, ['title' => $T('Une saison en chiffres', 'A season in numbers'), 'items' => [
                    [120, '+', $T('représentations et concerts', 'performances and concerts')],
                    [45000, '', $T('spectateurs', 'audience members')],
                    [800, '', $T('élèves dans nos ateliers', 'students in our classes')],
                    [6, '', $T('compagnies en résidence', 'resident companies')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Notre projet', 'Our mission'),
                    'title' => $T('La culture se partage, elle ne se réserve pas', 'Culture is for sharing, not for the few'),
                    'html' => '<p>'.e($T('Association à but non lucratif, le lieu accueille artistes confirmés et talents émergents, et travaille toute l’année avec les écoles, les associations de quartier et les structures sociales. Un tiers des places est proposé à tarif réduit.', 'As a non-profit association, the venue hosts established artists and emerging talent, and works year-round with schools, neighborhood groups and social organizations. A third of all seats are offered at reduced prices.')).'</p>',
                    'bullets' => [$T('Tarif solidaire à 5 € pour les minima sociaux', '$5 solidarity rate for people on low incomes'), $T('Salle accessible et séances adaptées', 'Accessible venue and relaxed performances'), $T('Rencontres avec les artistes après le spectacle', 'Post-show talks with the artists')],
                    'buttons' => [[$T('Nous soutenir', 'Support us'), $support, 'outline']],
                    'photo' => ['culture-lieu', 'landmark', $T('Façade d’un théâtre en soirée avec le public à l’entrée', 'Theater facade in the evening with the audience at the entrance')],
                    'reverse' => true,
                ]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Un théâtre, c’est d’abord une place publique avec un toit : on y vient pour voir, pour débattre et pour se rencontrer.', 'A theater is first and foremost a public square with a roof: people come to watch, to debate and to meet.'), 'source' => $T('— [Prénom Nom], directrice du lieu', '— [First Last], artistic director')]),
                B::posts($theme, ['eyebrow' => $T('Journal', 'Journal'), 'title' => $T('Coulisses et entretiens', 'Backstage and interviews'), 'source' => 'latest', 'count' => 3, 'more' => $T('Lire', 'Read')]),
                B::cta($theme, $cta),
            ]];

        // Tarifs de la billetterie, avec l’ancre « billetterie » visée par les boutons « Réserver ».
        $ticketing = B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Billetterie', 'Box office'), 'title' => $T('Tarifs et abonnements', 'Prices and season passes'), 'intro' => $T('Tarif réduit : moins de 26 ans, demandeurs d’emploi, plus de 65 ans et personnes en situation de handicap.', 'Reduced price: under 26, job seekers, over 65 and people with disabilities.'), 'items' => [
            [$T('À l’unité', 'Single ticket'), '22', $cur, $T('plein tarif', 'full price'), $T('Une place pour le spectacle de votre choix.', 'One seat for the show of your choice.'), [$T('Tarif réduit : 15 €', 'Reduced price: $17'), $T('Tarif solidaire : 5 €', 'Solidarity rate: $5'), $T('Jeune public : 6 €', 'Family shows: $7'), '-'.$T('Places prioritaires', 'Priority booking')], $T('Réserver', 'Book')],
            [$T('Pass 4 spectacles', '4-show pass'), '64', $cur, $T('soit 16 € la place', '$18 per show'), $T('Composez votre parcours dans la saison.', 'Build your own season.'), [$T('Quatre spectacles au choix', 'Any four shows'), $T('Places réservées avant l’ouverture', 'Booking before general sale'), $T('Échange gratuit jusqu’à 48 h avant', 'Free exchange up to 48 h before'), $T('– 10 % au café du théâtre', '10% off at the theater café')], $T('Choisir le pass', 'Choose the pass'), true, $T('Le plus choisi', 'Most popular')],
            [$T('Pass saison', 'Season pass'), '150', $cur, $T('/ saison', '/ season'), $T('Tous les spectacles, toute la saison.', 'Every show, all season long.'), [$T('Accès à tous les spectacles', 'Access to every show'), $T('Invitations aux répétitions publiques', 'Invitations to open rehearsals'), $T('Une place offerte pour un proche', 'One free ticket for a guest'), $T('Adhésion à l’association incluse', 'Association membership included')], $T('M’abonner', 'Subscribe')],
        ]]);
        $ticketing['settings']['css_id'] = 'billetterie';

        $pages[] = ['key' => 'program', 'slug' => $u('programmation', 'whats-on'), 'title' => $T('Programmation et billetterie', 'What’s on and tickets'),
            'description' => $T('Programmation de la saison : théâtre, concerts, danse, jeune public. Tarifs, abonnements, horaires de la billetterie et accessibilité.', 'This season’s program: theater, concerts, dance, family shows. Prices, season passes, box office hours and accessibility.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Programmation', 'What’s on'), 'title' => $T('La saison en un coup d’œil', 'The season at a glance'), 'intro' => $T('Réservez en ligne, par téléphone ou au guichet. Les places non retirées sont remises en vente dix minutes avant le début.', 'Book online, by phone or at the box office. Uncollected tickets are released ten minutes before curtain.')]),
                M::table($theme, ['eyebrow' => $T('Ce mois-ci', 'This month'), 'title' => $T('Agenda des spectacles', 'Performance calendar'), 'rows' => [
                    [$T('Date', 'Date'), $T('Spectacle', 'Show'), $T('Genre', 'Genre'), $T('Salle', 'Venue'), $T('Tarif', 'Price')],
                    [$T('Ven. 3 · 20 h', 'Fri 3 · 8 pm'), $T('La Traversée', 'The Crossing'), $T('Théâtre', 'Theater'), $T('Grande salle', 'Main hall'), $T('12 – 22 €', '$14 – 25')],
                    [$T('Sam. 4 · 20 h 30', 'Sat 4 · 8:30 pm'), $T('Nuit des musiques du monde', 'World Music Night'), $T('Concert', 'Concert'), $T('Grande salle', 'Main hall'), $T('15 – 25 €', '$17 – 28')],
                    [$T('Mer. 8 · 15 h', 'Wed 8 · 3 pm'), $T('Le Jardin des sons', 'The Sound Garden'), $T('Jeune public, dès 4 ans', 'Family, ages 4+'), $T('Studio', 'Studio'), $T('6 €', '$7')],
                    [$T('Ven. 10 · 20 h', 'Fri 10 · 8 pm'), $T('Lignes', 'Lines'), $T('Danse', 'Dance'), $T('Grande salle', 'Main hall'), $T('12 – 22 €', '$14 – 25')],
                    [$T('Jeu. 16 · 19 h', 'Thu 16 · 7 pm'), $T('Scène ouverte', 'Open stage'), $T('Talents émergents', 'Emerging artists'), $T('Café du théâtre', 'Theater café'), $T('Entrée libre', 'Free')],
                    [$T('Sam. 18 · 20 h', 'Sat 18 · 8 pm'), $T('Cabaret des étoiles', 'Starlight Cabaret'), $T('Cirque', 'Circus'), $T('Grande salle', 'Main hall'), $T('12 – 22 €', '$14 – 25')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('À ne pas manquer', 'Don’t miss'), 'title' => $T('Les temps forts', 'Highlights'), 'bg' => 'light', 'items' => $highlights]),
                $ticketing,
                B::schedule($theme, ['eyebrow' => $T('Infos pratiques', 'Practical info'), 'title' => $T('Venir au spectacle', 'Coming to a show'), 'bg' => 'white', 'groups' => [
                    [$T('Billetterie', 'Box office'), [
                        ['clock', $T('Mardi – samedi, 13 h – 19 h, et une heure avant chaque spectacle', 'Tuesday – Saturday, 1 – 7 pm, and one hour before each show')],
                        ['phone', $T('Réservations par téléphone aux mêmes horaires', 'Phone bookings during the same hours')],
                        ['credit-card', $T('Carte bancaire, espèces, paiement mobile, chèques culture', 'Card, cash, mobile payment, culture vouchers')],
                    ]],
                    [$T('Accès et accessibilité', 'Access and accessibility'), [
                        ['bus', $T('Bus et tramway : arrêt [Nom de l’arrêt]', 'Bus and tram: [Stop name] stop')],
                        ['accessibility', $T('Salles accessibles en fauteuil, places réservées', 'Wheelchair-accessible halls, reserved seats')],
                        ['headphones', $T('Boucle magnétique et séances adaptées', 'Hearing loop and relaxed performances')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions sur la billetterie', 'Box office questions'), 'bg' => 'light', 'items' => [
                    [$T('Puis-je échanger ou me faire rembourser un billet ?', 'Can I exchange or get a refund on a ticket?'), $T('Les billets sont échangeables jusqu’à 48 heures avant la représentation, contre un autre spectacle de la saison. Ils sont remboursés en cas d’annulation de notre fait.', 'Tickets can be exchanged for another show this season up to 48 hours before the performance. They are refunded if we cancel.')],
                    [$T('Les places sont-elles numérotées ?', 'Are seats numbered?'), $T('Oui dans la grande salle ; le placement est libre au studio et au café du théâtre.', 'Yes in the main hall; seating is unreserved in the studio and the theater café.')],
                    [$T('Les retardataires peuvent-ils entrer ?', 'Can latecomers get in?'), $T('Par respect pour les artistes et le public, l’accès en salle peut être refusé une fois le spectacle commencé.', 'Out of respect for the artists and the audience, entry may be refused once the show has started.')],
                    [$T('Proposez-vous des tarifs de groupe ?', 'Do you offer group rates?'), $T('Oui, dès dix personnes, ainsi qu’un accompagnement pour les écoles et les associations.', 'Yes, from ten people, along with support for schools and community groups.')],
                ]]),
            ]];

        $pages[] = ['key' => 'classes', 'slug' => $u('ateliers-et-cours', 'classes-and-workshops'), 'title' => $T('Ateliers et cours', 'Classes and workshops'),
            'description' => $T('Ateliers de théâtre, danse, musique, arts plastiques, photographie et écriture pour enfants, ados et adultes. Horaires, tarifs et inscription.', 'Theater, dance, music, visual arts, photography and writing classes for children, teens and adults. Times, prices and enrollment.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Ateliers', 'Classes'), 'title' => $T('Pratiquer, à tout âge', 'Make art, at any age'), 'intro' => $T('Des ateliers à l’année encadrés par des artistes professionnels, de 4 à 99 ans.', 'Year-round classes led by professional artists, for ages 4 to 99.')]),
                B::features($theme, ['items' => [
                    ['drama', $T('Théâtre', 'Theater'), $T('Improvisation, jeu et mise en scène, avec un spectacle en fin d’année.', 'Improvisation, acting and directing, with an end-of-year show.')],
                    ['sparkles', $T('Danse', 'Dance'), $T('Éveil, contemporain, danses urbaines et danses du monde.', 'Creative movement, contemporary, street and world dance.')],
                    ['music', $T('Musique', 'Music'), $T('Chant, percussions, guitare et pratique en groupe.', 'Singing, percussion, guitar and ensemble playing.')],
                    ['paintbrush', $T('Arts plastiques', 'Visual arts'), $T('Dessin, peinture, modelage et techniques mixtes.', 'Drawing, painting, sculpture and mixed media.')],
                    ['camera', $T('Photographie', 'Photography'), $T('Prise de vue, lumière, retouche et exposition collective.', 'Shooting, lighting, editing and a group exhibition.')],
                    ['pen-tool', $T('Écriture', 'Creative writing'), $T('Ateliers d’écriture, de conte et de slam.', 'Writing, storytelling and spoken-word workshops.')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Horaires et tarifs', 'Times and prices'), 'title' => $T('Les ateliers de l’année', 'Year-round classes'), 'bg' => 'light', 'note' => $T('Tarifs annuels, adhésion comprise. Paiement possible en trois fois, quotient familial appliqué sur demande.', 'Annual fees, membership included. Payment in three installments available, income-based rates on request.'), 'rows' => [
                    [$T('Atelier', 'Class'), $T('Public', 'Ages'), $T('Créneau', 'Time'), $T('Tarif annuel', 'Annual fee')],
                    [$T('Éveil artistique', 'Arts for little ones'), $T('4 – 6 ans', 'Ages 4–6'), $T('Mercredi 10 h', 'Wednesday 10 am'), $T('180 €', '$195')],
                    [$T('Théâtre enfants', 'Kids’ theater'), $T('7 – 11 ans', 'Ages 7–11'), $T('Mercredi 14 h', 'Wednesday 2 pm'), $T('220 €', '$240')],
                    [$T('Danses urbaines', 'Street dance'), $T('12 – 17 ans', 'Ages 12–17'), $T('Samedi 14 h', 'Saturday 2 pm'), $T('220 €', '$240')],
                    [$T('Théâtre adultes', 'Adult theater'), $T('Adultes', 'Adults'), $T('Lundi 19 h 30', 'Monday 7:30 pm'), $T('290 €', '$315')],
                    [$T('Chorale', 'Choir'), $T('Tous âges', 'All ages'), $T('Jeudi 19 h', 'Thursday 7 pm'), $T('150 €', '$165')],
                    [$T('Photographie', 'Photography'), $T('Ados et adultes', 'Teens and adults'), $T('Mardi 18 h 30', 'Tuesday 6:30 pm'), $T('260 €', '$285')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Inscription', 'Enrollment'), 'title' => $T('S’inscrire à un atelier', 'Signing up for a class'), 'items' => [
                    [$T('Séance d’essai', 'Trial class'), $T('Une séance gratuite en septembre pour découvrir l’atelier.', 'A free class in September to try it out.')],
                    [$T('Inscription', 'Registration'), $T('En ligne ou à l’accueil, avec le formulaire et le règlement.', 'Online or at reception, with the form and payment.')],
                    [$T('C’est parti', 'Let’s go'), $T('Trente séances de septembre à juin, hors vacances scolaires.', 'Thirty sessions from September to June, outside school holidays.')],
                ]]),
                B::testimonials($theme, ['eyebrow' => $T('Paroles d’élèves', 'Student voices'), 'title' => $T('Ils pratiquent chez nous', 'They make art with us'), 'items' => [
                    [$T('Je suis timide, et l’atelier théâtre m’a appris à prendre la parole devant tout le monde. Le spectacle de fin d’année, c’était incroyable.', 'I’m shy, and the theater class taught me to speak in front of everyone. The end-of-year show was amazing.'), '[Prénom Nom]', $T('Élève, 14 ans', 'Student, age 14')],
                    [$T('La chorale est devenue mon rendez-vous de la semaine. On chante, on rit, et on se produit même sur la grande scène.', 'Choir has become the highlight of my week. We sing, we laugh, and we even perform on the main stage.'), '[Prénom Nom]', $T('Choriste', 'Choir member')],
                    [$T('Les intervenants sont de vrais artistes, attentifs à chaque enfant. Ma fille ne rate jamais une séance.', 'The teachers are real artists who care about every child. My daughter never misses a class.'), '[Prénom Nom]', $T('Parent d’élève', 'Parent')],
                ]]),
            ]];

        $pages[] = ['key' => 'exhibitions', 'slug' => $u('expositions-residences', 'exhibitions-residencies'), 'title' => $T('Expositions et résidences', 'Exhibitions and residencies'),
            'description' => $T('Expositions en entrée libre, résidences d’artistes et appel à candidatures : soutenir la création et la rendre visible.', 'Free exhibitions, artist residencies and open calls: supporting new work and making it visible.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Arts visuels et création', 'Visual arts and new work'), 'title' => $T('Expositions et résidences d’artistes', 'Exhibitions and artist residencies'), 'intro' => $T('La galerie est ouverte en entrée libre du mardi au dimanche.', 'The gallery is open with free entry from Tuesday to Sunday.')]),
                B::cards($theme, ['eyebrow' => $T('Expositions', 'Exhibitions'), 'title' => $T('À voir à la galerie', 'At the gallery'), 'items' => [
                    ['culture-expo-photo', 'camera', $T('« Visages de la ville » · photographie', '“Faces of the City” · photography'), $T('Portraits d’habitants réalisés pendant un an dans tous les quartiers. Jusqu’au [date].', 'Portraits of residents taken over a year across every neighborhood. Until [date].')],
                    ['culture-expo-peinture', 'paintbrush', $T('« Couleurs premières » · peinture', '“Primary Colors” · painting'), $T('Grands formats d’une artiste en résidence, entre abstraction et paysage. À partir du [date].', 'Large-scale works by a resident artist, between abstraction and landscape. From [date].')],
                    ['culture-expo-ateliers', 'palette', $T('Exposition des ateliers', 'Student showcase'), $T('Les œuvres des élèves enfants et adultes, en fin de saison. En juin.', 'Work by our child and adult students, at the end of the season. In June.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Résidences', 'Residencies'),
                    'title' => $T('Un temps et un lieu pour créer', 'Time and space to create'),
                    'html' => '<p>'.e($T('Chaque saison, nous accueillons six compagnies et artistes en résidence : mise à disposition d’un plateau ou d’un atelier, accompagnement technique, aide à la production et rencontres avec les habitants. Les résidences se concluent par une présentation publique.', 'Each season we host six companies and artists in residence: a stage or studio, technical support, production assistance and meetings with local residents. Each residency ends with a public presentation.')).'</p>',
                    'bullets' => [$T('Résidences de 2 à 6 semaines', 'Residencies of 2 to 6 weeks'), $T('Hébergement possible pour les artistes', 'Accommodation available for artists'), $T('Actions avec les écoles et les associations', 'Outreach with schools and community groups')],
                    'photo' => ['culture-residence', 'drama', $T('Comédiens répétant sur un plateau de théâtre sous une lumière de travail', 'Actors rehearsing on a theater stage under work lights')],
                    'bg' => 'light',
                    'reverse' => true,
                ]),
                B::steps($theme, ['eyebrow' => $T('Appel à candidatures', 'Open call'), 'title' => $T('Candidater pour une résidence', 'Applying for a residency'), 'items' => [
                    [$T('Dossier', 'Application'), $T('Présentation du projet, calendrier et besoins techniques, avant le [date].', 'Project outline, timeline and technical needs, by [date].')],
                    [$T('Sélection', 'Selection'), $T('Un comité d’artistes et de l’équipe du lieu étudie chaque dossier.', 'A panel of artists and venue staff reviews every application.')],
                    [$T('Rencontre', 'Meeting'), $T('Échange avec les équipes retenues pour préparer l’accueil.', 'A meeting with selected artists to prepare the residency.')],
                    [$T('Résidence', 'Residency'), $T('Création, actions avec le public et présentation finale.', 'Creation, public outreach and a final presentation.')],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('La galerie et les ateliers d’artistes', 'The gallery and artists’ studios'), 'bg' => 'light', 'photos' => [
                    ['culture-galerie-1', 'frame', $T('Visiteurs observant des tableaux dans une galerie aux murs blancs', 'Visitors looking at paintings in a white-walled gallery')],
                    ['culture-galerie-2', 'paintbrush', $T('Artiste peignant une grande toile dans son atelier', 'Artist painting a large canvas in a studio')],
                    ['culture-galerie-3', 'camera', $T('Tirages photographiques accrochés pour une exposition', 'Photographic prints hung for an exhibition')],
                ]]),
            ]];

        $pages[] = ['key' => 'support', 'slug' => $u('adhesion-mecenat', 'membership-giving'), 'title' => $T('Adhésion et mécénat', 'Membership and giving'),
            'description' => $T('Devenez adhérent de l’association, faites un don ou soutenez le lieu en tant qu’entreprise mécène : avantages et projets financés.', 'Become a member of the association, make a gift or support the venue as a corporate sponsor: benefits and funded projects.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nous soutenir', 'Support us'), 'title' => $T('Faites vivre la culture avec nous', 'Help culture thrive with us'), 'intro' => $T('Adhérents, donateurs et mécènes rendent possible une programmation exigeante et accessible.', 'Members, donors and sponsors make an ambitious, affordable program possible.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Adhésion', 'Membership'), 'title' => $T('Devenir adhérent', 'Become a member'), 'intro' => $T('L’adhésion est valable une saison, de septembre à août.', 'Membership is valid for one season, from September to August.'), 'items' => [
                    [$T('Individuelle', 'Individual'), '20', $cur, $T('/ saison', '/ season'), $T('Pour participer à la vie de l’association.', 'To take part in the association’s life.'), [$T('Tarif réduit sur tous les spectacles', 'Reduced price on every show'), $T('Vote à l’assemblée générale', 'Vote at the annual meeting'), $T('Lettre d’information des adhérents', 'Members’ newsletter'), '-'.$T('Invitations aux avant-premières', 'Preview invitations')], $T('Adhérer', 'Join')],
                    [$T('Famille', 'Family'), '35', $cur, $T('/ saison', '/ season'), $T('Pour tout le foyer, enfants compris.', 'For the whole household, children included.'), [$T('Tarif réduit pour chaque membre', 'Reduced price for every member'), $T('– 10 % sur les ateliers', '10% off classes'), $T('Vote à l’assemblée générale', 'Vote at the annual meeting'), $T('Goûter de rentrée offert', 'Free back-to-season party')], $T('Adhérer en famille', 'Join as a family'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Bienfaiteur', 'Patron'), '100', $cur, $T('/ saison', '/ season'), $T('Pour soutenir le lieu un peu plus.', 'To give the venue a little more support.'), [$T('Tous les avantages de l’adhésion', 'All membership benefits'), $T('Invitations aux avant-premières', 'Preview invitations'), $T('Visite des coulisses', 'Backstage tour'), $T('Nom dans le programme de saison', 'Name in the season brochure')], $T('Devenir bienfaiteur', 'Become a patron')],
                ]]),
                B::donate($theme, [
                    'eyebrow' => $T('Campagne en cours', 'Current campaign'),
                    'title' => $T('Renouvelons les fauteuils de la grande salle', 'Let’s replace the main hall seats'),
                    'text' => $T('Après trente ans de spectacles, les 400 fauteuils de la grande salle doivent être remplacés. Chaque don compte, et votre nom peut figurer sur une plaque au dos d’un fauteuil.', 'After thirty years of shows, the main hall’s 400 seats need replacing. Every gift counts, and your name can appear on a plaque on the back of a seat.'),
                    'goal' => $T('62 % de l’objectif atteint', '62% of the goal reached'),
                    'percent' => 62,
                    'amounts' => [$T('30 €', '$30'), $T('80 €', '$80'), $T('250 €', '$250'), $T('Autre montant', 'Other amount')],
                    'button' => $T('Faire un don', 'Give now'),
                    'url' => $contact,
                    'photo' => ['culture-fauteuils', 'armchair', $T('Rangées de fauteuils rouges dans une salle de théâtre vide', 'Rows of red seats in an empty theater')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Mécénat d’entreprise', 'Corporate sponsorship'), 'title' => $T('Associez votre entreprise à un projet culturel', 'Link your company to a cultural project'), 'items' => [
                    ['handshake', $T('Visibilité', 'Visibility'), $T('Votre logo sur nos supports et une soirée dédiée à vos invités.', 'Your logo on our materials and an evening for your guests.')],
                    ['heart-handshake', $T('Engagement', 'Commitment'), $T('Financez un atelier dans un quartier ou un spectacle pour les écoles.', 'Fund a neighborhood class or a show for schools.')],
                    ['receipt', $T('Avantages fiscaux', 'Tax benefits'), $T('Selon la législation de votre pays, votre don peut ouvrir droit à une réduction d’impôt.', 'Depending on your country’s laws, your gift may be tax-deductible.')],
                ]]),
                B::cta($theme, ['tone' => 'dark', 'title' => $T('Parlons de votre mécénat', 'Let’s talk about sponsorship'), 'text' => $T('Notre chargée de mécénat vous présente les projets de la saison et construit avec vous un partenariat sur mesure.', 'Our development officer will present this season’s projects and build a tailored partnership with you.'),
                    'buttons' => [[$T('Prendre rendez-vous', 'Book a meeting'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'hire', 'slug' => $u('location-de-salles', 'venue-hire'), 'title' => $T('Location de salles', 'Venue hire'),
            'description' => $T('Louez nos salles pour vos conférences, séminaires, concerts privés et réceptions : capacités, équipements techniques et tarifs.', 'Hire our rooms for conferences, seminars, private concerts and receptions: capacities, technical equipment and rates.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Location', 'Venue hire'), 'title' => $T('Des espaces pour vos événements', 'Spaces for your events'), 'intro' => $T('Une équipe technique et d’accueil professionnelle à vos côtés, du devis au démontage.', 'A professional technical and front-of-house team with you, from quote to load-out.')]),
                B::cards($theme, ['cols' => 2, 'items' => [
                    ['culture-salle-grande', 'drama', $T('La grande salle', 'The main hall'), $T('400 places assises, scène de 12 × 10 m, son et lumière professionnels. Idéale pour conférences et spectacles.', '400 seats, 12 × 10 m stage, professional sound and lighting. Ideal for conferences and shows.')],
                    ['culture-salle-studio', 'music', $T('Le studio', 'The studio'), $T('120 places modulables, gradins rétractables. Parfait pour concerts intimistes et tables rondes.', '120 flexible seats, retractable seating. Perfect for intimate concerts and panel discussions.')],
                    ['culture-salle-galerie', 'frame', $T('La galerie', 'The gallery'), $T('200 m² lumineux pour cocktails, lancements et expositions privées.', '200 bright square meters for cocktails, launches and private exhibitions.')],
                    ['culture-salle-reunion', 'presentation', $T('La salle de réunion', 'The meeting room'), $T('30 personnes, écran, visioconférence et vue sur le jardin.', '30 people, screen, videoconferencing and a garden view.')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Capacités et tarifs', 'Capacity and rates'), 'title' => $T('Nos salles en détail', 'Our rooms in detail'), 'bg' => 'light', 'note' => $T('Tarifs indicatifs hors taxes, technicien inclus. Tarifs associatifs sur demande.', 'Indicative rates excluding taxes, technician included. Non-profit rates on request.'), 'rows' => [
                    [$T('Espace', 'Space'), $T('Capacité', 'Capacity'), $T('Demi-journée', 'Half day'), $T('Journée', 'Full day')],
                    [$T('Grande salle', 'Main hall'), $T('400 places', '400 seats'), $T('1 200 €', '$1,300'), $T('2 000 €', '$2,200')],
                    [$T('Studio', 'Studio'), $T('120 places', '120 seats'), $T('500 €', '$550'), $T('850 €', '$900')],
                    [$T('Galerie', 'Gallery'), $T('150 debout', '150 standing'), $T('400 €', '$450'), $T('700 €', '$750')],
                    [$T('Salle de réunion', 'Meeting room'), $T('30 personnes', '30 people'), $T('150 €', '$165'), $T('250 €', '$275')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Réserver', 'Booking'), 'title' => $T('Organiser votre événement', 'Planning your event'), 'items' => [
                    [$T('Demande', 'Request'), $T('Date, nombre de personnes et type d’événement.', 'Date, number of guests and type of event.')],
                    [$T('Visite', 'Site visit'), $T('Visite technique des espaces avec notre régisseur.', 'Technical walk-through with our stage manager.')],
                    [$T('Devis', 'Quote'), $T('Proposition détaillée sous cinq jours ouvrés.', 'Detailed proposal within five business days.')],
                    [$T('Le jour J', 'On the day'), $T('Accueil, technique et sécurité assurés par notre équipe.', 'Front of house, tech and security handled by our team.')],
                ]]),
                B::cta($theme, ['title' => $T('Un événement en préparation ?', 'Planning an event?'), 'text' => $T('Indiquez-nous la date et le format : nous vérifions les disponibilités en moins de 48 heures.', 'Tell us the date and format: we will check availability within 48 hours.'),
                    'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'journal', 'slug' => $u('journal', 'journal'), 'title' => $T('Journal', 'Journal'),
            'description' => $T('Entretiens avec les artistes, coulisses des créations et des répétitions, retours sur les ateliers et vie de l’association, et rencontres à venir.', 'Artist interviews, behind the scenes of new work and rehearsals, stories from our classes, association news and upcoming talks.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Journal', 'Journal'), 'title' => $T('Dans les coulisses du lieu', 'Behind the scenes'), 'intro' => $T('Entretiens, répétitions, ateliers : ce qui se passe avant le lever de rideau.', 'Interviews, rehearsals, classes: what happens before the curtain rises.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire', 'Read')]),
                B::events($theme, ['icon' => 'mic', 'eyebrow' => $T('Rencontres', 'Talks'), 'title' => $T('Bords de scène et rencontres', 'Post-show talks and meetings'), 'bg' => 'light', 'items' => [
                    [$T('Rencontre avec l’équipe de « La Traversée »', 'Meet the team behind “The Crossing”'), $T('Vendredi 3, après la représentation', 'Friday 3, after the show'), $T('Grande salle', 'Main hall')],
                    [$T('Répétition publique : « Lignes »', 'Open rehearsal: “Lines”'), $T('Mercredi 8, 18 h', 'Wednesday 8, 6 pm'), $T('Studio', 'Studio')],
                    [$T('Visite commentée de l’exposition', 'Guided exhibition tour'), $T('Samedi 11, 15 h', 'Saturday 11, 3 pm'), $T('Galerie', 'Gallery')],
                    [$T('Assemblée générale de l’association', 'Association annual meeting'), $T('Jeudi 23, 19 h', 'Thursday 23, 7 pm'), $T('Café du théâtre', 'Theater café')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez la billetterie, l’accueil des ateliers ou l’équipe du lieu. Adresse, accès, horaires et accessibilité.', 'Contact the box office, class reception or the venue team. Address, directions, opening hours and accessibility.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Nous écrire, nous rendre visite', 'Write to us, come and visit'), 'intro' => $T('Billetterie, ateliers, location, presse ou candidatures d’artistes : indiquez l’objet de votre message.', 'Box office, classes, venue hire, press or artist applications: please state the subject of your message.')]),
                B::schedule($theme, ['bg' => 'white', 'groups' => [
                    [$T('Accueil et galerie', 'Reception and gallery'), [
                        ['clock', $T('Mardi – dimanche, 10 h – 19 h', 'Tuesday – Sunday, 10 am – 7 pm')],
                        ['coffee', $T('Café du théâtre ouvert les soirs de spectacle', 'Theater café open on show nights')],
                    ]],
                    [$T('Billetterie', 'Box office'), [
                        ['ticket', $T('Mardi – samedi, 13 h – 19 h', 'Tuesday – Saturday, 1 – 7 pm')],
                        ['clock', $T('Une heure avant chaque représentation', 'One hour before each performance')],
                    ]],
                ]]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Nous répondons à chaque message sous trois jours ouvrés.', 'We answer every message within three business days.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['accessibility', $T('Lieu accessible aux personnes à mobilité réduite', 'Fully wheelchair accessible')],
                    ],
                    'social' => ['instagram', 'facebook', 'youtube'],
                    'subject' => true,
                    'bg' => 'light',
                ]),
            ]];

        $blog = [
            'base' => $u('journal', 'journal'),
            'title' => $T('Journal', 'Journal'),
            'categories' => ['creations' => $T('Créations', 'New work'), 'ateliers' => $T('Ateliers', 'Classes'), 'coulisses' => $T('Coulisses', 'Backstage')],
            'posts' => [
                ['key' => 'traversee', 'slug' => $u('entretien-la-traversee', 'interview-the-crossing'), 'category' => 'creations', 'tags' => [$T('Théâtre', 'Theater')],
                    'title' => $T('« La Traversée » : entretien avec la metteuse en scène', '“The Crossing”: an interview with the director'),
                    'excerpt' => $T('Six semaines de résidence pour écrire, répéter et rencontrer les habitants.', 'Six weeks in residence to write, rehearse and meet local residents.'),
                    'photo' => ['culture-blog-entretien', 'drama', $T('Metteuse en scène donnant des indications à des comédiens en répétition', 'Director giving notes to actors during rehearsal')],
                    'quote' => $T('« Nous avons écrit ce spectacle avec les histoires que les habitants nous ont confiées. »', '“We wrote this show from the stories residents shared with us.”'),
                    'body' => [
                        $T('La compagnie a passé six semaines en résidence au théâtre, entre écriture au plateau et ateliers avec les habitants du quartier.', 'The company spent six weeks in residence at the theater, writing on stage and running workshops with local residents.'),
                        $T('Le spectacle mêle comédiens professionnels et voix enregistrées lors de ces rencontres, sur une musique originale jouée en direct.', 'The show blends professional actors with voices recorded during those meetings, set to an original score played live.'),
                        $T('Première le vendredi 3 à 20 h, suivie d’une rencontre avec l’équipe artistique.', 'Opening night is Friday 3 at 8 pm, followed by a talk with the artistic team.'),
                    ]],
                ['key' => 'rentree', 'slug' => $u('rentree-des-ateliers', 'classes-are-back'), 'category' => 'ateliers', 'tags' => [$T('Inscriptions', 'Enrollment')],
                    'title' => $T('Rentrée des ateliers : trois nouveautés cette saison', 'Classes are back: three new offerings this season'),
                    'excerpt' => $T('Slam pour les ados, photographie au smartphone et chorale intergénérationnelle.', 'Spoken word for teens, smartphone photography and an all-ages choir.'),
                    'photo' => ['culture-blog-ateliers', 'palette', $T('Enfants peignant ensemble lors d’un atelier d’arts plastiques', 'Children painting together in an art class')],
                    'heading' => $T('Les nouveautés', 'What’s new'),
                    'body' => [
                        $T('Les ateliers reprennent la deuxième semaine de septembre, avec une séance d’essai gratuite pour chaque discipline.', 'Classes start again in the second week of September, with a free trial session for every discipline.'),
                        $T('Trois nouveaux ateliers ouvrent cette saison : slam pour les 12 – 17 ans, photographie au smartphone et une chorale qui réunit enfants, parents et grands-parents.', 'Three new classes open this season: spoken word for ages 12 to 17, smartphone photography and a choir bringing together children, parents and grandparents.'),
                        $T('Les places étant limitées, nous vous conseillons de vous inscrire rapidement.', 'Places are limited, so we recommend signing up early.'),
                    ]],
                ['key' => 'regie', 'slug' => $u('une-journee-en-regie', 'a-day-in-the-control-booth'), 'category' => 'coulisses', 'tags' => [$T('Métiers', 'Jobs')],
                    'title' => $T('Une journée avec l’équipe technique', 'A day with the technical crew'),
                    'excerpt' => $T('Montage, réglages lumière et balances : tout ce qui se passe avant l’ouverture des portes.', 'Load-in, lighting focus and sound checks: everything that happens before the doors open.'),
                    'photo' => ['culture-blog-regie', 'lightbulb', $T('Technicienne réglant des projecteurs au-dessus d’une scène', 'Technician adjusting stage lights above a stage')],
                    'heading' => $T('Avant le lever de rideau', 'Before curtain up'),
                    'body' => [
                        $T('Dès 9 heures, les techniciens déchargent les décors et montent le plateau selon les plans de la compagnie.', 'From 9 am, technicians unload the set and build the stage to the company’s plans.'),
                        $T('L’après-midi est consacré aux réglages des lumières, projecteur par projecteur, puis aux balances son avec les musiciens.', 'The afternoon is spent focusing the lights, one by one, then sound-checking with the musicians.'),
                        $T('À 19 h 30, tout est prêt : la régie lance le spectacle, et l’équipe sera encore là pour démonter après la dernière note.', 'By 7:30 pm everything is ready: the booth starts the show, and the crew will still be there to strike the set after the last note.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Programmation', 'What’s on'), 'url' => $program],
                    ['label' => $T('Ateliers', 'Classes'), 'url' => $classes],
                    ['label' => $T('Expositions', 'Exhibitions'), 'url' => $exhibitions],
                    ['label' => $T('Nous soutenir', 'Support us'), 'url' => $support],
                    ['label' => $T('Location', 'Venue hire'), 'url' => $hire],
                    ['label' => $T('Journal', 'Journal'), 'url' => $journal],
                ],
                'cta_label' => $T('Billetterie', 'Tickets'),
                'cta_url' => $tickets,
                'footer_text' => $T('Théâtre et centre culturel associatif : spectacles, ateliers, expositions et résidences d’artistes, ouverts à toutes et à tous.', 'Non-profit theater and arts center: shows, classes, exhibitions and artist residencies, open to everyone.'),
                'footer_links' => [
                    ['label' => $T('Programmation et billetterie', 'What’s on and tickets'), 'url' => $program],
                    ['label' => $T('Adhésion et mécénat', 'Membership and giving'), 'url' => $support],
                    ['label' => $T('Location de salles', 'Venue hire'), 'url' => $hire],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
