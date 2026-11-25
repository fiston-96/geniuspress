<?php

/*
 * Pack « Commerce et boutiques » : 8 pages, en français et en anglais.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du commerce). Les liens internes
 * sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Commerce et boutiques', 'en' => 'Retail and shops'],
        'icon' => 'store',
        'keywords' => ['commerce', 'boutique', 'magasin', 'supermarché', 'quincaillerie', 'grossiste', 'vente', 'épicerie', 'shop', 'store', 'retail', 'supermarket', 'wholesale', 'hardware'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $products = $u('/produits', '/products');
        $services = $u('/services', '/services');
        $offers = $u('/offres', '/offers');
        $about = $u('/a-propos', '/about');
        $reviews = $u('/avis-clients', '/reviews');
        $faq = '/faq';
        $contact = '/contact';

        $categories = [
            ['wheat', $T('Alimentation', 'Groceries'), $T('Produits de base, conserves, boissons et produits frais au quotidien.', 'Staples, canned goods, drinks and fresh products every day.')],
            ['sparkles', $T('Maison et entretien', 'Home and cleaning'), $T('Tout pour garder la maison propre : lessives, savons, ustensiles.', 'Everything to keep the house clean: detergents, soaps, utensils.')],
            ['smartphone', $T('Téléphones et accessoires', 'Phones and accessories'), $T('Smartphones, chargeurs, écouteurs et crédit de communication.', 'Smartphones, chargers, earphones and airtime.')],
            ['shirt', $T('Vêtements et chaussures', 'Clothing and shoes'), $T('Mode pour toute la famille, uniformes scolaires et tenues de travail.', 'Fashion for the whole family, school uniforms and workwear.')],
            ['hammer', $T('Quincaillerie', 'Hardware'), $T('Outils, peinture, matériel électrique et plomberie.', 'Tools, paint, electrical and plumbing supplies.')],
            ['baby', $T('Bébé et enfants', 'Baby and kids'), $T('Couches, lait, jouets et fournitures scolaires.', 'Diapers, milk, toys and school supplies.')],
        ];
        $stats = ['title' => $T('La confiance de nos clients', 'Our customers’ trust'), 'items' => [
            [8000, '+', $T('clients satisfaits', 'happy customers')],
            [2500, '+', $T('produits en rayon', 'products in store')],
            [12, '', $T('années d’expérience', 'years of experience')],
            [5, '/5', $T('note moyenne', 'average rating')],
        ]];
        $testimonials = ['eyebrow' => $T('Avis clients', 'Customer reviews'), 'title' => $T('Ils nous font confiance', 'They trust us'), 'rating' => 5, 'items' => [
            [$T('Des prix honnêtes et un personnel toujours souriant. Ma commande a été livrée en moins de deux heures.', 'Fair prices and always-smiling staff. My order was delivered in under two hours.'), '[Prénom Nom]', $T('Cliente fidèle', 'Loyal customer')],
            [$T('Je m’approvisionne ici pour mon restaurant. Les tarifs de gros sont très intéressants et le stock est fiable.', 'I stock up here for my restaurant. The wholesale rates are great and stock is reliable.'), '[Prénom Nom]', $T('Restaurateur', 'Restaurant owner')],
            [$T('Commander par WhatsApp est tellement simple. On m’envoie la photo, je valide, et c’est livré.', 'Ordering by WhatsApp is so easy. They send a photo, I confirm, and it’s delivered.'), '[Prénom Nom]', $T('Cliente à domicile', 'Home delivery customer')],
        ]];
        $cta = ['title' => $T('Prêt à faire vos achats ?', 'Ready to shop?'), 'text' => $T('Passez au magasin ou commandez en quelques messages : nous vous livrons dans toute la ville.', 'Visit the store or order in a few messages: we deliver across the city.'),
            'buttons' => [[$T('Commander sur WhatsApp', 'Order on WhatsApp'), $contact, 'accent'], [$T('Voir les produits', 'View products'), $products, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Votre magasin de proximité : produits de qualité, prix justes et livraison rapide dans toute la ville. Commandez en magasin ou par WhatsApp.', 'Your neighborhood store: quality products, fair prices and fast delivery across the city. Order in store or on WhatsApp.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Tout pour la maison et la famille', 'Everything for home and family'),
                    'title' => $T('Des produits de qualité à prix justes', 'Quality products at fair prices'),
                    'text' => $T('Alimentation, maison, téléphones, quincaillerie : retrouvez tout au même endroit, en magasin ou livré chez vous dans toute la ville.', 'Groceries, home, phones, hardware: find it all in one place, in store or delivered to your door across the city.'),
                    'buttons' => [[$T('Voir nos produits', 'Shop our products'), $products], [$T('Commander sur WhatsApp', 'Order on WhatsApp'), $contact]],
                    'photo' => ['commerce-accueil', 'store', $T('Intérieur lumineux d’un magasin avec des rayons bien rangés', 'Bright store interior with neatly stocked shelves')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos rayons', 'Our departments'), 'title' => $T('Tout ce dont vous avez besoin', 'Everything you need'), 'items' => $categories]),
                B::pillars($theme, ['eyebrow' => $T('Pourquoi nous choisir', 'Why shop with us'), 'title' => $T('Trois engagements pour vous', 'Three promises to you'), 'bg' => 'light', 'items' => [
                    ['tag', $T('Prix justes', 'Fair prices'), $T('Des tarifs clairs affichés en rayon, et des promotions chaque semaine.', 'Clear prices shown in store, and promotions every week.')],
                    ['shield-check', $T('Qualité garantie', 'Guaranteed quality'), $T('Des produits sélectionnés, contrôlés et échangés s’ils ne vous conviennent pas.', 'Selected, checked products that can be exchanged if they don’t suit you.')],
                    ['truck', $T('Livraison rapide', 'Fast delivery'), $T('Livraison à domicile dans toute la ville, le jour même pour les commandes du matin.', 'Home delivery across the city, same day for morning orders.')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Sélection du moment', 'Featured picks'), 'title' => $T('Nos produits à la une', 'Featured products'), 'more' => $T('Voir le détail', 'See details'), 'items' => [
                    ['commerce-produit-panier', 'shopping-bag', $T('Panier de la semaine', 'Weekly basket'), $T('Riz, huile, sucre, farine et savon : l’essentiel dès 25 $.', 'Rice, oil, sugar, flour and soap: the essentials from $25.'), $offers],
                    ['commerce-produit-telephone', 'smartphone', $T('Smartphone d’entrée de gamme', 'Entry-level smartphone'), $T('Écran large, grande batterie et garantie 6 mois, dès 90 $.', 'Large screen, long battery life and 6-month warranty, from $90.'), $products],
                    ['commerce-produit-outillage', 'hammer', $T('Kit d’outillage maison', 'Home toolkit'), $T('Marteau, tournevis, pinces et mètre dans une mallette, 35 $.', 'Hammer, screwdrivers, pliers and tape measure in a case, $35.'), $products],
                ]]),
                B::stats($theme, $stats),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'products', 'slug' => $u('produits', 'products'), 'title' => $T('Nos produits', 'Our products'),
            'description' => $T('Alimentation, maison, téléphones, vêtements, quincaillerie, bébé : découvrez tous nos rayons et nos nouveautés.', 'Groceries, home, phones, clothing, hardware, baby: discover all our departments and new arrivals.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Catalogue', 'Catalog'), 'title' => $T('Nos produits et nos rayons', 'Our products and departments'), 'intro' => $T('Un large choix pour toute la famille, renouvelé chaque semaine.', 'A wide choice for the whole family, refreshed every week.')]),
                B::cards($theme, ['items' => [
                    ['commerce-rayon-alimentation', 'wheat', $T('Alimentation', 'Groceries'), $T('Produits de base, conserves, boissons, produits frais.', 'Staples, canned goods, drinks, fresh products.')],
                    ['commerce-rayon-maison', 'sparkles', $T('Maison et entretien', 'Home and cleaning'), $T('Lessives, savons, ustensiles et petit électroménager.', 'Detergents, soaps, utensils and small appliances.')],
                    ['commerce-rayon-telephones', 'smartphone', $T('Téléphones et accessoires', 'Phones and accessories'), $T('Smartphones, chargeurs, coques et crédit.', 'Smartphones, chargers, cases and airtime.')],
                    ['commerce-rayon-mode', 'shirt', $T('Vêtements et chaussures', 'Clothing and shoes'), $T('Mode adulte et enfant, uniformes et tenues de travail.', 'Adult and kids’ fashion, uniforms and workwear.')],
                    ['commerce-rayon-quincaillerie', 'hammer', $T('Quincaillerie', 'Hardware'), $T('Outils, peinture, électricité, plomberie.', 'Tools, paint, electrical, plumbing.')],
                    ['commerce-rayon-bebe', 'baby', $T('Bébé et enfants', 'Baby and kids'), $T('Couches, lait, jouets, fournitures scolaires.', 'Diapers, milk, toys, school supplies.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Nouveautés', 'New arrivals'),
                    'title' => $T('De nouveaux produits chaque semaine', 'New products every week'),
                    'html' => '<p>'.e($T('Nous renouvelons régulièrement nos rayons avec des produits demandés par nos clients. Dites-nous ce que vous cherchez : nous le commandons pour vous.', 'We regularly refresh our shelves with products our customers ask for. Tell us what you’re looking for and we’ll order it for you.')).'</p>',
                    'bullets' => [$T('Arrivages chaque semaine', 'Weekly deliveries'), $T('Produits sur demande', 'Special orders on request'), $T('Étiquetage clair des prix', 'Clear price labels')],
                    'buttons' => [[$T('Demander un produit', 'Request a product'), $contact]],
                    'photo' => ['commerce-nouveautes', 'package', $T('Cartons de nouveaux produits en cours de rangement', 'Boxes of new products being shelved')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::gallery($theme, ['eyebrow' => $T('Au magasin', 'In store'), 'title' => $T('Un aperçu de nos rayons', 'A look at our shelves'), 'photos' => [
                    ['commerce-rayon-alimentation', 'wheat', $T('Rayon alimentation', 'Grocery aisle')],
                    ['commerce-rayon-maison', 'sparkles', $T('Rayon maison et entretien', 'Home and cleaning aisle')],
                    ['commerce-rayon-telephones', 'smartphone', $T('Coin téléphones et accessoires', 'Phones and accessories corner')],
                    ['commerce-rayon-mode', 'shirt', $T('Rayon vêtements', 'Clothing section')],
                    ['commerce-rayon-quincaillerie', 'hammer', $T('Rayon quincaillerie', 'Hardware aisle')],
                    ['commerce-caisse', 'credit-card', $T('Caisse et paiement', 'Checkout and payment')],
                ]]),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('services', 'services'), 'title' => $T('Nos services', 'Our services'),
            'description' => $T('Livraison à domicile, commande par WhatsApp, vente en gros, service après-vente et cartes cadeaux : nos services pour faciliter vos achats.', 'Home delivery, WhatsApp ordering, wholesale, after-sales service and gift cards: our services to make shopping easy.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Services', 'title' => $T('Plus que des produits, des services', 'More than products, real services'), 'intro' => $T('Nous facilitons vos achats, que vous soyez particulier ou professionnel.', 'We make shopping easier, whether you are an individual or a business.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['truck', $T('Livraison à domicile', 'Home delivery'), $T('Livraison dans toute la ville, gratuite à partir d’un montant minimum.', 'Delivery across the city, free above a minimum amount.')],
                    ['message-circle', $T('Commande par WhatsApp', 'WhatsApp ordering'), $T('Envoyez votre liste par message ou par téléphone, nous préparons tout.', 'Send your list by message or phone and we prepare everything.')],
                    ['layers', $T('Vente en gros', 'Wholesale'), $T('Tarifs dégressifs pour restaurants, écoles, boutiques et entreprises.', 'Volume rates for restaurants, schools, shops and businesses.')],
                    ['refresh-cw', $T('Service après-vente', 'After-sales service'), $T('Échange ou remboursement si un produit ne vous convient pas.', 'Exchange or refund if a product doesn’t suit you.')],
                    ['gift', $T('Cartes cadeaux', 'Gift cards'), $T('Offrez un bon d’achat pour les fêtes, mariages et anniversaires.', 'Give a voucher for holidays, weddings and birthdays.')],
                    ['wallet', $T('Paiement facile', 'Easy payment'), $T('Mobile Money, espèces ou carte bancaire, selon votre choix.', 'Mobile Money, cash or card, as you prefer.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Comment commander', 'How ordering works'), 'title' => $T('Votre commande en 4 étapes', 'Your order in 4 steps'), 'bg' => 'light', 'items' => [
                    [$T('Choisissez', 'Choose'), $T('Parcourez nos produits ou envoyez votre liste.', 'Browse our products or send your list.')],
                    [$T('Confirmez', 'Confirm'), $T('Nous vous confirmons les prix et la disponibilité.', 'We confirm prices and availability.')],
                    [$T('Payez', 'Pay'), $T('Mobile Money, espèces ou carte à la livraison.', 'Mobile Money, cash or card on delivery.')],
                    [$T('Recevez', 'Receive'), $T('Votre commande arrive chez vous, bien emballée.', 'Your order arrives at your door, well packed.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'offers', 'slug' => $u('offres', 'offers'), 'title' => $T('Offres et promotions', 'Offers and promotions'),
            'description' => $T('Paniers, packs et promotions du moment, programme de fidélité et réponses à vos questions sur nos offres.', 'Baskets, bundles and current promotions, loyalty program and answers to your questions about our offers.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Bons plans', 'Deals'), 'title' => $T('Nos offres du moment', 'Our current offers'), 'intro' => $T('Des packs pensés pour économiser sur vos achats courants.', 'Bundles designed to save you money on everyday purchases.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Pack Famille', 'Family Pack'), '25', '$', $T('le pack', 'per pack'), $T('L’essentiel pour une semaine.', 'The essentials for a week.'), [$T('Riz, huile, sucre et farine', 'Rice, oil, sugar and flour'), $T('Savon et lessive', 'Soap and detergent'), $T('Livraison offerte', 'Free delivery')], $T('Commander', 'Order')],
                    [$T('Pack Maison', 'Home Pack'), '60', '$', $T('le pack', 'per pack'), $T('Pour bien équiper la maison.', 'To equip the home well.'), [$T('Produits d’entretien complets', 'Full cleaning supplies'), $T('Ustensiles de cuisine', 'Kitchen utensils'), $T('Livraison offerte', 'Free delivery'), $T('Échange sous 7 jours', '7-day exchange')], $T('Commander', 'Order'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Pack Pro', 'Pro Pack'), '150', '$', $T('le pack', 'per pack'), $T('Pour restaurants et petits commerces.', 'For restaurants and small shops.'), [$T('Quantités en gros', 'Bulk quantities'), $T('Prix dégressifs', 'Volume pricing'), $T('Livraison gratuite', 'Free delivery'), $T('Facture détaillée', 'Detailed invoice')], $T('Demander un devis', 'Request a quote')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Fidélité', 'Loyalty'),
                    'title' => $T('Notre carte de fidélité', 'Our loyalty card'),
                    'html' => '<p>'.e($T('Chaque achat vous rapporte des points, à échanger contre des réductions et des cadeaux. L’inscription est gratuite et immédiate en magasin.', 'Every purchase earns you points to exchange for discounts and gifts. Signing up is free and instant in store.')).'</p>',
                    'bullets' => [$T('1 point pour chaque dollar dépensé', '1 point for every dollar spent'), $T('Réductions exclusives pour les membres', 'Exclusive member discounts'), $T('Offres d’anniversaire', 'Birthday offers')],
                    'buttons' => [[$T('Je m’inscris', 'Sign me up'), $contact]],
                    'photo' => ['commerce-fidelite', 'ticket', $T('Carte de fidélité remise à une cliente', 'Loyalty card handed to a customer')],
                    'bg' => 'white',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions sur nos offres', 'Questions about our offers'), 'bg' => 'light', 'items' => [
                    [$T('Jusqu’à quand les promotions sont-elles valables ?', 'How long do promotions last?'), $T('Chaque offre indique sa date de fin. Les packs restent disponibles tant que le stock le permet.', 'Each offer shows its end date. Bundles remain available while stock lasts.')],
                    [$T('Puis-je personnaliser un pack ?', 'Can I customize a bundle?'), $T('Oui, contactez-nous : nous adaptons le contenu à vos besoins et à votre budget.', 'Yes, contact us: we adapt the contents to your needs and budget.')],
                    [$T('Les offres sont-elles cumulables avec la carte de fidélité ?', 'Can offers be combined with the loyalty card?'), $T('Vous cumulez toujours vos points, même sur les produits en promotion.', 'You always earn points, even on promotional items.')],
                ]]),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('a-propos', 'about'), 'title' => $T('À propos', 'About us'),
            'description' => $T('Notre histoire, nos valeurs et l’équipe qui vous accueille chaque jour pour des achats simples et de confiance.', 'Our story, values and the team that welcomes you every day for simple, trustworthy shopping.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('À propos', 'About us'), 'title' => $T('Un commerce de proximité, à votre service', 'A neighborhood store, at your service'), 'intro' => $T('Depuis plus de dix ans, nous servons les familles et les professionnels de la ville.', 'For over ten years we have served the city’s families and businesses.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Né d’une petite boutique de quartier', 'Born from a small neighborhood shop'),
                    'html' => '<p>'.e($T('Tout a commencé avec un petit étal et l’envie de bien servir ses voisins. Au fil des années, notre magasin s’est agrandi grâce à la fidélité de nos clients, sans jamais perdre l’esprit d’accueil et de confiance du début.', 'It all started with a small stall and the wish to serve neighbors well. Over the years our store has grown thanks to our customers’ loyalty, without ever losing the welcoming, trusting spirit of the beginning.')).'</p>',
                    'photo' => ['commerce-histoire', 'store', $T('Façade du magasin avec ses clients devant l’entrée', 'Store front with customers at the entrance')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce qui guide notre commerce', 'What guides our business'), 'bg' => 'light', 'items' => [
                    ['handshake', $T('Confiance', 'Trust'), $T('Des prix honnêtes, des produits conformes, une parole tenue.', 'Honest prices, genuine products, promises kept.')],
                    ['heart-handshake', $T('Accueil', 'Welcome'), $T('Chaque client est reçu avec le sourire et écouté.', 'Every customer is greeted with a smile and listened to.')],
                    ['award', $T('Qualité', 'Quality'), $T('Nous choisissons nos fournisseurs avec soin.', 'We choose our suppliers carefully.')],
                ]]),
                B::quote($theme, ['text' => $T('Un client satisfait revient, et amène ses voisins.', 'A satisfied customer comes back and brings the neighbors.'), 'source' => $T('— L’esprit de la maison', '— Our guiding spirit')]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Les visages derrière le comptoir', 'The faces behind the counter'), 'bg' => 'light', 'items' => [
                    ['[Prénom Nom]', $T('Gérante', 'Manager'), 'commerce-equipe-1'],
                    ['[Prénom Nom]', $T('Responsable des achats', 'Purchasing manager'), 'commerce-equipe-2'],
                    ['[Prénom Nom]', $T('Chef de rayon', 'Department head'), 'commerce-equipe-3'],
                    ['[Prénom Nom]', $T('Responsable livraisons', 'Delivery coordinator'), 'commerce-equipe-4'],
                ]]),
            ]];

        $pages[] = ['key' => 'reviews', 'slug' => $u('avis-clients', 'reviews'), 'title' => $T('Avis clients', 'Customer reviews'),
            'description' => $T('Ce que nos clients disent de nous : témoignages, note moyenne et chiffres de satisfaction. Laissez aussi votre avis.', 'What our customers say about us: testimonials, average rating and satisfaction figures. Leave your own review too.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Avis clients', 'Reviews'), 'title' => $T('Vos mots font notre fierté', 'Your words make us proud'), 'intro' => $T('Des milliers de clients nous font confiance chaque année.', 'Thousands of customers trust us every year.')]),
                B::testimonials($theme, ['rating' => 5, 'bg' => 'white', 'items' => [
                    [$T('Un magasin propre, bien rangé, où l’on trouve presque tout.', 'A clean, well-organized store where you can find almost everything.'), '[Prénom Nom]', $T('Cliente', 'Customer')],
                    [$T('Le service après-vente est sérieux : mon article a été échangé sans discussion.', 'The after-sales service is reliable: my item was exchanged with no fuss.'), '[Prénom Nom]', $T('Client', 'Customer')],
                    [$T('Livraison rapide et colis bien emballé. Je recommande.', 'Fast delivery and well-packed parcel. I recommend it.'), '[Prénom Nom]', $T('Cliente', 'Customer')],
                    [$T('Les prix de gros m’aident à faire vivre ma petite boutique.', 'The wholesale prices help me keep my small shop going.'), '[Prénom Nom]', $T('Commerçant', 'Shop owner')],
                    [$T('Le personnel prend le temps de conseiller, sans jamais forcer la vente.', 'The staff take time to advise, never pushing a sale.'), '[Prénom Nom]', $T('Client', 'Customer')],
                    [$T('Je paie par Mobile Money et je reçois mon reçu tout de suite.', 'I pay by Mobile Money and get my receipt right away.'), '[Prénom Nom]', $T('Cliente', 'Customer')],
                ]]),
                B::stats($theme, $stats),
                B::cta($theme, ['title' => $T('Donnez-nous votre avis', 'Share your feedback'), 'text' => $T('Votre expérience nous aide à nous améliorer. Dites-nous ce que vous avez aimé, ou ce que nous pouvons mieux faire.', 'Your experience helps us improve. Tell us what you liked, or what we could do better.'),
                    'buttons' => [[$T('Laisser un avis', 'Leave a review'), $contact, 'accent'], [$T('Voir les produits', 'View products'), $products, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'faq', 'slug' => 'faq', 'title' => 'FAQ',
            'description' => $T('Paiement, livraison, retours et horaires : les réponses aux questions les plus fréquentes de nos clients.', 'Payment, delivery, returns and opening hours: answers to our customers’ most frequent questions.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions, nos réponses', 'Your questions, our answers'), 'intro' => $T('Tout savoir avant de commander ou de passer au magasin.', 'Everything to know before ordering or visiting the store.')]),
                B::faq($theme, ['eyebrow' => $T('Questions fréquentes', 'Frequently asked questions'), 'title' => $T('Les réponses essentielles', 'The essential answers'), 'intro' => $T('Une autre question ? Écrivez-nous.', 'Another question? Write to us.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Quels moyens de paiement acceptez-vous ?', 'Which payment methods do you accept?'), $T('Mobile Money, espèces et carte bancaire, en magasin comme à la livraison.', 'Mobile Money, cash and bank card, in store and on delivery.')],
                    [$T('Livrez-vous dans toute la ville ?', 'Do you deliver across the city?'), $T('Oui. La livraison est gratuite à partir d’un montant minimum, et payante en dessous selon la distance.', 'Yes. Delivery is free above a minimum amount, and charged below it depending on distance.')],
                    [$T('Combien de temps prend la livraison ?', 'How long does delivery take?'), $T('Le jour même pour les commandes passées le matin, le lendemain sinon.', 'Same day for orders placed in the morning, next day otherwise.')],
                    [$T('Puis-je échanger ou me faire rembourser un article ?', 'Can I exchange or get a refund?'), $T('Oui, sous 7 jours avec votre reçu, si le produit est dans son emballage d’origine.', 'Yes, within 7 days with your receipt, if the product is in its original packaging.')],
                    [$T('Vendez-vous en gros aux professionnels ?', 'Do you sell wholesale to businesses?'), $T('Oui, avec des tarifs dégressifs et une facture détaillée. Contactez-nous pour un devis.', 'Yes, with volume pricing and a detailed invoice. Contact us for a quote.')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Horaires', 'Opening hours'), 'title' => $T('Quand venir nous voir', 'When to visit us'), 'groups' => [
                    [$T('Au magasin', 'In store'), [
                        ['clock', $T('Lundi – vendredi : 8 h – 19 h', 'Monday – Friday: 8 am – 7 pm')],
                        ['clock', $T('Samedi : 8 h – 20 h', 'Saturday: 8 am – 8 pm')],
                        ['clock', $T('Dimanche : 9 h – 14 h', 'Sunday: 9 am – 2 pm')],
                    ]],
                    [$T('Livraisons et commandes', 'Deliveries and orders'), [
                        ['truck', $T('Livraisons : 9 h – 18 h, du lundi au samedi', 'Deliveries: 9 am – 6 pm, Monday to Saturday')],
                        ['message-circle', $T('WhatsApp : réponse en moins d’une heure', 'WhatsApp: reply within an hour')],
                        ['phone', $T('Téléphone : pendant les heures d’ouverture', 'Phone: during opening hours')],
                    ]],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Adresse, téléphone, WhatsApp, e-mail et horaires de notre magasin. Écrivez-nous ou passez nous voir.', 'Address, phone, WhatsApp, email and opening hours of our store. Write to us or drop by.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Passez nous voir ou écrivez-nous', 'Visit us or write to us'), 'intro' => $T('Nous répondons rapidement à vos messages et à vos commandes.', 'We reply quickly to your messages and orders.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Au magasin, par téléphone ou sur WhatsApp, nous sommes à votre écoute.', 'In store, by phone or on WhatsApp, we are here to help.'),
                    'items' => [
                        ['map-pin', $T('Avenue du Commerce, Lubumbashi, RDC', 'Avenue du Commerce, Lubumbashi, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['message-circle', 'WhatsApp : +243 000 000 000', 'https://wa.me/243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – samedi, 8 h – 19 h', 'Monday – Saturday, 8 am – 7 pm')],
                    ],
                    'social' => ['facebook', 'instagram', 'whatsapp', 'tiktok'],
                    'subject' => true,
                    'map' => 'Lubumbashi, Haut-Katanga',
                    'bg' => 'light',
                ]),
            ]];

        $blog = [
            'base' => $u('conseils', 'tips'),
            'title' => $T('Conseils et nouveautés', 'Tips and news'),
            'categories' => ['nouveautes' => $T('Nouveautés', 'New arrivals'), 'conseils' => $T('Conseils', 'Tips'), 'boutique' => $T('La boutique', 'The store')],
            'posts' => [
                ['key' => 'arrivages', 'slug' => $u('nouveaux-arrivages', 'new-arrivals'), 'category' => 'nouveautes', 'tags' => [$T('Arrivages', 'Arrivals')],
                    'title' => $T('Les nouveaux arrivages du mois', 'This month’s new arrivals'),
                    'excerpt' => $T('Produits frais, articles ménagers et téléphones : ce qui vient d’arriver en rayon.', 'Fresh produce, household goods and phones: what just arrived on the shelves.'),
                    'photo' => ['commerce-nouveautes', 'package', $T('Rayons approvisionnés', 'Stocked shelves')],
                    'heading' => $T('À découvrir en magasin', 'Come and see in store'),
                    'body' => [
                        $T('Ce mois-ci, nous avons élargi le rayon alimentation et reçu une nouvelle gamme de téléphones et d’accessoires.', 'This month, we expanded the grocery section and received a new range of phones and accessories.'),
                        $T('Les quantités sont limitées pour certains produits : passez tôt ou réservez par téléphone.', 'Quantities are limited for some products: come early or reserve by phone.'),
                        $T('Les prix et les disponibilités sont à confirmer en magasin.', 'Prices and availability are to be confirmed in store.'),
                    ]],
                ['key' => 'fidelite', 'slug' => $u('carte-de-fidelite', 'loyalty-card'), 'category' => 'boutique', 'tags' => [$T('Fidélité', 'Loyalty')],
                    'title' => $T('Notre carte de fidélité, comment ça marche', 'How our loyalty card works'),
                    'excerpt' => $T('Cumulez des points à chaque achat et profitez de réductions réservées.', 'Earn points with every purchase and enjoy exclusive discounts.'),
                    'photo' => ['commerce-fidelite', 'credit-card', $T('Cliente en boutique', 'Customer in the store')],
                    'quote' => $T('« Merci à nos clients fidèles : vous êtes la raison d’être de notre boutique. »', '“Thank you to our loyal customers: you are the reason our store exists.”'),
                    'body' => [
                        $T('La carte est gratuite : demandez-la en caisse, il suffit d’un nom et d’un numéro de téléphone.', 'The card is free: ask for it at the checkout, all we need is a name and a phone number.'),
                        $T('Chaque achat vous rapporte des points, échangeables contre des bons de réduction.', 'Every purchase earns points that can be exchanged for discount vouchers.'),
                        $T('Les titulaires de la carte reçoivent aussi nos offres en avant-première.', 'Cardholders also receive our offers first.'),
                    ]],
                ['key' => 'panier', 'slug' => $u('bien-remplir-son-panier', 'smart-shopping-tips'), 'category' => 'conseils', 'tags' => [$T('Budget', 'Budget')],
                    'title' => $T('Faire ses courses malin', 'Smart shopping tips'),
                    'excerpt' => $T('Liste, saisons et formats familiaux : nos conseils pour maîtriser votre budget.', 'Lists, seasons and family packs: our tips to keep your budget under control.'),
                    'photo' => ['commerce-rayon-alimentation', 'shopping-cart', $T('Rayon alimentation', 'Grocery section')],
                    'heading' => $T('Nos trois conseils', 'Our three tips'),
                    'body' => [
                        $T('Préparez une liste avant de venir : elle évite les achats imprévus.', 'Make a list before you come: it avoids impulse buys.'),
                        $T('Privilégiez les fruits et légumes de saison, plus frais et moins chers, et comparez le prix au kilo des formats familiaux.', 'Choose seasonal fruit and vegetables, fresher and cheaper, and compare the price per kilo of family packs.'),
                        $T('Notre équipe est là pour vous conseiller en rayon.', 'Our team is here to help you on the shop floor.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Produits', 'Products'), 'url' => $products],
                    ['label' => $T('Services', 'Services'), 'url' => $services],
                    ['label' => $T('Offres', 'Offers'), 'url' => $offers],
                    ['label' => $T('À propos', 'About'), 'url' => $about],
                    ['label' => $T('Avis clients', 'Reviews'), 'url' => $reviews],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Commander sur WhatsApp', 'Order on WhatsApp'),
                'cta_url' => $contact,
                'footer_text' => $T('Votre magasin de proximité : produits de qualité, prix justes et livraison rapide dans toute la ville.', 'Your neighborhood store: quality products, fair prices and fast delivery across the city.'),
                'footer_links' => [
                    ['label' => $blog['title'], 'url' => '/'.$blog['base']],
                    ['label' => $T('Nos produits', 'Our products'), 'url' => $products],
                    ['label' => $T('Offres et promotions', 'Offers and promotions'), 'url' => $offers],
                    ['label' => 'FAQ', 'url' => $faq],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
