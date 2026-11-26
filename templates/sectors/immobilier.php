<?php

/*
 * Pack « Agence immobilière » (secteur Immobilier) : 8 pages, en français et en
 * anglais. Achat, vente, location, gestion locative, estimation, biens à la une,
 * quartiers et conseils. Les textes et les biens sont des exemples réalistes à
 * personnaliser (l'assistant IA les réécrit à partir de la présentation de
 * l'agence). Les liens internes sont écrits avec les adresses de la langue de la
 * page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Immobilier', 'en' => 'Real estate'],
        'icon' => 'house',
        'keywords' => ['immobilier', 'agence immobilière', 'achat', 'vente', 'location', 'gestion locative', 'estimation', 'appartement', 'maison', 'agent immobilier', 'real estate', 'realtor', 'property', 'homes for sale', 'rentals', 'property management', 'valuation', 'broker'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $buy = $u('/acheter', '/buy');
        $sell = $u('/vendre', '/sell');
        $rent = $u('/louer', '/rent');
        $management = $u('/gestion-locative', '/property-management');
        $valuation = $u('/estimation', '/valuation');
        $advice = $u('/conseils', '/advice');
        $contact = '/contact';

        $forSale = [
            ['immobilier-bien-maison-jardin', 'house', $T('Maison familiale avec jardin arboré', 'Family house with a leafy garden'), $T('5 pièces · 140 m² · terrain de 600 m² · garage. 465 000 €', '4 bedrooms · 1,500 sq ft · 0.15-acre lot · garage. $495,000'), $buy],
            ['immobilier-bien-appartement-centre', 'building', $T('Appartement lumineux en centre-ville', 'Bright downtown apartment'), $T('3 pièces · 72 m² · balcon · ascenseur, proche des commerces. 289 000 €', '2 bedrooms · 775 sq ft · balcony · elevator, near shops. $310,000'), $buy],
            ['immobilier-bien-loft', 'warehouse', $T('Loft avec verrière dans un ancien atelier', 'Loft with glass roof in a former workshop'), $T('4 pièces · 120 m² · hauteur sous plafond 4 m · terrasse. 395 000 €', '3 bedrooms · 1,300 sq ft · 13 ft ceilings · terrace. $425,000'), $buy],
            ['immobilier-bien-villa-piscine', 'sun', $T('Villa contemporaine avec piscine', 'Contemporary villa with pool'), $T('6 pièces · 210 m² · piscine · vue dégagée. 780 000 €', '5 bedrooms · 2,250 sq ft · pool · open view. $830,000'), $buy],
            ['immobilier-bien-studio', 'key', $T('Studio rénové près de l’université', 'Renovated studio near the university'), $T('1 pièce · 26 m² · idéal investissement locatif. 119 000 €', 'Studio · 280 sq ft · ideal rental investment. $128,000'), $buy],
            ['immobilier-bien-maison-ville', 'house', $T('Maison de ville avec patio', 'Townhouse with patio'), $T('4 pièces · 95 m² · patio · à rafraîchir. 245 000 €', '3 bedrooms · 1,000 sq ft · patio · needs updating. $265,000'), $buy],
        ];
        $forRent = [
            ['immobilier-location-appartement', 'building', $T('Appartement meublé, 2 pièces', 'Furnished one-bedroom apartment'), $T('48 m² · cuisine équipée · disponible tout de suite. 890 € / mois', '520 sq ft · equipped kitchen · available now. $950 / month'), $rent],
            ['immobilier-location-maison', 'house', $T('Maison familiale avec jardin', 'Family house with garden'), $T('4 pièces · 110 m² · école à 5 minutes. 1 450 € / mois', '3 bedrooms · 1,200 sq ft · school 5 minutes away. $1,550 / month'), $rent],
            ['immobilier-location-bureau', 'briefcase', $T('Bureaux en rez-de-chaussée', 'Ground-floor office space'), $T('85 m² · vitrine · accès transports. 1 600 € / mois', '900 sq ft · storefront · transit access. $1,700 / month'), $rent],
        ];
        $neighborhoods = [
            ['immobilier-quartier-centre', 'store', $T('Centre historique', 'Historic center'), $T('Rues commerçantes, cafés et immeubles anciens : idéal pour vivre à pied.', 'Shopping streets, cafés and period buildings: perfect for living on foot.')],
            ['immobilier-quartier-residentiel', 'tree-pine', $T('Quartier résidentiel', 'Residential neighborhood'), $T('Maisons avec jardin, parcs et écoles réputées, au calme.', 'Houses with gardens, parks and well-regarded schools, in a quiet setting.')],
            ['immobilier-quartier-bord-eau', 'waves', $T('Bord de l’eau', 'Waterfront'), $T('Promenades, vue dégagée et programmes neufs recherchés.', 'Promenades, open views and sought-after new developments.')],
        ];
        $testimonials = ['eyebrow' => $T('Avis clients', 'Client reviews'), 'title' => $T('Ils ont trouvé, vendu ou loué avec nous', 'They bought, sold or rented with us'), 'rating' => 5, 'items' => [
            [$T('Notre maison vendue en cinq semaines, au prix estimé. Des visites bien préparées et un suivi à chaque étape.', 'Our house sold in five weeks, at the estimated price. Well-prepared viewings and follow-up at every step.'), '[Prénom Nom]', $T('Vendeurs, maison familiale', 'Sellers, family home')],
            [$T('L’agente a compris ce que nous cherchions et nous a évité de nombreuses visites inutiles.', 'Our agent understood what we were looking for and saved us many pointless viewings.'), '[Prénom Nom]', $T('Primo-accédants', 'First-time buyers')],
            [$T('Je confie mes trois appartements à l’agence : loyers versés à date, travaux gérés, plus aucun souci.', 'I trust the agency with my three apartments: rent paid on time, repairs handled, no more worries.'), '[Prénom Nom]', $T('Propriétaire bailleur', 'Landlord')],
        ]];
        $cta = ['title' => $T('Combien vaut votre bien ?', 'What is your property worth?'), 'text' => $T('Recevez une estimation gratuite et argumentée par un conseiller qui connaît votre quartier, sous 48 heures.', 'Get a free, well-argued valuation from an agent who knows your neighborhood, within 48 hours.'),
            'buttons' => [[$T('Estimer mon bien', 'Value my property'), $valuation, 'accent'], [$T('Voir les biens', 'Browse listings'), $buy, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Agence immobilière : achat, vente, location, gestion locative et estimation gratuite. Découvrez nos biens à la une et nos quartiers.', 'Real estate agency: buying, selling, renting, property management and free valuations. See our featured listings and neighborhoods.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Agence immobilière', 'Real estate agency'),
                    'title' => $T('Le bon bien, au bon prix, au bon moment', 'The right home, at the right price, at the right time'),
                    'text' => $T('Acheter, vendre, louer ou faire gérer : des conseillers qui connaissent chaque rue du secteur vous accompagnent de la première visite à la signature.', 'Buying, selling, renting or managing: agents who know every street in the area guide you from the first viewing to closing.'),
                    'buttons' => [[$T('Voir les biens', 'Browse listings'), $buy], [$T('Estimer mon bien', 'Value my property'), $valuation]],
                    'photo' => ['immobilier-accueil', 'house', $T('Façade d’une maison moderne avec jardin et allée sous un ciel bleu', 'Front of a modern house with a garden and driveway under a blue sky')],
                ]),
                B::features($theme, ['cols' => 4, 'items' => [
                    ['key', $T('Acheter', 'Buy'), $T('Une sélection de biens vérifiés et des visites ciblées.', 'A selection of verified properties and targeted viewings.'), $buy, $T('Voir les biens', 'Browse')],
                    ['tag', $T('Vendre', 'Sell'), $T('Un prix juste, une mise en valeur soignée, des acheteurs qualifiés.', 'A fair price, polished presentation and qualified buyers.'), $sell, $T('Vendre avec nous', 'Sell with us')],
                    ['building', $T('Louer', 'Rent'), $T('Appartements, maisons et locaux, vides ou meublés.', 'Apartments, houses and commercial space, furnished or not.'), $rent, $T('Les locations', 'Rentals')],
                    ['shield-check', $T('Faire gérer', 'Property management'), $T('Loyers, locataires et entretien gérés pour vous.', 'Rent, tenants and maintenance handled for you.'), $management, $T('La gestion', 'Learn more')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Biens à la une', 'Featured listings'), 'title' => $T('Nos coups de cœur du moment', 'Our current favorites'), 'more' => $T('Voir le bien', 'View listing'), 'bg' => 'light', 'items' => array_slice($forSale, 0, 3)]),
                B::stats($theme, ['items' => [
                    [350, '+', $T('biens vendus', 'homes sold')],
                    [600, '+', $T('logements en gestion', 'units under management')],
                    [45, ' j', $T('délai moyen de vente', 'average days to sell')],
                    [98, ' %', $T('du prix estimé obtenu', 'of the estimated price achieved')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Nos quartiers', 'Our neighborhoods'), 'title' => $T('Trouvez le quartier qui vous ressemble', 'Find the neighborhood that suits you'), 'items' => $neighborhoods]),
                B::split($theme, [
                    'eyebrow' => $T('Estimation gratuite', 'Free valuation'),
                    'title' => $T('Une estimation fondée sur les ventes réelles', 'A valuation based on actual sales'),
                    'html' => '<p>'.e($T('Votre conseiller visite le bien, le compare aux ventes récentes du quartier et vous remet un avis de valeur écrit, avec une stratégie de mise en vente.', 'Your agent visits the property, compares it with recent sales in the area and gives you a written opinion of value, with a sales strategy.')).'</p>',
                    'bullets' => [$T('Visite et avis de valeur sous 48 heures', 'Visit and opinion of value within 48 hours'), $T('Comparaison avec les ventes récentes', 'Comparison with recent sales'), $T('Sans engagement', 'No obligation')],
                    'buttons' => [[$T('Demander une estimation', 'Request a valuation'), $valuation]],
                    'photo' => ['immobilier-estimation', 'clipboard-check', $T('Agente immobilière prenant des notes lors de la visite d’un salon lumineux', 'Real estate agent taking notes while touring a bright living room')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'buy', 'slug' => $u('acheter', 'buy'), 'title' => $T('Acheter', 'Buy'),
            'description' => $T('Maisons, appartements et lofts à vendre, sélectionnés par l’agence. Accompagnement de la recherche à la signature, conseils de financement.', 'Houses, apartments and lofts for sale, selected by the agency. Support from search to closing, plus financing advice.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Acheter', 'Buy'), 'title' => $T('Votre futur chez-vous est peut-être ici', 'Your next home may be right here'), 'intro' => $T('Des biens visités et vérifiés par nos conseillers, et d’autres en avant-première pour nos acquéreurs inscrits.', 'Properties visited and checked by our agents, plus early access for registered buyers.')]),
                B::cards($theme, ['more' => $T('Demander une visite', 'Request a viewing'), 'items' => array_map(fn ($b) => [$b[0], $b[1], $b[2], $b[3], $contact], $forSale)]),
                B::steps($theme, ['eyebrow' => $T('Votre achat', 'Your purchase'), 'title' => $T('Acheter en cinq étapes', 'Buying in five steps'), 'bg' => 'light', 'items' => [
                    [$T('Votre projet', 'Your plan'), $T('Budget, quartiers, critères : nous définissons votre recherche.', 'Budget, areas, criteria: we define your search.')],
                    [$T('Le financement', 'Financing'), $T('Simulation et mise en relation avec des courtiers.', 'Simulation and referrals to mortgage brokers.')],
                    [$T('Les visites', 'Viewings'), $T('Seulement des biens qui correspondent vraiment.', 'Only properties that truly match.')],
                    [$T('L’offre', 'The offer'), $T('Conseil sur le prix et négociation.', 'Pricing advice and negotiation.')],
                    [$T('La signature', 'Closing'), $T('Suivi du compromis jusqu’à l’acte définitif.', 'Follow-up from the sales agreement to final deed.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions d’acheteurs', 'Buyer questions'), 'buttons' => [[$T('Être alerté des nouveaux biens', 'Get new listing alerts'), $contact, 'outline']], 'items' => [
                    [$T('Les frais d’agence sont-ils inclus dans le prix ?', 'Are agency fees included in the price?'), $T('Oui, nos prix sont affichés honoraires inclus. Les frais d’acte et taxes dépendent de votre pays et s’ajoutent au prix.', 'Yes, our prices include agency fees. Closing costs and taxes depend on your country and come on top.')],
                    [$T('Puis-je visiter à distance ?', 'Can I view a property remotely?'), $T('Oui, par visite vidéo en direct avec un conseiller, avant une visite sur place.', 'Yes, through a live video tour with an agent, before an in-person viewing.')],
                    [$T('Comment se passe la négociation ?', 'How does negotiation work?'), $T('Nous vous donnons les prix de vente récents du secteur pour formuler une offre réaliste, que nous défendons auprès du vendeur.', 'We give you recent sale prices in the area so you can make a realistic offer, which we then present to the seller.')],
                    [$T('Proposez-vous des biens hors marché ?', 'Do you offer off-market properties?'), $T('Certains vendeurs préfèrent la discrétion : nos acquéreurs inscrits sont prévenus en premier.', 'Some sellers prefer discretion: our registered buyers are notified first.')],
                ]]),
            ]];

        $pages[] = ['key' => 'sell', 'slug' => $u('vendre', 'sell'), 'title' => $T('Vendre', 'Sell'),
            'description' => $T('Vendez votre bien au juste prix : estimation, photos professionnelles, visite virtuelle, acheteurs qualifiés et suivi jusqu’à la signature.', 'Sell your property at the right price: valuation, professional photos, virtual tour, qualified buyers and follow-up to closing.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Vendre', 'Sell'), 'title' => $T('Vendre vite, et au juste prix', 'Sell fast, at the right price'), 'intro' => $T('Une stratégie de vente sur mesure et un conseiller dédié jusqu’à la remise des clés.', 'A tailored sales strategy and a dedicated agent until handover.')]),
                B::split($theme, [
                    'eyebrow' => $T('Mise en valeur', 'Presentation'),
                    'title' => $T('Votre bien présenté sous son meilleur jour', 'Your property at its very best'),
                    'html' => '<p>'.e($T('Photos professionnelles, visite virtuelle, plan et annonce soignée : nous mettons en avant les atouts de votre bien et le diffusons auprès des acheteurs de notre fichier et sur les principaux portails.', 'Professional photos, virtual tour, floor plan and a well-written listing: we highlight your property’s strengths and share it with our buyer database and on the main listing portals.')).'</p>',
                    'bullets' => [$T('Photos et visite virtuelle offertes', 'Free photos and virtual tour'), $T('Acheteurs qualifiés avant chaque visite', 'Buyers pre-qualified before every viewing'), $T('Compte rendu après chaque visite', 'Feedback after every viewing'), $T('Conseils de valorisation (home staging)', 'Home staging advice')],
                    'photo' => ['immobilier-vente-photos', 'camera', $T('Photographe immobilier cadrant une cuisine moderne avec un appareil sur trépied', 'Real estate photographer framing a modern kitchen with a camera on a tripod')],
                ]),
                B::steps($theme, ['eyebrow' => $T('Votre vente', 'Your sale'), 'title' => $T('Les étapes de la vente', 'The steps of selling'), 'bg' => 'light', 'items' => [
                    [$T('Estimation', 'Valuation'), $T('Visite, étude du marché et avis de valeur écrit.', 'Visit, market study and written opinion of value.')],
                    [$T('Mandat', 'Listing agreement'), $T('Prix, honoraires et plan de diffusion fixés ensemble.', 'Price, fees and marketing plan agreed together.')],
                    [$T('Visites', 'Viewings'), $T('Acheteurs vérifiés et visites accompagnées.', 'Verified buyers and accompanied viewings.')],
                    [$T('Offre et signature', 'Offer and closing'), $T('Négociation, compromis et acte définitif.', 'Negotiation, sales agreement and final deed.')],
                ]]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [45, ' j', $T('délai moyen de vente', 'average days to sell')],
                    [98, ' %', $T('du prix estimé obtenu', 'of the estimated price achieved')],
                    [2500, '+', $T('acquéreurs inscrits', 'registered buyers')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'rent', 'slug' => $u('louer', 'rent'), 'title' => $T('Louer', 'Rent'),
            'description' => $T('Appartements, maisons et bureaux à louer. Dossier de location simple, visites rapides et état des lieux détaillé avec l’agence.', 'Apartments, houses and offices for rent. Simple rental application, quick viewings and a detailed move-in inspection with the agency.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Louer', 'Rent'), 'title' => $T('Trouver une location sans stress', 'Finding a rental without the stress'), 'intro' => $T('Des logements et des locaux vérifiés, des visites organisées vite et un dossier simple.', 'Verified homes and commercial space, quickly arranged viewings and a simple application.')]),
                B::cards($theme, ['eyebrow' => $T('Disponibles', 'Available now'), 'title' => $T('Locations du moment', 'Current rentals'), 'more' => $T('Demander une visite', 'Request a viewing'), 'items' => array_map(fn ($b) => [$b[0], $b[1], $b[2], $b[3], $contact], $forRent)]),
                B::features($theme, ['eyebrow' => $T('Locataires', 'Tenants'), 'title' => $T('Ce que nous faisons pour vous', 'What we do for you'), 'bg' => 'light', 'items' => [
                    ['file-text', $T('Dossier simplifié', 'Simple application'), $T('Une liste claire des pièces à fournir et un dépôt en ligne.', 'A clear list of documents and online submission.')],
                    ['calendar-check', $T('Visites rapides', 'Quick viewings'), $T('Des créneaux en soirée et le samedi, ou en vidéo.', 'Evening and Saturday slots, or by video.')],
                    ['clipboard-check', $T('État des lieux détaillé', 'Detailed inspection'), $T('Photos datées à l’entrée comme à la sortie.', 'Dated photos at move-in and move-out.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions de locataires', 'Tenant questions'), 'items' => [
                    [$T('Quelles pièces dois-je fournir ?', 'Which documents do I need?'), $T('Une pièce d’identité, des justificatifs de revenus et de domicile, et éventuellement un garant. La liste exacte dépend du bien et du pays.', 'Photo ID, proof of income and address, and possibly a guarantor. The exact list depends on the property and the country.')],
                    [$T('Combien de temps pour obtenir une réponse ?', 'How long until I get an answer?'), $T('En général sous 48 heures après réception d’un dossier complet.', 'Usually within 48 hours of receiving a complete application.')],
                    [$T('Qui contacter en cas de problème dans le logement ?', 'Who do I contact if something breaks?'), $T('Si le bien est géré par l’agence, notre service gestion intervient directement auprès des artisans.', 'If the property is managed by the agency, our management team deals directly with contractors.')],
                ]]),
                B::cta($theme, ['title' => $T('Vous ne trouvez pas votre bonheur ?', 'Can’t find what you need?'), 'text' => $T('Décrivez votre recherche : nous vous prévenons dès qu’un bien correspondant arrive.', 'Describe what you are looking for: we will tell you as soon as a matching property comes in.'),
                    'buttons' => [[$T('Créer une alerte', 'Create an alert'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'management', 'slug' => $u('gestion-locative', 'property-management'), 'title' => $T('Gestion locative', 'Property management'),
            'description' => $T('Gestion locative complète : recherche de locataires, encaissement des loyers, entretien, garanties et reporting en ligne pour les propriétaires.', 'Full property management: tenant search, rent collection, maintenance, guarantees and online reporting for landlords.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Gestion locative', 'Property management'), 'title' => $T('Votre bien loué, vos revenus sereins', 'Your property rented, your income secure'), 'intro' => $T('Nous gérons tout, du choix du locataire aux travaux, et vous rendons compte chaque mois.', 'We handle everything, from tenant selection to repairs, and report to you every month.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['users', $T('Choix du locataire', 'Tenant selection'), $T('Annonce, visites et étude rigoureuse des dossiers.', 'Listing, viewings and careful screening of applications.')],
                    ['banknote', $T('Encaissement des loyers', 'Rent collection'), $T('Loyers versés chaque mois et relances en cas de retard.', 'Rent paid out every month, with follow-up on late payments.')],
                    ['wrench', $T('Entretien et travaux', 'Maintenance and repairs'), $T('Réseau d’artisans de confiance et devis soumis à votre accord.', 'Trusted contractors and quotes submitted for your approval.')],
                    ['file-text', $T('Baux et régularisations', 'Leases and adjustments'), $T('Rédaction des baux, révisions de loyer et décomptes de charges.', 'Lease drafting, rent reviews and service charge statements.')],
                    ['chart-line', $T('Compte rendu en ligne', 'Online reporting'), $T('Relevés, factures et documents fiscaux dans votre espace.', 'Statements, invoices and tax documents in your portal.')],
                    ['shield-check', $T('Garanties', 'Guarantees'), $T('Assurance loyers impayés et dégradations en option.', 'Optional insurance against unpaid rent and damage.')],
                ]]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Formules', 'Plans'), 'title' => $T('Des honoraires calculés sur les loyers encaissés', 'Fees based on rent collected'), 'bg' => 'light', 'items' => [
                    [$T('Location seule', 'Tenant placement'), $T('1 mois', '1 month'), '', $T('de loyer', 'of rent'), $T('Nous trouvons le locataire, vous gérez ensuite.', 'We find the tenant, you manage afterwards.'), [$T('Annonce et visites', 'Listing and viewings'), $T('Étude des dossiers', 'Application screening'), $T('Bail et état des lieux', 'Lease and move-in inspection'), '-'.$T('Encaissement des loyers', 'Rent collection')], $T('Choisir', 'Choose')],
                    [$T('Gestion essentielle', 'Essential management'), $T('6 %', '6%'), '', $T('des loyers', 'of rent'), $T('La gestion courante, sans souci.', 'Day-to-day management, hassle-free.'), [$T('Encaissement et relances', 'Collection and reminders'), $T('Régularisation des charges', 'Service charge adjustments'), $T('Petits travaux coordonnés', 'Minor repairs coordinated'), $T('Espace propriétaire en ligne', 'Online owner portal')], $T('Choisir', 'Choose'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Gestion sérénité', 'Peace-of-mind management'), $T('8 %', '8%'), '', $T('des loyers', 'of rent'), $T('La gestion avec garanties.', 'Management with guarantees.'), [$T('Tout Gestion essentielle', 'Everything in Essential'), $T('Garantie loyers impayés', 'Unpaid rent guarantee'), $T('Suivi des gros travaux', 'Major works supervision'), $T('Visite annuelle du bien', 'Annual property inspection')], $T('Choisir', 'Choose')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Ce que nous vous garantissons', 'What we guarantee'), 'items' => [
                    ['clock', $T('Versement rapide', 'Fast payout'), $T('Vos loyers reversés dans les cinq jours suivant l’encaissement.', 'Your rent paid out within five days of collection.')],
                    ['phone', $T('Un gestionnaire dédié', 'A dedicated manager'), $T('Un interlocuteur unique qui connaît votre bien.', 'A single contact who knows your property.')],
                    ['eye', $T('Transparence', 'Transparency'), $T('Chaque dépense justifiée et visible en ligne.', 'Every expense justified and visible online.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions de propriétaires', 'Landlord questions'), 'bg' => 'light', 'items' => [
                    [$T('Puis-je reprendre la gestion moi-même ?', 'Can I take management back myself?'), $T('Oui, le mandat de gestion est résiliable selon le préavis prévu au contrat.', 'Yes, the management agreement can be ended with the notice set out in the contract.')],
                    [$T('Les travaux sont-ils engagés sans mon accord ?', 'Are repairs carried out without my approval?'), $T('Non, sauf urgence. Tout devis au-delà d’un montant convenu vous est soumis.', 'No, except in emergencies. Any quote above an agreed amount is submitted to you.')],
                    [$T('Comment suis-je payé ?', 'How am I paid?'), $T('Par virement chaque mois, avec un relevé détaillé des loyers et des frais.', 'By bank transfer every month, with a detailed statement of rent and fees.')],
                ]]),
            ]];

        $pages[] = ['key' => 'valuation', 'slug' => $u('estimation', 'valuation'), 'title' => $T('Estimation', 'Valuation'),
            'description' => $T('Estimation gratuite de votre maison ou appartement par un conseiller local : visite, comparaison avec les ventes récentes et avis de valeur écrit.', 'Free valuation of your house or apartment by a local agent: visit, comparison with recent sales and written opinion of value.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Estimation', 'Valuation'), 'title' => $T('Estimez votre bien gratuitement', 'Get a free property valuation'), 'intro' => $T('Un conseiller du quartier, une visite et un avis de valeur écrit sous 48 heures.', 'A local agent, a visit and a written opinion of value within 48 hours.')]),
                B::steps($theme, ['items' => [
                    [$T('Votre demande', 'Your request'), $T('Adresse, type de bien et surface : deux minutes suffisent.', 'Address, property type and size: two minutes is all it takes.')],
                    [$T('La visite', 'The visit'), $T('Un conseiller vient voir le bien et ses atouts.', 'An agent comes to see the property and its strengths.')],
                    [$T('L’avis de valeur', 'Opinion of value'), $T('Une fourchette de prix argumentée, par écrit.', 'A well-argued price range, in writing.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Notre méthode', 'Our method'),
                    'title' => $T('Ce qui fait la valeur d’un bien', 'What drives a property’s value'),
                    'html' => '<p>'.e($T('Emplacement, surface, état, luminosité, extérieur, étage, travaux à prévoir : nous croisons ces critères avec les ventes récentes du quartier pour vous donner une valeur réaliste, ni trop haute ni trop basse.', 'Location, size, condition, light, outdoor space, floor level, work required: we cross these factors with recent sales in the area to give you a realistic value, neither too high nor too low.')).'</p>',
                    'bullets' => [$T('Ventes comparables des douze derniers mois', 'Comparable sales from the last twelve months'), $T('Analyse de la demande dans le quartier', 'Analysis of demand in the neighborhood'), $T('Conseils pour valoriser le bien avant la vente', 'Tips to increase value before selling')],
                    'photo' => ['immobilier-estimation-visite', 'clipboard-check', $T('Conseiller immobilier mesurant une pièce avec un télémètre laser', 'Real estate agent measuring a room with a laser distance meter')],
                    'bg' => 'light',
                ]),
                B::contact($theme, [
                    'eyebrow' => $T('Demande d’estimation', 'Valuation request'),
                    'title' => $T('Parlez-nous de votre bien', 'Tell us about your property'), 'intro' => $T('Précisez l’adresse, le type de bien, la surface et le nombre de pièces dans votre message.', 'Include the address, property type, size and number of rooms in your message.'),
                    'items' => [
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['circle-check', $T('Gratuit et sans engagement', 'Free and no obligation')],
                    ],
                    'subject' => true,
                    'button' => $T('Demander mon estimation', 'Request my valuation'),
                    'bg' => 'white',
                ]),
            ]];

        $pages[] = ['key' => 'advice', 'slug' => $u('conseils', 'advice'), 'title' => $T('Conseils', 'Advice'),
            'description' => $T('Conseils immobiliers pour acheter, vendre et louer : financement, préparation de la vente, investissement locatif et ateliers gratuits.', 'Real estate advice on buying, selling and renting: financing, preparing to sell, rental investment and free workshops.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Conseils', 'Advice'), 'title' => $T('Bien préparer son projet immobilier', 'Preparing your real estate project'), 'intro' => $T('Guides pratiques rédigés par nos conseillers. Les règles varient selon les pays : demandez-nous conseil.', 'Practical guides written by our agents. Rules vary by country: ask us for advice.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire le conseil', 'Read more')]),
                B::events($theme, ['icon' => 'house', 'eyebrow' => $T('Ateliers', 'Workshops'), 'title' => $T('Ateliers gratuits à l’agence', 'Free workshops at the agency'), 'bg' => 'light', 'items' => [
                    [$T('Primo-accédants : réussir son premier achat', 'First-time buyers: getting your first purchase right'), $T('Samedi 10 janvier, 10 h', 'Saturday, January 10, 10 am'), $T('À l’agence', 'At the agency')],
                    [$T('Investir dans la location', 'Investing in rental property'), $T('Jeudi 5 février, 18 h 30', 'Thursday, February 5, 6:30 pm'), $T('En ligne', 'Online')],
                    [$T('Préparer sa maison pour la vente', 'Getting your home ready to sell'), $T('Samedi 14 mars, 10 h', 'Saturday, March 14, 10 am'), $T('À l’agence', 'At the agency')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez l’agence immobilière : visites, estimation, location ou gestion. Horaires d’ouverture, conseillers et coordonnées.', 'Contact the real estate agency: viewings, valuations, rentals or management. Opening hours, agents and contact details.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Passez nous voir', 'Come and see us'), 'intro' => $T('À l’agence, par téléphone ou en visio : nos conseillers vous répondent six jours sur sept.', 'At the agency, by phone or by video: our agents answer six days a week.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Indiquez s’il s’agit d’un achat, d’une vente, d’une location ou de gestion.', 'Let us know whether it is about buying, selling, renting or management.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['id-card', $T('Carte professionnelle [N° de carte]', 'Broker license [License no.]')],
                    ],
                    'social' => ['facebook', 'instagram', 'whatsapp', 'youtube'],
                    'subject' => true,
                    'bg' => 'white',
                ]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Vos conseillers', 'Your agents'), 'bg' => 'light', 'shape' => 'circle', 'items' => [
                    ['[Prénom Nom]', $T('Directrice d’agence', 'Branch manager'), 'immobilier-equipe-1'],
                    ['[Prénom Nom]', $T('Conseiller transaction', 'Sales agent'), 'immobilier-equipe-2'],
                    ['[Prénom Nom]', $T('Conseillère location', 'Leasing agent'), 'immobilier-equipe-3'],
                    ['[Prénom Nom]', $T('Gestionnaire locatif', 'Property manager'), 'immobilier-equipe-4'],
                ]]),
                B::schedule($theme, ['title' => $T('Horaires de l’agence', 'Agency hours'), 'groups' => [
                    [$T('Accueil', 'Front desk'), [
                        ['clock', $T('Lundi – vendredi : 9 h – 19 h', 'Monday – Friday: 9 am – 7 pm')],
                        ['clock', $T('Samedi : 9 h 30 – 17 h', 'Saturday: 9:30 am – 5 pm')],
                    ]],
                    [$T('Visites', 'Viewings'), [
                        ['calendar-days', $T('Sur rendez-vous, y compris en soirée', 'By appointment, including evenings')],
                        ['video', $T('Visites vidéo sur demande', 'Video tours on request')],
                    ]],
                ]]),
            ]];

        $blog = [
            'base' => $u('conseils', 'advice'),
            'title' => $T('Conseils', 'Advice'),
            'categories' => ['acheter' => $T('Acheter', 'Buying'), 'vendre' => $T('Vendre', 'Selling'), 'investir' => $T('Investir', 'Investing')],
            'posts' => [
                ['key' => 'budget', 'slug' => $u('calculer-son-budget-achat', 'calculating-your-buying-budget'), 'category' => 'acheter', 'tags' => [$T('Financement', 'Financing')],
                    'title' => $T('Calculer son budget d’achat', 'Working out your buying budget'),
                    'excerpt' => $T('Apport, mensualités, frais annexes : le vrai coût d’un achat immobilier.', 'Down payment, monthly payments, extra costs: the real cost of buying a home.'),
                    'photo' => ['immobilier-article-budget', 'calculator', $T('Couple faisant des calculs avec une calculatrice et des documents à la table de la cuisine', 'Couple doing sums with a calculator and paperwork at the kitchen table')],
                    'heading' => $T('Les postes à prévoir', 'What to budget for'),
                    'body' => [
                        $T('Votre budget ne se limite pas au prix affiché : ajoutez les frais d’acte et taxes, les éventuels frais de garantie bancaire et les travaux.', 'Your budget is not just the listed price: add closing costs and taxes, any loan guarantee fees and renovation work.'),
                        $T('Les banques regardent votre apport, la stabilité de vos revenus et la part de vos revenus consacrée aux mensualités. Une simulation avec un courtier clarifie votre capacité réelle.', 'Lenders look at your down payment, income stability and the share of income going to repayments. A simulation with a broker clarifies what you can really afford.'),
                        $T('Gardez une réserve pour les imprévus des premiers mois : déménagement, mobilier, petites réparations.', 'Keep a reserve for the unexpected in the first months: moving, furniture, minor repairs.'),
                    ]],
                ['key' => 'preparer', 'slug' => $u('preparer-sa-maison-pour-la-vente', 'getting-your-home-ready-to-sell'), 'category' => 'vendre', 'tags' => [$T('Home staging', 'Home staging')],
                    'title' => $T('Préparer sa maison pour la vente', 'Getting your home ready to sell'),
                    'excerpt' => $T('Désencombrer, réparer, éclairer : les gestes simples qui font vendre plus vite.', 'Declutter, repair, brighten: simple steps that help you sell faster.'),
                    'photo' => ['immobilier-article-vente', 'sofa', $T('Salon épuré et lumineux préparé pour une visite, avec fleurs sur la table', 'Clean, bright living room staged for a viewing, with flowers on the table')],
                    'quote' => $T('« Les acheteurs se décident souvent dans les premières minutes de la visite. »', '“Buyers often make up their minds in the first few minutes of a viewing.”'),
                    'body' => [
                        $T('Une maison désencombrée paraît plus grande : rangez, retirez les objets personnels et laissez circuler la lumière.', 'A decluttered home looks bigger: tidy up, remove personal items and let the light in.'),
                        $T('Réparez les petits défauts visibles (poignées, joints, ampoules) et rafraîchissez les murs très marqués d’une couleur neutre.', 'Fix small visible flaws (handles, seals, bulbs) and repaint heavily marked walls in a neutral color.'),
                        $T('Enfin, soignez l’entrée et l’extérieur : c’est la première image que garde l’acheteur, sur l’annonce comme sur place.', 'Finally, take care of the entrance and outside: it is the first image buyers remember, online and in person.'),
                    ]],
                ['key' => 'investir', 'slug' => $u('reussir-investissement-locatif', 'successful-rental-investing'), 'category' => 'investir', 'tags' => [$T('Location', 'Rentals')],
                    'title' => $T('Réussir son premier investissement locatif', 'Making your first rental investment a success'),
                    'excerpt' => $T('Emplacement, rendement et gestion : les clés d’un placement rentable et serein.', 'Location, yield and management: the keys to a profitable, low-stress investment.'),
                    'photo' => ['immobilier-article-investissement', 'key', $T('Clés posées sur un bail signé dans un appartement vide et lumineux', 'Keys lying on a signed lease in an empty, bright apartment')],
                    'heading' => $T('Les bonnes questions', 'The right questions'),
                    'body' => [
                        $T('Un bon investissement locatif commence par l’emplacement : proximité des transports, des écoles, des commerces et des employeurs.', 'A good rental investment starts with location: proximity to transit, schools, shops and employers.'),
                        $T('Calculez le rendement net, après charges, taxes, assurance, travaux et périodes sans locataire, plutôt que le rendement brut affiché.', 'Calculate the net yield after charges, taxes, insurance, repairs and vacant periods, rather than the advertised gross yield.'),
                        $T('Décidez dès le départ si vous gérerez vous-même ou si vous confierez la gestion à une agence : le temps aussi a un coût.', 'Decide from the start whether you will manage the property yourself or hand it to an agency: your time has a cost too.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Acheter', 'Buy'), 'url' => $buy],
                    ['label' => $T('Vendre', 'Sell'), 'url' => $sell],
                    ['label' => $T('Louer', 'Rent'), 'url' => $rent],
                    ['label' => $T('Gestion locative', 'Management'), 'url' => $management],
                    ['label' => $T('Conseils', 'Advice'), 'url' => $advice],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Estimer mon bien', 'Value my property'),
                'cta_url' => $valuation,
                'footer_text' => $T('Agence immobilière : achat, vente, location et gestion locative, avec des conseillers qui connaissent chaque quartier. Biens présentés à titre d’exemple.', 'Real estate agency: buying, selling, renting and property management, with agents who know every neighborhood. Listings shown as examples.'),
                'footer_links' => [
                    ['label' => $T('Estimation gratuite', 'Free valuation'), 'url' => $valuation],
                    ['label' => $T('Gestion locative', 'Property management'), 'url' => $management],
                    ['label' => $T('Conseils', 'Advice'), 'url' => $advice],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
