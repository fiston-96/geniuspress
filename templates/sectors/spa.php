<?php

/*
 * Pack « Spa et institut de bien-être » (secteur Beauté et bien-être) : 8 pages,
 * en français et en anglais. Soins et massages, rituels et journées, yoga et
 * méditation, cartes cadeaux, philosophie, journal (blog).
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du spa). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Beauté et bien-être', 'en' => 'Beauty and wellness'],
        'icon' => 'sparkles',
        'keywords' => ['spa', 'institut', 'bien-être', 'massage', 'soins du visage', 'soins du corps', 'hammam', 'sauna', 'yoga', 'méditation', 'relaxation', 'wellness', 'day spa', 'facial', 'body treatment', 'meditation', 'retreat'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $p = fn (string $n) => $lang === 'fr' ? $n.' €' : '$'.$n; // prix dans un texte
        $cur = $T('€', '$');
        $treatments = $u('/soins', '/treatments');
        $rituals = $u('/rituels', '/rituals');
        $yoga = $u('/yoga-meditation', '/yoga-meditation');
        $gifts = $u('/cartes-cadeaux', '/gift-cards');
        $philosophy = $u('/philosophie', '/philosophy');
        $journal = '/journal';
        $contact = '/contact';

        $testimonials = ['eyebrow' => $T('Ils sont repartis légers', 'They left feeling light'), 'title' => $T('Paroles de nos hôtes', 'In our guests’ words'), 'rating' => 5, 'items' => [
            [$T('Le massage aux pierres chaudes le plus apaisant de ma vie. Le temps s’est arrêté pendant une heure et demie.', 'The most soothing hot stone massage of my life. Time stood still for an hour and a half.'), '[Prénom Nom]', $T('Hôte du spa', 'Spa guest')],
            [$T('Nous avons offert la journée évasion à ma mère : elle en parle encore. Un accueil d’une grande douceur.', 'We gave my mother the escape day: she still talks about it. Such a gentle welcome.'), '[Prénom Nom]', $T('Cliente', 'Client')],
            [$T('Les cours de yoga du matin ont changé mon rapport au stress. Petits groupes, professeurs attentifs.', 'The morning yoga classes changed how I handle stress. Small groups, attentive teachers.'), '[Prénom Nom]', $T('Élève de yoga', 'Yoga student')],
        ]];
        $cta = ['title' => $T('Accordez-vous une pause', 'Give yourself a break'), 'text' => $T('Réservez un soin, un rituel ou un cours : notre équipe vous conseille sur le moment qui vous fera le plus de bien.', 'Book a treatment, a ritual or a class: our team will help you choose what will do you the most good.'),
            'buttons' => [[$T('Réserver un soin', 'Book a treatment'), $contact, 'accent'], [$T('Offrir une carte cadeau', 'Give a gift card'), $gifts, 'outline-light']]];
        $signature = [
            ['spa-rituel-evasion', 'sparkles', $T('Rituel gommage et massage', 'Scrub and massage ritual'), $T('Gommage aux sels, enveloppement et massage relaxant : 2 h pour lâcher prise.', 'Salt scrub, body wrap and relaxing massage: 2 hours to let go.'), $rituals],
            ['spa-pierres-chaudes', 'flame', $T('Massage aux pierres chaudes', 'Hot stone massage'), $T('La chaleur des pierres de basalte dénoue les tensions en profondeur.', 'The warmth of basalt stones releases deep-seated tension.'), $treatments],
            ['spa-soin-visage', 'droplet', $T('Soin éclat du visage', 'Radiance facial'), $T('Nettoyage, exfoliation douce et masque pour une peau reposée et lumineuse.', 'Cleansing, gentle exfoliation and a mask for rested, glowing skin.'), $treatments],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Spa et institut de bien-être : massages, soins du visage et du corps, rituels, yoga et méditation. Offrez-vous une vraie pause.', 'Spa and wellness center: massages, facials and body treatments, rituals, yoga and meditation. Treat yourself to a real break.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Spa · Soins · Yoga', 'Spa · Treatments · Yoga'),
                    'title' => $T('Ralentir, respirer, se retrouver', 'Slow down, breathe, reconnect'),
                    'text' => $T('Un lieu de calme pour prendre soin du corps et de l’esprit : massages, soins naturels, espace détente, yoga et méditation.', 'A calm place to care for body and mind: massages, natural treatments, a relaxation area, yoga and meditation.'),
                    'buttons' => [[$T('Réserver un soin', 'Book a treatment'), $contact], [$T('Découvrir les soins', 'Explore treatments'), $treatments]],
                    'photo' => ['spa-accueil', 'flower-2', $T('Salle de soin d’un spa avec bougies, serviettes roulées et lumière tamisée', 'Spa treatment room with candles, rolled towels and soft lighting')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos univers', 'Our worlds'), 'title' => $T('Quatre façons de prendre soin de soi', 'Four ways to take care of yourself'), 'items' => [
                    ['hand-heart', $T('Massages', 'Massages'), $T('Relaxants, toniques ou profonds, adaptés à vos besoins du jour.', 'Relaxing, invigorating or deep, tailored to what you need today.')],
                    ['droplet', $T('Soins visage et corps', 'Face and body'), $T('Des protocoles naturels pour une peau nourrie et apaisée.', 'Natural protocols for nourished, soothed skin.')],
                    ['waves', $T('Espace détente', 'Relaxation area'), $T('Hammam, sauna, bassin chauffé et tisanerie.', 'Steam room, sauna, heated pool and tea lounge.')],
                    ['leaf', $T('Yoga et méditation', 'Yoga and meditation'), $T('Des cours en petits groupes pour tous les niveaux.', 'Small-group classes for every level.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Notre philosophie', 'Our philosophy'),
                    'title' => $T('Le temps de vous écouter', 'Time to listen to you'),
                    'html' => '<p>'.e($T('Chaque soin commence par un court échange : votre état de fatigue, vos zones de tension, vos envies. Nos praticiens adaptent la pression, les huiles et le rythme pour que le soin vous ressemble.', 'Every treatment starts with a short conversation: how tired you feel, where you hold tension, what you would like. Our therapists adapt pressure, oils and pace so the treatment suits you.')).'</p>',
                    'bullets' => [$T('Praticiens diplômés et expérimentés', 'Qualified, experienced therapists'), $T('Huiles et produits d’origine naturelle', 'Oils and products of natural origin'), $T('Accès à l’espace détente avant ou après le soin', 'Relaxation area access before or after your treatment')],
                    'buttons' => [[$T('Notre philosophie', 'Our philosophy'), $philosophy, 'outline']],
                    'photo' => ['spa-massage', 'hand-heart', $T('Praticienne réalisant un massage du dos dans une cabine apaisante', 'Therapist giving a back massage in a peaceful treatment room')],
                    'reverse' => true, 'bg' => 'light',
                ]),
                B::cards($theme, ['eyebrow' => $T('Soins signature', 'Signature treatments'), 'title' => $T('Les préférés de nos hôtes', 'Our guests’ favorites'), 'more' => $T('En savoir plus', 'Learn more'), 'items' => $signature]),
                B::quote($theme, ['text' => $T('Prendre soin de soi n’est pas un luxe : c’est la condition pour prendre soin des autres.', 'Taking care of yourself is not a luxury: it is what allows you to care for others.'), 'source' => $T('— [Prénom Nom], fondatrice du spa', '— [Prénom Nom], spa founder'), 'bg' => 'light']),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'treatments', 'slug' => $u('soins', 'treatments'), 'title' => $T('Soins et massages', 'Treatments and massages'),
            'description' => $T('Massages relaxants, profonds ou aux pierres chaudes, soins du visage et du corps : la carte des soins du spa, avec durées et prix.', 'Relaxing, deep tissue or hot stone massages, facials and body treatments: the spa treatment menu, with durations and prices.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('La carte des soins', 'Treatment menu'), 'title' => $T('Des soins pensés pour vous', 'Treatments designed around you'), 'intro' => $T('Chaque soin donne accès à l’espace détente pendant une heure. Arrivez 15 minutes à l’avance.', 'Every treatment includes one hour in the relaxation area. Please arrive 15 minutes early.')]),
                B::schedule($theme, ['bg' => 'white', 'groups' => [
                    [$T('Massages', 'Massages'), [
                        ['hand-heart', $T('Massage relaxant, 60 min — ', 'Relaxing massage, 60 min — ').$p('75')],
                        ['dumbbell', $T('Massage profond, 60 min — ', 'Deep tissue massage, 60 min — ').$p('85')],
                        ['flame', $T('Pierres chaudes, 90 min — ', 'Hot stones, 90 min — ').$p('110')],
                        ['person-standing', $T('Réflexologie plantaire, 45 min — ', 'Foot reflexology, 45 min — ').$p('60')],
                    ]],
                    [$T('Soins du visage', 'Facials'), [
                        ['droplet', $T('Soin éclat, 50 min — ', 'Radiance facial, 50 min — ').$p('70')],
                        ['sparkles', $T('Soin hydratation intense, 60 min — ', 'Intense hydration facial, 60 min — ').$p('80')],
                        ['sun', $T('Soin anti-âge, 75 min — ', 'Anti-aging facial, 75 min — ').$p('95')],
                        ['user-round', $T('Soin homme, 45 min — ', 'Men’s facial, 45 min — ').$p('65')],
                    ]],
                    [$T('Soins du corps', 'Body treatments'), [
                        ['waves', $T('Gommage aux sels marins, 30 min — ', 'Sea salt scrub, 30 min — ').$p('45')],
                        ['leaf', $T('Enveloppement à l’argile, 45 min — ', 'Clay body wrap, 45 min — ').$p('55')],
                        ['heart', $T('Massage future maman, 60 min — ', 'Pregnancy massage, 60 min — ').$p('80')],
                        ['moon', $T('Soin dos détente, 30 min — ', 'Back relief treatment, 30 min — ').$p('45')],
                    ]],
                ]]),
                B::features($theme, ['eyebrow' => $T('Bien choisir', 'Choosing well'), 'title' => $T('Quel massage pour vous ?', 'Which massage is right for you?'), 'bg' => 'light', 'items' => [
                    ['moon', $T('Pour lâcher prise', 'To let go'), $T('Le massage relaxant, aux gestes lents et enveloppants, apaise le système nerveux.', 'The relaxing massage, with slow, flowing strokes, calms the nervous system.')],
                    ['dumbbell', $T('Pour les tensions', 'For tension'), $T('Le massage profond travaille les muscles noués, idéal après le sport ou une longue journée assise.', 'Deep tissue work targets knotted muscles, ideal after sport or a long day at a desk.')],
                    ['flame', $T('Pour se réchauffer', 'To warm up'), $T('Les pierres chaudes diffusent une chaleur douce qui détend en profondeur.', 'Hot stones spread a gentle heat that relaxes you deeply.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Nos produits', 'Our products'),
                    'title' => $T('Des ingrédients choisis avec soin', 'Carefully chosen ingredients'),
                    'html' => '<p>'.e($T('Huiles végétales pressées à froid, argiles, sels marins et extraits de plantes : nous privilégions des produits d’origine naturelle, sans parfums agressifs, adaptés aux peaux sensibles.', 'Cold-pressed plant oils, clays, sea salts and botanical extracts: we favor products of natural origin, without harsh fragrances, suitable for sensitive skin.')).'</p>',
                    'bullets' => [$T('Huiles neutres disponibles sur demande', 'Unscented oils available on request'), $T('Produits testés sous contrôle dermatologique', 'Dermatologically tested products'), $T('Linge en coton lavé à haute température', 'Cotton linen washed at high temperature')],
                    'photo' => ['spa-produits', 'leaf', $T('Flacons d’huiles de massage, pierres et feuilles vertes sur un plateau en bois', 'Massage oil bottles, stones and green leaves on a wooden tray')],
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Avant votre soin', 'Before your treatment'), 'intro' => $T('Un souci de santé ? Parlez-en lors de la réservation, nous adaptons le soin.', 'A health concern? Mention it when you book and we will adapt the treatment.'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'outline']], 'bg' => 'light', 'items' => [
                    [$T('Que dois-je porter ?', 'What should I wear?'), $T('Peignoir, serviettes, chaussons et sous-vêtements jetables sont fournis. Apportez simplement un maillot pour l’espace détente.', 'Robe, towels, slippers and disposable underwear are provided. Just bring a swimsuit for the relaxation area.')],
                    [$T('Puis-je recevoir un massage enceinte ?', 'Can I have a massage while pregnant?'), $T('Oui, à partir du quatrième mois, avec notre massage future maman, sur avis favorable de votre médecin.', 'Yes, from the fourth month, with our pregnancy massage and your doctor’s approval.')],
                    [$T('Les massages sont-ils thérapeutiques ?', 'Are the massages therapeutic?'), $T('Nos soins sont des soins de bien-être, non médicaux. Ils ne remplacent pas un avis ou un traitement médical.', 'Our treatments are for well-being and are not medical. They do not replace medical advice or treatment.')],
                    [$T('Puis-je choisir un praticien ou une praticienne ?', 'Can I choose my therapist?'), $T('Oui, précisez votre préférence à la réservation, nous ferons notre possible.', 'Yes, state your preference when booking and we will do our best.')],
                ]]),
            ]];

        $pages[] = ['key' => 'rituals', 'slug' => $u('rituels', 'rituals'), 'title' => $T('Rituels et journées', 'Rituals and spa days'),
            'description' => $T('Rituels de soins, demi-journées et journées spa, en solo ou en duo : forfaits avec accès à l’espace détente, déjeuner léger inclus.', 'Treatment rituals, half days and full spa days, solo or for two: packages with relaxation area access and a light lunch included.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Rituels et forfaits', 'Rituals and packages'), 'title' => $T('Une parenthèse de quelques heures', 'A few hours just for you'), 'intro' => $T('Plusieurs soins enchaînés, l’espace détente et un moment de calme : la façon la plus complète de vivre le spa.', 'Several treatments in a row, the relaxation area and a quiet moment: the fullest way to enjoy the spa.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Forfaits', 'Packages'), 'title' => $T('Choisissez votre parenthèse', 'Choose your escape'), 'items' => [
                    [$T('Demi-journée sérénité', 'Serenity half day'), '135', $cur, $T('/ personne', '/ person'), $T('3 heures pour décompresser.', '3 hours to unwind.'), [$T('Massage relaxant 60 min', '60-min relaxing massage'), $T('Accès à l’espace détente', 'Relaxation area access'), $T('Tisane et fruits frais', 'Herbal tea and fresh fruit'), '-'.$T('Déjeuner', 'Lunch')], $T('Réserver', 'Book')],
                    [$T('Journée évasion', 'Escape day'), '240', $cur, $T('/ personne', '/ person'), $T('Une journée complète de soins.', 'A full day of treatments.'), [$T('Gommage et enveloppement', 'Scrub and body wrap'), $T('Massage pierres chaudes 90 min', '90-min hot stone massage'), $T('Soin éclat du visage', 'Radiance facial'), $T('Déjeuner léger inclus', 'Light lunch included')], $T('Réserver', 'Book'), true, $T('Coup de cœur', 'Guest favorite')],
                    [$T('Rituel duo', 'Duo ritual'), '290', $cur, $T('/ deux personnes', '/ two people'), $T('À partager à deux, en cabine double.', 'To share for two, in a double room.'), [$T('Massage côte à côte 60 min', '60-min side-by-side massage'), $T('Accès privatif au hammam', 'Private steam room access'), $T('Boisson pétillante sans alcool', 'Sparkling alcohol-free drink'), $T('Espace détente', 'Relaxation area')], $T('Réserver', 'Book')],
                ]]),
                M::timeline($theme, ['eyebrow' => $T('Le déroulé', 'Your day'), 'title' => $T('Une journée évasion, pas à pas', 'An escape day, step by step'), 'bg' => 'white', 'items' => [
                    [$T('9 h 30', '9:30 am'), $T('Accueil', 'Welcome'), $T('Tisane de bienvenue, peignoir et visite de l’espace détente.', 'Welcome tea, robe and a tour of the relaxation area.')],
                    [$T('10 h', '10 am'), $T('Hammam et gommage', 'Steam room and scrub'), $T('La vapeur prépare la peau au gommage aux sels marins.', 'The steam prepares your skin for the sea salt scrub.')],
                    [$T('11 h', '11 am'), $T('Enveloppement', 'Body wrap'), $T('Argile chaude et repos pendant que les actifs agissent.', 'Warm clay and rest while the active ingredients work.')],
                    [$T('12 h 30', '12:30 pm'), $T('Déjeuner léger', 'Light lunch'), $T('Cuisine de saison servie en peignoir, au calme.', 'Seasonal food served in your robe, in peace.')],
                    [$T('14 h', '2 pm'), $T('Massage et soin du visage', 'Massage and facial'), $T('Pierres chaudes puis soin éclat pour terminer en douceur.', 'Hot stones, then a radiance facial to finish gently.')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Toujours inclus', 'Always included'), 'title' => $T('L’espace détente', 'The relaxation area'), 'bg' => 'light', 'cols' => 4, 'items' => [
                    ['cloud', $T('Hammam', 'Steam room'), $T('Une chaleur humide qui ouvre les pores et détend les muscles.', 'Moist heat that opens pores and relaxes muscles.')],
                    ['flame', $T('Sauna', 'Sauna'), $T('Une chaleur sèche, à alterner avec une douche fraîche.', 'Dry heat, to alternate with a cool shower.')],
                    ['waves', $T('Bassin chauffé', 'Heated pool'), $T('Jets massants et banquettes à bulles.', 'Massage jets and bubble benches.')],
                    ['coffee', $T('Tisanerie', 'Tea lounge'), $T('Infusions, eau détox et fruits secs à volonté.', 'Herbal teas, infused water and dried fruit.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'yoga', 'slug' => $u('yoga-meditation', 'yoga-meditation'), 'title' => $T('Yoga et méditation', 'Yoga and meditation'),
            'description' => $T('Cours de yoga doux, vinyasa, yin et méditation guidée en petits groupes, tous niveaux : planning de la semaine, tarifs et abonnements.', 'Gentle yoga, vinyasa, yin and guided meditation in small groups for all levels: weekly schedule, prices and memberships.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Yoga et méditation', 'Yoga and meditation'), 'title' => $T('Bouger, respirer, apaiser l’esprit', 'Move, breathe, quiet the mind'), 'intro' => $T('Des cours de 12 personnes au plus, dans une salle baignée de lumière naturelle.', 'Classes of 12 people at most, in a room filled with natural light.')]),
                B::split($theme, [
                    'eyebrow' => $T('Nos cours', 'Our classes'),
                    'title' => $T('Une pratique accessible à tous', 'A practice open to everyone'),
                    'html' => '<p>'.e($T('Débutant ou pratiquant confirmé, chacun trouve son rythme. Nos professeurs proposent des variantes pour chaque posture et prennent le temps de corriger avec bienveillance.', 'Whether you are a beginner or experienced, everyone finds their pace. Our teachers offer variations for every pose and take the time to adjust you kindly.')).'</p>',
                    'bullets' => [$T('Tapis, briques et coussins fournis', 'Mats, blocks and cushions provided'), $T('Cours d’essai offert', 'Free trial class'), $T('Accès au hammam après le cours du soir', 'Steam room access after evening classes')],
                    'photo' => ['spa-yoga', 'leaf', $T('Cours de yoga en petit groupe dans une salle lumineuse au parquet clair', 'Small-group yoga class in a bright room with light wood floors')],
                    'reverse' => true,
                ]),
                M::table($theme, ['eyebrow' => $T('Planning', 'Schedule'), 'title' => $T('Les cours de la semaine', 'This week’s classes'), 'bg' => 'light', 'rows' => [
                    [$T('Jour', 'Day'), $T('Heure', 'Time'), $T('Cours', 'Class'), $T('Niveau', 'Level')],
                    [$T('Lundi', 'Monday'), $T('7 h 15', '7:15 am'), $T('Yoga du réveil', 'Morning yoga'), $T('Tous niveaux', 'All levels')],
                    [$T('Mardi', 'Tuesday'), $T('18 h 30', '6:30 pm'), 'Vinyasa', $T('Intermédiaire', 'Intermediate')],
                    [$T('Mercredi', 'Wednesday'), $T('12 h 15', '12:15 pm'), $T('Méditation guidée', 'Guided meditation'), $T('Tous niveaux', 'All levels')],
                    [$T('Jeudi', 'Thursday'), $T('19 h', '7 pm'), $T('Yin yoga', 'Yin yoga'), $T('Tous niveaux', 'All levels')],
                    [$T('Samedi', 'Saturday'), $T('10 h', '10 am'), $T('Yoga doux', 'Gentle yoga'), $T('Débutant', 'Beginner')],
                    [$T('Dimanche', 'Sunday'), $T('17 h', '5 pm'), $T('Respiration et relaxation', 'Breathwork and relaxation'), $T('Tous niveaux', 'All levels')],
                ], 'note' => $T('Cours d’une heure, méditation de 30 minutes. Réservation conseillée.', 'Classes last one hour, meditation 30 minutes. Booking recommended.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Tarifs', 'Prices'), 'title' => $T('Pratiquer à votre rythme', 'Practice at your own pace'), 'bg' => 'white', 'items' => [
                    [$T('Cours à l’unité', 'Drop-in class'), '18', $cur, $T('/ cours', '/ class'), $T('Pour découvrir ou pratiquer de temps en temps.', 'To try it out or practice now and then.'), [$T('Tous les cours au choix', 'Any class'), $T('Matériel fourni', 'Equipment provided'), '-'.$T('Accès au hammam', 'Steam room access')], $T('Réserver un cours', 'Book a class')],
                    [$T('Carte 10 cours', '10-class pass'), '150', $cur, $T('valable 6 mois', 'valid 6 months'), $T('La formule la plus souple.', 'The most flexible option.'), [$T('Tous les cours au choix', 'Any class'), $T('Matériel fourni', 'Equipment provided'), $T('Partageable avec un proche', 'Shareable with a loved one')], $T('Choisir la carte', 'Choose the pass'), true, $T('Populaire', 'Popular')],
                    [$T('Abonnement illimité', 'Unlimited membership'), '95', $cur, $T('/ mois', '/ month'), $T('Pour une pratique régulière.', 'For regular practice.'), [$T('Cours illimités', 'Unlimited classes'), $T('Accès au hammam après le cours', 'Steam room after class'), $T('−10 % sur les soins', '10% off treatments')], $T('S’abonner', 'Join')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions sur les cours', 'Class questions'), 'bg' => 'light', 'items' => [
                    [$T('Je n’ai jamais pratiqué, puis-je venir ?', 'I have never practiced. Can I come?'), $T('Bien sûr. Le yoga doux du samedi et les cours tous niveaux sont parfaits pour commencer.', 'Of course. Saturday gentle yoga and the all-levels classes are perfect for beginners.')],
                    [$T('Que faut-il apporter ?', 'What should I bring?'), $T('Une tenue confortable et une bouteille d’eau. Le matériel est fourni.', 'Comfortable clothes and a water bottle. Equipment is provided.')],
                    [$T('Puis-je pratiquer avec une douleur ou pendant une grossesse ?', 'Can I practice with pain or during pregnancy?'), $T('Prévenez le professeur avant le cours et demandez l’avis de votre médecin : certaines postures seront adaptées.', 'Tell the teacher before class and ask your doctor: some poses will be adapted.')],
                ]]),
            ]];

        $pages[] = ['key' => 'gifts', 'slug' => $u('cartes-cadeaux', 'gift-cards'), 'title' => $T('Cartes cadeaux', 'Gift cards'),
            'description' => $T('Offrez un massage, un rituel ou une journée spa : cartes cadeaux valables un an, envoyées par e-mail ou dans un joli coffret.', 'Give a massage, a ritual or a spa day: gift cards valid for one year, sent instantly by email or presented in a beautiful gift box.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Cartes cadeaux', 'Gift cards'), 'title' => $T('Offrez un moment de douceur', 'Give a moment of calm'), 'intro' => $T('Anniversaire, fête des mères, remerciement : le cadeau qui fait toujours plaisir.', 'Birthdays, Mother’s Day, a thank-you: the gift that always delights.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Carte valeur', 'Gift voucher'), '50', $cur, $T('ou le montant de votre choix', 'or any amount you choose'), $T('Utilisable sur tous les soins et cours.', 'Valid on all treatments and classes.'), [$T('Valable 12 mois', 'Valid for 12 months'), $T('Utilisable en plusieurs fois', 'Can be used over several visits'), $T('E-carte immédiate', 'Instant e-card')], $T('Offrir', 'Give')],
                    [$T('Coffret massage', 'Massage box'), '85', $cur, '', $T('Un massage de 60 minutes au choix.', 'A 60-minute massage of their choice.'), [$T('Valable 12 mois', 'Valid for 12 months'), $T('Espace détente inclus', 'Relaxation area included'), $T('Coffret ou e-carte', 'Gift box or e-card')], $T('Offrir', 'Give'), true, $T('Le plus offert', 'Most gifted')],
                    [$T('Coffret journée évasion', 'Escape day box'), '240', $cur, '', $T('La journée complète, déjeuner compris.', 'The full day, lunch included.'), [$T('Valable 12 mois', 'Valid for 12 months'), $T('Trois soins et déjeuner', 'Three treatments and lunch'), $T('Coffret ou e-carte', 'Gift box or e-card')], $T('Offrir', 'Give')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Comment ça marche', 'How it works'), 'title' => $T('Offrir en quatre étapes', 'Giving in four steps'), 'bg' => 'light', 'items' => [
                    [$T('Choisissez', 'Choose'), $T('Un montant libre, un soin ou un rituel complet.', 'A set amount, a treatment or a full ritual.')],
                    [$T('Personnalisez', 'Personalize'), $T('Ajoutez un message et le prénom de la personne.', 'Add a message and the recipient’s name.')],
                    [$T('Recevez', 'Receive'), $T('E-carte par e-mail ou coffret à retirer au spa.', 'E-card by email or a gift box to pick up at the spa.')],
                    [$T('Réservez', 'Book'), $T('L’heureux bénéficiaire réserve quand il le souhaite.', 'The lucky recipient books whenever they like.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions sur les cartes cadeaux', 'Gift card questions'), 'intro' => $T('Besoin d’un coffret pour une entreprise ou un groupe ? Contactez-nous.', 'Need gift boxes for a company or a group? Contact us.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Combien de temps la carte est-elle valable ?', 'How long is the card valid?'), $T('Douze mois à partir de la date d’achat. Une prolongation est possible une fois, sur demande.', 'Twelve months from the purchase date. It can be extended once on request.')],
                    [$T('La personne peut-elle changer de soin ?', 'Can the recipient choose a different treatment?'), $T('Oui, la valeur de la carte peut être utilisée pour un autre soin, avec un complément si nécessaire.', 'Yes, the card’s value can be used for another treatment, topping up if needed.')],
                    [$T('Comment payer ?', 'How do I pay?'), $T('Par carte bancaire, virement ou paiement mobile, en ligne ou à l’accueil du spa.', 'By card, bank transfer or mobile payment, online or at the spa reception.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'philosophy', 'slug' => $u('philosophie', 'philosophy'), 'title' => $T('Notre philosophie', 'Our philosophy'),
            'description' => $T('L’histoire du spa, nos valeurs de douceur, d’écoute et de respect de la nature, et l’équipe de praticiens qui vous accompagne.', 'The spa’s story, our values of gentleness, attentive care and respect for nature, and the team of therapists who look after you.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Notre philosophie', 'Our philosophy'), 'title' => $T('Le bien-être, simplement', 'Well-being, simply'), 'intro' => $T('Pas de promesses miracles : du temps, des gestes justes et un lieu pensé pour le calme.', 'No miracle promises: time, the right touch and a place designed for calm.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Un lieu né d’un besoin de calme', 'A place born from a need for calm'),
                    'html' => '<p>'.e($T('Après des années à exercer dans de grands hôtels, notre fondatrice a voulu créer un spa à taille humaine, où l’on prend le temps. Lumière douce, matériaux naturels, silence : chaque détail invite à ralentir.', 'After years working in large hotels, our founder wanted to create a human-scale spa where people take their time. Soft light, natural materials, silence: every detail invites you to slow down.')).'</p>',
                    'photo' => ['spa-histoire', 'house', $T('Espace de repos d’un spa avec transats, plantes et lumière naturelle', 'Spa relaxation lounge with loungers, plants and natural light')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce qui guide nos gestes', 'What guides our touch'), 'bg' => 'light', 'items' => [
                    ['heart-handshake', $T('Écoute', 'Attentiveness'), $T('Un soin adapté à ce que vous vivez aujourd’hui.', 'Treatment adapted to how you feel today.')],
                    ['sprout', $T('Nature', 'Nature'), $T('Des produits sobres, des fournisseurs choisis, peu de déchets.', 'Simple products, chosen suppliers, little waste.')],
                    ['moon', $T('Sérénité', 'Serenity'), $T('Un lieu silencieux, sans téléphone, pour vraiment décrocher.', 'A quiet, phone-free place to truly switch off.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Nos praticiens et professeurs', 'Our therapists and teachers'), 'shape' => 'circle', 'items' => [
                    ['[Prénom Nom]', $T('Fondatrice, praticienne en massages', 'Founder, massage therapist'), 'spa-equipe-1'],
                    ['[Prénom Nom]', $T('Esthéticienne, soins du visage', 'Esthetician, facials'), 'spa-equipe-2'],
                    ['[Prénom Nom]', $T('Praticien, massages profonds', 'Therapist, deep tissue massage'), 'spa-equipe-3'],
                    ['[Prénom Nom]', $T('Professeure de yoga et de méditation', 'Yoga and meditation teacher'), 'spa-equipe-4'],
                ]]),
                B::stats($theme, ['title' => $T('Le spa en quelques chiffres', 'The spa at a glance'), 'items' => [
                    [6, '', $T('cabines de soin', 'treatment rooms')],
                    [12, '', $T('praticiens et professeurs', 'therapists and teachers')],
                    [30, '+', $T('soins à la carte', 'treatments on the menu')],
                    [98, ' %', $T('d’hôtes satisfaits', 'satisfied guests')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'journal', 'slug' => 'journal', 'title' => $T('Journal', 'Journal'),
            'description' => $T('Conseils bien-être, sommeil, respiration, rituels à faire chez soi et nouveautés du spa, par notre équipe de praticiens et professeurs.', 'Well-being advice on sleep, breathing and home rituals, plus spa news, written by our team of therapists and yoga teachers.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Journal', 'Journal'), 'title' => $T('Le bien-être au quotidien', 'Everyday well-being'), 'intro' => $T('Des idées simples pour prolonger les bienfaits du spa à la maison.', 'Simple ideas to extend the benefits of the spa at home.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire', 'Read')]),
                B::events($theme, ['icon' => 'leaf', 'eyebrow' => $T('Ateliers', 'Workshops'), 'title' => $T('Prochains ateliers', 'Upcoming workshops'), 'bg' => 'light', 'items' => [
                    [$T('Atelier respiration et sommeil', 'Breathing and sleep workshop'), $T('Premier samedi du mois, 10 h', 'First Saturday of the month, 10 am'), $T('Salle de yoga', 'Yoga room')],
                    [$T('Auto-massage du visage', 'Facial self-massage'), $T('Deuxième mercredi du mois, 18 h 30', 'Second Wednesday of the month, 6:30 pm'), $T('Salon de thé du spa', 'Spa tea lounge')],
                    [$T('Retraite méditation d’une journée', 'One-day meditation retreat'), $T('Un dimanche par trimestre', 'One Sunday per quarter'), $T('Spa et jardin', 'Spa and garden')],
                    [$T('Yoga en duo parent-enfant', 'Parent and child yoga'), $T('Vacances scolaires, 11 h', 'School holidays, 11 am'), $T('Salle de yoga', 'Yoga room')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Réservez un soin, un rituel ou un cours de yoga. Adresse, horaires, téléphone et e-mail du spa : nous vous répondons rapidement.', 'Book a treatment, a ritual or a yoga class. The spa’s address, opening hours, phone and email: we will get back to you quickly.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Réservation et contact', 'Booking and contact'), 'title' => $T('Réservez votre moment', 'Book your moment'), 'intro' => $T('Indiquez le soin ou le cours souhaité et vos disponibilités : nous confirmons sous quelques heures.', 'Tell us the treatment or class you would like and your availability: we confirm within a few hours.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Écrivez-nous ou appelez l’accueil du spa.', 'Write to us or call the spa reception.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Tous les jours, 9 h – 21 h', 'Every day, 9 am – 9 pm')],
                    ],
                    'social' => ['instagram', 'facebook', 'pinterest'],
                    'subject' => true, 'button' => $T('Envoyer ma demande', 'Send my request'),
                    'bg' => 'white',
                ]),
                B::schedule($theme, ['eyebrow' => $T('Infos pratiques', 'Practical info'), 'title' => $T('Bien préparer votre venue', 'Planning your visit'), 'groups' => [
                    [$T('Horaires', 'Opening hours'), [
                        ['clock', $T('Soins : tous les jours, 9 h – 20 h', 'Treatments: every day, 9 am – 8 pm')],
                        ['waves', $T('Espace détente : 9 h – 21 h', 'Relaxation area: 9 am – 9 pm')],
                        ['leaf', $T('Yoga : voir le planning de la semaine', 'Yoga: see the weekly schedule')],
                    ]],
                    [$T('Conditions', 'Policies'), [
                        ['calendar-check', $T('Annulation gratuite jusqu’à 24 h avant', 'Free cancellation up to 24 hours before')],
                        ['timer', $T('Arrivée 15 minutes avant le soin', 'Arrive 15 minutes before your treatment')],
                        ['user', $T('Soins réservés aux plus de 16 ans', 'Treatments for guests aged 16 and over')],
                    ]],
                ]]),
                B::quote($theme, ['text' => $T('Ici, la seule chose que l’on vous demande, c’est de ralentir.', 'Here, the only thing we ask is that you slow down.'), 'source' => $T('— L’équipe du spa', '— The spa team'), 'bg' => 'white']),
            ]];

        $blog = [
            'base' => 'journal',
            'title' => $T('Journal', 'Journal'),
            'categories' => ['bien-etre' => $T('Bien-être', 'Well-being'), 'yoga' => $T('Yoga et méditation', 'Yoga and meditation'), 'rituels' => $T('Rituels maison', 'Home rituals')],
            'posts' => [
                ['key' => 'sommeil', 'slug' => $u('mieux-dormir', 'sleep-better'), 'category' => 'bien-etre', 'tags' => [$T('Sommeil', 'Sleep')],
                    'title' => $T('Cinq habitudes pour mieux dormir', 'Five habits for better sleep'),
                    'excerpt' => $T('Lumière, respiration et rituel du soir : des gestes simples pour retrouver un sommeil réparateur.', 'Light, breathing and an evening ritual: simple habits for restful sleep.'),
                    'photo' => ['spa-blog-sommeil', 'moon', $T('Chambre paisible avec lumière tamisée et tasse de tisane sur la table de nuit', 'Peaceful bedroom with soft light and a cup of herbal tea on the nightstand')],
                    'heading' => $T('Le rituel du soir', 'The evening ritual'),
                    'body' => [
                        $T('Baissez les lumières une heure avant le coucher et éloignez les écrans : votre corps comprend qu’il est temps de ralentir.', 'Dim the lights an hour before bed and put screens away: your body understands it is time to slow down.'),
                        $T('Quelques minutes de respiration lente, en allongeant l’expiration, apaisent le rythme cardiaque.', 'A few minutes of slow breathing, with a longer exhale, calms your heart rate.'),
                        $T('Si les troubles du sommeil persistent, parlez-en à un professionnel de santé.', 'If sleep problems persist, talk to a health professional.'),
                    ]],
                ['key' => 'respiration', 'slug' => $u('respiration-anti-stress', 'breathing-for-stress'), 'category' => 'yoga', 'tags' => [$T('Respiration', 'Breathing')],
                    'title' => $T('Trois exercices de respiration anti-stress', 'Three breathing exercises for stress'),
                    'excerpt' => $T('À pratiquer au bureau, dans les transports ou avant une réunion importante.', 'To practice at the office, on your commute or before an important meeting.'),
                    'photo' => ['spa-blog-respiration', 'leaf', $T('Personne assise en tailleur méditant près d’une fenêtre lumineuse', 'Person sitting cross-legged meditating by a bright window')],
                    'quote' => $T('« Le souffle est le seul geste du corps que l’on peut à la fois subir et diriger. »', '“Breath is the one bodily function we can both let happen and direct.”'),
                    'body' => [
                        $T('La respiration carrée : inspirez sur quatre temps, retenez quatre temps, expirez quatre temps, attendez quatre temps.', 'Box breathing: inhale for four counts, hold for four, exhale for four, wait for four.'),
                        $T('La respiration abdominale : une main sur le ventre, laissez-le se gonfler à l’inspiration, puis se relâcher.', 'Belly breathing: one hand on your belly, let it rise as you inhale, then soften.'),
                        $T('Enfin, la cohérence cardiaque : six respirations par minute pendant cinq minutes, trois fois par jour.', 'Finally, coherent breathing: six breaths a minute for five minutes, three times a day.'),
                    ]],
                ['key' => 'bain', 'slug' => $u('bain-relaxant-maison', 'relaxing-bath-at-home'), 'category' => 'rituels', 'tags' => [$T('À la maison', 'At home')],
                    'title' => $T('Un bain relaxant comme au spa', 'A spa-style relaxing bath at home'),
                    'excerpt' => $T('Sels, huiles et ambiance : recréez un moment de spa dans votre salle de bain.', 'Salts, oils and atmosphere: recreate a spa moment in your bathroom.'),
                    'photo' => ['spa-blog-bain', 'droplet', $T('Sels de bain, serviette blanche et bougie au bord d’une baignoire', 'Bath salts, a white towel and a candle on the edge of a bathtub')],
                    'heading' => $T('La recette', 'The recipe'),
                    'body' => [
                        $T('Faites couler une eau chaude, mais pas brûlante, et ajoutez une poignée de sels marins ou de sels d’Epsom.', 'Run warm, not scalding, water and add a handful of sea salt or Epsom salt.'),
                        $T('Quelques gouttes d’huile végétale nourrissent la peau ; une bougie et une musique douce font le reste.', 'A few drops of plant oil nourish the skin; a candle and soft music do the rest.'),
                        $T('Restez vingt minutes, puis hydratez-vous avec un grand verre d’eau ou une tisane.', 'Stay for twenty minutes, then rehydrate with a large glass of water or herbal tea.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Soins', 'Treatments'), 'url' => $treatments],
                    ['label' => $T('Rituels', 'Rituals'), 'url' => $rituals],
                    ['label' => 'Yoga', 'url' => $yoga],
                    ['label' => $T('Cartes cadeaux', 'Gift cards'), 'url' => $gifts],
                    ['label' => $T('Philosophie', 'Philosophy'), 'url' => $philosophy],
                    ['label' => $T('Journal', 'Journal'), 'url' => $journal],
                ],
                'cta_label' => $T('Réserver', 'Book now'),
                'cta_url' => $contact,
                'footer_text' => $T('Spa et institut de bien-être : massages, soins naturels, espace détente, yoga et méditation, dans un lieu pensé pour le calme.', 'Spa and wellness center: massages, natural treatments, relaxation area, yoga and meditation, in a place designed for calm.'),
                'footer_links' => [
                    ['label' => $T('La carte des soins', 'Treatment menu'), 'url' => $treatments],
                    ['label' => $T('Cartes cadeaux', 'Gift cards'), 'url' => $gifts],
                    ['label' => $T('Planning yoga', 'Yoga schedule'), 'url' => $yoga],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
