<?php

/*
 * Pack « Restaurant et hôtellerie » : 8 pages, en français et en anglais.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'établissement). Les liens internes
 * sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Restaurant et hôtellerie', 'en' => 'Restaurant and hospitality'],
        'icon' => 'utensils',
        'keywords' => ['restaurant', 'hôtel', 'hotel', 'auberge', 'maison d’hôtes', 'guesthouse', 'traiteur', 'catering', 'café', 'cafe', 'bar', 'lodge', 'chambres', 'rooms', 'cuisine', 'réservation', 'booking'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $menu = $u('/la-carte', '/menu');
        $rooms = $u('/chambres', '/rooms');
        $events = $u('/evenements', '/events');
        $gallery = $u('/galerie', '/gallery');
        $about = $u('/a-propos', '/about');
        $booking = $u('/reservation', '/booking');
        $contact = '/contact';

        $testimonials = ['eyebrow' => $T('Livre d’or', 'Guest book'), 'title' => $T('Ils sont venus, ils reviennent', 'They came, they return'), 'items' => [
            [$T('Un poisson grillé à tomber, un service chaleureux et une vue magnifique sur le lac. Notre repas de famille préféré.', 'Outstanding grilled fish, warm service and a gorgeous view of the lake. Our favorite family meal.'), '[Prénom Nom]', $T('Cliente du restaurant', 'Restaurant guest')],
            [$T('Chambre impeccable, petit-déjeuner généreux et équipe aux petits soins. Je recommande pour un séjour d’affaires.', 'Spotless room, generous breakfast and an attentive team. Highly recommended for a business stay.'), '[Prénom Nom]', $T('Voyageur d’affaires', 'Business traveler')],
            [$T('Notre mariage s’est déroulé dans la joie, avec un buffet remarquable. Tout était parfait, jusqu’au dernier détail.', 'Our wedding was joyful, with a remarkable buffet. Everything was perfect, down to the last detail.'), '[Prénom Nom]', $T('Mariée', 'Bride')],
        ]];
        $cta = ['title' => $T('Une table vous attend', 'A table is waiting for you'), 'text' => $T('Réservez votre repas, votre chambre ou votre événement : nous vous répondons rapidement.', 'Book your meal, your room or your event: we reply quickly.'),
            'buttons' => [[$T('Réserver', 'Book now'), $booking, 'accent'], [$T('Voir la carte', 'See the menu'), $menu, 'outline-light']]];
        $dishPhotos = [
            ['restaurant-poisson-grille', 'utensils', $T('Capitaine grillé servi avec plantain et sauce pimentée', 'Grilled Nile perch served with plantain and chili sauce')],
            ['restaurant-moambe', 'chef-hat', $T('Poulet moambé fumant avec du riz blanc', 'Steaming chicken moambe with white rice')],
            ['restaurant-brochettes', 'flame', $T('Brochettes de bœuf grillées au charbon', 'Charcoal-grilled beef skewers')],
            ['restaurant-dessert', 'cake', $T('Gâteau à la banane et glace vanille', 'Banana cake with vanilla ice cream')],
            ['restaurant-cafe', 'coffee', $T('Café local servi sur la terrasse', 'Local coffee served on the terrace')],
            ['restaurant-salade', 'leaf', $T('Salade fraîche d’avocat et de mangue', 'Fresh avocado and mango salad')],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Restaurant, chambres et événements au bord de l’eau : une cuisine généreuse et un accueil chaleureux. Réservez votre table ou votre séjour.', 'Restaurant, rooms and events by the water: generous cooking and a warm welcome. Book your table or your stay.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Restaurant · Hôtel · Événements', 'Restaurant · Hotel · Events'),
                    'title' => $T('Savourez l’hospitalité, du premier plat au dernier sourire', 'Enjoy true hospitality, from the first dish to the last smile'),
                    'text' => $T('Une cuisine généreuse aux saveurs d’ici et d’ailleurs, des chambres confortables et un cadre apaisant pour vos repas, vos séjours et vos fêtes.', 'Generous cooking with flavors from here and beyond, comfortable rooms and a peaceful setting for your meals, stays and celebrations.'),
                    'buttons' => [[$T('Réserver une table', 'Book a table'), $booking], [$T('Découvrir la carte', 'Discover the menu'), $menu]],
                    'photo' => ['restaurant-accueil', 'utensils', $T('Terrasse du restaurant au coucher du soleil', 'Restaurant terrace at sunset')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Notre maison', 'Our house'), 'title' => $T('Manger, dormir, célébrer', 'Dine, stay, celebrate'), 'items' => [
                    ['chef-hat', $T('Cuisine', 'Cuisine'), $T('Des produits frais du marché, cuisinés chaque jour par notre brigade.', 'Fresh market produce, cooked daily by our kitchen team.')],
                    ['bed', $T('Chambres', 'Rooms'), $T('Des chambres calmes et soignées pour un repos complet.', 'Quiet, carefully kept rooms for a proper rest.')],
                    ['party-popper', $T('Événements', 'Events'), $T('Mariages, séminaires et fêtes de famille, de 20 à 200 invités.', 'Weddings, seminars and family parties, from 20 to 200 guests.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Une table ouverte à tous', 'A table open to everyone'),
                    'html' => '<p>'.e($T('Née d’une passion pour la bonne cuisine partagée, notre maison accueille voyageurs, familles et gens du quartier. Ici, on cuisine avec des produits locaux, on prend le temps et on s’assure que chacun se sente chez soi.', 'Born from a passion for good food shared around the table, our house welcomes travelers, families and locals. Here we cook with local produce, take our time and make sure everyone feels at home.')).'</p>',
                    'bullets' => [$T('Produits frais achetés auprès de producteurs locaux', 'Fresh produce from local growers'), $T('Cuisine africaine et internationale', 'African and international cuisine'), $T('Terrasse avec vue et service attentionné', 'Terrace with a view and attentive service')],
                    'buttons' => [[$T('Notre histoire', 'Our story'), $about, 'outline']],
                    'photo' => ['restaurant-salle', 'armchair', $T('Salle du restaurant chaleureuse et lumineuse', 'Warm, bright restaurant dining room')],
                    'reverse' => true,
                ]),
                B::cards($theme, ['eyebrow' => $T('À la carte', 'On the menu'), 'title' => $T('Nos plats signature', 'Our signature dishes'), 'more' => $T('Voir la carte', 'See the menu'), 'items' => [
                    ['restaurant-poisson-grille', 'utensils', $T('Capitaine grillé', 'Grilled Nile perch'), $T('Poisson du lac mariné aux épices, plantains frais et sauce pimentée maison.', 'Lake fish marinated in spices, fresh plantain and house chili sauce.'), $menu],
                    ['restaurant-moambe', 'chef-hat', $T('Poulet moambé', 'Chicken moambe'), $T('Le grand classique mijoté à l’huile de palme, servi avec riz et chikwangue.', 'The great classic simmered in palm oil, served with rice and cassava bread.'), $menu],
                    ['restaurant-brochettes', 'flame', $T('Brochettes au charbon', 'Charcoal skewers'), $T('Bœuf tendre grillé au feu de bois, frites de manioc et salade fraîche.', 'Tender beef grilled over wood fire, cassava fries and fresh salad.'), $menu],
                ]]),
                B::stats($theme, ['items' => [
                    [15, '', $T('chambres confortables', 'comfortable rooms')],
                    [120, '', $T('couverts en salle et en terrasse', 'seats indoors and on the terrace')],
                    [500, '+', $T('événements organisés', 'events hosted')],
                    [10, '+', $T('ans d’hospitalité', 'years of hospitality')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'menu', 'slug' => $u('la-carte', 'menu'), 'title' => $T('La carte', 'Menu'),
            'description' => $T('Entrées, plats, desserts et boissons : notre carte aux saveurs africaines et internationales, avec les prix.', 'Starters, mains, desserts and drinks: our menu of African and international flavors, with prices.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('La carte', 'Menu'), 'title' => $T('Une cuisine de saison, faite maison', 'Seasonal, home-made cooking'), 'intro' => $T('Prix indicatifs en dollars américains. Dites-nous vos allergies ou vos préférences : nous adaptons volontiers.', 'Indicative prices in US dollars. Tell us about allergies or preferences: we are happy to adapt.')]),
                B::schedule($theme, ['bg' => 'white', 'groups' => [
                    [$T('Entrées', 'Starters'), [
                        ['leaf', $T('Salade d’avocat et mangue — 6 $', 'Avocado and mango salad — $6')],
                        ['utensils', $T('Beignets de poisson, sauce pili-pili — 7 $', 'Fish fritters with pili-pili sauce — $7')],
                        ['flame', $T('Brochettes de gésiers — 6 $', 'Gizzard skewers — $6')],
                        ['sprout', $T('Soupe de légumes du jour — 5 $', 'Soup of the day — $5')],
                    ]],
                    [$T('Plats', 'Mains'), [
                        ['chef-hat', $T('Poulet moambé, riz — 12 $', 'Chicken moambe with rice — $12')],
                        ['utensils', $T('Capitaine grillé, plantain — 15 $', 'Grilled Nile perch with plantain — $15')],
                        ['flame', $T('Brochettes de bœuf, frites — 13 $', 'Beef skewers with fries — $13')],
                        ['wheat', $T('Pâtes sauce tomate et basilic — 10 $', 'Pasta with tomato and basil sauce — $10')],
                        ['leaf', $T('Assiette végétarienne — 9 $', 'Vegetarian platter — $9')],
                    ]],
                    [$T('Desserts', 'Desserts'), [
                        ['cake', $T('Gâteau à la banane, glace vanille — 6 $', 'Banana cake with vanilla ice cream — $6')],
                        ['sparkles', $T('Salade de fruits frais — 5 $', 'Fresh fruit salad — $5')],
                        ['gift', $T('Crêpe au miel et aux arachides — 5 $', 'Honey and peanut crepe — $5')],
                    ]],
                    [$T('Boissons', 'Drinks'), [
                        ['coffee', $T('Café ou thé local — 2 $', 'Local coffee or tea — $2')],
                        ['droplet', $T('Jus frais maison — 4 $', 'Fresh home-made juice — $4')],
                        ['sun', $T('Limonade au gingembre — 3 $', 'Ginger lemonade — $3')],
                        ['star', $T('Bière locale ou vin — dès 3 $', 'Local beer or wine — from $3')],
                    ]],
                ]]),
                B::quote($theme, ['text' => $T('Une bonne cuisine, c’est du temps, de bons produits et l’envie de faire plaisir.', 'Good cooking is time, good ingredients and the wish to please.'), 'source' => $T('— [Prénom Nom], chef de cuisine', '— [Prénom Nom], head chef'), 'bg' => 'light']),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('Nos plats en images', 'Our dishes in pictures'), 'photos' => $dishPhotos]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'rooms', 'slug' => $u('chambres', 'rooms'), 'title' => $T('Chambres', 'Rooms'),
            'description' => $T('Chambres standard, supérieures et suites avec petit-déjeuner, wifi et services. Tarifs par nuit et conditions de séjour.', 'Standard, superior rooms and suites with breakfast, wifi and services. Nightly rates and stay conditions.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Hébergement', 'Accommodation'), 'title' => $T('Des chambres pensées pour bien dormir', 'Rooms designed for a good night’s sleep'), 'intro' => $T('Calme, propreté et confort, à deux pas du restaurant.', 'Quiet, clean and comfortable, a few steps from the restaurant.')]),
                B::pricing($theme, ['eyebrow' => $T('Tarifs par nuit', 'Nightly rates'), 'title' => $T('Choisissez votre chambre', 'Choose your room'), 'url' => $booking, 'items' => [
                    [$T('Chambre standard', 'Standard room'), '45', '$', $T('/ nuit', '/ night'), $T('Lit double, salle d’eau privée, idéale pour une ou deux personnes.', 'Double bed, private bathroom, ideal for one or two guests.'), [$T('Petit-déjeuner inclus', 'Breakfast included'), $T('Wifi gratuit', 'Free wifi'), $T('Ventilateur et moustiquaire', 'Fan and mosquito net'), '-'.$T('Vue sur le lac', 'Lake view')], $T('Réserver', 'Book')],
                    [$T('Chambre supérieure', 'Superior room'), '75', '$', $T('/ nuit', '/ night'), $T('Plus spacieuse, climatisée, avec balcon et coin salon.', 'Larger, air-conditioned, with a balcony and sitting area.'), [$T('Petit-déjeuner inclus', 'Breakfast included'), $T('Wifi gratuit', 'Free wifi'), $T('Climatisation et télévision', 'Air conditioning and TV'), $T('Vue sur le lac', 'Lake view')], $T('Réserver', 'Book'), true, $T('Préférée', 'Most popular')],
                    [$T('Suite', 'Suite'), '120', '$', $T('/ nuit', '/ night'), $T('Chambre et salon séparés, grande terrasse privée pour les séjours en famille.', 'Separate bedroom and lounge with a large private terrace, perfect for families.'), [$T('Petit-déjeuner inclus', 'Breakfast included'), $T('Wifi gratuit', 'Free wifi'), $T('Climatisation et minibar', 'Air conditioning and minibar'), $T('Terrasse privée vue lac', 'Private lake-view terrace')], $T('Réserver', 'Book')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Services', 'Amenities'), 'title' => $T('Tout pour votre confort', 'Everything for your comfort'), 'cols' => 3, 'items' => [
                    ['wifi', $T('Wifi gratuit', 'Free wifi'), $T('Connexion disponible dans les chambres, au restaurant et à la terrasse.', 'Connection available in the rooms, restaurant and terrace.')],
                    ['coffee', $T('Petit-déjeuner', 'Breakfast'), $T('Buffet servi de 6 h 30 à 10 h : fruits, pain frais, œufs, café local.', 'Buffet served 6:30 to 10 am: fruit, fresh bread, eggs, local coffee.')],
                    ['waves', $T('Piscine et détente', 'Pool and relaxation'), $T('Piscine entourée de transats pour se détendre après une journée chargée.', 'A pool surrounded by loungers to unwind after a busy day.')],
                    ['car', $T('Parking sécurisé', 'Secure parking'), $T('Parking gardé jour et nuit pour les voitures de nos clients.', 'Guarded parking, day and night, for guests’ cars.')],
                    ['plane', $T('Navette aéroport', 'Airport shuttle'), $T('Transfert depuis l’aéroport ou la gare sur réservation.', 'Transfer from the airport or station on request.')],
                    ['bell', $T('Service en chambre', 'Room service'), $T('Repas et boissons livrés dans votre chambre, de 7 h à 22 h.', 'Meals and drinks delivered to your room, 7 am to 10 pm.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Votre séjour', 'Your stay'), 'intro' => $T('Une autre question ? Écrivez-nous, nous répondons vite.', 'Another question? Write to us, we reply quickly.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('À quelle heure est l’arrivée et le départ ?', 'What are check-in and check-out times?'), $T('L’arrivée se fait à partir de 14 h et le départ avant 11 h. Arrivée tardive ou départ différé possibles sur demande.', 'Check-in is from 2 pm and check-out before 11 am. Late arrival or late check-out available on request.')],
                    [$T('Le petit-déjeuner est-il inclus ?', 'Is breakfast included?'), $T('Oui, il est inclus dans tous nos tarifs et servi chaque matin au restaurant.', 'Yes, it is included in all our rates and served every morning in the restaurant.')],
                    [$T('Acceptez-vous les enfants et les animaux ?', 'Do you accept children and pets?'), $T('Les enfants sont les bienvenus (lit d’appoint disponible). Les animaux ne sont pas acceptés.', 'Children are welcome (extra bed available). Pets are not accepted.')],
                    [$T('Comment payer mon séjour ?', 'How can I pay for my stay?'), $T('En espèces, par Mobile Money ou par carte bancaire à la réception.', 'By cash, Mobile Money or bank card at reception.')],
                ]]),
            ]];

        $pages[] = ['key' => 'events', 'slug' => $u('evenements', 'events'), 'title' => $T('Événements', 'Events'),
            'description' => $T('Mariages, séminaires, anniversaires et repas d’entreprise : salles, traiteur et organisation clés en main.', 'Weddings, seminars, birthdays and corporate meals: venues, catering and turnkey organization.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Événements', 'Events'), 'title' => $T('Vos plus beaux moments, chez nous', 'Your best moments, with us'), 'intro' => $T('Nous nous occupons de la salle, du repas et de l’ambiance. Vous profitez de vos invités.', 'We take care of the venue, the food and the atmosphere. You enjoy your guests.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['heart', $T('Mariages et fiançailles', 'Weddings and engagements'), $T('Cérémonie, cocktail et dîner dans un cadre fleuri, avec décoration et musique.', 'Ceremony, cocktail and dinner in a flowered setting, with decoration and music.')],
                    ['presentation', $T('Séminaires et réunions', 'Seminars and meetings'), $T('Salle équipée, pauses café, déjeuner et hébergement pour vos équipes.', 'Equipped room, coffee breaks, lunch and accommodation for your teams.')],
                    ['cake', $T('Anniversaires et fêtes', 'Birthdays and parties'), $T('Gâteaux, buffets et animations pour les petits comme pour les grands.', 'Cakes, buffets and entertainment for kids and adults alike.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Comment ça marche', 'How it works'), 'title' => $T('Organiser votre événement', 'Organizing your event'), 'bg' => 'light', 'items' => [
                    [$T('Parlons-en', 'Let’s talk'), $T('Dites-nous la date, le nombre d’invités et vos envies.', 'Tell us the date, number of guests and your wishes.')],
                    [$T('Devis', 'Quote'), $T('Vous recevez une proposition claire sous 48 heures.', 'You receive a clear proposal within 48 hours.')],
                    [$T('Dégustation', 'Tasting'), $T('Vous goûtez le menu et ajustez les détails.', 'You taste the menu and fine-tune the details.')],
                    [$T('Le grand jour', 'The big day'), $T('Notre équipe s’occupe de tout, du montage au service.', 'Our team handles everything, from setup to service.')],
                ]]),
                B::events($theme, ['eyebrow' => $T('Agenda', 'Calendar'), 'title' => $T('Prochaines soirées', 'Upcoming evenings'), 'items' => [
                    [$T('Soirée musique live', 'Live music night'), $T('Chaque vendredi, 19 h', 'Every Friday, 7 pm'), $T('Terrasse du restaurant', 'Restaurant terrace')],
                    [$T('Brunch du dimanche', 'Sunday brunch'), $T('Chaque dimanche, 10 h – 14 h', 'Every Sunday, 10 am – 2 pm'), $T('Salle principale', 'Main dining room')],
                    [$T('Dégustation de poissons du lac', 'Lake fish tasting'), $T('Premier samedi du mois, 18 h', 'First Saturday of the month, 6 pm'), $T('Terrasse du restaurant', 'Restaurant terrace')],
                    [$T('Soirée dansante', 'Dance night'), $T('Dernier samedi du mois, 20 h', 'Last Saturday of the month, 8 pm'), $T('Jardin', 'Garden')],
                ]]),
                B::cta($theme, ['title' => $T('Parlons de votre événement', 'Let’s talk about your event'), 'text' => $T('Donnez-nous la date et le nombre d’invités : nous vous envoyons un devis sur mesure.', 'Give us the date and number of guests: we will send you a tailored quote.'), 'buttons' => [[$T('Demander un devis', 'Request a quote'), $booking, 'accent'], [$T('Nous écrire', 'Write to us'), $contact, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'gallery', 'slug' => $u('galerie', 'gallery'), 'title' => $T('Galerie', 'Gallery'),
            'description' => $T('Plats, chambres, terrasse et fêtes : découvrez notre établissement en photos.', 'Dishes, rooms, terrace and parties: discover our place in pictures.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Galerie', 'Gallery'), 'title' => $T('Un avant-goût en images', 'A taste in pictures'), 'intro' => $T('La table, les chambres et les moments partagés chez nous.', 'The table, the rooms and the moments shared with us.')]),
                B::gallery($theme, ['cols' => 3, 'photos' => [
                    ['restaurant-accueil', 'utensils', $T('Terrasse du restaurant au coucher du soleil', 'Restaurant terrace at sunset')],
                    ['restaurant-poisson-grille', 'utensils', $T('Capitaine grillé servi avec plantain et sauce pimentée', 'Grilled Nile perch served with plantain and chili sauce')],
                    ['restaurant-salle', 'armchair', $T('Salle du restaurant chaleureuse et lumineuse', 'Warm, bright restaurant dining room')],
                    ['restaurant-chambre-standard', 'bed', $T('Chambre standard avec lit double', 'Standard room with a double bed')],
                    ['restaurant-chambre-suite', 'sofa', $T('Suite avec salon et terrasse privée', 'Suite with lounge and private terrace')],
                    ['restaurant-piscine', 'waves', $T('Piscine entourée de transats', 'Pool surrounded by loungers')],
                    ['restaurant-mariage', 'party-popper', $T('Table dressée pour un mariage', 'Table set for a wedding')],
                    ['restaurant-moambe', 'chef-hat', $T('Poulet moambé fumant avec du riz blanc', 'Steaming chicken moambe with white rice')],
                    ['restaurant-cafe', 'coffee', $T('Café local servi sur la terrasse', 'Local coffee served on the terrace')],
                    ['restaurant-dessert', 'cake', $T('Gâteau à la banane et glace vanille', 'Banana cake with vanilla ice cream')],
                    ['restaurant-jardin', 'flower-2', $T('Jardin fleuri de l’établissement', 'The flowering garden')],
                    ['restaurant-brochettes', 'flame', $T('Brochettes de bœuf grillées au charbon', 'Charcoal-grilled beef skewers')],
                ]]),
                B::quote($theme, ['text' => $T('Venez comme invités, repartez comme amis.', 'Come as guests, leave as friends.'), 'source' => $T('— Notre devise', '— Our motto'), 'bg' => 'light']),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('a-propos', 'about'), 'title' => $T('À propos', 'About us'),
            'description' => $T('Notre histoire, notre chef, notre équipe et les valeurs qui font l’esprit de la maison.', 'Our story, our chef, our team and the values behind the spirit of the house.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('À propos', 'About us'), 'title' => $T('L’esprit de la maison', 'The spirit of the house'), 'intro' => $T('Une famille, une cuisine, et l’envie de bien recevoir.', 'A family, a kitchen, and the wish to host well.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Tout a commencé dans une petite cuisine', 'It all started in a small kitchen'),
                    'html' => '<p>'.e($T('Il y a plus de dix ans, nous avons ouvert quelques tables sur une terrasse. Le bouche-à-oreille a fait le reste : aujourd’hui, nous avons un restaurant, des chambres et une salle de fêtes, toujours avec la même exigence.', 'More than ten years ago we opened a few tables on a terrace. Word of mouth did the rest: today we have a restaurant, rooms and an event hall, always with the same standards.')).'</p><p>'.e($T('Nous restons une maison familiale : on connaît nos clients, et nos clients connaissent notre équipe.', 'We remain a family house: we know our guests, and our guests know our team.')).'</p>',
                    'photo' => ['restaurant-histoire', 'house', $T('Façade de l’établissement et sa terrasse', 'The house façade and its terrace')],
                ]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Ceux qui vous accueillent', 'The people who welcome you'), 'bg' => 'light', 'items' => [
                    ['[Prénom Nom]', $T('Chef de cuisine', 'Head chef'), 'restaurant-equipe-chef'],
                    ['[Prénom Nom]', $T('Directrice de l’établissement', 'General manager'), 'restaurant-equipe-directrice'],
                    ['[Prénom Nom]', $T('Maître d’hôtel', 'Maître d’'), 'restaurant-equipe-maitre'],
                    ['[Prénom Nom]', $T('Responsable des chambres', 'Housekeeping manager'), 'restaurant-equipe-chambres'],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce qui guide notre accueil', 'What guides our welcome'), 'items' => [
                    ['heart-handshake', $T('Hospitalité', 'Hospitality'), $T('Chaque client est un invité que l’on reçoit comme à la maison.', 'Every customer is a guest welcomed like at home.')],
                    ['leaf', $T('Produits locaux', 'Local produce'), $T('Nous travaillons avec les pêcheurs, maraîchers et éleveurs de la région.', 'We work with fishermen, market gardeners and farmers of the region.')],
                    ['sparkles', $T('Qualité et propreté', 'Quality and cleanliness'), $T('Une cuisine soignée, des chambres impeccables, aucun compromis.', 'Careful cooking, spotless rooms, no compromise.')],
                ]]),
            ]];

        $pages[] = ['key' => 'booking', 'slug' => $u('reservation', 'booking'), 'title' => $T('Réservation', 'Booking'),
            'description' => $T('Réservez une table, une chambre ou un événement : horaires d’ouverture, démarche et conditions d’annulation.', 'Book a table, a room or an event: opening hours, how to book and cancellation terms.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Réservation', 'Booking'), 'title' => $T('Réservez en quelques minutes', 'Book in just a few minutes'), 'intro' => $T('Table, chambre ou événement : choisissez votre moyen préféré.', 'Table, room or event: choose the way you prefer.')]),
                B::steps($theme, ['eyebrow' => $T('Comment réserver', 'How to book'), 'title' => $T('Trois étapes simples', 'Three simple steps'), 'items' => [
                    [$T('Écrivez-nous', 'Write to us'), $T('Remplissez le formulaire ci-dessous ou appelez-nous, WhatsApp compris.', 'Fill in the form below or call us, WhatsApp included.')],
                    [$T('Confirmation', 'Confirmation'), $T('Nous vous répondons sous quelques heures avec la disponibilité.', 'We reply within a few hours with availability.')],
                    [$T('Profitez', 'Enjoy'), $T('Présentez-vous : votre table ou votre chambre vous attend.', 'Just show up: your table or room is waiting for you.')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Horaires', 'Opening hours'), 'title' => $T('Quand nous rendre visite', 'When to visit'), 'groups' => [
                    [$T('Restaurant', 'Restaurant'), [
                        ['clock', $T('Déjeuner : 12 h – 15 h', 'Lunch: 12 – 3 pm')],
                        ['clock', $T('Dîner : 18 h 30 – 22 h 30', 'Dinner: 6:30 – 10:30 pm')],
                        ['calendar-days', $T('Ouvert tous les jours', 'Open every day')],
                    ]],
                    [$T('Hôtel', 'Hotel'), [
                        ['key', $T('Arrivée dès 14 h', 'Check-in from 2 pm')],
                        ['clock', $T('Départ avant 11 h', 'Check-out before 11 am')],
                        ['bell', $T('Réception ouverte 24 h/24', 'Reception open 24/7')],
                    ]],
                ]]),
                B::contact($theme, [
                    'eyebrow' => $T('Demande de réservation', 'Booking request'), 'title' => $T('Votre demande', 'Your request'),
                    'intro' => $T('Précisez dans le message la date, l’heure et le nombre de personnes.', 'Please state the date, time and number of guests in your message.'),
                    'items' => [
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['message-circle', $T('WhatsApp : +243 000 000 000', 'WhatsApp: +243 000 000 000'), 'https://wa.me/243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                    ],
                    'subject' => true, 'button' => $T('Envoyer ma demande', 'Send my request'), 'bg' => 'white',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Conditions de réservation', 'Booking conditions'), 'bg' => 'light', 'items' => [
                    [$T('Puis-je annuler ma réservation ?', 'Can I cancel my booking?'), $T('Oui, gratuitement jusqu’à 48 heures avant l’arrivée. Passé ce délai, la première nuit peut être facturée.', 'Yes, free of charge up to 48 hours before arrival. After that, the first night may be charged.')],
                    [$T('Faut-il verser un acompte ?', 'Is a deposit required?'), $T('Pour les chambres, un acompte de 30 % confirme la réservation. Pour un événement, il est de 50 % à la signature du devis.', 'For rooms, a 30% deposit confirms the booking. For events, it is 50% when the quote is signed.')],
                    [$T('Réservez-vous les tables ?', 'Do you take table reservations?'), $T('Oui, et c’est conseillé le week-end et pour les groupes de plus de six personnes.', 'Yes, and it is advised on weekends and for groups of more than six.')],
                    [$T('Comment payer l’acompte ?', 'How do I pay the deposit?'), $T('Par Mobile Money ou virement bancaire ; nous vous envoyons les coordonnées à la confirmation.', 'By Mobile Money or bank transfer; we send the details with the confirmation.')],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Adresse, téléphone, WhatsApp, e-mail et horaires : toutes les façons de nous joindre.', 'Address, phone, WhatsApp, email and hours: every way to reach us.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Nous sommes à votre écoute', 'We are here for you'), 'intro' => $T('Une question, une réservation ou une demande spéciale ? Écrivez-nous.', 'A question, a booking or a special request? Write to us.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Passez nous voir ou contactez-nous : nous répondons chaque jour.', 'Come by or get in touch: we reply every day.'),
                    'items' => [
                        ['map-pin', $T('Boulevard du Lac, Goma, RDC', 'Boulevard du Lac, Goma, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['message-circle', $T('WhatsApp : +243 000 000 000', 'WhatsApp: +243 000 000 000'), 'https://wa.me/243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Tous les jours, 7 h – 23 h', 'Every day, 7 am – 11 pm')],
                    ],
                    'social' => ['instagram', 'facebook', 'whatsapp', 'tiktok'],
                    'subject' => true,
                    'map' => 'Goma, Nord-Kivu',
                    'bg' => 'white',
                ]),
            ]];

        $blog = [
            'base' => $u('journal', 'journal'),
            'title' => $T('Le journal de la maison', 'House journal'),
            'categories' => ['cuisine' => $T('Cuisine', 'Kitchen'), 'evenements' => $T('Événements', 'Events'), 'maison' => $T('La maison', 'The house')],
            'posts' => [
                ['key' => 'moambe', 'slug' => $u('le-secret-de-notre-moambe', 'the-secret-of-our-moambe'), 'category' => 'cuisine', 'tags' => [$T('Recettes', 'Recipes')],
                    'title' => $T('Le secret de notre poulet moambe', 'The secret of our chicken moambe'),
                    'excerpt' => $T('Le chef partage l’histoire d’un plat transmis de génération en génération.', 'The chef shares the story of a dish passed down through generations.'),
                    'photo' => ['restaurant-moambe', 'chef-hat', $T('Plat mijoté', 'Slow-cooked dish')],
                    'heading' => $T('Une cuisson lente', 'Slow cooking'),
                    'body' => [
                        $T('La moambe est l’un des plats préférés de nos clients. Sa sauce, préparée avec soin, demande patience et des ingrédients frais du marché.', 'Moambe is one of our guests’ favorite dishes. Its sauce, carefully prepared, takes patience and fresh ingredients from the market.'),
                        $T('Notre chef la prépare chaque matin selon la recette de sa grand-mère, en laissant mijoter plusieurs heures.', 'Our chef prepares it every morning from his grandmother’s recipe, letting it simmer for several hours.'),
                        $T('Elle est servie avec du riz ou du fufu, au déjeuner comme au dîner.', 'It is served with rice or fufu, at lunch and dinner.'),
                    ]],
                ['key' => 'mariages', 'slug' => $u('organiser-son-mariage', 'planning-your-wedding'), 'category' => 'evenements', 'tags' => [$T('Mariages', 'Weddings')],
                    'title' => $T('Organiser son mariage chez nous', 'Planning your wedding with us'),
                    'excerpt' => $T('Salle, menu, décoration et hébergement des invités : un seul interlocuteur.', 'Venue, menu, decoration and guest accommodation: one single contact.'),
                    'photo' => ['restaurant-mariage', 'party-popper', $T('Table de mariage dressée', 'Wedding table setting')],
                    'quote' => $T('« Notre plus belle récompense : voir les familles repartir heureuses. »', '“Our greatest reward: seeing families leave happy.”'),
                    'body' => [
                        $T('Nous accueillons les mariages jusqu’à [nombre] invités, en salle ou dans le jardin.', 'We host weddings for up to [number] guests, indoors or in the garden.'),
                        $T('Un menu sur mesure est composé avec vous, et nos chambres permettent d’héberger la famille venue de loin.', 'A bespoke menu is designed with you, and our rooms can host family members traveling from afar.'),
                        $T('Contactez-nous au moins [nombre] mois à l’avance pour réserver votre date.', 'Contact us at least [number] months ahead to book your date.'),
                    ]],
                ['key' => 'jardin', 'slug' => $u('le-jardin-en-fleurs', 'the-garden-in-bloom'), 'category' => 'maison', 'tags' => [$T('Jardin', 'Garden')],
                    'title' => $T('Le jardin en fleurs', 'The garden in bloom'),
                    'excerpt' => $T('Un coin de calme pour prendre le café ou le petit-déjeuner.', 'A quiet corner for coffee or breakfast.'),
                    'photo' => ['restaurant-jardin', 'flower-2', $T('Fleurs du jardin', 'Garden flowers')],
                    'heading' => $T('Ouvert à tous', 'Open to all'),
                    'body' => [
                        $T('Notre jardin vient de retrouver ses couleurs : bougainvilliers, hibiscus et oiseaux de paradis accueillent nos hôtes.', 'Our garden has its colors back: bougainvillea, hibiscus and birds of paradise welcome our guests.'),
                        $T('Le petit-déjeuner y est servi chaque matin, et le café local l’après-midi.', 'Breakfast is served there every morning, and local coffee in the afternoon.'),
                        $T('Il est ouvert aux clients du restaurant comme aux résidents de l’hôtel.', 'It is open to restaurant guests and hotel residents alike.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('La carte', 'Menu'), 'url' => $menu],
                    ['label' => $T('Chambres', 'Rooms'), 'url' => $rooms],
                    ['label' => $T('Événements', 'Events'), 'url' => $events],
                    ['label' => $T('Galerie', 'Gallery'), 'url' => $gallery],
                    ['label' => $T('À propos', 'About'), 'url' => $about],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Réserver', 'Book now'),
                'cta_url' => $booking,
                'footer_text' => $T('Restaurant, chambres et événements : une cuisine généreuse et un accueil chaleureux, tous les jours.', 'Restaurant, rooms and events: generous cooking and a warm welcome, every day.'),
                'footer_links' => [
                    ['label' => $blog['title'], 'url' => '/'.$blog['base']],
                    ['label' => $T('La carte', 'Menu'), 'url' => $menu],
                    ['label' => $T('Nos chambres', 'Our rooms'), 'url' => $rooms],
                    ['label' => $T('Réserver', 'Book now'), 'url' => $booking],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
