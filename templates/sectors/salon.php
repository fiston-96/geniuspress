<?php

/*
 * Pack « Salon de coiffure et d'esthétique » (secteur Beauté et bien-être) : 8 pages,
 * en français et en anglais. Prestations et tarifs, équipe, réservation en ligne,
 * boutique et cartes cadeaux, avant/après, conseils (blog).
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du salon). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Beauté et bien-être', 'en' => 'Beauty and wellness'],
        'icon' => 'sparkles',
        'keywords' => ['salon', 'coiffure', 'coiffeur', 'coiffeuse', 'barbier', 'esthétique', 'institut de beauté', 'onglerie', 'maquillage', 'couleur', 'balayage', 'hair salon', 'hairdresser', 'barber', 'beauty salon', 'nails', 'makeup', 'hair color'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $p = fn (string $n) => $lang === 'fr' ? $n.' €' : '$'.$n; // prix dans un texte
        $services = $u('/prestations', '/services');
        $team = $u('/equipe', '/our-team');
        $booking = $u('/reservation', '/booking');
        $shop = $u('/boutique', '/shop');
        $results = $u('/avant-apres', '/before-after');
        $tips = $u('/conseils', '/tips');
        $contact = '/contact';

        $serviceItems = [
            ['scissors', $T('Coupe et coiffage', 'Cut and styling'), $T('Coupes femme, homme et enfant, brushing, chignons et coiffures de soirée.', 'Women’s, men’s and children’s cuts, blow-dries, updos and evening styles.')],
            ['palette', $T('Couleur et balayage', 'Color and balayage'), $T('Coloration, mèches, balayage, ombré et patine, avec des produits respectueux du cheveu.', 'Color, highlights, balayage, ombré and toner, with hair-friendly products.')],
            ['droplet', $T('Soins capillaires', 'Hair treatments'), $T('Diagnostic du cuir chevelu, soins profonds, lissage et traitements réparateurs.', 'Scalp diagnosis, deep treatments, smoothing and repair rituals.')],
            ['sparkles', $T('Cheveux bouclés et texturés', 'Curly and textured hair'), $T('Coupes adaptées aux boucles, tresses, vanilles et coiffures protectrices.', 'Curl-friendly cuts, braids, twists and protective styles.')],
            ['user-round', $T('Barbier', 'Barber'), $T('Taille de barbe, rasage à l’ancienne, contours nets et soins du visage homme.', 'Beard trims, traditional shaves, sharp line-ups and men’s facials.')],
            ['brush', $T('Esthétique', 'Beauty treatments'), $T('Manucure, pose de vernis semi-permanent, sourcils, cils et maquillage.', 'Manicures, gel polish, brows, lashes and makeup.')],
        ];
        $testimonials = ['eyebrow' => $T('Avis clients', 'Client reviews'), 'title' => $T('Elles et ils nous font confiance', 'They trust us with their look'), 'rating' => 5, 'items' => [
            [$T('Un vrai diagnostic avant la couleur et un résultat exactement comme je l’imaginais. Je ne vais plus ailleurs.', 'A real consultation before the color and a result exactly as I pictured it. I won’t go anywhere else.'), '[Prénom Nom]', $T('Cliente depuis trois ans', 'Client for three years')],
            [$T('Enfin un salon qui sait couper les cheveux bouclés à sec, boucle par boucle. Mes boucles n’ont jamais été aussi belles.', 'Finally a salon that knows how to cut curly hair dry, curl by curl. My curls have never looked better.'), '[Prénom Nom]', $T('Cliente', 'Client')],
            [$T('Rasage à la serviette chaude, conseils pour l’entretien de la barbe et un accueil impeccable.', 'Hot towel shave, beard care tips and an impeccable welcome.'), '[Prénom Nom]', $T('Client du barbier', 'Barber client')],
        ]];
        $cta = ['title' => $T('Envie de changement ?', 'Ready for a change?'), 'text' => $T('Réservez votre rendez-vous en ligne en quelques clics, ou demandez conseil à notre équipe.', 'Book your appointment online in a few clicks, or ask our team for advice.'),
            'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $booking, 'accent'], [$T('Voir les tarifs', 'See prices'), $services, 'outline-light']]];
        $hours = [$T('Horaires du salon', 'Salon hours'), [
            ['clock', $T('Mardi – vendredi : 9 h – 19 h', 'Tuesday – Friday: 9 am – 7 pm')],
            ['clock', $T('Samedi : 8 h 30 – 18 h', 'Saturday: 8:30 am – 6 pm')],
            ['calendar-days', $T('Nocturne le jeudi jusqu’à 21 h', 'Late opening on Thursdays until 9 pm')],
            ['moon', $T('Fermé le dimanche et le lundi', 'Closed Sunday and Monday')],
        ]];
        $beforeAfter = [
            ['salon-avant-apres-balayage', 'palette', $T('Balayage blond lumineux sur cheveux châtains, vue de dos', 'Bright blonde balayage on brown hair, seen from behind')],
            ['salon-avant-apres-boucles', 'sparkles', $T('Cheveux bouclés définis après une coupe boucle par boucle', 'Defined curly hair after a curl-by-curl cut')],
            ['salon-avant-apres-carre', 'scissors', $T('Carré net et brillant après la coupe', 'Sleek, glossy bob after the cut')],
            ['salon-avant-apres-barbe', 'user-round', $T('Homme à la barbe taillée avec des contours nets', 'Man with a neatly trimmed beard and sharp lines')],
            ['salon-avant-apres-tresses', 'sparkles', $T('Tresses fines et régulières réalisées au salon', 'Fine, even braids done at the salon')],
            ['salon-avant-apres-ongles', 'brush', $T('Mains avec une manucure et un vernis nude soignés', 'Hands with a neat manicure and nude polish')],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Salon de coiffure et d’esthétique : coupe, couleur, soins, barbier, ongles et maquillage. Réservez votre rendez-vous en ligne.', 'Hair and beauty salon: cuts, color, treatments, barber, nails and makeup. Book your appointment online in a few clicks.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Coiffure · Couleur · Esthétique', 'Hair · Color · Beauty'),
                    'title' => $T('Votre style, sublimé par des experts', 'Your style, elevated by experts'),
                    'text' => $T('Coupes sur mesure, couleurs lumineuses et soins attentionnés, pour tous les types de cheveux. Prenez le temps de vous faire du bien.', 'Tailored cuts, luminous color and thoughtful care, for every hair type. Take the time to treat yourself.'),
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $booking], [$T('Nos prestations', 'Our services'), $services]],
                    'photo' => ['salon-accueil', 'scissors', $T('Coiffeuse réalisant un brushing dans un salon lumineux et moderne', 'Hairdresser blow-drying a client’s hair in a bright, modern salon')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos prestations', 'Our services'), 'title' => $T('Tout pour vos cheveux et votre beauté', 'Everything for your hair and beauty'), 'intro' => $T('Femmes, hommes et enfants : une équipe formée à toutes les textures et à toutes les envies.', 'Women, men and children: a team trained for every texture and every wish.'), 'items' => $serviceItems]),
                B::split($theme, [
                    'eyebrow' => $T('L’expérience du salon', 'The salon experience'),
                    'title' => $T('Un moment rien que pour vous', 'A moment just for you'),
                    'html' => '<p>'.e($T('Chaque rendez-vous commence par un vrai échange : vos habitudes, votre style de vie, l’entretien que vous êtes prête à consacrer. Nous vous conseillons honnêtement, puis nous prenons soin de vous dans un cadre calme et chaleureux.', 'Every appointment starts with a real conversation: your habits, your lifestyle, the upkeep you are ready for. We give honest advice, then take care of you in a calm, welcoming space.')).'</p>',
                    'bullets' => [$T('Diagnostic offert avant chaque prestation', 'Free consultation before every service'), $T('Produits professionnels, sans compromis sur la qualité', 'Professional products, no compromise on quality'), $T('Boisson offerte et wifi pendant votre pause', 'Complimentary drink and wifi while you relax')],
                    'buttons' => [[$T('Rencontrer l’équipe', 'Meet the team'), $team, 'outline']],
                    'photo' => ['salon-interieur', 'armchair', $T('Intérieur d’un salon de coiffure avec fauteuils, miroirs et plantes', 'Hair salon interior with chairs, mirrors and plants')],
                    'reverse' => true,
                ]),
                B::gallery($theme, ['eyebrow' => $T('Avant / après', 'Before / after'), 'title' => $T('Nos dernières transformations', 'Our latest transformations'), 'photos' => array_slice($beforeAfter, 0, 3), 'bg' => 'light']),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::schedule($theme, ['eyebrow' => $T('Infos pratiques', 'Practical info'), 'title' => $T('Quand venir nous voir', 'When to visit us'), 'groups' => [
                    $hours,
                    [$T('Bon à savoir', 'Good to know'), [
                        ['calendar-check', $T('Réservation en ligne 24 h/24', 'Online booking 24/7')],
                        ['gift', $T('Cartes cadeaux en vente au salon et en ligne', 'Gift cards sold in the salon and online')],
                        ['baby', $T('Coupes enfant le mercredi et le samedi', 'Children’s cuts on Wednesdays and Saturdays')],
                        ['accessibility', $T('Salon de plain-pied, accessible à tous', 'Step-free salon, accessible to all')],
                    ]],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('prestations', 'services'), 'title' => $T('Prestations et tarifs', 'Services and prices'),
            'description' => $T('Tarifs de coupe, couleur, soins, barbier et esthétique selon la longueur des cheveux. Prix clairs, diagnostic offert avant chaque prestation.', 'Prices for cuts, color, treatments, barber and beauty services by hair length. Clear prices and a free consultation before every service.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Prestations et tarifs', 'Services and prices'), 'title' => $T('Des tarifs clairs, sans surprise', 'Clear prices, no surprises'), 'intro' => $T('Prix indicatifs, shampooing et coiffage compris. Le tarif exact est confirmé lors du diagnostic.', 'Indicative prices, shampoo and styling included. The exact price is confirmed during the consultation.')]),
                M::table($theme, ['eyebrow' => $T('Coiffure', 'Hair'), 'title' => $T('Coupe, couleur et soins', 'Cuts, color and treatments'), 'rows' => [
                    [$T('Prestation', 'Service'), $T('Cheveux courts', 'Short hair'), $T('Mi-longs', 'Medium'), $T('Longs', 'Long')],
                    [$T('Coupe et brushing', 'Cut and blow-dry'), $p('38'), $p('45'), $p('52')],
                    [$T('Brushing seul', 'Blow-dry only'), $p('25'), $p('30'), $p('35')],
                    [$T('Coloration racines', 'Root color'), $p('45'), $p('50'), $p('55')],
                    [$T('Balayage ou mèches', 'Balayage or highlights'), $p('75'), $p('95'), $p('120')],
                    [$T('Soin profond réparateur', 'Deep repair treatment'), $p('20'), $p('25'), $p('30')],
                    [$T('Coupe cheveux bouclés à sec', 'Dry curly cut'), $p('48'), $p('55'), $p('62')],
                ], 'note' => $T('Coupe enfant (moins de 12 ans) : ', 'Children’s cut (under 12): ').$p('20').'.']),
                M::table($theme, ['eyebrow' => $T('Homme et barbier', 'Men and barber'), 'title' => $T('Coupes homme et soins de la barbe', 'Men’s cuts and beard care'), 'bg' => 'light', 'rows' => [
                    [$T('Prestation', 'Service'), $T('Durée', 'Duration'), $T('Prix', 'Price')],
                    [$T('Coupe homme', 'Men’s cut'), '30 min', $p('28')],
                    [$T('Coupe et taille de barbe', 'Cut and beard trim'), '45 min', $p('40')],
                    [$T('Rasage à la serviette chaude', 'Hot towel shave'), '30 min', $p('30')],
                    [$T('Contours et dégradé', 'Line-up and fade'), '20 min', $p('18')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Esthétique', 'Beauty'), 'title' => $T('Ongles, regard et maquillage', 'Nails, brows and makeup'), 'bg' => 'white', 'groups' => [
                    [$T('Mains et pieds', 'Hands and feet'), [
                        ['brush', $T('Manucure classique — ', 'Classic manicure — ').$p('25')],
                        ['sparkles', $T('Vernis semi-permanent — ', 'Gel polish — ').$p('35')],
                        ['droplet', $T('Beauté des pieds — ', 'Pedicure — ').$p('40')],
                    ]],
                    [$T('Regard', 'Brows and lashes'), [
                        ['eye', $T('Restructuration des sourcils — ', 'Brow shaping — ').$p('18')],
                        ['eye', $T('Rehaussement de cils — ', 'Lash lift — ').$p('55')],
                        ['brush', $T('Teinture des sourcils — ', 'Brow tint — ').$p('15')],
                    ]],
                    [$T('Maquillage', 'Makeup'), [
                        ['palette', $T('Maquillage jour — ', 'Day makeup — ').$p('35')],
                        ['star', $T('Maquillage soirée — ', 'Evening makeup — ').$p('50')],
                        ['heart', $T('Essai et mariée — dès ', 'Bridal with trial — from ').$p('120')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur nos prestations', 'Your questions about our services'), 'intro' => $T('Un doute sur la prestation qui vous convient ? Demandez-nous conseil.', 'Not sure which service suits you? Ask us for advice.'), 'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $booking, 'outline']], 'bg' => 'light', 'items' => [
                    [$T('Faut-il faire un test avant une coloration ?', 'Do I need a test before coloring?'), $T('Pour une première coloration, nous réalisons un test de tolérance 48 heures avant, gratuitement, au salon.', 'For a first color, we carry out a free skin test at the salon 48 hours beforehand.')],
                    [$T('Pourquoi le prix dépend-il de la longueur ?', 'Why does the price depend on length?'), $T('Des cheveux longs demandent plus de produit et plus de temps. Le tarif exact est annoncé avant de commencer.', 'Long hair takes more product and more time. The exact price is announced before we start.')],
                    [$T('Travaillez-vous tous les types de cheveux ?', 'Do you work with all hair types?'), $T('Oui : raides, ondulés, bouclés, frisés ou crépus. Plusieurs coiffeurs sont formés aux coupes et soins des cheveux texturés.', 'Yes: straight, wavy, curly, coily or kinky. Several stylists are trained in cuts and care for textured hair.')],
                    [$T('Combien de temps dure un balayage ?', 'How long does balayage take?'), $T('Comptez de deux à trois heures selon la longueur et l’effet souhaité, soin et coiffage compris.', 'Allow two to three hours depending on length and the desired effect, treatment and styling included.')],
                ]]),
            ]];

        $pages[] = ['key' => 'team', 'slug' => $u('equipe', 'our-team'), 'title' => $T('L’équipe', 'Our team'),
            'description' => $T('Coiffeurs, coloristes, barbier et esthéticiennes : rencontrez l’équipe du salon, ses spécialités et sa passion du métier.', 'Stylists, colorists, barber and beauticians: meet the salon team, their specialties and their passion for the craft of beauty.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’équipe', 'Our team'), 'title' => $T('Des mains expertes, des conseils sincères', 'Expert hands, honest advice'), 'intro' => $T('Choisissez votre créateur ou votre créatrice au moment de réserver.', 'Choose your stylist when you book.')]),
                B::team($theme, ['eyebrow' => $T('Coiffure', 'Hair'), 'title' => $T('Coiffeurs et coloristes', 'Stylists and colorists'), 'items' => [
                    ['[Prénom Nom]', $T('Fondatrice, coiffeuse visagiste', 'Founder, stylist and face-shape expert'), 'salon-equipe-1'],
                    ['[Prénom Nom]', $T('Coloriste, spécialiste du balayage', 'Colorist, balayage specialist'), 'salon-equipe-2'],
                    ['[Prénom Nom]', $T('Coiffeuse, cheveux bouclés et texturés', 'Stylist, curly and textured hair'), 'salon-equipe-3'],
                    ['[Prénom Nom]', $T('Coiffeur, coupes homme et barbier', 'Stylist, men’s cuts and barber'), 'salon-equipe-4'],
                ]]),
                B::team($theme, ['eyebrow' => $T('Esthétique', 'Beauty'), 'title' => $T('Esthéticiennes et maquilleuse', 'Beauticians and makeup artist'), 'bg' => 'light', 'items' => [
                    ['[Prénom Nom]', $T('Esthéticienne, ongles et pieds', 'Beautician, nails and feet'), 'salon-equipe-5'],
                    ['[Prénom Nom]', $T('Esthéticienne, sourcils et cils', 'Beautician, brows and lashes'), 'salon-equipe-6'],
                    ['[Prénom Nom]', $T('Maquilleuse, mariées et événements', 'Makeup artist, brides and events'), 'salon-equipe-7'],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Ce qui nous tient à cœur', 'What matters to us'), 'items' => [
                    ['graduation-cap', $T('Formation continue', 'Ongoing training'), $T('L’équipe se forme chaque année aux nouvelles techniques de coupe et de couleur.', 'The team trains every year in new cutting and color techniques.')],
                    ['shield-check', $T('Hygiène irréprochable', 'Spotless hygiene'), $T('Outils désinfectés après chaque client, serviettes et capes propres à chaque passage.', 'Tools disinfected after every client, fresh towels and capes every time.')],
                    ['heart-handshake', $T('Écoute et honnêteté', 'Listening and honesty'), $T('Nous vous disons ce qui vous ira vraiment, même si c’est moins cher.', 'We tell you what will really suit you, even if it costs less.')],
                ]]),
                B::cta($theme, ['title' => $T('Rejoindre l’équipe', 'Join the team'), 'text' => $T('Coiffeur, coloriste ou esthéticienne passionné ? Nous recrutons des talents attentifs à leurs clients.', 'A passionate stylist, colorist or beautician? We are hiring talented people who care about their clients.'),
                    'buttons' => [[$T('Envoyer ma candidature', 'Send my application'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'booking', 'slug' => $u('reservation', 'booking'), 'title' => $T('Réservation', 'Booking'),
            'description' => $T('Réservez votre rendez-vous de coiffure ou d’esthétique en ligne : choisissez la prestation, le créateur et le créneau qui vous conviennent.', 'Book your hair or beauty appointment online: choose the service, the stylist and the time slot that suit you best, then get a reminder.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Réservation', 'Booking'), 'title' => $T('Réservez en ligne, en deux minutes', 'Book online in two minutes'), 'intro' => $T('Choisissez votre prestation et votre créneau ; nous confirmons par SMS ou par e-mail.', 'Choose your service and time slot; we confirm by text message or email.')]),
                B::steps($theme, ['eyebrow' => $T('Comment ça marche', 'How it works'), 'title' => $T('Votre rendez-vous en quatre étapes', 'Your appointment in four steps'), 'items' => [
                    [$T('La prestation', 'The service'), $T('Coupe, couleur, soin ou esthétique : indiquez ce que vous souhaitez.', 'Cut, color, treatment or beauty: tell us what you would like.')],
                    [$T('Le créateur', 'The stylist'), $T('Choisissez un membre de l’équipe ou laissez-nous vous orienter.', 'Choose a team member or let us guide you.')],
                    [$T('Le créneau', 'The time slot'), $T('Proposez une date et une heure, en journée ou en nocturne.', 'Suggest a date and time, during the day or in the evening.')],
                    [$T('La confirmation', 'Confirmation'), $T('Nous confirmons rapidement et vous envoyons un rappel la veille.', 'We confirm quickly and send you a reminder the day before.')],
                ]]),
                B::contact($theme, [
                    'eyebrow' => $T('Demande de rendez-vous', 'Appointment request'), 'title' => $T('Réservez votre moment', 'Book your moment'),
                    'intro' => $T('Précisez la prestation, la longueur de vos cheveux et vos disponibilités dans le message.', 'Please state the service, your hair length and your availability in the message.'),
                    'items' => [
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Réponse sous deux heures pendant les horaires d’ouverture', 'Reply within two hours during opening hours')],
                    ],
                    'subject' => true, 'button' => $T('Demander mon rendez-vous', 'Request my appointment'), 'bg' => 'light',
                ]),
                B::schedule($theme, ['eyebrow' => $T('Conditions', 'Policies'), 'title' => $T('Avant votre venue', 'Before your visit'), 'bg' => 'white', 'groups' => [
                    $hours,
                    [$T('Annulation et retard', 'Cancellation and lateness'), [
                        ['calendar-check', $T('Annulation gratuite jusqu’à 24 h avant', 'Free cancellation up to 24 hours before')],
                        ['wallet', $T('Acompte de 30 % pour les prestations de plus de deux heures', '30% deposit for services over two hours')],
                        ['timer', $T('Au-delà de 15 min de retard, la prestation peut être raccourcie', 'Over 15 minutes late, the service may be shortened')],
                        ['credit-card', $T('Paiement par carte bancaire, espèces ou paiement mobile', 'Payment by card, cash or mobile payment')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions sur la réservation', 'Booking questions'), 'bg' => 'light', 'items' => [
                    [$T('Puis-je réserver pour plusieurs personnes ?', 'Can I book for several people?'), $T('Oui, indiquez-le dans votre demande : nous organisons les rendez-vous en parallèle quand c’est possible.', 'Yes, mention it in your request: we schedule the appointments side by side when possible.')],
                    [$T('Comment modifier mon rendez-vous ?', 'How do I change my appointment?'), $T('Répondez au message de confirmation ou appelez le salon au moins 24 heures à l’avance.', 'Reply to the confirmation message or call the salon at least 24 hours ahead.')],
                    [$T('Proposez-vous des rendez-vous pour les mariées ?', 'Do you offer bridal appointments?'), $T('Oui, avec un essai coiffure et maquillage quelques semaines avant le jour J. Réservez tôt pour les samedis.', 'Yes, with a hair and makeup trial a few weeks before the big day. Book early for Saturdays.')],
                ]]),
            ]];

        $pages[] = ['key' => 'shop', 'slug' => $u('boutique', 'shop'), 'title' => $T('Boutique et cartes cadeaux', 'Shop and gift cards'),
            'description' => $T('Shampooings, soins, huiles et accessoires sélectionnés par nos coiffeurs, et cartes cadeaux à offrir pour toutes les occasions.', 'Shampoos, treatments, oils and tools selected by our stylists, plus gift cards to give for every occasion of the year.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Boutique', 'Shop'), 'title' => $T('Prolongez le résultat à la maison', 'Keep the look going at home'), 'intro' => $T('Des produits professionnels que nous utilisons au salon, choisis selon votre type de cheveux.', 'Professional products we use in the salon, chosen for your hair type.')]),
                B::cards($theme, ['eyebrow' => $T('Nos produits', 'Our products'), 'title' => $T('La sélection de nos coiffeurs', 'Our stylists’ picks'), 'items' => [
                    ['salon-produit-shampooing', 'droplet', $T('Shampooing doux', 'Gentle shampoo'), $T('Sans sulfates, pour un lavage quotidien qui respecte la couleur. ', 'Sulfate-free, for daily washing that protects color. ').$p('18')],
                    ['salon-produit-masque', 'sparkles', $T('Masque réparateur', 'Repair mask'), $T('Nourrit en profondeur les cheveux secs ou abîmés, en cinq minutes. ', 'Deeply nourishes dry or damaged hair in five minutes. ').$p('24')],
                    ['salon-produit-huile', 'leaf', $T('Huile sublimatrice', 'Finishing oil'), $T('Quelques gouttes pour la brillance et contre les frisottis. ', 'A few drops for shine and to tame frizz. ').$p('22')],
                    ['salon-produit-boucles', 'waves', $T('Crème pour boucles', 'Curl cream'), $T('Définit et hydrate les boucles, sans effet carton. ', 'Defines and hydrates curls, without crunch. ').$p('20')],
                    ['salon-produit-barbe', 'user-round', $T('Kit barbe', 'Beard kit'), $T('Huile, baume et peigne pour une barbe douce et disciplinée. ', 'Oil, balm and comb for a soft, tidy beard. ').$p('35')],
                    ['salon-produit-brosse', 'brush', $T('Brosse démêlante', 'Detangling brush'), $T('Picots souples qui démêlent sans casser la fibre. ', 'Flexible bristles that detangle without breakage. ').$p('16')],
                ]]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Cartes cadeaux', 'Gift cards'), 'title' => $T('Offrez un moment de beauté', 'Give the gift of beauty'), 'intro' => $T('Valables un an sur toutes les prestations et les produits du salon, en carte imprimée ou par e-mail.', 'Valid for one year on all salon services and products, as a printed card or by email.'), 'items' => [
                    [$T('Carte découverte', 'Discovery card'), '50', $T('€', '$'), '', $T('Pour un brushing, une manucure ou un soin.', 'For a blow-dry, a manicure or a treatment.'), [$T('Valable 12 mois', 'Valid for 12 months'), $T('Utilisable en plusieurs fois', 'Can be used in several visits'), $T('Envoi par e-mail possible', 'Can be sent by email'), '-'.$T('Coffret cadeau', 'Gift box')], $T('Offrir cette carte', 'Give this card')],
                    [$T('Carte transformation', 'Makeover card'), '120', $T('€', '$'), '', $T('Pour une coupe et une couleur complète.', 'For a cut and full color.'), [$T('Valable 12 mois', 'Valid for 12 months'), $T('Diagnostic personnalisé', 'Personal consultation'), $T('Envoi par e-mail possible', 'Can be sent by email'), $T('Pochette cadeau offerte', 'Free gift envelope')], $T('Offrir cette carte', 'Give this card'), true, $T('La plus offerte', 'Most gifted')],
                    [$T('Coffret beauté', 'Beauty box'), '180', $T('€', '$'), '', $T('Coiffure, manucure et un produit de soin.', 'Hair, manicure and a care product.'), [$T('Valable 12 mois', 'Valid for 12 months'), $T('Trois prestations au choix', 'Three services of your choice'), $T('Un produit offert', 'One product included'), $T('Coffret cadeau', 'Gift box')], $T('Offrir ce coffret', 'Give this box')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Conseil personnalisé', 'Personal advice'),
                    'title' => $T('Le bon produit pour vos cheveux', 'The right product for your hair'),
                    'html' => '<p>'.e($T('Un produit n’est efficace que s’il convient à votre nature de cheveux. Lors de votre rendez-vous, votre coiffeur vous explique la routine adaptée et vous montre les bons gestes.', 'A product only works if it suits your hair type. During your appointment, your stylist explains the right routine and shows you how to use it.')).'</p>',
                    'bullets' => [$T('Conseils gratuits, sans obligation d’achat', 'Free advice, no obligation to buy'), $T('Échantillons disponibles sur demande', 'Samples available on request'), $T('Retrait au salon ou envoi à domicile', 'Pick up at the salon or home delivery')],
                    'photo' => ['salon-boutique', 'shopping-bag', $T('Étagère de produits capillaires dans un salon de coiffure', 'Shelf of hair care products in a hair salon')],
                    'bg' => 'white',
                ]),
            ]];

        $pages[] = ['key' => 'results', 'slug' => $u('avant-apres', 'before-after'), 'title' => $T('Avant / après', 'Before / after'),
            'description' => $T('Balayages, coupes, boucles, tresses, barbe et ongles : découvrez en images les transformations réalisées par notre équipe au salon.', 'Balayage, cuts, curls, braids, beards and nails: discover in pictures the transformations created by our team at the salon.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Avant / après', 'Before / after'), 'title' => $T('Des transformations qui font sourire', 'Transformations that make people smile'), 'intro' => $T('Photos publiées avec l’accord de nos clientes et clients, sans filtre ni retouche.', 'Photos shared with our clients’ consent, with no filters or retouching.')]),
                B::gallery($theme, ['cols' => 3, 'photos' => $beforeAfter]),
                B::split($theme, [
                    'eyebrow' => $T('Notre méthode', 'Our method'),
                    'title' => $T('Un résultat qui dure, pas seulement le jour même', 'A result that lasts, not just on the day'),
                    'html' => '<p>'.e($T('Une belle transformation se prépare : nous étudions votre base, l’état de vos cheveux et votre routine pour proposer un résultat réaliste, qui reste beau des semaines après votre passage.', 'A great transformation takes preparation: we study your base, the condition of your hair and your routine to suggest a realistic result that still looks good weeks after your visit.')).'</p>',
                    'bullets' => [$T('Photo de départ pour suivre l’évolution', 'Starting photo to track progress'), $T('Plan en plusieurs séances si nécessaire', 'Plan over several sessions if needed'), $T('Conseils d’entretien écrits', 'Written aftercare advice')],
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $booking]],
                    'photo' => ['salon-diagnostic', 'search', $T('Coiffeuse examinant les longueurs d’une cliente devant le miroir', 'Stylist examining a client’s lengths in front of the mirror')],
                    'reverse' => true, 'bg' => 'light',
                ]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'tips', 'slug' => $u('conseils', 'tips'), 'title' => $T('Conseils beauté', 'Beauty tips'),
            'description' => $T('Conseils de nos coiffeurs et esthéticiennes : routines capillaires, entretien de la couleur, boucles, barbe et ongles, expliqués simplement.', 'Advice from our stylists and beauticians: hair routines, color care, curls, beards and nails, explained simply by our team.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Conseils', 'Tips'), 'title' => $T('Les conseils de nos experts', 'Advice from our experts'), 'intro' => $T('Des gestes simples pour prendre soin de vos cheveux et de votre peau entre deux rendez-vous.', 'Simple habits to care for your hair and skin between appointments.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire le conseil', 'Read the tip')]),
                B::features($theme, ['eyebrow' => $T('Les essentiels', 'The essentials'), 'title' => $T('Trois règles d’or', 'Three golden rules'), 'bg' => 'light', 'items' => [
                    ['droplet', $T('Laver moins, mais mieux', 'Wash less, but better'), $T('Un shampooing doux deux ou trois fois par semaine suffit à la plupart des cheveux.', 'A gentle shampoo two or three times a week is enough for most hair.')],
                    ['sun', $T('Protéger de la chaleur', 'Protect from heat'), $T('Un protecteur thermique avant le sèche-cheveux ou le fer, toujours.', 'Heat protectant before the dryer or flat iron, always.')],
                    ['scissors', $T('Couper régulièrement', 'Trim regularly'), $T('Rafraîchir les pointes toutes les six à dix semaines garde des longueurs saines.', 'Trimming the ends every six to ten weeks keeps lengths healthy.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Adresse, téléphone, e-mail et horaires du salon de coiffure et d’esthétique. Une question ? Écrivez-nous, nous répondons vite.', 'Address, phone, email and opening hours of the hair and beauty salon. Have a question? Write to us and we will reply quickly.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Passez nous voir', 'Come and see us'), 'intro' => $T('Une question sur une prestation, un produit ou une carte cadeau ? Nous sommes à votre écoute.', 'A question about a service, a product or a gift card? We are here to help.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Écrivez-nous, appelez-nous ou passez au salon pendant les horaires d’ouverture.', 'Write, call or drop by the salon during opening hours.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Mardi – samedi, nocturne le jeudi', 'Tuesday – Saturday, late opening Thursday')],
                    ],
                    'social' => ['instagram', 'facebook', 'tiktok', 'pinterest'],
                    'subject' => true,
                    'bg' => 'white',
                ]),
                B::schedule($theme, ['eyebrow' => $T('Accès', 'Getting here'), 'title' => $T('Venir au salon', 'Getting to the salon'), 'groups' => [
                    $hours,
                    [$T('Accès', 'Access'), [
                        ['car', $T('Parking public à 2 minutes à pied', 'Public parking a 2-minute walk away')],
                        ['bus', $T('Arrêt de bus juste devant le salon', 'Bus stop right outside the salon')],
                        ['accessibility', $T('Entrée de plain-pied', 'Step-free entrance')],
                        ['wifi', $T('Wifi gratuit pour nos clients', 'Free wifi for clients')],
                    ]],
                ]]),
                B::cta($theme, ['title' => $T('Prête pour votre rendez-vous ?', 'Ready for your appointment?'), 'text' => $T('Réservez en ligne en deux minutes, nous confirmons rapidement.', 'Book online in two minutes, we confirm quickly.'), 'buttons' => [[$T('Réserver maintenant', 'Book now'), $booking, 'accent']]]),
            ]];

        $blog = [
            'base' => $u('conseils', 'tips'),
            'title' => $T('Conseils beauté', 'Beauty tips'),
            'categories' => ['cheveux' => $T('Cheveux', 'Hair'), 'couleur' => $T('Couleur', 'Color'), 'beaute' => $T('Beauté', 'Beauty')],
            'posts' => [
                ['key' => 'boucles', 'slug' => $u('routine-cheveux-boucles', 'curly-hair-routine'), 'category' => 'cheveux', 'tags' => [$T('Boucles', 'Curls')],
                    'title' => $T('La routine idéale pour des boucles définies', 'The ideal routine for defined curls'),
                    'excerpt' => $T('Lavage, hydratation et séchage : les gestes qui changent tout.', 'Washing, moisture and drying: the steps that change everything.'),
                    'photo' => ['salon-blog-boucles', 'sparkles', $T('Femme aux cheveux bouclés souriante, lumière naturelle', 'Smiling woman with curly hair in natural light')],
                    'heading' => $T('Trois gestes essentiels', 'Three essential steps'),
                    'body' => [
                        $T('Les cheveux bouclés sont naturellement plus secs : ils ont besoin d’un lavage doux et d’un après-shampooing généreux à chaque passage sous l’eau.', 'Curly hair is naturally drier: it needs gentle cleansing and a generous conditioner every time it is washed.'),
                        $T('Appliquez votre crème sur cheveux trempés, puis froissez les mèches vers le haut. Séchez avec un t-shirt en coton ou un diffuseur, sans frotter.', 'Apply your cream to soaking-wet hair, then scrunch upward. Dry with a cotton T-shirt or a diffuser, without rubbing.'),
                        $T('Une coupe adaptée, réalisée à sec boucle par boucle, donne enfin à vos boucles la forme qu’elles méritent.', 'A suitable cut, done dry curl by curl, finally gives your curls the shape they deserve.'),
                    ]],
                ['key' => 'couleur', 'slug' => $u('faire-durer-sa-couleur', 'make-your-color-last'), 'category' => 'couleur', 'tags' => [$T('Entretien', 'Care')],
                    'title' => $T('Faire durer sa couleur plus longtemps', 'Making your color last longer'),
                    'excerpt' => $T('Eau tiède, soins adaptés, protection solaire : nos astuces de coloristes.', 'Lukewarm water, the right care, sun protection: our colorists’ tips.'),
                    'photo' => ['salon-blog-couleur', 'palette', $T('Coloriste appliquant une couleur au pinceau sur des mèches', 'Colorist applying color to hair strands with a brush')],
                    'quote' => $T('« Une belle couleur, c’est 50 % de technique au salon et 50 % d’entretien à la maison. »', '“Great color is 50% technique in the salon and 50% care at home.”'),
                    'body' => [
                        $T('Attendez 48 heures avant le premier shampooing pour laisser les pigments se fixer.', 'Wait 48 hours before the first shampoo to let the pigments settle.'),
                        $T('Préférez une eau tiède et un shampooing spécial cheveux colorés, et protégez vos cheveux du soleil et du chlore en été.', 'Use lukewarm water and a shampoo for color-treated hair, and protect your hair from sun and chlorine in summer.'),
                        $T('Une patine entre deux couleurs ravive les reflets sans abîmer la fibre.', 'A toner between two color appointments revives the tones without damaging the hair.'),
                    ]],
                ['key' => 'ongles', 'slug' => $u('prendre-soin-de-ses-ongles', 'caring-for-your-nails'), 'category' => 'beaute', 'tags' => [$T('Ongles', 'Nails')],
                    'title' => $T('Des ongles en bonne santé entre deux manucures', 'Healthy nails between manicures'),
                    'excerpt' => $T('Hydratation, lime et pauses : prendre soin de ses ongles au quotidien.', 'Moisture, filing and breaks: everyday nail care.'),
                    'photo' => ['salon-blog-ongles', 'brush', $T('Esthéticienne réalisant une manucure dans un institut', 'Beautician doing a manicure in a beauty salon')],
                    'heading' => $T('Nos recommandations', 'Our recommendations'),
                    'body' => [
                        $T('Hydratez vos cuticules chaque soir avec une goutte d’huile : c’est le secret d’ongles souples qui ne cassent pas.', 'Moisturize your cuticles every evening with a drop of oil: it is the secret to supple nails that do not break.'),
                        $T('Limez toujours dans le même sens et portez des gants pour la vaisselle et le ménage.', 'Always file in the same direction and wear gloves for dishes and cleaning.'),
                        $T('Après plusieurs poses de vernis semi-permanent, accordez à vos ongles une pause de quelques semaines.', 'After several rounds of gel polish, give your nails a few weeks off.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Prestations', 'Services'), 'url' => $services],
                    ['label' => $T('Équipe', 'Team'), 'url' => $team],
                    ['label' => $T('Avant / après', 'Before / after'), 'url' => $results],
                    ['label' => $T('Boutique', 'Shop'), 'url' => $shop],
                    ['label' => $T('Conseils', 'Tips'), 'url' => $tips],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Réserver', 'Book now'),
                'cta_url' => $booking,
                'footer_text' => $T('Salon de coiffure et d’esthétique : coupe, couleur, soins, barbier et beauté des mains, pour toutes et tous.', 'Hair and beauty salon: cuts, color, treatments, barber and nail care, for everyone.'),
                'footer_links' => [
                    ['label' => $T('Prestations et tarifs', 'Services and prices'), 'url' => $services],
                    ['label' => $T('Cartes cadeaux', 'Gift cards'), 'url' => $shop],
                    ['label' => $T('Réserver', 'Book now'), 'url' => $booking],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
