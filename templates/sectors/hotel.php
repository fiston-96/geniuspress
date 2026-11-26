<?php

/*
 * Pack « Hôtel » (secteur Restaurants et hôtellerie) : 8 pages, en français et en anglais.
 * Boutique-hôtel ou hôtel de ville : chambres et suites, restaurant et bar, services,
 * séminaires, galerie, journal (guide de la ville) et réservation.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'établissement). Les liens internes
 * sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Restaurants et hôtellerie', 'en' => 'Restaurants and hospitality'],
        'icon' => 'bed',
        'keywords' => ['hôtel', 'boutique-hôtel', 'chambres', 'suites', 'séjour', 'réservation', 'séminaire', 'spa', 'hébergement', 'hotel', 'boutique hotel', 'rooms', 'suites', 'stay', 'booking', 'meetings', 'spa', 'accommodation'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $rooms = $u('/chambres', '/rooms');
        $dining = $u('/restaurant-bar', '/dining');
        $services = $u('/services', '/services');
        $meetings = $u('/seminaires', '/meetings');
        $gallery = $u('/galerie', '/gallery');
        $journal = '/journal';
        $contact = '/contact';
        $cur = $T('€', '$');

        $testimonials = ['eyebrow' => $T('Avis de nos hôtes', 'Guest reviews'), 'title' => $T('Ils ont séjourné chez nous', 'They stayed with us'), 'rating' => 5, 'items' => [
            [$T('Une chambre calme malgré le centre-ville, une literie excellente et un petit-déjeuner qui vaut le détour. Nous reviendrons.', 'A quiet room despite the city-center location, excellent bedding and a breakfast worth the trip. We will be back.'), '[Prénom Nom]', $T('Séjour en couple', 'Couple’s stay')],
            [$T('Le personnel connaît la ville par cœur : chaque recommandation de la réception a été une belle découverte.', 'The staff know the city inside out: every tip from the front desk turned out to be a great find.'), '[Prénom Nom]', $T('Voyageuse en solo', 'Solo traveler')],
            [$T('Notre séminaire de deux jours s’est déroulé sans accroc : salle lumineuse, matériel prêt, pauses soignées.', 'Our two-day seminar went off without a hitch: bright room, equipment ready, carefully prepared breaks.'), '[Prénom Nom]', $T('Responsable des ressources humaines', 'Human resources manager')],
        ]];
        $cta = ['title' => $T('Votre chambre vous attend', 'Your room is waiting'), 'text' => $T('Réservez en direct pour bénéficier du meilleur tarif, du surclassement selon disponibilité et de l’annulation gratuite jusqu’à 48 heures avant l’arrivée.', 'Book direct for the best rate, an upgrade when available and free cancellation up to 48 hours before arrival.'),
            'buttons' => [[$T('Réserver mon séjour', 'Book my stay'), $contact, 'accent'], [$T('Voir les chambres', 'See the rooms'), $rooms, 'outline-light']]];
        $photos = [
            ['hotel-hall', 'hotel', $T('Hall d’entrée d’un boutique-hôtel avec fauteuils en velours et éclairage chaleureux', 'Boutique hotel lobby with velvet armchairs and warm lighting')],
            ['hotel-chambre-deluxe', 'bed', $T('Chambre d’hôtel élégante avec grand lit, coussins et vue sur la ville', 'Elegant hotel room with a large bed, cushions and a city view')],
            ['hotel-salle-de-bain', 'droplet', $T('Salle de bain d’hôtel en marbre avec douche à l’italienne', 'Marble hotel bathroom with a walk-in shower')],
            ['hotel-restaurant', 'utensils', $T('Salle de restaurant d’hôtel dressée pour le dîner, nappes blanches et bougies', 'Hotel restaurant dining room set for dinner, white tablecloths and candles')],
            ['hotel-bar', 'coffee', $T('Barman préparant un cocktail au comptoir d’un bar d’hôtel', 'Bartender mixing a cocktail at a hotel bar counter')],
            ['hotel-spa', 'sparkles', $T('Espace bien-être d’hôtel avec bassin intérieur et lumière tamisée', 'Hotel wellness area with an indoor pool and soft lighting')],
            ['hotel-terrasse', 'sun', $T('Terrasse sur le toit d’un hôtel au coucher du soleil, avec vue sur les toits', 'Hotel rooftop terrace at sunset overlooking the rooftops')],
            ['hotel-petit-dejeuner', 'coffee', $T('Petit-déjeuner servi en chambre : croissants, fruits frais et café', 'Breakfast served in the room: croissants, fresh fruit and coffee')],
            ['hotel-facade', 'building', $T('Façade d’un hôtel de charme dans une rue piétonne en soirée', 'Facade of a charming hotel on a pedestrian street in the evening')],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Boutique-hôtel au cœur de la ville : chambres et suites raffinées, restaurant, bar sur le toit et espaces de séminaire. Réservez en direct.', 'Boutique hotel in the heart of the city: refined rooms and suites, restaurant, rooftop bar and meeting spaces. Book direct.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Boutique-hôtel · Centre-ville', 'Boutique hotel · City center'),
                    'title' => $T('Un hôtel de caractère au cœur de la ville', 'A hotel with character in the heart of the city'),
                    'text' => $T('42 chambres et suites, une table de saison, un bar sur le toit et une équipe attentionnée : tout pour un séjour d’affaires ou une escapade réussie.', '42 rooms and suites, a seasonal restaurant, a rooftop bar and an attentive team: everything for a successful business trip or getaway.'),
                    'buttons' => [[$T('Réserver', 'Book now'), $contact], [$T('Découvrir les chambres', 'Discover the rooms'), $rooms]],
                    'photo' => ['hotel-accueil', 'hotel', $T('Réception d’un boutique-hôtel avec un réceptionniste accueillant un client', 'Boutique hotel front desk with a receptionist welcoming a guest')],
                ]),
                B::split($theme, [
                    'eyebrow' => $T('L’hôtel', 'The hotel'),
                    'title' => $T('Le confort d’une grande maison, l’attention d’une petite équipe', 'The comfort of a grand house, the care of a small team'),
                    'html' => '<p>'.e($T('Installé dans un immeuble ancien entièrement rénové, notre hôtel marie matériaux nobles, design contemporain et service personnalisé. Musées, boutiques et quartier d’affaires sont à quelques minutes à pied.', 'Set in a fully renovated historic building, our hotel blends fine materials, contemporary design and personal service. Museums, shops and the business district are a few minutes’ walk away.')).'</p>',
                    'bullets' => [$T('Meilleur tarif garanti en réservant en direct', 'Best rate guaranteed when booking direct'), $T('Réception et conciergerie 24 h/24', '24-hour front desk and concierge'), $T('Wi-Fi rapide et gratuit dans tout l’hôtel', 'Fast free Wi-Fi throughout the hotel'), $T('Arrivée anticipée selon disponibilité', 'Early check-in when available')],
                    'buttons' => [[$T('Nos services', 'Our services'), $services, 'outline']],
                    'photo' => $photos[0],
                    'reverse' => true,
                ]),
                B::cards($theme, ['eyebrow' => $T('Chambres et suites', 'Rooms and suites'), 'title' => $T('Choisissez votre chambre', 'Choose your room'), 'more' => $T('Voir les tarifs', 'See rates'), 'items' => [
                    ['hotel-chambre-classique', 'bed', $T('Chambre classique avec lit double', 'Classic room with a double bed'), $T('20 m², calme et lumineuse, idéale pour un court séjour en ville. À partir de 129 € la nuit.', '215 sq ft, quiet and bright, ideal for a short city stay. From $139 per night.'), $rooms],
                    ['hotel-chambre-deluxe', 'bed', $T('Chambre deluxe avec vue sur les toits', 'Deluxe room with a rooftop view'), $T('28 m², coin salon et vue sur les toits de la ville. À partir de 199 € la nuit.', '300 sq ft, seating area and a view over the city rooftops. From $219 per night.'), $rooms],
                    ['hotel-suite', 'gem', $T('Suite avec salon séparé et balcon', 'Suite with separate living room and balcony'), $T('45 m², salon, balcon et service de majordome. À partir de 349 € la nuit.', '485 sq ft, living room, balcony and butler service. From $379 per night.'), $rooms],
                ]]),
                B::stats($theme, ['items' => [
                    [42, '', $T('chambres et suites', 'rooms and suites')],
                    [4, '', $T('salles de séminaire', 'meeting rooms')],
                    [96, ' %', $T('d’hôtes satisfaits', 'satisfied guests')],
                    [24, $T(' h/24', '/7'), $T('réception et conciergerie', 'front desk and concierge')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Pendant votre séjour', 'During your stay'), 'title' => $T('Tout est prévu pour vous', 'Everything is taken care of'), 'bg' => 'light', 'items' => [
                    ['utensils', $T('Restaurant de saison', 'Seasonal restaurant'), $T('Une carte courte, des produits frais et un chef qui travaille avec les producteurs de la région.', 'A short menu, fresh produce and a chef who works with local growers.'), $dining, $T('Voir la carte', 'See the menu')],
                    ['sparkles', $T('Bien-être', 'Wellness'), $T('Bassin intérieur, sauna, hammam et soins sur rendez-vous.', 'Indoor pool, sauna, steam room and treatments by appointment.'), $services, $T('Découvrir', 'Discover')],
                    ['presentation', $T('Séminaires', 'Meetings'), $T('Quatre salles équipées, de 10 à 120 personnes, et des forfaits tout compris.', 'Four equipped rooms for 10 to 120 people, with all-inclusive packages.'), $meetings, $T('Organiser un événement', 'Plan an event')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::posts($theme, ['eyebrow' => $T('Le journal', 'Journal'), 'title' => $T('Nos idées pour découvrir la ville', 'Our ideas for exploring the city'), 'source' => 'latest', 'count' => 3, 'more' => $T('Lire la suite', 'Read more')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'rooms', 'slug' => $u('chambres', 'rooms'), 'title' => $T('Chambres et suites', 'Rooms and suites'),
            'description' => $T('Chambres classiques, supérieures, deluxe et suites : équipements, superficies et tarifs indicatifs par nuit. Petit-déjeuner en option.', 'Classic, superior and deluxe rooms and suites: amenities, sizes and indicative nightly rates. Breakfast optional.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Chambres et suites', 'Rooms and suites'), 'title' => $T('Dormir au calme, en plein centre', 'Sleep soundly, right downtown'), 'intro' => $T('Toutes nos chambres sont insonorisées, climatisées et non-fumeurs. Tarifs indicatifs par nuit, selon la saison.', 'All our rooms are soundproofed, air-conditioned and non-smoking. Indicative nightly rates, depending on the season.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Chambres', 'Rooms'), 'title' => $T('Trois catégories de chambres', 'Three room categories'), 'intro' => $T('Prix par nuit pour deux personnes, taxe de séjour en sus. Petit-déjeuner buffet : 22 € par personne.', 'Nightly price for two guests, city tax extra. Buffet breakfast: $24 per person.'), 'items' => [
                    [$T('Classique', 'Classic'), $T('129', '139'), $cur, $T('/ nuit', '/ night'), $T('20 m², lit double ou deux lits simples.', '215 sq ft, double bed or two twin beds.'), [$T('Literie haut de gamme', 'Premium bedding'), $T('Douche à l’italienne', 'Walk-in shower'), $T('Wi-Fi gratuit', 'Free Wi-Fi'), '-'.$T('Vue dégagée', 'Open view')], $T('Réserver', 'Book')],
                    [$T('Supérieure', 'Superior'), $T('159', '169'), $cur, $T('/ nuit', '/ night'), $T('24 m², lit queen size et bureau.', '260 sq ft, queen bed and desk.'), [$T('Tout le confort Classique', 'Everything in Classic'), $T('Machine à café et thé', 'Coffee and tea maker'), $T('Bureau de travail', 'Work desk'), $T('Peignoirs et chaussons', 'Bathrobes and slippers')], $T('Réserver', 'Book'), true, $T('La plus réservée', 'Most booked')],
                    [$T('Deluxe', 'Deluxe'), $T('199', '219'), $cur, $T('/ nuit', '/ night'), $T('28 m², lit king size et coin salon.', '300 sq ft, king bed and seating area.'), [$T('Vue sur les toits', 'Rooftop view'), $T('Baignoire et douche', 'Bathtub and shower'), $T('Minibar offert', 'Complimentary minibar'), $T('Départ tardif selon disponibilité', 'Late check-out when available')], $T('Réserver', 'Book')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Suites', 'Suites'), 'title' => $T('Pour les séjours d’exception', 'For exceptional stays'), 'more' => $T('Demander un devis', 'Request a quote'), 'items' => [
                    ['hotel-suite', 'gem', $T('Suite avec salon séparé et balcon', 'Suite with separate living room and balcony'), $T('45 m², chambre, salon et balcon. À partir de 349 € la nuit.', '485 sq ft, bedroom, living room and balcony. From $379 per night.'), $contact],
                    ['hotel-suite-familiale', 'users', $T('Suite familiale avec deux chambres', 'Family suite with two bedrooms'), $T('55 m², deux chambres communicantes pour quatre personnes. À partir de 389 € la nuit.', '590 sq ft, two connecting bedrooms for four guests. From $419 per night.'), $contact],
                    ['hotel-suite-terrasse', 'sun', $T('Suite sous les toits avec terrasse privée', 'Top-floor suite with a private terrace'), $T('60 m² et terrasse de 20 m² avec vue panoramique. À partir de 489 € la nuit.', '645 sq ft and a 215 sq ft terrace with panoramic views. From $529 per night.'), $contact],
                ]]),
                B::features($theme, ['eyebrow' => $T('Dans toutes les chambres', 'In every room'), 'title' => $T('Les équipements', 'Amenities'), 'bg' => 'light', 'cols' => 4, 'items' => [
                    ['wifi', $T('Wi-Fi rapide', 'Fast Wi-Fi'), $T('Connexion gratuite et illimitée.', 'Free, unlimited connection.')],
                    ['moon', $T('Insonorisation', 'Soundproofing'), $T('Fenêtres isolantes et rideaux occultants.', 'Insulated windows and blackout curtains.')],
                    ['monitor', $T('Écran connecté', 'Smart TV'), $T('Chaînes internationales et diffusion depuis votre téléphone.', 'International channels and casting from your phone.')],
                    ['lock', $T('Coffre-fort', 'In-room safe'), $T('Pour un ordinateur portable et vos objets de valeur.', 'Fits a laptop and your valuables.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Bon à savoir', 'Good to know'), 'intro' => $T('Une demande particulière ? La réception y répond volontiers.', 'A special request? The front desk will be happy to help.'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'outline']], 'items' => [
                    [$T('À quelle heure puis-je arriver et partir ?', 'What are check-in and check-out times?'), $T('Les chambres sont disponibles à partir de 15 h et doivent être libérées avant 11 h. Une bagagerie gratuite est à votre disposition.', 'Rooms are available from 3 pm and must be vacated by 11 am. Free luggage storage is available.')],
                    [$T('Proposez-vous des lits pour bébé ?', 'Do you provide baby cots?'), $T('Oui, gratuitement et sur demande, dans toutes les catégories sauf la chambre classique.', 'Yes, free of charge on request, in every category except the classic room.')],
                    [$T('Les animaux sont-ils acceptés ?', 'Are pets allowed?'), $T('Les chiens de petite taille sont les bienvenus moyennant un supplément de 20 € par nuit.', 'Small dogs are welcome for a supplement of $20 per night.')],
                    [$T('Avez-vous des chambres accessibles ?', 'Do you have accessible rooms?'), $T('Oui, trois chambres sont adaptées aux personnes à mobilité réduite, avec douche de plain-pied.', 'Yes, three rooms are adapted for guests with reduced mobility, with a step-free shower.')],
                ]]),
            ]];

        $pages[] = ['key' => 'dining', 'slug' => $u('restaurant-bar', 'dining'), 'title' => $T('Restaurant et bar', 'Restaurant and bar'),
            'description' => $T('Restaurant de saison ouvert à tous, petit-déjeuner buffet et bar à cocktails sur le toit : horaires, menus et réservation de table.', 'Seasonal restaurant open to all, buffet breakfast and rooftop cocktail bar: hours, menus and table reservations.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Restaurant et bar', 'Restaurant and bar'), 'title' => $T('Une table ouverte sur la ville', 'A table open to the city'), 'intro' => $T('Hôtes de l’hôtel ou gens du quartier : notre restaurant et notre bar accueillent tout le monde.', 'Hotel guests or locals: our restaurant and bar welcome everyone.')]),
                B::split($theme, [
                    'eyebrow' => $T('Le restaurant', 'The restaurant'),
                    'title' => $T('Une cuisine de saison, pensée par notre chef', 'Seasonal cooking, created by our chef'),
                    'html' => '<p>'.e($T('La carte change au fil des saisons et des arrivages : légumes de producteurs voisins, poissons de ligne et viandes de qualité. Une option végétarienne est proposée à chaque service.', 'The menu changes with the seasons and the market: vegetables from nearby growers, line-caught fish and quality meat. A vegetarian option is offered at every service.')).'</p>',
                    'bullets' => [$T('Menu du midi en deux ou trois plats', 'Two- or three-course lunch menu'), $T('Menu dégustation le soir', 'Tasting menu in the evening'), $T('Allergies et régimes pris en compte', 'Allergies and diets catered for')],
                    'buttons' => [[$T('Réserver une table', 'Book a table'), $contact]],
                    'photo' => $photos[3],
                ]),
                B::schedule($theme, ['eyebrow' => $T('Horaires et formules', 'Hours and menus'), 'title' => $T('Du petit-déjeuner au dernier verre', 'From breakfast to the last drink'), 'groups' => [
                    [$T('Petit-déjeuner', 'Breakfast'), [
                        ['clock', $T('Tous les jours, 6 h 30 – 10 h 30', 'Daily, 6:30 – 10:30 am')],
                        ['coffee', $T('Buffet sucré et salé — 22 €', 'Sweet and savory buffet — $24')],
                        ['bed', $T('Servi en chambre sans supplément', 'Served in your room at no extra charge')],
                    ]],
                    [$T('Restaurant', 'Restaurant'), [
                        ['clock', $T('Midi, 12 h – 14 h 30 ; soir, 19 h – 22 h 30', 'Lunch, noon – 2:30 pm; dinner, 7 – 10:30 pm')],
                        ['utensils', $T('Menu du midi — dès 28 €', 'Lunch menu — from $30')],
                        ['chef-hat', $T('Menu dégustation en cinq plats — 65 €', 'Five-course tasting menu — $70')],
                    ]],
                    [$T('Bar sur le toit', 'Rooftop bar'), [
                        ['clock', $T('Tous les jours, 17 h – 1 h', 'Daily, 5 pm – 1 am')],
                        ['sun', $T('Cocktails signature — dès 12 €', 'Signature cocktails — from $13')],
                        ['music', $T('Musique live le jeudi soir', 'Live music on Thursday evenings')],
                    ]],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Le bar', 'The bar'),
                    'title' => $T('Le coucher du soleil, un verre à la main', 'Sunset, drink in hand'),
                    'html' => '<p>'.e($T('Au dernier étage, notre bar offre une vue dégagée sur la ville. Cocktails de saison, sélection de vins au verre, boissons sans alcool maison et petites assiettes à partager.', 'On the top floor, our bar offers an open view over the city. Seasonal cocktails, wines by the glass, house-made alcohol-free drinks and small plates to share.')).'</p>',
                    'photo' => $photos[6],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::quote($theme, ['text' => $T('Je cuisine ce que la saison me donne, avec le moins d’artifices possible : le produit doit rester la vedette.', 'I cook what the season gives me, with as few tricks as possible: the produce must remain the star.'), 'source' => $T('— [Prénom Nom], chef du restaurant', '— [Prénom Nom], head chef')]),
                B::cta($theme, ['title' => $T('Réservez votre table', 'Book your table'), 'text' => $T('Pour un déjeuner d’affaires, un dîner en amoureux ou un verre entre amis.', 'For a business lunch, a romantic dinner or drinks with friends.'), 'buttons' => [[$T('Réserver', 'Book'), $contact, 'accent'], [$T('Nos séminaires', 'Our meetings offer'), $meetings, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('services', 'services'), 'title' => $T('Services et bien-être', 'Services and wellness'),
            'description' => $T('Conciergerie, espace bien-être, salle de fitness, navette, blanchisserie et parking : tous les services de l’hôtel pour un séjour serein.', 'Concierge, wellness area, gym, shuttle, laundry and parking: all the hotel’s services for a relaxed stay.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Services', 'Services'), 'title' => $T('Un séjour sans souci', 'A worry-free stay'), 'intro' => $T('Notre équipe s’occupe des détails pour que vous profitiez pleinement de votre temps.', 'Our team takes care of the details so you can make the most of your time.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['key', $T('Conciergerie', 'Concierge'), $T('Restaurants, spectacles, visites guidées ou fleurs : nous réservons pour vous.', 'Restaurants, shows, guided tours or flowers: we book for you.')],
                    ['sparkles', $T('Espace bien-être', 'Wellness area'), $T('Bassin intérieur, sauna et hammam en accès libre, soins sur rendez-vous.', 'Indoor pool, sauna and steam room included, treatments by appointment.')],
                    ['dumbbell', $T('Salle de fitness', 'Gym'), $T('Ouverte 24 h/24, avec équipements de cardio et de musculation.', 'Open 24 hours, with cardio and strength equipment.')],
                    ['bus', $T('Navette aéroport', 'Airport shuttle'), $T('Transferts sur réservation, accueil personnalisé à l’arrivée.', 'Transfers on request, with a personal welcome on arrival.')],
                    ['shirt', $T('Blanchisserie', 'Laundry'), $T('Lavage, repassage et pressing en 24 heures.', 'Washing, ironing and dry cleaning within 24 hours.')],
                    ['car', $T('Parking', 'Parking'), $T('Parking couvert et sécurisé, service voiturier en soirée.', 'Secure covered parking, valet service in the evening.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Bien-être', 'Wellness'),
                    'title' => $T('Une parenthèse de calme en ville', 'A haven of calm in the city'),
                    'html' => '<p>'.e($T('Après une journée de réunions ou de visites, retrouvez le calme de notre espace bien-être. Nos praticiennes proposent massages relaxants, soins du visage et rituels sur rendez-vous.', 'After a day of meetings or sightseeing, unwind in our wellness area. Our therapists offer relaxing massages, facials and rituals by appointment.')).'</p>',
                    'bullets' => [$T('Accès libre pour les hôtes, 7 h – 21 h', 'Free access for guests, 7 am – 9 pm'), $T('Massage de 60 minutes — dès 85 €', '60-minute massage — from $90'), $T('Peignoirs et serviettes fournis', 'Robes and towels provided')],
                    'buttons' => [[$T('Réserver un soin', 'Book a treatment'), $contact]],
                    'photo' => $photos[5],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('Votre séjour', 'Your stay'), 'title' => $T('De la réservation au départ', 'From booking to check-out'), 'items' => [
                    [$T('Avant l’arrivée', 'Before arrival'), $T('Un message pour connaître vos préférences : oreiller, horaires, transfert.', 'A message to learn your preferences: pillow, timing, transfer.')],
                    [$T('Accueil', 'Welcome'), $T('Enregistrement rapide et présentation de l’hôtel et du quartier.', 'Quick check-in and an introduction to the hotel and the neighborhood.')],
                    [$T('Pendant le séjour', 'During your stay'), $T('La réception reste joignable jour et nuit, sur place ou par message.', 'The front desk can be reached day and night, in person or by message.')],
                    [$T('Départ', 'Departure'), $T('Facture envoyée par e-mail et bagagerie jusqu’à votre train ou votre vol.', 'Invoice sent by email and luggage storage until your train or flight.')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Un hôtel responsable', 'A responsible hotel'), 'bg' => 'light', 'items' => [
                    ['leaf', $T('Moins de déchets', 'Less waste'), $T('Produits d’accueil rechargeables, eau filtrée et tri sélectif.', 'Refillable toiletries, filtered water and recycling.')],
                    ['store', $T('Ancré dans le quartier', 'Rooted locally'), $T('Fournisseurs, artisans et artistes de la ville mis à l’honneur.', 'Suppliers, craftspeople and artists from the city in the spotlight.')],
                    ['shield-check', $T('Sécurité', 'Safety'), $T('Accès par carte, personnel formé et veille de nuit.', 'Key-card access, trained staff and a night watch.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'meetings', 'slug' => $u('seminaires', 'meetings'), 'title' => $T('Séminaires et événements', 'Meetings and events'),
            'description' => $T('Quatre salles lumineuses de 10 à 120 personnes, forfaits journée d’étude et séminaire résidentiel, réceptions privées sur le toit.', 'Four bright rooms for 10 to 120 people, day delegate and residential packages, private receptions on the rooftop.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Séminaires et événements', 'Meetings and events'), 'title' => $T('Réunir vos équipes dans les meilleures conditions', 'Bring your teams together in the best conditions'), 'intro' => $T('Un interlocuteur unique organise votre événement, de la salle aux pauses et à l’hébergement.', 'A single point of contact organizes your event, from the room to the breaks and accommodation.')]),
                B::split($theme, [
                    'eyebrow' => $T('Nos espaces', 'Our spaces'),
                    'title' => $T('Des salles à la lumière du jour', 'Rooms with natural daylight'),
                    'html' => '<p>'.e($T('Nos quatre salles modulables accueillent comités de direction, formations, conférences et cocktails. Chacune dispose de la lumière naturelle, d’un écran ou d’un vidéoprojecteur et d’une connexion très haut débit.', 'Our four flexible rooms host board meetings, training sessions, conferences and cocktail parties. Each has natural light, a screen or projector and a high-speed connection.')).'</p>',
                    'bullets' => [$T('Matériel de visioconférence', 'Video-conferencing equipment'), $T('Pauses gourmandes préparées par notre chef', 'Breaks prepared by our chef'), $T('Tarifs préférentiels sur les chambres', 'Preferential room rates')],
                    'photo' => ['hotel-salle-seminaire', 'presentation', $T('Salle de séminaire d’hôtel disposée en U, avec écran et grandes fenêtres', 'Hotel meeting room set up in a U-shape, with a screen and large windows')],
                ]),
                M::table($theme, ['eyebrow' => $T('Capacités', 'Capacities'), 'title' => $T('Nos salles en un coup d’œil', 'Our rooms at a glance'), 'bg' => 'light', 'rows' => [
                    [$T('Salle', 'Room'), $T('Surface', 'Size'), $T('Théâtre', 'Theater'), $T('Classe', 'Classroom'), $T('En U', 'U-shape'), $T('Cocktail', 'Reception')],
                    [$T('Atelier', 'Studio'), $T('30 m²', '320 sq ft'), '20', '12', '10', '25'],
                    [$T('Bibliothèque', 'Library'), $T('55 m²', '590 sq ft'), '40', '24', '20', '50'],
                    [$T('Verrière', 'Glasshouse'), $T('90 m²', '970 sq ft'), '80', '48', '32', '100'],
                    [$T('Salon panoramique', 'Panorama lounge'), $T('140 m²', '1,500 sq ft'), '120', '70', '40', '150'],
                ], 'note' => $T('Capacités maximales selon la disposition choisie.', 'Maximum capacities depending on the chosen layout.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Forfaits', 'Packages'), 'title' => $T('Des formules tout compris', 'All-inclusive packages'), 'intro' => $T('Prix indicatifs par participant, à partir de 10 personnes.', 'Indicative prices per attendee, from 10 people.'), 'items' => [
                    [$T('Demi-journée d’étude', 'Half-day package'), $T('49', '55'), $cur, $T('/ pers.', '/ person'), $T('Matinée ou après-midi.', 'Morning or afternoon.'), [$T('Salle équipée', 'Equipped room'), $T('Une pause gourmande', 'One coffee break'), $T('Eau et papeterie', 'Water and stationery'), '-'.$T('Déjeuner', 'Lunch')], $T('Demander un devis', 'Request a quote')],
                    [$T('Journée d’étude', 'Day delegate package'), $T('79', '89'), $cur, $T('/ pers.', '/ person'), $T('La formule la plus demandée.', 'Our most requested package.'), [$T('Salle équipée', 'Equipped room'), $T('Deux pauses gourmandes', 'Two coffee breaks'), $T('Déjeuner au restaurant', 'Lunch at the restaurant'), $T('Visioconférence incluse', 'Video conferencing included')], $T('Demander un devis', 'Request a quote'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Séminaire résidentiel', 'Residential package'), $T('219', '239'), $cur, $T('/ pers.', '/ person'), $T('Journée d’étude et nuit sur place.', 'Day package plus an overnight stay.'), [$T('Journée d’étude complète', 'Full day package'), $T('Dîner trois plats', 'Three-course dinner'), $T('Chambre et petit-déjeuner', 'Room and breakfast'), $T('Accès à l’espace bien-être', 'Wellness area access')], $T('Demander un devis', 'Request a quote')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Organisation', 'Planning'), 'title' => $T('Votre événement en quatre étapes', 'Your event in four steps'), 'items' => [
                    [$T('Votre demande', 'Your request'), $T('Date, nombre de participants, format : un devis vous parvient sous 24 heures.', 'Date, number of attendees, format: you receive a quote within 24 hours.')],
                    [$T('Visite', 'Site visit'), $T('Nous vous faisons visiter les salles, sur place ou en vidéo.', 'We show you the rooms, on site or by video.')],
                    [$T('Préparation', 'Preparation'), $T('Disposition, menus, matériel et hébergement sont validés ensemble.', 'Layout, menus, equipment and accommodation are agreed together.')],
                    [$T('Le jour J', 'On the day'), $T('Un coordinateur reste disponible du début à la fin.', 'A coordinator stays on hand from start to finish.')],
                ]]),
                B::cta($theme, ['tone' => 'dark', 'title' => $T('Un projet de séminaire ou de réception ?', 'Planning a meeting or reception?'), 'text' => $T('Décrivez-nous votre événement : nous vous proposons une formule sur mesure.', 'Tell us about your event: we will propose a tailored package.'), 'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'gallery', 'slug' => $u('galerie', 'gallery'), 'title' => $T('Galerie', 'Gallery'),
            'description' => $T('L’hôtel en images : chambres, suites, restaurant, bar sur le toit, espace bien-être et quartier. Une visite avant votre séjour.', 'The hotel in pictures: rooms, suites, restaurant, rooftop bar, wellness area and neighborhood. A visit before your stay.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Galerie', 'Gallery'), 'title' => $T('L’hôtel en images', 'The hotel in pictures'), 'intro' => $T('Un aperçu de nos chambres, de nos espaces et de l’ambiance de la maison.', 'A glimpse of our rooms, our spaces and the atmosphere of the house.')]),
                B::gallery($theme, ['photos' => $photos, 'cols' => 3]),
                B::gallery($theme, ['eyebrow' => $T('Le quartier', 'The neighborhood'), 'title' => $T('À deux pas de l’hôtel', 'Steps from the hotel'), 'bg' => 'light', 'photos' => [
                    ['hotel-quartier-marche', 'store', $T('Marché couvert animé avec étals de fruits et de fleurs', 'Lively covered market with fruit and flower stalls')],
                    ['hotel-quartier-musee', 'landmark', $T('Visiteurs dans la grande salle d’un musée d’art', 'Visitors in the main hall of an art museum')],
                    ['hotel-quartier-rue', 'map-pin', $T('Rue pavée bordée de cafés et de boutiques en fin de journée', 'Cobbled street lined with cafés and shops at dusk')],
                ]]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'journal', 'slug' => 'journal', 'title' => $T('Journal', 'Journal'),
            'description' => $T('Le journal de l’hôtel : bonnes adresses, idées de sorties, vie de la maison et agenda des soirées et événements à venir.', 'The hotel journal: favorite local spots, ideas for outings, life at the hotel and upcoming evenings and events.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Journal', 'Journal'), 'title' => $T('Vivre la ville comme un habitant', 'Experience the city like a local'), 'intro' => $T('Les bonnes adresses de notre équipe et les nouvelles de la maison.', 'Our team’s favorite spots and news from the hotel.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['icon' => 'calendar-days', 'eyebrow' => $T('À l’agenda', 'Coming up'), 'title' => $T('Soirées et rendez-vous', 'Evenings and events'), 'bg' => 'light', 'items' => [
                    [$T('Soirée jazz sur le toit', 'Jazz night on the rooftop'), $T('Tous les jeudis, 19 h', 'Every Thursday, 7 pm'), $T('Bar sur le toit', 'Rooftop bar')],
                    [$T('Brunch du dimanche', 'Sunday brunch'), $T('Chaque dimanche, 11 h – 15 h', 'Every Sunday, 11 am – 3 pm'), $T('Restaurant', 'Restaurant')],
                    [$T('Dîner accords mets et vins', 'Wine-pairing dinner'), $T('Premier vendredi du mois, 20 h', 'First Friday of the month, 8 pm'), $T('Restaurant', 'Restaurant')],
                    [$T('Atelier cocktails sans alcool', 'Alcohol-free cocktail workshop'), $T('Un samedi par mois, 17 h', 'One Saturday a month, 5 pm'), $T('Bar sur le toit', 'Rooftop bar')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => $T('Réservation et contact', 'Booking and contact'),
            'description' => $T('Réservez votre chambre, votre table ou votre séminaire en direct : meilleur tarif garanti et réponse rapide de la réception.', 'Book your room, table or meeting direct: best rate guaranteed and a quick reply from the front desk.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Réservation', 'Booking'), 'title' => $T('Réservez votre séjour', 'Book your stay'), 'intro' => $T('Indiquez vos dates et vos envies : la réception confirme votre réservation dans l’heure, de 7 h à 23 h.', 'Tell us your dates and wishes: the front desk confirms your booking within the hour, from 7 am to 11 pm.')]),
                B::steps($theme, ['items' => [
                    [$T('Vos dates', 'Your dates'), $T('Précisez vos dates, le nombre de personnes et la chambre souhaitée.', 'Give your dates, number of guests and preferred room.')],
                    [$T('Confirmation', 'Confirmation'), $T('Nous vous envoyons le tarif et la confirmation par e-mail.', 'We send you the rate and confirmation by email.')],
                    [$T('Garantie', 'Guarantee'), $T('Votre réservation est garantie par carte bancaire ou par acompte.', 'Your booking is guaranteed by card or by deposit.')],
                    [$T('Bienvenue', 'Welcome'), $T('Nous préparons votre arrivée selon vos préférences.', 'We prepare your arrival according to your preferences.')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Nous contacter', 'Contact us'), 'intro' => $T('Par téléphone, par e-mail ou avec le formulaire : précisez vos dates d’arrivée et de départ.', 'By phone, email or the form: please give your arrival and departure dates.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Réception ouverte 24 h/24, 7 j/7', 'Front desk open 24/7')],
                    ],
                    'social' => ['instagram', 'facebook', 'whatsapp'],
                    'subject' => true,
                    'button' => $T('Envoyer ma demande', 'Send my request'),
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Conditions de réservation', 'Booking terms'), 'items' => [
                    [$T('Puis-je annuler sans frais ?', 'Can I cancel free of charge?'), $T('Oui, jusqu’à 48 heures avant la date d’arrivée pour les tarifs flexibles. Les tarifs non remboursables sont signalés comme tels.', 'Yes, up to 48 hours before arrival on flexible rates. Non-refundable rates are clearly marked.')],
                    [$T('Comment puis-je payer ?', 'How can I pay?'), $T('Par carte bancaire, virement ou paiement mobile. Le solde est réglé à la réception au moment du départ.', 'By card, bank transfer or mobile payment. The balance is settled at the front desk on departure.')],
                    [$T('La taxe de séjour est-elle incluse ?', 'Is the city tax included?'), $T('Non, elle est calculée par personne et par nuit selon le barème de la ville, et ajoutée à la facture.', 'No, it is charged per person per night according to the city’s rates and added to the bill.')],
                    [$T('Puis-je réserver pour un groupe ?', 'Can I book for a group?'), $T('Oui, à partir de cinq chambres, nous vous proposons un tarif de groupe et un contact dédié.', 'Yes, from five rooms we offer a group rate and a dedicated contact.')],
                ]]),
            ]];

        $blog = [
            'base' => 'journal',
            'title' => $T('Journal', 'Journal'),
            'categories' => ['ville' => $T('Découvrir la ville', 'Explore the city'), 'maison' => $T('Vie de l’hôtel', 'Hotel life'), 'table' => $T('À table', 'Food and drink')],
            'posts' => [
                ['key' => 'week-end', 'slug' => $u('48-heures-en-ville', '48-hours-in-the-city'), 'category' => 'ville', 'tags' => [$T('Week-end', 'Weekend')],
                    'title' => $T('48 heures en ville : notre itinéraire idéal', '48 hours in the city: our ideal itinerary'),
                    'excerpt' => $T('Musées, marchés, balades et bonnes tables : le programme concocté par notre conciergerie.', 'Museums, markets, walks and great food: the plan put together by our concierge.'),
                    'photo' => ['hotel-blog-ville', 'map', $T('Voyageurs se promenant dans une rue piétonne historique un matin', 'Travelers strolling down a historic pedestrian street in the morning')],
                    'heading' => $T('Le programme', 'The plan'),
                    'body' => [
                        $T('Un week-end suffit pour saisir l’âme d’une ville, à condition de bien choisir. Notre conciergerie a rassemblé ses adresses préférées, testées par l’équipe.', 'A weekend is enough to capture a city’s soul, as long as you choose well. Our concierge has gathered favorite spots, tested by the team.'),
                        $T('Samedi matin, le marché couvert et le quartier ancien ; l’après-midi, le grand musée puis un café en terrasse. Dimanche, une balade le long de l’eau et le brunch de l’hôtel.', 'Saturday morning, the covered market and the old town; in the afternoon, the main museum then a terrace café. Sunday, a walk along the water and the hotel brunch.'),
                        $T('Demandez la carte détaillée à la réception : nous réservons volontiers vos visites et vos tables.', 'Ask the front desk for the detailed map: we are happy to book your visits and tables.'),
                    ]],
                ['key' => 'renovation', 'slug' => $u('nouvelles-suites', 'new-suites'), 'category' => 'maison', 'tags' => [$T('Nouveautés', 'News')],
                    'title' => $T('Nos nouvelles suites sous les toits', 'Our new top-floor suites'),
                    'excerpt' => $T('Six mois de travaux, des artisans de la région et une terrasse privée avec vue.', 'Six months of work, local craftspeople and a private terrace with a view.'),
                    'photo' => ['hotel-blog-suite', 'gem', $T('Suite d’hôtel lumineuse sous les toits avec poutres apparentes', 'Bright top-floor hotel suite with exposed beams')],
                    'quote' => $T('« Nous voulions des suites qui racontent la ville, du parquet aux luminaires. »', '“We wanted suites that tell the city’s story, from the floorboards to the lights.”'),
                    'body' => [
                        $T('Le dernier étage de l’hôtel accueille désormais trois suites, aménagées avec des menuisiers, des céramistes et des tisserands de la région.', 'The hotel’s top floor now houses three suites, fitted out with local carpenters, ceramicists and weavers.'),
                        $T('Chacune dispose d’une terrasse privée, d’un salon et d’une salle de bain avec baignoire face à la vue.', 'Each has a private terrace, a living room and a bathroom with a tub facing the view.'),
                        $T('Les suites sont réservables dès aujourd’hui, avec un tarif d’ouverture pour les premiers séjours.', 'The suites can be booked from today, with an opening rate for the first stays.'),
                    ]],
                ['key' => 'brunch', 'slug' => $u('brunch-du-dimanche', 'sunday-brunch'), 'category' => 'table', 'tags' => ['Brunch'],
                    'title' => $T('Le brunch du dimanche fait peau neuve', 'Sunday brunch gets a makeover'),
                    'excerpt' => $T('Pâtisseries maison, plats chauds de saison et jus pressés : la nouvelle formule de notre chef.', 'Home-made pastries, seasonal hot dishes and fresh juices: our chef’s new formula.'),
                    'photo' => ['hotel-blog-brunch', 'coffee', $T('Table de brunch garnie de pâtisseries, d’œufs et de jus de fruits frais', 'Brunch table laid with pastries, eggs and fresh fruit juice')],
                    'heading' => $T('Au menu', 'On the menu'),
                    'body' => [
                        $T('Chaque dimanche, le restaurant ouvre ses portes aux hôtes comme aux habitants du quartier pour un brunch généreux.', 'Every Sunday, the restaurant opens its doors to guests and locals alike for a generous brunch.'),
                        $T('Viennoiseries de notre pâtissier, œufs cuisinés à la minute, légumes de saison, fromages et desserts : tout est fait maison.', 'Pastries from our baker, eggs cooked to order, seasonal vegetables, cheeses and desserts: everything is home-made.'),
                        $T('La réservation est conseillée, surtout pour les tables en terrasse.', 'Booking is recommended, especially for terrace tables.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Chambres', 'Rooms'), 'url' => $rooms],
                    ['label' => $T('Restaurant et bar', 'Dining'), 'url' => $dining],
                    ['label' => 'Services', 'url' => $services],
                    ['label' => $T('Séminaires', 'Meetings'), 'url' => $meetings],
                    ['label' => $T('Galerie', 'Gallery'), 'url' => $gallery],
                    ['label' => $T('Journal', 'Journal'), 'url' => $journal],
                ],
                'cta_label' => $T('Réserver', 'Book now'),
                'cta_url' => $contact,
                'footer_text' => $T('Boutique-hôtel au cœur de la ville : chambres et suites, restaurant de saison, bar sur le toit et espaces de séminaire.', 'Boutique hotel in the heart of the city: rooms and suites, seasonal restaurant, rooftop bar and meeting spaces.'),
                'footer_links' => [
                    ['label' => $T('Chambres et suites', 'Rooms and suites'), 'url' => $rooms],
                    ['label' => $T('Séminaires', 'Meetings'), 'url' => $meetings],
                    ['label' => $T('Journal', 'Journal'), 'url' => $journal],
                    ['label' => $T('Réservation', 'Booking'), 'url' => $contact],
                ],
            ],
        ];
    },
];
