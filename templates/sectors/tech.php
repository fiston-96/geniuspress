<?php

/*
 * Pack « Tech et agences numériques » : 8 pages, en français et en anglais.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'entreprise). Les liens internes
 * sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Tech et agences numériques', 'en' => 'Tech and digital agencies'],
        'icon' => 'cpu',
        'keywords' => ['tech', 'startup', 'logiciel', 'agence', 'numérique', 'développement', 'cloud', 'saas', 'it', 'software', 'digital', 'agency', 'web', 'application'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $solutions = $u('/solutions', '/solutions');
        $pricing = $u('/tarifs', '/pricing');
        $work = $u('/realisations', '/work');
        $about = $u('/a-propos', '/about');
        $careers = $u('/carrieres', '/careers');
        $blog = '/blog';
        $contact = '/contact';

        $serviceItems = [
            ['code', $T('Développement web et mobile', 'Web and mobile development'), $T('Sites, plateformes et applications Android et iOS, conçus pour les usages et les réseaux locaux.', 'Websites, platforms and Android and iOS apps, designed for local usage and networks.')],
            ['cloud', $T('Cloud et infrastructure', 'Cloud and infrastructure'), $T('Migration, hébergement, supervision et sauvegardes avec une disponibilité garantie.', 'Migration, hosting, monitoring and backups with guaranteed uptime.')],
            ['brain-circuit', $T('Données et IA', 'Data and AI'), $T('Tableaux de bord, automatisation et modèles d’IA appliqués à vos données métier.', 'Dashboards, automation and AI models applied to your business data.')],
            ['shield-check', $T('Cybersécurité', 'Cybersecurity'), $T('Audits, tests d’intrusion, conformité et sensibilisation de vos équipes.', 'Audits, penetration tests, compliance and awareness training for your teams.')],
            ['workflow', $T('Transformation digitale', 'Digital transformation'), $T('Cartographie des processus, choix des outils et accompagnement du changement.', 'Process mapping, tool selection and change management.')],
            ['headphones', $T('Support et maintenance', 'Support and maintenance'), $T('Une équipe disponible pour corriger, faire évoluer et superviser vos systèmes.', 'A team available to fix, evolve and monitor your systems.')],
        ];
        $stats = ['title' => $T('Des résultats qui se mesurent', 'Results you can measure'), 'items' => [
            [120, '+', $T('projets livrés', 'projects delivered')],
            [45, '', $T('ingénieurs et designers', 'engineers and designers')],
            [99, ' %', $T('de disponibilité moyenne', 'average uptime')],
            [8, '', $T('ans d’expérience', 'years of experience')],
        ]];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Ce que disent nos clients', 'What our clients say'), 'items' => [
            [$T('Livraison dans les délais, code propre et une équipe qui comprend vraiment notre métier.', 'Delivered on time, clean code and a team that truly understands our business.'), '[Prénom Nom]', $T('Directrice des systèmes d’information, une banque régionale', 'CIO, a regional bank')],
            [$T('Notre application mobile fonctionne même avec une connexion faible. Nos agents de terrain l’ont adoptée en une semaine.', 'Our mobile app works even on a weak connection. Our field agents adopted it within a week.'), '[Prénom Nom]', $T('Directeur des opérations, un opérateur logistique', 'Operations director, a logistics operator')],
            [$T('Des rapports clairs, des incidents traités vite : un vrai partenaire sur la durée.', 'Clear reports, incidents handled fast: a real long-term partner.'), '[Prénom Nom]', $T('Fondateur, une startup de paiement mobile', 'Founder, a mobile payments startup')],
        ]];
        $cta = ['title' => $T('Parlons de votre projet', 'Let’s talk about your project'), 'text' => $T('Décrivez-nous votre besoin : nous revenons vers vous sous 24 heures avec une première proposition.', 'Tell us what you need: we get back to you within 24 hours with a first proposal.'),
            'buttons' => [[$T('Demander une démo', 'Request a demo'), $contact, 'accent'], [$T('Voir nos réalisations', 'See our work'), $work, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Nous concevons des logiciels, applications et infrastructures fiables pour les entreprises et institutions africaines. Demandez une démo.', 'We build reliable software, apps and infrastructure for African businesses and institutions. Request a demo.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Logiciels et solutions numériques', 'Software and digital solutions'),
                    'title' => $T('Le numérique qui fait avancer votre entreprise', 'Technology that moves your business forward'),
                    'text' => $T('Applications, cloud, données et sécurité : nous concevons et opérons les outils dont vos équipes ont besoin, pensés pour les réalités du terrain.', 'Apps, cloud, data and security: we design and run the tools your teams need, built for real-world conditions.'),
                    'buttons' => [[$T('Demander une démo', 'Request a demo'), $contact], [$T('Nos solutions', 'Our solutions'), $solutions]],
                    'photo' => ['tech-accueil', 'laptop', $T('Équipe de développeurs travaillant sur un projet logiciel', 'Team of developers working on a software project')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos solutions', 'Our solutions'), 'title' => $T('Tout ce qu’il faut pour passer à l’échelle', 'Everything you need to scale'), 'items' => array_slice($serviceItems, 0, 3)]),
                B::split($theme, [
                    'eyebrow' => $T('Pourquoi nous choisir', 'Why choose us'),
                    'title' => $T('Une équipe d’ingénieurs proche de vous', 'An engineering team close to you'),
                    'html' => '<p>'.e($T('Nous travaillons avec vous comme une extension de votre équipe : même fuseau horaire, même langue, et une vraie compréhension de vos contraintes (connectivité, paiement mobile, conformité locale).', 'We work with you as an extension of your team: same time zone, same language, and a real understanding of your constraints (connectivity, mobile payments, local compliance).')).'</p>',
                    'bullets' => [$T('Équipes locales, standards internationaux', 'Local teams, international standards'), $T('Livraisons par étapes, démonstration toutes les deux semaines', 'Staged delivery, a demo every two weeks'), $T('Code et données vous appartiennent', 'You own the code and the data')],
                    'buttons' => [[$T('Notre histoire', 'Our story'), $about, 'outline']],
                    'photo' => ['tech-equipe-dev', 'users', $T('Ingénieurs en réunion autour d’un tableau blanc', 'Engineers meeting around a whiteboard')],
                    'reverse' => true,
                ]),
                B::stats($theme, $stats),
                B::steps($theme, ['eyebrow' => $T('Notre méthode', 'Our method'), 'title' => $T('De l’idée à la mise en production', 'From idea to production'), 'items' => [
                    [$T('Cadrage', 'Discovery'), $T('Ateliers pour définir les objectifs, les utilisateurs et le budget.', 'Workshops to define goals, users and budget.')],
                    [$T('Conception', 'Design'), $T('Maquettes testées avec de vrais utilisateurs avant le développement.', 'Prototypes tested with real users before development.')],
                    [$T('Développement', 'Build'), $T('Cycles courts, tests automatisés et démonstrations régulières.', 'Short cycles, automated tests and regular demos.')],
                    [$T('Exploitation', 'Run'), $T('Mise en ligne, supervision et amélioration continue.', 'Launch, monitoring and continuous improvement.')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'solutions', 'slug' => $u('solutions', 'solutions'), 'title' => $T('Solutions', 'Solutions'),
            'description' => $T('Développement web et mobile, cloud, données et IA, cybersécurité, transformation digitale et support : nos services en détail.', 'Web and mobile development, cloud, data and AI, cybersecurity, digital transformation and support: our services in detail.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Solutions', 'Solutions'), 'title' => $T('Six expertises, une seule équipe', 'Six areas of expertise, one team'), 'intro' => $T('Du prototype à l’exploitation, nous couvrons tout le cycle de vie de vos systèmes.', 'From prototype to operations, we cover the full lifecycle of your systems.')]),
                B::features($theme, ['items' => $serviceItems, 'cols' => 3]),
                B::split($theme, [
                    'eyebrow' => $T('Notre approche', 'Our approach'),
                    'title' => $T('Simple, mesurable, maintenable', 'Simple, measurable, maintainable'),
                    'html' => '<p>'.e($T('Nous choisissons les technologies selon votre besoin, pas selon la mode. Chaque projet a des indicateurs clairs et une documentation complète, pour que vous restiez autonome.', 'We choose technologies for your needs, not for trends. Every project has clear indicators and full documentation so you stay in control.')).'</p>',
                    'bullets' => [$T('Architecture sobre et évolutive', 'Lean, scalable architecture'), $T('Fonctionne avec une connexion limitée', 'Works with limited connectivity'), $T('Transfert de compétences à vos équipes', 'Skills transfer to your teams')],
                    'photo' => ['tech-approche', 'layers', $T('Schéma d’architecture logicielle sur un écran', 'Software architecture diagram on a screen')],
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'intro' => $T('Une autre question ? Notre équipe vous répond.', 'Another question? Our team will answer.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Combien de temps dure un projet type ?', 'How long does a typical project take?'), $T('Un premier produit utilisable est livré en 8 à 12 semaines, puis amélioré par cycles de deux semaines.', 'A first usable product is delivered in 8 to 12 weeks, then improved in two-week cycles.')],
                    [$T('Pouvez-vous reprendre un projet existant ?', 'Can you take over an existing project?'), $T('Oui. Nous commençons par un audit du code et de l’infrastructure, puis nous proposons un plan de reprise.', 'Yes. We start with a code and infrastructure audit, then propose a takeover plan.')],
                    [$T('Où sont hébergées nos données ?', 'Where is our data hosted?'), $T('Selon vos exigences : cloud international, centre de données local ou hébergement hybride.', 'Depending on your requirements: international cloud, local data center or hybrid hosting.')],
                    [$T('Le code source nous appartient-il ?', 'Do we own the source code?'), $T('Oui. Le code, la documentation et les accès vous sont remis à la fin de chaque étape.', 'Yes. The code, documentation and access are handed over at the end of each stage.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'pricing', 'slug' => $u('tarifs', 'pricing'), 'title' => $T('Tarifs', 'Pricing'),
            'description' => $T('Des formules claires pour démarrer, grandir et opérer à grande échelle. Sans frais cachés, résiliable à tout moment.', 'Clear plans to start, grow and operate at scale. No hidden fees, cancel anytime.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Tarifs', 'Pricing'), 'title' => $T('Des formules claires, sans surprise', 'Clear plans, no surprises'), 'intro' => $T('Choisissez la formule adaptée à votre taille. Changez à tout moment.', 'Pick the plan that fits your size. Switch at any time.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Démarrage', 'Starter'), '99', '$', $T('/ mois', '/ month'), $T('Pour valider un produit ou un petit site.', 'To validate a product or a small site.'), [$T('Hébergement et sauvegardes', 'Hosting and backups'), $T('Support par e-mail', 'Email support'), $T('1 environnement', '1 environment'), '-'.$T('Supervision 24h/24', '24/7 monitoring')], $T('Commencer', 'Get started')],
                    [$T('Croissance', 'Growth'), '349', '$', $T('/ mois', '/ month'), $T('Pour les entreprises qui développent leurs usages.', 'For businesses growing their usage.'), [$T('Tout le plan Démarrage', 'Everything in Starter'), $T('Supervision 24h/24', '24/7 monitoring'), $T('3 environnements', '3 environments'), $T('Support prioritaire', 'Priority support')], $T('Demander une démo', 'Request a demo'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Entreprise', 'Enterprise'), '899', '$', $T('/ mois', '/ month'), $T('Pour les systèmes critiques et les institutions.', 'For critical systems and institutions.'), [$T('Tout le plan Croissance', 'Everything in Growth'), $T('Engagement de disponibilité (SLA)', 'Uptime commitment (SLA)'), $T('Ingénieur référent dédié', 'Dedicated lead engineer'), $T('Audit de sécurité annuel', 'Annual security audit')], $T('Nous contacter', 'Contact us')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Inclus dans chaque formule', 'Included in every plan'), 'title' => $T('Les bases solides, toujours', 'Solid foundations, always'), 'items' => [
                    ['lock', $T('Sécurité de base', 'Baseline security'), $T('Chiffrement, sauvegardes quotidiennes et mises à jour régulières.', 'Encryption, daily backups and regular updates.')],
                    ['chart-line', $T('Rapports mensuels', 'Monthly reports'), $T('Performance, incidents et usage, expliqués simplement.', 'Performance, incidents and usage, explained simply.')],
                    ['handshake', $T('Sans engagement', 'No lock-in'), $T('Résiliable à tout moment, vos données restent les vôtres.', 'Cancel anytime, your data stays yours.')],
                ]]),
                B::faq($theme, ['title' => $T('Facturation : vos questions', 'Billing: your questions'), 'items' => [
                    [$T('Quels moyens de paiement acceptez-vous ?', 'Which payment methods do you accept?'), $T('Virement bancaire, carte et Mobile Money. Une facture est émise chaque mois.', 'Bank transfer, card and Mobile Money. An invoice is issued every month.')],
                    [$T('Puis-je changer de formule ?', 'Can I change plans?'), $T('Oui, à tout moment. Le changement s’applique au prochain cycle de facturation.', 'Yes, at any time. The change applies from the next billing cycle.')],
                    [$T('Les développements sur mesure sont-ils inclus ?', 'Is custom development included?'), $T('Non, ils font l’objet d’un devis séparé, à prix fixe ou au temps passé.', 'No, they are quoted separately, at a fixed price or time and materials.')],
                    [$T('Proposez-vous une remise annuelle ?', 'Do you offer an annual discount?'), $T('Oui, deux mois offerts pour tout paiement annuel.', 'Yes, two months free for any annual payment.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'work', 'slug' => $u('realisations', 'work'), 'title' => $T('Réalisations', 'Work'),
            'description' => $T('Études de cas : banque, logistique, santé, éducation. Découvrez comment nos solutions produisent des résultats concrets.', 'Case studies: banking, logistics, health, education. See how our solutions deliver concrete results.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Réalisations', 'Work'), 'title' => $T('Des projets livrés, des résultats concrets', 'Delivered projects, concrete results'), 'intro' => $T('Quelques études de cas, présentées de façon anonyme à la demande de nos clients.', 'A few case studies, presented anonymously at our clients’ request.')]),
                B::cards($theme, ['items' => [
                    ['tech-cas-banque', 'landmark', $T('Une banque régionale', 'A regional bank'), $T('Application mobile d’ouverture de compte : 40 000 clients en six mois.', 'Mobile account-opening app: 40,000 customers in six months.')],
                    ['tech-cas-logistique', 'truck', $T('Un opérateur logistique', 'A logistics operator'), $T('Suivi des livraisons en temps réel, retards réduits de 30 %.', 'Real-time delivery tracking, delays cut by 30%.')],
                    ['tech-cas-sante', 'hospital', $T('Un réseau de cliniques', 'A network of clinics'), $T('Dossier patient numérique déployé dans 12 sites, même hors ligne.', 'Digital patient records deployed across 12 sites, even offline.')],
                    ['tech-cas-education', 'graduation-cap', $T('Une université privée', 'A private university'), $T('Plateforme d’inscription et d’apprentissage en ligne pour 8 000 étudiants.', 'Online enrollment and learning platform for 8,000 students.')],
                    ['tech-cas-commerce', 'shopping-cart', $T('Une chaîne de distribution', 'A retail chain'), $T('Boutique en ligne et caisse connectée au paiement mobile.', 'Online store and point of sale connected to mobile payments.')],
                    ['tech-cas-ong', 'database', $T('Une organisation internationale', 'An international organization'), $T('Tableau de bord de données de terrain pour piloter 30 projets.', 'Field data dashboard to steer 30 projects.')],
                ]]),
                B::stats($theme, $stats + ['bg' => 'primary']),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('a-propos', 'about'), 'title' => $T('À propos', 'About us'),
            'description' => $T('Notre histoire, notre mission, nos valeurs et l’équipe de direction qui porte l’entreprise.', 'Our story, mission, values and the leadership team behind the company.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('À propos', 'About us'), 'title' => $T('Construire la tech africaine, depuis l’Afrique', 'Building African tech, from Africa'), 'intro' => $T('Une équipe d’ingénieurs et de designers au service des entreprises du continent.', 'A team of engineers and designers serving businesses across the continent.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Né d’un constat simple', 'Born from a simple observation'),
                    'html' => '<p>'.e($T('Il y a huit ans, trois ingénieurs ont constaté que les entreprises locales importaient des outils mal adaptés à leur réalité. Ils ont fondé une équipe pour construire des solutions pensées ici : mobile d’abord, résistantes aux coupures, simples à utiliser.', 'Eight years ago, three engineers noticed that local companies were importing tools poorly suited to their reality. They founded a team to build solutions designed here: mobile first, resilient to outages, simple to use.')).'</p><p>'.e($T('Aujourd’hui, nous sommes 45 et nous accompagnons des clients dans plusieurs pays.', 'Today we are 45 people supporting clients in several countries.')).'</p>',
                    'photo' => ['tech-histoire', 'rocket', $T('Premiers locaux de l’équipe', 'The team’s first office')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce qui guide notre travail', 'What guides our work'), 'bg' => 'light', 'items' => [
                    ['target', $T('Exigence', 'Excellence'), $T('Du code testé, relu et documenté, sans compromis.', 'Tested, reviewed and documented code, no compromises.')],
                    ['eye', $T('Transparence', 'Transparency'), $T('Des délais, des coûts et des risques annoncés clairement.', 'Timelines, costs and risks stated clearly.')],
                    ['sprout', $T('Transmission', 'Sharing'), $T('Nous formons des talents locaux et partageons nos savoirs.', 'We train local talent and share our knowledge.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('Direction', 'Leadership'), 'title' => $T('Les personnes qui dirigent l’équipe', 'The people leading the team'), 'items' => [
                    ['[Prénom Nom]', $T('Directeur général', 'Chief executive'), 'tech-equipe-1'],
                    ['[Prénom Nom]', $T('Directrice technique', 'Chief technology officer'), 'tech-equipe-2'],
                    ['[Prénom Nom]', $T('Directrice produit', 'Head of product'), 'tech-equipe-3'],
                    ['[Prénom Nom]', $T('Responsable sécurité', 'Head of security'), 'tech-equipe-4'],
                ]]),
                B::quote($theme, ['text' => $T('Notre mission : mettre une technologie fiable et accessible au service de la croissance des entreprises africaines.', 'Our mission: putting reliable, accessible technology at the service of African business growth.'), 'source' => $T('— Notre mission', '— Our mission'), 'bg' => 'light']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'careers', 'slug' => $u('carrieres', 'careers'), 'title' => $T('Carrières', 'Careers'),
            'description' => $T('Rejoignez une équipe d’ingénieurs et de designers ambitieux. Découvrez nos postes ouverts et notre processus de recrutement.', 'Join a team of ambitious engineers and designers. See our open positions and hiring process.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Carrières', 'Careers'), 'title' => $T('Construisez des produits qui comptent', 'Build products that matter'), 'intro' => $T('Nous recrutons des profils curieux, rigoureux et attachés au travail d’équipe.', 'We hire curious, rigorous people who value teamwork.')]),
                B::features($theme, ['eyebrow' => $T('Pourquoi nous rejoindre', 'Why join us'), 'title' => $T('Un cadre pour progresser', 'A place to grow'), 'items' => [
                    ['graduation-cap', $T('Formation continue', 'Continuous learning'), $T('Budget annuel de formation et certifications prises en charge.', 'Annual training budget and certifications covered.')],
                    ['laptop', $T('Travail flexible', 'Flexible work'), $T('Bureau moderne avec énergie de secours, et télétravail possible.', 'Modern office with backup power, and remote work possible.')],
                    ['heart-pulse', $T('Bien-être', 'Well-being'), $T('Assurance santé, congés généreux et horaires respectés.', 'Health insurance, generous leave and respected working hours.')],
                ]]),
                B::events($theme, ['icon' => 'briefcase', 'eyebrow' => $T('Postes ouverts', 'Open positions'), 'title' => $T('Nous recrutons', 'We are hiring'), 'bg' => 'light', 'items' => [
                    [$T('Ingénieur·e logiciel back-end', 'Back-end software engineer'), $T('CDI, temps plein', 'Full-time, permanent'), $T('Kinshasa ou à distance', 'Kinshasa or remote')],
                    [$T('Développeur·se mobile', 'Mobile developer'), $T('CDI, temps plein', 'Full-time, permanent'), 'Abidjan'],
                    [$T('Ingénieur·e DevOps', 'DevOps engineer'), $T('CDI, temps plein', 'Full-time, permanent'), $T('Dakar ou à distance', 'Dakar or remote')],
                    [$T('Designer produit (UX/UI)', 'Product designer (UX/UI)'), $T('CDD, 12 mois', 'Fixed-term, 12 months'), 'Kigali'],
                    [$T('Analyste cybersécurité', 'Cybersecurity analyst'), $T('CDI, temps plein', 'Full-time, permanent'), 'Kinshasa'],
                    [$T('Stagiaire développement web', 'Web development intern'), $T('Stage, 6 mois', 'Internship, 6 months'), 'Kinshasa'],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Recrutement', 'Hiring'), 'title' => $T('Un processus clair en quatre étapes', 'A clear four-step process'), 'items' => [
                    [$T('Candidature', 'Application'), $T('Envoyez votre CV et vos projets, nous répondons sous une semaine.', 'Send your CV and projects, we reply within a week.')],
                    [$T('Entretien', 'Interview'), $T('Un échange sur votre parcours et vos attentes.', 'A conversation about your background and expectations.')],
                    [$T('Cas pratique', 'Practical exercise'), $T('Un exercice réaliste, discuté avec l’équipe.', 'A realistic exercise, discussed with the team.')],
                    [$T('Proposition', 'Offer'), $T('Une réponse rapide et une intégration accompagnée.', 'A quick answer and a guided onboarding.')],
                ]]),
                B::cta($theme, ['title' => $T('Pas de poste pour vous ?', 'No role for you?'), 'text' => $T('Envoyez-nous une candidature spontanée : nous gardons le contact.', 'Send us an open application: we keep in touch.'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'blog', 'slug' => 'blog', 'title' => 'Blog',
            'description' => $T('Articles techniques, retours d’expérience et webinaires sur le développement, le cloud, les données et la sécurité.', 'Technical articles, lessons learned and webinars on development, cloud, data and security.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Blog', 'title' => $T('Ressources et retours d’expérience', 'Resources and lessons learned'), 'intro' => $T('Ce que nous apprenons en construisant des produits numériques.', 'What we learn while building digital products.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['icon' => 'video', 'eyebrow' => $T('Événements', 'Events'), 'title' => $T('Prochains webinaires et rencontres', 'Upcoming webinars and meetups'), 'bg' => 'light', 'items' => [
                    [$T('Webinaire : migrer vers le cloud sans interruption', 'Webinar: migrating to the cloud with no downtime'), $T('12 mars, 15 h', 'March 12, 3 pm'), $T('En ligne', 'Online')],
                    [$T('Atelier : l’IA appliquée à vos données', 'Workshop: AI applied to your data'), $T('9 avril, 10 h', 'April 9, 10 am'), 'Kinshasa'],
                    [$T('Rencontre développeurs', 'Developer meetup'), $T('21 mai, 18 h', 'May 21, 6 pm'), 'Abidjan'],
                    [$T('Table ronde : cybersécurité et conformité', 'Roundtable: cybersecurity and compliance'), $T('18 juin, 14 h', 'June 18, 2 pm'), $T('En ligne', 'Online')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Demandez une démo ou parlez-nous de votre projet. Notre équipe vous répond sous 24 heures ouvrées.', 'Request a demo or tell us about your project. Our team replies within one business day.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons de votre projet', 'Let’s talk about your project'), 'intro' => $T('Notre équipe vous répond sous 24 heures ouvrées.', 'Our team replies within one business day.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Écrivez-nous ou passez nous voir du lundi au vendredi.', 'Write to us or visit us Monday to Friday.'),
                    'items' => [
                        ['map-pin', $T('Boulevard du 30 Juin, Kinshasa, RDC', 'Boulevard du 30 Juin, Kinshasa, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h – 18 h', 'Monday – Friday, 8 am – 6 pm')],
                    ],
                    'social' => ['x', 'github', 'youtube'],
                    'subject' => true,
                    'map' => 'Kinshasa, RDC',
                    'bg' => 'white',
                ]),
            ]];

        $blog = [
            'base' => 'blog',
            'title' => 'Blog',
            'categories' => ['produit' => $T('Produit', 'Product'), 'securite' => $T('Sécurité', 'Security'), 'infra' => $T('Infrastructure', 'Infrastructure')],
            'posts' => [
                ['key' => 'hors-ligne', 'slug' => $u('application-hors-ligne', 'offline-mobile-app'), 'category' => 'produit', 'tags' => ['Mobile'],
                    'title' => $T('Concevoir une application mobile qui fonctionne hors ligne', 'Designing a mobile app that works offline'),
                    'excerpt' => $T('Synchronisation, stockage local et gestion des conflits : les bonnes pratiques.', 'Sync, local storage and conflict handling: best practices.'),
                    'photo' => ['tech-blog-1', 'smartphone', $T('Application sur un téléphone', 'App on a phone')],
                    'heading' => $T('Trois principes', 'Three principles'),
                    'body' => [
                        $T('Dans beaucoup de régions, la connexion est intermittente. Une application utile doit continuer à fonctionner quand le réseau disparaît.', 'In many regions, connectivity is intermittent. A useful app must keep working when the network drops.'),
                        $T('Stockez localement d’abord, synchronisez ensuite ; affichez clairement ce qui n’est pas encore envoyé ; prévoyez comment résoudre deux modifications contradictoires.', 'Store locally first, sync later; clearly show what has not been sent yet; plan how to resolve two conflicting edits.'),
                        $T('Ces choix se font dès la conception : les ajouter après coup coûte beaucoup plus cher.', 'These choices are made at design time: adding them afterwards costs far more.'),
                    ]],
                ['key' => 'cyber', 'slug' => $u('cybersecurite-pme', 'cybersecurity-small-business'), 'category' => 'securite', 'tags' => [$T('PME', 'Small business')],
                    'title' => $T('Cinq réflexes de cybersécurité pour les PME', 'Five cybersecurity habits for small businesses'),
                    'excerpt' => $T('Des mesures simples qui évitent la majorité des incidents.', 'Simple measures that prevent most incidents.'),
                    'photo' => ['tech-blog-2', 'shield-check', $T('Écran de sécurité', 'Security screen')],
                    'quote' => $T('« La plupart des attaques réussies exploitent une négligence, pas une faille sophistiquée. »', '“Most successful attacks exploit carelessness, not a sophisticated flaw.”'),
                    'body' => [
                        $T('Activez la double authentification, faites des sauvegardes régulières hors ligne et mettez à jour vos logiciels.', 'Turn on two-factor authentication, make regular offline backups and keep your software up to date.'),
                        $T('Formez vos équipes à reconnaître les messages piégés et limitez les droits d’administration aux personnes qui en ont vraiment besoin.', 'Train your teams to spot phishing messages and limit admin rights to the people who really need them.'),
                        $T('Ces cinq réflexes ne coûtent presque rien et protègent l’essentiel de votre activité.', 'These five habits cost almost nothing and protect most of your business.'),
                    ]],
                ['key' => 'cloud', 'slug' => $u('cloud-ou-local', 'cloud-or-local'), 'category' => 'infra', 'tags' => ['Cloud'],
                    'title' => $T('Cloud public ou hébergement local ?', 'Public cloud or local hosting?'),
                    'excerpt' => $T('Coûts, latence, conformité : comment choisir selon votre situation.', 'Cost, latency, compliance: how to choose for your situation.'),
                    'photo' => ['tech-blog-3', 'cloud', $T('Salle de serveurs', 'Server room')],
                    'heading' => $T('Notre conseil', 'Our advice'),
                    'body' => [
                        $T('Le cloud public offre souplesse et services prêts à l’emploi ; l’hébergement local rassure sur la localisation des données et réduit la latence pour les utilisateurs proches.', 'The public cloud offers flexibility and ready-made services; local hosting reassures about data location and reduces latency for nearby users.'),
                        $T('Comparez le coût total sur trois ans, les exigences réglementaires de votre secteur et la qualité de la connexion de vos utilisateurs.', 'Compare the total cost over three years, your sector’s regulatory requirements and your users’ connection quality.'),
                        $T('Souvent, la bonne réponse est hybride : les données sensibles en local, le reste dans le cloud.', 'Often, the right answer is hybrid: sensitive data locally, the rest in the cloud.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => 'Solutions', 'url' => $solutions],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('Réalisations', 'Work'), 'url' => $work],
                    ['label' => $T('À propos', 'About'), 'url' => $about],
                    ['label' => $T('Carrières', 'Careers'), 'url' => $careers],
                    ['label' => 'Blog', 'url' => $blog],
                ],
                'cta_label' => $T('Demander une démo', 'Request a demo'),
                'cta_url' => $contact,
                'footer_text' => $T('Logiciels, cloud, données et cybersécurité pour les entreprises et institutions africaines.', 'Software, cloud, data and cybersecurity for African businesses and institutions.'),
                'footer_links' => [
                    ['label' => $T('Nos solutions', 'Our solutions'), 'url' => $solutions],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('Carrières', 'Careers'), 'url' => $careers],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
