<?php

/*
 * Pack « Églises et communautés de foi » : 8 pages, en français et en anglais.
 * Contenu neutre sur le plan confessionnel, à personnaliser (l'assistant IA le
 * réécrit à partir de la présentation de la communauté). Les liens internes
 * sont écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Églises et communautés de foi', 'en' => 'Churches and faith communities'],
        'icon' => 'church',
        'keywords' => ['église', 'eglise', 'culte', 'paroisse', 'pasteur', 'ministère', 'communauté de foi', 'chrétien', 'prière', 'church', 'worship', 'ministry', 'parish', 'pastor', 'congregation', 'faith'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $about = $u('/notre-eglise', '/our-church');
        $services = $u('/cultes', '/services');
        $ministries = $u('/ministeres', '/ministries');
        $events = $u('/evenements', '/events');
        $teachings = $u('/enseignements', '/teachings');
        $giving = $u('/dons', '/giving');
        $contact = '/contact';

        $ministryItems = [
            ['baby', $T('Enfants', 'Children'), $T('École du dimanche, histoires bibliques, jeux et chants dans un cadre sûr et joyeux.', 'Sunday school, Bible stories, games and songs in a safe and joyful setting.')],
            ['sparkles', $T('Jeunesse', 'Youth'), $T('Rencontres, camps, mentorat et projets pour aider les jeunes à grandir dans leur foi et leur vie.', 'Gatherings, camps, mentoring and projects to help young people grow in faith and in life.')],
            ['heart', $T('Femmes', 'Women'), $T('Prière, partage, formation et entraide entre femmes de tous âges.', 'Prayer, sharing, training and mutual support among women of all ages.')],
            ['users', $T('Hommes', 'Men'), $T('Fraternité, responsabilité et service : des hommes qui s’encouragent à vivre leur foi au quotidien.', 'Fellowship, accountability and service: men encouraging one another to live out their faith daily.')],
            ['music', $T('Chorale et louange', 'Choir and worship'), $T('Chants, instruments et louange pour porter la prière de toute l’assemblée.', 'Singing, instruments and praise to carry the prayer of the whole congregation.')],
            ['hand-heart', $T('Action sociale', 'Outreach'), $T('Visites, aide alimentaire, soutien scolaire et accompagnement des familles en difficulté.', 'Visits, food aid, school support and care for families in need.')],
        ];
        $cta = ['title' => $T('Vous êtes les bienvenus', 'You are welcome here'), 'text' => $T('Quelle que soit votre histoire, il y a une place pour vous. Venez comme vous êtes ce dimanche.', 'Whatever your story, there is a place for you. Come as you are this Sunday.'),
            'buttons' => [[$T('Préparer ma visite', 'Plan my visit'), $services, 'accent'], [$T('Nous contacter', 'Contact us'), $contact, 'outline-light']]];
        $upcoming = [
            [$T('Veillée de prière', 'Prayer vigil'), $T('Vendredi, 19 h', 'Friday, 7 pm'), $T('Salle principale', 'Main hall')],
            [$T('Journée des familles', 'Family day'), $T('Samedi 14 mars, 10 h', 'Saturday, March 14, 10 am'), $T('Cour de l’église', 'Church courtyard')],
            [$T('Conférence de la jeunesse', 'Youth conference'), $T('Samedi 11 avril, 14 h', 'Saturday, April 11, 2 pm'), $T('Salle principale', 'Main hall')],
            [$T('Concert de louange', 'Worship concert'), $T('Dimanche 17 mai, 16 h', 'Sunday, May 17, 4 pm'), $T('Salle principale', 'Main hall')],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Une communauté chaleureuse où chacun trouve sa place. Découvrez nos cultes, nos ministères et nos prochains événements.', 'A warm community where everyone belongs. Discover our services, ministries and upcoming events.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Bienvenue à la maison', 'Welcome home'),
                    'title' => $T('Une famille pour prier, grandir et servir', 'A family to pray, grow and serve together'),
                    'text' => $T('Notre église accueille chacun avec amour. Venez adorer, apprendre et partager la vie d’une communauté qui marche ensemble.', 'Our church welcomes everyone with love. Come to worship, learn and share life with a community that walks together.'),
                    'buttons' => [[$T('Horaires des cultes', 'Service times'), $services], [$T('Notre église', 'Our church'), $about]],
                    'photo' => ['eglise-accueil', 'church', $T('Assemblée réunie pendant un culte', 'Congregation gathered during a service')],
                ]),
                B::schedule($theme, ['eyebrow' => $T('Nous rejoindre', 'Join us'), 'title' => $T('Quand nous nous rassemblons', 'When we gather'), 'bg' => 'white', 'groups' => [
                    [$T('Culte du dimanche', 'Sunday service'), [['clock', $T('1er culte : 8 h – 10 h', 'First service: 8 am – 10 am')], ['clock', $T('2e culte : 10 h 30 – 12 h 30', 'Second service: 10:30 am – 12:30 pm')], ['baby', $T('École du dimanche pendant le culte', 'Sunday school during the service')]]],
                    [$T('En semaine', 'During the week'), [['book-open', $T('Mercredi, 18 h : étude biblique', 'Wednesday, 6 pm: Bible study')], ['heart', $T('Vendredi, 19 h : veillée de prière', 'Friday, 7 pm: prayer vigil')], ['users', $T('Samedi, 15 h : jeunesse', 'Saturday, 3 pm: youth')]]],
                ]]),
                B::features($theme, ['eyebrow' => $T('Nos ministères', 'Our ministries'), 'title' => $T('Une place pour chaque âge', 'A place for every age'), 'bg' => 'light', 'cols' => 3, 'items' => array_slice($ministryItems, 0, 3)]),
                B::quote($theme, ['text' => $T('« Car là où deux ou trois sont assemblés en mon nom, je suis au milieu d’eux. »', '“For where two or three gather in my name, there am I with them.”'), 'source' => $T('Matthieu 18.20', 'Matthew 18:20')]),
                B::events($theme, ['eyebrow' => $T('Agenda', 'Calendar'), 'title' => $T('Prochains événements', 'Upcoming events'), 'bg' => 'light', 'items' => $upcoming]),
                B::split($theme, [
                    'eyebrow' => $T('Première visite ?', 'First visit?'),
                    'title' => $T('Nous serons heureux de vous accueillir', 'We would love to welcome you'),
                    'html' => '<p>'.e($T('Des accueillants vous guideront à votre arrivée, et nos équipes seront là pour vous présenter l’église. Aucune tenue particulière n’est demandée : venez simplement tel que vous êtes.', 'Greeters will guide you when you arrive, and our teams will be glad to introduce you to the church. There is no dress code: simply come as you are.')).'</p>',
                    'bullets' => [$T('Un accueil chaleureux à l’entrée', 'A warm welcome at the door'), $T('Un espace sûr pour les enfants', 'A safe space for children'), $T('Un temps de rencontre après le culte', 'Time to meet people after the service')],
                    'buttons' => [[$T('Ce qui vous attend', 'What to expect'), $services, 'outline']],
                    'photo' => ['eglise-accueillants', 'smile', $T('Équipe d’accueil souriante devant l’entrée', 'Smiling welcome team at the entrance')],
                    'reverse' => true,
                ]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('notre-eglise', 'our-church'), 'title' => $T('Notre église', 'Our church'),
            'description' => $T('Notre histoire, notre vision, nos valeurs et les pasteurs et responsables qui servent la communauté.', 'Our story, vision, values, and the pastors and leaders who serve the community.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Qui sommes-nous', 'Who we are'), 'title' => $T('Notre église, notre histoire', 'Our church, our story'), 'intro' => $T('Une communauté de croyants unis par l’amour de Dieu et le service des autres.', 'A community of believers united by the love of God and service to others.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Quelques familles, une grande foi', 'A few families, a great faith'),
                    'html' => '<p>'.e($T('Notre église est née dans un salon, autour de quelques familles qui priaient ensemble chaque semaine. Au fil des années, la communauté a grandi et accueille aujourd’hui plusieurs centaines de personnes de tous âges et de tous horizons.', 'Our church began in a living room, with a few families praying together each week. Over the years the community has grown and now welcomes several hundred people of all ages and backgrounds.')).'</p><p>'.e($T('Nous restons fidèles à cet esprit : une maison ouverte, où l’on prie, où l’on s’entraide et où l’on se sent attendu.', 'We remain faithful to that spirit: an open house where people pray, support one another and feel expected.')).'</p>',
                    'photo' => ['eglise-histoire', 'church', $T('Le premier bâtiment de l’église', 'The church’s first building')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce qui nous rassemble', 'What brings us together'), 'bg' => 'light', 'items' => [
                    ['book-open', $T('La Parole', 'The Word'), $T('Nous fondons notre vie sur l’enseignement de la Bible.', 'We build our lives on the teaching of the Bible.')],
                    ['heart-handshake', $T('L’amour', 'Love'), $T('Nous accueillons chacun avec respect, sans jugement.', 'We welcome everyone with respect, without judgment.')],
                    ['hand-helping', $T('Le service', 'Service'), $T('Nous agissons concrètement auprès de notre prochain.', 'We act in practical ways for our neighbor.')],
                    ['users-round', $T('La communion', 'Fellowship'), $T('Nous avançons ensemble, en famille de foi.', 'We walk together as a family of faith.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Notre vision', 'Our vision'),
                    'title' => $T('Des vies transformées, une ville touchée', 'Lives transformed, a city touched'),
                    'html' => '<p>'.e($T('Nous désirons voir des personnes rencontrer Dieu, grandir dans la foi et devenir une bénédiction pour leur famille, leur quartier et leur pays.', 'We long to see people meet God, grow in faith and become a blessing to their families, neighborhoods and nation.')).'</p>',
                    'bullets' => [$T('Adorer Dieu de tout notre cœur', 'Worship God with all our hearts'), $T('Former des disciples', 'Make disciples'), $T('Servir notre communauté', 'Serve our community')],
                    'photo' => ['eglise-vision', 'sun', $T('Lever de soleil sur la ville', 'Sunrise over the city')],
                    'reverse' => true,
                ]),
                B::team($theme, ['eyebrow' => $T('Nos responsables', 'Our leaders'), 'title' => $T('Pasteurs et équipe de direction', 'Pastors and leadership team'), 'bg' => 'light', 'items' => [
                    ['[Prénom Nom]', $T('Pasteur principal', 'Senior pastor'), 'eglise-pasteur-1'],
                    ['[Prénom Nom]', $T('Pasteure adjointe', 'Associate pastor'), 'eglise-pasteur-2'],
                    ['[Prénom Nom]', $T('Responsable de la louange', 'Worship leader'), 'eglise-responsable-3'],
                    ['[Prénom Nom]', $T('Diacre et trésorier', 'Deacon and treasurer'), 'eglise-responsable-4'],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('cultes', 'services'), 'title' => $T('Cultes', 'Services'),
            'description' => $T('Horaires des cultes et des réunions de la semaine, déroulement d’une première visite et réponses à vos questions.', 'Service and weekly meeting times, what to expect on your first visit, and answers to your questions.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Cultes', 'Services'), 'title' => $T('Venez adorer avec nous', 'Come and worship with us'), 'intro' => $T('Des temps de prière, de louange et d’enseignement pour toute la famille.', 'Times of prayer, praise and teaching for the whole family.')]),
                B::schedule($theme, ['title' => $T('Horaires de la semaine', 'Weekly schedule'), 'bg' => 'light', 'groups' => [
                    [$T('Dimanche', 'Sunday'), [['clock', $T('8 h – 10 h : premier culte', '8 am – 10 am: first service')], ['clock', $T('10 h 30 – 12 h 30 : second culte', '10:30 am – 12:30 pm: second service')], ['baby', $T('École du dimanche (3 – 12 ans)', 'Sunday school (ages 3–12)')]]],
                    [$T('Semaine', 'Weekdays'), [['book-open', $T('Mercredi, 18 h : étude biblique', 'Wednesday, 6 pm: Bible study')], ['heart', $T('Vendredi, 19 h : veillée de prière', 'Friday, 7 pm: prayer vigil')], ['music', $T('Samedi, 16 h : répétition de la chorale', 'Saturday, 4 pm: choir rehearsal')]]],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Première visite', 'First visit'), 'title' => $T('Ce qui vous attend', 'What to expect'), 'items' => [
                    [$T('Accueil', 'Welcome'), $T('Des accueillants vous saluent et vous placent.', 'Greeters welcome you and help you find a seat.')],
                    [$T('Louange', 'Praise'), $T('Environ trente minutes de chants et de prière.', 'About thirty minutes of singing and prayer.')],
                    [$T('Message', 'Message'), $T('Un enseignement de la Bible, simple et concret.', 'A Bible message, simple and practical.')],
                    [$T('Rencontre', 'Meeting'), $T('Un moment convivial pour faire connaissance.', 'A friendly time to get to know one another.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Pour les familles', 'For families'),
                    'title' => $T('Vos enfants sont les bienvenus', 'Your children are welcome'),
                    'html' => '<p>'.e($T('Pendant le culte, des animateurs formés accueillent les enfants par tranche d’âge avec des histoires, des chants et des activités adaptés.', 'During the service, trained leaders welcome children by age group with suitable stories, songs and activities.')).'</p>',
                    'photo' => ['eglise-enfants', 'baby', $T('Enfants chantant à l’école du dimanche', 'Children singing at Sunday school')],
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'intro' => $T('Une autre question ? Nous serons heureux de vous répondre.', 'Another question? We would be glad to answer.'), 'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'outline']], 'items' => [
                    [$T('Comment dois-je m’habiller ?', 'What should I wear?'), $T('Venez comme vous êtes : il n’y a pas de tenue imposée.', 'Come as you are: there is no dress code.')],
                    [$T('Combien de temps dure un culte ?', 'How long is a service?'), $T('Environ deux heures, avec des temps de louange, de prière et d’enseignement.', 'About two hours, with times of praise, prayer and teaching.')],
                    [$T('Y a-t-il un programme pour les enfants ?', 'Is there a program for children?'), $T('Oui, l’école du dimanche accueille les enfants de 3 à 12 ans pendant le culte.', 'Yes, Sunday school welcomes children aged 3 to 12 during the service.')],
                    [$T('Dois-je appartenir à une confession particulière ?', 'Do I need to belong to a particular denomination?'), $T('Non. Toute personne qui cherche Dieu est la bienvenue, quelle que soit son origine.', 'No. Anyone seeking God is welcome, whatever their background.')],
                ]]),
            ]];

        $pages[] = ['key' => 'ministries', 'slug' => $u('ministeres', 'ministries'), 'title' => $T('Ministères', 'Ministries'),
            'description' => $T('Enfants, jeunesse, femmes, hommes, chorale et action sociale : trouvez le ministère où grandir et servir.', 'Children, youth, women, men, choir and outreach: find the ministry where you can grow and serve.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Ministères', 'Ministries'), 'title' => $T('Grandir et servir ensemble', 'Grow and serve together'), 'intro' => $T('Chacun a reçu des talents : trouvez la place où les mettre au service des autres.', 'Everyone has received gifts: find the place to use them for others.')]),
                B::features($theme, ['cols' => 3, 'items' => $ministryItems]),
                B::split($theme, [
                    'eyebrow' => $T('Louange', 'Worship'),
                    'title' => $T('La musique au cœur de nos cultes', 'Music at the heart of our services'),
                    'html' => '<p>'.e($T('Chanteurs, musiciens, techniciens du son : l’équipe de louange accueille de nouveaux membres, débutants comme expérimentés.', 'Singers, musicians, sound technicians: the worship team welcomes new members, beginners and experienced alike.')).'</p>',
                    'bullets' => [$T('Répétitions chaque samedi', 'Rehearsals every Saturday'), $T('Formation vocale et instrumentale', 'Vocal and instrumental training'), $T('Ouvert à tous les niveaux', 'Open to all levels')],
                    'photo' => ['eglise-chorale', 'music', $T('Chorale chantant pendant le culte', 'Choir singing during the service')],
                    'bg' => 'light',
                ]),
                B::stats($theme, ['title' => $T('La communauté en chiffres', 'Our community in numbers'), 'items' => [
                    [600, '+', $T('membres et amis', 'members and friends')],
                    [12, '', $T('ministères actifs', 'active ministries')],
                    [80, '+', $T('serviteurs bénévoles', 'volunteer servants')],
                    [25, '', $T('années de présence', 'years of presence')],
                ]]),
                B::cta($theme, ['title' => $T('Prêt à servir ?', 'Ready to serve?'), 'text' => $T('Dites-nous ce qui vous anime : un responsable vous contactera pour en parler.', 'Tell us what you are passionate about: a leader will contact you to talk it through.'),
                    'buttons' => [[$T('Rejoindre un ministère', 'Join a ministry'), $contact, 'accent'], [$T('Voir les événements', 'See events'), $events, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'events', 'slug' => $u('evenements', 'events'), 'title' => $T('Événements', 'Events'),
            'description' => $T('Retrouvez les prochains événements de l’église : veillées, conférences, journées des familles et concerts.', 'Find the church’s upcoming events: vigils, conferences, family days and concerts.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Agenda', 'Calendar'), 'title' => $T('Les rendez-vous de la communauté', 'Community gatherings'), 'intro' => $T('Des moments pour prier, apprendre, célébrer et se retrouver.', 'Times to pray, learn, celebrate and meet.')]),
                B::events($theme, ['title' => $T('Prochains événements', 'Upcoming events'), 'bg' => 'light', 'items' => array_merge($upcoming, [
                    [$T('Baptêmes', 'Baptisms'), $T('Dimanche 7 juin, 10 h 30', 'Sunday, June 7, 10:30 am'), $T('Bord de rivière', 'Riverside')],
                    [$T('Camp de jeunes', 'Youth camp'), $T('Du 3 au 7 août', 'August 3 to 7'), $T('Centre de retraite', 'Retreat center')],
                ])]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('Souvenirs de nos rencontres', 'Memories of our gatherings'), 'photos' => [
                    ['eglise-galerie-1', 'camera', $T('Culte de Pâques', 'Easter service')],
                    ['eglise-galerie-2', 'camera', $T('Baptêmes en rivière', 'River baptisms')],
                    ['eglise-galerie-3', 'camera', $T('Camp de jeunes', 'Youth camp')],
                    ['eglise-galerie-4', 'camera', $T('Journée des familles', 'Family day')],
                    ['eglise-galerie-5', 'camera', $T('Concert de louange', 'Worship concert')],
                    ['eglise-galerie-6', 'camera', $T('Distribution de vivres', 'Food distribution')],
                ]]),
                B::cta($theme, ['title' => $T('Organiser avec nous ?', 'Want to help organize?'), 'text' => $T('Nos événements reposent sur l’aide de nombreux bénévoles. Rejoignez l’équipe d’organisation.', 'Our events rely on many volunteers. Join the organizing team.'),
                    'buttons' => [[$T('Proposer mon aide', 'Offer my help'), $contact, 'accent'], [$T('Nos ministères', 'Our ministries'), $ministries, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'teachings', 'slug' => $u('enseignements', 'teachings'), 'title' => $T('Enseignements', 'Teachings'),
            'description' => $T('Messages récents, séries d’enseignement et groupes d’étude biblique pour nourrir votre foi au quotidien.', 'Recent messages, teaching series and Bible study groups to nourish your faith every day.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Enseignements', 'Teachings'), 'title' => $T('Se nourrir de la Parole', 'Nourished by the Word'), 'intro' => $T('Retrouvez les messages du dimanche et prolongez-les au fil de la semaine.', 'Catch up on Sunday messages and carry them through the week.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::quote($theme, ['text' => $T('« Ta parole est une lampe à mes pieds, et une lumière sur mon sentier. »', '“Your word is a lamp for my feet, a light on my path.”'), 'source' => $T('Psaume 119.105', 'Psalm 119:105'), 'bg' => 'light']),
                B::features($theme, ['eyebrow' => $T('Groupes d’étude', 'Study groups'), 'title' => $T('Approfondir ensemble', 'Go deeper together'), 'cols' => 3, 'items' => [
                    ['users', $T('Groupes de maison', 'Home groups'), $T('Des petits groupes dans les quartiers, chaque semaine, pour prier et partager.', 'Small neighborhood groups meeting weekly to pray and share.')],
                    ['book-open-text', $T('Étude biblique', 'Bible study'), $T('Un parcours pas à pas pour lire la Bible en profondeur, le mercredi soir.', 'A step-by-step journey through Scripture on Wednesday evenings.')],
                    ['graduation-cap', $T('Parcours des nouveaux', 'New believers course'), $T('Six rencontres pour découvrir les bases de la foi et de la vie d’église.', 'Six sessions to discover the basics of faith and church life.')],
                ]]),
                B::cta($theme, ['title' => $T('Rejoindre un groupe', 'Join a group'), 'text' => $T('Indiquez-nous votre quartier : nous vous orienterons vers le groupe le plus proche.', 'Tell us your neighborhood: we will point you to the nearest group.'),
                    'buttons' => [[$T('Je m’inscris', 'Sign me up'), $contact, 'accent'], [$T('Horaires des cultes', 'Service times'), $services, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'giving', 'slug' => $u('dons', 'giving'), 'title' => $T('Dons', 'Giving'),
            'description' => $T('Dîmes, offrandes et soutien de nos projets : donnez par Mobile Money ou virement, en toute simplicité.', 'Tithes, offerings and project support: give by Mobile Money or bank transfer, simply and securely.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Dons', 'Giving'), 'title' => $T('Donner avec joie', 'Giving with joy'), 'intro' => $T('Chaque offrande permet à l’église de servir, d’enseigner et de secourir.', 'Every offering lets the church serve, teach and help.')]),
                B::donate($theme, [
                    'eyebrow' => $T('Projet en cours', 'Current project'),
                    'title' => $T('Un nouveau toit pour notre salle de culte', 'A new roof for our worship hall'),
                    'text' => $T('Pour accueillir tous nos fidèles en saison de pluie, nous rénovons la toiture de la salle principale. Chaque contribution, petite ou grande, nous rapproche du but.', 'To welcome everyone during the rainy season, we are renovating the roof of the main hall. Every gift, small or large, brings us closer to the goal.'),
                    'goal' => $T('Objectif atteint', 'Goal reached'), 'percent' => 55,
                    'amounts' => ['10 $', '25 $', '50 $', '100 $'],
                    'button' => $T('Je donne', 'I give'), 'url' => $contact,
                    'photo' => ['eglise-projet', 'church', $T('Salle de culte en cours de rénovation', 'Worship hall under renovation')],
                    'bg' => 'white',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Votre générosité en action', 'Your generosity at work'), 'title' => $T('Ce que permettent vos dons', 'What your gifts make possible'), 'bg' => 'light', 'items' => [
                    ['book-open', $T('L’enseignement', 'Teaching'), $T('Formation des responsables, Bibles et supports d’étude.', 'Leader training, Bibles and study materials.')],
                    ['hand-heart', $T('L’entraide', 'Compassion'), $T('Aide aux familles, visites aux malades et soutien scolaire.', 'Help for families, visits to the sick and school support.')],
                    ['church', $T('Le lieu de culte', 'The place of worship'), $T('Entretien du bâtiment, sonorisation et accueil des fidèles.', 'Building upkeep, sound system and welcoming the congregation.')],
                ]]),
                B::faq($theme, ['title' => $T('Vos questions sur les dons', 'Your questions about giving'), 'items' => [
                    [$T('Comment donner par Mobile Money ?', 'How can I give by Mobile Money?'), $T('Contactez-nous pour recevoir le numéro officiel de l’église. Indiquez en référence « dîme », « offrande » ou le nom du projet.', 'Contact us to receive the church’s official number. Use “tithe”, “offering” or the project name as the reference.')],
                    [$T('Puis-je faire un virement bancaire ?', 'Can I make a bank transfer?'), $T('Oui. Les coordonnées bancaires vous sont transmises sur simple demande auprès de notre trésorier.', 'Yes. Bank details are provided on request from our treasurer.')],
                    [$T('Puis-je donner pendant le culte ?', 'Can I give during the service?'), $T('Bien sûr : un temps d’offrande a lieu à chaque culte, en espèces ou par téléphone.', 'Of course: there is an offering time at every service, in cash or by phone.')],
                    [$T('Mes dons sont-ils suivis avec transparence ?', 'Are gifts handled transparently?'), $T('Oui. Un comité de gestion rend compte régulièrement à l’assemblée de l’utilisation des fonds.', 'Yes. A finance committee regularly reports to the congregation on how funds are used.')],
                ]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Adresse, téléphone, horaires des cultes et formulaire pour nous écrire : nous vous répondons avec joie.', 'Address, phone, service times and a form to write to us: we will gladly reply.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Nous serions heureux de vous lire', 'We would love to hear from you'), 'intro' => $T('Une question, une demande de prière ou une visite à planifier ? Écrivez-nous.', 'A question, a prayer request or a visit to plan? Write to us.')]),
                B::contact($theme, [
                    'title' => $T('Nous trouver', 'Find us'), 'intro' => $T('Le secrétariat est ouvert du mardi au vendredi. Les cultes ont lieu le dimanche.', 'The office is open Tuesday to Friday. Services are held on Sunday.'),
                    'items' => [
                        ['map-pin', $T('Avenue de l’Espoir, Kinshasa, RDC', 'Avenue de l’Espoir, Kinshasa, DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Cultes : dimanche 8 h et 10 h 30', 'Services: Sunday 8 am and 10:30 am')],
                        ['clock', $T('Secrétariat : mardi – vendredi, 9 h – 16 h', 'Office: Tuesday – Friday, 9 am – 4 pm')],
                    ],
                    'social' => ['facebook', 'youtube', 'whatsapp'],
                    'subject' => true,
                    'map' => 'Kinshasa',
                    'bg' => 'white',
                ]),
            ]];

        $blog = [
            'base' => $u('enseignements', 'teachings'),
            'title' => $T('Enseignements', 'Teachings'),
            'categories' => ['messages' => $T('Messages', 'Messages'), 'famille' => $T('Famille', 'Family'), 'vie' => $T('Vie chrétienne', 'Christian life')],
            'posts' => [
                ['key' => 'esperance', 'slug' => $u('marcher-dans-l-esperance', 'walking-in-hope'), 'category' => 'messages', 'tags' => [$T('Espérance', 'Hope')],
                    'title' => $T('Marcher dans l’espérance', 'Walking in hope'),
                    'excerpt' => $T('Quatre messages pour garder confiance dans les saisons difficiles.', 'Four messages to keep trust through difficult seasons.'),
                    'photo' => ['eglise-serie-1', 'book-open', $T('Chemin vers la lumière', 'Path toward the light')],
                    'quote' => $T('« L’espérance ne trompe point. » Romains 5.5', '“Hope does not put us to shame.” Romans 5:5'),
                    'body' => [
                        $T('Cette série de quatre messages nous invite à garder confiance quand les circonstances sont difficiles : maladie, chômage, deuil ou incertitude.', 'This four-message series invites us to keep trusting when circumstances are hard: illness, unemployment, grief or uncertainty.'),
                        $T('Chaque dimanche, un passage de la Bible et un témoignage de la communauté. Les messages sont disponibles à l’accueil après chaque culte.', 'Each Sunday, a Bible passage and a testimony from the community. The messages are available at the welcome desk after each service.'),
                        $T('Prolongez la réflexion en semaine dans l’un de nos groupes de maison.', 'Carry the reflection through the week in one of our home groups.'),
                    ]],
                ['key' => 'famille', 'slug' => $u('la-famille-selon-dieu', 'family-as-god-intended'), 'category' => 'famille', 'tags' => [$T('Couples', 'Couples')],
                    'title' => $T('La famille selon Dieu', 'Family as God intended'),
                    'excerpt' => $T('Couples, parents et enfants : bâtir un foyer d’amour et de paix.', 'Couples, parents and children: building a home of love and peace.'),
                    'photo' => ['eglise-serie-2', 'heart-handshake', $T('Famille réunie', 'Family together')],
                    'heading' => $T('Pour aller plus loin', 'Going further'),
                    'body' => [
                        $T('Comment s’écouter dans le couple ? Comment transmettre la foi aux enfants ? Cette série aborde avec simplicité les questions de la vie de famille.', 'How do we listen to each other as a couple? How do we pass faith on to our children? This series addresses family life questions simply.'),
                        $T('Le ministère des couples propose un parcours de six rencontres, et le ministère des enfants accueille les plus jeunes pendant les cultes.', 'The couples ministry offers a six-session course, and the children’s ministry welcomes the youngest during services.'),
                        $T('Pour participer, inscrivez-vous à l’accueil ou contactez-nous.', 'To take part, sign up at the welcome desk or contact us.'),
                    ]],
                ['key' => 'racines', 'slug' => $u('des-racines-profondes', 'deep-roots'), 'category' => 'vie', 'tags' => [$T('Prière', 'Prayer')],
                    'title' => $T('Des racines profondes', 'Deep roots'),
                    'excerpt' => $T('Prière, lecture de la Bible et vie de communauté pour grandir durablement.', 'Prayer, Bible reading and community life for lasting growth.'),
                    'photo' => ['eglise-serie-3', 'sprout', $T('Racines d’un arbre', 'Tree roots')],
                    'heading' => $T('Trois habitudes simples', 'Three simple habits'),
                    'body' => [
                        $T('Un arbre résiste à la tempête grâce à ses racines. Notre foi aussi a besoin de racines solides pour traverser les épreuves.', 'A tree withstands the storm thanks to its roots. Our faith also needs strong roots to get through hardship.'),
                        $T('Un temps de prière chaque jour, une lecture régulière de la Bible et la fidélité à la communauté : trois habitudes à cultiver ensemble.', 'A daily time of prayer, regular Bible reading and faithfulness to the community: three habits to grow together.'),
                        $T('Un plan de lecture est disponible à l’accueil pour vous accompagner pendant trois mois.', 'A reading plan is available at the welcome desk to guide you for three months.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Notre église', 'Our church'), 'url' => $about],
                    ['label' => $T('Cultes', 'Services'), 'url' => $services],
                    ['label' => $T('Ministères', 'Ministries'), 'url' => $ministries],
                    ['label' => $T('Événements', 'Events'), 'url' => $events],
                    ['label' => $T('Enseignements', 'Teachings'), 'url' => $teachings],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Donner', 'Give'),
                'cta_url' => $giving,
                'footer_text' => $T('Une communauté de foi ouverte à tous, pour prier, grandir et servir ensemble.', 'A faith community open to all, to pray, grow and serve together.'),
                'footer_links' => [
                    ['label' => $T('Horaires des cultes', 'Service times'), 'url' => $services],
                    ['label' => $T('Ministères', 'Ministries'), 'url' => $ministries],
                    ['label' => $T('Donner', 'Give'), 'url' => $giving],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
