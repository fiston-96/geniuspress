<?php

/*
 * Pack « Ferme et producteur » : 8 pages, en français et en anglais.
 * Produits de saison, vente directe et paniers, marchés et points de vente,
 * visites de la ferme, pratiques durables, professionnels et restaurateurs,
 * recettes sur le blog.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de la ferme). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Agriculture et alimentation', 'en' => 'Farming and food'],
        'icon' => 'wheat',
        'keywords' => ['ferme', 'agriculteur', 'producteur', 'exploitation agricole', 'maraîcher', 'maraîchage', 'vente directe', 'paniers', 'circuit court', 'marché', 'bio', 'élevage', 'verger', 'farm', 'farmer', 'grower', 'produce', 'farm shop', 'csa', 'farmers market', 'organic', 'orchard', 'local food'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $products = $u('/nos-produits', '/our-produce');
        $baskets = $u('/paniers', '/farm-boxes');
        $farm = $u('/la-ferme', '/the-farm');
        $visits = $u('/visites', '/farm-visits');
        $pros = $u('/professionnels', '/wholesale');
        $recipes = $u('/recettes', '/recipes');
        $contact = '/contact';
        $cur = $T('€', '$');

        $range = [
            ['sprout', $T('Légumes', 'Vegetables'), $T('Plus de quarante variétés cultivées au fil des saisons, récoltées le matin même.', 'More than forty varieties grown through the seasons, picked the same morning.')],
            ['tree-pine', $T('Fruits du verger', 'Orchard fruit'), $T('Pommes, poires, prunes et petits fruits, cueillis à maturité.', 'Apples, pears, plums and berries, picked when ripe.')],
            ['wheat', $T('Céréales et farines', 'Grains and flour'), $T('Blé ancien et sarrasin moulus à la meule sur la ferme.', 'Heritage wheat and buckwheat, stone-ground on the farm.')],
            ['heart', $T('Œufs et volailles', 'Eggs and poultry'), $T('Poules élevées en plein air, nourries avec les céréales de la ferme.', 'Free-range hens fed with grain grown on the farm.')],
            ['coffee', $T('Jus et confitures', 'Juices and jams'), $T('Jus de pomme pressé à froid et confitures cuites en petites quantités.', 'Cold-pressed apple juice and small-batch jams.')],
            ['flower-2', $T('Miel et fleurs', 'Honey and flowers'), $T('Miel de nos ruches et bouquets de fleurs de saison.', 'Honey from our hives and seasonal flower bouquets.')],
        ];
        $testimonials = ['eyebrow' => $T('Ils en parlent', 'What people say'), 'title' => $T('Le goût de la ferme', 'The taste of the farm'), 'rating' => 5, 'items' => [
            [$T('Le panier du vendredi est devenu un rituel. Des légumes qui ont du goût, et des idées de recettes chaque semaine.', 'The Friday box has become a ritual. Vegetables with real flavor, and recipe ideas every week.'), '[Prénom Nom]', $T('Abonnée aux paniers', 'Farm box member')],
            [$T('Nous travaillons avec la ferme depuis l’ouverture du restaurant. La carte suit leurs récoltes, nos clients adorent.', 'We have worked with the farm since our restaurant opened. Our menu follows their harvests, and guests love it.'), '[Prénom Nom]', $T('Chef de cuisine', 'Head chef')],
            [$T('La visite avec les enfants était passionnante : traite, ramassage des œufs et goûter à la ferme.', 'The visit with the kids was fascinating: milking, egg collecting and a farm snack.'), '[Prénom Nom]', $T('Visite en famille', 'Family visit')],
        ]];
        $cta = ['title' => $T('Goûtez la saison', 'Taste the season'), 'text' => $T('Abonnez-vous aux paniers ou retrouvez-nous au marché : des produits cueillis le jour même, sans intermédiaire.', 'Join our farm box or find us at the market: produce picked the same day, straight from the farmer.'),
            'buttons' => [[$T('Commander un panier', 'Order a box'), $baskets, 'accent'], [$T('Nous contacter', 'Contact us'), $contact, 'outline-light']]];
        $markets = ['eyebrow' => $T('Points de vente', 'Where to buy'), 'title' => $T('Où nous trouver ?', 'Where to find us'), 'groups' => [
            [$T('À la ferme', 'At the farm'), [
                ['store', $T('Boutique : mercredi et vendredi, 15 h – 19 h', 'Farm shop: Wednesday and Friday, 3 pm – 7 pm')],
                ['clock', $T('Samedi, 9 h – 12 h 30', 'Saturday, 9 am – 12:30 pm')],
                ['shopping-bag', $T('Retrait des paniers commandés', 'Pickup for pre-ordered boxes')],
            ]],
            [$T('Sur les marchés', 'At the markets'), [
                ['map-pin', $T('Mardi matin : marché de [Ville]', 'Tuesday morning: [City] market')],
                ['map-pin', $T('Samedi matin : marché couvert de [Ville]', 'Saturday morning: [City] covered market')],
                ['map-pin', $T('Épicerie partenaire : [Nom du magasin], [Ville]', 'Partner grocer: [Store name], [City]')],
            ]],
        ]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Légumes, fruits, œufs et produits de la ferme en vente directe : paniers de saison, boutique, marchés, visites et offre pour les restaurateurs.', 'Vegetables, fruit, eggs and farm products sold direct: seasonal boxes, farm shop, markets, visits and supply for restaurants.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Producteurs en vente directe', 'Family farm, sold direct'),
                    'title' => $T('Du champ à votre table, sans détour', 'From our fields to your table'),
                    'text' => $T('Nous cultivons légumes, fruits et céréales dans le respect du sol et des saisons. Retrouvez-les dans nos paniers, à la boutique de la ferme et sur les marchés.', 'We grow vegetables, fruit and grain with care for the soil and the seasons. Find them in our farm boxes, at the farm shop and at local markets.'),
                    'buttons' => [[$T('Découvrir les paniers', 'See our farm boxes'), $baskets], [$T('Nos produits', 'Our produce'), $products]],
                    'photo' => ['ferme-accueil', 'sprout', $T('Agricultrice tenant une caisse de légumes fraîchement récoltés dans un champ au lever du soleil', 'Farmer holding a crate of freshly harvested vegetables in a field at sunrise')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos produits', 'Our produce'), 'title' => $T('Ce qui pousse à la ferme', 'What grows on the farm'), 'items' => $range]),
                B::split($theme, [
                    'eyebrow' => $T('Paniers de saison', 'Seasonal farm boxes'),
                    'title' => $T('Un panier chaque semaine, au rythme des récoltes', 'A weekly box that follows the harvest'),
                    'html' => '<p>'.e($T('Chaque semaine, nous composons un panier avec ce que la ferme offre de meilleur. Vous le retirez à la ferme ou dans un point relais, et vous recevez une recette pour cuisiner les légumes moins connus.', 'Every week we fill a box with the best the farm has to offer. Pick it up at the farm or at a drop-off point, along with a recipe for the less familiar vegetables.')).'</p>',
                    'bullets' => [$T('Récolte du matin même', 'Picked the same morning'), $T('Sans engagement, suspendable pendant les vacances', 'No commitment, pause it during vacations'), $T('Une recette dans chaque panier', 'A recipe in every box')],
                    'buttons' => [[$T('Choisir mon panier', 'Choose my box'), $baskets]],
                    'photo' => ['ferme-panier', 'shopping-bag', $T('Panier en osier rempli de légumes de saison colorés posé sur une table en bois', 'Wicker basket full of colorful seasonal vegetables on a wooden table')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos pratiques', 'How we farm'), 'title' => $T('Une agriculture qui prend soin du vivant', 'Farming that cares for living things'), 'items' => [
                    ['leaf', $T('Sans pesticides de synthèse', 'No synthetic pesticides'), $T('Rotations, haies et auxiliaires naturels pour protéger les cultures.', 'Crop rotation, hedgerows and beneficial insects protect our crops.')],
                    ['droplet', $T('Eau économisée', 'Water saved'), $T('Goutte-à-goutte, paillage et récupération de l’eau de pluie.', 'Drip irrigation, mulching and rainwater harvesting.')],
                    ['recycle', $T('Sol vivant', 'Living soil'), $T('Compost de la ferme et couverts végétaux toute l’année.', 'Farm-made compost and cover crops all year round.')],
                ]]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [3, $T('e génération', 'rd generation'), $T('d’agriculteurs', 'of farmers')],
                    [$lang === 'fr' ? 42 : 104, '', $T('hectares cultivés', 'acres farmed')],
                    [45, '', $T('variétés de légumes', 'vegetable varieties')],
                    [300, '', $T('paniers chaque semaine', 'boxes every week')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::posts($theme, ['eyebrow' => $T('Recettes', 'Recipes'), 'title' => $T('Que cuisiner cette semaine ?', 'What to cook this week?'), 'count' => 3, 'more' => $T('Voir la recette', 'See the recipe')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'products', 'slug' => $u('nos-produits', 'our-produce'), 'title' => $T('Nos produits', 'Our produce'),
            'description' => $T('Légumes, fruits, céréales, œufs, jus et miel de la ferme, et le calendrier des récoltes pour savoir ce qui est de saison.', 'Farm vegetables, fruit, grain, eggs, juice and honey, plus our harvest calendar so you know what is in season.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nos produits', 'Our produce'), 'title' => $T('Des produits de saison, rien d’autre', 'Seasonal produce, nothing else'), 'intro' => $T('Nous ne vendons que ce que nous produisons, au moment où c’est le meilleur.', 'We only sell what we grow, when it is at its best.')]),
                B::cards($theme, ['items' => [
                    ['ferme-legumes', 'sprout', $T('Légumes du potager fraîchement récoltés', 'Freshly harvested garden vegetables'), $T('Tomates anciennes, courges, salades, carottes, poireaux et légumes oubliés.', 'Heirloom tomatoes, squash, salad greens, carrots, leeks and forgotten varieties.')],
                    ['ferme-fruits', 'tree-pine', $T('Pommes et poires cueillies au verger', 'Apples and pears picked in the orchard'), $T('Quinze variétés de pommes, poires, prunes, fraises et framboises.', 'Fifteen apple varieties, pears, plums, strawberries and raspberries.')],
                    ['ferme-farine', 'wheat', $T('Farine moulue à la meule de pierre', 'Stone-ground flour'), $T('Farines de blé ancien, de seigle et de sarrasin, moulues chaque semaine.', 'Heritage wheat, rye and buckwheat flours, milled every week.')],
                    ['ferme-oeufs', 'heart', $T('Œufs frais de poules élevées en plein air', 'Fresh eggs from free-range hens'), $T('Ramassés chaque jour, vendus dans la semaine.', 'Collected daily, sold within the week.')],
                    ['ferme-jus', 'coffee', $T('Bouteilles de jus de pomme et pots de confiture', 'Bottles of apple juice and jars of jam'), $T('Jus pressés à froid, confitures et compotes préparés avec nos fruits.', 'Cold-pressed juices, jams and applesauce made from our fruit.')],
                    ['ferme-miel', 'flower-2', $T('Pots de miel devant des ruches dans un pré fleuri', 'Jars of honey in front of beehives in a flower meadow'), $T('Miel de printemps et d’été, récolté par nos soins.', 'Spring and summer honey, harvested by us.')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Calendrier', 'Calendar'), 'title' => $T('Ce qui est de saison', 'What’s in season'), 'bg' => 'light', 'rows' => [
                    [$T('Saison', 'Season'), $T('Légumes', 'Vegetables'), $T('Fruits', 'Fruit')],
                    [$T('Printemps', 'Spring'), $T('Asperges, radis, petits pois, salades', 'Asparagus, radishes, peas, salad greens'), $T('Rhubarbe, premières fraises', 'Rhubarb, first strawberries')],
                    [$T('Été', 'Summer'), $T('Tomates, courgettes, haricots, poivrons', 'Tomatoes, zucchini, beans, peppers'), $T('Fraises, framboises, prunes', 'Strawberries, raspberries, plums')],
                    [$T('Automne', 'Fall'), $T('Courges, choux, betteraves, poireaux', 'Squash, cabbage, beets, leeks'), $T('Pommes, poires, raisins', 'Apples, pears, grapes')],
                    [$T('Hiver', 'Winter'), $T('Carottes, panais, céleri, endives', 'Carrots, parsnips, celery root, endives'), $T('Pommes de garde, jus et compotes', 'Storage apples, juice and applesauce')],
                ], 'note' => $T('Les récoltes dépendent de la météo : le contenu exact des paniers est annoncé chaque semaine.', 'Harvests depend on the weather: exact box contents are announced every week.')]),
                B::split($theme, [
                    'eyebrow' => $T('Fraîcheur', 'Freshness'),
                    'title' => $T('Cueillis le matin, vendus l’après-midi', 'Picked in the morning, sold in the afternoon'),
                    'html' => '<p>'.e($T('Pas d’entrepôt, pas de longs transports : nos produits voyagent quelques kilomètres seulement. Ils gardent leur goût, leurs vitamines et se conservent plus longtemps chez vous.', 'No warehouse, no long hauls: our produce travels just a few miles. It keeps its flavor and nutrients and lasts longer in your kitchen.')).'</p>',
                    'photo' => ['ferme-recolte', 'sprout', $T('Mains récoltant des carottes dans la terre d’un potager', 'Hands pulling carrots from the soil of a vegetable garden')],
                ]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'baskets', 'slug' => $u('paniers', 'farm-boxes'), 'title' => $T('Paniers et vente directe', 'Farm boxes and shop'),
            'description' => $T('Paniers de légumes et de fruits chaque semaine, boutique à la ferme, marchés et points relais : toutes les façons d’acheter en direct.', 'Weekly vegetable and fruit boxes, farm shop, markets and drop-off points: every way to buy direct from the farm.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Vente directe', 'Buy direct'), 'title' => $T('Nos paniers de saison', 'Our seasonal farm boxes'), 'intro' => $T('Sans engagement de durée : vous suspendez ou arrêtez quand vous voulez.', 'No long-term commitment: pause or stop whenever you like.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Petit panier', 'Small box'), $T('15', '18'), $cur, $T('/ semaine', '/ week'), $T('Pour 1 à 2 personnes.', 'For 1 to 2 people.'), [$T('4 à 5 légumes de saison', '4 to 5 seasonal vegetables'), $T('Une recette par semaine', 'A weekly recipe'), $T('Retrait à la ferme ou en point relais', 'Pickup at the farm or a drop-off point'), '-'.$T('Fruits du verger', 'Orchard fruit')], $T('Je m’abonne', 'Sign up')],
                    [$T('Panier famille', 'Family box'), $T('25', '30'), $cur, $T('/ semaine', '/ week'), $T('Pour 3 à 4 personnes.', 'For 3 to 4 people.'), [$T('6 à 8 légumes de saison', '6 to 8 seasonal vegetables'), $T('Fruits du verger', 'Orchard fruit'), $T('Une recette par semaine', 'A weekly recipe'), $T('Retrait à la ferme ou en point relais', 'Pickup at the farm or a drop-off point')], $T('Je m’abonne', 'Sign up'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Panier gourmand', 'Pantry box'), $T('38', '45'), $cur, $T('/ semaine', '/ week'), $T('Le panier famille et l’épicerie de la ferme.', 'The family box plus farm pantry goods.'), [$T('Tout le panier famille', 'Everything in the family box'), $T('Six œufs frais', 'Six fresh eggs'), $T('Un produit d’épicerie (farine, jus, miel)', 'One pantry item (flour, juice, honey)'), $T('Livraison possible', 'Delivery available')], $T('Je m’abonne', 'Sign up')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Comment ça marche', 'How it works'), 'title' => $T('Votre panier en trois étapes', 'Your box in three steps'), 'bg' => 'white', 'items' => [
                    [$T('Choisissez', 'Choose'), $T('Le format de panier et votre point de retrait.', 'Your box size and pickup point.')],
                    [$T('Recevez', 'Receive'), $T('Le contenu de la semaine par e-mail chaque lundi.', 'This week’s contents by email every Monday.')],
                    [$T('Retirez', 'Pick up'), $T('Votre panier le vendredi, à la ferme ou en point relais.', 'Your box on Friday, at the farm or a drop-off point.')],
                ]]),
                B::schedule($theme, $markets),
                B::events($theme, ['icon' => 'calendar-days', 'eyebrow' => $T('Rendez-vous', 'Dates'), 'title' => $T('Prochains marchés et fêtes', 'Upcoming markets and events'), 'items' => [
                    [$T('Marché de producteurs à la ferme', 'Farmers market at the farm'), $T('Premier samedi du mois, 9 h – 13 h', 'First Saturday of the month, 9 am – 1 pm'), $T('Cour de la ferme', 'Farmyard')],
                    [$T('Fête des récoltes', 'Harvest festival'), $T('Fin septembre, toute la journée', 'Late September, all day'), $T('À la ferme', 'At the farm')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur les paniers', 'Your questions about farm boxes'), 'bg' => 'light', 'items' => [
                    [$T('Puis-je choisir le contenu de mon panier ?', 'Can I choose what’s in my box?'), $T('Le contenu suit les récoltes, mais vous pouvez signaler un ou deux légumes que vous ne souhaitez pas recevoir.', 'Contents follow the harvest, but you can list one or two vegetables you would rather not receive.')],
                    [$T('Comment se passe le paiement ?', 'How does payment work?'), $T('Par carte bancaire, virement ou paiement mobile, chaque mois ou au trimestre.', 'By card, bank transfer or mobile payment, monthly or quarterly.')],
                    [$T('Et pendant mes vacances ?', 'What about vacations?'), $T('Prévenez-nous une semaine avant : votre panier est suspendu et non facturé.', 'Let us know a week ahead: your box is paused and not billed.')],
                    [$T('Les produits sont-ils certifiés ?', 'Is your produce certified?'), $T('Nos parcelles sont certifiées en agriculture biologique, certificat [N° de certificat].', 'Our fields are certified organic, certificate [Certificate no.].')],
                ]]),
            ]];

        $pages[] = ['key' => 'farm', 'slug' => $u('la-ferme', 'the-farm'), 'title' => $T('La ferme', 'The farm'),
            'description' => $T('Trois générations d’agriculteurs, des pratiques durables pour le sol, l’eau et la biodiversité, et l’équipe qui fait vivre la ferme.', 'Three generations of farmers, sustainable practices for soil, water and biodiversity, and the team who keep the farm going.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('La ferme', 'The farm'), 'title' => $T('Une ferme familiale, tournée vers l’avenir', 'A family farm with an eye on the future'), 'intro' => $T('Nous cultivons ces terres depuis trois générations, et nous voulons les transmettre en meilleur état.', 'We have farmed this land for three generations, and we want to pass it on in better shape.')]),
                M::timeline($theme, ['eyebrow' => $T('Notre histoire', 'Our story'), 'title' => $T('De la ferme laitière au maraîchage', 'From dairy farm to market garden'), 'items' => [
                    ['1968', $T('Les débuts', 'The beginning'), $T('Nos grands-parents s’installent avec un troupeau laitier.', 'Our grandparents settle here with a dairy herd.')],
                    ['1995', $T('Le verger', 'The orchard'), $T('Plantation de 600 arbres fruitiers de variétés anciennes.', '600 heritage fruit trees are planted.')],
                    ['2010', $T('Conversion', 'Going organic'), $T('Passage à l’agriculture biologique et premiers paniers.', 'Switch to organic farming and the first farm boxes.')],
                    ['2023', $T('La boutique', 'The farm shop'), $T('Ouverture de la boutique et du moulin à la ferme.', 'The farm shop and mill open.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Pratiques durables', 'Sustainable practices'),
                    'title' => $T('Nourrir le sol pour nourrir les gens', 'Feeding the soil to feed people'),
                    'html' => '<p>'.e($T('Nous n’utilisons ni pesticides ni engrais de synthèse. Les rotations, le compost, les couverts végétaux et deux kilomètres de haies replantées entretiennent la fertilité du sol et accueillent oiseaux et insectes utiles.', 'We use no synthetic pesticides or fertilizers. Crop rotation, compost, cover crops and over a mile of replanted hedgerows maintain soil fertility and shelter birds and beneficial insects.')).'</p>',
                    'bullets' => [$T('Certification agriculture biologique [N° de certificat]', 'Certified organic [Certificate no.]'), $T('Panneaux solaires sur les hangars', 'Solar panels on the barns'), $T('Emballages consignés ou compostables', 'Returnable or compostable packaging')],
                    'photo' => ['ferme-haies', 'leaf', $T('Champ cultivé bordé de haies et de fleurs sauvages sous un ciel bleu', 'Cultivated field lined with hedgerows and wildflowers under a blue sky')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Celles et ceux qui cultivent', 'The people who grow your food'), 'items' => [
                    ['[Prénom Nom]', $T('Maraîchère, cogérante', 'Market gardener, co-owner'), 'ferme-equipe-1'],
                    ['[Prénom Nom]', $T('Arboriculteur, cogérant', 'Orchardist, co-owner'), 'ferme-equipe-2'],
                    ['[Prénom Nom]', $T('Responsable de la boutique', 'Farm shop manager'), 'ferme-equipe-3'],
                    ['[Prénom Nom]', $T('Apiculteur et meunier', 'Beekeeper and miller'), 'ferme-equipe-4'],
                ]]),
                B::quote($theme, ['text' => $T('Nous ne possédons pas cette terre : nous la gardons pour ceux qui viendront après nous.', 'We don’t own this land: we look after it for those who will come after us.'), 'source' => $T('— [Prénom Nom], troisième génération à la ferme', '— [First Last], third generation on the farm')]),
                B::cta($theme, ['title' => $T('Venez voir par vous-même', 'Come and see for yourself'), 'text' => $T('Visites guidées pour les familles, les écoles et les groupes, de mai à octobre.', 'Guided tours for families, schools and groups, May to October.'),
                    'buttons' => [[$T('Réserver une visite', 'Book a visit'), $visits, 'accent']]]),
            ]];

        $pages[] = ['key' => 'visits', 'slug' => $u('visites', 'farm-visits'), 'title' => $T('Visites de la ferme', 'Farm visits'),
            'description' => $T('Visites guidées, ateliers pour enfants, sorties scolaires et cueillette : découvrez la ferme en famille ou en groupe, de mai à octobre.', 'Guided tours, kids’ workshops, school trips and pick-your-own: discover the farm with family or a group, May to October.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Visites', 'Visits'), 'title' => $T('Passez une journée à la ferme', 'Spend a day on the farm'), 'intro' => $T('Champs, verger, ruches et poulailler : petits et grands découvrent d’où vient ce qu’ils mangent.', 'Fields, orchard, beehives and henhouse: kids and grown-ups discover where their food comes from.')]),
                B::features($theme, ['cols' => 4, 'items' => [
                    ['users', $T('Visite guidée', 'Guided tour'), $T('Une heure et demie avec un agriculteur, dégustation comprise.', 'Ninety minutes with a farmer, tasting included.')],
                    ['baby', $T('Ateliers enfants', 'Kids’ workshops'), $T('Semis, ramassage des œufs et fabrication de jus.', 'Sowing, egg collecting and juice making.')],
                    ['school', $T('Sorties scolaires', 'School trips'), $T('Parcours pédagogiques adaptés à chaque âge.', 'Learning trails adapted to every age group.')],
                    ['shopping-bag', $T('Cueillette', 'Pick your own'), $T('Fraises en juin, pommes en septembre, au poids.', 'Strawberries in June, apples in September, by weight.')],
                ]]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Tarifs', 'Prices'), 'title' => $T('Tarifs des visites', 'Visit prices'), 'items' => [
                    [$T('Adulte', 'Adult'), $T('8', '10'), $cur, $T('/ personne', '/ person'), $T('Visite guidée et dégustation.', 'Guided tour and tasting.'), [$T('Visite d’une heure et demie', '90-minute tour'), $T('Dégustation de produits', 'Product tasting'), $T('Accès à la boutique', 'Farm shop access')], $T('Réserver', 'Book')],
                    [$T('Famille', 'Family'), $T('24', '30'), $cur, $T('2 adultes + 2 enfants', '2 adults + 2 kids'), $T('La visite et un atelier enfants.', 'The tour plus a kids’ workshop.'), [$T('Visite guidée', 'Guided tour'), $T('Atelier enfants', 'Kids’ workshop'), $T('Goûter à la ferme', 'Farm snack'), $T('Enfant supplémentaire : 4 €', 'Extra child: $5')], $T('Réserver', 'Book'), true, $T('Idéal en famille', 'Best for families')],
                    [$T('Groupe scolaire', 'School group'), $T('5', '6'), $cur, $T('/ élève', '/ student'), $T('Accompagnateurs gratuits.', 'Chaperones free.'), [$T('Parcours pédagogique', 'Learning trail'), $T('Livret pour la classe', 'Classroom booklet'), $T('De 15 à 30 élèves', '15 to 30 students')], $T('Demander un devis', 'Request a quote')],
                ]]),
                B::events($theme, ['eyebrow' => $T('Agenda', 'Calendar'), 'title' => $T('Prochaines visites et ateliers', 'Upcoming tours and workshops'), 'bg' => 'light', 'items' => [
                    [$T('Visite guidée du printemps', 'Spring guided tour'), $T('Samedis de mai, 10 h', 'Saturdays in May, 10 am'), $T('Départ devant la boutique', 'Meet at the farm shop')],
                    [$T('Cueillette de fraises', 'Strawberry picking'), $T('Juin, du mercredi au dimanche', 'June, Wednesday to Sunday'), $T('Parcelle des fraisiers', 'Strawberry field')],
                    [$T('Atelier « Du blé au pain »', '“From wheat to bread” workshop'), $T('Mercredis de juillet, 14 h', 'Wednesdays in July, 2 pm'), $T('Moulin de la ferme', 'Farm mill')],
                    [$T('Cueillette de pommes', 'Apple picking'), $T('Septembre, week-ends', 'September, weekends'), $T('Verger', 'Orchard')],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('La ferme au fil des saisons', 'The farm through the seasons'), 'cols' => 3, 'photos' => [
                    ['ferme-visite-enfants', 'baby', $T('Enfants ramassant des œufs dans un poulailler avec un agriculteur', 'Children collecting eggs in a henhouse with a farmer')],
                    ['ferme-verger', 'tree-pine', $T('Rangées de pommiers chargés de fruits rouges dans un verger', 'Rows of apple trees heavy with red fruit in an orchard')],
                    ['ferme-ruches', 'flower-2', $T('Apiculteur en tenue ouvrant une ruche au bord d’un pré fleuri', 'Beekeeper in a suit opening a hive beside a flower meadow')],
                ]]),
            ]];

        $pages[] = ['key' => 'pros', 'slug' => $u('professionnels', 'wholesale'), 'title' => $T('Professionnels', 'Wholesale'),
            'description' => $T('Restaurateurs, épiceries, cantines et traiteurs : nos produits de saison livrés en direct, avec une liste de disponibilités chaque semaine.', 'Restaurants, grocers, school kitchens and caterers: seasonal produce delivered direct, with a weekly availability list.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Professionnels', 'Wholesale'), 'title' => $T('Des produits de la ferme dans votre cuisine', 'Farm produce for your kitchen'), 'intro' => $T('Restaurants, épiceries fines, restauration collective : travaillons ensemble, en direct.', 'Restaurants, specialty grocers, institutional kitchens: let’s work together, direct.')]),
                B::split($theme, [
                    'eyebrow' => $T('Restaurateurs', 'Chefs'),
                    'title' => $T('Une carte qui suit les saisons', 'A menu that follows the seasons'),
                    'html' => '<p>'.e($T('Chaque lundi, vous recevez la liste des produits disponibles et leurs prix. Vous commandez avant mardi midi, nous livrons le jeudi matin, récoltés la veille. Nous pouvons aussi cultiver des variétés pour vous.', 'Every Monday you receive the list of available produce and prices. Order by Tuesday noon and we deliver Thursday morning, picked the day before. We can also grow varieties just for you.')).'</p>',
                    'bullets' => [$T('Liste de disponibilités chaque semaine', 'Weekly availability list'), $T('Livraison en caisses consignées', 'Delivered in returnable crates'), $T('Cultures à la demande pour les chefs', 'Custom growing for chefs')],
                    'buttons' => [[$T('Recevoir la liste', 'Get the list'), $contact]],
                    'photo' => ['ferme-restaurant', 'chef-hat', $T('Cheffe de cuisine examinant des légumes frais livrés en caisse dans sa cuisine', 'Chef inspecting a crate of fresh vegetables delivered to her kitchen')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos clients professionnels', 'Who we supply'), 'title' => $T('Une offre pour chaque besoin', 'Something for every kitchen'), 'bg' => 'light', 'items' => [
                    ['chef-hat', $T('Restaurants', 'Restaurants'), $T('Légumes de chef, fleurs comestibles et variétés rares.', 'Chef’s vegetables, edible flowers and rare varieties.')],
                    ['store', $T('Épiceries et magasins', 'Grocers and shops'), $T('Fruits, légumes et épicerie de la ferme, avec étiquetage.', 'Fruit, vegetables and pantry goods, labeled and ready to sell.')],
                    ['utensils', $T('Restauration collective', 'Institutional kitchens'), $T('Volumes réguliers pour cantines scolaires et entreprises.', 'Regular volumes for school and workplace cafeterias.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Commande', 'Ordering'), 'title' => $T('Une semaine type', 'A typical week'), 'items' => [
                    [$T('Lundi', 'Monday'), $T('Envoi de la liste des disponibilités et des prix.', 'Availability list and prices sent.')],
                    [$T('Mardi', 'Tuesday'), $T('Commande avant midi, par e-mail ou message.', 'Order by noon, by email or text.')],
                    [$T('Mercredi', 'Wednesday'), $T('Récolte et préparation des caisses.', 'Harvest and crate packing.')],
                    [$T('Jeudi', 'Thursday'), $T('Livraison le matin, facture mensuelle.', 'Morning delivery, monthly invoice.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions des professionnels', 'Questions from businesses'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'outline']], 'items' => [
                    [$T('Y a-t-il un minimum de commande ?', 'Is there a minimum order?'), $T('Oui, 60 € par livraison dans notre zone, ou retrait à la ferme sans minimum.', 'Yes, $70 per delivery within our area, or pick up at the farm with no minimum.')],
                    [$T('Pouvez-vous garantir des volumes réguliers ?', 'Can you guarantee regular volumes?'), $T('Pour la restauration collective, nous planifions les cultures avec vous en début de saison.', 'For institutional kitchens, we plan crops with you at the start of the season.')],
                    [$T('Fournissez-vous les documents de traçabilité ?', 'Do you provide traceability documents?'), $T('Oui : certificat biologique, origine des parcelles et numéro de lot sur chaque caisse.', 'Yes: organic certificate, field origin and a lot number on every crate.')],
                ]]),
            ]];

        $pages[] = ['key' => 'recipes', 'slug' => $u('recettes', 'recipes'), 'title' => $T('Recettes', 'Recipes'),
            'description' => $T('Recettes de saison pour cuisiner les légumes et les fruits de la ferme, conseils de conservation et nouvelles des champs.', 'Seasonal recipes for cooking the farm’s vegetables and fruit, storage tips and news from the fields.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Recettes', 'Recipes'), 'title' => $T('Cuisiner la saison', 'Cooking with the seasons'), 'intro' => $T('Des recettes simples pour profiter de chaque panier, et des nouvelles de la ferme.', 'Simple recipes to make the most of every box, and news from the farm.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Voir la recette', 'See the recipe')]),
                B::features($theme, ['eyebrow' => $T('Conservation', 'Storage'), 'title' => $T('Garder vos légumes plus longtemps', 'Keep your vegetables fresh longer'), 'bg' => 'light', 'items' => [
                    ['sprout', $T('Salades et herbes', 'Greens and herbs'), $T('Dans un linge humide, au bas du réfrigérateur.', 'Wrapped in a damp cloth, in the bottom of the fridge.')],
                    ['tree-pine', $T('Pommes et poires', 'Apples and pears'), $T('Au frais et à part : elles font mûrir les autres fruits.', 'Cool and apart: they ripen other produce.')],
                    ['sun', $T('Tomates', 'Tomatoes'), $T('Jamais au réfrigérateur, qui leur fait perdre leur goût.', 'Never in the fridge, which dulls their flavor.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez la ferme : abonnement aux paniers, réservation de visite, commande professionnelle ou simple question. Réponse sous 48 heures.', 'Contact the farm: farm box sign-up, visit booking, wholesale order or a simple question. Reply within 48 hours.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Écrivez-nous', 'Get in touch'), 'intro' => $T('Nous sommes souvent aux champs : laissez-nous un message, nous répondons sous 48 heures.', 'We are often out in the fields: leave us a message and we will reply within 48 hours.')]),
                B::contact($theme, [
                    'title' => $T('La ferme', 'The farm'), 'intro' => $T('Paniers, visites, commandes professionnelles : précisez l’objet de votre message.', 'Farm boxes, visits, wholesale orders: let us know what your message is about.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Boutique : mercredi et vendredi, 15 h – 19 h ; samedi, 9 h – 12 h 30', 'Farm shop: Wednesday and Friday, 3 pm – 7 pm; Saturday, 9 am – 12:30 pm')],
                    ],
                    'social' => ['instagram', 'facebook', 'whatsapp'],
                    'subject' => true,
                    'bg' => 'white',
                ]),
                B::schedule($theme, $markets),
                B::cta($theme, $cta),
            ]];

        $blog = [
            'base' => $u('recettes', 'recipes'),
            'title' => $T('Recettes', 'Recipes'),
            'categories' => ['recettes' => $T('Recettes', 'Recipes'), 'saison' => $T('De saison', 'In season'), 'ferme' => $T('Nouvelles de la ferme', 'Farm news')],
            'posts' => [
                ['key' => 'courge', 'slug' => $u('veloute-de-courge', 'roasted-squash-soup'), 'category' => 'recettes', 'tags' => [$T('Automne', 'Fall')],
                    'title' => $T('Velouté de courge rôtie et noisettes', 'Roasted squash soup with hazelnuts'),
                    'excerpt' => $T('Une soupe douce et réconfortante, prête en quarante minutes avec la courge du panier.', 'A smooth, comforting soup, ready in forty minutes with the squash from your box.'),
                    'photo' => ['ferme-blog-courge', 'utensils', $T('Bol de velouté orange garni de noisettes sur une table en bois', 'Bowl of orange squash soup topped with hazelnuts on a wooden table')],
                    'heading' => $T('La recette', 'The recipe'),
                    'body' => [
                        $T('Coupez une courge en morceaux sans l’éplucher, arrosez d’huile d’olive et faites rôtir 30 minutes au four avec un oignon et deux gousses d’ail.', 'Cut a squash into chunks without peeling, drizzle with olive oil and roast for 30 minutes with an onion and two garlic cloves.'),
                        $T('Mixez avec 70 cl de bouillon chaud, salez, poivrez et ajoutez un trait de crème si vous le souhaitez.', 'Blend with 3 cups of hot stock, season with salt and pepper, and add a splash of cream if you like.'),
                        $T('Servez avec des noisettes torréfiées concassées et un filet d’huile.', 'Serve with crushed toasted hazelnuts and a drizzle of oil.'),
                    ]],
                ['key' => 'tomates', 'slug' => $u('tomates-anciennes', 'heirloom-tomatoes'), 'category' => 'saison', 'tags' => [$T('Été', 'Summer')],
                    'title' => $T('Pourquoi nos tomates anciennes ont plus de goût', 'Why our heirloom tomatoes taste better'),
                    'excerpt' => $T('Variétés, maturité, culture en pleine terre : les secrets d’une bonne tomate.', 'Varieties, ripeness, growing in the ground: the secrets of a good tomato.'),
                    'photo' => ['ferme-blog-tomates', 'sun', $T('Tomates anciennes de couleurs variées dans une cagette au marché', 'Heirloom tomatoes of many colors in a crate at the market')],
                    'quote' => $T('« Une tomate cueillie mûre n’a rien à voir avec une tomate qui a mûri dans un camion. »', '“A tomato picked ripe is nothing like one that ripened in a truck.”'),
                    'body' => [
                        $T('Nous cultivons une vingtaine de variétés anciennes, choisies pour leur goût plutôt que pour leur résistance au transport.', 'We grow about twenty heirloom varieties, chosen for flavor rather than for surviving long-distance shipping.'),
                        $T('Cultivées en pleine terre et cueillies à pleine maturité, elles développent tous leurs arômes.', 'Grown in the ground and picked fully ripe, they develop their full flavor.'),
                        $T('Conservez-les à température ambiante et dégustez-les simplement, avec un peu de sel et d’huile d’olive.', 'Keep them at room temperature and enjoy them simply, with a little salt and olive oil.'),
                    ]],
                ['key' => 'haies', 'slug' => $u('plantation-de-haies', 'hedgerow-planting'), 'category' => 'ferme', 'tags' => [$T('Biodiversité', 'Biodiversity')],
                    'title' => $T('Deux kilomètres de haies plantées cet hiver', 'Over a mile of hedgerows planted this winter'),
                    'excerpt' => $T('Avec des bénévoles et une classe de l’école voisine, nous avons planté 1 500 arbustes.', 'With volunteers and a class from the local school, we planted 1,500 shrubs.'),
                    'photo' => ['ferme-blog-haies', 'leaf', $T('Bénévoles plantant de jeunes arbustes le long d’un champ en hiver', 'Volunteers planting young shrubs along a field edge in winter')],
                    'heading' => $T('À quoi servent les haies ?', 'What do hedgerows do?'),
                    'body' => [
                        $T('Les haies protègent les cultures du vent, limitent l’érosion et retiennent l’eau dans le sol.', 'Hedgerows shelter crops from wind, limit erosion and keep water in the soil.'),
                        $T('Elles abritent les oiseaux et les insectes qui nous aident à protéger nos légumes sans traitement.', 'They are home to the birds and insects that help us protect our vegetables without spraying.'),
                        $T('Merci aux soixante bénévoles venus planter avec nous : rendez-vous l’hiver prochain !', 'Thank you to the sixty volunteers who came to plant with us: see you next winter!'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Nos produits', 'Produce'), 'url' => $products],
                    ['label' => $T('Paniers', 'Farm boxes'), 'url' => $baskets],
                    ['label' => $T('La ferme', 'The farm'), 'url' => $farm],
                    ['label' => $T('Visites', 'Visits'), 'url' => $visits],
                    ['label' => $T('Professionnels', 'Wholesale'), 'url' => $pros],
                    ['label' => $T('Recettes', 'Recipes'), 'url' => $recipes],
                ],
                'cta_label' => $T('Commander un panier', 'Order a box'),
                'cta_url' => $baskets,
                'footer_text' => $T('Ferme familiale en vente directe : légumes, fruits, céréales et produits de la ferme, cultivés dans le respect du sol et des saisons.', 'Family farm selling direct: vegetables, fruit, grain and farm products, grown with care for the soil and the seasons.'),
                'footer_links' => [
                    ['label' => $T('Paniers de saison', 'Seasonal boxes'), 'url' => $baskets],
                    ['label' => $T('Visites de la ferme', 'Farm visits'), 'url' => $visits],
                    ['label' => $T('Professionnels', 'Wholesale'), 'url' => $pros],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
