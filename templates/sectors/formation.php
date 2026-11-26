<?php

/*
 * Pack « Centre de formation professionnelle » (secteur Écoles et universités) :
 * 8 pages, en français et en anglais. Organismes de formation continue,
 * centres de formation aux métiers, écoles de management ou du numérique pour
 * adultes : catalogue, sessions, entreprises, financement, certifications.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du centre). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Écoles et universités', 'en' => 'Schools and universities'],
        'icon' => 'graduation-cap',
        'keywords' => ['formation', 'formation professionnelle', 'formation continue', 'organisme de formation', 'centre de formation', 'certification', 'reconversion', 'e-learning', 'apprentissage', 'compétences', 'training', 'vocational training', 'professional development', 'continuing education', 'training center', 'certification', 'upskilling', 'reskilling', 'online courses'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $courses = $u('/formations', '/courses');
        $sessions = $u('/calendrier-des-sessions', '/session-calendar');
        $business = $u('/entreprises', '/for-business');
        $funding = $u('/financement', '/funding');
        $about = $u('/le-centre', '/about-us');
        $news = $u('/actualites', '/news');
        $contact = '/contact';
        $cur = $T('€', '$');

        $domains = [
            ['monitor', $T('Numérique et data', 'Digital and data'), $T('Bureautique avancée, développement web, analyse de données, cybersécurité.', 'Advanced office tools, web development, data analysis, cybersecurity.')],
            ['briefcase', $T('Management et RH', 'Management and HR'), $T('Encadrer une équipe, conduire le changement, recruter, piloter la performance.', 'Leading a team, driving change, recruiting, managing performance.')],
            ['calculator', $T('Gestion et finance', 'Accounting and finance'), $T('Comptabilité, contrôle de gestion, paie, fiscalité de l’entreprise.', 'Accounting, management control, payroll, business taxation.')],
            ['megaphone', $T('Marketing et vente', 'Marketing and sales'), $T('Marketing digital, réseaux sociaux, négociation, relation client.', 'Digital marketing, social media, negotiation, customer relations.')],
            ['hard-hat', $T('Sécurité et qualité', 'Safety and quality'), $T('Prévention des risques, secourisme au travail, démarche qualité.', 'Risk prevention, workplace first aid, quality management.')],
            ['languages', $T('Langues', 'Languages'), $T('Anglais et français professionnels, préparation aux tests reconnus.', 'Business English and French, preparation for recognized tests.')],
        ];
        $catalog = [
            ['formation-cours-data', 'chart-line', $T('Analyste de données', 'Data analyst'), $T('120 heures sur 12 semaines · présentiel ou à distance · certification reconnue · 1 890 €', '120 hours over 12 weeks · on site or online · recognized certification · $1,990')],
            ['formation-cours-web', 'code', $T('Développeur web', 'Web developer'), $T('400 heures sur 6 mois · parcours certifiant · projet réel en fin de parcours · 4 900 €', '400 hours over 6 months · certification track · real-world final project · $5,200')],
            ['formation-cours-management', 'users', $T('Manager d’équipe', 'Team manager'), $T('5 jours (35 heures) · présentiel · études de cas et mises en situation · 1 450 €', '5 days (35 hours) · on site · case studies and role plays · $1,550')],
            ['formation-cours-compta', 'calculator', $T('Comptabilité et paie', 'Accounting and payroll'), $T('70 heures sur 10 semaines · à distance en classe virtuelle · 1 200 €', '70 hours over 10 weeks · live virtual classroom · $1,290')],
            ['formation-cours-marketing', 'megaphone', $T('Marketing digital', 'Digital marketing'), $T('42 heures sur 6 semaines · hybride · campagne réelle à mener · 990 €', '42 hours over 6 weeks · blended · real campaign to run · $1,050')],
            ['formation-cours-securite', 'shield-check', $T('Sauveteur secouriste du travail', 'Workplace first aid'), $T('2 jours (14 heures) · présentiel · certificat valable 2 ans · 290 €', '2 days (14 hours) · on site · certificate valid for 2 years · $310')],
        ];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Ils se sont formés chez nous', 'They trained with us'), 'rating' => 5, 'items' => [
            [$T('Après quinze ans dans la vente, je voulais changer de métier. Le parcours développeur web m’a donné une méthode et un vrai portfolio : j’ai été recruté deux mois après.', 'After fifteen years in sales, I wanted a new career. The web developer track gave me a method and a real portfolio: I was hired two months later.'), '[Prénom Nom]', $T('Développeur, en reconversion', 'Developer, career changer')],
            [$T('Des formateurs qui exercent encore leur métier : on repart avec des outils utilisables dès le lundi suivant.', 'Trainers who still work in their field: you leave with tools you can use the following Monday.'), '[Prénom Nom]', $T('Responsable d’équipe, secteur logistique', 'Team leader, logistics sector')],
            [$T('Nous avons formé quarante collaborateurs en intra. Le programme a été adapté à nos outils et à nos horaires.', 'We trained forty employees in-house. The program was tailored to our tools and our schedules.'), '[Prénom Nom]', $T('Directrice des ressources humaines', 'HR director')],
        ]];
        $cta = ['title' => $T('Trouvons la formation qu’il vous faut', 'Let’s find the right course for you'), 'text' => $T('Un conseiller fait le point avec vous sur votre projet, votre niveau et les financements possibles. L’entretien est gratuit et sans engagement.', 'An adviser reviews your goals, your level and possible funding with you. The meeting is free, with no commitment.'),
            'buttons' => [[$T('Être rappelé par un conseiller', 'Talk to an adviser'), $contact, 'accent'], [$T('Voir les sessions', 'See upcoming sessions'), $sessions, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Centre de formation professionnelle : formations courtes et parcours certifiants, en présentiel ou à distance, pour les particuliers et les entreprises.', 'Professional training center: short courses and certification tracks, on site or online, for individuals and businesses.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Formation professionnelle et continue', 'Professional and continuing education'),
                    'title' => $T('Des compétences utiles, dès demain au travail', 'Skills you can use at work tomorrow'),
                    'text' => $T('Formations courtes, parcours certifiants et programmes sur mesure pour les entreprises : en salle, à distance ou les deux, avec des formateurs issus du terrain.', 'Short courses, certification tracks and tailored programs for businesses: in the classroom, online or both, with trainers who work in the field.'),
                    'buttons' => [[$T('Voir le catalogue', 'Browse courses'), $courses], [$T('Former mes équipes', 'Train my teams'), $business]],
                    'photo' => ['formation-accueil', 'presentation', $T('Formatrice animant un atelier devant un groupe d’adultes en salle de formation', 'Trainer leading a workshop for a group of adults in a training room')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos domaines', 'Our fields'), 'title' => $T('Six domaines de formation', 'Six fields of training'), 'intro' => $T('Plus de 80 formations, de la journée d’initiation au parcours certifiant de plusieurs mois.', 'Over 80 courses, from one-day introductions to certification tracks lasting several months.'), 'items' => $domains, 'bg' => 'light']),
                B::split($theme, [
                    'eyebrow' => $T('En salle ou à distance', 'On site or online'),
                    'title' => $T('Apprenez au rythme qui vous convient', 'Learn at the pace that suits you'),
                    'html' => '<p>'.e($T('Chaque formation existe en présentiel dans nos salles équipées, en classe virtuelle avec un formateur en direct, ou en format hybride. Vous accédez aussi à notre plateforme en ligne : supports, exercices et replays restent disponibles après la session.', 'Every course runs on site in our equipped rooms, in a live virtual classroom with a trainer, or in a blended format. You also get access to our online platform: materials, exercises and recordings remain available after the session.')).'</p>',
                    'bullets' => [$T('Groupes de 12 personnes au plus', 'Groups of 12 people at most'), $T('Sessions en journée, en soirée ou le samedi', 'Daytime, evening or Saturday sessions'), $T('Évaluation des acquis et attestation de fin de formation', 'Skills assessment and certificate of completion')],
                    'buttons' => [[$T('Le calendrier des sessions', 'Session calendar'), $sessions, 'outline']],
                    'photo' => ['formation-distance', 'laptop', $T('Adulte suivant une classe virtuelle sur un ordinateur portable, casque sur les oreilles', 'Adult attending a virtual class on a laptop, wearing a headset')],
                    'reverse' => true,
                ]),
                B::stats($theme, ['title' => $T('Le centre en chiffres', 'The center at a glance'), 'items' => [
                    [3200, '', $T('stagiaires formés par an', 'learners trained each year')],
                    [94, ' %', $T('de stagiaires satisfaits', 'learner satisfaction')],
                    [82, ' %', $T('de réussite aux certifications', 'certification pass rate')],
                    [150, '+', $T('entreprises clientes', 'client companies')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Votre parcours', 'Your journey'), 'title' => $T('De la première question au certificat', 'From your first question to your certificate'), 'items' => [
                    [$T('Entretien conseil', 'Advice meeting'), $T('Nous analysons votre projet, votre niveau et vos contraintes d’agenda.', 'We look at your goals, your level and your schedule.')],
                    [$T('Financement', 'Funding'), $T('Nous identifions avec vous les aides et dispositifs possibles.', 'We identify the grants and schemes available to you.')],
                    [$T('Formation', 'Training'), $T('Cours, cas pratiques et suivi personnalisé par un formateur référent.', 'Classes, case studies and personal follow-up by a lead trainer.')],
                    [$T('Certification', 'Certification'), $T('Évaluation finale, certificat et accompagnement vers l’emploi.', 'Final assessment, certificate and career support.')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::posts($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Conseils et nouveautés', 'Tips and updates'), 'source' => 'latest', 'count' => 3, 'more' => $T('Lire l’article', 'Read the article')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'courses', 'slug' => $u('formations', 'courses'), 'title' => $T('Nos formations', 'Our courses'),
            'description' => $T('Catalogue des formations : numérique, management, gestion, marketing, sécurité et langues. Durées, formats, prix et certifications.', 'Course catalog: digital, management, finance, marketing, safety and languages. Durations, formats, prices and certifications.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Catalogue', 'Catalog'), 'title' => $T('Nos formations', 'Our courses'), 'intro' => $T('Durée, format, prix et certification : tout est indiqué pour vous aider à choisir.', 'Duration, format, price and certification: everything is listed to help you choose.')]),
                B::cards($theme, ['eyebrow' => $T('Les plus demandées', 'Most popular'), 'title' => $T('Formations phares', 'Featured courses'), 'more' => $T('Demander le programme', 'Request the syllabus'), 'items' => array_map(fn ($c) => [...$c, $contact], $catalog)]),
                B::features($theme, ['eyebrow' => $T('Formats', 'Formats'), 'title' => $T('Quatre façons de vous former', 'Four ways to learn'), 'cols' => 4, 'bg' => 'light', 'items' => [
                    ['building-2', $T('Présentiel', 'On site'), $T('Dans nos salles équipées, en petit groupe, avec un formateur.', 'In our equipped rooms, in small groups, with a trainer.')],
                    ['video', $T('Classe virtuelle', 'Virtual classroom'), $T('Cours en direct à distance, aux mêmes horaires qu’en salle.', 'Live online classes, on the same schedule as on site.')],
                    ['layers', $T('Hybride', 'Blended'), $T('Alternance de modules en ligne et de journées en présentiel.', 'Online modules alternating with on-site days.')],
                    ['laptop', $T('E-learning', 'E-learning'), $T('Modules en autonomie, accessibles 24 h/24 pendant 12 mois.', 'Self-paced modules, available 24/7 for 12 months.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Parcours certifiants', 'Certification tracks'),
                    'title' => $T('Des certifications reconnues par les employeurs', 'Certifications employers recognize'),
                    'html' => '<p>'.e($T('Nos parcours longs préparent à des certifications professionnelles enregistrées [référence du registre officiel] ou délivrées par des éditeurs et organismes reconnus. Le programme, les modalités d’évaluation et les taux de réussite sont communiqués avant l’inscription.', 'Our longer tracks prepare for professional certifications listed in [official register reference] or awarded by recognized vendors and bodies. The syllabus, assessment methods and pass rates are shared before you enroll.')).'</p>',
                    'bullets' => [$T('Positionnement à l’entrée pour adapter le parcours', 'Entry assessment to tailor your track'), $T('Projet professionnel encadré', 'Supervised professional project'), $T('Passage de la certification inclus', 'Certification exam included')],
                    'buttons' => [[$T('Financer ma formation', 'Fund my training'), $funding]],
                    'photo' => ['formation-certification', 'award', $T('Stagiaire recevant son certificat lors d’une remise de diplômes', 'Learner receiving a certificate at a graduation ceremony')],
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Avant de vous inscrire', 'Before you enroll'), 'intro' => $T('Une question sur un programme ? Un conseiller vous répond.', 'A question about a course? An adviser will answer.'), 'buttons' => [[$T('Poser une question', 'Ask a question'), $contact, 'outline']], 'bg' => 'light', 'items' => [
                    [$T('Faut-il un diplôme pour s’inscrire ?', 'Do I need a degree to enroll?'), $T('Non pour la plupart des formations courtes. Les parcours certifiants demandent parfois un niveau minimal, vérifié lors d’un test de positionnement.', 'Not for most short courses. Certification tracks sometimes require a minimum level, checked with a placement test.')],
                    [$T('Puis-je suivre une formation tout en travaillant ?', 'Can I train while working?'), $T('Oui : sessions en soirée, le samedi, en classe virtuelle ou en e-learning, selon les formations.', 'Yes: evening, Saturday, virtual classroom or e-learning sessions, depending on the course.')],
                    [$T('Que se passe-t-il si je manque une séance ?', 'What if I miss a session?'), $T('Les supports et les enregistrements des classes virtuelles restent disponibles sur la plateforme, et votre formateur vous aide à rattraper.', 'Materials and virtual class recordings stay available on the platform, and your trainer helps you catch up.')],
                    [$T('Vos locaux sont-ils accessibles aux personnes handicapées ?', 'Are your premises accessible to people with disabilities?'), $T('Oui. Un référent handicap étudie avec vous les aménagements nécessaires avant le début de la formation.', 'Yes. A disability officer reviews any adjustments you need with you before the course starts.')],
                ]]),
            ]];

        $pages[] = ['key' => 'sessions', 'slug' => $u('calendrier-des-sessions', 'session-calendar'), 'title' => $T('Calendrier des sessions', 'Session calendar'),
            'description' => $T('Prochaines sessions de formation : dates, durées, formats et places disponibles. Réunions d’information et journées portes ouvertes.', 'Upcoming training sessions: dates, durations, formats and seats available. Information sessions and open days.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Calendrier', 'Calendar'), 'title' => $T('Les prochaines sessions', 'Upcoming sessions'), 'intro' => $T('Inscriptions ouvertes jusqu’à dix jours avant le début de chaque session, dans la limite des places disponibles.', 'Enrollment is open until ten days before each session starts, subject to availability.')]),
                M::table($theme, ['eyebrow' => $T('Prochains départs', 'Next start dates'), 'title' => $T('Sessions du semestre', 'This semester’s sessions'), 'note' => $T('Dates données à titre indicatif : le calendrier à jour est envoyé avec le programme détaillé.', 'Dates are indicative: the up-to-date calendar is sent with the detailed syllabus.'), 'rows' => [
                    [$T('Formation', 'Course'), $T('Début', 'Start'), $T('Durée', 'Duration'), $T('Format', 'Format'), $T('Places', 'Seats')],
                    [$T('Analyste de données', 'Data analyst'), $T('8 septembre', 'September 8'), $T('12 semaines', '12 weeks'), $T('Hybride', 'Blended'), $T('4 restantes', '4 left')],
                    [$T('Manager d’équipe', 'Team manager'), $T('15 septembre', 'September 15'), $T('5 jours', '5 days'), $T('Présentiel', 'On site'), $T('Complet', 'Full')],
                    [$T('Comptabilité et paie', 'Accounting and payroll'), $T('22 septembre', 'September 22'), $T('10 semaines', '10 weeks'), $T('Classe virtuelle', 'Virtual'), $T('7 restantes', '7 left')],
                    [$T('Marketing digital', 'Digital marketing'), $T('6 octobre', 'October 6'), $T('6 semaines', '6 weeks'), $T('Hybride', 'Blended'), $T('9 restantes', '9 left')],
                    [$T('Développeur web', 'Web developer'), $T('3 novembre', 'November 3'), $T('6 mois', '6 months'), $T('Présentiel', 'On site'), $T('5 restantes', '5 left')],
                    [$T('Sauveteur secouriste du travail', 'Workplace first aid'), $T('Chaque mois', 'Every month'), $T('2 jours', '2 days'), $T('Présentiel', 'On site'), $T('Sur demande', 'On request')],
                ]]),
                B::events($theme, ['icon' => 'calendar-check', 'eyebrow' => $T('Pour vous informer', 'Find out more'), 'title' => $T('Réunions d’information et portes ouvertes', 'Information sessions and open days'), 'bg' => 'light', 'items' => [
                    [$T('Réunion d’information : se reconvertir dans le numérique', 'Information session: switching to a career in tech'), $T('Mardi 2 septembre, 18 h', 'Tuesday, September 2, 6 pm'), $T('En ligne', 'Online')],
                    [$T('Journée portes ouvertes', 'Open day'), $T('Samedi 13 septembre, 10 h – 16 h', 'Saturday, September 13, 10 am – 4 pm'), $T('Au centre', 'At the center')],
                    [$T('Atelier découverte : initiation à l’analyse de données', 'Taster workshop: introduction to data analysis'), $T('Jeudi 25 septembre, 14 h', 'Thursday, September 25, 2 pm'), $T('Au centre', 'At the center')],
                    [$T('Webinaire : financer sa formation', 'Webinar: how to fund your training'), $T('Mercredi 8 octobre, 12 h 30', 'Wednesday, October 8, 12:30 pm'), $T('En ligne', 'Online')],
                ]]),
                B::cta($theme, ['title' => $T('Aucune date ne vous convient ?', 'None of these dates work for you?'), 'text' => $T('De nombreuses formations existent aussi en e-learning ou peuvent être organisées pour un groupe de votre entreprise, aux dates de votre choix.', 'Many courses are also available as e-learning or can be organized for a group from your company, on dates you choose.'),
                    'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'accent'], [$T('Formation en entreprise', 'In-house training'), $business, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'business', 'slug' => $u('entreprises', 'for-business'), 'title' => $T('Entreprises', 'For business'),
            'description' => $T('Formations intra-entreprise et sur mesure, plan de développement des compétences, accompagnement des managers et des équipes.', 'In-house and custom training, skills development plans, support for managers and teams.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Entreprises', 'For business'), 'title' => $T('Faites grandir les compétences de vos équipes', 'Grow your teams’ skills'), 'intro' => $T('Des programmes conçus avec vous, dans vos locaux, chez nous ou à distance.', 'Programs designed with you, at your premises, at ours or online.')]),
                B::features($theme, ['eyebrow' => $T('Nos solutions', 'Our solutions'), 'title' => $T('Une offre pour chaque besoin', 'An option for every need'), 'items' => [
                    ['users', $T('Formation intra', 'In-house training'), $T('Une formation du catalogue réservée à vos collaborateurs, adaptée à votre contexte.', 'A catalog course reserved for your employees, adapted to your context.')],
                    ['pencil', $T('Programme sur mesure', 'Custom program'), $T('Un dispositif conçu de A à Z à partir de vos objectifs et de vos outils.', 'A program designed from scratch around your goals and your tools.')],
                    ['clipboard-check', $T('Plan de compétences', 'Skills plan'), $T('Diagnostic des besoins, priorités et calendrier de formation sur l’année.', 'Needs analysis, priorities and a yearly training schedule.')],
                    ['target', $T('Accompagnement des managers', 'Manager coaching'), $T('Cycles de management et coaching individuel des encadrants.', 'Management programs and one-to-one coaching for team leaders.')],
                    ['laptop', $T('Licences e-learning', 'E-learning licenses'), $T('Accès à notre plateforme pour vos équipes, avec suivi des progrès.', 'Access to our platform for your teams, with progress tracking.')],
                    ['file-text', $T('Gestion administrative', 'Admin handled'), $T('Conventions, feuilles de présence, attestations et bilans fournis.', 'Agreements, attendance sheets, certificates and reports provided.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Notre démarche', 'Our approach'), 'title' => $T('Un projet de formation en quatre temps', 'A training project in four stages'), 'bg' => 'light', 'items' => [
                    [$T('Diagnostic', 'Diagnosis'), $T('Entretiens avec la direction et les managers pour cerner les besoins réels.', 'Interviews with management to pinpoint real needs.')],
                    [$T('Proposition', 'Proposal'), $T('Programme, formateurs, calendrier et budget détaillés sous dix jours.', 'Syllabus, trainers, schedule and budget detailed within ten days.')],
                    [$T('Déploiement', 'Delivery'), $T('Sessions adaptées à vos horaires et à vos contraintes de production.', 'Sessions fitted around your hours and operational constraints.')],
                    [$T('Évaluation', 'Evaluation'), $T('Bilan à chaud, mesure à trois mois et recommandations.', 'Immediate feedback, three-month follow-up and recommendations.')],
                ]]),
                B::stats($theme, ['bg' => 'primary', 'title' => $T('Nos résultats en entreprise', 'Our results with businesses'), 'items' => [
                    [150, '+', $T('entreprises clientes', 'client companies')],
                    [420, '', $T('sessions intra par an', 'in-house sessions a year')],
                    [9, '/10', $T('note moyenne des participants', 'average participant rating')],
                    [10, ' j', $T('pour recevoir une proposition', 'to receive a proposal')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, ['title' => $T('Un projet de formation pour vos équipes ?', 'Planning training for your teams?'), 'text' => $T('Décrivez-nous votre besoin : un responsable entreprises vous rappelle sous 48 heures.', 'Tell us what you need: a business account manager will call you back within 48 hours.'),
                    'buttons' => [[$T('Demander une proposition', 'Request a proposal'), $contact, 'accent'], [$T('Les financements', 'Funding options'), $funding, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'funding', 'slug' => $u('financement', 'funding'), 'title' => $T('Tarifs et financement', 'Pricing and funding'),
            'description' => $T('Tarifs des formations, paiement en plusieurs fois, prise en charge par l’employeur et dispositifs de financement de la formation.', 'Course pricing, installment plans, employer sponsorship and training funding schemes.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Financement', 'Funding'), 'title' => $T('Tarifs et solutions de financement', 'Pricing and funding options'), 'intro' => $T('Des prix affichés, et un conseiller pour trouver la prise en charge la plus adaptée.', 'Clear prices, and an adviser to find the best way to cover the cost.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Exemples de tarifs', 'Sample pricing'), 'title' => $T('Trois façons de vous former', 'Three ways to train'), 'intro' => $T('Prix par personne, supports et évaluation inclus.', 'Price per person, materials and assessment included.'), 'items' => [
                    [$T('Formation courte', 'Short course'), $T('290', '310'), $cur, $T('dès', 'from'), $T('De 1 à 5 jours pour une compétence précise.', '1 to 5 days for a specific skill.'), [$T('Présentiel ou classe virtuelle', 'On site or virtual classroom'), $T('Supports et exercices', 'Materials and exercises'), $T('Attestation de fin de formation', 'Certificate of completion'), '-'.$T('Certification officielle', 'Official certification')], $T('Voir les dates', 'See dates')],
                    [$T('Parcours certifiant', 'Certification track'), $T('1 890', '1,990'), $cur, $T('dès', 'from'), $T('De 3 à 6 mois pour changer de métier ou évoluer.', '3 to 6 months to change careers or move up.'), [$T('Formateur référent', 'Lead trainer'), $T('Projet professionnel encadré', 'Supervised project'), $T('Passage de la certification', 'Certification exam'), $T('Paiement en 10 fois sans frais', '10 interest-free installments')], $T('Être conseillé', 'Get advice'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Abonnement e-learning', 'E-learning subscription'), $T('29', '32'), $cur, $T('/ mois', '/ month'), $T('Tout le catalogue en ligne, à votre rythme.', 'The whole online catalog, at your own pace.'), [$T('Plus de 200 modules', 'Over 200 modules'), $T('Quiz et badges de progression', 'Quizzes and progress badges'), $T('Résiliable à tout moment', 'Cancel anytime'), '-'.$T('Formateur en direct', 'Live trainer')], $T('S’abonner', 'Subscribe')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Prise en charge', 'Cost coverage'), 'title' => $T('Qui peut financer votre formation ?', 'Who can pay for your training?'), 'bg' => 'white', 'groups' => [
                    [$T('Vous êtes salarié', 'You are employed'), [
                        ['building-2', $T('Plan de formation de votre employeur', 'Your employer’s training plan')],
                        ['wallet', $T('Compte ou budget individuel de formation, selon votre pays', 'Individual training account or budget, depending on your country')],
                        ['handshake', $T('Fonds de formation de votre branche professionnelle', 'Your industry’s training fund')],
                    ]],
                    [$T('Vous êtes indépendant ou en recherche d’emploi', 'You are self-employed or job seeking'), [
                        ['landmark', $T('Aides des services publics de l’emploi', 'Public employment service grants')],
                        ['hand-coins', $T('Aides régionales ou bourses de reconversion', 'Regional grants or career-change scholarships')],
                        ['credit-card', $T('Financement personnel, paiement en plusieurs fois', 'Self-funding, payment in installments')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur le financement', 'Your funding questions'), 'bg' => 'light', 'items' => [
                    [$T('Vous occupez-vous des démarches de prise en charge ?', 'Do you handle the funding paperwork?'), $T('Nous vous fournissons le devis, le programme et la convention, et nous vous guidons pour chaque dossier de financement.', 'We provide the quote, syllabus and agreement, and guide you through each funding application.')],
                    [$T('Comment puis-je payer ?', 'How can I pay?'), $T('Par carte bancaire, virement ou paiement mobile, en une fois ou en plusieurs mensualités sans frais pour les parcours certifiants.', 'By card, bank transfer or mobile payment, in full or in interest-free monthly installments for certification tracks.')],
                    [$T('Puis-je annuler mon inscription ?', 'Can I cancel my enrollment?'), $T('Oui, sans frais jusqu’à quatorze jours avant le début de la session. Les conditions détaillées figurent dans nos conditions générales.', 'Yes, free of charge up to fourteen days before the session starts. Full terms are set out in our terms and conditions.')],
                    [$T('Les tarifs entreprises sont-ils différents ?', 'Are business rates different?'), $T('Les formations intra sont facturées par groupe, avec un devis personnalisé selon le nombre de participants et la durée.', 'In-house training is billed per group, with a custom quote based on the number of participants and the duration.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('le-centre', 'about-us'), 'title' => $T('Le centre', 'About us'),
            'description' => $T('Notre histoire, notre démarche qualité auditée, nos engagements d’accessibilité et l’équipe de formateurs praticiens et de conseillers du centre.', 'Our story, our audited quality process, our accessibility commitments and the center’s team of practitioner trainers and advisers.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Le centre', 'About us'), 'title' => $T('Former pour faire progresser', 'Training that moves people forward'), 'intro' => $T('Un centre indépendant, des formateurs praticiens et une exigence de qualité contrôlée.', 'An independent center, practitioner trainers and an audited quality standard.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Né des besoins des entreprises de la région', 'Born from the needs of local employers'),
                    'html' => '<p>'.e($T('Le centre a été créé par des professionnels du recrutement et de la formation qui constataient le même écart : des postes à pourvoir, et des candidats à qui il manquait quelques compétences clés. Depuis, nous construisons nos programmes avec les employeurs, pour former à des métiers qui recrutent.', 'The center was founded by recruitment and training professionals who kept seeing the same gap: open positions, and candidates missing a few key skills. Since then, we have built our programs with employers, to train people for jobs that are hiring.')).'</p><p>'.e($T('Organisme de formation enregistré sous le numéro [N° de déclaration d’activité].', 'Training provider registered under number [Registration no.].')).'</p>',
                    'photo' => ['formation-centre', 'building-2', $T('Hall d’accueil lumineux d’un centre de formation avec des stagiaires qui discutent', 'Bright reception area of a training center with learners chatting')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Qualité', 'Quality'), 'title' => $T('Nos engagements qualité', 'Our quality commitments'), 'bg' => 'light', 'items' => [
                    ['shield-check', $T('Certifié', 'Certified'), $T('Démarche qualité auditée chaque année par un organisme indépendant [Nom de la certification].', 'Quality process audited every year by an independent body [Certification name].')],
                    ['chart-line', $T('Mesuré', 'Measured'), $T('Satisfaction, réussite et insertion publiées pour chaque parcours.', 'Satisfaction, pass and employment rates published for every track.')],
                    ['accessibility', $T('Accessible', 'Accessible'), $T('Un référent handicap et des aménagements étudiés au cas par cas.', 'A disability officer and adjustments reviewed case by case.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Formateurs et conseillers', 'Trainers and advisers'), 'items' => [
                    ['[Prénom Nom]', $T('Directrice du centre', 'Center director'), 'formation-equipe-1'],
                    ['[Prénom Nom]', $T('Responsable pédagogique', 'Head of learning'), 'formation-equipe-2'],
                    ['[Prénom Nom]', $T('Formateur numérique et data', 'Digital and data trainer'), 'formation-equipe-3'],
                    ['[Prénom Nom]', $T('Conseillère formation et financement', 'Training and funding adviser'), 'formation-equipe-4'],
                ]]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Une bonne formation ne se juge pas le dernier jour, mais trois mois plus tard, à ce que l’on fait mieux au travail.', 'Good training isn’t judged on the last day, but three months later, by what you do better at work.'), 'source' => $T('— [Prénom Nom], directrice du centre', '— [First Last], center director')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites', 'news'), 'title' => $T('Actualités', 'News'),
            'description' => $T('Conseils pour se former et réussir sa reconversion, nouvelles formations du catalogue, solutions de financement et parcours d’anciens stagiaires.', 'Tips on training and making a career change, new courses in the catalog, funding options and stories from former learners.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Se former, évoluer, se reconvertir', 'Learn, grow, change careers'), 'intro' => $T('Conseils pratiques, nouveautés du catalogue et parcours de nos anciens stagiaires.', 'Practical tips, new courses and stories from our former learners.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire l’article', 'Read the article')]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('La vie du centre', 'Life at the center'), 'bg' => 'light', 'photos' => [
                    ['formation-galerie-1', 'presentation', $T('Groupe de stagiaires travaillant en atelier autour d’une table', 'Group of learners working together around a table')],
                    ['formation-galerie-2', 'laptop', $T('Salle informatique équipée d’ordinateurs pendant un cours', 'Computer lab during a class')],
                    ['formation-galerie-3', 'award', $T('Remise des certificats en fin de parcours', 'Certificate ceremony at the end of a track')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez un conseiller formation : orientation, devis entreprise, financement. Réponse sous 48 heures ouvrées.', 'Contact a training adviser: guidance, business quotes, funding. Reply within two business days.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons de votre projet', 'Let’s talk about your goals'), 'intro' => $T('Un conseiller vous répond sous 48 heures ouvrées.', 'An adviser will reply within two business days.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['user-round', $T('Particuliers', 'Individuals'), $T('Choisir une formation, vérifier les prérequis et étudier les financements.', 'Choose a course, check prerequisites and look at funding.')],
                    ['building-2', $T('Entreprises', 'Businesses'), $T('Demander une formation intra, un programme sur mesure ou un devis.', 'Request in-house training, a custom program or a quote.')],
                    ['accessibility', $T('Référent handicap', 'Disability officer'), $T('Étudier ensemble les aménagements de votre formation.', 'Discuss adjustments to your training together.')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Accueil sur place du lundi au vendredi, et le samedi matin pendant les sessions.', 'On-site reception Monday to Friday, and Saturday mornings during sessions.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h 30 – 18 h 30 ; samedi, 9 h – 12 h', 'Monday – Friday, 8:30 am – 6:30 pm; Saturday, 9 am – 12 pm')],
                    ],
                    'social' => ['facebook', 'instagram', 'youtube'],
                    'subject' => true,
                    'button' => $T('Envoyer ma demande', 'Send my request'),
                    'bg' => 'light',
                ]),
            ]];

        $blog = [
            'base' => $u('actualites', 'news'),
            'title' => $T('Actualités', 'News'),
            'categories' => ['carrieres' => $T('Métiers et carrières', 'Careers'), 'financement' => $T('Financement', 'Funding'), 'apprendre' => $T('Bien apprendre', 'Learning tips')],
            'posts' => [
                ['key' => 'reconversion', 'slug' => $u('reussir-sa-reconversion', 'making-a-career-change'), 'category' => 'carrieres', 'tags' => [$T('Reconversion', 'Career change')],
                    'title' => $T('Réussir sa reconversion professionnelle en cinq étapes', 'Five steps to a successful career change'),
                    'excerpt' => $T('Faire le point, tester un métier, choisir une formation : la méthode de nos conseillers.', 'Take stock, try out a job, choose a course: our advisers’ method.'),
                    'photo' => ['formation-blog-reconversion', 'compass', $T('Adulte prenant des notes devant un ordinateur, en train de planifier son projet', 'Adult taking notes at a computer, planning a project')],
                    'heading' => $T('Les étapes clés', 'The key steps'),
                    'body' => [
                        $T('Une reconversion réussie commence par un bilan honnête : ce que vous aimez faire, ce que vous savez déjà faire, et les métiers qui recrutent près de chez vous.', 'A successful career change starts with an honest review: what you enjoy, what you can already do, and which jobs are hiring near you.'),
                        $T('Avant de vous engager, testez le métier : rencontrez des professionnels, faites une immersion de quelques jours ou suivez un atelier découverte.', 'Before committing, try the job out: meet professionals, spend a few days shadowing or join a taster workshop.'),
                        $T('Choisissez ensuite une formation certifiante dont les résultats sont publiés, et préparez votre financement avec un conseiller.', 'Then choose a certification course with published results, and plan your funding with an adviser.'),
                    ]],
                ['key' => 'financer', 'slug' => $u('financer-sa-formation', 'how-to-fund-your-training'), 'category' => 'financement', 'tags' => [$T('Aides', 'Grants')],
                    'title' => $T('Financer sa formation : les questions à se poser', 'Funding your training: the questions to ask'),
                    'excerpt' => $T('Employeur, fonds professionnels, aides publiques, paiement échelonné : faites le tour des options.', 'Employer, industry funds, public grants, installments: review your options.'),
                    'photo' => ['formation-blog-financement', 'wallet', $T('Conseillère expliquant un devis de formation à un adulte', 'Adviser explaining a training quote to an adult')],
                    'quote' => $T('« La plupart de nos stagiaires ne paient pas seuls leur formation. »', '“Most of our learners don’t pay for their training alone.”'),
                    'body' => [
                        $T('Les dispositifs varient selon les pays et votre situation : salarié, indépendant ou demandeur d’emploi. Commencez par interroger votre employeur et votre branche professionnelle.', 'Schemes vary by country and by situation: employee, self-employed or job seeker. Start by asking your employer and your industry body.'),
                        $T('Les services publics de l’emploi et certaines collectivités proposent des aides pour les métiers en tension.', 'Public employment services and some local authorities offer grants for jobs in short supply.'),
                        $T('Enfin, le paiement en plusieurs fois permet d’étaler le reste à charge. Nos conseillers montent le dossier avec vous.', 'Finally, installment plans spread out what remains to pay. Our advisers prepare the application with you.'),
                    ]],
                ['key' => 'distance', 'slug' => $u('apprendre-a-distance', 'learning-online-effectively'), 'category' => 'apprendre', 'tags' => [$T('À distance', 'Online')],
                    'title' => $T('Six conseils pour bien apprendre à distance', 'Six tips for learning online effectively'),
                    'excerpt' => $T('Organisation, concentration, entraide : nos formateurs partagent leurs astuces.', 'Organization, focus, peer support: our trainers share their tips.'),
                    'photo' => ['formation-blog-distance', 'headphones', $T('Personne suivant un cours en ligne à son bureau, carnet ouvert', 'Person taking an online course at a desk, notebook open')],
                    'heading' => $T('Nos conseils', 'Our advice'),
                    'body' => [
                        $T('Bloquez des créneaux fixes dans votre agenda, comme pour un cours en salle, et installez-vous dans un endroit calme.', 'Block fixed slots in your calendar, as you would for a classroom course, and settle in a quiet place.'),
                        $T('Coupez les notifications, prenez des notes à la main et posez vos questions en direct : le formateur est là pour ça.', 'Turn off notifications, take notes by hand and ask your questions live: that’s what the trainer is there for.'),
                        $T('Rejoignez le groupe d’entraide de votre promotion : on apprend beaucoup en expliquant aux autres.', 'Join your cohort’s study group: you learn a lot by explaining things to others.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Formations', 'Courses'), 'url' => $courses],
                    ['label' => $T('Sessions', 'Sessions'), 'url' => $sessions],
                    ['label' => $T('Entreprises', 'For business'), 'url' => $business],
                    ['label' => $T('Financement', 'Funding'), 'url' => $funding],
                    ['label' => $T('Le centre', 'About us'), 'url' => $about],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                ],
                'cta_label' => $T('Être conseillé', 'Get advice'),
                'cta_url' => $contact,
                'footer_text' => $T('Centre de formation professionnelle et continue : formations courtes, parcours certifiants et programmes sur mesure, en présentiel et à distance.', 'Professional and continuing education center: short courses, certification tracks and custom programs, on site and online.'),
                'footer_links' => [
                    ['label' => $T('Nos formations', 'Our courses'), 'url' => $courses],
                    ['label' => $T('Calendrier des sessions', 'Session calendar'), 'url' => $sessions],
                    ['label' => $T('Tarifs et financement', 'Pricing and funding'), 'url' => $funding],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
