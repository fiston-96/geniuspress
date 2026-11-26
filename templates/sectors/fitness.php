<?php

/*
 * Pack « Salle de sport et studio de fitness » (secteur Sport et culture) :
 * 8 pages, en français et en anglais. Salle de remise en forme, studio de
 * cours collectifs, cross-training, yoga ou pilates : cours, planning,
 * coachs, abonnements, essai gratuit, transformations, conseils.
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de la salle). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Sport et culture', 'en' => 'Sports and culture'],
        'icon' => 'trophy',
        'keywords' => ['salle de sport', 'fitness', 'musculation', 'remise en forme', 'cours collectifs', 'coach sportif', 'yoga', 'pilates', 'cross-training', 'abonnement', 'gym', 'fitness studio', 'personal trainer', 'group classes', 'workout', 'strength training', 'membership', 'wellness'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $classes = $u('/cours-collectifs', '/classes');
        $timetable = $u('/planning', '/timetable');
        $coaches = $u('/coachs', '/coaches');
        $plans = $u('/abonnements', '/memberships');
        $results = $u('/transformations', '/transformations');
        $tips = $u('/conseils', '/tips');
        $contact = '/contact';
        $trial = '/contact#contact';
        $cur = $T('€', '$');

        $plansItems = [
            [$T('Essentiel', 'Essential'), $T('29', '32'), $cur, $T('/ mois', '/ month'), $T('L’accès libre à la salle, à votre rythme.', 'Open gym access, at your own pace.'), [$T('Plateau musculation et cardio', 'Weights and cardio floor'), $T('Accès 6 h – 23 h, 7 j/7', 'Access 6 am – 11 pm, 7 days a week'), $T('Programme de démarrage', 'Starter program'), '-'.$T('Cours collectifs', 'Group classes'), '-'.$T('Bilan avec un coach', 'Coach assessment')], $T('Choisir Essentiel', 'Choose Essential')],
            [$T('Illimité', 'Unlimited'), $T('45', '49'), $cur, $T('/ mois', '/ month'), $T('La salle et tous les cours collectifs.', 'The gym and every group class.'), [$T('Tout l’abonnement Essentiel', 'Everything in Essential'), $T('Plus de 60 cours par semaine', 'Over 60 classes a week'), $T('Réservation des cours dans l’appli', 'Class booking in the app'), $T('Un bilan forme par trimestre', 'A fitness assessment every quarter'), '-'.$T('Coaching individuel', 'Personal training')], $T('Choisir Illimité', 'Choose Unlimited'), true, $T('Le plus choisi', 'Most popular')],
            [$T('Premium', 'Premium'), $T('79', '85'), $cur, $T('/ mois', '/ month'), $T('Un accompagnement complet, toute l’année.', 'Full support, all year round.'), [$T('Tout l’abonnement Illimité', 'Everything in Unlimited'), $T('Deux séances de coaching par mois', 'Two personal training sessions a month'), $T('Programme et suivi nutritionnel', 'Program and nutrition follow-up'), $T('Serviette, casier et boisson offerts', 'Towel, locker and drink included'), $T('Accès invité un samedi par mois', 'Guest pass one Saturday a month')], $T('Choisir Premium', 'Choose Premium')],
        ];
        $testimonials = ['eyebrow' => $T('Avis des membres', 'Member reviews'), 'title' => $T('Ils ont trouvé leur rythme', 'They found their rhythm'), 'rating' => 5, 'items' => [
            [$T('Je n’avais jamais mis les pieds dans une salle. Le coach m’a fait un programme simple et je viens maintenant trois fois par semaine, sans me forcer.', 'I had never set foot in a gym. The coach gave me a simple program and now I come three times a week without having to push myself.'), '[Prénom Nom]', $T('Membre depuis 1 an', 'Member for 1 year')],
            [$T('Les cours du soir sont mon moment à moi. L’ambiance est motivante et personne ne juge personne.', 'Evening classes are my time for myself. The atmosphere is motivating and nobody judges anyone.'), '[Prénom Nom]', $T('Membre depuis 2 ans', 'Member for 2 years')],
            [$T('Après une blessure au dos, la coach a adapté chaque séance. J’ai repris le sport en confiance.', 'After a back injury, the coach adapted every session. I got back into exercise with confidence.'), '[Prénom Nom]', $T('Membre depuis 8 mois', 'Member for 8 months')],
        ]];
        $cta = ['title' => $T('Votre première séance est offerte', 'Your first session is on us'), 'text' => $T('Visite de la salle, bilan avec un coach et un cours collectif au choix. Sans engagement.', 'A tour of the gym, an assessment with a coach and a group class of your choice. No commitment.'),
            'buttons' => [[$T('Réserver mon essai gratuit', 'Book my free trial'), $trial, 'accent'], [$T('Voir les abonnements', 'See memberships'), $plans, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Salle de sport et studio de cours collectifs : musculation, cardio, cross-training, yoga et coaching. Essai gratuit, sans engagement.', 'Gym and group fitness studio: strength, cardio, cross-training, yoga and personal training. Free trial, no commitment.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Salle de sport et studio', 'Gym and studio'),
                    'title' => $T('Bougez plus, sentez-vous mieux', 'Move more, feel better'),
                    'text' => $T('Un plateau de musculation complet, plus de 60 cours collectifs par semaine et des coachs qui vous suivent vraiment, que vous débutiez ou que vous prépariez une compétition.', 'A full weights floor, over 60 group classes a week and coaches who really follow your progress, whether you are just starting out or training for a competition.'),
                    'buttons' => [[$T('Essai gratuit', 'Free trial'), $trial], [$T('Le planning des cours', 'Class timetable'), $timetable]],
                    'photo' => ['fitness-accueil', 'dumbbell', $T('Personnes s’entraînant avec des haltères dans une salle de sport lumineuse', 'People training with dumbbells in a bright gym')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos espaces', 'Our spaces'), 'title' => $T('Tout pour vous entraîner', 'Everything you need to train'), 'cols' => 4, 'items' => [
                    ['dumbbell', $T('Musculation', 'Strength'), $T('Machines guidées, poids libres et zone de force.', 'Guided machines, free weights and a power zone.')],
                    ['heart-pulse', $T('Cardio', 'Cardio'), $T('Tapis, vélos, rameurs et elliptiques connectés.', 'Connected treadmills, bikes, rowers and cross-trainers.')],
                    ['flame', $T('Cours collectifs', 'Group classes'), $T('HIIT, cycling, cross-training, yoga, pilates, danse.', 'HIIT, cycling, cross-training, yoga, Pilates, dance.')],
                    ['target', $T('Coaching', 'Coaching'), $T('Séances individuelles ou en duo, sur objectif.', 'One-to-one or duo sessions, goal-based.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Pour tous les niveaux', 'For every level'),
                    'title' => $T('On vous accompagne dès le premier jour', 'We support you from day one'),
                    'html' => '<p>'.e($T('Chaque nouveau membre commence par un bilan avec un coach : vos objectifs, vos contraintes, votre condition actuelle. Vous repartez avec un programme clair, à refaire seul ou en cours collectif.', 'Every new member starts with a coach assessment: your goals, your limitations, your current fitness. You leave with a clear program to follow on your own or in group classes.')).'</p>',
                    'bullets' => [$T('Bilan forme et programme personnalisé', 'Fitness assessment and personal program'), $T('Coachs diplômés présents sur le plateau', 'Qualified coaches on the gym floor'), $T('Vestiaires, douches et casiers sécurisés', 'Changing rooms, showers and secure lockers')],
                    'buttons' => [[$T('Rencontrer les coachs', 'Meet the coaches'), $coaches, 'outline']],
                    'photo' => ['fitness-coaching', 'users', $T('Coach corrigeant la posture d’une adhérente pendant un exercice de squat', 'Coach correcting a member’s posture during a squat')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('Essai gratuit', 'Free trial'), 'title' => $T('Démarrer en trois étapes', 'Get started in three steps'), 'items' => [
                    [$T('Réservez', 'Book'), $T('Choisissez un créneau en ligne ou à l’accueil.', 'Pick a time slot online or at the front desk.')],
                    [$T('Essayez', 'Try'), $T('Visite, bilan avec un coach et un cours au choix.', 'Tour, coach assessment and a class of your choice.')],
                    [$T('Décidez', 'Decide'), $T('Choisissez votre formule, ou repartez simplement avec vos conseils.', 'Pick a membership, or simply leave with your advice.')],
                ]]),
                B::stats($theme, ['bg' => 'primary', 'title' => $T('La salle en chiffres', 'The gym at a glance'), 'items' => [
                    [1200, ' m²', $T('d’espace d’entraînement', 'of training space')],
                    [60, '+', $T('cours par semaine', 'classes a week')],
                    [12, '', $T('coachs diplômés', 'qualified coaches')],
                    [17, ' h', $T('d’ouverture par jour', 'open per day')],
                ]]),
                B::pricing($theme, ['url' => $plans, 'eyebrow' => $T('Abonnements', 'Memberships'), 'title' => $T('Une formule pour chaque objectif', 'A plan for every goal'), 'intro' => $T('Sans frais d’inscription ce mois-ci. Engagement de 12 mois ou formule mensuelle sans engagement.', 'No joining fee this month. 12-month commitment or no-commitment monthly plan.'), 'items' => $plansItems]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'classes', 'slug' => $u('cours-collectifs', 'classes'), 'title' => $T('Cours collectifs', 'Group classes'),
            'description' => $T('HIIT, cycling, cross-training, yoga, pilates, cardio-boxe : découvrez nos cours collectifs, leur intensité et à qui ils s’adressent.', 'HIIT, cycling, cross-training, yoga, Pilates, cardio boxing: discover our group classes, their intensity and who they are for.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Cours collectifs', 'Group classes'), 'title' => $T('L’énergie du groupe, le suivi d’un coach', 'Group energy, a coach’s attention'), 'intro' => $T('Des cours de 30 à 60 minutes, en petits groupes, réservables dans l’application.', '30- to 60-minute classes in small groups, bookable in the app.')]),
                B::cards($theme, ['items' => [
                    ['fitness-cours-hiit', 'flame', 'HIIT', $T('45 min · intensité élevée · enchaînements courts et intenses pour brûler un maximum d’énergie.', '45 min · high intensity · short, intense intervals to burn maximum energy.')],
                    ['fitness-cours-cycling', 'bike', 'Cycling', $T('45 min · intensité modérée à élevée · vélo en musique, lumière tamisée.', '45 min · moderate to high intensity · indoor cycling to music, low lights.')],
                    ['fitness-cours-cross', 'dumbbell', $T('Cross-training', 'Cross-training'), $T('60 min · tous niveaux · force, gainage et cardio en circuit.', '60 min · all levels · strength, core and cardio circuits.')],
                    ['fitness-cours-yoga', 'leaf', 'Yoga', $T('60 min · intensité douce · souplesse, respiration et récupération.', '60 min · gentle intensity · flexibility, breathing and recovery.')],
                    ['fitness-cours-pilates', 'activity', 'Pilates', $T('45 min · intensité douce · renforcement profond et posture.', '45 min · gentle intensity · deep core strength and posture.')],
                    ['fitness-cours-boxe', 'zap', $T('Cardio-boxe', 'Cardio boxing'), $T('45 min · intensité élevée · enchaînements de boxe, sans contact.', '45 min · high intensity · boxing combinations, no contact.')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Choisir son cours', 'Choosing a class'), 'title' => $T('Trois niveaux d’intensité', 'Three intensity levels'), 'bg' => 'light', 'items' => [
                    ['leaf', $T('Douce', 'Gentle'), $T('Yoga, pilates, stretching : idéal pour débuter ou récupérer.', 'Yoga, Pilates, stretching: ideal to start out or recover.')],
                    ['activity', $T('Modérée', 'Moderate'), $T('Cycling, renforcement, danse : on transpire, à son rythme.', 'Cycling, toning, dance: you sweat, at your own pace.')],
                    ['flame', $T('Élevée', 'High'), $T('HIIT, cross-training, cardio-boxe : pour se dépasser.', 'HIIT, cross-training, cardio boxing: to push your limits.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Débutants', 'Beginners'),
                    'title' => $T('Jamais pris de cours ? Commencez ici', 'Never taken a class? Start here'),
                    'html' => '<p>'.e($T('Chaque samedi matin, le cours « Premiers pas » présente les mouvements de base, les bons réglages et les variantes plus faciles. Arrivez dix minutes avant : le coach vous installe et adapte la séance à votre niveau.', 'Every Saturday morning, the “First steps” class covers the basic moves, the right setup and easier variations. Arrive ten minutes early: the coach will set you up and adapt the session to your level.')).'</p>',
                    'bullets' => [$T('Groupes de 15 personnes au plus', 'Groups of 15 people at most'), $T('Variantes proposées pour chaque exercice', 'Variations offered for every exercise'), $T('Matériel fourni', 'Equipment provided')],
                    'buttons' => [[$T('Voir le planning', 'See the timetable'), $timetable]],
                    'photo' => ['fitness-debutants', 'smile', $T('Groupe de débutants souriants pendant un cours de fitness en studio', 'Smiling group of beginners during a studio fitness class')],
                ]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'timetable', 'slug' => $u('planning', 'timetable'), 'title' => $T('Planning', 'Timetable'),
            'description' => $T('Planning hebdomadaire des cours collectifs du matin au soir, et horaires d’ouverture de la salle et de l’accueil.', 'Weekly group class timetable from morning to evening, plus gym and front desk opening hours.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Planning', 'Timetable'), 'title' => $T('Le planning de la semaine', 'This week’s timetable'), 'intro' => $T('Réservez votre place dans l’application jusqu’à sept jours à l’avance.', 'Book your spot in the app up to seven days in advance.')]),
                M::table($theme, ['note' => $T('Planning susceptible d’évoluer pendant les vacances : consultez l’application pour les mises à jour.', 'Timetable may change during holidays: check the app for updates.'), 'rows' => [
                    [$T('Jour', 'Day'), $T('Matin', 'Morning'), $T('Midi', 'Lunchtime'), $T('Soir', 'Evening'), $T('Fin de soirée', 'Late evening')],
                    [$T('Lundi', 'Monday'), '07:00 HIIT', '12:15 Pilates', '18:30 Cycling', '19:45 Yoga'],
                    [$T('Mardi', 'Tuesday'), $T('07:00 Cross-training', '07:00 Cross-training'), '12:15 Cycling', $T('18:30 Cardio-boxe', '18:30 Cardio boxing'), '19:45 Pilates'],
                    [$T('Mercredi', 'Wednesday'), '07:00 Yoga', '12:15 HIIT', $T('18:30 Cross-training', '18:30 Cross-training'), $T('19:45 Danse', '19:45 Dance')],
                    [$T('Jeudi', 'Thursday'), '07:00 Cycling', '12:15 Pilates', '18:30 HIIT', '19:45 Yoga'],
                    [$T('Vendredi', 'Friday'), $T('07:00 Cross-training', '07:00 Cross-training'), $T('12:15 Cardio-boxe', '12:15 Cardio boxing'), '18:30 Cycling', $T('19:45 Stretching', '19:45 Stretching')],
                    [$T('Samedi', 'Saturday'), $T('09:00 Premiers pas', '09:00 First steps'), $T('10:30 Cross-training', '10:30 Cross-training'), '—', '—'],
                    [$T('Dimanche', 'Sunday'), '09:30 Yoga', '11:00 Cycling', '—', '—'],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('Horaires', 'Opening hours'), 'title' => $T('Quand venir', 'When to come'), 'groups' => [
                    [$T('La salle', 'The gym'), [
                        ['clock', $T('Lundi – vendredi : 6 h – 23 h', 'Monday – Friday: 6 am – 11 pm')],
                        ['clock', $T('Samedi et dimanche : 8 h – 20 h', 'Saturday and Sunday: 8 am – 8 pm')],
                        ['users', $T('Moins de monde : 10 h – 16 h en semaine', 'Quietest: 10 am – 4 pm on weekdays')],
                    ]],
                    [$T('L’accueil et les coachs', 'Front desk and coaches'), [
                        ['user-round', $T('Accueil : 8 h – 21 h en semaine, 9 h – 18 h le week-end', 'Front desk: 8 am – 9 pm weekdays, 9 am – 6 pm weekends')],
                        ['dumbbell', $T('Coach sur le plateau : 7 h – 10 h et 17 h – 21 h', 'Coach on the floor: 7 – 10 am and 5 – 9 pm')],
                        ['smartphone', $T('Réservation des cours 7 j/7 dans l’application', 'Class booking 7 days a week in the app')],
                    ]],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'coaches', 'slug' => $u('coachs', 'coaches'), 'title' => $T('Coachs et coaching', 'Coaches and personal training'),
            'description' => $T('Nos coachs diplômés et leurs spécialités, et nos formules de coaching individuel : séance, pack de dix ou suivi mensuel.', 'Our qualified coaches and their specialties, and our personal training options: single session, ten-pack or monthly program.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Coachs', 'Coaches'), 'title' => $T('Des coachs à votre écoute', 'Coaches who listen'), 'intro' => $T('Diplômés, formés chaque année, et passionnés par ce qu’ils transmettent.', 'Qualified, trained every year, and passionate about what they teach.')]),
                B::team($theme, ['shape' => 'circle', 'items' => [
                    ['[Prénom Nom]', $T('Préparation physique et musculation', 'Strength and conditioning'), 'fitness-equipe-1'],
                    ['[Prénom Nom]', $T('Yoga et pilates', 'Yoga and Pilates'), 'fitness-equipe-2'],
                    ['[Prénom Nom]', $T('Cross-training et HIIT', 'Cross-training and HIIT'), 'fitness-equipe-3'],
                    ['[Prénom Nom]', $T('Remise en forme et nutrition', 'Fitness and nutrition'), 'fitness-equipe-4'],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Coaching individuel', 'Personal training'),
                    'title' => $T('Un programme fait pour vous, et quelqu’un pour vous y tenir', 'A program built for you, and someone to keep you on track'),
                    'html' => '<p>'.e($T('Perte de poids, prise de force, préparation d’une course, reprise après une grossesse ou une blessure : votre coach construit un plan sur mesure, ajuste chaque séance et mesure vos progrès toutes les quatre semaines.', 'Weight loss, building strength, preparing for a race, getting back in shape after pregnancy or injury: your coach builds a custom plan, adjusts every session and measures your progress every four weeks.')).'</p>',
                    'bullets' => [$T('Bilan complet à la première séance', 'Full assessment at the first session'), $T('Séances seul ou en duo', 'Sessions alone or as a pair'), $T('Conseils nutrition inclus', 'Nutrition advice included')],
                    'photo' => ['fitness-coach-individuel', 'target', $T('Coach notant les progrès d’un client sur une tablette dans une salle de sport', 'Coach recording a client’s progress on a tablet in a gym')],
                    'bg' => 'light',
                ]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Tarifs', 'Rates'), 'title' => $T('Formules de coaching', 'Personal training options'), 'intro' => $T('Accessibles aux membres et aux non-membres.', 'Open to members and non-members.'), 'bg' => 'white', 'items' => [
                    [$T('Séance découverte', 'Single session'), $T('45', '49'), $cur, $T('/ séance', '/ session'), $T('Une heure pour faire le point et tester.', 'One hour to assess and try it out.'), [$T('Bilan et objectifs', 'Assessment and goals'), $T('Séance complète', 'Full session'), $T('Conseils pour continuer seul', 'Tips to keep going alone'), '-'.$T('Suivi dans la durée', 'Long-term follow-up')], $T('Réserver', 'Book')],
                    [$T('Pack 10 séances', '10-session pack'), $T('390', '420'), $cur, $T('valable 4 mois', 'valid 4 months'), $T('Pour progresser régulièrement.', 'For steady progress.'), [$T('Programme personnalisé', 'Custom program'), $T('Mesures toutes les 4 semaines', 'Measurements every 4 weeks'), $T('Séances en duo possibles', 'Duo sessions possible'), $T('Application de suivi', 'Tracking app')], $T('Choisir le pack', 'Choose the pack'), true, $T('Le meilleur rapport', 'Best value')],
                    [$T('Suivi mensuel', 'Monthly program'), $T('180', '195'), $cur, $T('/ mois', '/ month'), $T('Un accompagnement complet sur objectif.', 'Full goal-based support.'), [$T('Une séance par semaine', 'One session a week'), $T('Plan d’entraînement complet', 'Full training plan'), $T('Suivi nutritionnel', 'Nutrition follow-up'), $T('Échanges illimités par message', 'Unlimited messaging')], $T('Nous contacter', 'Contact us')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'memberships', 'slug' => $u('abonnements', 'memberships'), 'title' => $T('Abonnements', 'Memberships'),
            'description' => $T('Nos abonnements Essentiel, Illimité et Premium, tarifs étudiants et entreprises, conditions de résiliation et questions fréquentes.', 'Our Essential, Unlimited and Premium memberships, student and corporate rates, cancellation terms and FAQ.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Abonnements', 'Memberships'), 'title' => $T('Des formules claires, sans surprise', 'Clear plans, no surprises'), 'intro' => $T('Avec ou sans engagement, et toujours une séance d’essai offerte avant de choisir.', 'With or without commitment, and always a free trial session before you choose.')]),
                B::pricing($theme, ['url' => $trial, 'items' => $plansItems]),
                B::features($theme, ['eyebrow' => $T('Tarifs réduits', 'Reduced rates'), 'title' => $T('Des réductions pour vous', 'Discounts for you'), 'cols' => 4, 'bg' => 'light', 'items' => [
                    ['graduation-cap', $T('Étudiants', 'Students'), $T('– 25 % sur présentation de la carte étudiante.', '25% off with a valid student card.')],
                    ['building-2', $T('Entreprises', 'Companies'), $T('Tarifs de groupe pour les salariés et cours en entreprise.', 'Group rates for employees and on-site classes.')],
                    ['users', $T('Duo', 'Duo'), $T('– 15 % pour deux abonnements souscrits ensemble.', '15% off two memberships taken out together.')],
                    ['calendar-days', $T('Paiement annuel', 'Annual payment'), $T('Deux mois offerts pour tout paiement en une fois.', 'Two months free when you pay upfront.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur l’abonnement', 'Your membership questions'), 'intro' => $T('Une autre question ? L’accueil vous répond sur place, par téléphone ou par e-mail.', 'Another question? The front desk can help in person, by phone or by email.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Y a-t-il des frais d’inscription ?', 'Is there a joining fee?'), $T('Les frais d’inscription de 49 € sont offerts pour tout abonnement avec engagement de 12 mois.', 'The $49 joining fee is waived for any 12-month membership.')],
                    [$T('Puis-je mettre mon abonnement en pause ?', 'Can I freeze my membership?'), $T('Oui, jusqu’à trois mois par an sur justificatif (voyage, maladie, grossesse).', 'Yes, up to three months a year with supporting documents (travel, illness, pregnancy).')],
                    [$T('Comment résilier ?', 'How do I cancel?'), $T('La formule mensuelle se résilie à tout moment avec un mois de préavis, depuis votre espace membre ou à l’accueil.', 'The monthly plan can be cancelled anytime with one month’s notice, from your member account or at the front desk.')],
                    [$T('Quels moyens de paiement acceptez-vous ?', 'Which payment methods do you accept?'), $T('Carte bancaire, prélèvement mensuel, virement ou paiement mobile.', 'Card, monthly direct debit, bank transfer or mobile payment.')],
                    [$T('Faut-il un certificat médical ?', 'Do I need a medical certificate?'), $T('Un questionnaire de santé est demandé à l’inscription. En cas de doute, demandez l’avis de votre médecin avant de reprendre le sport.', 'A health questionnaire is required when you sign up. If in doubt, ask your doctor before starting exercise again.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'results', 'slug' => $u('transformations', 'transformations'), 'title' => $T('Transformations', 'Transformations'),
            'description' => $T('Parcours de nos membres et programme de transformation en 12 semaines : entraînement, nutrition et suivi par un coach.', 'Our members’ journeys and the 12-week transformation program: training, nutrition and coach follow-up.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Transformations', 'Transformations'), 'title' => $T('Des progrès réels, à votre rythme', 'Real progress, at your own pace'), 'intro' => $T('Chaque parcours est unique : les résultats dépendent de chacun, de sa régularité et de son point de départ.', 'Every journey is unique: results depend on each person, their consistency and their starting point.')]),
                B::steps($theme, ['eyebrow' => $T('Programme 12 semaines', '12-week program'), 'title' => $T('Comment se déroule le programme', 'How the program works'), 'items' => [
                    [$T('Bilan de départ', 'Starting assessment'), $T('Mesures, tests de forme et objectifs réalistes fixés avec votre coach.', 'Measurements, fitness tests and realistic goals set with your coach.')],
                    [$T('Entraînement', 'Training'), $T('Trois séances par semaine, dont une avec votre coach.', 'Three sessions a week, one of them with your coach.')],
                    [$T('Nutrition', 'Nutrition'), $T('Des repères alimentaires simples, sans régime extrême.', 'Simple eating guidelines, no extreme diets.')],
                    [$T('Bilan final', 'Final review'), $T('Nouvelles mesures, comparaison et plan pour la suite.', 'New measurements, comparison and a plan for what comes next.')],
                ]]),
                B::gallery($theme, ['eyebrow' => $T('Nos membres', 'Our members'), 'title' => $T('Ils se sont lancés', 'They took the plunge'), 'bg' => 'light', 'photos' => [
                    ['fitness-transformation-1', 'dumbbell', $T('Femme soulevant une barre avec assurance dans une salle de musculation', 'Woman confidently lifting a barbell in a weights room')],
                    ['fitness-transformation-2', 'bike', $T('Homme d’âge mûr souriant sur un vélo de cycling', 'Smiling middle-aged man on an indoor cycling bike')],
                    ['fitness-transformation-3', 'heart-pulse', $T('Deux amies se tapant dans la main après un entraînement', 'Two friends high-fiving after a workout')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::stats($theme, ['title' => $T('Le programme en chiffres', 'The program in numbers'), 'items' => [
                    [12, '', $T('semaines d’accompagnement', 'weeks of support')],
                    [36, '', $T('séances d’entraînement', 'training sessions')],
                    [87, ' %', $T('des participants vont jusqu’au bout', 'of participants finish the program')],
                    [4, '', $T('bilans avec votre coach', 'reviews with your coach')],
                ]]),
                B::cta($theme, ['title' => $T('Prochain départ du programme', 'Next program start'), 'text' => $T('Groupes de 10 personnes, départ le premier lundi de chaque mois. Places limitées.', 'Groups of 10, starting on the first Monday of every month. Limited places.'),
                    'buttons' => [[$T('Je m’inscris', 'Sign me up'), $trial, 'accent'], [$T('Les coachs', 'The coaches'), $coaches, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'tips', 'slug' => $u('conseils', 'tips'), 'title' => $T('Conseils', 'Tips'),
            'description' => $T('Conseils d’entraînement, de récupération et d’alimentation rédigés par nos coachs, pour progresser sans vous blesser.', 'Training, recovery and nutrition tips written by our coaches, to progress without getting hurt.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Conseils', 'Tips'), 'title' => $T('Les conseils de nos coachs', 'Advice from our coaches'), 'intro' => $T('Des repères simples et fiables. Ils ne remplacent pas l’avis d’un médecin.', 'Simple, reliable guidance. It does not replace medical advice.')]),
                M::terms($theme, ['bg' => 'light']),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire le conseil', 'Read the tip')]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Réservez votre séance d’essai gratuite, sans engagement, ou contactez l’accueil de la salle : adresse, horaires d’ouverture, accès et parking.', 'Book your free, no-commitment trial session or contact the front desk: address, opening hours, directions and parking.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Essai gratuit', 'Free trial'), 'title' => $T('Réservez votre séance offerte', 'Book your free session'), 'intro' => $T('Indiquez vos disponibilités : nous vous proposons un créneau sous 24 heures.', 'Tell us when you are available: we will offer you a slot within 24 hours.')]),
                B::pillars($theme, ['items' => [
                    ['shirt', $T('Venez en tenue', 'Come dressed to train'), $T('Tenue de sport, baskets propres et une serviette.', 'Sportswear, clean sneakers and a towel.')],
                    ['droplet', $T('Pensez à boire', 'Stay hydrated'), $T('Une gourde : des fontaines sont à votre disposition.', 'A water bottle: refill stations are available.')],
                    ['clock', $T('Arrivez en avance', 'Arrive early'), $T('Dix minutes avant, pour la visite et le bilan.', 'Ten minutes before, for the tour and assessment.')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Nous trouver', 'Find us'), 'intro' => $T('Parking gratuit pour les membres et arrêt de bus à deux minutes.', 'Free parking for members and a bus stop two minutes away.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Salle ouverte 6 h – 23 h en semaine, 8 h – 20 h le week-end', 'Gym open 6 am – 11 pm weekdays, 8 am – 8 pm weekends')],
                    ],
                    'social' => ['instagram', 'tiktok', 'facebook'],
                    'button' => $T('Réserver mon essai', 'Book my trial'),
                    'bg' => 'light',
                ]),
            ]];

        $blog = [
            'base' => $u('conseils', 'tips'),
            'title' => $T('Conseils', 'Tips'),
            'categories' => ['entrainement' => $T('Entraînement', 'Training'), 'nutrition' => $T('Nutrition', 'Nutrition'), 'recuperation' => $T('Récupération', 'Recovery')],
            'posts' => [
                ['key' => 'reprendre', 'slug' => $u('reprendre-le-sport', 'getting-back-into-exercise'), 'category' => 'entrainement', 'tags' => [$T('Débutants', 'Beginners')],
                    'title' => $T('Reprendre le sport sans se décourager', 'Getting back into exercise without giving up'),
                    'excerpt' => $T('Trois séances courtes valent mieux qu’une longue : nos coachs expliquent pourquoi.', 'Three short sessions beat one long one: our coaches explain why.'),
                    'photo' => ['fitness-blog-reprise', 'activity', $T('Personne faisant des étirements sur un tapis avant une séance', 'Person stretching on a mat before a workout')],
                    'heading' => $T('La bonne méthode', 'The right approach'),
                    'body' => [
                        $T('Le premier mois, l’objectif n’est pas la performance mais l’habitude. Fixez deux ou trois créneaux par semaine et tenez-les comme un rendez-vous.', 'In the first month, the goal is not performance but habit. Set two or three slots a week and keep them like appointments.'),
                        $T('Commencez à une intensité où vous pouvez encore parler, puis augmentez progressivement la charge ou la durée, jamais les deux à la fois.', 'Start at an intensity where you can still talk, then gradually increase the load or the duration, never both at once.'),
                        $T('Notez chaque séance : voir ses progrès est le meilleur moteur de motivation.', 'Log every session: seeing your progress is the best motivation there is.'),
                    ]],
                ['key' => 'assiette', 'slug' => $u('que-manger-avant-apres', 'what-to-eat-before-and-after'), 'category' => 'nutrition', 'tags' => [$T('Alimentation', 'Eating')],
                    'title' => $T('Que manger avant et après l’entraînement ?', 'What to eat before and after training'),
                    'excerpt' => $T('Des repères simples pour avoir de l’énergie et bien récupérer.', 'Simple guidelines to have energy and recover well.'),
                    'photo' => ['fitness-blog-nutrition', 'utensils', $T('Bol de fruits frais, flocons d’avoine et yaourt sur une table', 'Bowl of fresh fruit, oats and yogurt on a table')],
                    'quote' => $T('« Pas besoin de compléments miracles : une assiette variée fait l’essentiel du travail. »', '“No need for miracle supplements: a varied plate does most of the work.”'),
                    'body' => [
                        $T('Avant la séance, privilégiez un en-cas léger deux heures avant : un fruit, une tartine, un yaourt. Évitez les repas lourds juste avant de vous entraîner.', 'Before your session, have a light snack two hours ahead: a piece of fruit, toast, a yogurt. Avoid heavy meals just before training.'),
                        $T('Après, associez une source de protéines et des glucides : œufs, légumineuses, poisson ou laitages, avec du riz, des pâtes ou du pain.', 'Afterwards, combine protein and carbohydrates: eggs, legumes, fish or dairy, with rice, pasta or bread.'),
                        $T('Et surtout, buvez de l’eau régulièrement, avant, pendant et après l’effort.', 'Above all, drink water regularly before, during and after exercise.'),
                    ]],
                ['key' => 'sommeil', 'slug' => $u('recuperer-grace-au-sommeil', 'recovering-through-sleep'), 'category' => 'recuperation', 'tags' => [$T('Sommeil', 'Sleep')],
                    'title' => $T('Le sommeil, votre meilleur allié pour progresser', 'Sleep, your best ally for progress'),
                    'excerpt' => $T('C’est au repos que le corps se renforce : comment mieux dormir quand on s’entraîne.', 'Your body gets stronger while resting: how to sleep better when you train.'),
                    'photo' => ['fitness-blog-sommeil', 'moon', $T('Chambre calme et lumière douce du soir', 'Calm bedroom with soft evening light')],
                    'heading' => $T('Nos conseils', 'Our advice'),
                    'body' => [
                        $T('Les muscles se réparent et se renforcent pendant le sommeil. Visez sept à neuf heures par nuit, à heures régulières.', 'Muscles repair and grow stronger during sleep. Aim for seven to nine hours a night, at regular times.'),
                        $T('Évitez les séances très intenses tard le soir si vous avez du mal à vous endormir, et préférez alors le yoga ou les étirements.', 'Avoid very intense sessions late in the evening if you struggle to fall asleep, and opt for yoga or stretching instead.'),
                        $T('Écrans éteints une heure avant le coucher et chambre fraîche : deux gestes simples qui changent beaucoup.', 'Screens off an hour before bed and a cool bedroom: two simple habits that make a big difference.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Cours', 'Classes'), 'url' => $classes],
                    ['label' => $T('Planning', 'Timetable'), 'url' => $timetable],
                    ['label' => $T('Coachs', 'Coaches'), 'url' => $coaches],
                    ['label' => $T('Abonnements', 'Memberships'), 'url' => $plans],
                    ['label' => $T('Transformations', 'Transformations'), 'url' => $results],
                    ['label' => $T('Conseils', 'Tips'), 'url' => $tips],
                ],
                'cta_label' => $T('Essai gratuit', 'Free trial'),
                'cta_url' => $trial,
                'footer_text' => $T('Salle de sport et studio de cours collectifs : musculation, cardio, coaching et plus de 60 cours par semaine, pour tous les niveaux.', 'Gym and group fitness studio: strength, cardio, personal training and over 60 classes a week, for every level.'),
                'footer_links' => [
                    ['label' => $T('Planning des cours', 'Class timetable'), 'url' => $timetable],
                    ['label' => $T('Abonnements', 'Memberships'), 'url' => $plans],
                    ['label' => $T('Coaching individuel', 'Personal training'), 'url' => $coaches],
                    ['label' => $T('Essai gratuit', 'Free trial'), 'url' => $trial],
                ],
            ],
        ];
    },
];
