<?php

/*
 * Pack « Gouvernement et collectivités » : 8 pages, en français et en anglais.
 * Les textes sont des exemples neutres à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'institution). Les liens internes
 * sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Gouvernement et collectivités', 'en' => 'Government and municipalities'],
        'icon' => 'landmark',
        'keywords' => ['gouvernement', 'mairie', 'commune', 'municipalité', 'administration', 'collectivité', 'service public', 'état civil', 'government', 'town hall', 'municipality', 'council', 'public agency', 'city hall'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $about = $u('/la-commune', '/the-municipality');
        $services = '/services';
        $procedures = $u('/demarches', '/procedures');
        $projects = $u('/projets', '/projects');
        $organization = $u('/organisation', '/organization');
        $news = $u('/actualites', '/news');
        $contact = '/contact';

        $serviceItems = [
            ['id-card', $T('État civil', 'Civil registry'), $T('Actes de naissance, de mariage et de décès, déclarations et copies d’actes.', 'Birth, marriage and death certificates, declarations and certified copies.')],
            ['hammer', $T('Urbanisme et habitat', 'Urban planning and housing'), $T('Permis de construire, attestations de parcelle, plans d’aménagement du territoire.', 'Building permits, plot certificates and land-use plans.')],
            ['hand-heart', $T('Affaires sociales', 'Social affairs'), $T('Accompagnement des familles vulnérables, des personnes âgées et des personnes handicapées.', 'Support for vulnerable families, older people and people with disabilities.')],
            ['heart-pulse', $T('Santé publique', 'Public health'), $T('Centres de santé, campagnes de vaccination et prévention de proximité.', 'Health centers, vaccination campaigns and local prevention.')],
            ['graduation-cap', $T('Éducation et jeunesse', 'Education and youth'), $T('Écoles communales, inscriptions, bourses et activités pour les jeunes.', 'Municipal schools, enrolment, scholarships and youth activities.')],
            ['recycle', $T('Assainissement et propreté', 'Sanitation and cleanliness'), $T('Collecte des déchets, drainage, entretien des marchés et des espaces publics.', 'Waste collection, drainage, upkeep of markets and public spaces.')],
        ];
        $quickItems = [
            ['id-card', $T('État civil', 'Civil registry'), $T('Actes de naissance, mariage, décès.', 'Birth, marriage and death certificates.'), $procedures, $T('Voir la démarche', 'See the procedure')],
            ['hammer', $T('Urbanisme', 'Urban planning'), $T('Permis de construire et attestations.', 'Building permits and certificates.'), $services, $T('Voir les services', 'See the services')],
            ['recycle', $T('Propreté', 'Cleanliness'), $T('Collecte des déchets et signalements.', 'Waste collection and reports.'), $services, $T('Voir les services', 'See the services')],
            ['calendar-days', $T('Agenda', 'Calendar'), $T('Séances, réunions et cérémonies.', 'Sessions, meetings and ceremonies.'), $news, $T('Voir l’agenda', 'See the calendar')],
        ];
        $stats = ['title' => $T('La commune en chiffres', 'The municipality in figures'), 'items' => [
            [250000, '', $T('habitants', 'residents')],
            [12, '', $T('quartiers', 'neighborhoods')],
            [35, '', $T('écoles communales', 'municipal schools')],
            [18, '', $T('projets en cours', 'ongoing projects')],
        ]];
        $newsItems = [
            ['gouvernement-actu-1', 'newspaper', $T('Ouverture du guichet unique', 'One-stop service desk opens'), $T('Les principales démarches sont désormais regroupées dans un seul lieu d’accueil.', 'The main procedures are now gathered in a single reception point.')],
            ['gouvernement-actu-2', 'recycle', $T('Campagne de salubrité publique', 'Public cleanliness campaign'), $T('Une opération de nettoyage mobilise les quartiers chaque premier samedi du mois.', 'A clean-up operation mobilizes neighborhoods on the first Saturday of every month.')],
            ['gouvernement-actu-3', 'school', $T('Rentrée scolaire : inscriptions ouvertes', 'Back to school: enrolment is open'), $T('Les familles peuvent inscrire leurs enfants dans les écoles communales.', 'Families can enrol their children in municipal schools.')],
        ];
        $cta = ['title' => $T('Vos démarches, plus simplement', 'Your procedures, made simpler'), 'text' => $T('Consultez les pièces à fournir et préparez votre dossier avant de vous déplacer.', 'Check the documents required and prepare your file before you visit.'),
            'buttons' => [[$T('Démarches en ligne', 'Online procedures'), $procedures, 'accent'], [$T('Nous contacter', 'Contact us'), $contact, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Bienvenue sur le site officiel de [Nom de la commune] : services publics, démarches, projets, actualités et agenda.', 'Welcome to the official website of [Municipality name]: public services, procedures, projects, news and events.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Bienvenue à [Nom de la commune]', 'Welcome to [Municipality name]'),
                    'title' => $T('Au service de tous les habitants', 'At the service of every resident'),
                    'text' => $T('Retrouvez vos services publics, vos démarches administratives et toute l’actualité de la commune, en un seul endroit.', 'Find your public services, administrative procedures and all the news of the municipality in one place.'),
                    'buttons' => [[$T('Démarches en ligne', 'Online procedures'), $procedures], [$T('Nos services', 'Our services'), $services]],
                    'photo' => ['gouvernement-accueil', 'landmark', $T('Façade de l’hôtel de ville', 'Front of the town hall')],
                ]),
                B::features($theme, ['eyebrow' => $T('Accès rapide', 'Quick access'), 'title' => $T('Que souhaitez-vous faire ?', 'What would you like to do?'), 'cols' => 4, 'items' => $quickItems]),
                B::stats($theme, $stats),
                B::posts($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('À la une', 'Latest news'), 'source' => 'latest', 'count' => 3, 'more' => $T('Lire la suite', 'Read more')]),
                B::split($theme, [
                    'eyebrow' => $T('La commune', 'The municipality'),
                    'title' => $T('Une commune proche de ses habitants', 'A municipality close to its residents'),
                    'html' => '<p>'.e($T('[Nom de la commune] s’engage pour des services publics accessibles, une gestion transparente et un cadre de vie agréable pour chaque quartier.', '[Municipality name] is committed to accessible public services, transparent management and a pleasant living environment in every neighborhood.')).'</p>',
                    'bullets' => [$T('Un accueil ouvert à toutes et à tous', 'A welcome open to everyone'), $T('Des informations claires et à jour', 'Clear and up-to-date information'), $T('Des comptes rendus de séances publics', 'Public records of council sessions')],
                    'buttons' => [[$T('Découvrir la commune', 'Discover the municipality'), $about, 'outline']],
                    'photo' => ['gouvernement-hotel-de-ville', 'landmark', $T('Hôtel de ville et place centrale', 'Town hall and central square')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::events($theme, ['eyebrow' => $T('Agenda', 'Calendar'), 'title' => $T('Prochains rendez-vous', 'Upcoming events'), 'items' => [
                    [$T('Séance publique du conseil', 'Public council session'), $T('[Date], 10 h', '[Date], 10 am'), $T('Salle du conseil, hôtel de ville', 'Council chamber, town hall')],
                    [$T('Journée de salubrité', 'Clean-up day'), $T('[Date], 8 h', '[Date], 8 am'), $T('Tous les quartiers', 'All neighborhoods')],
                    [$T('Campagne de vaccination', 'Vaccination campaign'), $T('[Date], 9 h', '[Date], 9 am'), $T('Centres de santé communaux', 'Municipal health centers')],
                    [$T('Réunion d’information citoyenne', 'Citizens’ information meeting'), $T('[Date], 15 h', '[Date], 3 pm'), $T('Centre communautaire', 'Community center')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('la-commune', 'the-municipality'), 'title' => $T('La commune', 'The municipality'),
            'description' => $T('Présentation de [Nom de la commune] : son histoire, sa mission, ses engagements et la parole de son maire.', 'About [Municipality name]: its history, mission, commitments and a word from the mayor.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('La commune', 'The municipality'), 'title' => $T('Présentation de [Nom de la commune]', 'About [Municipality name]'), 'intro' => $T('Un territoire, une histoire et un engagement : servir les habitants.', 'A territory, a history and one commitment: serving residents.')]),
                B::split($theme, [
                    'eyebrow' => $T('Présentation', 'Overview'),
                    'title' => $T('Un territoire vivant et solidaire', 'A lively and caring territory'),
                    'html' => '<p>'.e($T('[Nom de la commune] regroupe plusieurs quartiers, des marchés, des écoles et des centres de santé. Elle est administrée par un conseil élu et une administration au service de la population.', '[Municipality name] brings together several neighborhoods, markets, schools and health centers. It is run by an elected council and an administration serving the population.')).'</p>',
                    'bullets' => [$T('Population : [à compléter]', 'Population: [to be completed]'), $T('Superficie : [à compléter]', 'Area: [to be completed]'), $T('Création : [à compléter]', 'Established: [to be completed]')],
                    'photo' => ['gouvernement-territoire', 'map-pin', $T('Vue générale de la commune', 'General view of the municipality')],
                ]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our history'),
                    'title' => $T('De village à ville en développement', 'From village to growing city'),
                    'html' => '<p>'.e($T('Au fil des décennies, la commune s’est développée autour de son marché et de ses axes de circulation. Cette histoire se transmet aujourd’hui à travers ses lieux de mémoire et ses traditions. [Texte à compléter]', 'Over the decades, the municipality grew around its market and main roads. This history lives on today through its landmarks and traditions. [Text to be completed]')).'</p>',
                    'photo' => ['gouvernement-histoire', 'landmark', $T('Ancien bâtiment administratif de la commune', 'Historic administrative building of the municipality')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Mission et engagements', 'Mission and commitments'), 'title' => $T('Ce qui guide notre action', 'What guides our action'), 'items' => [
                    ['users-round', $T('Proximité', 'Closeness'), $T('Un accueil et des services accessibles dans chaque quartier.', 'Reception and services accessible in every neighborhood.')],
                    ['eye', $T('Transparence', 'Transparency'), $T('Des décisions, des budgets et des comptes rendus publiés.', 'Decisions, budgets and minutes made public.')],
                    ['scale', $T('Équité', 'Fairness'), $T('Un traitement égal de chaque habitant, sans discrimination.', 'Equal treatment of every resident, without discrimination.')],
                    ['leaf', $T('Développement durable', 'Sustainable development'), $T('Un cadre de vie sain pour les générations futures.', 'A healthy living environment for future generations.')],
                ]]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Notre priorité est simple : que chaque habitant trouve une administration à l’écoute, claire et efficace.', 'Our priority is simple: every resident should find an administration that listens, is clear and works well.'), 'source' => $T('— [Prénom Nom], maire de [Nom de la commune]', '— [Prénom Nom], mayor of [Municipality name]')]),
            ]];

        $pages[] = ['key' => 'services', 'slug' => 'services', 'title' => $T('Services publics', 'Public services'),
            'description' => $T('État civil, urbanisme, affaires sociales, santé, éducation, assainissement : les services publics de la commune.', 'Civil registry, urban planning, social affairs, health, education, sanitation: the municipality’s public services.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Services', 'title' => $T('Les services publics de la commune', 'The municipality’s public services'), 'intro' => $T('Des services répartis par domaine pour vous orienter facilement.', 'Services grouped by field to help you find your way.')]),
                B::features($theme, ['items' => $serviceItems, 'cols' => 3]),
                B::split($theme, [
                    'eyebrow' => $T('Accueil du public', 'Public reception'),
                    'title' => $T('Un guichet pour vous orienter', 'A desk to guide you'),
                    'html' => '<p>'.e($T('Le service d’accueil vous informe, vérifie votre dossier et vous dirige vers le bon service. Il accueille aussi les personnes à mobilité réduite.', 'The reception service informs you, checks your file and directs you to the right department. It also assists people with reduced mobility.')).'</p>',
                    'bullets' => [$T('Information gratuite', 'Free information'), $T('Aide à la rédaction des demandes', 'Help with writing requests'), $T('Accès adapté aux personnes handicapées', 'Accessible to people with disabilities')],
                    'buttons' => [[$T('Préparer ma démarche', 'Prepare my procedure'), $procedures]],
                    'photo' => ['gouvernement-accueil-public', 'users', $T('Agent d’accueil renseignant une habitante', 'Reception officer assisting a resident')],
                    'bg' => 'light',
                ]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'procedures', 'slug' => $u('demarches', 'procedures'), 'title' => $T('Démarches administratives', 'Administrative procedures'),
            'description' => $T('Comment obtenir un acte, une attestation ou une autorisation : étapes, pièces à fournir et questions fréquentes.', 'How to obtain a certificate, attestation or permit: steps, required documents and frequently asked questions.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Démarches', 'Procedures'), 'title' => $T('Vos démarches, pas à pas', 'Your procedures, step by step'), 'intro' => $T('Préparez votre dossier pour gagner du temps au guichet.', 'Prepare your file to save time at the desk.')]),
                B::steps($theme, ['eyebrow' => $T('Comment faire', 'How it works'), 'title' => $T('Obtenir un document en quatre étapes', 'Getting a document in four steps'), 'items' => [
                    [$T('Choisir la démarche', 'Choose the procedure'), $T('Identifiez le document dont vous avez besoin.', 'Identify the document you need.')],
                    [$T('Réunir les pièces', 'Gather the documents'), $T('Rassemblez les pièces demandées, en original et en copie.', 'Collect the required documents, original and copy.')],
                    [$T('Déposer la demande', 'Submit the request'), $T('Présentez-vous au guichet ou envoyez votre dossier.', 'Visit the desk or send your file.')],
                    [$T('Retirer le document', 'Collect the document'), $T('Venez le retirer à la date indiquée, avec une pièce d’identité.', 'Pick it up on the date given, with an ID.')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Pièces à fournir', 'Required documents'), 'title' => $T('Les documents à apporter', 'Documents to bring'), 'groups' => [
                    [$T('Acte de naissance', 'Birth certificate'), [
                        ['file-text', $T('Déclaration de naissance de l’hôpital ou du centre de santé', 'Birth notification from the hospital or health center')],
                        ['id-card', $T('Pièce d’identité des parents', 'Parents’ ID documents')],
                        ['wallet', $T('Frais : [à compléter]', 'Fee: [to be completed]')],
                    ]],
                    [$T('Attestation de résidence', 'Residence certificate'), [
                        ['id-card', $T('Pièce d’identité', 'ID document')],
                        ['map-pin', $T('Justificatif de domicile', 'Proof of address')],
                        ['wallet', $T('Frais : [à compléter]', 'Fee: [to be completed]')],
                    ]],
                    [$T('Permis de construire', 'Building permit'), [
                        ['file-text', $T('Titre de propriété ou attestation de parcelle', 'Title deed or plot certificate')],
                        ['hammer', $T('Plans de la construction', 'Construction plans')],
                        ['wallet', $T('Frais : [à compléter]', 'Fee: [to be completed]')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'intro' => $T('Une autre question ? Notre accueil vous répond.', 'Another question? Our reception desk can help.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Combien de temps faut-il attendre un document ?', 'How long does a document take?'), $T('Le délai dépend de la démarche : [à compléter]. Il vous est indiqué lors du dépôt du dossier.', 'The time depends on the procedure: [to be completed]. You are told when you submit your file.')],
                    [$T('Puis-je envoyer quelqu’un à ma place ?', 'Can someone go on my behalf?'), $T('Oui, avec une procuration écrite et sa pièce d’identité, pour la plupart des démarches.', 'Yes, with a written authorization and their ID, for most procedures.')],
                    [$T('Que faire si mon dossier est incomplet ?', 'What if my file is incomplete?'), $T('L’agent vous indique les pièces manquantes. Vous pouvez revenir les déposer sans reprendre la file depuis le début.', 'The officer tells you what is missing. You can return with it without starting over.')],
                    [$T('Les démarches sont-elles payantes ?', 'Are procedures charged?'), $T('Certaines demandent des frais officiels : [à compléter]. Demandez toujours un reçu.', 'Some require official fees: [to be completed]. Always ask for a receipt.')],
                ]]),
            ]];

        $pages[] = ['key' => 'projects', 'slug' => $u('projets', 'projects'), 'title' => $T('Projets de développement', 'Development projects'),
            'description' => $T('Routes, marchés, écoles, eau et énergie : les projets de développement de la commune et leur avancement.', 'Roads, markets, schools, water and energy: the municipality’s development projects and their progress.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Projets', 'Projects'), 'title' => $T('Construire la commune de demain', 'Building tomorrow’s municipality'), 'intro' => $T('Des projets suivis et présentés en toute transparence.', 'Projects monitored and presented with full transparency.')]),
                B::cards($theme, ['items' => [
                    ['gouvernement-projet-routes', 'construction', $T('Réhabilitation des routes', 'Road rehabilitation'), $T('Remise en état des axes principaux et du drainage. Avancement : [à compléter].', 'Restoring main roads and drainage. Progress: [to be completed].')],
                    ['gouvernement-projet-marche', 'store', $T('Nouveau marché couvert', 'New covered market'), $T('Un espace propre et sécurisé pour les commerçants et les clients.', 'A clean and safe space for traders and customers.')],
                    ['gouvernement-projet-ecoles', 'school', $T('Écoles communales', 'Municipal schools'), $T('Construction et équipement de salles de classe dans les quartiers.', 'Building and equipping classrooms in neighborhoods.')],
                    ['gouvernement-projet-eau', 'droplet', $T('Accès à l’eau potable', 'Access to safe water'), $T('Extension du réseau et réhabilitation de bornes-fontaines.', 'Network extension and rehabilitation of public water points.')],
                    ['gouvernement-projet-energie', 'lightbulb', $T('Éclairage public', 'Public lighting'), $T('Installation de lampadaires solaires dans les rues et les places.', 'Installing solar streetlights on streets and squares.')],
                    ['gouvernement-projet-sante', 'stethoscope', $T('Centres de santé', 'Health centers'), $T('Rénovation et équipement des centres de santé de proximité.', 'Renovating and equipping local health centers.')],
                ]]),
                B::stats($theme, ['title' => $T('Les projets en chiffres', 'Projects in figures'), 'items' => [
                    [18, '', $T('projets en cours', 'ongoing projects')],
                    [42, '', $T('km de routes à réhabiliter', 'km of roads to rehabilitate')],
                    [35, '', $T('salles de classe prévues', 'planned classrooms')],
                    [5, '', $T('quartiers concernés', 'neighborhoods involved')],
                ], 'bg' => 'primary']),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('Les chantiers de la commune', 'Municipal worksites'), 'photos' => [
                    ['gouvernement-galerie-1', 'construction', $T('Chantier de route en cours', 'Road works in progress')],
                    ['gouvernement-galerie-2', 'hard-hat', $T('Ouvriers sur un chantier d’école', 'Workers on a school site')],
                    ['gouvernement-galerie-3', 'droplet', $T('Borne-fontaine réhabilitée', 'Rehabilitated public water point')],
                    ['gouvernement-galerie-4', 'store', $T('Marché en travaux', 'Market under construction')],
                    ['gouvernement-galerie-5', 'lightbulb', $T('Lampadaires solaires installés', 'Installed solar streetlights')],
                    ['gouvernement-galerie-6', 'users', $T('Inauguration avec les habitants', 'Inauguration with residents')],
                ]]),
            ]];

        $pages[] = ['key' => 'organization', 'slug' => $u('organisation', 'organization'), 'title' => $T('Organisation', 'Organization'),
            'description' => $T('Le conseil, les responsables, les départements et les horaires d’ouverture des services de la commune.', 'The council, officials, departments and opening hours of the municipal services.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Organisation', 'Organization'), 'title' => $T('Qui fait quoi à la commune', 'Who does what in the municipality'), 'intro' => $T('Les élus, les responsables et les services à votre disposition.', 'Elected members, officials and services at your disposal.')]),
                B::team($theme, ['eyebrow' => $T('Le conseil et la direction', 'Council and management'), 'title' => $T('Vos responsables', 'Your officials'), 'items' => [
                    ['[Prénom Nom]', $T('Maire', 'Mayor'), 'gouvernement-equipe-1'],
                    ['[Prénom Nom]', $T('Maire adjoint', 'Deputy mayor'), 'gouvernement-equipe-2'],
                    ['[Prénom Nom]', $T('Secrétaire général', 'Secretary general'), 'gouvernement-equipe-3'],
                    ['[Prénom Nom]', $T('Responsable de l’état civil', 'Head of civil registry'), 'gouvernement-equipe-4'],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Départements', 'Departments'), 'title' => $T('Les grands services', 'Main departments'), 'bg' => 'light', 'items' => [
                    ['briefcase', $T('Administration et finances', 'Administration and finance'), $T('Budget, recettes, ressources humaines et archives.', 'Budget, revenue, human resources and archives.')],
                    ['hard-hat', $T('Travaux et urbanisme', 'Works and urban planning'), $T('Voirie, permis de construire et aménagement.', 'Roads, building permits and planning.')],
                    ['hand-heart', $T('Affaires sociales et santé', 'Social affairs and health'), $T('Solidarité, santé de proximité et éducation.', 'Solidarity, local health and education.')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Horaires', 'Hours'), 'title' => $T('Heures d’ouverture au public', 'Public opening hours'), 'bg' => 'white', 'groups' => [
                    [$T('Accueil et état civil', 'Reception and civil registry'), [
                        ['clock', $T('Lundi – vendredi : 8 h – 15 h 30', 'Monday – Friday: 8 am – 3:30 pm')],
                        ['clock', $T('Samedi : [à compléter]', 'Saturday: [to be completed]')],
                    ]],
                    [$T('Autres services', 'Other departments'), [
                        ['clock', $T('Lundi – vendredi : 8 h – 16 h', 'Monday – Friday: 8 am – 4 pm')],
                        ['calendar-days', $T('Fermé les jours fériés', 'Closed on public holidays')],
                    ]],
                ]]),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites', 'news'), 'title' => $T('Actualités et avis', 'News and notices'),
            'description' => $T('Actualités, avis officiels et calendrier des événements de la commune.', 'News, official notices and calendar of municipal events.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Informations et avis officiels', 'News and official notices'), 'intro' => $T('Restez informé des décisions et des événements de la commune.', 'Stay informed about municipal decisions and events.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['eyebrow' => $T('Calendrier', 'Calendar'), 'title' => $T('Événements à venir', 'Upcoming events'), 'bg' => 'light', 'items' => [
                    [$T('Séance publique du conseil', 'Public council session'), $T('[Date], 10 h', '[Date], 10 am'), $T('Salle du conseil', 'Council chamber')],
                    [$T('Marché de l’artisanat local', 'Local crafts market'), $T('[Date], toute la journée', '[Date], all day'), $T('Place centrale', 'Central square')],
                    [$T('Cérémonie de la fête nationale', 'National day ceremony'), $T('[Date], 9 h', '[Date], 9 am'), $T('Hôtel de ville', 'Town hall')],
                    [$T('Forum de la jeunesse', 'Youth forum'), $T('[Date], 14 h', '[Date], 2 pm'), $T('Centre communautaire', 'Community center')],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Adresse, téléphone, courriel et horaires de la commune. Écrivez-nous ou venez nous rencontrer.', 'Address, phone, email and opening hours of the municipality. Write to us or come and see us.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Nous contacter', 'Contact us'), 'intro' => $T('Une question, une demande ou un signalement ? Nous vous répondons.', 'A question, a request or a report? We will get back to you.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Écrivez-nous ou présentez-vous à l’accueil de l’hôtel de ville.', 'Write to us or visit the town hall reception.'),
                    'items' => [
                        ['map-pin', $T('Hôtel de ville, [adresse], [Nom de la commune]', 'Town hall, [address], [Municipality name]')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h – 15 h 30', 'Monday – Friday, 8 am – 3:30 pm')],
                    ],
                    'social' => ['facebook', 'x', 'whatsapp', 'youtube'],
                    'subject' => true,
                    'map' => $T('Kinshasa, RDC', 'Kinshasa, DRC'),
                    'bg' => 'white',
                ]),
            ]];

        $blog = [
            'base' => $u('actualites', 'news'),
            'title' => $T('Actualités', 'News'),
            'categories' => ['avis' => $T('Avis officiels', 'Official notices'), 'services' => $T('Services', 'Services'), 'vie' => $T('Vie de la commune', 'Community life')],
            'posts' => [
                ['key' => 'guichet', 'slug' => $u('ouverture-guichet-unique', 'one-stop-desk-opens'), 'category' => 'services', 'tags' => [$T('Démarches', 'Procedures')],
                    'title' => $T('Ouverture du guichet unique', 'One-stop service desk opens'),
                    'excerpt' => $T('Les principales démarches sont désormais regroupées dans un seul lieu d’accueil.', 'The main procedures are now handled at a single reception point.'),
                    'photo' => ['gouvernement-actu-1', 'newspaper', $T('Accueil du public', 'Public reception desk')],
                    'heading' => $T('Comment ça marche', 'How it works'),
                    'body' => [
                        $T('Depuis le [date], état civil, urbanisme et taxes se traitent au même guichet, à la maison communale, du lundi au vendredi.', 'Since [date], civil registration, planning and taxes are handled at the same desk, at the town hall, Monday to Friday.'),
                        $T('Un agent d’accueil vous oriente dès votre arrivée et vous remet un ticket. Les pièces à fournir pour chaque démarche sont affichées et disponibles sur ce site.', 'A reception officer guides you as soon as you arrive and gives you a ticket. The documents required for each procedure are posted and available on this website.'),
                        $T('Objectif : moins d’attente et des dossiers complets dès le premier passage.', 'The goal: less waiting and complete files from the first visit.'),
                    ]],
                ['key' => 'salubrite', 'slug' => $u('campagne-salubrite', 'cleanliness-campaign'), 'category' => 'vie', 'tags' => [$T('Environnement', 'Environment')],
                    'title' => $T('Campagne de salubrité publique', 'Public cleanliness campaign'),
                    'excerpt' => $T('Une opération de nettoyage mobilise les quartiers chaque premier samedi du mois.', 'A clean-up operation brings neighborhoods together on the first Saturday of every month.'),
                    'photo' => ['gouvernement-actu-2', 'recycle', $T('Nettoyage des rues', 'Street cleaning')],
                    'quote' => $T('« Une commune propre, c’est l’affaire de tous. »', '“A clean town is everyone’s business.”'),
                    'body' => [
                        $T('Chaque premier samedi du mois, de 7 h à 10 h, habitants, associations et services communaux nettoient ensemble les rues et les caniveaux.', 'Every first Saturday of the month, from 7 to 10 am, residents, associations and municipal services clean streets and gutters together.'),
                        $T('La commune fournit le matériel et organise l’enlèvement des déchets collectés. Les chefs de quartier coordonnent les équipes.', 'The municipality provides the equipment and collects the waste gathered. Neighborhood leaders coordinate the teams.'),
                        $T('Pour inscrire votre association ou votre école, contactez le service de l’environnement.', 'To register your association or school, contact the environment department.'),
                    ]],
                ['key' => 'eau', 'slug' => $u('avis-coupure-eau', 'water-cut-notice'), 'category' => 'avis', 'tags' => [$T('Eau', 'Water')],
                    'title' => $T('Avis : coupure d’eau programmée', 'Notice: scheduled water cut'),
                    'excerpt' => $T('Des travaux sur le réseau perturberont la distribution le [date].', 'Network works will disrupt supply on [date].'),
                    'photo' => ['gouvernement-actu-4', 'megaphone', $T('Robinet d’eau', 'Water tap')],
                    'body' => [
                        $T('En raison de travaux de rénovation du réseau, la distribution d’eau sera interrompue le [date], de [heure] à [heure], dans les quartiers [noms].', 'Due to network renovation works, the water supply will be interrupted on [date], from [time] to [time], in the [names] neighborhoods.'),
                        $T('Nous vous conseillons de constituer une réserve d’eau la veille. Les bornes-fontaines de [lieu] resteront en service.', 'We advise you to store water the day before. The public fountains at [place] will remain in service.'),
                        $T('Merci de votre compréhension. Ces travaux amélioreront durablement la pression et la qualité de l’eau.', 'Thank you for your understanding. These works will lastingly improve water pressure and quality.'),
                    ]],
                ['key' => 'conseil', 'slug' => $u('compte-rendu-conseil', 'council-meeting-minutes'), 'category' => 'avis', 'tags' => [$T('Conseil communal', 'Council')],
                    'title' => $T('Compte rendu du dernier conseil', 'Minutes of the last council meeting'),
                    'excerpt' => $T('Les décisions de la séance du [date] sont disponibles.', 'The decisions of the [date] session are available.'),
                    'photo' => ['gouvernement-actu-5', 'gavel', $T('Salle du conseil', 'Council chamber')],
                    'heading' => $T('Principales décisions', 'Main decisions'),
                    'body' => [
                        $T('Le conseil communal s’est réuni le [date] sous la présidence de [nom]. [Nombre] délibérations ont été adoptées.', 'The municipal council met on [date], chaired by [name]. [Number] resolutions were adopted.'),
                        $T('Parmi elles : le budget des travaux de voirie, le règlement du marché central et le programme de la rentrée scolaire.', 'Among them: the road works budget, the central market regulations and the back-to-school program.'),
                        $T('Le compte rendu complet peut être consulté à la maison communale et sur demande auprès du secrétariat.', 'The full minutes can be consulted at the town hall and on request from the secretariat.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('La commune', 'Municipality'), 'url' => $about],
                    ['label' => 'Services', 'url' => $services],
                    ['label' => $T('Démarches', 'Procedures'), 'url' => $procedures],
                    ['label' => $T('Projets', 'Projects'), 'url' => $projects],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Démarches en ligne', 'Online procedures'),
                'cta_url' => $procedures,
                'footer_text' => $T('Site officiel de [Nom de la commune] : des services publics proches, clairs et accessibles à tous.', 'Official website of [Municipality name]: public services that are close, clear and accessible to all.'),
                'footer_links' => [
                    ['label' => $T('Démarches', 'Procedures'), 'url' => $procedures],
                    ['label' => $T('Organisation', 'Organization'), 'url' => $organization],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
