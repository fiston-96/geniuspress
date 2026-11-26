<?php

/*
 * Pack « Cabinet d'avocats » (secteur Cabinets et services professionnels) :
 * 8 pages, en français et en anglais. Domaines du droit, avocats associés,
 * honoraires, prise de rendez-vous et actualités juridiques. Les textes sont des
 * exemples réalistes à personnaliser (l'assistant IA les réécrit à partir de la
 * présentation du cabinet). Les liens internes sont écrits avec les adresses de
 * la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Cabinets et services professionnels', 'en' => 'Professional services firms'],
        'icon' => 'scale',
        'keywords' => ['avocat', 'avocats', 'cabinet d’avocats', 'juridique', 'droit', 'contentieux', 'barreau', 'conseil juridique', 'divorce', 'droit du travail', 'law firm', 'lawyer', 'attorney', 'legal', 'litigation', 'counsel', 'family law', 'employment law'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $practice = $u('/domaines', '/practice-areas');
        $firm = $u('/le-cabinet', '/the-firm');
        $lawyers = $u('/avocats', '/our-lawyers');
        $fees = $u('/honoraires', '/fees');
        $appointment = $u('/rendez-vous', '/appointments');
        $news = $u('/actualites', '/legal-news');
        $contact = '/contact';

        $areas = [
            ['briefcase', $T('Droit des affaires', 'Business law'), $T('Création de société, pactes d’associés, contrats commerciaux, cessions et recouvrement de créances.', 'Company formation, shareholder agreements, commercial contracts, business sales and debt recovery.')],
            ['users', $T('Droit du travail', 'Employment law'), $T('Contrats, ruptures, procédures disciplinaires et défense devant la juridiction du travail, côté employeur comme salarié.', 'Contracts, terminations, disciplinary procedures and representation before labor courts, for employers and employees.')],
            ['heart-handshake', $T('Droit de la famille', 'Family law'), $T('Divorce, garde des enfants, pension alimentaire, régimes matrimoniaux et successions.', 'Divorce, child custody, support payments, marital property and estates.')],
            ['house', $T('Droit immobilier', 'Real estate law'), $T('Ventes, baux d’habitation et commerciaux, copropriété, litiges de construction.', 'Sales, residential and commercial leases, condominiums, construction disputes.')],
            ['gavel', $T('Contentieux', 'Litigation'), $T('Représentation devant les tribunaux civils et commerciaux, en première instance comme en appel.', 'Representation before civil and commercial courts, at first instance and on appeal.')],
            ['shield', $T('Droit pénal', 'Criminal law'), $T('Assistance en garde à vue, défense des prévenus et accompagnement des victimes.', 'Assistance during police custody, defense of the accused and support for victims.')],
        ];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Ils nous ont confié leur dossier', 'They trusted us with their case'), 'items' => [
            [$T('Mon avocate m’a expliqué chaque étape de mon divorce avec patience. Je me suis sentie écoutée et défendue.', 'My lawyer explained every step of my divorce with patience. I felt heard and well defended.'), '[Prénom Nom]', $T('Cliente, droit de la famille', 'Client, family law')],
            [$T('Un litige commercial réglé par une transaction en trois mois, sans procès. Une vraie stratégie, pas seulement des actes.', 'A commercial dispute settled out of court within three months. A real strategy, not just paperwork.'), '[Prénom Nom]', $T('Gérant, entreprise de négoce', 'Managing director, trading company')],
            [$T('Des honoraires fixés par écrit dès le premier rendez-vous et des réponses rapides à chaque question.', 'Fees set in writing at the first meeting and quick answers to every question.'), '[Prénom Nom]', $T('Directrice des ressources humaines', 'Human resources director')],
        ]];
        $cta = ['title' => $T('Exposez-nous votre situation', 'Tell us about your situation'), 'text' => $T('Un premier rendez-vous permet d’évaluer vos droits, vos chances et le coût d’une procédure, en toute confidentialité.', 'A first meeting lets us assess your rights, your prospects and the cost of proceedings, in strict confidence.'),
            'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $appointment, 'accent'], [$T('Nos honoraires', 'Our fees'), $fees, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Cabinet d’avocats en droit des affaires, du travail, de la famille et immobilier. Conseil, négociation et défense devant les tribunaux.', 'Law firm practicing business, employment, family and real estate law. Advice, negotiation and representation in court.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Avocats au barreau', 'Attorneys at law'),
                    'title' => $T('Défendre vos droits, avec méthode et humanité', 'Defending your rights, with method and humanity'),
                    'text' => $T('Particuliers, dirigeants et entreprises : nous vous conseillons pour éviter le litige et vous défendons quand il devient inévitable.', 'Individuals, executives and companies: we advise you to avoid disputes and defend you when they become unavoidable.'),
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $appointment], [$T('Nos domaines', 'Practice areas'), $practice]],
                    'photo' => ['avocats-accueil', 'scale', $T('Deux avocats en costume relisant un dossier dans une bibliothèque juridique', 'Two lawyers in suits reviewing a case file in a law library')],
                ]),
                B::pillars($theme, ['items' => [
                    ['lock', $T('Secret professionnel', 'Attorney–client privilege'), $T('Tout ce que vous nous confiez reste strictement confidentiel.', 'Everything you share with us remains strictly confidential.')],
                    ['file-text', $T('Honoraires écrits', 'Written fee agreement'), $T('Une convention signée avant toute intervention, sans surprise.', 'A signed agreement before any work begins, with no surprises.')],
                    ['clock', $T('Réponse sous 48 h', 'Reply within 48 hours'), $T('Un avocat vous rappelle rapidement pour faire le point.', 'A lawyer calls you back promptly to review your situation.')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Domaines d’intervention', 'Practice areas'), 'title' => $T('Le conseil et la défense, dans six domaines', 'Advice and defense across six areas'), 'bg' => 'light', 'items' => $areas]),
                B::split($theme, [
                    'eyebrow' => $T('Notre manière d’exercer', 'How we practice'),
                    'title' => $T('Un avocat référent, du premier rendez-vous à la décision', 'One lead lawyer, from the first meeting to the ruling'),
                    'html' => '<p>'.e($T('Votre dossier est suivi par le même avocat du début à la fin. Il vous dit franchement ce qui est possible, ce qui ne l’est pas, et privilégie l’accord amiable chaque fois qu’il sert mieux vos intérêts.', 'Your case is handled by the same lawyer from start to finish. They tell you frankly what is possible and what is not, and favor an amicable settlement whenever it better serves your interests.')).'</p>',
                    'bullets' => [$T('Évaluation honnête de vos chances', 'An honest assessment of your prospects'), $T('Négociation et médiation avant le procès', 'Negotiation and mediation before trial'), $T('Plaidoirie devant toutes les juridictions', 'Advocacy before every court'), $T('Point d’étape écrit à chaque audience', 'A written update after every hearing')],
                    'buttons' => [[$T('Découvrir le cabinet', 'About the firm'), $firm, 'outline']],
                    'photo' => ['avocats-entretien', 'handshake', $T('Avocate écoutant un client lors d’un entretien dans un bureau lumineux', 'Lawyer listening to a client during a meeting in a bright office')],
                    'reverse' => true,
                ]),
                B::steps($theme, ['eyebrow' => $T('Votre dossier', 'Your case'), 'title' => $T('Comment se déroule notre accompagnement', 'How we handle your case'), 'items' => [
                    [$T('Premier rendez-vous', 'First meeting'), $T('Vous exposez les faits, nous identifions vos droits et les options possibles.', 'You explain the facts; we identify your rights and the available options.')],
                    [$T('Stratégie et convention', 'Strategy and agreement'), $T('Nous proposons une démarche et des honoraires, fixés par écrit.', 'We propose an approach and fees, set out in writing.')],
                    [$T('Négociation ou procédure', 'Negotiation or proceedings'), $T('Recherche d’un accord, puis saisine du tribunal si nécessaire.', 'We seek an agreement, then go to court if needed.')],
                    [$T('Décision et suites', 'Ruling and follow-up'), $T('Exécution du jugement, recours éventuel et conseils pour la suite.', 'Enforcement of the ruling, possible appeal and advice going forward.')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::posts($theme, ['eyebrow' => $T('Actualités juridiques', 'Legal news'), 'title' => $T('Comprendre vos droits', 'Understanding your rights'), 'count' => 3, 'more' => $T('Lire l’article', 'Read the article'), 'bg' => 'light']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'practice', 'slug' => $u('domaines', 'practice-areas'), 'title' => $T('Domaines du droit', 'Practice areas'),
            'description' => $T('Droit des affaires, du travail, de la famille, immobilier, pénal et contentieux : nos domaines d’intervention et nos réponses à vos questions.', 'Business, employment, family, real estate and criminal law, and litigation: our practice areas and answers to your questions.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Domaines du droit', 'Practice areas'), 'title' => $T('À chaque situation, l’avocat qu’il vous faut', 'The right lawyer for every situation'), 'intro' => $T('Nos avocats interviennent en conseil comme en contentieux, pour les particuliers et les entreprises.', 'Our lawyers handle both advisory work and litigation, for individuals and companies.')]),
                B::features($theme, ['items' => $areas, 'cols' => 3]),
                B::split($theme, [
                    'eyebrow' => $T('Particuliers', 'Individuals'),
                    'title' => $T('Famille, logement, travail : les moments qui comptent', 'Family, housing, work: the moments that matter'),
                    'html' => '<p>'.e($T('Séparation, conflit avec un employeur ou un propriétaire, succession difficile : nous vous aidons à comprendre vos droits et à prendre les bonnes décisions, avec tact et clarté.', 'Separation, a dispute with an employer or a landlord, a difficult estate: we help you understand your rights and make the right decisions, with tact and clarity.')).'</p>',
                    'bullets' => [$T('Divorce amiable ou contentieux', 'Uncontested or contested divorce'), $T('Contestation d’un licenciement', 'Challenging a dismissal'), $T('Litiges avec un bailleur ou un voisin', 'Disputes with a landlord or a neighbor'), $T('Règlement des successions', 'Settling estates')],
                    'photo' => ['avocats-particuliers', 'heart-handshake', $T('Couple échangeant avec une avocate autour d’une table de réunion', 'Couple talking with a lawyer around a meeting table')],
                    'bg' => 'light',
                ]),
                B::split($theme, [
                    'eyebrow' => $T('Entreprises', 'Businesses'),
                    'title' => $T('Un conseil juridique qui protège votre activité', 'Legal advice that protects your business'),
                    'html' => '<p>'.e($T('Contrats, relations sociales, impayés, litiges avec un partenaire : nous sécurisons vos décisions en amont et défendons vos intérêts devant les juridictions commerciales et sociales.', 'Contracts, employee relations, unpaid invoices, disputes with a partner: we secure your decisions upfront and defend your interests before commercial and labor courts.')).'</p>',
                    'bullets' => [$T('Rédaction et audit de contrats', 'Drafting and reviewing contracts'), $T('Recouvrement amiable et judiciaire', 'Amicable and court-ordered debt recovery'), $T('Abonnement juridique pour les PME', 'Legal retainer for small businesses')],
                    'buttons' => [[$T('Voir l’abonnement entreprise', 'See the business retainer'), $fees]],
                    'photo' => ['avocats-entreprises', 'building-2', $T('Avocat présentant un contrat à deux dirigeants dans une salle de réunion', 'Lawyer presenting a contract to two executives in a meeting room')],
                    'reverse' => true,
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions avant de nous consulter', 'Your questions before consulting us'), 'intro' => $T('Chaque situation est particulière : ces réponses sont générales.', 'Every situation is different: these answers are general.'), 'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $appointment, 'outline']], 'items' => [
                    [$T('Dois-je prendre un avocat pour aller au tribunal ?', 'Do I need a lawyer to go to court?'), $T('Cela dépend de la juridiction et du montant en jeu. Même lorsqu’il n’est pas obligatoire, l’avocat augmente nettement vos chances d’être entendu.', 'It depends on the court and the amount at stake. Even when it is not mandatory, a lawyer greatly improves your chances of being heard.')],
                    [$T('Combien de temps dure une procédure ?', 'How long do proceedings take?'), $T('De quelques semaines pour un référé à plus d’un an pour un procès au fond. Nous vous donnons une estimation réaliste dès le départ.', 'From a few weeks for an emergency hearing to more than a year for a full trial. We give you a realistic estimate from the outset.')],
                    [$T('Puis-je éviter le procès ?', 'Can I avoid going to trial?'), $T('Souvent, oui : négociation, médiation ou transaction permettent de régler beaucoup de litiges plus vite et à moindre coût.', 'Often, yes: negotiation, mediation or a settlement can resolve many disputes faster and at lower cost.')],
                    [$T('Mon assurance peut-elle prendre en charge mes frais ?', 'Can my insurance cover my legal costs?'), $T('Beaucoup de contrats d’assurance incluent une protection juridique. Apportez votre contrat : nous vérifions avec vous.', 'Many insurance policies include legal expenses cover. Bring your policy and we will check it with you.')],
                ]]),
            ]];

        $pages[] = ['key' => 'firm', 'slug' => $u('le-cabinet', 'the-firm'), 'title' => $T('Le cabinet', 'The firm'),
            'description' => $T('Histoire du cabinet, déontologie et valeurs : indépendance, confidentialité et engagement au service de nos clients.', 'The firm’s story, ethics and values: independence, confidentiality and commitment to our clients.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Le cabinet', 'The firm'), 'title' => $T('Une équipe d’avocats indépendants et engagés', 'A team of independent, committed lawyers'), 'intro' => $T('Nous exerçons avec une conviction : le droit doit être compréhensible et accessible.', 'We practice with one conviction: the law must be understandable and accessible.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('D’un cabinet de quartier à une équipe pluridisciplinaire', 'From a neighborhood practice to a multidisciplinary team'),
                    'html' => '<p>'.e($T('Le cabinet a été fondé par deux avocats qui voulaient offrir aux particuliers et aux PME la même qualité de défense qu’aux grands groupes. Il réunit aujourd’hui associés, collaborateurs et juristes autour de dossiers partagés.', 'The firm was founded by two lawyers who wanted to give individuals and small businesses the same quality of representation as large corporations. Today it brings partners, associates and paralegals together on shared cases.')).'</p><p>'.e($T('Nos avocats sont inscrits au barreau sous les numéros [N° d’inscription] et respectent ses règles déontologiques.', 'Our lawyers are admitted to the bar under numbers [Bar no.] and abide by its rules of professional conduct.')).'</p>',
                    'photo' => ['avocats-bureaux', 'building', $T('Hall d’accueil d’un cabinet d’avocats avec bibliothèque en bois', 'Reception area of a law firm with wooden bookshelves')],
                ]),
                M::timeline($theme, ['eyebrow' => $T('Repères', 'Milestones'), 'title' => $T('Notre parcours', 'Our journey'), 'bg' => 'light', 'items' => [
                    ['2008', $T('Fondation', 'Founded'), $T('Ouverture du cabinet par deux associés, en droit de la famille et des affaires.', 'Two partners open the firm, practicing family and business law.')],
                    ['2013', $T('Droit du travail', 'Employment law'), $T('Création d’un pôle dédié aux relations de travail.', 'An employment law team is created.')],
                    ['2018', $T('Nouveaux associés', 'New partners'), $T('Deux collaborateurs deviennent associés ; le cabinet compte dix personnes.', 'Two associates become partners; the firm grows to ten people.')],
                    ['2023', $T('Rendez-vous en ligne', 'Online meetings'), $T('Consultations en visioconférence et espace client sécurisé.', 'Video consultations and a secure client portal.')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce qui guide notre exercice', 'What guides our practice'), 'items' => [
                    ['scale', $T('Indépendance', 'Independence'), $T('Aucun conflit d’intérêts : nous vérifions chaque dossier avant de l’accepter.', 'No conflicts of interest: we check every case before accepting it.')],
                    ['message-circle', $T('Clarté', 'Clarity'), $T('Des explications sans jargon et des écrits que vous comprenez.', 'Plain explanations and documents you can understand.')],
                    ['heart', $T('Humanité', 'Humanity'), $T('Derrière chaque dossier, une personne ou une entreprise qui traverse une épreuve.', 'Behind every case is a person or a company going through a hard time.')],
                ]]),
                B::quote($theme, ['text' => $T('Le meilleur procès est souvent celui que l’on a su éviter ; mais quand il faut plaider, il faut le faire sans rien laisser au hasard.', 'The best lawsuit is often the one you managed to avoid; but when you must go to court, you leave nothing to chance.'), 'source' => $T('— [Maître Prénom Nom], associée fondatrice', '— [First Last], founding partner'), 'bg' => 'light']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'lawyers', 'slug' => $u('avocats', 'our-lawyers'), 'title' => $T('Nos avocats', 'Our lawyers'),
            'description' => $T('Associés, avocats collaborateurs et juristes : rencontrez l’équipe qui défendra vos intérêts, et leurs domaines de prédilection.', 'Partners, associates and paralegals: meet the team who will defend your interests, and their areas of focus.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nos avocats', 'Our lawyers'), 'title' => $T('Des avocats que vous pouvez joindre', 'Lawyers you can actually reach'), 'intro' => $T('Chaque client a un avocat référent, épaulé par une équipe qui connaît son dossier.', 'Every client has a lead lawyer, backed by a team that knows the case.')]),
                B::team($theme, ['eyebrow' => $T('Associés', 'Partners'), 'title' => $T('Les associés', 'The partners'), 'items' => [
                    ['[Maître Prénom Nom]', $T('Associée fondatrice, droit de la famille', 'Founding partner, family law'), 'avocats-equipe-1'],
                    ['[Maître Prénom Nom]', $T('Associé fondateur, droit des affaires', 'Founding partner, business law'), 'avocats-equipe-2'],
                    ['[Maître Prénom Nom]', $T('Associée, droit du travail', 'Partner, employment law'), 'avocats-equipe-3'],
                    ['[Maître Prénom Nom]', $T('Associé, contentieux et pénal', 'Partner, litigation and criminal law'), 'avocats-equipe-4'],
                ]]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Collaborateurs et juristes', 'Associates and paralegals'), 'bg' => 'light', 'shape' => 'circle', 'items' => [
                    ['[Maître Prénom Nom]', $T('Avocate collaboratrice, droit immobilier', 'Associate, real estate law'), 'avocats-equipe-5'],
                    ['[Maître Prénom Nom]', $T('Avocat collaborateur, droit des affaires', 'Associate, business law'), 'avocats-equipe-6'],
                    ['[Prénom Nom]', $T('Juriste, recouvrement', 'Paralegal, debt recovery'), 'avocats-equipe-7'],
                    ['[Prénom Nom]', $T('Assistante juridique', 'Legal assistant'), 'avocats-equipe-8'],
                ]]),
                B::features($theme, ['eyebrow' => $T('À votre service', 'At your service'), 'title' => $T('Une équipe organisée pour vous répondre', 'A team organized to answer you'), 'cols' => 3, 'items' => [
                    ['languages', $T('Plusieurs langues', 'Several languages'), $T('Rendez-vous possibles en français, en anglais et dans d’autres langues sur demande.', 'Meetings available in French, English and other languages on request.')],
                    ['video', $T('Consultation à distance', 'Remote consultations'), $T('Visioconférence et échanges de pièces par un espace sécurisé.', 'Video calls and document exchange through a secure portal.')],
                    ['calendar-check', $T('Suivi des échéances', 'Deadline tracking'), $T('Délais de procédure et audiences suivis par toute l’équipe.', 'Procedural deadlines and hearings tracked by the whole team.')],
                ]]),
                B::cta($theme, ['title' => $T('Rejoindre le cabinet', 'Join the firm'), 'text' => $T('Nous accueillons des avocats collaborateurs, des juristes et des élèves avocats en stage. Envoyez-nous votre candidature.', 'We welcome associate lawyers, paralegals and law student interns. Send us your application.'),
                    'buttons' => [[$T('Envoyer ma candidature', 'Send my application'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'fees', 'slug' => $u('honoraires', 'fees'), 'title' => $T('Honoraires', 'Fees'),
            'description' => $T('Consultation, forfait ou temps passé : nos honoraires indicatifs, fixés par une convention écrite avant toute intervention.', 'Consultation, flat fee or hourly rate: our indicative fees, set in a written agreement before any work begins.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Honoraires', 'Fees'), 'title' => $T('Des honoraires connus d’avance', 'Fees you know in advance'), 'intro' => $T('Chaque mission fait l’objet d’une convention d’honoraires signée avant de commencer.', 'Every engagement is covered by a fee agreement signed before we start.')]),
                B::pricing($theme, ['url' => $appointment, 'eyebrow' => $T('Exemples de tarifs', 'Sample rates'), 'title' => $T('Trois façons de travailler ensemble', 'Three ways to work together'), 'intro' => $T('Montants indicatifs hors taxes, adaptés à la complexité de votre dossier.', 'Indicative amounts before taxes, adjusted to the complexity of your case.'), 'items' => [
                    [$T('Consultation', 'Consultation'), '120', $T('€', '$'), $T('/ rendez-vous', '/ meeting'), $T('Un avis juridique sur votre situation.', 'Legal advice on your situation.'), [$T('Entretien d’une heure', 'One-hour meeting'), $T('Au cabinet ou en visioconférence', 'At the office or by video'), $T('Analyse de vos documents', 'Review of your documents'), '-'.$T('Rédaction d’actes', 'Drafting of documents')], $T('Prendre rendez-vous', 'Book an appointment')],
                    [$T('Forfait procédure', 'Flat-fee case'), '1 500', $T('€', '$'), $T('à partir de', 'from'), $T('Un prix global pour une procédure définie.', 'One overall price for a defined procedure.'), [$T('Divorce amiable, bail, prud’hommes…', 'Uncontested divorce, lease, labor claim…'), $T('Rédaction des actes et des conclusions', 'Drafting of pleadings and briefs'), $T('Audiences incluses', 'Hearings included'), $T('Paiement en plusieurs fois possible', 'Payment in installments available')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus demandé', 'Most requested')],
                    [$T('Abonnement entreprise', 'Business retainer'), '390', $T('€', '$'), $T('/ mois', '/ month'), $T('Un avocat à vos côtés au quotidien.', 'A lawyer at your side every day.'), [$T('Questions illimitées par téléphone et e-mail', 'Unlimited questions by phone and email'), $T('Relecture de vos contrats', 'Review of your contracts'), $T('Lettres de mise en demeure', 'Formal demand letters'), $T('Tarif réduit pour les procédures', 'Reduced rates for litigation')], $T('Nous contacter', 'Contact us')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Modes de facturation', 'Billing methods'), 'title' => $T('Comment sont calculés nos honoraires', 'How our fees are calculated'), 'rows' => [
                    [$T('Mode', 'Method'), $T('Principe', 'How it works'), $T('Adapté pour', 'Best for')],
                    [$T('Forfait', 'Flat fee'), $T('Prix global fixé à l’avance', 'Overall price set in advance'), $T('Procédures courantes', 'Routine procedures')],
                    [$T('Temps passé', 'Hourly'), $T('Taux horaire de 150 à 250 €', '$150 to $250 per hour'), $T('Dossiers complexes ou incertains', 'Complex or uncertain cases')],
                    [$T('Honoraire de résultat', 'Success fee'), $T('Complément convenu en cas de succès', 'Agreed bonus if successful'), $T('Recouvrement, indemnisation', 'Recovery, compensation claims')],
                    [$T('Abonnement', 'Retainer'), $T('Montant mensuel fixe', 'Fixed monthly amount'), $T('Entreprises et associations', 'Companies and nonprofits')],
                ], 'note' => $T('Frais de justice, d’huissier et de déplacement facturés en sus, sur justificatif.', 'Court, bailiff and travel costs are billed separately, with receipts.')]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur les honoraires', 'Your questions about fees'), 'bg' => 'light', 'items' => [
                    [$T('Le premier échange est-il payant ?', 'Is the first call free?'), $T('Un appel de quelques minutes pour vérifier que nous pouvons vous aider est gratuit. La consultation approfondie est facturée.', 'A short call to check that we can help you is free. An in-depth consultation is billed.')],
                    [$T('Comment puis-je payer ?', 'How can I pay?'), $T('Par carte bancaire, virement ou paiement mobile. Un échéancier peut être prévu dans la convention.', 'By card, bank transfer or mobile payment. An installment plan can be included in the agreement.')],
                    [$T('Puis-je bénéficier d’une aide juridictionnelle ?', 'Can I get legal aid?'), $T('Selon vos revenus et le droit applicable, une aide publique peut couvrir tout ou partie des frais. Nous vous aidons à la demander.', 'Depending on your income and the applicable law, public legal aid may cover all or part of the costs. We help you apply.')],
                    [$T('Les frais sont-ils récupérables auprès de l’adversaire ?', 'Can I recover costs from the other party?'), $T('Le tribunal peut condamner la partie perdante à verser une somme au titre de vos frais. Ce montant n’est jamais garanti.', 'The court may order the losing party to pay part of your costs. That amount is never guaranteed.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'appointment', 'slug' => $u('rendez-vous', 'appointments'), 'title' => $T('Rendez-vous', 'Appointments'),
            'description' => $T('Prenez rendez-vous avec un avocat, au cabinet ou en visioconférence. Pièces à préparer, déroulement et réponse sous 48 heures.', 'Book a meeting with a lawyer, at the office or by video. Documents to prepare, what to expect and a reply within 48 hours.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Rendez-vous', 'Appointments'), 'title' => $T('Rencontrer un avocat', 'Meet a lawyer'), 'intro' => $T('Au cabinet ou en visioconférence, en semaine et certains samedis matin.', 'At the office or by video, on weekdays and some Saturday mornings.')]),
                B::steps($theme, ['eyebrow' => $T('Simple et rapide', 'Quick and simple'), 'title' => $T('Votre rendez-vous en trois étapes', 'Your appointment in three steps'), 'items' => [
                    [$T('Votre demande', 'Your request'), $T('Décrivez brièvement votre situation dans le formulaire ci-dessous.', 'Briefly describe your situation in the form below.')],
                    [$T('Notre rappel', 'Our call back'), $T('Sous 48 heures, nous vous proposons un créneau et l’avocat le plus adapté.', 'Within 48 hours, we offer you a time slot and the most suitable lawyer.')],
                    [$T('La consultation', 'The consultation'), $T('Une heure pour analyser vos documents et définir la suite.', 'One hour to review your documents and decide on next steps.')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Bien préparer', 'Getting ready'), 'title' => $T('Avant votre rendez-vous', 'Before your appointment'), 'groups' => [
                    [$T('Documents à apporter', 'Documents to bring'), [
                        ['id-card', $T('Une pièce d’identité', 'Photo identification')],
                        ['file-text', $T('Contrats, courriers et décisions reçus', 'Contracts, letters and rulings received')],
                        ['calendar-days', $T('Une chronologie des faits, même succincte', 'A timeline of events, even a short one')],
                        ['receipt', $T('Votre contrat d’assurance (protection juridique)', 'Your insurance policy (legal expenses cover)')],
                    ]],
                    [$T('Modalités', 'Practical details'), [
                        ['clock', $T('Lundi – vendredi, 9 h – 19 h', 'Monday – Friday, 9 am – 7 pm')],
                        ['calendar-check', $T('Samedi matin sur rendez-vous', 'Saturday mornings by appointment')],
                        ['video', $T('Visioconférence sur demande', 'Video calls on request')],
                        ['triangle-alert', $T('Urgence (garde à vue, audience proche) : appelez-nous', 'Emergency (police custody, imminent hearing): call us')],
                    ]],
                ]]),
                B::contact($theme, [
                    'eyebrow' => $T('Demande de rendez-vous', 'Appointment request'),
                    'title' => $T('Décrivez votre situation', 'Describe your situation'), 'intro' => $T('Quelques lignes suffisent. Vos informations sont couvertes par le secret professionnel.', 'A few lines are enough. Your information is protected by attorney–client privilege.'),
                    'items' => [
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['shield-check', $T('Échanges confidentiels', 'Confidential exchanges')],
                    ],
                    'subject' => true,
                    'button' => $T('Demander un rendez-vous', 'Request an appointment'),
                    'bg' => 'light',
                ]),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites', 'legal-news'), 'title' => $T('Actualités juridiques', 'Legal news'),
            'description' => $T('Articles pratiques sur le droit de la famille, du travail et des affaires, et rencontres d’information gratuites organisées par le cabinet.', 'Practical articles on family, employment and business law, and free information sessions hosted by the firm.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités juridiques', 'Legal news'), 'title' => $T('Le droit expliqué simplement', 'The law, explained simply'), 'intro' => $T('Des articles rédigés par nos avocats. Ils informent mais ne remplacent pas une consultation.', 'Articles written by our lawyers. They inform but do not replace a consultation.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['icon' => 'presentation', 'eyebrow' => $T('Rencontres', 'Events'), 'title' => $T('Ateliers d’information gratuits', 'Free information sessions'), 'bg' => 'light', 'items' => [
                    [$T('Se séparer : les étapes et les droits de chacun', 'Separating: steps and everyone’s rights'), $T('Mardi 14 janvier, 18 h 30', 'Tuesday, January 14, 6:30 pm'), $T('Au cabinet', 'At the firm')],
                    [$T('Employeurs : réussir un recrutement sans risque', 'Employers: hiring without legal risk'), $T('Jeudi 6 février, 12 h', 'Thursday, February 6, noon'), $T('En ligne', 'Online')],
                    [$T('Bail commercial : les clauses à négocier', 'Commercial leases: clauses to negotiate'), $T('Mercredi 12 mars, 18 h', 'Wednesday, March 12, 6 pm'), $T('Chambre de commerce', 'Chamber of commerce')],
                    [$T('Préparer sa succession', 'Planning your estate'), $T('Samedi 5 avril, 10 h', 'Saturday, April 5, 10 am'), $T('Au cabinet', 'At the firm')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Coordonnées du cabinet d’avocats, horaires et accès. Écrivez-nous : un avocat vous répond sous 48 heures, en toute confidentialité.', 'Law firm contact details, hours and directions. Write to us: a lawyer will reply within 48 hours, in strict confidence.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Nous contacter', 'Contact us'), 'intro' => $T('Par téléphone, par e-mail ou au cabinet, sur rendez-vous.', 'By phone, by email or at the office, by appointment.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Précisez l’objet de votre demande : nous l’orientons vers l’avocat compétent.', 'Tell us what your request is about: we will route it to the right lawyer.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 9 h – 19 h', 'Monday – Friday, 9 am – 7 pm')],
                    ],
                    'social' => ['facebook', 'x', 'whatsapp'],
                    'subject' => true,
                    'bg' => 'white',
                ]),
                B::schedule($theme, ['title' => $T('Venir au cabinet', 'Visiting the firm'), 'groups' => [
                    [$T('Accès', 'Getting here'), [
                        ['bus', $T('Arrêt de bus à 3 minutes à pied', 'Bus stop a 3-minute walk away')],
                        ['car', $T('Parking public à proximité', 'Public parking nearby')],
                        ['accessibility', $T('Bureaux accessibles aux personnes à mobilité réduite', 'Offices accessible to people with reduced mobility')],
                    ]],
                    [$T('Permanences', 'Drop-in hours'), [
                        ['calendar-days', $T('Mercredi 17 h – 19 h : premier conseil sans rendez-vous', 'Wednesday 5 – 7 pm: first advice without appointment')],
                        ['phone', $T('Urgences pénales : ligne joignable 7 j / 7', 'Criminal emergencies: line available 7 days a week')],
                        ['mail', $T('Réponse écrite sous 48 heures ouvrées', 'Written reply within 2 business days')],
                    ]],
                ]]),
            ]];

        $blog = [
            'base' => $u('actualites', 'legal-news'),
            'title' => $T('Actualités juridiques', 'Legal news'),
            'categories' => ['famille' => $T('Famille', 'Family'), 'travail' => $T('Travail', 'Employment'), 'entreprise' => $T('Entreprise', 'Business')],
            'posts' => [
                ['key' => 'divorce', 'slug' => $u('divorce-amiable-etapes', 'uncontested-divorce-steps'), 'category' => 'famille', 'tags' => [$T('Divorce', 'Divorce')],
                    'title' => $T('Divorce amiable : les étapes clés', 'Uncontested divorce: the key steps'),
                    'excerpt' => $T('Accord sur les enfants, le logement et les biens : comment préparer une séparation apaisée.', 'Agreeing on children, housing and property: how to prepare a peaceful separation.'),
                    'photo' => ['avocats-article-famille', 'heart-handshake', $T('Mains d’un couple signant des documents sur une table en bois', 'Hands of a couple signing documents on a wooden table')],
                    'heading' => $T('Ce qu’il faut décider ensemble', 'What you need to agree on'),
                    'body' => [
                        $T('Le divorce amiable suppose que les deux époux soient d’accord sur le principe de la séparation et sur toutes ses conséquences. Chacun est en principe assisté de son propre avocat.', 'An uncontested divorce requires both spouses to agree on the separation and all its consequences. Each spouse is usually assisted by their own lawyer.'),
                        $T('Résidence des enfants, pension alimentaire, attribution du logement, partage des biens et des dettes : ces points sont consignés dans une convention, relue et discutée avant signature.', 'Children’s residence, support payments, the family home, division of assets and debts: these points are set out in an agreement, reviewed and discussed before signing.'),
                        $T('Les règles et les délais varient selon le pays : votre avocat vous indique la procédure applicable à votre situation.', 'Rules and timelines vary from country to country: your lawyer will tell you which procedure applies to you.'),
                    ]],
                ['key' => 'licenciement', 'slug' => $u('licenciement-vos-droits', 'dismissal-your-rights'), 'category' => 'travail', 'tags' => [$T('Salariés', 'Employees')],
                    'title' => $T('Licenciement : connaître ses droits', 'Dismissal: know your rights'),
                    'excerpt' => $T('Procédure, motifs, indemnités : les réflexes à avoir dès la convocation.', 'Procedure, grounds, severance: what to do as soon as you are notified.'),
                    'photo' => ['avocats-article-travail', 'briefcase', $T('Salariée lisant un courrier à son bureau, l’air concentré', 'Employee reading a letter at her desk, looking focused')],
                    'quote' => $T('« Les délais pour contester sont souvent courts : agir vite, c’est préserver ses droits. »', '“Deadlines to challenge a dismissal are often short: acting fast protects your rights.”'),
                    'body' => [
                        $T('Un licenciement doit reposer sur un motif réel et respecter une procédure : convocation, entretien, notification écrite. Une irrégularité peut ouvrir droit à indemnisation.', 'A dismissal must rest on genuine grounds and follow a procedure: notice of a meeting, the meeting itself, written notification. Any irregularity may entitle you to compensation.'),
                        $T('Conservez tous les documents : contrat, bulletins de salaire, courriels, attestations de collègues. Ils serviront à établir les faits.', 'Keep every document: contract, payslips, emails, statements from colleagues. They will help establish the facts.'),
                        $T('Un avocat peut négocier une rupture plus favorable ou saisir la juridiction compétente dans les délais.', 'A lawyer can negotiate better exit terms or take the case to the competent court in time.'),
                    ]],
                ['key' => 'impayes', 'slug' => $u('factures-impayees', 'unpaid-invoices'), 'category' => 'entreprise', 'tags' => [$T('Recouvrement', 'Debt recovery')],
                    'title' => $T('Factures impayées : que faire ?', 'Unpaid invoices: what can you do?'),
                    'excerpt' => $T('Relance, mise en demeure, injonction de payer : la marche à suivre pour récupérer votre argent.', 'Reminders, formal demand, payment order: the steps to recover your money.'),
                    'photo' => ['avocats-article-entreprise', 'receipt', $T('Dirigeant examinant des factures et un ordinateur portable sur son bureau', 'Business owner reviewing invoices and a laptop at his desk')],
                    'heading' => $T('Les étapes du recouvrement', 'The recovery steps'),
                    'body' => [
                        $T('Commencez par une relance écrite et courtoise : beaucoup d’impayés sont des oublis. Gardez une trace de chaque échange.', 'Start with a polite written reminder: many unpaid invoices are simple oversights. Keep a record of every exchange.'),
                        $T('Si rien ne bouge, une mise en demeure envoyée par un avocat produit souvent son effet. À défaut, des procédures simplifiées permettent d’obtenir un titre exécutoire.', 'If nothing happens, a formal demand sent by a lawyer often does the trick. Otherwise, simplified procedures let you obtain an enforceable order.'),
                        $T('Pour l’avenir, des conditions générales claires et des acomptes limitent le risque.', 'For the future, clear terms and conditions and upfront deposits reduce the risk.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Domaines', 'Practice areas'), 'url' => $practice],
                    ['label' => $T('Le cabinet', 'The firm'), 'url' => $firm],
                    ['label' => $T('Avocats', 'Lawyers'), 'url' => $lawyers],
                    ['label' => $T('Honoraires', 'Fees'), 'url' => $fees],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Prendre rendez-vous', 'Book an appointment'),
                'cta_url' => $appointment,
                'footer_text' => $T('Cabinet d’avocats : conseil et défense des particuliers et des entreprises, en toute confidentialité. Les informations de ce site ne constituent pas une consultation juridique.', 'Law firm: advice and representation for individuals and businesses, in strict confidence. The information on this site does not constitute legal advice.'),
                'footer_links' => [
                    ['label' => $T('Domaines du droit', 'Practice areas'), 'url' => $practice],
                    ['label' => $T('Honoraires', 'Fees'), 'url' => $fees],
                    ['label' => $T('Rendez-vous', 'Appointments'), 'url' => $appointment],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
