<?php

/*
 * Pack « Agence de voyages » (secteur Tourisme et voyages) : 8 pages, en français et en anglais.
 * Agence ou voyagiste : destinations, circuits accompagnés avec prix, voyages sur mesure,
 * groupes et entreprises, conseils pratiques (blog), l'agence et les avis des voyageurs.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'agence). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Tourisme et voyages', 'en' => 'Travel and tourism'],
        'icon' => 'plane',
        'keywords' => ['voyage', 'agence de voyages', 'voyagiste', 'circuit', 'séjour', 'tourisme', 'sur mesure', 'vacances', 'excursion', 'travel', 'travel agency', 'tour operator', 'tours', 'holidays', 'vacation', 'tailor-made', 'trip'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $destinations = '/destinations';
        $tours = $u('/circuits', '/tours');
        $custom = $u('/voyages-sur-mesure', '/tailor-made');
        $groups = $u('/groupes-entreprises', '/groups-and-business');
        $tips = $u('/conseils', '/travel-tips');
        $about = $u('/l-agence', '/about-us');
        $contact = '/contact';
        $cur = $T('€', '$');

        $places = [
            ['voyage-dest-portugal', 'map-pin', $T('Portugal : Lisbonne, Porto et la vallée du Douro', 'Portugal: Lisbon, Porto and the Douro Valley'), $T('Ruelles en pente, azulejos, vignobles en terrasses et océan à perte de vue.', 'Steep lanes, azulejo tiles, terraced vineyards and endless ocean views.'), $tours],
            ['voyage-dest-japon', 'map-pin', $T('Japon : Tokyo, Kyoto et les Alpes japonaises', 'Japan: Tokyo, Kyoto and the Japanese Alps'), $T('Temples, jardins zen, trains à grande vitesse et auberges traditionnelles.', 'Temples, Zen gardens, bullet trains and traditional inns.'), $tours],
            ['voyage-dest-perou', 'map-pin', $T('Pérou : Cuzco, la Vallée sacrée et le Machu Picchu', 'Peru: Cusco, the Sacred Valley and Machu Picchu'), $T('Sites incas, marchés colorés et paysages andins spectaculaires.', 'Inca sites, colorful markets and spectacular Andean scenery.'), $tours],
            ['voyage-dest-islande', 'map-pin', $T('Islande : cascades, glaciers et aurores boréales', 'Iceland: waterfalls, glaciers and northern lights'), $T('Un road trip entre volcans, sources chaudes et plages de sable noir.', 'A road trip among volcanoes, hot springs and black-sand beaches.'), $custom],
            ['voyage-dest-tanzanie', 'map-pin', $T('Tanzanie : safari dans le Serengeti et Zanzibar', 'Tanzania: Serengeti safari and Zanzibar'), $T('Grande migration, cratère du Ngorongoro et plages de l’océan Indien.', 'The great migration, Ngorongoro Crater and Indian Ocean beaches.'), $custom],
            ['voyage-dest-vietnam', 'map-pin', $T('Vietnam : baie d’Halong et delta du Mékong', 'Vietnam: Ha Long Bay and the Mekong Delta'), $T('Jonques dans la baie, rizières en terrasses et cuisine de rue inoubliable.', 'Junks on the bay, terraced rice fields and unforgettable street food.'), $custom],
        ];
        $testimonials = ['eyebrow' => $T('Avis de voyageurs', 'Traveler reviews'), 'title' => $T('Ils sont partis avec nous', 'They traveled with us'), 'rating' => 5, 'items' => [
            [$T('Un circuit parfaitement rythmé, un guide passionnant et des hôtels bien choisis. Nous n’avons eu à penser à rien.', 'A perfectly paced tour, a fascinating guide and well-chosen hotels. We didn’t have to think about a thing.'), '[Prénom Nom]', $T('Circuit au Portugal', 'Portugal tour')],
            [$T('Notre conseillère a construit un voyage de noces qui nous ressemble, avec de vraies surprises à chaque étape.', 'Our adviser built a honeymoon that suited us perfectly, with real surprises at every stage.'), '[Prénom Nom]', $T('Voyage sur mesure au Japon', 'Tailor-made trip to Japan')],
            [$T('Un vol annulé au retour : l’assistance de l’agence nous a trouvé une solution en moins de deux heures.', 'A canceled return flight: the agency’s assistance found us a solution in under two hours.'), '[Prénom Nom]', $T('Voyage en famille', 'Family trip')],
        ]];
        $cta = ['title' => $T('Où rêvez-vous de partir ?', 'Where do you dream of going?'), 'text' => $T('Parlez-nous de vos envies, de vos dates et de votre budget : un conseiller vous envoie une proposition détaillée sous 48 heures.', 'Tell us your wishes, dates and budget: an adviser sends you a detailed proposal within 48 hours.'),
            'buttons' => [[$T('Demander un devis gratuit', 'Request a free quote'), $contact, 'accent'], [$T('Voir les circuits', 'See our tours'), $tours, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Agence de voyages : circuits accompagnés, voyages sur mesure et séjours de groupe dans plus de 40 pays. Devis gratuit sous 48 heures.', 'Travel agency: escorted tours, tailor-made trips and group travel in more than 40 countries. Free quote within 48 hours.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Agence de voyages', 'Travel agency'),
                    'title' => $T('Des voyages pensés pour vous, du rêve au retour', 'Trips designed for you, from the dream to the return'),
                    'text' => $T('Circuits accompagnés, itinéraires sur mesure et voyages de groupe : nos conseillers ont parcouru les destinations qu’ils vous proposent.', 'Escorted tours, tailor-made itineraries and group travel: our advisers have explored the destinations they recommend.'),
                    'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact], [$T('Explorer les destinations', 'Explore destinations'), $destinations]],
                    'photo' => ['voyage-accueil', 'plane', $T('Voyageuse avec sac à dos admirant un panorama de montagnes au lever du soleil', 'Traveler with a backpack admiring a mountain panorama at sunrise')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Trois façons de voyager', 'Three ways to travel'), 'title' => $T('Le voyage qui vous ressemble', 'The trip that suits you'), 'items' => [
                    ['compass', $T('Circuits accompagnés', 'Escorted tours'), $T('Des itinéraires éprouvés en petits groupes, avec un guide francophone ou anglophone.', 'Proven itineraries in small groups, with an English- or French-speaking guide.')],
                    ['map', $T('Voyages sur mesure', 'Tailor-made trips'), $T('Votre itinéraire, à votre rythme, construit avec un conseiller dédié.', 'Your itinerary, at your pace, built with a dedicated adviser.')],
                    ['users', $T('Groupes et entreprises', 'Groups and business'), $T('Associations, écoles, séminaires et voyages de récompense clés en main.', 'Associations, schools, meetings and incentive trips, all arranged.')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Destinations', 'Destinations'), 'title' => $T('Nos destinations du moment', 'Our destinations of the moment'), 'more' => $T('Découvrir', 'Discover'), 'bg' => 'light', 'items' => array_slice($places, 0, 3)]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [40, '+', $T('pays explorés', 'countries explored')],
                    [12000, '+', $T('voyageurs accompagnés', 'travelers looked after')],
                    [98, ' %', $T('de voyageurs satisfaits', 'satisfied travelers')],
                    [24, $T(' h/24', '/7'), $T('assistance pendant le voyage', 'assistance during your trip')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Sur mesure', 'Tailor-made'),
                    'title' => $T('Votre voyage, dessiné avec un conseiller', 'Your trip, designed with an adviser'),
                    'html' => '<p>'.e($T('Lune de miel, anniversaire, année sabbatique ou première grande aventure en famille : nous construisons avec vous un itinéraire unique, ajusté jusqu’à ce qu’il vous corresponde parfaitement.', 'Honeymoon, anniversary, sabbatical or a first big family adventure: we build a unique itinerary with you, refined until it fits you perfectly.')).'</p>',
                    'bullets' => [$T('Un conseiller unique du devis au retour', 'One adviser from quote to return'), $T('Hébergements visités par notre équipe', 'Accommodation visited by our team'), $T('Carnet de voyage personnalisé', 'Personalized travel guide')],
                    'buttons' => [[$T('Créer mon voyage', 'Create my trip'), $custom]],
                    'photo' => ['voyage-conseillere', 'map', $T('Conseillère en voyages montrant un itinéraire sur une carte à un couple', 'Travel adviser showing an itinerary on a map to a couple')],
                    'reverse' => true,
                ]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'destinations', 'slug' => 'destinations', 'title' => 'Destinations',
            'description' => $T('Europe, Asie, Amériques, Afrique : nos destinations coup de cœur, en circuit accompagné ou en voyage sur mesure, selon vos envies.', 'Europe, Asia, the Americas, Africa: our favorite destinations, as an escorted tour or a tailor-made trip, as you wish.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Destinations', 'title' => $T('Le monde, choisi avec soin', 'The world, carefully chosen'), 'intro' => $T('Chaque destination de notre catalogue a été explorée par un membre de l’équipe.', 'Every destination in our catalog has been explored by a member of the team.')]),
                B::cards($theme, ['more' => $T('Voir le voyage', 'See the trip'), 'items' => $places]),
                B::features($theme, ['eyebrow' => $T('Par envie', 'By interest'), 'title' => $T('Quel voyageur êtes-vous ?', 'What kind of traveler are you?'), 'bg' => 'light', 'items' => [
                    ['landmark', $T('Culture et patrimoine', 'Culture and heritage'), $T('Sites classés, musées et rencontres avec des artisans et des historiens.', 'World Heritage sites, museums and meetings with craftspeople and historians.')],
                    ['mountain', $T('Nature et randonnée', 'Nature and hiking'), $T('Parcs nationaux, sentiers de montagne et observation de la faune.', 'National parks, mountain trails and wildlife watching.')],
                    ['waves', $T('Plages et îles', 'Beaches and islands'), $T('Séjours balnéaires, croisières en voilier et plongée.', 'Beach stays, sailing cruises and diving.')],
                    ['chef-hat', $T('Gastronomie', 'Food and wine'), $T('Cours de cuisine, marchés et dégustations chez les producteurs.', 'Cooking classes, markets and tastings with producers.')],
                    ['baby', $T('En famille', 'Family travel'), $T('Rythme adapté, activités pour les enfants et hébergements spacieux.', 'A gentle pace, activities for children and spacious accommodation.')],
                    ['heart', $T('Voyage de noces', 'Honeymoons'), $T('Adresses intimistes et attentions particulières tout au long du séjour.', 'Intimate places and special touches throughout the stay.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Quand partir ?', 'When to go?'),
                    'title' => $T('La bonne destination au bon moment', 'The right destination at the right time'),
                    'html' => '<p>'.e($T('Saison des pluies, fêtes locales, affluence touristique : nos conseillers vous indiquent la meilleure période pour chaque destination et vous proposent des alternatives si vos dates sont imposées.', 'Rainy seasons, local festivals, peak crowds: our advisers tell you the best time for each destination and suggest alternatives if your dates are fixed.')).'</p>',
                    'buttons' => [[$T('Nos conseils pratiques', 'Our travel tips'), $tips, 'outline']],
                    'photo' => ['voyage-saisons', 'sun', $T('Carte du monde, carnet et appareil photo posés sur une table en bois', 'World map, notebook and camera on a wooden table')],
                ]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'tours', 'slug' => $u('circuits', 'tours'), 'title' => $T('Circuits', 'Tours'),
            'description' => $T('Circuits accompagnés en petits groupes : itinéraires jour par jour, dates de départ et prix par personne, vols et hébergements compris.', 'Escorted small-group tours: day-by-day itineraries, departure dates and prices per person, flights and accommodation included.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Circuits accompagnés', 'Escorted tours'), 'title' => $T('Partez l’esprit léger, en petit groupe', 'Travel light-hearted, in a small group'), 'intro' => $T('16 voyageurs au maximum, un guide local et un itinéraire équilibré entre visites et temps libre.', '16 travelers at most, a local guide and an itinerary balanced between visits and free time.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Nos circuits phares', 'Our signature tours'), 'title' => $T('Trois circuits, trois continents', 'Three tours, three continents'), 'intro' => $T('Prix par personne en chambre double, au départ de votre ville, vols compris.', 'Price per person in a double room, from your city, flights included.'), 'items' => [
                    [$T('Portugal essentiel', 'Essential Portugal'), $T('1 490', '1,590'), $cur, $T('/ 9 jours', '/ 9 days'), $T('Lisbonne, Sintra, Coimbra, Porto et le Douro.', 'Lisbon, Sintra, Coimbra, Porto and the Douro.'), [$T('Vols et transferts', 'Flights and transfers'), $T('Hôtels 3 et 4 étoiles', '3- and 4-star hotels'), $T('Guide local francophone ou anglophone', 'Local English- or French-speaking guide'), $T('Demi-pension', 'Half board')], $T('Réserver ma place', 'Book my seat')],
                    [$T('Japon des temples', 'Japan of the temples'), $T('3 290', '3,490'), $cur, $T('/ 12 jours', '/ 12 days'), $T('Tokyo, Hakone, Kyoto, Nara et Osaka.', 'Tokyo, Hakone, Kyoto, Nara and Osaka.'), [$T('Vols et pass ferroviaire', 'Flights and rail pass'), $T('Une nuit en auberge traditionnelle', 'One night in a traditional inn'), $T('Guide accompagnateur', 'Tour leader'), $T('Cérémonie du thé', 'Tea ceremony')], $T('Réserver ma place', 'Book my seat'), true, $T('Coup de cœur', 'Staff pick')],
                    [$T('Pérou inca', 'Inca Peru'), $T('2 690', '2,890'), $cur, $T('/ 14 jours', '/ 14 days'), $T('Lima, Arequipa, lac Titicaca, Cuzco, Machu Picchu.', 'Lima, Arequipa, Lake Titicaca, Cusco, Machu Picchu.'), [$T('Vols intérieurs compris', 'Domestic flights included'), $T('Train pour le Machu Picchu', 'Train to Machu Picchu'), $T('Acclimatation à l’altitude prévue', 'Altitude acclimatization planned'), '-'.$T('Assurance voyage', 'Travel insurance')], $T('Réserver ma place', 'Book my seat')],
                ]]),
                M::timeline($theme, ['eyebrow' => $T('Exemple d’itinéraire', 'Sample itinerary'), 'title' => $T('Portugal essentiel, jour par jour', 'Essential Portugal, day by day'), 'bg' => 'light', 'items' => [
                    [$T('Jours 1 et 2', 'Days 1 and 2'), $T('Lisbonne', 'Lisbon'), $T('Arrivée, quartiers de l’Alfama et de Belém, dîner avec concert de fado.', 'Arrival, the Alfama and Belém districts, dinner with a fado concert.')],
                    [$T('Jour 3', 'Day 3'), $T('Sintra et la côte', 'Sintra and the coast'), $T('Palais romantiques dans la forêt, puis le cap da Roca, point le plus à l’ouest du continent.', 'Romantic palaces in the forest, then Cabo da Roca, the westernmost point of the continent.')],
                    [$T('Jour 4', 'Day 4'), $T('Óbidos et Nazaré', 'Óbidos and Nazaré'), $T('Village fortifié, puis port de pêche réputé pour ses vagues géantes.', 'A walled village, then a fishing port famous for its giant waves.')],
                    [$T('Jour 5', 'Day 5'), 'Coimbra', $T('Visite de l’université historique et de sa bibliothèque baroque.', 'Visit of the historic university and its baroque library.')],
                    [$T('Jours 6 et 7', 'Days 6 and 7'), $T('Porto et le Douro', 'Porto and the Douro'), $T('Croisière sur le fleuve, vignobles en terrasses et dégustation dans une quinta.', 'River cruise, terraced vineyards and a tasting at a quinta.')],
                    [$T('Jours 8 et 9', 'Days 8 and 9'), $T('Porto, retour', 'Porto, return'), $T('Matinée libre dans le quartier de la Ribeira, puis vol retour.', 'Free morning in the Ribeira district, then return flight.')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Calendrier', 'Calendar'), 'title' => $T('Prochains départs', 'Upcoming departures'), 'rows' => [
                    [$T('Circuit', 'Tour'), $T('Durée', 'Length'), $T('Départs', 'Departures'), $T('Prix dès', 'From')],
                    [$T('Portugal essentiel', 'Essential Portugal'), $T('9 jours', '9 days'), $T('Avril, mai, septembre, octobre', 'April, May, September, October'), $T('1 490 €', '$1,590')],
                    [$T('Japon des temples', 'Japan of the temples'), $T('12 jours', '12 days'), $T('Mars, avril, octobre, novembre', 'March, April, October, November'), $T('3 290 €', '$3,490')],
                    [$T('Pérou inca', 'Inca Peru'), $T('14 jours', '14 days'), $T('Mai à octobre', 'May to October'), $T('2 690 €', '$2,890')],
                    [$T('Islande, terre de feu et de glace', 'Iceland, land of fire and ice'), $T('8 jours', '8 days'), $T('Juin à septembre', 'June to September'), $T('2 190 €', '$2,390')],
                ], 'note' => $T('Départ garanti dès 6 voyageurs. Prix indicatifs, confirmés lors de la réservation.', 'Guaranteed departure from 6 travelers. Indicative prices, confirmed at booking.')]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur les circuits', 'Your questions about tours'), 'bg' => 'light', 'items' => [
                    [$T('Que comprend le prix ?', 'What does the price include?'), $T('Les vols, les transferts, l’hébergement, les visites mentionnées et le guide. Les boissons, pourboires et dépenses personnelles restent à votre charge.', 'Flights, transfers, accommodation, the listed visits and the guide. Drinks, tips and personal expenses are not included.')],
                    [$T('Puis-je voyager seul ?', 'Can I travel solo?'), $T('Oui. Un supplément chambre individuelle s’applique, ou nous vous proposons de partager une chambre avec un autre voyageur.', 'Yes. A single supplement applies, or we can arrange for you to share a room with another traveler.')],
                    [$T('Le circuit est-il adapté à tous ?', 'Is the tour suitable for everyone?'), $T('Chaque fiche indique le niveau de marche. Signalez-nous toute contrainte de santé ou de mobilité avant de réserver.', 'Each tour page states the walking level. Let us know about any health or mobility constraints before booking.')],
                    [$T('Que se passe-t-il si le départ n’est pas garanti ?', 'What if the departure is not guaranteed?'), $T('Nous vous prévenons au moins 30 jours avant le départ et vous proposons une autre date ou le remboursement intégral.', 'We tell you at least 30 days before departure and offer another date or a full refund.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'custom', 'slug' => $u('voyages-sur-mesure', 'tailor-made'), 'title' => $T('Voyages sur mesure', 'Tailor-made trips'),
            'description' => $T('Un itinéraire unique construit avec un conseiller dédié : vols, hébergements, guides et activités, à votre rythme et selon votre budget.', 'A unique itinerary built with a dedicated adviser: flights, accommodation, guides and activities, at your pace and within your budget.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Sur mesure', 'Tailor-made'), 'title' => $T('Un voyage qui n’existe que pour vous', 'A trip that exists only for you'), 'intro' => $T('Vous choisissez les envies, les dates et le budget ; nous nous occupons de tout le reste.', 'You choose the wishes, dates and budget; we take care of everything else.')]),
                B::steps($theme, ['eyebrow' => $T('Comment ça marche', 'How it works'), 'title' => $T('De la première idée au départ', 'From the first idea to departure'), 'items' => [
                    [$T('Échange', 'Conversation'), $T('Un rendez-vous, en agence ou en visio, pour cerner vos envies.', 'A meeting, at the agency or by video, to understand your wishes.')],
                    [$T('Proposition', 'Proposal'), $T('Un itinéraire détaillé et chiffré sous 48 heures, sans engagement.', 'A detailed, priced itinerary within 48 hours, with no obligation.')],
                    [$T('Ajustements', 'Fine-tuning'), $T('Nous modifions étapes, hôtels et activités jusqu’à votre accord.', 'We adjust stops, hotels and activities until you are happy.')],
                    [$T('Départ', 'Departure'), $T('Carnet de voyage, documents et assistance joignable jour et nuit.', 'Travel guide, documents and assistance reachable day and night.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Votre conseiller', 'Your adviser'),
                    'title' => $T('Un spécialiste de la destination, rien que pour vous', 'A destination specialist, just for you'),
                    'html' => '<p>'.e($T('Chacun de nos conseillers est spécialiste d’une région du monde qu’il visite régulièrement. Il connaît les meilleures saisons, les adresses discrètes et les guides de confiance.', 'Each of our advisers specializes in a region of the world they visit regularly. They know the best seasons, the hidden gems and the trusted guides.')).'</p><p>'.e($T('Frais de dossier offerts pour tout voyage réservé.', 'No booking fee for any trip you book.')).'</p>',
                    'bullets' => [$T('Itinéraire modifiable jusqu’au départ', 'Itinerary can change until departure'), $T('Partenaires locaux sélectionnés', 'Hand-picked local partners'), $T('Budget respecté, sans frais cachés', 'Budget respected, no hidden costs')],
                    'buttons' => [[$T('Prendre rendez-vous', 'Book a meeting'), $contact]],
                    'photo' => ['voyage-sur-mesure', 'compass', $T('Couple préparant un voyage avec un ordinateur portable, une carte et des guides', 'Couple planning a trip with a laptop, a map and guidebooks')],
                    'bg' => 'light',
                ]),
                B::features($theme, ['eyebrow' => $T('Tout est organisé', 'All taken care of'), 'title' => $T('Ce que nous réservons pour vous', 'What we book for you'), 'items' => [
                    ['plane', $T('Vols', 'Flights'), $T('Les meilleurs horaires et correspondances, avec bagages et sièges choisis.', 'The best schedules and connections, with luggage and seats selected.')],
                    ['hotel', $T('Hébergements', 'Accommodation'), $T('Hôtels de charme, lodges, maisons d’hôtes ou villas, tous visités.', 'Boutique hotels, lodges, guesthouses or villas, all visited.')],
                    ['car', $T('Transports', 'Transport'), $T('Voiture de location, chauffeur privé, trains et ferries.', 'Rental car, private driver, trains and ferries.')],
                    ['user-round', $T('Guides', 'Guides'), $T('Guides locaux passionnés, dans votre langue.', 'Passionate local guides, in your language.')],
                    ['ticket', $T('Activités', 'Activities'), $T('Visites, excursions, spectacles et expériences hors des sentiers battus.', 'Tours, excursions, shows and off-the-beaten-track experiences.')],
                    ['shield-check', $T('Assurance', 'Insurance'), $T('Assurance annulation, bagages et assistance médicale en option.', 'Optional cancellation, luggage and medical assistance cover.')],
                ]]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Un beau voyage commence par une bonne écoute : nous posons beaucoup de questions avant de proposer la moindre étape.', 'A great trip starts with good listening: we ask a lot of questions before suggesting a single stop.'), 'source' => $T('— [Prénom Nom], conseillère voyages', '— [Prénom Nom], travel adviser')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'groups', 'slug' => $u('groupes-entreprises', 'groups-and-business'), 'title' => $T('Groupes et entreprises', 'Groups and business'),
            'description' => $T('Voyages de groupe, séminaires à l’étranger, voyages de récompense et voyages scolaires : une organisation complète et un interlocuteur unique.', 'Group trips, meetings abroad, incentive travel and school trips: full organization and a single point of contact.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Groupes et entreprises', 'Groups and business'), 'title' => $T('Voyager ensemble, sans les soucis d’organisation', 'Travel together, without the planning headaches'), 'intro' => $T('De 10 à 300 participants, nous gérons la logistique pour que vous profitiez du voyage.', 'From 10 to 300 participants, we handle the logistics so you can enjoy the trip.')]),
                B::features($theme, ['cols' => 4, 'items' => [
                    ['briefcase', $T('Séminaires', 'Meetings abroad'), $T('Salles, hébergement et activités de cohésion à l’étranger.', 'Venues, accommodation and team-building abroad.')],
                    ['trophy', $T('Voyages de récompense', 'Incentive trips'), $T('Des expériences marquantes pour remercier vos équipes ou vos clients.', 'Memorable experiences to thank your teams or clients.')],
                    ['school', $T('Voyages scolaires', 'School trips'), $T('Programmes pédagogiques, encadrement et sécurité renforcée.', 'Educational programs, supervision and enhanced safety.')],
                    ['heart-handshake', $T('Associations et clubs', 'Associations and clubs'), $T('Voyages culturels, sportifs ou solidaires pour vos membres.', 'Cultural, sports or volunteer trips for your members.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Entreprises', 'Business'),
                    'title' => $T('Un séminaire qui marque les esprits', 'A meeting people will remember'),
                    'html' => '<p>'.e($T('Nous trouvons la destination adaptée à vos objectifs et à votre budget, réservons vols et hôtels, organisons les réunions et imaginons des activités qui rapprochent les équipes.', 'We find the destination that fits your goals and budget, book flights and hotels, organize the meetings and create activities that bring teams together.')).'</p>',
                    'bullets' => [$T('Repérages et accompagnement sur place', 'Site inspections and on-site support'), $T('Facturation unique et détaillée', 'Single, itemized invoice'), $T('Inscriptions en ligne des participants', 'Online registration for participants')],
                    'buttons' => [[$T('Demander une proposition', 'Request a proposal'), $contact]],
                    'photo' => ['voyage-seminaire', 'briefcase', $T('Équipe de collègues en activité de cohésion sur une plage', 'Team of colleagues doing a team-building activity on a beach')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::stats($theme, ['title' => $T('Nos groupes en chiffres', 'Our groups in numbers'), 'items' => [
                    [350, '+', $T('groupes accompagnés', 'groups organized')],
                    [120, '', $T('entreprises clientes', 'corporate clients')],
                    [300, '', $T('participants pour notre plus grand groupe', 'participants in our largest group')],
                    [1, '', $T('interlocuteur unique', 'single point of contact')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Méthode', 'Method'), 'title' => $T('Un projet de groupe en quatre temps', 'A group project in four stages'), 'items' => [
                    [$T('Cahier des charges', 'Brief'), $T('Objectifs, dates, nombre de participants et budget.', 'Goals, dates, number of participants and budget.')],
                    [$T('Proposition', 'Proposal'), $T('Deux ou trois scénarios chiffrés sous une semaine.', 'Two or three priced options within a week.')],
                    [$T('Organisation', 'Organization'), $T('Réservations, inscriptions, documents de voyage.', 'Bookings, registrations, travel documents.')],
                    [$T('Sur place', 'On site'), $T('Un accompagnateur de l’agence pour les groupes de plus de 20 personnes.', 'An agency escort for groups of more than 20 people.')],
                ]]),
                B::cta($theme, ['tone' => 'dark', 'title' => $T('Parlons de votre projet de groupe', 'Let’s talk about your group project'), 'text' => $T('Un conseiller dédié aux groupes vous répond sous 24 heures ouvrées.', 'A dedicated group adviser replies within one business day.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'tips', 'slug' => $u('conseils', 'travel-tips'), 'title' => $T('Conseils pratiques', 'Travel tips'),
            'description' => $T('Formalités, santé, budget, bagages : nos conseils pour préparer votre voyage, et nos articles pour trouver l’inspiration.', 'Paperwork, health, budget, packing: our tips for preparing your trip, and our articles for inspiration.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Conseils pratiques', 'Travel tips'), 'title' => $T('Bien préparer son voyage', 'Preparing your trip well'), 'intro' => $T('Des conseils généraux : les règles changent selon la destination et votre nationalité, vérifiez-les toujours auprès des sources officielles.', 'General advice: rules vary by destination and nationality, so always check them with official sources.')]),
                B::schedule($theme, ['eyebrow' => $T('Avant le départ', 'Before you go'), 'title' => $T('La liste à vérifier', 'The checklist'), 'bg' => 'white', 'groups' => [
                    [$T('Documents', 'Documents'), [
                        ['id-card', $T('Passeport valide au moins six mois après le retour', 'Passport valid at least six months after return')],
                        ['file-text', $T('Visa ou autorisation de voyage si nécessaire', 'Visa or travel authorization if required')],
                        ['clipboard-check', $T('Copies numériques de vos documents', 'Digital copies of your documents')],
                    ]],
                    [$T('Santé', 'Health'), [
                        ['stethoscope', $T('Consulter un médecin quelques semaines avant le départ', 'See a doctor a few weeks before departure')],
                        ['syringe', $T('Vérifier les vaccins recommandés pour la destination', 'Check recommended vaccinations for the destination')],
                        ['shield-check', $T('Souscrire une assurance assistance et rapatriement', 'Take out assistance and repatriation insurance')],
                    ]],
                    [$T('Argent et bagages', 'Money and luggage'), [
                        ['credit-card', $T('Prévenir votre banque et prévoir deux moyens de paiement', 'Notify your bank and bring two means of payment')],
                        ['backpack', $T('Respecter les limites de poids de la compagnie', 'Respect the airline’s weight limits')],
                        ['smartphone', $T('Télécharger cartes et billets hors ligne', 'Download maps and tickets for offline use')],
                    ]],
                ]]),
                B::posts($theme, ['eyebrow' => $T('Nos articles', 'Our articles'), 'title' => $T('Inspiration et préparation', 'Inspiration and planning'), 'source' => 'current', 'count' => 9, 'bg' => 'light', 'more' => $T('Lire la suite', 'Read more')]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'intro' => $T('Pour toute question sur votre dossier, votre conseiller reste votre premier contact.', 'For any question about your booking, your adviser remains your first contact.'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'outline']], 'items' => [
                    [$T('L’agence s’occupe-t-elle des visas ?', 'Does the agency handle visas?'), $T('Nous vous indiquons les démarches et pouvons transmettre votre dossier à un service spécialisé, moyennant des frais annoncés à l’avance.', 'We explain the steps and can pass your file to a specialist service, for a fee stated in advance.')],
                    [$T('Faut-il une assurance voyage ?', 'Do I need travel insurance?'), $T('Nous la recommandons vivement : frais médicaux à l’étranger, annulation et bagages peuvent coûter très cher sans couverture.', 'We strongly recommend it: medical costs abroad, cancellation and luggage can be very expensive without cover.')],
                    [$T('Que faire en cas de problème sur place ?', 'What if something goes wrong on the trip?'), $T('Appelez notre ligne d’assistance, joignable jour et nuit pendant tout votre voyage. En cas d’urgence médicale, contactez d’abord les secours locaux.', 'Call our assistance line, available day and night throughout your trip. In a medical emergency, contact local emergency services first.')],
                ]]),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('l-agence', 'about-us'), 'title' => $T('L’agence', 'About us'),
            'description' => $T('Notre histoire, nos garanties, l’équipe de conseillers spécialistes et les avis de nos voyageurs. Agence immatriculée et assurée.', 'Our story, our guarantees, our team of specialist advisers and our travelers’ reviews. Registered and insured agency.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’agence', 'About us'), 'title' => $T('Des voyageurs au service des voyageurs', 'Travelers serving travelers'), 'intro' => $T('Depuis quinze ans, nous organisons des voyages que nous aimerions faire nous-mêmes.', 'For fifteen years, we have organized trips we would love to take ourselves.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Une agence indépendante et passionnée', 'An independent, passionate agency'),
                    'html' => '<p>'.e($T('Fondée par deux anciens guides, l’agence réunit aujourd’hui une équipe de conseillers qui partent chaque année en repérage. Nous travaillons avec des partenaires locaux choisis pour leur sérieux et leur respect des populations et de l’environnement.', 'Founded by two former guides, the agency now brings together advisers who scout destinations every year. We work with local partners chosen for their reliability and respect for local people and the environment.')).'</p><p>'.e($T('Agence immatriculée sous le numéro [N° d’immatriculation], garantie financière et assurance responsabilité civile professionnelle souscrites.', 'Agency registered under number [Registration no.], with financial guarantee and professional liability insurance.')).'</p>',
                    'photo' => ['voyage-agence', 'store', $T('Intérieur lumineux d’une agence de voyages avec cartes du monde au mur', 'Bright travel agency interior with world maps on the wall')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos garanties', 'Our guarantees'), 'title' => $T('Voyager en toute confiance', 'Travel with confidence'), 'bg' => 'light', 'items' => [
                    ['shield-check', $T('Fonds protégés', 'Protected funds'), $T('Votre paiement est couvert par une garantie financière.', 'Your payment is covered by a financial guarantee.')],
                    ['headphones', $T('Assistance permanente', 'Round-the-clock assistance'), $T('Une ligne joignable jour et nuit pendant votre voyage.', 'A line you can reach day and night during your trip.')],
                    ['leaf', $T('Voyage responsable', 'Responsible travel'), $T('Petits groupes, partenaires locaux et compensation carbone proposée.', 'Small groups, local partners and carbon offsetting offered.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Vos conseillers spécialistes', 'Your specialist advisers'), 'shape' => 'circle', 'items' => [
                    ['[Prénom Nom]', $T('Fondatrice, spécialiste Asie', 'Founder, Asia specialist'), 'voyage-equipe-1'],
                    ['[Prénom Nom]', $T('Spécialiste Amériques', 'Americas specialist'), 'voyage-equipe-2'],
                    ['[Prénom Nom]', $T('Spécialiste Europe et Méditerranée', 'Europe and Mediterranean specialist'), 'voyage-equipe-3'],
                    ['[Prénom Nom]', $T('Responsable groupes et entreprises', 'Groups and business manager'), 'voyage-equipe-4'],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Demandez un devis gratuit ou prenez rendez-vous en agence ou en visio. Un conseiller vous répond sous 48 heures.', 'Request a free quote or book a meeting at the agency or by video. An adviser replies within 48 hours.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Préparons votre prochain voyage', 'Let’s plan your next trip'), 'intro' => $T('Décrivez-nous votre projet : destination, dates, nombre de voyageurs et budget approximatif.', 'Tell us about your plans: destination, dates, number of travelers and approximate budget.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Rendez-vous en agence, par téléphone ou en visio, du lundi au samedi.', 'Meet us at the agency, by phone or by video, Monday to Saturday.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 9 h – 19 h ; samedi, 10 h – 17 h', 'Monday – Friday, 9 am – 7 pm; Saturday, 10 am – 5 pm')],
                    ],
                    'social' => ['instagram', 'facebook', 'youtube'],
                    'subject' => true,
                    'button' => $T('Recevoir ma proposition', 'Get my proposal'),
                    'bg' => 'white',
                ]),
                B::schedule($theme, ['title' => $T('Pendant votre voyage', 'During your trip'), 'groups' => [
                    [$T('Assistance de l’agence', 'Agency assistance'), [
                        ['phone', $T('Ligne d’urgence joignable 24 h/24 (numéro sur votre carnet)', '24/7 emergency line (number in your travel guide)')],
                        ['message-circle', $T('Messagerie pour les questions non urgentes', 'Messaging for non-urgent questions')],
                    ]],
                    [$T('Vos documents', 'Your documents'), [
                        ['file-text', $T('Carnet de voyage envoyé 10 jours avant le départ', 'Travel guide sent 10 days before departure')],
                        ['smartphone', $T('Billets et vouchers disponibles hors ligne', 'Tickets and vouchers available offline')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Réservation et paiement', 'Booking and payment'), 'bg' => 'white', 'items' => [
                    [$T('Le devis est-il gratuit ?', 'Is the quote free?'), $T('Oui, le devis et les ajustements sont gratuits et sans engagement.', 'Yes, the quote and any adjustments are free and without obligation.')],
                    [$T('Comment se passe le paiement ?', 'How does payment work?'), $T('Un acompte de 30 % confirme la réservation, le solde est dû 30 jours avant le départ. Carte bancaire, virement ou paiement mobile.', 'A 30% deposit confirms the booking; the balance is due 30 days before departure. Card, bank transfer or mobile payment.')],
                    [$T('Puis-je annuler mon voyage ?', 'Can I cancel my trip?'), $T('Oui, selon les conditions d’annulation remises avec le devis. Une assurance annulation peut couvrir les frais en cas d’imprévu.', 'Yes, under the cancellation terms provided with the quote. Cancellation insurance can cover the costs if something unexpected happens.')],
                ]]),
            ]];

        $blog = [
            'base' => $u('conseils', 'travel-tips'),
            'title' => $T('Conseils pratiques', 'Travel tips'),
            'categories' => ['preparer' => $T('Préparer son voyage', 'Trip planning'), 'inspiration' => $T('Inspiration', 'Inspiration'), 'responsable' => $T('Voyager autrement', 'Responsible travel')],
            'posts' => [
                ['key' => 'valise', 'slug' => $u('faire-sa-valise', 'packing-light'), 'category' => 'preparer', 'tags' => [$T('Bagages', 'Packing')],
                    'title' => $T('Faire sa valise : voyager léger sans rien oublier', 'Packing light without forgetting anything'),
                    'excerpt' => $T('Notre méthode pour tout faire tenir dans un bagage cabine, quelle que soit la destination.', 'Our method for fitting everything into a carry-on, whatever the destination.'),
                    'photo' => ['voyage-blog-valise', 'backpack', $T('Valise ouverte avec vêtements roulés, passeport et appareil photo', 'Open suitcase with rolled clothes, a passport and a camera')],
                    'heading' => $T('Notre méthode', 'Our method'),
                    'body' => [
                        $T('Un bagage léger, c’est moins d’attente à l’aéroport, plus de liberté sur place et moins de fatigue entre deux étapes.', 'Light luggage means less waiting at the airport, more freedom on the ground and less fatigue between stops.'),
                        $T('Choisissez des vêtements qui se combinent entre eux, roulez-les plutôt que de les plier et prévoyez une lessive à mi-parcours. Gardez documents, médicaments et chargeurs dans le bagage à main.', 'Choose clothes that mix and match, roll them rather than fold them and plan a laundry stop halfway. Keep documents, medication and chargers in your hand luggage.'),
                        $T('Votre conseiller vous envoie une liste adaptée au climat et aux activités de votre voyage.', 'Your adviser sends you a list tailored to the climate and activities of your trip.'),
                    ]],
                ['key' => 'saison', 'slug' => $u('partir-hors-saison', 'off-season-travel'), 'category' => 'inspiration', 'tags' => [$T('Saisons', 'Seasons')],
                    'title' => $T('Cinq destinations à découvrir hors saison', 'Five destinations to discover off-season'),
                    'excerpt' => $T('Moins de monde, des prix plus doux et une autre lumière : nos idées pour voyager à contre-courant.', 'Fewer crowds, gentler prices and a different light: our ideas for traveling against the flow.'),
                    'photo' => ['voyage-blog-hors-saison', 'sun', $T('Ruelle méditerranéenne déserte et ensoleillée en automne', 'Quiet, sunny Mediterranean alley in autumn')],
                    'quote' => $T('« Les plus beaux souvenirs viennent souvent des saisons où personne ne pense à partir. »', '“The best memories often come from the seasons when nobody thinks of going.”'),
                    'body' => [
                        $T('Partir hors saison, c’est profiter des sites sans la foule et rencontrer les habitants dans leur quotidien.', 'Traveling off-season means enjoying the sights without crowds and meeting locals in their everyday lives.'),
                        $T('Le Portugal en novembre, le Japon en fin d’automne ou l’Islande au printemps offrent des paysages superbes et des tarifs plus accessibles.', 'Portugal in November, Japan in late autumn or Iceland in spring offer superb scenery and more affordable prices.'),
                        $T('Seule précaution : vérifier les horaires d’ouverture des sites et la météo, ce que votre conseiller fait pour vous.', 'The only precaution: check opening times and the weather, which your adviser does for you.'),
                    ]],
                ['key' => 'responsable', 'slug' => $u('voyager-responsable', 'travel-responsibly'), 'category' => 'responsable', 'tags' => [$T('Environnement', 'Environment')],
                    'title' => $T('Voyager de façon plus responsable', 'Traveling more responsibly'),
                    'excerpt' => $T('Séjours plus longs, partenaires locaux, respect des lieux : des gestes simples qui changent beaucoup.', 'Longer stays, local partners, respect for places: simple choices that make a big difference.'),
                    'photo' => ['voyage-blog-responsable', 'leaf', $T('Randonneurs sur un sentier de montagne avec un guide local', 'Hikers on a mountain trail with a local guide')],
                    'heading' => $T('Nos engagements', 'Our commitments'),
                    'body' => [
                        $T('Voyager moins souvent mais plus longtemps, privilégier le train lorsque c’est possible et choisir des hébergements tenus par des habitants : chaque choix compte.', 'Travel less often but for longer, take the train when possible and choose accommodation run by locals: every choice counts.'),
                        $T('Nous travaillons en petits groupes avec des guides et des prestataires locaux, et nous proposons de compenser les émissions de vos vols.', 'We work in small groups with local guides and providers, and we offer to offset your flight emissions.'),
                        $T('Sur place, respectez les coutumes, demandez avant de photographier et consommez local.', 'On the ground, respect local customs, ask before taking photos and buy local.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => 'Destinations', 'url' => $destinations],
                    ['label' => $T('Circuits', 'Tours'), 'url' => $tours],
                    ['label' => $T('Sur mesure', 'Tailor-made'), 'url' => $custom],
                    ['label' => $T('Groupes', 'Groups'), 'url' => $groups],
                    ['label' => $T('Conseils', 'Tips'), 'url' => $tips],
                    ['label' => $T('L’agence', 'About'), 'url' => $about],
                ],
                'cta_label' => $T('Demander un devis', 'Get a quote'),
                'cta_url' => $contact,
                'footer_text' => $T('Agence de voyages indépendante : circuits accompagnés, voyages sur mesure et voyages de groupe, avec une assistance jour et nuit.', 'Independent travel agency: escorted tours, tailor-made trips and group travel, with day-and-night assistance.'),
                'footer_links' => [
                    ['label' => $T('Nos circuits', 'Our tours'), 'url' => $tours],
                    ['label' => $T('Groupes et entreprises', 'Groups and business'), 'url' => $groups],
                    ['label' => $T('Conseils pratiques', 'Travel tips'), 'url' => $tips],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
