<?php

/*
 * Pack « Pharmacie » (secteur Hôpitaux, cliniques et centres de santé) : 8 pages,
 * en français et en anglais. Pharmacie d'officine : services, ordonnances,
 * parapharmacie, vaccination et dépistage, horaires et gardes, conseils santé (blog).
 * Prudence : les textes informent et renvoient toujours vers un professionnel de
 * santé ; aucun dosage ni conseil de traitement. Les textes sont des exemples
 * à personnaliser (l'assistant IA les réécrit à partir de la présentation de
 * l'officine). Les liens internes sont écrits avec les adresses de la langue
 * de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Hôpitaux, cliniques et centres de santé', 'en' => 'Hospitals, clinics and health centers'],
        'icon' => 'pill',
        'keywords' => ['pharmacie', 'officine', 'pharmacien', 'ordonnance', 'médicaments', 'parapharmacie', 'vaccination', 'pharmacie de garde', 'pharmacy', 'drugstore', 'pharmacist', 'prescription', 'medicines', 'vaccination', 'health'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $services = $u('/services', '/services');
        $prescriptions = $u('/ordonnances', '/prescriptions');
        $shop = $u('/parapharmacie', '/health-and-beauty');
        $vaccination = $u('/vaccination-depistage', '/vaccination-and-testing');
        $hours = $u('/horaires-gardes', '/opening-hours');
        $advice = $u('/conseils-sante', '/health-advice');
        $contact = '/contact';

        $hoursGroups = [
            [$T('Horaires d’ouverture', 'Opening hours'), [
                ['clock', $T('Lundi – vendredi : 8 h 30 – 19 h 30', 'Monday – Friday: 8:30 am – 7:30 pm')],
                ['clock', $T('Samedi : 9 h – 18 h', 'Saturday: 9 am – 6 pm')],
                ['calendar-days', $T('Dimanche et jours fériés : selon le tour de garde', 'Sunday and public holidays: according to the on-duty rota')],
            ]],
            [$T('En dehors des horaires', 'Outside opening hours'), [
                ['moon', $T('Pharmacie de garde : consultez le tableau ci-dessous ou l’affiche sur notre porte', 'On-duty pharmacy: see the table below or the notice on our door')],
                ['phone', $T('Urgence vitale : appelez le numéro d’urgence de votre pays', 'Life-threatening emergency: call your country’s emergency number')],
                ['stethoscope', $T('Besoin d’un médecin la nuit : contactez le service de garde médicale', 'Need a doctor at night: contact the out-of-hours medical service')],
            ]],
        ];
        $team = ['eyebrow' => $T('L’équipe', 'Our team'), 'title' => $T('Des professionnels à votre écoute', 'Professionals who listen'), 'shape' => 'circle', 'items' => [
            ['[Prénom Nom]', $T('Pharmacienne titulaire', 'Head pharmacist'), 'pharmacie-equipe-1'],
            ['[Prénom Nom]', $T('Pharmacien adjoint', 'Staff pharmacist'), 'pharmacie-equipe-2'],
            ['[Prénom Nom]', $T('Préparatrice en pharmacie', 'Pharmacy technician'), 'pharmacie-equipe-3'],
            ['[Prénom Nom]', $T('Conseillère en dermocosmétique', 'Skincare adviser'), 'pharmacie-equipe-4'],
        ]];
        $cta = ['title' => $T('Gagnez du temps au comptoir', 'Save time at the counter'), 'text' => $T('Envoyez votre ordonnance en ligne : nous préparons votre traitement et vous prévenons dès qu’il est prêt. Présentez l’original lors du retrait.', 'Send your prescription online: we prepare your medicines and let you know as soon as they are ready. Bring the original when you collect.'),
            'buttons' => [[$T('Envoyer mon ordonnance', 'Send my prescription'), $contact, 'accent'], [$T('Horaires et gardes', 'Hours and on-duty'), $hours, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Votre pharmacie de quartier : ordonnances, conseils, parapharmacie, vaccination et dépistage. Horaires, gardes et envoi d’ordonnance en ligne.', 'Your neighborhood pharmacy: prescriptions, advice, health and beauty, vaccination and testing. Hours, on-duty rota and online prescriptions.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Pharmacie de quartier', 'Neighborhood pharmacy'),
                    'title' => $T('Votre santé mérite un vrai conseil', 'Your health deserves real advice'),
                    'text' => $T('Une équipe de pharmaciens disponible pour vos ordonnances, vos questions et votre prévention, avec un espace confidentiel pour échanger en toute discrétion.', 'A team of pharmacists available for your prescriptions, your questions and your prevention, with a private room to talk in confidence.'),
                    'buttons' => [[$T('Envoyer mon ordonnance', 'Send my prescription'), $prescriptions], [$T('Nos horaires', 'Our hours'), $hours]],
                    'photo' => ['pharmacie-accueil', 'pill', $T('Pharmacienne souriante conseillant une cliente au comptoir d’une pharmacie lumineuse', 'Smiling pharmacist advising a customer at the counter of a bright pharmacy')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos services', 'Our services'), 'title' => $T('Bien plus que des médicaments', 'Much more than medicines'), 'items' => [
                    ['clipboard-plus', $T('Ordonnances', 'Prescriptions'), $T('Préparation rapide, envoi en ligne et suivi de vos traitements au long cours.', 'Fast dispensing, online submission and follow-up of long-term treatments.'), $prescriptions, $T('En savoir plus', 'Learn more')],
                    ['syringe', $T('Vaccination et dépistage', 'Vaccination and testing'), $T('Vaccins et tests rapides réalisés sur rendez-vous par un pharmacien habilité.', 'Vaccines and rapid tests by appointment with an authorized pharmacist.'), $vaccination, $T('Prendre rendez-vous', 'Book an appointment')],
                    ['sparkles', $T('Parapharmacie', 'Health and beauty'), $T('Soins de la peau, bébé, hygiène et nutrition, avec les conseils de l’équipe.', 'Skincare, baby care, hygiene and nutrition, with advice from the team.'), $shop, $T('Découvrir', 'Discover')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Le conseil', 'Our advice'),
                    'title' => $T('Prendre le temps de vous expliquer', 'Taking the time to explain'),
                    'html' => '<p>'.e($T('Comment prendre un traitement, à quel moment, avec quelles précautions : nous vérifions les interactions entre vos médicaments et répondons à vos questions. Lorsque votre situation le demande, nous vous orientons vers votre médecin.', 'How and when to take a treatment, with what precautions: we check interactions between your medicines and answer your questions. When your situation calls for it, we refer you to your doctor.')).'</p>',
                    'bullets' => [$T('Espace de confidentialité pour les échanges privés', 'Private room for confidential conversations'), $T('Vérification des interactions médicamenteuses', 'Drug interaction checks'), $T('Orientation vers un médecin si nécessaire', 'Referral to a doctor when needed')],
                    'buttons' => [[$T('Tous nos services', 'All our services'), $services, 'outline']],
                    'photo' => ['pharmacie-conseil', 'message-circle', $T('Pharmacien expliquant une notice de médicament à un patient âgé', 'Pharmacist explaining a medicine leaflet to an older patient')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::schedule($theme, ['eyebrow' => $T('Horaires', 'Hours'), 'title' => $T('Quand nous trouver', 'When to find us'), 'bg' => 'white', 'groups' => $hoursGroups]),
                B::stats($theme, ['items' => [
                    [6, '', $T('jours sur 7', 'days a week')],
                    [4, '', $T('pharmaciens et préparateurs', 'pharmacists and technicians')],
                    [3000, '+', $T('références en parapharmacie', 'health and beauty products')],
                    [2, ' h', $T('pour préparer une ordonnance envoyée en ligne', 'to prepare an online prescription')],
                ]]),
                B::team($theme, $team),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'services', 'slug' => $u('services', 'services'), 'title' => $T('Nos services', 'Our services'),
            'description' => $T('Conseil pharmaceutique, entretiens de suivi, piluliers, prise de tension, location de matériel médical, livraison et collecte des médicaments.', 'Pharmacist advice, follow-up consultations, pill organizers, blood pressure checks, medical equipment rental, delivery and medicine take-back.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Nos services', 'Our services'), 'title' => $T('Un accompagnement au quotidien', 'Everyday support'), 'intro' => $T('Des services pensés pour vous simplifier la vie et sécuriser vos traitements.', 'Services designed to make life easier and keep your treatments safe.')]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['message-circle', $T('Entretiens pharmaceutiques', 'Medication reviews'), $T('Un rendez-vous pour faire le point sur l’ensemble de vos traitements.', 'An appointment to review all of your treatments.')],
                    ['calendar-check', $T('Préparation de piluliers', 'Pill organizers'), $T('Vos médicaments préparés à la semaine, pour éviter les oublis.', 'Your medicines prepared weekly, to avoid missed doses.')],
                    ['activity', $T('Prise de tension', 'Blood pressure check'), $T('Une mesure gratuite et sans rendez-vous, à transmettre à votre médecin.', 'A free check with no appointment, to share with your doctor.')],
                    ['accessibility', $T('Matériel médical', 'Medical equipment'), $T('Location et vente : fauteuils, déambulateurs, tire-lait, lits médicalisés.', 'Rental and sale: wheelchairs, walkers, breast pumps, hospital beds.')],
                    ['truck', $T('Livraison à domicile', 'Home delivery'), $T('Pour les personnes qui ne peuvent pas se déplacer, sur ordonnance.', 'For people who cannot travel, with a prescription.')],
                    ['recycle', $T('Collecte des médicaments', 'Medicine take-back'), $T('Rapportez vos médicaments non utilisés : nous les éliminons en toute sécurité.', 'Bring back unused medicines: we dispose of them safely.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Confidentialité', 'Confidentiality'),
                    'title' => $T('Un espace pour parler librement', 'A space to talk freely'),
                    'html' => '<p>'.e($T('Certaines questions ne se posent pas devant une file d’attente. Notre espace de confidentialité vous permet d’échanger avec un pharmacien en toute discrétion, sur simple demande au comptoir.', 'Some questions are not asked in front of a queue. Our private room lets you talk with a pharmacist in confidence, just ask at the counter.')).'</p>',
                    'bullets' => [$T('Contraception et santé sexuelle', 'Contraception and sexual health'), $T('Arrêt du tabac', 'Quitting smoking'), $T('Suivi de traitements longs', 'Long-term treatment follow-up')],
                    'photo' => ['pharmacie-espace-confidentiel', 'lock', $T('Petit bureau de consultation calme dans une pharmacie, deux chaises et une table', 'Small quiet consultation room in a pharmacy, two chairs and a table')],
                    'bg' => 'light',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Ce qui guide notre pratique', 'What guides our practice'), 'items' => [
                    ['shield-check', $T('Sécurité', 'Safety'), $T('Chaque délivrance est vérifiée par un pharmacien.', 'Every dispensing is checked by a pharmacist.')],
                    ['lock', $T('Secret professionnel', 'Professional secrecy'), $T('Vos informations de santé restent strictement confidentielles.', 'Your health information remains strictly confidential.')],
                    ['heart-handshake', $T('Proximité', 'Proximity'), $T('Une équipe stable qui connaît ses patients.', 'A stable team that knows its patients.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'bg' => 'light', 'intro' => $T('Pour toute question sur votre santé, votre pharmacien et votre médecin restent vos interlocuteurs.', 'For any question about your health, your pharmacist and your doctor remain your contacts.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Le pharmacien peut-il remplacer une consultation médicale ?', 'Can the pharmacist replace a doctor’s visit?'), $T('Non. Il conseille pour les troubles bénins et vous oriente vers un médecin dès que vos symptômes le justifient.', 'No. They advise on minor ailments and refer you to a doctor whenever your symptoms warrant it.')],
                    [$T('La prise de tension remplace-t-elle un suivi médical ?', 'Does a blood pressure check replace medical follow-up?'), $T('Non, c’est un repère utile à transmettre à votre médecin, qui seul peut interpréter vos résultats.', 'No, it is a useful reference to share with your doctor, who alone can interpret your results.')],
                    [$T('Comment louer du matériel médical ?', 'How do I rent medical equipment?'), $T('Venez avec votre ordonnance ou appelez-nous : nous vérifions la disponibilité et organisons la livraison si besoin.', 'Bring your prescription or call us: we check availability and arrange delivery if needed.')],
                ]]),
            ]];

        $pages[] = ['key' => 'prescriptions', 'slug' => $u('ordonnances', 'prescriptions'), 'title' => $T('Ordonnances', 'Prescriptions'),
            'description' => $T('Envoyez votre ordonnance en ligne, retirez votre traitement sans attendre et faites suivre vos traitements chroniques par votre pharmacien.', 'Send your prescription online, collect your medicines without waiting and have your long-term treatments followed by your pharmacist.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Ordonnances', 'Prescriptions'), 'title' => $T('Votre traitement prêt quand vous arrivez', 'Your medicines ready when you arrive'), 'intro' => $T('Envoyez une photo de votre ordonnance : nous la préparons et vous prévenons. L’original reste à présenter au retrait.', 'Send a photo of your prescription: we prepare it and let you know. The original must be shown on collection.')]),
                B::steps($theme, ['eyebrow' => $T('Comment ça marche', 'How it works'), 'title' => $T('Trois étapes, sans file d’attente', 'Three steps, no queue'), 'items' => [
                    [$T('Envoyez', 'Send'), $T('Une photo lisible de l’ordonnance par le formulaire ou par e-mail.', 'A clear photo of the prescription via the form or by email.')],
                    [$T('Nous préparons', 'We prepare'), $T('Un pharmacien vérifie l’ordonnance et prépare votre traitement.', 'A pharmacist checks the prescription and prepares your medicines.')],
                    [$T('Vous retirez', 'You collect'), $T('Un message vous prévient : passez avec l’original et votre carte d’assurance.', 'A message lets you know: come with the original and your insurance card.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Traitements au long cours', 'Long-term treatments'),
                    'title' => $T('Un suivi régulier de vos traitements', 'Regular follow-up of your treatments'),
                    'html' => '<p>'.e($T('Diabète, hypertension, asthme : nous vous aidons à ne jamais manquer de médicaments. Nous anticipons les commandes, vous rappelons les renouvellements et faisons le lien avec votre médecin lorsque votre ordonnance arrive à échéance.', 'Diabetes, high blood pressure, asthma: we help you never run out of medicine. We order ahead, remind you of renewals and liaise with your doctor when your prescription is about to expire.')).'</p>',
                    'bullets' => [$T('Rappel avant la fin de votre traitement', 'Reminder before your treatment runs out'), $T('Historique de délivrance conservé en sécurité', 'Dispensing history kept securely'), $T('Livraison possible pour les personnes fragiles', 'Delivery available for vulnerable people')],
                    'buttons' => [[$T('Envoyer mon ordonnance', 'Send my prescription'), $contact]],
                    'photo' => ['pharmacie-ordonnance', 'clipboard-plus', $T('Préparatrice rangeant des boîtes de médicaments dans les tiroirs d’une pharmacie', 'Pharmacy technician putting medicine boxes away in pharmacy drawers')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur les ordonnances', 'Your prescription questions'), 'items' => [
                    [$T('L’envoi en ligne suffit-il ?', 'Is sending it online enough?'), $T('Non : il permet de préparer votre traitement à l’avance, mais l’ordonnance originale doit être présentée lors du retrait, selon la réglementation en vigueur.', 'No: it lets us prepare your medicines in advance, but the original prescription must be shown on collection, as required by regulations.')],
                    [$T('Mon médicament n’est pas en stock, que se passe-t-il ?', 'What if my medicine is out of stock?'), $T('Nous le commandons, généralement pour le jour même ou le lendemain, et vous prévenons dès son arrivée.', 'We order it, usually for the same or next day, and let you know as soon as it arrives.')],
                    [$T('Pouvez-vous me délivrer un générique ?', 'Can you give me a generic?'), $T('Selon la réglementation de votre pays et sauf mention contraire du médecin, le pharmacien peut proposer un médicament générique équivalent et vous l’explique.', 'Depending on your country’s rules and unless the doctor specifies otherwise, the pharmacist may offer an equivalent generic and will explain it to you.')],
                    [$T('Prenez-vous en charge mon assurance santé ?', 'Do you handle my health insurance?'), $T('Nous travaillons avec la plupart des organismes d’assurance. Présentez votre carte : nous vous indiquons ce qui reste à votre charge.', 'We work with most insurers. Show your card: we tell you what remains for you to pay.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'shop', 'slug' => $u('parapharmacie', 'health-and-beauty'), 'title' => $T('Parapharmacie', 'Health and beauty'),
            'description' => $T('Soins du visage et du corps, bébé et maman, hygiène, solaires, nutrition et orthopédie : une sélection de marques et les conseils de l’équipe.', 'Face and body care, baby and mother, hygiene, sun care, nutrition and orthopedics: a selection of brands and advice from the team.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Parapharmacie', 'Health and beauty'), 'title' => $T('Prendre soin de soi, bien conseillé', 'Self-care, well advised'), 'intro' => $T('Plus de 3 000 produits sélectionnés par l’équipe, avec des conseils adaptés à votre peau et à vos besoins.', 'More than 3,000 products selected by the team, with advice tailored to your skin and needs.')]),
                B::cards($theme, ['items' => [
                    ['pharmacie-rayon-visage', 'sparkles', $T('Soins du visage et dermocosmétique', 'Face care and dermocosmetics'), $T('Crèmes, sérums et nettoyants pour chaque type de peau, y compris les peaux sensibles.', 'Creams, serums and cleansers for every skin type, including sensitive skin.')],
                    ['pharmacie-rayon-bebe', 'baby', $T('Bébé et maman', 'Baby and mother'), $T('Toilette, change, allaitement, biberons et soins de la future maman.', 'Bathing, changing, breastfeeding, bottles and care for mothers-to-be.')],
                    ['pharmacie-rayon-solaire', 'sun', $T('Protection solaire', 'Sun protection'), $T('Crèmes adaptées aux enfants, aux peaux claires ou sensibles et au sport.', 'Sunscreens for children, fair or sensitive skin and sport.')],
                    ['pharmacie-rayon-hygiene', 'droplet', $T('Hygiène et soins bucco-dentaires', 'Hygiene and oral care'), $T('Brosses à dents, dentifrices, soins du corps et des cheveux.', 'Toothbrushes, toothpaste, body and hair care.')],
                    ['pharmacie-rayon-nutrition', 'leaf', $T('Nutrition et compléments', 'Nutrition and supplements'), $T('Vitamines et compléments, à choisir avec un pharmacien, surtout en cas de traitement.', 'Vitamins and supplements, best chosen with a pharmacist, especially if you take medication.')],
                    ['pharmacie-rayon-orthopedie', 'accessibility', $T('Orthopédie et maintien', 'Orthopedics and support'), $T('Bas de contention, genouillères, ceintures lombaires, avec prise de mesures.', 'Compression stockings, knee braces, back supports, with fitting.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Conseil beauté', 'Skincare advice'),
                    'title' => $T('Un diagnostic de peau offert', 'A free skin assessment'),
                    'html' => '<p>'.e($T('Notre conseillère en dermocosmétique vous reçoit sur rendez-vous pour analyser votre peau et vous proposer une routine simple. En cas de problème persistant, elle vous recommandera de consulter un dermatologue.', 'Our skincare adviser sees you by appointment to assess your skin and suggest a simple routine. For persistent problems, she will recommend that you see a dermatologist.')).'</p>',
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact]],
                    'photo' => ['pharmacie-conseil-beaute', 'sparkles', $T('Conseillère présentant des soins pour la peau à une cliente en parapharmacie', 'Adviser showing skincare products to a customer in a pharmacy beauty aisle')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::features($theme, ['cols' => 3, 'items' => [
                    ['gift', $T('Carte de fidélité', 'Loyalty card'), $T('Des avantages sur la parapharmacie à chaque passage.', 'Savings on health and beauty with every visit.')],
                    ['shopping-bag', $T('Commande et retrait', 'Order and collect'), $T('Réservez un produit par téléphone, il vous attend au comptoir.', 'Reserve a product by phone and it waits for you at the counter.')],
                    ['tag', $T('Offres du mois', 'Monthly offers'), $T('Une sélection de produits à prix réduit, renouvelée chaque mois.', 'A selection of discounted products, renewed every month.')],
                ]]),
                B::cta($theme, ['title' => $T('Besoin d’un conseil produit ?', 'Need product advice?'), 'text' => $T('Passez nous voir ou appelez-nous : nous vous aidons à choisir.', 'Drop by or call us: we will help you choose.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'vaccination', 'slug' => $u('vaccination-depistage', 'vaccination-and-testing'), 'title' => $T('Vaccination et dépistage', 'Vaccination and testing'),
            'description' => $T('Vaccination et tests rapides à la pharmacie, sur rendez-vous, par un pharmacien habilité, selon la réglementation en vigueur dans votre pays.', 'Vaccination and rapid tests at the pharmacy, by appointment, with an authorized pharmacist, as permitted by local regulations.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Vaccination et dépistage', 'Vaccination and testing'), 'title' => $T('La prévention, près de chez vous', 'Prevention, close to home'), 'intro' => $T('Les vaccins et tests proposés dépendent de la réglementation locale. Votre pharmacien vérifie toujours qu’ils vous conviennent.', 'The vaccines and tests offered depend on local regulations. Your pharmacist always checks that they are suitable for you.')]),
                B::features($theme, ['eyebrow' => $T('Ce que nous proposons', 'What we offer'), 'title' => $T('Vaccins et tests sur rendez-vous', 'Vaccines and tests by appointment'), 'items' => [
                    ['syringe', $T('Grippe saisonnière', 'Seasonal flu'), $T('Chaque automne, pour les adultes, en particulier les personnes à risque.', 'Every autumn, for adults, especially people at risk.')],
                    ['shield-check', $T('Rappels de vaccins', 'Booster shots'), $T('Vérification de votre carnet et rappels selon les recommandations officielles.', 'A check of your record and boosters according to official guidelines.')],
                    ['plane', $T('Conseils avant un voyage', 'Pre-travel advice'), $T('Nous vous orientons vers un centre de vaccinations internationales si nécessaire.', 'We refer you to an international vaccination center when needed.')],
                    ['thermometer', $T('Tests rapides', 'Rapid tests'), $T('Angine, grippe ou infections respiratoires : un résultat en quelques minutes.', 'Strep throat, flu or respiratory infections: a result in minutes.')],
                    ['droplet', $T('Dépistage du diabète', 'Diabetes screening'), $T('Une mesure de la glycémie lors de nos journées de prévention.', 'A blood sugar check during our prevention days.')],
                    ['heart-pulse', $T('Suivi cardiovasculaire', 'Heart health'), $T('Prise de tension et conseils, à transmettre à votre médecin.', 'Blood pressure check and advice, to share with your doctor.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Déroulé', 'What to expect'), 'title' => $T('Votre rendez-vous de vaccination', 'Your vaccination appointment'), 'bg' => 'light', 'items' => [
                    [$T('Rendez-vous', 'Booking'), $T('Réservez un créneau par téléphone ou avec le formulaire.', 'Book a slot by phone or with the form.')],
                    [$T('Questionnaire', 'Health check'), $T('Le pharmacien vérifie vos antécédents, vos traitements et vos allergies.', 'The pharmacist checks your history, treatments and allergies.')],
                    [$T('Vaccination', 'Vaccination'), $T('Injection réalisée dans l’espace de confidentialité.', 'The injection is given in the private room.')],
                    [$T('Surveillance', 'Observation'), $T('Quelques minutes de surveillance, puis le vaccin est noté dans votre carnet.', 'A few minutes of observation, then the vaccine is recorded in your record.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Bon à savoir', 'Good to know'),
                    'title' => $T('Quand faut-il consulter un médecin ?', 'When should you see a doctor?'),
                    'html' => '<p>'.e($T('Certaines situations demandent l’avis d’un médecin avant toute vaccination : grossesse, maladie chronique non stabilisée, traitement affaiblissant l’immunité ou réaction lors d’un vaccin précédent. Le pharmacien vous le signale et vous oriente.', 'Some situations require a doctor’s opinion before any vaccination: pregnancy, an unstable chronic illness, treatment that weakens immunity or a reaction to a previous vaccine. The pharmacist will tell you and refer you.')).'</p>',
                    'photo' => ['pharmacie-vaccination', 'syringe', $T('Pharmacien préparant une vaccination dans une salle de soin de pharmacie', 'Pharmacist preparing a vaccination in a pharmacy treatment room')],
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'bg' => 'light', 'items' => [
                    [$T('Dois-je apporter une ordonnance ?', 'Do I need a prescription?'), $T('Cela dépend du vaccin et de la réglementation de votre pays. Appelez-nous : nous vous dirons quoi apporter.', 'It depends on the vaccine and your country’s rules. Call us: we will tell you what to bring.')],
                    [$T('Un test rapide positif suffit-il pour être soigné ?', 'Is a positive rapid test enough to get treatment?'), $T('Le résultat est transmis à votre médecin, qui décide du traitement. Le pharmacien vous oriente selon le protocole en vigueur.', 'The result is shared with your doctor, who decides on treatment. The pharmacist refers you according to the current protocol.')],
                    [$T('Les enfants peuvent-ils être vaccinés à la pharmacie ?', 'Can children be vaccinated at the pharmacy?'), $T('Selon l’âge et la réglementation locale. Pour les plus jeunes, la vaccination se fait le plus souvent chez le médecin ou le pédiatre.', 'Depending on age and local rules. Young children are usually vaccinated by a doctor or pediatrician.')],
                ]]),
                B::cta($theme, ['title' => $T('Prenez rendez-vous', 'Book an appointment'), 'text' => $T('Un créneau de 15 minutes, sans attente, avec un pharmacien habilité.', 'A 15-minute slot, no waiting, with an authorized pharmacist.'), 'buttons' => [[$T('Réserver un créneau', 'Book a slot'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'hours', 'slug' => $u('horaires-gardes', 'opening-hours'), 'title' => $T('Horaires et gardes', 'Hours and on-duty'),
            'description' => $T('Horaires d’ouverture, pharmacies de garde du secteur, accès et stationnement, et numéros utiles en cas d’urgence.', 'Opening hours, on-duty pharmacies in the area, access and parking, and useful numbers in an emergency.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Horaires et gardes', 'Hours and on-duty'), 'title' => $T('Ouverts six jours sur sept', 'Open six days a week'), 'intro' => $T('Et, le dimanche ou la nuit, une pharmacie de garde est toujours ouverte près de chez vous.', 'And on Sundays or at night, an on-duty pharmacy is always open near you.')]),
                B::schedule($theme, ['groups' => $hoursGroups]),
                M::table($theme, ['eyebrow' => $T('Tour de garde', 'On-duty rota'), 'title' => $T('Pharmacies de garde du secteur', 'On-duty pharmacies in the area'), 'rows' => [
                    [$T('Période', 'Period'), $T('Pharmacie', 'Pharmacy'), $T('Adresse', 'Address'), $T('Téléphone', 'Phone')],
                    [$T('Semaine 1', 'Week 1'), $T('[Nom de la pharmacie]', '[Pharmacy name]'), $T('[Numéro et rue], [Ville]', '[Street address], [City]'), '+00 0 00 00 00 00'],
                    [$T('Semaine 2', 'Week 2'), $T('Notre pharmacie', 'Our pharmacy'), $T('[Numéro et rue], [Ville]', '[Street address], [City]'), '+00 0 00 00 00 00'],
                    [$T('Semaine 3', 'Week 3'), $T('[Nom de la pharmacie]', '[Pharmacy name]'), $T('[Numéro et rue], [Ville]', '[Street address], [City]'), '+00 0 00 00 00 00'],
                    [$T('Semaine 4', 'Week 4'), $T('[Nom de la pharmacie]', '[Pharmacy name]'), $T('[Numéro et rue], [Ville]', '[Street address], [City]'), '+00 0 00 00 00 00'],
                ], 'note' => $T('Tableau indicatif : vérifiez toujours la pharmacie de garde auprès du service officiel de votre ville avant de vous déplacer.', 'Indicative table: always check the on-duty pharmacy with your city’s official service before you travel.')]),
                B::pillars($theme, ['eyebrow' => $T('En cas d’urgence', 'In an emergency'), 'title' => $T('Les bons réflexes', 'What to do'), 'bg' => 'light', 'items' => [
                    ['ambulance', $T('Urgence vitale', 'Life-threatening emergency'), $T('Appelez immédiatement le numéro d’urgence de votre pays.', 'Call your country’s emergency number immediately.')],
                    ['triangle-alert', $T('Intoxication', 'Poisoning'), $T('Contactez le centre antipoison, gardez l’emballage du produit en cause.', 'Contact the poison control center and keep the product packaging.')],
                    ['stethoscope', $T('Médecin de garde', 'Out-of-hours doctor'), $T('La nuit et le week-end, joignez le service de garde médicale.', 'At night and at weekends, call the out-of-hours medical service.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Nous trouver', 'Find us'),
                    'title' => $T('Accès facile et stationnement', 'Easy access and parking'),
                    'html' => '<p>'.e($T('La pharmacie se trouve en rez-de-chaussée, avec une entrée de plain-pied. Deux places de stationnement minute sont réservées à nos patients devant l’officine, et un arrêt de bus se trouve à 50 mètres.', 'The pharmacy is on the ground floor with step-free access. Two short-stay parking spaces are reserved for our patients in front, and a bus stop is 50 meters away.')).'</p>',
                    'bullets' => [$T('Accès pour fauteuils roulants', 'Wheelchair access'), $T('Stationnement minute devant la pharmacie', 'Short-stay parking in front'), $T('Arrêt de bus à proximité', 'Bus stop nearby')],
                    'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']],
                    'photo' => ['pharmacie-facade', 'map-pin', $T('Devanture de pharmacie avec enseigne en croix verte lumineuse dans une rue commerçante', 'Pharmacy storefront with a lit green cross sign on a shopping street')],
                    'reverse' => true,
                ]),
            ]];

        $pages[] = ['key' => 'advice', 'slug' => $u('conseils-sante', 'health-advice'), 'title' => $T('Conseils santé', 'Health advice'),
            'description' => $T('Conseils de prévention de vos pharmaciens : bien utiliser ses médicaments, trousse de secours, soleil, saisons. Des informations générales, à compléter par un professionnel.', 'Prevention advice from your pharmacists: using medicines well, first-aid kits, sun, seasons. General information, to be completed by a professional.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Conseils santé', 'Health advice'), 'title' => $T('Mieux comprendre pour mieux se soigner', 'Understand more, care better'), 'intro' => $T('Ces articles donnent des informations générales : ils ne remplacent jamais l’avis de votre médecin ou de votre pharmacien.', 'These articles give general information: they never replace the advice of your doctor or pharmacist.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::events($theme, ['icon' => 'heart-pulse', 'eyebrow' => $T('Prévention', 'Prevention'), 'title' => $T('Nos journées de prévention', 'Our prevention days'), 'bg' => 'light', 'items' => [
                    [$T('Campagne de vaccination contre la grippe', 'Flu vaccination campaign'), $T('Octobre à décembre', 'October to December'), $T('Sur rendez-vous', 'By appointment')],
                    [$T('Journée de dépistage du diabète', 'Diabetes screening day'), $T('Mi-novembre, 9 h – 13 h', 'Mid-November, 9 am – 1 pm'), $T('Sans rendez-vous', 'No appointment needed')],
                    [$T('Atelier : bien utiliser son inhalateur', 'Workshop: using your inhaler correctly'), $T('Un mardi par mois, 17 h', 'One Tuesday a month, 5 pm'), $T('Espace de confidentialité', 'Private room')],
                    [$T('Conseils avant l’été : soleil et voyages', 'Before summer: sun and travel'), $T('Juin', 'June'), $T('À la pharmacie', 'At the pharmacy')],
                ]]),
                B::cta($theme, ['title' => $T('Une question sur votre traitement ?', 'A question about your treatment?'), 'text' => $T('Votre pharmacien vous répond au comptoir, par téléphone ou dans l’espace de confidentialité.', 'Your pharmacist answers at the counter, by phone or in the private room.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'accent'], [$T('Nos services', 'Our services'), $services, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Contactez la pharmacie, envoyez votre ordonnance ou prenez rendez-vous pour une vaccination ou un entretien pharmaceutique.', 'Contact the pharmacy, send your prescription or book a vaccination or medication review.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Nous sommes là pour vous', 'We are here for you'), 'intro' => $T('Pour une urgence, appelez le numéro d’urgence de votre pays : ce formulaire n’est pas lu en continu.', 'For an emergency, call your country’s emergency number: this form is not monitored around the clock.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Joignez une photo lisible de votre ordonnance ou précisez le service souhaité.', 'Attach a clear photo of your prescription or state the service you need.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 8 h 30 – 19 h 30 ; samedi, 9 h – 18 h', 'Monday – Friday, 8:30 am – 7:30 pm; Saturday, 9 am – 6 pm')],
                    ],
                    'social' => ['facebook', 'instagram', 'whatsapp'],
                    'subject' => true,
                    'button' => $T('Envoyer', 'Send'),
                    'bg' => 'white',
                ]),
                B::team($theme, $team + ['bg' => 'light']),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Avant de nous écrire', 'Before you write'), 'items' => [
                    [$T('Pouvez-vous me conseiller un médicament par message ?', 'Can you recommend a medicine by message?'), $T('Pour votre sécurité, nous préférons vous conseiller de vive voix, au comptoir ou par téléphone, après quelques questions sur votre situation.', 'For your safety, we prefer to advise you in person, at the counter or by phone, after a few questions about your situation.')],
                    [$T('Sous quel délai répondez-vous ?', 'How quickly do you reply?'), $T('Pendant les heures d’ouverture, en général dans l’heure. Les messages reçus le soir sont traités le lendemain matin.', 'During opening hours, usually within the hour. Messages received in the evening are handled the next morning.')],
                    [$T('Mes données de santé sont-elles protégées ?', 'Is my health data protected?'), $T('Oui, elles sont couvertes par le secret professionnel et ne servent qu’à la préparation de votre traitement.', 'Yes, they are covered by professional secrecy and used only to prepare your treatment.')],
                ]]),
            ]];

        $blog = [
            'base' => $u('conseils-sante', 'health-advice'),
            'title' => $T('Conseils santé', 'Health advice'),
            'categories' => ['medicaments' => $T('Bon usage des médicaments', 'Using medicines well'), 'prevention' => $T('Prévention', 'Prevention'), 'saisons' => $T('Au fil des saisons', 'Seasonal health')],
            'posts' => [
                ['key' => 'armoire', 'slug' => $u('ranger-son-armoire-a-pharmacie', 'tidying-your-medicine-cabinet'), 'category' => 'medicaments', 'tags' => [$T('Maison', 'Home')],
                    'title' => $T('Faire le tri dans son armoire à pharmacie', 'Sorting out your medicine cabinet'),
                    'excerpt' => $T('Dates de péremption, conservation, médicaments à rapporter : les bons gestes, deux fois par an.', 'Expiry dates, storage, medicines to bring back: good habits, twice a year.'),
                    'photo' => ['pharmacie-blog-armoire', 'box', $T('Armoire à pharmacie familiale rangée avec boîtes et pansements', 'Tidy family medicine cabinet with boxes and bandages')],
                    'heading' => $T('Les bons gestes', 'Good habits'),
                    'body' => [
                        $T('Une armoire à pharmacie mal rangée, c’est le risque de prendre un médicament périmé ou destiné à quelqu’un d’autre.', 'A cluttered medicine cabinet means a risk of taking an expired medicine or one meant for someone else.'),
                        $T('Conservez chaque médicament dans sa boîte d’origine avec sa notice, à l’abri de la chaleur et hors de portée des enfants. Ne réutilisez jamais un traitement prescrit pour une autre personne ou une autre maladie.', 'Keep each medicine in its original box with its leaflet, away from heat and out of children’s reach. Never reuse a treatment prescribed for another person or another illness.'),
                        $T('Rapportez à la pharmacie les médicaments périmés ou inutilisés : nous les éliminons en toute sécurité.', 'Bring expired or unused medicines back to the pharmacy: we dispose of them safely.'),
                    ]],
                ['key' => 'trousse', 'slug' => $u('trousse-de-secours', 'first-aid-kit'), 'category' => 'prevention', 'tags' => [$T('Premiers secours', 'First aid')],
                    'title' => $T('Que mettre dans une trousse de secours ?', 'What goes in a first-aid kit?'),
                    'excerpt' => $T('L’essentiel pour les petits bobos à la maison, en voiture ou en voyage.', 'The essentials for minor injuries at home, in the car or when traveling.'),
                    'photo' => ['pharmacie-blog-trousse', 'bandage', $T('Trousse de premiers secours ouverte avec pansements, compresses et désinfectant', 'Open first-aid kit with bandages, gauze and antiseptic')],
                    'quote' => $T('« Une trousse de secours sert aux petits soins : pour le reste, on appelle un professionnel. »', '“A first-aid kit is for minor care: for anything else, call a professional.”'),
                    'body' => [
                        $T('Pansements de plusieurs tailles, compresses stériles, désinfectant, bande, ciseaux, pince à écharde et thermomètre forment la base d’une bonne trousse.', 'Bandages in several sizes, sterile gauze, antiseptic, a roll bandage, scissors, tweezers and a thermometer form the basis of a good kit.'),
                        $T('Ajoutez la liste des numéros d’urgence et, pour chaque membre de la famille, ses traitements et allergies éventuels. Demandez conseil à votre pharmacien avant d’y ajouter des médicaments.', 'Add the list of emergency numbers and, for each family member, any treatments and allergies. Ask your pharmacist before adding any medicines.'),
                        $T('Vérifiez son contenu chaque année et, en cas de blessure sérieuse, consultez ou appelez les secours sans attendre.', 'Check its contents every year and, for a serious injury, see a doctor or call emergency services without delay.'),
                    ]],
                ['key' => 'soleil', 'slug' => $u('se-proteger-du-soleil', 'sun-protection'), 'category' => 'saisons', 'tags' => [$T('Été', 'Summer')],
                    'title' => $T('Se protéger du soleil, à tout âge', 'Sun protection at every age'),
                    'excerpt' => $T('Choisir sa crème, protéger les enfants, éviter les heures chaudes : nos conseils pour l’été.', 'Choosing sunscreen, protecting children, avoiding the hottest hours: our summer advice.'),
                    'photo' => ['pharmacie-blog-soleil', 'sun', $T('Parent appliquant de la crème solaire à un enfant à la plage', 'Parent applying sunscreen to a child at the beach')],
                    'heading' => $T('Nos conseils', 'Our advice'),
                    'body' => [
                        $T('Le soleil est bon pour le moral, mais une exposition excessive abîme la peau et les yeux, surtout chez les enfants.', 'Sunshine is good for the mood, but too much exposure damages skin and eyes, especially in children.'),
                        $T('Évitez les heures les plus chaudes, portez chapeau, lunettes et vêtements couvrants, et appliquez une protection adaptée à votre peau en renouvelant régulièrement l’application.', 'Avoid the hottest hours, wear a hat, sunglasses and covering clothes, and apply sunscreen suited to your skin, reapplying regularly.'),
                        $T('Certains médicaments rendent la peau plus sensible au soleil : demandez à votre pharmacien si c’est le cas de votre traitement. Un grain de beauté qui change doit être montré à un médecin.', 'Some medicines make skin more sensitive to the sun: ask your pharmacist whether yours does. A mole that changes should be shown to a doctor.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => 'Services', 'url' => $services],
                    ['label' => $T('Ordonnances', 'Prescriptions'), 'url' => $prescriptions],
                    ['label' => $T('Parapharmacie', 'Health and beauty'), 'url' => $shop],
                    ['label' => 'Vaccination', 'url' => $vaccination],
                    ['label' => $T('Horaires et gardes', 'Hours'), 'url' => $hours],
                    ['label' => $T('Conseils santé', 'Health advice'), 'url' => $advice],
                ],
                'cta_label' => $T('Envoyer une ordonnance', 'Send a prescription'),
                'cta_url' => $contact,
                'footer_text' => $T('Pharmacie de quartier : ordonnances, conseil, parapharmacie, vaccination et dépistage. En cas d’urgence, appelez le numéro d’urgence de votre pays.', 'Neighborhood pharmacy: prescriptions, advice, health and beauty, vaccination and testing. In an emergency, call your country’s emergency number.'),
                'footer_links' => [
                    ['label' => $T('Horaires et gardes', 'Hours and on-duty'), 'url' => $hours],
                    ['label' => $T('Ordonnances', 'Prescriptions'), 'url' => $prescriptions],
                    ['label' => $T('Conseils santé', 'Health advice'), 'url' => $advice],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
