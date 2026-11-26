<?php

/*
 * Pack « Startup SaaS » (secteur Entreprises tech) : 8 pages, en français et en
 * anglais, pour une jeune entreprise qui vend un logiciel en ligne. Fonctionnalités,
 * intégrations, tarifs mensuels et annuels, sécurité, clients et études de cas,
 * ressources (documentation et blog), demande de démo.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du produit). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Entreprises tech', 'en' => 'Tech companies'],
        'icon' => 'rocket',
        'keywords' => ['saas', 'logiciel', 'startup', 'application web', 'plateforme', 'abonnement', 'essai gratuit', 'démo', 'api', 'intégrations', 'software', 'web app', 'platform', 'subscription', 'free trial', 'demo', 'integrations', 'b2b'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $cur = $T('€', '$');
        $features = $u('/fonctionnalites', '/features');
        $integrations = '/integrations';
        $pricing = $u('/tarifs', '/pricing');
        $security = $u('/securite', '/security');
        $customers = $u('/clients', '/customers');
        $resources = $u('/ressources', '/resources');
        $contact = '/contact';

        $featureItems = [
            ['layout-grid', $T('Tableaux de projets', 'Project boards'), $T('Listes, tableaux kanban et chronologies : chaque équipe choisit sa vue, sur les mêmes données.', 'Lists, kanban boards and timelines: each team picks its view, on the same data.')],
            ['workflow', $T('Automatisations', 'Automations'), $T('Relances, affectations et changements de statut se font tout seuls, sans une ligne de code.', 'Reminders, assignments and status changes happen on their own, no code required.')],
            ['chart-line', $T('Rapports en temps réel', 'Real-time reports'), $T('Charge de travail, délais et budget dans des tableaux de bord partagés.', 'Workload, deadlines and budget in shared dashboards.')],
            ['message-square', $T('Collaboration', 'Collaboration'), $T('Commentaires, mentions, fichiers et validations au même endroit que les tâches.', 'Comments, mentions, files and approvals right next to the tasks.')],
            ['smartphone', $T('Applications mobiles', 'Mobile apps'), $T('iOS et Android, avec mode hors ligne et notifications intelligentes.', 'iOS and Android, with offline mode and smart notifications.')],
            ['code', $T('API ouverte', 'Open API'), $T('API REST documentée et webhooks pour connecter vos outils internes.', 'Documented REST API and webhooks to connect your internal tools.')],
        ];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Des équipes qui avancent plus vite', 'Teams that move faster'), 'items' => [
            [$T('Nous avons remplacé trois outils et une dizaine de tableurs. Les réunions de suivi ont été divisées par deux.', 'We replaced three tools and a dozen spreadsheets. Status meetings were cut in half.'), '[Prénom Nom]', $T('Directrice des opérations, agence de 80 personnes', 'Head of operations, 80-person agency')],
            [$T('La mise en place a pris une après-midi. Le support répond en quelques minutes, dans notre langue.', 'Setup took one afternoon. Support answers within minutes, in our language.'), '[Prénom Nom]', $T('Fondateur, startup e-commerce', 'Founder, e-commerce startup')],
            [$T('L’authentification unique et les journaux d’audit ont convaincu notre équipe sécurité dès la première démo.', 'Single sign-on and audit logs won over our security team at the very first demo.'), '[Prénom Nom]', $T('Responsable informatique, groupe industriel', 'IT manager, industrial group')],
        ]];
        $cta = ['title' => $T('Essayez gratuitement pendant 14 jours', 'Try it free for 14 days'), 'text' => $T('Sans carte bancaire, sans engagement. Ou réservez une démo de 30 minutes avec notre équipe.', 'No credit card, no commitment. Or book a 30-minute demo with our team.'),
            'buttons' => [[$T('Demander une démo', 'Request a demo'), $contact, 'accent'], [$T('Voir les tarifs', 'See pricing'), $pricing, 'outline-light']]];
        $plans = fn (bool $annual) => [
            [$T('Starter', 'Starter'), '0', $cur, $T('/ utilisateur / mois', '/ user / month'), $T('Pour les petites équipes qui démarrent.', 'For small teams getting started.'), [$T('Jusqu’à 5 utilisateurs', 'Up to 5 users'), $T('Projets illimités', 'Unlimited projects'), $T('Applications mobiles', 'Mobile apps'), '-'.$T('Automatisations', 'Automations'), '-'.$T('Rapports avancés', 'Advanced reports')], $T('Commencer gratuitement', 'Start for free')],
            [$T('Pro', 'Pro'), $annual ? '10' : '12', $cur, $annual ? $T('/ utilisateur / mois, facturé à l’année', '/ user / month, billed yearly') : $T('/ utilisateur / mois', '/ user / month'), $T('Pour les équipes qui veulent automatiser.', 'For teams ready to automate.'), [$T('Utilisateurs illimités', 'Unlimited users'), $T('1 000 automatisations par mois', '1,000 automations per month'), $T('Rapports et tableaux de bord', 'Reports and dashboards'), $T('Support par chat', 'Chat support'), '-'.$T('Authentification unique (SSO)', 'Single sign-on (SSO)')], $T('Essayer 14 jours', 'Try for 14 days'), true, $annual ? $T('2 mois offerts', '2 months free') : $T('Le plus choisi', 'Most popular')],
            [$T('Business', 'Business'), $annual ? '20' : '24', $cur, $annual ? $T('/ utilisateur / mois, facturé à l’année', '/ user / month, billed yearly') : $T('/ utilisateur / mois', '/ user / month'), $T('Pour les organisations exigeantes.', 'For demanding organizations.'), [$T('Tout le plan Pro', 'Everything in Pro'), $T('Automatisations illimitées', 'Unlimited automations'), $T('Authentification unique (SSO)', 'Single sign-on (SSO)'), $T('Journaux d’audit', 'Audit logs'), $T('Gestionnaire de compte dédié', 'Dedicated account manager')], $T('Demander une démo', 'Request a demo')],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Le logiciel en ligne qui réunit projets, tâches, automatisations et rapports de vos équipes. Essai gratuit de 14 jours, sans carte bancaire.', 'The online software that brings your teams’ projects, tasks, automations and reports together. 14-day free trial, no credit card.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Logiciel de gestion de projets', 'Project management software'),
                    'title' => $T('Toutes vos équipes, un seul espace de travail', 'All your teams, one workspace'),
                    'text' => $T('Planifiez, automatisez et suivez votre travail au même endroit. Moins de réunions, moins de tableurs, plus de projets livrés à temps.', 'Plan, automate and track your work in one place. Fewer meetings, fewer spreadsheets, more projects delivered on time.'),
                    'buttons' => [[$T('Essai gratuit de 14 jours', 'Start 14-day free trial'), $contact], [$T('Découvrir les fonctionnalités', 'Explore features'), $features]],
                    'photo' => ['saas-accueil', 'laptop', $T('Ordinateur portable affichant un tableau de bord de projets sur un bureau lumineux', 'Laptop showing a project dashboard on a bright desk')],
                ]),
                B::features($theme, ['eyebrow' => $T('Fonctionnalités', 'Features'), 'title' => $T('Tout ce qu’il faut pour livrer, rien de superflu', 'Everything you need to ship, nothing you don’t'), 'items' => $featureItems]),
                B::split($theme, [
                    'eyebrow' => $T('Automatisations', 'Automations'),
                    'title' => $T('Laissez le logiciel faire les relances', 'Let the software do the chasing'),
                    'html' => '<p>'.e($T('Quand une tâche change de statut, la bonne personne est prévenue. Quand une échéance approche, une relance part toute seule. Choisissez parmi plus de 50 modèles d’automatisations ou créez les vôtres en quelques clics.', 'When a task changes status, the right person is notified. When a deadline approaches, a reminder goes out on its own. Choose from more than 50 automation templates or build your own in a few clicks.')).'</p>',
                    'bullets' => [$T('Plus de 50 modèles prêts à l’emploi', 'More than 50 ready-made templates'), $T('Déclencheurs, conditions et actions', 'Triggers, conditions and actions'), $T('Historique de chaque exécution', 'History of every run')],
                    'buttons' => [[$T('Voir toutes les fonctionnalités', 'See all features'), $features, 'outline']],
                    'photo' => ['saas-automatisations', 'workflow', $T('Écran montrant un schéma d’automatisation avec des blocs reliés', 'Screen showing an automation flow with connected blocks')],
                    'reverse' => true, 'bg' => 'light',
                ]),
                B::stats($theme, ['title' => $T('Des résultats mesurés chez nos clients', 'Results measured with our customers'), 'items' => [
                    [4000, '+', $T('équipes utilisatrices', 'teams using it')],
                    [6, ' h', $T('gagnées par personne et par semaine', 'saved per person per week')],
                    [24, '/7', $T('surveillance de la plateforme', 'platform monitoring')],
                    [97, ' %', $T('d’utilisateurs satisfaits', 'of users satisfied')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Démarrer', 'Getting started'), 'title' => $T('Opérationnel en une journée', 'Up and running in a day'), 'items' => [
                    [$T('Créez votre espace', 'Create your workspace'), $T('Inscription en deux minutes, sans carte bancaire.', 'Sign up in two minutes, no credit card.')],
                    [$T('Importez vos données', 'Import your data'), $T('Depuis un tableur ou votre ancien outil, en quelques clics.', 'From a spreadsheet or your old tool, in a few clicks.')],
                    [$T('Invitez votre équipe', 'Invite your team'), $T('Rôles et droits d’accès définis dès le départ.', 'Roles and access rights set from day one.')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'features', 'slug' => $u('fonctionnalites', 'features'), 'title' => $T('Fonctionnalités', 'Features'),
            'description' => $T('Tableaux de projets, automatisations, rapports en temps réel, collaboration, applications mobiles et API : toutes les fonctionnalités en détail.', 'Project boards, automations, real-time reports, collaboration, mobile apps and API: every feature of the platform explained in detail.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Fonctionnalités', 'Features'), 'title' => $T('Pensé pour la façon dont vous travaillez', 'Built for the way you work'), 'intro' => $T('Des outils simples pour chaque équipe, une vision d’ensemble pour la direction.', 'Simple tools for every team, a big-picture view for leadership.')]),
                B::features($theme, ['items' => $featureItems, 'cols' => 3]),
                B::split($theme, [
                    'eyebrow' => $T('Tableaux de bord', 'Dashboards'),
                    'title' => $T('Une vision claire de chaque projet', 'A clear view of every project'),
                    'html' => '<p>'.e($T('Avancement, charge de chaque personne, budget consommé et risques : vos indicateurs se mettent à jour en temps réel et se partagent en un lien, y compris avec vos clients.', 'Progress, each person’s workload, budget spent and risks: your metrics update in real time and can be shared with a link, including with your clients.')).'</p>',
                    'bullets' => [$T('Plus de 20 graphiques personnalisables', 'More than 20 customizable charts'), $T('Export PDF et tableur', 'PDF and spreadsheet export'), $T('Rapports envoyés automatiquement chaque semaine', 'Reports sent automatically every week')],
                    'photo' => ['saas-tableau-de-bord', 'chart-line', $T('Écran d’ordinateur affichant des graphiques et des indicateurs de projet', 'Computer screen displaying project charts and metrics')],
                    'bg' => 'light',
                ]),
                B::split($theme, [
                    'eyebrow' => $T('Mobile', 'Mobile'),
                    'title' => $T('Votre travail vous suit partout', 'Your work goes where you go'),
                    'html' => '<p>'.e($T('Validez une tâche entre deux rendez-vous, répondez à un commentaire dans le train, consultez vos projets sans connexion : les applications iOS et Android se synchronisent dès que le réseau revient.', 'Approve a task between meetings, reply to a comment on the train, check your projects offline: the iOS and Android apps sync as soon as you are back online.')).'</p>',
                    'bullets' => [$T('Mode hors ligne', 'Offline mode'), $T('Notifications réglables par projet', 'Notifications you can tune per project'), $T('Photos et pièces jointes depuis le téléphone', 'Photos and attachments from your phone')],
                    'photo' => ['saas-mobile', 'smartphone', $T('Personne consultant une application de gestion de tâches sur son téléphone', 'Person checking a task management app on a smartphone')],
                    'reverse' => true,
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions sur le produit', 'Product questions'), 'intro' => $T('Vous ne trouvez pas votre réponse ? Notre équipe vous fait une démonstration.', 'Can’t find your answer? Our team will give you a demo.'), 'buttons' => [[$T('Demander une démo', 'Request a demo'), $contact, 'outline']], 'bg' => 'light', 'items' => [
                    [$T('Faut-il installer quelque chose ?', 'Do I need to install anything?'), $T('Non. Le logiciel fonctionne dans le navigateur ; les applications mobiles sont facultatives.', 'No. The software runs in your browser; the mobile apps are optional.')],
                    [$T('Puis-je importer mes données existantes ?', 'Can I import my existing data?'), $T('Oui, depuis un fichier tableur ou depuis les principaux outils du marché, avec un assistant d’import.', 'Yes, from a spreadsheet file or the main tools on the market, with an import wizard.')],
                    [$T('Le logiciel est-il disponible en plusieurs langues ?', 'Is the software available in several languages?'), $T('Oui, l’interface est traduite en plusieurs langues et chaque utilisateur choisit la sienne.', 'Yes, the interface is translated into several languages and each user picks their own.')],
                    [$T('Pouvez-vous ajouter une fonctionnalité ?', 'Can you add a feature?'), $T('Notre feuille de route publique recueille vos idées ; les plus demandées sont développées en priorité.', 'Our public roadmap collects your ideas; the most requested ones are built first.')],
                ]]),
            ]];

        $pages[] = ['key' => 'integrations', 'slug' => 'integrations', 'title' => $T('Intégrations', 'Integrations'),
            'description' => $T('Connectez le logiciel à votre messagerie, votre stockage, votre CRM, votre comptabilité et vos outils internes grâce aux intégrations et à l’API.', 'Connect the software to your messaging, file storage, CRM, accounting and in-house tools with ready-made integrations and the open API.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Intégrations', 'Integrations'), 'title' => $T('Branché sur les outils que vous utilisez déjà', 'Plugged into the tools you already use'), 'intro' => $T('Plus de 80 intégrations prêtes à l’emploi, et une API ouverte pour tout le reste.', 'More than 80 ready-made integrations, and an open API for everything else.')]),
                B::features($theme, ['cols' => 4, 'items' => [
                    ['message-circle', $T('Messagerie d’équipe', 'Team chat'), $T('Notifications et création de tâches depuis vos canaux.', 'Notifications and task creation from your channels.')],
                    ['folder', $T('Stockage de fichiers', 'File storage'), $T('Joignez vos documents sans les dupliquer.', 'Attach your documents without duplicating them.')],
                    ['calendar-days', $T('Agendas', 'Calendars'), $T('Échéances et jalons synchronisés dans votre calendrier.', 'Deadlines and milestones synced to your calendar.')],
                    ['mail', $T('E-mail', 'Email'), $T('Transformez un e-mail en tâche en le transférant.', 'Turn an email into a task by forwarding it.')],
                    ['users', $T('CRM', 'CRM'), $T('Un nouveau contrat signé crée le projet automatiquement.', 'A newly signed deal creates the project automatically.')],
                    ['calculator', $T('Comptabilité', 'Accounting'), $T('Temps passé et dépenses envoyés pour la facturation.', 'Time tracked and expenses sent for invoicing.')],
                    ['git-branch', $T('Outils de développement', 'Developer tools'), $T('Commits et demandes de fusion liés aux tâches.', 'Commits and pull requests linked to tasks.')],
                    ['credit-card', $T('Paiements', 'Payments'), $T('Suivi des paiements clients dans vos projets.', 'Track customer payments within your projects.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('API et webhooks', 'API and webhooks'),
                    'title' => $T('Construisez vos propres connexions', 'Build your own connections'),
                    'html' => '<p>'.e($T('Notre API REST couvre l’ensemble du produit : projets, tâches, utilisateurs, commentaires et rapports. Les webhooks préviennent vos systèmes en temps réel, et une documentation interactive permet de tester chaque appel.', 'Our REST API covers the whole product: projects, tasks, users, comments and reports. Webhooks notify your systems in real time, and interactive documentation lets you test every call.')).'</p>',
                    'bullets' => [$T('Clés d’API par espace et par rôle', 'API keys per workspace and role'), $T('Environnement de test gratuit', 'Free sandbox environment'), $T('Bibliothèques pour les langages courants', 'Libraries for common languages')],
                    'buttons' => [[$T('Lire la documentation', 'Read the docs'), $resources]],
                    'photo' => ['saas-api', 'terminal', $T('Développeuse écrivant du code sur deux écrans dans un bureau', 'Developer writing code on two monitors in an office')],
                    'reverse' => true, 'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('En trois clics', 'In three clicks'), 'title' => $T('Activer une intégration', 'Turning on an integration'), 'items' => [
                    [$T('Choisissez', 'Choose'), $T('Sélectionnez l’outil dans la bibliothèque d’intégrations.', 'Pick the tool from the integrations library.')],
                    [$T('Autorisez', 'Authorize'), $T('Connectez votre compte en toute sécurité, sans partager de mot de passe.', 'Connect your account securely, without sharing a password.')],
                    [$T('Réglez', 'Configure'), $T('Décidez ce qui se synchronise, et dans quel sens.', 'Decide what syncs, and in which direction.')],
                ]]),
                B::cta($theme, ['title' => $T('Il vous manque une intégration ?', 'Missing an integration?'), 'text' => $T('Dites-nous quel outil vous utilisez : nous priorisons les intégrations les plus demandées.', 'Tell us which tool you use: we prioritize the most requested integrations.'), 'buttons' => [[$T('Proposer une intégration', 'Suggest an integration'), $contact, 'accent'], [$T('Documentation de l’API', 'API documentation'), $resources, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'pricing', 'slug' => $u('tarifs', 'pricing'), 'title' => $T('Tarifs', 'Pricing'),
            'description' => $T('Tarifs mensuels ou annuels par utilisateur : Starter gratuit, Pro et Business. Deux mois offerts sur l’abonnement annuel, essai de 14 jours.', 'Monthly or annual per-user pricing: free Starter, Pro and Business plans. Two months free on annual billing and a 14-day free trial.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Tarifs', 'Pricing'), 'title' => $T('Un prix simple, qui grandit avec vous', 'Simple pricing that grows with you'), 'intro' => $T('Commencez gratuitement, passez à la formule supérieure quand vous en avez besoin. Prix hors taxes.', 'Start for free, upgrade when you need to. Prices exclude taxes.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Facturation mensuelle', 'Monthly billing'), 'title' => $T('Sans engagement', 'No commitment'), 'intro' => $T('Résiliable à tout moment, payé chaque mois.', 'Cancel anytime, paid every month.'), 'items' => $plans(false)]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Facturation annuelle', 'Annual billing'), 'title' => $T('Deux mois offerts', 'Two months free'), 'intro' => $T('Payé une fois par an, pour les équipes qui ont choisi leur outil.', 'Paid once a year, for teams that have settled on their tool.'), 'bg' => 'white', 'items' => $plans(true)]),
                M::table($theme, ['eyebrow' => $T('Comparatif', 'Comparison'), 'title' => $T('Comparez les formules', 'Compare plans'), 'bg' => 'light', 'rows' => [
                    [$T('Fonctionnalité', 'Feature'), 'Starter', 'Pro', 'Business'],
                    [$T('Utilisateurs', 'Users'), $T('Jusqu’à 5', 'Up to 5'), $T('Illimités', 'Unlimited'), $T('Illimités', 'Unlimited')],
                    [$T('Stockage', 'Storage'), '2 Go', '100 Go', $T('Illimité', 'Unlimited')],
                    [$T('Automatisations par mois', 'Automations per month'), '—', '1 000', $T('Illimitées', 'Unlimited')],
                    [$T('Authentification unique (SSO)', 'Single sign-on (SSO)'), '—', '—', '✓'],
                    [$T('Journaux d’audit', 'Audit logs'), '—', '—', '✓'],
                    ['Support', $T('Centre d’aide', 'Help center'), 'Chat', $T('Gestionnaire dédié', 'Dedicated manager')],
                ], 'note' => $T('Plus de 250 utilisateurs ou besoins spécifiques ? Contactez-nous pour une offre Entreprise.', 'More than 250 users or specific needs? Contact us for an Enterprise offer.')]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Facturation : vos questions', 'Billing: your questions'), 'items' => [
                    [$T('Faut-il une carte bancaire pour l’essai ?', 'Do I need a card for the trial?'), $T('Non. L’essai de 14 jours donne accès à la formule Pro, sans carte et sans engagement.', 'No. The 14-day trial gives you the Pro plan, with no card and no commitment.')],
                    [$T('Quels moyens de paiement acceptez-vous ?', 'Which payment methods do you accept?'), $T('Carte bancaire, virement ou paiement mobile selon les pays. Une facture est émise à chaque échéance.', 'Credit card, bank transfer or mobile payment depending on the country. An invoice is issued for every payment.')],
                    [$T('Que se passe-t-il si j’ajoute des utilisateurs ?', 'What if I add users?'), $T('Ils sont facturés au prorata jusqu’à la fin de la période en cours.', 'They are billed pro rata until the end of the current period.')],
                    [$T('Proposez-vous des remises aux associations ?', 'Do you offer nonprofit discounts?'), $T('Oui, 50 % sur les formules Pro et Business pour les associations et les établissements d’enseignement.', 'Yes, 50% off the Pro and Business plans for nonprofits and educational institutions.')],
                ]]),
            ]];

        $pages[] = ['key' => 'security', 'slug' => $u('securite', 'security'), 'title' => $T('Sécurité', 'Security'),
            'description' => $T('Chiffrement, authentification unique, sauvegardes, journaux d’audit et conformité : comment nous protégeons vos données au quotidien.', 'Encryption, single sign-on, backups, audit logs and compliance: how we protect your data every day, with clear and verifiable commitments.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Sécurité', 'Security'), 'title' => $T('Vos données sont votre bien le plus précieux', 'Your data is your most valuable asset'), 'intro' => $T('La sécurité n’est pas une option de la formule Business : elle protège chaque compte, dès le premier jour.', 'Security is not a Business-plan extra: it protects every account from day one.')]),
                B::pillars($theme, ['items' => [
                    ['lock', $T('Chiffrement', 'Encryption'), $T('Données chiffrées en transit et au repos, clés gérées séparément.', 'Data encrypted in transit and at rest, with keys managed separately.')],
                    ['key', $T('Accès maîtrisés', 'Controlled access'), $T('Double authentification, SSO et rôles finement réglables.', 'Two-factor authentication, SSO and fine-grained roles.')],
                    ['database', $T('Sauvegardes', 'Backups'), $T('Copies chiffrées toutes les heures, conservées 30 jours.', 'Encrypted copies every hour, kept for 30 days.')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Nos pratiques', 'Our practices'), 'title' => $T('La sécurité à chaque niveau', 'Security at every layer'), 'bg' => 'light', 'items' => [
                    ['server', $T('Hébergement certifié', 'Certified hosting'), $T('Centres de données certifiés, avec choix de la région d’hébergement.', 'Certified data centers, with a choice of hosting region.')],
                    ['shield-check', $T('Tests d’intrusion', 'Penetration testing'), $T('Audits indépendants chaque année et programme de signalement des failles.', 'Independent audits every year and a vulnerability disclosure program.')],
                    ['file-text', $T('Journaux d’audit', 'Audit logs'), $T('Chaque connexion et chaque action sensible est tracée et exportable.', 'Every sign-in and sensitive action is logged and exportable.')],
                    ['scale', $T('Conformité', 'Compliance'), $T('Respect des réglementations sur la protection des données, contrat de sous-traitance disponible.', 'Compliance with data protection regulations, data processing agreement available.')],
                    ['activity', $T('Surveillance 24 h/24', '24/7 monitoring'), $T('Alertes automatiques et équipe d’astreinte en permanence.', 'Automatic alerts and an on-call team at all times.')],
                    ['download', $T('Réversibilité', 'Data portability'), $T('Exportez toutes vos données à tout moment, dans des formats ouverts.', 'Export all your data at any time, in open formats.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Centre de confiance', 'Trust center'),
                    'title' => $T('La transparence, documents à l’appui', 'Transparency, with documents to back it up'),
                    'html' => '<p>'.e($T('Rapports d’audit, politique de sécurité, liste des sous-traitants et historique de disponibilité : votre équipe sécurité trouve tout ce dont elle a besoin pour valider le logiciel, et peut nous poser ses questions directement.', 'Audit reports, security policy, list of subprocessors and uptime history: your security team finds everything it needs to approve the software, and can ask us questions directly.')).'</p>',
                    'bullets' => [$T('Questionnaires de sécurité remplis sous 5 jours', 'Security questionnaires completed within 5 days'), $T('Page de statut publique', 'Public status page'), $T('Notification des incidents sous 72 heures', 'Incident notification within 72 hours')],
                    'buttons' => [[$T('Contacter l’équipe sécurité', 'Contact the security team'), $contact]],
                    'photo' => ['saas-securite', 'shield-check', $T('Salle de serveurs éclairée avec des baies alignées', 'Lit server room with rows of racks')],
                    'reverse' => true,
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions de sécurité', 'Security questions'), 'bg' => 'light', 'items' => [
                    [$T('Où sont hébergées nos données ?', 'Where is our data hosted?'), $T('Dans des centres de données certifiés ; les formules Business permettent de choisir la région d’hébergement.', 'In certified data centers; Business plans let you choose the hosting region.')],
                    [$T('Vos employés peuvent-ils voir nos projets ?', 'Can your staff see our projects?'), $T('Non, sauf à votre demande explicite pour le support, avec un accès limité dans le temps et journalisé.', 'No, except at your explicit request for support, with time-limited, logged access.')],
                    [$T('Que deviennent nos données si nous partons ?', 'What happens to our data if we leave?'), $T('Vous les exportez librement, puis elles sont supprimées de nos systèmes sous 30 jours.', 'You export them freely, then they are deleted from our systems within 30 days.')],
                ]]),
            ]];

        $pages[] = ['key' => 'customers', 'slug' => $u('clients', 'customers'), 'title' => $T('Clients', 'Customers'),
            'description' => $T('Agences, industriels, startups et associations : études de cas et résultats mesurés de nos clients qui utilisent le logiciel au quotidien.', 'Agencies, manufacturers, startups and nonprofits: case studies and measured results from customers who use the software every day.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Clients', 'Customers'), 'title' => $T('Ils ont transformé leur façon de travailler', 'They changed the way they work'), 'intro' => $T('Études de cas présentées de façon anonyme, chiffres mesurés avec nos clients.', 'Case studies presented anonymously, with figures measured alongside our customers.')]),
                B::cards($theme, ['eyebrow' => $T('Études de cas', 'Case studies'), 'title' => $T('Des résultats concrets', 'Concrete results'), 'items' => [
                    ['saas-cas-agence', 'palette', $T('Une agence de communication', 'A creative agency'), $T('80 personnes, 300 projets par an : 40 % de réunions en moins et des délais tenus.', '80 people, 300 projects a year: 40% fewer meetings and deadlines met.')],
                    ['saas-cas-industrie', 'factory', $T('Un groupe industriel', 'A manufacturing group'), $T('Maintenance de cinq usines suivie dans un seul outil, avec l’application mobile sur le terrain.', 'Maintenance across five plants tracked in one tool, with the mobile app on the floor.')],
                    ['saas-cas-startup', 'rocket', $T('Une startup en croissance', 'A fast-growing startup'), $T('De 10 à 120 salariés en deux ans, sans changer d’outil ni perdre l’historique.', 'From 10 to 120 employees in two years, without switching tools or losing history.')],
                    ['saas-cas-association', 'hand-heart', $T('Une association internationale', 'An international nonprofit'), $T('Projets dans douze pays coordonnés, rapports aux bailleurs générés en un clic.', 'Projects in twelve countries coordinated, donor reports generated in one click.')],
                    ['saas-cas-cabinet', 'briefcase', $T('Un cabinet de conseil', 'A consulting firm'), $T('Temps passé suivi par mission, facturation accélérée de dix jours.', 'Time tracked per engagement, invoicing ten days faster.')],
                    ['saas-cas-ecole', 'graduation-cap', $T('Un établissement d’enseignement', 'An educational institution'), $T('Équipes pédagogiques et administratives réunies dans un même espace.', 'Teaching and admin teams brought together in one workspace.')],
                ]]),
                M::chart($theme, [
                    'eyebrow' => $T('Impact mesuré', 'Measured impact'),
                    'title' => $T('Ce qui change après six mois', 'What changes after six months'),
                    'html' => '<p>'.e($T('Moyennes observées chez nos clients six mois après la mise en place, comparées à leur situation de départ.', 'Averages observed among our customers six months after rollout, compared with their starting point.')).'</p>',
                    'bullets' => [$T('Moins de temps en réunions de suivi', 'Less time in status meetings'), $T('Plus de projets livrés à temps', 'More projects delivered on time'), $T('Moins d’e-mails internes', 'Fewer internal emails')],
                    'chart' => ['kind' => 'bar', 'title' => $T('Évolution moyenne (%)', 'Average change (%)'), 'suffix' => ' %', 'rows' => [
                        [$T('Réunions de suivi', 'Status meetings'), -40], [$T('Projets à l’heure', 'On-time projects'), 32], [$T('E-mails internes', 'Internal emails'), -55], [$T('Satisfaction des équipes', 'Team satisfaction'), 24],
                    ]],
                    'bg' => 'light',
                ]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [4000, '+', $T('équipes', 'teams')],
                    [60, '', $T('pays', 'countries')],
                    [2, ' M', $T('tâches terminées chaque mois', 'tasks completed every month')],
                    [95, ' %', $T('de clients renouvelant leur abonnement', 'of customers renewing')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'resources', 'slug' => $u('ressources', 'resources'), 'title' => $T('Ressources', 'Resources'),
            'description' => $T('Documentation, guide de démarrage, référence de l’API, webinaires et articles du blog pour tirer le meilleur parti du logiciel.', 'Documentation, getting-started guide, API reference, webinars and blog articles to get the most out of the software for your team.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Ressources', 'Resources'), 'title' => $T('Apprendre, configurer, aller plus loin', 'Learn, set up, go further'), 'intro' => $T('Documentation, tutoriels et bonnes pratiques rédigés par notre équipe produit.', 'Documentation, tutorials and best practices written by our product team.')]),
                B::features($theme, ['eyebrow' => 'Documentation', 'title' => $T('Trouvez la bonne ressource', 'Find the right resource'), 'items' => [
                    ['rocket', $T('Guide de démarrage', 'Getting-started guide'), $T('Créer son espace, importer ses données et inviter son équipe pas à pas.', 'Create your workspace, import data and invite your team step by step.')],
                    ['book-open', $T('Centre d’aide', 'Help center'), $T('Plus de 300 articles et vidéos courtes, classés par fonctionnalité.', 'More than 300 articles and short videos, organized by feature.')],
                    ['code', $T('Référence de l’API', 'API reference'), $T('Tous les points d’accès, exemples de requêtes et environnement de test.', 'Every endpoint, sample requests and a sandbox environment.')],
                    ['layers', $T('Modèles', 'Templates'), $T('Modèles de projets et d’automatisations prêts à l’emploi.', 'Ready-made project and automation templates.')],
                    ['activity', $T('Statut du service', 'System status'), $T('Disponibilité en temps réel et historique des incidents.', 'Real-time availability and incident history.')],
                    ['refresh-cw', $T('Nouveautés', 'Changelog'), $T('Les améliorations publiées chaque semaine.', 'Improvements shipped every week.')],
                ]]),
                B::posts($theme, ['eyebrow' => 'Blog', 'title' => $T('Derniers articles', 'Latest articles'), 'source' => 'current', 'count' => 9, 'more' => $T('Lire l’article', 'Read the article'), 'bg' => 'light']),
                B::events($theme, ['icon' => 'video', 'eyebrow' => $T('Webinaires', 'Webinars'), 'title' => $T('Prochaines sessions en ligne', 'Upcoming live sessions'), 'items' => [
                    [$T('Prise en main en 45 minutes', 'Getting started in 45 minutes'), $T('Chaque mardi, 11 h', 'Every Tuesday, 11 am'), $T('En ligne', 'Online')],
                    [$T('Automatisations avancées', 'Advanced automations'), $T('Premier jeudi du mois, 16 h', 'First Thursday of the month, 4 pm'), $T('En ligne', 'Online')],
                    [$T('Rapports pour la direction', 'Reporting for leadership'), $T('Troisième mercredi du mois, 14 h', 'Third Wednesday of the month, 2 pm'), $T('En ligne', 'Online')],
                    [$T('Questions-réponses avec l’équipe produit', 'Q&A with the product team'), $T('Chaque trimestre', 'Every quarter'), $T('En ligne', 'Online')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => $T('Démo et contact', 'Demo and contact'),
            'description' => $T('Réservez une démo personnalisée de 30 minutes, démarrez votre essai gratuit ou contactez notre équipe commerciale et notre support.', 'Book a personalized 30-minute demo, start your free trial or get in touch with our sales and support teams, who reply within one business day.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Démo', 'Demo'), 'title' => $T('Voyez le logiciel en action', 'See the software in action'), 'intro' => $T('30 minutes avec un expert, adaptées à votre équipe et à vos projets.', '30 minutes with an expert, tailored to your team and projects.')]),
                B::contact($theme, [
                    'eyebrow' => $T('Demander une démo', 'Request a demo'), 'title' => $T('Parlons de vos besoins', 'Tell us what you need'),
                    'intro' => $T('Indiquez la taille de votre équipe et les outils que vous utilisez : nous préparons une démonstration sur mesure.', 'Tell us your team size and the tools you use: we will prepare a tailored demo.'),
                    'items' => [
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['clock', $T('Équipe commerciale : lundi – vendredi, 9 h – 18 h', 'Sales team: Monday – Friday, 9 am – 6 pm')],
                    ],
                    'social' => ['x', 'youtube', 'github'],
                    'subject' => true, 'button' => $T('Réserver ma démo', 'Book my demo'),
                    'bg' => 'white',
                ]),
                B::steps($theme, ['eyebrow' => $T('Déroulé', 'What to expect'), 'title' => $T('Comment se passe la démo', 'How the demo works'), 'bg' => 'light', 'items' => [
                    [$T('Échange', 'Discovery'), $T('10 minutes pour comprendre votre organisation et vos difficultés.', '10 minutes to understand your organization and pain points.')],
                    [$T('Démonstration', 'Walkthrough'), $T('Le logiciel configuré avec un exemple proche de vos projets.', 'The software set up with an example close to your projects.')],
                    [$T('Questions', 'Questions'), $T('Sécurité, intégrations, tarifs : toutes vos questions.', 'Security, integrations, pricing: all your questions.')],
                    [$T('Essai', 'Trial'), $T('Votre espace d’essai prêt, avec un accompagnement personnalisé.', 'Your trial workspace ready, with personal onboarding.')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Besoin d’aide ?', 'Need help?'), 'title' => $T('Le bon contact pour chaque question', 'The right contact for every question'), 'bg' => 'white', 'groups' => [
                    [$T('Clients', 'Customers'), [
                        ['message-circle', $T('Chat dans l’application, réponse en moins de 10 minutes', 'In-app chat, reply in under 10 minutes')],
                        ['book-open', $T('Centre d’aide ouvert 24 h/24', 'Help center open 24/7')],
                        ['activity', $T('Page de statut du service', 'Service status page')],
                    ]],
                    [$T('Entreprises et partenaires', 'Companies and partners'), [
                        ['briefcase', $T('Offres Entreprise et achats publics', 'Enterprise offers and public procurement')],
                        ['handshake', $T('Programme partenaires et revendeurs', 'Partner and reseller program')],
                        ['shield-check', $T('Questionnaires de sécurité', 'Security questionnaires')],
                    ]],
                ]]),
            ]];

        $blog = [
            'base' => $u('ressources', 'resources'),
            'title' => $T('Ressources', 'Resources'),
            'categories' => ['produit' => $T('Produit', 'Product'), 'methodes' => $T('Méthodes de travail', 'Ways of working'), 'securite' => $T('Sécurité', 'Security')],
            'posts' => [
                ['key' => 'automatisations', 'slug' => $u('cinq-automatisations', 'five-automations'), 'category' => 'produit', 'tags' => [$T('Automatisations', 'Automations')],
                    'title' => $T('Cinq automatisations à activer dès le premier jour', 'Five automations to turn on from day one'),
                    'excerpt' => $T('Relances, affectations, rapports : les règles qui font gagner des heures chaque semaine.', 'Reminders, assignments, reports: the rules that save hours every week.'),
                    'photo' => ['saas-blog-automatisations', 'workflow', $T('Personne travaillant sur un ordinateur portable avec un carnet et une tasse de café', 'Person working on a laptop with a notebook and a cup of coffee')],
                    'heading' => $T('Notre sélection', 'Our picks'),
                    'body' => [
                        $T('Relancez automatiquement la personne responsable deux jours avant une échéance, et prévenez le chef de projet si la tâche est en retard.', 'Automatically remind the owner two days before a deadline, and alert the project manager if the task is late.'),
                        $T('Affectez les nouvelles demandes selon leur catégorie, et déplacez une tâche dans la colonne suivante dès qu’elle est validée.', 'Assign new requests by category, and move a task to the next column as soon as it is approved.'),
                        $T('Enfin, programmez l’envoi d’un rapport hebdomadaire : plus besoin de réunion pour savoir où en sont les projets.', 'Finally, schedule a weekly report: no more meetings just to know where projects stand.'),
                    ]],
                ['key' => 'reunions', 'slug' => $u('moins-de-reunions', 'fewer-meetings'), 'category' => 'methodes', 'tags' => [$T('Productivité', 'Productivity')],
                    'title' => $T('Diviser par deux les réunions de suivi', 'Cutting status meetings in half'),
                    'excerpt' => $T('Remplacer les points d’avancement par un suivi asynchrone, sans perdre en visibilité.', 'Replacing status updates with async tracking, without losing visibility.'),
                    'photo' => ['saas-blog-reunions', 'users', $T('Petite équipe échangeant debout devant un tableau couvert de notes', 'Small team talking in front of a board covered with sticky notes')],
                    'quote' => $T('« Une réunion de suivi est souvent un tableau de bord que personne n’a ouvert. »', '“A status meeting is often a dashboard nobody opened.”'),
                    'body' => [
                        $T('Demandez à chacun de mettre à jour ses tâches avant midi le lundi : statut, blocages et prochaines étapes.', 'Ask everyone to update their tasks by noon on Monday: status, blockers and next steps.'),
                        $T('Le tableau de bord partagé fait le reste. Gardez la réunion pour les décisions, pas pour l’information.', 'The shared dashboard does the rest. Keep meetings for decisions, not information.'),
                        $T('Nos clients qui appliquent cette règle réduisent en moyenne de 40 % le temps passé en réunion.', 'Customers who follow this rule cut time spent in meetings by 40% on average.'),
                    ]],
                ['key' => 'sso', 'slug' => $u('pourquoi-activer-le-sso', 'why-turn-on-sso'), 'category' => 'securite', 'tags' => ['SSO'],
                    'title' => $T('Pourquoi activer l’authentification unique', 'Why turn on single sign-on'),
                    'excerpt' => $T('Moins de mots de passe, des départs mieux gérés, une sécurité renforcée.', 'Fewer passwords, smoother offboarding, stronger security.'),
                    'photo' => ['saas-blog-sso', 'lock', $T('Écran de connexion sécurisée sur un ordinateur portable', 'Secure sign-in screen on a laptop')],
                    'heading' => $T('Les bénéfices', 'The benefits'),
                    'body' => [
                        $T('Avec l’authentification unique, vos collaborateurs se connectent avec leur compte d’entreprise : un mot de passe de moins à retenir, et à voler.', 'With single sign-on, your staff sign in with their company account: one less password to remember, or to steal.'),
                        $T('Quand une personne quitte l’entreprise, son accès est coupé partout en une seule action.', 'When someone leaves the company, their access is cut everywhere in a single action.'),
                        $T('La configuration prend une quinzaine de minutes avec notre guide pas à pas.', 'Setup takes about fifteen minutes with our step-by-step guide.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Fonctionnalités', 'Features'), 'url' => $features],
                    ['label' => $T('Intégrations', 'Integrations'), 'url' => $integrations],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('Sécurité', 'Security'), 'url' => $security],
                    ['label' => $T('Clients', 'Customers'), 'url' => $customers],
                    ['label' => $T('Ressources', 'Resources'), 'url' => $resources],
                ],
                'cta_label' => $T('Demander une démo', 'Request a demo'),
                'cta_url' => $contact,
                'footer_text' => $T('Le logiciel en ligne qui réunit projets, tâches, automatisations et rapports de toutes vos équipes.', 'The online software that brings projects, tasks, automations and reports together for all your teams.'),
                'footer_links' => [
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('Sécurité', 'Security'), 'url' => $security],
                    ['label' => 'Documentation', 'url' => $resources],
                    ['label' => $T('Démo et contact', 'Demo and contact'), 'url' => $contact],
                ],
            ],
        ];
    },
];
