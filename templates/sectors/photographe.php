<?php

/*
 * Pack « Photographe et vidéaste professionnel » (secteur Événementiel et
 * photographie) : 8 pages, en français et en anglais. Portfolio par thème,
 * mariage, portrait et famille, entreprise, offres et tarifs (déroulé d'une
 * séance), journal (blog), contact.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du photographe). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Événementiel et photographie', 'en' => 'Events and photography'],
        'icon' => 'party-popper',
        'keywords' => ['photographe', 'vidéaste', 'photographie', 'vidéo', 'portrait', 'mariage', 'reportage', 'shooting', 'studio photo', 'portfolio', 'photographer', 'videographer', 'photography', 'wedding photographer', 'headshots', 'photo studio'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $p = fn (string $n) => $lang === 'fr' ? $n.' €' : '$'.$n; // prix dans un texte
        $cur = $T('€', '$');
        $portfolio = $u('/portfolio', '/portfolio');
        $wedding = $u('/mariage', '/weddings');
        $portrait = $u('/portrait', '/portraits');
        $business = $u('/entreprise', '/business');
        $pricing = $u('/tarifs', '/pricing');
        $journal = '/journal';
        $contact = '/contact';

        $testimonials = ['eyebrow' => $T('Avis', 'Reviews'), 'title' => $T('Ce qu’en disent mes clients', 'What my clients say'), 'rating' => 5, 'items' => [
            [$T('Nous avons oublié l’appareil photo toute la journée, et pourtant chaque émotion est là. Nous redécouvrons notre mariage à chaque page de l’album.', 'We forgot about the camera all day, and yet every emotion is there. We relive our wedding on every page of the album.'), '[Prénom Nom]', $T('Mariés', 'Newlyweds')],
            [$T('Une séance famille détendue, des enfants qui rient et des photos naturelles que nous avons fait encadrer.', 'A relaxed family session, laughing kids and natural photos we have had framed.'), '[Prénom Nom]', $T('Séance famille', 'Family session')],
            [$T('Portraits de toute notre équipe réalisés en une matinée, dans nos locaux, livrés en trois jours. Très professionnel.', 'Portraits of our whole team shot in one morning at our office, delivered in three days. Very professional.'), '[Prénom Nom]', $T('Responsable communication', 'Communications manager')],
        ]];
        $cta = ['title' => $T('Racontons votre histoire en images', 'Let’s tell your story in pictures'), 'text' => $T('Dites-moi votre projet et votre date : je vous réponds sous 48 heures avec une proposition sur mesure.', 'Tell me about your project and date: I will reply within 48 hours with a tailored proposal.'),
            'buttons' => [[$T('Vérifier mes disponibilités', 'Check my availability'), $contact, 'accent'], [$T('Voir les tarifs', 'See pricing'), $pricing, 'outline-light']]];
        $weddingPhotos = [
            ['photographe-mariage-1', 'heart', $T('Mariés se tenant la main dans un champ au coucher du soleil', 'Newlyweds holding hands in a field at sunset')],
            ['photographe-mariage-2', 'heart', $T('Échange des alliances pendant une cérémonie de mariage', 'Exchange of rings during a wedding ceremony')],
            ['photographe-mariage-3', 'camera', $T('Invités qui lancent des pétales sur les mariés à la sortie', 'Guests throwing petals over the newlyweds as they leave')],
            ['photographe-mariage-4', 'camera', $T('Mariée se préparant devant une fenêtre lumineuse', 'Bride getting ready by a bright window')],
            ['photographe-mariage-5', 'music', $T('Première danse des mariés sous des guirlandes lumineuses', 'Couple’s first dance under string lights')],
            ['photographe-mariage-6', 'camera', $T('Détail d’un bouquet de mariée et des alliances', 'Close-up of a bridal bouquet and wedding rings')],
        ];
        $portraitPhotos = [
            ['photographe-portrait-1', 'user-round', $T('Portrait en lumière naturelle d’une femme souriante', 'Natural light portrait of a smiling woman')],
            ['photographe-portrait-2', 'users', $T('Famille avec deux enfants jouant dans un parc en automne', 'Family with two children playing in a park in autumn')],
            ['photographe-portrait-3', 'baby', $T('Nouveau-né endormi dans une couverture en laine', 'Newborn baby asleep in a wool blanket')],
            ['photographe-portrait-4', 'heart', $T('Couple enlacé lors d’une séance photo en ville', 'Couple embracing during a city photo session')],
            ['photographe-portrait-5', 'user-round', $T('Portrait noir et blanc d’un homme âgé au regard expressif', 'Black and white portrait of an older man with an expressive gaze')],
            ['photographe-portrait-6', 'sparkles', $T('Femme enceinte en robe longue au bord d’un lac', 'Pregnant woman in a long dress by a lake')],
        ];
        $businessPhotos = [
            ['photographe-entreprise-1', 'briefcase', $T('Portrait professionnel d’une dirigeante dans un bureau moderne', 'Professional portrait of an executive in a modern office')],
            ['photographe-entreprise-2', 'users', $T('Équipe en réunion photographiée sur le vif', 'Team in a meeting, candid shot')],
            ['photographe-entreprise-3', 'presentation', $T('Intervenant sur scène lors d’une conférence', 'Speaker on stage at a conference')],
            ['photographe-entreprise-4', 'package', $T('Photo de produit sur fond uni dans un studio', 'Product photo on a plain background in a studio')],
            ['photographe-entreprise-5', 'chef-hat', $T('Chef dressant une assiette dans une cuisine de restaurant', 'Chef plating a dish in a restaurant kitchen')],
            ['photographe-entreprise-6', 'factory', $T('Artisan au travail dans son atelier', 'Craftsperson at work in a workshop')],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Photographe et vidéaste professionnel : mariages, portraits, familles et entreprises. Des images naturelles et sincères, qui traversent le temps.', 'Professional photographer and videographer: weddings, portraits, families and businesses. Natural, honest images that stand the test of time.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Photographe et vidéaste', 'Photographer and videographer'),
                    'title' => $T('Des images vraies, pour des souvenirs qui durent', 'Honest images, for memories that last'),
                    'text' => $T('Mariages, portraits, familles et entreprises : je capte les moments tels qu’ils sont, avec discrétion et un regard attentif à la lumière.', 'Weddings, portraits, families and businesses: I capture moments as they are, discreetly and with an eye for light.'),
                    'buttons' => [[$T('Réserver une séance', 'Book a session'), $contact], [$T('Voir le portfolio', 'View portfolio'), $portfolio]],
                    'photo' => ['photographe-accueil', 'camera', $T('Photographe tenant un appareil reflex en extérieur à la lumière dorée', 'Photographer holding a camera outdoors in golden light')],
                ]),
                B::cards($theme, ['eyebrow' => $T('Mes univers', 'What I shoot'), 'title' => $T('Trois façons de raconter une histoire', 'Three ways to tell a story'), 'more' => $T('Découvrir', 'Discover'), 'items' => [
                    [$weddingPhotos[0][0], 'heart', $T('Mariage', 'Weddings'), $T('Un reportage documentaire, des préparatifs à la soirée, en photo et en film.', 'Documentary coverage, from getting ready to the party, in photo and film.'), $wedding],
                    [$portraitPhotos[1][0], 'users', $T('Portrait et famille', 'Portraits and families'), $T('Séances en studio ou en extérieur : grossesse, naissance, famille, couple.', 'Studio or outdoor sessions: maternity, newborn, family, couples.'), $portrait],
                    [$businessPhotos[0][0], 'briefcase', $T('Entreprise', 'Business'), $T('Portraits d’équipe, événements, produits et vidéos pour votre communication.', 'Team portraits, events, products and videos for your communications.'), $business],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('Sélection', 'Selection'), 'title' => $T('Quelques images récentes', 'A few recent images'), 'bg' => 'light', 'photos' => [$weddingPhotos[1], $portraitPhotos[0], $businessPhotos[2], $weddingPhotos[4], $portraitPhotos[2], $businessPhotos[4]]]),
                B::split($theme, [
                    'eyebrow' => $T('Qui suis-je', 'About me'),
                    'title' => $T('Un regard discret, une approche humaine', 'A discreet eye, a human approach'),
                    'html' => '<p>'.e($T('Photographe depuis plus de dix ans, j’aime les images qui ne posent pas : un rire, un regard, une main qui se serre. Je prends le temps de vous connaître pour que vous soyez à l’aise devant l’objectif, et je retouche avec légèreté pour garder des photos qui vous ressemblent.', 'A photographer for over ten years, I love images that do not pose: a laugh, a glance, a hand held tight. I take the time to get to know you so you feel at ease in front of the lens, and I edit with a light touch to keep photos that look like you.')).'</p>',
                    'bullets' => [$T('Photo et vidéo par un même regard', 'Photo and video from one point of view'), $T('Galerie en ligne privée pour chaque client', 'Private online gallery for every client'), $T('Déplacements dans tout le pays et à l’étranger', 'Travel nationwide and abroad')],
                    'buttons' => [[$T('Mes tarifs', 'My pricing'), $pricing, 'outline']],
                    'photo' => ['photographe-portrait-artiste', 'user-round', $T('Photographe souriant en train de régler son objectif', 'Smiling photographer adjusting a camera lens')],
                    'reverse' => true,
                ]),
                B::steps($theme, ['eyebrow' => $T('Le déroulé', 'How it works'), 'title' => $T('Une séance, étape par étape', 'A session, step by step'), 'bg' => 'light', 'items' => [
                    [$T('Échange', 'Conversation'), $T('Nous parlons de vos envies, du lieu et du style d’images que vous aimez.', 'We talk about your wishes, the location and the style of images you like.')],
                    [$T('Préparation', 'Preparation'), $T('Conseils de tenues, choix de l’heure pour la plus belle lumière.', 'Outfit advice and choosing the time for the best light.')],
                    [$T('Séance', 'The shoot'), $T('Un moment détendu, guidé sans être figé.', 'A relaxed moment, guided but never stiff.')],
                    [$T('Livraison', 'Delivery'), $T('Images retouchées dans votre galerie privée, prêtes à imprimer.', 'Edited images in your private gallery, ready to print.')],
                ]]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'portfolio', 'slug' => $u('portfolio', 'portfolio'), 'title' => 'Portfolio',
            'description' => $T('Portfolio photo par thème : mariages, portraits et familles, entreprises. Une sélection d’images réalisées pour mes clients ces dernières années.', 'Photography portfolio by theme: weddings, portraits and families, businesses. A selection of images created for my clients in recent years.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Portfolio', 'title' => $T('Une sélection de mon travail', 'A selection of my work'), 'intro' => $T('Images publiées avec l’accord de mes clients. Cliquez sur une photo pour l’agrandir.', 'Images shared with my clients’ consent. Click a photo to enlarge it.')]),
                B::gallery($theme, ['eyebrow' => $T('Mariage', 'Weddings'), 'title' => $T('Le jour J, sans mise en scène', 'The big day, unstaged'), 'photos' => $weddingPhotos]),
                B::gallery($theme, ['eyebrow' => $T('Portrait et famille', 'Portraits and families'), 'title' => $T('Des liens qui se voient', 'Bonds you can see'), 'bg' => 'light', 'photos' => $portraitPhotos]),
                B::gallery($theme, ['eyebrow' => $T('Entreprise', 'Business'), 'title' => $T('Les visages et les métiers', 'Faces and crafts'), 'photos' => $businessPhotos]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'wedding', 'slug' => $u('mariage', 'weddings'), 'title' => $T('Mariage', 'Weddings'),
            'description' => $T('Photographe et vidéaste de mariage : reportage documentaire des préparatifs à la soirée, galerie en ligne, album et film souvenir.', 'Wedding photographer and videographer: documentary coverage from getting ready to the party, online gallery, album and keepsake film.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Mariage', 'Weddings'), 'title' => $T('Votre mariage, raconté avec sincérité', 'Your wedding, told with honesty'), 'intro' => $T('Je ne dirige pas votre journée : je la regarde, je l’accompagne et j’en garde le meilleur.', 'I do not direct your day: I watch it, follow it and keep the best of it.')]),
                B::split($theme, [
                    'eyebrow' => $T('Reportage documentaire', 'Documentary coverage'),
                    'title' => $T('Les émotions plutôt que les poses', 'Emotions rather than poses'),
                    'html' => '<p>'.e($T('Je me fonds parmi vos invités pour saisir les instants spontanés : les larmes du père, les fous rires des témoins, la piste de danse en fin de soirée. Quelques photos de groupe et un moment de portraits à deux complètent le reportage, sans jamais vous priver de votre fête.', 'I blend in with your guests to catch spontaneous moments: a father’s tears, the best man’s laughter, the dance floor late at night. A few group photos and a short couple portrait session complete the coverage, without ever taking you away from the party.')).'</p>',
                    'bullets' => [$T('Rendez-vous de préparation et repérage', 'Planning meeting and location scouting'), $T('Second photographe en option', 'Second shooter available'), $T('Film souvenir de 5 à 10 minutes en option', 'Optional 5 to 10-minute keepsake film')],
                    'photo' => ['photographe-mariage-reportage', 'heart', $T('Mariés riant aux éclats entourés de leurs invités', 'Newlyweds laughing out loud surrounded by their guests')],
                ]),
                B::features($theme, ['eyebrow' => $T('Ce qui est inclus', 'What is included'), 'title' => $T('Un accompagnement complet', 'Complete support'), 'bg' => 'light', 'items' => [
                    ['message-circle', $T('Rencontre préalable', 'Initial meeting'), $T('Un échange pour connaître votre histoire, vos proches et le programme.', 'A conversation to learn your story, your loved ones and the schedule.')],
                    ['camera', $T('Reportage photo', 'Photo coverage'), $T('De 4 heures à la journée complète, selon la formule.', 'From 4 hours to the full day, depending on the package.')],
                    ['video', $T('Film de mariage', 'Wedding film'), $T('Un film monté en musique, avec les discours et les vœux.', 'A film edited to music, with speeches and vows.')],
                    ['image', $T('Galerie en ligne', 'Online gallery'), $T('Toutes les images retouchées, à partager avec vos invités.', 'All edited images, to share with your guests.')],
                    ['book-open', $T('Album d’art', 'Fine art album'), $T('Un album relié à la main, conçu avec vous.', 'A hand-bound album designed with you.')],
                    ['clock', $T('Livraison rapide', 'Fast delivery'), $T('Un aperçu sous une semaine, la galerie complète sous six semaines.', 'A sneak peek within a week, the full gallery within six weeks.')],
                ]]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Formules mariage', 'Wedding packages'), 'title' => $T('Choisissez votre formule', 'Choose your package'), 'bg' => 'white', 'items' => [
                    [$T('Essentiel', 'Essential'), '1 290', $cur, $T('forfait', 'package'), $T('Pour les mariages intimes ou la cérémonie seule.', 'For intimate weddings or the ceremony only.'), [$T('4 heures de reportage', '4 hours of coverage'), $T('300 images retouchées', '300 edited images'), $T('Galerie en ligne', 'Online gallery'), '-'.$T('Film de mariage', 'Wedding film')], $T('Demander mes dates', 'Check my dates')],
                    [$T('Complet', 'Complete'), '2 190', $cur, $T('forfait', 'package'), $T('Des préparatifs à l’ouverture du bal.', 'From getting ready to the first dance.'), [$T('10 heures de reportage', '10 hours of coverage'), $T('600 images retouchées', '600 edited images'), $T('Séance couple offerte', 'Free couple session'), $T('Galerie en ligne', 'Online gallery')], $T('Demander mes dates', 'Check my dates'), true, $T('La plus choisie', 'Most chosen')],
                    [$T('Photo et film', 'Photo and film'), '3 290', $cur, $T('forfait', 'package'), $T('Le reportage complet, en images et en film.', 'Full coverage, in photos and on film.'), [$T('Journée complète', 'Full day'), $T('Film souvenir de 8 minutes', '8-minute keepsake film'), $T('Album d’art 30 × 30 cm', '12 × 12 in fine art album'), $T('Second photographe', 'Second shooter')], $T('Demander mes dates', 'Check my dates')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions des futurs mariés', 'Questions from couples'), 'intro' => $T('Votre date est-elle encore libre ? Écrivez-moi pour le savoir.', 'Is your date still available? Write to me to find out.'), 'buttons' => [[$T('Vérifier mes disponibilités', 'Check my availability'), $contact, 'outline']], 'bg' => 'light', 'items' => [
                    [$T('Combien de temps à l’avance réserver ?', 'How far ahead should we book?'), $T('Souvent un an à l’avance pour les samedis de printemps et d’été. Un acompte de 30 % confirme la date.', 'Often a year ahead for spring and summer Saturdays. A 30% deposit secures the date.')],
                    [$T('Vous déplacez-vous loin ?', 'Do you travel far?'), $T('Oui, partout dans le pays et à l’étranger. Les frais de déplacement et d’hébergement sont indiqués dans le devis.', 'Yes, nationwide and abroad. Travel and accommodation costs are shown in the quote.')],
                    [$T('Recevrons-nous toutes les photos ?', 'Will we receive all the photos?'), $T('Vous recevez une sélection de toutes les bonnes images, retouchées. Les doublons et les photos floues sont écartés.', 'You receive a selection of all the good images, edited. Duplicates and blurry shots are left out.')],
                    [$T('Et en cas de pluie ?', 'What if it rains?'), $T('La pluie donne souvent les photos les plus originales ! Nous repérons ensemble des solutions à l’abri.', 'Rain often makes for the most original photos! We scout sheltered options together.')],
                ]]),
            ]];

        $pages[] = ['key' => 'portrait', 'slug' => $u('portrait', 'portraits'), 'title' => $T('Portrait et famille', 'Portraits and families'),
            'description' => $T('Séances portrait, couple, grossesse, naissance et famille, en studio, à domicile ou en extérieur, avec des photos naturelles et lumineuses.', 'Portrait, couple, maternity, newborn and family sessions, in the studio, at home or outdoors, with natural and luminous photos.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Portrait et famille', 'Portraits and families'), 'title' => $T('Des portraits qui vous ressemblent', 'Portraits that look like you'), 'intro' => $T('Pas besoin d’être à l’aise devant l’objectif : c’est mon travail de vous y mettre.', 'You do not need to be comfortable in front of the camera: that is my job.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['user-round', $T('Portrait individuel', 'Individual portrait'), $T('Pour vous, pour un projet artistique ou pour vos réseaux.', 'For yourself, an artistic project or your social media.')],
                    ['heart', $T('Couple et fiançailles', 'Couples and engagement'), $T('Une séance complice, en ville ou dans la nature.', 'A playful session, in town or in nature.')],
                    ['sparkles', $T('Grossesse', 'Maternity'), $T('Idéalement entre le septième et le huitième mois.', 'Ideally between the seventh and eighth month.')],
                    ['baby', $T('Nouveau-né', 'Newborn'), $T('À domicile, dans les premières semaines, en toute douceur.', 'At home, in the first weeks, very gently.')],
                    ['users', $T('Famille', 'Family'), $T('Des moments de jeu et de tendresse avec petits et grands.', 'Moments of play and tenderness with young and old.')],
                    ['drama', $T('Artistes et créateurs', 'Artists and creators'), $T('Photos de presse, pochettes et images de scène.', 'Press photos, album covers and stage images.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Studio ou extérieur', 'Studio or outdoors'),
                    'title' => $T('Le lieu qui vous correspond', 'The setting that suits you'),
                    'html' => '<p>'.e($T('Le studio offre une lumière maîtrisée et des fonds sobres, parfaits pour les nouveau-nés et les portraits classiques. En extérieur, nous profitons de la lumière douce du matin ou de la fin de journée, dans un parc, une forêt ou votre quartier.', 'The studio offers controlled light and simple backdrops, perfect for newborns and classic portraits. Outdoors, we use the soft light of early morning or late afternoon, in a park, a forest or your neighborhood.')).'</p>',
                    'bullets' => [$T('Studio chauffé avec accessoires et tenues de grossesse', 'Heated studio with props and maternity gowns'), $T('Séance à domicile possible', 'At-home sessions available'), $T('Conseils de tenues envoyés avant la séance', 'Outfit tips sent before the session')],
                    'buttons' => [[$T('Voir les tarifs', 'See pricing'), $pricing]],
                    'photo' => ['photographe-studio', 'lamp', $T('Studio photo avec fond clair, éclairage doux et fauteuil en velours', 'Photo studio with a light backdrop, soft lighting and a velvet armchair')],
                    'reverse' => true, 'bg' => 'light',
                ]),
                B::gallery($theme, ['eyebrow' => $T('Galerie', 'Gallery'), 'title' => $T('Séances récentes', 'Recent sessions'), 'photos' => $portraitPhotos]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Préparer sa séance', 'Preparing your session'), 'bg' => 'light', 'items' => [
                    [$T('Comment s’habiller ?', 'What should we wear?'), $T('Des couleurs douces et unies, sans gros logos, coordonnées entre les membres de la famille sans être identiques.', 'Soft, solid colors without big logos, coordinated across the family without matching exactly.')],
                    [$T('Et si mon enfant ne veut pas poser ?', 'What if my child won’t pose?'), $T('C’est normal ! Je propose des jeux et je photographie les moments spontanés, souvent les plus beaux.', 'That is normal! I suggest games and capture the spontaneous moments, often the most beautiful.')],
                    [$T('Combien de temps dure une séance ?', 'How long does a session last?'), $T('Une heure environ, deux à trois heures pour un nouveau-né, avec les pauses nécessaires.', 'About an hour, two to three hours for a newborn, with breaks as needed.')],
                ]]),
            ]];

        $pages[] = ['key' => 'business', 'slug' => $u('entreprise', 'business'), 'title' => $T('Entreprise', 'Business'),
            'description' => $T('Photographe et vidéaste pour les entreprises : portraits d’équipe, événements, photos de produits, reportages métier et vidéos institutionnelles.', 'Photographer and videographer for businesses: team portraits, events, product photos, behind-the-scenes coverage and corporate videos.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Entreprise', 'Business'), 'title' => $T('Des images qui valorisent votre activité', 'Images that showcase your business'), 'intro' => $T('Pour votre site, vos réseaux, vos rapports et la presse : des visuels cohérents, livrés rapidement.', 'For your website, social media, reports and the press: consistent visuals, delivered fast.')]),
                B::features($theme, ['items' => [
                    ['user-round', $T('Portraits corporate', 'Corporate headshots'), $T('Studio mobile installé dans vos locaux, pour une équipe entière en une matinée.', 'Mobile studio set up at your office, for a whole team in one morning.')],
                    ['presentation', $T('Événements', 'Events'), $T('Conférences, séminaires, inaugurations et soirées d’entreprise.', 'Conferences, seminars, openings and company parties.')],
                    ['package', $T('Produits', 'Products'), $T('Packshots sur fond blanc et mises en scène pour la vente en ligne.', 'White-background packshots and styled shots for online stores.')],
                    ['factory', $T('Reportage métier', 'Behind the scenes'), $T('Vos équipes au travail, vos ateliers et vos savoir-faire.', 'Your teams at work, your workshops and your expertise.')],
                    ['video', $T('Vidéo institutionnelle', 'Corporate video'), $T('Films de présentation, interviews et témoignages clients.', 'Company films, interviews and customer testimonials.')],
                    ['smartphone', $T('Contenus réseaux sociaux', 'Social media content'), $T('Photos et vidéos courtes au format vertical, prêtes à publier.', 'Photos and short vertical videos, ready to post.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Vidéo', 'Video'),
                    'title' => $T('Votre histoire en quelques minutes', 'Your story in a few minutes'),
                    'html' => '<p>'.e($T('Écriture du scénario, tournage, interviews, prise de son et montage : je réalise des vidéos courtes et soignées qui expliquent ce que vous faites et donnent envie de travailler avec vous.', 'Script writing, filming, interviews, sound and editing: I produce short, polished videos that explain what you do and make people want to work with you.')).'</p>',
                    'bullets' => [$T('Formats horizontal et vertical', 'Landscape and vertical formats'), $T('Sous-titres et musique libre de droits', 'Subtitles and royalty-free music'), $T('Deux allers-retours de corrections inclus', 'Two rounds of edits included')],
                    'photo' => ['photographe-video', 'video', $T('Vidéaste filmant une interview avec une caméra sur trépied et un éclairage', 'Videographer filming an interview with a tripod camera and lighting')],
                    'bg' => 'light',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Engagements', 'Commitments'), 'title' => $T('Travailler sereinement', 'Working with peace of mind'), 'items' => [
                    ['clock', $T('Délais courts', 'Quick turnaround'), $T('Sélection sous 48 heures, images finales sous cinq jours ouvrés.', 'Selection within 48 hours, final images within five business days.')],
                    ['file-text', $T('Droits clairs', 'Clear usage rights'), $T('Cession des droits d’utilisation précisée sur chaque devis.', 'Usage rights clearly set out in every quote.')],
                    ['receipt', $T('Facturation simple', 'Simple invoicing'), $T('Devis détaillé, facture professionnelle et paiement à 30 jours.', 'Detailed quote, professional invoice and 30-day payment terms.')],
                ]]),
                B::cta($theme, ['title' => $T('Un projet pour votre entreprise ?', 'A project for your business?'), 'text' => $T('Décrivez votre besoin, votre calendrier et vos usages : je vous envoie un devis sous 48 heures.', 'Describe your needs, schedule and intended use: I will send a quote within 48 hours.'), 'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact, 'accent'], [$T('Voir le portfolio', 'View portfolio'), $portfolio, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'pricing', 'slug' => $u('tarifs', 'pricing'), 'title' => $T('Offres et tarifs', 'Packages and pricing'),
            'description' => $T('Tarifs des séances portrait, famille et mini-séances, options de tirages et d’albums, et déroulé d’une séance photo, de la réservation à la livraison.', 'Prices for portrait, family and mini sessions, print and album options, and how a photo session works, from booking to delivery.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Offres et tarifs', 'Packages and pricing'), 'title' => $T('Des offres claires, toutes retouches comprises', 'Clear packages, editing included'), 'intro' => $T('Séances particuliers ci-dessous ; mariages et entreprises sur leurs pages dédiées.', 'Personal sessions below; weddings and businesses on their own pages.')]),
                B::pricing($theme, ['url' => $contact, 'items' => [
                    [$T('Mini-séance', 'Mini session'), '120', $cur, $T('/ 30 min', '/ 30 min'), $T('Lors de journées thématiques au studio.', 'On themed studio days.'), [$T('15 images retouchées', '15 edited images'), $T('Galerie en ligne', 'Online gallery'), $T('Un lieu, une tenue', 'One location, one outfit'), '-'.$T('Séance à domicile', 'At-home session')], $T('Réserver', 'Book')],
                    [$T('Séance portrait ou couple', 'Portrait or couple session'), '190', $cur, $T('/ 1 h', '/ 1 hr'), $T('En studio ou en extérieur.', 'In the studio or outdoors.'), [$T('30 images retouchées', '30 edited images'), $T('Galerie en ligne', 'Online gallery'), $T('Deux tenues', 'Two outfits'), $T('Conseils de préparation', 'Preparation tips')], $T('Réserver', 'Book'), true, $T('Populaire', 'Popular')],
                    [$T('Séance famille ou naissance', 'Family or newborn session'), '290', $cur, $T('/ 2 h', '/ 2 hr'), $T('À domicile, en studio ou en extérieur.', 'At home, in the studio or outdoors.'), [$T('50 images retouchées', '50 edited images'), $T('Galerie en ligne', 'Online gallery'), $T('Jusqu’à 6 personnes', 'Up to 6 people'), $T('Un tirage 30 × 40 cm offert', 'One free 12 × 16 in print')], $T('Réserver', 'Book')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Options', 'Add-ons'), 'title' => $T('Tirages, albums et extras', 'Prints, albums and extras'), 'bg' => 'white', 'rows' => [
                    [$T('Option', 'Add-on'), $T('Détail', 'Details'), $T('Prix', 'Price')],
                    [$T('Image supplémentaire', 'Extra image'), $T('Retouchée, haute définition', 'Edited, high resolution'), $p('15')],
                    [$T('Tirage d’art', 'Fine art print'), '30 × 40 cm', $p('45')],
                    [$T('Album photo', 'Photo album'), $T('20 pages, couverture lin', '20 pages, linen cover'), $p('180')],
                    [$T('Heure supplémentaire', 'Extra hour'), $T('Séance ou reportage', 'Session or coverage'), $p('90')],
                    [$T('Vidéo souvenir', 'Keepsake video'), $T('1 à 2 minutes en musique', '1 to 2 minutes with music'), $p('250')],
                ], 'note' => $T('Frais de déplacement offerts dans un rayon de 30 km.', 'No travel fee within 30 km (about 20 miles).')]),
                B::steps($theme, ['eyebrow' => $T('Le déroulé d’une séance', 'How a session works'), 'title' => $T('De la réservation à vos images', 'From booking to your images'), 'bg' => 'light', 'items' => [
                    [$T('Réservation', 'Booking'), $T('Vous choisissez une date ; un acompte de 30 % la confirme.', 'You choose a date; a 30% deposit confirms it.')],
                    [$T('Préparation', 'Preparation'), $T('Un appel pour le lieu, les tenues et vos envies.', 'A call about the location, outfits and your wishes.')],
                    [$T('Séance', 'The shoot'), $T('Un moment naturel et détendu, à votre rythme.', 'A natural, relaxed moment at your pace.')],
                    [$T('Galerie', 'Gallery'), $T('Vos images retouchées sous deux à trois semaines.', 'Your edited images within two to three weeks.')],
                ]]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions pratiques', 'Practical questions'), 'bg' => 'light', 'items' => [
                    [$T('Comment payer ?', 'How can I pay?'), $T('Par carte bancaire, virement ou paiement mobile. Le solde est réglé le jour de la séance.', 'By card, bank transfer or mobile payment. The balance is paid on the day of the session.')],
                    [$T('Puis-je reporter ma séance ?', 'Can I reschedule?'), $T('Oui, une fois gratuitement jusqu’à 72 heures avant. En extérieur, la météo peut aussi justifier un report.', 'Yes, once for free up to 72 hours before. For outdoor sessions, weather may also justify a new date.')],
                    [$T('Fournissez-vous les fichiers bruts ?', 'Do you provide raw files?'), $T('Non : la retouche fait partie de mon travail. Toutes les images livrées sont retouchées et en haute définition.', 'No: editing is part of my work. All delivered images are edited and in high resolution.')],
                ]]),
            ]];

        $pages[] = ['key' => 'journal', 'slug' => 'journal', 'title' => $T('Journal', 'Journal'),
            'description' => $T('Le journal du photographe : récits de mariages et de séances, conseils pour être à l’aise devant l’objectif et coulisses du métier.', 'The photographer’s journal: wedding and session stories, tips for feeling at ease in front of the camera and behind-the-scenes notes.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Journal', 'Journal'), 'title' => $T('Histoires et coulisses', 'Stories and behind the scenes'), 'intro' => $T('Récits de séances, conseils pratiques et quelques mots sur ma façon de travailler.', 'Session stories, practical tips and a few words on how I work.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire', 'Read')]),
                B::split($theme, [
                    'eyebrow' => $T('Derrière l’objectif', 'Behind the lens'),
                    'title' => $T('Du matériel fiable, une sauvegarde en double', 'Reliable gear, double backups'),
                    'html' => '<p>'.e($T('Je travaille avec deux boîtiers professionnels et des cartes doublées : chaque image est enregistrée deux fois pendant la prise de vue, puis sauvegardée sur trois supports, dont un hors du studio.', 'I work with two professional camera bodies and dual card slots: every image is recorded twice during the shoot, then backed up on three drives, one of them off-site.')).'</p>',
                    'photo' => ['photographe-materiel', 'camera', $T('Appareils photo, objectifs et cartes mémoire posés sur une table en bois', 'Cameras, lenses and memory cards laid out on a wooden table')],
                    'reverse' => true, 'bg' => 'light',
                ]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Réservez votre séance photo ou demandez un devis pour votre mariage ou votre entreprise. Réponse sous 48 heures, dans le pays et à l’étranger.', 'Book your photo session or request a quote for your wedding or business. Reply within 48 hours, working nationwide and abroad.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons de votre projet', 'Let’s talk about your project'), 'intro' => $T('Indiquez le type de séance, la date et le lieu : je vous réponds sous 48 heures.', 'Tell me the type of session, the date and the location: I will reply within 48 hours.')]),
                B::contact($theme, [
                    'title' => $T('Me contacter', 'Get in touch'), 'intro' => $T('Studio sur rendez-vous uniquement. Je me déplace pour les reportages et les séances en extérieur.', 'Studio by appointment only. I travel for coverage and outdoor sessions.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['calendar-days', $T('Rendez-vous du lundi au samedi', 'Appointments Monday to Saturday')],
                    ],
                    'social' => ['instagram', 'pinterest', 'vimeo', 'facebook'],
                    'subject' => true, 'button' => $T('Envoyer ma demande', 'Send my request'),
                    'bg' => 'white',
                ]),
                B::schedule($theme, ['eyebrow' => $T('Infos pratiques', 'Practical info'), 'title' => $T('Avant de réserver', 'Before you book'), 'groups' => [
                    [$T('Zone d’intervention', 'Where I work'), [
                        ['map-pin', $T('Studio et séances dans un rayon de 30 km', 'Studio and sessions within 30 km')],
                        ['car', $T('Partout dans le pays sur devis', 'Anywhere in the country on quote')],
                        ['plane', $T('Mariages et reportages à l’étranger', 'Weddings and assignments abroad')],
                    ]],
                    [$T('Délais', 'Turnaround'), [
                        ['clock', $T('Réponse aux demandes sous 48 heures', 'Replies to enquiries within 48 hours')],
                        ['image', $T('Portraits livrés sous deux à trois semaines', 'Portraits delivered within two to three weeks')],
                        ['heart', $T('Mariages livrés sous six semaines', 'Weddings delivered within six weeks')],
                    ]],
                ]]),
                B::quote($theme, ['text' => $T('Une photographie, c’est un instant qu’on a choisi de ne pas laisser partir.', 'A photograph is a moment you chose not to let go.'), 'source' => $T('— [Prénom Nom], photographe', '— [Prénom Nom], photographer'), 'bg' => 'white']),
            ]];

        $blog = [
            'base' => 'journal',
            'title' => $T('Journal', 'Journal'),
            'categories' => ['mariage' => $T('Mariage', 'Weddings'), 'conseils' => $T('Conseils photo', 'Photo tips'), 'coulisses' => $T('Coulisses', 'Behind the scenes')],
            'posts' => [
                ['key' => 'lumiere', 'slug' => $u('heure-doree-mariage', 'golden-hour-wedding'), 'category' => 'mariage', 'tags' => [$T('Lumière', 'Light')],
                    'title' => $T('Pourquoi réserver vingt minutes à l’heure dorée', 'Why save twenty minutes for golden hour'),
                    'excerpt' => $T('La lumière de fin de journée transforme les portraits de couple.', 'Late-day light transforms couple portraits.'),
                    'photo' => ['photographe-blog-heure-doree', 'sun', $T('Couple de mariés en contre-jour au soleil couchant', 'Newlywed couple backlit by the setting sun')],
                    'heading' => $T('Comment l’organiser', 'How to plan it'),
                    'body' => [
                        $T('Une heure avant le coucher du soleil, la lumière devient douce, chaude et rasante : la peau est sublimée et les ombres disparaissent.', 'An hour before sunset, the light turns soft, warm and low: skin looks its best and harsh shadows disappear.'),
                        $T('Prévoyez une courte pause pendant le cocktail ou entre deux plats : vingt minutes suffisent, à deux pas du lieu de réception.', 'Plan a short break during the cocktail hour or between courses: twenty minutes is enough, close to the venue.'),
                        $T('Ce sont souvent les images que les couples préfèrent, et celles qu’ils font encadrer.', 'These are often the images couples love most, and the ones they frame.'),
                    ]],
                ['key' => 'aise', 'slug' => $u('etre-a-l-aise-en-photo', 'feeling-at-ease-on-camera'), 'category' => 'conseils', 'tags' => [$T('Portrait', 'Portrait')],
                    'title' => $T('Cinq astuces pour être à l’aise devant l’objectif', 'Five tips to feel at ease on camera'),
                    'excerpt' => $T('Respirer, bouger, oublier l’appareil : la recette de photos naturelles.', 'Breathe, move, forget the camera: the recipe for natural photos.'),
                    'photo' => ['photographe-blog-aise', 'smile', $T('Femme riant spontanément lors d’une séance portrait en extérieur', 'Woman laughing spontaneously during an outdoor portrait session')],
                    'quote' => $T('« Personne n’est photogénique sur commande ; tout le monde l’est quand il oublie qu’on le regarde. »', '“Nobody is photogenic on command; everyone is when they forget they are being watched.”'),
                    'body' => [
                        $T('Choisissez une tenue dans laquelle vous vous sentez bien, plutôt qu’une tenue achetée pour l’occasion.', 'Pick an outfit you feel good in, rather than one bought for the occasion.'),
                        $T('Bougez : marchez, tournez-vous, parlez à la personne qui vous accompagne. Le mouvement efface la crispation.', 'Move: walk, turn, talk to whoever is with you. Movement melts away tension.'),
                        $T('Et faites-moi confiance pour vous guider : vous n’avez rien à savoir faire.', 'And trust me to guide you: you do not need to know how to do anything.'),
                    ]],
                ['key' => 'sauvegarde', 'slug' => $u('proteger-ses-photos', 'protecting-your-photos'), 'category' => 'coulisses', 'tags' => [$T('Archives', 'Archiving')],
                    'title' => $T('Vos photos de famille méritent une sauvegarde', 'Your family photos deserve a backup'),
                    'excerpt' => $T('La règle du 3-2-1 expliquée simplement, et l’importance d’imprimer.', 'The 3-2-1 rule explained simply, and why printing matters.'),
                    'photo' => ['photographe-blog-tirages', 'image', $T('Album photo ouvert et tirages éparpillés sur une table', 'Open photo album and prints spread out on a table')],
                    'heading' => $T('La règle du 3-2-1', 'The 3-2-1 rule'),
                    'body' => [
                        $T('Trois copies de vos images, sur deux supports différents, dont une hors de chez vous : c’est la règle que j’applique pour mes clients.', 'Three copies of your images, on two different types of storage, one of them away from home: that is the rule I follow for my clients.'),
                        $T('Téléchargez votre galerie dès sa livraison, sur votre ordinateur et un disque externe ou un service en ligne.', 'Download your gallery as soon as it is delivered, to your computer and an external drive or online service.'),
                        $T('Et imprimez vos préférées : un album traverse les générations bien mieux qu’un fichier oublié.', 'And print your favorites: an album lasts through generations far better than a forgotten file.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => 'Portfolio', 'url' => $portfolio],
                    ['label' => $T('Mariage', 'Weddings'), 'url' => $wedding],
                    ['label' => $T('Portrait', 'Portraits'), 'url' => $portrait],
                    ['label' => $T('Entreprise', 'Business'), 'url' => $business],
                    ['label' => $T('Tarifs', 'Pricing'), 'url' => $pricing],
                    ['label' => $T('Journal', 'Journal'), 'url' => $journal],
                ],
                'cta_label' => $T('Réserver une séance', 'Book a session'),
                'cta_url' => $contact,
                'footer_text' => $T('Photographe et vidéaste professionnel : mariages, portraits, familles et entreprises, dans le pays et à l’étranger.', 'Professional photographer and videographer: weddings, portraits, families and businesses, nationwide and abroad.'),
                'footer_links' => [
                    ['label' => 'Portfolio', 'url' => $portfolio],
                    ['label' => $T('Offres et tarifs', 'Packages and pricing'), 'url' => $pricing],
                    ['label' => $T('Journal', 'Journal'), 'url' => $journal],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
