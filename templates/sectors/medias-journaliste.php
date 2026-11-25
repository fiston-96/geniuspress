<?php

/*
 * Pack « Médias et presse » — journaliste indépendant(e) : 8 pages, en français et en
 * anglais. Portfolio de reportages (articles, vidéos, photos), parcours, articles du blog,
 * prestations pour les rédactions et les organisations, formations, publications et prix,
 * contact. Les vidéos d'exemple sont des films libres de la Blender Foundation (CC BY).
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Médias et presse', 'en' => 'Media and press'],
        'icon' => 'newspaper',
        'keywords' => ['média', 'medias', 'presse', 'journal', 'radio', 'télévision', 'podcast', 'journaliste', 'pigiste', 'reporter', 'media', 'press', 'journalist', 'freelance', 'reporter'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $about = $u('/a-propos', '/about');
        $work = $u('/reportages', '/work');
        $articles = '/articles';
        $services = $u('/prestations', '/services');
        $training = $u('/formations', '/training');
        $press = $u('/publications', '/publications');
        $contact = '/contact';
        $post = fn (string $fr, string $en) => $articles.'/'.$T($fr, $en);
        $yt = fn (string $id) => 'https://www.youtube.com/watch?v='.$id;

        $reports = [
            ['medias-journaliste-reportage-1', 'droplet', $T('Les gardiennes du lac', 'Guardians of the lake'), $T('Reportage sur les femmes pêcheuses qui protègent les frayères du lac. Publié dans [nom du média].', 'A report on women fishers protecting the lake’s spawning grounds. Published in [outlet name].'), $post('les-gardiennes-du-lac', 'guardians-of-the-lake')],
            ['medias-journaliste-reportage-2', 'hard-hat', $T('Dans les mines artisanales', 'Inside artisanal mines'), $T('Enquête de six mois sur le travail des enfants dans les mines d’or artisanales.', 'A six-month investigation into child labor in artisanal gold mines.'), $post('enquete-mines-artisanales', 'investigation-artisanal-mines')],
            ['medias-journaliste-reportage-3', 'graduation-cap', $T('L’école sous les arbres', 'School under the trees'), $T('Comment des enseignants bénévoles font classe aux enfants déplacés.', 'How volunteer teachers hold classes for displaced children.'), $post('ecole-sous-les-arbres', 'school-under-the-trees')],
            ['medias-journaliste-reportage-4', 'smartphone', $T('L’argent mobile au village', 'Mobile money in the village'), $T('Portrait d’agents de transfert d’argent qui ont changé l’économie rurale.', 'Profiles of money-transfer agents who changed the rural economy.')],
            ['medias-journaliste-reportage-5', 'music', $T('La nouvelle vague de la rumba', 'Rumba’s new wave'), $T('Rencontre avec les jeunes artistes qui réinventent un patrimoine classé à l’Unesco.', 'Meeting the young artists reinventing a UNESCO-listed heritage.')],
            ['medias-journaliste-reportage-6', 'heart-pulse', $T('Sages-femmes de brousse', 'Bush midwives'), $T('Trois jours avec une sage-femme qui couvre seule douze villages.', 'Three days with a midwife who covers twelve villages on her own.')],
        ];
        $cta = ['title' => $T('Un sujet, une commande, une collaboration ?', 'A story, a commission, a collaboration?'), 'text' => $T('Rédactions, ONG, institutions : parlons de votre projet. Je réponds sous 48 heures.', 'Newsrooms, NGOs, institutions: let’s talk about your project. I reply within 48 hours.'),
            'buttons' => [[$T('Me contacter', 'Get in touch'), $contact, 'accent'], [$T('Voir mes prestations', 'See my services'), $services, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Journaliste indépendante : reportages, enquêtes et vérification des faits pour les médias et les organisations.', 'Independent journalist: reports, investigations and fact-checking for media outlets and organizations.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Journaliste indépendante · Grands Lacs', 'Independent journalist · Great Lakes'),
                    'title' => $T('Je raconte les histoires que l’on n’entend pas', 'I tell the stories no one else hears'),
                    'text' => $T('Reportages, enquêtes et portraits publiés dans la presse nationale et internationale. Écrit, photo et vidéo, depuis le terrain.', 'Reports, investigations and profiles published in national and international media. Text, photo and video, from the field.'),
                    'buttons' => [[$T('Voir mes reportages', 'See my work'), $work], [$T('Me contacter', 'Get in touch'), $contact]],
                    'photo' => ['medias-journaliste-accueil', 'camera', $T('Journaliste avec un appareil photo sur le terrain', 'Journalist with a camera in the field')],
                ]),
                B::split($theme, [
                    'eyebrow' => $T('Qui suis-je', 'About me'),
                    'title' => $T('Douze ans de terrain, une même exigence', 'Twelve years in the field, one standard'),
                    'html' => '<p>'.e($T('Ancienne reporter d’un quotidien national, je travaille en indépendante depuis 2017. Je couvre l’environnement, les droits humains et l’économie informelle, en français, en anglais et en swahili.', 'A former reporter for a national daily, I have worked freelance since 2017. I cover the environment, human rights and the informal economy, in French, English and Swahili.')).'</p>',
                    'bullets' => [$T('Écrit, photo et vidéo', 'Text, photo and video'), $T('Vérification rigoureuse des faits', 'Rigorous fact-checking'), $T('Réseau de sources dans toute la région', 'Network of sources across the region')],
                    'buttons' => [[$T('Mon parcours', 'My background'), $about, 'outline']],
                    'photo' => ['medias-journaliste-portrait', 'user-round', $T('Portrait de la journaliste', 'Portrait of the journalist')],
                    'reverse' => true,
                ]),
                B::stats($theme, ['items' => [
                    [240, '+', $T('articles et reportages publiés', 'stories and reports published')],
                    [35, '', $T('médias partenaires', 'partner outlets')],
                    [9, '', $T('pays couverts', 'countries covered')],
                    [4, '', $T('prix de journalisme', 'journalism awards')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Sélection', 'Selected work'), 'title' => $T('Reportages récents', 'Recent reports'), 'more' => $T('Lire', 'Read'), 'items' => array_slice($reports, 0, 3)]),
                M::posts($theme, ['eyebrow' => $T('Articles', 'Articles'), 'title' => $T('Derniers articles', 'Latest articles'), 'count' => 3, 'more' => $T('Lire la suite', 'Read more'), 'bg' => 'light']),
                B::testimonials($theme, ['eyebrow' => $T('Ils travaillent avec moi', 'Who I work with'), 'title' => $T('Ce qu’en disent les rédactions', 'What editors say'), 'items' => [
                    [$T('Des sujets solides, des sources fiables, des délais respectés : une correspondante sur qui l’on peut compter.', 'Solid stories, reliable sources, deadlines met: a correspondent you can count on.'), '[Prénom Nom]', $T('Chef du service Afrique, [média]', 'Africa editor, [outlet]')],
                    [$T('Son enquête sur les mines a été l’un de nos articles les plus lus de l’année.', 'Her mining investigation was one of our most-read stories of the year.'), '[Prénom Nom]', $T('Rédactrice en chef, [média]', 'Editor-in-chief, [outlet]')],
                    [$T('Une formation concrète : nos équipes vérifient désormais chaque chiffre avant publication.', 'Practical training: our teams now check every figure before publishing.'), '[Prénom Nom]', $T('Directeur de la communication, [ONG]', 'Communications director, [NGO]')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('a-propos', 'about'), 'title' => $T('À propos', 'About'),
            'description' => $T('Mon parcours, mes sujets, ma façon de travailler et mes engagements éthiques.', 'My background, my beats, how I work and my ethical commitments.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('À propos', 'About'), 'title' => $T('Journaliste, d’abord sur le terrain', 'A journalist, in the field first'), 'intro' => $T('Environnement, droits humains, économie du quotidien : les sujets qui comptent pour les gens.', 'Environment, human rights, everyday economy: the subjects that matter to people.')]),
                B::split($theme, [
                    'eyebrow' => $T('Mon histoire', 'My story'),
                    'title' => $T('De la radio communautaire aux grands médias', 'From community radio to major outlets'),
                    'html' => '<p>'.e($T('J’ai commencé à 19 ans dans une radio communautaire de mon quartier. Après des études de journalisme, j’ai rejoint la rédaction d’un quotidien national, puis choisi l’indépendance pour prendre le temps des sujets longs.', 'I started at 19 at a community radio station in my neighborhood. After studying journalism, I joined a national daily, then went freelance to take the time long stories need.')).'</p><p>'.e($T('Je collabore aujourd’hui avec des médias nationaux et internationaux, et je forme de jeunes journalistes à la vérification des faits.', 'Today I work with national and international outlets, and train young journalists in fact-checking.')).'</p>',
                    'photo' => ['medias-journaliste-bio', 'notebook-pen', $T('Journaliste prenant des notes pendant une interview', 'Journalist taking notes during an interview')],
                ]),
                M::timeline($theme, ['eyebrow' => $T('Parcours', 'Career'), 'title' => $T('Les grandes étapes', 'Key milestones'), 'bg' => 'light', 'items' => [
                    ['2012', $T('Radio communautaire', 'Community radio'), $T('Premiers reportages et présentation du journal local.', 'First reports and presenting the local news.')],
                    ['2014', $T('Diplôme de journalisme', 'Journalism degree'), $T('Spécialisation en journalisme d’investigation.', 'Specialized in investigative journalism.')],
                    ['2015', $T('Reporter dans un quotidien national', 'Reporter at a national daily'), $T('Rubriques société et environnement.', 'Society and environment desks.')],
                    ['2017', $T('Indépendante', 'Freelance'), $T('Correspondante pour des médias nationaux et internationaux.', 'Correspondent for national and international outlets.')],
                    ['2023', $T('Formatrice', 'Trainer'), $T('Ateliers de vérification des faits pour les rédactions et les ONG.', 'Fact-checking workshops for newsrooms and NGOs.')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Mes engagements', 'My commitments'), 'title' => $T('Une éthique, pas d’exception', 'Ethics, no exceptions'), 'items' => [
                    ['shield-check', $T('Exactitude', 'Accuracy'), $T('Chaque fait est vérifié auprès de plusieurs sources.', 'Every fact is checked with several sources.')],
                    ['lock', $T('Protection des sources', 'Source protection'), $T('Personne n’est exposé sans son consentement éclairé.', 'No one is exposed without their informed consent.')],
                    ['scale', $T('Indépendance', 'Independence'), $T('Je refuse tout contenu de complaisance ou publicité déguisée.', 'I refuse puff pieces and disguised advertising.')],
                ]]),
                B::quote($theme, ['text' => $T('Un bon reportage ne parle pas à la place des gens : il leur tend le micro.', 'A good report doesn’t speak for people: it hands them the microphone.'), 'source' => '— [Prénom Nom]', 'bg' => 'light']),
            ]];

        $pages[] = ['key' => 'work', 'slug' => $u('reportages', 'work'), 'title' => $T('Reportages', 'Work'),
            'description' => $T('Portfolio : reportages écrits, vidéos et photographies publiés dans la presse.', 'Portfolio: written reports, videos and photographs published in the press.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Portfolio', 'Portfolio'), 'title' => $T('Mes reportages', 'My work'), 'intro' => $T('Une sélection de travaux publiés : écrit, vidéo et photographie.', 'A selection of published work: text, video and photography.')]),
                B::cards($theme, ['more' => $T('Lire', 'Read'), 'items' => $reports]),
                M::videos($theme, ['eyebrow' => $T('Vidéo', 'Video'), 'title' => $T('Reportages filmés', 'Video reports'), 'intro' => $T('Tournés et montés en solo, au téléphone ou à la caméra.', 'Filmed and edited solo, on a phone or a camera.'), 'bg' => 'light', 'items' => [
                    [$T('Les gardiennes du lac', 'Guardians of the lake'), $yt('Y-rmzh0PI3c'), '12:10'],
                    [$T('L’école sous les arbres', 'School under the trees'), $yt('SkVqJ1SGeL0'), '2:30'],
                    [$T('La nouvelle vague de la rumba', 'Rumba’s new wave'), $yt('WhWc3b3KhnY'), '7:44'],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('Photographie', 'Photography'), 'title' => $T('Images de terrain', 'Field images'), 'photos' => [
                    ['medias-journaliste-photo-1', 'camera', $T('Pêcheuses sur le lac au lever du soleil', 'Women fishing on the lake at sunrise')],
                    ['medias-journaliste-photo-2', 'camera', $T('Marché de village', 'Village market')],
                    ['medias-journaliste-photo-3', 'camera', $T('Enfants sur le chemin de l’école', 'Children on their way to school')],
                    ['medias-journaliste-photo-4', 'camera', $T('Musiciens en répétition', 'Musicians rehearsing')],
                    ['medias-journaliste-photo-5', 'camera', $T('Route de terre après la pluie', 'Dirt road after the rain')],
                    ['medias-journaliste-photo-6', 'camera', $T('Portrait d’une sage-femme', 'Portrait of a midwife')],
                ]]),
            ]];

        $pages[] = ['key' => 'articles', 'slug' => 'articles', 'title' => 'Articles',
            'description' => $T('Mes articles : reportages, enquêtes, carnets de terrain et conseils sur le métier.', 'My articles: reports, investigations, field notes and advice on the craft.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Articles', 'title' => $T('Articles et carnets de terrain', 'Articles and field notes'), 'intro' => $T('Reportages, enquêtes et coulisses du métier.', 'Reports, investigations and behind-the-scenes of the craft.')]),
                M::terms($theme, ['bg' => 'light']),
                M::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('prestations', 'services'), 'title' => $T('Prestations', 'Services'),
            'description' => $T('Reportages, enquêtes, vérification des faits, rédaction et production locale pour les médias et les organisations.', 'Reports, investigations, fact-checking, writing and local production for media outlets and organizations.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Prestations', 'Services'), 'title' => $T('Ce que je peux faire pour vous', 'What I can do for you'), 'intro' => $T('Pour les rédactions, les ONG, les institutions et les entreprises.', 'For newsrooms, NGOs, institutions and companies.')]),
                B::features($theme, ['items' => [
                    ['newspaper', $T('Reportage', 'Reporting'), $T('Sujets écrits, photo ou vidéo, livrés prêts à publier.', 'Text, photo or video stories, delivered ready to publish.')],
                    ['search', $T('Enquête', 'Investigation'), $T('Enquêtes au long cours, documents et sources recoupées.', 'In-depth investigations, documents and cross-checked sources.')],
                    ['shield-check', $T('Vérification des faits', 'Fact-checking'), $T('Relecture et vérification de vos contenus avant publication.', 'Review and verification of your content before publication.')],
                    ['pencil', $T('Rédaction', 'Writing'), $T('Rapports, portraits et récits pour les organisations.', 'Reports, profiles and stories for organizations.')],
                    ['video', $T('Photo et vidéo', 'Photo and video'), $T('Images de terrain, montage court pour les réseaux.', 'Field images, short edits for social media.')],
                    ['map', $T('Production locale', 'Local production'), $T('Fixeur, traduction et logistique pour les équipes de passage.', 'Fixing, translation and logistics for visiting crews.')],
                ]]),
                B::pricing($theme, ['eyebrow' => $T('Tarifs', 'Rates'), 'title' => $T('Tarifs indicatifs', 'Indicative rates'), 'intro' => $T('Chaque projet fait l’objet d’un devis ; frais de déplacement en sus.', 'Every project gets a quote; travel costs extra.'), 'url' => $contact, 'bg' => 'light', 'items' => [
                    [$T('Article', 'Article'), '250', '$', $T('/ article', '/ article'), $T('Un article de 6 000 à 8 000 signes, sources vérifiées.', 'A 1,000–1,300 word article, sources checked.'), [$T('Interviews et recherches', 'Interviews and research'), $T('Deux relectures incluses', 'Two revisions included'), $T('Photos sur demande', 'Photos on request'), '-'.$T('Vidéo', 'Video')], $T('Demander un devis', 'Request a quote')],
                    [$T('Journée de reportage', 'Reporting day'), '400', '$', $T('/ jour', '/ day'), $T('Une journée de terrain, écrit, photo et vidéo.', 'A day in the field: text, photo and video.'), [$T('Préparation et contacts', 'Preparation and contacts'), $T('Photos retouchées', 'Edited photos'), $T('Vidéo courte montée', 'Short edited video'), $T('Droits d’usage définis', 'Defined usage rights')], $T('Réserver', 'Book'), true, $T('Le plus demandé', 'Most requested')],
                    [$T('Fixeur', 'Fixer'), '200', '$', $T('/ jour', '/ day'), $T('Accompagnement des équipes étrangères sur le terrain.', 'Supporting foreign crews in the field.'), [$T('Contacts et autorisations', 'Contacts and permits'), $T('Traduction', 'Translation'), $T('Logistique et sécurité', 'Logistics and safety'), '-'.$T('Véhicule', 'Vehicle')], $T('Me contacter', 'Get in touch')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Méthode', 'Method'), 'title' => $T('Comment nous travaillons ensemble', 'How we work together'), 'items' => [
                    [$T('Échange', 'Brief'), $T('Vous me présentez le sujet, l’angle et le format.', 'You tell me the story, the angle and the format.')],
                    [$T('Devis', 'Quote'), $T('Je propose un plan, un calendrier et un tarif.', 'I propose a plan, a timeline and a rate.')],
                    [$T('Terrain', 'Fieldwork'), $T('Recherches, interviews, images : je vous tiens informé.', 'Research, interviews, images: I keep you posted.')],
                    [$T('Livraison', 'Delivery'), $T('Contenus vérifiés, livrés dans les délais convenus.', 'Checked content, delivered on time.')],
                ]]),
                B::faq($theme, ['title' => $T('Questions fréquentes', 'Frequently asked questions'), 'bg' => 'light', 'items' => [
                    [$T('Travaillez-vous pour des organisations non médiatiques ?', 'Do you work for non-media organizations?'), $T('Oui, pour des rapports et récits. Ces contenus sont toujours signés et identifiés comme tels, jamais présentés comme du journalisme indépendant.', 'Yes, for reports and stories. That content is always signed and labeled as such, never presented as independent journalism.')],
                    [$T('Qui détient les droits des contenus ?', 'Who owns the rights to the content?'), $T('Les droits d’usage sont précisés dans le devis : publication, durée, supports.', 'Usage rights are set out in the quote: publication, duration, media.')],
                    [$T('Dans quelles langues travaillez-vous ?', 'Which languages do you work in?'), $T('Français, anglais et swahili ; lingala avec un interprète.', 'French, English and Swahili; Lingala with an interpreter.')],
                ]]),
            ]];

        $pages[] = ['key' => 'training', 'slug' => $u('formations', 'training'), 'title' => $T('Formations', 'Training'),
            'description' => $T('Ateliers pour les rédactions, les ONG et les étudiants : vérification des faits, écriture, journalisme mobile, sécurité.', 'Workshops for newsrooms, NGOs and students: fact-checking, writing, mobile journalism, safety.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Formations', 'Training'), 'title' => $T('Transmettre le métier', 'Passing on the craft'), 'intro' => $T('Des ateliers pratiques, sur vos sujets et avec vos outils.', 'Hands-on workshops, on your topics and with your tools.')]),
                B::features($theme, ['cols' => 4, 'items' => [
                    ['shield-check', $T('Vérification des faits', 'Fact-checking'), $T('Repérer une rumeur, vérifier une image, un chiffre, une citation.', 'Spot a rumor, verify an image, a figure, a quote.')],
                    ['pencil', $T('Écriture journalistique', 'News writing'), $T('Angle, structure, titres : écrire pour être lu.', 'Angle, structure, headlines: writing to be read.')],
                    ['smartphone', $T('Journalisme mobile', 'Mobile journalism'), $T('Filmer, enregistrer et monter avec un simple téléphone.', 'Film, record and edit with just a phone.')],
                    ['shield', $T('Sécurité', 'Safety'), $T('Se protéger, protéger ses sources et ses données.', 'Protect yourself, your sources and your data.')],
                ]]),
                B::events($theme, ['eyebrow' => $T('Agenda', 'Calendar'), 'title' => $T('Prochaines sessions', 'Upcoming sessions'), 'bg' => 'light', 'items' => [
                    [$T('Vérifier une image en 10 minutes', 'Verify an image in 10 minutes'), $T('12 mars, 9 h – 13 h', 'March 12, 9 am – 1 pm'), $T('En ligne', 'Online')],
                    [$T('Journalisme mobile', 'Mobile journalism'), $T('2 – 3 avril', 'April 2–3'), $T('Maison de la presse, Goma', 'Press House, Goma')],
                    [$T('Enquêter avec des données', 'Investigating with data'), $T('15 mai, 9 h – 17 h', 'May 15, 9 am – 5 pm'), $T('Université, Bukavu', 'University, Bukavu')],
                    [$T('Sécurité numérique des journalistes', 'Digital safety for journalists'), $T('5 juin, 14 h – 17 h', 'June 5, 2 – 5 pm'), $T('En ligne', 'Online')],
                ]]),
                B::testimonials($theme, ['eyebrow' => $T('Participants', 'Participants'), 'title' => $T('Ce qu’ils en retiennent', 'What they took away'), 'items' => [
                    [$T('J’ai appris à vérifier une photo virale avant de la partager. Indispensable.', 'I learned to verify a viral photo before sharing it. Essential.'), '[Prénom Nom]', $T('Journaliste radio', 'Radio journalist')],
                    [$T('Une formation très concrète, avec nos propres exemples.', 'Very practical training, using our own examples.'), '[Prénom Nom]', $T('Chargée de communication', 'Communications officer')],
                    [$T('Je tourne maintenant mes reportages au téléphone, avec un vrai son.', 'I now shoot my reports on my phone, with proper sound.'), '[Prénom Nom]', $T('Étudiant en journalisme', 'Journalism student')],
                ]]),
                B::cta($theme, ['title' => $T('Une formation pour votre équipe ?', 'Training for your team?'), 'text' => $T('Ateliers sur mesure d’une demi-journée à cinq jours, sur place ou en ligne.', 'Tailored workshops from half a day to five days, on site or online.'), 'buttons' => [[$T('Demander un programme', 'Request a program'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'publications', 'slug' => 'publications', 'title' => $T('Publications et prix', 'Publications and awards'),
            'description' => $T('Les médias qui publient mon travail, mes prix et mes interventions publiques.', 'The outlets that publish my work, my awards and my public talks.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Publications', 'Publications'), 'title' => $T('Publications et prix', 'Publications and awards'), 'intro' => $T('Où lire mon travail, et les distinctions qu’il a reçues.', 'Where to read my work, and the recognition it has received.')]),
                B::features($theme, ['eyebrow' => $T('Ils ont publié mon travail', 'My work has appeared in'), 'title' => $T('Médias partenaires', 'Partner outlets'), 'cols' => 4, 'items' => [
                    ['newspaper', '[Nom du quotidien]', $T('Correspondante régionale depuis 2017.', 'Regional correspondent since 2017.')],
                    ['globe', '[Nom du média international]', $T('Reportages et enquêtes en français et en anglais.', 'Reports and investigations in French and English.')],
                    ['mic', '[Nom de la radio]', $T('Chroniques hebdomadaires sur l’environnement.', 'Weekly environment segments.')],
                    ['video', '[Nom de la chaîne]', $T('Documentaires et reportages filmés.', 'Documentaries and filmed reports.')],
                ]]),
                M::timeline($theme, ['eyebrow' => $T('Distinctions', 'Awards'), 'title' => $T('Prix et récompenses', 'Prizes and awards'), 'bg' => 'light', 'items' => [
                    ['2024', $T('Prix du journalisme d’investigation', 'Investigative journalism prize'), $T('Pour l’enquête « Dans les mines artisanales ».', 'For the investigation “Inside artisanal mines”.')],
                    ['2022', $T('Prix du reportage environnemental', 'Environmental reporting prize'), $T('Pour « Les gardiennes du lac ».', 'For “Guardians of the lake”.')],
                    ['2020', $T('Bourse de reportage', 'Reporting fellowship'), $T('Six mois de reportage sur l’économie informelle.', 'Six months reporting on the informal economy.')],
                    ['2018', $T('Prix du jeune journaliste', 'Young journalist award'), $T('Catégorie presse écrite.', 'Print category.')],
                ]]),
                B::events($theme, ['eyebrow' => $T('Interventions', 'Talks'), 'title' => $T('Conférences et tables rondes', 'Conferences and panels'), 'icon' => 'mic', 'items' => [
                    [$T('Forum des médias africains', 'African Media Forum'), $T('Septembre 2025', 'September 2025'), $T('Table ronde sur le journalisme d’investigation', 'Panel on investigative journalism')],
                    [$T('Semaine de la presse', 'Press Week'), $T('Mai 2025', 'May 2025'), $T('Atelier sur la vérification des faits', 'Fact-checking workshop')],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez-moi pour une commande, une formation, une interview ou pour transmettre une information.', 'Contact me for a commission, training, an interview or to share information.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons de votre projet', 'Let’s talk about your project'), 'intro' => $T('Je réponds à chaque message sous 48 heures.', 'I reply to every message within 48 hours.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['briefcase', $T('Commande', 'Commission'), $T('Reportage, enquête, vérification : décrivez votre besoin et votre calendrier.', 'Report, investigation, fact-check: describe your need and timeline.'), $services, $T('Voir les prestations', 'See services')],
                    ['graduation-cap', $T('Formation', 'Training'), $T('Indiquez le public, le nombre de participants et les dates souhaitées.', 'Tell me the audience, number of participants and preferred dates.'), $training, $T('Voir les formations', 'See training')],
                    ['lock', $T('Information confidentielle', 'Confidential tip'), $T('Pour transmettre un document en toute sécurité, demandez-moi un canal chiffré.', 'To share a document securely, ask me for an encrypted channel.')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Coordonnées', 'Contact details'), 'intro' => $T('Basée à Goma, disponible pour des missions dans toute la région.', 'Based in Goma, available for assignments across the region.'),
                    'items' => [
                        ['map-pin', $T('Goma, Nord-Kivu, RDC', 'Goma, North Kivu, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['languages', $T('Français · anglais · swahili', 'French · English · Swahili')],
                    ],
                    'social' => ['x', 'instagram', 'facebook', 'youtube'],
                    'subject' => true,
                ]),
            ]];

        $blog = [
            'base' => 'articles',
            'title' => 'Articles',
            'layout' => ['share' => true, 'terms' => true],
            'interval' => 7,
            'categories' => ['reportages' => $T('Reportages', 'Reports'), 'enquetes' => $T('Enquêtes', 'Investigations'), 'carnets' => $T('Carnets de terrain', 'Field notes'), 'metier' => $T('Le métier', 'The craft')],
            'posts' => [
                ['key' => 'lac', 'slug' => $u('les-gardiennes-du-lac', 'guardians-of-the-lake'), 'category' => 'reportages', 'tags' => [$T('Environnement', 'Environment'), $T('Femmes', 'Women')],
                    'title' => $T('Les gardiennes du lac', 'Guardians of the lake'),
                    'excerpt' => $T('Sur les rives du lac, des femmes pêcheuses ont créé des zones de repos pour les poissons. Leurs prises ont augmenté.', 'On the lakeshore, women fishers have set up rest zones for fish. Their catches have grown.'),
                    'photo' => ['medias-journaliste-reportage-1', 'droplet', $T('Pirogues de pêche au bord d’un lac', 'Fishing canoes on a lakeshore')],
                    'heading' => $T('Des résultats mesurés', 'Measured results'),
                    'quote' => $T('« Nous ne pêchons plus dans les frayères : le lac nous le rend. » — [Prénom Nom], pêcheuse', '“We no longer fish in the spawning grounds: the lake gives it back to us.” — [First Last], fisher'),
                    'body' => [
                        $T('Il est 5 h quand les pirogues quittent la rive. Depuis trois ans, l’association des femmes pêcheuses interdit la pêche dans deux baies où les poissons se reproduisent.', 'It is 5 am when the canoes leave the shore. For three years, the women fishers’ association has banned fishing in two bays where fish breed.'),
                        $T('Selon les relevés du service des pêches, les prises ont augmenté de [x] % dans les zones voisines. D’autres villages veulent désormais copier le modèle.', 'According to fisheries data, catches have risen by [x]% in neighboring areas. Other villages now want to copy the model.'),
                        $T('Ce reportage a été publié dans [nom du média] et récompensé par le prix du reportage environnemental.', 'This report was published in [outlet name] and won the environmental reporting prize.'),
                    ]],
                ['key' => 'mines', 'slug' => $u('enquete-mines-artisanales', 'investigation-artisanal-mines'), 'category' => 'enquetes', 'tags' => [$T('Droits humains', 'Human rights'), $T('Mines', 'Mining')],
                    'title' => $T('Dans les mines artisanales, six mois d’enquête', 'Inside artisanal mines: a six-month investigation'),
                    'excerpt' => $T('Comment j’ai documenté le travail des enfants dans les mines d’or, sans exposer ceux qui m’ont parlé.', 'How I documented child labor in gold mines without exposing those who spoke to me.'),
                    'photo' => ['medias-journaliste-reportage-2', 'hard-hat', $T('Site minier artisanal', 'Artisanal mining site')],
                    'heading' => $T('Protéger les sources', 'Protecting sources'),
                    'body' => [
                        $T('Pendant six mois, j’ai suivi la filière de l’or artisanal, des puits jusqu’aux comptoirs d’achat. Une enquête menée avec prudence, dans une zone où parler peut coûter cher.', 'For six months, I followed the artisanal gold trade, from the pits to the buying houses. An investigation carried out with care, in an area where speaking out can be costly.'),
                        $T('Aucun enfant n’est identifiable sur les photos publiées. Les témoignages ont été recueillis avec l’accord des familles et les noms modifiés.', 'No child can be identified in the published photos. Testimonies were collected with families’ consent and names changed.'),
                        $T('L’enquête a été publiée en trois volets et a conduit les autorités provinciales à annoncer des contrôles.', 'The investigation ran in three parts and led provincial authorities to announce inspections.'),
                    ]],
                ['key' => 'ecole', 'slug' => $u('ecole-sous-les-arbres', 'school-under-the-trees'), 'category' => 'reportages', 'tags' => [$T('Éducation', 'Education'), $T('Déplacés', 'Displaced people')],
                    'title' => $T('L’école sous les arbres', 'School under the trees'),
                    'excerpt' => $T('Dans un camp de déplacés, des enseignants bénévoles font classe à 300 enfants, à l’ombre des manguiers.', 'In a displacement camp, volunteer teachers hold class for 300 children in the shade of mango trees.'),
                    'photo' => ['medias-journaliste-reportage-3', 'graduation-cap', $T('Enfants assis en classe en plein air', 'Children sitting in an open-air class')],
                    'body' => [
                        $T('Chaque matin, des enfants s’assoient sur des bancs de bois sous les manguiers. Leurs enseignants sont eux-mêmes déplacés : ils ont fui avec leurs élèves.', 'Every morning, children sit on wooden benches under the mango trees. Their teachers are displaced too: they fled with their pupils.'),
                        $T('Sans salaire, avec quelques craies et un tableau, ils suivent le programme officiel pour que les enfants ne perdent pas leur année.', 'Unpaid, with a few pieces of chalk and a blackboard, they follow the official curriculum so the children don’t lose their school year.'),
                        $T('Le reportage vidéo est à voir sur la page « Reportages ».', 'The video report is on the “Work” page.'),
                    ]],
                ['key' => 'carnet', 'slug' => $u('carnet-route-saison-des-pluies', 'field-notes-rainy-season'), 'category' => 'carnets', 'tags' => [$T('Terrain', 'Fieldwork')],
                    'title' => $T('Carnet de route : reporter en saison des pluies', 'Field notes: reporting in the rainy season'),
                    'excerpt' => $T('Routes coupées, matériel trempé, rendez-vous reportés : les coulisses d’un reportage en pleine saison des pluies.', 'Washed-out roads, soaked gear, postponed interviews: behind the scenes of a rainy-season report.'),
                    'photo' => ['medias-journaliste-photo-5', 'map', $T('Route de terre boueuse', 'Muddy dirt road')],
                    'body' => [
                        $T('Quatre heures prévues, onze heures de route. La saison des pluies impose son rythme au reportage : il faut prévoir large, et accepter l’imprévu.', 'Four hours planned, eleven hours on the road. The rainy season sets the pace for reporting: you have to plan generously and accept the unexpected.'),
                        $T('Mes indispensables : sacs étanches, batterie externe, carnet papier et un plan B pour chaque interview.', 'My essentials: dry bags, a power bank, a paper notebook and a plan B for every interview.'),
                        $T('Et surtout, des contacts locaux de confiance, sans qui aucun reportage ne serait possible.', 'And above all, trusted local contacts, without whom no report would be possible.'),
                    ]],
                ['key' => 'verifier', 'slug' => $u('verifier-une-image-virale', 'verify-a-viral-image'), 'category' => 'metier', 'tags' => [$T('Vérification', 'Fact-checking'), $T('Outils', 'Tools')],
                    'title' => $T('Vérifier une image virale en cinq étapes', 'Verify a viral image in five steps'),
                    'excerpt' => $T('Recherche inversée, métadonnées, contexte : la méthode que j’enseigne en formation.', 'Reverse search, metadata, context: the method I teach in my workshops.'),
                    'photo' => ['medias-journaliste-actu-verif', 'search', $T('Journaliste vérifiant une image sur un ordinateur', 'Journalist checking an image on a computer')],
                    'heading' => $T('La méthode', 'The method'),
                    'body' => [
                        $T('Une photo choc circule sur les réseaux ? Avant de la partager, prenez cinq minutes. La plupart des fausses images sont d’anciennes photos sorties de leur contexte.', 'A shocking photo is going around? Before sharing it, take five minutes. Most fake images are old photos taken out of context.'),
                        $T('Première étape : la recherche d’image inversée, pour retrouver sa première publication. Ensuite, observer les détails : panneaux, météo, végétation, vêtements.', 'Step one: a reverse image search to find where it first appeared. Then look at the details: signs, weather, vegetation, clothing.'),
                        $T('Enfin, contacter la source d’origine. Ces étapes sont détaillées dans mon atelier « Vérifier une image en 10 minutes ».', 'Finally, contact the original source. These steps are covered in my workshop “Verify an image in 10 minutes”.'),
                    ]],
                ['key' => 'pitch', 'slug' => $u('proposer-un-sujet-a-une-redaction', 'pitching-a-story-to-an-editor'), 'category' => 'metier', 'tags' => [$T('Pige', 'Freelancing'), $T('Conseils', 'Tips')],
                    'title' => $T('Comment proposer un sujet à une rédaction', 'How to pitch a story to an editor'),
                    'excerpt' => $T('Un angle, des sources, un format : mes conseils aux jeunes pigistes pour se faire publier.', 'An angle, sources, a format: my advice to young freelancers on getting published.'),
                    'photo' => ['medias-journaliste-actu-pitch', 'send', $T('Journaliste écrivant sur un ordinateur portable', 'Journalist writing on a laptop')],
                    'body' => [
                        $T('Les rédactions reçoivent des dizaines de propositions par semaine. Pour sortir du lot, votre courriel doit tenir en dix lignes.', 'Editors receive dozens of pitches a week. To stand out, your email should fit in ten lines.'),
                        $T('Un titre provisoire, l’angle en une phrase, pourquoi maintenant, vos sources et le format proposé. Ajoutez deux liens vers vos meilleurs travaux.', 'A working title, the angle in one sentence, why now, your sources and the proposed format. Add two links to your best work.'),
                        $T('Pas de réponse au bout d’une semaine ? Une relance polie, puis proposez le sujet ailleurs.', 'No reply after a week? One polite follow-up, then pitch it elsewhere.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('À propos', 'About'), 'url' => $about],
                    ['label' => $T('Reportages', 'Work'), 'url' => $work],
                    ['label' => 'Articles', 'url' => $articles],
                    ['label' => $T('Prestations', 'Services'), 'url' => $services],
                    ['label' => $T('Formations', 'Training'), 'url' => $training],
                    ['label' => 'Publications', 'url' => $press],
                ],
                'cta_label' => $T('Me contacter', 'Get in touch'),
                'cta_url' => $contact,
                'footer_text' => $T('Journaliste indépendante : reportages, enquêtes, vérification des faits et formations.', 'Independent journalist: reports, investigations, fact-checking and training.'),
                'footer_links' => [
                    ['label' => $T('Reportages', 'Work'), 'url' => $work],
                    ['label' => $T('Prestations', 'Services'), 'url' => $services],
                    ['label' => $T('Formations', 'Training'), 'url' => $training],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
