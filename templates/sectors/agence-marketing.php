<?php

/*
 * Pack « Agence de communication et marketing digital » (secteur Agences et
 * studios créatifs) : 8 pages, en français et en anglais. Stratégie, réseaux
 * sociaux, référencement, publicité, études de cas, méthode et tarifs. Les
 * textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'agence). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Agences et studios créatifs', 'en' => 'Agencies and creative studios'],
        'icon' => 'palette',
        'keywords' => ['agence', 'communication', 'marketing', 'marketing digital', 'réseaux sociaux', 'référencement', 'SEO', 'publicité', 'community manager', 'branding', 'agency', 'digital marketing', 'social media', 'advertising', 'content', 'brand strategy', 'growth'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $services = '/services';
        $cases = $u('/etudes-de-cas', '/case-studies');
        $method = $u('/methode', '/our-method');
        $pricing = $u('/tarifs', '/pricing');
        $agency = $u('/agence', '/agency');
        $blog = '/blog';
        $contact = '/contact';
        $cur = $T('€', '$');

        $serviceItems = [
            ['target', $T('Stratégie de marque', 'Brand strategy'), $T('Positionnement, cibles, messages clés et plan de communication sur douze mois.', 'Positioning, audiences, key messages and a twelve-month communication plan.')],
            ['share-2', $T('Réseaux sociaux', 'Social media'), $T('Ligne éditoriale, création de contenus, animation de communauté et veille.', 'Editorial line, content creation, community management and social listening.')],
            ['search', $T('Référencement naturel', 'Search engine optimization'), $T('Audit technique, contenus optimisés et netlinking pour gagner des visites durables.', 'Technical audit, optimized content and link building for lasting traffic.')],
            ['megaphone', $T('Publicité en ligne', 'Paid advertising'), $T('Campagnes sur les moteurs de recherche et les réseaux, pilotées au coût par client.', 'Search and social campaigns, managed by cost per customer.')],
            ['pen-tool', $T('Identité visuelle', 'Visual identity'), $T('Logo, charte graphique, supports imprimés et gabarits pour vos équipes.', 'Logo, brand guidelines, print materials and templates for your teams.')],
            ['video', $T('Contenus et vidéo', 'Content and video'), $T('Articles, newsletters, photos et vidéos courtes pensées pour chaque canal.', 'Articles, newsletters, photos and short videos built for each channel.')],
        ];
        $caseItems = [
            ['agence-marketing-cas-cosmetique', 'sparkles', $T('Lancement d’une marque de cosmétiques naturels', 'Launching a natural cosmetics brand'), $T('Identité, influence et publicité sociale : 12 000 abonnés et 3 000 commandes en six mois.', 'Identity, influencer marketing and social ads: 12,000 followers and 3,000 orders in six months.'), $cases],
            ['agence-marketing-cas-restaurant', 'utensils', $T('Restaurant de quartier complet le week-end', 'Neighborhood restaurant fully booked on weekends'), $T('Fiche locale optimisée et contenus vidéo : réservations en ligne multipliées par trois.', 'Optimized local listing and video content: online bookings tripled.'), $cases],
            ['agence-marketing-cas-formation', 'graduation-cap', $T('Centre de formation professionnelle', 'Vocational training center'), $T('Refonte du site et référencement : +140 % de demandes d’inscription en un an.', 'Website redesign and SEO: +140% enrollment requests in one year.'), $cases],
            ['agence-marketing-cas-industrie', 'factory', $T('Fabricant industriel en B2B', 'B2B industrial manufacturer'), $T('Campagnes ciblées auprès des acheteurs : 85 rendez-vous commerciaux qualifiés.', 'Targeted campaigns to buyers: 85 qualified sales meetings.'), $cases],
            ['agence-marketing-cas-association', 'hand-heart', $T('Campagne de collecte pour une association', 'Fundraising campaign for a nonprofit'), $T('Vidéo et emailing de fin d’année : objectif de dons dépassé de 30 %.', 'Year-end video and email campaign: donation goal exceeded by 30%.'), $cases],
            ['agence-marketing-cas-immobilier', 'building', $T('Commercialisation d’une résidence neuve', 'Marketing a new residential development'), $T('Site dédié, visites virtuelles et publicité locale : 70 % des lots réservés avant travaux.', 'Dedicated site, virtual tours and local ads: 70% of units reserved before construction.'), $cases],
        ];
        $testimonials = ['eyebrow' => $T('Ils nous recommandent', 'Client reviews'), 'title' => $T('Des partenaires, pas des prestataires', 'Partners, not just vendors'), 'items' => [
            [$T('L’agence a compris notre marque mieux que nous. Nos publications ont enfin une voix, et nos ventes suivent.', 'The agency understood our brand better than we did. Our posts finally have a voice, and sales are following.'), '[Prénom Nom]', $T('Fondatrice, marque de mode', 'Founder, fashion brand')],
            [$T('Un reporting mensuel clair : on sait ce que rapporte chaque euro investi en publicité.', 'Clear monthly reporting: we know what every dollar spent on ads brings back.'), '[Prénom Nom]', $T('Directeur commercial, PME industrielle', 'Sales director, manufacturing company')],
            [$T('Réactifs, créatifs et honnêtes quand une idée ne marche pas. Exactement ce qu’on cherchait.', 'Responsive, creative and honest when an idea doesn’t work. Exactly what we were looking for.'), '[Prénom Nom]', $T('Responsable marketing, réseau de franchises', 'Marketing manager, franchise network')],
        ]];
        $cta = ['title' => $T('Un projet, une ambition ?', 'Got a project in mind?'), 'text' => $T('Parlez-nous de vos objectifs : nous vous proposons un premier plan d’action et un budget sous une semaine.', 'Tell us about your goals: we will send you a first action plan and budget within a week.'),
            'buttons' => [[$T('Demander une proposition', 'Request a proposal'), $contact, 'accent'], [$T('Voir nos études de cas', 'See our case studies'), $cases, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Agence de communication et marketing digital : stratégie de marque, réseaux sociaux, SEO et publicité en ligne pour des résultats mesurables.', 'Communication and digital marketing agency: brand strategy, social media, SEO and paid ads for measurable results.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Agence de communication et marketing digital', 'Communication and digital marketing agency'),
                    'title' => $T('Faites parler de vous, et mesurez-le', 'Get people talking, and measure it'),
                    'text' => $T('Stratégie, création et campagnes : nous transformons votre marque en conversations, en visites et en clients, avec des chiffres à l’appui.', 'Strategy, creative and campaigns: we turn your brand into conversations, visits and customers, with the numbers to prove it.'),
                    'buttons' => [[$T('Demander une proposition', 'Request a proposal'), $contact], [$T('Nos services', 'Our services'), $services]],
                    'photo' => ['agence-marketing-accueil', 'megaphone', $T('Équipe créative réunie autour d’un tableau couvert de post-it et de maquettes', 'Creative team gathered around a board covered with sticky notes and mockups')],
                ]),
                B::features($theme, ['eyebrow' => $T('Ce que nous faisons', 'What we do'), 'title' => $T('Six expertises, une seule stratégie', 'Six skills, one strategy'), 'items' => $serviceItems]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [150, '+', $T('marques accompagnées', 'brands supported')],
                    [3, ' M', $T('de personnes touchées chaque mois', 'people reached every month')],
                    [14, '', $T('experts du digital', 'digital specialists')],
                    [92, ' %', $T('de clients fidèles après un an', 'client retention after one year')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Études de cas', 'Case studies'), 'title' => $T('Des résultats, pas des promesses', 'Results, not promises'), 'more' => $T('Lire l’étude de cas', 'Read the case study'), 'items' => array_slice($caseItems, 0, 3)]),
                B::steps($theme, ['eyebrow' => $T('Notre méthode', 'Our method'), 'title' => $T('Comprendre, créer, diffuser, mesurer', 'Understand, create, publish, measure'), 'bg' => 'light', 'items' => [
                    [$T('Comprendre', 'Understand'), $T('Atelier de lancement, audit de vos canaux et de vos concurrents.', 'Kickoff workshop, audit of your channels and competitors.')],
                    [$T('Créer', 'Create'), $T('Messages, visuels et contenus testés avant diffusion.', 'Messages, visuals and content tested before launch.')],
                    [$T('Diffuser', 'Publish'), $T('Calendrier éditorial et campagnes au bon moment, sur les bons canaux.', 'Editorial calendar and campaigns at the right time, on the right channels.')],
                    [$T('Mesurer', 'Measure'), $T('Un rapport mensuel et des ajustements continus.', 'A monthly report and continuous adjustments.')],
                ]]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'services', 'slug' => 'services', 'title' => 'Services',
            'description' => $T('Stratégie de marque, réseaux sociaux, référencement naturel, publicité en ligne, identité visuelle et vidéo : les services de l’agence.', 'Brand strategy, social media, SEO, paid advertising, visual identity and video: the agency’s services.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Services', 'title' => $T('Tout ce qu’il faut pour grandir en ligne', 'Everything you need to grow online'), 'intro' => $T('Des services à la carte ou réunis dans un accompagnement mensuel.', 'Services à la carte or combined in a monthly retainer.')]),
                B::features($theme, ['items' => $serviceItems, 'cols' => 3, 'bg' => 'light']),
                B::split($theme, [
                    'eyebrow' => $T('Réseaux sociaux', 'Social media'),
                    'title' => $T('Une communauté qui vous ressemble', 'A community that reflects who you are'),
                    'html' => '<p>'.e($T('Nous définissons votre ton, planifions vos publications et répondons à votre communauté. Chaque mois, nous analysons ce qui fonctionne pour faire mieux le mois suivant.', 'We define your tone of voice, plan your posts and engage with your community. Every month we analyze what works to do better the next.')).'</p>',
                    'bullets' => [$T('Calendrier éditorial validé avec vous', 'Editorial calendar approved by you'), $T('Shooting photo et vidéos courtes', 'Photo shoots and short videos'), $T('Modération et réponses aux messages', 'Moderation and message replies'), $T('Partenariats avec des créateurs de contenu', 'Partnerships with content creators')],
                    'photo' => ['agence-marketing-reseaux', 'share-2', $T('Créatrice de contenu filmant un produit avec un smartphone sur trépied', 'Content creator filming a product with a smartphone on a tripod')],
                ]),
                B::split($theme, [
                    'eyebrow' => $T('Référencement et publicité', 'SEO and paid ads'),
                    'title' => $T('Être trouvé au moment où l’on vous cherche', 'Be found the moment people search for you'),
                    'html' => '<p>'.e($T('Le référencement naturel construit une audience durable ; la publicité accélère les résultats. Nous combinons les deux selon votre budget et votre saisonnalité.', 'SEO builds a lasting audience; paid ads speed up results. We combine both based on your budget and seasonality.')).'</p>',
                    'bullets' => [$T('Audit SEO technique et sémantique', 'Technical and keyword SEO audit'), $T('Campagnes optimisées chaque semaine', 'Campaigns optimized every week'), $T('Suivi des conversions et du coût par client', 'Tracking of conversions and cost per customer')],
                    'buttons' => [[$T('Voir les tarifs', 'See pricing'), $pricing]],
                    'photo' => ['agence-marketing-seo', 'chart-line', $T('Analyste marketing étudiant des courbes de trafic sur un grand écran', 'Marketing analyst studying traffic charts on a large screen')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'outline']], 'items' => [
                    [$T('En combien de temps voit-on des résultats ?', 'How soon will we see results?'), $T('La publicité produit des effets en quelques jours ; le référencement naturel et la croissance d’une communauté demandent trois à six mois.', 'Paid ads show effects within days; SEO and community growth take three to six months.')],
                    [$T('Qui possède les contenus et les comptes ?', 'Who owns the content and accounts?'), $T('Vous. Les comptes publicitaires, les réseaux et les fichiers sources restent à votre nom.', 'You do. Ad accounts, social profiles and source files remain in your name.')],
                    [$T('Travaillez-vous avec de petits budgets ?', 'Do you work with small budgets?'), $T('Oui : nous priorisons alors un ou deux canaux à fort impact plutôt que de disperser vos moyens.', 'Yes: we then focus on one or two high-impact channels instead of spreading your budget thin.')],
                    [$T('Pouvez-vous travailler avec notre équipe interne ?', 'Can you work with our in-house team?'), $T('Bien sûr. Nous pouvons former vos équipes, les épauler ou prendre en charge une partie précise.', 'Of course. We can train your team, support them or take over a specific part.')],
                ]]),
            ]];

        $pages[] = ['key' => 'cases', 'slug' => $u('etudes-de-cas', 'case-studies'), 'title' => $T('Études de cas', 'Case studies'),
            'description' => $T('Études de cas de l’agence : cosmétiques, restauration, formation, industrie, association et immobilier. Objectifs, actions et résultats chiffrés.', 'Agency case studies: cosmetics, restaurants, training, industry, nonprofits and real estate. Goals, actions and measured results.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Études de cas', 'Case studies'), 'title' => $T('Ce que nous avons construit avec nos clients', 'What we have built with our clients'), 'intro' => $T('Chaque projet part d’un objectif chiffré. Voici comment nous l’avons atteint.', 'Every project starts from a measurable goal. Here is how we reached it.')]),
                B::cards($theme, ['items' => array_map(fn ($c) => array_slice($c, 0, 4), $caseItems)]),
                M::chart($theme, [
                    'eyebrow' => $T('Résultats', 'Results'),
                    'title' => $T('Ce que change une stratégie suivie', 'What a consistent strategy changes'),
                    'html' => $T('Moyenne observée chez nos clients accompagnés depuis au moins un an, par rapport à l’année précédant notre collaboration.', 'Average observed across clients we have supported for at least one year, compared with the year before working with us.'),
                    'bullets' => [$T('Résultats variables selon le secteur et le budget', 'Results vary by industry and budget'), $T('Objectifs fixés ensemble dès le départ', 'Goals set together from the start')],
                    'bg' => 'light',
                    'chart' => ['kind' => 'bar', 'suffix' => ' %', 'series' => [$T('Progression', 'Increase')], 'rows' => [
                        [$T('Visites du site', 'Website visits'), '120'],
                        [$T('Abonnés', 'Followers'), '85'],
                        [$T('Demandes de contact', 'Leads'), '64'],
                        [$T('Ventes en ligne', 'Online sales'), '48'],
                    ]],
                ]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'method', 'slug' => $u('methode', 'our-method'), 'title' => $T('Notre méthode', 'Our method'),
            'description' => $T('La méthode de l’agence en cinq étapes : atelier de lancement, stratégie, création, diffusion et mesure des résultats chaque mois.', 'The agency’s five-step method: kickoff workshop, strategy, creative, publishing and monthly measurement of results.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Notre méthode', 'Our method'), 'title' => $T('Une méthode simple, des décisions fondées sur les chiffres', 'A simple method, decisions based on data'), 'intro' => $T('Pas de recette toute faite : un cadre de travail clair, adapté à chaque marque.', 'No one-size-fits-all recipe: a clear framework adapted to every brand.')]),
                B::steps($theme, ['items' => [
                    [$T('Atelier de lancement', 'Kickoff workshop'), $T('Une demi-journée pour définir objectifs, cibles et indicateurs.', 'Half a day to define goals, audiences and KPIs.')],
                    [$T('Stratégie', 'Strategy'), $T('Plan de communication, canaux prioritaires et budget.', 'Communication plan, priority channels and budget.')],
                    [$T('Création', 'Creative'), $T('Concepts, visuels et messages testés auprès de votre cible.', 'Concepts, visuals and messages tested with your audience.')],
                    [$T('Diffusion', 'Publishing'), $T('Publications, campagnes et relations presse coordonnées.', 'Coordinated posts, campaigns and press relations.')],
                    [$T('Mesure', 'Measurement'), $T('Rapport mensuel, bilan trimestriel et nouvelles pistes.', 'Monthly report, quarterly review and new ideas.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Atelier de lancement', 'Kickoff workshop'),
                    'title' => $T('Tout commence par une demi-journée ensemble', 'It all starts with half a day together'),
                    'html' => '<p>'.e($T('Nous réunissons vos décideurs pour clarifier ce qui rend votre offre unique, qui sont vos meilleurs clients et ce que vous voulez avoir accompli dans un an. Vous repartez avec une feuille de route écrite.', 'We bring your decision-makers together to clarify what makes your offer unique, who your best customers are and what you want to have achieved in a year. You leave with a written roadmap.')).'</p>',
                    'bullets' => [$T('Portraits de vos clients types', 'Profiles of your ideal customers'), $T('Analyse de trois concurrents', 'Analysis of three competitors'), $T('Indicateurs de réussite chiffrés', 'Quantified success metrics')],
                    'photo' => ['agence-marketing-atelier', 'lightbulb', $T('Atelier de stratégie avec des clients autour d’une grande table et d’un mur de post-it', 'Strategy workshop with clients around a large table and a wall of sticky notes')],
                    'bg' => 'light',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos principes', 'Our principles'), 'title' => $T('Notre façon de travailler', 'How we work'), 'items' => [
                    ['chart-column', $T('Mesurer ce qui compte', 'Measure what matters'), $T('Des ventes et des contacts, pas seulement des « j’aime ».', 'Sales and leads, not just likes.')],
                    ['users', $T('Un chef de projet dédié', 'A dedicated project lead'), $T('Un seul interlocuteur qui connaît votre marque par cœur.', 'One contact who knows your brand inside out.')],
                    ['refresh-cw', $T('Tester et ajuster', 'Test and adjust'), $T('Chaque campagne est testée, puis améliorée.', 'Every campaign is tested, then improved.')],
                    ['eye', $T('Transparence', 'Transparency'), $T('Budgets publicitaires et résultats visibles en temps réel.', 'Ad spend and results visible in real time.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'pricing', 'slug' => $u('tarifs', 'pricing'), 'title' => $T('Tarifs', 'Pricing'),
            'description' => $T('Tarifs de l’agence : accompagnements mensuels Visibilité, Croissance et Performance, et prestations à la carte. Sans engagement long.', 'Agency pricing: Visibility, Growth and Performance monthly retainers, plus à la carte services. No long-term commitment.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Tarifs', 'Pricing'), 'title' => $T('Des formules claires, un budget maîtrisé', 'Clear plans, a budget under control'), 'intro' => $T('Honoraires de l’agence hors budget publicitaire, qui reste payé directement aux plateformes.', 'Agency fees exclude ad spend, which you pay directly to the platforms.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Visibilité', 'Visibility'), '690', $cur, $T('/ mois', '/ month'), $T('Pour exister sur les réseaux, régulièrement.', 'To show up on social media, consistently.'), [$T('2 réseaux sociaux animés', '2 social networks managed'), $T('12 publications par mois', '12 posts a month'), $T('Rapport mensuel', 'Monthly report'), '-'.$T('Campagnes publicitaires', 'Ad campaigns')], $T('Démarrer', 'Get started')],
                    [$T('Croissance', 'Growth'), '1 490', $cur, $T('/ mois', '/ month'), $T('Pour générer des contacts et des ventes.', 'To generate leads and sales.'), [$T('Tout Visibilité', 'Everything in Visibility'), $T('Gestion des campagnes publicitaires', 'Ad campaign management'), $T('Référencement : 2 contenus par mois', 'SEO: 2 articles a month'), $T('Point mensuel avec votre chef de projet', 'Monthly call with your project lead')], $T('Demander une proposition', 'Request a proposal'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Performance', 'Performance'), '2 990', $cur, $T('/ mois', '/ month'), $T('Pour les marques qui accélèrent.', 'For brands ready to accelerate.'), [$T('Tout Croissance', 'Everything in Growth'), $T('Vidéos courtes chaque mois', 'Short videos every month'), $T('Partenariats avec des créateurs', 'Creator partnerships'), $T('Atelier stratégique trimestriel', 'Quarterly strategy workshop')], $T('Nous contacter', 'Contact us')],
                ]]),
                M::table($theme, ['eyebrow' => $T('À la carte', 'À la carte'), 'title' => $T('Prestations ponctuelles', 'One-off projects'), 'bg' => 'light', 'rows' => [
                    [$T('Prestation', 'Service'), $T('Délai', 'Timeline'), $T('À partir de', 'From')],
                    [$T('Atelier stratégie de marque', 'Brand strategy workshop'), $T('2 semaines', '2 weeks'), $T('1 200 €', '$1,300')],
                    [$T('Logo et charte graphique', 'Logo and brand guidelines'), $T('4 semaines', '4 weeks'), $T('2 400 €', '$2,600')],
                    [$T('Audit SEO complet', 'Full SEO audit'), $T('3 semaines', '3 weeks'), $T('900 €', '$1,000')],
                    [$T('Shooting photo produits (demi-journée)', 'Product photo shoot (half day)'), $T('1 semaine', '1 week'), $T('650 €', '$700')],
                    [$T('Vidéo de marque (60 secondes)', 'Brand video (60 seconds)'), $T('3 semaines', '3 weeks'), $T('1 800 €', '$1,950')],
                ], 'note' => $T('Montants indicatifs hors taxes. Devis détaillé gratuit sous une semaine.', 'Indicative amounts before taxes. Free detailed quote within a week.')]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur nos tarifs', 'Your questions about pricing'), 'items' => [
                    [$T('Le budget publicitaire est-il inclus ?', 'Is ad spend included?'), $T('Non. Il est payé directement aux plateformes depuis votre compte ; nous vous conseillons un montant selon vos objectifs.', 'No. It is paid directly to the platforms from your account; we recommend an amount based on your goals.')],
                    [$T('Y a-t-il une durée minimale ?', 'Is there a minimum term?'), $T('Nous demandons trois mois pour que les actions produisent leurs effets, puis l’accompagnement est résiliable chaque mois.', 'We ask for three months so the work can show results; after that, the retainer can be cancelled monthly.')],
                    [$T('Comment se fait le paiement ?', 'How do I pay?'), $T('Par carte bancaire, virement ou paiement mobile, au début de chaque mois. Les prestations ponctuelles demandent un acompte de 40 %.', 'By card, bank transfer or mobile payment, at the start of each month. One-off projects require a 40% deposit.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'agency', 'slug' => $u('agence', 'agency'), 'title' => $T('L’agence', 'The agency'),
            'description' => $T('L’agence de communication : notre histoire, nos valeurs, l’équipe de stratèges, créatifs et experts du digital, et nos locaux.', 'The communication agency: our story, our values, our team of strategists, creatives and digital experts, and our studio.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’agence', 'The agency'), 'title' => $T('Des créatifs qui aiment les chiffres', 'Creatives who love numbers'), 'intro' => $T('Stratèges, designers, rédacteurs et analystes réunis dans une même équipe.', 'Strategists, designers, copywriters and analysts in one team.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Née de l’envie de rendre le marketing utile', 'Born from a wish to make marketing useful'),
                    'html' => '<p>'.e($T('L’agence a été créée par une directrice artistique et un spécialiste de la publicité en ligne, lassés des campagnes jolies mais sans effet. Depuis, nous associons la créativité et la rigueur de la mesure pour des marques de toutes tailles.', 'The agency was founded by an art director and a paid-media specialist tired of campaigns that looked good but achieved nothing. Since then, we have combined creativity and rigorous measurement for brands of every size.')).'</p>',
                    'photo' => ['agence-marketing-studio', 'palette', $T('Studio de création lumineux avec des designers travaillant sur de grands écrans', 'Bright creative studio with designers working on large screens')],
                ]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Les visages de l’agence', 'The faces of the agency'), 'bg' => 'light', 'shape' => 'circle', 'items' => [
                    ['[Prénom Nom]', $T('Cofondatrice, directrice de création', 'Co-founder, creative director'), 'agence-marketing-equipe-1'],
                    ['[Prénom Nom]', $T('Cofondateur, responsable acquisition', 'Co-founder, head of acquisition'), 'agence-marketing-equipe-2'],
                    ['[Prénom Nom]', $T('Responsable réseaux sociaux', 'Social media lead'), 'agence-marketing-equipe-3'],
                    ['[Prénom Nom]', $T('Consultant SEO', 'SEO consultant'), 'agence-marketing-equipe-4'],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('Les coulisses', 'Behind the scenes'), 'title' => $T('Une journée à l’agence', 'A day at the agency'), 'photos' => [
                    ['agence-marketing-coulisses-1', 'camera', $T('Photographe en plein shooting de produits sur fond coloré', 'Photographer in the middle of a product shoot on a colored backdrop')],
                    ['agence-marketing-coulisses-2', 'users', $T('Réunion d’équipe détendue dans un espace de travail partagé', 'Relaxed team meeting in a shared workspace')],
                    ['agence-marketing-coulisses-3', 'pen-tool', $T('Designer dessinant un logo sur une tablette graphique', 'Designer sketching a logo on a graphics tablet')],
                ]]),
                B::cta($theme, ['title' => $T('Envie de nous rejoindre ?', 'Want to join us?'), 'text' => $T('Nous cherchons régulièrement des talents créatifs et des profils data. Candidatures spontanées bienvenues.', 'We regularly look for creative talent and data profiles. Open applications welcome.'),
                    'buttons' => [[$T('Envoyer ma candidature', 'Send my application'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'blog', 'slug' => 'blog', 'title' => 'Blog',
            'description' => $T('Conseils de l’agence sur les réseaux sociaux, le référencement et la publicité en ligne, et webinaires gratuits pour les dirigeants.', 'Agency tips on social media, SEO and paid advertising, plus free webinars for business leaders.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Blog', 'title' => $T('Idées et conseils marketing', 'Marketing ideas and tips'), 'intro' => $T('Ce que nous apprenons chaque jour en accompagnant nos clients.', 'What we learn every day working with our clients.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['icon' => 'video', 'eyebrow' => $T('Webinaires', 'Webinars'), 'title' => $T('Apprendre avec l’agence', 'Learn with the agency'), 'bg' => 'light', 'items' => [
                    [$T('Construire un calendrier éditorial en une heure', 'Building an editorial calendar in one hour'), $T('Mardi 13 janvier, 12 h 30', 'Tuesday, January 13, 12:30 pm'), $T('En ligne', 'Online')],
                    [$T('Publicité sociale : les erreurs qui coûtent cher', 'Social ads: costly mistakes to avoid'), $T('Jeudi 5 février, 18 h', 'Thursday, February 5, 6 pm'), $T('En ligne', 'Online')],
                    [$T('Le référencement local pour les commerces', 'Local SEO for shops and services'), $T('Mardi 10 mars, 12 h 30', 'Tuesday, March 10, 12:30 pm'), $T('En ligne', 'Online')],
                    [$T('Matinale : tendances du marketing digital', 'Breakfast talk: digital marketing trends'), $T('Vendredi 3 avril, 8 h 30', 'Friday, April 3, 8:30 am'), $T('À l’agence', 'At the agency')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez l’agence de communication pour un projet, une proposition chiffrée ou un premier échange. Réponse sous 48 heures ouvrées.', 'Contact the communication agency about a project, a costed proposal or a first chat. Reply within 2 business days.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons de votre marque', 'Let’s talk about your brand'), 'intro' => $T('Un premier échange de trente minutes, sans engagement, pour comprendre vos objectifs.', 'A free, no-obligation thirty-minute call to understand your goals.')]),
                B::contact($theme, [
                    'title' => $T('Votre projet', 'Your project'), 'intro' => $T('Décrivez vos objectifs, vos délais et, si possible, votre budget.', 'Describe your goals, timeline and, if possible, your budget.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 9 h – 18 h', 'Monday – Friday, 9 am – 6 pm')],
                    ],
                    'social' => ['instagram', 'tiktok', 'facebook', 'youtube'],
                    'subject' => true,
                    'button' => $T('Envoyer ma demande', 'Send my request'),
                    'bg' => 'white',
                ]),
                B::faq($theme, ['title' => $T('Avant de nous écrire', 'Before you write'), 'bg' => 'light', 'items' => [
                    [$T('Sous quel délai répondez-vous ?', 'How quickly do you reply?'), $T('Sous 48 heures ouvrées, avec une proposition de rendez-vous.', 'Within 2 business days, with a proposed meeting time.')],
                    [$T('Faut-il un cahier des charges ?', 'Do I need a brief?'), $T('Non. Quelques lignes sur votre activité et vos objectifs suffisent ; nous rédigeons le brief ensemble.', 'No. A few lines about your business and goals are enough; we write the brief together.')],
                    [$T('Travaillez-vous à distance ?', 'Do you work remotely?'), $T('Oui, avec des clients dans plusieurs pays, en visioconférence et sur des outils partagés.', 'Yes, with clients in several countries, over video calls and shared tools.')],
                ]]),
            ]];

        $blogData = [
            'base' => 'blog',
            'title' => 'Blog',
            'categories' => ['reseaux' => $T('Réseaux sociaux', 'Social media'), 'seo' => $T('Référencement', 'SEO'), 'publicite' => $T('Publicité', 'Advertising')],
            'posts' => [
                ['key' => 'calendrier', 'slug' => $u('calendrier-editorial', 'editorial-calendar'), 'category' => 'reseaux', 'tags' => [$T('Contenus', 'Content')],
                    'title' => $T('Construire un calendrier éditorial qui tient la distance', 'Building an editorial calendar that lasts'),
                    'excerpt' => $T('Thèmes récurrents, formats et rythme : publier régulièrement sans s’épuiser.', 'Recurring themes, formats and cadence: posting consistently without burning out.'),
                    'photo' => ['agence-marketing-article-calendrier', 'calendar-days', $T('Planning de publications affiché sur un ordinateur portable à côté d’un carnet', 'Posting schedule on a laptop next to a notebook')],
                    'heading' => $T('La méthode des piliers', 'The pillar method'),
                    'body' => [
                        $T('La régularité compte plus que la quantité : mieux vaut trois publications soignées par semaine, toute l’année, qu’un mois intense suivi du silence.', 'Consistency matters more than volume: three polished posts a week, all year long, beat one intense month followed by silence.'),
                        $T('Choisissez trois ou quatre thèmes récurrents (coulisses, conseils, clients, offres) et associez à chacun un format. Votre calendrier se remplit alors presque tout seul.', 'Pick three or four recurring themes (behind the scenes, tips, customers, offers) and pair each with a format. Your calendar then almost fills itself.'),
                        $T('Préparez vos contenus par lots une fois par mois et gardez de la place pour l’actualité.', 'Batch your content once a month and leave room for timely posts.'),
                    ]],
                ['key' => 'seo-local', 'slug' => $u('referencement-local', 'local-seo'), 'category' => 'seo', 'tags' => [$T('Commerces', 'Local business')],
                    'title' => $T('Référencement local : être trouvé près de chez soi', 'Local SEO: getting found nearby'),
                    'excerpt' => $T('Fiche d’établissement, avis clients et pages locales : les leviers les plus rentables.', 'Business listing, customer reviews and local pages: the most cost-effective levers.'),
                    'photo' => ['agence-marketing-article-seo', 'map-pin', $T('Personne cherchant un commerce sur une carte dans son téléphone, dans une rue animée', 'Person searching for a shop on a phone map in a busy street')],
                    'quote' => $T('« Un avis client récent vaut souvent plus qu’une publicité. »', '“A recent customer review is often worth more than an ad.”'),
                    'body' => [
                        $T('Pour un commerce ou un service de proximité, la fiche d’établissement sur les moteurs de recherche et les cartes est souvent le premier point de contact.', 'For a local shop or service, your business listing on search engines and maps is often the very first point of contact.'),
                        $T('Complétez-la entièrement, ajoutez des photos récentes et répondez à chaque avis, positif comme négatif.', 'Fill it out completely, add recent photos and respond to every review, positive or negative.'),
                        $T('Sur votre site, une page par service et par zone desservie aide les moteurs à comprendre où vous travaillez.', 'On your website, one page per service and area served helps search engines understand where you work.'),
                    ]],
                ['key' => 'budget-pub', 'slug' => $u('budget-publicitaire', 'ad-budget'), 'category' => 'publicite', 'tags' => [$T('Budget', 'Budget')],
                    'title' => $T('Quel budget publicitaire pour démarrer ?', 'How much should you spend on ads to start?'),
                    'excerpt' => $T('Coût par client, phase de test et montée en charge : une approche raisonnée.', 'Cost per customer, testing phase and scaling up: a reasoned approach.'),
                    'photo' => ['agence-marketing-article-publicite', 'chart-column', $T('Tableau de résultats de campagne publicitaire affiché sur un écran de bureau', 'Ad campaign results dashboard on an office screen')],
                    'heading' => $T('Partir du coût par client', 'Start from cost per customer'),
                    'body' => [
                        $T('Il n’y a pas de montant magique : tout dépend de ce que vous rapporte un client et de ce que vous êtes prêt à payer pour l’acquérir.', 'There is no magic number: it all depends on what a customer brings you and how much you are willing to pay to acquire one.'),
                        $T('Prévoyez une phase de test de quatre à six semaines avec plusieurs messages et audiences, puis concentrez le budget sur ce qui fonctionne.', 'Plan a four-to-six-week testing phase with several messages and audiences, then concentrate your budget on what works.'),
                        $T('Mesurez les ventes ou les contacts réels, pas seulement les clics : c’est la seule façon de savoir si la publicité est rentable.', 'Measure actual sales or leads, not just clicks: it is the only way to know whether advertising pays off.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blogData,
            'navigation' => [
                'menu' => [
                    ['label' => 'Services', 'url' => $services],
                    ['label' => $T('Études de cas', 'Case studies'), 'url' => $cases],
                    ['label' => $T('Méthode', 'Method'), 'url' => $method],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('L’agence', 'Agency'), 'url' => $agency],
                    ['label' => 'Blog', 'url' => $blog],
                ],
                'cta_label' => $T('Demander une proposition', 'Request a proposal'),
                'cta_url' => $contact,
                'footer_text' => $T('Agence de communication et marketing digital : stratégie, réseaux sociaux, référencement et publicité, avec des résultats mesurés chaque mois.', 'Communication and digital marketing agency: strategy, social media, SEO and advertising, with results measured every month.'),
                'footer_links' => [
                    ['label' => 'Services', 'url' => $services],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('Études de cas', 'Case studies'), 'url' => $cases],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
