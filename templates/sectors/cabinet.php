<?php

/*
 * Pack « Cabinets et services professionnels » : 8 pages, en français et en anglais.
 * Avocats, experts-comptables, conseils, notaires, courtiers d'assurance.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du cabinet). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Cabinets et services professionnels', 'en' => 'Professional services firms'],
        'icon' => 'scale',
        'keywords' => ['cabinet', 'avocat', 'juridique', 'droit', 'comptable', 'expert-comptable', 'audit', 'fiscalité', 'notaire', 'conseil', 'assurance', 'courtier', 'law firm', 'lawyer', 'accounting', 'consulting', 'tax', 'notary', 'advisory'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $expertise = $u('/expertises', '/expertise');
        $about = $u('/le-cabinet', '/the-firm');
        $team = $u('/equipe', '/our-team');
        $fees = $u('/honoraires', '/fees');
        $cases = $u('/references', '/track-record');
        $resources = $u('/ressources', '/insights');
        $contact = '/contact';

        $areas = [
            ['building-2', $T('Droit des affaires', 'Business law'), $T('Contrats commerciaux, sociétés, OHADA, fusions-acquisitions et gouvernance.', 'Commercial contracts, corporate matters, OHADA law, M&A and governance.')],
            ['calculator', $T('Fiscalité', 'Tax'), $T('Optimisation légale, déclarations, contrôles et contentieux fiscaux.', 'Lawful tax planning, filings, audits and tax disputes.')],
            ['chart-pie', $T('Comptabilité et audit', 'Accounting and audit'), $T('Tenue des comptes, états financiers, audit légal et contractuel.', 'Bookkeeping, financial statements, statutory and contractual audit.')],
            ['users', $T('Droit du travail', 'Labor law'), $T('Contrats, règlement intérieur, relations sociales et gestion des litiges.', 'Contracts, internal rules, employee relations and dispute handling.')],
            ['house', $T('Immobilier', 'Real estate'), $T('Acquisitions, baux, titres fonciers et sécurisation des transactions.', 'Acquisitions, leases, land titles and secure transactions.')],
            ['shield-check', $T('Conformité', 'Compliance'), $T('Lutte anti-blanchiment, protection des données, programmes de conformité.', 'Anti-money laundering, data protection, compliance programs.')],
        ];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('La confiance de nos clients', 'The trust of our clients'), 'items' => [
            [$T('Une équipe rigoureuse, disponible et claire dans ses conseils. Notre société a été accompagnée à chaque étape.', 'A rigorous, available team with clear advice. Our company was supported at every step.'), '[Prénom Nom]', $T('Directeur général, PME de distribution', 'Managing director, distribution SME')],
            [$T('Le cabinet a sécurisé notre acquisition immobilière avec une grande précision et une parfaite discrétion.', 'The firm secured our property acquisition with great precision and complete discretion.'), '[Prénom Nom]', $T('Investisseur', 'Investor')],
            [$T('Des honoraires annoncés dès le départ et un suivi régulier : nous savons toujours où en est notre dossier.', 'Fees announced upfront and regular updates: we always know where our file stands.'), '[Prénom Nom]', $T('Gérante, société de services', 'Manager, services company')],
        ]];
        $cta = ['title' => $T('Parlons de votre situation', 'Let’s discuss your situation'), 'text' => $T('Un premier échange confidentiel permet de cerner vos besoins et de vous proposer la démarche la plus adaptée.', 'A first confidential conversation helps us understand your needs and propose the most suitable approach.'),
            'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact, 'accent'], [$T('Voir nos honoraires', 'See our fees'), $fees, 'outline-light']]];
        $engagements = [
            ['cabinet-dossier-creation', 'building', $T('Création d’un groupe de sociétés', 'Setting up a group of companies'), $T('Structuration juridique et fiscale d’un groupe régional : holding, filiales et pacte d’associés.', 'Legal and tax structuring of a regional group: holding company, subsidiaries and shareholders’ agreement.')],
            ['cabinet-dossier-fiscal', 'calculator', $T('Contrôle fiscal d’une PME', 'Tax audit of an SME'), $T('Assistance lors d’une vérification de comptabilité et préparation de la réponse à l’administration.', 'Assistance during a tax audit and preparation of the response to the authorities.')],
            ['cabinet-dossier-immobilier', 'house', $T('Acquisition d’un site industriel', 'Acquisition of an industrial site'), $T('Vérification des titres, négociation et sécurisation de la transaction foncière.', 'Title checks, negotiation and securing of the land transaction.')],
            ['cabinet-dossier-social', 'users', $T('Restructuration des équipes', 'Workforce restructuring'), $T('Accompagnement d’une entreprise dans la réorganisation de ses effectifs, dans le respect du droit du travail.', 'Supporting a company through a workforce reorganization in compliance with labor law.')],
            ['cabinet-dossier-conformite', 'shield-check', $T('Programme de conformité', 'Compliance program'), $T('Mise en place de procédures anti-blanchiment et de protection des données pour un établissement financier.', 'Rolling out anti-money laundering and data protection procedures for a financial institution.')],
            ['cabinet-dossier-audit', 'chart-line', $T('Audit des comptes d’une ONG', 'Audit of an NGO’s accounts'), $T('Audit contractuel pour un bailleur et recommandations de contrôle interne.', 'Contractual audit for a donor and internal control recommendations.')],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Cabinet de conseil juridique, fiscal et comptable pour entreprises et particuliers. Rigueur, confidentialité et réactivité.', 'Legal, tax and accounting advisory firm for businesses and individuals. Rigor, confidentiality and responsiveness.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Conseil juridique, fiscal et comptable', 'Legal, tax and accounting advice'),
                    'title' => $T('Des conseils précis pour sécuriser vos décisions', 'Precise advice to secure your decisions'),
                    'text' => $T('Nous accompagnons les entreprises, les investisseurs et les particuliers dans le respect du droit OHADA et de la réglementation locale, avec rigueur et en toute confidentialité.', 'We support businesses, investors and individuals under OHADA law and local regulations, with rigor and in strict confidence.'),
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact], [$T('Nos expertises', 'Our expertise'), $expertise]],
                    'photo' => ['cabinet-accueil', 'scale', $T('Associés du cabinet en discussion dans une salle de réunion', 'Partners of the firm talking in a meeting room')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos expertises', 'Our expertise'), 'title' => $T('Un accompagnement complet pour votre activité', 'Complete support for your business'), 'items' => $areas, 'bg' => 'light']),
                B::split($theme, [
                    'eyebrow' => $T('Pourquoi nous choisir', 'Why choose us'),
                    'title' => $T('L’exigence d’un grand cabinet, la proximité en plus', 'The standards of a large firm, with a personal touch'),
                    'html' => '<p>'.e($T('Chaque dossier est suivi par un associé référent. Nous expliquons les enjeux simplement, annonçons nos honoraires dès le départ et vous tenons informé à chaque étape.', 'Every file is overseen by a partner. We explain the issues plainly, state our fees upfront and keep you informed at each stage.')).'</p>',
                    'bullets' => [$T('Un associé référent pour chaque dossier', 'A lead partner on every file'), $T('Confidentialité stricte, secret professionnel respecté', 'Strict confidentiality, professional secrecy upheld'), $T('Honoraires annoncés avant le démarrage', 'Fees agreed before work begins'), $T('Réponse sous 24 heures ouvrées', 'Reply within one business day')],
                    'buttons' => [[$T('Découvrir le cabinet', 'Discover the firm'), $about, 'outline']],
                    'photo' => ['cabinet-salle-reunion', 'users', $T('Entretien de conseil avec un client', 'Advisory meeting with a client')],
                    'reverse' => true,
                ]),
                B::stats($theme, ['title' => $T('Le cabinet en quelques chiffres', 'The firm at a glance'), 'items' => [
                    [20, '+', $T('années d’expérience cumulée', 'years of combined experience')],
                    [850, '+', $T('dossiers traités', 'files handled')],
                    [12, '', $T('professionnels', 'professionals')],
                    [6, '', $T('domaines d’expertise', 'practice areas')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Notre méthode', 'Our approach'), 'title' => $T('Comment nous travaillons ensemble', 'How we work together'), 'items' => [
                    [$T('Écoute', 'Listening'), $T('Un premier entretien confidentiel pour comprendre votre situation.', 'A first confidential meeting to understand your situation.')],
                    [$T('Analyse', 'Analysis'), $T('Étude du dossier et des risques juridiques, fiscaux et financiers.', 'Review of the file and of legal, tax and financial risks.')],
                    [$T('Proposition', 'Proposal'), $T('Une stratégie claire et une convention d’honoraires transparente.', 'A clear strategy and a transparent fee agreement.')],
                    [$T('Suivi', 'Follow-up'), $T('Mise en œuvre, points d’avancement et conseils dans la durée.', 'Implementation, progress updates and ongoing advice.')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'expertise', 'slug' => $u('expertises', 'expertise'), 'title' => $T('Nos expertises', 'Our expertise'),
            'description' => $T('Droit des affaires, fiscalité, comptabilité et audit, droit du travail, immobilier, conformité : nos domaines d’intervention.', 'Business law, tax, accounting and audit, labor law, real estate, compliance: our practice areas.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nos expertises', 'Our expertise'), 'title' => $T('Six domaines, une même exigence', 'Six practice areas, one standard'), 'intro' => $T('Des spécialistes qui travaillent ensemble pour couvrir l’ensemble de vos besoins.', 'Specialists working together to cover all of your needs.')]),
                B::features($theme, ['items' => $areas, 'cols' => 3]),
                B::split($theme, [
                    'eyebrow' => $T('Domaine phare', 'Flagship area'),
                    'title' => $T('Création et développement d’entreprise', 'Business creation and development'),
                    'html' => '<p>'.e($T('De l’idée à l’immatriculation, puis tout au long de la vie de la société : choix de la forme juridique, statuts, pacte d’associés, formalités et conseil au quotidien, dans le cadre du droit OHADA.', 'From the idea to registration, then throughout the life of the company: choice of legal form, articles of association, shareholders’ agreement, formalities and day-to-day advice under OHADA law.')).'</p>',
                    'bullets' => [$T('Choix de la structure la plus adaptée', 'Choosing the most suitable structure'), $T('Rédaction des statuts et des actes', 'Drafting articles and corporate documents'), $T('Immatriculation et formalités', 'Registration and formalities'), $T('Accompagnement fiscal et comptable', 'Tax and accounting support')],
                    'buttons' => [[$T('Voir le pack création', 'See the creation pack'), $fees]],
                    'photo' => ['cabinet-creation-entreprise', 'rocket', $T('Entrepreneurs signant les statuts de leur société', 'Entrepreneurs signing their company’s articles')],
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'intro' => $T('Une autre question ? Nous vous répondons en toute confidentialité.', 'Another question? We will answer in strict confidence.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Intervenez-vous pour les particuliers comme pour les entreprises ?', 'Do you act for individuals as well as companies?'), $T('Oui. Nous conseillons les entreprises, les investisseurs et les particuliers, selon les domaines.', 'Yes. We advise companies, investors and individuals, depending on the area.')],
                    [$T('Mes informations restent-elles confidentielles ?', 'Will my information remain confidential?'), $T('Oui. Nous sommes tenus au secret professionnel et chaque dossier est protégé par des accès restreints.', 'Yes. We are bound by professional secrecy and every file is protected by restricted access.')],
                    [$T('Pouvez-vous garantir l’issue d’un litige ?', 'Can you guarantee the outcome of a dispute?'), $T('Non, aucun professionnel sérieux ne le peut. Nous évaluons honnêtement vos chances et vos risques avant d’agir.', 'No, no serious professional can. We honestly assess your prospects and risks before acting.')],
                    [$T('Combien de temps prend la création d’une société ?', 'How long does it take to set up a company?'), $T('Les délais dépendent de la forme choisie et de l’administration. Nous vous donnons un calendrier réaliste dès le premier rendez-vous.', 'Timelines depend on the chosen form and the administration. We give you a realistic schedule from the first meeting.')],
                    [$T('Travaillez-vous avec des clients situés à l’étranger ?', 'Do you work with clients based abroad?'), $T('Oui, à distance ou avec des correspondants dans la région, pour les investisseurs qui s’implantent localement.', 'Yes, remotely or with correspondents in the region, for investors setting up locally.')],
                ]]),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('le-cabinet', 'the-firm'), 'title' => $T('Le cabinet', 'The firm'),
            'description' => $T('Notre histoire, nos valeurs d’éthique, de confidentialité et de réactivité, et la vision de nos associés fondateurs.', 'Our story, our values of ethics, confidentiality and responsiveness, and the vision of our founding partners.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Le cabinet', 'The firm'), 'title' => $T('Une pratique fondée sur la confiance', 'A practice built on trust'), 'intro' => $T('Indépendance, rigueur et discrétion guident notre travail depuis l’origine.', 'Independence, rigor and discretion have guided our work from the start.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Du cabinet individuel à une équipe pluridisciplinaire', 'From a solo practice to a multidisciplinary team'),
                    'html' => '<p>'.e($T('Le cabinet a été fondé par deux associés convaincus que les entreprises de la région méritent des conseils de haut niveau, accessibles et compréhensibles. Il réunit aujourd’hui juristes, fiscalistes et experts-comptables autour de dossiers communs.', 'The firm was founded by two partners convinced that businesses in the region deserve high-level advice that is accessible and clear. It now brings lawyers, tax specialists and accountants together on shared files.')).'</p><p>'.e($T('Inscrit au barreau sous le numéro [N° d’inscription], le cabinet est soumis à une déontologie stricte.', 'Registered with the bar under number [Registration no.], the firm is bound by strict professional ethics.')).'</p>',
                    'photo' => ['cabinet-histoire', 'building-2', $T('Façade du cabinet et entrée de l’immeuble', 'Firm’s building and entrance')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Trois engagements envers nos clients', 'Three commitments to our clients'), 'bg' => 'light', 'items' => [
                    ['scale', $T('Éthique', 'Ethics'), $T('Indépendance, loyauté et refus de tout conflit d’intérêts.', 'Independence, loyalty and no tolerance for conflicts of interest.')],
                    ['lock', $T('Confidentialité', 'Confidentiality'), $T('Secret professionnel absolu et protection rigoureuse de vos données.', 'Absolute professional secrecy and rigorous protection of your data.')],
                    ['clock', $T('Réactivité', 'Responsiveness'), $T('Un retour rapide et des échéances tenues, car votre activité n’attend pas.', 'Prompt replies and deadlines met, because your business cannot wait.')],
                ]]),
                B::quote($theme, ['text' => $T('Un bon conseil ne se mesure pas au nombre de pages, mais à la sérénité qu’il apporte à celui qui décide.', 'Good advice is measured not by page count, but by the peace of mind it brings to the person who decides.'), 'source' => $T('— [Maître Prénom Nom], associé fondateur', '— [Maître First Last], founding partner')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'team', 'slug' => $u('equipe', 'our-team'), 'title' => $T('Notre équipe', 'Our team'),
            'description' => $T('Associés, avocats, fiscalistes et experts-comptables : rencontrez l’équipe qui suivra vos dossiers.', 'Partners, lawyers, tax specialists and accountants: meet the team who will handle your files.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Notre équipe', 'Our team'), 'title' => $T('Des professionnels engagés à vos côtés', 'Committed professionals at your side'), 'intro' => $T('Chaque dossier est confié à un associé référent, entouré d’une équipe complémentaire.', 'Every file is entrusted to a lead partner, supported by a complementary team.')]),
                B::team($theme, ['eyebrow' => $T('Associés', 'Partners'), 'title' => $T('Les associés', 'The partners'), 'items' => [
                    ['[Maître Prénom Nom]', $T('Associé fondateur, droit des affaires', 'Founding partner, business law'), 'cabinet-associe-1'],
                    ['[Maître Prénom Nom]', $T('Associée, droit social et contentieux', 'Partner, labor law and litigation'), 'cabinet-associe-2'],
                    ['[Prénom Nom]', $T('Associé, expert-comptable', 'Partner, chartered accountant'), 'cabinet-associe-3'],
                    ['[Prénom Nom]', $T('Associée, fiscalité', 'Partner, tax'), 'cabinet-associe-4'],
                ]]),
                B::team($theme, ['eyebrow' => $T('Collaborateurs', 'Associates'), 'title' => $T('Les collaborateurs', 'The associates'), 'bg' => 'light', 'items' => [
                    ['[Maître Prénom Nom]', $T('Avocate collaboratrice', 'Associate lawyer'), 'cabinet-collab-1'],
                    ['[Prénom Nom]', $T('Juriste, conformité', 'Legal counsel, compliance'), 'cabinet-collab-2'],
                    ['[Prénom Nom]', $T('Auditeur senior', 'Senior auditor'), 'cabinet-collab-3'],
                    ['[Prénom Nom]', $T('Responsable administratif', 'Office manager'), 'cabinet-collab-4'],
                ]]),
                B::cta($theme, ['title' => $T('Rejoignez le cabinet', 'Join the firm'), 'text' => $T('Nous recrutons des juristes, comptables et auditeurs rigoureux, soucieux d’évoluer dans une équipe exigeante et bienveillante.', 'We are recruiting rigorous lawyers, accountants and auditors who want to grow in a demanding yet supportive team.'),
                    'buttons' => [[$T('Envoyer ma candidature', 'Send my application'), $contact, 'accent'], [$T('Découvrir le cabinet', 'Discover the firm'), $about, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'fees', 'slug' => $u('honoraires', 'fees'), 'title' => $T('Honoraires', 'Fees'),
            'description' => $T('Des honoraires clairs : consultation, forfait mensuel et pack création d’entreprise. Convention écrite avant chaque mission.', 'Clear fees: consultation, monthly retainer and company creation pack. Written agreement before every engagement.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Honoraires', 'Fees'), 'title' => $T('Des honoraires transparents', 'Transparent fees'), 'intro' => $T('Chaque mission fait l’objet d’une convention écrite, signée avant le démarrage.', 'Every engagement is covered by a written agreement, signed before work begins.')]),
                B::pricing($theme, ['url' => $contact, 'title' => $T('Nos formules indicatives', 'Our indicative packages'), 'eyebrow' => $T('Exemples de tarifs', 'Sample rates'), 'intro' => $T('Montants indicatifs hors taxes, ajustés à la complexité de votre dossier.', 'Indicative amounts excluding taxes, adjusted to the complexity of your file.'), 'items' => [
                    [$T('Consultation', 'Consultation'), '80', '$', $T('/ heure', '/ hour'), $T('Un avis juridique ou fiscal ciblé.', 'Targeted legal or tax advice.'), [$T('Entretien d’une heure', 'One-hour meeting'), $T('Avis écrit sur demande', 'Written opinion on request'), $T('Confidentialité garantie', 'Confidentiality guaranteed'), '-'.$T('Suivi de dossier', 'File follow-up')], $T('Prendre rendez-vous', 'Book an appointment')],
                    [$T('Forfait mensuel', 'Monthly retainer'), '350', '$', $T('/ mois', '/ month'), $T('Un conseil continu pour votre entreprise.', 'Ongoing advice for your business.'), [$T('Conseil juridique illimité par courriel', 'Unlimited legal advice by email'), $T('Tenue comptable de base', 'Basic bookkeeping'), $T('Déclarations fiscales courantes', 'Routine tax filings'), $T('Rendez-vous mensuel', 'Monthly meeting')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Pack création d’entreprise', 'Company creation pack'), '650', '$', $T('forfait', 'flat fee'), $T('De l’idée à l’immatriculation.', 'From the idea to registration.'), [$T('Choix de la forme juridique', 'Choice of legal form'), $T('Rédaction des statuts', 'Drafting of the articles'), $T('Formalités d’immatriculation', 'Registration formalities'), $T('Frais officiels en sus', 'Official fees extra')], $T('Créer mon entreprise', 'Create my company')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos principes', 'Our principles'), 'title' => $T('Ce que vous pouvez attendre de nous', 'What you can expect from us'), 'items' => [
                    ['file-text', $T('Convention écrite', 'Written agreement'), $T('Périmètre, tarif et calendrier fixés avant toute intervention.', 'Scope, rates and schedule set before any work.')],
                    ['eye', $T('Pas de surprise', 'No surprises'), $T('Tout dépassement est validé avec vous au préalable.', 'Any overrun is agreed with you beforehand.')],
                    ['receipt', $T('Facturation détaillée', 'Itemized invoicing'), $T('Chaque facture précise le temps passé et les débours.', 'Each invoice details time spent and disbursements.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur la facturation', 'Your billing questions'), 'bg' => 'light', 'items' => [
                    [$T('La première consultation est-elle gratuite ?', 'Is the first consultation free?'), $T('Un court échange d’orientation est offert. Les consultations approfondies sont facturées selon notre grille.', 'A short orientation call is free. In-depth consultations are billed according to our rates.')],
                    [$T('Comment puis-je payer ?', 'How can I pay?'), $T('Par virement bancaire ou Mobile Money. Un acompte est demandé au démarrage de certaines missions.', 'By bank transfer or Mobile Money. A deposit is requested at the start of some engagements.')],
                    [$T('Les frais de greffe et de timbres sont-ils inclus ?', 'Are registry and stamp fees included?'), $T('Non, les frais officiels sont refacturés au coût réel, sur justificatif.', 'No, official fees are rebilled at cost, with supporting documents.')],
                    [$T('Proposez-vous un paiement échelonné ?', 'Do you offer payment in installments?'), $T('Oui, pour les missions longues, selon un calendrier convenu dans la convention.', 'Yes, for longer engagements, on a schedule agreed in the engagement letter.')],
                ]]),
            ]];

        $pages[] = ['key' => 'cases', 'slug' => $u('references', 'track-record'), 'title' => $T('Références', 'Track record'),
            'description' => $T('Exemples de missions anonymisées dans nos domaines d’expertise. Chaque dossier est unique et les résultats ne sont jamais garantis.', 'Anonymized sample engagements across our practice areas. Every matter is unique and outcomes are never guaranteed.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Références', 'Track record'), 'title' => $T('Des missions variées, menées avec rigueur', 'Varied engagements, handled with rigor'), 'intro' => $T('Exemples anonymisés : par respect du secret professionnel, aucun client n’est nommé.', 'Anonymized examples: out of respect for professional secrecy, no client is named.')]),
                B::cards($theme, ['eyebrow' => $T('Exemples de missions', 'Sample engagements'), 'title' => $T('Ce que nous avons accompli pour nos clients', 'What we have done for our clients'), 'items' => $engagements]),
                B::testimonials($theme, $testimonials),
                B::stats($theme, ['title' => $T('Notre activité en chiffres', 'Our work in numbers'), 'items' => [
                    [850, '+', $T('dossiers traités', 'files handled')],
                    [300, '+', $T('sociétés accompagnées', 'companies supported')],
                    [95, '%', $T('clients qui nous recommandent', 'clients who recommend us')],
                    [24, ' h', $T('délai moyen de première réponse', 'average time to first reply')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'resources', 'slug' => $u('ressources', 'insights'), 'title' => $T('Ressources', 'Insights'),
            'description' => $T('Guides pratiques, analyses juridiques et fiscales, et séminaires pour dirigeants et entrepreneurs.', 'Practical guides, legal and tax insights, and seminars for executives and entrepreneurs.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Ressources', 'Insights'), 'title' => $T('Comprendre pour mieux décider', 'Understand to decide better'), 'intro' => $T('Guides et analyses rédigés par nos équipes. Ils ne remplacent pas un conseil personnalisé.', 'Guides and insights written by our teams. They do not replace personalized advice.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['eyebrow' => $T('Séminaires', 'Seminars'), 'title' => $T('Prochains séminaires', 'Upcoming seminars'), 'bg' => 'light', 'items' => [
                    [$T('Fiscalité des PME : les nouveautés', 'SME taxation: what’s new'), $T('12 février, 9 h', 'February 12, 9 am'), $T('Salle de conférence du cabinet', 'Firm’s conference room')],
                    [$T('Droit du travail pour dirigeants', 'Labor law for executives'), $T('18 mars, 14 h', 'March 18, 2 pm'), $T('Hôtel partenaire, centre-ville', 'Partner hotel, city center')],
                    [$T('Créer et protéger son entreprise', 'Creating and protecting your business'), $T('22 avril, 10 h', 'April 22, 10 am'), $T('En ligne', 'Online')],
                    [$T('Conformité et protection des données', 'Compliance and data protection'), $T('13 mai, 15 h', 'May 13, 3 pm'), $T('Salle de conférence du cabinet', 'Firm’s conference room')],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Prenez rendez-vous avec le cabinet. Premier échange confidentiel, réponse sous 24 heures ouvrées.', 'Book an appointment with the firm. Confidential first conversation, reply within one business day.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Prenez rendez-vous', 'Book an appointment'), 'intro' => $T('Vos informations restent strictement confidentielles. Nous répondons sous 24 heures ouvrées.', 'Your information remains strictly confidential. We reply within one business day.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Écrivez-nous en précisant l’objet de votre demande, ou venez nous rencontrer sur rendez-vous.', 'Write to us stating the subject of your request, or visit us by appointment.'),
                    'items' => [
                        ['map-pin', $T('Boulevard du 30 Juin, Kinshasa, RDC', 'Boulevard du 30 Juin, Kinshasa, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h – 17 h, sur rendez-vous', 'Monday – Friday, 8 am – 5 pm, by appointment')],
                    ],
                    'social' => ['x', 'facebook', 'whatsapp'],
                    'subject' => true,
                    'map' => 'Boulevard du 30 Juin, Kinshasa',
                    'bg' => 'white',
                ]),
            ]];

        $blog = [
            'base' => $u('ressources', 'insights'),
            'title' => $T('Ressources', 'Insights'),
            'categories' => ['entreprise' => $T('Entreprise', 'Business'), 'fiscalite' => $T('Fiscalité', 'Tax'), 'immobilier' => $T('Immobilier', 'Real estate')],
            'posts' => [
                ['key' => 'ohada', 'slug' => $u('creer-societe-ohada', 'company-ohada-zone'), 'category' => 'entreprise', 'tags' => ['OHADA'],
                    'title' => $T('Guide : créer sa société en zone OHADA', 'Guide: setting up a company in the OHADA zone'),
                    'excerpt' => $T('Formes juridiques, étapes et pièges à éviter pour bien démarrer.', 'Legal forms, steps and pitfalls to avoid for a strong start.'),
                    'photo' => ['cabinet-guide-creation', 'book-open', $T('Entrepreneur au travail', 'Entrepreneur at work')],
                    'heading' => $T('Les étapes', 'The steps'),
                    'body' => [
                        $T('SARL, SA ou SAS : la forme juridique dépend du nombre d’associés, du capital et de vos projets de croissance.', 'LLC, PLC or simplified company: the legal form depends on the number of partners, the capital and your growth plans.'),
                        $T('Rédaction des statuts, dépôt du capital, immatriculation au registre du commerce et déclarations fiscales : chaque étape a ses pièces à fournir.', 'Drafting the articles, depositing the capital, registering with the trade register and tax filings: each step has its own documents.'),
                        $T('Ce guide est général : pour votre situation, prenez rendez-vous avec l’un de nos conseils.', 'This guide is general: for your own situation, book a meeting with one of our advisers.'),
                    ]],
                ['key' => 'fiscal', 'slug' => $u('calendrier-fiscal', 'tax-calendar'), 'category' => 'fiscalite', 'tags' => [$T('Échéances', 'Deadlines')],
                    'title' => $T('Calendrier fiscal : ne manquez aucune échéance', 'Tax calendar: never miss a deadline'),
                    'excerpt' => $T('Les principales déclarations et paiements de l’année, expliqués simplement.', 'The main filings and payments of the year, explained simply.'),
                    'photo' => ['cabinet-article-fiscal', 'newspaper', $T('Calculatrice et documents', 'Calculator and documents')],
                    'quote' => $T('« Une échéance anticipée coûte toujours moins cher qu’une pénalité. »', '“A deadline met early always costs less than a penalty.”'),
                    'body' => [
                        $T('Déclarations mensuelles, acomptes, déclaration annuelle des résultats : les dates varient selon le pays et le régime de l’entreprise.', 'Monthly returns, installments, the annual results filing: dates vary with the country and the company’s tax regime.'),
                        $T('Tenez un calendrier partagé avec votre comptable et préparez les pièces justificatives au fil de l’eau.', 'Keep a calendar shared with your accountant and prepare supporting documents as you go.'),
                        $T('Notre équipe peut prendre en charge le suivi de vos échéances et vous alerter à l’avance.', 'Our team can monitor your deadlines and alert you in advance.'),
                    ]],
                ['key' => 'terrain', 'slug' => $u('acheter-terrain', 'buying-land-safely'), 'category' => 'immobilier', 'tags' => [$T('Foncier', 'Land')],
                    'title' => $T('Acheter un terrain en sécurité', 'Buying land safely'),
                    'excerpt' => $T('Titres, vérifications et précautions avant de signer.', 'Titles, checks and precautions before signing.'),
                    'photo' => ['cabinet-article-immobilier', 'house', $T('Terrain à bâtir', 'Building land')],
                    'heading' => $T('Avant de signer', 'Before you sign'),
                    'body' => [
                        $T('Vérifiez le titre de propriété auprès du service des affaires foncières et assurez-vous que le vendeur est bien le propriétaire.', 'Check the title deed with the land registry and make sure the seller is really the owner.'),
                        $T('Faites borner le terrain, consultez le plan d’urbanisme et demandez l’historique des transactions.', 'Have the land surveyed, check the zoning plan and ask for the transaction history.'),
                        $T('Un acte rédigé ou relu par un professionnel vous protège en cas de litige.', 'A deed drafted or reviewed by a professional protects you in case of dispute.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Expertises', 'Expertise'), 'url' => $expertise],
                    ['label' => $T('Le cabinet', 'The firm'), 'url' => $about],
                    ['label' => $T('Équipe', 'Team'), 'url' => $team],
                    ['label' => $T('Honoraires', 'Fees'), 'url' => $fees],
                    ['label' => $T('Références', 'Track record'), 'url' => $cases],
                    ['label' => $T('Ressources', 'Insights'), 'url' => $resources],
                ],
                'cta_label' => $T('Prendre rendez-vous', 'Book an appointment'),
                'cta_url' => $contact,
                'footer_text' => $T('Cabinet de conseil juridique, fiscal et comptable. Rigueur, confidentialité et réactivité au service des entreprises et des particuliers.', 'Legal, tax and accounting advisory firm. Rigor, confidentiality and responsiveness for businesses and individuals.'),
                'footer_links' => [
                    ['label' => $T('Nos expertises', 'Our expertise'), 'url' => $expertise],
                    ['label' => $T('Honoraires', 'Fees'), 'url' => $fees],
                    ['label' => $T('Ressources', 'Insights'), 'url' => $resources],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
