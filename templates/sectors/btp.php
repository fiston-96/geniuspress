<?php

/*
 * Pack « Entreprise de BTP et construction » : 8 pages, en français et en anglais.
 * Construction neuve, génie civil, réhabilitation ; références de chantiers,
 * sécurité et qualité, certifications, appels d'offres, recrutement.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'entreprise). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Artisans et BTP', 'en' => 'Trades and construction'],
        'icon' => 'hammer',
        'keywords' => ['btp', 'construction', 'bâtiment', 'génie civil', 'travaux publics', 'gros œuvre', 'chantier', 'entreprise générale', 'réhabilitation', 'voirie', 'ouvrage d’art', 'appel d’offres', 'construction company', 'general contractor', 'civil engineering', 'building', 'infrastructure', 'public works', 'tender'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $expertise = $u('/metiers', '/expertise');
        $projects = $u('/references', '/projects');
        $quality = $u('/securite-qualite', '/safety-quality');
        $about = $u('/entreprise', '/company');
        $tenders = $u('/appels-offres', '/tenders');
        $careers = $u('/recrutement', '/careers');
        $contact = '/contact';

        $trades = [
            ['building-2', $T('Construction neuve', 'New construction'), $T('Logements collectifs, bureaux, établissements scolaires et de santé, en entreprise générale ou en lots.', 'Apartment buildings, offices, schools and healthcare facilities, as general contractor or by trade package.')],
            ['construction', $T('Génie civil', 'Civil engineering'), $T('Ouvrages d’art, fondations spéciales, réservoirs, murs de soutènement et dalles industrielles.', 'Bridges and structures, special foundations, tanks, retaining walls and industrial slabs.')],
            ['map', $T('Voirie et réseaux', 'Roads and utilities'), $T('Terrassement, voiries, assainissement, adduction d’eau et réseaux secs.', 'Earthworks, roads, sewers, water mains and dry utilities.')],
            ['factory', $T('Bâtiments industriels', 'Industrial buildings'), $T('Entrepôts, ateliers et plateformes logistiques en charpente métallique ou béton.', 'Warehouses, workshops and logistics hubs in steel frame or concrete.')],
            ['refresh-cw', $T('Réhabilitation', 'Rehabilitation'), $T('Renforcement de structures, restructuration lourde et mise aux normes de bâtiments occupés.', 'Structural strengthening, major restructuring and code upgrades of occupied buildings.')],
            ['pencil', $T('Conception-réalisation', 'Design-build'), $T('Un contrat unique avec notre bureau d’études intégré, de l’esquisse à la livraison.', 'A single contract with our in-house design office, from sketch to handover.')],
        ];
        $projectCards = [
            ['btp-chantier-logements', 'building-2', $T('Résidence de 120 logements', '120-unit residential complex'), $T('Gros œuvre et corps d’état en entreprise générale, livrée en 22 mois.', 'Structural work and all trades as general contractor, delivered in 22 months.')],
            ['btp-chantier-pont', 'construction', $T('Pont routier de 85 mètres', '280-foot road bridge'), $T('Ouvrage en béton précontraint sur une rivière, avec maintien de la circulation.', 'Prestressed concrete bridge over a river, with traffic kept open.')],
            ['btp-chantier-entrepot', 'warehouse', $T('Plateforme logistique de 18 000 m²', '190,000 sq ft logistics hub'), $T('Charpente métallique, dallage industriel et quais de chargement.', 'Steel frame, industrial slab and loading docks.')],
            ['btp-chantier-ecole', 'school', $T('Groupe scolaire de 24 classes', '24-classroom school campus'), $T('Construction neuve en site occupé, livrée avant la rentrée.', 'New build next to an operating school, handed over before term started.')],
            ['btp-chantier-voirie', 'map', $T('Aménagement d’une avenue urbaine', 'Urban avenue redevelopment'), $T('3 km de voirie, assainissement, éclairage et pistes cyclables.', '2 miles of roadway, drainage, lighting and bike lanes.')],
            ['btp-chantier-rehabilitation', 'refresh-cw', $T('Réhabilitation d’un hôpital', 'Hospital rehabilitation'), $T('Renforcement parasismique et restructuration de deux ailes en activité.', 'Seismic retrofit and restructuring of two wings while in operation.')],
        ];
        $cta = ['title' => $T('Un projet de construction ?', 'Planning a construction project?'), 'text' => $T('Maîtres d’ouvrage publics et privés : consultez-nous dès la phase d’études pour sécuriser coûts et délais.', 'Public and private owners: involve us from the design stage to secure cost and schedule.'),
            'buttons' => [[$T('Consulter l’entreprise', 'Contact our team'), $contact, 'accent'], [$T('Nos références', 'Our projects'), $projects, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Entreprise générale de BTP : construction neuve, génie civil, voirie et réhabilitation. Sécurité, qualité et respect des délais sur chaque chantier.', 'General contractor for building and civil works: new construction, civil engineering, roads and rehabilitation. Safety, quality and on-time delivery.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Bâtiment, génie civil et travaux publics', 'Building, civil engineering and public works'),
                    'title' => $T('Nous construisons des ouvrages qui durent', 'We build structures that last'),
                    'text' => $T('Entreprise générale de construction, nous réalisons logements, équipements publics, ouvrages d’art et infrastructures, avec nos propres équipes, notre matériel et un bureau d’études intégré.', 'As a general contractor, we deliver housing, public facilities, bridges and infrastructure with our own crews, our own equipment and an in-house design office.'),
                    'buttons' => [[$T('Consulter l’entreprise', 'Contact our team'), $contact], [$T('Nos références', 'Our projects'), $projects]],
                    'photo' => ['btp-accueil', 'hard-hat', $T('Grand chantier de bâtiment avec grue à tour et ouvriers en casque sur une dalle de béton', 'Large building site with a tower crane and workers in hard hats on a concrete slab')],
                ]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [35, '', $T('ans d’expérience', 'years of experience')],
                    [480, '', $T('collaborateurs', 'employees')],
                    [600, '+', $T('ouvrages livrés', 'projects delivered')],
                    [100, ' %', $T('des chantiers avec un plan de prévention', 'of sites with a safety plan')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Nos métiers', 'What we do'), 'title' => $T('Du terrassement à la livraison clés en main', 'From earthworks to turnkey handover'), 'items' => array_slice($trades, 0, 3), 'bg' => 'light']),
                B::cards($theme, ['eyebrow' => $T('Références', 'Projects'), 'title' => $T('Quelques chantiers récents', 'Recent projects'), 'more' => $T('Toutes nos références', 'All our projects'), 'items' => array_map(fn ($c) => [...$c, $projects], array_slice($projectCards, 0, 3))]),
                B::split($theme, [
                    'eyebrow' => $T('Sécurité avant tout', 'Safety first'),
                    'title' => $T('Zéro accident, notre premier objectif', 'Zero harm, our first goal'),
                    'html' => '<p>'.e($T('Chaque chantier dispose d’un plan de prévention, d’un animateur sécurité et d’un quart d’heure sécurité chaque matin. Nos certifications qualité, sécurité et environnement sont auditées chaque année.', 'Every site has a prevention plan, a safety coordinator and a toolbox talk every morning. Our quality, safety and environmental certifications are audited every year.')).'</p>',
                    'bullets' => [$T('Système de management certifié', 'Certified management system'), $T('Formation sécurité pour chaque compagnon', 'Safety training for every worker'), $T('Gestion et tri des déchets de chantier', 'Site waste management and sorting')],
                    'buttons' => [[$T('Sécurité et qualité', 'Safety and quality'), $quality, 'outline']],
                    'photo' => ['btp-securite', 'shield-check', $T('Chef de chantier en gilet haute visibilité animant une réunion sécurité avec son équipe', 'Site manager in a high-visibility vest leading a safety briefing with the crew')],
                    'reverse' => true,
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Ce que nos clients attendent de nous', 'What our clients count on'), 'bg' => 'dark', 'items' => [
                    ['calendar-check', $T('Délais tenus', 'On schedule'), $T('Un planning détaillé, suivi chaque semaine avec le maître d’ouvrage.', 'A detailed schedule, reviewed weekly with the owner.')],
                    ['calculator', $T('Coûts maîtrisés', 'Cost control'), $T('Un chiffrage précis et des avenants justifiés et validés.', 'Accurate estimates and change orders that are justified and approved.')],
                    ['award', $T('Qualité contrôlée', 'Quality assured'), $T('Plan d’assurance qualité et contrôles à chaque étape clé.', 'Quality plan and inspections at every key stage.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'expertise', 'slug' => $u('metiers', 'expertise'), 'title' => $T('Nos métiers', 'Our expertise'),
            'description' => $T('Construction neuve, génie civil, voirie et réseaux, bâtiments industriels, réhabilitation et conception-réalisation : nos métiers et nos moyens.', 'New construction, civil engineering, roads and utilities, industrial buildings, rehabilitation and design-build: our expertise and resources.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nos métiers', 'Our expertise'), 'title' => $T('Six métiers complémentaires', 'Six complementary disciplines'), 'intro' => $T('Nos équipes travaillent ensemble pour proposer une offre globale, de la conception à la maintenance.', 'Our teams work together to offer a complete service, from design to maintenance.')]),
                B::features($theme, ['items' => $trades, 'cols' => 3]),
                B::split($theme, [
                    'eyebrow' => $T('Bureau d’études intégré', 'In-house design office'),
                    'title' => $T('Des ingénieurs au service du chantier', 'Engineers working for the site'),
                    'html' => '<p>'.e($T('Ingénieurs structure, méthodes et économistes travaillent en maquette numérique (BIM) pour optimiser les ouvrages, anticiper les phases de chantier et fiabiliser les coûts.', 'Structural engineers, methods engineers and estimators work in BIM to optimize structures, plan site phases and firm up costs.')).'</p>',
                    'bullets' => [$T('Études de structure et variantes', 'Structural design and value engineering'), $T('Méthodes et phasage de chantier', 'Construction methods and phasing'), $T('Maquette numérique partagée', 'Shared digital model')],
                    'photo' => ['btp-bureau-etudes', 'monitor', $T('Ingénieurs examinant une maquette numérique de bâtiment sur un grand écran', 'Engineers reviewing a 3D building model on a large screen')],
                    'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('Conduite de projet', 'Project delivery'), 'title' => $T('Les grandes étapes d’un chantier', 'The main stages of a project'), 'items' => [
                    [$T('Études', 'Design'), $T('Analyse du programme, variantes techniques et chiffrage.', 'Program review, technical options and cost estimate.')],
                    [$T('Préparation', 'Preparation'), $T('Installation de chantier, plans d’exécution et approvisionnements.', 'Site setup, shop drawings and procurement.')],
                    [$T('Réalisation', 'Construction'), $T('Exécution des travaux, contrôles qualité et réunions hebdomadaires.', 'Works execution, quality inspections and weekly meetings.')],
                    [$T('Livraison', 'Handover'), $T('Réception, levée des réserves et dossier des ouvrages exécutés.', 'Acceptance, punch list and as-built documentation.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'projects', 'slug' => $u('references', 'projects'), 'title' => $T('Références', 'Projects'),
            'description' => $T('Logements, ouvrages d’art, plateformes logistiques, écoles, voiries et hôpitaux : nos références de chantiers et le journal de nos opérations.', 'Housing, bridges, logistics hubs, schools, roads and hospitals: our project references and site news.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Références', 'Projects'), 'title' => $T('Des ouvrages livrés, des clients fidèles', 'Projects delivered, clients who come back'), 'intro' => $T('Une sélection de chantiers réalisés pour des maîtres d’ouvrage publics et privés.', 'A selection of projects completed for public and private owners.')]),
                B::cards($theme, ['items' => $projectCards]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Un chantier exigeant, mené en site occupé sans une seule interruption de service. L’équipe a tenu ses engagements du premier au dernier jour.', 'A demanding project, carried out in an occupied building without a single service interruption. The team kept its commitments from day one to the last.'), 'source' => $T('— [Prénom Nom], directeur de l’immobilier d’un maître d’ouvrage public', '— [First Last], real estate director at a public owner')]),
                B::posts($theme, ['source' => 'current', 'eyebrow' => $T('Journal de chantier', 'Site news'), 'title' => $T('L’actualité de nos chantiers', 'News from our sites'), 'count' => 6, 'more' => $T('Lire la suite', 'Read more')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'quality', 'slug' => $u('securite-qualite', 'safety-quality'), 'title' => $T('Sécurité et qualité', 'Safety and quality'),
            'description' => $T('Prévention des risques, plan qualité, environnement et certifications : comment nous construisons en sécurité et dans le respect des normes.', 'Risk prevention, quality plans, environment and certifications: how we build safely and to standard.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Sécurité, qualité, environnement', 'Safety, quality, environment'), 'title' => $T('Construire sans compromis', 'Building without compromise'), 'intro' => $T('Nos exigences s’appliquent à nos équipes comme à nos sous-traitants.', 'Our standards apply to our own crews and to our subcontractors alike.')]),
                M::chart($theme, [
                    'eyebrow' => $T('Prévention', 'Prevention'),
                    'title' => $T('Des accidents en baisse constante', 'Injuries steadily falling'),
                    'html' => '<p>'.e($T('Accueil sécurité de chaque nouvel arrivant, visites de chantier par la direction et analyse de chaque presque-accident : notre taux de fréquence a été divisé par quatre en cinq ans.', 'Safety induction for every newcomer, site walks by management and analysis of every near miss: our injury rate has been cut by four in five years.')).'</p>',
                    'bullets' => [$T('Quart d’heure sécurité quotidien', 'Daily toolbox talk'), $T('Équipements de protection fournis et contrôlés', 'Protective equipment supplied and checked'), $T('Droit d’arrêt pour tout danger grave', 'Stop-work authority for any serious hazard')],
                    'chart' => ['kind' => 'bar', 'title' => $T('Taux de fréquence des accidents avec arrêt', 'Lost-time injury frequency rate'), 'rows' => [['2020', '3.8'], ['2021', '2.9'], ['2022', '2.1'], ['2023', '1.4'], ['2024', '0.9']]],
                ]),
                B::features($theme, ['eyebrow' => $T('Certifications', 'Certifications'), 'title' => $T('Un système de management audité', 'An audited management system'), 'intro' => $T('Certificats disponibles sur simple demande pour vos dossiers de consultation.', 'Certificates available on request for your tender files.'), 'bg' => 'light', 'cols' => 4, 'items' => [
                    ['award', $T('Qualité', 'Quality'), $T('Certification ISO 9001, certificat [N° de certificat].', 'ISO 9001 certification, certificate [Certificate no.].')],
                    ['hard-hat', $T('Santé et sécurité', 'Health and safety'), $T('Certification ISO 45001, certificat [N° de certificat].', 'ISO 45001 certification, certificate [Certificate no.].')],
                    ['leaf', $T('Environnement', 'Environment'), $T('Certification ISO 14001, certificat [N° de certificat].', 'ISO 14001 certification, certificate [Certificate no.].')],
                    ['id-card', $T('Qualifications', 'Licenses'), $T('Qualifications professionnelles bâtiment et travaux publics [N° de qualification].', 'Building and public works contractor licenses [License no.].')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Environnement', 'Environment'), 'title' => $T('Des chantiers plus responsables', 'Greener construction sites'), 'items' => [
                    ['recycle', $T('Déchets valorisés', 'Waste recovered'), $T('Tri sur site et filières de recyclage pour le béton, le métal et le bois.', 'On-site sorting and recycling streams for concrete, metal and wood.')],
                    ['droplet', $T('Eau préservée', 'Water protected'), $T('Bacs de rétention, lavage des toupies en circuit fermé.', 'Spill containment, closed-loop washing of mixer trucks.')],
                    ['users', $T('Riverains respectés', 'Neighbors respected'), $T('Horaires adaptés, réduction du bruit et information régulière.', 'Adapted hours, noise reduction and regular updates.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('entreprise', 'company'), 'title' => $T('L’entreprise', 'Company'),
            'description' => $T('Notre histoire depuis la création, nos valeurs, notre direction et nos implantations : une entreprise de construction indépendante et solide.', 'Our story since day one, our values, our leadership and our locations: an independent, financially sound construction company.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’entreprise', 'Company'), 'title' => $T('Trente-cinq ans de chantiers', 'Thirty-five years of building'), 'intro' => $T('Une entreprise indépendante, détenue par ses dirigeants et ses salariés.', 'An independent company, owned by its managers and employees.')]),
                M::timeline($theme, ['eyebrow' => $T('Notre histoire', 'Our story'), 'title' => $T('Les grandes étapes', 'Milestones'), 'items' => [
                    ['1990', $T('Création', 'Founded'), $T('Une entreprise de maçonnerie de douze compagnons.', 'A masonry business with twelve workers.')],
                    ['2001', $T('Génie civil', 'Civil engineering'), $T('Premiers ouvrages d’art et création du service travaux publics.', 'First bridges and launch of the public works division.')],
                    ['2012', $T('Bureau d’études', 'Design office'), $T('Intégration d’ingénieurs et premières opérations en conception-réalisation.', 'Engineers join in-house and first design-build contracts.')],
                    ['2024', $T('Trois agences', 'Three branches'), $T('480 collaborateurs et un parc matériel en propre.', '480 employees and a company-owned equipment fleet.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Nos moyens', 'Our resources'),
                    'title' => $T('Nos équipes, notre matériel', 'Our crews, our equipment'),
                    'html' => '<p>'.e($T('Nous réalisons nous-mêmes l’essentiel du gros œuvre grâce à nos compagnons et à notre parc : grues, engins de terrassement, coffrages et centrale à béton. Cette maîtrise est la meilleure garantie de qualité et de délais.', 'We carry out most structural work ourselves with our own crews and fleet: cranes, earthmoving equipment, formwork and a batching plant. This control is the best guarantee of quality and schedule.')).'</p><p>'.e($T('Société immatriculée sous le numéro [N° d’immatriculation].', 'Company registration number [Registration no.].')).'</p>',
                    'photo' => ['btp-parc-materiel', 'truck', $T('Parc d’engins de chantier alignés : pelles hydrauliques, chargeuses et camions-bennes', 'Row of construction equipment: excavators, loaders and dump trucks')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::team($theme, ['eyebrow' => $T('Direction', 'Leadership'), 'title' => $T('Le comité de direction', 'The leadership team'), 'items' => [
                    ['[Prénom Nom]', $T('Président-directeur général', 'Chief executive officer'), 'btp-equipe-1'],
                    ['[Prénom Nom]', $T('Directrice des travaux', 'Director of operations'), 'btp-equipe-2'],
                    ['[Prénom Nom]', $T('Directeur du bureau d’études', 'Head of engineering'), 'btp-equipe-3'],
                    ['[Prénom Nom]', $T('Directrice QHSE', 'Head of QHSE'), 'btp-equipe-4'],
                ]]),
                B::cta($theme, ['title' => $T('Construisons ensemble', 'Let’s build together'), 'text' => $T('Maîtres d’ouvrage, architectes, bureaux d’études : parlons de votre prochaine opération.', 'Owners, architects, engineering firms: let’s talk about your next project.'),
                    'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'accent'], [$T('Rejoindre nos équipes', 'Join our teams'), $careers, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'tenders', 'slug' => $u('appels-offres', 'tenders'), 'title' => $T('Appels d’offres', 'Tenders'),
            'description' => $T('Maîtres d’ouvrage : nos capacités et nos documents de candidature. Sous-traitants et fournisseurs : nos consultations en cours et le référencement.', 'Owners: our capabilities and prequalification documents. Subcontractors and suppliers: our open bid requests and how to register.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Appels d’offres', 'Tenders'), 'title' => $T('Maîtres d’ouvrage, sous-traitants, fournisseurs', 'Owners, subcontractors, suppliers'), 'intro' => $T('Tout ce qu’il faut pour nous consulter ou travailler avec nous sur nos chantiers.', 'Everything you need to invite us to bid or to work with us on our sites.')]),
                M::table($theme, ['eyebrow' => $T('Pour les maîtres d’ouvrage', 'For owners'), 'title' => $T('Nos capacités en un coup d’œil', 'Our capabilities at a glance'), 'rows' => [
                    [$T('Domaine', 'Area'), $T('Montant des marchés', 'Contract size'), $T('Références récentes', 'Recent references')],
                    [$T('Bâtiment neuf', 'New buildings'), $T('1 à 40 M€', '$1M to $45M'), $T('Logements, écoles, bureaux', 'Housing, schools, offices')],
                    [$T('Génie civil', 'Civil engineering'), $T('0,5 à 25 M€', '$0.5M to $28M'), $T('Ponts, réservoirs, fondations', 'Bridges, tanks, foundations')],
                    [$T('Voirie et réseaux', 'Roads and utilities'), $T('0,2 à 15 M€', '$0.2M to $17M'), $T('Avenues, assainissement, eau potable', 'Avenues, sewers, drinking water')],
                    [$T('Réhabilitation', 'Rehabilitation'), $T('0,3 à 20 M€', '$0.3M to $22M'), $T('Hôpitaux, bâtiments publics', 'Hospitals, public buildings')],
                ], 'note' => $T('Attestations fiscales et sociales, assurances, certifications et références détaillées envoyées sur demande.', 'Tax and social security certificates, insurance, certifications and detailed references sent on request.')]),
                B::events($theme, ['icon' => 'clipboard', 'eyebrow' => $T('Pour les sous-traitants', 'For subcontractors'), 'title' => $T('Consultations en cours', 'Open bid requests'), 'bg' => 'light', 'items' => [
                    [$T('Lot menuiseries extérieures, résidence de 80 logements', 'Exterior windows package, 80-unit residence'), $T('Remise des offres : 15 mars', 'Bids due: March 15'), $T('Dossier sur demande', 'Documents on request')],
                    [$T('Lot électricité courants forts et faibles, groupe scolaire', 'Electrical package, school campus'), $T('Remise des offres : 28 mars', 'Bids due: March 28'), $T('Visite obligatoire', 'Site visit required')],
                    [$T('Fourniture d’acier pour béton armé, 1 200 tonnes', 'Rebar supply, 1,300 tons'), $T('Remise des offres : 4 avril', 'Bids due: April 4'), $T('Livraison échelonnée sur 10 mois', 'Phased delivery over 10 months')],
                    [$T('Location de grue à tour avec grutier', 'Tower crane rental with operator'), $T('Remise des offres : 12 avril', 'Bids due: April 12'), $T('Durée : 14 mois', 'Duration: 14 months')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Référencement', 'Registration'), 'title' => $T('Devenir partenaire de nos chantiers', 'Becoming one of our partners'), 'items' => [
                    [$T('Candidature', 'Apply'), $T('Envoyez votre présentation, vos références et vos attestations.', 'Send your company profile, references and certificates.')],
                    [$T('Évaluation', 'Review'), $T('Notre service achats vérifie capacités, assurances et sécurité.', 'Our procurement team checks capacity, insurance and safety record.')],
                    [$T('Référencement', 'Approval'), $T('Vous êtes consulté sur les lots correspondant à votre métier.', 'You are invited to bid on packages matching your trade.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions sur nos consultations', 'Questions about our tenders'), 'buttons' => [[$T('Contacter le service achats', 'Contact procurement'), $contact, 'outline']], 'items' => [
                    [$T('Comment obtenir un dossier de consultation ?', 'How do I get the bid documents?'), $T('Faites la demande par le formulaire de contact en précisant le lot : le dossier vous est envoyé sous 48 heures.', 'Request them through the contact form, stating the package: the documents are sent within 48 hours.')],
                    [$T('Quels documents fournir pour être référencé ?', 'Which documents do I need to register?'), $T('Immatriculation, attestations d’assurance, attestations fiscales et sociales, et trois références récentes.', 'Company registration, insurance certificates, tax and social security certificates, and three recent references.')],
                    [$T('Quels sont vos délais de paiement ?', 'What are your payment terms?'), $T('Situations mensuelles payées à 30 jours, conformément aux contrats de sous-traitance.', 'Monthly progress invoices paid at 30 days, as set out in the subcontract.')],
                ]]),
            ]];

        $pages[] = ['key' => 'careers', 'slug' => $u('recrutement', 'careers'), 'title' => $T('Recrutement', 'Careers'),
            'description' => $T('Compagnons, chefs de chantier, conducteurs de travaux, ingénieurs : rejoignez une entreprise qui forme, fait évoluer et protège ses équipes.', 'Skilled workers, site managers, project managers, engineers: join a company that trains, promotes and protects its people.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Recrutement', 'Careers'), 'title' => $T('Bâtissez votre carrière avec nous', 'Build your career with us'), 'intro' => $T('Plus de 60 recrutements par an, de l’apprenti à l’ingénieur.', 'More than 60 hires a year, from apprentices to engineers.')]),
                B::features($theme, ['eyebrow' => $T('Pourquoi nous rejoindre', 'Why join us'), 'title' => $T('Une entreprise qui fait grandir', 'A company where you grow'), 'items' => [
                    ['graduation-cap', $T('Formation', 'Training'), $T('Centre de formation interne, habilitations et permis d’engins pris en charge.', 'In-house training center, certifications and equipment licenses covered.')],
                    ['trending-up', $T('Évolution', 'Career growth'), $T('La moitié de nos chefs de chantier ont commencé comme compagnons.', 'Half of our site managers started out as skilled workers.')],
                    ['shield-check', $T('Sécurité', 'Safety'), $T('Équipements fournis et culture de prévention partagée.', 'Equipment provided and a shared safety culture.')],
                ]]),
                B::events($theme, ['icon' => 'briefcase', 'eyebrow' => $T('Offres d’emploi', 'Open positions'), 'title' => $T('Nous recrutons', 'We are hiring'), 'bg' => 'light', 'items' => [
                    [$T('Conducteur·rice de travaux bâtiment', 'Building project manager'), $T('CDI, temps plein', 'Full-time, permanent'), $T('[Ville]', '[City]')],
                    [$T('Chef·fe de chantier génie civil', 'Civil works site manager'), $T('CDI, temps plein', 'Full-time, permanent'), $T('Chantiers de la région', 'Regional sites')],
                    [$T('Ingénieur·e structure', 'Structural engineer'), $T('CDI, temps plein', 'Full-time, permanent'), $T('Bureau d’études, [Ville]', 'Design office, [City]')],
                    [$T('Coffreur·se bancheur·se', 'Formwork carpenter'), $T('CDI, temps plein', 'Full-time, permanent'), $T('Chantiers de la région', 'Regional sites')],
                    [$T('Conducteur·rice d’engins', 'Heavy equipment operator'), $T('CDD, 12 mois', 'Fixed-term, 12 months'), $T('Chantiers de la région', 'Regional sites')],
                    [$T('Apprenti·e maçon', 'Mason apprentice'), $T('Apprentissage, 2 ans', 'Apprenticeship, 2 years'), $T('[Ville]', '[City]')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Témoignage', 'Testimonial'),
                    'title' => $T('« Entré comme manœuvre, aujourd’hui chef de chantier »', '“I started as a laborer, now I run sites”'),
                    'html' => '<p>'.e($T('« L’entreprise m’a formé à la lecture de plans, à la sécurité puis à l’encadrement. Douze ans après mon arrivée, je dirige une équipe de vingt-cinq personnes. » — [Prénom Nom], chef de chantier', '“The company trained me to read drawings, then in safety, then in supervision. Twelve years after I joined, I lead a crew of twenty-five.” — [First Last], site manager')).'</p>',
                    'photo' => ['btp-recrutement', 'hard-hat', $T('Jeune compagnon en casque et gilet souriant sur un chantier de construction', 'Young construction worker in a hard hat and vest smiling on a building site')],
                ]),
                B::cta($theme, ['title' => $T('Envie de nous rejoindre ?', 'Want to join us?'), 'text' => $T('Envoyez votre CV pour une offre ou une candidature spontanée : chaque candidature reçoit une réponse.', 'Send your résumé for an opening or as an open application: every applicant gets a reply.'),
                    'buttons' => [[$T('Postuler', 'Apply'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez notre siège ou nos agences : demande de consultation, service achats, recrutement ou information riverains.', 'Contact our head office or branches: bid invitations, procurement, careers or neighbor information.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons de votre opération', 'Let’s talk about your project'), 'intro' => $T('Indiquez l’objet de votre demande : elle est transmise au bon service dans la journée.', 'State the subject of your request: it reaches the right team the same day.')]),
                B::contact($theme, [
                    'title' => $T('Siège social', 'Head office'), 'intro' => $T('Accueil du lundi au vendredi. Pour une urgence sur un chantier, appelez le numéro affiché sur le panneau de chantier.', 'Open Monday to Friday. For an urgent matter on a site, call the number shown on the site board.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 7 h 30 – 17 h 30', 'Monday – Friday, 7:30 am – 5:30 pm')],
                    ],
                    'social' => ['facebook', 'youtube', 'instagram'],
                    'subject' => true,
                    'bg' => 'white',
                ]),
                B::features($theme, ['eyebrow' => $T('Le bon interlocuteur', 'The right contact'), 'title' => $T('À qui s’adresser ?', 'Who to contact?'), 'cols' => 4, 'bg' => 'light', 'items' => [
                    ['building-2', $T('Maîtres d’ouvrage', 'Owners'), $T('Direction commerciale : consultations, études et partenariats.', 'Business development: bids, studies and partnerships.'), $tenders, $T('Appels d’offres', 'Tenders')],
                    ['handshake', $T('Sous-traitants', 'Subcontractors'), $T('Service achats : référencement et dossiers de consultation.', 'Procurement: registration and bid documents.'), $tenders, $T('Consultations', 'Bid requests')],
                    ['user-plus', $T('Candidats', 'Job seekers'), $T('Ressources humaines : offres d’emploi et candidatures.', 'Human resources: job openings and applications.'), $careers, $T('Nos offres', 'Openings')],
                    ['house', $T('Riverains', 'Neighbors'), $T('Information sur un chantier proche de chez vous.', 'Information about a site near your home.')],
                ]]),
                B::pillars($theme, ['title' => $T('Nos agences', 'Our branches'), 'items' => [
                    ['map-pin', $T('Siège', 'Head office'), $T('[Ville] : direction, bureau d’études, services supports.', '[City]: management, design office, support services.')],
                    ['map-pin', $T('Agence Nord', 'North branch'), $T('[Ville] : bâtiment et réhabilitation.', '[City]: building and rehabilitation.')],
                    ['map-pin', $T('Agence Sud', 'South branch'), $T('[Ville] : travaux publics et dépôt matériel.', '[City]: public works and equipment yard.')],
                ]]),
            ]];

        $blog = [
            'base' => $u('references', 'projects'),
            'title' => $T('Journal de chantier', 'Site news'),
            'categories' => ['chantiers' => $T('Chantiers', 'Projects'), 'securite' => $T('Sécurité', 'Safety'), 'entreprise' => $T('Entreprise', 'Company')],
            'posts' => [
                ['key' => 'pont', 'slug' => $u('pont-mise-en-service', 'bridge-opens'), 'category' => 'chantiers', 'tags' => [$T('Génie civil', 'Civil engineering')],
                    'title' => $T('Le nouveau pont routier est ouvert à la circulation', 'The new road bridge is open to traffic'),
                    'excerpt' => $T('Dix-huit mois de travaux, 85 mètres de béton précontraint et une circulation maintenue tout au long du chantier.', 'Eighteen months of work, 280 feet of prestressed concrete and traffic kept moving throughout.'),
                    'photo' => ['btp-blog-pont', 'construction', $T('Pont routier en béton neuf au-dessus d’une rivière au lever du soleil', 'New concrete road bridge over a river at sunrise')],
                    'heading' => $T('Un chantier en trois phases', 'A three-phase project'),
                    'body' => [
                        $T('Le nouvel ouvrage remplace un pont métallique devenu trop étroit pour le trafic actuel.', 'The new structure replaces a steel bridge that had become too narrow for today’s traffic.'),
                        $T('Fondations sur pieux, piles coulées en place, puis tablier construit par encorbellements successifs : chaque phase a été préparée en maquette numérique.', 'Pile foundations, cast-in-place piers, then a deck built by balanced cantilever: every phase was planned in a digital model.'),
                        $T('Merci aux riverains pour leur patience et aux équipes pour un chantier sans accident.', 'Thank you to local residents for their patience and to the crews for an injury-free project.'),
                    ]],
                ['key' => 'securite', 'slug' => $u('journee-securite', 'safety-day'), 'category' => 'securite', 'tags' => [$T('Prévention', 'Prevention')],
                    'title' => $T('Journée sécurité : tous les chantiers à l’arrêt pendant une heure', 'Safety day: every site stops for an hour'),
                    'excerpt' => $T('Ateliers de prévention, démonstrations et échanges avec la direction sur tous nos chantiers.', 'Prevention workshops, demonstrations and discussions with management on all our sites.'),
                    'photo' => ['btp-blog-securite', 'shield-check', $T('Ouvriers du bâtiment en équipements de protection assistant à une démonstration de harnais', 'Construction workers in protective gear watching a safety harness demonstration')],
                    'quote' => $T('« Aucun délai ne justifie de prendre un risque. »', '“No deadline is worth taking a risk.”'),
                    'body' => [
                        $T('Chaque année, l’ensemble de nos équipes s’arrête pour parler prévention : travail en hauteur, levage, circulation des engins.', 'Every year, all our crews stop to talk prevention: working at height, lifting, equipment traffic.'),
                        $T('Les compagnons ont proposé plus de quarante améliorations, dont la moitié sont déjà appliquées.', 'Workers put forward more than forty improvements, half of which are already in place.'),
                        $T('La sécurité est l’affaire de tous, de l’apprenti au président.', 'Safety is everyone’s business, from apprentice to CEO.'),
                    ]],
                ['key' => 'apprentis', 'slug' => $u('promotion-apprentis', 'apprentice-class'), 'category' => 'entreprise', 'tags' => [$T('Formation', 'Training')],
                    'title' => $T('Vingt apprentis rejoignent nos équipes', 'Twenty apprentices join our crews'),
                    'excerpt' => $T('Maçons, coffreurs, conducteurs d’engins : une nouvelle promotion formée sur nos chantiers.', 'Masons, formwork carpenters, equipment operators: a new class trained on our sites.'),
                    'photo' => ['btp-blog-apprentis', 'graduation-cap', $T('Groupe d’apprentis en tenue de chantier écoutant un formateur devant un mur en construction', 'Group of apprentices in work gear listening to an instructor in front of a wall under construction')],
                    'heading' => $T('Un tuteur pour chaque apprenti', 'A mentor for every apprentice'),
                    'body' => [
                        $T('Chaque apprenti est accompagné par un compagnon expérimenté pendant toute sa formation.', 'Every apprentice is paired with an experienced worker for the whole program.'),
                        $T('L’alternance entre centre de formation et chantier permet d’apprendre le geste juste et les règles de sécurité.', 'Alternating between the training center and the site teaches the right technique and safety rules.'),
                        $T('À l’issue de leur parcours, la plupart des apprentis se voient proposer un contrat durable.', 'At the end of the program, most apprentices are offered a permanent position.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Métiers', 'Expertise'), 'url' => $expertise],
                    ['label' => $T('Références', 'Projects'), 'url' => $projects],
                    ['label' => $T('Sécurité et qualité', 'Safety and quality'), 'url' => $quality],
                    ['label' => $T('L’entreprise', 'Company'), 'url' => $about],
                    ['label' => $T('Appels d’offres', 'Tenders'), 'url' => $tenders],
                    ['label' => $T('Recrutement', 'Careers'), 'url' => $careers],
                ],
                'cta_label' => $T('Nous consulter', 'Invite us to bid'),
                'cta_url' => $contact,
                'footer_text' => $T('Entreprise générale de bâtiment, génie civil et travaux publics. Sécurité, qualité et délais tenus, du terrassement à la livraison.', 'General contractor for building, civil engineering and public works. Safety, quality and schedules met, from earthworks to handover.'),
                'footer_links' => [
                    ['label' => $T('Nos métiers', 'Our expertise'), 'url' => $expertise],
                    ['label' => $T('Appels d’offres', 'Tenders'), 'url' => $tenders],
                    ['label' => $T('Recrutement', 'Careers'), 'url' => $careers],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
