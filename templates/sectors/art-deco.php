<?php

/*
 * Pack « Art et décoration » : 8 pages, en français et en anglais.
 * Pour studios de décoration, artisans, designers, photographes et décorateurs
 * d'événements. Les textes sont des exemples à personnaliser. Les liens internes
 * sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Art et décoration', 'en' => 'Art and decoration'],
        'icon' => 'palette',
        'keywords' => ['art', 'décoration', 'design', 'artisan', 'atelier', 'intérieur', 'photographe', 'événement', 'mobilier', 'galerie', 'decor', 'interior', 'craft', 'studio', 'photographer', 'furniture'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $studio = $u('/atelier', '/studio');
        $collections = $u('/collections', '/collections');
        $services = $u('/services', '/services');
        $portfolio = $u('/realisations', '/portfolio');
        $process = $u('/demarche', '/process');
        $pricing = $u('/tarifs', '/pricing');
        $contact = '/contact';

        $collectionItems = [
            ['art-deco-collection-wax', 'shirt', $T('Collection Wax', 'Wax collection'), $T('Coussins, rideaux et têtes de lit dans des wax aux motifs audacieux.', 'Cushions, curtains and headboards in bold wax prints.')],
            ['art-deco-collection-bois', 'tree-pine', $T('Bois sculpté', 'Carved wood'), $T('Tables, tabourets et sculptures taillés à la main dans des essences locales.', 'Tables, stools and sculptures hand-carved from local woods.')],
            ['art-deco-collection-raphia', 'leaf', $T('Raphia et fibres', 'Raffia and fibres'), $T('Luminaires, tentures et paniers tressés par nos artisanes.', 'Lighting, wall hangings and baskets woven by our craftswomen.')],
            ['art-deco-collection-ceramique', 'flower-2', $T('Céramique', 'Ceramics'), $T('Vases, vaisselle et jarres aux émaux terreux, pièces uniques.', 'Vases, tableware and jars in earthy glazes, one-of-a-kind pieces.')],
            ['art-deco-collection-art', 'frame', $T('Art contemporain', 'Contemporary art'), $T('Toiles, tirages et œuvres textiles d’artistes d’Afrique, sélectionnées avec soin.', 'Canvases, prints and textile works by African artists, carefully selected.')],
            ['art-deco-collection-lumiere', 'lamp', $T('Lumières', 'Lighting'), $T('Suspensions et lampes qui dessinent des ombres chaleureuses.', 'Pendants and lamps that cast warm, sculpted shadows.')],
        ];
        $serviceItems = [
            ['sofa', $T('Décoration d’intérieur', 'Interior decoration'), $T('Salon, chambre, boutique ou bureau : une atmosphère pensée pièce par pièce.', 'Living room, bedroom, shop or office: an atmosphere designed room by room.')],
            ['party-popper', $T('Décoration d’événements', 'Event decoration'), $T('Mariages, cérémonies et lancements mis en scène avec des matières naturelles.', 'Weddings, ceremonies and launches staged with natural materials.')],
            ['hammer', $T('Mobilier sur mesure', 'Custom furniture'), $T('Meubles dessinés et fabriqués dans notre atelier, à vos dimensions.', 'Furniture designed and made in our workshop, to your dimensions.')],
            ['frame', $T('Œuvres et encadrement', 'Artwork and framing'), $T('Choix des œuvres, accrochage et encadrement sur mesure.', 'Artwork selection, hanging and bespoke framing.')],
            ['lightbulb', $T('Conseil et accompagnement', 'Consulting and guidance'), $T('Un regard d’expert pour choisir couleurs, matières et agencement.', 'An expert eye to choose colours, materials and layout.')],
            ['paintbrush', $T('Peinture et finitions', 'Painting and finishes'), $T('Enduits, patines et fresques murales aux teintes de la terre.', 'Plasters, patinas and wall murals in earthy tones.')],
        ];
        $galleryPhotos = [
            ['art-deco-salon-1', 'sofa', $T('Salon chaleureux avec coussins en wax et table en bois', 'Warm living room with wax cushions and a wooden table')],
            ['art-deco-chambre-1', 'bed-double', $T('Chambre aux tons terre et tête de lit en raphia', 'Bedroom in earth tones with a raffia headboard')],
            ['art-deco-evenement-1', 'party-popper', $T('Table de réception décorée de fleurs et de tissus', 'Reception table dressed with flowers and fabrics')],
            ['art-deco-atelier-1', 'hammer', $T('Artisan sculptant un tabouret en bois', 'Craftsman carving a wooden stool')],
            ['art-deco-boutique-1', 'house', $T('Boutique aménagée avec des luminaires tressés', 'Shop fitted out with woven lighting')],
            ['art-deco-art-1', 'frame', $T('Mur de tableaux contemporains dans un couloir lumineux', 'Wall of contemporary paintings in a bright hallway')],
        ];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Des intérieurs qui nous ressemblent', 'Interiors that feel like us'), 'items' => [
            [$T('Notre salon est devenu l’endroit où tout le monde veut s’asseoir. L’équipe a compris notre histoire dès le premier rendez-vous.', 'Our living room has become the place everyone wants to sit. The team understood our story from the first meeting.'), '[Prénom Nom]', $T('Cliente, rénovation d’un salon', 'Client, living room makeover')],
            [$T('Une décoration de mariage digne d’un rêve, avec des matières vraies et un sens du détail remarquable.', 'A dream wedding decoration, with real materials and remarkable attention to detail.'), '[Prénom Nom]', $T('Mariée', 'Bride')],
            [$T('Le mobilier sur mesure de notre restaurant attire les compliments chaque soir. Livraison dans les délais.', 'The custom furniture in our restaurant draws compliments every evening. Delivered on time.'), '[Prénom Nom]', $T('Restaurateur', 'Restaurant owner')],
        ]];
        $cta = ['title' => $T('Imaginons votre espace ensemble', 'Let’s imagine your space together'), 'text' => $T('Parlez-nous de votre projet : nous revenons vers vous avec des idées, des matières et un devis clair.', 'Tell us about your project: we will come back with ideas, materials and a clear quote.'),
            'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact, 'accent'], [$T('Voir les réalisations', 'See our projects'), $portfolio, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Studio de décoration et atelier d’artisans : intérieurs, événements et mobilier sur mesure inspirés par l’héritage africain.', 'Decoration studio and artisan workshop: interiors, events and custom furniture inspired by African heritage.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Studio de décoration et atelier', 'Decoration studio and workshop'),
                    'title' => $T('Des espaces qui racontent votre histoire', 'Spaces that tell your story'),
                    'text' => $T('Wax, bois, raphia et art contemporain : nous composons des intérieurs chaleureux et des événements inoubliables, façonnés à la main.', 'Wax, wood, raffia and contemporary art: we compose warm interiors and unforgettable events, shaped by hand.'),
                    'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact], [$T('Découvrir les collections', 'Explore the collections'), $collections]],
                    'photo' => ['art-deco-accueil', 'palette', $T('Salon lumineux orné de tissus wax et de sculptures en bois', 'Bright living room adorned with wax fabrics and wooden sculptures')],
                ]),
                B::cards($theme, ['eyebrow' => $T('Nos collections', 'Our collections'), 'title' => $T('Des pièces uniques, des matières vraies', 'Unique pieces, real materials'), 'more' => $T('Découvrir', 'Discover'), 'items' => [
                    [$collectionItems[0][0], $collectionItems[0][1], $collectionItems[0][2], $collectionItems[0][3], $collections],
                    [$collectionItems[1][0], $collectionItems[1][1], $collectionItems[1][2], $collectionItems[1][3], $collections],
                    [$collectionItems[2][0], $collectionItems[2][1], $collectionItems[2][2], $collectionItems[2][3], $collections],
                ]]),
                B::features($theme, ['eyebrow' => $T('Ce que nous faisons', 'What we do'), 'title' => $T('Du croquis à l’installation', 'From sketch to installation'), 'bg' => 'light', 'cols' => 3, 'items' => array_slice($serviceItems, 0, 3)]),
                B::quote($theme, ['text' => $T('Une maison réussie est une maison qui ressemble à ceux qui l’habitent : leurs voyages, leurs racines et leurs rêves.', 'A successful home is one that resembles the people who live in it: their journeys, their roots and their dreams.'), 'source' => $T('— Notre philosophie', '— Our philosophy')]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('Ambiances récentes', 'Recent moods'), 'bg' => 'light', 'photos' => $galleryPhotos]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'studio', 'slug' => $u('atelier', 'studio'), 'title' => $T('L’atelier', 'The studio'),
            'description' => $T('Notre histoire, notre philosophie, nos matières et les artisans qui donnent vie à chaque pièce.', 'Our story, philosophy, materials and the artisans who bring every piece to life.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’atelier', 'The studio'), 'title' => $T('Un savoir-faire transmis de main en main', 'Know-how passed from hand to hand'), 'intro' => $T('Un studio de création où l’héritage africain rencontre le design contemporain.', 'A creative studio where African heritage meets contemporary design.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Né d’un atelier de quartier', 'Born in a neighbourhood workshop'),
                    'html' => '<p>'.e($T('Notre studio a commencé dans un petit atelier de menuiserie, entouré de tissus et de bobines de raphia. Aujourd’hui, designers, artisans et couturières y travaillent côte à côte pour créer des intérieurs qui ont du caractère.', 'Our studio began in a small carpentry workshop, surrounded by fabrics and spools of raffia. Today designers, craftsmen and seamstresses work side by side to create interiors with character.')).'</p><p>'.e($T('Nous croyons à la beauté lente : des pièces durables, fabriquées localement, qui vieillissent bien.', 'We believe in slow beauty: lasting pieces, made locally, that age gracefully.')).'</p>',
                    'bullets' => [$T('Création et fabrication sur place', 'Design and making in-house'), $T('Matières locales et responsables', 'Local, responsible materials'), $T('Pièces uniques ou petites séries', 'One-of-a-kind pieces or small series')],
                    'photo' => ['art-deco-atelier', 'hammer', $T('Intérieur de l’atelier avec établis et tissus', 'Workshop interior with benches and fabrics')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Matières et savoir-faire', 'Materials and craft'), 'title' => $T('Ce qui donne son âme à nos pièces', 'What gives our pieces their soul'), 'bg' => 'light', 'items' => [
                    ['shirt', $T('Wax et tissus', 'Wax and fabrics'), $T('Des motifs vibrants, coupés et assemblés avec précision.', 'Vibrant prints, cut and assembled with precision.')],
                    ['tree-pine', $T('Bois et sculpture', 'Wood and carving'), $T('Des essences locales travaillées à la main, du tronc à la finition.', 'Local woods worked by hand, from log to finish.')],
                    ['leaf', $T('Raphia et fibres', 'Raffia and fibres'), $T('Un tressage ancestral pour des textures légères et chaleureuses.', 'Ancestral weaving for light, warm textures.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('Les artisans', 'The artisans'), 'title' => $T('Les mains derrière chaque création', 'The hands behind every creation'), 'shape' => 'rounded', 'items' => [
                    ['[Prénom Nom]', $T('Directrice artistique', 'Art director'), 'art-deco-equipe-1'],
                    ['[Prénom Nom]', $T('Maître menuisier', 'Master carpenter'), 'art-deco-equipe-2'],
                    ['[Prénom Nom]', $T('Créatrice textile', 'Textile designer'), 'art-deco-equipe-3'],
                    ['[Prénom Nom]', $T('Chef de projet décoration', 'Decoration project manager'), 'art-deco-equipe-4'],
                ]]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Chaque objet porte la trace de la main qui l’a fait. C’est cela, le luxe.', 'Every object carries the trace of the hand that made it. That is true luxury.'), 'source' => $T('— La directrice artistique', '— The art director')]),
            ]];

        $pages[] = ['key' => 'collections', 'slug' => $u('collections', 'collections'), 'title' => $T('Collections', 'Collections'),
            'description' => $T('Wax, bois sculpté, raphia, céramique, art contemporain et luminaires : découvrez nos collections.', 'Wax, carved wood, raffia, ceramics, contemporary art and lighting: discover our collections.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Collections', 'Collections'), 'title' => $T('Pièces à habiter, à offrir, à collectionner', 'Pieces to live with, give and collect'), 'intro' => $T('Six univers de matières, créés dans notre atelier ou choisis auprès d’artistes partenaires.', 'Six material worlds, created in our workshop or chosen from partner artists.')]),
                B::cards($theme, ['items' => $collectionItems]),
                B::gallery($theme, ['eyebrow' => $T('Détails', 'Details'), 'title' => $T('Matières et textures', 'Materials and textures'), 'bg' => 'light', 'photos' => [
                    ['art-deco-detail-wax', 'shirt', $T('Détail d’un coussin en wax coloré', 'Close-up of a colourful wax cushion')],
                    ['art-deco-detail-bois', 'tree-pine', $T('Grain d’un plateau en bois sculpté', 'Grain of a carved wooden tray')],
                    ['art-deco-detail-raphia', 'leaf', $T('Tressage d’un abat-jour en raphia', 'Weave of a raffia lampshade')],
                    ['art-deco-detail-ceramique', 'flower-2', $T('Vase en céramique émaillée', 'Glazed ceramic vase')],
                    ['art-deco-detail-lumiere', 'lamp', $T('Suspension tressée allumée', 'Lit woven pendant lamp')],
                    ['art-deco-detail-art', 'frame', $T('Toile abstraite dans un cadre en bois', 'Abstract canvas in a wooden frame')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('services', 'services'), 'title' => 'Services',
            'description' => $T('Décoration d’intérieur et d’événements, mobilier sur mesure, œuvres et encadrement, conseil : nos services.', 'Interior and event decoration, custom furniture, artwork and framing, consulting: our services.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Services', 'title' => $T('Un accompagnement complet, à votre image', 'Complete support, in your image'), 'intro' => $T('Du simple conseil au projet clé en main, nous nous adaptons à votre espace et à votre budget.', 'From simple advice to turnkey projects, we adapt to your space and budget.')]),
                B::features($theme, ['cols' => 3, 'items' => $serviceItems]),
                B::split($theme, [
                    'eyebrow' => $T('Projets sur mesure', 'Bespoke projects'),
                    'title' => $T('Une pièce imaginée rien que pour vous', 'A piece imagined just for you'),
                    'html' => '<p>'.e($T('Une table de 3 mètres, une bibliothèque sous escalier, un décor de cérémonie : nous dessinons, fabriquons et installons ce que le catalogue ne propose pas.', 'A three-metre table, a bookcase under the stairs, a ceremony set: we design, make and install what no catalogue offers.')).'</p>',
                    'bullets' => [$T('Plans et visuels avant fabrication', 'Plans and visuals before making'), $T('Essences et tissus à choisir ensemble', 'Woods and fabrics chosen together'), $T('Livraison et installation incluses', 'Delivery and installation included')],
                    'buttons' => [[$T('Parler de mon projet', 'Discuss my project'), $contact]],
                    'photo' => ['art-deco-sur-mesure', 'pen-tool', $T('Table en bois massif réalisée sur mesure', 'Bespoke solid wood table')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'portfolio', 'slug' => $u('realisations', 'portfolio'), 'title' => $T('Réalisations', 'Portfolio'),
            'description' => $T('Salons, boutiques, mariages et mobilier : une sélection de nos réalisations récentes.', 'Living rooms, shops, weddings and furniture: a selection of our recent projects.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Réalisations', 'Portfolio'), 'title' => $T('Projets qui nous tiennent à cœur', 'Projects close to our hearts'), 'intro' => $T('Une sélection d’intérieurs, d’événements et de pièces sur mesure.', 'A selection of interiors, events and bespoke pieces.')]),
                B::gallery($theme, ['cols' => 3, 'photos' => array_merge($galleryPhotos, [
                    ['art-deco-salon-2', 'sofa', $T('Salon d’une maison familiale aux couleurs de la terre', 'Family home living room in earthy colours')],
                    ['art-deco-evenement-2', 'party-popper', $T('Arche de cérémonie en fleurs et raphia', 'Ceremony arch of flowers and raffia')],
                    ['art-deco-restaurant-1', 'armchair', $T('Salle de restaurant avec chaises sculptées', 'Restaurant dining room with carved chairs')],
                ])]),
                B::cards($theme, ['eyebrow' => $T('À la une', 'Featured'), 'title' => $T('Trois projets en détail', 'Three projects in detail'), 'bg' => 'light', 'items' => [
                    ['art-deco-projet-maison', 'house', $T('Une maison de famille réinventée', 'A family home reinvented'), $T('Six pièces redécorées en wax, bois clair et lin, avec mobilier sur mesure.', 'Six rooms redecorated in wax, pale wood and linen, with custom furniture.')],
                    ['art-deco-projet-mariage', 'heart', $T('Un mariage sous les palmiers', 'A wedding under the palms'), $T('300 invités, une scénographie en raphia, fleurs locales et tissus peints.', '300 guests, a raffia set design, local flowers and painted fabrics.')],
                    ['art-deco-projet-boutique', 'gem', $T('Une boutique de créateurs', 'A designers’ boutique'), $T('Agencement complet, vitrines en bois et éclairage tressé.', 'Complete fit-out, wooden display cases and woven lighting.')],
                ]]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
            ]];

        $pages[] = ['key' => 'process', 'slug' => $u('demarche', 'process'), 'title' => $T('Notre démarche', 'Our process'),
            'description' => $T('De la première rencontre à la livraison : les étapes d’un projet de décoration avec notre studio.', 'From the first meeting to delivery: the stages of a decoration project with our studio.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Démarche', 'Process'), 'title' => $T('Un projet serein, étape par étape', 'A calm project, step by step'), 'intro' => $T('Vous savez toujours où nous en sommes, ce que cela coûte et quand ce sera prêt.', 'You always know where we stand, what it costs and when it will be ready.')]),
                B::steps($theme, ['eyebrow' => $T('Les étapes', 'The stages'), 'title' => $T('De la rencontre à la livraison', 'From meeting to delivery'), 'items' => [
                    [$T('Rencontre', 'Meeting'), $T('Visite de l’espace, écoute de vos envies et de votre budget.', 'Site visit, listening to your wishes and budget.')],
                    [$T('Proposition', 'Proposal'), $T('Planche d’ambiance, plans, matières et devis détaillé.', 'Mood board, plans, materials and detailed quote.')],
                    [$T('Fabrication', 'Making'), $T('Nos artisans créent le mobilier et les textiles dans l’atelier.', 'Our artisans make the furniture and textiles in the workshop.')],
                    [$T('Installation', 'Installation'), $T('Livraison, mise en place et touches finales sur place.', 'Delivery, set-up and final touches on site.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions', 'Your questions'), 'intro' => $T('Une autre question ? Écrivez-nous.', 'Another question? Write to us.'), 'bg' => 'light', 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Combien de temps dure un projet ?', 'How long does a project take?'), $T('Comptez 2 à 4 semaines pour une pièce décorée, 6 à 10 semaines avec du mobilier sur mesure.', 'Allow 2 to 4 weeks for a decorated room, 6 to 10 weeks with custom furniture.')],
                    [$T('Puis-je garder certains de mes meubles ?', 'Can I keep some of my furniture?'), $T('Bien sûr. Nous intégrons vos pièces favorites et les mettons en valeur.', 'Of course. We work your favourite pieces into the design and show them off.')],
                    [$T('Travaillez-vous hors de la ville ?', 'Do you work outside the city?'), $T('Oui, pour les événements et les projets importants. Les frais de déplacement sont indiqués dans le devis.', 'Yes, for events and larger projects. Travel costs are shown in the quote.')],
                    [$T('Comment se passe le paiement ?', 'How does payment work?'), $T('Un acompte au lancement, puis un solde à la livraison. Chaque étape est détaillée dans le devis.', 'A deposit at launch, then the balance on delivery. Each stage is detailed in the quote.')],
                ]]),
            ]];

        $pages[] = ['key' => 'pricing', 'slug' => $u('tarifs', 'pricing'), 'title' => $T('Tarifs', 'Pricing'),
            'description' => $T('Consultation, relooking d’une pièce ou projet complet : nos formules et ce qu’elles comprennent.', 'Consultation, room makeover or full project: our packages and what they include.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Tarifs', 'Pricing'), 'title' => $T('Des formules claires', 'Clear packages'), 'intro' => $T('Prix indicatifs : chaque projet reçoit un devis personnalisé.', 'Indicative prices: every project receives a personalised quote.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Consultation', 'Consultation'), '90', '$', $T('/ séance', '/ session'), $T('Un regard d’expert sur votre espace.', 'An expert eye on your space.'), [$T('Visite de 2 heures', '2-hour visit'), $T('Conseils couleurs et matières', 'Colour and material advice'), $T('Planche d’ambiance', 'Mood board'), '-'.$T('Fabrication et installation', 'Making and installation')], $T('Réserver', 'Book')],
                    [$T('Relooking d’une pièce', 'Room makeover'), '1 200', '$', $T('à partir de', 'from'), $T('La pièce transformée, clé en main.', 'One room transformed, turnkey.'), [$T('Plans et visuels', 'Plans and visuals'), $T('Textiles et accessoires sur mesure', 'Bespoke textiles and accessories'), $T('Installation complète', 'Full installation'), $T('Suivi après livraison', 'After-delivery follow-up')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Projet complet', 'Full project'), '5 500', '$', $T('à partir de', 'from'), $T('Maison, boutique ou restaurant.', 'Home, shop or restaurant.'), [$T('Direction artistique complète', 'Complete art direction'), $T('Mobilier sur mesure', 'Custom furniture'), $T('Sélection d’œuvres et encadrement', 'Artwork selection and framing'), $T('Coordination du chantier', 'Site coordination')], $T('Demander un devis', 'Request a quote')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Toujours inclus', 'Always included'), 'title' => $T('Ce que comprend chaque projet', 'What every project includes'), 'bg' => 'white', 'items' => [
                    ['pen-tool', $T('Plans et visuels', 'Plans and visuals'), $T('Pour voir le résultat avant de décider.', 'To see the result before you decide.')],
                    ['award', $T('Qualité garantie', 'Quality guarantee'), $T('Un an de garantie sur le mobilier fabriqué.', 'One-year guarantee on made furniture.')],
                    ['truck', $T('Livraison soignée', 'Careful delivery'), $T('Transport, installation et nettoyage final.', 'Transport, installation and final cleaning.')],
                ]]),
                B::faq($theme, ['title' => $T('Questions sur les tarifs', 'Pricing questions'), 'bg' => 'light', 'items' => [
                    [$T('Le devis est-il gratuit ?', 'Is the quote free?'), $T('Oui, le devis est gratuit. La consultation est déduite si vous lancez le projet.', 'Yes, the quote is free. The consultation fee is deducted if you start the project.')],
                    [$T('Peut-on étaler le paiement ?', 'Can payment be spread out?'), $T('Oui, un échéancier est possible pour les projets complets.', 'Yes, a payment schedule is available for full projects.')],
                    [$T('Les prix incluent-ils les œuvres d’art ?', 'Do prices include artwork?'), $T('Les œuvres sont chiffrées à part, selon vos choix.', 'Artworks are priced separately, depending on your choices.')],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez le studio pour un devis, un rendez-vous ou une visite de l’atelier.', 'Contact the studio for a quote, an appointment or a workshop visit.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons de votre projet', 'Let’s talk about your project'), 'intro' => $T('Nous répondons sous 48 heures et proposons un premier rendez-vous gratuit.', 'We reply within 48 hours and offer a free first appointment.')]),
                B::contact($theme, [
                    'title' => $T('Venez nous rencontrer', 'Come and meet us'), 'intro' => $T('Le showroom est ouvert à tous ; un rendez-vous est conseillé pour parler de votre projet.', 'The showroom is open to everyone; an appointment is recommended to discuss your project.'),
                    'items' => [
                        ['map-pin', $T('Avenue des Artisans, Kinshasa, RDC', 'Avenue des Artisans, Kinshasa, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Mardi – samedi, 9 h – 18 h', 'Tuesday – Saturday, 9 am – 6 pm')],
                        ['calendar-days', $T('Visites de l’atelier sur rendez-vous', 'Workshop visits by appointment')],
                    ],
                    'social' => ['instagram', 'pinterest', 'facebook', 'whatsapp'],
                    'subject' => true,
                    'map' => 'Kinshasa',
                    'bg' => 'white',
                ]),
            ]];

        return [
            'pages' => $pages,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Atelier', 'Studio'), 'url' => $studio],
                    ['label' => 'Collections', 'url' => $collections],
                    ['label' => 'Services', 'url' => $services],
                    ['label' => $T('Réalisations', 'Portfolio'), 'url' => $portfolio],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Demander un devis', 'Request a quote'),
                'cta_url' => $contact,
                'footer_text' => $T('Studio de décoration et atelier d’artisans : des intérieurs et des pièces uniques façonnés à la main, inspirés par l’héritage africain.', 'Decoration studio and artisan workshop: interiors and unique pieces shaped by hand, inspired by African heritage.'),
                'footer_links' => [
                    ['label' => 'Collections', 'url' => $collections],
                    ['label' => $T('Notre démarche', 'Our process'), 'url' => $process],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
