<?php

/*
 * Pack « Écoles et universités » : 8 pages, en français et en anglais.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'établissement). Les liens internes
 * sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Écoles et universités', 'en' => 'Schools and universities'],
        'icon' => 'graduation-cap',
        'keywords' => ['école', 'collège', 'lycée', 'université', 'institut', 'formation', 'enseignement', 'inscriptions', 'éducation', 'school', 'college', 'university', 'academy', 'institute', 'education'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $about = $u('/a-propos', '/about');
        $programs = $u('/formations', '/programs');
        $admissions = $u('/inscriptions', '/admissions');
        $campus = $u('/vie-scolaire', '/campus-life');
        $staff = $u('/equipe', '/our-team');
        $news = $u('/actualites', '/news');
        $contact = '/contact';

        $levels = [
            ['baby', $T('Maternelle', 'Nursery'), $T('Éveil, langage et jeux éducatifs pour les 3 à 5 ans, dans un cadre sécurisant.', 'Early learning, language and educational play for ages 3 to 5, in a safe setting.')],
            ['backpack', $T('Primaire', 'Primary'), $T('Lecture, calcul et découverte du monde, avec un suivi personnalisé de chaque élève.', 'Reading, numeracy and discovery of the world, with personal follow-up for every pupil.')],
            ['book-open', $T('Secondaire', 'Secondary'), $T('Sciences, lettres et langues, avec préparation aux examens d’État.', 'Sciences, humanities and languages, with preparation for national exams.')],
            ['flask-conical', $T('Filière scientifique', 'Science track'), $T('Mathématiques, physique, chimie et biologie avec travaux pratiques en laboratoire.', 'Mathematics, physics, chemistry and biology with hands-on lab work.')],
            ['calculator', $T('Gestion et commerce', 'Business and commerce'), $T('Comptabilité, économie et entrepreneuriat pour préparer l’entrée dans la vie active.', 'Accounting, economics and entrepreneurship to prepare for working life.')],
            ['laptop', $T('Informatique et numérique', 'Computing and digital skills'), $T('Bureautique, programmation et culture numérique dès le plus jeune âge.', 'Office tools, programming and digital literacy from an early age.')],
        ];
        $stats = ['title' => $T('L’école en chiffres', 'Our school in numbers'), 'items' => [
            [1200, '+', $T('élèves et étudiants', 'students')],
            [65, '', $T('enseignants qualifiés', 'qualified teachers')],
            [94, ' %', $T('de réussite aux examens', 'exam success rate')],
            [25, '', $T('années d’expérience', 'years of experience')],
        ]];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Parents et anciens élèves témoignent', 'Parents and alumni share their experience'), 'items' => [
            [$T('Mon fils a gagné en confiance et en résultats. Les enseignants sont à l’écoute et très disponibles.', 'My son has gained confidence and better grades. The teachers listen and are always available.'), '[Prénom Nom]', $T('Parent d’élève', 'Parent')],
            [$T('Les professeurs m’ont poussée à viser plus haut. Aujourd’hui, je poursuis mes études à l’étranger.', 'My teachers pushed me to aim higher. Today I am continuing my studies abroad.'), '[Prénom Nom]', $T('Ancienne élève, promotion 2018', 'Alumna, class of 2018')],
            [$T('Un cadre sérieux, une vraie discipline et beaucoup d’activités pour s’épanouir.', 'A serious environment, real discipline and plenty of activities to help children flourish.'), '[Prénom Nom]', $T('Parent d’élève', 'Parent')],
        ]];
        $cta = ['title' => $T('Rejoignez notre communauté éducative', 'Join our learning community'), 'text' => $T('Les inscriptions sont ouvertes. Venez visiter l’établissement et rencontrer notre équipe.', 'Admissions are open. Come and visit the school and meet our team.'),
            'buttons' => [[$T('Inscrire mon enfant', 'Apply now'), $admissions, 'accent'], [$T('Nous contacter', 'Contact us'), $contact, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Un établissement d’excellence où chaque élève apprend, grandit et réussit. Découvrez nos formations et inscrivez-vous.', 'A school of excellence where every student learns, grows and succeeds. Discover our programs and apply.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Inscriptions ouvertes', 'Admissions open'),
                    'title' => $T('Apprendre aujourd’hui, réussir demain', 'Learn today, succeed tomorrow'),
                    'text' => $T('Un enseignement exigeant, des classes à taille humaine et un accompagnement de chaque élève, de la maternelle à l’enseignement supérieur.', 'Demanding teaching, human-sized classes and personal support for every student, from nursery to higher education.'),
                    'buttons' => [[$T('S’inscrire', 'Apply now'), $admissions], [$T('Nos formations', 'Our programs'), $programs]],
                    'photo' => ['ecole-accueil', 'graduation-cap', $T('Élèves souriants devant le bâtiment de l’école', 'Smiling students in front of the school building')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Pourquoi nous choisir', 'Why choose us'), 'title' => $T('Trois raisons de nous faire confiance', 'Three reasons to trust us'), 'items' => [
                    ['award', $T('Excellence académique', 'Academic excellence'), $T('Des programmes rigoureux et d’excellents résultats aux examens.', 'Rigorous programs and excellent exam results.')],
                    ['heart-handshake', $T('Suivi personnalisé', 'Personal support'), $T('Des classes à effectif réduit et des enseignants proches des familles.', 'Small classes and teachers who work closely with families.')],
                    ['shield-check', $T('Cadre sûr et bienveillant', 'A safe, caring environment'), $T('Discipline, respect et sécurité au quotidien.', 'Discipline, respect and everyday safety.')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Nos formations', 'Our programs'), 'title' => $T('Un parcours pour chaque élève', 'A pathway for every student'), 'bg' => 'light', 'items' => array_slice($levels, 0, 3)]),
                B::stats($theme, $stats),
                B::split($theme, [
                    'eyebrow' => $T('Notre établissement', 'Our school'),
                    'title' => $T('Une école ouverte sur le monde', 'A school open to the world'),
                    'html' => '<p>'.e($T('Notre établissement forme des jeunes compétents, curieux et responsables. Nous allions rigueur scolaire, valeurs humaines et ouverture aux langues et au numérique.', 'Our school shapes skilled, curious and responsible young people. We combine academic rigor, human values and a strong focus on languages and digital skills.')).'</p>',
                    'bullets' => [$T('Enseignants formés et suivis', 'Trained, supervised teachers'), $T('Laboratoires, bibliothèque et salle informatique', 'Labs, library and computer room'), $T('Activités sportives et culturelles', 'Sports and cultural activities')],
                    'buttons' => [[$T('Découvrir l’école', 'Discover the school'), $about, 'outline']],
                    'photo' => ['ecole-classe', 'school', $T('Salle de classe lumineuse avec des élèves attentifs', 'Bright classroom with attentive students')],
                    'reverse' => true,
                ]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('a-propos', 'about'), 'title' => $T('À propos', 'About us'),
            'description' => $T('Notre histoire, notre projet éducatif, nos valeurs et l’équipe de direction de l’établissement.', 'Our history, educational project, values and the school’s leadership team.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('À propos', 'About us'), 'title' => $T('Notre histoire et notre projet éducatif', 'Our history and educational project'), 'intro' => $T('Un établissement au service de la réussite et de l’épanouissement de chaque élève.', 'A school dedicated to the success and personal growth of every student.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our history'),
                    'title' => $T('Plus de vingt ans au service de l’éducation', 'Over twenty years serving education'),
                    'html' => '<p>'.e($T('Fondé par un groupe d’éducateurs convaincus que l’école change des vies, notre établissement a commencé avec deux classes et quelques dizaines d’élèves. Il accueille aujourd’hui plus de mille élèves, de la maternelle au supérieur.', 'Founded by educators convinced that school changes lives, our school started with two classrooms and a few dozen students. Today it welcomes over a thousand students, from nursery to higher education.')).'</p><p>'.e($T('Établissement reconnu : [Numéro d’agrément].', 'Recognized institution: [Accreditation number].')).'</p>',
                    'photo' => ['ecole-histoire', 'landmark', $T('Façade historique de l’établissement', 'The school’s historic building')],
                ]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Notre mission : former des citoyens instruits, ouverts et responsables, capables de bâtir l’avenir de leur pays.', 'Our mission: to educate learned, open-minded and responsible citizens able to build their country’s future.'), 'source' => $T('— Projet éducatif', '— Educational project')]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce qui guide notre pédagogie', 'What guides our teaching'), 'items' => [
                    ['book-open', $T('Exigence', 'Rigor'), $T('Le goût de l’effort et du travail bien fait.', 'A taste for effort and work well done.')],
                    ['users-round', $T('Respect', 'Respect'), $T('Le respect de soi, des autres et de la communauté.', 'Respect for oneself, others and the community.')],
                    ['lightbulb', $T('Curiosité', 'Curiosity'), $T('L’envie d’apprendre, d’expérimenter et d’innover.', 'The desire to learn, experiment and innovate.')],
                    ['globe', $T('Ouverture', 'Openness'), $T('Les langues, les cultures et le monde numérique.', 'Languages, cultures and the digital world.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('La direction', 'Leadership'), 'title' => $T('L’équipe qui dirige l’établissement', 'The team leading the school'), 'bg' => 'light', 'shape' => 'rounded', 'items' => [
                    ['[Prénom Nom]', $T('Directeur général', 'Principal'), 'ecole-direction-1'],
                    ['[Prénom Nom]', $T('Directrice des études', 'Director of studies'), 'ecole-direction-2'],
                    ['[Prénom Nom]', $T('Responsable de la vie scolaire', 'Head of student life'), 'ecole-direction-3'],
                    ['[Prénom Nom]', $T('Responsable administratif et financier', 'Administrative and finance manager'), 'ecole-direction-4'],
                ]]),
            ]];

        $pages[] = ['key' => 'programs', 'slug' => $u('formations', 'programs'), 'title' => $T('Nos formations', 'Our programs'),
            'description' => $T('De la maternelle au secondaire et aux filières spécialisées : découvrez nos niveaux, notre pédagogie et le déroulement de l’année.', 'From nursery to secondary and specialized tracks: discover our levels, teaching approach and the school year.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nos formations', 'Our programs'), 'title' => $T('Des parcours adaptés à chaque âge', 'Pathways suited to every age'), 'intro' => $T('Des programmes complets, du premier apprentissage à la préparation de l’avenir.', 'Complete programs, from first lessons to preparing for the future.')]),
                B::features($theme, ['items' => $levels, 'cols' => 3]),
                B::split($theme, [
                    'eyebrow' => $T('Notre pédagogie', 'Our teaching approach'),
                    'title' => $T('Apprendre en comprenant, en pratiquant', 'Learning by understanding and doing'),
                    'html' => '<p>'.e($T('Nous privilégions une pédagogie active : exercices, projets, travaux de groupe et expériences. Chaque élève est évalué régulièrement et reçoit un soutien adapté si nécessaire.', 'We favor active learning: exercises, projects, group work and experiments. Every student is assessed regularly and receives tailored support when needed.')).'</p>',
                    'bullets' => [$T('Classes à effectif réduit', 'Small class sizes'), $T('Soutien scolaire et remise à niveau', 'Tutoring and catch-up classes'), $T('Bulletins et rencontres parents-enseignants', 'Report cards and parent-teacher meetings')],
                    'photo' => ['ecole-laboratoire', 'flask-conical', $T('Élèves réalisant une expérience en laboratoire', 'Students carrying out a lab experiment')],
                    'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('L’année scolaire', 'The school year'), 'title' => $T('Une année rythmée en quatre temps', 'A year in four stages'), 'items' => [
                    [$T('Rentrée', 'Start of year'), $T('Accueil des élèves, tests de niveau et présentation des programmes.', 'Welcome, placement tests and presentation of the programs.')],
                    [$T('Premier semestre', 'First semester'), $T('Cours, évaluations continues et premier bulletin.', 'Lessons, continuous assessment and first report card.')],
                    [$T('Second semestre', 'Second semester'), $T('Approfondissement, projets et activités culturelles.', 'Deeper study, projects and cultural activities.')],
                    [$T('Examens', 'Exams'), $T('Révisions, examens de fin d’année et remise des diplômes.', 'Revision, end-of-year exams and graduation.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'admissions', 'slug' => $u('inscriptions', 'admissions'), 'title' => $T('Inscriptions', 'Admissions'),
            'description' => $T('Étapes d’inscription, frais de scolarité, documents à fournir et réponses à vos questions.', 'Enrolment steps, tuition fees, required documents and answers to your questions.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Inscriptions', 'Admissions'), 'title' => $T('Inscrire votre enfant en quatre étapes', 'Enrol your child in four steps'), 'intro' => $T('Une procédure simple, un accompagnement à chaque étape.', 'A simple process with support at every step.')]),
                B::steps($theme, ['eyebrow' => $T('Procédure', 'Process'), 'title' => $T('Comment s’inscrire', 'How to apply'), 'items' => [
                    [$T('Prise de contact', 'Get in touch'), $T('Contactez le secrétariat ou venez visiter l’établissement.', 'Contact the office or come and visit the school.')],
                    [$T('Dossier', 'Application'), $T('Remplissez le formulaire et déposez les documents demandés.', 'Complete the form and submit the required documents.')],
                    [$T('Test et entretien', 'Test and interview'), $T('Un test de niveau et un entretien avec la famille.', 'A placement test and an interview with the family.')],
                    [$T('Confirmation', 'Confirmation'), $T('Réponse sous une semaine, puis paiement des frais d’inscription.', 'Answer within a week, then payment of the enrolment fee.')],
                ]]),
                B::pricing($theme, ['eyebrow' => $T('Frais de scolarité', 'Tuition fees'), 'title' => $T('Des tarifs clairs, par année scolaire', 'Clear fees, per school year'), 'url' => $contact, 'items' => [
                    [$T('Maternelle', 'Nursery'), '350', '$', $T('/ an', '/ year'), $T('Pour les 3 à 5 ans.', 'For ages 3 to 5.'), [$T('Journée complète', 'Full days'), $T('Repas du midi en option', 'Optional lunch'), $T('Activités d’éveil', 'Early learning activities')], $T('Demander un devis', 'Request a quote')],
                    [$T('Primaire et secondaire', 'Primary and secondary'), '500', '$', $T('/ an', '/ year'), $T('Tous les niveaux jusqu’à la terminale.', 'All levels up to final year.'), [$T('Manuels inclus', 'Textbooks included'), $T('Soutien scolaire', 'Tutoring'), $T('Activités extrascolaires', 'Extracurricular activities'), $T('Suivi personnalisé', 'Personal follow-up')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Enseignement supérieur', 'Higher education'), '[à compléter]', '', '', $T('Selon la filière choisie.', 'Depending on the chosen track.'), [$T('Cours et travaux pratiques', 'Lectures and practical work'), $T('Accès à la bibliothèque', 'Library access'), $T('Accompagnement de stage', 'Internship support')], $T('Demander un devis', 'Request a quote')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Dossier d’inscription', 'Application file'), 'title' => $T('Documents à fournir', 'Required documents'), 'bg' => 'white', 'groups' => [
                    [$T('Pour l’élève', 'For the student'), [
                        ['file-text', $T('Acte de naissance', 'Birth certificate')],
                        ['id-card', $T('Deux photos d’identité', 'Two passport photos')],
                        ['clipboard-check', $T('Dernier bulletin de notes', 'Latest report card')],
                        ['heart-pulse', $T('Carnet de vaccination', 'Vaccination record')],
                    ]],
                    [$T('Pour la famille', 'For the family'), [
                        ['id-card', $T('Pièce d’identité du parent ou tuteur', 'ID of the parent or guardian')],
                        ['file-text', $T('Formulaire d’inscription signé', 'Signed application form')],
                        ['receipt', $T('Reçu des frais d’inscription', 'Receipt for the enrolment fee')],
                        ['phone', $T('Coordonnées à jour', 'Up-to-date contact details')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'intro' => $T('Une autre question ? Le secrétariat vous répond.', 'Another question? Our office will answer.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'bg' => 'light', 'items' => [
                    [$T('Quand ouvrent les inscriptions ?', 'When do admissions open?'), $T('Les inscriptions ouvrent plusieurs mois avant la rentrée et se poursuivent dans la limite des places disponibles.', 'Admissions open several months before the start of the year and continue while places remain.')],
                    [$T('Les frais peuvent-ils être payés en plusieurs fois ?', 'Can fees be paid in instalments?'), $T('Oui, un paiement échelonné par trimestre est possible. Contactez le secrétariat.', 'Yes, quarterly instalments are possible. Contact the office.')],
                    [$T('Y a-t-il un test d’entrée ?', 'Is there an entrance test?'), $T('Un test de niveau permet de placer l’élève dans la classe la mieux adaptée.', 'A placement test helps place the student in the most suitable class.')],
                    [$T('Proposez-vous des bourses ou des réductions ?', 'Do you offer scholarships or discounts?'), $T('Des réductions pour les fratries et des aides au mérite peuvent être accordées. Renseignez-vous auprès de la direction.', 'Sibling discounts and merit-based aid may be granted. Ask the principal’s office.')],
                ]]),
            ]];

        $pages[] = ['key' => 'campus', 'slug' => $u('vie-scolaire', 'campus-life'), 'title' => $T('Vie scolaire', 'Campus life'),
            'description' => $T('Bibliothèque, laboratoires, terrains de sport, cantine et clubs : découvrez la vie au sein de notre établissement.', 'Library, labs, sports grounds, canteen and clubs: discover life at our school.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Vie scolaire', 'Campus life'), 'title' => $T('Un cadre pour apprendre et s’épanouir', 'A place to learn and thrive'), 'intro' => $T('Des infrastructures modernes et de nombreuses activités tout au long de l’année.', 'Modern facilities and many activities throughout the year.')]),
                B::features($theme, ['eyebrow' => $T('Nos infrastructures', 'Our facilities'), 'title' => $T('Tout pour bien étudier', 'Everything to study well'), 'cols' => 4, 'items' => [
                    ['library', $T('Bibliothèque', 'Library'), $T('Livres, revues et espaces de travail calmes.', 'Books, journals and quiet study areas.')],
                    ['microscope', $T('Laboratoires', 'Laboratories'), $T('Sciences expérimentales et travaux pratiques.', 'Experimental sciences and practical work.')],
                    ['dumbbell', $T('Terrains de sport', 'Sports grounds'), $T('Football, basketball, athlétisme et volley-ball.', 'Football, basketball, athletics and volleyball.')],
                    ['utensils', $T('Cantine', 'Canteen'), $T('Repas équilibrés préparés chaque jour.', 'Balanced meals prepared every day.')],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('La vie de l’établissement', 'Life at the school'), 'bg' => 'light', 'photos' => [
                    ['ecole-bibliotheque', 'library', $T('Élèves à la bibliothèque', 'Students in the library')],
                    ['ecole-laboratoire', 'flask-conical', $T('Élèves réalisant une expérience en laboratoire', 'Students carrying out a lab experiment')],
                    ['ecole-sport', 'dumbbell', $T('Match de football entre classes', 'Football match between classes')],
                    ['ecole-cantine', 'utensils', $T('Repas à la cantine', 'Lunch in the canteen')],
                    ['ecole-musique', 'music', $T('Chorale de l’école en répétition', 'School choir rehearsing')],
                    ['ecole-remise-diplomes', 'graduation-cap', $T('Cérémonie de remise des diplômes', 'Graduation ceremony')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Clubs et activités', 'Clubs and activities'), 'title' => $T('Pour développer tous les talents', 'To develop every talent'), 'items' => [
                    ['music', $T('Musique et théâtre', 'Music and drama'), $T('Chorale, orchestre et troupe de théâtre.', 'Choir, band and drama group.')],
                    ['trophy', $T('Sport', 'Sports'), $T('Compétitions entre classes et tournois inter-écoles.', 'Inter-class competitions and inter-school tournaments.')],
                    ['laptop', $T('Club informatique', 'Computer club'), $T('Initiation à la programmation et à la robotique.', 'Introduction to programming and robotics.')],
                ]]),
            ]];

        $pages[] = ['key' => 'staff', 'slug' => $u('equipe', 'our-team'), 'title' => $T('Notre équipe', 'Our team'),
            'description' => $T('Rencontrez les enseignants et le personnel qui accompagnent chaque jour nos élèves.', 'Meet the teachers and staff who support our students every day.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Notre équipe', 'Our team'), 'title' => $T('Des enseignants engagés', 'Dedicated teachers'), 'intro' => $T('Des professionnels qualifiés, passionnés par la transmission du savoir.', 'Qualified professionals who are passionate about sharing knowledge.')]),
                B::team($theme, ['title' => $T('Enseignants et personnel', 'Teachers and staff'), 'shape' => 'rounded', 'items' => [
                    ['[Prénom Nom]', $T('Mathématiques', 'Mathematics'), 'ecole-prof-1'],
                    ['[Prénom Nom]', $T('Français et littérature', 'French and literature'), 'ecole-prof-2'],
                    ['[Prénom Nom]', $T('Sciences physiques', 'Physical sciences'), 'ecole-prof-3'],
                    ['[Prénom Nom]', $T('Anglais', 'English'), 'ecole-prof-4'],
                    ['[Prénom Nom]', $T('Informatique', 'Computer science'), 'ecole-prof-5'],
                    ['[Prénom Nom]', $T('Éducation physique', 'Physical education'), 'ecole-prof-6'],
                    ['[Prénom Nom]', $T('Conseillère d’orientation', 'Guidance counselor'), 'ecole-prof-7'],
                    ['[Prénom Nom]', $T('Secrétaire général', 'Registrar'), 'ecole-prof-8'],
                ]]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Enseigner, c’est croire en chaque élève et l’aider à découvrir ce dont il est capable.', 'To teach is to believe in every student and help them discover what they are capable of.'), 'source' => '— [Prénom Nom], '.$T('directeur des études', 'director of studies')]),
                B::cta($theme, ['title' => $T('Rejoignez notre équipe', 'Join our team'), 'text' => $T('Nous recrutons régulièrement des enseignants et du personnel passionnés. Envoyez-nous votre candidature.', 'We regularly recruit passionate teachers and staff. Send us your application.'),
                    'buttons' => [[$T('Postuler', 'Apply'), $contact, 'accent']], 'tone' => 'dark']),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites', 'news'), 'title' => $T('Actualités', 'News'),
            'description' => $T('Les dernières nouvelles de l’établissement et le calendrier : portes ouvertes, examens, remise des diplômes.', 'The latest school news and calendar: open day, exams, graduation.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('La vie de l’établissement', 'What’s happening at school'), 'intro' => $T('Résultats, événements et informations pour les familles.', 'Results, events and information for families.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['eyebrow' => $T('Calendrier', 'Calendar'), 'title' => $T('Dates à retenir', 'Key dates'), 'bg' => 'light', 'items' => [
                    [$T('Journée portes ouvertes', 'Open day'), $T('14 mars, 9 h – 15 h', 'March 14, 9 am – 3 pm'), $T('Campus principal', 'Main campus')],
                    [$T('Réunion parents-enseignants', 'Parent-teacher meeting'), $T('9 avril, 16 h', 'April 9, 4 pm'), $T('Salle polyvalente', 'Multipurpose hall')],
                    [$T('Examens de fin d’année', 'End-of-year exams'), $T('Du 8 au 19 juin', 'June 8 to 19'), $T('Salles d’examen', 'Exam rooms')],
                    [$T('Remise des diplômes', 'Graduation ceremony'), $T('3 juillet, 10 h', 'July 3, 10 am'), $T('Cour d’honneur', 'Main courtyard')],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez le secrétariat pour une inscription, une visite de l’établissement ou toute question.', 'Contact the school office about admissions, a visit or any question.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Nous sommes à votre écoute', 'We’re here to help'), 'intro' => $T('Le secrétariat vous répond sous 48 heures.', 'Our office replies within 48 hours.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Écrivez-nous ou passez nous voir pour visiter l’établissement.', 'Write to us or drop in for a tour of the school.'),
                    'items' => [
                        ['map-pin', $T('Avenue de l’Université, Douala, Cameroun', 'Avenue de l’Université, Douala, Cameroon')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 7 h 30 – 16 h 30', 'Monday – Friday, 7:30 am – 4:30 pm')],
                    ],
                    'social' => ['facebook', 'instagram', 'youtube', 'whatsapp'],
                    'subject' => true,
                    'map' => 'Douala, Cameroun',
                    'bg' => 'white',
                ]),
            ]];

        $blog = [
            'base' => $u('actualites', 'news'),
            'title' => $T('Actualités', 'News'),
            'categories' => ['resultats' => $T('Résultats', 'Results'), 'vie' => $T('Vie scolaire', 'School life'), 'campus' => $T('Campus', 'Campus')],
            'posts' => [
                ['key' => 'examens', 'slug' => $u('resultats-examens-etat', 'national-exam-results'), 'category' => 'resultats', 'tags' => [$T('Examens', 'Exams')],
                    'title' => $T('Excellents résultats aux examens d’État', 'Excellent national exam results'),
                    'excerpt' => $T('Nos élèves de dernière année signent une nouvelle année de réussite.', 'Our final-year students achieve another successful year.'),
                    'photo' => ['ecole-actu-1', 'trophy', $T('Élève qui révise', 'Student revising')],
                    'heading' => $T('Merci aux enseignants et aux familles', 'Thank you to teachers and families'),
                    'body' => [
                        $T('Cette année encore, [taux] % de nos élèves ont réussi les examens d’État, dont [nombre] avec une mention.', 'Once again this year, [rate]% of our students passed the national exams, [number] of them with honors.'),
                        $T('Ces résultats sont le fruit d’un travail régulier : séances de révision le samedi, devoirs suivis et accompagnement personnalisé des élèves en difficulté.', 'These results come from steady work: Saturday revision sessions, monitored homework and personal support for students who struggle.'),
                        $T('La cérémonie de remise des diplômes aura lieu le [date]. Les familles y sont chaleureusement invitées.', 'The graduation ceremony will take place on [date]. Families are warmly invited.'),
                    ]],
                ['key' => 'laboratoire', 'slug' => $u('nouveau-laboratoire', 'new-science-lab'), 'category' => 'campus', 'tags' => [$T('Sciences', 'Science')],
                    'title' => $T('Un nouveau laboratoire de sciences', 'A new science laboratory'),
                    'excerpt' => $T('Un espace moderne pour les travaux pratiques de physique et de chimie.', 'A modern space for physics and chemistry practicals.'),
                    'photo' => ['ecole-actu-2', 'flask-conical', $T('Microscope de laboratoire', 'Laboratory microscope')],
                    'quote' => $T('« Les élèves apprennent mieux quand ils manipulent eux-mêmes. »', '“Students learn better when they handle things themselves.”'),
                    'body' => [
                        $T('Le nouveau laboratoire accueille une classe entière avec des paillasses équipées, des microscopes et le matériel de sécurité nécessaire.', 'The new laboratory hosts a full class with equipped benches, microscopes and the necessary safety equipment.'),
                        $T('Chaque classe du secondaire y aura au moins une séance de travaux pratiques par semaine, encadrée par nos professeurs de sciences.', 'Every secondary class will have at least one practical session there each week, supervised by our science teachers.'),
                        $T('Les familles pourront visiter le laboratoire lors de la journée portes ouvertes du [date].', 'Families can visit the laboratory during the open day on [date].'),
                    ]],
                ['key' => 'gala', 'slug' => $u('gala-fin-annee', 'end-of-year-gala'), 'category' => 'vie', 'tags' => [$T('Culture', 'Culture')],
                    'title' => $T('Gala de fin d’année', 'End-of-year gala'),
                    'excerpt' => $T('Chants, théâtre et danses : les élèves ont enchanté les familles.', 'Songs, drama and dance: students delighted the families.'),
                    'photo' => ['ecole-actu-3', 'music', $T('Danseuse sur scène', 'Dancer on stage')],
                    'heading' => $T('Rendez-vous l’an prochain', 'See you next year'),
                    'body' => [
                        $T('Le gala de fin d’année a réuni élèves, enseignants et familles pour une soirée de spectacles préparés pendant tout le trimestre.', 'The end-of-year gala brought students, teachers and families together for an evening of performances prepared all term.'),
                        $T('La chorale, la troupe de théâtre et les groupes de danse ont montré tout leur talent, avant la remise des prix d’excellence.', 'The choir, the drama group and the dance groups showed all their talent, before the excellence awards were presented.'),
                        $T('Merci à toutes les familles venues partager ce moment. Les photos de la soirée sont disponibles auprès du secrétariat.', 'Thank you to all the families who came to share this moment. Photos of the evening are available from the school office.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('À propos', 'About'), 'url' => $about],
                    ['label' => $T('Formations', 'Programs'), 'url' => $programs],
                    ['label' => $T('Vie scolaire', 'Campus life'), 'url' => $campus],
                    ['label' => $T('Équipe', 'Our team'), 'url' => $staff],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Inscriptions', 'Admissions'),
                'cta_url' => $admissions,
                'footer_text' => $T('Un établissement d’enseignement où chaque élève apprend, grandit et réussit.', 'A school where every student learns, grows and succeeds.'),
                'footer_links' => [
                    ['label' => $T('Inscriptions', 'Admissions'), 'url' => $admissions],
                    ['label' => $T('Nos formations', 'Our programs'), 'url' => $programs],
                    ['label' => $T('Vie scolaire', 'Campus life'), 'url' => $campus],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
