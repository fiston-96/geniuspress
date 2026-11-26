<?php

/*
 * Pack « Artisan du bâtiment multiservice » : 8 pages, en français et en anglais.
 * Plomberie, électricité, menuiserie, rénovation : interventions, devis gratuit,
 * dépannage urgent, réalisations avant/après, garanties, zones d'intervention.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'entreprise). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Artisans et BTP', 'en' => 'Trades and construction'],
        'icon' => 'hammer',
        'keywords' => ['artisan', 'plombier', 'plomberie', 'électricien', 'électricité', 'menuisier', 'menuiserie', 'rénovation', 'dépannage', 'travaux', 'multiservice', 'bricolage', 'devis', 'handyman', 'plumber', 'plumbing', 'electrician', 'carpenter', 'carpentry', 'renovation', 'repairs', 'home improvement', 'contractor'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $services = $u('/interventions', '/services');
        $emergency = $u('/depannage-urgent', '/emergency-repairs');
        $work = $u('/realisations', '/our-work');
        $pricing = $u('/tarifs-devis', '/pricing');
        $about = $u('/entreprise', '/about-us');
        $tips = $u('/conseils', '/tips');
        $contact = '/contact';
        $cur = $T('€', '$');
        $phone = ['+00 0 00 00 00 00', 'tel:+000000000000'];

        $trades = [
            ['droplet', $T('Plomberie', 'Plumbing'), $T('Fuites, débouchage, chauffe-eau, robinetterie et création de salles de bains.', 'Leaks, drain unblocking, water heaters, faucets and new bathrooms.')],
            ['zap', $T('Électricité', 'Electrical'), $T('Mise aux normes, tableau électrique, prises, éclairage et recherche de panne.', 'Code upgrades, electrical panels, outlets, lighting and fault finding.')],
            ['hammer', $T('Menuiserie', 'Carpentry'), $T('Portes, fenêtres, placards, parquet et agencements sur mesure.', 'Doors, windows, closets, wood floors and custom fittings.')],
            ['paintbrush', $T('Rénovation', 'Renovation'), $T('Peinture, carrelage, plâtrerie et rénovation complète de pièces.', 'Painting, tiling, drywall and complete room renovations.')],
        ];
        $testimonials = ['eyebrow' => $T('Avis clients', 'Customer reviews'), 'title' => $T('Ils nous ont confié leurs travaux', 'They trusted us with their projects'), 'rating' => 5, 'items' => [
            [$T('Fuite sous l’évier un dimanche matin : le technicien était là en une heure, propre et efficace. Le prix annoncé a été respecté.', 'A leak under the sink on a Sunday morning: the technician arrived within an hour, tidy and efficient. The quoted price was honored.'), '[Prénom Nom]', $T('Particulier, dépannage plomberie', 'Homeowner, plumbing repair')],
            [$T('Salle de bains entièrement refaite en dix jours. Un seul interlocuteur pour la plomberie, l’électricité et le carrelage : un vrai confort.', 'Bathroom completely redone in ten days. One contact for plumbing, electrical and tiling: a real relief.'), '[Prénom Nom]', $T('Propriétaire, rénovation salle de bains', 'Owner, bathroom remodel')],
            [$T('Nous faisons appel à eux pour l’entretien de nos logements locatifs. Devis rapides, travail soigné, factures claires.', 'We rely on them to maintain our rental units. Quick quotes, careful work, clear invoices.'), '[Prénom Nom]', $T('Gestionnaire immobilier', 'Property manager')],
        ]];
        $cta = ['title' => $T('Un projet ou une panne ?', 'A project or a breakdown?'), 'text' => $T('Décrivez-nous vos travaux : nous vous rappelons dans la journée et le devis est gratuit, sans engagement.', 'Tell us about your job: we call you back the same day and the quote is free, with no obligation.'),
            'buttons' => [[$T('Demander un devis gratuit', 'Get a free quote'), $contact, 'accent'], [$T('Dépannage urgent', 'Emergency repairs'), $emergency, 'outline-light']]];
        $zones = ['eyebrow' => $T('Zones d’intervention', 'Service area'), 'title' => $T('Où intervenons-nous ?', 'Where do we work?'), 'intro' => $T('Nos véhicules équipés partent chaque matin de l’atelier. Hors zone, nous étudions chaque demande.', 'Our equipped vans leave the workshop every morning. Outside our area, we consider every request.'), 'groups' => [
            [$T('Zone principale', 'Main area'), [
                ['map-pin', $T('[Ville] et communes voisines', '[City] and nearby towns')],
                ['timer', $T('Intervention en moins d’une heure pour les urgences', 'Under one hour for emergencies')],
                ['truck', $T('Déplacement inclus dans le prix', 'Travel included in the price')],
            ]],
            [$T('Zone élargie', 'Extended area'), [
                ['map', $T('Jusqu’à 40 km autour de l’atelier', 'Up to 25 miles from the workshop')],
                ['calendar-check', $T('Rendez-vous sous 48 heures', 'Appointment within 48 hours')],
                ['receipt', $T('Forfait de déplacement annoncé au devis', 'Travel fee stated in the quote')],
            ]],
        ]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Plomberie, électricité, menuiserie et rénovation : un artisan multiservice, devis gratuit, dépannage 7 j/7 et travaux garantis.', 'Plumbing, electrical, carpentry and renovation: one multi-trade contractor, free quotes, 7-day emergency repairs and guaranteed work.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Artisan multiservice du bâtiment', 'Multi-trade home contractor'),
                    'title' => $T('Vos travaux entre de bonnes mains', 'Your home in good hands'),
                    'text' => $T('Plomberie, électricité, menuiserie et rénovation : une seule équipe qualifiée pour dépanner, réparer et embellir votre logement, avec un devis gratuit et un prix tenu.', 'Plumbing, electrical, carpentry and renovation: one qualified team to fix, repair and improve your home, with a free quote and a price we stick to.'),
                    'buttons' => [[$T('Devis gratuit', 'Free quote'), $contact], [$T('Dépannage urgent', 'Emergency repairs'), $emergency]],
                    'photo' => ['artisan-accueil', 'wrench', $T('Artisan en tenue de travail réparant la tuyauterie sous un évier de cuisine', 'Tradesperson in work clothes repairing the pipes under a kitchen sink')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos métiers', 'Our trades'), 'title' => $T('Quatre métiers, un seul interlocuteur', 'Four trades, one point of contact'), 'cols' => 4, 'items' => array_map(fn ($i) => [...$i, $services, $T('En savoir plus', 'Learn more')], $trades)]),
                B::split($theme, [
                    'eyebrow' => $T('Pourquoi nous choisir', 'Why choose us'),
                    'title' => $T('Un travail propre, un prix annoncé', 'Clean work, an upfront price'),
                    'html' => '<p>'.e($T('Nous nous déplaçons pour évaluer les travaux, nous vous remettons un devis détaillé et nous nous y tenons. Le chantier est protégé, rangé chaque soir et nettoyé à la fin.', 'We come to assess the job, give you a detailed quote and stick to it. The work area is protected, tidied every evening and cleaned at the end.')).'</p>',
                    'bullets' => [$T('Devis gratuit et détaillé sous 48 heures', 'Free, itemized quote within 48 hours'), $T('Artisans qualifiés et assurés', 'Qualified, insured tradespeople'), $T('Travaux garantis, pièces et main-d’œuvre', 'Work guaranteed, parts and labor'), $T('Chantier protégé et nettoyé', 'Work area protected and cleaned')],
                    'buttons' => [[$T('Nos garanties', 'Our guarantees'), $about, 'outline']],
                    'photo' => ['artisan-devis', 'clipboard-check', $T('Artisan présentant un devis à une cliente dans sa cuisine', 'Contractor going over a written quote with a homeowner in her kitchen')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::stats($theme, ['title' => $T('Notre activité en chiffres', 'Our work in numbers'), 'items' => [
                    [15, '', $T('ans de métier', 'years in the trade')],
                    [3200, '+', $T('interventions réalisées', 'jobs completed')],
                    [60, ' min', $T('délai moyen en urgence', 'average emergency response')],
                    [96, ' %', $T('clients qui nous recommandent', 'customers who recommend us')],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('Avant / après', 'Before / after'), 'title' => $T('Quelques transformations récentes', 'A few recent makeovers'), 'cols' => 2, 'photos' => [
                    ['artisan-avant-salle-de-bains', 'image', $T('Salle de bains ancienne avec carrelage usé et baignoire démodée, avant travaux', 'Dated bathroom with worn tiles and an old bathtub, before renovation')],
                    ['artisan-apres-salle-de-bains', 'image', $T('Salle de bains rénovée avec douche à l’italienne et carrelage clair', 'Renovated bathroom with a walk-in shower and light tiles')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Comment ça se passe', 'How it works'), 'title' => $T('De votre appel à la fin du chantier', 'From your call to the finished job'), 'items' => [
                    [$T('Appel', 'Call'), $T('Vous décrivez le problème ou le projet, photos à l’appui si possible.', 'You describe the problem or project, with photos if possible.')],
                    [$T('Visite et devis', 'Visit and quote'), $T('Nous évaluons sur place et remettons un devis gratuit et détaillé.', 'We assess on site and give you a free, itemized quote.')],
                    [$T('Travaux', 'The work'), $T('Intervention à la date convenue, chantier protégé et suivi quotidien.', 'Work on the agreed date, protected site and daily updates.')],
                    [$T('Réception', 'Handover'), $T('Contrôle avec vous, nettoyage, facture et garantie remises.', 'Final check with you, cleanup, invoice and warranty handed over.')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('interventions', 'services'), 'title' => $T('Nos interventions', 'Our services'),
            'description' => $T('Plomberie, électricité, menuiserie, peinture, carrelage et rénovation complète : toutes nos interventions pour la maison et les locaux.', 'Plumbing, electrical, carpentry, painting, tiling and full renovations: all our services for homes and business premises.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Interventions', 'Services'), 'title' => $T('Du petit dépannage à la rénovation complète', 'From small repairs to full renovations'), 'intro' => $T('Particuliers, propriétaires bailleurs, commerces et bureaux : nous intervenons sur tous les corps d’état du second œuvre.', 'Homeowners, landlords, shops and offices: we handle every interior finishing trade.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['droplet', $T('Plomberie et sanitaires', 'Plumbing and fixtures'), $T('Recherche de fuite, débouchage, remplacement de chauffe-eau, WC, mitigeurs et évacuations.', 'Leak detection, drain cleaning, water heater replacement, toilets, mixer taps and drains.')],
                    ['zap', $T('Électricité', 'Electrical'), $T('Diagnostic, remplacement de tableau, ajout de prises et circuits, éclairage intérieur et extérieur.', 'Inspections, panel replacement, new outlets and circuits, indoor and outdoor lighting.')],
                    ['thermometer', $T('Chauffage et eau chaude', 'Heating and hot water'), $T('Installation et entretien de chauffe-eau, radiateurs et petits systèmes de chauffage.', 'Installation and servicing of water heaters, radiators and small heating systems.')],
                    ['hammer', $T('Menuiserie', 'Carpentry'), $T('Pose de portes et fenêtres, placards sur mesure, escaliers, parquet et réglages.', 'Door and window fitting, custom closets, stairs, wood floors and adjustments.')],
                    ['paintbrush', $T('Peinture et finitions', 'Painting and finishes'), $T('Préparation des murs, enduits, peinture intérieure, papier peint et boiseries.', 'Wall prep, plastering, interior painting, wallpaper and trim.')],
                    ['layout-grid', $T('Carrelage et sols', 'Tiling and flooring'), $T('Carrelage mural et sol, faïence, ragréage, sols stratifiés et vinyles.', 'Wall and floor tiles, backsplashes, leveling, laminate and vinyl floors.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Rénovation clé en main', 'Turnkey renovation'),
                    'title' => $T('Cuisine, salle de bains ou pièce entière', 'Kitchen, bathroom or a whole room'),
                    'html' => '<p>'.e($T('Pour une rénovation, nous coordonnons tous les métiers : dépose, plomberie, électricité, plâtrerie, carrelage et peinture. Vous avez un seul devis, un seul planning et un seul responsable de chantier.', 'For a renovation, we coordinate every trade: removal, plumbing, electrical, drywall, tiling and painting. You get one quote, one schedule and one site lead.')).'</p>',
                    'bullets' => [$T('Conseils sur les matériaux et les finitions', 'Advice on materials and finishes'), $T('Planning écrit, jour par jour', 'Written, day-by-day schedule'), $T('Évacuation des gravats incluse', 'Debris removal included')],
                    'buttons' => [[$T('Voir nos réalisations', 'See our work'), $work]],
                    'photo' => ['artisan-renovation', 'paintbrush', $T('Cuisine en cours de rénovation avec meubles neufs en cours de pose', 'Kitchen being renovated with new cabinets being installed')],
                    'bg' => 'light',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Notre exigence', 'Our standards'), 'title' => $T('Ce qui ne change jamais, quel que soit le chantier', 'What never changes, whatever the job'), 'items' => [
                    ['shield-check', $T('Normes respectées', 'Code-compliant'), $T('Installations conformes aux normes électriques et sanitaires en vigueur.', 'Installations that meet current electrical and plumbing codes.')],
                    ['box', $T('Matériel de qualité', 'Quality materials'), $T('Des marques reconnues, avec la garantie du fabricant.', 'Trusted brands, backed by the manufacturer’s warranty.')],
                    ['sparkles', $T('Chantier propre', 'Tidy site'), $T('Bâches, protections et nettoyage complet avant notre départ.', 'Drop cloths, protection and a full cleanup before we leave.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur nos interventions', 'Questions about our services'), 'intro' => $T('Vous ne trouvez pas la réponse ? Appelez-nous, nous vous conseillons gratuitement.', 'Can’t find your answer? Call us, our advice is free.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Intervenez-vous pour les petits travaux ?', 'Do you take on small jobs?'), $T('Oui. Un robinet qui goutte, une prise à remplacer ou une porte à régler : aucun travail n’est trop petit.', 'Yes. A dripping faucet, an outlet to replace or a door to adjust: no job is too small.')],
                    [$T('Fournissez-vous le matériel ?', 'Do you supply the materials?'), $T('Oui, nous le fournissons au prix indiqué sur le devis. Vous pouvez aussi fournir vos propres équipements.', 'Yes, at the price shown on the quote. You can also supply your own fixtures.')],
                    [$T('Travaillez-vous pour les professionnels ?', 'Do you work for businesses?'), $T('Oui : commerces, bureaux, syndics et propriétaires bailleurs, avec des contrats d’entretien sur demande.', 'Yes: shops, offices, property managers and landlords, with maintenance contracts on request.')],
                    [$T('Faut-il être présent pendant les travaux ?', 'Do I need to be home during the work?'), $T('Pas forcément. Nous convenons ensemble de la remise des clés et vous envoyons des photos à chaque étape.', 'Not necessarily. We agree on key handover together and send you photos at each stage.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'emergency', 'slug' => $u('depannage-urgent', 'emergency-repairs'), 'title' => $T('Dépannage urgent', 'Emergency repairs'),
            'description' => $T('Fuite d’eau, panne de courant, porte bloquée : dépannage 7 j/7 en moins d’une heure dans notre zone, avec un tarif annoncé avant le déplacement.', 'Water leak, power outage, jammed door: 7-day emergency repairs in under an hour in our area, with the price stated before we travel.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Urgences 7 j/7', '7-day emergencies'), 'title' => $T('Une urgence ? Nous arrivons.', 'An emergency? We’re on our way.'), 'intro' => $T('Un technicien d’astreinte répond tous les jours, de 7 h à 22 h, et se déplace en moins d’une heure dans notre zone principale.', 'An on-call technician answers every day from 7 am to 10 pm and arrives within an hour in our main area.')]),
                B::features($theme, ['eyebrow' => $T('Nos dépannages', 'What we fix'), 'title' => $T('Les urgences que nous traitons', 'The emergencies we handle'), 'cols' => 4, 'items' => [
                    ['droplet', $T('Fuite ou dégât des eaux', 'Leak or water damage'), $T('Recherche et réparation de fuite, canalisation percée, chauffe-eau qui fuit.', 'Finding and fixing leaks, burst pipes, leaking water heaters.')],
                    ['zap', $T('Panne électrique', 'Power failure'), $T('Disjoncteur qui saute, court-circuit, prise ou tableau endommagé.', 'Tripping breakers, short circuits, damaged outlets or panels.')],
                    ['refresh-cw', $T('Canalisation bouchée', 'Blocked drain'), $T('Évier, douche ou WC bouché : débouchage manuel ou mécanique.', 'Clogged sink, shower or toilet: manual or machine unblocking.')],
                    ['key', $T('Porte ou fenêtre bloquée', 'Jammed door or window'), $T('Serrure cassée, porte qui ne ferme plus, vitre brisée à sécuriser.', 'Broken lock, door that won’t close, broken pane to secure.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Tarif annoncé avant le départ', 'Price stated before we leave'),
                    'title' => $T('Pas de mauvaise surprise, même en urgence', 'No nasty surprises, even in an emergency'),
                    'html' => '<p>'.e($T('Au téléphone, nous vous indiquons le prix du déplacement et de la première heure. Sur place, le technicien confirme le montant avant de commencer : vous restez libre d’accepter ou non.', 'On the phone, we tell you the price of the call-out and the first hour. On site, the technician confirms the amount before starting: you are always free to say no.')).'</p>',
                    'bullets' => [$T('Déplacement et première heure annoncés', 'Call-out and first hour stated upfront'), $T('Véhicule équipé des pièces courantes', 'Van stocked with common parts'), $T('Facture remise sur place', 'Invoice handed over on site')],
                    'buttons' => [[$T('Appeler maintenant', 'Call now'), $phone[1]]],
                    'photo' => ['artisan-urgence', 'truck', $T('Technicien sortant sa caisse à outils d’une camionnette de dépannage', 'Technician taking his toolbox out of a service van')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('En attendant notre arrivée', 'While you wait for us'), 'title' => $T('Les bons réflexes', 'What to do right away'), 'items' => [
                    [$T('Couper', 'Shut off'), $T('Fermez le robinet d’arrivée d’eau ou coupez le disjoncteur général.', 'Close the main water valve or switch off the main breaker.')],
                    [$T('Protéger', 'Protect'), $T('Éloignez les objets de valeur et épongez pour limiter les dégâts.', 'Move valuables away and mop up to limit the damage.')],
                    [$T('Photographier', 'Take photos'), $T('Prenez des photos : elles serviront pour votre assurance.', 'Take photos: they will help with your insurance claim.')],
                    [$T('Nous appeler', 'Call us'), $T('Décrivez la situation : nous vous guidons au téléphone.', 'Describe the situation: we guide you over the phone.')],
                ]]),
                B::schedule($theme, ['title' => $T('Horaires du service d’urgence', 'Emergency service hours'), 'bg' => 'white', 'groups' => [
                    [$T('Disponibilité', 'Availability'), [
                        ['clock', $T('Tous les jours, de 7 h à 22 h', 'Every day, 7 am to 10 pm')],
                        ['calendar-days', $T('Week-ends et jours fériés inclus', 'Weekends and public holidays included')],
                        ['phone', $phone[0]],
                    ]],
                    [$T('Tarifs d’urgence', 'Emergency rates'), [
                        ['tag', $T('Déplacement et diagnostic : 69 €', 'Call-out and diagnosis: $75')],
                        ['clock', $T('Majoration de 30 % le soir et le dimanche', '30% surcharge evenings and Sundays')],
                        ['credit-card', $T('Carte bancaire, virement ou paiement mobile', 'Card, bank transfer or mobile payment')],
                    ]],
                ]]),
                B::cta($theme, ['title' => $T('Besoin d’un dépannage maintenant ?', 'Need a repair right now?'), 'text' => $T('Un technicien vous répond tout de suite et vous donne un délai d’arrivée.', 'A technician answers right away and gives you an arrival time.'),
                    'buttons' => [[$T('Appeler le ', 'Call ').$phone[0], $phone[1], 'accent'], [$T('Écrire un message', 'Send a message'), $contact, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'work', 'slug' => $u('realisations', 'our-work'), 'title' => $T('Réalisations', 'Our work'),
            'description' => $T('Salles de bains, cuisines, électricité, parquet et menuiserie : nos réalisations en photos avant et après travaux, et l’avis de nos clients.', 'Bathrooms, kitchens, electrical, wood floors and carpentry: before-and-after photos of our projects and what our customers say.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Réalisations', 'Our work'), 'title' => $T('Avant, après : jugez sur pièce', 'Before and after: see for yourself'), 'intro' => $T('Quelques chantiers récents, photographiés avec l’accord de nos clients.', 'A few recent projects, photographed with our customers’ permission.')]),
                B::gallery($theme, ['eyebrow' => $T('Avant / après', 'Before / after'), 'title' => $T('Trois transformations', 'Three makeovers'), 'intro' => $T('À gauche avant les travaux, à droite après.', 'Before on the left, after on the right.'), 'cols' => 2, 'photos' => [
                    ['artisan-avant-salle-de-bains', 'image', $T('Salle de bains ancienne avec carrelage usé et baignoire démodée, avant travaux', 'Dated bathroom with worn tiles and an old bathtub, before renovation')],
                    ['artisan-apres-salle-de-bains', 'image', $T('Salle de bains rénovée avec douche à l’italienne et carrelage clair', 'Renovated bathroom with a walk-in shower and light tiles')],
                    ['artisan-avant-cuisine', 'image', $T('Cuisine vieillie aux meubles abîmés, avant rénovation', 'Worn-out kitchen with damaged cabinets, before renovation')],
                    ['artisan-apres-cuisine', 'image', $T('Cuisine moderne rénovée avec plan de travail en bois et éclairage encastré', 'Modern renovated kitchen with a wooden countertop and recessed lighting')],
                    ['artisan-avant-parquet', 'image', $T('Vieux parquet terne et rayé dans un salon vide', 'Dull, scratched old wood floor in an empty living room')],
                    ['artisan-apres-parquet', 'image', $T('Parquet poncé et vitrifié brillant dans un salon lumineux', 'Sanded and varnished wood floor gleaming in a bright living room')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Chantiers récents', 'Recent projects'), 'title' => $T('Le détail de nos interventions', 'Inside our projects'), 'bg' => 'light', 'items' => [
                    ['artisan-chantier-electricite', 'zap', $T('Tableau électrique remplacé', 'Electrical panel replaced'), $T('Maison des années 70 : nouveau tableau, différentiels et mise à la terre, en une journée.', '1970s house: new panel, ground-fault protection and grounding, in one day.')],
                    ['artisan-chantier-menuiserie', 'hammer', $T('Dressing sur mesure', 'Custom walk-in closet'), $T('Dressing en bois dans une chambre mansardée, conçu au centimètre près.', 'Wooden closet in an attic bedroom, built to the inch.')],
                    ['artisan-chantier-plomberie', 'droplet', $T('Chauffe-eau et réseau neufs', 'New water heater and pipes'), $T('Remplacement du chauffe-eau et des canalisations d’un appartement ancien.', 'Water heater and pipework replaced in an older apartment.')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::stats($theme, ['bg' => 'primary', 'title' => $T('La satisfaction avant tout', 'Satisfaction comes first'), 'items' => [
                    [98, ' %', $T('de clients satisfaits', 'satisfied customers')],
                    [450, '+', $T('salles de bains et cuisines rénovées', 'bathrooms and kitchens renovated')],
                    [0, '', $T('frais cachés', 'hidden fees')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'pricing', 'slug' => $u('tarifs-devis', 'pricing'), 'title' => $T('Tarifs et devis', 'Pricing and quotes'),
            'description' => $T('Tarifs indicatifs de dépannage, contrat d’entretien et rénovation sur devis. Devis gratuit et détaillé, paiement par carte, virement ou mobile.', 'Indicative rates for repairs, maintenance plans and quoted renovations. Free itemized quote; pay by card, bank transfer or mobile.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Tarifs', 'Pricing'), 'title' => $T('Des prix clairs avant chaque intervention', 'Clear prices before every job'), 'intro' => $T('Montants indicatifs : le prix définitif figure toujours sur un devis écrit, gratuit et sans engagement.', 'Indicative amounts: the final price always appears on a written quote, free and with no obligation.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Dépannage', 'Repair call'), $T('69', '75'), $cur, $T('déplacement + 1re heure', 'call-out + 1st hour'), $T('Pour une panne ou une petite réparation.', 'For a breakdown or a small repair.'), [$T('Diagnostic sur place', 'On-site diagnosis'), $T('Première heure de main-d’œuvre', 'First hour of labor'), $T('Pièces courantes en stock', 'Common parts in stock'), '-'.$T('Pièces spécifiques', 'Special-order parts')], $T('Demander un dépannage', 'Request a repair')],
                    [$T('Contrat d’entretien', 'Maintenance plan'), $T('149', '165'), $cur, $T('/ an', '/ year'), $T('Pour garder votre logement en bon état.', 'To keep your home in good shape.'), [$T('Visite annuelle plomberie et électricité', 'Annual plumbing and electrical check'), $T('Entretien du chauffe-eau', 'Water heater servicing'), $T('Dépannages prioritaires', 'Priority repair calls'), $T('10 % sur la main-d’œuvre', '10% off labor')], $T('Souscrire', 'Sign up'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Rénovation', 'Renovation'), $T('Sur devis', 'Quoted'), '', $T('gratuit', 'free'), $T('Pour une pièce ou un logement entier.', 'For a room or a whole home.'), [$T('Visite et métré gratuits', 'Free visit and measurements'), $T('Devis détaillé par poste', 'Quote itemized by trade'), $T('Planning écrit', 'Written schedule'), $T('Un responsable de chantier', 'One site lead')], $T('Demander un devis', 'Request a quote')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Devis gratuit', 'Free quote'), 'title' => $T('Comment obtenir votre devis', 'How to get your quote'), 'bg' => 'white', 'items' => [
                    [$T('Votre demande', 'Your request'), $T('Par téléphone ou formulaire, avec quelques photos.', 'By phone or form, with a few photos.')],
                    [$T('La visite', 'The visit'), $T('Un artisan mesure et vérifie l’existant, gratuitement.', 'A tradesperson measures and checks the site, free of charge.')],
                    [$T('Le devis', 'The quote'), $T('Vous le recevez sous 48 heures, détaillé poste par poste.', 'You receive it within 48 hours, itemized line by line.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Paiement et facturation', 'Payment and billing'), 'bg' => 'light', 'items' => [
                    [$T('Le devis est-il vraiment gratuit ?', 'Is the quote really free?'), $T('Oui, la visite et le devis sont gratuits et sans engagement dans notre zone d’intervention.', 'Yes, the visit and the quote are free with no obligation within our service area.')],
                    [$T('Demandez-vous un acompte ?', 'Do you ask for a deposit?'), $T('Pour les rénovations, un acompte de 30 % est demandé à la commande. Les dépannages se règlent à la fin de l’intervention.', 'For renovations, a 30% deposit is due when you sign. Repairs are paid when the job is done.')],
                    [$T('Comment puis-je payer ?', 'How can I pay?'), $T('Par carte bancaire, virement ou paiement mobile. Une facture détaillée vous est toujours remise.', 'By card, bank transfer or mobile payment. You always receive a detailed invoice.')],
                    [$T('Et si les travaux coûtent plus cher que prévu ?', 'What if the job costs more than planned?'), $T('Aucun supplément n’est facturé sans votre accord écrit. Si nous découvrons un imprévu, nous vous montrons le problème et chiffrons la solution.', 'No extra is billed without your written approval. If we find something unexpected, we show you the problem and price the fix.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('entreprise', 'about-us'), 'title' => $T('L’entreprise', 'About us'),
            'description' => $T('Une équipe d’artisans qualifiés et assurés : notre histoire, nos garanties sur les travaux, nos assurances et nos zones d’intervention.', 'A team of qualified, insured tradespeople: our story, our workmanship guarantees, our insurance and our service area.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’entreprise', 'About us'), 'title' => $T('Des artisans de métier, près de chez vous', 'Skilled tradespeople, close to home'), 'intro' => $T('Une entreprise familiale qui a grandi grâce au bouche-à-oreille.', 'A family business that grew by word of mouth.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('D’un plombier seul à une équipe de huit artisans', 'From one plumber to a team of eight'),
                    'html' => '<p>'.e($T('L’entreprise a commencé avec un plombier, une camionnette et beaucoup de dépannages. Les clients ont demandé plus : un électricien, puis un menuisier et un carreleur nous ont rejoints. Aujourd’hui, nous réalisons des rénovations complètes sans perdre le sens du service.', 'The business started with one plumber, one van and a lot of repair calls. Customers asked for more: an electrician joined, then a carpenter and a tiler. Today we deliver full renovations without losing our service mindset.')).'</p><p>'.e($T('Immatriculée sous le numéro [N° d’immatriculation], l’entreprise forme chaque année des apprentis.', 'Registered under number [Registration no.], the business trains apprentices every year.')).'</p>',
                    'photo' => ['artisan-atelier', 'warehouse', $T('Atelier d’artisan rangé avec établi, outils muraux et camionnette garée devant', 'Tidy contractor workshop with a workbench, wall-mounted tools and a van parked outside')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Garanties et assurances', 'Guarantees and insurance'), 'title' => $T('Vous êtes couvert, du devis à la fin des travaux', 'You’re covered, from quote to completion'), 'bg' => 'light', 'items' => [
                    ['shield-check', $T('Assurance professionnelle', 'Liability insurance'), $T('Responsabilité civile professionnelle et garantie des travaux, police [N° de police].', 'Professional liability and workmanship insurance, policy [Policy no.].')],
                    ['award', $T('Main-d’œuvre garantie', 'Labor guaranteed'), $T('Nos interventions sont garanties un an : en cas de souci, nous revenons sans frais.', 'Our work is guaranteed for one year: if anything goes wrong, we come back free of charge.')],
                    ['box', $T('Pièces garanties', 'Parts under warranty'), $T('Équipements posés avec la garantie du fabricant, que nous gérons pour vous.', 'Fixtures installed with the manufacturer’s warranty, which we handle for you.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Ceux qui interviennent chez vous', 'The people who come to your home'), 'items' => [
                    ['[Prénom Nom]', $T('Fondateur, plombier chauffagiste', 'Founder, plumber and heating technician'), 'artisan-equipe-1'],
                    ['[Prénom Nom]', $T('Électricienne', 'Electrician'), 'artisan-equipe-2'],
                    ['[Prénom Nom]', $T('Menuisier agenceur', 'Finish carpenter'), 'artisan-equipe-3'],
                    ['[Prénom Nom]', $T('Responsable des devis et du planning', 'Quotes and scheduling manager'), 'artisan-equipe-4'],
                ]]),
                B::schedule($theme, $zones),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'tips', 'slug' => $u('conseils', 'tips'), 'title' => $T('Conseils', 'Tips'),
            'description' => $T('Conseils pratiques de nos artisans : prévenir les fuites, entretenir son installation électrique, préparer une rénovation.', 'Practical advice from our tradespeople: preventing leaks, looking after your wiring, planning a renovation.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Conseils', 'Tips'), 'title' => $T('Les conseils de nos artisans', 'Advice from our tradespeople'), 'intro' => $T('Des gestes simples pour entretenir votre logement et éviter les pannes.', 'Simple habits to look after your home and avoid breakdowns.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire le conseil', 'Read the tip')]),
                B::features($theme, ['eyebrow' => $T('En bref', 'Quick tips'), 'title' => $T('Trois réflexes à adopter', 'Three habits worth keeping'), 'bg' => 'light', 'items' => [
                    ['droplet', $T('Repérez votre vanne d’eau', 'Find your water valve'), $T('Savoir où couper l’eau peut éviter un dégât des eaux en quelques secondes.', 'Knowing where to shut off the water can prevent flood damage in seconds.')],
                    ['zap', $T('Testez vos différentiels', 'Test your GFCIs'), $T('Appuyez sur le bouton test une fois par mois : le courant doit se couper.', 'Press the test button once a month: the power should cut off.')],
                    ['thermometer', $T('Détartrez le chauffe-eau', 'Flush your water heater'), $T('Un entretien tous les deux ans prolonge sa durée de vie et réduit la facture.', 'Servicing it every two years extends its life and lowers your bill.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Demandez un devis gratuit ou un dépannage. Réponse dans la journée, intervention en moins d’une heure pour les urgences.', 'Request a free quote or a repair. Same-day reply, under one hour for emergencies.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Demandez votre devis gratuit', 'Get your free quote'), 'intro' => $T('Décrivez vos travaux, joignez des photos si possible : nous vous rappelons dans la journée.', 'Describe your job and add photos if you can: we call you back the same day.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Pour une urgence, appelez-nous directement : un technicien vous répond.', 'For an emergency, call us directly: a technician will answer.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', $phone[0], $phone[1]],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Bureau : lundi – vendredi, 8 h – 18 h ; urgences 7 j/7, 7 h – 22 h', 'Office: Monday – Friday, 8 am – 6 pm; emergencies 7 days, 7 am – 10 pm')],
                    ],
                    'social' => ['facebook', 'instagram', 'whatsapp'],
                    'subject' => true,
                    'button' => $T('Envoyer ma demande', 'Send my request'),
                    'bg' => 'white',
                ]),
                B::schedule($theme, $zones),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Avant de nous écrire', 'Before you write'), 'items' => [
                    [$T('Sous quel délai aurai-je une réponse ?', 'How soon will I hear back?'), $T('Nous rappelons dans la journée ouvrée. Pour une urgence, appelez plutôt : nous répondons tout de suite.', 'We call back the same business day. For an emergency, please call: we answer right away.')],
                    [$T('Que dois-je indiquer dans ma demande ?', 'What should I include in my request?'), $T('L’adresse, le type de travaux, vos disponibilités et, si possible, deux ou trois photos.', 'The address, the type of work, when you’re available and, if possible, two or three photos.')],
                    [$T('Intervenez-vous le week-end ?', 'Do you work on weekends?'), $T('Oui pour les urgences. Les chantiers de rénovation ont lieu du lundi au samedi.', 'Yes for emergencies. Renovation work takes place Monday to Saturday.')],
                ]]),
            ]];

        $blog = [
            'base' => $u('conseils', 'tips'),
            'title' => $T('Conseils', 'Tips'),
            'categories' => ['plomberie' => $T('Plomberie', 'Plumbing'), 'electricite' => $T('Électricité', 'Electrical'), 'renovation' => $T('Rénovation', 'Renovation')],
            'posts' => [
                ['key' => 'fuite', 'slug' => $u('detecter-une-fuite', 'spotting-a-leak'), 'category' => 'plomberie', 'tags' => [$T('Fuite', 'Leaks')],
                    'title' => $T('Comment détecter une fuite d’eau cachée', 'How to spot a hidden water leak'),
                    'excerpt' => $T('Compteur, traces d’humidité, bruit d’écoulement : les signes qui doivent alerter.', 'Water meter, damp stains, running water sounds: the warning signs.'),
                    'photo' => ['artisan-blog-fuite', 'droplet', $T('Plombier vérifiant un compteur d’eau avec une lampe torche', 'Plumber checking a water meter with a flashlight')],
                    'heading' => $T('Le test du compteur', 'The meter test'),
                    'body' => [
                        $T('Une fuite cachée peut faire grimper la facture d’eau et abîmer murs et planchers avant d’être visible.', 'A hidden leak can drive up your water bill and damage walls and floors long before you see it.'),
                        $T('Le soir, relevez le compteur, n’utilisez pas d’eau pendant la nuit et relevez-le à nouveau le matin : s’il a tourné, il y a une fuite.', 'In the evening, read the meter, use no water overnight and read it again in the morning: if it has moved, you have a leak.'),
                        $T('Surveillez aussi les taches au plafond, la peinture qui cloque et les joints noircis. Au moindre doute, faites appel à un professionnel équipé pour localiser la fuite sans casser.', 'Also watch for ceiling stains, blistering paint and darkened grout. If in doubt, call a professional with the equipment to locate the leak without tearing things open.'),
                    ]],
                ['key' => 'tableau', 'slug' => $u('securite-electrique', 'electrical-safety'), 'category' => 'electricite', 'tags' => [$T('Sécurité', 'Safety')],
                    'title' => $T('Cinq signes qu’il faut revoir votre installation électrique', 'Five signs your wiring needs attention'),
                    'excerpt' => $T('Disjoncteurs qui sautent, prises qui chauffent : quand faire appel à un électricien.', 'Tripping breakers, warm outlets: when to call an electrician.'),
                    'photo' => ['artisan-blog-tableau', 'zap', $T('Électricienne travaillant sur un tableau électrique ouvert', 'Electrician working on an open electrical panel')],
                    'quote' => $T('« Une installation ancienne n’est pas forcément dangereuse, mais elle doit être vérifiée. »', '“Old wiring isn’t necessarily dangerous, but it should be checked.”'),
                    'body' => [
                        $T('Un disjoncteur qui saute souvent, une prise qui noircit ou chauffe, une odeur de brûlé ou des lumières qui vacillent sont des signaux à prendre au sérieux.', 'A breaker that trips often, an outlet that darkens or gets warm, a burning smell or flickering lights are signals to take seriously.'),
                        $T('L’absence de protection différentielle ou de prise de terre est fréquente dans les logements anciens.', 'Missing ground-fault protection or grounding is common in older homes.'),
                        $T('Un diagnostic par un électricien qualifié permet de prioriser les travaux et d’éviter les risques d’incendie.', 'An inspection by a qualified electrician lets you prioritize the work and avoid fire risks.'),
                    ]],
                ['key' => 'renovation', 'slug' => $u('preparer-sa-renovation', 'planning-a-renovation'), 'category' => 'renovation', 'tags' => [$T('Budget', 'Budget')],
                    'title' => $T('Bien préparer la rénovation de sa salle de bains', 'Planning your bathroom remodel'),
                    'excerpt' => $T('Budget, choix des matériaux, planning : les questions à se poser avant de commencer.', 'Budget, materials, schedule: the questions to ask before you start.'),
                    'photo' => ['artisan-blog-renovation', 'paintbrush', $T('Échantillons de carrelage et plan de salle de bains posés sur une table', 'Tile samples and a bathroom floor plan laid out on a table')],
                    'heading' => $T('Les étapes clés', 'The key steps'),
                    'body' => [
                        $T('Commencez par lister vos besoins : douche ou baignoire, rangements, accessibilité. Ils guideront le plan et le budget.', 'Start by listing your needs: shower or bathtub, storage, accessibility. They will shape the layout and the budget.'),
                        $T('Prévoyez une marge de 10 % pour les imprévus, surtout dans un logement ancien où les canalisations peuvent réserver des surprises.', 'Allow a 10% contingency, especially in older homes where the pipes may hold surprises.'),
                        $T('Choisissez une entreprise qui coordonne tous les métiers : vous gagnerez du temps et éviterez les reprises.', 'Choose a company that coordinates every trade: you will save time and avoid rework.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Interventions', 'Services'), 'url' => $services],
                    ['label' => $T('Urgences', 'Emergencies'), 'url' => $emergency],
                    ['label' => $T('Réalisations', 'Our work'), 'url' => $work],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('L’entreprise', 'About'), 'url' => $about],
                    ['label' => $T('Conseils', 'Tips'), 'url' => $tips],
                ],
                'cta_label' => $T('Devis gratuit', 'Free quote'),
                'cta_url' => $contact,
                'footer_text' => $T('Plomberie, électricité, menuiserie et rénovation. Devis gratuit, dépannage 7 j/7, travaux garantis par des artisans qualifiés et assurés.', 'Plumbing, electrical, carpentry and renovation. Free quotes, 7-day emergency repairs, guaranteed work by qualified, insured tradespeople.'),
                'footer_links' => [
                    ['label' => $T('Nos interventions', 'Our services'), 'url' => $services],
                    ['label' => $T('Dépannage urgent', 'Emergency repairs'), 'url' => $emergency],
                    ['label' => $T('Tarifs et devis', 'Pricing and quotes'), 'url' => $pricing],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
