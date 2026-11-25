<?php

/*
 * Pack « ONG et associations » : 8 pages, en français et en anglais.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'organisation). Les liens internes
 * sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'ONG et associations', 'en' => 'NGOs and nonprofits'],
        'icon' => 'hand-heart',
        'keywords' => ['ong', 'association', 'humanitaire', 'développement', 'dons', 'bénévolat', 'ngo', 'nonprofit', 'charity', 'foundation', 'fondation'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $about = $u('/a-propos', '/about');
        $programs = $u('/programmes', '/programs');
        $projects = $u('/projets', '/projects');
        $involved = $u('/s-impliquer', '/get-involved');
        $donate = $u('/faire-un-don', '/donate');
        $news = $u('/actualites', '/news');
        $contact = '/contact';

        $programItems = [
            ['graduation-cap', $T('Éducation', 'Education'), $T('Scolarisation des enfants vulnérables, fournitures, soutien scolaire et formation des enseignants.', 'Schooling for vulnerable children, school supplies, tutoring and teacher training.')],
            ['heart-pulse', $T('Santé', 'Health'), $T('Consultations mobiles, prévention, accompagnement des mères et des nouveau-nés.', 'Mobile clinics, prevention, support for mothers and newborns.')],
            ['droplet', $T('Eau et hygiène', 'Water and hygiene'), $T('Points d’eau potable, latrines et sensibilisation à l’hygiène dans les villages.', 'Safe water points, latrines and hygiene awareness in villages.')],
            ['sprout', $T('Moyens de subsistance', 'Livelihoods'), $T('Formation professionnelle, agriculture durable et petits crédits pour les familles.', 'Vocational training, sustainable farming and small loans for families.')],
            ['users', $T('Femmes et jeunesse', 'Women and youth'), $T('Groupes d’entraide, leadership des femmes et accompagnement des jeunes.', 'Self-help groups, women’s leadership and youth mentoring.')],
            ['shield-check', $T('Protection', 'Protection'), $T('Accueil et suivi des enfants et des personnes vulnérables, avec les autorités locales.', 'Care and follow-up for children and vulnerable people, with local authorities.')],
        ];
        $stats = ['title' => $T('Notre impact en chiffres', 'Our impact in numbers'), 'items' => [
            [12500, '+', $T('personnes accompagnées', 'people supported')],
            [48, '', $T('villages partenaires', 'partner villages')],
            [320, '', $T('bénévoles engagés', 'committed volunteers')],
            [15, '', $T('années d’action', 'years of action')],
        ]];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Ils racontent le changement', 'They tell the story of change'), 'items' => [
            [$T('Grâce au programme, mes trois enfants vont à l’école et j’ai pu lancer un petit commerce.', 'Thanks to the program, my three children go to school and I was able to start a small business.'), '[Prénom Nom]', $T('Mère de famille bénéficiaire', 'Mother and beneficiary')],
            [$T('Une équipe sérieuse et transparente : chaque rapport montre exactement où vont les fonds.', 'A serious and transparent team: every report shows exactly where the funds go.'), '[Prénom Nom]', $T('Partenaire financier', 'Funding partner')],
            [$T('Être bénévole ici m’a appris autant que j’ai donné. On voit les résultats sur le terrain.', 'Volunteering here taught me as much as I gave. You can see the results on the ground.'), '[Prénom Nom]', $T('Bénévole depuis 3 ans', 'Volunteer for 3 years')],
        ]];
        $cta = ['title' => $T('Ensemble, allons plus loin', 'Together, let’s go further'), 'text' => $T('Votre don, votre temps ou votre partenariat change concrètement des vies. Rejoignez-nous.', 'Your donation, your time or your partnership truly changes lives. Join us.'),
            'buttons' => [[$T('Faire un don', 'Donate'), $donate, 'accent'], [$T('Devenir bénévole', 'Volunteer'), $involved, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Nous agissons pour l’éducation, la santé et la dignité des familles vulnérables. Découvrez nos programmes et soutenez notre action.', 'We work for the education, health and dignity of vulnerable families. Discover our programs and support our work.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Ensemble pour la dignité', 'Together for dignity'),
                    'title' => $T('Chaque enfant mérite un avenir digne', 'Every child deserves a future with dignity'),
                    'text' => $T('Depuis 15 ans, nous accompagnons les familles vulnérables avec des programmes d’éducation, de santé et d’accès à l’eau, main dans la main avec les communautés.', 'For 15 years we have supported vulnerable families with education, health and water programs, hand in hand with communities.'),
                    'buttons' => [[$T('Faire un don', 'Donate'), $donate], [$T('Découvrir nos actions', 'See our work'), $programs]],
                    'photo' => ['ong-accueil', 'hand-heart', $T('Enfants souriants dans une salle de classe', 'Smiling children in a classroom')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Ce que nous faisons', 'What we do'), 'title' => $T('Trois priorités, un même engagement', 'Three priorities, one commitment'), 'items' => array_slice($programItems, 0, 3)]),
                B::split($theme, [
                    'eyebrow' => $T('Qui sommes-nous', 'Who we are'),
                    'title' => $T('Une organisation née sur le terrain', 'An organization born in the field'),
                    'html' => '<p>'.e($T('Fondée par des acteurs locaux, notre organisation travaille directement avec les communautés pour construire des solutions durables. Nous croyons que le changement vient de l’intérieur, avec un appui fiable et transparent.', 'Founded by local leaders, our organization works directly with communities to build lasting solutions. We believe change comes from within, with reliable and transparent support.')).'</p>',
                    'bullets' => [$T('Équipes locales et formées', 'Local, trained teams'), $T('Rapports financiers publiés chaque année', 'Financial reports published every year'), $T('Partenariats avec les autorités et les écoles', 'Partnerships with authorities and schools')],
                    'buttons' => [[$T('Notre histoire', 'Our story'), $about, 'outline']],
                    'photo' => ['ong-equipe-terrain', 'users', $T('Équipe de terrain avec des habitants', 'Field team with residents')],
                    'reverse' => true,
                ]),
                B::stats($theme, $stats),
                B::cards($theme, ['eyebrow' => $T('Sur le terrain', 'In the field'), 'title' => $T('Projets récents', 'Recent projects'), 'more' => $T('Voir le projet', 'View project'), 'items' => [
                    ['ong-projet-ecole', 'school', $T('Une école pour Kanyaruchinya', 'A school for Kanyaruchinya'), $T('Six salles de classe construites et équipées pour 400 élèves.', 'Six classrooms built and equipped for 400 pupils.'), $projects],
                    ['ong-projet-eau', 'droplet', $T('L’eau au cœur du village', 'Water at the heart of the village'), $T('Trois forages réhabilités, 5 000 habitants desservis.', 'Three boreholes restored, serving 5,000 residents.'), $projects],
                    ['ong-projet-femmes', 'sprout', $T('Femmes entrepreneures', 'Women entrepreneurs'), $T('120 femmes formées et accompagnées dans leur activité.', '120 women trained and supported in their business.'), $projects],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('a-propos', 'about'), 'title' => $T('À propos', 'About us'),
            'description' => $T('Notre histoire, notre mission, nos valeurs et l’équipe qui porte notre action.', 'Our story, mission, values and the team behind our work.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('À propos', 'About us'), 'title' => $T('Notre histoire et notre mission', 'Our story and our mission'), 'intro' => $T('Une organisation locale, des résultats mesurables, une gestion transparente.', 'A local organization, measurable results, transparent management.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Tout a commencé par une salle de classe', 'It all started with one classroom'),
                    'html' => '<p>'.e($T('En 2010, un groupe d’enseignants et de parents a ouvert une classe pour les enfants déplacés de leur quartier. Aujourd’hui, notre organisation agit dans 48 villages avec une équipe de professionnels et des centaines de bénévoles.', 'In 2010, a group of teachers and parents opened a classroom for displaced children in their neighborhood. Today our organization works in 48 villages with a team of professionals and hundreds of volunteers.')).'</p><p>'.e($T('Notre force : rester proches des familles, écouter leurs besoins et construire avec elles.', 'Our strength: staying close to families, listening to their needs and building with them.')).'</p>',
                    'photo' => ['ong-histoire', 'school', $T('Première salle de classe de l’organisation', 'The organization’s first classroom')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce qui guide chacune de nos actions', 'What guides everything we do'), 'bg' => 'light', 'items' => [
                    ['heart-handshake', $T('Dignité', 'Dignity'), $T('Chaque personne est actrice de son propre changement.', 'Every person drives their own change.')],
                    ['eye', $T('Transparence', 'Transparency'), $T('Nos comptes et nos résultats sont publics.', 'Our accounts and results are public.')],
                    ['users-round', $T('Proximité', 'Closeness'), $T('Des équipes locales, présentes chaque jour.', 'Local teams, present every day.')],
                ]]),
                B::quote($theme, ['text' => $T('Notre mission : permettre à chaque famille vulnérable d’accéder à l’éducation, à la santé et à une vie digne.', 'Our mission: enabling every vulnerable family to access education, health and a dignified life.'), 'source' => $T('— Notre mission', '— Our mission')]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Les personnes derrière l’action', 'The people behind the work'), 'shape' => 'rounded', 'items' => [
                    ['[Prénom Nom]', $T('Directrice exécutive', 'Executive director'), 'ong-equipe-1'],
                    ['[Prénom Nom]', $T('Responsable des programmes', 'Head of programs'), 'ong-equipe-2'],
                    ['[Prénom Nom]', $T('Responsable financier', 'Finance officer'), 'ong-equipe-3'],
                    ['[Prénom Nom]', $T('Coordinatrice des bénévoles', 'Volunteer coordinator'), 'ong-equipe-4'],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'programs', 'slug' => $u('programmes', 'programs'), 'title' => $T('Nos programmes', 'Our programs'),
            'description' => $T('Éducation, santé, eau, moyens de subsistance, femmes et jeunesse, protection : nos programmes en détail.', 'Education, health, water, livelihoods, women and youth, protection: our programs in detail.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nos programmes', 'Our programs'), 'title' => $T('Des réponses concrètes aux besoins essentiels', 'Practical answers to essential needs'), 'intro' => $T('Six programmes complémentaires, conçus avec les communautés.', 'Six complementary programs, designed with communities.')]),
                B::features($theme, ['items' => $programItems, 'cols' => 3]),
                B::split($theme, [
                    'eyebrow' => $T('Notre approche', 'Our approach'),
                    'title' => $T('Écouter, agir, mesurer', 'Listen, act, measure'),
                    'html' => '<p>'.e($T('Chaque programme commence par un diagnostic avec les habitants. Nous fixons ensemble des objectifs clairs, suivons les résultats et publions un bilan chaque année.', 'Every program starts with an assessment with residents. Together we set clear goals, track results and publish a yearly report.')).'</p>',
                    'bullets' => [$T('Diagnostic participatif', 'Participatory assessment'), $T('Indicateurs suivis tous les trimestres', 'Indicators tracked every quarter'), $T('Passage de relais aux comités locaux', 'Handover to local committees')],
                    'photo' => ['ong-approche', 'clipboard-check', $T('Réunion communautaire', 'Community meeting')],
                    'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('Comment nous travaillons', 'How we work'), 'title' => $T('Du besoin au résultat', 'From need to result'), 'items' => [
                    [$T('Écoute', 'Listening'), $T('Rencontres avec les familles et les leaders locaux.', 'Meetings with families and local leaders.')],
                    [$T('Conception', 'Design'), $T('Un plan d’action adapté et chiffré.', 'A tailored, costed action plan.')],
                    [$T('Mise en œuvre', 'Delivery'), $T('Des équipes locales formées et suivies.', 'Trained and supervised local teams.')],
                    [$T('Évaluation', 'Evaluation'), $T('Des résultats mesurés et partagés.', 'Results measured and shared.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'projects', 'slug' => $u('projets', 'projects'), 'title' => $T('Nos projets', 'Our projects'),
            'description' => $T('Découvrez nos réalisations sur le terrain : écoles, points d’eau, centres de santé et formations.', 'Discover our work on the ground: schools, water points, health centers and training.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Réalisations', 'Achievements'), 'title' => $T('Nos projets sur le terrain', 'Our projects in the field'), 'intro' => $T('Chaque projet est suivi, évalué et documenté.', 'Every project is monitored, evaluated and documented.')]),
                B::cards($theme, ['items' => [
                    ['ong-projet-ecole', 'school', $T('Une école pour Kanyaruchinya', 'A school for Kanyaruchinya'), $T('Six salles de classe construites et équipées pour 400 élèves.', 'Six classrooms built and equipped for 400 pupils.')],
                    ['ong-projet-eau', 'droplet', $T('L’eau au cœur du village', 'Water at the heart of the village'), $T('Trois forages réhabilités, 5 000 habitants desservis.', 'Three boreholes restored, serving 5,000 residents.')],
                    ['ong-projet-femmes', 'sprout', $T('Femmes entrepreneures', 'Women entrepreneurs'), $T('120 femmes formées et accompagnées dans leur activité.', '120 women trained and supported in their business.')],
                    ['ong-projet-sante', 'stethoscope', $T('Cliniques mobiles', 'Mobile clinics'), $T('2 300 consultations par an dans les zones éloignées.', '2,300 consultations a year in remote areas.')],
                    ['ong-projet-jeunes', 'graduation-cap', $T('Ateliers pour les jeunes', 'Youth workshops'), $T('Formations en couture, menuiserie et informatique.', 'Training in sewing, carpentry and computing.')],
                    ['ong-projet-agri', 'wheat', $T('Jardins communautaires', 'Community gardens'), $T('35 jardins pour améliorer l’alimentation des familles.', '35 gardens to improve family nutrition.')],
                ]]),
                B::stats($theme, $stats + ['bg' => 'primary']),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('Moments partagés', 'Shared moments'), 'photos' => [
                    ['ong-galerie-1', 'camera', $T('Distribution de kits scolaires', 'School kit distribution')],
                    ['ong-galerie-2', 'camera', $T('Formation des enseignants', 'Teacher training')],
                    ['ong-galerie-3', 'camera', $T('Inauguration d’un point d’eau', 'Water point opening')],
                    ['ong-galerie-4', 'camera', $T('Atelier de couture', 'Sewing workshop')],
                    ['ong-galerie-5', 'camera', $T('Consultation mobile', 'Mobile consultation')],
                    ['ong-galerie-6', 'camera', $T('Fête de fin d’année', 'End-of-year celebration')],
                ]]),
            ]];

        $pages[] = ['key' => 'involved', 'slug' => $u('s-impliquer', 'get-involved'), 'title' => $T('S’impliquer', 'Get involved'),
            'description' => $T('Bénévolat, partenariat, mécénat : toutes les façons de rejoindre notre action.', 'Volunteering, partnership, sponsorship: all the ways to join our work.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('S’impliquer', 'Get involved'), 'title' => $T('Rejoignez le mouvement', 'Join the movement'), 'intro' => $T('Chacun peut agir, selon son temps et ses moyens.', 'Everyone can help, according to their time and means.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['hand-helping', $T('Devenir bénévole', 'Volunteer'), $T('Soutien scolaire, logistique, communication : donnez quelques heures par mois.', 'Tutoring, logistics, communication: give a few hours a month.')],
                    ['handshake', $T('Devenir partenaire', 'Become a partner'), $T('Entreprises, fondations, institutions : construisons un projet ensemble.', 'Companies, foundations, institutions: let’s build a project together.')],
                    ['megaphone', $T('Faire connaître', 'Spread the word'), $T('Partagez nos actions autour de vous et sur les réseaux sociaux.', 'Share our work with people around you and on social media.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Bénévolat', 'Volunteering'),
                    'title' => $T('Vos compétences ont de la valeur', 'Your skills matter'),
                    'html' => '<p>'.e($T('Nous accueillons des bénévoles de tous horizons. Après un entretien, vous suivez une courte formation et rejoignez une équipe encadrée.', 'We welcome volunteers from all backgrounds. After an interview, you take a short training and join a supervised team.')).'</p>',
                    'bullets' => [$T('Missions courtes ou régulières', 'Short or regular assignments'), $T('Formation et encadrement', 'Training and supervision'), $T('Attestation de bénévolat', 'Volunteering certificate')],
                    'buttons' => [[$T('Proposer mon aide', 'Offer my help'), $contact]],
                    'photo' => ['ong-benevoles', 'hand-helping', $T('Bénévoles en action', 'Volunteers in action')],
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'intro' => $T('Vous ne trouvez pas votre réponse ? Écrivez-nous.', 'Can’t find your answer? Write to us.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Faut-il des compétences particulières pour être bénévole ?', 'Do I need special skills to volunteer?'), $T('Non. Nous vous proposons une mission adaptée à votre profil et une formation au démarrage.', 'No. We offer an assignment suited to your profile and training to get started.')],
                    [$T('Combien de temps dois-je m’engager ?', 'How much time do I need to commit?'), $T('Quelques heures par mois suffisent. Vous choisissez votre rythme.', 'A few hours a month is enough. You choose your pace.')],
                    [$T('Mon entreprise peut-elle devenir partenaire ?', 'Can my company become a partner?'), $T('Oui : mécénat financier, de compétences ou en nature. Contactez-nous pour en parler.', 'Yes: financial, skills-based or in-kind sponsorship. Contact us to discuss it.')],
                    [$T('Comment sont utilisés les dons ?', 'How are donations used?'), $T('La grande majorité finance directement les programmes. Notre rapport annuel détaille chaque dépense.', 'Most of it directly funds programs. Our annual report details every expense.')],
                ]]),
            ]];

        $pages[] = ['key' => 'donate', 'slug' => $u('faire-un-don', 'donate'), 'title' => $T('Faire un don', 'Donate'),
            'description' => $T('Soutenez nos programmes par un don ponctuel ou mensuel. Chaque contribution compte.', 'Support our programs with a one-time or monthly donation. Every contribution counts.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Faire un don', 'Donate'), 'title' => $T('Votre don change des vies', 'Your gift changes lives'), 'intro' => $T('100 % sécurisé. Un reçu vous est envoyé pour chaque don.', '100% secure. A receipt is sent for every donation.')]),
                B::donate($theme, [
                    'eyebrow' => $T('Campagne en cours', 'Current campaign'),
                    'title' => $T('Une rentrée scolaire pour 500 enfants', 'Back to school for 500 children'),
                    'text' => $T('Avec 25 $, un enfant reçoit son kit scolaire complet. Avec 100 $, une classe entière est équipée en manuels.', 'With $25, a child receives a full school kit. With $100, a whole class gets textbooks.'),
                    'goal' => $T('Objectif atteint', 'Goal reached'), 'percent' => 68,
                    'amounts' => ['10 $', '25 $', '50 $', '100 $'],
                    'button' => $T('Je fais un don', 'I donate'), 'url' => $contact,
                    'photo' => ['ong-don', 'gift', $T('Enfant recevant un kit scolaire', 'Child receiving a school kit')],
                    'bg' => 'white',
                ]),
                B::pillars($theme, ['title' => $T('Ce que permet votre don', 'What your gift makes possible'), 'bg' => 'light', 'items' => [
                    ['book-open', '25 $', $T('Un kit scolaire complet pour un enfant.', 'A full school kit for one child.')],
                    ['droplet', '150 $', $T('L’entretien d’un point d’eau pendant un an.', 'Maintaining a water point for a year.')],
                    ['stethoscope', '500 $', $T('Une journée de clinique mobile pour un village.', 'A mobile clinic day for a village.')],
                ]]),
                B::faq($theme, ['title' => $T('Vos questions sur les dons', 'Your questions about donations'), 'items' => [
                    [$T('Comment donner ?', 'How can I give?'), $T('Par Mobile Money, virement bancaire ou carte. Contactez-nous pour recevoir les coordonnées.', 'By Mobile Money, bank transfer or card. Contact us to receive the details.')],
                    [$T('Puis-je donner chaque mois ?', 'Can I give every month?'), $T('Oui, le don mensuel nous aide à planifier nos programmes dans la durée.', 'Yes, monthly giving helps us plan our programs over time.')],
                    [$T('Vais-je recevoir un reçu ?', 'Will I receive a receipt?'), $T('Oui, un reçu est envoyé pour chaque don.', 'Yes, a receipt is sent for every donation.')],
                ]]),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites', 'news'), 'title' => $T('Actualités', 'News'),
            'description' => $T('Les dernières nouvelles de nos programmes, nos événements et nos rapports.', 'The latest news from our programs, events and reports.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Les nouvelles du terrain', 'News from the field'), 'intro' => $T('Événements, résultats et histoires de nos programmes.', 'Events, results and stories from our programs.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['eyebrow' => $T('Agenda', 'Calendar'), 'title' => $T('Prochains événements', 'Upcoming events'), 'bg' => 'light', 'items' => [
                    [$T('Assemblée générale', 'General assembly'), $T('15 mars, 9 h', 'March 15, 9 am'), $T('Siège de l’organisation', 'Head office')],
                    [$T('Collecte solidaire', 'Charity drive'), $T('22 avril, toute la journée', 'April 22, all day'), $T('Place du marché', 'Market square')],
                    [$T('Formation des bénévoles', 'Volunteer training'), $T('6 mai, 14 h', 'May 6, 2 pm'), $T('Centre communautaire', 'Community center')],
                    [$T('Concert de soutien', 'Benefit concert'), $T('20 juin, 18 h', 'June 20, 6 pm'), $T('Salle des fêtes', 'Town hall')],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez-nous pour un don, un partenariat, du bénévolat ou toute question.', 'Contact us about a donation, a partnership, volunteering or any question.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons-en', 'Let’s talk'), 'intro' => $T('Notre équipe vous répond sous 48 heures.', 'Our team replies within 48 hours.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Écrivez-nous ou passez nous voir du lundi au vendredi.', 'Write to us or visit us Monday to Friday.'),
                    'items' => [
                        ['map-pin', $T('Avenue de la Paix, Goma, RDC', 'Avenue de la Paix, Goma, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h – 17 h', 'Monday – Friday, 8 am – 5 pm')],
                    ],
                    'social' => ['facebook', 'whatsapp', 'youtube'],
                    'subject' => true,
                    'map' => 'Goma, Nord-Kivu',
                    'bg' => 'white',
                ]),
            ]];

        $blog = [
            'base' => $u('actualites', 'news'),
            'title' => $T('Actualités', 'News'),
            'categories' => ['rapports' => $T('Rapports', 'Reports'), 'evenements' => $T('Événements', 'Events'), 'projets' => $T('Projets', 'Projects')],
            'posts' => [
                ['key' => 'rapport', 'slug' => $u('rapport-annuel', 'annual-report'), 'category' => 'rapports', 'tags' => [$T('Transparence', 'Transparency')],
                    'title' => $T('Rapport annuel : une année de résultats', 'Annual report: a year of results'),
                    'excerpt' => $T('Découvrez les chiffres clés et les histoires qui ont marqué l’année.', 'Discover the key figures and stories that marked the year.'),
                    'photo' => ['ong-actu-1', 'newspaper', $T('Enfants en classe', 'Children in class')],
                    'heading' => $T('Ce que vos dons ont permis', 'What your donations made possible'),
                    'quote' => $T('« Chaque famille accompagnée est une victoire partagée avec nos donateurs. »', '“Every family we support is a victory shared with our donors.”'),
                    'body' => [
                        $T('Notre rapport annuel présente le travail accompli avec les communautés : [nombre] enfants scolarisés, [nombre] familles accompagnées et [nombre] points d’eau entretenus.', 'Our annual report presents the work carried out with communities: [number] children in school, [number] families supported and [number] water points maintained.'),
                        $T('Les comptes ont été vérifiés par un cabinet indépendant. Le document complet détaille l’utilisation de chaque franc reçu, programme par programme.', 'The accounts were checked by an independent firm. The full document details how every franc received was used, program by program.'),
                        $T('Vous pouvez demander une copie du rapport à notre équipe, ou le consulter lors de notre prochaine assemblée générale.', 'You can ask our team for a copy of the report, or consult it at our next general assembly.'),
                    ]],
                ['key' => 'portes-ouvertes', 'slug' => $u('journee-portes-ouvertes', 'open-day'), 'category' => 'evenements', 'tags' => [$T('Bénévolat', 'Volunteering')],
                    'title' => $T('Journée portes ouvertes', 'Open day'),
                    'excerpt' => $T('Venez rencontrer nos équipes et nos bénévoles le premier samedi du mois.', 'Come and meet our teams and volunteers on the first Saturday of the month.'),
                    'photo' => ['ong-actu-2', 'calendar-days', $T('Enfants de la communauté', 'Children from the community')],
                    'heading' => $T('Au programme', 'On the program'),
                    'body' => [
                        $T('Chaque premier samedi du mois, nos locaux sont ouverts à toutes celles et ceux qui veulent découvrir notre action, de 9 h à 13 h.', 'Every first Saturday of the month, our offices are open to anyone who wants to discover our work, from 9 am to 1 pm.'),
                        $T('Présentation des programmes, rencontre avec les équipes de terrain et témoignages de familles accompagnées : une matinée pour comprendre comment nous travaillons.', 'Program presentations, meetings with field teams and testimonies from the families we support: a morning to understand how we work.'),
                        $T('C’est aussi le bon moment pour devenir bénévole : nous vous présentons les missions ouvertes et le parcours d’accueil.', 'It is also the right time to become a volunteer: we present the open missions and the welcome program.'),
                    ]],
                ['key' => 'eau', 'slug' => $u('nouveau-partenariat-eau', 'new-water-partnership'), 'category' => 'projets', 'tags' => [$T('Eau potable', 'Safe water')],
                    'title' => $T('Un nouveau partenariat pour l’eau', 'A new water partnership'),
                    'excerpt' => $T('Grâce à un nouveau partenaire, dix villages auront accès à l’eau potable.', 'Thanks to a new partner, ten villages will have access to safe water.'),
                    'photo' => ['ong-actu-3', 'handshake', $T('Point d’eau dans un village', 'Water point in a village')],
                    'heading' => $T('Les prochaines étapes', 'Next steps'),
                    'body' => [
                        $T('Nous avons signé un partenariat avec [nom du partenaire] pour construire et entretenir des points d’eau dans dix villages de la région.', 'We have signed a partnership with [partner name] to build and maintain water points in ten villages in the region.'),
                        $T('Les comités de village choisiront l’emplacement des forages et formeront les gestionnaires qui veilleront sur les installations.', 'Village committees will choose the location of the boreholes and train the managers who will look after the facilities.'),
                        $T('Les premiers travaux commencent en [mois]. Nous publierons ici l’avancement du chantier, village par village.', 'The first works start in [month]. We will publish progress here, village by village.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('À propos', 'About'), 'url' => $about],
                    ['label' => $T('Programmes', 'Programs'), 'url' => $programs],
                    ['label' => $T('Projets', 'Projects'), 'url' => $projects],
                    ['label' => $T('S’impliquer', 'Get involved'), 'url' => $involved],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Faire un don', 'Donate'),
                'cta_url' => $donate,
                'footer_text' => $T('Organisation à but non lucratif engagée pour l’éducation, la santé et la dignité des familles vulnérables.', 'Nonprofit organization committed to the education, health and dignity of vulnerable families.'),
                'footer_links' => [
                    ['label' => $T('Faire un don', 'Donate'), 'url' => $donate],
                    ['label' => $T('Devenir bénévole', 'Volunteer'), 'url' => $involved],
                    ['label' => $T('Nos programmes', 'Our programs'), 'url' => $programs],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
