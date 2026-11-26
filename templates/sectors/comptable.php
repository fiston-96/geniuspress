<?php

/*
 * Pack « Cabinet d'expertise comptable » (secteur Cabinets et services
 * professionnels) : 8 pages, en français et en anglais. Tenue comptable,
 * fiscalité, paie, création d'entreprise, offres mensuelles et outils en ligne.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du cabinet). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Cabinets et services professionnels', 'en' => 'Professional services firms'],
        'icon' => 'scale',
        'keywords' => ['expert-comptable', 'comptabilité', 'cabinet comptable', 'fiscalité', 'paie', 'bilan', 'création d’entreprise', 'TVA', 'gestion', 'accountant', 'accounting firm', 'bookkeeping', 'tax', 'payroll', 'CPA', 'small business', 'financial statements'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $services = $u('/missions', '/services');
        $startup = $u('/creation-entreprise', '/start-a-business');
        $plans = $u('/offres', '/plans');
        $tools = $u('/outils-en-ligne', '/online-tools');
        $firm = $u('/le-cabinet', '/about-us');
        $resources = $u('/ressources', '/resources');
        $contact = '/contact';
        $cur = $T('€', '$');

        $missions = [
            ['book-open', $T('Tenue comptable', 'Bookkeeping'), $T('Saisie, rapprochements bancaires et comptes annuels, à partir de vos justificatifs numérisés.', 'Data entry, bank reconciliations and year-end accounts, from your scanned receipts.')],
            ['calculator', $T('Fiscalité', 'Tax'), $T('Déclarations de TVA et d’impôt sur les bénéfices, optimisation légale et assistance en cas de contrôle.', 'Sales tax and income tax returns, lawful tax planning and support during audits.')],
            ['wallet', $T('Paie et social', 'Payroll and HR'), $T('Bulletins de salaire, déclarations sociales, contrats et soldes de tout compte.', 'Payslips, payroll tax filings, employment contracts and final pay.')],
            ['rocket', $T('Création d’entreprise', 'Business formation'), $T('Choix du statut, prévisionnel financier, formalités et premier rendez-vous bancaire.', 'Choice of legal structure, financial forecast, formalities and first bank meeting.')],
            ['chart-line', $T('Pilotage et conseil', 'Advisory and reporting'), $T('Tableaux de bord mensuels, trésorerie prévisionnelle et aide à la décision.', 'Monthly dashboards, cash-flow forecasts and decision support.')],
            ['clipboard-check', $T('Juridique annuel', 'Annual corporate filings'), $T('Approbation des comptes, assemblées générales et dépôts obligatoires.', 'Approval of accounts, annual meetings and mandatory filings.')],
        ];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Des dirigeants plus sereins', 'Business owners with peace of mind'), 'rating' => 5, 'items' => [
            [$T('Je photographie mes tickets, le cabinet s’occupe du reste. Mon bilan est prêt deux mois plus tôt qu’avant.', 'I snap photos of my receipts and the firm does the rest. My year-end accounts are ready two months earlier than before.'), '[Prénom Nom]', $T('Gérante, salon de coiffure', 'Owner, hair salon')],
            [$T('Un interlocuteur unique qui connaît mon entreprise et me prévient avant chaque échéance fiscale.', 'A single point of contact who knows my business and warns me before every tax deadline.'), '[Prénom Nom]', $T('Fondateur, agence de voyages', 'Founder, travel agency')],
            [$T('Ils ont repris notre paie de douze salariés en un mois, sans une seule erreur sur les bulletins.', 'They took over payroll for our twelve employees within a month, without a single payslip error.'), '[Prénom Nom]', $T('Directrice, PME du bâtiment', 'Director, construction company')],
        ]];
        $cta = ['title' => $T('Recevez votre devis en 24 heures', 'Get your quote within 24 hours'), 'text' => $T('Indiquez votre activité et votre chiffre d’affaires : nous vous proposons une offre claire, sans engagement.', 'Tell us about your business and revenue: we will send you a clear, no-obligation offer.'),
            'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact, 'accent'], [$T('Voir les offres', 'See our plans'), $plans, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Cabinet d’expertise comptable pour TPE, PME, indépendants et créateurs : comptabilité, fiscalité, paie et outils en ligne, à prix mensuel fixe.', 'Accounting firm for small businesses, freelancers and founders: bookkeeping, tax, payroll and online tools for a fixed monthly fee.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Expertise comptable', 'Accounting firm'),
                    'title' => $T('Votre comptabilité à jour, vos décisions éclairées', 'Your books up to date, your decisions well informed'),
                    'text' => $T('Un expert-comptable dédié, des outils en ligne pour gagner du temps et un forfait mensuel connu d’avance : vous vous concentrez sur votre métier.', 'A dedicated accountant, online tools that save you time and a fixed monthly fee: you focus on running your business.'),
                    'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact], [$T('Nos offres', 'Our plans'), $plans]],
                    'photo' => ['comptable-accueil', 'calculator', $T('Expert-comptable présentant un tableau de bord financier à une cheffe d’entreprise', 'Accountant showing a financial dashboard to a business owner')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos missions', 'What we do'), 'title' => $T('Tout ce qu’un dirigeant doit déléguer', 'Everything a business owner should delegate'), 'cols' => 3, 'items' => array_map(fn ($m) => [...$m, $services, $T('En savoir plus', 'Learn more')], array_slice($missions, 0, 3))]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [400, '+', $T('entreprises clientes', 'client businesses')],
                    [15, '', $T('collaborateurs', 'team members')],
                    [5000, '+', $T('bulletins de paie par an', 'payslips a year')],
                    [98, ' %', $T('de déclarations déposées en avance', 'of returns filed early')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Outils en ligne', 'Online tools'),
                    'title' => $T('Votre cabinet dans votre poche', 'Your accounting firm in your pocket'),
                    'html' => '<p>'.e($T('Déposez vos factures depuis votre téléphone, suivez votre trésorerie en temps réel et retrouvez tous vos documents dans un espace sécurisé. Votre banque est synchronisée chaque jour.', 'Upload invoices from your phone, track your cash in real time and find all your documents in a secure portal. Your bank feed syncs every day.')).'</p>',
                    'bullets' => [$T('Justificatifs photographiés et classés automatiquement', 'Receipts photographed and filed automatically'), $T('Tableau de bord mis à jour chaque semaine', 'Dashboard updated every week'), $T('Signature électronique des documents', 'Electronic signature of documents')],
                    'buttons' => [[$T('Découvrir les outils', 'Explore the tools'), $tools, 'outline']],
                    'photo' => ['comptable-application', 'smartphone', $T('Commerçante photographiant une facture avec son téléphone au comptoir de sa boutique', 'Shop owner photographing an invoice with her phone at the counter')],
                    'reverse' => true,
                ]),
                B::steps($theme, ['eyebrow' => $T('Changer d’expert-comptable', 'Switching accountants'), 'title' => $T('Nous nous occupons de tout', 'We handle everything'), 'items' => [
                    [$T('Rendez-vous découverte', 'Discovery call'), $T('Trente minutes pour comprendre votre activité et vos besoins.', 'Thirty minutes to understand your business and your needs.')],
                    [$T('Proposition', 'Proposal'), $T('Une lettre de mission avec un forfait mensuel détaillé.', 'An engagement letter with a detailed monthly fee.')],
                    [$T('Reprise du dossier', 'Handover'), $T('Nous contactons votre ancien cabinet et récupérons vos comptes.', 'We contact your previous firm and retrieve your records.')],
                    [$T('Mise en route', 'Getting started'), $T('Accès à vos outils en ligne et premier point mensuel.', 'Access to your online tools and first monthly review.')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('missions', 'services'), 'title' => $T('Nos missions', 'Our services'),
            'description' => $T('Tenue comptable, fiscalité, paie, conseil de gestion et juridique annuel : les missions de notre cabinet d’expertise comptable en détail.', 'Bookkeeping, tax, payroll, management advice and annual filings: our accounting firm’s services in detail.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nos missions', 'Our services'), 'title' => $T('De la saisie au conseil stratégique', 'From data entry to strategic advice'), 'intro' => $T('Choisissez les missions dont vous avez besoin : nous les assemblons dans un forfait unique.', 'Pick the services you need: we combine them into a single monthly fee.')]),
                B::features($theme, ['items' => $missions, 'cols' => 3, 'bg' => 'light']),
                B::split($theme, [
                    'eyebrow' => $T('Comptabilité et fiscalité', 'Accounting and tax'),
                    'title' => $T('Des comptes justes, des échéances tenues', 'Accurate books, deadlines met'),
                    'html' => '<p>'.e($T('Nous tenons votre comptabilité au fil de l’eau, préparons vos déclarations et établissons vos comptes annuels. Chaque clôture se termine par un rendez-vous de bilan pour comprendre vos chiffres et préparer l’année suivante.', 'We keep your books on an ongoing basis, prepare your tax returns and produce your annual accounts. Every year-end closes with a review meeting to understand your numbers and plan the year ahead.')).'</p>',
                    'bullets' => [$T('Calendrier fiscal suivi pour vous', 'Tax calendar tracked for you'), $T('Révision des comptes par un expert-comptable', 'Accounts reviewed by a certified accountant'), $T('Assistance en cas de contrôle fiscal', 'Support in the event of a tax audit')],
                    'photo' => ['comptable-bilan', 'chart-pie', $T('Comptable vérifiant des états financiers imprimés avec une calculatrice', 'Accountant checking printed financial statements with a calculator')],
                ]),
                B::split($theme, [
                    'eyebrow' => $T('Paie et ressources humaines', 'Payroll and HR'),
                    'title' => $T('Vos salariés payés juste et à l’heure', 'Your employees paid correctly and on time'),
                    'html' => '<p>'.e($T('Embauche, bulletins, absences, déclarations sociales, fin de contrat : nous gérons la paie de 1 à 100 salariés et répondons aux questions de vos équipes.', 'Hiring, payslips, leave, payroll filings, end of contract: we run payroll for 1 to 100 employees and answer your team’s questions.')).'</p>',
                    'bullets' => [$T('Bulletins dématérialisés', 'Digital payslips'), $T('Veille sur les évolutions sociales', 'Monitoring of employment regulation changes'), $T('Rédaction des contrats de travail', 'Drafting of employment contracts')],
                    'buttons' => [[$T('Voir les offres', 'See our plans'), $plans]],
                    'photo' => ['comptable-paie', 'users', $T('Gestionnaire de paie travaillant sur deux écrans dans un bureau moderne', 'Payroll specialist working on two screens in a modern office')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'buttons' => [[$T('Poser une question', 'Ask a question'), $contact, 'outline']], 'items' => [
                    [$T('Travaillez-vous avec tous les secteurs d’activité ?', 'Do you work with every industry?'), $T('Oui : commerce, services, artisanat, professions libérales, associations. Certains collaborateurs sont spécialisés par secteur.', 'Yes: retail, services, trades, professionals, nonprofits. Some of our team specialize by industry.')],
                    [$T('Dois-je garder mes justificatifs papier ?', 'Do I need to keep paper receipts?'), $T('Une fois numérisés dans votre espace, ils sont archivés de façon sécurisée. Les durées de conservation légales dépendent de votre pays : nous vous les indiquons.', 'Once scanned into your portal, they are securely archived. Legal retention periods depend on your country: we will tell you what applies.')],
                    [$T('Qui sera mon interlocuteur ?', 'Who will be my contact?'), $T('Un collaborateur comptable dédié, supervisé par un expert-comptable diplômé qui signe vos comptes.', 'A dedicated accountant, supervised by a certified accountant who signs off your accounts.')],
                    [$T('Pouvez-vous intervenir en cours d’année ?', 'Can you start mid-year?'), $T('Oui, nous reprenons votre dossier à tout moment, y compris juste avant une clôture.', 'Yes, we can take over your file at any time, including right before year-end.')],
                ]]),
            ]];

        $pages[] = ['key' => 'startup', 'slug' => $u('creation-entreprise', 'start-a-business'), 'title' => $T('Création d’entreprise', 'Starting a business'),
            'description' => $T('Choix du statut, prévisionnel, formalités et accompagnement de la première année : créez votre entreprise avec un expert-comptable.', 'Choosing a structure, financial forecast, formalities and first-year support: start your business with an accountant.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Création d’entreprise', 'Starting a business'), 'title' => $T('Lancez votre activité sur de bonnes bases', 'Launch your business on solid ground'), 'intro' => $T('Statut, chiffres, démarches : un accompagnement concret, de l’idée au premier bilan.', 'Structure, numbers, paperwork: hands-on support from the idea to your first year-end.')]),
                B::steps($theme, ['eyebrow' => $T('Le parcours', 'The journey'), 'title' => $T('Cinq étapes pour créer', 'Five steps to get started'), 'items' => [
                    [$T('Votre projet', 'Your project'), $T('Activité, clients visés, besoins de départ.', 'Business, target customers, start-up needs.')],
                    [$T('Le prévisionnel', 'The forecast'), $T('Chiffre d’affaires, charges et trésorerie sur trois ans.', 'Revenue, costs and cash over three years.')],
                    [$T('Le statut', 'The structure'), $T('Entreprise individuelle ou société : la forme la plus adaptée.', 'Sole proprietorship or company: the best fit.')],
                    [$T('Les formalités', 'The formalities'), $T('Statuts, immatriculation, compte bancaire professionnel.', 'Articles, registration, business bank account.')],
                    [$T('La première année', 'The first year'), $T('Mise en place des outils et points réguliers.', 'Tools set up and regular check-ins.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Le prévisionnel', 'The forecast'),
                    'title' => $T('Des chiffres solides pour convaincre votre banque', 'Solid numbers to convince your bank'),
                    'html' => '<p>'.e($T('Un prévisionnel réaliste est la meilleure assurance contre les mauvaises surprises. Nous le construisons avec vous, hypothèse par hypothèse, et vous préparons au rendez-vous avec votre banquier ou vos investisseurs.', 'A realistic forecast is the best protection against bad surprises. We build it with you, assumption by assumption, and prepare you for meetings with your banker or investors.')).'</p>',
                    'bullets' => [$T('Plan de financement et seuil de rentabilité', 'Financing plan and break-even point'), $T('Simulation de votre rémunération', 'Simulation of your own pay'), $T('Dossier de prêt prêt à présenter', 'Loan application ready to present')],
                    'photo' => ['comptable-creation', 'rocket', $T('Deux jeunes entrepreneurs étudiant un plan d’affaires avec leur conseillère', 'Two young entrepreneurs going over a business plan with their adviser')],
                    'bg' => 'light',
                ]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Packs création', 'Formation packages'), 'title' => $T('Un prix fixe pour démarrer', 'A fixed price to get started'), 'bg' => 'white', 'items' => [
                    [$T('Indépendant', 'Freelancer'), '290', $cur, $T('forfait', 'flat fee'), $T('Pour une activité en solo.', 'For a one-person business.'), [$T('Choix du régime fiscal', 'Choice of tax regime'), $T('Immatriculation', 'Registration'), $T('Prise en main des outils', 'Onboarding to our tools'), '-'.$T('Prévisionnel sur trois ans', 'Three-year forecast')], $T('Démarrer', 'Get started')],
                    [$T('Société', 'Company'), '690', $cur, $T('forfait', 'flat fee'), $T('Pour une société à un ou plusieurs associés.', 'For a company with one or more partners.'), [$T('Prévisionnel sur trois ans', 'Three-year forecast'), $T('Rédaction des statuts', 'Drafting of the articles'), $T('Formalités d’immatriculation', 'Registration formalities'), $T('Trois mois de comptabilité offerts', 'Three months of bookkeeping included')], $T('Créer ma société', 'Form my company'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Reprise', 'Acquisition'), $T('Sur devis', 'Quote'), '', '', $T('Pour racheter une entreprise ou un fonds.', 'To buy a business or its assets.'), [$T('Analyse des comptes de la cible', 'Review of the target’s accounts'), $T('Évaluation et plan de financement', 'Valuation and financing plan'), $T('Accompagnement à la négociation', 'Support during negotiation'), $T('Frais officiels en sus', 'Official fees extra')], $T('Nous consulter', 'Talk to us')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Avant de vous lancer', 'Before you start'), 'bg' => 'light', 'items' => [
                    [$T('Faut-il un expert-comptable pour créer son entreprise ?', 'Do I need an accountant to start a business?'), $T('Ce n’est pas toujours obligatoire, mais le choix du statut et du régime fiscal a des effets pendant des années. Un conseil au départ évite des erreurs coûteuses.', 'It is not always mandatory, but your choice of structure and tax regime has effects for years. Advice at the outset avoids costly mistakes.')],
                    [$T('Combien de temps faut-il pour créer une société ?', 'How long does it take to form a company?'), $T('Comptez en général de deux à quatre semaines selon votre pays, la forme choisie et le délai de dépôt du capital.', 'Usually two to four weeks depending on your country, the chosen structure and the time to deposit the capital.')],
                    [$T('Puis-je changer de statut plus tard ?', 'Can I change structure later?'), $T('Oui, une entreprise peut évoluer. Nous vous indiquons le bon moment et le coût de la transformation.', 'Yes, a business can evolve. We tell you the right time and the cost of converting.')],
                ]]),
            ]];

        $pages[] = ['key' => 'plans', 'slug' => $u('offres', 'plans'), 'title' => $T('Offres', 'Plans'),
            'description' => $T('Offres mensuelles d’expertise comptable : Essentiel, Sérénité et Croissance. Prix fixe, sans engagement de durée, outils en ligne inclus.', 'Monthly accounting plans: Essential, Peace of mind and Growth. Fixed price, no long-term commitment, online tools included.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Offres', 'Plans'), 'title' => $T('Un forfait mensuel, tout compris', 'One all-inclusive monthly fee'), 'intro' => $T('Le prix dépend de votre activité et du nombre de pièces, jamais du temps passé au téléphone.', 'The price depends on your business and transaction volume, never on time spent on the phone.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Essentiel', 'Essential'), '89', $cur, $T('/ mois', '/ month'), $T('Indépendants et micro-entreprises.', 'Freelancers and micro-businesses.'), [$T('Tenue comptable et comptes annuels', 'Bookkeeping and annual accounts'), $T('Déclarations fiscales', 'Tax returns'), $T('Application et banque synchronisée', 'App and bank feed'), '-'.$T('Paie', 'Payroll')], $T('Choisir Essentiel', 'Choose Essential')],
                    [$T('Sérénité', 'Peace of mind'), '190', $cur, $T('/ mois', '/ month'), $T('TPE avec quelques salariés.', 'Small businesses with a few employees.'), [$T('Tout Essentiel', 'Everything in Essential'), $T('Paie jusqu’à 5 salariés', 'Payroll up to 5 employees'), $T('Tableau de bord trimestriel', 'Quarterly dashboard'), $T('Juridique annuel inclus', 'Annual corporate filings included')], $T('Choisir Sérénité', 'Choose Peace of mind'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Croissance', 'Growth'), '390', $cur, $T('/ mois', '/ month'), $T('PME qui veulent piloter finement.', 'Growing businesses that want close financial control.'), [$T('Tout Sérénité', 'Everything in Peace of mind'), $T('Paie jusqu’à 20 salariés', 'Payroll up to 20 employees'), $T('Situation mensuelle et trésorerie prévisionnelle', 'Monthly reporting and cash-flow forecast'), $T('Rendez-vous de conseil chaque trimestre', 'Quarterly advisory meeting')], $T('Choisir Croissance', 'Choose Growth')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Comparatif', 'Comparison'), 'title' => $T('Ce que comprend chaque offre', 'What each plan includes'), 'bg' => 'light', 'rows' => [
                    ['', $T('Essentiel', 'Essential'), $T('Sérénité', 'Peace of mind'), $T('Croissance', 'Growth')],
                    [$T('Comptes annuels', 'Annual accounts'), '✓', '✓', '✓'],
                    [$T('Déclarations de TVA', 'Sales tax returns'), '✓', '✓', '✓'],
                    [$T('Bulletins de paie', 'Payslips'), '—', $T('jusqu’à 5', 'up to 5'), $T('jusqu’à 20', 'up to 20')],
                    [$T('Tableau de bord', 'Dashboard'), $T('annuel', 'yearly'), $T('trimestriel', 'quarterly'), $T('mensuel', 'monthly')],
                    [$T('Rendez-vous conseil', 'Advisory meetings'), $T('1 par an', '1 a year'), $T('2 par an', '2 a year'), $T('4 par an', '4 a year')],
                ], 'note' => $T('Prix hors taxes, indicatifs. Bulletin supplémentaire : 25 € par mois.', 'Indicative prices before taxes. Additional payslip: $25 per month.')]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur nos offres', 'Questions about our plans'), 'items' => [
                    [$T('Y a-t-il une durée d’engagement ?', 'Is there a minimum term?'), $T('Non. L’offre est mensuelle et résiliable avec un préavis de trois mois, le temps de transmettre votre dossier proprement.', 'No. Plans are monthly and can be cancelled with three months’ notice, so your file can be handed over properly.')],
                    [$T('Comment se fait le paiement ?', 'How do I pay?'), $T('Par prélèvement, carte bancaire, virement ou paiement mobile, chaque mois. Une facture détaillée est disponible dans votre espace.', 'By direct debit, card, bank transfer or mobile payment, every month. A detailed invoice is available in your portal.')],
                    [$T('Que se passe-t-il si mon activité grandit ?', 'What if my business grows?'), $T('Vous changez d’offre à tout moment ; le nouveau prix s’applique le mois suivant.', 'You can switch plans at any time; the new price applies from the following month.')],
                    [$T('Les frais de logiciel sont-ils inclus ?', 'Is software included?'), $T('Oui, l’application, l’espace client et la synchronisation bancaire sont compris dans toutes les offres.', 'Yes, the app, the client portal and the bank feed are included in every plan.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'tools', 'slug' => $u('outils-en-ligne', 'online-tools'), 'title' => $T('Outils en ligne', 'Online tools'),
            'description' => $T('Application mobile, espace client sécurisé, banque synchronisée et tableau de bord : les outils en ligne inclus dans nos offres comptables.', 'Mobile app, secure client portal, bank feed and dashboard: the online tools included in our accounting plans.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Outils en ligne', 'Online tools'), 'title' => $T('Moins de papier, plus de visibilité', 'Less paperwork, more visibility'), 'intro' => $T('Des outils simples, inclus dans chaque offre, pour échanger avec le cabinet en quelques clics.', 'Simple tools, included in every plan, to work with the firm in a few clicks.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['camera', $T('Justificatifs en photo', 'Receipts by photo'), $T('Photographiez vos tickets et factures : ils sont lus et classés automatiquement.', 'Snap your receipts and invoices: they are read and filed automatically.')],
                    ['landmark', $T('Banque synchronisée', 'Bank feed'), $T('Vos opérations bancaires remontent chaque jour, sans ressaisie.', 'Your bank transactions come in every day, with no manual entry.')],
                    ['chart-column', $T('Tableau de bord', 'Dashboard'), $T('Chiffre d’affaires, marge et trésorerie, toujours à jour.', 'Revenue, margin and cash, always up to date.')],
                    ['file-text', $T('Factures et devis', 'Invoices and quotes'), $T('Créez vos factures à vos couleurs et suivez les paiements.', 'Create branded invoices and track payments.')],
                    ['folder', $T('Coffre-fort numérique', 'Digital vault'), $T('Bilans, déclarations et bulletins archivés et accessibles à tout moment.', 'Accounts, returns and payslips archived and available at any time.')],
                    ['message-square', $T('Messagerie avec le cabinet', 'Messaging with the firm'), $T('Une question ? Votre comptable vous répond directement dans l’application.', 'A question? Your accountant answers you right in the app.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Tableau de bord', 'Dashboard'),
                    'title' => $T('Vos chiffres clés en un coup d’œil', 'Your key figures at a glance'),
                    'html' => '<p>'.e($T('Plus besoin d’attendre le bilan pour savoir où vous en êtes. Votre tableau de bord compare chaque mois à l’année précédente et vous alerte en cas de tension de trésorerie.', 'No need to wait for year-end to know where you stand. Your dashboard compares each month with last year and alerts you to cash-flow pressure.')).'</p>',
                    'bullets' => [$T('Disponible sur ordinateur et téléphone', 'Available on desktop and mobile'), $T('Alertes avant chaque échéance', 'Alerts before every deadline'), $T('Export pour votre banque', 'Export for your bank')],
                    'photo' => ['comptable-tableau-de-bord', 'chart-line', $T('Écran d’ordinateur affichant des graphiques de trésorerie sur un bureau', 'Computer screen showing cash-flow charts on a desk')],
                    'bg' => 'light',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Sécurité', 'Security'), 'title' => $T('Vos données sont protégées', 'Your data is protected'), 'items' => [
                    ['lock', $T('Chiffrement', 'Encryption'), $T('Échanges et documents chiffrés, de bout en bout.', 'Exchanges and documents encrypted end to end.')],
                    ['server', $T('Hébergement sécurisé', 'Secure hosting'), $T('Serveurs redondants et sauvegardes quotidiennes.', 'Redundant servers and daily backups.')],
                    ['key', $T('Accès maîtrisés', 'Controlled access'), $T('Double authentification et droits par utilisateur.', 'Two-factor authentication and per-user permissions.')],
                ]]),
                B::cta($theme, ['title' => $T('Essayez l’espace client', 'Try the client portal'), 'text' => $T('Demandez une démonstration de quinze minutes : nous vous montrons les outils sur un dossier d’exemple.', 'Request a fifteen-minute demo: we will walk you through the tools on a sample file.'),
                    'buttons' => [[$T('Demander une démo', 'Request a demo'), $contact, 'accent'], [$T('Voir les offres', 'See our plans'), $plans, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'firm', 'slug' => $u('le-cabinet', 'about-us'), 'title' => $T('Le cabinet', 'About us'),
            'description' => $T('Notre cabinet d’expertise comptable : histoire, engagements, équipe d’experts-comptables et de gestionnaires de paie à votre service.', 'Our accounting firm: our story, commitments and team of accountants and payroll specialists at your service.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Le cabinet', 'About us'), 'title' => $T('Des experts-comptables proches de vous', 'Accountants who stay close to you'), 'intro' => $T('Une équipe à taille humaine, des méthodes modernes et une vraie disponibilité.', 'A close-knit team, modern methods and genuine availability.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Le chiffre au service des entrepreneurs', 'Numbers in the service of entrepreneurs'),
                    'html' => '<p>'.e($T('Fondé par une experte-comptable passée par l’audit et la direction financière, le cabinet accompagne aujourd’hui plus de 400 entreprises. Notre conviction : un bon comptable ne se contente pas de produire un bilan, il aide à décider.', 'Founded by a certified accountant with a background in audit and corporate finance, the firm now supports more than 400 businesses. Our belief: a good accountant does not just produce year-end accounts, they help you make decisions.')).'</p><p>'.e($T('Cabinet inscrit à l’ordre des experts-comptables sous le numéro [N° d’inscription].', 'Firm registered with the professional accounting body under number [Registration no.].')).'</p>',
                    'photo' => ['comptable-bureaux', 'building-2', $T('Open space lumineux d’un cabinet comptable avec des collaborateurs au travail', 'Bright open-plan office of an accounting firm with staff at work')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Ce que vous pouvez attendre de nous', 'What you can expect from us'), 'bg' => 'light', 'items' => [
                    ['clock', $T('Réponse sous 24 h', 'Reply within 24 hours'), $T('Chaque question reçoit une réponse le jour ouvré suivant.', 'Every question is answered by the next business day.')],
                    ['calendar-check', $T('Zéro retard', 'No late filings'), $T('Vos déclarations sont déposées avant l’échéance, toujours.', 'Your returns are always filed before the deadline.')],
                    ['eye', $T('Prix transparent', 'Transparent pricing'), $T('Un forfait mensuel connu d’avance, sans frais cachés.', 'A monthly fee known in advance, with no hidden costs.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Vos interlocuteurs', 'Your contacts'), 'items' => [
                    ['[Prénom Nom]', $T('Experte-comptable, associée fondatrice', 'Certified accountant, founding partner'), 'comptable-equipe-1'],
                    ['[Prénom Nom]', $T('Expert-comptable, responsable conseil', 'Certified accountant, head of advisory'), 'comptable-equipe-2'],
                    ['[Prénom Nom]', $T('Responsable paie et social', 'Head of payroll and HR'), 'comptable-equipe-3'],
                    ['[Prénom Nom]', $T('Collaboratrice comptable', 'Staff accountant'), 'comptable-equipe-4'],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'resources', 'slug' => $u('ressources', 'resources'), 'title' => $T('Ressources', 'Resources'),
            'description' => $T('Guides pratiques de gestion, de fiscalité et de création d’entreprise, et webinaires gratuits animés par nos experts-comptables.', 'Practical guides on management, tax and starting a business, and free webinars hosted by our accountants.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Ressources', 'Resources'), 'title' => $T('Gérer son entreprise, simplement', 'Running your business, simply'), 'intro' => $T('Conseils et guides rédigés par l’équipe. Les règles varient selon les pays : parlez-en à votre comptable.', 'Tips and guides written by our team. Rules vary by country: talk to your accountant.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire le guide', 'Read the guide')]),
                B::events($theme, ['icon' => 'video', 'eyebrow' => $T('Webinaires', 'Webinars'), 'title' => $T('Prochains webinaires gratuits', 'Upcoming free webinars'), 'bg' => 'light', 'items' => [
                    [$T('Préparer sa clôture annuelle sans stress', 'Preparing for year-end without stress'), $T('Jeudi 11 décembre, 12 h 30', 'Thursday, December 11, 12:30 pm'), $T('En ligne, 45 minutes', 'Online, 45 minutes')],
                    [$T('Se verser un salaire ou des dividendes ?', 'Salary or dividends: how to pay yourself?'), $T('Mardi 20 janvier, 18 h', 'Tuesday, January 20, 6 pm'), $T('En ligne, 1 heure', 'Online, 1 hour')],
                    [$T('Lire son bilan en 30 minutes', 'Read your balance sheet in 30 minutes'), $T('Jeudi 12 février, 12 h 30', 'Thursday, February 12, 12:30 pm'), $T('En ligne', 'Online')],
                    [$T('Créer son entreprise : les bons choix', 'Starting a business: the right choices'), $T('Samedi 7 mars, 10 h', 'Saturday, March 7, 10 am'), $T('Au cabinet', 'At the firm')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez notre cabinet d’expertise comptable pour un devis gratuit en 24 heures, un rendez-vous au cabinet ou en visioconférence.', 'Contact our accounting firm for a free quote within 24 hours, a meeting at the office or by video call.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons de votre entreprise', 'Let’s talk about your business'), 'intro' => $T('Devis gratuit sous 24 heures, rendez-vous au cabinet ou en visioconférence.', 'Free quote within 24 hours, meetings at the office or by video call.')]),
                B::contact($theme, [
                    'title' => $T('Demander un devis', 'Request a quote'), 'intro' => $T('Précisez votre activité, votre chiffre d’affaires approximatif et le nombre de salariés.', 'Tell us your industry, approximate revenue and number of employees.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h 30 – 18 h', 'Monday – Friday, 8:30 am – 6 pm')],
                    ],
                    'social' => ['facebook', 'instagram', 'whatsapp'],
                    'subject' => true,
                    'button' => $T('Recevoir mon devis', 'Get my quote'),
                    'bg' => 'white',
                ]),
                B::schedule($theme, ['title' => $T('Bon à savoir', 'Good to know'), 'groups' => [
                    [$T('Périodes chargées', 'Busy periods'), [
                        ['calendar-days', $T('Clôtures annuelles : prenez rendez-vous tôt', 'Year-end closings: book early')],
                        ['hourglass', $T('Devis toujours envoyé sous 24 heures', 'Quotes always sent within 24 hours')],
                    ]],
                    [$T('Pour votre premier rendez-vous', 'For your first meeting'), [
                        ['file-text', $T('Vos derniers comptes annuels, si vous en avez', 'Your latest annual accounts, if any')],
                        ['receipt', $T('Une estimation de vos charges mensuelles', 'An estimate of your monthly costs')],
                    ]],
                ]]),
            ]];

        $blog = [
            'base' => $u('ressources', 'resources'),
            'title' => $T('Ressources', 'Resources'),
            'categories' => ['fiscalite' => $T('Fiscalité', 'Tax'), 'gestion' => $T('Gestion', 'Management'), 'creation' => $T('Création', 'Starting up')],
            'posts' => [
                ['key' => 'echeances', 'slug' => $u('anticiper-echeances-fiscales', 'plan-for-tax-deadlines'), 'category' => 'fiscalite', 'tags' => [$T('Échéances', 'Deadlines')],
                    'title' => $T('Anticiper ses échéances fiscales et sociales', 'Planning ahead for tax and payroll deadlines'),
                    'excerpt' => $T('Un calendrier partagé et une réserve de trésorerie évitent les pénalités et le stress.', 'A shared calendar and a cash reserve avoid penalties and stress.'),
                    'photo' => ['comptable-article-fiscalite', 'calendar-days', $T('Agenda ouvert à côté d’une calculatrice et de factures sur un bureau', 'Open planner beside a calculator and invoices on a desk')],
                    'quote' => $T('« La taxe qui surprend est toujours celle qu’on n’a pas provisionnée. »', '“The tax that catches you off guard is always the one you didn’t set money aside for.”'),
                    'body' => [
                        $T('TVA, acomptes d’impôt, cotisations sociales : les échéances reviennent chaque mois ou chaque trimestre. Notez-les dans un calendrier partagé avec votre comptable.', 'Sales tax, estimated tax payments, payroll taxes: deadlines come back every month or quarter. Put them in a calendar shared with your accountant.'),
                        $T('Mettez de côté chaque mois une part de votre chiffre d’affaires sur un compte dédié : le jour de l’échéance, l’argent est déjà là.', 'Each month, set aside part of your revenue in a dedicated account: when the deadline comes, the money is already there.'),
                        $T('Votre cabinet peut vous envoyer une alerte quelques jours avant chaque date, avec le montant estimé.', 'Your firm can send you an alert a few days before each date, with the estimated amount.'),
                    ]],
                ['key' => 'statut', 'slug' => $u('choisir-son-statut', 'choosing-a-business-structure'), 'category' => 'creation', 'tags' => [$T('Statut', 'Structure')],
                    'title' => $T('Entreprise individuelle ou société : comment choisir ?', 'Sole proprietorship or company: how to choose?'),
                    'excerpt' => $T('Responsabilité, fiscalité, protection sociale : les critères qui comptent vraiment.', 'Liability, tax, social coverage: the criteria that really matter.'),
                    'photo' => ['comptable-article-creation', 'rocket', $T('Entrepreneuse comparant des documents à une table de café avec son ordinateur', 'Entrepreneur comparing documents at a café table with her laptop')],
                    'heading' => $T('Les questions à se poser', 'Questions to ask yourself'),
                    'body' => [
                        $T('L’entreprise individuelle est simple et peu coûteuse ; la société sépare mieux le patrimoine personnel et facilite l’arrivée d’associés.', 'A sole proprietorship is simple and inexpensive; a company better separates personal assets and makes it easier to bring in partners.'),
                        $T('Comparez le coût fiscal et social à plusieurs niveaux de bénéfice, vos besoins de financement et vos projets à trois ans.', 'Compare tax and social costs at several profit levels, your financing needs and your three-year plans.'),
                        $T('Les formes juridiques et leurs règles varient d’un pays à l’autre : une simulation chiffrée avec votre comptable reste le meilleur moyen de trancher.', 'Legal forms and their rules vary from country to country: a figures-based simulation with your accountant is the best way to decide.'),
                    ]],
                ['key' => 'tresorerie', 'slug' => $u('piloter-sa-tresorerie', 'managing-cash-flow'), 'category' => 'gestion', 'tags' => [$T('Trésorerie', 'Cash flow')],
                    'title' => $T('Cinq réflexes pour piloter sa trésorerie', 'Five habits for managing cash flow'),
                    'excerpt' => $T('Relances, délais de paiement, prévisionnel : garder la main sur son compte bancaire.', 'Reminders, payment terms, forecasts: staying in control of your bank balance.'),
                    'photo' => ['comptable-article-gestion', 'piggy-bank', $T('Gérant de boutique consultant son solde bancaire sur une tablette', 'Shop manager checking his bank balance on a tablet')],
                    'heading' => $T('Les cinq réflexes', 'The five habits'),
                    'body' => [
                        $T('Une entreprise rentable peut manquer de trésorerie : les clients paient tard, les fournisseurs tôt, et les impôts tombent au mauvais moment.', 'A profitable business can still run short of cash: customers pay late, suppliers early, and taxes fall at the wrong time.'),
                        $T('Facturez sans attendre, relancez dès le premier jour de retard, négociez vos délais fournisseurs, demandez des acomptes et tenez un prévisionnel sur trois mois.', 'Invoice right away, follow up from the first day of delay, negotiate supplier terms, ask for deposits and keep a three-month forecast.'),
                        $T('Votre tableau de bord vous montre chaque semaine l’évolution de votre solde et vous alerte avant une période tendue.', 'Your dashboard shows your balance trend every week and warns you before a tight period.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Missions', 'Services'), 'url' => $services],
                    ['label' => $T('Création', 'Start a business'), 'url' => $startup],
                    ['label' => $T('Offres', 'Plans'), 'url' => $plans],
                    ['label' => $T('Outils en ligne', 'Online tools'), 'url' => $tools],
                    ['label' => $T('Le cabinet', 'About'), 'url' => $firm],
                    ['label' => $T('Ressources', 'Resources'), 'url' => $resources],
                ],
                'cta_label' => $T('Demander un devis', 'Request a quote'),
                'cta_url' => $contact,
                'footer_text' => $T('Cabinet d’expertise comptable : comptabilité, fiscalité, paie et conseil pour les indépendants, TPE et PME, avec des outils en ligne inclus.', 'Accounting firm: bookkeeping, tax, payroll and advice for freelancers and small businesses, with online tools included.'),
                'footer_links' => [
                    ['label' => $T('Nos offres', 'Our plans'), 'url' => $plans],
                    ['label' => $T('Création d’entreprise', 'Starting a business'), 'url' => $startup],
                    ['label' => $T('Ressources', 'Resources'), 'url' => $resources],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
