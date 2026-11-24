<?php

/*
 * Pack « Hôpitaux et cliniques » : 8 pages, en français et en anglais.
 * Les textes sont des exemples à personnaliser (l'assistant IA les réécrit à
 * partir de la présentation de l'établissement). Aucune promesse médicale :
 * les informations entre crochets sont à compléter. Les liens internes sont
 * écrits avec les adresses de la langue de la page, sans préfixe.
 */

use App\Templates\Blocks as B;
use App\Templates\Theme;

return [
    'sector' => [
        'name' => ['fr' => 'Hôpitaux et cliniques', 'en' => 'Hospitals and clinics'],
        'icon' => 'hospital',
        'keywords' => ['hôpital', 'hopital', 'clinique', 'centre de santé', 'santé', 'médecin', 'maternité', 'urgences', 'soins', 'hospital', 'clinic', 'health center', 'health', 'doctor', 'medical', 'emergency'],
    ],
    'build' => function (string $lang, Theme $theme, array $o): array {
        $T = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en;
        $u = fn (string $fr, string $en) => $lang === 'fr' ? $fr : $en; // adresses des pages
        $about = $u('/a-propos', '/about');
        $departments = $u('/services', '/departments');
        $doctors = $u('/medecins', '/doctors');
        $patients = $u('/patients', '/patients');
        $appointment = $u('/rendez-vous', '/appointments');
        $news = $u('/sante-prevention', '/health-tips');
        $contact = '/contact';

        $emergency = $T('[Numéro d’urgence]', '[Emergency number]');

        $deptItems = [
            ['stethoscope', $T('Médecine générale', 'General medicine'), $T('Consultations, suivi des maladies courantes et orientation vers les spécialistes.', 'Consultations, follow-up of common conditions and referral to specialists.')],
            ['baby', $T('Maternité', 'Maternity'), $T('Suivi de grossesse, accouchement et soins aux nouveau-nés dans un cadre rassurant.', 'Pregnancy follow-up, childbirth and newborn care in a reassuring setting.')],
            ['heart-pulse', $T('Pédiatrie', 'Pediatrics'), $T('Soins et suivi des enfants, de la naissance à l’adolescence.', 'Care and follow-up for children, from birth to adolescence.')],
            ['bandage', $T('Chirurgie', 'Surgery'), $T('Interventions programmées et prise en charge chirurgicale par une équipe expérimentée.', 'Scheduled procedures and surgical care by an experienced team.')],
            ['microscope', $T('Laboratoire', 'Laboratory'), $T('Analyses médicales sur prescription, avec des résultats remis dans la confidentialité.', 'Medical tests on prescription, with results delivered confidentially.')],
            ['activity', $T('Imagerie médicale', 'Medical imaging'), $T('Radiographie et échographie réalisées par des professionnels qualifiés.', 'X-ray and ultrasound carried out by qualified professionals.')],
        ];
        $stats = ['title' => $T('L’hôpital en quelques chiffres', 'The hospital at a glance'), 'items' => [
            [120, '', $T('lits d’hospitalisation', 'inpatient beds')],
            [45, '+', $T('médecins et spécialistes', 'doctors and specialists')],
            [24, '/7', $T('accueil des urgences', 'emergency reception')],
            [20, '', $T('années au service de la santé', 'years serving health')],
        ]];
        $apptCta = ['title' => $T('Besoin d’une consultation ?', 'Need a consultation?'), 'text' => $T('Prenez rendez-vous en quelques minutes avec le service de votre choix. Notre équipe vous répond dans les meilleurs délais.', 'Book an appointment in a few minutes with the department of your choice. Our team replies as soon as possible.'),
            'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $appointment, 'accent'], [$T('Nous contacter', 'Contact us'), $contact, 'outline-light']]];

        $pages = [];

        $pages[] = ['key' => 'home', 'home' => true, 'slug' => $u('accueil', 'home'), 'title' => $T('Accueil', 'Home'),
            'description' => $T('Des soins de qualité, humains et accessibles : urgences 24 h/24, maternité, pédiatrie, chirurgie et plus. Prenez rendez-vous en ligne.', 'Quality, caring and accessible health care: 24/7 emergencies, maternity, pediatrics, surgery and more. Book an appointment online.'),
            'sections' => [
                B::hero($theme, [
                    'eyebrow' => $T('Hôpital et centre de santé', 'Hospital and health center'),
                    'title' => $T('Votre santé, notre priorité', 'Your health, our priority'),
                    'text' => $T('Une équipe de professionnels à l’écoute, des services complets et un accueil respectueux pour toute la famille.', 'A listening team of professionals, complete services and a respectful welcome for the whole family.'),
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $appointment], [$T('Nos services', 'Our departments'), $departments]],
                    'photo' => ['hopital-accueil', 'hospital', $T('Entrée principale de l’hôpital avec le personnel d’accueil', 'Main hospital entrance with the reception staff')],
                ]),
                B::cta($theme, [
                    'tone' => 'dark',
                    'title' => $T('Urgences ouvertes 24 h/24, 7 j/7', 'Emergencies open 24/7'),
                    'text' => $T('En cas d’urgence, appelez le '.$emergency.' ou présentez-vous directement au service des urgences. Ne tardez pas.', 'In an emergency, call '.$emergency.' or come straight to the emergency department. Do not wait.'),
                    'buttons' => [[$T('Appeler les urgences', 'Call emergencies'), 'tel:+243000000000', 'accent'], [$T('Comment venir', 'How to find us'), $contact, 'outline-light']],
                ]),
                B::features($theme, ['eyebrow' => $T('Nos services', 'Our departments'), 'title' => $T('Des soins pour toute la famille', 'Care for the whole family'), 'cols' => 3, 'items' => $deptItems]),
                B::stats($theme, $stats),
                B::split($theme, [
                    'eyebrow' => $T('Qui sommes-nous', 'Who we are'),
                    'title' => $T('Un établissement proche de ses patients', 'A facility close to its patients'),
                    'html' => '<p>'.e($T('Notre hôpital accueille chaque jour des patients de tous âges. Nous mettons l’écoute, le respect et la sécurité des soins au cœur de notre travail, dans la confidentialité la plus stricte.', 'Our hospital welcomes patients of all ages every day. We place listening, respect and safe care at the heart of our work, with the strictest confidentiality.')).'</p>',
                    'bullets' => [$T('Équipes médicales et soignantes qualifiées', 'Qualified medical and nursing teams'), $T('Respect de la confidentialité des patients', 'Respect for patient confidentiality'), $T('Accueil attentif des familles', 'Attentive welcome for families')],
                    'buttons' => [[$T('Découvrir l’hôpital', 'Discover the hospital'), $about, 'outline']],
                    'photo' => ['hopital-equipe-soignante', 'users', $T('Équipe soignante au chevet d’un patient', 'Care team at a patient’s bedside')],
                    'reverse' => true,
                ]),
                B::testimonials($theme, ['eyebrow' => $T('Témoignages', 'Testimonials'), 'title' => $T('Ce que disent nos patients', 'What our patients say'), 'items' => [
                    [$T('J’ai été accueillie avec beaucoup de gentillesse et on m’a expliqué chaque étape de mon suivi.', 'I was welcomed very kindly and every step of my follow-up was explained to me.'), '[Prénom Nom]', $T('Patiente', 'Patient')],
                    [$T('Le personnel a su rassurer mon enfant. Nous avons apprécié le temps pris pour répondre à nos questions.', 'The staff reassured my child. We appreciated the time taken to answer our questions.'), '[Prénom Nom]', $T('Parent d’un patient', 'Parent of a patient')],
                    [$T('Des rendez-vous bien organisés et une équipe disponible. Je recommande cet établissement.', 'Well-organized appointments and an available team. I recommend this facility.'), '[Prénom Nom]', $T('Patient', 'Patient')],
                ]]),
                B::cta($theme, $apptCta),
            ]];

        $pages[] = ['key' => 'about', 'slug' => $u('a-propos', 'about'), 'title' => $T('À propos', 'About us'),
            'description' => $T('Notre histoire, notre mission, nos valeurs et nos engagements pour la qualité des soins et la sécurité des patients.', 'Our story, mission, values and commitments to quality of care and patient safety.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('À propos', 'About us'), 'title' => $T('Un hôpital au service de la communauté', 'A hospital serving the community'), 'intro' => $T('Des soins humains, accessibles et sûrs, pour chaque patient et chaque famille.', 'Humane, accessible and safe care for every patient and every family.')]),
                B::split($theme, [
                    'eyebrow' => $T('Notre histoire', 'Our story'),
                    'title' => $T('Du petit dispensaire à l’hôpital d’aujourd’hui', 'From a small dispensary to today’s hospital'),
                    'html' => '<p>'.e($T('Ouvert comme un modeste dispensaire de quartier, notre établissement s’est développé grâce à l’engagement de ses équipes et à la confiance des familles. Il propose aujourd’hui des services variés, de la médecine générale à la chirurgie.', 'Opened as a modest neighborhood dispensary, our facility grew thanks to the commitment of its teams and the trust of families. Today it offers a wide range of services, from general medicine to surgery.')).'</p><p>'.e($T('[Année de création] marque le début de cette aventure, que nous poursuivons avec la même exigence.', '[Year founded] marks the start of this journey, which we continue with the same standards.')).'</p>',
                    'photo' => ['hopital-histoire', 'building-2', $T('Façade historique de l’établissement', 'Historic front of the facility')],
                ]),
                B::pillars($theme, ['eyebrow' => $T('Mission et valeurs', 'Mission and values'), 'title' => $T('Ce qui guide nos équipes', 'What guides our teams'), 'bg' => 'light', 'items' => [
                    ['heart-handshake', $T('Humanité', 'Compassion'), $T('Chaque patient est accueilli avec respect, écoute et dignité.', 'Every patient is welcomed with respect, attention and dignity.')],
                    ['shield-check', $T('Sécurité', 'Safety'), $T('La sécurité des patients guide nos protocoles et nos pratiques.', 'Patient safety guides our protocols and practices.')],
                    ['award', $T('Excellence', 'Excellence'), $T('Formation continue et amélioration permanente de nos soins.', 'Ongoing training and continuous improvement of our care.')],
                ]]),
                B::features($theme, ['eyebrow' => $T('Nos engagements', 'Our commitments'), 'title' => $T('Qualité des soins et sécurité des patients', 'Quality of care and patient safety'), 'cols' => 3, 'items' => [
                    ['lock', $T('Confidentialité', 'Confidentiality'), $T('Votre dossier médical est protégé et n’est partagé qu’avec les professionnels qui vous soignent.', 'Your medical file is protected and shared only with the professionals caring for you.')],
                    ['droplet', $T('Hygiène', 'Hygiene'), $T('Des règles d’hygiène et de désinfection strictes dans tous nos services.', 'Strict hygiene and disinfection rules in all our departments.')],
                    ['clipboard-check', $T('Information claire', 'Clear information'), $T('Nous vous expliquons votre prise en charge afin que vous puissiez décider en connaissance de cause.', 'We explain your care so you can make informed decisions.')],
                ]]),
                B::quote($theme, ['bg' => 'light', 'text' => $T('Soigner, c’est d’abord écouter. Nous voulons que chaque personne qui franchit nos portes se sente en confiance.', 'To care is first to listen. We want everyone who walks through our doors to feel at ease.'), 'source' => $T('— [Dr Prénom Nom], directeur de l’hôpital', '— [Dr First Last], hospital director')]),
                B::cta($theme, $apptCta),
            ]];

        $pages[] = ['key' => 'departments', 'slug' => $u('services', 'departments'), 'title' => $T('Nos services', 'Our departments'),
            'description' => $T('Médecine générale, maternité, pédiatrie, chirurgie, laboratoire et imagerie : découvrez nos services médicaux.', 'General medicine, maternity, pediatrics, surgery, laboratory and imaging: discover our medical departments.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Services médicaux', 'Medical departments'), 'title' => $T('Des services complets, près de chez vous', 'Complete services, close to home'), 'intro' => $T('Chaque service est animé par une équipe qualifiée, dans le respect de la confidentialité.', 'Each department is run by a qualified team, with respect for confidentiality.')]),
                B::features($theme, ['cols' => 3, 'items' => $deptItems]),
                B::split($theme, [
                    'eyebrow' => $T('Service phare', 'Flagship service'),
                    'title' => $T('La maternité : accompagner chaque naissance', 'Maternity: supporting every birth'),
                    'html' => '<p>'.e($T('De la consultation prénatale au suivi du nouveau-né, nos sages-femmes et médecins vous accompagnent avec attention. En cas de signe inquiétant pendant la grossesse, rendez-vous sans attendre aux urgences.', 'From prenatal consultations to newborn follow-up, our midwives and doctors support you attentively. If you notice any worrying sign during pregnancy, come to the emergency department without delay.')).'</p>',
                    'bullets' => [$T('Consultations prénatales', 'Prenatal consultations'), $T('Salle d’accouchement et suivi après la naissance', 'Delivery room and postnatal follow-up'), $T('Conseils sur l’allaitement et les soins du bébé', 'Advice on breastfeeding and baby care')],
                    'buttons' => [[$T('Prendre rendez-vous', 'Book an appointment'), $appointment]],
                    'photo' => ['hopital-maternite', 'baby', $T('Sage-femme avec une jeune maman et son bébé', 'Midwife with a young mother and her baby')],
                    'bg' => 'light',
                ]),
                B::steps($theme, ['eyebrow' => $T('Votre parcours', 'Your journey'), 'title' => $T('De l’accueil au suivi', 'From reception to follow-up'), 'items' => [
                    [$T('Accueil', 'Reception'), $T('Enregistrement et orientation vers le bon service.', 'Registration and guidance to the right department.')],
                    [$T('Consultation', 'Consultation'), $T('Échange avec le médecin et examens si nécessaire.', 'Discussion with the doctor and tests if needed.')],
                    [$T('Soins', 'Treatment'), $T('Prise en charge adaptée à votre situation.', 'Care adapted to your situation.')],
                    [$T('Suivi', 'Follow-up'), $T('Conseils, ordonnance et rendez-vous de contrôle.', 'Advice, prescription and follow-up appointment.')],
                ]]),
            ]];

        $pages[] = ['key' => 'doctors', 'slug' => $u('medecins', 'doctors'), 'title' => $T('Nos médecins', 'Our doctors'),
            'description' => $T('Rencontrez les médecins et spécialistes de notre hôpital, à votre écoute pour vous soigner et vous conseiller.', 'Meet the doctors and specialists of our hospital, here to listen, treat and advise you.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Équipe médicale', 'Medical team'), 'title' => $T('Des professionnels à votre écoute', 'Professionals who listen'), 'intro' => $T('Médecins, sages-femmes et soignants travaillent ensemble pour votre santé.', 'Doctors, midwives and nurses work together for your health.')]),
                B::team($theme, ['title' => $T('Nos médecins et spécialistes', 'Our doctors and specialists'), 'shape' => 'circle', 'items' => [
                    ['[Dr Prénom Nom]', $T('Médecin généraliste', 'General practitioner'), 'hopital-medecin-1'],
                    ['[Dr Prénom Nom]', $T('Gynécologue-obstétricien', 'Obstetrician-gynecologist'), 'hopital-medecin-2'],
                    ['[Dr Prénom Nom]', $T('Pédiatre', 'Pediatrician'), 'hopital-medecin-3'],
                    ['[Dr Prénom Nom]', $T('Chirurgien', 'Surgeon'), 'hopital-medecin-4'],
                    ['[Dr Prénom Nom]', $T('Biologiste médical', 'Medical biologist'), 'hopital-medecin-5'],
                    ['[Dr Prénom Nom]', $T('Radiologue', 'Radiologist'), 'hopital-medecin-6'],
                    ['[Dr Prénom Nom]', $T('Médecin urgentiste', 'Emergency physician'), 'hopital-medecin-7'],
                    ['[Prénom Nom]', $T('Sage-femme en chef', 'Head midwife'), 'hopital-medecin-8'],
                ]]),
                B::cta($theme, [
                    'title' => $T('Rejoignez notre équipe', 'Join our team'),
                    'text' => $T('Médecins, infirmiers, sages-femmes, techniciens : nous recrutons des professionnels engagés. Envoyez-nous votre candidature.', 'Doctors, nurses, midwives, technicians: we are hiring committed professionals. Send us your application.'),
                    'buttons' => [[$T('Nous écrire', 'Write to us'), $contact, 'accent'], [$T('Nos services', 'Our departments'), $departments, 'outline-light']],
                ]),
            ]];

        $pages[] = ['key' => 'patients', 'slug' => $u('patients', 'patients'), 'title' => $T('Informations patients', 'Patient information'),
            'description' => $T('Horaires de visite, documents à apporter, assurance et paiement, droits des patients et confidentialité.', 'Visiting hours, what to bring, insurance and payment, patient rights and confidentiality.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Informations pratiques', 'Practical information'), 'title' => $T('Préparer votre venue', 'Preparing your visit'), 'intro' => $T('Horaires, documents, paiement et droits : tout ce qu’il faut savoir avant de venir.', 'Hours, documents, payment and rights: everything to know before you come.')]),
                B::schedule($theme, ['title' => $T('Horaires', 'Opening and visiting hours'), 'groups' => [
                    [$T('Horaires de visite', 'Visiting hours'), [
                        ['clock', $T('Tous les jours : [à compléter]', 'Every day: [to be completed]')],
                        ['users', $T('Deux visiteurs maximum par patient', 'Two visitors per patient at most')],
                        ['baby', $T('Visites d’enfants : selon le service', 'Visits by children: depending on the department')],
                    ]],
                    [$T('Ouverture des services', 'Department hours'), [
                        ['ambulance', $T('Urgences : 24 h/24, 7 j/7', 'Emergencies: 24/7')],
                        ['stethoscope', $T('Consultations : [à compléter]', 'Consultations: [to be completed]')],
                        ['microscope', $T('Laboratoire : [à compléter]', 'Laboratory: [to be completed]')],
                    ]],
                ]]),
                B::features($theme, ['eyebrow' => $T('Admission', 'Admission'), 'title' => $T('Que apporter ?', 'What to bring'), 'cols' => 3, 'items' => [
                    ['id-card', $T('Pièce d’identité', 'Identity document'), $T('Une pièce d’identité valide du patient.', 'A valid identity document for the patient.')],
                    ['file-text', $T('Documents médicaux', 'Medical documents'), $T('Ordonnances, résultats d’examens et carnet de santé ou de vaccination.', 'Prescriptions, test results and health or vaccination record.')],
                    ['credit-card', $T('Assurance ou moyen de paiement', 'Insurance or payment method'), $T('Carte d’assurance ou de mutuelle, si vous en avez une.', 'Insurance or health plan card, if you have one.')],
                ]]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Assurance et paiement', 'Insurance and payment'), 'intro' => $T('Une autre question ? Contactez notre accueil.', 'Another question? Contact our reception.'), 'buttons' => [[$T('Nous contacter', 'Contact us'), $contact, 'outline']], 'items' => [
                    [$T('Quelles assurances acceptez-vous ?', 'Which insurers do you accept?'), $T('La liste des assurances et mutuelles partenaires est [à compléter]. Renseignez-vous auprès de l’accueil.', 'The list of partner insurers and health plans is [to be completed]. Ask at reception.')],
                    [$T('Comment payer mes soins ?', 'How can I pay for my care?'), $T('Les modes de paiement acceptés sont [à compléter] : espèces, Mobile Money ou carte selon l’établissement.', 'Accepted payment methods are [to be completed]: cash, Mobile Money or card depending on the facility.')],
                    [$T('Puis-je obtenir un devis ?', 'Can I get an estimate?'), $T('Oui, l’accueil peut vous renseigner sur le coût prévisible d’une consultation ou d’un acte avant votre venue.', 'Yes, reception can tell you the expected cost of a consultation or procedure before you come.')],
                    [$T('Que faire en cas d’urgence ?', 'What should I do in an emergency?'), $T('Appelez le '.$emergency.' ou venez aux urgences, ouvertes jour et nuit. Les soins urgents ne doivent jamais être retardés.', 'Call '.$emergency.' or come to the emergency department, open day and night. Urgent care must never be delayed.')],
                ]]),
                B::pillars($theme, ['eyebrow' => $T('Vos droits', 'Your rights'), 'title' => $T('Droits des patients et confidentialité', 'Patient rights and confidentiality'), 'bg' => 'light', 'items' => [
                    ['heart-handshake', $T('Respect et dignité', 'Respect and dignity'), $T('Vous êtes traité avec respect, sans discrimination.', 'You are treated with respect, without discrimination.')],
                    ['file-text', $T('Information et consentement', 'Information and consent'), $T('Vous êtes informé de vos soins et vous donnez votre accord.', 'You are informed about your care and give your consent.')],
                    ['lock', $T('Secret médical', 'Medical confidentiality'), $T('Vos informations de santé restent strictement confidentielles.', 'Your health information remains strictly confidential.')],
                ]]),
            ]];

        $pages[] = ['key' => 'appointment', 'slug' => $u('rendez-vous', 'appointments'), 'title' => $T('Rendez-vous', 'Appointments'),
            'description' => $T('Prenez rendez-vous avec nos services : par formulaire, par téléphone ou à l’accueil. Simple, rapide et confidentiel.', 'Book an appointment with our departments: by form, phone or at reception. Simple, fast and confidential.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Rendez-vous', 'Appointments'), 'title' => $T('Prendre rendez-vous', 'Book an appointment'), 'intro' => $T('Pour une consultation non urgente. En cas d’urgence, appelez le '.$emergency.' ou rendez-vous aux urgences.', 'For a non-urgent consultation. In an emergency, call '.$emergency.' or come to the emergency department.')]),
                B::steps($theme, ['eyebrow' => $T('Comment ça marche', 'How it works'), 'title' => $T('Réserver en quatre étapes', 'Book in four steps'), 'items' => [
                    [$T('Choisissez un service', 'Choose a department'), $T('Médecine générale, maternité, pédiatrie ou autre.', 'General medicine, maternity, pediatrics or other.')],
                    [$T('Envoyez votre demande', 'Send your request'), $T('Remplissez le formulaire ou appelez-nous.', 'Fill in the form or call us.')],
                    [$T('Recevez la confirmation', 'Receive confirmation'), $T('Notre équipe vous confirme la date et l’heure.', 'Our team confirms the date and time.')],
                    [$T('Venez à votre rendez-vous', 'Come to your appointment'), $T('Apportez votre pièce d’identité et vos documents médicaux.', 'Bring your ID and your medical documents.')],
                ]]),
                B::contact($theme, [
                    'title' => $T('Demande de rendez-vous', 'Appointment request'),
                    'intro' => $T('Indiquez le service souhaité et vos disponibilités. Vos informations restent confidentielles.', 'Tell us the department you need and your availability. Your information stays confidential.'),
                    'items' => [
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Prise de rendez-vous : lundi – vendredi, [à compléter]', 'Booking desk: Monday – Friday, [to be completed]')],
                    ],
                    'subject' => true,
                    'button' => $T('Envoyer ma demande', 'Send my request'),
                    'bg' => 'light',
                ]),
                B::faq($theme, ['eyebrow' => 'FAQ', 'title' => $T('Questions sur les rendez-vous', 'Questions about appointments'), 'bg' => 'white', 'items' => [
                    [$T('Dois-je prendre rendez-vous pour venir ?', 'Do I need an appointment?'), $T('Pour les consultations, c’est recommandé. Les urgences, elles, sont ouvertes sans rendez-vous.', 'For consultations it is recommended. Emergencies are open without an appointment.')],
                    [$T('Puis-je annuler ou déplacer mon rendez-vous ?', 'Can I cancel or reschedule?'), $T('Oui, prévenez-nous le plus tôt possible par téléphone ou par courriel pour libérer le créneau.', 'Yes, let us know as early as possible by phone or email so the slot can be freed.')],
                    [$T('Combien coûte une consultation ?', 'How much does a consultation cost?'), $T('Le tarif dépend du service : [à compléter]. L’accueil vous renseigne volontiers.', 'The fee depends on the department: [to be completed]. Reception will gladly inform you.')],
                    [$T('Mes informations sont-elles protégées ?', 'Is my information protected?'), $T('Oui. Les données de votre demande ne sont utilisées que pour organiser votre prise en charge.', 'Yes. The data in your request is used only to organize your care.')],
                ]]),
            ]];

        $pages[] = ['key' => 'health-news', 'slug' => $u('sante-prevention', 'health-tips'), 'title' => $T('Santé et prévention', 'Health and prevention'),
            'description' => $T('Conseils de prévention, paludisme, vaccination, santé maternelle et hygiène, ainsi que nos campagnes et journées de dépistage.', 'Prevention tips on malaria, vaccination, maternal health and hygiene, plus our campaigns and screening days.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => $T('Santé et prévention', 'Health and prevention'), 'title' => $T('Mieux vaut prévenir', 'Prevention matters'), 'intro' => $T('Des conseils généraux pour protéger votre santé. Ils ne remplacent pas l’avis d’un médecin.', 'General advice to protect your health. It does not replace a doctor’s advice.')]),
                B::cards($theme, ['cols' => 2, 'items' => [
                    ['hopital-prevention-paludisme', 'thermometer', $T('Se protéger du paludisme', 'Protecting yourself from malaria'), $T('Moustiquaire, assainissement autour de la maison et consultation rapide en cas de fièvre.', 'Bed nets, a clean surrounding environment and prompt consultation in case of fever.')],
                    ['hopital-prevention-vaccination', 'syringe', $T('La vaccination des enfants', 'Vaccinating children'), $T('Gardez le carnet de vaccination à jour et demandez conseil à votre centre de santé.', 'Keep the vaccination record up to date and ask your health center for advice.')],
                    ['hopital-maternite', 'baby', $T('Une grossesse suivie', 'A monitored pregnancy'), $T('Les consultations prénatales régulières aident à protéger la mère et l’enfant.', 'Regular prenatal visits help protect mother and child.')],
                    ['hopital-prevention-hygiene', 'droplet', $T('Les gestes d’hygiène', 'Hygiene habits'), $T('Lavage des mains, eau propre et conservation des aliments au quotidien.', 'Handwashing, clean water and safe food storage every day.')],
                ]]),
                B::events($theme, ['eyebrow' => $T('Agenda santé', 'Health calendar'), 'title' => $T('Campagnes et journées de dépistage', 'Campaigns and screening days'), 'bg' => 'light', 'items' => [
                    [$T('Campagne de vaccination', 'Vaccination campaign'), $T('[Date à compléter]', '[Date to be completed]'), $T('Hôpital, entrée principale', 'Hospital, main entrance')],
                    [$T('Journée de dépistage', 'Screening day'), $T('[Date à compléter]', '[Date to be completed]'), $T('Centre de santé', 'Health center')],
                    [$T('Sensibilisation à la santé maternelle', 'Maternal health awareness'), $T('[Date à compléter]', '[Date to be completed]'), $T('Salle de la maternité', 'Maternity hall')],
                    [$T('Journée hygiène et eau propre', 'Hygiene and clean water day'), $T('[Date à compléter]', '[Date to be completed]'), $T('Quartier voisin', 'Neighboring district')],
                ]]),
                B::cta($theme, $apptCta),
            ]];

        $pages[] = ['key' => 'contact', 'slug' => 'contact', 'title' => 'Contact',
            'description' => $T('Adresse, téléphones, courriel et horaires des urgences. Contactez notre hôpital ou venez nous voir.', 'Address, phones, email and emergency hours. Contact our hospital or come and see us.'),
            'sections' => [
                B::pageHeader($theme, ['eyebrow' => 'Contact', 'title' => $T('Nous contacter', 'Contact us'), 'intro' => $T('En cas d’urgence, appelez le '.$emergency.' ou venez directement aux urgences.', 'In an emergency, call '.$emergency.' or come straight to the emergency department.')]),
                B::contact($theme, [
                    'title' => $T('Nos coordonnées', 'Our details'), 'intro' => $T('Écrivez-nous ou appelez-nous. Les urgences sont ouvertes jour et nuit.', 'Write or call us. Emergencies are open day and night.'),
                    'items' => [
                        ['map-pin', $T('Avenue de la Santé, [Ville], RDC', 'Avenue de la Santé, [City], DRC')],
                        ['phone', '+243 000 000 000', 'tel:+243000000000'],
                        ['ambulance', $T('Urgences : '.$emergency.' (24 h/24)', 'Emergencies: '.$emergency.' (24/7)')],
                        ['mail', 'contact@exemple.org', 'mailto:contact@exemple.org'],
                        ['clock', $T('Accueil : lundi – vendredi, 7 h – 17 h', 'Reception: Monday – Friday, 7 am – 5 pm')],
                    ],
                    'social' => ['facebook', 'whatsapp', 'youtube'],
                    'subject' => true,
                    'map' => 'Kinshasa',
                    'bg' => 'white',
                ]),
            ]];

        return [
            'pages' => $pages,
            'navigation' => [
                'menu' => [
                    ['label' => $T('À propos', 'About'), 'url' => $about],
                    ['label' => $T('Services', 'Departments'), 'url' => $departments],
                    ['label' => $T('Médecins', 'Doctors'), 'url' => $doctors],
                    ['label' => $T('Patients', 'Patients'), 'url' => $patients],
                    ['label' => $T('Santé et prévention', 'Health tips'), 'url' => $news],
                    ['label' => 'Contact', 'url' => $contact],
                ],
                'cta_label' => $T('Prendre rendez-vous', 'Book an appointment'),
                'cta_url' => $appointment,
                'footer_text' => $T('Hôpital engagé pour des soins de qualité, humains et accessibles. Urgences ouvertes 24 h/24.', 'Hospital committed to quality, caring and accessible care. Emergencies open 24/7.'),
                'footer_links' => [
                    ['label' => $T('Prendre rendez-vous', 'Book an appointment'), 'url' => $appointment],
                    ['label' => $T('Informations patients', 'Patient information'), 'url' => $patients],
                    ['label' => $T('Nos services', 'Our departments'), 'url' => $departments],
                    ['label' => 'Contact', 'url' => $contact],
                ],
            ],
        ];
    },
];
