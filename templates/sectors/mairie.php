<?php

/*
 * Pack « Mairie et collectivité locale » (secteur Gouvernement et institutions
 * publiques) : 8 pages, en français et en anglais. Mairie, commune, ville ou
 * communauté de communes : démarches en ligne, services municipaux, conseil
 * municipal et élus, vie associative, actualités et agenda, signalements.
 * Les textes sont des exemples neutres à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de la commune). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Gouvernement et institutions publiques', 'en' => 'Government and public institutions'],
        'icon' => 'landmark',
        'keywords' => ['mairie', 'commune', 'ville', 'municipalité', 'collectivité locale', 'conseil municipal', 'maire', 'démarches', 'état civil', 'urbanisme', 'services publics', 'city hall', 'town hall', 'municipality', 'local government', 'city council', 'mayor', 'public services', 'council'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $online = $u('/demarches-en-ligne', '/online-services');
        $services = $u('/services-municipaux', '/city-services');
        $council = $u('/conseil-municipal', '/city-council');
        $community = $u('/vie-associative', '/community-life');
        $news = $u('/actualites-agenda', '/news-events');
        $report = $u('/signaler-un-probleme', '/report-an-issue');
        $contact = '/contact';

        $agenda = ['eyebrow' => $T('Agenda', 'Events'), 'title' => $T('Prochains rendez-vous', 'Upcoming events'), 'items' => [
            [$T('Conseil municipal (séance publique)', 'City council meeting (open to the public)'), $T('Jeudi 12, 19 h', 'Thursday 12, 7 pm'), $T('Salle du conseil, hôtel de ville', 'Council chamber, City Hall')],
            [$T('Forum des associations', 'Community groups fair'), $T('Samedi 6, 10 h – 17 h', 'Saturday 6, 10 am – 5 pm'), $T('Gymnase municipal', 'Municipal gym')],
            [$T('Réunion publique : réaménagement de la place du marché', 'Public meeting: redesign of the market square'), $T('Mardi 17, 18 h 30', 'Tuesday 17, 6:30 pm'), $T('Salle des fêtes', 'Community hall')],
            [$T('Marché des producteurs', 'Farmers’ market'), $T('Chaque samedi, 8 h – 13 h', 'Every Saturday, 8 am – 1 pm'), $T('Place du marché', 'Market square')],
        ]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Site officiel de la mairie de [Nom de la commune] : démarches en ligne, services municipaux, actualités, agenda, conseil municipal et signalements.', 'Official website of [Town name] City Hall: online services, city services, news, events, city council and issue reporting.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Mairie de [Nom de la commune]', '[Town name] City Hall'),
                    'title' => $T('Votre mairie, plus simple au quotidien', 'Your city hall, simpler every day'),
                    'text' => $T('Faites vos démarches en ligne, signalez un problème dans votre rue, suivez les décisions du conseil municipal et l’agenda de la commune, où que vous soyez.', 'Complete forms online, report a problem on your street, follow city council decisions and local events, wherever you are.'),
                    'buttons' => [[$T('Démarches en ligne', 'Online services'), $online], [$T('Signaler un problème', 'Report an issue'), $report]],
                    'photo' => ['mairie-accueil', 'landmark', $T('Façade d’un hôtel de ville avec son drapeau, sur une place piétonne', 'Front of a city hall with its flag, on a pedestrian square')],
                ]),
                B::features($theme, ['eyebrow' => $T('En un clic', 'In one click'), 'title' => $T('Les démarches les plus demandées', 'Most requested services'), 'cols' => 4, 'items' => [
                    ['id-card', $T('Papiers d’identité', 'ID documents'), $T('Carte d’identité et passeport sur rendez-vous.', 'ID cards and passports by appointment.'), $online, $T('Prendre rendez-vous', 'Book an appointment')],
                    ['file-text', $T('Actes d’état civil', 'Civil records'), $T('Naissance, mariage, décès : demande de copie.', 'Birth, marriage, death: request a copy.'), $online, $T('Faire une demande', 'Make a request')],
                    ['school', $T('Inscriptions scolaires', 'School enrollment'), $T('École, cantine et accueil périscolaire.', 'School, canteen and after-school care.'), $services, $T('S’inscrire', 'Enroll')],
                    ['triangle-alert', $T('Signaler un problème', 'Report an issue'), $T('Voirie, éclairage, propreté, dépôt sauvage.', 'Roads, street lights, litter, illegal dumping.'), $report, $T('Faire un signalement', 'Report it')],
                ]]),
                B::posts($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('À la une', 'Top stories'), 'source' => 'latest', 'count' => 3, 'more' => $T('Lire la suite', 'Read more'), 'bg' => 'light']),
                B::events($theme, $agenda),
                B::split($theme, [
                    'eyebrow' => $T('Le mot du maire', 'A word from the mayor'),
                    'title' => $T('Une commune à l’écoute de chacun', 'A town that listens to everyone'),
                    'html' => '<p>'.e($T('Notre équipe s’est engagée sur trois priorités : des services publics plus accessibles, un cadre de vie plus vert et une gestion transparente du budget communal. Ce site vous permet de suivre nos actions et d’y prendre part.', 'Our team has committed to three priorities: more accessible public services, a greener living environment and transparent management of the town budget. This website lets you follow our work and take part in it.')).'</p><p>'.e($T('— [Prénom Nom], maire de [Nom de la commune]', '— [First Last], mayor of [Town name]')).'</p>',
                    'buttons' => [[$T('Le conseil municipal', 'The city council'), $council, 'outline']],
                    'photo' => ['mairie-maire', 'user-round', $T('Élue municipale échangeant avec des habitants sur un marché', 'Local councillor talking with residents at a market')],
                    'reverse' => true,
                ]),
                B::stats($theme, ['title' => $T('La commune en chiffres', 'The town in numbers'), 'items' => [
                    [18500, '', $T('habitants', 'residents')],
                    [9, '', $T('écoles publiques', 'public schools')],
                    [140, '', $T('associations', 'community groups')],
                    [48, ' h', $T('pour traiter un signalement', 'to handle a report')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Horaires', 'Opening hours'), 'title' => $T('L’hôtel de ville vous accueille', 'City Hall is open'), 'groups' => [
                    [$T('Accueil et état civil', 'Reception and civil records'), [
                        ['clock', $T('Lundi – vendredi : 8 h 30 – 12 h et 13 h 30 – 17 h', 'Monday – Friday: 8:30 am – 12 pm and 1:30 – 5 pm')],
                        ['clock', $T('Samedi : 9 h – 12 h (état civil uniquement)', 'Saturday: 9 am – 12 pm (civil records only)')],
                    ]],
                    [$T('Services techniques et urbanisme', 'Public works and planning'), [
                        ['calendar-days', $T('Sur rendez-vous, du lundi au jeudi', 'By appointment, Monday to Thursday')],
                        ['phone', $T('Permanence téléphonique 9 h – 12 h', 'Phone line 9 am – 12 pm')],
                    ]],
                ]]),
            ]];

        $pages[] = ['key' => 'online', 'slug' => $u('demarches-en-ligne', 'online-services'), 'title' => $T('Démarches en ligne', 'Online services'),
            'description' => $T('Toutes vos démarches auprès de la mairie : état civil, papiers d’identité, urbanisme, écoles, élections. Pièces à fournir et délais.', 'All your city hall services: civil records, ID documents, planning, schools, elections. Required documents and processing times.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Démarches', 'Services'), 'title' => $T('Vos démarches, sans vous déplacer', 'Your paperwork, without the trip'), 'intro' => $T('La plupart des demandes se font en ligne. L’accueil de la mairie reste disponible pour vous aider.', 'Most requests can be made online. City Hall reception is still there to help you.')]),
                B::features($theme, ['items' => [
                    ['file-text', $T('État civil', 'Civil records'), $T('Copies et extraits d’actes de naissance, de mariage et de décès, reconnaissance, changement de prénom.', 'Copies and extracts of birth, marriage and death certificates, acknowledgments, name changes.'), $contact, $T('Faire une demande', 'Make a request')],
                    ['id-card', $T('Identité', 'Identity'), $T('Carte d’identité et passeport : pré-demande en ligne puis rendez-vous en mairie.', 'ID card and passport: pre-application online, then an appointment at City Hall.'), $contact, $T('Prendre rendez-vous', 'Book an appointment')],
                    ['heart', $T('Mariage et union civile', 'Marriage and civil union'), $T('Dépôt du dossier, choix de la date et de la salle de cérémonie.', 'Filing, choosing the date and the ceremony room.'), $contact, $T('Préparer le dossier', 'Prepare the file')],
                    ['house', $T('Urbanisme', 'Planning'), $T('Permis de construire, déclaration de travaux, certificat d’urbanisme.', 'Building permits, works declarations, planning certificates.'), $services, $T('En savoir plus', 'Learn more')],
                    ['vote', $T('Élections', 'Elections'), $T('Inscription sur les listes électorales, procurations, bureaux de vote.', 'Voter registration, proxy voting, polling stations.'), $contact, $T('S’inscrire', 'Register')],
                    ['school', $T('Écoles et périscolaire', 'Schools and after-school'), $T('Inscription scolaire, cantine, garderie et centre de loisirs.', 'School enrollment, canteen, childcare and holiday club.'), $services, $T('Inscrire mon enfant', 'Enroll my child')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Comment ça marche', 'How it works'), 'title' => $T('Une demande en ligne, étape par étape', 'An online request, step by step'), 'bg' => 'light', 'items' => [
                    [$T('Formulaire', 'Form'), $T('Choisissez votre démarche et remplissez le formulaire.', 'Choose your service and fill in the form.')],
                    [$T('Pièces', 'Documents'), $T('Joignez les justificatifs demandés, en photo ou en PDF.', 'Attach the supporting documents as photos or PDFs.')],
                    [$T('Suivi', 'Tracking'), $T('Un accusé de réception puis un message à chaque étape.', 'An acknowledgment, then a message at each step.')],
                    [$T('Réponse', 'Outcome'), $T('Document envoyé, ou rendez-vous pour le retirer.', 'Document sent, or an appointment to collect it.')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('À préparer', 'Have ready'), 'title' => $T('Les justificatifs les plus demandés', 'Most commonly requested documents'), 'bg' => 'white', 'groups' => [
                    [$T('Pour vous identifier', 'To prove your identity'), [
                        ['id-card', $T('Pièce d’identité en cours de validité', 'Valid ID document')],
                        ['house', $T('Justificatif de domicile de moins de 3 mois', 'Proof of address less than 3 months old')],
                        ['camera', $T('Photo d’identité conforme (papiers d’identité)', 'Compliant ID photo (for ID documents)')],
                    ]],
                    [$T('Délais indicatifs', 'Indicative processing times'), [
                        ['hourglass', $T('Copie d’acte d’état civil : 3 à 5 jours', 'Civil record copy: 3 to 5 days')],
                        ['hourglass', $T('Carte d’identité, passeport : 3 à 6 semaines', 'ID card, passport: 3 to 6 weeks')],
                        ['hourglass', $T('Permis de construire : 2 à 3 mois', 'Building permit: 2 to 3 months')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'intro' => $T('Vous ne trouvez pas votre démarche ? L’accueil vous oriente.', 'Can’t find what you need? Reception will point you in the right direction.'), 'buttons' => [[$T('Contacter la mairie', 'Contact City Hall'), $contact, 'outline']], 'bg' => 'light', 'items' => [
                    [$T('Les démarches en ligne sont-elles payantes ?', 'Do online services cost anything?'), $T('Non, les démarches proposées par la mairie sont gratuites. Méfiez-vous des sites privés qui facturent ces services.', 'No, City Hall services are free. Beware of private websites that charge for them.')],
                    [$T('Je n’ai pas d’ordinateur, comment faire ?', 'I don’t have a computer. What can I do?'), $T('Un point d’accès numérique avec un agent est disponible à l’accueil de la mairie, aux heures d’ouverture.', 'A digital access point with a staff member is available at City Hall reception during opening hours.')],
                    [$T('Puis-je faire une démarche pour un proche ?', 'Can I apply on behalf of a relative?'), $T('Oui pour certaines démarches, avec une procuration et la pièce d’identité de la personne concernée.', 'Yes for some services, with a signed authorization and the person’s ID.')],
                    [$T('Comment suivre l’avancement de ma demande ?', 'How do I track my request?'), $T('Vous recevez un numéro de dossier par e-mail ; l’accueil peut aussi vous renseigner par téléphone.', 'You receive a file number by email; reception can also update you by phone.')],
                ]]),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('services-municipaux', 'city-services'), 'title' => $T('Services municipaux', 'City services'),
            'description' => $T('État civil, urbanisme, collecte des déchets, écoles et périscolaire, action sociale, sport et culture : les services de la commune.', 'Civil records, planning, waste collection, schools and after-school care, social services, sport and culture: your city services.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Services', 'Services'), 'title' => $T('Les services de la commune', 'Your city services'), 'intro' => $T('Des équipes au service des habitants, de la naissance au grand âge.', 'Teams serving residents at every stage of life.')]),
                B::cards($theme, ['items' => [
                    ['mairie-service-etat-civil', 'file-text', $T('État civil et citoyenneté', 'Civil records and citizenship'), $T('Naissances, mariages, décès, papiers d’identité, élections et recensement.', 'Births, marriages, deaths, ID documents, elections and census.'), $online],
                    ['mairie-service-urbanisme', 'hammer', $T('Urbanisme et travaux', 'Planning and public works'), $T('Autorisations de construire, plan local d’urbanisme, voirie et chantiers.', 'Building permits, local zoning plan, roads and worksites.'), $online],
                    ['mairie-service-dechets', 'recycle', $T('Propreté et déchets', 'Waste and recycling'), $T('Collecte des ordures et du tri, encombrants, centre de recyclage.', 'Trash and recycling collection, bulky items, recycling center.')],
                    ['mairie-service-ecoles', 'school', $T('Écoles et jeunesse', 'Schools and youth'), $T('Écoles publiques, cantine, accueil périscolaire, centre de loisirs.', 'Public schools, canteen, after-school care, holiday club.')],
                    ['mairie-service-social', 'hand-heart', $T('Action sociale', 'Social services'), $T('Aides aux familles, aînés, logement et accompagnement des personnes fragiles.', 'Support for families, older people, housing and vulnerable residents.')],
                    ['mairie-service-sport', 'library', $T('Sport et culture', 'Sport and culture'), $T('Médiathèque, gymnases, piscine, salle des fêtes et événements.', 'Library, gyms, swimming pool, community hall and events.'), $community],
                ], 'more' => $T('Voir les démarches', 'See the services')]),
                M::table($theme, ['eyebrow' => $T('Déchets', 'Waste'), 'title' => $T('Calendrier de collecte', 'Collection schedule'), 'bg' => 'light', 'note' => $T('Sortez vos bacs la veille au soir, couvercle fermé. Jours fériés : collecte décalée au lendemain.', 'Put your bins out the night before, lids closed. Public holidays: collection moves to the next day.'), 'rows' => [
                    [$T('Secteur', 'Area'), $T('Ordures ménagères', 'Household trash'), $T('Tri sélectif', 'Recycling'), $T('Déchets verts', 'Garden waste')],
                    [$T('Centre-ville', 'Town center'), $T('Lundi et jeudi', 'Monday and Thursday'), $T('Mercredi', 'Wednesday'), $T('—', '—')],
                    [$T('Quartiers nord', 'North side'), $T('Mardi et vendredi', 'Tuesday and Friday'), $T('Jeudi', 'Thursday'), $T('Lundi (avril – novembre)', 'Monday (April – November)')],
                    [$T('Quartiers sud', 'South side'), $T('Mardi et vendredi', 'Tuesday and Friday'), $T('Lundi', 'Monday'), $T('Mercredi (avril – novembre)', 'Wednesday (April – November)')],
                    [$T('Hameaux', 'Outlying areas'), $T('Mercredi', 'Wednesday'), $T('Vendredi, une semaine sur deux', 'Every other Friday'), $T('Sur rendez-vous', 'By appointment')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Écoles', 'Schools'),
                    'title' => $T('Bien grandir dans nos écoles', 'Growing up well in our schools'),
                    'html' => '<p>'.e($T('La commune gère neuf écoles maternelles et élémentaires, la restauration scolaire et l’accueil avant et après la classe. Les repas sont préparés sur place, avec des produits locaux et un menu végétarien chaque semaine.', 'The town runs nine nursery and elementary schools, school meals and before- and after-school care. Meals are cooked on site with local produce, with a vegetarian menu every week.')).'</p>',
                    'bullets' => [$T('Tarifs de cantine selon les revenus du foyer', 'Canteen prices based on household income'), $T('Accueil périscolaire de 7 h 30 à 18 h 30', 'After-school care from 7:30 am to 6:30 pm'), $T('Centre de loisirs pendant les vacances', 'Holiday club during school breaks')],
                    'buttons' => [[$T('Inscrire mon enfant', 'Enroll my child'), $online]],
                    'photo' => ['mairie-ecole', 'school', $T('Enfants dans la cour d’une école primaire publique', 'Children in the playground of a public elementary school')],
                ]),
                B::schedule($theme, ['eyebrow' => $T('Équipements', 'Facilities'), 'title' => $T('Horaires des équipements', 'Facility opening hours'), 'bg' => 'light', 'groups' => [
                    [$T('Centre de recyclage', 'Recycling center'), [
                        ['recycle', $T('Lundi – samedi : 9 h – 12 h et 14 h – 18 h', 'Monday – Saturday: 9 am – 12 pm and 2 – 6 pm')],
                        ['truck', $T('Encombrants : enlèvement gratuit sur rendez-vous', 'Bulky items: free pickup by appointment')],
                    ]],
                    [$T('Médiathèque', 'Library'), [
                        ['book-open', $T('Mardi – samedi : 10 h – 18 h ; mercredi jusqu’à 19 h', 'Tuesday – Saturday: 10 am – 6 pm; Wednesday until 7 pm')],
                        ['wifi', $T('Wi-Fi et postes informatiques en libre accès', 'Free Wi-Fi and public computers')],
                    ]],
                ]]),
            ]];

        $pages[] = ['key' => 'council', 'slug' => $u('conseil-municipal', 'city-council'), 'title' => $T('Conseil municipal', 'City council'),
            'description' => $T('Le maire, les adjoints et les conseillers municipaux, les commissions, le calendrier des séances publiques et les décisions du conseil.', 'The mayor, deputy mayors and councillors, committees, the public meeting calendar and council decisions.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Vie démocratique', 'Local democracy'), 'title' => $T('Le conseil municipal', 'The city council'), 'intro' => $T('Vingt-neuf élus pour six ans, au service de la commune. Les séances sont publiques.', 'Twenty-nine councillors elected for six years to serve the town. Meetings are open to the public.')]),
                B::team($theme, ['eyebrow' => $T('La municipalité', 'The executive'), 'title' => $T('Le maire et les adjoints', 'The mayor and deputy mayors'), 'items' => [
                    ['[Prénom Nom]', $T('Maire', 'Mayor'), 'mairie-equipe-1'],
                    ['[Prénom Nom]', $T('1ʳᵉ adjointe, finances et administration', '1st deputy mayor, finance and administration'), 'mairie-equipe-2'],
                    ['[Prénom Nom]', $T('Adjoint à l’urbanisme et aux travaux', 'Deputy mayor, planning and public works'), 'mairie-equipe-3'],
                    ['[Prénom Nom]', $T('Adjointe à l’éducation et à la jeunesse', 'Deputy mayor, education and youth'), 'mairie-equipe-4'],
                    ['[Prénom Nom]', $T('Adjoint à l’environnement', 'Deputy mayor, environment'), 'mairie-equipe-5'],
                    ['[Prénom Nom]', $T('Adjointe aux solidarités', 'Deputy mayor, social affairs'), 'mairie-equipe-6'],
                    ['[Prénom Nom]', $T('Adjoint à la vie associative et au sport', 'Deputy mayor, community life and sport'), 'mairie-equipe-7'],
                    ['[Prénom Nom]', $T('Adjointe à la culture', 'Deputy mayor, culture'), 'mairie-equipe-8'],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Commissions', 'Committees'), 'title' => $T('Où se préparent les décisions', 'Where decisions are prepared'), 'bg' => 'light', 'items' => [
                    ['banknote', $T('Finances', 'Finance'), $T('Budget, impôts locaux, subventions et marchés publics.', 'Budget, local taxes, grants and public contracts.')],
                    ['construction', $T('Urbanisme et cadre de vie', 'Planning and environment'), $T('Projets d’aménagement, mobilités, espaces verts.', 'Development projects, transport, green spaces.')],
                    ['users', $T('Solidarités et éducation', 'Social affairs and education'), $T('Écoles, jeunesse, aînés, logement et santé.', 'Schools, youth, older people, housing and health.')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Séances', 'Meetings'), 'title' => $T('Prochaines séances publiques', 'Upcoming public meetings'), 'note' => $T('Les ordres du jour sont publiés cinq jours avant chaque séance, les comptes rendus dans la semaine qui suit.', 'Agendas are published five days before each meeting, and minutes within the following week.'), 'rows' => [
                    [$T('Date', 'Date'), $T('Ordre du jour principal', 'Main agenda item'), $T('Lieu', 'Venue')],
                    [$T('Jeudi 12 février, 19 h', 'Thursday, February 12, 7 pm'), $T('Orientations budgétaires', 'Budget guidelines'), $T('Salle du conseil', 'Council chamber')],
                    [$T('Jeudi 26 mars, 19 h', 'Thursday, March 26, 7 pm'), $T('Vote du budget primitif', 'Vote on the annual budget'), $T('Salle du conseil', 'Council chamber')],
                    [$T('Jeudi 21 mai, 19 h', 'Thursday, May 21, 7 pm'), $T('Plan de mobilité et pistes cyclables', 'Mobility plan and bike lanes'), $T('Salle des fêtes', 'Community hall')],
                ]]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Chaque habitant a le droit de savoir comment est dépensé l’argent public, et de donner son avis avant que les décisions soient prises.', 'Every resident has the right to know how public money is spent, and to have a say before decisions are made.'), 'source' => $T('— [Prénom Nom], maire de [Nom de la commune]', '— [First Last], mayor of [Town name]')]),
                B::cta($theme, ['title' => $T('Assistez au prochain conseil', 'Attend the next council meeting'), 'text' => $T('Les séances sont ouvertes à tous et retransmises en direct. Les comptes rendus sont publiés dans les actualités.', 'Meetings are open to all and streamed live. Minutes are published in the news section.'),
                    'buttons' => [[$T('Voir l’agenda', 'See the calendar'), $news, 'accent'], [$T('Écrire aux élus', 'Write to councillors'), $contact, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'community', 'slug' => $u('vie-associative', 'community-life'), 'title' => $T('Vie associative', 'Community life'),
            'description' => $T('Annuaire des associations, demandes de subvention, prêt de salles et de matériel municipal, forum des associations.', 'Community group directory, grant applications, loan of municipal rooms and equipment, community groups fair.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Vie associative', 'Community life'), 'title' => $T('Une commune riche de ses associations', 'A town powered by its community groups'), 'intro' => $T('Plus de 140 associations font vivre le sport, la culture, la solidarité et l’environnement.', 'Over 140 groups bring sport, culture, solidarity and the environment to life.')]),
                B::features($theme, ['eyebrow' => $T('Annuaire', 'Directory'), 'title' => $T('Trouver une association', 'Find a community group'), 'cols' => 4, 'items' => [
                    ['dumbbell', $T('Sport', 'Sport'), $T('Une quarantaine de clubs, de l’athlétisme au tennis de table.', 'Some forty clubs, from athletics to table tennis.')],
                    ['palette', $T('Culture et loisirs', 'Culture and leisure'), $T('Musique, théâtre, arts, patrimoine et jeux.', 'Music, theater, arts, heritage and games.')],
                    ['heart-handshake', $T('Solidarité', 'Solidarity'), $T('Aide alimentaire, soutien scolaire, entraide.', 'Food aid, tutoring, mutual support.')],
                    ['sprout', $T('Environnement', 'Environment'), $T('Jardins partagés, nature, réparation et réemploi.', 'Community gardens, nature, repair and reuse.')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Équipements', 'Facilities'), 'title' => $T('Salles et matériel à disposition', 'Rooms and equipment available'), 'bg' => 'light', 'items' => [
                    ['mairie-salle-fetes', 'party-popper', $T('Salle des fêtes', 'Community hall'), $T('300 personnes, cuisine équipée. Gratuite une fois par an pour chaque association.', '300 people, equipped kitchen. Free once a year for every registered group.')],
                    ['mairie-maison-associations', 'building', $T('Maison des associations', 'Community center'), $T('Bureaux partagés, salles de réunion et domiciliation postale.', 'Shared offices, meeting rooms and a mailing address.')],
                    ['mairie-materiel', 'package', $T('Prêt de matériel', 'Equipment loan'), $T('Tables, chaises, barnums, sonorisation pour vos événements.', 'Tables, chairs, marquees and sound systems for your events.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Subventions', 'Grants'), 'title' => $T('Demander une subvention', 'Applying for a grant'), 'items' => [
                    [$T('Dossier', 'Application'), $T('Formulaire, budget prévisionnel et bilan de l’année, avant le 31 janvier.', 'Form, budget forecast and annual report, by January 31.')],
                    [$T('Instruction', 'Review'), $T('Étude par le service vie associative et la commission concernée.', 'Review by the community life team and the relevant committee.')],
                    [$T('Vote', 'Vote'), $T('Décision du conseil municipal au moment du budget.', 'City council decision when the budget is voted.')],
                    [$T('Versement', 'Payment'), $T('Paiement, puis compte rendu de l’utilisation des fonds.', 'Payment, followed by a report on how the funds were used.')],
                ]]),
                B::cta($theme, ['title' => $T('Forum des associations', 'Community groups fair'), 'text' => $T('Le premier samedi de septembre, toutes les associations vous attendent au gymnase municipal pour les inscriptions de la saison.', 'On the first Saturday of September, every group will be at the municipal gym to sign up new members for the season.'),
                    'buttons' => [[$T('Inscrire mon association', 'Register my group'), $contact, 'accent'], [$T('Voir l’agenda', 'See the calendar'), $news, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites-agenda', 'news-events'), 'title' => $T('Actualités et agenda', 'News and events'),
            'description' => $T('Actualités de la commune, travaux en cours, décisions du conseil municipal, avis officiels et agenda des événements, réunions publiques et marchés.', 'Town news, current roadworks, city council decisions, official notices and the calendar of events, public meetings and markets.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Toute l’actualité de la commune', 'All the news from your town'), 'intro' => $T('Travaux, décisions, événements et avis officiels.', 'Roadworks, decisions, events and official notices.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, $agenda + ['bg' => 'light']),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('La vie de la commune', 'Life in town'), 'photos' => [
                    ['mairie-galerie-1', 'store', $T('Marché de producteurs sur une place de centre-ville', 'Farmers’ market on a town square')],
                    ['mairie-galerie-2', 'tree-pine', $T('Habitants plantant des arbres dans un parc municipal', 'Residents planting trees in a city park')],
                    ['mairie-galerie-3', 'party-popper', $T('Fête de quartier avec des familles dans une rue piétonne', 'Neighborhood festival with families on a pedestrian street')],
                ]]),
            ]];

        $pages[] = ['key' => 'report', 'slug' => $u('signaler-un-probleme', 'report-an-issue'), 'title' => $T('Signaler un problème', 'Report an issue'),
            'description' => $T('Signalez un problème sur l’espace public : nid-de-poule, éclairage en panne, dépôt sauvage, mobilier abîmé. Suivi sous 48 heures.', 'Report a problem in a public space: pothole, broken street light, illegal dumping, damaged street furniture. Follow-up within 48 hours.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Signalement', 'Report'), 'title' => $T('Signaler un problème dans la commune', 'Report a problem in town'), 'intro' => $T('Pour une urgence (danger immédiat, incendie, accident), appelez les numéros d’urgence de votre pays.', 'For an emergency (immediate danger, fire, accident), call your local emergency number.')]),
                B::features($theme, ['eyebrow' => $T('Que signaler ?', 'What can you report?'), 'title' => $T('Les signalements les plus fréquents', 'The most common reports'), 'items' => [
                    ['construction', $T('Voirie', 'Roads and sidewalks'), $T('Nid-de-poule, trottoir abîmé, panneau tombé.', 'Potholes, damaged sidewalks, fallen signs.')],
                    ['lightbulb', $T('Éclairage public', 'Street lights'), $T('Lampadaire éteint ou qui clignote.', 'A street light that is out or flickering.')],
                    ['recycle', $T('Propreté', 'Cleanliness'), $T('Dépôt sauvage, poubelle pleine, tag.', 'Illegal dumping, full bins, graffiti.')],
                    ['tree-pine', $T('Espaces verts', 'Green spaces'), $T('Arbre dangereux, aire de jeux abîmée.', 'Dangerous tree, damaged playground.')],
                    ['droplet', $T('Eau et voirie', 'Water and drainage'), $T('Fuite sur la voie publique, avaloir bouché.', 'Leak on the street, blocked drain.')],
                    ['car', $T('Stationnement', 'Parking'), $T('Véhicule abandonné, stationnement gênant.', 'Abandoned vehicle, obstructive parking.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Suivi', 'Follow-up'), 'title' => $T('Ce que devient votre signalement', 'What happens to your report'), 'bg' => 'light', 'items' => [
                    [$T('Réception', 'Received'), $T('Un accusé de réception vous est envoyé immédiatement.', 'You get an acknowledgment right away.')],
                    [$T('Transmission', 'Assigned'), $T('Le service concerné est prévenu sous 48 heures.', 'The relevant team is alerted within 48 hours.')],
                    [$T('Intervention', 'Action'), $T('Réparation ou intervention planifiée selon l’urgence.', 'Repair or action scheduled according to urgency.')],
                    [$T('Clôture', 'Closed'), $T('Vous êtes informé de la résolution du problème.', 'You are told when the problem is solved.')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Faire un signalement', 'Submit a report'), 'intro' => $T('Précisez le lieu exact (adresse, repère) et décrivez le problème. Une photo aide nos équipes : envoyez-la par e-mail en rappelant votre nom.', 'Give the exact location (address, landmark) and describe the problem. A photo helps our teams: email it to us with your name.'),
                    'items' => [
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['phone', $T('Services techniques : +00 0 00 00 00 00', 'Public works: +00 0 00 00 00 00'), 'tel:+000000000000'],
                        ['clock', $T('Traitement sous 48 heures ouvrées', 'Handled within two business days')],
                    ],
                    'subject' => true,
                    'button' => $T('Envoyer le signalement', 'Send the report'),
                    'bg' => 'white',
                ]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez la mairie de [Nom de la commune] : adresse, horaires d’ouverture, téléphone et formulaire de contact.', 'Contact [Town name] City Hall: address, opening hours, phone and contact form.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Contacter la mairie', 'Contact City Hall'), 'intro' => $T('Nos services vous répondent sous cinq jours ouvrés.', 'Our teams reply within five business days.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['file-text', $T('Une démarche', 'A form or document'), $T('La plupart des demandes se font directement en ligne.', 'Most requests can be made directly online.'), $online, $T('Démarches en ligne', 'Online services')],
                    ['triangle-alert', $T('Un problème dans la rue', 'A problem on the street'), $T('Voirie, éclairage, propreté : utilisez le formulaire dédié.', 'Roads, lighting, litter: use the dedicated form.'), $report, $T('Signaler', 'Report it')],
                    ['users', $T('Écrire aux élus', 'Write to councillors'), $T('Le maire et les adjoints reçoivent sur rendez-vous.', 'The mayor and deputy mayors meet residents by appointment.'), $council, $T('Le conseil municipal', 'City council')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Hôtel de ville', 'City Hall'), 'intro' => $T('Accueil du public sans rendez-vous aux heures d’ouverture ; urbanisme et papiers d’identité sur rendez-vous.', 'Walk-in reception during opening hours; planning and ID documents by appointment.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h 30 – 12 h et 13 h 30 – 17 h ; samedi, 9 h – 12 h', 'Monday – Friday, 8:30 am – 12 pm and 1:30 – 5 pm; Saturday, 9 am – 12 pm')],
                        ['accessibility', $T('Bâtiment accessible aux personnes à mobilité réduite', 'Building fully wheelchair accessible')],
                    ],
                    'social' => ['facebook', 'instagram', 'youtube'],
                    'subject' => true,
                    'bg' => 'light',
                ]),
            ]];

        $blog = [
            'base' => $u('actualites-agenda', 'news-events'),
            'title' => $T('Actualités', 'News'),
            'categories' => ['travaux' => $T('Travaux', 'Roadworks'), 'conseil' => $T('Conseil municipal', 'City council'), 'vie-locale' => $T('Vie locale', 'Local life')],
            'posts' => [
                ['key' => 'travaux', 'slug' => $u('travaux-rue-principale', 'main-street-works'), 'category' => 'travaux', 'tags' => [$T('Circulation', 'Traffic')],
                    'title' => $T('Travaux de la rue principale : ce qui change', 'Main street works: what changes'),
                    'excerpt' => $T('Trottoirs élargis, piste cyclable et nouvel éclairage : calendrier et déviations.', 'Wider sidewalks, a bike lane and new lighting: schedule and detours.'),
                    'photo' => ['mairie-blog-travaux', 'construction', $T('Chantier de rénovation d’une rue avec ouvriers et engins', 'Street renovation worksite with workers and machinery')],
                    'heading' => $T('Calendrier', 'Schedule'),
                    'body' => [
                        $T('Les travaux de requalification de la rue principale débutent le 3 mars pour une durée de quatre mois, en trois tronçons successifs.', 'Work to redesign the main street starts on March 3 and will last four months, in three successive sections.'),
                        $T('La circulation sera maintenue en sens unique ; les commerces restent accessibles à pied pendant toute la durée du chantier.', 'Traffic will continue one way; shops remain accessible on foot throughout the works.'),
                        $T('Une réunion d’information pour les riverains aura lieu le 25 février à la salle des fêtes.', 'An information meeting for local residents will be held on February 25 at the community hall.'),
                    ]],
                ['key' => 'budget', 'slug' => $u('budget-vote-conseil', 'budget-approved-by-council'), 'category' => 'conseil', 'tags' => [$T('Budget', 'Budget')],
                    'title' => $T('Le conseil municipal adopte le budget de l’année', 'City council approves this year’s budget'),
                    'excerpt' => $T('Écoles, mobilités douces et rénovation énergétique : les grandes lignes.', 'Schools, active travel and energy renovation: the main points.'),
                    'photo' => ['mairie-blog-budget', 'landmark', $T('Élus siégeant autour de la table d’une salle de conseil municipal', 'Councillors seated around the table in a council chamber')],
                    'quote' => $T('« Un budget sans hausse des taux d’imposition, et avec un tiers des investissements consacré aux écoles. »', '“A budget with no tax rate increase, and a third of investment going to schools.”'),
                    'body' => [
                        $T('Réuni en séance publique, le conseil municipal a adopté le budget primitif à la majorité.', 'Meeting in public session, the city council approved the annual budget by a majority vote.'),
                        $T('Les investissements portent en priorité sur la rénovation de deux écoles, la création de pistes cyclables et l’isolation des bâtiments communaux.', 'Investment focuses on renovating two schools, creating bike lanes and insulating municipal buildings.'),
                        $T('Le document complet et le compte rendu de la séance sont consultables à l’accueil de la mairie.', 'The full document and the meeting minutes are available at City Hall reception.'),
                    ]],
                ['key' => 'jardins', 'slug' => $u('jardins-partages', 'community-gardens'), 'category' => 'vie-locale', 'tags' => [$T('Environnement', 'Environment')],
                    'title' => $T('Trois nouveaux jardins partagés ouvrent au printemps', 'Three new community gardens open this spring'),
                    'excerpt' => $T('Des parcelles pour cultiver, apprendre et se rencontrer dans chaque quartier.', 'Plots to grow food, learn and meet neighbors in every part of town.'),
                    'photo' => ['mairie-blog-jardins', 'sprout', $T('Voisins de tous âges jardinant ensemble dans un jardin partagé', 'Neighbors of all ages gardening together in a community garden')],
                    'heading' => $T('Comment participer', 'How to take part'),
                    'body' => [
                        $T('Trois jardins partagés ouvriront en avril, gérés par des associations d’habitants avec le soutien de la commune.', 'Three community gardens will open in April, run by residents’ associations with support from the town.'),
                        $T('Les parcelles sont attribuées pour un an, en priorité aux habitants sans jardin. Des ateliers de jardinage sont proposés chaque mois.', 'Plots are allocated for one year, with priority for residents without a garden. Gardening workshops run every month.'),
                        $T('Inscriptions auprès du service vie associative jusqu’au 15 mars.', 'Sign up with the community life team until March 15.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Démarches', 'Services'), 'url' => $online],
                    ['label' => $T('Services', 'City services'), 'url' => $services],
                    ['label' => $T('Conseil municipal', 'City council'), 'url' => $council],
                    ['label' => $T('Vie associative', 'Community'), 'url' => $community],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => $T('Signaler', 'Report'), 'url' => $report],
                ],
                'cta_label' => $T('Démarches en ligne', 'Online services'),
                'cta_url' => $online,
                'footer_text' => $T('Mairie de [Nom de la commune] : des services publics accessibles, une gestion transparente et une commune à l’écoute de ses habitants.', '[Town name] City Hall: accessible public services, transparent management and a town that listens to its residents.'),
                'footer_links' => [
                    ['label' => $T('Démarches en ligne', 'Online services'), 'url' => $online],
                    ['label' => $T('Signaler un problème', 'Report an issue'), 'url' => $report],
                    ['label' => $T('Conseil municipal', 'City council'), 'url' => $council],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
