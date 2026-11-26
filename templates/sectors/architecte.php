<?php

/*
 * Pack « Studio d'architecture et d'architecture intérieure » (secteur Agences
 * et studios créatifs) : 8 pages, en français et en anglais. Projets, approche,
 * étapes d'un projet, honoraires indicatifs, équipe et journal. Les textes sont
 * des exemples réalistes à personnaliser (l'assistant IA les réécrit à partir de
 * la présentation du studio). Les liens internes sont écrits avec les adresses
 * de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Agences et studios créatifs', 'en' => 'Agencies and creative studios'],
        'icon' => 'palette',
        'keywords' => ['architecte', 'architecture', 'architecture intérieure', 'architecte d’intérieur', 'rénovation', 'construction', 'maison', 'permis de construire', 'aménagement', 'architect', 'interior design', 'interior architecture', 'renovation', 'residential', 'studio', 'building design'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $projects = $u('/projets', '/projects');
        $approach = $u('/approche', '/approach');
        $process = $u('/etapes', '/process');
        $fees = $u('/honoraires', '/fees');
        $studio = '/studio';
        $journal = '/journal';
        $contact = '/contact';
        $cur = $T('€', '$');

        $projectItems = [
            ['architecte-projet-maison', 'house', $T('Maison familiale ouverte sur le jardin', 'Family house opening onto the garden'), $T('Construction neuve, 180 m² : grandes baies, bois et béton brut, toiture végétalisée.', 'New build, 1,900 sq ft: large glazing, wood and raw concrete, green roof.')],
            ['architecte-projet-appartement', 'sofa', $T('Appartement ancien rénové en duplex', 'Period apartment renovated into a duplex'), $T('Rénovation complète, 110 m² : escalier sculptural, cuisine ouverte et parquet restauré.', 'Full renovation, 1,200 sq ft: sculptural staircase, open kitchen and restored floors.')],
            ['architecte-projet-bureaux', 'building-2', $T('Bureaux lumineux pour une jeune entreprise', 'Bright offices for a young company'), $T('Aménagement de 450 m² : espaces ouverts, salles de réunion vitrées et coin détente.', 'Fit-out of 4,800 sq ft: open spaces, glazed meeting rooms and a lounge area.')],
            ['architecte-projet-restaurant', 'utensils', $T('Restaurant aux matériaux naturels', 'Restaurant in natural materials'), $T('Architecture intérieure, 160 couverts : terre cuite, rotin et éclairage sur mesure.', 'Interior architecture, 160 covers: terracotta, rattan and custom lighting.')],
            ['architecte-projet-extension', 'layers', $T('Extension contemporaine d’une maison de ville', 'Contemporary extension to a townhouse'), $T('Extension de 45 m² sur cour : verrière, cuisine et salle à manger baignées de lumière.', '480 sq ft courtyard extension: glass roof, kitchen and dining room bathed in light.')],
            ['architecte-projet-ecole', 'school', $T('École primaire bioclimatique', 'Bioclimatic primary school'), $T('Équipement public de huit classes : ventilation naturelle, brise-soleil et cour arborée.', 'Eight-classroom public building: natural ventilation, sun shading and a tree-lined yard.')],
        ];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Ils ont vécu le projet avec nous', 'They went through the project with us'), 'items' => [
            [$T('Le studio a su traduire nos envies en une maison qui nous ressemble, en tenant le budget annoncé au départ.', 'The studio turned our wishes into a house that feels like us, within the budget announced at the start.'), '[Prénom Nom]', $T('Propriétaires, maison neuve', 'Homeowners, new house')],
            [$T('Un suivi de chantier exemplaire : comptes rendus chaque semaine et aucune mauvaise surprise à la livraison.', 'Exemplary site supervision: weekly reports and no bad surprises at handover.'), '[Prénom Nom]', $T('Gérant, restaurant', 'Owner, restaurant')],
            [$T('Nos bureaux sont devenus un vrai atout pour recruter. Les équipes adorent y travailler.', 'Our offices have become a real asset for hiring. The team loves working there.'), '[Prénom Nom]', $T('Directrice générale, entreprise de services', 'CEO, services company')],
        ]];
        $cta = ['title' => $T('Parlons de votre projet', 'Let’s talk about your project'), 'text' => $T('Construction, rénovation ou aménagement intérieur : un premier rendez-vous pour comprendre vos besoins, votre terrain et votre budget.', 'New build, renovation or interior design: a first meeting to understand your needs, your site and your budget.'),
            'buttons' => [[$T('Prendre rendez-vous', 'Book a meeting'), $contact, 'accent'], [$T('Voir nos projets', 'See our projects'), $projects, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Studio d’architecture et d’architecture intérieure : maisons, rénovations, bureaux et commerces conçus avec soin, de l’esquisse au chantier.', 'Architecture and interior design studio: houses, renovations, offices and shops designed with care, from sketch to site.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Architecture et architecture intérieure', 'Architecture and interior design'),
                    'title' => $T('Des lieux pensés pour la vie qu’on y mène', 'Places designed for the life lived in them'),
                    'text' => $T('Maisons, rénovations, bureaux et commerces : nous dessinons des espaces lumineux, durables et justes, et suivons leur réalisation jusqu’à la remise des clés.', 'Houses, renovations, offices and shops: we design bright, sustainable, well-proportioned spaces and oversee their construction until handover.'),
                    'buttons' => [[$T('Voir nos projets', 'See our projects'), $projects], [$T('Notre approche', 'Our approach'), $approach]],
                    'photo' => ['architecte-accueil', 'house', $T('Maison contemporaine aux grandes baies vitrées au coucher du soleil', 'Contemporary house with large glass windows at sunset')],
                ]),
                B::quote($theme, ['text' => $T('Une bonne architecture ne se remarque pas d’abord : elle se ressent, dans la lumière du matin et la façon dont on circule.', 'Good architecture is not noticed at first: it is felt, in the morning light and in the way you move through a space.'), 'source' => $T('— Le studio', '— The studio')]),
                B::cards($theme, ['eyebrow' => $T('Projets récents', 'Recent projects'), 'title' => $T('Quelques réalisations', 'Selected work'), 'more' => $T('Voir le projet', 'View project'), 'bg' => 'light', 'items' => array_map(fn ($p) => [...$p, $projects], array_slice($projectItems, 0, 3))]),
                B::features($theme, ['eyebrow' => $T('Nos missions', 'What we do'), 'title' => $T('Du terrain nu à la dernière poignée de porte', 'From bare land to the last door handle'), 'cols' => 4, 'items' => [
                    ['house', $T('Construction neuve', 'New builds'), $T('Maisons individuelles et petits immeubles, adaptés au site et au climat.', 'Houses and small buildings, suited to the site and climate.')],
                    ['hammer', $T('Rénovation', 'Renovation'), $T('Transformer l’existant, gagner en lumière et en confort.', 'Transforming existing buildings for more light and comfort.')],
                    ['sofa', $T('Architecture intérieure', 'Interior design'), $T('Plans, matériaux, mobilier et éclairage sur mesure.', 'Layouts, materials, furniture and custom lighting.')],
                    ['store', $T('Lieux professionnels', 'Commercial spaces'), $T('Bureaux, boutiques et restaurants qui servent votre activité.', 'Offices, shops and restaurants that serve your business.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Notre approche', 'Our approach'),
                    'title' => $T('Lumière, climat et matière', 'Light, climate and materials'),
                    'html' => '<p>'.e($T('Chaque projet commence par l’observation du lieu : orientation, vents, vues, usages. Nous privilégions des matériaux sains et durables, et une conception bioclimatique qui réduit les besoins en énergie.', 'Every project begins by observing the site: orientation, winds, views, uses. We favor healthy, durable materials and bioclimatic design that cuts energy needs.')).'</p>',
                    'bullets' => [$T('Esquisses dessinées à la main et maquettes 3D', 'Hand-drawn sketches and 3D models'), $T('Budget suivi à chaque étape', 'Budget tracked at every stage'), $T('Un architecte référent jusqu’à la livraison', 'One lead architect until handover')],
                    'buttons' => [[$T('Découvrir notre approche', 'Discover our approach'), $approach, 'outline']],
                    'photo' => ['architecte-esquisse', 'pencil', $T('Architecte dessinant une esquisse sur un calque posé sur des plans', 'Architect drawing a sketch on tracing paper over plans')],
                    'reverse' => true,
                ]),
                B::stats($theme, ['items' => [
                    [120, '+', $T('projets livrés', 'projects delivered')],
                    [15, '', $T('années d’expérience', 'years of experience')],
                    [8, '', $T('architectes et designers', 'architects and designers')],
                    [3, '', $T('prix d’architecture', 'architecture awards')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'projects', 'slug' => $u('projets', 'projects'), 'title' => $T('Projets', 'Projects'),
            'description' => $T('Projets du studio d’architecture : maisons neuves, rénovations, extensions, bureaux, restaurants et équipements publics.', 'Projects by the architecture studio: new houses, renovations, extensions, offices, restaurants and public buildings.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Projets', 'Projects'), 'title' => $T('Des projets à l’écoute de leur lieu', 'Projects attuned to their setting'), 'intro' => $T('Habitat, lieux de travail, commerces et équipements : une sélection de réalisations récentes.', 'Homes, workplaces, shops and public buildings: a selection of recent work.')]),
                B::cards($theme, ['items' => $projectItems]),
                B::gallery($theme, ['eyebrow' => $T('Détails', 'Details'), 'title' => $T('Matières et lumière', 'Materials and light'), 'bg' => 'light', 'photos' => [
                    ['architecte-detail-escalier', 'layers', $T('Escalier en bois clair éclairé par une fenêtre zénithale', 'Light wood staircase lit by a skylight')],
                    ['architecte-detail-cuisine', 'utensils', $T('Cuisine minimaliste en béton ciré et bois', 'Minimalist kitchen in polished concrete and wood')],
                    ['architecte-detail-salon', 'sofa', $T('Salon lumineux avec grande baie vitrée donnant sur un jardin', 'Bright living room with a large window onto a garden')],
                    ['architecte-detail-facade', 'building', $T('Façade avec brise-soleil en lames de bois', 'Facade with wooden louvered sun shading')],
                    ['architecte-detail-salle-de-bain', 'droplet', $T('Salle de bain en pierre naturelle et lumière douce', 'Bathroom in natural stone with soft light')],
                    ['architecte-detail-patio', 'leaf', $T('Patio intérieur planté au cœur d’une maison', 'Planted inner courtyard at the heart of a house')],
                ]]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'approach', 'slug' => $u('approche', 'approach'), 'title' => $T('Approche', 'Approach'),
            'description' => $T('L’approche du studio : conception bioclimatique, matériaux durables, lumière naturelle et architecture intérieure sur mesure.', 'The studio’s approach: bioclimatic design, durable materials, natural light and bespoke interior design.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Approche', 'Approach'), 'title' => $T('Construire juste, pour longtemps', 'Building right, for the long term'), 'intro' => $T('Une architecture sobre, confortable et ancrée dans son environnement.', 'Understated, comfortable architecture rooted in its surroundings.')]),
                B::pillars($theme, ['items' => [
                    ['sun', $T('Bioclimatique', 'Bioclimatic'), $T('Orientation, ombrage et ventilation naturelle pour un confort sans excès d’énergie.', 'Orientation, shading and natural ventilation for comfort without wasting energy.')],
                    ['leaf', $T('Matériaux durables', 'Durable materials'), $T('Bois, terre, pierre et matériaux locaux, choisis pour leur longévité.', 'Wood, earth, stone and local materials, chosen for their longevity.')],
                    ['lamp', $T('Lumière naturelle', 'Natural light'), $T('Chaque pièce dessinée selon la course du soleil.', 'Every room designed around the path of the sun.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Architecture', 'Architecture'),
                    'title' => $T('Partir du site, pas d’un catalogue', 'Starting from the site, not a catalog'),
                    'html' => '<p>'.e($T('Nous visitons le terrain à différentes heures, étudions les règles d’urbanisme et écoutons vos habitudes de vie. Le projet naît de cette rencontre, jamais d’un modèle tout fait.', 'We visit the site at different times of day, study the zoning rules and listen to how you live. The design grows out of that encounter, never from a ready-made plan.')).'</p>',
                    'bullets' => [$T('Étude du terrain et de l’ensoleillement', 'Site and sunlight study'), $T('Maquettes 3D et visites virtuelles', '3D models and virtual walkthroughs'), $T('Dossiers d’autorisation complets', 'Complete permit applications')],
                    'photo' => ['architecte-maquette', 'building', $T('Maquette en bois d’une maison posée sur une table d’atelier', 'Wooden scale model of a house on a studio table')],
                    'bg' => 'light',
                ]),
                B::split($theme, [
                    'eyebrow' => $T('Architecture intérieure', 'Interior design'),
                    'title' => $T('Des intérieurs dessinés au centimètre près', 'Interiors designed down to the inch'),
                    'html' => '<p>'.e($T('Plans d’aménagement, menuiseries sur mesure, choix des matériaux, des couleurs et de l’éclairage : nous concevons des intérieurs cohérents avec l’architecture et faciles à vivre.', 'Floor plans, custom joinery, choice of materials, colors and lighting: we design interiors that are consistent with the architecture and easy to live in.')).'</p>',
                    'bullets' => [$T('Planches de matériaux et d’ambiance', 'Material and mood boards'), $T('Mobilier et rangements sur mesure', 'Custom furniture and storage'), $T('Coordination des artisans', 'Coordination of craftspeople')],
                    'buttons' => [[$T('Voir les honoraires', 'See our fees'), $fees]],
                    'photo' => ['architecte-interieur', 'sofa', $T('Planche d’échantillons de bois, de tissus et de pierre sur un bureau d’architecte d’intérieur', 'Board of wood, fabric and stone samples on an interior designer’s desk')],
                    'reverse' => true,
                ]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'process', 'slug' => $u('etapes', 'process'), 'title' => $T('Étapes d’un projet', 'Project stages'),
            'description' => $T('Les étapes d’un projet d’architecture : esquisse, avant-projet, permis, consultation des entreprises, chantier et réception des travaux.', 'The stages of an architecture project: sketch design, preliminary design, permit, contractor tendering, construction and handover.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Étapes d’un projet', 'Project stages'), 'title' => $T('Votre projet, étape par étape', 'Your project, step by step'), 'intro' => $T('Vous validez chaque phase avant de passer à la suivante : rien n’est engagé sans votre accord.', 'You approve each phase before moving on: nothing goes ahead without your agreement.')]),
                B::steps($theme, ['items' => [
                    [$T('Esquisse', 'Sketch design'), $T('Analyse du site et premières intentions, en deux ou trois options.', 'Site analysis and initial ideas, in two or three options.')],
                    [$T('Avant-projet', 'Design development'), $T('Plans, volumes, matériaux et estimation du coût des travaux.', 'Plans, volumes, materials and construction cost estimate.')],
                    [$T('Autorisations', 'Permits'), $T('Dépôt du permis de construire ou de la déclaration de travaux.', 'Filing the building permit or works declaration.')],
                    [$T('Consultation', 'Tendering'), $T('Plans d’exécution, appel d’offres et choix des entreprises.', 'Construction drawings, tender and contractor selection.')],
                    [$T('Chantier', 'Construction'), $T('Suivi hebdomadaire, contrôle de la qualité et du budget, réception.', 'Weekly supervision, quality and budget control, handover.')],
                ]]),
                M::timeline($theme, ['eyebrow' => $T('Exemple', 'Example'), 'title' => $T('Le calendrier type d’une maison neuve', 'A typical timeline for a new house'), 'bg' => 'light', 'items' => [
                    [$T('Mois 1 – 2', 'Months 1 – 2'), $T('Conception', 'Design'), $T('Esquisse puis avant-projet, avec deux réunions de travail.', 'Sketch, then design development, with two working sessions.')],
                    [$T('Mois 3 – 5', 'Months 3 – 5'), $T('Autorisations', 'Permits'), $T('Instruction du permis par l’administration, délais variables selon la commune.', 'Permit review by the authorities, timing varies by municipality.')],
                    [$T('Mois 5 – 6', 'Months 5 – 6'), $T('Consultation', 'Tendering'), $T('Comparaison des devis et signature des marchés de travaux.', 'Comparison of bids and signing of construction contracts.')],
                    [$T('Mois 7 – 16', 'Months 7 – 16'), $T('Chantier', 'Construction'), $T('Gros œuvre, second œuvre et finitions, sous notre direction.', 'Structure, fit-out and finishes, under our supervision.')],
                    [$T('Mois 17', 'Month 17'), $T('Réception', 'Handover'), $T('Visite de réception, levée des réserves et remise des clés.', 'Final inspection, snag list cleared and keys handed over.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'buttons' => [[$T('Prendre rendez-vous', 'Book a meeting'), $contact, 'outline']], 'items' => [
                    [$T('Un architecte est-il obligatoire ?', 'Is an architect required?'), $T('Selon votre pays et la surface du projet, le recours à un architecte peut être obligatoire. Même lorsqu’il ne l’est pas, il sécurise le projet et le budget.', 'Depending on your country and the size of the project, hiring an architect may be mandatory. Even when it is not, an architect protects your project and budget.')],
                    [$T('Peut-on ne confier qu’une partie de la mission ?', 'Can I hire you for part of the project only?'), $T('Oui : conception seule, dossier de permis, ou architecture intérieure. La mission complète reste la plus sûre pour tenir le budget.', 'Yes: design only, permit application or interior design. A full commission remains the safest way to stay on budget.')],
                    [$T('Comment le budget est-il maîtrisé ?', 'How is the budget kept under control?'), $T('Une estimation à chaque phase, une consultation de plusieurs entreprises et un suivi mensuel des dépenses du chantier.', 'A cost estimate at every phase, bids from several contractors and monthly tracking of construction spending.')],
                    [$T('Travaillez-vous à distance ?', 'Do you work remotely?'), $T('Pour la conception, oui, en visioconférence. Le suivi de chantier se fait sur place, avec des partenaires locaux si besoin.', 'For design, yes, by video call. Site supervision is done in person, with local partners if needed.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'fees', 'slug' => $u('honoraires', 'fees'), 'title' => $T('Honoraires', 'Fees'),
            'description' => $T('Honoraires indicatifs du studio : conseil et visite, architecture intérieure au mètre carré, mission complète en pourcentage des travaux.', 'The studio’s indicative fees: consultation and site visit, interior design per square foot, full commission as a percentage of construction.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Honoraires', 'Fees'), 'title' => $T('Des honoraires clairs dès le départ', 'Clear fees from the start'), 'intro' => $T('Chaque mission fait l’objet d’un contrat écrit qui précise le périmètre, le calendrier et le prix.', 'Every commission is covered by a written contract setting out scope, schedule and price.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Exemples de tarifs', 'Sample rates'), 'title' => $T('Trois formules', 'Three options'), 'intro' => $T('Montants indicatifs hors taxes, ajustés après la visite du lieu.', 'Indicative amounts before taxes, adjusted after a site visit.'), 'items' => [
                    [$T('Conseil sur place', 'Site consultation'), '250', $cur, $T('la visite', 'per visit'), $T('Un avis d’architecte sur votre bien ou votre terrain.', 'An architect’s view on your property or land.'), [$T('Visite de deux heures', 'Two-hour visit'), $T('Pistes d’aménagement', 'Layout ideas'), $T('Compte rendu écrit et croquis', 'Written summary and sketches'), '-'.$T('Plans détaillés', 'Detailed drawings')], $T('Réserver une visite', 'Book a visit')],
                    [$T('Architecture intérieure', 'Interior design'), $T('60', '6'), $cur, $T('/ m²', '/ sq ft'), $T('Aménagement et décoration d’un logement ou d’un local.', 'Layout and decoration of a home or commercial space.'), [$T('Plans et vues 3D', 'Plans and 3D views'), $T('Choix des matériaux et du mobilier', 'Choice of materials and furniture'), $T('Consultation des artisans', 'Tendering to craftspeople'), $T('Suivi des travaux en option', 'Site supervision optional')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus demandé', 'Most requested')],
                    [$T('Mission complète', 'Full commission'), $T('10 %', '10%'), '', $T('des travaux', 'of construction'), $T('De l’esquisse à la remise des clés.', 'From sketch to handover.'), [$T('Conception et autorisations', 'Design and permits'), $T('Consultation des entreprises', 'Contractor tendering'), $T('Direction du chantier', 'Site supervision'), $T('Réception des travaux', 'Final inspection')], $T('Parler de mon projet', 'Discuss my project')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Répartition', 'Breakdown'), 'title' => $T('Comment se répartissent les honoraires', 'How fees are split across phases'), 'bg' => 'light', 'rows' => [
                    [$T('Phase', 'Phase'), $T('Part des honoraires', 'Share of fees'), $T('Ce que vous recevez', 'What you receive')],
                    [$T('Esquisse', 'Sketch design'), '10 %', $T('Deux ou trois options d’implantation', 'Two or three layout options')],
                    [$T('Avant-projet', 'Design development'), '25 %', $T('Plans, vues 3D et estimation', 'Plans, 3D views and cost estimate')],
                    [$T('Autorisations', 'Permits'), '10 %', $T('Dossier de permis complet', 'Complete permit application')],
                    [$T('Consultation', 'Tendering'), '20 %', $T('Plans d’exécution et analyse des offres', 'Construction drawings and bid analysis')],
                    [$T('Chantier', 'Construction'), '35 %', $T('Visites, comptes rendus et réception', 'Site visits, reports and handover')],
                ], 'note' => $T('Le pourcentage varie de 8 à 14 % selon la complexité et la taille du projet.', 'The percentage ranges from 8 to 14% depending on project size and complexity.')]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur les honoraires', 'Your questions about fees'), 'items' => [
                    [$T('Quand les honoraires sont-ils payés ?', 'When are fees paid?'), $T('À la fin de chaque phase validée, selon l’échéancier du contrat.', 'At the end of each approved phase, according to the contract schedule.')],
                    [$T('Quels moyens de paiement acceptez-vous ?', 'Which payment methods do you accept?'), $T('Virement, carte bancaire ou paiement mobile.', 'Bank transfer, card or mobile payment.')],
                    [$T('Que se passe-t-il si j’arrête le projet ?', 'What if I stop the project?'), $T('Seules les phases engagées sont dues. Vous conservez les documents produits.', 'Only the phases already started are due. You keep the documents produced.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'studio', 'slug' => 'studio', 'title' => $T('Le studio', 'The studio'),
            'description' => $T('Le studio d’architecture : notre histoire, l’équipe d’architectes et d’architectes d’intérieur, et nos chiffres clés.', 'The architecture studio: our story, our team of architects and interior designers, and our key figures.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Le studio', 'The studio'), 'title' => $T('Une équipe, un même regard', 'One team, one way of seeing'), 'intro' => $T('Architectes, architectes d’intérieur et dessinateurs réunis dans un atelier lumineux.', 'Architects, interior designers and draftspeople together in a bright studio.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Un atelier né du goût du détail', 'A studio born from a love of detail'),
                    'html' => '<p>'.e($T('Le studio a été fondé par deux architectes formés à l’architecture bioclimatique et à la rénovation du patrimoine. Il conçoit aujourd’hui des projets privés et publics, avec la même exigence pour une cuisine que pour une école.', 'The studio was founded by two architects trained in bioclimatic design and heritage renovation. It now designs private and public projects, with the same care for a kitchen as for a school.')).'</p><p>'.e($T('Architectes inscrits à l’ordre sous les numéros [N° d’inscription].', 'Architects licensed under registration numbers [License no.].')).'</p>',
                    'photo' => ['architecte-atelier', 'pen-tool', $T('Atelier d’architecture avec grandes tables, plans et maquettes', 'Architecture studio with large tables, drawings and scale models')],
                ]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Les architectes du studio', 'The studio’s architects'), 'bg' => 'light', 'items' => [
                    ['[Prénom Nom]', $T('Architecte, cofondatrice', 'Architect, co-founder'), 'architecte-equipe-1'],
                    ['[Prénom Nom]', $T('Architecte, cofondateur', 'Architect, co-founder'), 'architecte-equipe-2'],
                    ['[Prénom Nom]', $T('Architecte d’intérieur', 'Interior designer'), 'architecte-equipe-3'],
                    ['[Prénom Nom]', $T('Conducteur de travaux', 'Site supervisor'), 'architecte-equipe-4'],
                ]]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [120, '+', $T('projets livrés', 'projects delivered')],
                    [40, '+', $T('rénovations', 'renovations')],
                    [95, ' %', $T('de chantiers livrés dans les délais', 'of projects delivered on time')],
                ]]),
                B::cta($theme, ['title' => $T('Rejoindre le studio', 'Join the studio'), 'text' => $T('Nous accueillons des architectes, des architectes d’intérieur et des stagiaires passionnés par le détail.', 'We welcome architects, interior designers and interns who care about detail.'),
                    'buttons' => [[$T('Envoyer mon portfolio', 'Send my portfolio'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'journal', 'slug' => 'journal', 'title' => 'Journal',
            'description' => $T('Le journal du studio : conseils pour rénover, construire et aménager, coulisses de chantier, visites et portes ouvertes.', 'The studio journal: tips on renovating, building and furnishing, behind the scenes on site, tours and open days.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Journal', 'title' => $T('Idées pour mieux habiter', 'Ideas for better living'), 'intro' => $T('Conseils, coulisses de chantier et réflexions de l’équipe.', 'Tips, behind-the-scenes site stories and thoughts from the team.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire l’article', 'Read the article')]),
                B::events($theme, ['icon' => 'building-2', 'eyebrow' => $T('Rencontres', 'Events'), 'title' => $T('Visites et portes ouvertes', 'Tours and open days'), 'bg' => 'light', 'items' => [
                    [$T('Visite d’une maison bioclimatique livrée', 'Tour of a completed bioclimatic house'), $T('Samedi 17 janvier, 10 h', 'Saturday, January 17, 10 am'), $T('Sur inscription', 'Registration required')],
                    [$T('Portes ouvertes du studio', 'Studio open day'), $T('Jeudi 19 février, 17 h – 20 h', 'Thursday, February 19, 5 – 8 pm'), $T('Au studio', 'At the studio')],
                    [$T('Conférence : rénover sans perdre le caractère', 'Talk: renovating without losing character'), $T('Mardi 24 mars, 18 h 30', 'Tuesday, March 24, 6:30 pm'), $T('Médiathèque municipale', 'Public library')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez le studio d’architecture pour un premier rendez-vous : construction, rénovation, extension ou architecture intérieure.', 'Contact the architecture studio for a first meeting: new build, renovation, extension or interior design.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Rencontrons-nous', 'Let’s meet'), 'intro' => $T('Au studio, sur votre terrain ou en visioconférence.', 'At the studio, on your site or by video call.')]),
                B::contact($theme, [
                    'title' => $T('Parlez-nous de votre projet', 'Tell us about your project'), 'intro' => $T('Type de projet, lieu, surface approximative et calendrier souhaité : quelques lignes suffisent.', 'Type of project, location, approximate size and desired timeline: a few lines are enough.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 9 h – 18 h', 'Monday – Friday, 9 am – 6 pm')],
                    ],
                    'social' => ['instagram', 'pinterest', 'facebook'],
                    'subject' => true,
                    'bg' => 'white',
                ]),
                B::schedule($theme, ['title' => $T('Préparer le premier rendez-vous', 'Preparing for the first meeting'), 'groups' => [
                    [$T('À apporter', 'What to bring'), [
                        ['map', $T('Plan cadastral ou adresse du terrain', 'Site plan or land address')],
                        ['camera', $T('Photos du lieu existant', 'Photos of the existing space')],
                        ['file-text', $T('Plans existants, si vous en avez', 'Existing drawings, if any')],
                    ]],
                    [$T('À réfléchir', 'Things to consider'), [
                        ['wallet', $T('Votre budget global, travaux compris', 'Your overall budget, including construction')],
                        ['calendar-days', $T('La date d’emménagement souhaitée', 'Your desired move-in date')],
                        ['image', $T('Quelques images qui vous inspirent', 'A few images that inspire you')],
                    ]],
                ]]),
            ]];

        $blog = [
            'base' => 'journal',
            'title' => 'Journal',
            'categories' => ['renovation' => $T('Rénovation', 'Renovation'), 'interieur' => $T('Intérieur', 'Interiors'), 'construction' => $T('Construction', 'Building')],
            'posts' => [
                ['key' => 'renover', 'slug' => $u('renover-maison-ancienne', 'renovating-an-old-house'), 'category' => 'renovation', 'tags' => [$T('Patrimoine', 'Heritage')],
                    'title' => $T('Rénover une maison ancienne sans perdre son âme', 'Renovating an old house without losing its soul'),
                    'excerpt' => $T('Garder les matériaux d’origine, gagner en lumière et en confort thermique.', 'Keeping original materials while gaining light and thermal comfort.'),
                    'photo' => ['architecte-article-renovation', 'hammer', $T('Mur en pierre restauré dans une pièce rénovée avec poutres apparentes', 'Restored stone wall in a renovated room with exposed beams')],
                    'heading' => $T('Nos conseils', 'Our advice'),
                    'body' => [
                        $T('Avant de démolir, observez : murs épais, parquets, moulures et menuiseries anciennes font souvent le charme d’une maison et peuvent être restaurés.', 'Before demolishing anything, look closely: thick walls, wood floors, moldings and old joinery often give a house its charm and can be restored.'),
                        $T('L’isolation doit respecter la façon dont les murs anciens respirent : des matériaux perspirants évitent l’humidité et les dégradations.', 'Insulation must respect the way old walls breathe: breathable materials prevent damp and damage.'),
                        $T('Ouvrir une cloison, créer une verrière ou abaisser un appui de fenêtre suffit parfois à transformer la lumière d’une pièce.', 'Opening up a partition, adding interior glazing or lowering a window sill is sometimes enough to transform a room’s light.'),
                    ]],
                ['key' => 'lumiere', 'slug' => $u('eclairer-un-interieur', 'lighting-an-interior'), 'category' => 'interieur', 'tags' => [$T('Éclairage', 'Lighting')],
                    'title' => $T('Bien éclairer un intérieur : la règle des trois lumières', 'Lighting an interior well: the three-layer rule'),
                    'excerpt' => $T('Lumière d’ambiance, fonctionnelle et d’accent : comment les combiner pièce par pièce.', 'Ambient, task and accent lighting: how to combine them room by room.'),
                    'photo' => ['architecte-article-interieur', 'lamp', $T('Salon chaleureux éclairé par des suspensions et des lampes basses le soir', 'Cozy living room lit by pendant lamps and low lights in the evening')],
                    'quote' => $T('« Un plafonnier seul aplatit une pièce ; plusieurs sources lui donnent du relief. »', '“A single ceiling light flattens a room; several sources give it depth.”'),
                    'body' => [
                        $T('Un intérieur agréable combine trois types de lumière : une lumière d’ambiance douce, des lumières fonctionnelles là où l’on travaille et quelques accents pour mettre en valeur une matière ou un objet.', 'A pleasant interior combines three kinds of light: soft ambient light, task lighting where you work and a few accents to highlight a material or an object.'),
                        $T('Privilégiez des températures de couleur chaudes dans les pièces de vie et prévoyez des variateurs.', 'Favor warm color temperatures in living spaces and plan for dimmers.'),
                        $T('Pensez l’éclairage dès les plans : ajouter des points lumineux après les travaux coûte bien plus cher.', 'Plan lighting at the drawing stage: adding light points after construction costs far more.'),
                    ]],
                ['key' => 'terrain', 'slug' => $u('choisir-son-terrain', 'choosing-a-plot'), 'category' => 'construction', 'tags' => [$T('Terrain', 'Land')],
                    'title' => $T('Choisir un terrain pour faire construire', 'Choosing a plot to build on'),
                    'excerpt' => $T('Orientation, règles d’urbanisme, sol et réseaux : ce qu’il faut vérifier avant d’acheter.', 'Orientation, zoning rules, soil and utilities: what to check before buying.'),
                    'photo' => ['architecte-article-construction', 'mountain', $T('Terrain à bâtir en légère pente avec arbres et vue dégagée', 'Gently sloping building plot with trees and an open view')],
                    'heading' => $T('Les points à vérifier', 'What to check'),
                    'body' => [
                        $T('Un beau terrain n’est pas toujours un bon terrain à bâtir. Avant de signer, faites-le visiter par un architecte.', 'A beautiful plot is not always a good one to build on. Before signing, have an architect visit it.'),
                        $T('Vérifiez l’orientation, la pente, la nature du sol, l’accès aux réseaux et les règles d’urbanisme qui fixent hauteur, emprise et aspect des constructions.', 'Check orientation, slope, soil type, access to utilities and the zoning rules that set height, footprint and appearance.'),
                        $T('Une étude de sol et un premier croquis d’implantation permettent d’estimer le budget réel du projet.', 'A soil survey and a first site sketch help estimate the project’s real budget.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Projets', 'Projects'), 'url' => $projects],
                    ['label' => $T('Approche', 'Approach'), 'url' => $approach],
                    ['label' => $T('Étapes', 'Process'), 'url' => $process],
                    ['label' => $T('Honoraires', 'Fees'), 'url' => $fees],
                    ['label' => $T('Le studio', 'Studio'), 'url' => $studio],
                    ['label' => 'Journal', 'url' => $journal],
                ],
                'cta_label' => $T('Prendre rendez-vous', 'Book a meeting'),
                'cta_url' => $contact,
                'footer_text' => $T('Studio d’architecture et d’architecture intérieure : construction neuve, rénovation et aménagement, de l’esquisse à la remise des clés.', 'Architecture and interior design studio: new builds, renovation and interiors, from first sketch to handover.'),
                'footer_links' => [
                    ['label' => $T('Projets', 'Projects'), 'url' => $projects],
                    ['label' => $T('Étapes d’un projet', 'Project stages'), 'url' => $process],
                    ['label' => $T('Honoraires', 'Fees'), 'url' => $fees],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
