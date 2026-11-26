<?php

/*
 * Pack « Garage automobile » : 8 pages, en français et en anglais.
 * Entretien, réparation, pneus, carrosserie, diagnostic, véhicules d'occasion,
 * tarifs forfaitaires et prise de rendez-vous.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du garage). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Transport et automobile', 'en' => 'Transport and automotive'],
        'icon' => 'truck',
        'keywords' => ['garage', 'mécanique', 'mécanicien', 'automobile', 'voiture', 'entretien', 'vidange', 'révision', 'pneus', 'carrosserie', 'diagnostic', 'occasion', 'atelier', 'auto repair', 'car repair', 'mechanic', 'auto shop', 'car service', 'tires', 'body shop', 'used cars', 'oil change'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $services = $u('/entretien-reparation', '/service-repairs');
        $tires = $u('/pneus', '/tires');
        $bodywork = $u('/carrosserie', '/body-shop');
        $pricing = $u('/tarifs', '/pricing');
        $used = $u('/occasions', '/used-cars');
        $tips = $u('/conseils-auto', '/car-tips');
        $contact = '/contact';
        $cur = $T('€', '$');

        $serviceItems = [
            ['wrench', $T('Entretien et révision', 'Scheduled maintenance'), $T('Révision selon le carnet du constructeur, vidange, filtres et niveaux, sans perdre la garantie.', 'Factory-schedule servicing, oil change, filters and fluids, without voiding your warranty.')],
            ['activity', $T('Diagnostic électronique', 'Computer diagnostics'), $T('Lecture des codes défaut, voyants allumés et recherche de panne toutes marques.', 'Fault code reading, warning lights and troubleshooting for all makes.')],
            ['settings', $T('Mécanique', 'Mechanical repairs'), $T('Freins, embrayage, distribution, suspension, échappement et direction.', 'Brakes, clutch, timing belt, suspension, exhaust and steering.')],
            ['car', $T('Pneus et géométrie', 'Tires and alignment'), $T('Vente et montage de pneus, équilibrage, parallélisme et stockage saisonnier.', 'Tire sales and fitting, balancing, alignment and seasonal storage.')],
            ['paintbrush', $T('Carrosserie et peinture', 'Body and paint'), $T('Réparation après sinistre, débosselage, rayures et peinture en cabine.', 'Collision repair, dent removal, scratches and booth painting.')],
            ['thermometer', $T('Climatisation', 'Air conditioning'), $T('Recharge, désinfection et réparation du circuit de climatisation.', 'Recharge, sanitizing and repair of the A/C system.')],
        ];
        $testimonials = ['eyebrow' => $T('Avis clients', 'Customer reviews'), 'title' => $T('Ce que disent nos clients', 'What our customers say'), 'rating' => 5, 'items' => [
            [$T('Devis clair, voiture prête à l’heure annoncée et pièces usagées montrées à la restitution. Enfin un garage de confiance.', 'Clear quote, car ready when promised and old parts shown at pickup. Finally a shop I can trust.'), '[Prénom Nom]', $T('Révision et freins', 'Service and brakes')],
            [$T('Voyant moteur allumé sur l’autoroute : diagnostic le jour même et réparation le lendemain, pour moins que prévu.', 'Check-engine light on the highway: diagnosed the same day, fixed the next, for less than expected.'), '[Prénom Nom]', $T('Diagnostic et réparation', 'Diagnostics and repair')],
            [$T('Ils ont géré la déclaration avec mon assurance et m’ont prêté un véhicule pendant les travaux de carrosserie.', 'They dealt with my insurer and lent me a car during the body work.'), '[Prénom Nom]', $T('Réparation carrosserie', 'Collision repair')],
        ]];
        $cta = ['title' => $T('Votre voiture mérite un bon garage', 'Your car deserves a good shop'), 'text' => $T('Réservez un créneau en ligne ou par téléphone : devis gratuit avant toute intervention.', 'Book a slot online or by phone: free estimate before any work.'),
            'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact, 'accent'], [$T('Voir les tarifs', 'See prices'), $pricing, 'outline-light']]];
        $hours = ['title' => $T('Horaires de l’atelier', 'Shop hours'), 'groups' => [
            [$T('Atelier et accueil', 'Shop and front desk'), [
                ['clock', $T('Lundi – vendredi : 8 h – 18 h 30', 'Monday – Friday: 8 am – 6:30 pm')],
                ['clock', $T('Samedi : 8 h 30 – 13 h', 'Saturday: 8:30 am – 1 pm')],
                ['key', $T('Boîte à clés pour dépôt en dehors des horaires', 'Key drop box for after-hours drop-off')],
            ]],
            [$T('Services pratiques', 'Convenient extras'), [
                ['car', $T('Véhicule de prêt sur réservation', 'Loaner car on request')],
                ['coffee', $T('Salle d’attente avec wifi et café', 'Waiting room with wifi and coffee')],
                ['credit-card', $T('Carte bancaire, virement ou paiement mobile', 'Card, bank transfer or mobile payment')],
            ]],
        ]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Garage toutes marques : entretien, diagnostic, freins, pneus, carrosserie et véhicules d’occasion. Tarifs forfaitaires et rendez-vous rapide.', 'All-makes auto repair: servicing, diagnostics, brakes, tires, body work and used cars. Flat-rate prices and quick appointments.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Garage toutes marques', 'All-makes auto repair'),
                    'title' => $T('Roulez l’esprit tranquille', 'Drive with peace of mind'),
                    'text' => $T('Entretien, réparation, pneus et carrosserie : des mécaniciens qualifiés, des prix affichés à l’avance et votre voiture rendue à l’heure.', 'Servicing, repairs, tires and body work: qualified mechanics, prices shown upfront and your car back on time.'),
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact], [$T('Nos tarifs', 'Our prices'), $pricing]],
                    'photo' => ['garage-accueil', 'wrench', $T('Mécanicien inspectant le dessous d’une voiture levée sur un pont dans un atelier propre', 'Mechanic inspecting the underside of a car on a lift in a clean workshop')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos services', 'Our services'), 'title' => $T('Tout pour votre véhicule', 'Everything your car needs'), 'cols' => 3, 'items' => $serviceItems]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Forfaits', 'Packages'), 'title' => $T('Nos forfaits les plus demandés', 'Our most popular packages'), 'intro' => $T('Prix TTC pour une citadine, pièces et main-d’œuvre comprises.', 'All-in prices for a compact car, parts and labor included.'), 'items' => [
                    [$T('Vidange', 'Oil change'), $T('79', '69'), $cur, $T('à partir de', 'from'), $T('Huile, filtre à huile et contrôle de 20 points.', 'Oil, oil filter and 20-point check.'), [$T('Huile adaptée au moteur', 'Oil matched to your engine'), $T('Filtre à huile neuf', 'New oil filter'), $T('Contrôle des niveaux', 'Fluid level check'), '-'.$T('Filtres air et habitacle', 'Air and cabin filters')], $T('Réserver', 'Book')],
                    [$T('Révision complète', 'Full service'), $T('189', '199'), $cur, $T('à partir de', 'from'), $T('Selon le carnet d’entretien du constructeur.', 'Following the manufacturer’s schedule.'), [$T('Vidange et tous les filtres', 'Oil change and all filters'), $T('Contrôle de 50 points', '50-point inspection'), $T('Mise à jour du carnet', 'Service book stamped'), $T('Garantie constructeur préservée', 'Factory warranty preserved')], $T('Réserver', 'Book'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Freins avant', 'Front brakes'), $T('149', '159'), $cur, $T('à partir de', 'from'), $T('Plaquettes et contrôle des disques.', 'Pads and rotor inspection.'), [$T('Plaquettes neuves', 'New pads'), $T('Contrôle du liquide de frein', 'Brake fluid check'), $T('Essai sur route', 'Road test'), '-'.$T('Disques, si nécessaire', 'Rotors, if needed')], $T('Réserver', 'Book')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Notre engagement', 'Our promise'),
                    'title' => $T('Aucune réparation sans votre accord', 'No repair without your approval'),
                    'html' => '<p>'.e($T('Après le diagnostic, nous vous appelons pour expliquer ce qui est urgent et ce qui peut attendre, avec un devis chiffré. Les pièces remplacées vous sont présentées à la restitution du véhicule.', 'After the diagnosis, we call you to explain what is urgent and what can wait, with a written estimate. Replaced parts are shown to you when you pick up your car.')).'</p>',
                    'bullets' => [$T('Devis gratuit avant intervention', 'Free estimate before any work'), $T('Pièces d’origine ou de qualité équivalente', 'OEM or equivalent-quality parts'), $T('Réparations garanties 12 mois', 'Repairs guaranteed for 12 months')],
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact]],
                    'photo' => ['garage-diagnostic', 'activity', $T('Mécanicienne branchant une valise de diagnostic sous le volant d’une voiture', 'Technician plugging a diagnostic scanner in under a car’s steering wheel')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::stats($theme, ['items' => [
                    [20, '', $T('ans d’atelier', 'years in business')],
                    [6, '', $T('mécaniciens qualifiés', 'qualified mechanics')],
                    [9000, '+', $T('véhicules entretenus', 'vehicles serviced')],
                    [12, $T(' mois', ' months'), $T('de garantie sur nos réparations', 'warranty on repairs')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('entretien-reparation', 'service-repairs'), 'title' => $T('Entretien et réparation', 'Service and repairs'),
            'description' => $T('Révision constructeur, vidange, diagnostic, freins, embrayage, distribution, climatisation : l’entretien et la réparation de votre véhicule toutes marques.', 'Factory servicing, oil changes, diagnostics, brakes, clutch, timing belt, A/C: maintenance and repair for all makes and models.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Entretien et réparation', 'Service and repairs'), 'title' => $T('Des mécaniciens pour toutes les marques', 'Mechanics for every make'), 'intro' => $T('Essence, diesel, hybride ou électrique : notre atelier est équipé pour les véhicules récents comme pour les plus anciens.', 'Gas, diesel, hybrid or electric: our shop is equipped for new and older vehicles alike.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['wrench', $T('Révision constructeur', 'Factory service'), $T('Respect du carnet d’entretien : votre garantie constructeur est préservée.', 'We follow your service schedule: your factory warranty stays valid.')],
                    ['droplet', $T('Vidange et filtres', 'Oil and filters'), $T('Huiles homologuées, filtres à huile, à air, à carburant et d’habitacle.', 'Approved oils; oil, air, fuel and cabin filters.')],
                    ['activity', $T('Diagnostic', 'Diagnostics'), $T('Valise multimarque et mise à jour des calculateurs.', 'Multi-brand scanners and control unit updates.')],
                    ['shield-check', $T('Freinage', 'Brakes'), $T('Plaquettes, disques, étriers, liquide et frein de stationnement.', 'Pads, rotors, calipers, fluid and parking brake.')],
                    ['settings', $T('Embrayage et distribution', 'Clutch and timing belt'), $T('Remplacement de kit d’embrayage, courroie ou chaîne de distribution.', 'Clutch kit, timing belt or chain replacement.')],
                    ['zap', $T('Électricité et batterie', 'Electrical and battery'), $T('Batterie, alternateur, démarreur, éclairage et équipements.', 'Battery, alternator, starter, lighting and accessories.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Hybrides et électriques', 'Hybrids and EVs'),
                    'title' => $T('Un atelier prêt pour les nouvelles motorisations', 'A shop ready for new powertrains'),
                    'html' => '<p>'.e($T('Nos techniciens sont habilités à intervenir sur les véhicules hybrides et électriques : contrôle de la batterie haute tension, système de freinage régénératif et entretien courant.', 'Our technicians are certified to work on hybrid and electric vehicles: high-voltage battery checks, regenerative braking and routine maintenance.')).'</p>',
                    'bullets' => [$T('Techniciens habilités haute tension', 'High-voltage certified technicians'), $T('Bilan de santé de la batterie', 'Battery health report'), $T('Entretien sans perte de garantie', 'Service without voiding the warranty')],
                    'photo' => ['garage-electrique', 'zap', $T('Technicien contrôlant une voiture électrique branchée à une borne dans un atelier', 'Technician checking an electric car plugged into a charger in a workshop')],
                    'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('Votre passage à l’atelier', 'Your visit'), 'title' => $T('Comment se déroule une intervention', 'How a repair works'), 'items' => [
                    [$T('Accueil', 'Check-in'), $T('Nous notons vos remarques et le kilométrage avec vous.', 'We note your concerns and the mileage with you.')],
                    [$T('Diagnostic', 'Diagnosis'), $T('Le mécanicien contrôle le véhicule et identifie la cause.', 'The mechanic inspects the car and finds the cause.')],
                    [$T('Accord', 'Approval'), $T('Vous recevez le devis par téléphone ou message et validez.', 'You get the estimate by phone or text and approve it.')],
                    [$T('Restitution', 'Pickup'), $T('Explication des travaux, pièces usagées et facture détaillée.', 'Work explained, old parts shown and itemized invoice.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'bg' => 'light', 'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact, 'outline']], 'items' => [
                    [$T('Puis-je faire entretenir ma voiture neuve chez vous ?', 'Can you service my new car?'), $T('Oui. En suivant le carnet du constructeur avec des pièces de qualité équivalente, votre garantie est préservée.', 'Yes. By following the manufacturer’s schedule with equivalent-quality parts, your warranty is preserved.')],
                    [$T('Combien de temps dure une révision ?', 'How long does a service take?'), $T('Environ deux heures. Vous pouvez patienter sur place ou récupérer votre véhicule en fin de journée.', 'About two hours. You can wait on site or pick the car up at the end of the day.')],
                    [$T('Un voyant s’est allumé, que faire ?', 'A warning light came on, what should I do?'), $T('S’il est rouge, arrêtez-vous et appelez-nous. S’il est orange, prenez rendez-vous rapidement pour un diagnostic.', 'If it is red, stop and call us. If it is amber, book a diagnosis soon.')],
                    [$T('Utilisez-vous des pièces d’origine ?', 'Do you use OEM parts?'), $T('Nous proposons des pièces d’origine ou de qualité équivalente, au choix, avec le prix de chaque option sur le devis.', 'We offer OEM or equivalent-quality parts, your choice, with both prices on the estimate.')],
                ]]),
            ]];

        $pages[] = ['key' => 'tires', 'slug' => $u('pneus', 'tires'), 'title' => $T('Pneus', 'Tires'),
            'description' => $T('Pneus été, hiver et toutes saisons, montage, équilibrage, parallélisme, réparation de crevaison et gardiennage de vos pneus.', 'Summer, winter and all-season tires, fitting, balancing, alignment, puncture repair and tire storage.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Pneus', 'Tires'), 'title' => $T('Des pneus adaptés à votre conduite', 'Tires that suit the way you drive'), 'intro' => $T('Plusieurs gammes de prix, montage rapide et conseils honnêtes.', 'Several price ranges, quick fitting and honest advice.')]),
                B::split($theme, [
                    'eyebrow' => $T('Conseil et montage', 'Advice and fitting'),
                    'title' => $T('Le bon pneu au bon prix', 'The right tire at the right price'),
                    'html' => '<p>'.e($T('Nous vous proposons trois gammes, de l’économique au premium, en fonction de votre véhicule, de votre budget et des kilomètres que vous parcourez. Le montage, l’équilibrage et la valve neuve sont inclus.', 'We offer three ranges, from budget to premium, based on your car, your budget and how far you drive. Fitting, balancing and a new valve are included.')).'</p>',
                    'bullets' => [$T('Montage en 45 minutes, sans rendez-vous le matin', 'Fitted in 45 minutes, walk-ins welcome in the morning'), $T('Recyclage des pneus usagés', 'Old tires recycled'), $T('Contrôle gratuit de la pression et de l’usure', 'Free pressure and tread check')],
                    'photo' => ['garage-pneus', 'car', $T('Mécanicien montant un pneu neuf sur une jante avec une machine de démontage', 'Mechanic mounting a new tire on a rim with a tire changer')],
                    'reverse' => true,
                ]),
                B::features($theme, ['eyebrow' => $T('Nos prestations', 'Our tire services'), 'title' => $T('Tout pour vos pneus', 'Everything for your tires'), 'cols' => 4, 'bg' => 'light', 'items' => [
                    ['car', $T('Montage', 'Fitting'), $T('Pneus été, hiver, toutes saisons, 4x4 et utilitaires.', 'Summer, winter, all-season, SUV and van tires.')],
                    ['target', $T('Parallélisme', 'Alignment'), $T('Réglage de la géométrie pour une usure régulière.', 'Wheel alignment for even tire wear.')],
                    ['wrench', $T('Crevaison', 'Punctures'), $T('Réparation par mèche ou champignon quand c’est possible.', 'Plug or patch repair when possible.')],
                    ['warehouse', $T('Gardiennage', 'Storage'), $T('Stockage de vos pneus de saison entre deux changements.', 'Seasonal tire storage between changeovers.')],
                ]]),
                M::table($theme, ['title' => $T('Prix indicatifs du montage', 'Indicative fitting prices'), 'rows' => [
                    [$T('Prestation', 'Service'), $T('Prix', 'Price')],
                    [$T('Montage et équilibrage, par pneu', 'Fitting and balancing, per tire'), $T('15 €', '$18')],
                    [$T('Parallélisme', 'Wheel alignment'), $T('69 €', '$79')],
                    [$T('Réparation de crevaison', 'Puncture repair'), $T('25 €', '$29')],
                    [$T('Gardiennage, par saison', 'Storage, per season'), $T('40 €', '$45')],
                ], 'note' => $T('Prix TTC, hors fourniture du pneu.', 'Prices include tax, tires not included.')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'bodywork', 'slug' => $u('carrosserie', 'body-shop'), 'title' => $T('Carrosserie', 'Body shop'),
            'description' => $T('Réparation après accident, débosselage sans peinture, rayures, pare-brise et peinture en cabine. Prise en charge avec votre assurance.', 'Collision repair, paintless dent removal, scratches, windshields and booth painting. We work directly with your insurer.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Carrosserie et peinture', 'Body and paint'), 'title' => $T('Comme neuve après un accrochage', 'Like new after a fender bender'), 'intro' => $T('Petits chocs ou gros sinistre : nous réparons et gérons les démarches avec votre assureur.', 'Small dents or major damage: we repair your car and handle the paperwork with your insurer.')]),
                B::gallery($theme, ['eyebrow' => $T('Avant / après', 'Before / after'), 'title' => $T('Nos réparations en images', 'Our repairs in pictures'), 'cols' => 2, 'photos' => [
                    ['garage-avant-carrosserie', 'image', $T('Portière de voiture enfoncée et rayée après un accrochage', 'Dented, scratched car door after a minor collision')],
                    ['garage-apres-carrosserie', 'image', $T('Portière de voiture réparée et repeinte, brillante sous les lumières de l’atelier', 'Repaired, repainted car door gleaming under shop lights')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Nos prestations', 'Our body services'), 'title' => $T('Un savoir-faire complet', 'Full body-shop expertise'), 'bg' => 'light', 'items' => [
                    ['hammer', $T('Débosselage', 'Dent removal'), $T('Débosselage sans peinture pour les petits impacts et la grêle.', 'Paintless dent repair for small dings and hail.')],
                    ['paintbrush', $T('Peinture en cabine', 'Booth painting'), $T('Teinte reproduite à l’identique et vernis haute résistance.', 'Exact color match and durable clear coat.')],
                    ['shield', $T('Vitrage', 'Glass'), $T('Réparation d’impact et remplacement de pare-brise.', 'Chip repair and windshield replacement.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Sinistre', 'Insurance claim'), 'title' => $T('Après un accident, on s’occupe de tout', 'After an accident, we take care of everything'), 'items' => [
                    [$T('Déclaration', 'Report'), $T('Déclarez le sinistre à votre assureur et prévenez-nous.', 'Report the claim to your insurer and let us know.')],
                    [$T('Expertise', 'Assessment'), $T('Nous accueillons l’expert et chiffrons la réparation.', 'We meet the adjuster and price the repair.')],
                    [$T('Réparation', 'Repair'), $T('Un véhicule de prêt vous est proposé pendant les travaux.', 'A loaner car is offered during the work.')],
                    [$T('Restitution', 'Pickup'), $T('Véhicule lavé, contrôlé et rendu à la date convenue.', 'Car washed, checked and returned on the agreed date.')],
                ]]),
                B::cta($theme, ['title' => $T('Un devis carrosserie ?', 'Need a body repair estimate?'), 'text' => $T('Envoyez-nous quelques photos des dégâts : nous vous répondons avec une première estimation.', 'Send us a few photos of the damage: we reply with a first estimate.'),
                    'buttons' => [[$T('Demander un devis', 'Request an estimate'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'pricing', 'slug' => $u('tarifs', 'pricing'), 'title' => $T('Tarifs', 'Pricing'),
            'description' => $T('Nos tarifs forfaitaires : vidange, révision, freins, distribution, climatisation et diagnostic. Prix TTC, pièces et main-d’œuvre comprises.', 'Our flat-rate prices: oil change, service, brakes, timing belt, A/C and diagnostics. All-in prices, parts and labor included.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Tarifs', 'Pricing'), 'title' => $T('Des forfaits clairs, sans surprise', 'Clear flat rates, no surprises'), 'intro' => $T('Le prix final dépend de votre modèle : il est toujours confirmé par un devis avant l’intervention.', 'The final price depends on your model: it is always confirmed by an estimate before any work.')]),
                M::table($theme, ['title' => $T('Nos forfaits', 'Our flat rates'), 'rows' => [
                    [$T('Prestation', 'Service'), $T('Citadine', 'Compact'), $T('Berline / SUV', 'Sedan / SUV')],
                    [$T('Vidange et filtre à huile', 'Oil and filter change'), $T('79 €', '$69'), $T('99 €', '$89')],
                    [$T('Révision complète', 'Full service'), $T('189 €', '$199'), $T('249 €', '$269')],
                    [$T('Plaquettes de frein avant', 'Front brake pads'), $T('149 €', '$159'), $T('189 €', '$199')],
                    [$T('Kit de distribution', 'Timing belt kit'), $T('459 €', '$499'), $T('649 €', '$699')],
                    [$T('Recharge de climatisation', 'A/C recharge'), $T('89 €', '$99'), $T('99 €', '$109')],
                    [$T('Diagnostic électronique', 'Computer diagnostics'), $T('49 €', '$59'), $T('49 €', '$59')],
                ], 'note' => $T('Prix TTC indicatifs, pièces et main-d’œuvre comprises. Le diagnostic est déduit si vous faites réaliser la réparation chez nous.', 'Indicative prices including tax, parts and labor. The diagnostic fee is waived if we do the repair.')]),
                B::pillars($theme, ['eyebrow' => $T('Inclus', 'Included'), 'title' => $T('Avec chaque intervention', 'With every job'), 'bg' => 'light', 'items' => [
                    ['clipboard-check', $T('Contrôle de sécurité', 'Safety check'), $T('Pneus, freins, éclairage et niveaux vérifiés gratuitement.', 'Tires, brakes, lights and fluids checked for free.')],
                    ['award', $T('Garantie 12 mois', '12-month warranty'), $T('Pièces et main-d’œuvre garanties un an.', 'Parts and labor guaranteed for one year.')],
                    ['receipt', $T('Facture détaillée', 'Itemized invoice'), $T('Chaque pièce et chaque temps de travail indiqués.', 'Every part and labor time listed.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Paiement et devis', 'Payment and estimates'), 'items' => [
                    [$T('Le devis est-il payant ?', 'Do you charge for estimates?'), $T('Non, le devis est gratuit. Seul le diagnostic électronique est facturé, et il est déduit si vous faites la réparation chez nous.', 'No, estimates are free. Only computer diagnostics are billed, and that fee is waived if we do the repair.')],
                    [$T('Comment puis-je payer ?', 'How can I pay?'), $T('Par carte bancaire, virement ou paiement mobile. Un paiement en plusieurs fois est possible pour les grosses réparations.', 'By card, bank transfer or mobile payment. Installments are available for major repairs.')],
                    [$T('Et si le prix change pendant la réparation ?', 'What if the price changes during the repair?'), $T('Nous vous appelons avant tout travail supplémentaire. Rien n’est facturé sans votre accord.', 'We call you before any extra work. Nothing is billed without your approval.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'used', 'slug' => $u('occasions', 'used-cars'), 'title' => $T('Véhicules d’occasion', 'Used cars'),
            'description' => $T('Véhicules d’occasion contrôlés et garantis 12 mois, reprise de votre ancien véhicule et accompagnement pour l’achat.', 'Inspected used cars with a 12-month warranty, trade-in of your current car and help with your purchase.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Occasions', 'Used cars'), 'title' => $T('Des occasions contrôlées et garanties', 'Inspected, guaranteed used cars'), 'intro' => $T('Chaque véhicule passe un contrôle de 100 points dans notre atelier avant la vente.', 'Every car passes a 100-point inspection in our shop before it goes on sale.')]),
                B::cards($theme, ['more' => $T('Je suis intéressé', 'I’m interested'), 'items' => [
                    ['garage-occasion-citadine', 'car', $T('Citadine 5 portes essence', '5-door gas hatchback'), $T('2020, 42 000 km, boîte manuelle, garantie 12 mois. 10 900 €.', '2020, 26,000 miles, manual, 12-month warranty. $11,900.'), $contact],
                    ['garage-occasion-suv', 'car', $T('SUV compact hybride', 'Compact hybrid SUV'), $T('2021, 35 000 km, boîte automatique, caméra de recul. 21 500 €.', '2021, 22,000 miles, automatic, backup camera. $23,500.'), $contact],
                    ['garage-occasion-break', 'car', $T('Break familial diesel', 'Diesel family wagon'), $T('2019, 78 000 km, grand coffre, régulateur de vitesse. 13 900 €.', '2019, 48,000 miles, large trunk, cruise control. $14,900.'), $contact],
                    ['garage-occasion-electrique', 'zap', $T('Citadine électrique', 'Electric city car'), $T('2022, 18 000 km, autonomie 300 km, câble de recharge fourni. 17 900 €.', '2022, 11,000 miles, 185-mile range, charging cable included. $19,500.'), $contact],
                    ['garage-occasion-utilitaire', 'truck', $T('Fourgon utilitaire', 'Cargo van'), $T('2020, 95 000 km, 3 places, cloison de séparation. 15 400 € HT.', '2020, 59,000 miles, 3 seats, bulkhead. $16,900 plus tax.'), $contact],
                    ['garage-occasion-berline', 'car', $T('Berline confort', 'Comfort sedan'), $T('2018, 88 000 km, sièges chauffants, entretien complet. 12 500 €.', '2018, 55,000 miles, heated seats, full service history. $13,500.'), $contact],
                ]]),
                B::features($theme, ['eyebrow' => $T('Nos garanties', 'Our guarantees'), 'title' => $T('Acheter en confiance', 'Buy with confidence'), 'bg' => 'light', 'cols' => 4, 'items' => [
                    ['clipboard-check', $T('Contrôle de 100 points', '100-point inspection'), $T('Mécanique, carrosserie, électronique et essai routier.', 'Mechanical, body, electronics and road test.')],
                    ['award', $T('Garantie 12 mois', '12-month warranty'), $T('Pièces et main-d’œuvre, prolongeable sur demande.', 'Parts and labor, extendable on request.')],
                    ['file-text', $T('Historique vérifié', 'Verified history'), $T('Kilométrage contrôlé et carnet d’entretien fourni.', 'Mileage checked and service history provided.')],
                    ['refresh-cw', $T('Reprise', 'Trade-in'), $T('Estimation gratuite de votre véhicule actuel.', 'Free appraisal of your current car.')],
                ]]),
                B::cta($theme, ['title' => $T('Vous cherchez un modèle précis ?', 'Looking for a specific model?'), 'text' => $T('Dites-nous votre budget et vos critères : nous recherchons le véhicule pour vous.', 'Tell us your budget and what you need: we will find the car for you.'),
                    'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'accent'], [$T('Nos services', 'Our services'), $services, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'tips', 'slug' => $u('conseils-auto', 'car-tips'), 'title' => $T('Conseils auto', 'Car tips'),
            'description' => $T('Les conseils de nos mécaniciens : voyants du tableau de bord, usure des pneus, entretien d’une voiture hybride et bons réflexes.', 'Advice from our mechanics: dashboard warning lights, tire wear, hybrid car maintenance and good habits.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Conseils auto', 'Car tips'), 'title' => $T('Le coin des mécaniciens', 'From our mechanics'), 'intro' => $T('Des explications simples pour mieux entretenir votre véhicule.', 'Plain explanations to help you look after your car.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire le conseil', 'Read the tip')]),
                B::pillars($theme, ['eyebrow' => $T('Chaque mois', 'Every month'), 'title' => $T('Trois vérifications à faire vous-même', 'Three checks you can do yourself'), 'bg' => 'light', 'items' => [
                    ['car', $T('Pression des pneus', 'Tire pressure'), $T('À froid, selon l’étiquette située sur la portière.', 'When cold, using the label on the door frame.')],
                    ['droplet', $T('Niveau d’huile', 'Oil level'), $T('Moteur froid, voiture à plat, entre les repères mini et maxi.', 'Engine cold, car level, between the min and max marks.')],
                    ['lightbulb', $T('Éclairage', 'Lights'), $T('Feux, clignotants et feux stop, avec l’aide d’un proche.', 'Headlights, turn signals and brake lights, with a helper.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => $T('Rendez-vous', 'Book a visit'),
            'description' => $T('Prenez rendez-vous à l’atelier ou demandez un devis. Réponse rapide, véhicule de prêt sur réservation et dépôt des clés en dehors des horaires.', 'Book a shop appointment or request an estimate. Quick reply, loaner car on request and after-hours key drop.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Rendez-vous', 'Appointments'), 'title' => $T('Réservez votre créneau', 'Book your slot'), 'intro' => $T('Indiquez votre véhicule, l’intervention souhaitée et vos disponibilités : nous confirmons le rendez-vous dans la journée.', 'Tell us your car, the work needed and when you’re available: we confirm the same day.')]),
                B::contact($theme, [
                    'title' => $T('Le garage', 'The shop'), 'intro' => $T('Précisez la marque, le modèle, l’année et le kilométrage pour un devis plus précis.', 'Include the make, model, year and mileage for a more accurate estimate.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h – 18 h 30 ; samedi, 8 h 30 – 13 h', 'Monday – Friday, 8 am – 6:30 pm; Saturday, 8:30 am – 1 pm')],
                    ],
                    'social' => ['facebook', 'instagram', 'whatsapp'],
                    'subject' => true,
                    'button' => $T('Demander un rendez-vous', 'Request an appointment'),
                    'bg' => 'white',
                ]),
                B::schedule($theme, $hours),
                B::steps($theme, ['eyebrow' => $T('Le jour du rendez-vous', 'On the day'), 'title' => $T('Simple et rapide', 'Quick and easy'), 'items' => [
                    [$T('Déposez', 'Drop off'), $T('À l’accueil ou dans la boîte à clés, avec vos papiers.', 'At the front desk or in the key box, with your papers.')],
                    [$T('Validez', 'Approve'), $T('Nous vous appelons avec le diagnostic et le devis.', 'We call you with the diagnosis and estimate.')],
                    [$T('Récupérez', 'Pick up'), $T('Un message vous prévient dès que la voiture est prête.', 'A text tells you as soon as the car is ready.')],
                ]]),
            ]];

        $blog = [
            'base' => $u('conseils-auto', 'car-tips'),
            'title' => $T('Conseils auto', 'Car tips'),
            'categories' => ['entretien' => $T('Entretien', 'Maintenance'), 'securite' => $T('Sécurité', 'Safety'), 'hybride' => $T('Hybride et électrique', 'Hybrid and electric')],
            'posts' => [
                ['key' => 'voyants', 'slug' => $u('comprendre-les-voyants', 'dashboard-warning-lights'), 'category' => 'securite', 'tags' => [$T('Voyants', 'Warning lights')],
                    'title' => $T('Voyants du tableau de bord : lesquels sont urgents ?', 'Dashboard warning lights: which ones are urgent?'),
                    'excerpt' => $T('Rouge, orange, vert : comment réagir selon la couleur et le symbole.', 'Red, amber, green: how to react depending on the color and symbol.'),
                    'photo' => ['garage-blog-voyants', 'triangle-alert', $T('Tableau de bord de voiture avec plusieurs voyants allumés', 'Car dashboard with several warning lights on')],
                    'heading' => $T('La règle des couleurs', 'The color rule'),
                    'body' => [
                        $T('Un voyant rouge signale un danger : arrêtez-vous dès que possible en sécurité et coupez le moteur.', 'A red light signals danger: stop as soon as it is safe and switch off the engine.'),
                        $T('Un voyant orange indique une anomalie : vous pouvez rouler prudemment, mais faites contrôler le véhicule rapidement.', 'An amber light indicates a fault: you can drive carefully, but have the car checked soon.'),
                        $T('Les voyants verts ou bleus sont simplement informatifs. En cas de doute, consultez la notice ou appelez l’atelier.', 'Green or blue lights are just informative. If in doubt, check the owner’s manual or call the shop.'),
                    ]],
                ['key' => 'pneus', 'slug' => $u('quand-changer-ses-pneus', 'when-to-replace-tires'), 'category' => 'entretien', 'tags' => [$T('Pneus', 'Tires')],
                    'title' => $T('Quand faut-il changer ses pneus ?', 'When should you replace your tires?'),
                    'excerpt' => $T('Témoins d’usure, âge du pneu, déformations : les signes à surveiller.', 'Wear bars, tire age, bulges: the signs to watch for.'),
                    'photo' => ['garage-blog-pneus', 'car', $T('Gros plan sur la bande de roulement usée d’un pneu de voiture', 'Close-up of a worn car tire tread')],
                    'quote' => $T('« Les pneus sont le seul contact entre votre voiture et la route. »', '“Tires are the only contact between your car and the road.”'),
                    'body' => [
                        $T('La profondeur des sculptures ne doit pas descendre sous le témoin d’usure moulé dans la bande de roulement.', 'Tread depth must not fall below the wear bar molded into the tread.'),
                        $T('Au-delà de cinq à six ans, la gomme durcit et adhère moins, même si le pneu semble peu usé.', 'After five to six years, rubber hardens and grips less, even if the tire looks barely worn.'),
                        $T('Une hernie, une coupure sur le flanc ou une usure irrégulière imposent un contrôle immédiat.', 'A bulge, a sidewall cut or uneven wear calls for an immediate check.'),
                    ]],
                ['key' => 'hybride', 'slug' => $u('entretenir-une-hybride', 'servicing-a-hybrid'), 'category' => 'hybride', 'tags' => [$T('Hybride', 'Hybrid')],
                    'title' => $T('Entretenir une voiture hybride : ce qui change', 'Servicing a hybrid car: what’s different'),
                    'excerpt' => $T('Moins d’usure des freins, une batterie à surveiller : l’entretien d’une hybride expliqué.', 'Less brake wear, a battery to monitor: hybrid servicing explained.'),
                    'photo' => ['garage-blog-hybride', 'zap', $T('Compartiment moteur d’une voiture hybride ouvert dans un atelier', 'Open engine bay of a hybrid car in a workshop')],
                    'heading' => $T('Les points de contrôle', 'What to check'),
                    'body' => [
                        $T('Le moteur thermique d’une hybride demande toujours vidange et filtres, selon le carnet du constructeur.', 'A hybrid’s gas engine still needs oil and filter changes on the manufacturer’s schedule.'),
                        $T('Grâce au freinage régénératif, les plaquettes s’usent moins vite, mais le liquide de frein doit être remplacé régulièrement.', 'Thanks to regenerative braking, pads wear more slowly, but brake fluid still needs regular replacement.'),
                        $T('Un bilan de la batterie haute tension, réalisé par un technicien habilité, permet d’anticiper son vieillissement.', 'A high-voltage battery check by a certified technician helps anticipate its aging.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Entretien', 'Service'), 'url' => $services],
                    ['label' => $T('Pneus', 'Tires'), 'url' => $tires],
                    ['label' => $T('Carrosserie', 'Body shop'), 'url' => $bodywork],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('Occasions', 'Used cars'), 'url' => $used],
                    ['label' => $T('Conseils', 'Tips'), 'url' => $tips],
                ],
                'cta_label' => $T('Prendre rendez-vous', 'Book now'),
                'cta_url' => $contact,
                'footer_text' => $T('Garage toutes marques : entretien, diagnostic, pneus, carrosserie et véhicules d’occasion garantis. Devis gratuit avant chaque intervention.', 'All-makes auto repair: servicing, diagnostics, tires, body work and guaranteed used cars. Free estimate before every job.'),
                'footer_links' => [
                    ['label' => $T('Entretien et réparation', 'Service and repairs'), 'url' => $services],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('Véhicules d’occasion', 'Used cars'), 'url' => $used],
                    ['label' => $T('Rendez-vous', 'Book a visit'), 'url' => $contact],
                ],
            ],
        ];
    },
];
