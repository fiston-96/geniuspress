<?php

/*
 * Pack « Université » (secteur Écoles et universités) : 8 pages, en français et en anglais.
 * Université ou grande école : formations (licence, master, doctorat), recherche,
 * admissions et calendrier, frais et bourses, international, vie de campus (avec
 * les actualités du blog). Les textes sont des exemples réalistes à personnaliser
 * (l'assistant IA les réécrit à partir de la présentation de l'établissement).
 * Les liens internes sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Écoles et universités', 'en' => 'Schools and universities'],
        'icon' => 'graduation-cap',
        'keywords' => ['université', 'grande école', 'enseignement supérieur', 'licence', 'master', 'doctorat', 'recherche', 'campus', 'étudiants', 'university', 'college', 'higher education', 'bachelor', 'master’s degree', 'phd', 'research', 'campus', 'students'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $programs = $u('/formations', '/programs');
        $research = $u('/recherche', '/research');
        $admissions = $u('/admissions', '/admissions');
        $fees = $u('/frais-et-bourses', '/tuition-and-scholarships');
        $international = '/international';
        $campus = $u('/vie-de-campus', '/campus-life');
        $contact = '/contact';
        $cur = $T('€', '$');

        $levels = [
            ['book-open', $T('Licence', 'Bachelor’s degrees'), $T('Trois ans pour acquérir des bases solides, avec stages et projets dès la deuxième année.', 'Three years to build solid foundations, with internships and projects from the second year.'), $programs, $T('Voir les licences', 'See bachelor’s degrees')],
            ['graduation-cap', $T('Master', 'Master’s degrees'), $T('Deux ans de spécialisation, en lien étroit avec les laboratoires et les entreprises.', 'Two years of specialization, closely linked to research labs and employers.'), $programs, $T('Voir les masters', 'See master’s degrees')],
            ['microscope', $T('Doctorat', 'PhD'), $T('Trois ans de recherche au sein de l’une de nos équipes, encadrés par l’école doctorale.', 'Three years of research in one of our teams, supervised by the doctoral school.'), $research, $T('La recherche', 'Research')],
        ];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Ils racontent leur parcours', 'Their stories'), 'items' => [
            [$T('Les projets avec des entreprises m’ont donné une vraie expérience avant même mon premier stage. J’ai été recrutée à la fin de mon master.', 'Projects with companies gave me real experience even before my first internship. I was hired at the end of my master’s.'), '[Prénom Nom]', $T('Diplômée du master en science des données', 'Data science master’s graduate')],
            [$T('Arrivé de l’étranger, j’ai été accompagné pour le logement, les démarches et la langue. Je me suis senti chez moi dès le premier mois.', 'Coming from abroad, I was helped with housing, paperwork and language. I felt at home within the first month.'), '[Prénom Nom]', $T('Étudiant international en licence', 'International bachelor’s student')],
            [$T('Mon directeur de thèse et l’équipe du laboratoire m’ont poussé à publier et à présenter mes travaux dans des conférences internationales.', 'My thesis supervisor and the lab team encouraged me to publish and present my work at international conferences.'), '[Prénom Nom]', $T('Doctorant en sciences de l’environnement', 'Environmental science PhD candidate')],
        ]];
        $cta = ['title' => $T('Rejoignez-nous à la rentrée', 'Join us next semester'), 'text' => $T('Candidatures ouvertes pour les licences, masters et doctorats. Venez nous rencontrer lors de nos journées portes ouvertes.', 'Applications are open for bachelor’s, master’s and PhD programs. Come and meet us at our open days.'),
            'buttons' => [[$T('Candidater', 'Apply now'), $admissions, 'accent'], [$T('Nous contacter', 'Contact us'), $contact, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Université pluridisciplinaire : licences, masters et doctorats, recherche reconnue, campus vivant et ouverture internationale. Candidatures ouvertes.', 'Multidisciplinary university: bachelor’s, master’s and PhD programs, recognized research, a lively campus and international reach. Applications open.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Université · Enseignement et recherche', 'University · Teaching and research'),
                    'title' => $T('Apprendre, chercher, s’ouvrir au monde', 'Learn, discover, open up to the world'),
                    'text' => $T('12 000 étudiants, 40 formations de la licence au doctorat, des laboratoires reconnus et plus de 120 universités partenaires dans le monde.', '12,000 students, 40 programs from bachelor’s to PhD, recognized research labs and more than 120 partner universities worldwide.'),
                    'buttons' => [[$T('Candidater', 'Apply now'), $admissions], [$T('Nos formations', 'Our programs'), $programs]],
                    'photo' => ['universite-accueil', 'graduation-cap', $T('Étudiants marchant et discutant sur l’esplanade d’un campus universitaire', 'Students walking and talking on a university campus plaza')],
                ]),
                B::stats($theme, ['items' => [
                    [12000, '', $T('étudiants', 'students')],
                    [40, '', $T('formations diplômantes', 'degree programs')],
                    [18, '', $T('laboratoires de recherche', 'research labs')],
                    [91, ' %', $T('d’insertion professionnelle à 18 mois', 'employment rate at 18 months')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Formations', 'Programs'), 'title' => $T('Trois cycles, un même niveau d’exigence', 'Three cycles, one standard of excellence'), 'items' => $levels]),
                B::cards($theme, ['eyebrow' => $T('Facultés et écoles', 'Faculties and schools'), 'title' => $T('Six grands domaines d’études', 'Six major fields of study'), 'more' => $T('Découvrir', 'Discover'), 'bg' => 'light', 'items' => [
                    ['universite-sciences', 'atom', $T('Sciences et ingénierie', 'Science and engineering'), $T('Mathématiques, physique, informatique, génie civil et énergie.', 'Mathematics, physics, computer science, civil engineering and energy.'), $programs],
                    ['universite-sante', 'heart-pulse', $T('Santé et sciences de la vie', 'Health and life sciences'), $T('Biologie, santé publique, nutrition et sciences pharmaceutiques.', 'Biology, public health, nutrition and pharmaceutical sciences.'), $programs],
                    ['universite-economie', 'chart-line', $T('Économie et gestion', 'Economics and management'), $T('Finance, management, marketing, entrepreneuriat et data.', 'Finance, management, marketing, entrepreneurship and data.'), $programs],
                    ['universite-droit', 'scale', $T('Droit et science politique', 'Law and political science'), $T('Droit des affaires, droit international, administration publique.', 'Business law, international law, public administration.'), $programs],
                    ['universite-lettres', 'book-open-text', $T('Lettres, langues et sciences humaines', 'Humanities and languages'), $T('Langues étrangères, histoire, sociologie, communication.', 'Foreign languages, history, sociology, communication.'), $programs],
                    ['universite-environnement', 'sprout', $T('Environnement et agronomie', 'Environment and agriculture'), $T('Climat, gestion de l’eau, agriculture durable et biodiversité.', 'Climate, water management, sustainable farming and biodiversity.'), $programs],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Recherche', 'Research'),
                    'title' => $T('Une recherche qui répond aux défis de notre temps', 'Research that tackles the challenges of our time'),
                    'html' => '<p>'.e($T('Climat, santé, numérique, sociétés : nos 18 laboratoires réunissent 600 chercheurs et doctorants qui travaillent avec des partenaires publics et privés du monde entier.', 'Climate, health, digital technology, societies: our 18 labs bring together 600 researchers and PhD candidates working with public and private partners worldwide.')).'</p>',
                    'bullets' => [$T('Plateformes scientifiques partagées', 'Shared scientific facilities'), $T('Contrats de recherche avec l’industrie', 'Industry research contracts'), $T('Science ouverte et publications en libre accès', 'Open science and open-access publications')],
                    'buttons' => [[$T('Découvrir la recherche', 'Explore our research'), $research, 'outline']],
                    'photo' => ['universite-laboratoire', 'microscope', $T('Chercheuse en blouse observant un échantillon au microscope dans un laboratoire', 'Researcher in a lab coat examining a sample under a microscope')],
                    'reverse' => true,
                ]),
                B::testimonials($theme, $testimonials),
                B::posts($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('La vie de l’université', 'University life'), 'source' => 'latest', 'count' => 3, 'more' => $T('Lire la suite', 'Read more')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'programs', 'slug' => $u('formations', 'programs'), 'title' => $T('Formations', 'Programs'),
            'description' => $T('Licences, masters, doctorats, formation continue et diplômes en alternance : découvrez nos programmes par domaine et par niveau.', 'Bachelor’s, master’s and PhD programs, continuing education and work-study degrees: explore our programs by field and level.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Formations', 'Programs'), 'title' => $T('Un parcours pour chaque projet', 'A path for every ambition'), 'intro' => $T('De la licence au doctorat, en formation initiale, en alternance ou en reprise d’études.', 'From bachelor’s to PhD, as a full-time student, in work-study or returning to education.')]),
                B::features($theme, ['cols' => 3, 'items' => array_merge($levels, [
                    ['briefcase', $T('Alternance', 'Work-study'), $T('Licences professionnelles et masters en alternance avec nos entreprises partenaires.', 'Professional bachelor’s and master’s degrees combined with work at partner companies.')],
                    ['users', $T('Formation continue', 'Continuing education'), $T('Diplômes et certificats pour les professionnels, en cours du soir ou à distance.', 'Degrees and certificates for professionals, in evening classes or online.')],
                    ['laptop', $T('Enseignement à distance', 'Online learning'), $T('Certains diplômes sont accessibles entièrement en ligne, avec un tutorat personnalisé.', 'Some degrees are available fully online, with personal tutoring.')],
                ])]),
                M::table($theme, ['eyebrow' => $T('Catalogue', 'Catalog'), 'title' => $T('Nos formations par domaine', 'Our programs by field'), 'bg' => 'light', 'rows' => [
                    [$T('Domaine', 'Field'), $T('Licence', 'Bachelor’s'), $T('Master', 'Master’s'), $T('Langue', 'Language')],
                    [$T('Sciences et ingénierie', 'Science and engineering'), $T('Informatique, mathématiques, génie civil', 'Computer science, mathematics, civil engineering'), $T('Science des données, énergies renouvelables', 'Data science, renewable energy'), $T('Français, anglais', 'French, English')],
                    [$T('Santé et sciences de la vie', 'Health and life sciences'), $T('Biologie, sciences de la santé', 'Biology, health sciences'), $T('Santé publique, biotechnologies', 'Public health, biotechnology'), $T('Français', 'French')],
                    [$T('Économie et gestion', 'Economics and management'), $T('Économie, gestion', 'Economics, management'), $T('Finance, marketing, entrepreneuriat', 'Finance, marketing, entrepreneurship'), $T('Français, anglais', 'French, English')],
                    [$T('Droit et science politique', 'Law and political science'), $T('Droit, science politique', 'Law, political science'), $T('Droit des affaires, droit international', 'Business law, international law'), $T('Français', 'French')],
                    [$T('Lettres et sciences humaines', 'Humanities'), $T('Langues, histoire, sociologie', 'Languages, history, sociology'), $T('Communication, traduction', 'Communication, translation'), $T('Français, anglais', 'French, English')],
                ], 'note' => $T('Liste indicative : le détail des cours, des crédits et des débouchés figure dans la fiche de chaque formation.', 'Indicative list: course details, credits and career outcomes are in each program’s description.')]),
                B::split($theme, [
                    'eyebrow' => $T('Pédagogie', 'Teaching'),
                    'title' => $T('Apprendre par la pratique', 'Learning by doing'),
                    'html' => '<p>'.e($T('Petits groupes de travaux dirigés, projets réels proposés par des entreprises et des associations, stages obligatoires et semestre à l’étranger possible : nos diplômés sont prêts pour le monde professionnel ou la recherche.', 'Small tutorial groups, real projects from companies and associations, compulsory internships and an optional semester abroad: our graduates are ready for the workplace or for research.')).'</p>',
                    'bullets' => [$T('Système de crédits européens (ECTS)', 'European credit system (ECTS)'), $T('Tutorat en première année', 'First-year tutoring'), $T('Service d’orientation et d’insertion', 'Careers and guidance service')],
                    'photo' => ['universite-cours', 'presentation', $T('Petit groupe d’étudiants travaillant sur un projet avec une enseignante', 'Small group of students working on a project with a lecturer')],
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions sur les formations', 'Questions about programs'), 'bg' => 'light', 'intro' => $T('Notre service d’orientation vous aide à choisir votre parcours.', 'Our guidance service helps you choose your path.'), 'buttons' => [[$T('Contacter l’orientation', 'Contact guidance'), $contact, 'outline']], 'items' => [
                    [$T('Puis-je changer de formation en cours de route ?', 'Can I switch programs along the way?'), $T('Oui, des passerelles existent en fin de semestre ou d’année, après avis de l’équipe pédagogique.', 'Yes, bridges exist at the end of a semester or year, subject to the teaching team’s approval.')],
                    [$T('Mes diplômes sont-ils reconnus à l’étranger ?', 'Are your degrees recognized abroad?'), $T('Nos diplômes sont délivrés selon le système licence-master-doctorat et les crédits ECTS, ce qui facilite leur reconnaissance internationale.', 'Our degrees follow the bachelor’s–master’s–PhD system and ECTS credits, which makes international recognition easier.')],
                    [$T('Peut-on valider son expérience professionnelle ?', 'Can work experience count toward a degree?'), $T('Oui, une procédure de validation des acquis permet d’obtenir tout ou partie d’un diplôme grâce à votre expérience.', 'Yes, a prior learning assessment can grant all or part of a degree based on your experience.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'research', 'slug' => $u('recherche', 'research'), 'title' => $T('Recherche', 'Research'),
            'description' => $T('Laboratoires, école doctorale, plateformes scientifiques et partenariats : une recherche pluridisciplinaire au service de la société.', 'Labs, doctoral school, scientific facilities and partnerships: multidisciplinary research that serves society.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Recherche', 'Research'), 'title' => $T('Produire et partager le savoir', 'Creating and sharing knowledge'), 'intro' => $T('Nos équipes publient, innovent et forment les chercheurs de demain.', 'Our teams publish, innovate and train tomorrow’s researchers.')]),
                B::cards($theme, ['eyebrow' => $T('Laboratoires', 'Labs'), 'title' => $T('Nos axes de recherche', 'Our research areas'), 'items' => [
                    ['universite-labo-climat', 'cloud-rain', $T('Climat et ressources en eau', 'Climate and water resources'), $T('Modélisation du climat, gestion des bassins versants et adaptation des territoires.', 'Climate modeling, watershed management and regional adaptation.')],
                    ['universite-labo-sante', 'dna', $T('Santé et biotechnologies', 'Health and biotechnology'), $T('Maladies infectieuses, génétique et nouvelles méthodes de diagnostic.', 'Infectious diseases, genetics and new diagnostic methods.')],
                    ['universite-labo-numerique', 'brain-circuit', $T('Intelligence artificielle et données', 'Artificial intelligence and data'), $T('Apprentissage automatique, traitement du langage et éthique des algorithmes.', 'Machine learning, natural language processing and algorithmic ethics.')],
                    ['universite-labo-energie', 'zap', $T('Énergies renouvelables', 'Renewable energy'), $T('Solaire, stockage de l’énergie et réseaux électriques intelligents.', 'Solar power, energy storage and smart grids.')],
                    ['universite-labo-societes', 'users-round', $T('Sociétés et territoires', 'Societies and regions'), $T('Villes, migrations, politiques publiques et économie du développement.', 'Cities, migration, public policy and development economics.')],
                    ['universite-labo-agronomie', 'wheat', $T('Agriculture durable', 'Sustainable agriculture'), $T('Sols, semences, sécurité alimentaire et agroécologie.', 'Soils, seeds, food security and agroecology.')],
                ]]),
                M::chart($theme, [
                    'eyebrow' => $T('En chiffres', 'In figures'),
                    'title' => $T('Une production scientifique en croissance', 'Growing scientific output'),
                    'html' => '<p>'.e($T('Le nombre de publications dans des revues à comité de lecture a presque doublé en cinq ans, porté par nos doctorants et nos collaborations internationales.', 'The number of peer-reviewed publications has almost doubled in five years, driven by our PhD candidates and international collaborations.')).'</p>',
                    'bullets' => [$T('600 chercheurs et doctorants', '600 researchers and PhD candidates'), $T('45 contrats de recherche en cours', '45 ongoing research contracts'), $T('12 brevets déposés', '12 patents filed')],
                    'bg' => 'light',
                    'chart' => ['kind' => 'bar', 'title' => $T('Publications par an', 'Publications per year'), 'series' => [$T('Publications', 'Publications')], 'rows' => [
                        ['2020', '240'], ['2021', '285'], ['2022', '330'], ['2023', '390'], ['2024', '450'],
                    ]],
                ]),
                B::split($theme, [
                    'eyebrow' => $T('École doctorale', 'Doctoral school'),
                    'title' => $T('Préparer un doctorat chez nous', 'Doing a PhD with us'),
                    'html' => '<p>'.e($T('Notre école doctorale accueille 250 doctorants. Chacun est encadré par un directeur de thèse, suit des formations transversales (écriture scientifique, gestion de projet, enseignement) et peut bénéficier d’un contrat doctoral ou d’une bourse.', 'Our doctoral school hosts 250 PhD candidates. Each is supervised by a thesis director, takes transferable-skills training (scientific writing, project management, teaching) and may receive a doctoral contract or scholarship.')).'</p>',
                    'bullets' => [$T('Contrats doctoraux et cotutelles internationales', 'Doctoral contracts and joint international PhDs'), $T('Comité de suivi chaque année', 'Annual progress committee'), $T('Aide à la mobilité pour les conférences', 'Travel grants for conferences')],
                    'buttons' => [[$T('Candidater en doctorat', 'Apply for a PhD'), $admissions]],
                    'photo' => ['universite-doctorante', 'graduation-cap', $T('Doctorante présentant ses travaux devant un poster lors d’un congrès scientifique', 'PhD candidate presenting her work in front of a poster at a scientific conference')],
                    'reverse' => true,
                ]),
                B::cta($theme, ['tone' => 'dark', 'title' => $T('Entreprises et institutions : travaillons ensemble', 'Companies and institutions: let’s work together'), 'text' => $T('Contrats de recherche, thèses en entreprise, chaires et accès à nos plateformes : notre service des partenariats vous accompagne.', 'Research contracts, industry PhDs, chairs and access to our facilities: our partnerships office will guide you.'), 'buttons' => [[$T('Contacter les partenariats', 'Contact partnerships'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'admissions', 'slug' => $u('admissions', 'admissions'), 'title' => $T('Admissions', 'Admissions'),
            'description' => $T('Candidater en licence, master ou doctorat : étapes, calendrier des admissions, pièces du dossier et réponses aux questions fréquentes.', 'Applying for a bachelor’s, master’s or PhD: steps, admissions calendar, required documents and answers to frequent questions.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Admissions', 'Admissions'), 'title' => $T('Candidater, étape par étape', 'Applying, step by step'), 'intro' => $T('Une candidature en ligne, un dossier complet et une réponse dans les délais annoncés.', 'An online application, a complete file and an answer within the stated deadlines.')]),
                B::steps($theme, ['items' => [
                    [$T('Choisir', 'Choose'), $T('Consultez les fiches des formations et les prérequis.', 'Read the program descriptions and entry requirements.')],
                    [$T('Candidater', 'Apply'), $T('Déposez votre dossier sur la plateforme en ligne.', 'Submit your file on the online platform.')],
                    [$T('Entretien', 'Interview'), $T('Selon la formation, un entretien ou une épreuve écrite.', 'Depending on the program, an interview or written test.')],
                    [$T('S’inscrire', 'Enroll'), $T('Après admission, confirmez votre inscription et réglez les frais.', 'Once admitted, confirm your enrollment and pay the fees.')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Calendrier', 'Calendar'), 'title' => $T('Les dates clés de l’année', 'Key dates of the year'), 'bg' => 'light', 'rows' => [
                    [$T('Étape', 'Step'), $T('Licence', 'Bachelor’s'), $T('Master', 'Master’s'), $T('Doctorat', 'PhD')],
                    [$T('Ouverture des candidatures', 'Applications open'), $T('Janvier', 'January'), $T('Février', 'February'), $T('Toute l’année', 'All year')],
                    [$T('Date limite de dépôt', 'Application deadline'), $T('Fin mars', 'End of March'), $T('Fin avril', 'End of April'), $T('Selon les offres de thèse', 'Depends on PhD offers')],
                    [$T('Entretiens', 'Interviews'), $T('Avril', 'April'), $T('Mai', 'May'), $T('Sur convocation', 'By invitation')],
                    [$T('Résultats', 'Results'), $T('Mi-mai', 'Mid-May'), $T('Mi-juin', 'Mid-June'), $T('Sous un mois', 'Within a month')],
                    [$T('Rentrée', 'Start of classes'), $T('Septembre', 'September'), $T('Septembre', 'September'), $T('Octobre ou janvier', 'October or January')],
                ], 'note' => $T('Dates indicatives, à confirmer chaque année sur la plateforme de candidature.', 'Indicative dates, to be confirmed each year on the application platform.')]),
                B::schedule($theme, ['eyebrow' => $T('Votre dossier', 'Your file'), 'title' => $T('Pièces à fournir', 'Required documents'), 'bg' => 'white', 'groups' => [
                    [$T('Pour tous', 'For everyone'), [
                        ['id-card', $T('Pièce d’identité ou passeport', 'ID card or passport')],
                        ['file-text', $T('Relevés de notes et diplômes obtenus', 'Transcripts and degrees obtained')],
                        ['pencil', $T('Lettre de motivation', 'Personal statement')],
                    ]],
                    [$T('Selon le niveau', 'Depending on level'), [
                        ['user-round', $T('Curriculum vitae (master et doctorat)', 'CV (master’s and PhD)')],
                        ['notebook-pen', $T('Projet de recherche (doctorat)', 'Research proposal (PhD)')],
                        ['languages', $T('Certificat de langue si la formation l’exige', 'Language certificate if required')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'bg' => 'light', 'items' => [
                    [$T('Combien de formations puis-je demander ?', 'How many programs can I apply to?'), $T('Jusqu’à cinq vœux par année, classés par ordre de préférence.', 'Up to five choices per year, ranked by preference.')],
                    [$T('Les frais de candidature sont-ils remboursés ?', 'Are application fees refunded?'), $T('Les frais de dossier de 40 € couvrent l’étude de votre candidature et ne sont pas remboursés. Les candidats boursiers en sont exemptés.', 'The $45 application fee covers the review of your application and is non-refundable. Scholarship applicants are exempt.')],
                    [$T('Peut-on visiter le campus avant de candidater ?', 'Can I visit the campus before applying?'), $T('Oui, lors des journées portes ouvertes ou sur rendez-vous avec le service des admissions.', 'Yes, during open days or by appointment with the admissions office.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'fees', 'slug' => $u('frais-et-bourses', 'tuition-and-scholarships'), 'title' => $T('Frais et bourses', 'Tuition and scholarships'),
            'description' => $T('Frais de scolarité par niveau, bourses au mérite et sur critères sociaux, paiement échelonné et aides au logement : financer vos études.', 'Tuition by level, merit-based and need-based scholarships, installment plans and housing support: funding your studies.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Frais et bourses', 'Tuition and scholarships'), 'title' => $T('Financer vos études', 'Funding your studies'), 'intro' => $T('Des frais annoncés clairement et des aides pour qu’aucun étudiant motivé ne renonce pour des raisons financières.', 'Clearly stated fees and financial aid so that no motivated student has to give up for financial reasons.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Frais de scolarité', 'Tuition'), 'title' => $T('Frais annuels par niveau', 'Annual fees by level'), 'intro' => $T('Montants indicatifs par année, hors frais de dossier et assurance.', 'Indicative amounts per year, excluding application fees and insurance.'), 'items' => [
                    [$T('Licence', 'Bachelor’s'), $T('3 900', '4,500'), $cur, $T('/ an', '/ year'), $T('Trois années d’études.', 'Three years of study.'), [$T('Tutorat en première année', 'First-year tutoring'), $T('Accès à la bibliothèque et aux salles informatiques', 'Library and computer lab access'), $T('Accompagnement aux stages', 'Internship support'), '-'.$T('Logement', 'Housing')], $T('Demander des informations', 'Request information')],
                    [$T('Master', 'Master’s'), $T('5 600', '6,400'), $cur, $T('/ an', '/ year'), $T('Deux années de spécialisation.', 'Two years of specialization.'), [$T('Projets avec des entreprises', 'Company projects'), $T('Semestre à l’étranger possible', 'Optional semester abroad'), $T('Service carrières', 'Careers service'), $T('Ateliers de préparation à l’emploi', 'Job-readiness workshops')], $T('Demander des informations', 'Request information'), true, $T('Le plus demandé', 'Most popular')],
                    [$T('Doctorat', 'PhD'), $T('650', '750'), $cur, $T('/ an', '/ year'), $T('Droits d’inscription en école doctorale.', 'Doctoral school enrollment fees.'), [$T('Encadrement par un directeur de thèse', 'Supervision by a thesis director'), $T('Formations transversales', 'Transferable-skills training'), $T('Aide à la mobilité', 'Travel grants'), $T('Contrat doctoral possible', 'Doctoral contract available')], $T('Demander des informations', 'Request information')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Bourses et aides', 'Scholarships and aid'), 'title' => $T('Des aides pour chaque situation', 'Support for every situation'), 'bg' => 'white', 'cols' => 4, 'items' => [
                    ['award', $T('Bourses au mérite', 'Merit scholarships'), $T('Réduction de 25 à 100 % des frais pour les meilleurs dossiers.', '25% to 100% off tuition for top applicants.')],
                    ['heart-handshake', $T('Bourses sociales', 'Need-based grants'), $T('Attribuées selon les ressources de la famille.', 'Awarded according to family income.')],
                    ['trophy', $T('Sportifs et artistes', 'Athletes and artists'), $T('Aménagements et aides pour les talents de haut niveau.', 'Flexible schedules and aid for high-level talent.')],
                    ['globe', $T('Bourses internationales', 'International scholarships'), $T('Pour les étudiants venus de nos universités partenaires.', 'For students from our partner universities.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Vie étudiante', 'Student life'),
                    'title' => $T('Travailler et se loger pendant ses études', 'Working and housing during your studies'),
                    'html' => '<p>'.e($T('Notre service de la vie étudiante propose des emplois sur le campus compatibles avec l’emploi du temps, une bourse aux logements et un fonds d’urgence pour les étudiants en difficulté passagère.', 'Our student life office offers on-campus jobs that fit your timetable, a housing board and an emergency fund for students facing temporary hardship.')).'</p>',
                    'bullets' => [$T('Emplois étudiants sur le campus', 'On-campus student jobs'), $T('Résidences universitaires à tarif modéré', 'Affordable university residences'), $T('Fonds d’aide d’urgence', 'Emergency aid fund')],
                    'photo' => ['universite-residence', 'house', $T('Chambre lumineuse d’une résidence étudiante avec bureau et étagères', 'Bright student residence room with a desk and shelves')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Paiement et aides', 'Payment and aid'), 'items' => [
                    [$T('Peut-on payer en plusieurs fois ?', 'Can I pay in installments?'), $T('Oui, en trois ou dix mensualités sans frais. Paiement par carte bancaire, virement ou paiement mobile.', 'Yes, in three or ten interest-free installments. Payment by card, bank transfer or mobile payment.')],
                    [$T('Quand demander une bourse ?', 'When should I apply for a scholarship?'), $T('En même temps que votre candidature : la décision vous est communiquée avec le résultat d’admission.', 'At the same time as your application: the decision is sent with your admission result.')],
                    [$T('Les frais sont-ils remboursés en cas d’abandon ?', 'Are fees refunded if I withdraw?'), $T('Un remboursement partiel est possible selon la date de votre demande, comme précisé dans le règlement financier.', 'A partial refund is possible depending on the date of your request, as set out in the financial regulations.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'international', 'slug' => 'international', 'title' => 'International',
            'description' => $T('Étudier chez nous depuis l’étranger, partir en échange, doubles diplômes et école d’été : une université ouverte sur le monde.', 'Studying with us from abroad, going on exchange, dual degrees and summer school: a university open to the world.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'International', 'title' => $T('Une université ouverte sur le monde', 'A university open to the world'), 'intro' => $T('Des étudiants de 60 nationalités et plus de 120 universités partenaires sur cinq continents.', 'Students from 60 countries and more than 120 partner universities on five continents.')]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [120, '+', $T('universités partenaires', 'partner universities')],
                    [60, '', $T('nationalités sur le campus', 'nationalities on campus')],
                    [22, ' %', $T('d’étudiants internationaux', 'international students')],
                    [15, '', $T('doubles diplômes', 'dual degrees')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Partir', 'Going abroad'), 'title' => $T('Trois façons de vivre l’international', 'Three ways to go international'), 'items' => [
                    ['plane', $T('Semestre d’échange', 'Exchange semester'), $T('Un ou deux semestres dans une université partenaire, crédits reconnus.', 'One or two semesters at a partner university, with credits recognized.')],
                    ['award', $T('Doubles diplômes', 'Dual degrees'), $T('Deux diplômes en un parcours, avec une année dans chaque établissement.', 'Two degrees in one program, with a year at each institution.')],
                    ['sun', $T('École d’été', 'Summer school'), $T('Trois semaines de cours intensifs et de découverte culturelle.', 'Three weeks of intensive classes and cultural discovery.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Venir étudier chez nous', 'Studying with us'),
                    'title' => $T('Un accueil pensé pour les étudiants internationaux', 'A welcome designed for international students'),
                    'html' => '<p>'.e($T('Notre bureau international vous accompagne avant et après votre arrivée : admission, logement, assurance, ouverture d’un compte bancaire et cours de langue. Des étudiants parrains vous aident à prendre vos repères.', 'Our international office supports you before and after you arrive: admission, housing, insurance, opening a bank account and language classes. Student buddies help you settle in.')).'</p>',
                    'bullets' => [$T('Semaine d’accueil à la rentrée', 'Welcome week at the start of the year'), $T('Cours de langue gratuits', 'Free language classes'), $T('Programme de parrainage étudiant', 'Student buddy program')],
                    'buttons' => [[$T('Contacter le bureau international', 'Contact the international office'), $contact]],
                    'photo' => ['universite-international', 'globe', $T('Étudiants de différentes origines échangeant autour d’une table à la cafétéria du campus', 'Students from different backgrounds chatting around a table in the campus cafeteria')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('Votre arrivée', 'Your arrival'), 'title' => $T('Préparer votre venue', 'Preparing your arrival'), 'items' => [
                    [$T('Admission', 'Admission'), $T('Candidature en ligne et lettre d’admission.', 'Online application and admission letter.')],
                    [$T('Visa', 'Visa'), $T('Renseignez-vous auprès de l’ambassade ou du consulat de votre pays de résidence.', 'Check with the embassy or consulate in your country of residence.')],
                    [$T('Logement', 'Housing'), $T('Réservez une chambre en résidence ou consultez notre bourse aux logements.', 'Book a residence room or browse our housing board.')],
                    [$T('Accueil', 'Welcome'), $T('Semaine d’intégration, démarches et rencontre avec votre parrain.', 'Orientation week, paperwork and meeting your buddy.')],
                ]]),
                B::cta($theme, ['tone' => 'dark', 'title' => $T('Votre projet international commence ici', 'Your international journey starts here'), 'text' => $T('Le bureau international répond à vos questions en français et en anglais.', 'The international office answers your questions in English and French.'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'accent'], [$T('Frais et bourses', 'Tuition and scholarships'), $fees, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'campus', 'slug' => $u('vie-de-campus', 'campus-life'), 'title' => $T('Vie de campus', 'Campus life'),
            'description' => $T('Bibliothèque, logement, restauration, sport, santé et associations étudiantes : la vie sur le campus, ses actualités et son agenda.', 'Library, housing, dining, sports, health and student clubs: life on campus, its news and its calendar.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Vie de campus', 'Campus life'), 'title' => $T('Bien plus qu’un lieu d’études', 'Much more than a place to study'), 'intro' => $T('Un campus vert et vivant, où l’on apprend, se rencontre et s’engage.', 'A green, lively campus where people learn, meet and get involved.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['library', $T('Bibliothèque', 'Library'), $T('Ouverte jusqu’à 22 h, 1 200 places et des ressources numériques accessibles à distance.', 'Open until 10 pm, 1,200 seats and digital resources available remotely.')],
                    ['house', $T('Logement', 'Housing'), $T('Deux résidences sur le campus et une bourse aux logements en ville.', 'Two residences on campus and a housing board for the city.')],
                    ['utensils', $T('Restauration', 'Dining'), $T('Restaurant universitaire et cafétérias à prix étudiant.', 'University restaurant and cafeterias at student prices.')],
                    ['dumbbell', $T('Sport', 'Sports'), $T('Plus de 30 disciplines, un gymnase, une piscine et des terrains extérieurs.', 'More than 30 sports, a gym, a pool and outdoor fields.')],
                    ['heart-pulse', $T('Santé et bien-être', 'Health and well-being'), $T('Centre de santé, écoute psychologique et accompagnement du handicap.', 'Health center, counseling and disability support.')],
                    ['party-popper', $T('Associations', 'Student clubs'), $T('80 associations : culture, solidarité, entrepreneuriat, sport.', '80 clubs: culture, volunteering, entrepreneurship, sports.')],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('Le campus au quotidien', 'Campus, day to day'), 'bg' => 'light', 'photos' => [
                    ['universite-bibliotheque', 'library', $T('Étudiants travaillant dans une grande bibliothèque universitaire lumineuse', 'Students working in a large, bright university library')],
                    ['universite-amphitheatre', 'presentation', $T('Amphithéâtre rempli d’étudiants pendant un cours magistral', 'Lecture hall full of students during a lecture')],
                    ['universite-pelouse', 'tree-pine', $T('Étudiants assis sur la pelouse du campus entre deux cours', 'Students sitting on the campus lawn between classes')],
                    ['universite-sport', 'dumbbell', $T('Match de basket-ball entre équipes étudiantes dans un gymnase', 'Basketball game between student teams in a gym')],
                    ['universite-association', 'party-popper', $T('Forum des associations étudiantes avec stands et affiches', 'Student club fair with stands and posters')],
                    ['universite-remise-diplomes', 'graduation-cap', $T('Diplômés en toge lançant leur toque lors de la cérémonie de remise des diplômes', 'Graduates in gowns throwing their caps at the graduation ceremony')],
                ]]),
                B::posts($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Les dernières nouvelles du campus', 'Latest campus news'), 'source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['eyebrow' => $T('Agenda', 'Calendar'), 'title' => $T('À ne pas manquer', 'Not to be missed'), 'bg' => 'light', 'items' => [
                    [$T('Journées portes ouvertes', 'Open days'), $T('Premier samedi de février, 9 h – 17 h', 'First Saturday in February, 9 am – 5 pm'), $T('Campus principal', 'Main campus')],
                    [$T('Forum entreprises et stages', 'Careers and internship fair'), $T('Mi-octobre', 'Mid-October'), $T('Hall des sports', 'Sports hall')],
                    [$T('Fête de la science', 'Science festival'), $T('Novembre', 'November'), $T('Laboratoires et amphithéâtres', 'Labs and lecture halls')],
                    [$T('Cérémonie de remise des diplômes', 'Graduation ceremony'), $T('Fin juin', 'Late June'), $T('Grand amphithéâtre', 'Main auditorium')],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez l’université : admissions, scolarité, bureau international, recherche et partenariats. Accueil du lundi au vendredi.', 'Contact the university: admissions, registrar, international office, research and partnerships. Open Monday to Friday.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Nous sommes à votre écoute', 'We are here to help'), 'intro' => $T('Précisez l’objet de votre demande : elle sera transmise au service concerné.', 'State the subject of your request: it will be passed to the right office.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Accueil général du campus du lundi au vendredi.', 'Main campus reception, Monday to Friday.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h 30 – 17 h 30', 'Monday – Friday, 8:30 am – 5:30 pm')],
                    ],
                    'social' => ['facebook', 'instagram', 'youtube'],
                    'subject' => true,
                    'bg' => 'white',
                ]),
                B::schedule($theme, ['eyebrow' => $T('Services', 'Offices'), 'title' => $T('Le bon interlocuteur', 'The right contact'), 'groups' => [
                    [$T('Études', 'Studies'), [
                        ['clipboard-check', $T('Admissions : candidatures et résultats', 'Admissions: applications and results')],
                        ['file-text', $T('Scolarité : inscriptions, attestations, diplômes', 'Registrar: enrollment, certificates, diplomas')],
                        ['compass', $T('Orientation et insertion professionnelle', 'Guidance and careers')],
                    ]],
                    [$T('Campus et partenaires', 'Campus and partners'), [
                        ['globe', $T('Bureau international : échanges et accueil', 'International office: exchanges and welcome')],
                        ['microscope', $T('Recherche et partenariats', 'Research and partnerships')],
                        ['house', $T('Vie étudiante et logement', 'Student life and housing')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Avant de nous écrire', 'Before you write'), 'bg' => 'white', 'items' => [
                    [$T('Sous quel délai recevrai-je une réponse ?', 'How soon will I get a reply?'), $T('En général sous trois jours ouvrés ; un peu plus pendant la période des admissions.', 'Usually within three business days; a little longer during the admissions period.')],
                    [$T('Puis-je obtenir un rendez-vous avec un conseiller d’orientation ?', 'Can I meet a guidance counselor?'), $T('Oui, sur place ou en visio, en le demandant avec le formulaire.', 'Yes, on campus or by video, by asking through the form.')],
                    [$T('Comment venir au campus ?', 'How do I get to the campus?'), $T('Le campus est desservi par les transports en commun ; un parking est réservé aux visiteurs à l’entrée principale.', 'The campus is served by public transport; visitor parking is available at the main entrance.')],
                ]]),
            ]];

        $blog = [
            'base' => $u('vie-de-campus', 'campus-life'),
            'title' => $T('Vie de campus', 'Campus life'),
            'categories' => ['campus' => $T('Campus', 'Campus'), 'recherche' => $T('Recherche', 'Research'), 'international' => 'International'],
            'posts' => [
                ['key' => 'learning-center', 'slug' => $u('nouveau-learning-center', 'new-learning-center'), 'category' => 'campus', 'tags' => [$T('Bibliothèque', 'Library')],
                    'title' => $T('Le nouveau centre d’apprentissage ouvre ses portes', 'The new learning center opens its doors'),
                    'excerpt' => $T('Salles de travail en groupe, studio vidéo et horaires élargis : un lieu pensé avec les étudiants.', 'Group study rooms, a video studio and longer hours: a space designed with students.'),
                    'photo' => ['universite-blog-learning-center', 'library', $T('Espace de travail moderne d’une bibliothèque universitaire avec étudiants en groupe', 'Modern university library study space with students working in groups')],
                    'heading' => $T('Ce qui change', 'What’s new'),
                    'body' => [
                        $T('Après deux ans de travaux, l’ancienne bibliothèque devient un centre d’apprentissage ouvert de 8 h à 22 h, six jours sur sept.', 'After two years of work, the old library becomes a learning center open from 8 am to 10 pm, six days a week.'),
                        $T('Les étudiants y trouvent 40 salles de travail en groupe réservables en ligne, un studio d’enregistrement vidéo, des postes informatiques et des espaces de silence.', 'Students will find 40 group study rooms bookable online, a video recording studio, computer stations and quiet areas.'),
                        $T('Le projet a été conçu avec les représentants étudiants, qui ont participé au choix du mobilier et des services.', 'The project was designed with student representatives, who helped choose the furniture and services.'),
                    ]],
                ['key' => 'prix-recherche', 'slug' => $u('prix-recherche-climat', 'climate-research-award'), 'category' => 'recherche', 'tags' => [$T('Climat', 'Climate')],
                    'title' => $T('Une équipe de recherche distinguée pour ses travaux sur l’eau', 'Research team honored for its work on water'),
                    'excerpt' => $T('Le laboratoire Climat et ressources en eau reçoit un prix international pour un outil de prévision des sécheresses.', 'The Climate and Water Resources lab receives an international award for a drought forecasting tool.'),
                    'photo' => ['universite-blog-recherche', 'cloud-rain', $T('Chercheurs prélevant des échantillons d’eau au bord d’une rivière', 'Researchers collecting water samples on a riverbank')],
                    'quote' => $T('« Notre objectif est que cet outil serve directement aux agriculteurs et aux collectivités. »', '“Our goal is for this tool to be used directly by farmers and local authorities.”'),
                    'body' => [
                        $T('Pendant cinq ans, l’équipe a combiné données satellitaires, mesures de terrain et intelligence artificielle pour anticiper les épisodes de sécheresse plusieurs semaines à l’avance.', 'For five years, the team combined satellite data, field measurements and artificial intelligence to predict droughts several weeks ahead.'),
                        $T('L’outil est désormais testé avec des coopératives agricoles et des services de gestion de l’eau dans trois régions.', 'The tool is now being tested with farming cooperatives and water management agencies in three regions.'),
                        $T('Deux doctorants de l’équipe présenteront ces travaux lors de la prochaine conférence internationale sur le climat.', 'Two of the team’s PhD candidates will present the work at the next international climate conference.'),
                    ]],
                ['key' => 'echange', 'slug' => $u('semestre-a-l-etranger', 'semester-abroad'), 'category' => 'international', 'tags' => [$T('Échanges', 'Exchanges')],
                    'title' => $T('Un semestre à l’étranger : retours d’étudiants', 'A semester abroad: students share their experience'),
                    'excerpt' => $T('Ils sont partis étudier dans une université partenaire et racontent ce que l’expérience leur a apporté.', 'They studied at a partner university and describe what the experience gave them.'),
                    'photo' => ['universite-blog-echange', 'plane', $T('Étudiante avec valise et sac à dos devant le bâtiment d’une université étrangère', 'Student with a suitcase and backpack in front of a university building abroad')],
                    'heading' => $T('Leurs conseils', 'Their advice'),
                    'body' => [
                        $T('Chaque année, plus de 400 étudiants partent un ou deux semestres dans l’une de nos universités partenaires.', 'Every year, more than 400 students spend one or two semesters at one of our partner universities.'),
                        $T('Tous parlent d’autonomie, de langues apprises sur le terrain et d’amitiés durables. Leur conseil : préparer son dossier tôt, rencontrer d’anciens participants et choisir ses cours avec son responsable pédagogique.', 'They all mention independence, languages learned on the spot and lasting friendships. Their advice: prepare your file early, talk to former participants and choose courses with your academic adviser.'),
                        $T('Les candidatures pour l’année prochaine ouvrent en novembre auprès du bureau international.', 'Applications for next year open in November with the international office.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Formations', 'Programs'), 'url' => $programs],
                    ['label' => $T('Recherche', 'Research'), 'url' => $research],
                    ['label' => 'Admissions', 'url' => $admissions],
                    ['label' => $T('Frais et bourses', 'Tuition'), 'url' => $fees],
                    ['label' => 'International', 'url' => $international],
                    ['label' => $T('Vie de campus', 'Campus life'), 'url' => $campus],
                ],
                'cta_label' => $T('Candidater', 'Apply now'),
                'cta_url' => $admissions,
                'footer_text' => $T('Université pluridisciplinaire : formations de la licence au doctorat, recherche reconnue et ouverture internationale.', 'Multidisciplinary university: programs from bachelor’s to PhD, recognized research and international reach.'),
                'footer_links' => [
                    ['label' => 'Admissions', 'url' => $admissions],
                    ['label' => $T('Frais et bourses', 'Tuition and scholarships'), 'url' => $fees],
                    ['label' => $T('Vie de campus', 'Campus life'), 'url' => $campus],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
