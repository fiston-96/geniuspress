<?php

/*
 * Pack « Cabinet dentaire » (secteur Hôpitaux, cliniques et centres de santé) : 8 pages,
 * en français et en anglais. Soins et urgences, orthodontie, implants et esthétique,
 * première visite et tarifs indicatifs, le cabinet et l'équipe, conseils (blog).
 * Prudence : informations générales, aucun diagnostic ; chaque traitement est
 * décidé après examen par le praticien. Les textes sont des exemples à
 * personnaliser (l'assistant IA les réécrit à partir de la présentation du
 * cabinet). Les liens internes sont écrits avec les adresses de la langue de
 * la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\MediaBlocks as M;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Hôpitaux, cliniques et centres de santé', 'en' => 'Hospitals, clinics and health centers'],
        'icon' => 'smile',
        'keywords' => ['dentiste', 'cabinet dentaire', 'chirurgien-dentiste', 'orthodontie', 'implant', 'blanchiment', 'soins dentaires', 'urgence dentaire', 'dentist', 'dental clinic', 'dental practice', 'orthodontics', 'implants', 'teeth whitening', 'dental care'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $care = $u('/soins', '/treatments');
        $ortho = $u('/orthodontie', '/orthodontics');
        $implants = $u('/implants-esthetique', '/implants-and-cosmetic');
        $visit = $u('/premiere-visite', '/first-visit');
        $team = $u('/le-cabinet', '/our-practice');
        $advice = $u('/conseils', '/dental-tips');
        $contact = '/contact';

        $treatments = [
            ['shield-check', $T('Prévention et détartrage', 'Check-ups and cleaning'), $T('Bilan, détartrage et polissage pour garder des dents et des gencives saines.', 'Check-up, scaling and polishing to keep teeth and gums healthy.'), $care, $T('Nos soins', 'Our treatments')],
            ['sparkles', $T('Esthétique du sourire', 'Cosmetic dentistry'), $T('Blanchiment professionnel, facettes et couronnes en céramique.', 'Professional whitening, veneers and ceramic crowns.'), $implants, $T('Découvrir', 'Discover')],
            ['smile', $T('Orthodontie', 'Orthodontics'), $T('Appareils et gouttières transparentes, pour les enfants comme pour les adultes.', 'Braces and clear aligners, for children and adults alike.'), $ortho, $T('En savoir plus', 'Learn more')],
            ['layers', $T('Implants et prothèses', 'Implants and prosthetics'), $T('Remplacer une ou plusieurs dents de façon durable et naturelle.', 'Replacing one or more teeth in a lasting, natural-looking way.'), $implants, $T('En savoir plus', 'Learn more')],
            ['baby', $T('Soins des enfants', 'Children’s dentistry'), $T('Des rendez-vous adaptés pour que les enfants n’aient pas peur du dentiste.', 'Appointments designed so children are not afraid of the dentist.'), $care, $T('Nos soins', 'Our treatments')],
            ['triangle-alert', $T('Urgences dentaires', 'Dental emergencies'), $T('Créneaux réservés chaque jour pour les douleurs et les dents cassées.', 'Slots kept free every day for pain and broken teeth.'), $care, $T('Que faire ?', 'What to do?')],
        ];
        $testimonials = ['eyebrow' => $T('Avis de patients', 'Patient reviews'), 'title' => $T('Ils nous confient leur sourire', 'They trust us with their smile'), 'rating' => 5, 'items' => [
            [$T('Moi qui redoutais le dentiste, j’ai été mise en confiance dès le premier rendez-vous. Tout m’a été expliqué avant chaque geste.', 'I used to dread the dentist, but I felt at ease from the first appointment. Everything was explained before each step.'), '[Prénom Nom]', $T('Patiente', 'Patient')],
            [$T('Mon fils porte des gouttières depuis un an : suivi régulier, équipe patiente et résultats visibles.', 'My son has worn aligners for a year: regular follow-up, a patient team and visible results.'), '[Prénom Nom]', $T('Parent d’un patient', 'Parent of a patient')],
            [$T('Reçu le jour même pour une dent cassée un samedi matin. Merci pour la réactivité.', 'Seen the same day for a broken tooth on a Saturday morning. Thank you for the quick response.'), '[Prénom Nom]', $T('Patient', 'Patient')],
        ]];
        $cta = ['title' => $T('Prenez soin de votre sourire', 'Take care of your smile'), 'text' => $T('Nouveaux patients bienvenus : premier rendez-vous sous une semaine, devis écrit avant tout traitement.', 'New patients welcome: first appointment within a week, written estimate before any treatment.'),
            'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact, 'accent'], [$T('Votre première visite', 'Your first visit'), $visit, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Cabinet dentaire : soins, prévention, orthodontie, implants et esthétique pour toute la famille. Urgences reçues le jour même, rendez-vous en ligne.', 'Dental practice: treatments, prevention, orthodontics, implants and cosmetic care for the whole family. Same-day emergencies, online booking.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Cabinet dentaire', 'Dental practice'),
                    'title' => $T('Des soins dentaires attentifs, pour toute la famille', 'Gentle dental care for the whole family'),
                    'text' => $T('Une équipe de chirurgiens-dentistes qui prend le temps d’expliquer, des équipements modernes et des urgences reçues le jour même.', 'A team of dentists who take time to explain, modern equipment and same-day emergency appointments.'),
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact], [$T('Nos soins', 'Our treatments'), $care]],
                    'photo' => ['dentiste-accueil', 'smile', $T('Dentiste souriante montrant une radiographie dentaire à une patiente dans un cabinet lumineux', 'Smiling dentist showing a dental X-ray to a patient in a bright treatment room')],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos soins', 'Our treatments'), 'title' => $T('Tous les soins réunis en un seul lieu', 'All your dental care in one place'), 'items' => $treatments]),
                B::split($theme, [
                    'eyebrow' => $T('Le cabinet', 'The practice'),
                    'title' => $T('La technologie au service de votre confort', 'Technology for your comfort'),
                    'html' => '<p>'.e($T('Radiographie numérique à faible dose, empreintes optiques sans pâte, stérilisation contrôlée à chaque patient : nos équipements rendent les soins plus précis, plus rapides et plus confortables.', 'Low-dose digital X-rays, paste-free optical impressions, sterilization checked for every patient: our equipment makes treatment more precise, faster and more comfortable.')).'</p>',
                    'bullets' => [$T('Explications et devis avant chaque traitement', 'Explanations and estimate before every treatment'), $T('Anesthésie douce et écoute de l’anxiété', 'Gentle anesthesia and attention to anxiety'), $T('Cabinet accessible aux personnes à mobilité réduite', 'Wheelchair-accessible practice')],
                    'buttons' => [[$T('Découvrir l’équipe', 'Meet the team'), $team, 'outline']],
                    'photo' => ['dentiste-cabinet', 'stethoscope', $T('Salle de soins dentaires moderne avec fauteuil et écran', 'Modern dental treatment room with a chair and screen')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::stats($theme, ['bg' => 'primary', 'items' => [
                    [5, '', $T('praticiens', 'practitioners')],
                    [15, '', $T('ans d’expérience', 'years of experience')],
                    [24, ' h', $T('pour être reçu en urgence', 'to be seen in an emergency')],
                    [97, ' %', $T('de patients satisfaits', 'satisfied patients')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Nouveau patient ?', 'New patient?'), 'title' => $T('Votre premier rendez-vous', 'Your first appointment'), 'items' => [
                    [$T('Réservez', 'Book'), $T('En ligne ou par téléphone, un créneau d’une demi-heure.', 'Online or by phone, a half-hour slot.')],
                    [$T('Faites le point', 'Review'), $T('Antécédents, traitements, attentes : nous vous écoutons.', 'History, medication, expectations: we listen.')],
                    [$T('Examen', 'Examination'), $T('Bilan complet et radiographie si nécessaire.', 'A full check-up and X-ray if needed.')],
                    [$T('Plan de soins', 'Treatment plan'), $T('Des explications claires et un devis écrit.', 'Clear explanations and a written estimate.')],
                ]]),
                B::testimonials($theme, $testimonials),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'care', 'slug' => $u('soins', 'treatments'), 'title' => $T('Soins et urgences', 'Treatments and emergencies'),
            'description' => $T('Prévention, détartrage, caries, dévitalisation, gencives, soins des enfants et urgences dentaires : ce que nous faisons et comment réagir en cas de douleur.', 'Prevention, cleaning, fillings, root canals, gum care, children’s dentistry and dental emergencies: what we do and how to react to pain.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Soins et urgences', 'Treatments and emergencies'), 'title' => $T('Des soins précis, expliqués simplement', 'Precise care, simply explained'), 'intro' => $T('Chaque traitement est proposé après examen, avec ses avantages, ses alternatives et son coût.', 'Each treatment is proposed after examination, with its benefits, alternatives and cost.')]),
                B::features($theme, ['eyebrow' => $T('Soins courants', 'General dentistry'), 'title' => $T('Ce que nous soignons', 'What we treat'), 'items' => [
                    ['shield-check', $T('Bilan et détartrage', 'Check-up and cleaning'), $T('Une visite par an, ou plus selon l’état de vos gencives.', 'One visit a year, or more depending on your gums.')],
                    ['wrench', $T('Caries', 'Fillings'), $T('Soins conservateurs avec des matériaux de la couleur de la dent.', 'Conservative care with tooth-colored materials.')],
                    ['activity', $T('Dévitalisation', 'Root canal treatment'), $T('Traitement de la racine pour conserver une dent abîmée.', 'Treating the root to save a damaged tooth.')],
                    ['droplet', $T('Gencives', 'Gum care'), $T('Prise en charge des saignements et des maladies des gencives.', 'Treatment of bleeding gums and gum disease.')],
                    ['baby', $T('Enfants', 'Children'), $T('Premières visites, scellement des sillons et conseils aux parents.', 'First visits, fissure sealants and advice for parents.')],
                    ['scissors', $T('Extractions', 'Extractions'), $T('Dents de sagesse et dents non conservables, sous anesthésie locale.', 'Wisdom teeth and teeth that cannot be saved, under local anesthesia.')],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Urgences', 'Emergencies'),
                    'title' => $T('Une douleur ? Appelez-nous dès l’ouverture', 'In pain? Call us as soon as we open'),
                    'html' => '<p>'.e($T('Rage de dents, dent cassée, couronne descellée ou choc : des créneaux sont réservés chaque jour pour les urgences. En dehors des heures d’ouverture, contactez le service de garde dentaire de votre région.', 'Toothache, a broken tooth, a loose crown or an injury: slots are kept free every day for emergencies. Outside opening hours, contact your area’s on-call dental service.')).'</p><p>'.e($T('En cas de gonflement important du visage avec fièvre, ou de difficulté à avaler ou à respirer, appelez sans attendre le numéro d’urgence de votre pays.', 'If your face swells badly with fever, or you have difficulty swallowing or breathing, call your country’s emergency number immediately.')).'</p>',
                    'bullets' => [$T('Créneaux d’urgence chaque jour', 'Emergency slots every day'), $T('Soulagement de la douleur en priorité', 'Pain relief first'), $T('Soins définitifs planifiés ensuite', 'Definitive treatment planned afterwards')],
                    'buttons' => [[$T('Appeler le cabinet', 'Call the practice'), 'tel:+000000000000']],
                    'photo' => ['dentiste-urgence', 'triangle-alert', $T('Dentiste examinant un patient pour une douleur dentaire', 'Dentist examining a patient with tooth pain')],
                    'reverse' => true,
                    'bg' => 'light',
                ]),
                B::pillars($theme, ['eyebrow' => $T('Prévention', 'Prevention'), 'title' => $T('Trois habitudes qui changent tout', 'Three habits that make all the difference'), 'items' => [
                    ['clock', $T('Deux brossages par jour', 'Brush twice a day'), $T('Deux minutes, matin et soir, avec une brosse souple.', 'Two minutes, morning and evening, with a soft brush.')],
                    ['calendar-check', $T('Un contrôle par an', 'A yearly check-up'), $T('Pour traiter les problèmes avant qu’ils fassent mal.', 'To treat problems before they hurt.')],
                    ['leaf', $T('Moins de sucre', 'Less sugar'), $T('Surtout entre les repas et dans les boissons.', 'Especially between meals and in drinks.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'bg' => 'light', 'intro' => $T('Ces réponses sont générales : seul un examen permet de poser un diagnostic.', 'These answers are general: only an examination can provide a diagnosis.'), 'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact, 'outline']], 'items' => [
                    [$T('Les soins sont-ils douloureux ?', 'Do treatments hurt?'), $T('La plupart des soins se font sous anesthésie locale. Dites-nous si vous êtes anxieux : nous adaptons le rythme et faisons des pauses.', 'Most treatments are done under local anesthesia. Tell us if you are anxious: we adapt the pace and take breaks.')],
                    [$T('Mes gencives saignent, est-ce grave ?', 'My gums bleed, is it serious?'), $T('C’est souvent le signe d’une inflammation qu’il faut faire examiner. Prenez rendez-vous pour un bilan.', 'It is often a sign of inflammation that should be checked. Book a check-up.')],
                    [$T('Puis-je être soigné pendant une grossesse ?', 'Can I be treated during pregnancy?'), $T('Oui, de nombreux soins sont possibles. Signalez-nous votre grossesse : le praticien adapte les soins et les examens.', 'Yes, many treatments are possible. Tell us you are pregnant: the dentist will adapt treatments and examinations.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'ortho', 'slug' => $u('orthodontie', 'orthodontics'), 'title' => $T('Orthodontie', 'Orthodontics'),
            'description' => $T('Orthodontie pour enfants, adolescents et adultes : appareils, gouttières transparentes, déroulé du traitement et contention.', 'Orthodontics for children, teenagers and adults: braces, clear aligners, treatment steps and retention.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Orthodontie', 'Orthodontics'), 'title' => $T('Des dents alignées, à tout âge', 'Straight teeth at any age'), 'intro' => $T('Une meilleure mastication, un brossage plus facile et un sourire plus harmonieux.', 'Better chewing, easier brushing and a more harmonious smile.')]),
                B::split($theme, [
                    'eyebrow' => $T('Enfants et adultes', 'Children and adults'),
                    'title' => $T('Il n’est jamais trop tard', 'It is never too late'),
                    'html' => '<p>'.e($T('Un premier bilan est conseillé vers 7 ans pour repérer les problèmes de croissance des mâchoires. Chez l’adulte, les gouttières transparentes permettent un traitement discret, compatible avec la vie professionnelle.', 'A first assessment is recommended around age 7 to spot jaw growth problems. For adults, clear aligners allow discreet treatment that fits with working life.')).'</p>',
                    'bullets' => [$T('Bilan orthodontique complet', 'Full orthodontic assessment'), $T('Simulation du résultat sur écran', 'On-screen simulation of the result'), $T('Rendez-vous de suivi réguliers', 'Regular follow-up appointments')],
                    'photo' => ['dentiste-orthodontie', 'smile', $T('Adolescente souriante portant un appareil dentaire', 'Smiling teenager wearing dental braces')],
                ]),
                B::cards($theme, ['eyebrow' => $T('Les solutions', 'The options'), 'title' => $T('Quel appareil pour vous ?', 'Which appliance is right for you?'), 'bg' => 'light', 'items' => [
                    ['dentiste-appareil-fixe', 'wrench', $T('Appareil fixe à bagues céramique', 'Fixed braces with ceramic brackets'), $T('Le plus polyvalent, avec des bagues de la couleur des dents pour plus de discrétion.', 'The most versatile, with tooth-colored brackets for greater discretion.')],
                    ['dentiste-gouttieres', 'sparkles', $T('Gouttières transparentes', 'Clear aligners'), $T('Presque invisibles et amovibles, portées environ 22 heures par jour.', 'Almost invisible and removable, worn about 22 hours a day.')],
                    ['dentiste-appareil-enfant', 'baby', $T('Appareil amovible pour enfant', 'Removable appliance for children'), $T('Pour guider la croissance des mâchoires chez les plus jeunes.', 'To guide jaw growth in younger children.')],
                ]]),
                B::steps($theme, ['eyebrow' => $T('Le traitement', 'The treatment'), 'title' => $T('Quatre étapes vers un sourire aligné', 'Four steps to a straighter smile'), 'items' => [
                    [$T('Bilan', 'Assessment'), $T('Examen, photos, radiographies et empreintes numériques.', 'Examination, photos, X-rays and digital impressions.')],
                    [$T('Plan de traitement', 'Treatment plan'), $T('Durée estimée, solution choisie et devis détaillé.', 'Estimated duration, chosen option and detailed estimate.')],
                    [$T('Traitement', 'Treatment'), $T('Un contrôle toutes les quatre à huit semaines.', 'A check-up every four to eight weeks.')],
                    [$T('Contention', 'Retention'), $T('Un fil discret ou une gouttière de nuit pour stabiliser le résultat.', 'A discreet wire or night retainer to keep the result stable.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Vos questions sur l’orthodontie', 'Your questions about orthodontics'), 'bg' => 'light', 'items' => [
                    [$T('Combien de temps dure un traitement ?', 'How long does treatment take?'), $T('Cela dépend de chaque situation : souvent entre 12 et 30 mois. La durée estimée vous est donnée après le bilan.', 'It depends on each case: often between 12 and 30 months. The estimated duration is given after the assessment.')],
                    [$T('Les gouttières conviennent-elles à tout le monde ?', 'Are aligners suitable for everyone?'), $T('Non, certains cas nécessitent un appareil fixe. Le praticien vous indique la solution adaptée après examen.', 'No, some cases require fixed braces. The dentist tells you the right option after examination.')],
                    [$T('Faut-il porter une contention après ?', 'Do I need a retainer afterwards?'), $T('Oui, les dents ont tendance à bouger à nouveau. La contention protège le résultat obtenu.', 'Yes, teeth tend to move again. A retainer protects the result.')],
                ]]),
                B::cta($theme, ['title' => $T('Un bilan orthodontique ?', 'An orthodontic assessment?'), 'text' => $T('Pour votre enfant ou pour vous : un premier rendez-vous permet de savoir si un traitement est utile.', 'For your child or yourself: a first appointment tells you whether treatment would help.'), 'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'implants', 'slug' => $u('implants-esthetique', 'implants-and-cosmetic'), 'title' => $T('Implants et esthétique', 'Implants and cosmetic'),
            'description' => $T('Implants dentaires, couronnes, bridges, blanchiment professionnel et facettes : retrouver un sourire complet et naturel, après un bilan détaillé.', 'Dental implants, crowns, bridges, professional whitening and veneers: regain a complete, natural smile after a detailed assessment.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Implants et esthétique', 'Implants and cosmetic'), 'title' => $T('Retrouver un sourire complet et naturel', 'Regain a complete, natural smile'), 'intro' => $T('Des solutions durables, proposées uniquement après un examen complet et un devis détaillé.', 'Lasting solutions, offered only after a full examination and a detailed estimate.')]),
                B::split($theme, [
                    'eyebrow' => $T('Implantologie', 'Implants'),
                    'title' => $T('Remplacer une dent manquante', 'Replacing a missing tooth'),
                    'html' => '<p>'.e($T('L’implant est une petite racine en titane placée dans l’os de la mâchoire, sur laquelle on fixe une couronne. Il remplace une dent sans toucher aux dents voisines. Sa faisabilité est évaluée lors d’un bilan avec imagerie.', 'An implant is a small titanium root placed in the jawbone, onto which a crown is fixed. It replaces a tooth without affecting neighboring teeth. Its feasibility is assessed during a check-up with imaging.')).'</p>',
                    'bullets' => [$T('Imagerie 3D pour planifier la pose', '3D imaging to plan placement'), $T('Pose sous anesthésie locale', 'Placement under local anesthesia'), $T('Couronne en céramique sur mesure', 'Custom ceramic crown')],
                    'photo' => ['dentiste-implant', 'layers', $T('Chirurgien-dentiste expliquant un modèle d’implant dentaire à un patient', 'Dentist explaining a dental implant model to a patient')],
                    'reverse' => true,
                ]),
                B::steps($theme, ['eyebrow' => $T('Les étapes', 'The steps'), 'title' => $T('Le parcours d’un implant', 'The implant journey'), 'bg' => 'light', 'items' => [
                    [$T('Bilan', 'Assessment'), $T('Examen, imagerie et étude de votre santé générale.', 'Examination, imaging and review of your general health.')],
                    [$T('Pose', 'Placement'), $T('Intervention sous anesthésie locale, en une séance.', 'Procedure under local anesthesia, in one session.')],
                    [$T('Cicatrisation', 'Healing'), $T('L’os se solidarise avec l’implant en quelques mois.', 'The bone bonds with the implant over a few months.')],
                    [$T('Couronne', 'Crown'), $T('Pose de la dent définitive, puis contrôles réguliers.', 'Fitting of the final tooth, then regular check-ups.')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Esthétique', 'Cosmetic'), 'title' => $T('Embellir votre sourire', 'Enhancing your smile'), 'cols' => 4, 'items' => [
                    ['sun', $T('Blanchiment', 'Whitening'), $T('Un éclaircissement encadré par le praticien, au cabinet ou à domicile.', 'Supervised whitening, in the chair or at home.')],
                    ['gem', $T('Facettes', 'Veneers'), $T('De fines pellicules de céramique pour corriger forme et couleur.', 'Thin ceramic shells to correct shape and color.')],
                    ['award', $T('Couronnes céramique', 'Ceramic crowns'), $T('Pour protéger une dent fragile avec un rendu naturel.', 'To protect a weak tooth with a natural look.')],
                    ['layers', $T('Bridges et prothèses', 'Bridges and dentures'), $T('Des solutions fixes ou amovibles pour plusieurs dents.', 'Fixed or removable options for several teeth.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions fréquentes', 'Frequently asked questions'), 'items' => [
                    [$T('Tout le monde peut-il recevoir un implant ?', 'Can everyone have an implant?'), $T('Non. La qualité de l’os, certaines maladies, certains traitements ou le tabac peuvent compliquer la pose. Le bilan permet de le déterminer.', 'No. Bone quality, some illnesses, some treatments or smoking can make placement difficult. The assessment determines this.')],
                    [$T('Le blanchiment abîme-t-il les dents ?', 'Does whitening damage teeth?'), $T('Réalisé et suivi par un professionnel, il est sans danger pour l’émail. Évitez les produits vendus sans encadrement.', 'Carried out and monitored by a professional, it is safe for enamel. Avoid products sold without supervision.')],
                    [$T('Combien de temps dure un implant ?', 'How long does an implant last?'), $T('Avec une bonne hygiène et des contrôles réguliers, un implant peut durer de nombreuses années.', 'With good hygiene and regular check-ups, an implant can last many years.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'visit', 'slug' => $u('premiere-visite', 'first-visit'), 'title' => $T('Première visite et tarifs', 'First visit and fees'),
            'description' => $T('Déroulé du premier rendez-vous, documents à apporter et tarifs indicatifs des principaux soins. Devis écrit remis avant tout traitement.', 'How the first appointment works, documents to bring and indicative fees for the main treatments. Written estimate before any treatment.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Première visite', 'First visit'), 'title' => $T('Bienvenue au cabinet', 'Welcome to the practice'), 'intro' => $T('Un premier rendez-vous pour faire connaissance, faire le point et établir un plan de soins clair.', 'A first appointment to get to know you, review your situation and set out a clear treatment plan.')]),
                B::steps($theme, ['items' => [
                    [$T('Accueil', 'Welcome'), $T('Arrivez dix minutes en avance pour remplir votre dossier.', 'Arrive ten minutes early to fill in your file.')],
                    [$T('Questionnaire médical', 'Medical questionnaire'), $T('Vos antécédents, allergies et traitements en cours.', 'Your history, allergies and current medication.')],
                    [$T('Examen', 'Examination'), $T('Bilan des dents et des gencives, radiographie si nécessaire.', 'Check of teeth and gums, X-ray if needed.')],
                    [$T('Devis', 'Estimate'), $T('Un plan de soins et un devis écrit, sans obligation.', 'A treatment plan and written estimate, without obligation.')],
                ]]),
                B::schedule($theme, ['eyebrow' => $T('À apporter', 'What to bring'), 'title' => $T('Pour votre rendez-vous', 'For your appointment'), 'groups' => [
                    [$T('Documents', 'Documents'), [
                        ['id-card', $T('Pièce d’identité', 'Photo ID')],
                        ['credit-card', $T('Carte d’assurance santé ou de mutuelle', 'Health or dental insurance card')],
                        ['file-text', $T('Anciennes radiographies si vous en avez', 'Previous X-rays if you have them')],
                    ]],
                    [$T('Informations de santé', 'Health information'), [
                        ['pill', $T('Liste de vos médicaments en cours', 'List of your current medication')],
                        ['triangle-alert', $T('Allergies connues', 'Known allergies')],
                        ['heart-pulse', $T('Maladies ou traitements particuliers', 'Any illnesses or special treatments')],
                    ]],
                ]]),
                M::table($theme, ['eyebrow' => $T('Tarifs indicatifs', 'Indicative fees'), 'title' => $T('Nos principaux soins', 'Our main treatments'), 'bg' => 'white', 'rows' => [
                    [$T('Soin', 'Treatment'), $T('Tarif indicatif', 'Indicative fee')],
                    [$T('Consultation et bilan', 'Consultation and check-up'), $T('50 €', '$60')],
                    [$T('Détartrage et polissage', 'Scaling and polishing'), $T('70 €', '$90')],
                    [$T('Soin de carie', 'Filling'), $T('80 – 150 €', '$100 – $180')],
                    [$T('Dévitalisation', 'Root canal treatment'), $T('250 – 450 €', '$400 – $900')],
                    [$T('Couronne céramique', 'Ceramic crown'), $T('700 – 1 000 €', '$900 – $1,400')],
                    [$T('Implant (hors couronne)', 'Implant (crown not included)'), $T('1 000 – 1 400 €', '$1,500 – $2,200')],
                    [$T('Blanchiment professionnel', 'Professional whitening'), $T('350 – 500 €', '$400 – $650')],
                    [$T('Orthodontie par gouttières', 'Clear aligner treatment'), $T('à partir de 2 500 €', 'from $3,000')],
                ], 'note' => $T('Montants donnés à titre indicatif. Un devis écrit, adapté à votre situation, vous est remis avant tout traitement ; la prise en charge dépend de votre assurance.', 'Amounts are for guidance only. A written estimate tailored to your situation is given before any treatment; coverage depends on your insurance.')]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Paiement et remboursement', 'Payment and coverage'), 'bg' => 'light', 'items' => [
                    [$T('Comment puis-je payer ?', 'How can I pay?'), $T('Par carte bancaire, virement ou paiement mobile. Les traitements importants peuvent être réglés en plusieurs fois.', 'By card, bank transfer or mobile payment. Larger treatments can be paid in installments.')],
                    [$T('Mon assurance rembourse-t-elle les soins ?', 'Does my insurance cover treatment?'), $T('Cela dépend de votre contrat. Le devis vous permet de demander une estimation à votre assureur avant de commencer.', 'It depends on your plan. The estimate lets you ask your insurer for a quote before starting.')],
                    [$T('Que se passe-t-il si j’annule un rendez-vous ?', 'What if I cancel an appointment?'), $T('Merci de nous prévenir au moins 24 heures à l’avance pour libérer le créneau à un autre patient.', 'Please let us know at least 24 hours ahead so we can offer the slot to another patient.')],
                ]]),
                B::cta($theme, $cta),
            ]];

        $pages[] = ['key' => 'team', 'slug' => $u('le-cabinet', 'our-practice'), 'title' => $T('Le cabinet et l’équipe', 'Our practice and team'),
            'description' => $T('Chirurgiens-dentistes, orthodontiste, assistantes et équipe d’accueil : rencontrez l’équipe et découvrez nos équipements et notre protocole d’hygiène.', 'Dentists, orthodontist, dental assistants and front-desk team: meet the team and discover our equipment and hygiene protocol.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Le cabinet', 'Our practice'), 'title' => $T('Une équipe à l’écoute de chaque patient', 'A team that listens to every patient'), 'intro' => $T('Des praticiens complémentaires, formés régulièrement aux techniques actuelles.', 'Complementary practitioners, regularly trained in current techniques.')]),
                B::team($theme, ['eyebrow' => $T('Praticiens', 'Practitioners'), 'title' => $T('Les chirurgiens-dentistes', 'The dentists'), 'shape' => 'circle', 'items' => [
                    ['[Dr Prénom Nom]', $T('Chirurgien-dentiste, fondateur', 'Dentist, founder'), 'dentiste-equipe-1'],
                    ['[Dr Prénom Nom]', $T('Orthodontiste', 'Orthodontist'), 'dentiste-equipe-2'],
                    ['[Dr Prénom Nom]', $T('Implantologie et parodontologie', 'Implants and periodontics'), 'dentiste-equipe-3'],
                    ['[Dr Prénom Nom]', $T('Soins des enfants', 'Children’s dentistry'), 'dentiste-equipe-4'],
                ]]),
                B::team($theme, ['eyebrow' => $T('Équipe', 'Team'), 'title' => $T('Assistantes et accueil', 'Assistants and front desk'), 'shape' => 'circle', 'bg' => 'light', 'items' => [
                    ['[Prénom Nom]', $T('Assistante dentaire', 'Dental assistant'), 'dentiste-equipe-5'],
                    ['[Prénom Nom]', $T('Assistante dentaire', 'Dental assistant'), 'dentiste-equipe-6'],
                    ['[Prénom Nom]', $T('Secrétaire médicale', 'Medical receptionist'), 'dentiste-equipe-7'],
                ]]),
                B::split($theme, [
                    'eyebrow' => $T('Hygiène', 'Hygiene'),
                    'title' => $T('Un protocole d’hygiène rigoureux', 'A rigorous hygiene protocol'),
                    'html' => '<p>'.e($T('Chaque instrument est nettoyé, emballé et stérilisé après chaque patient, avec une traçabilité complète. Les surfaces sont désinfectées entre deux rendez-vous et le matériel à usage unique est privilégié.', 'Every instrument is cleaned, packed and sterilized after each patient, with full traceability. Surfaces are disinfected between appointments and single-use equipment is preferred.')).'</p><p>'.e($T('Cabinet inscrit sous le numéro [N° d’inscription].', 'Practice registered under number [Registration no.].')).'</p>',
                    'bullets' => [$T('Stérilisation contrôlée et tracée', 'Monitored, traceable sterilization'), $T('Radiographie numérique à faible dose', 'Low-dose digital X-rays'), $T('Formation continue de toute l’équipe', 'Ongoing training for the whole team')],
                    'photo' => ['dentiste-sterilisation', 'shield-check', $T('Assistante dentaire plaçant des instruments emballés dans un autoclave', 'Dental assistant placing packaged instruments in an autoclave')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Nos valeurs', 'Our values'), 'title' => $T('Ce qui compte pour nous', 'What matters to us'), 'bg' => 'light', 'items' => [
                    ['message-circle', $T('Expliquer', 'Explain'), $T('Chaque soin est présenté avant d’être réalisé.', 'Every treatment is explained before it is done.')],
                    ['heart', $T('Rassurer', 'Reassure'), $T('Un rythme adapté aux patients anxieux et aux enfants.', 'A pace adapted to anxious patients and children.')],
                    ['eye', $T('Être transparent', 'Be transparent'), $T('Des devis clairs, sans surprise sur la facture.', 'Clear estimates, no surprises on the bill.')],
                ]]),
            ]];

        $pages[] = ['key' => 'advice', 'slug' => $u('conseils', 'dental-tips'), 'title' => $T('Conseils', 'Dental tips'),
            'description' => $T('Conseils de l’équipe pour des dents et des gencives en bonne santé : brossage, enfants, alimentation et bons réflexes en cas d’urgence.', 'Advice from the team for healthy teeth and gums: brushing, children, diet and what to do in an emergency.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Conseils', 'Dental tips'), 'title' => $T('Prendre soin de ses dents au quotidien', 'Caring for your teeth every day'), 'intro' => $T('Des conseils généraux de notre équipe. Pour votre situation personnelle, parlez-en avec votre dentiste.', 'General advice from our team. For your own situation, talk to your dentist.')]),
                B::posts($theme, ['source' => 'current', 'count' => 9, 'more' => $T('Lire la suite', 'Read more')]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('La meilleure dent est celle qu’on n’a jamais eu besoin de soigner : la prévention reste notre premier soin.', 'The best tooth is the one that never needed treatment: prevention remains our first treatment.'), 'source' => $T('— [Dr Prénom Nom], chirurgien-dentiste', '— [Dr Prénom Nom], dentist')]),
                B::cta($theme, ['title' => $T('À quand remonte votre dernier contrôle ?', 'When was your last check-up?'), 'text' => $T('Un bilan par an suffit souvent à éviter les soins lourds.', 'A yearly check-up is often enough to avoid major treatment.'), 'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $contact, 'accent']]]),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Prenez rendez-vous au cabinet dentaire par téléphone ou en ligne. Urgences reçues le jour même pendant les heures d’ouverture.', 'Book an appointment at the dental practice by phone or online. Same-day emergencies during opening hours.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Prendre rendez-vous', 'Book an appointment'), 'intro' => $T('Pour une urgence, appelez directement le cabinet : le formulaire est traité dans la journée.', 'For an emergency, call the practice directly: the form is handled the same day.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Indiquez le motif de votre rendez-vous et vos disponibilités : nous vous rappelons pour confirmer.', 'Tell us the reason for your visit and when you are available: we will call you back to confirm.'),
                    'items' => [
                        ['map-pin', $T('[Numéro et rue], [Ville], [Pays]', '[Street address], [City], [Country]')],
                        ['phone', '+00 0 00 00 00 00', 'tel:+000000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['accessibility', $T('Cabinet de plain-pied, accessible en fauteuil', 'Step-free, wheelchair-accessible practice')],
                    ],
                    'social' => ['facebook', 'instagram', 'whatsapp'],
                    'subject' => true,
                    'button' => $T('Demander un rendez-vous', 'Request an appointment'),
                    'bg' => 'white',
                ]),
                B::schedule($theme, ['title' => $T('Horaires', 'Opening hours'), 'groups' => [
                    [$T('Consultations', 'Appointments'), [
                        ['clock', $T('Lundi – vendredi : 8 h 30 – 19 h', 'Monday – Friday: 8:30 am – 7 pm')],
                        ['clock', $T('Samedi : 9 h – 13 h', 'Saturday: 9 am – 1 pm')],
                        ['calendar-days', $T('Fermé le dimanche et les jours fériés', 'Closed on Sundays and public holidays')],
                    ]],
                    [$T('Urgences', 'Emergencies'), [
                        ['phone', $T('Pendant les horaires : appelez le cabinet dès l’ouverture', 'During opening hours: call the practice as soon as we open')],
                        ['moon', $T('En dehors des horaires : service de garde dentaire de votre région', 'Outside opening hours: your area’s on-call dental service')],
                        ['ambulance', $T('Gonflement important, fièvre, gêne respiratoire : numéro d’urgence', 'Severe swelling, fever, trouble breathing: emergency number')],
                    ]],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Avant votre rendez-vous', 'Before your appointment'), 'bg' => 'white', 'items' => [
                    [$T('Acceptez-vous les nouveaux patients ?', 'Do you accept new patients?'), $T('Oui, avec un premier rendez-vous proposé en général sous une semaine.', 'Yes, with a first appointment usually offered within a week.')],
                    [$T('Puis-je venir avec mon enfant ?', 'Can I bring my child?'), $T('Bien sûr. Nous recommandons une première visite dès l’apparition des premières dents de lait ou vers 2 à 3 ans.', 'Of course. We recommend a first visit once the first baby teeth appear or around age 2 to 3.')],
                    [$T('Le cabinet est-il accessible ?', 'Is the practice accessible?'), $T('Oui, le cabinet est de plain-pied et une place de stationnement adaptée se trouve devant l’entrée.', 'Yes, the practice is step-free and an accessible parking space is in front of the entrance.')],
                ]]),
            ]];

        $blog = [
            'base' => $u('conseils', 'dental-tips'),
            'title' => $T('Conseils', 'Dental tips'),
            'categories' => ['hygiene' => $T('Hygiène bucco-dentaire', 'Oral hygiene'), 'enfants' => $T('Enfants', 'Children'), 'urgences' => $T('Urgences', 'Emergencies')],
            'posts' => [
                ['key' => 'brossage', 'slug' => $u('bien-se-brosser-les-dents', 'brushing-your-teeth-properly'), 'category' => 'hygiene', 'tags' => [$T('Brossage', 'Brushing')],
                    'title' => $T('Bien se brosser les dents : les gestes qui comptent', 'Brushing properly: the habits that count'),
                    'excerpt' => $T('Durée, technique, brosse et fil dentaire : l’essentiel pour une hygiène efficace.', 'Duration, technique, toothbrush and floss: the essentials for effective hygiene.'),
                    'photo' => ['dentiste-blog-brossage', 'sparkles', $T('Brosse à dents, dentifrice et fil dentaire sur un lavabo blanc', 'Toothbrush, toothpaste and dental floss on a white sink')],
                    'heading' => $T('La bonne méthode', 'The right method'),
                    'body' => [
                        $T('Deux brossages de deux minutes par jour suffisent, à condition d’atteindre toutes les faces des dents et la limite des gencives.', 'Two two-minute brushings a day are enough, as long as you reach every surface of the teeth and the gum line.'),
                        $T('Choisissez une brosse souple, inclinez-la vers la gencive et faites de petits mouvements doux. Le fil dentaire ou les brossettes nettoient les espaces entre les dents, là où la brosse ne passe pas.', 'Choose a soft brush, angle it toward the gum and make small, gentle movements. Floss or interdental brushes clean between the teeth, where the brush cannot reach.'),
                        $T('Votre dentiste ou votre hygiéniste peut vous montrer la technique adaptée à votre bouche lors de votre prochain contrôle.', 'Your dentist or hygienist can show you the right technique for your mouth at your next check-up.'),
                    ]],
                ['key' => 'enfants', 'slug' => $u('premiere-visite-enfant', 'childs-first-dental-visit'), 'category' => 'enfants', 'tags' => [$T('Parents', 'Parents')],
                    'title' => $T('La première visite de votre enfant chez le dentiste', 'Your child’s first dental visit'),
                    'excerpt' => $T('À quel âge, comment la préparer et pourquoi elle compte pour la suite.', 'At what age, how to prepare and why it matters for the future.'),
                    'photo' => ['dentiste-blog-enfant', 'baby', $T('Jeune enfant souriant assis dans un fauteuil dentaire avec un parent à côté', 'Smiling young child in a dental chair with a parent alongside')],
                    'quote' => $T('« Une première visite sans soin, juste pour découvrir, change souvent tout le rapport de l’enfant au dentiste. »', '“A first visit with no treatment, just to explore, often changes a child’s whole relationship with the dentist.”'),
                    'body' => [
                        $T('Une première visite vers 2 ou 3 ans permet à l’enfant de découvrir le cabinet sans appréhension et au dentiste de vérifier que tout se développe bien.', 'A first visit around age 2 or 3 lets the child discover the practice without fear and lets the dentist check that everything is developing well.'),
                        $T('Présentez-la comme une sortie normale, sans évoquer la douleur. Le dentiste compte les dents, montre ses instruments et donne des conseils de brossage adaptés à l’âge.', 'Present it as an ordinary outing, without mentioning pain. The dentist counts the teeth, shows the instruments and gives age-appropriate brushing advice.'),
                        $T('Ensuite, un contrôle par an aide à repérer tôt les caries et les problèmes d’alignement.', 'After that, a yearly check-up helps spot cavities and alignment problems early.'),
                    ]],
                ['key' => 'urgence', 'slug' => $u('dent-cassee-que-faire', 'broken-tooth-what-to-do'), 'category' => 'urgences', 'tags' => [$T('Urgence', 'Emergency')],
                    'title' => $T('Dent cassée ou douleur soudaine : que faire ?', 'Broken tooth or sudden pain: what to do?'),
                    'excerpt' => $T('Les premiers réflexes en attendant d’être reçu par un dentiste.', 'First steps while you wait to see a dentist.'),
                    'photo' => ['dentiste-blog-urgence', 'phone', $T('Personne au téléphone tenant sa joue, appelant un cabinet dentaire', 'Person on the phone holding their cheek, calling a dental practice')],
                    'heading' => $T('Les bons réflexes', 'What to do'),
                    'body' => [
                        $T('Une dent cassée, une douleur intense ou un choc doivent être examinés rapidement. Appelez le cabinet dès l’ouverture ou, en dehors des horaires, le service de garde dentaire.', 'A broken tooth, severe pain or an injury should be examined quickly. Call the practice as soon as it opens or, outside opening hours, the on-call dental service.'),
                        $T('Conservez le fragment de dent s’il y en a un. Si une dent définitive est tombée après un choc, tenez-la par la couronne sans toucher la racine, placez-la dans un peu de lait et consultez immédiatement. Ne prenez aucun médicament sans l’avis d’un professionnel.', 'Keep the tooth fragment if there is one. If an adult tooth has been knocked out, hold it by the crown without touching the root, place it in a little milk and seek care immediately. Do not take any medicine without professional advice.'),
                        $T('En cas de gonflement important, de fièvre ou de difficulté à respirer ou à avaler, appelez sans attendre le numéro d’urgence de votre pays.', 'If there is severe swelling, fever or difficulty breathing or swallowing, call your country’s emergency number immediately.'),
                    ]],
            ],
        ];

        return [
            'pages' => $pages,
            'blog' => $blog,
            'navigation' => [
                'menu' => [
                    ['label' => $T('Soins', 'Treatments'), 'url' => $care],
                    ['label' => $T('Orthodontie', 'Orthodontics'), 'url' => $ortho],
                    ['label' => $T('Implants et esthétique', 'Implants and cosmetic'), 'url' => $implants],
                    ['label' => $T('Première visite', 'First visit'), 'url' => $visit],
                    ['label' => $T('Le cabinet', 'Our practice'), 'url' => $team],
                    ['label' => $T('Conseils', 'Tips'), 'url' => $advice],
                ],
                'cta_label' => $T('Prendre rendez-vous', 'Book an appointment'),
                'cta_url' => $contact,
                'footer_text' => $T('Cabinet dentaire : soins, prévention, orthodontie, implants et esthétique pour toute la famille. Urgences reçues le jour même.', 'Dental practice: treatments, prevention, orthodontics, implants and cosmetic care for the whole family. Same-day emergencies.'),
                'footer_links' => [
                    ['label' => $T('Soins et urgences', 'Treatments and emergencies'), 'url' => $care],
                    ['label' => $T('Tarifs indicatifs', 'Indicative fees'), 'url' => $visit],
                    ['label' => $T('Conseils', 'Dental tips'), 'url' => $advice],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
