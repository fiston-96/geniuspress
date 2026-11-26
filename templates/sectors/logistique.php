<?php

/*
 * Pack « Transport et logistique » : 8 pages, en français et en anglais.
 * Fret routier, maritime et aérien, entreposage, dernier kilomètre, suivi
 * d'envoi, secteurs servis, demande de cotation, réseau.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'entreprise). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Transport et automobile', 'en' => 'Transport and automotive'],
        'icon' => 'truck',
        'keywords' => ['transport', 'logistique', 'fret', 'transitaire', 'commissionnaire', 'entreposage', 'entrepôt', 'messagerie', 'livraison', 'douane', 'conteneur', 'supply chain', 'logistics', 'freight', 'freight forwarder', 'shipping', 'warehousing', 'delivery', 'last mile', 'customs', 'cargo', 'trucking'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $services = $u('/solutions-transport', '/freight-services');
        $warehousing = $u('/entreposage', '/warehousing');
        $tracking = $u('/suivi-envoi', '/tracking');
        $industries = $u('/secteurs', '/industries');
        $network = $u('/reseau', '/network');
        $news = $u('/actualites', '/news');
        $contact = '/contact';

        $modes = [
            ['truck', $T('Fret routier', 'Road freight'), $T('Lots complets, groupage et transport exceptionnel, en national comme en international.', 'Full truckloads, groupage and oversized loads, domestic and cross-border.'), $services, $T('En savoir plus', 'Learn more')],
            ['globe', $T('Fret maritime', 'Ocean freight'), $T('Conteneurs complets ou groupage, import et export, de port à porte.', 'Full containers or consolidated cargo, import and export, port to door.'), $services, $T('En savoir plus', 'Learn more')],
            ['plane', $T('Fret aérien', 'Air freight'), $T('Envois urgents et marchandises sensibles, en vol direct ou en consolidation.', 'Urgent and sensitive shipments, direct or consolidated.'), $services, $T('En savoir plus', 'Learn more')],
            ['warehouse', $T('Entreposage', 'Warehousing'), $T('Stockage, préparation de commandes et gestion des stocks en temps réel.', 'Storage, order picking and real-time inventory management.'), $warehousing, $T('Nos entrepôts', 'Our warehouses')],
            ['package', $T('Dernier kilomètre', 'Last-mile delivery'), $T('Livraison aux magasins, aux entreprises et aux particuliers, avec preuve de livraison.', 'Delivery to stores, businesses and consumers, with proof of delivery.'), $services, $T('En savoir plus', 'Learn more')],
            ['file-text', $T('Douane', 'Customs'), $T('Formalités d’import et d’export, classement tarifaire et conseil en réglementation.', 'Import and export clearance, tariff classification and compliance advice.'), $services, $T('En savoir plus', 'Learn more')],
        ];
        $testimonials = ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Ils nous confient leurs marchandises', 'They trust us with their goods'), 'items' => [
            [$T('Un interlocuteur unique pour le maritime, la douane et la livraison finale : nos délais d’approvisionnement ont baissé de six jours.', 'One contact for ocean freight, customs and final delivery: our supply lead time dropped by six days.'), '[Prénom Nom]', $T('Responsable achats, distributeur d’équipements', 'Purchasing manager, equipment distributor')],
            [$T('L’entrepôt gère nos stocks et prépare nos commandes en ligne le jour même. Le taux d’erreur est quasi nul.', 'The warehouse manages our stock and ships our online orders the same day. The error rate is close to zero.'), '[Prénom Nom]', $T('Fondatrice, marque de cosmétiques', 'Founder, cosmetics brand')],
            [$T('Quand une pièce critique doit arriver demain, ils trouvent une solution. Et nous savons à tout moment où elle se trouve.', 'When a critical part has to arrive tomorrow, they find a way. And we always know where it is.'), '[Prénom Nom]', $T('Directeur maintenance, site industriel', 'Maintenance director, industrial plant')],
        ]];
        $cta = ['title' => $T('Une marchandise à expédier ?', 'Something to ship?'), 'text' => $T('Indiquez l’origine, la destination et la nature de l’envoi : vous recevez une cotation détaillée sous 24 heures.', 'Tell us the origin, destination and type of cargo: you get a detailed quote within 24 hours.'),
            'buttons' => [[$T('Demander une cotation', 'Request a quote'), $contact, 'accent'], [$T('Suivre un envoi', 'Track a shipment'), $tracking, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Transport routier, maritime et aérien, entreposage, douane et livraison du dernier kilomètre. Cotation sous 24 heures et suivi en temps réel.', 'Road, ocean and air freight, warehousing, customs and last-mile delivery. Quotes within 24 hours and real-time tracking.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Transport et logistique', 'Transport and logistics'),
                    'title' => $T('Vos marchandises, partout, à l’heure', 'Your goods, anywhere, on time'),
                    'text' => $T('Route, mer, air, entrepôt et livraison finale : nous organisons toute votre chaîne logistique, avec un interlocuteur unique et un suivi en temps réel.', 'Road, sea, air, warehouse and final delivery: we run your entire supply chain, with one point of contact and real-time tracking.'),
                    'buttons' => [[$T('Demander une cotation', 'Request a quote'), $contact], [$T('Suivre un envoi', 'Track a shipment'), $tracking]],
                    'photo' => ['logistique-accueil', 'truck', $T('Camion semi-remorque roulant sur une autoroute au coucher du soleil', 'Semi-truck driving on a highway at sunset')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos solutions', 'Our services'), 'title' => $T('Une chaîne logistique complète', 'A complete supply chain'), 'items' => $modes]),
                B::stats($theme, ['items' => [
                    [25, '', $T('ans d’expérience', 'years of experience')],
                    [180, '', $T('véhicules en propre', 'company-owned vehicles')],
                    [$lang === 'fr' ? 65000 : 700000, $T(' m²', ' sq ft'), $T('d’entrepôts', 'of warehouse space')],
                    [98, ' %', $T('de livraisons à l’heure', 'on-time deliveries')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Suivi en temps réel', 'Real-time tracking'),
                    'title' => $T('Vous savez toujours où est votre envoi', 'You always know where your shipment is'),
                    'html' => '<p>'.e($T('Chaque envoi reçoit un numéro de suivi. Étapes, position, documents et preuve de livraison sont accessibles en ligne, et vous êtes alerté en cas d’écart.', 'Every shipment gets a tracking number. Milestones, location, documents and proof of delivery are available online, and you are alerted to any delay.')).'</p>',
                    'bullets' => [$T('Notifications par e-mail ou SMS', 'Email or text notifications'), $T('Preuve de livraison avec photo et signature', 'Proof of delivery with photo and signature'), $T('Documents de transport téléchargeables', 'Downloadable shipping documents')],
                    'buttons' => [[$T('Suivre un envoi', 'Track a shipment'), $tracking, 'outline']],
                    'photo' => ['logistique-suivi', 'map', $T('Opératrice suivant des livraisons sur une carte numérique dans un centre de contrôle', 'Operator monitoring deliveries on a digital map in a control room')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::cards($theme, ['eyebrow' => $T('Secteurs servis', 'Industries'), 'title' => $T('Une expertise par métier', 'Expertise for every industry'), 'more' => $T('Voir les secteurs', 'See industries'), 'items' => [
                    ['logistique-secteur-industrie', 'factory', $T('Pièces industrielles chargées dans un conteneur', 'Industrial parts loaded into a container'), $T('Pièces détachées, machines et flux d’approvisionnement des usines.', 'Spare parts, machinery and plant supply flows.'), $industries],
                    ['logistique-secteur-commerce', 'shopping-cart', $T('Colis de commandes en ligne prêts à être livrés', 'Online order parcels ready for delivery'), $T('Préparation de commandes, réassort des magasins et livraison aux clients.', 'Order fulfillment, store replenishment and customer delivery.'), $industries],
                    ['logistique-secteur-sante', 'stethoscope', $T('Cartons de produits pharmaceutiques en température dirigée', 'Temperature-controlled pharmaceutical boxes'), $T('Produits de santé sous température dirigée et traçabilité complète.', 'Healthcare products under temperature control with full traceability.'), $industries],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('solutions-transport', 'freight-services'), 'title' => $T('Solutions de transport', 'Freight services'),
            'description' => $T('Fret routier, maritime et aérien, douane et livraison du dernier kilomètre : nos solutions, nos délais indicatifs et nos engagements.', 'Road, ocean and air freight, customs and last-mile delivery: our services, typical transit times and commitments.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Solutions de transport', 'Freight services'), 'title' => $T('Le bon mode pour chaque envoi', 'The right mode for every shipment'), 'intro' => $T('Nous comparons coût, délai et empreinte carbone pour vous proposer la meilleure combinaison.', 'We weigh cost, transit time and carbon footprint to offer the best combination.')]),
                B::features($theme, ['cols' => 3, 'items' => array_map(fn ($m) => array_slice($m, 0, 3), $modes)]),
                M::table($theme, ['eyebrow' => $T('Délais indicatifs', 'Typical transit times'), 'title' => $T('Combien de temps pour arriver ?', 'How long will it take?'), 'bg' => 'light', 'rows' => [
                    [$T('Mode', 'Mode'), $T('Délai de porte à porte', 'Door-to-door time'), $T('Idéal pour', 'Best for')],
                    [$T('Routier national', 'Domestic road'), $T('24 à 48 heures', '24 to 48 hours'), $T('Palettes, lots complets', 'Pallets, full loads')],
                    [$T('Routier international', 'Cross-border road'), $T('3 à 7 jours', '3 to 7 days'), $T('Flux réguliers entre pays voisins', 'Regular flows between neighboring countries')],
                    [$T('Maritime', 'Ocean'), $T('15 à 40 jours', '15 to 40 days'), $T('Volumes importants, coût réduit', 'Large volumes, lower cost')],
                    [$T('Aérien', 'Air'), $T('2 à 5 jours', '2 to 5 days'), $T('Urgences, produits à forte valeur', 'Urgent, high-value goods')],
                ], 'note' => $T('Délais donnés à titre indicatif, selon l’origine, la destination et les formalités douanières.', 'Indicative times, depending on origin, destination and customs formalities.')]),
                B::split($theme, [
                    'eyebrow' => $T('Commission de transport', 'Freight forwarding'),
                    'title' => $T('Un seul interlocuteur, de l’usine au client final', 'One contact, from factory to end customer'),
                    'html' => '<p>'.e($T('Réservation des capacités, documents de transport, assurance marchandises, dédouanement et livraison : votre chargé de compte coordonne chaque maillon et vous informe à chaque étape.', 'Capacity booking, shipping documents, cargo insurance, customs clearance and delivery: your account manager coordinates every link and keeps you informed at each step.')).'</p>',
                    'bullets' => [$T('Conseil sur les incoterms et la réglementation', 'Advice on Incoterms and regulations'), $T('Assurance ad valorem sur demande', 'Cargo insurance on request'), $T('Marchandises dangereuses et hors gabarit', 'Dangerous and oversized goods')],
                    'photo' => ['logistique-port', 'globe', $T('Porte-conteneurs à quai et portiques de chargement dans un port de commerce', 'Container ship at the quay with gantry cranes in a cargo port')],
                    'reverse' => true,
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'bg' => 'light', 'buttons' => [[$T('Demander une cotation', 'Request a quote'), $contact, 'outline']], 'items' => [
                    [$T('Transportez-vous de petits volumes ?', 'Do you handle small shipments?'), $T('Oui. Le groupage permet d’expédier une seule palette ou quelques colis à un coût partagé.', 'Yes. Groupage lets you ship a single pallet or a few parcels at a shared cost.')],
                    [$T('Vos marchandises sont-elles assurées ?', 'Is my cargo insured?'), $T('Notre responsabilité de transporteur s’applique selon les conventions en vigueur. Nous proposons une assurance complémentaire à la valeur réelle.', 'Our carrier liability applies under the relevant conventions. We offer additional insurance for the full value.')],
                    [$T('Gérez-vous les formalités douanières ?', 'Do you handle customs formalities?'), $T('Oui, nos déclarants en douane préparent les déclarations d’import et d’export et vous conseillent sur les droits et taxes.', 'Yes, our customs brokers prepare import and export declarations and advise you on duties and taxes.')],
                    [$T('Transportez-vous des marchandises dangereuses ?', 'Do you ship dangerous goods?'), $T('Oui, avec des conducteurs et des agents formés et des emballages conformes à la réglementation.', 'Yes, with trained drivers and agents and packaging that meets regulations.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'warehousing', 'slug' => $u('entreposage', 'warehousing'), 'title' => $T('Entreposage', 'Warehousing'),
            'description' => $T('Entrepôts sous douane et en température dirigée, préparation de commandes, cross-docking et gestion des stocks en ligne pour vos marchandises.', 'Bonded and temperature-controlled warehouses, order fulfillment, cross-docking and online inventory management for your goods.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Entreposage', 'Warehousing'), 'title' => $T('Des entrepôts qui travaillent pour vous', 'Warehouses that work for you'), 'intro' => $T('65 000 m² de surfaces sécurisées, reliées à notre réseau de transport.', '700,000 sq ft of secure space, connected to our transport network.')]),
                B::split($theme, [
                    'eyebrow' => $T('Logistique contractuelle', 'Contract logistics'),
                    'title' => $T('Stockez, préparez, expédiez', 'Store, pick, ship'),
                    'html' => '<p>'.e($T('Réception, contrôle, mise en stock, préparation et expédition : nos équipes prennent en charge vos flux avec un logiciel de gestion d’entrepôt connecté à votre boutique en ligne ou à votre système de gestion.', 'Receiving, inspection, put-away, picking and shipping: our teams run your flows with a warehouse management system connected to your online store or ERP.')).'</p>',
                    'bullets' => [$T('Stock consultable en ligne en temps réel', 'Live inventory online'), $T('Commandes passées avant 14 h expédiées le jour même', 'Orders placed by 2 pm ship the same day'), $T('Inventaires tournants et rapports mensuels', 'Cycle counts and monthly reports')],
                    'photo' => ['logistique-entrepot', 'warehouse', $T('Allées d’un grand entrepôt avec rayonnages de palettes et chariot élévateur', 'Aisles of a large warehouse with pallet racking and a forklift')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos prestations', 'Our services'), 'title' => $T('Une offre modulable', 'Flexible services'), 'bg' => 'light', 'cols' => 4, 'items' => [
                    ['box', $T('Stockage', 'Storage'), $T('Palettes, colis ou vrac, au mètre carré ou à l’emplacement.', 'Pallets, cartons or bulk, by the square foot or by the slot.')],
                    ['list-checks', $T('Préparation de commandes', 'Order picking'), $T('Prélèvement, colisage, étiquetage et kits promotionnels.', 'Picking, packing, labeling and promotional kits.')],
                    ['refresh-cw', $T('Cross-docking', 'Cross-docking'), $T('Éclatement des arrivages et réexpédition sans stockage.', 'Splitting inbound loads and shipping out without storage.')],
                    ['thermometer', $T('Température dirigée', 'Temperature control'), $T('Zones à température contrôlée pour les produits sensibles.', 'Temperature-controlled zones for sensitive products.')],
                    ['lock', $T('Sous douane', 'Bonded storage'), $T('Entrepôt sous douane pour différer le paiement des droits.', 'Bonded warehouse to defer duty payments.')],
                    ['arrow-left', $T('Retours', 'Returns'), $T('Réception, contrôle et remise en stock des retours clients.', 'Receiving, inspecting and restocking customer returns.')],
                    ['shield-check', $T('Sécurité', 'Security'), $T('Vidéosurveillance, contrôle d’accès et gardiennage 24 h/24.', 'CCTV, access control and 24/7 guards.')],
                    ['chart-column', $T('Pilotage', 'Reporting'), $T('Indicateurs de performance partagés chaque mois.', 'Performance indicators shared every month.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Démarrage', 'Onboarding'), 'title' => $T('Lancer votre logistique en quatre étapes', 'Launch your logistics in four steps'), 'items' => [
                    [$T('Analyse', 'Analysis'), $T('Étude de vos volumes, références et saisonnalité.', 'Review of your volumes, SKUs and seasonality.')],
                    [$T('Proposition', 'Proposal'), $T('Organisation, tarifs et indicateurs de service.', 'Setup, pricing and service levels.')],
                    [$T('Intégration', 'Integration'), $T('Connexion des systèmes et transfert du stock.', 'System connection and stock transfer.')],
                    [$T('Exploitation', 'Operations'), $T('Suivi quotidien et revue de performance mensuelle.', 'Daily follow-up and monthly performance review.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'tracking', 'slug' => $u('suivi-envoi', 'tracking'), 'title' => $T('Suivi d’envoi', 'Shipment tracking'),
            'description' => $T('Suivez votre envoi avec son numéro : étapes du transport, documents, alertes et preuve de livraison. Notre service client répond 6 j/7.', 'Track your shipment with its number: transport milestones, documents, alerts and proof of delivery. Customer service answers 6 days a week.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Suivi d’envoi', 'Tracking'), 'title' => $T('Où en est mon envoi ?', 'Where is my shipment?'), 'intro' => $T('Munissez-vous du numéro de suivi indiqué sur votre bon de commande ou votre e-mail de confirmation.', 'Have your tracking number ready: it appears on your order form or confirmation email.')]),
                B::features($theme, ['eyebrow' => $T('Trois façons de suivre', 'Three ways to track'), 'title' => $T('Choisissez la plus pratique', 'Pick the most convenient'), 'items' => [
                    ['laptop', $T('Espace client en ligne', 'Online customer portal'), $T('Tous vos envois, leurs documents et leurs preuves de livraison.', 'All your shipments, their documents and proofs of delivery.')],
                    ['bell', $T('Alertes automatiques', 'Automatic alerts'), $T('Un message à chaque étape clé et en cas de retard.', 'A message at each key milestone and if there is a delay.')],
                    ['headphones', $T('Service client', 'Customer service'), $T('Un conseiller vous répond du lundi au samedi.', 'An agent answers Monday to Saturday.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Les étapes', 'Milestones'), 'title' => $T('Ce que signifient les statuts', 'What each status means'), 'bg' => 'light', 'items' => [
                    [$T('Enlevé', 'Picked up'), $T('La marchandise a été chargée chez l’expéditeur.', 'The goods have been collected from the shipper.')],
                    [$T('En transit', 'In transit'), $T('L’envoi voyage vers le pays ou la plateforme de destination.', 'The shipment is on its way to the destination country or hub.')],
                    [$T('En dédouanement', 'In customs'), $T('Les formalités sont en cours auprès des services douaniers.', 'Formalities are under way with customs.')],
                    [$T('En livraison', 'Out for delivery'), $T('Le livreur est en route : un créneau vous est communiqué.', 'The driver is on the way: you receive a time window.')],
                    [$T('Livré', 'Delivered'), $T('La preuve de livraison est disponible en ligne.', 'Proof of delivery is available online.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Un souci avec votre envoi ?', 'A problem with your shipment?'), 'intro' => $T('Notre service client traite chaque réclamation sous 48 heures.', 'Our customer service handles every claim within 48 hours.'), 'buttons' => [[$T('Contacter le service client', 'Contact customer service'), $contact, 'outline']], 'items' => [
                    [$T('Mon numéro de suivi ne donne aucun résultat', 'My tracking number shows nothing'), $T('Le suivi s’active à l’enlèvement de la marchandise. Si rien n’apparaît après 24 heures, contactez-nous.', 'Tracking starts once the goods are picked up. If nothing shows after 24 hours, contact us.')],
                    [$T('Mon envoi est bloqué en douane', 'My shipment is held in customs'), $T('Un document peut manquer. Notre équipe douane vous contacte pour le compléter au plus vite.', 'A document may be missing. Our customs team will contact you to complete it quickly.')],
                    [$T('Je ne serai pas présent à la livraison', 'I won’t be there for delivery'), $T('Vous pouvez choisir un autre créneau, une autre adresse ou un retrait en agence.', 'You can choose another time slot, another address or pickup at a branch.')],
                    [$T('Ma marchandise est arrivée endommagée', 'My goods arrived damaged'), $T('Notez des réserves précises sur le bon de livraison et envoyez-nous des photos sous 3 jours.', 'Write specific reservations on the delivery note and send us photos within 3 days.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'industries', 'slug' => $u('secteurs', 'industries'), 'title' => $T('Secteurs servis', 'Industries'),
            'description' => $T('Industrie, commerce et e-commerce, santé, agroalimentaire, énergie et projets : des solutions logistiques adaptées à chaque secteur.', 'Manufacturing, retail and e-commerce, healthcare, food, energy and projects: logistics solutions tailored to each industry.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Secteurs servis', 'Industries'), 'title' => $T('Chaque secteur a ses contraintes', 'Every industry has its constraints'), 'intro' => $T('Nos équipes connaissent les exigences de vos marchandises et de vos clients.', 'Our teams know what your goods and your customers require.')]),
                B::cards($theme, ['items' => [
                    ['logistique-secteur-industrie', 'factory', $T('Pièces industrielles chargées dans un conteneur', 'Industrial parts loaded into a container'), $T('Approvisionnement des usines, pièces détachées urgentes et machines.', 'Plant supply, urgent spare parts and machinery.')],
                    ['logistique-secteur-commerce', 'shopping-cart', $T('Colis de commandes en ligne prêts à être livrés', 'Online order parcels ready for delivery'), $T('Préparation de commandes, réassort et livraison à domicile.', 'Order fulfillment, replenishment and home delivery.')],
                    ['logistique-secteur-sante', 'stethoscope', $T('Cartons de produits pharmaceutiques en température dirigée', 'Temperature-controlled pharmaceutical boxes'), $T('Chaîne du froid, traçabilité par lot et bonnes pratiques de distribution.', 'Cold chain, batch traceability and good distribution practice.')],
                    ['logistique-secteur-agro', 'wheat', $T('Palettes de produits alimentaires dans un camion frigorifique', 'Pallets of food products in a refrigerated truck'), $T('Denrées sèches et fraîches, export de récoltes et respect des normes sanitaires.', 'Dry and fresh foods, crop exports and food safety standards.')],
                    ['logistique-secteur-energie', 'zap', $T('Colis lourds de matériel énergétique sur une remorque surbaissée', 'Heavy energy equipment on a low-bed trailer'), $T('Matériel lourd, transport exceptionnel et logistique de chantier.', 'Heavy equipment, oversized transport and site logistics.')],
                    ['logistique-secteur-projets', 'construction', $T('Chargement d’une pièce hors gabarit par une grue mobile', 'Mobile crane lifting an oversized load'), $T('Logistique de projets : planification, levage et convois hors gabarit.', 'Project cargo: planning, lifting and oversized convoys.')],
                ]]),
                B::stats($theme, ['bg' => 'primary', 'title' => $T('Notre activité chaque année', 'Our activity each year'), 'items' => [
                    [12000, '', $T('conteneurs organisés', 'containers handled')],
                    [1400000, '', $T('colis livrés', 'parcels delivered')],
                    [850, '', $T('clients actifs', 'active customers')],
                    [40, '', $T('pays desservis', 'countries served')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'network', 'slug' => $u('reseau', 'network'), 'title' => $T('Notre réseau', 'Our network'),
            'description' => $T('Agences, plateformes, entrepôts et partenaires dans le monde : un réseau international et une entreprise engagée pour un transport plus sobre.', 'Branches, hubs, warehouses and partners worldwide: an international network and a company committed to cleaner transport.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Notre réseau', 'Our network'), 'title' => $T('Un réseau local, une portée mondiale', 'Local roots, global reach'), 'intro' => $T('Nos agences et nos partenaires agréés couvrent plus de 40 pays.', 'Our branches and certified partners cover more than 40 countries.')]),
                B::split($theme, [
                    'eyebrow' => $T('Qui sommes-nous', 'Who we are'),
                    'title' => $T('Une entreprise familiale devenue opérateur international', 'A family business turned international operator'),
                    'html' => '<p>'.e($T('Fondée avec trois camions, l’entreprise a ouvert sa première agence de commission de transport puis ses entrepôts. Elle reste indépendante et dirigée par ses fondateurs.', 'Founded with three trucks, the company opened its first freight forwarding office, then its warehouses. It remains independent and led by its founders.')).'</p><p>'.e($T('Commissionnaire de transport agréé, licence [N° de licence] ; représentant en douane enregistré [N° d’agrément].', 'Licensed freight forwarder, license [License no.]; registered customs representative [Registration no.].')).'</p>',
                    'photo' => ['logistique-agence', 'building', $T('Quai de chargement d’une plateforme logistique avec camions à cul de quai', 'Loading dock of a logistics hub with trucks backed up to the doors')],
                ]),
                B::schedule($theme, ['eyebrow' => $T('Implantations', 'Locations'), 'title' => $T('Nos plateformes', 'Our hubs'), 'groups' => [
                    [$T('Agences et entrepôts', 'Branches and warehouses'), [
                        ['map-pin', $T('Siège et plateforme principale : [Ville]', 'Head office and main hub: [City]')],
                        ['map-pin', $T('Agence portuaire : [Ville]', 'Port office: [City]')],
                        ['map-pin', $T('Agence aéroportuaire : [Ville]', 'Airport office: [City]')],
                    ]],
                    [$T('Partenaires', 'Partners'), [
                        ['globe', $T('Correspondants dans plus de 40 pays', 'Correspondents in more than 40 countries')],
                        ['handshake', $T('Membre d’un réseau international de transitaires', 'Member of an international forwarders network')],
                        ['plane', $T('Accords avec les principales compagnies aériennes et maritimes', 'Agreements with major airlines and shipping lines')],
                    ]],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Transporter mieux', 'Moving goods better'), 'bg' => 'light', 'items' => [
                    ['leaf', $T('Moins d’émissions', 'Lower emissions'), $T('Flotte renouvelée, écoconduite et optimisation des tournées.', 'Modern fleet, eco-driving and route optimization.')],
                    ['shield-check', $T('Sécurité', 'Safety'), $T('Conducteurs formés chaque année et véhicules contrôlés.', 'Drivers trained every year and vehicles inspected.')],
                    ['users', $T('Équipes', 'People'), $T('Formation continue de nos conducteurs, agents et déclarants.', 'Ongoing training for our drivers, agents and customs brokers.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('Direction', 'Leadership'), 'title' => $T('Vos interlocuteurs', 'Your contacts'), 'items' => [
                    ['[Prénom Nom]', $T('Directeur général', 'Chief executive officer'), 'logistique-equipe-1'],
                    ['[Prénom Nom]', $T('Directrice des opérations', 'Director of operations'), 'logistique-equipe-2'],
                    ['[Prénom Nom]', $T('Responsable commission de transport', 'Head of freight forwarding'), 'logistique-equipe-3'],
                    ['[Prénom Nom]', $T('Responsable entrepôts', 'Head of warehousing'), 'logistique-equipe-4'],
                ]]),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites', 'news'), 'title' => $T('Actualités', 'News'),
            'description' => $T('Actualités de l’entreprise, conseils pour expédier à l’international et évolutions de la réglementation douanière et du transport.', 'Company news, tips for shipping internationally and updates on customs and transport regulations.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Actualités et conseils logistiques', 'News and logistics tips'), 'intro' => $T('Ce qu’il faut savoir pour expédier sereinement.', 'What you need to know to ship with confidence.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Une chaîne logistique fiable, c’est d’abord une information fiable, partagée au bon moment.', 'A reliable supply chain starts with reliable information, shared at the right time.'), 'source' => $T('— Notre direction des opérations', '— Our operations team')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Demandez une cotation de transport ou d’entreposage, ou contactez notre service client. Réponse sous 24 heures ouvrées.', 'Request a freight or warehousing quote, or contact customer service. Reply within one business day.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Cotation et contact', 'Quote and contact'), 'title' => $T('Demandez votre cotation', 'Request your quote'), 'intro' => $T('Plus votre demande est précise, plus notre proposition le sera.', 'The more precise your request, the more precise our proposal.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Pour une cotation, indiquez dans votre message l’origine, la destination, le poids, le volume et la nature de la marchandise.', 'For a quote, include the origin, destination, weight, volume and type of goods in your message.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 7 h – 19 h ; samedi, 8 h – 12 h', 'Monday – Friday, 7 am – 7 pm; Saturday, 8 am – noon')],
                    ],
                    'social' => ['facebook', 'x', 'whatsapp'],
                    'subject' => true,
                    'button' => $T('Envoyer ma demande', 'Send my request'),
                    'bg' => 'white',
                ]),
                B::steps($theme, ['eyebrow' => $T('Après votre demande', 'After your request'), 'title' => $T('Votre cotation en 24 heures', 'Your quote within 24 hours'), 'bg' => 'light', 'items' => [
                    [$T('Analyse', 'Review'), $T('Un chargé de compte étudie votre besoin et vous rappelle si nécessaire.', 'An account manager reviews your needs and calls you if needed.')],
                    [$T('Proposition', 'Proposal'), $T('Vous recevez une offre détaillée : mode, délai, prix et conditions.', 'You receive a detailed offer: mode, transit time, price and terms.')],
                    [$T('Réservation', 'Booking'), $T('Vous validez, nous réservons et organisons l’enlèvement.', 'You approve, we book and arrange pickup.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Cotation et paiement', 'Quotes and payment'), 'items' => [
                    [$T('La cotation est-elle gratuite ?', 'Is the quote free?'), $T('Oui, gratuite et sans engagement. Elle est valable 15 jours pour le routier et selon les tarifs des compagnies pour l’aérien et le maritime.', 'Yes, free and with no obligation. It is valid for 15 days for road freight, and per carrier rates for air and ocean.')],
                    [$T('Comment puis-je payer ?', 'How can I pay?'), $T('Par virement, carte bancaire ou paiement mobile. Les clients réguliers bénéficient d’un compte avec facturation mensuelle.', 'By bank transfer, card or mobile payment. Regular customers get an account with monthly invoicing.')],
                    [$T('Proposez-vous des contrats annuels ?', 'Do you offer annual contracts?'), $T('Oui, avec des tarifs négociés et des engagements de service pour les flux réguliers.', 'Yes, with negotiated rates and service commitments for regular flows.')],
                ]]),
            ]];

        $blog = [
            'base' => $u('actualites', 'news'),
            'title' => $T('Actualités', 'News'),
            'categories' => ['conseils' => $T('Conseils', 'Tips'), 'douane' => $T('Douane', 'Customs'), 'entreprise' => $T('Entreprise', 'Company')],
            'posts' => [
                ['key' => 'incoterms', 'slug' => $u('choisir-son-incoterm', 'choosing-an-incoterm'), 'category' => 'conseils', 'tags' => ['Incoterms'],
                    'title' => $T('Bien choisir son incoterm à l’export', 'Choosing the right Incoterm for export'),
                    'excerpt' => $T('Qui paie le transport, qui assure la marchandise, où le risque change de mains : l’essentiel à savoir.', 'Who pays for transport, who insures the goods, where risk changes hands: the essentials.'),
                    'photo' => ['logistique-blog-incoterms', 'globe', $T('Responsable logistique consultant des documents d’expédition devant des conteneurs', 'Logistics manager reviewing shipping documents in front of containers')],
                    'heading' => $T('Trois questions à se poser', 'Three questions to ask'),
                    'body' => [
                        $T('Les incoterms précisent la répartition des frais, des risques et des formalités entre vendeur et acheteur.', 'Incoterms set out how costs, risks and formalities are split between seller and buyer.'),
                        $T('Avant de choisir, demandez-vous qui maîtrise le mieux le transport, qui doit dédouaner et à quel moment vous voulez transférer le risque.', 'Before choosing, ask who is best placed to manage transport, who must clear customs and when you want risk to pass.'),
                        $T('Votre chargé de compte peut vous aider à choisir la règle la plus adaptée à chaque contrat.', 'Your account manager can help you pick the most suitable rule for each contract.'),
                    ]],
                ['key' => 'douane', 'slug' => $u('dedouanement-sans-retard', 'customs-without-delays'), 'category' => 'douane', 'tags' => [$T('Documents', 'Documents')],
                    'title' => $T('Dédouanement : les documents à préparer pour éviter les retards', 'Customs clearance: documents to prepare to avoid delays'),
                    'excerpt' => $T('Facture commerciale, liste de colisage, certificat d’origine : la liste à vérifier avant chaque envoi.', 'Commercial invoice, packing list, certificate of origin: the checklist before every shipment.'),
                    'photo' => ['logistique-blog-douane', 'file-text', $T('Déclarant en douane vérifiant des documents à côté d’un conteneur ouvert', 'Customs broker checking paperwork next to an open container')],
                    'quote' => $T('« La plupart des blocages en douane viennent d’un document incomplet. »', '“Most customs holds come from an incomplete document.”'),
                    'body' => [
                        $T('Une facture commerciale complète, une liste de colisage précise et le bon code tarifaire évitent l’essentiel des blocages.', 'A complete commercial invoice, an accurate packing list and the right tariff code prevent most holds.'),
                        $T('Selon les marchandises, des certificats sanitaires, d’origine ou de conformité peuvent être exigés.', 'Depending on the goods, health, origin or conformity certificates may be required.'),
                        $T('Envoyez-nous vos documents avant le départ : nos déclarants les vérifient gratuitement.', 'Send us your documents before departure: our brokers check them free of charge.'),
                    ]],
                ['key' => 'entrepot', 'slug' => $u('nouvel-entrepot', 'new-warehouse'), 'category' => 'entreprise', 'tags' => [$T('Entreposage', 'Warehousing')],
                    'title' => $T('Notre nouvel entrepôt de 20 000 m² ouvre ses portes', 'Our new 215,000 sq ft warehouse opens'),
                    'excerpt' => $T('Zones à température dirigée, panneaux solaires et 40 quais de chargement.', 'Temperature-controlled zones, solar panels and 40 loading docks.'),
                    'photo' => ['logistique-blog-entrepot', 'warehouse', $T('Entrepôt neuf et lumineux avec rayonnages vides et sol en résine', 'Bright new warehouse with empty racking and a resin floor')],
                    'heading' => $T('Ce qui change pour nos clients', 'What changes for our customers'),
                    'body' => [
                        $T('Ce nouveau site double nos capacités de stockage à proximité des grands axes routiers.', 'The new site doubles our storage capacity close to major highways.'),
                        $T('Ses zones à température dirigée accueillent les produits de santé et les denrées sensibles.', 'Its temperature-controlled zones handle healthcare products and sensitive foods.'),
                        $T('Les panneaux solaires de la toiture couvrent une large part de sa consommation d’énergie.', 'Rooftop solar panels cover a large share of its energy use.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Transport', 'Freight'), 'url' => $services],
                    ['label' => $T('Entreposage', 'Warehousing'), 'url' => $warehousing],
                    ['label' => $T('Secteurs', 'Industries'), 'url' => $industries],
                    ['label' => $T('Suivi d’envoi', 'Tracking'), 'url' => $tracking],
                    ['label' => $T('Réseau', 'Network'), 'url' => $network],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                ],
                'cta_label' => $T('Demander une cotation', 'Request a quote'),
                'cta_url' => $contact,
                'footer_text' => $T('Transport routier, maritime et aérien, entreposage, douane et livraison du dernier kilomètre, avec un suivi en temps réel.', 'Road, ocean and air freight, warehousing, customs and last-mile delivery, with real-time tracking.'),
                'footer_links' => [
                    ['label' => $T('Solutions de transport', 'Freight services'), 'url' => $services],
                    ['label' => $T('Suivi d’envoi', 'Shipment tracking'), 'url' => $tracking],
                    ['label' => $T('Demander une cotation', 'Request a quote'), 'url' => $contact],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
