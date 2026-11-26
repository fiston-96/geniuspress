<?php

/*
 * Pack « Agence événementielle et wedding planner » (secteur Événementiel et
 * photographie) : 8 pages, en français et en anglais. Mariages, événements
 * d'entreprise et séminaires, formules, réalisations, l'agence (déroulé, équipe,
 * partenaires prestataires), inspirations (blog).
 * Les textes sont des exemples réalistes à personnaliser (l'assistant IA les
 * réécrit à partir de la présentation de l'agence). Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Événementiel et photographie', 'en' => 'Events and photography'],
        'icon' => 'party-popper',
        'keywords' => ['événementiel', 'agence événementielle', 'wedding planner', 'organisatrice de mariage', 'mariage', 'séminaire', 'événement d’entreprise', 'gala', 'réception', 'décoration', 'event agency', 'event planner', 'wedding', 'corporate events', 'seminar', 'conference', 'party'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $cur = $T('€', '$');
        $weddings = $u('/mariages', '/weddings');
        $corporate = $u('/entreprises', '/corporate-events');
        $packages = $u('/formules', '/packages');
        $portfolio = $u('/realisations', '/portfolio');
        $agency = $u('/agence', '/about-us');
        $inspiration = $u('/inspirations', '/inspiration');
        $contact = '/contact';

        $testimonials = ['eyebrow' => $T('Ils nous ont fait confiance', 'They trusted us'), 'title' => $T('Des souvenirs, pas du stress', 'Memories, not stress'), 'items' => [
            [$T('Nous n’avons rien eu à gérer le jour J. Tout était fluide, nos invités parlent encore de la décoration.', 'We had nothing to handle on the day. Everything ran smoothly and our guests still talk about the decor.'), '[Prénom Nom]', $T('Mariée', 'Bride')],
            [$T('Un séminaire de 120 personnes organisé en six semaines, logistique impeccable et budget respecté.', 'A 120-person seminar organized in six weeks, flawless logistics and on budget.'), '[Prénom Nom]', $T('Directrice des ressources humaines, groupe industriel', 'HR director, industrial group')],
            [$T('Une équipe créative et à l’écoute, qui a su transformer notre lancement de produit en vrai spectacle.', 'A creative, attentive team that turned our product launch into a real show.'), '[Prénom Nom]', $T('Responsable marketing', 'Marketing manager')],
        ]];
        $cta = ['title' => $T('Votre événement commence ici', 'Your event starts here'), 'text' => $T('Racontez-nous votre projet, votre date et votre nombre d’invités : nous vous proposons un premier rendez-vous gratuit.', 'Tell us about your project, date and guest count: we will offer you a free first meeting.'),
            'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact, 'accent'], [$T('Voir nos formules', 'See our packages'), $packages, 'outline-light']]];
        $projects = [
            ['evenementiel-realisation-mariage-jardin', 'heart', $T('Mariage champêtre en plein air', 'Outdoor countryside wedding'), $T('150 invités, cérémonie laïque sous les arbres et dîner sous une tente illuminée.', '150 guests, an outdoor ceremony under the trees and dinner in a lit marquee.'), $portfolio],
            ['evenementiel-realisation-seminaire', 'presentation', $T('Séminaire de direction', 'Leadership retreat'), $T('Deux jours au vert pour 60 cadres : plénières, ateliers et soirée de gala.', 'Two days in the countryside for 60 managers: plenaries, workshops and a gala night.'), $portfolio],
            ['evenementiel-realisation-lancement', 'rocket', $T('Lancement de produit', 'Product launch'), $T('Scénographie, son et lumière pour 300 invités et une retransmission en direct.', 'Staging, sound and lighting for 300 guests and a live stream.'), $portfolio],
            ['evenementiel-realisation-gala', 'star', $T('Gala caritatif', 'Charity gala'), $T('Dîner assis de 400 couverts, vente aux enchères et spectacle.', '400-cover seated dinner, auction and live entertainment.'), $portfolio],
            ['evenementiel-realisation-anniversaire', 'cake', $T('Anniversaire surprise', 'Surprise birthday party'), $T('Une soirée privée sur un toit-terrasse, organisée dans le plus grand secret.', 'A private party on a rooftop terrace, planned in total secrecy.'), $portfolio],
            ['evenementiel-realisation-mariage-ville', 'gem', $T('Mariage élégant en ville', 'Elegant city wedding'), $T('Réception dans un hôtel particulier, décor floral blanc et doré.', 'Reception in a historic town house, white and gold floral design.'), $portfolio],
        ];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Agence événementielle et wedding planner : mariages, séminaires, soirées d’entreprise et réceptions privées organisés de A à Z.', 'Event agency and wedding planner: weddings, seminars, corporate parties and private receptions organized from start to finish.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Wedding planner · Événements d’entreprise', 'Wedding planner · Corporate events'),
                    'title' => $T('Des événements à votre image, sans le stress', 'Events that reflect you, without the stress'),
                    'text' => $T('Mariages, séminaires, galas ou fêtes privées : nous imaginons, coordonnons et faisons vivre vos événements, pour que vous profitiez pleinement du moment.', 'Weddings, seminars, galas or private parties: we design, coordinate and run your events so you can fully enjoy the moment.'),
                    'buttons' => [[$T('Demander un devis', 'Request a quote'), $contact], [$T('Nos réalisations', 'Our work'), $portfolio]],
                    'photo' => ['evenementiel-accueil', 'party-popper', $T('Salle de réception décorée avec des tables dressées, des fleurs et des guirlandes lumineuses', 'Reception hall decorated with set tables, flowers and string lights')],
                ]),
                B::features($theme, ['eyebrow' => $T('Ce que nous organisons', 'What we organize'), 'title' => $T('Chaque événement mérite son scénario', 'Every event deserves its own story'), 'items' => [
                    ['heart', $T('Mariages', 'Weddings'), $T('Organisation complète ou coordination du jour J, en ville, à la campagne ou à l’étranger.', 'Full planning or day-of coordination, in the city, the countryside or abroad.'), $weddings, $T('Découvrir', 'Discover')],
                    ['presentation', $T('Séminaires', 'Seminars'), $T('Lieux, hébergement, salles équipées et programme d’animations pour vos équipes.', 'Venues, accommodation, equipped rooms and team activities.'), $corporate, $T('Découvrir', 'Discover')],
                    ['briefcase', $T('Événements d’entreprise', 'Corporate events'), $T('Soirées annuelles, lancements de produit, conventions et inaugurations.', 'Annual parties, product launches, conventions and openings.'), $corporate, $T('Découvrir', 'Discover')],
                    ['cake', $T('Fêtes privées', 'Private parties'), $T('Anniversaires, fiançailles, baptêmes et cousinades, de 20 à 300 invités.', 'Birthdays, engagements, christenings and family reunions, from 20 to 300 guests.')],
                    ['star', $T('Galas et dîners', 'Galas and dinners'), $T('Dîners de prestige, soirées caritatives et remises de prix.', 'Prestige dinners, charity evenings and award ceremonies.')],
                    ['palette', $T('Décoration et scénographie', 'Decor and staging'), $T('Fleurs, mobilier, lumière et signalétique créés pour votre événement.', 'Flowers, furniture, lighting and signage designed for your event.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Notre approche', 'Our approach'),
                    'title' => $T('Un interlocuteur unique, du premier rendez-vous au dernier invité', 'One single contact, from the first meeting to the last guest'),
                    'html' => '<p>'.e($T('Votre chef de projet vous accompagne à chaque étape : il construit le budget avec vous, sélectionne les prestataires, négocie les contrats et dirige les équipes le jour J. Vous gardez les décisions, nous gérons tout le reste.', 'Your project manager supports you at every stage: building the budget with you, selecting suppliers, negotiating contracts and leading the teams on the day. You keep the decisions, we handle the rest.')).'</p>',
                    'bullets' => [$T('Budget détaillé et suivi en temps réel', 'Detailed budget and real-time tracking'), $T('Réseau de prestataires sélectionnés', 'Network of handpicked suppliers'), $T('Présence de l’équipe du montage au démontage', 'Team on site from setup to teardown')],
                    'buttons' => [[$T('Découvrir l’agence', 'About the agency'), $agency, 'outline']],
                    'photo' => ['evenementiel-chef-projet', 'clipboard-check', $T('Organisatrice d’événements vérifiant un plan de table avec un carnet', 'Event planner checking a seating plan with a notebook')],
                    'reverse' => true, 'bg' => 'light',
                ]),
                B::stats($theme, ['title' => $T('L’agence en chiffres', 'The agency in numbers'), 'items' => [
                    [350, '+', $T('événements organisés', 'events organized')],
                    [120, '+', $T('mariages célébrés', 'weddings celebrated')],
                    [80, '', $T('prestataires partenaires', 'partner suppliers')],
                    [12, '', $T('ans d’expérience', 'years of experience')],
                ]]),
                B::cards($theme, ['eyebrow' => $T('Réalisations', 'Our work'), 'title' => $T('Quelques événements récents', 'A few recent events'), 'more' => $T('Voir le projet', 'View project'), 'items' => array_slice($projects, 0, 3)]),
                B::steps($theme, ['eyebrow' => $T('Comment nous travaillons', 'How we work'), 'title' => $T('Quatre étapes vers le grand jour', 'Four steps to the big day'), 'bg' => 'light', 'items' => [
                    [$T('Rencontre', 'Meeting'), $T('Vos envies, votre date, votre budget : un premier échange gratuit.', 'Your wishes, date and budget: a free first conversation.')],
                    [$T('Conception', 'Design'), $T('Concept, planche d’ambiance, devis détaillé et sélection des lieux.', 'Concept, mood board, detailed quote and venue shortlist.')],
                    [$T('Préparation', 'Planning'), $T('Réservations, prestataires, planning et répétitions.', 'Bookings, suppliers, schedule and rehearsals.')],
                    [$T('Jour J', 'The day'), $T('Coordination sur place pour que vous profitiez de chaque instant.', 'On-site coordination so you can enjoy every moment.')],
                ]]),
                B::testimonials($theme, $testimonials + ['bg' => 'white']),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'weddings', 'slug' => $u('mariages', 'weddings'), 'title' => $T('Mariages', 'Weddings'),
            'description' => $T('Wedding planner : organisation complète ou coordination du jour J, recherche du lieu, décoration, traiteur et prestataires, mariage à l’étranger.', 'Wedding planner: full planning or day-of coordination, venue search, decor, catering and suppliers, and destination weddings abroad.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Mariages', 'Weddings'), 'title' => $T('Le mariage qui vous ressemble', 'The wedding that feels like you'), 'intro' => $T('Intime ou grandiose, en ville ou au bout du monde : nous orchestrons chaque détail pour que vous viviez pleinement votre journée.', 'Intimate or grand, in town or across the world: we orchestrate every detail so you can live your day to the full.')]),
                B::split($theme, [
                    'eyebrow' => $T('Wedding planning', 'Wedding planning'),
                    'title' => $T('De la demande à la dernière danse', 'From the proposal to the last dance'),
                    'html' => '<p>'.e($T('Nous commençons par vous écouter : votre histoire, vos traditions, vos goûts et ceux de vos familles. Nous construisons ensuite un projet cohérent, un budget réaliste et un calendrier serein, mois après mois.', 'We start by listening: your story, your traditions, your tastes and those of your families. Then we build a coherent plan, a realistic budget and a calm timeline, month after month.')).'</p>',
                    'bullets' => [$T('Cérémonies civiles, religieuses ou laïques', 'Civil, religious or celebrant-led ceremonies'), $T('Mariages multiculturels et bilingues', 'Multicultural and bilingual weddings'), $T('Mariages à l’étranger et week-ends complets', 'Destination weddings and full weekends')],
                    'photo' => ['evenementiel-mariage-ceremonie', 'heart', $T('Cérémonie de mariage en plein air avec une arche de fleurs et des invités assis', 'Outdoor wedding ceremony with a floral arch and seated guests')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos services', 'Our services'), 'title' => $T('Tout ce que nous prenons en charge', 'Everything we take care of'), 'bg' => 'light', 'items' => [
                    ['map-pin', $T('Recherche du lieu', 'Venue search'), $T('Visites, comparaison des devis et négociation des conditions.', 'Visits, quote comparison and negotiation of terms.')],
                    ['palette', $T('Décoration et fleurs', 'Decor and flowers'), $T('Thème, palette de couleurs, art floral et mise en lumière.', 'Theme, color palette, floral design and lighting.')],
                    ['utensils', $T('Traiteur et gâteau', 'Catering and cake'), $T('Sélection, dégustation et menu adapté à vos invités.', 'Selection, tasting and a menu suited to your guests.')],
                    ['camera', $T('Photo et vidéo', 'Photo and video'), $T('Photographes et vidéastes choisis selon votre style.', 'Photographers and videographers chosen for your style.')],
                    ['music', $T('Musique et animation', 'Music and entertainment'), $T('DJ, orchestre, musiciens pour la cérémonie et le cocktail.', 'DJ, band and musicians for the ceremony and cocktail hour.')],
                    ['users', $T('Gestion des invités', 'Guest management'), $T('Faire-part, réponses, plan de table, hébergement et navettes.', 'Invitations, RSVPs, seating plan, accommodation and shuttles.')],
                ]]),
                M::timeline($theme, ['eyebrow' => $T('Le jour J', 'The big day'), 'title' => $T('Une journée bien orchestrée', 'A well-orchestrated day'), 'items' => [
                    [$T('10 h', '10 am'), $T('Préparatifs', 'Getting ready'), $T('Coiffure, maquillage et photos des préparatifs, pendant que nous installons le lieu.', 'Hair, makeup and getting-ready photos while we set up the venue.')],
                    [$T('15 h', '3 pm'), $T('Cérémonie', 'Ceremony'), $T('Accueil des invités, placement et entrée des mariés, au bon tempo.', 'Guest welcome, seating and the couple’s entrance, perfectly timed.')],
                    [$T('16 h 30', '4:30 pm'), $T('Cocktail', 'Cocktail hour'), $T('Musique live, photos de groupe et animations pour les enfants.', 'Live music, group photos and activities for children.')],
                    [$T('19 h 30', '7:30 pm'), $T('Dîner', 'Dinner'), $T('Service coordonné avec le traiteur, discours et surprises.', 'Service coordinated with the caterer, speeches and surprises.')],
                    [$T('22 h', '10 pm'), $T('Soirée', 'Party'), $T('Ouverture du bal, gâteau et fête jusqu’au bout de la nuit.', 'First dance, cake and celebration late into the night.')],
                ]]),
                B::testimonials($theme, ['eyebrow' => $T('Ils se sont dit oui', 'They said yes'), 'title' => $T('Paroles de mariés', 'From our couples'), 'rating' => 5, 'bg' => 'light', 'items' => [
                    [$T('Une organisation parfaite et une équipe d’une grande douceur avec nos familles.', 'Perfect organization and a team that was so kind with our families.'), '[Prénom Nom]', $T('Marié', 'Groom')],
                    [$T('Notre mariage à l’étranger semblait impossible à organiser à distance. Ils l’ont rendu simple.', 'Our destination wedding seemed impossible to plan from afar. They made it simple.'), '[Prénom Nom]', $T('Mariée', 'Bride')],
                    [$T('Chaque détail nous ressemblait, jusqu’aux marque-places. Merci pour ce rêve.', 'Every detail was so us, down to the place cards. Thank you for this dream.'), '[Prénom Nom]', $T('Mariée', 'Bride')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur le mariage', 'Your wedding questions'), 'intro' => $T('Chaque mariage est unique : parlons du vôtre.', 'Every wedding is unique: let’s talk about yours.'), 'buttons' => [[$T('Prendre rendez-vous', 'Book a meeting'), $contact, 'outline']], 'items' => [
                    [$T('Combien de temps à l’avance faut-il vous contacter ?', 'How far ahead should we contact you?'), $T('Idéalement douze à dix-huit mois avant la date, surtout pour un samedi d’été. Pour une coordination du jour J, trois mois suffisent.', 'Ideally twelve to eighteen months before the date, especially for a summer Saturday. For day-of coordination, three months is enough.')],
                    [$T('Pouvez-vous travailler avec nos propres prestataires ?', 'Can you work with our own suppliers?'), $T('Oui. Nous coordonnons volontiers les prestataires que vous avez déjà choisis.', 'Yes. We are happy to coordinate suppliers you have already chosen.')],
                    [$T('Organisez-vous des mariages à l’étranger ?', 'Do you plan destination weddings?'), $T('Oui, avec des partenaires sur place, les formalités, l’hébergement et le transport des invités.', 'Yes, with local partners, paperwork, accommodation and guest transport.')],
                    [$T('Comment est calculé votre tarif ?', 'How are your fees calculated?'), $T('Selon la formule choisie, le nombre d’invités et la complexité du projet. Le devis détaille chaque poste.', 'Based on the package, the guest count and the project’s complexity. The quote itemizes everything.')],
                ]]),
            ]];

        $pages[] = ['key' => 'corporate', 'slug' => $u('entreprises', 'corporate-events'), 'title' => $T('Entreprises', 'Corporate events'),
            'description' => $T('Séminaires, conventions, soirées d’entreprise, lancements de produit et team building : des événements professionnels clés en main.', 'Seminars, conventions, company parties, product launches and team building: turnkey professional events for your teams and clients.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Événements d’entreprise', 'Corporate events'), 'title' => $T('Des événements qui servent vos objectifs', 'Events that serve your goals'), 'intro' => $T('Fédérer vos équipes, convaincre vos clients, marquer vos partenaires : chaque format est pensé pour un résultat.', 'Bringing teams together, winning clients, impressing partners: every format is designed for results.')]),
                B::features($theme, ['items' => [
                    ['presentation', $T('Séminaires et conventions', 'Seminars and conventions'), $T('Lieu, salles équipées, hébergement, restauration et programme sur un ou plusieurs jours.', 'Venue, equipped rooms, accommodation, catering and agenda over one or more days.')],
                    ['users-round', $T('Team building', 'Team building'), $T('Ateliers créatifs, défis sportifs, cuisine ou activités solidaires.', 'Creative workshops, sports challenges, cooking or volunteering activities.')],
                    ['party-popper', $T('Soirées d’entreprise', 'Company parties'), $T('Fêtes de fin d’année, anniversaires d’entreprise et remises de trophées.', 'End-of-year parties, company anniversaries and award nights.')],
                    ['rocket', $T('Lancements de produit', 'Product launches'), $T('Scénographie, son, lumière, accueil presse et retransmission en ligne.', 'Staging, sound, lighting, press welcome and live streaming.')],
                    ['store', $T('Salons et stands', 'Trade shows and booths'), $T('Conception du stand, logistique et animation de vos équipes sur place.', 'Booth design, logistics and support for your teams on site.')],
                    ['building-2', $T('Inaugurations', 'Openings'), $T('Coupure du ruban, visites guidées et réception pour vos invités.', 'Ribbon cutting, guided tours and a reception for your guests.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Séminaires clés en main', 'Turnkey seminars'),
                    'title' => $T('Vous préparez le contenu, nous gérons le reste', 'You prepare the content, we handle the rest'),
                    'html' => '<p>'.e($T('Inscriptions des participants, transport, hébergement, salles, technique et repas : un seul interlocuteur et un tableau de bord partagé pour suivre l’avancement et le budget.', 'Participant registration, transport, accommodation, rooms, AV and meals: one single contact and a shared dashboard to track progress and budget.')).'</p>',
                    'bullets' => [$T('Plateforme d’inscription en ligne', 'Online registration platform'), $T('Régie technique et captation vidéo', 'Technical management and video recording'), $T('Options éco-responsables', 'Eco-friendly options')],
                    'buttons' => [[$T('Voir les formules entreprise', 'See corporate packages'), $packages]],
                    'photo' => ['evenementiel-seminaire', 'presentation', $T('Salle de conférence avec scène, écran et participants assis en rangs', 'Conference room with a stage, a screen and seated attendees')],
                    'reverse' => true, 'bg' => 'light',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Ce que vous pouvez attendre de nous', 'What you can expect from us'), 'items' => [
                    ['wallet', $T('Budget maîtrisé', 'Controlled budget'), $T('Un devis détaillé et aucune dépense engagée sans votre accord.', 'A detailed quote and no spending without your approval.')],
                    ['clock', $T('Délais tenus', 'Deadlines met'), $T('Rétroplanning partagé et points réguliers jusqu’au jour J.', 'Shared schedule and regular check-ins up to the day.')],
                    ['recycle', $T('Événements responsables', 'Responsible events'), $T('Prestataires locaux, moins de déchets et compensation possible.', 'Local suppliers, less waste and optional offsetting.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions des entreprises', 'Questions from companies'), 'bg' => 'light', 'items' => [
                    [$T('Quel délai faut-il pour organiser un séminaire ?', 'How much lead time does a seminar need?'), $T('Comptez deux à quatre mois. Nous savons aussi organiser en quelques semaines selon les disponibilités des lieux.', 'Allow two to four months. We can also organize in a few weeks depending on venue availability.')],
                    [$T('Gérez-vous les événements hybrides ?', 'Do you handle hybrid events?'), $T('Oui : captation, diffusion en direct et interaction avec les participants à distance.', 'Yes: filming, live streaming and interaction with remote attendees.')],
                    [$T('Établissez-vous des factures pour notre comptabilité ?', 'Do you issue invoices for our accounts?'), $T('Oui, avec un acompte à la commande et un solde après l’événement, détaillés par poste.', 'Yes, with a deposit on order and the balance after the event, itemized by line.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'packages', 'slug' => $u('formules', 'packages'), 'title' => $T('Formules', 'Packages'),
            'description' => $T('Formules de wedding planning et d’événements d’entreprise : coordination du jour J, organisation partielle ou complète, prix de départ.', 'Wedding planning and corporate event packages: day-of coordination, partial or full planning, with clear starting prices.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Formules', 'Packages'), 'title' => $T('Un accompagnement à la carte', 'Support tailored to you'), 'intro' => $T('Honoraires de l’agence, hors prestataires (lieu, traiteur, décoration…), qui sont facturés à part et à prix négociés.', 'Agency fees, excluding suppliers (venue, catering, decor…), which are billed separately at negotiated rates.')]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Mariages', 'Weddings'), 'title' => $T('Formules mariage', 'Wedding packages'), 'intro' => $T('Prix de départ, ajustés selon le nombre d’invités et la complexité du projet.', 'Starting prices, adjusted to the guest count and the complexity of the project.'), 'items' => [
                    [$T('Coordination du jour J', 'Day-of coordination'), '1 200', $cur, $T('forfait', 'package'), $T('Vous avez tout préparé, nous orchestrons la journée.', 'You have planned it all, we run the day.'),
                        [$T('Deux rendez-vous de préparation', 'Two planning meetings'), $T('Planning détaillé du jour J', 'Detailed day-of schedule'), $T('Coordinatrice présente 12 h', 'Coordinator on site for 12 hours'), '-'.$T('Recherche des prestataires', 'Supplier search')], $T('Demander un devis', 'Request a quote')],
                    [$T('Organisation partielle', 'Partial planning'), '3 500', $cur, $T('forfait', 'package'), $T('Nous prenons le relais à mi-parcours.', 'We take over halfway through.'),
                        [$T('Sélection de 3 à 5 prestataires', 'Selection of 3 to 5 suppliers'), $T('Suivi du budget', 'Budget tracking'), $T('Plan de table et logistique invités', 'Seating plan and guest logistics'), $T('Coordination du jour J incluse', 'Day-of coordination included')], $T('Demander un devis', 'Request a quote'), true, $T('La plus choisie', 'Most chosen')],
                    [$T('Organisation complète', 'Full planning'), '7 500', $cur, $T('forfait', 'package'), $T('De la recherche du lieu au démontage.', 'From the venue search to teardown.'),
                        [$T('Concept et décoration sur mesure', 'Bespoke concept and decor'), $T('Tous les prestataires', 'All suppliers'), $T('Rendez-vous illimités', 'Unlimited meetings'), $T('Équipe de deux coordinateurs le jour J', 'Two coordinators on the day')], $T('Demander un devis', 'Request a quote')],
                ]]),
                B::pricing($theme, ['url' => $contact, 'eyebrow' => $T('Entreprises', 'Corporate'), 'title' => $T('Formules entreprise', 'Corporate packages'), 'bg' => 'white', 'items' => [
                    [$T('Journée d’étude', 'Day meeting'), '85', $cur, $T('/ participant', '/ attendee'), $T('Salle, technique et restauration.', 'Room, AV and catering.'),
                        [$T('Salle équipée', 'Equipped room'), $T('Deux pauses et déjeuner', 'Two breaks and lunch'), $T('Accueil et signalétique', 'Welcome desk and signage'), '-'.$T('Hébergement', 'Accommodation')], $T('Demander un devis', 'Request a quote')],
                    [$T('Séminaire résidentiel', 'Residential seminar'), '290', $cur, $T('/ participant / jour', '/ attendee / day'), $T('Tout compris, hébergement inclus.', 'All-inclusive, with accommodation.'),
                        [$T('Hébergement et repas', 'Accommodation and meals'), $T('Salles plénière et ateliers', 'Plenary and breakout rooms'), $T('Activité de team building', 'Team-building activity'), $T('Transport des participants', 'Attendee transport')], $T('Demander un devis', 'Request a quote'), true, $T('Clé en main', 'Turnkey')],
                    [$T('Soirée d’entreprise', 'Company party'), $T('Sur devis', 'Custom'), '', '', $T('Gala, fête annuelle ou lancement.', 'Gala, annual party or launch.'),
                        [$T('Concept et scénographie', 'Concept and staging'), $T('Traiteur et animation', 'Catering and entertainment'), $T('Son, lumière et régie', 'Sound, lighting and stage management'), $T('Coordination sur place', 'On-site coordination')], $T('Nous consulter', 'Contact us')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Paiement et conditions', 'Payment and terms'), 'bg' => 'light', 'items' => [
                    [$T('Comment se déroule le paiement ?', 'How does payment work?'), $T('Un acompte de 30 % à la signature, 40 % à mi-parcours et le solde un mois avant l’événement. Paiement par carte bancaire, virement ou paiement mobile.', 'A 30% deposit on signing, 40% midway and the balance one month before the event. Payment by card, bank transfer or mobile payment.')],
                    [$T('Vos honoraires comprennent-ils les prestataires ?', 'Do your fees include suppliers?'), $T('Non. Les prestataires vous facturent directement, aux tarifs que nous avons négociés pour vous.', 'No. Suppliers invoice you directly, at the rates we negotiated for you.')],
                    [$T('Que se passe-t-il en cas d’annulation ?', 'What happens if we cancel?'), $T('Les conditions sont précisées dans le contrat et dépendent de la date d’annulation. Nous vous conseillons une assurance annulation.', 'Terms are set out in the contract and depend on the cancellation date. We recommend cancellation insurance.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'portfolio', 'slug' => $u('realisations', 'portfolio'), 'title' => $T('Réalisations', 'Portfolio'),
            'description' => $T('Mariages, séminaires, lancements, galas et fêtes privées : découvrez en images les événements imaginés et coordonnés par l’agence.', 'Weddings, seminars, launches, galas and private parties: discover in pictures the events designed and coordinated by our agency.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Réalisations', 'Portfolio'), 'title' => $T('Des événements qui ont marqué les esprits', 'Events people still talk about'), 'intro' => $T('Une sélection de projets, présentés avec l’accord de nos clients.', 'A selection of projects, shared with our clients’ consent.')]),
                B::cards($theme, ['items' => $projects]),
                B::gallery($theme, ['eyebrow' => $T('En images', 'In pictures'), 'title' => $T('Détails et ambiances', 'Details and atmospheres'), 'bg' => 'light', 'photos' => [
                    ['evenementiel-galerie-table', 'utensils', $T('Table de réception dressée avec vaisselle dorée et bouquets de fleurs', 'Reception table set with gold tableware and flower bouquets')],
                    ['evenementiel-galerie-arche', 'heart', $T('Arche de fleurs blanches pour une cérémonie de mariage', 'White flower arch for a wedding ceremony')],
                    ['evenementiel-galerie-scene', 'presentation', $T('Scène éclairée pour une conférence d’entreprise', 'Lit stage for a corporate conference')],
                    ['evenementiel-galerie-cocktail', 'party-popper', $T('Invités discutant lors d’un cocktail en terrasse', 'Guests chatting at a terrace cocktail party')],
                    ['evenementiel-galerie-gateau', 'cake', $T('Pièce montée de mariage décorée de fleurs fraîches', 'Tiered wedding cake decorated with fresh flowers')],
                    ['evenementiel-galerie-lumieres', 'lightbulb', $T('Guirlandes lumineuses au-dessus d’une piste de danse en plein air', 'String lights over an outdoor dance floor')],
                ]]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [350, '+', $T('événements', 'events')],
                    [45000, '+', $T('invités accueillis', 'guests welcomed')],
                    [97, ' %', $T('de clients qui nous recommandent', 'of clients recommend us')],
                    [15, '', $T('pays d’intervention', 'countries')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'agency', 'slug' => $u('agence', 'about-us'), 'title' => $T('L’agence', 'About us'),
            'description' => $T('L’agence, notre méthode de travail, l’équipe de chefs de projet et notre réseau de prestataires partenaires sélectionnés avec soin.', 'Meet the agency: our working method, our team of project managers and our network of carefully selected partner suppliers.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('L’agence', 'About us'), 'title' => $T('Créateurs de moments, artisans du détail', 'Moment makers, detail crafters'), 'intro' => $T('Une équipe passionnée, un réseau de confiance et une méthode éprouvée.', 'A passionate team, a trusted network and a proven method.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Tout a commencé par un mariage', 'It all began with a wedding'),
                    'html' => '<p>'.e($T('Notre fondatrice a organisé le mariage de sa meilleure amie… puis celui de dix autres couples. L’agence est née de cette passion et s’est ouverte aux entreprises, avec la même exigence : que chaque invité vive un moment unique.', 'Our founder planned her best friend’s wedding… then ten more couples’ weddings. The agency grew out of that passion and opened up to companies, with the same standard: every guest should experience something special.')).'</p>',
                    'photo' => ['evenementiel-agence', 'users', $T('Équipe d’organisateurs d’événements autour d’une table avec des échantillons de tissus et de fleurs', 'Event planning team around a table with fabric and flower samples')],
                ]),
                B::steps($theme, ['eyebrow' => $T('Notre méthode', 'Our method'), 'title' => $T('Le déroulé d’un projet', 'How a project unfolds'), 'bg' => 'light', 'items' => [
                    [$T('Brief', 'Brief'), $T('Objectifs, invités, budget, contraintes : nous posons les bases ensemble.', 'Goals, guests, budget, constraints: we lay the foundations together.')],
                    [$T('Proposition', 'Proposal'), $T('Concept créatif, lieux possibles et devis détaillé sous dix jours.', 'Creative concept, venue options and a detailed quote within ten days.')],
                    [$T('Production', 'Production'), $T('Contrats, prestataires, rétroplanning et visites techniques.', 'Contracts, suppliers, schedule and site visits.')],
                    [$T('Bilan', 'Debrief'), $T('Après l’événement, un bilan, les photos et les retours des invités.', 'After the event, a debrief, the photos and guest feedback.')],
                ]]),
                B::team($theme, ['eyebrow' => $T('L’équipe', 'The team'), 'title' => $T('Vos chefs de projet', 'Your project managers'), 'items' => [
                    ['[Prénom Nom]', $T('Fondatrice, wedding planner', 'Founder, wedding planner'), 'evenementiel-equipe-1'],
                    ['[Prénom Nom]', $T('Chef de projet événements d’entreprise', 'Corporate events manager'), 'evenementiel-equipe-2'],
                    ['[Prénom Nom]', $T('Designer décoration et scénographie', 'Decor and staging designer'), 'evenementiel-equipe-3'],
                    ['[Prénom Nom]', $T('Coordinateur logistique', 'Logistics coordinator'), 'evenementiel-equipe-4'],
                ]]),
                B::features($theme, ['eyebrow' => $T('Partenaires prestataires', 'Partner suppliers'), 'title' => $T('Un réseau choisi avec exigence', 'A carefully chosen network'), 'intro' => $T('Nous travaillons avec des prestataires que nous avons vus à l’œuvre, et nous les évaluons après chaque événement.', 'We work with suppliers we have seen in action, and we review them after every event.'), 'bg' => 'light', 'items' => [
                    ['castle', $T('Lieux de réception', 'Venues'), $T('Domaines, hôtels, rooftops, salles atypiques et lieux en plein air.', 'Estates, hotels, rooftops, unusual spaces and outdoor venues.')],
                    ['chef-hat', $T('Traiteurs', 'Caterers'), $T('Cuisines du monde, buffets, dîners assis et options végétariennes.', 'World cuisines, buffets, seated dinners and vegetarian options.')],
                    ['flower-2', $T('Fleuristes', 'Florists'), $T('Art floral de saison, arches, centres de table et bouquets.', 'Seasonal floral design, arches, centerpieces and bouquets.')],
                    ['camera', $T('Photographes et vidéastes', 'Photographers and videographers'), $T('Reportage, portraits et films souvenirs.', 'Documentary coverage, portraits and keepsake films.')],
                    ['music', $T('Musique et animation', 'Music and entertainment'), $T('DJ, groupes, musiciens classiques et animateurs.', 'DJs, bands, classical musicians and hosts.')],
                    ['truck', $T('Matériel et technique', 'Equipment and AV'), $T('Mobilier, tentes, son, lumière et scènes.', 'Furniture, marquees, sound, lighting and stages.')],
                ]]),
                B::cta($theme, ['title' => $T('Vous êtes prestataire ?', 'Are you a supplier?'), 'text' => $T('Traiteur, fleuriste, musicien ou lieu de réception : présentez-nous votre travail pour rejoindre notre réseau.', 'Caterer, florist, musician or venue: show us your work to join our network.'), 'buttons' => [[$T('Devenir partenaire', 'Become a partner'), $contact, 'accent'], [$T('Nos réalisations', 'Our work'), $portfolio, 'outline-light']]]),
            ]];

        $pages[] = ['key' => 'inspiration', 'slug' => $u('inspirations', 'inspiration'), 'title' => $T('Inspirations', 'Inspiration'),
            'description' => $T('Idées de décoration, tendances mariage, conseils d’organisation et retours d’expérience pour réussir votre prochain événement.', 'Decor ideas, wedding trends, planning tips and lessons learned from the field to help your next event succeed in every detail.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Inspirations', 'Inspiration'), 'title' => $T('Idées et conseils pour vos événements', 'Ideas and tips for your events'), 'intro' => $T('Tendances, astuces d’organisation et coulisses de nos projets.', 'Trends, planning tips and behind the scenes of our projects.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire l’article', 'Read the article')]),
                B::events($theme, ['eyebrow' => $T('Rencontrons-nous', 'Let’s meet'), 'title' => $T('Prochains rendez-vous', 'Upcoming dates'), 'bg' => 'light', 'items' => [
                    [$T('Journée portes ouvertes de l’agence', 'Agency open day'), $T('Premier samedi du mois, 10 h – 17 h', 'First Saturday of the month, 10 am – 5 pm'), $T('Showroom de l’agence', 'Agency showroom')],
                    [$T('Salon du mariage', 'Wedding fair'), $T('Mi-janvier, tout le week-end', 'Mid-January, all weekend'), $T('Parc des expositions', 'Exhibition center')],
                    [$T('Atelier : budgéter son mariage', 'Workshop: budgeting your wedding'), $T('Un mardi par mois, 19 h', 'One Tuesday a month, 7 pm'), $T('En ligne', 'Online')],
                    [$T('Petit-déjeuner événements d’entreprise', 'Corporate events breakfast'), $T('Chaque trimestre, 8 h 30', 'Every quarter, 8:30 am'), $T('Showroom de l’agence', 'Agency showroom')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Demandez un devis pour votre mariage ou votre événement d’entreprise. Premier rendez-vous gratuit, réponse sous 48 heures ouvrées.', 'Request a quote for your wedding or corporate event. Free first meeting, reply within two business days, no commitment.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Parlons de votre événement', 'Let’s talk about your event'), 'intro' => $T('Date, lieu envisagé, nombre d’invités, budget : plus vous nous en dites, plus notre proposition sera précise.', 'Date, venue ideas, guest count, budget: the more you tell us, the more precise our proposal.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Rendez-vous à l’agence, en visio ou sur le lieu de votre événement.', 'Meet us at the agency, by video call or at your event venue.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Lundi – vendredi, 9 h – 18 h ; samedi sur rendez-vous', 'Monday – Friday, 9 am – 6 pm; Saturday by appointment')],
                    ],
                    'social' => ['instagram', 'pinterest', 'facebook', 'youtube'],
                    'subject' => true, 'button' => $T('Demander un devis', 'Request a quote'),
                    'bg' => 'white',
                ]),
                B::steps($theme, ['eyebrow' => $T('Et ensuite ?', 'What happens next?'), 'title' => $T('Après votre demande', 'After your request'), 'bg' => 'light', 'items' => [
                    [$T('Réponse', 'Reply'), $T('Nous revenons vers vous sous 48 heures ouvrées.', 'We get back to you within two business days.')],
                    [$T('Rendez-vous', 'Meeting'), $T('Un premier échange gratuit, à l’agence ou en visio.', 'A free first conversation, at the agency or by video.')],
                    [$T('Proposition', 'Proposal'), $T('Concept et devis détaillé, sans engagement.', 'Concept and detailed quote, no commitment.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Avant de nous écrire', 'Before you write'), 'items' => [
                    [$T('Le premier rendez-vous est-il payant ?', 'Is the first meeting charged?'), $T('Non, il est gratuit et sans engagement.', 'No, it is free and with no commitment.')],
                    [$T('Intervenez-vous en dehors de votre ville ?', 'Do you work outside your city?'), $T('Oui, partout dans le pays et à l’étranger. Les frais de déplacement sont indiqués dans le devis.', 'Yes, anywhere in the country and abroad. Travel costs are shown in the quote.')],
                    [$T('Mon budget est serré, pouvez-vous m’aider ?', 'My budget is tight. Can you help?'), $T('Oui : la coordination du jour J et nos conseils permettent souvent de faire des économies sur les prestataires.', 'Yes: day-of coordination and our advice often save money on suppliers.')],
                ]]),
            ]];

        $blog = [
            'base' => $u('inspirations', 'inspiration'),
            'title' => $T('Inspirations', 'Inspiration'),
            'categories' => ['mariage' => $T('Mariage', 'Weddings'), 'entreprise' => $T('Entreprise', 'Corporate'), 'tendances' => $T('Tendances', 'Trends')],
            'posts' => [
                ['key' => 'retroplanning', 'slug' => $u('retroplanning-mariage', 'wedding-planning-timeline'), 'category' => 'mariage', 'tags' => [$T('Organisation', 'Planning')],
                    'title' => $T('Le rétroplanning idéal de votre mariage', 'The ideal wedding planning timeline'),
                    'excerpt' => $T('De douze mois à la veille du jour J : ce qu’il faut prévoir, et quand.', 'From twelve months out to the day before: what to plan, and when.'),
                    'photo' => ['evenementiel-blog-retroplanning', 'calendar-check', $T('Carnet de mariage ouvert avec un calendrier, des échantillons et des fleurs séchées', 'Open wedding planner notebook with a calendar, samples and dried flowers')],
                    'heading' => $T('Les grandes étapes', 'The key milestones'),
                    'body' => [
                        $T('Douze mois avant : fixez le budget, la date, le nombre d’invités et réservez le lieu, puis le traiteur et le photographe.', 'Twelve months out: set the budget, date and guest count, and book the venue, then the caterer and photographer.'),
                        $T('Six mois avant : faire-part, tenue, décoration et musique. Trois mois avant : menu définitif, plan de table et essais coiffure et maquillage.', 'Six months out: invitations, outfits, decor and music. Three months out: final menu, seating plan and hair and makeup trials.'),
                        $T('La dernière semaine, déléguez : votre coordinatrice gère les confirmations et le planning minute par minute.', 'In the last week, delegate: your coordinator handles confirmations and the minute-by-minute schedule.'),
                    ]],
                ['key' => 'seminaire', 'slug' => $u('reussir-son-seminaire', 'running-a-great-offsite'), 'category' => 'entreprise', 'tags' => [$T('Séminaire', 'Offsite')],
                    'title' => $T('Cinq clés pour un séminaire réussi', 'Five keys to a successful offsite'),
                    'excerpt' => $T('Objectifs clairs, rythme, lieu inspirant : ce qui fait la différence.', 'Clear goals, pacing, an inspiring venue: what makes the difference.'),
                    'photo' => ['evenementiel-blog-seminaire', 'presentation', $T('Participants d’un séminaire en atelier autour de tableaux blancs', 'Seminar attendees in a workshop around whiteboards')],
                    'quote' => $T('« Un bon séminaire se juge à ce que les équipes en font le lundi suivant. »', '“A good offsite is judged by what teams do with it the following Monday.”'),
                    'body' => [
                        $T('Définissez un objectif principal, partagé avec les participants avant le départ : fédérer, décider, former ou célébrer.', 'Set one main goal and share it with attendees beforehand: bonding, deciding, training or celebrating.'),
                        $T('Alternez plénières courtes, ateliers en petits groupes et vrais temps de pause, à l’extérieur si possible.', 'Alternate short plenaries, small-group workshops and real breaks, outdoors if possible.'),
                        $T('Prévoyez un compte rendu et des actions concrètes : c’est ce qui transforme l’événement en résultat.', 'Plan a summary and concrete actions: that is what turns the event into results.'),
                    ]],
                ['key' => 'tendances', 'slug' => $u('tendances-deco', 'decor-trends'), 'category' => 'tendances', 'tags' => [$T('Décoration', 'Decor')],
                    'title' => $T('Les tendances déco de la saison', 'This season’s decor trends'),
                    'excerpt' => $T('Fleurs séchées, tables longues, lumières douces et matières naturelles.', 'Dried flowers, long tables, soft lighting and natural materials.'),
                    'photo' => ['evenementiel-blog-tendances', 'flower-2', $T('Longue table de banquet décorée de fleurs séchées et de bougies', 'Long banquet table decorated with dried flowers and candles')],
                    'heading' => $T('Ce que nous aimons', 'What we love'),
                    'body' => [
                        $T('Les grandes tablées remplacent les tables rondes : elles créent de la convivialité et mettent la décoration en valeur.', 'Long banquet tables are replacing round ones: they feel more convivial and show off the decor.'),
                        $T('Les fleurs séchées et de saison se marient aux bougies et au lin pour une ambiance chaleureuse et durable.', 'Dried and seasonal flowers pair with candles and linen for a warm, sustainable feel.'),
                        $T('Côté lumière, les guirlandes et lanternes transforment n’importe quel jardin en salle de bal.', 'For lighting, string lights and lanterns turn any garden into a ballroom.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Mariages', 'Weddings'), 'url' => $weddings],
                    ['label' => $T('Entreprises', 'Corporate'), 'url' => $corporate],
                    ['label' => $T('Formules', 'Packages'), 'url' => $packages],
                    ['label' => $T('Réalisations', 'Portfolio'), 'url' => $portfolio],
                    ['label' => $T('L’agence', 'About'), 'url' => $agency],
                    ['label' => $T('Inspirations', 'Inspiration'), 'url' => $inspiration],
                ],
                'cta_label' => $T('Demander un devis', 'Request a quote'),
                'cta_url' => $contact,
                'footer_text' => $T('Agence événementielle et wedding planner : mariages, séminaires, soirées d’entreprise et réceptions privées, organisés de A à Z.', 'Event agency and wedding planner: weddings, seminars, corporate parties and private receptions, organized from start to finish.'),
                'footer_links' => [
                    ['label' => $T('Mariages', 'Weddings'), 'url' => $weddings],
                    ['label' => $T('Événements d’entreprise', 'Corporate events'), 'url' => $corporate],
                    ['label' => $T('Devenir partenaire', 'Become a partner'), 'url' => $agency],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
