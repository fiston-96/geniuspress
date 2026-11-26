<?php

/*
 * Pack « Club et association sportive » (secteur Sport et culture) : 8 pages,
 * en français et en anglais. Club amateur d'un sport collectif ou club
 * omnisports : équipes et catégories, entraînements, inscriptions et
 * cotisations, calendrier et résultats, partenaires et bénévoles.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation du club). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Sport et culture', 'en' => 'Sports and culture'],
        'icon' => 'trophy',
        'keywords' => ['club', 'club sportif', 'association sportive', 'sport', 'football', 'basket', 'handball', 'rugby', 'volley', 'équipe', 'licence', 'cotisation', 'match', 'sports club', 'team', 'league', 'soccer', 'basketball', 'youth sports', 'membership', 'fixtures'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $club = $u('/le-club', '/the-club');
        $teams = $u('/equipes', '/teams');
        $join = $u('/inscriptions', '/join');
        $fixtures = $u('/calendrier-resultats', '/fixtures-results');
        $news = $u('/actualites', '/news');
        $support = $u('/partenaires-benevoles', '/sponsors-volunteers');
        $contact = '/contact';
        $cur = $T('€', '$');
        $post = fn (string $fr, string $en) => $u('/actualites/', '/news/').$u($fr, $en);

        $categories = [
            ['baby', $T('École de sport (5 – 8 ans)', 'Kids’ academy (ages 5–8)'), $T('Jeux, motricité et premiers gestes techniques, le mercredi et le samedi matin.', 'Games, coordination and first skills, on Wednesday and Saturday mornings.')],
            ['backpack', $T('Jeunes (9 – 14 ans)', 'Youth (ages 9–14)'), $T('Apprentissage du jeu collectif et premiers championnats départementaux.', 'Learning team play and first district leagues.')],
            ['flame', $T('Ados (15 – 18 ans)', 'Teens (ages 15–18)'), $T('Trois entraînements par semaine et compétitions régionales.', 'Three practices a week and regional competitions.')],
            ['trophy', $T('Seniors', 'Seniors'), $T('Équipe première et équipe réserve engagées en championnat.', 'First team and reserve team playing in league competition.')],
            ['users-round', $T('Féminines', 'Women’s teams'), $T('Des équipes féminines dans chaque catégorie, des jeunes aux seniors.', 'Girls’ and women’s teams in every age group.')],
            ['heart-pulse', $T('Loisir et vétérans', 'Recreational and masters'), $T('Pour jouer sans pression, garder la forme et partager un moment.', 'To play without pressure, stay fit and enjoy time together.')],
        ];
        $cta = ['title' => $T('Envie de rejoindre le club ?', 'Want to join the club?'), 'text' => $T('Deux séances d’essai gratuites, quel que soit votre âge ou votre niveau. Venez avec une tenue de sport et une bouteille d’eau !', 'Two free trial sessions, whatever your age or level. Just bring sportswear and a water bottle!'),
            'buttons' => [[$T('S’inscrire', 'Sign up'), $join, 'accent'], [$T('Voir les équipes', 'See the teams'), $teams, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('[Nom du club] : équipes de 5 ans aux vétérans, entraînements, inscriptions, calendrier des matchs et résultats. Rejoignez-nous !', '[Club name]: teams from age 5 to masters, practice times, registration, fixtures and results. Come and join us!'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('[Nom du club] · depuis [année]', '[Club name] · since [year]'),
                    'title' => $T('Un club, une famille, la même passion', 'One club, one family, one passion'),
                    'text' => $T('Plus de 400 licenciés, de l’école de sport aux vétérans, filles et garçons. Ici, on progresse, on gagne parfois, et on apprend toujours à jouer ensemble.', 'Over 400 members, from the kids’ academy to the masters, girls and boys. Here we improve, we sometimes win, and we always learn to play together.'),
                    'buttons' => [[$T('S’inscrire', 'Sign up'), $join], [$T('Prochains matchs', 'Upcoming games'), $fixtures]],
                    'photo' => ['club-sport-accueil', 'trophy', $T('Équipe de jeunes joueurs célébrant ensemble sur un terrain de sport', 'Youth team celebrating together on a sports field')],
                ]),
                M::ticker($theme, ['label' => $T('Résultats', 'Results'), 'items' => [
                    [$T('Seniors : victoire 3 – 1 à domicile, l’équipe remonte à la 2ᵉ place', 'Seniors: 3–1 home win, the team climbs to 2nd place'), $post('victoire-seniors-derby', 'seniors-win-the-derby')],
                    [$T('Féminines U15 : qualifiées pour les demi-finales de la coupe régionale', 'Girls U15: through to the regional cup semi-finals'), $fixtures],
                    [$T('Samedi : tournoi de l’école de sport, 16 équipes attendues', 'Saturday: kids’ academy tournament, 16 teams expected'), $fixtures],
                    [$T('Inscriptions de la saison ouvertes : deux séances d’essai offertes', 'Season registration is open: two free trial sessions'), $join],
                ]]),
                B::features($theme, ['eyebrow' => $T('Nos équipes', 'Our teams'), 'title' => $T('Une place pour chacun, à chaque âge', 'A place for everyone, at every age'), 'items' => $categories]),
                B::stats($theme, ['title' => $T('Le club en chiffres', 'The club in numbers'), 'items' => [
                    [420, '', $T('licenciés', 'members')],
                    [24, '', $T('équipes engagées', 'teams entered')],
                    [35, '', $T('éducateurs et entraîneurs', 'coaches and trainers')],
                    [60, '+', $T('bénévoles', 'volunteers')],
                ]]),
                B::events($theme, ['icon' => 'flag', 'eyebrow' => $T('Ce week-end', 'This weekend'), 'title' => $T('Prochains matchs', 'Upcoming games'), 'items' => [
                    [$T('Seniors · [Nom du club] – [Adversaire]', 'Seniors · [Club name] vs [Opponent]'), $T('Samedi, 18 h', 'Saturday, 6 pm'), $T('Stade municipal, terrain d’honneur', 'Municipal stadium, main field')],
                    [$T('Féminines seniors · [Adversaire] – [Nom du club]', 'Women’s seniors · [Opponent] vs [Club name]'), $T('Dimanche, 15 h', 'Sunday, 3 pm'), $T('À l’extérieur', 'Away')],
                    [$T('U15 garçons · [Nom du club] – [Adversaire]', 'Boys U15 · [Club name] vs [Opponent]'), $T('Samedi, 14 h', 'Saturday, 2 pm'), $T('Complexe sportif, terrain 2', 'Sports complex, field 2')],
                    [$T('Tournoi de l’école de sport', 'Kids’ academy tournament'), $T('Samedi, 9 h – 13 h', 'Saturday, 9 am – 1 pm'), $T('Complexe sportif', 'Sports complex')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Notre projet', 'Our approach'),
                    'title' => $T('Former des joueurs, et des personnes', 'Developing players, and people'),
                    'html' => '<p>'.e($T('Nos éducateurs sont diplômés et formés chaque saison. Respect de l’arbitre, de l’adversaire et des coéquipiers, assiduité, entraide : ces valeurs comptent autant que le score.', 'Our coaches are qualified and trained every season. Respect for referees, opponents and teammates, commitment and mutual support matter as much as the score.')).'</p>',
                    'bullets' => [$T('Éducateurs diplômés dans chaque catégorie', 'Qualified coaches in every age group'), $T('Aide aux devoirs avant l’entraînement des jeunes', 'Homework help before youth practice'), $T('Charte du supporter signée par les parents', 'Supporter code of conduct signed by parents')],
                    'buttons' => [[$T('Découvrir le club', 'About the club'), $club, 'outline']],
                    'photo' => ['club-sport-entrainement', 'users', $T('Entraîneur donnant des consignes à un groupe d’enfants pendant un entraînement', 'Coach giving instructions to a group of children during practice')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::posts($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('La vie du club', 'Club news'), 'source' => 'latest', 'count' => 3, 'more' => $T('Lire la suite', 'Read more')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'club', 'slug' => $u('le-club', 'the-club'), 'title' => $T('Le club', 'The club'),
            'description' => $T('Histoire du club depuis sa création, valeurs, projet sportif et éducatif pour les jeunes, et bénévoles qui composent le bureau de l’association.', 'The club’s history since it was founded, our values, our sports and youth development program, and the volunteers on the association’s board.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Le club', 'The club'), 'title' => $T('Plus qu’un club, un lieu de vie', 'More than a club, a community'), 'intro' => $T('Une association à but non lucratif, gérée par des bénévoles et ouverte à tous.', 'A non-profit association, run by volunteers and open to everyone.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Fondé par une bande d’amis, porté par tout un quartier', 'Founded by a group of friends, carried by a whole neighborhood'),
                    'html' => '<p>'.e($T('Le club est né en [année] sur un terrain vague, à l’initiative de quelques passionnés. Il compte aujourd’hui vingt-quatre équipes, une école de sport labellisée et une section féminine en pleine croissance.', 'The club was born in [year] on a vacant lot, started by a few enthusiasts. Today it has twenty-four teams, an accredited kids’ academy and a fast-growing women’s section.')).'</p><p>'.e($T('Association déclarée sous le numéro [N° d’enregistrement], affiliée à la fédération [Nom de la fédération].', 'Association registered under number [Registration no.], affiliated with [Federation name].')).'</p>',
                    'photo' => ['club-sport-histoire', 'history', $T('Photo de groupe d’un club sportif amateur posant devant les tribunes', 'Group photo of an amateur sports club posing in front of the stands')],
                ]),
                M::timeline($theme, ['eyebrow' => $T('Repères', 'Milestones'), 'title' => $T('Les grandes dates du club', 'Key dates in the club’s history'), 'bg' => 'light', 'items' => [
                    ['[année]', $T('Création du club', 'Club founded'), $T('Une seule équipe, des maillots prêtés et beaucoup d’enthousiasme.', 'One team, borrowed jerseys and plenty of enthusiasm.')],
                    ['[année]', $T('Ouverture de l’école de sport', 'Kids’ academy opens'), $T('Les premiers enfants du quartier rejoignent le club.', 'The first local children join the club.')],
                    ['[année]', $T('Première montée en division supérieure', 'First promotion'), $T('L’équipe première accède au niveau régional.', 'The first team reaches the regional level.')],
                    ['[année]', $T('Création de la section féminine', 'Women’s section launched'), $T('Aujourd’hui plus de cent joueuses licenciées.', 'Now more than a hundred registered players.')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce que nous défendons', 'What we stand for'), 'items' => [
                    ['handshake', $T('Respect', 'Respect'), $T('Des arbitres, des adversaires, des coéquipiers et des installations.', 'For referees, opponents, teammates and facilities.')],
                    ['users', $T('Solidarité', 'Solidarity'), $T('Une cotisation adaptée aux moyens de chaque famille.', 'Fees adapted to what every family can afford.')],
                    ['target', $T('Exigence', 'Commitment'), $T('Progresser à l’entraînement pour mieux se faire plaisir en match.', 'Working hard in practice to enjoy the game more.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('Le bureau', 'The board'), 'title' => $T('Les bénévoles qui font vivre le club', 'The volunteers who run the club'), 'bg' => 'light', 'items' => [
                    ['[Prénom Nom]', $T('Président', 'President'), 'club-sport-equipe-1'],
                    ['[Prénom Nom]', $T('Secrétaire générale', 'Secretary'), 'club-sport-equipe-2'],
                    ['[Prénom Nom]', $T('Trésorier', 'Treasurer'), 'club-sport-equipe-3'],
                    ['[Prénom Nom]', $T('Directrice sportive', 'Sporting director'), 'club-sport-equipe-4'],
                ]]),
            ]];

        $pages[] = ['key' => 'teams', 'slug' => $u('equipes', 'teams'), 'title' => $T('Équipes et entraînements', 'Teams and practice times'),
            'description' => $T('Toutes les équipes du club par catégorie, horaires des entraînements et lieux : stade municipal et complexe sportif.', 'All club teams by age group, practice times and venues: municipal stadium and sports complex.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Équipes', 'Teams'), 'title' => $T('Nos équipes et leurs entraînements', 'Our teams and practice times'), 'intro' => $T('Vingt-quatre équipes encadrées par des éducateurs diplômés.', 'Twenty-four teams led by qualified coaches.')]),
                B::cards($theme, ['items' => [
                    ['club-sport-categorie-ecole', 'baby', $T('École de sport', 'Kids’ academy'), $T('Des ateliers ludiques pour découvrir le sport et le plaisir de jouer ensemble.', 'Fun drills to discover the sport and the joy of playing together.')],
                    ['club-sport-categorie-jeunes', 'backpack', $T('Jeunes et ados', 'Youth and teens'), $T('Des équipes par tranche d’âge, engagées en championnat chaque week-end.', 'Teams by age group, playing league games every weekend.')],
                    ['club-sport-categorie-seniors', 'trophy', $T('Équipe première', 'First team'), $T('Le fer de lance du club, en championnat régional.', 'The club’s flagship side, in the regional league.')],
                    ['club-sport-categorie-feminines', 'users-round', $T('Section féminine', 'Women’s section'), $T('Des équipes féminines de 9 ans aux seniors, en pleine progression.', 'Girls’ and women’s teams from age 9 to seniors, on the rise.')],
                    ['club-sport-categorie-loisir', 'heart-pulse', $T('Loisir et vétérans', 'Recreational and masters'), $T('Pour jouer chaque semaine dans une ambiance conviviale.', 'To play every week in a friendly atmosphere.')],
                    ['club-sport-categorie-handisport', 'accessibility', $T('Sport partagé', 'Inclusive sport'), $T('Des séances adaptées, ouvertes aux personnes en situation de handicap.', 'Adapted sessions, open to people with disabilities.')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Entraînements', 'Practice'), 'title' => $T('Horaires des entraînements', 'Practice schedule'), 'bg' => 'light', 'note' => $T('Horaires de la saison en cours, hors vacances scolaires.', 'Current season schedule, outside school holidays.'), 'rows' => [
                    [$T('Catégorie', 'Age group'), $T('Jours', 'Days'), $T('Horaires', 'Times'), $T('Lieu', 'Venue')],
                    [$T('École de sport', 'Kids’ academy'), $T('Mercredi et samedi', 'Wednesday and Saturday'), '10:00 – 11:30', $T('Complexe sportif', 'Sports complex')],
                    [$T('Jeunes (9 – 14 ans)', 'Youth (9–14)'), $T('Mardi et jeudi', 'Tuesday and Thursday'), '17:30 – 19:00', $T('Complexe sportif', 'Sports complex')],
                    [$T('Ados (15 – 18 ans)', 'Teens (15–18)'), $T('Lundi, mercredi, vendredi', 'Monday, Wednesday, Friday'), '18:00 – 19:45', $T('Stade municipal', 'Municipal stadium')],
                    [$T('Seniors', 'Seniors'), $T('Mardi, jeudi, vendredi', 'Tuesday, Thursday, Friday'), '19:30 – 21:30', $T('Stade municipal', 'Municipal stadium')],
                    [$T('Féminines seniors', 'Women’s seniors'), $T('Lundi et mercredi', 'Monday and Wednesday'), '19:30 – 21:00', $T('Complexe sportif', 'Sports complex')],
                    [$T('Loisir et vétérans', 'Recreational and masters'), $T('Dimanche', 'Sunday'), '09:30 – 11:30', $T('Stade municipal', 'Municipal stadium')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Nos installations', 'Our facilities'), 'title' => $T('Où nous trouver', 'Where we train'), 'bg' => 'white', 'groups' => [
                    [$T('Stade municipal', 'Municipal stadium'), [
                        ['map-pin', $T('[Numéro et rue], [Ville]', '[Street address], [City]')],
                        ['flag', $T('Terrain d’honneur et terrain synthétique éclairé', 'Main field and floodlit artificial pitch')],
                        ['house', $T('Vestiaires, buvette et club-house', 'Changing rooms, snack bar and clubhouse')],
                    ]],
                    [$T('Complexe sportif', 'Sports complex'), [
                        ['map-pin', $T('[Numéro et rue], [Ville]', '[Street address], [City]')],
                        ['building', $T('Deux terrains et une salle couverte', 'Two fields and an indoor hall')],
                        ['bus', $T('Accessible en bus, parking à vélos', 'Reachable by bus, bike racks')],
                    ]],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'join', 'slug' => $u('inscriptions', 'join'), 'title' => $T('Inscriptions et cotisations', 'Registration and fees'),
            'description' => $T('S’inscrire au club : séances d’essai, cotisations par catégorie, pièces à fournir, aides et paiement en plusieurs fois.', 'Join the club: trial sessions, fees by age group, required documents, financial help and installment plans.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Inscriptions', 'Registration'), 'title' => $T('Rejoindre le club en quatre étapes', 'Join the club in four steps'), 'intro' => $T('Les inscriptions sont ouvertes toute l’année, dans la limite des places disponibles par catégorie.', 'Registration is open all year round, subject to places available in each age group.')]),
                B::steps($theme, ['items' => [
                    [$T('Séances d’essai', 'Trial sessions'), $T('Deux entraînements gratuits pour découvrir le groupe et l’éducateur.', 'Two free practices to meet the group and the coach.')],
                    [$T('Dossier', 'Paperwork'), $T('Formulaire, certificat médical et photo d’identité.', 'Form, medical certificate and ID photo.')],
                    [$T('Cotisation', 'Fees'), $T('Paiement en une ou trois fois, aides déduites.', 'Payment in one or three installments, after any discounts.')],
                    [$T('Licence', 'License'), $T('Licence fédérale enregistrée et équipement remis.', 'Federation license registered and kit handed out.')],
                ]]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Cotisations', 'Fees'), 'title' => $T('Cotisations de la saison', 'Season fees'), 'intro' => $T('Licence fédérale, assurance et équipement compris. Réduction de 20 % dès le deuxième enfant de la même famille.', 'Federation license, insurance and kit included. 20% off from the second child in the same family.'), 'items' => [
                    [$T('Jeunes', 'Youth'), '150', $cur, $T('/ saison', '/ season'), $T('De l’école de sport à 14 ans.', 'From the kids’ academy up to age 14.'), [$T('Deux entraînements par semaine', 'Two practices a week'), $T('Matchs et tournois', 'Games and tournaments'), $T('Maillot, short et chaussettes', 'Jersey, shorts and socks'), $T('Stage pendant les vacances en option', 'Holiday camp as an option')], $T('Inscrire mon enfant', 'Register my child')],
                    [$T('Ados et seniors', 'Teens and seniors'), '210', $cur, $T('/ saison', '/ season'), $T('Compétition, de 15 ans aux seniors.', 'Competitive play, from age 15 to seniors.'), [$T('Trois entraînements par semaine', 'Three practices a week'), $T('Championnat et coupes', 'League and cup games'), $T('Survêtement du club', 'Club tracksuit'), $T('Préparation physique', 'Strength and conditioning')], $T('M’inscrire', 'Sign up'), true, $T('Compétition', 'Competitive')],
                    [$T('Loisir', 'Recreational'), '120', $cur, $T('/ saison', '/ season'), $T('Jouer pour le plaisir, sans championnat.', 'Play for fun, no league games.'), [$T('Une séance par semaine', 'One session a week'), $T('Tournois amicaux', 'Friendly tournaments'), $T('Accès au club-house', 'Clubhouse access'), '-'.$T('Équipement de match', 'Match kit')], $T('M’inscrire', 'Sign up')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Le dossier', 'Your file'), 'title' => $T('Pièces à fournir et aides', 'Documents and financial help'), 'bg' => 'white', 'groups' => [
                    [$T('Pièces à fournir', 'Documents to provide'), [
                        ['file-text', $T('Formulaire d’inscription signé', 'Signed registration form')],
                        ['stethoscope', $T('Certificat médical ou questionnaire de santé', 'Medical certificate or health questionnaire')],
                        ['camera', $T('Une photo d’identité récente', 'A recent ID photo')],
                        ['id-card', $T('Pièce d’identité (pour les nouveaux licenciés)', 'ID document (for new members)')],
                    ]],
                    [$T('Aides et paiement', 'Help and payment'), [
                        ['credit-card', $T('Carte bancaire, virement ou paiement mobile', 'Card, bank transfer or mobile payment')],
                        ['calendar-days', $T('Paiement possible en trois fois sans frais', 'Three interest-free installments available')],
                        ['hand-coins', $T('Aides municipales et chèques sport acceptés', 'City grants and sports vouchers accepted')],
                        ['heart-handshake', $T('Fonds de solidarité du club sur demande', 'Club hardship fund on request')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions des parents et des joueurs', 'Questions from parents and players'), 'bg' => 'light', 'items' => [
                    [$T('Mon enfant n’a jamais pratiqué, peut-il s’inscrire ?', 'My child has never played. Can they join?'), $T('Bien sûr. Les groupes sont organisés par âge et par niveau, et les éducateurs accueillent les débutants toute l’année.', 'Of course. Groups are organized by age and level, and coaches welcome beginners all year round.')],
                    [$T('Les parents doivent-ils accompagner aux matchs ?', 'Do parents have to drive to away games?'), $T('Un planning de covoiturage est organisé chaque mois. Nous comptons sur chaque famille, à tour de rôle.', 'A monthly carpool schedule is organized. We count on each family, taking turns.')],
                    [$T('Que se passe-t-il en cas de blessure ?', 'What happens if a player gets injured?'), $T('La licence comprend une assurance. Prévenez l’éducateur et le secrétariat pour la déclaration.', 'The license includes insurance. Let the coach and the office know so we can file a claim.')],
                    [$T('Peut-on s’inscrire en cours de saison ?', 'Can you join mid-season?'), $T('Oui, avec une cotisation calculée au prorata des mois restants.', 'Yes, with fees prorated for the remaining months.')],
                ]]),
            ]];

        $pages[] = ['key' => 'fixtures', 'slug' => $u('calendrier-resultats', 'fixtures-results'), 'title' => $T('Calendrier et résultats', 'Fixtures and results'),
            'description' => $T('Calendrier des prochains matchs à domicile et à l’extérieur, derniers résultats et bilan de la saison de chaque équipe.', 'Upcoming home and away games, latest results and season record for each team.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Calendrier', 'Fixtures'), 'title' => $T('Matchs et résultats', 'Games and results'), 'intro' => $T('Venez encourager nos équipes : l’entrée est gratuite pour tous les matchs à domicile.', 'Come and cheer our teams on: entry is free for every home game.')]),
                M::table($theme, ['eyebrow' => $T('À venir', 'Coming up'), 'title' => $T('Prochains matchs', 'Upcoming games'), 'rows' => [
                    [$T('Date', 'Date'), $T('Équipe', 'Team'), $T('Rencontre', 'Game'), $T('Lieu', 'Venue')],
                    [$T('Sam. 18 h', 'Sat 6 pm'), $T('Seniors', 'Seniors'), $T('[Nom du club] – [Adversaire]', '[Club name] vs [Opponent]'), $T('Domicile', 'Home')],
                    [$T('Dim. 15 h', 'Sun 3 pm'), $T('Féminines seniors', 'Women’s seniors'), $T('[Adversaire] – [Nom du club]', '[Opponent] vs [Club name]'), $T('Extérieur', 'Away')],
                    [$T('Sam. 14 h', 'Sat 2 pm'), 'U15', $T('[Nom du club] – [Adversaire]', '[Club name] vs [Opponent]'), $T('Domicile', 'Home')],
                    [$T('Sam. 10 h', 'Sat 10 am'), 'U13', $T('[Adversaire] – [Nom du club]', '[Opponent] vs [Club name]'), $T('Extérieur', 'Away')],
                    [$T('Mer. 19 h', 'Wed 7 pm'), $T('Seniors', 'Seniors'), $T('Coupe : [Nom du club] – [Adversaire]', 'Cup: [Club name] vs [Opponent]'), $T('Domicile', 'Home')],
                ]]),
                B::stats($theme, ['title' => $T('Équipe première : la saison en cours', 'First team: this season so far'), 'items' => [
                    [11, '', $T('victoires', 'wins')],
                    [4, '', $T('nuls', 'draws')],
                    [3, '', $T('défaites', 'losses')],
                    [2, $T('ᵉ', 'nd'), $T('au classement', 'in the standings')],
                ]]),
                M::table($theme, ['eyebrow' => $T('Derniers résultats', 'Latest results'), 'title' => $T('Le week-end dernier', 'Last weekend'), 'bg' => 'light', 'rows' => [
                    [$T('Équipe', 'Team'), $T('Rencontre', 'Game'), $T('Score', 'Score')],
                    [$T('Seniors', 'Seniors'), $T('[Nom du club] – [Adversaire]', '[Club name] vs [Opponent]'), '3 – 1'],
                    [$T('Féminines U15', 'Girls U15'), $T('[Nom du club] – [Adversaire]', '[Club name] vs [Opponent]'), '2 – 0'],
                    ['U17', $T('[Adversaire] – [Nom du club]', '[Opponent] vs [Club name]'), '1 – 1'],
                    [$T('Vétérans', 'Masters'), $T('[Adversaire] – [Nom du club]', '[Opponent] vs [Club name]'), '2 – 3'],
                ]]),
                B::cta($theme, ['title' => $T('Venez nous encourager !', 'Come and support us!'), 'text' => $T('Entrée libre à domicile, buvette tenue par les bénévoles : chaque supporter compte.', 'Free entry at home games, snack bar run by volunteers: every supporter counts.'),
                    'buttons' => [[$T('Devenir bénévole', 'Volunteer'), $support, 'accent'], [$T('Actualités du club', 'Club news'), $news, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'news', 'slug' => $u('actualites', 'news'), 'title' => $T('Actualités', 'News'),
            'description' => $T('Comptes rendus de matchs, vie du club, stages de vacances, tournois de jeunes et événements : toute l’actualité de [Nom du club], en mots et en images.', 'Game reports, club life, holiday camps, youth tournaments and events: all the news from [Club name], in words and pictures.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Actualités', 'News'), 'title' => $T('Toute l’actualité du club', 'All the club news'), 'intro' => $T('Matchs, stages, tournois et moments de fête.', 'Games, camps, tournaments and celebrations.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('Les plus beaux moments de la saison', 'Highlights of the season'), 'bg' => 'light', 'photos' => [
                    ['club-sport-galerie-1', 'trophy', $T('Joueuses levant une coupe après une finale', 'Players lifting a trophy after a final')],
                    ['club-sport-galerie-2', 'users', $T('Supporters encourageant leur équipe dans une tribune', 'Supporters cheering their team in the stands')],
                    ['club-sport-galerie-3', 'baby', $T('Enfants jouant lors d’un tournoi de jeunes', 'Children playing at a youth tournament')],
                    ['club-sport-galerie-4', 'flag', $T('Action de jeu pendant un match amateur', 'Game action during an amateur match')],
                    ['club-sport-galerie-5', 'medal', $T('Remise de médailles aux jeunes joueurs', 'Medal ceremony for young players')],
                    ['club-sport-galerie-6', 'heart-handshake', $T('Bénévoles servant à la buvette du club', 'Volunteers serving at the club snack bar')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'support', 'slug' => $u('partenaires-benevoles', 'sponsors-volunteers'), 'title' => $T('Partenaires et bénévoles', 'Sponsors and volunteers'),
            'description' => $T('Devenez partenaire du club : visibilité locale, packs de sponsoring, mécénat. Rejoignez aussi l’équipe des bénévoles.', 'Sponsor the club: local visibility, sponsorship packages, donations. You can also join our team of volunteers.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nous soutenir', 'Support us'), 'title' => $T('Faites équipe avec nous', 'Team up with us'), 'intro' => $T('Entreprises, commerçants et bénévoles : le club vit grâce à vous.', 'Businesses, local shops and volunteers: the club lives thanks to you.')]),
                B::pillars($theme, ['eyebrow' => $T('Pourquoi nous soutenir', 'Why support us'), 'title' => $T('Un partenariat qui a du sens', 'A partnership that matters'), 'items' => [
                    ['eye', $T('Visibilité locale', 'Local visibility'), $T('Plus de 3 000 spectateurs par saison et une communauté active en ligne.', 'Over 3,000 spectators a season and an active online community.')],
                    ['sprout', $T('Impact éducatif', 'Youth impact'), $T('Votre soutien finance l’école de sport et les équipements des jeunes.', 'Your support funds the kids’ academy and youth equipment.')],
                    ['handshake', $T('Réseau', 'Network'), $T('Des rencontres régulières entre partenaires au club-house.', 'Regular partner get-togethers at the clubhouse.')],
                ]]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Partenariats', 'Sponsorship'), 'title' => $T('Nos packs partenaires', 'Our sponsorship packages'), 'bg' => 'light', 'items' => [
                    [$T('Supporter', 'Supporter'), '300', $cur, $T('/ saison', '/ season'), $T('Pour les commerces de proximité.', 'For neighborhood businesses.'), [$T('Logo sur le site du club', 'Logo on the club website'), $T('Mention sur nos réseaux', 'Shout-out on our social media'), $T('Deux invitations aux matchs de gala', 'Two invitations to showcase games'), '-'.$T('Panneau au stade', 'Stadium board')], $T('Devenir partenaire', 'Become a sponsor')],
                    [$T('Partenaire', 'Partner'), '1 200', $cur, $T('/ saison', '/ season'), $T('La formule la plus visible au stade.', 'The most visible package at the stadium.'), [$T('Panneau publicitaire au bord du terrain', 'Pitch-side advertising board'), $T('Logo sur les maillots d’entraînement', 'Logo on training tops'), $T('Publication dédiée chaque mois', 'Dedicated monthly post'), $T('Soirée des partenaires', 'Partners’ evening')], $T('Devenir partenaire', 'Become a sponsor'), true, $T('Le plus choisi', 'Most popular')],
                    [$T('Partenaire majeur', 'Main sponsor'), '4 000', $cur, $T('/ saison', '/ season'), $T('Associez votre nom à une équipe.', 'Put your name on a team.'), [$T('Logo sur les maillots de match', 'Logo on match jerseys'), $T('Nom donné à un tournoi', 'Naming rights for a tournament'), $T('Espace réceptif les jours de match', 'Hospitality area on game days'), $T('Bilan de visibilité de fin de saison', 'End-of-season visibility report')], $T('Nous contacter', 'Contact us')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Bénévolat', 'Volunteering'),
                    'title' => $T('Donnez un peu de temps, recevez beaucoup', 'Give a little time, get a lot back'),
                    'html' => '<p>'.e($T('Arbitrer un match de jeunes, tenir la buvette, conduire une équipe, gérer le site ou les réseaux, aider à l’organisation d’un tournoi : chaque compétence est utile, même deux heures par mois.', 'Refereeing a youth game, running the snack bar, driving a team, managing the website or social media, helping organize a tournament: every skill helps, even two hours a month.')).'</p>',
                    'bullets' => [$T('Formations d’arbitre et d’éducateur prises en charge', 'Referee and coaching courses paid for'), $T('Un référent bénévoles pour vous accueillir', 'A volunteer coordinator to welcome you'), $T('Une vraie ambiance d’équipe', 'A real team spirit')],
                    'buttons' => [[$T('Devenir bénévole', 'Become a volunteer'), $contact]],
                    'photo' => ['club-sport-benevoles', 'hand-helping', $T('Bénévoles installant des équipements avant un tournoi', 'Volunteers setting up equipment before a tournament')],
                    'reverse' => true,
                ]),
                B::cta($theme, ['tone' => 'dark', 'title' => $T('Parlons de votre partenariat', 'Let’s talk about sponsorship'), 'text' => $T('Le président et le responsable partenariats vous présentent le club et ses projets.', 'The president and our sponsorship lead will present the club and its plans.'),
                    'buttons' => [[$T('Prendre contact', 'Get in touch'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez le secrétariat du club : inscriptions, séances d’essai, partenariats, bénévolat. Horaires et adresses des installations.', 'Contact the club office: registration, trial sessions, sponsorship, volunteering. Opening hours and venue addresses.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Contacter le club', 'Contact the club'), 'intro' => $T('Le secrétariat vous répond, et les éducateurs vous accueillent au bord du terrain.', 'The office will reply, and the coaches will welcome you at the field.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['user-plus', $T('Inscriptions', 'Registration'), $T('Séance d’essai, dossier, cotisation : le secrétariat vous guide.', 'Trial session, paperwork, fees: the office will guide you.'), $join, $T('S’inscrire', 'Sign up')],
                    ['handshake', $T('Partenariats', 'Sponsorship'), $T('Présentez votre entreprise au responsable partenariats.', 'Introduce your business to our sponsorship lead.'), $support, $T('Nos packs', 'Our packages')],
                    ['hand-helping', $T('Bénévolat', 'Volunteering'), $T('Proposez votre aide, même ponctuelle : arbitrage, buvette, transport.', 'Offer your help, even occasionally: refereeing, snack bar, driving.'), $support, $T('Devenir bénévole', 'Volunteer')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Secrétariat du club', 'Club office'), 'intro' => $T('Pour une séance d’essai, précisez l’âge du joueur ou de la joueuse et son expérience.', 'For a trial session, tell us the player’s age and experience.'),
                    'items' => [
                        ['map-pin', $T('Club-house : [Numéro et rue], [Ville], [Pays]', 'Clubhouse: [Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Mardi et jeudi, 17 h – 20 h ; samedi, 9 h – 12 h', 'Tuesday and Thursday, 5 – 8 pm; Saturday, 9 am – 12 pm')],
                    ],
                    'social' => ['facebook', 'instagram', 'youtube'],
                    'subject' => true,
                    'bg' => 'white',
                ]),
            ]];

        $blog = [
            'base' => $u('actualites', 'news'),
            'title' => $T('Actualités', 'News'),
            'categories' => ['matchs' => $T('Matchs', 'Games'), 'vie-du-club' => $T('Vie du club', 'Club life'), 'jeunes' => $T('Jeunes', 'Youth')],
            'posts' => [
                ['key' => 'derby', 'slug' => $u('victoire-seniors-derby', 'seniors-win-the-derby'), 'category' => 'matchs', 'tags' => [$T('Seniors', 'Seniors')],
                    'title' => $T('Les seniors remportent le derby 3 – 1', 'Seniors win the derby 3–1'),
                    'excerpt' => $T('Une victoire construite en seconde période, devant plus de 400 supporters.', 'A win built in the second half, in front of more than 400 supporters.'),
                    'photo' => ['club-sport-blog-derby', 'trophy', $T('Joueurs amateurs se congratulant après un but', 'Amateur players congratulating each other after a goal')],
                    'heading' => $T('Le film du match', 'How the game unfolded'),
                    'body' => [
                        $T('Menés à la pause, nos joueurs ont réagi dès la reprise avec deux buts en dix minutes, avant de sceller la victoire en fin de match.', 'Trailing at half-time, our players hit back right after the break with two goals in ten minutes, before sealing the win late on.'),
                        $T('L’entraîneur a salué l’état d’esprit du groupe et le soutien du public, venu nombreux malgré la pluie.', 'The coach praised the team’s attitude and the support of the crowd, who turned out in numbers despite the rain.'),
                        $T('Prochain rendez-vous samedi à domicile : venez nombreux, l’entrée est libre.', 'Next up: a home game on Saturday. Come along, entry is free.'),
                    ]],
                ['key' => 'tournoi', 'slug' => $u('tournoi-ecole-de-sport', 'kids-academy-tournament'), 'category' => 'jeunes', 'tags' => [$T('École de sport', 'Kids’ academy')],
                    'title' => $T('Tournoi de l’école de sport : 16 équipes et beaucoup de sourires', 'Kids’ academy tournament: 16 teams and lots of smiles'),
                    'excerpt' => $T('Une matinée de jeu, de fair-play et de goûters partagés.', 'A morning of games, fair play and shared snacks.'),
                    'photo' => ['club-sport-blog-tournoi', 'baby', $T('Jeunes enfants en tenue de sport attendant leur match', 'Young children in sports kit waiting for their game')],
                    'quote' => $T('« À cet âge, le plus beau score, c’est le sourire des enfants à la fin du match. »', '“At this age, the best score is the smile on the kids’ faces at the end of the game.”'),
                    'body' => [
                        $T('Seize équipes de la région ont participé au tournoi organisé par nos éducateurs et une trentaine de bénévoles.', 'Sixteen teams from the area took part in the tournament organized by our coaches and about thirty volunteers.'),
                        $T('Pas de classement à cet âge : chaque enfant est reparti avec une médaille et un diplôme du fair-play.', 'No rankings at this age: every child went home with a medal and a fair-play certificate.'),
                        $T('Merci aux parents pour les gâteaux et à nos partenaires pour les lots.', 'Thanks to the parents for the cakes and to our sponsors for the prizes.'),
                    ]],
                ['key' => 'assemblee', 'slug' => $u('assemblee-generale', 'annual-general-meeting'), 'category' => 'vie-du-club', 'tags' => [$T('Association', 'Association')],
                    'title' => $T('Assemblée générale : bilan de la saison et projets', 'Annual general meeting: season review and plans'),
                    'excerpt' => $T('Comptes approuvés, nouveau bureau et projet de vestiaires rénovés.', 'Accounts approved, new board and a plan for renovated changing rooms.'),
                    'photo' => ['club-sport-blog-assemblee', 'users', $T('Membres d’une association réunis dans une salle pour une assemblée', 'Association members gathered in a room for a general meeting')],
                    'heading' => $T('Ce qu’il faut retenir', 'Key takeaways'),
                    'body' => [
                        $T('Les adhérents ont approuvé les comptes de la saison et élu un bureau renouvelé pour moitié.', 'Members approved the season’s accounts and elected a board with half of its seats renewed.'),
                        $T('La priorité de l’an prochain : la rénovation des vestiaires, en lien avec la municipalité, et la création d’une deuxième équipe féminine senior.', 'Next year’s priority: renovating the changing rooms with the city council, and starting a second women’s senior team.'),
                        $T('Le compte rendu complet est disponible auprès du secrétariat.', 'The full minutes are available from the club office.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Le club', 'The club'), 'url' => $club],
                    ['label' => $T('Équipes', 'Teams'), 'url' => $teams],
                    ['label' => $T('Inscriptions', 'Join'), 'url' => $join],
                    ['label' => $T('Calendrier', 'Fixtures'), 'url' => $fixtures],
                    ['label' => $T('Actualités', 'News'), 'url' => $news],
                    ['label' => $T('Partenaires', 'Sponsors'), 'url' => $support],
                ],
                'cta_label' => $T('S’inscrire', 'Sign up'),
                'cta_url' => $join,
                'footer_text' => $T('[Nom du club] : association sportive ouverte à tous, de l’école de sport aux vétérans. Respect, solidarité et passion du jeu.', '[Club name]: a sports association open to all, from the kids’ academy to the masters. Respect, solidarity and a love of the game.'),
                'footer_links' => [
                    ['label' => $T('Inscriptions', 'Join'), 'url' => $join],
                    ['label' => $T('Calendrier et résultats', 'Fixtures and results'), 'url' => $fixtures],
                    ['label' => $T('Devenir partenaire', 'Become a sponsor'), 'url' => $support],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
