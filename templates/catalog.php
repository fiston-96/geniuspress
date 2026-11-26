<?php

/*
 * Catalogue des modèles de sites : 10 secteurs × 6 modèles, le secteur Médias
 * et presse (6 packs × 2 modèles), puis 25 packs métiers internationaux × 2 modèles
 * (avocats, comptables, agences, immobilier, hôtels, voyages, santé, université,
 * formation, sport, culture, mairies, artisans, BTP, transport, agriculture, beauté,
 * événementiel, SaaS) = 122 modèles, 8 pages chacun, en français et en anglais. Un modèle = un pack de secteur (templates/sectors/{pack}.php :
 * pages et textes ; par défaut le pack porte le nom du secteur) + un thème. Construire avec :
 *
 *   php artisan geniuspress:templates
 *
 * Couleurs : [principale, foncée, accent, fond clair, texte, texte secondaire].
 * La principale porte du texte blanc (contraste ≥ 4,5) ; l'accent porte du
 * texte foncé (boutons d'appel). Polices disponibles : SettingController::FONTS.
 * Héros : split | center | image | gradient. Cartes : shadow | border | flat | tinted.
 */

use App\Templates\Theme;

$tpl = function (string $sector, array $name, array $description, array $tags, array $c, string $font, ?string $heading, string $hero, string $cards, string $buttons, int $radius, ?string $pack = null): array {
    return [
        'sector' => $sector,
        ...($pack ? ['pack' => $pack] : []),
        'name' => ['fr' => $name[0], 'en' => $name[1]],
        'description' => ['fr' => $description[0], 'en' => $description[1]],
        'tags' => $tags,
        'theme' => new Theme(
            colors: ['primary' => $c[0], 'secondary' => $c[1], 'accent' => $c[2], 'light' => $c[3], 'text' => $c[4] ?? '#0f172a', 'muted' => $c[5] ?? '#475569'],
            font: $font, headingFont: $heading, hero: $hero, cards: $cards, buttons: $buttons, radius: $radius,
        ),
    ];
};

return [
    // ─── ONG et associations ─────────────────────────────────────────────
    'ong-horizon' => $tpl('ong', ['Horizon', 'Horizon'], ['Moderne et lumineux : grande photo d’accueil, cartes aérées, mise en avant des dons.', 'Modern and bright: large welcome photo, airy cards, donations front and center.'], ['moderne', 'dons'],
        ['#0f766e', '#0b2a33', '#f59e0b', '#f0f7f6'], 'Inter', 'Poppins', 'split', 'shadow', 'pill', 16),
    'ong-espoir' => $tpl('ong', ['Espoir', 'Hope'], ['Chaleureux et engagé : photo plein écran, couleurs terre, cartes encadrées.', 'Warm and committed: full-screen photo, earthy colors, outlined cards.'], ['chaleureux', 'humanitaire'],
        ['#b45309', '#3b1f0e', '#facc15', '#fdf6ec', '#1c1917', '#57534e'], 'Nunito', 'Merriweather', 'image', 'border', 'rounded', 10),
    'ong-solidarite' => $tpl('ong', ['Solidarité', 'Solidarity'], ['Institutionnel et rassurant : dégradé bleu, cartes teintées, idéal pour les grandes ONG.', 'Institutional and reassuring: blue gradient, tinted cards, ideal for large NGOs.'], ['institutionnel', 'bleu'],
        ['#1d4ed8', '#0b1b3f', '#f97316', '#eff4ff'], 'Open Sans', 'Montserrat', 'gradient', 'tinted', 'rounded', 12),
    'ong-racines' => $tpl('ong', ['Racines', 'Roots'], ['Proche des communautés : vert naturel, titres élégants, présentation centrée.', 'Close to communities: natural green, elegant headings, centered layout.'], ['nature', 'communauté'],
        ['#15803d', '#052e16', '#eab308', '#f0fdf4'], 'Lato', 'Merriweather', 'center', 'flat', 'pill', 14),
    'ong-elan' => $tpl('ong', ['Élan', 'Momentum'], ['Jeune et dynamique : violet vif, cartes encadrées, parfait pour la jeunesse et les femmes.', 'Young and dynamic: vivid purple, outlined cards, perfect for youth and women’s causes.'], ['jeunesse', 'femmes'],
        ['#7c3aed', '#1e1b4b', '#f472b6', '#f5f3ff'], 'Poppins', null, 'split', 'border', 'rounded', 20),
    'ong-terre' => $tpl('ong', ['Terre', 'Earth'], ['Sobre et puissant : photo plein écran, titres à empattements, angles nets.', 'Bold and sober: full-screen photo, serif headings, sharp corners.'], ['photo', 'sobre'],
        ['#c2410c', '#1c1917', '#38bdf8', '#fff7ed', '#1c1917', '#57534e'], 'Nunito', 'Playfair Display', 'image', 'shadow', 'square', 4),

    // ─── Églises et communautés de foi ───────────────────────────────────
    'eglise-lumiere' => $tpl('eglise', ['Lumière', 'Light'], ['Solennel et accueillant : photo plein écran, or et blanc, titres élégants.', 'Solemn and welcoming: full-screen photo, gold and white, elegant headings.'], ['solennel', 'photo'],
        ['#a16207', '#1f2937', '#fcd34d', '#fefce8'], 'Lato', 'Playfair Display', 'image', 'flat', 'pill', 12),
    'eglise-grace' => $tpl('eglise', ['Grâce', 'Grace'], ['Classique et rassurant : bleu royal, présentation centrée, cartes avec ombre.', 'Classic and reassuring: royal blue, centered layout, shadowed cards.'], ['classique', 'bleu'],
        ['#1e40af', '#0f172a', '#fbbf24', '#eff6ff'], 'Open Sans', 'Merriweather', 'center', 'shadow', 'rounded', 12),
    'eglise-alliance' => $tpl('eglise', ['Alliance', 'Covenant'], ['Chaleureux et traditionnel : bordeaux et or, photo à côté du titre.', 'Warm and traditional: burgundy and gold, photo beside the headline.'], ['traditionnel', 'bordeaux'],
        ['#9f1239', '#2a0a12', '#f5c16c', '#fff1f2'], 'Nunito', 'Playfair Display', 'split', 'border', 'rounded', 10),
    'eglise-paix' => $tpl('eglise', ['Paix', 'Peace'], ['Moderne et apaisant : vert profond en dégradé, idéal pour une église jeune.', 'Modern and calming: deep green gradient, ideal for a young church.'], ['moderne', 'jeunes'],
        ['#047857', '#022c22', '#fcd34d', '#ecfdf5'], 'Inter', 'Poppins', 'gradient', 'tinted', 'pill', 16),
    'eglise-esperance' => $tpl('eglise', ['Espérance', 'Hope'], ['Vibrant et festif : violet et or, photo plein écran, pour une communauté en mouvement.', 'Vibrant and joyful: purple and gold, full-screen photo, for a community on the move.'], ['louange', 'festif'],
        ['#6d28d9', '#1e1033', '#fbbf24', '#f5f3ff'], 'Lato', 'Montserrat', 'image', 'shadow', 'rounded', 14),
    'eglise-communion' => $tpl('eglise', ['Communion', 'Communion'], ['Sobre et lisible : bleu canard, présentation centrée, angles nets.', 'Sober and readable: teal blue, centered layout, sharp corners.'], ['sobre', 'paroisse'],
        ['#0e7490', '#082f49', '#fb923c', '#ecfeff'], 'Inter', 'Merriweather', 'center', 'flat', 'square', 4),

    // ─── Écoles et universités ───────────────────────────────────────────
    'ecole-savoir' => $tpl('ecole', ['Savoir', 'Knowledge'], ['Clair et moderne : bleu franc, photo à côté du titre, inscriptions bien visibles.', 'Clear and modern: bright blue, photo beside the headline, prominent admissions.'], ['moderne', 'inscriptions'],
        ['#2563eb', '#0f172a', '#f59e0b', '#eff6ff'], 'Inter', 'Poppins', 'split', 'shadow', 'pill', 16),
    'ecole-excellence' => $tpl('ecole', ['Excellence', 'Excellence'], ['Prestigieux : bleu nuit et or, photo plein écran, titres à empattements.', 'Prestigious: navy and gold, full-screen photo, serif headings.'], ['prestige', 'lycée'],
        ['#1e3a8a', '#0b1437', '#facc15', '#f8fafc'], 'Lato', 'Playfair Display', 'image', 'border', 'square', 4),
    'ecole-avenir' => $tpl('ecole', ['Avenir', 'Future'], ['Dynamique : dégradé vert, cartes teintées, ton positif.', 'Dynamic: green gradient, tinted cards, upbeat tone.'], ['dynamique', 'vert'],
        ['#15803d', '#052e16', '#facc15', '#f0fdf4'], 'Nunito', null, 'gradient', 'tinted', 'rounded', 20),
    'ecole-campus' => $tpl('ecole', ['Campus', 'Campus'], ['Universitaire : bordeaux profond, présentation centrée, cartes à plat.', 'University style: deep maroon, centered layout, flat cards.'], ['université', 'classique'],
        ['#991b1b', '#1f0a0a', '#fbbf24', '#fef2f2'], 'Open Sans', 'Merriweather', 'center', 'flat', 'rounded', 10),
    'ecole-eveil' => $tpl('ecole', ['Éveil', 'Bloom'], ['Joyeux et coloré : orange et turquoise, formes arrondies, pour la maternelle et le primaire.', 'Joyful and colorful: orange and turquoise, rounded shapes, for nursery and primary schools.'], ['primaire', 'enfants'],
        ['#c2410c', '#1e293b', '#22d3ee', '#fff7ed'], 'Nunito', 'Poppins', 'split', 'shadow', 'pill', 24),
    'ecole-universite' => $tpl('ecole', ['Université', 'University'], ['Académique : vert sapin et or, photo plein écran, cartes encadrées.', 'Academic: pine green and gold, full-screen photo, outlined cards.'], ['université', 'recherche'],
        ['#115e59', '#042f2e', '#f59e0b', '#f0fdfa'], 'Roboto', 'Montserrat', 'image', 'border', 'rounded', 12),

    // ─── Gouvernement et institutions publiques ──────────────────────────
    'gouvernement-republique' => $tpl('gouvernement', ['République', 'Republic'], ['Officiel et lisible : bleu institutionnel, accès rapide aux démarches.', 'Official and readable: institutional blue, quick access to procedures.'], ['officiel', 'démarches'],
        ['#1d4ed8', '#0f172a', '#fbbf24', '#eff6ff'], 'Roboto', 'Montserrat', 'split', 'border', 'rounded', 8),
    'gouvernement-commune' => $tpl('gouvernement', ['Commune', 'Town'], ['Proche des habitants : vert, présentation centrée, cartes simples.', 'Close to residents: green, centered layout, simple cards.'], ['mairie', 'proximité'],
        ['#166534', '#052e16', '#facc15', '#f0fdf4'], 'Open Sans', 'Merriweather', 'center', 'flat', 'square', 4),
    'gouvernement-citoyen' => $tpl('gouvernement', ['Citoyen', 'Citizen'], ['Moderne et accueillant : dégradé turquoise, services mis en avant.', 'Modern and welcoming: teal gradient, services up front.'], ['moderne', 'services'],
        ['#0f766e', '#134e4a', '#fdba74', '#f0fdfa'], 'Inter', 'Poppins', 'gradient', 'shadow', 'pill', 14),
    'gouvernement-institution' => $tpl('gouvernement', ['Institution', 'Institution'], ['Solennel : bleu nuit et or, photo plein écran, titres à empattements.', 'Solemn: navy and gold, full-screen photo, serif headings.'], ['solennel', 'agence'],
        ['#1e3a8a', '#0b1220', '#facc15', '#f1f5f9'], 'Lato', 'Playfair Display', 'image', 'tinted', 'square', 2),
    'gouvernement-province' => $tpl('gouvernement', ['Province', 'Province'], ['Affirmé : rouge et bleu ciel, photo à côté du titre.', 'Assertive: red and sky blue, photo beside the headline.'], ['province', 'développement'],
        ['#b91c1c', '#1f1111', '#38bdf8', '#fef2f2'], 'Roboto', null, 'split', 'shadow', 'rounded', 10),
    'gouvernement-services' => $tpl('gouvernement', ['Services publics', 'Public Services'], ['Pratique : indigo, présentation centrée, cartes encadrées faciles à parcourir.', 'Practical: indigo, centered layout, easy-to-scan outlined cards.'], ['pratique', 'guichet'],
        ['#4338ca', '#1e1b4b', '#4ade80', '#eef2ff'], 'Inter', null, 'center', 'border', 'rounded', 10),

    // ─── Hôpitaux, cliniques et centres de santé ─────────────────────────
    'hopital-sante' => $tpl('hopital', ['Santé', 'Health'], ['Rassurant et clair : bleu médical, prise de rendez-vous mise en avant.', 'Reassuring and clear: medical blue, appointments up front.'], ['rendez-vous', 'bleu'],
        ['#0369a1', '#0c2a43', '#34d399', '#f0f9ff'], 'Inter', 'Poppins', 'split', 'shadow', 'pill', 16),
    'hopital-clinique' => $tpl('hopital', ['Clinique', 'Clinic'], ['Haut de gamme : turquoise, photo plein écran, cartes encadrées.', 'Premium: teal, full-screen photo, outlined cards.'], ['clinique', 'premium'],
        ['#0f766e', '#042f2e', '#fbbf24', '#f0fdfa'], 'Open Sans', 'Montserrat', 'image', 'border', 'rounded', 12),
    'hopital-soin' => $tpl('hopital', ['Soin', 'Care'], ['Efficace : dégradé bleu, urgences bien visibles, cartes à plat.', 'Efficient: blue gradient, visible emergency info, flat cards.'], ['urgences', 'hôpital'],
        ['#1d4ed8', '#172554', '#f87171', '#eff6ff'], 'Roboto', null, 'gradient', 'flat', 'rounded', 10),
    'hopital-maternite' => $tpl('hopital', ['Maternité', 'Maternity'], ['Doux et bienveillant : rose et turquoise, pour maternités et pédiatrie.', 'Gentle and caring: pink and turquoise, for maternity and pediatrics.'], ['maternité', 'pédiatrie'],
        ['#be185d', '#3b0a24', '#2dd4bf', '#fdf2f8'], 'Nunito', 'Poppins', 'split', 'tinted', 'pill', 20),
    'hopital-centre' => $tpl('hopital', ['Centre de santé', 'Health Center'], ['Communautaire : vert, présentation centrée, titres à empattements.', 'Community care: green, centered layout, serif headings.'], ['communautaire', 'prévention'],
        ['#15803d', '#052e16', '#38bdf8', '#f0fdf4'], 'Lato', 'Merriweather', 'center', 'shadow', 'rounded', 12),
    'hopital-polyclinique' => $tpl('hopital', ['Polyclinique', 'Polyclinic'], ['Moderne et précis : indigo et cyan, photo plein écran, angles nets.', 'Modern and precise: indigo and cyan, full-screen photo, sharp corners.'], ['spécialités', 'moderne'],
        ['#4f46e5', '#1e1b4b', '#22d3ee', '#eef2ff'], 'Inter', null, 'image', 'flat', 'square', 6),

    // ─── Entreprises tech ────────────────────────────────────────────────
    'tech-nova' => $tpl('tech', ['Nova', 'Nova'], ['Startup : dégradé indigo, cartes encadrées, tarifs clairs.', 'Startup: indigo gradient, outlined cards, clear pricing.'], ['startup', 'saas'],
        ['#4f46e5', '#0b1020', '#22d3ee', '#eef2ff'], 'Inter', 'Poppins', 'gradient', 'border', 'rounded', 12),
    'tech-pulse' => $tpl('tech', ['Pulse', 'Pulse'], ['Audacieux : violet et rose, présentation centrée, boutons arrondis.', 'Bold: purple and pink, centered layout, pill buttons.'], ['agence', 'créatif'],
        ['#7c3aed', '#12062b', '#f472b6', '#f5f3ff'], 'Poppins', null, 'center', 'shadow', 'pill', 18),
    'tech-code' => $tpl('tech', ['Code', 'Code'], ['Technique et sobre : vert émeraude, cartes à plat, angles nets.', 'Technical and sober: emerald green, flat cards, sharp corners.'], ['développement', 'logiciel'],
        ['#047857', '#022c22', '#fbbf24', '#ecfdf5'], 'Roboto', 'Montserrat', 'split', 'flat', 'square', 6),
    'tech-cloud' => $tpl('tech', ['Cloud', 'Cloud'], ['Aérien : bleu ciel profond, photo plein écran, cartes teintées.', 'Airy: deep sky blue, full-screen photo, tinted cards.'], ['cloud', 'infrastructure'],
        ['#0369a1', '#0c1a2e', '#fb923c', '#f0f9ff'], 'Inter', null, 'image', 'tinted', 'rounded', 14),
    'tech-orbit' => $tpl('tech', ['Orbit', 'Orbit'], ['Énergique : orange et indigo, photo à côté du titre, cartes avec ombre.', 'Energetic: orange and indigo, photo beside the headline, shadowed cards.'], ['digital', 'énergie'],
        ['#c2410c', '#111827', '#a5b4fc', '#fff7ed'], 'Open Sans', 'Montserrat', 'split', 'shadow', 'rounded', 12),
    'tech-data' => $tpl('tech', ['Data', 'Data'], ['Analytique : bleu et vert citron, présentation centrée, cartes encadrées.', 'Analytical: blue and lime, centered layout, outlined cards.'], ['données', 'ia'],
        ['#2563eb', '#020617', '#a3e635', '#f1f5f9'], 'Inter', null, 'center', 'border', 'square', 4),

    // ─── Commerces et PME ────────────────────────────────────────────────
    'commerce-marche' => $tpl('commerce', ['Marché', 'Market'], ['Vivant et accueillant : orange et vert, produits mis en avant.', 'Lively and welcoming: orange and green, products up front.'], ['supermarché', 'produits'],
        ['#c2410c', '#1c1917', '#4ade80', '#fff7ed', '#1c1917', '#57534e'], 'Inter', 'Poppins', 'split', 'shadow', 'pill', 16),
    'commerce-boutique' => $tpl('commerce', ['Boutique', 'Boutique'], ['Mode et beauté : framboise et or, photo plein écran, cartes à plat.', 'Fashion and beauty: raspberry and gold, full-screen photo, flat cards.'], ['mode', 'beauté'],
        ['#be185d', '#2d0a1c', '#fbbf24', '#fdf2f8'], 'Lato', 'Montserrat', 'image', 'flat', 'rounded', 14),
    'commerce-quartier' => $tpl('commerce', ['Quartier', 'Neighborhood'], ['Proche et simple : vert, présentation centrée, cartes encadrées.', 'Friendly and simple: green, centered layout, outlined cards.'], ['proximité', 'alimentation'],
        ['#15803d', '#14281d', '#fbbf24', '#f0fdf4'], 'Nunito', null, 'center', 'border', 'rounded', 12),
    'commerce-express' => $tpl('commerce', ['Express', 'Express'], ['Rapide et percutant : rouge et jaune, dégradé, idéal pour la livraison.', 'Fast and punchy: red and yellow, gradient, ideal for delivery.'], ['livraison', 'promotions'],
        ['#dc2626', '#111827', '#facc15', '#fef2f2'], 'Roboto', 'Poppins', 'gradient', 'tinted', 'rounded', 12),
    'commerce-depot' => $tpl('commerce', ['Dépôt', 'Depot'], ['Professionnel : bleu et orange, pour quincailleries et grossistes.', 'Professional: blue and orange, for hardware stores and wholesalers.'], ['grossiste', 'matériaux'],
        ['#1d4ed8', '#0f172a', '#fb923c', '#eff6ff'], 'Open Sans', 'Montserrat', 'split', 'border', 'square', 4),
    'commerce-elegance' => $tpl('commerce', ['Élégance', 'Elegance'], ['Raffiné : brun et ambre, titres à empattements, photo plein écran.', 'Refined: brown and amber, serif headings, full-screen photo.'], ['luxe', 'bijoux'],
        ['#92400e', '#1c1410', '#fbbf24', '#fefaf3', '#1c1917', '#57534e'], 'Lato', 'Playfair Display', 'image', 'shadow', 'square', 2),

    // ─── Art, décoration et design ───────────────────────────────────────
    'art-deco-wax' => $tpl('art-deco', ['Wax', 'Wax'], ['Inspiré des tissus africains : terracotta et turquoise, photo plein écran.', 'Inspired by African fabrics: terracotta and turquoise, full-screen photo.'], ['africain', 'textile'],
        ['#c2410c', '#1c1917', '#2dd4bf', '#fdf6ee', '#1c1917', '#57534e'], 'Lato', 'Playfair Display', 'image', 'flat', 'square', 2),
    'art-deco-raphia' => $tpl('art-deco', ['Raphia', 'Raffia'], ['Naturel : vert olive et ambre, matières brutes, cartes encadrées.', 'Natural: olive and amber, raw materials, outlined cards.'], ['naturel', 'artisanat'],
        ['#4d7c0f', '#1a2e05', '#fbbf24', '#f7fee7'], 'Nunito', 'Merriweather', 'split', 'border', 'rounded', 12),
    'art-deco-galerie' => $tpl('art-deco', ['Galerie', 'Gallery'], ['Minimaliste : noir et blanc, présentation centrée, angles nets.', 'Minimalist: black and white, centered layout, sharp corners.'], ['galerie', 'minimaliste'],
        ['#18181b', '#09090b', '#fb7185', '#f5f5f4', '#09090b', '#52525b'], 'Inter', 'Playfair Display', 'center', 'flat', 'square', 0),
    'art-deco-atelier' => $tpl('art-deco', ['Atelier', 'Workshop'], ['Créatif : dégradé indigo et ambre, cartes avec ombre.', 'Creative: indigo and amber gradient, shadowed cards.'], ['créatif', 'design'],
        ['#4338ca', '#1e1b4b', '#fbbf24', '#f5f5ff'], 'Open Sans', 'Montserrat', 'gradient', 'shadow', 'pill', 16),
    'art-deco-lumiere' => $tpl('art-deco', ['Lumière dorée', 'Golden Light'], ['Chaleureux : or et cuivre, titres élégants, photo à côté du titre.', 'Warm: gold and copper, elegant headings, photo beside the headline.'], ['décoration', 'intérieur'],
        ['#a16207', '#1c1917', '#fb923c', '#fffbeb', '#1c1917', '#57534e'], 'Lato', 'Playfair Display', 'split', 'tinted', 'rounded', 12),
    'art-deco-design' => $tpl('art-deco', ['Design', 'Design'], ['Contemporain : sarcelle et corail, photo plein écran, cartes encadrées.', 'Contemporary: teal and coral, full-screen photo, outlined cards.'], ['contemporain', 'mobilier'],
        ['#0f766e', '#0b1f1d', '#fb7185', '#f0fdfa'], 'Poppins', null, 'image', 'border', 'rounded', 18),

    // ─── Restaurants et hôtellerie ───────────────────────────────────────
    'restaurant-saveurs' => $tpl('restaurant', ['Saveurs', 'Flavors'], ['Gourmand : rouge profond, photo plein écran, titres élégants.', 'Gourmet: deep red, full-screen photo, elegant headings.'], ['gastronomie', 'photo'],
        ['#b91c1c', '#1c0a0a', '#fbbf24', '#fef7ed', '#1c1917', '#57534e'], 'Lato', 'Playfair Display', 'image', 'flat', 'rounded', 12),
    'restaurant-lac' => $tpl('restaurant', ['Bord du lac', 'Lakeside'], ['Détente : bleu lac, photo à côté du titre, réservation mise en avant.', 'Relaxed: lake blue, photo beside the headline, booking up front.'], ['hôtel', 'détente'],
        ['#0369a1', '#0b1d2e', '#fbbf24', '#f0f9ff'], 'Open Sans', 'Merriweather', 'split', 'shadow', 'pill', 16),
    'restaurant-terrasse' => $tpl('restaurant', ['Terrasse', 'Terrace'], ['Convivial : vert olive et orange, présentation centrée, cartes encadrées.', 'Friendly: olive green and orange, centered layout, outlined cards.'], ['convivial', 'terrasse'],
        ['#3f6212', '#1a2310', '#fb923c', '#f7fee7'], 'Nunito', 'Poppins', 'center', 'border', 'rounded', 14),
    'restaurant-maquis' => $tpl('restaurant', ['Maquis', 'Grill House'], ['Festif : dégradé orange et jaune, ambiance grillades et musique.', 'Festive: orange and yellow gradient, grill-house vibe.'], ['grillades', 'festif'],
        ['#c2410c', '#1c1917', '#facc15', '#fff7ed', '#1c1917', '#57534e'], 'Poppins', null, 'gradient', 'tinted', 'pill', 18),
    'restaurant-lodge' => $tpl('restaurant', ['Lodge', 'Lodge'], ['Nature et authenticité : brun et vert, photo plein écran, angles nets.', 'Nature and authenticity: brown and green, full-screen photo, sharp corners.'], ['lodge', 'nature'],
        ['#78350f', '#1c1410', '#a3e635', '#fdf8f1', '#1c1917', '#57534e'], 'Nunito', 'Playfair Display', 'image', 'border', 'square', 4),
    'restaurant-ocean' => $tpl('restaurant', ['Océan', 'Ocean'], ['Bord de mer : bleu canard et corail, photo à côté du titre, cartes à plat.', 'Seaside: teal blue and coral, photo beside the headline, flat cards.'], ['plage', 'fruits de mer'],
        ['#0e7490', '#042f2e', '#fb7185', '#ecfeff'], 'Lato', 'Montserrat', 'split', 'flat', 'rounded', 14),

    // ─── Cabinets et services professionnels ─────────────────────────────
    'cabinet-prestige' => $tpl('cabinet', ['Prestige', 'Prestige'], ['Haut de gamme : bleu nuit et or, photo plein écran, titres à empattements.', 'High-end: navy and gold, full-screen photo, serif headings.'], ['avocats', 'prestige'],
        ['#1e3a8a', '#0b1229', '#facc15', '#f8fafc'], 'Lato', 'Playfair Display', 'image', 'border', 'square', 2),
    'cabinet-conseil' => $tpl('cabinet', ['Conseil', 'Advisory'], ['Moderne et accessible : sarcelle, photo à côté du titre, cartes avec ombre.', 'Modern and approachable: teal, photo beside the headline, shadowed cards.'], ['conseil', 'pme'],
        ['#0f766e', '#0b2a29', '#fbbf24', '#f0fdfa'], 'Inter', 'Montserrat', 'split', 'shadow', 'rounded', 12),
    'cabinet-justice' => $tpl('cabinet', ['Justice', 'Justice'], ['Traditionnel : bordeaux et or, présentation centrée, cartes à plat.', 'Traditional: burgundy and gold, centered layout, flat cards.'], ['droit', 'tradition'],
        ['#881337', '#1f0a12', '#facc15', '#fff1f2'], 'Open Sans', 'Merriweather', 'center', 'flat', 'square', 4),
    'cabinet-audit' => $tpl('cabinet', ['Audit', 'Audit'], ['Rigoureux : dégradé bleu, cartes teintées, chiffres mis en avant.', 'Rigorous: blue gradient, tinted cards, figures up front.'], ['comptabilité', 'audit'],
        ['#1d4ed8', '#0f172a', '#34d399', '#eff6ff'], 'Roboto', 'Poppins', 'gradient', 'tinted', 'rounded', 10),
    'cabinet-notaire' => $tpl('cabinet', ['Étude', 'Chambers'], ['Discret et solide : ardoise et ambre, photo à côté du titre, angles nets.', 'Discreet and solid: slate and amber, photo beside the headline, sharp corners.'], ['notaire', 'immobilier'],
        ['#334155', '#0f172a', '#fbbf24', '#f8fafc'], 'Lato', 'Playfair Display', 'split', 'border', 'square', 2),
    'cabinet-strategie' => $tpl('cabinet', ['Stratégie', 'Strategy'], ['Ambitieux : indigo et orange, présentation centrée, boutons arrondis.', 'Ambitious: indigo and orange, centered layout, pill buttons.'], ['stratégie', 'transformation'],
        ['#4f46e5', '#111827', '#fb923c', '#eef2ff'], 'Inter', null, 'center', 'shadow', 'pill', 16),

    // ─── Médias et presse : un pack par type de média, deux modèles chacun ───
    'medias-journal-quotidien' => $tpl('medias', ['Le Quotidien', 'The Daily'], ['Journal en ligne : la une en diaporama, flash info, rubriques et enquêtes, rouge et noir.', 'Online newspaper: front-page slider, breaking news ticker, sections and investigations, red and black.'], ['journal', 'actualité'],
        ['#b91c1c', '#111827', '#facc15', '#f8fafc'], 'Roboto', 'Merriweather', 'split', 'border', 'square', 4, 'medias-journal'),
    'medias-journal-continent' => $tpl('medias', ['Continent', 'Continent'], ['Grand journal d’information : bleu nuit et or, titres à empattements, photo plein écran.', 'Major news outlet: navy and gold, serif headings, full-screen photo.'], ['journal', 'national'],
        ['#1e3a8a', '#0b1220', '#fbbf24', '#f1f5f9'], 'Inter', 'Playfair Display', 'image', 'shadow', 'rounded', 8, 'medias-journal'),
    'medias-magazine-iris' => $tpl('medias', ['Iris', 'Iris'], ['Magazine culture et mode : framboise et or, photo plein écran, titres élégants, articles en carrousel.', 'Culture and fashion magazine: raspberry and gold, full-screen photo, elegant headings, story carousel.'], ['magazine', 'mode'],
        ['#9d174d', '#1f0a17', '#fbbf24', '#fdf2f8'], 'Lato', 'Playfair Display', 'image', 'flat', 'square', 2, 'medias-magazine'),
    'medias-magazine-safari' => $tpl('medias', ['Safari', 'Safari'], ['Magazine société et voyages : terre cuite et ocre, photo à côté du titre, abonnements mis en avant.', 'Society and travel magazine: terracotta and ochre, photo beside the headline, subscriptions up front.'], ['magazine', 'voyages'],
        ['#9a3412', '#1c1917', '#fcd34d', '#fdf8f3', '#1c1917', '#57534e'], 'Nunito', 'Montserrat', 'split', 'shadow', 'pill', 16, 'medias-magazine'),
    'medias-radio-onde' => $tpl('medias', ['Onde', 'Wave'], ['Radio musicale et populaire : dégradé violet, bouton « Écouter en direct », grille des programmes.', 'Popular music radio: purple gradient, “Listen live” button, program schedule.'], ['radio', 'musique'],
        ['#6d28d9', '#1e1033', '#facc15', '#f5f3ff'], 'Poppins', null, 'gradient', 'shadow', 'pill', 18, 'medias-radio'),
    'medias-radio-fm' => $tpl('medias', ['FM', 'FM'], ['Radio d’information locale : orange et bleu ciel, photo plein écran, flash « À l’antenne ».', 'Local news radio: orange and sky blue, full-screen photo, “On air” ticker.'], ['radio', 'information'],
        ['#c2410c', '#111827', '#22d3ee', '#fff7ed', '#1c1917', '#57534e'], 'Inter', 'Montserrat', 'image', 'border', 'rounded', 12, 'medias-radio'),
    'medias-tv-ecran' => $tpl('medias', ['Écran', 'Screen'], ['Chaîne de télévision : bleu électrique et corail, direct en vidéo, replays et grille des programmes.', 'TV channel: electric blue and coral, live video, replays and program schedule.'], ['télévision', 'replay'],
        ['#1d4ed8', '#0b1020', '#fb7185', '#eff4ff'], 'Inter', 'Poppins', 'image', 'shadow', 'rounded', 12, 'medias-tv'),
    'medias-tv-antenne' => $tpl('medias', ['Antenne', 'Broadcast'], ['Chaîne d’information : sarcelle et or, dégradé, cartes teintées, angles nets.', 'News channel: teal and gold, gradient, tinted cards, sharp corners.'], ['télévision', 'information'],
        ['#0f766e', '#042f2e', '#fbbf24', '#f0fdfa'], 'Roboto', 'Montserrat', 'gradient', 'tinted', 'square', 4, 'medias-tv'),
    'medias-journaliste-plume' => $tpl('medias', ['Plume', 'Quill'], ['Journaliste indépendant : ardoise et ambre, portfolio sobre, titres à empattements.', 'Independent journalist: slate and amber, understated portfolio, serif headings.'], ['journaliste', 'portfolio'],
        ['#334155', '#0f172a', '#fbbf24', '#f8fafc'], 'Lato', 'Playfair Display', 'split', 'border', 'square', 2, 'medias-journaliste'),
    'medias-journaliste-terrain' => $tpl('medias', ['Terrain', 'Field'], ['Reporter de terrain : vert olive et orange, photo plein écran, reportages vidéo et photo.', 'Field reporter: olive green and orange, full-screen photo, video and photo reports.'], ['reporter', 'photo'],
        ['#3f6212', '#1a2310', '#fb923c', '#f7fee7'], 'Nunito', 'Merriweather', 'image', 'flat', 'rounded', 10, 'medias-journaliste'),
    'medias-podcast-echo' => $tpl('medias', ['Écho', 'Echo'], ['Podcast : indigo et vert citron, présentation centrée, plateformes d’écoute et épisodes.', 'Podcast: indigo and lime, centered layout, listening platforms and episodes.'], ['podcast', 'audio'],
        ['#4f46e5', '#111827', '#a3e635', '#eef2ff'], 'Poppins', null, 'center', 'border', 'pill', 16, 'medias-podcast'),
    'medias-podcast-voix' => $tpl('medias', ['Voix', 'Voices'], ['Podcast chaleureux : framboise et or, photo à côté du titre, soutien des auditeurs.', 'Warm podcast: raspberry and gold, photo beside the headline, listener support.'], ['podcast', 'communauté'],
        ['#be185d', '#2d0a1c', '#fbbf24', '#fdf2f8'], 'Nunito', 'Poppins', 'split', 'shadow', 'rounded', 20, 'medias-podcast'),
    // ═══ Modèles internationaux : un pack par métier, deux thèmes chacun ═══

    // ─── Cabinets : avocats et experts-comptables ──────────────────────────
    'avocats-plaidoirie' => $tpl('cabinet', ['Plaidoirie', 'Advocacy'], ['Solennel : bleu marine et or, photo plein écran, titres à empattements, angles nets.', 'Solemn: navy and gold, full-screen photo, serif headings, sharp corners.'], ['avocats', 'prestige'],
        ['#1e3a5f', '#0b1626', '#d4a72c', '#f5f3ee', '#1c1917', '#57534e'], 'Lato', 'Playfair Display', 'image', 'flat', 'square', 4, 'avocats'),
    'avocats-barreau' => $tpl('cabinet', ['Barreau', 'The Bar'], ['Classique et chaleureux : bordeaux et or clair, photo à côté du titre, cartes encadrées.', 'Classic and warm: burgundy and soft gold, photo beside the headline, outlined cards.'], ['avocats', 'classique'],
        ['#7f1d1d', '#1f0d0d', '#f2c46d', '#fbf6f3'], 'Inter', 'DM Serif Display', 'split', 'border', 'rounded', 8, 'avocats'),
    'comptable-bilan' => $tpl('cabinet', ['Bilan', 'Balance'], ['Clair et rassurant : sarcelle et ambre, photo à côté du titre, cartes avec ombre, boutons arrondis.', 'Clear and reassuring: teal and amber, photo beside the headline, shadowed cards, pill buttons.'], ['comptabilité', 'moderne'],
        ['#0f766e', '#0b2b2a', '#fbbf24', '#f0fdfa'], 'DM Sans', 'Plus Jakarta Sans', 'split', 'shadow', 'pill', 14, 'comptable'),
    'comptable-chiffres' => $tpl('cabinet', ['Chiffres', 'Figures'], ['Rigoureux : dégradé bleu roi, cartes teintées, offres mensuelles mises en avant.', 'Rigorous: royal blue gradient, tinted cards, monthly plans up front.'], ['expert-comptable', 'bleu'],
        ['#1e40af', '#0f172a', '#34d399', '#eff6ff'], 'Inter', 'Montserrat', 'gradient', 'tinted', 'rounded', 10, 'comptable'),

    // ─── Agences et studios créatifs ───────────────────────────────────────
    'agence-marketing-impact' => $tpl('agence', ['Impact', 'Impact'], ['Audacieux : framboise et jaune vif, présentation centrée, cartes avec ombre, boutons arrondis.', 'Bold: raspberry and bright yellow, centered layout, shadowed cards, pill buttons.'], ['marketing', 'créatif'],
        ['#be185d', '#1e0b2e', '#fde047', '#fdf2f8'], 'Poppins', null, 'center', 'shadow', 'pill', 20, 'agence-marketing'),
    'agence-marketing-signal' => $tpl('agence', ['Signal', 'Signal'], ['Énergique : orange brûlé et turquoise, photo plein écran, cartes encadrées, angles nets.', 'Energetic: burnt orange and turquoise, full-screen photo, outlined cards, sharp corners.'], ['digital', 'réseaux sociaux'],
        ['#c2410c', '#111827', '#5eead4', '#fff7ed', '#1c1917', '#57534e'], 'Plus Jakarta Sans', 'Montserrat', 'image', 'border', 'square', 6, 'agence-marketing'),
    'architecte-beton' => $tpl('agence', ['Béton', 'Concrete'], ['Minéral et épuré : anthracite et sable, photo plein écran, cartes à plat, angles nets.', 'Mineral and minimal: charcoal and sand, full-screen photo, flat cards, sharp corners.'], ['architecture', 'minimaliste'],
        ['#44403c', '#1c1917', '#e7b98a', '#f5f5f4', '#1c1917', '#57534e'], 'DM Sans', 'Instrument Serif', 'image', 'flat', 'square', 2, 'architecte'),
    'architecte-lumiere' => $tpl('agence', ['Lumière naturelle', 'Daylight'], ['Doux et naturel : vert sauge et miel, photo à côté du titre, cartes encadrées, titres élégants.', 'Soft and natural: sage green and honey, photo beside the headline, outlined cards, elegant headings.'], ['architecture intérieure', 'nature'],
        ['#4d6b3c', '#1f2a1a', '#f4c27a', '#f6f7f2', '#1a1f16', '#4b5563'], 'Inter', 'Fraunces', 'split', 'border', 'rounded', 6, 'architecte'),

    // ─── Immobilier ────────────────────────────────────────────────────────
    'immobilier-horizon' => $tpl('immobilier', ['Quartiers', 'Neighborhoods'], ['Lumineux et moderne : bleu franc et ambre, photo à côté du titre, cartes avec ombre, boutons arrondis.', 'Bright and modern: vivid blue and amber, photo beside the headline, shadowed cards, pill buttons.'], ['agence', 'moderne'],
        ['#1d4ed8', '#0f172a', '#fbbf24', '#eff6ff'], 'Plus Jakarta Sans', null, 'split', 'shadow', 'pill', 14, 'immobilier'),
    'immobilier-residence' => $tpl('immobilier', ['Résidence', 'Residence'], ['Haut de gamme : vert profond et or, photo plein écran, cartes teintées, titres à empattements.', 'Upscale: deep green and gold, full-screen photo, tinted cards, serif headings.'], ['prestige', 'gestion locative'],
        ['#166534', '#052e16', '#facc15', '#f0fdf4'], 'Open Sans', 'DM Serif Display', 'image', 'tinted', 'rounded', 10, 'immobilier'),

    // ─── Hôtellerie ────────────────────────────────────────────────────────
    'hotel-boutique' => $tpl('restaurant', ['Boutique-hôtel', 'Boutique Hotel'], ['Raffiné : prune et or, photo plein écran, titres à empattements, angles nets.', 'Refined: plum and gold, full-screen photo, serif headings, sharp corners.'], ['boutique-hôtel', 'élégant'],
        ['#6b2142', '#1f0c16', '#e8b86d', '#fbf6f1', '#1c1917', '#57534e'], 'Lato', 'Playfair Display', 'image', 'flat', 'square', 2, 'hotel'),
    'hotel-urbain' => $tpl('restaurant', ['Urbain', 'City'], ['Contemporain : bleu nuit et laiton, photo à côté du titre, cartes encadrées.', 'Contemporary: midnight blue and brass, photo beside the headline, outlined cards.'], ['hôtel de ville', 'affaires'],
        ['#1e3a5f', '#0b1626', '#d4a373', '#f5f7fa'], 'Inter', 'DM Serif Display', 'split', 'border', 'rounded', 10, 'hotel'),

    // ─── Tourisme et voyages ───────────────────────────────────────────────
    'voyage-horizon' => $tpl('tourisme', ['Grand large', 'Open Sea'], ['Évasion : bleu océan et corail, photo plein écran, cartes avec ombre.', 'Escape: ocean blue and coral, full-screen photo, shadowed cards.'], ['voyage', 'photo'],
        ['#0369a1', '#082f49', '#fb923c', '#f0f9ff'], 'Poppins', null, 'image', 'shadow', 'pill', 18, 'voyage'),
    'voyage-escale' => $tpl('tourisme', ['Escale', 'Stopover'], ['Chaleureux : terre cuite et sable, photo à côté du titre, cartes teintées.', 'Warm: terracotta and sand, photo beside the headline, tinted cards.'], ['sur mesure', 'chaleureux'],
        ['#b4532a', '#2b1409', '#fcd34d', '#fdf7f0', '#1c1917', '#57534e'], 'Nunito', 'Fraunces', 'split', 'tinted', 'rounded', 14, 'voyage'),

    // ─── Santé : pharmacies et cabinets dentaires ──────────────────────────
    'pharmacie-officine' => $tpl('hopital', ['Officine', 'Apothecary'], ['Rassurant : vert pharmacie, photo à côté du titre, cartes avec ombre.', 'Reassuring: pharmacy green, photo beside the headline, shadowed cards.'], ['pharmacie', 'conseil'],
        ['#047857', '#022c22', '#6ee7b7', '#f0fdf6'], 'Inter', 'Plus Jakarta Sans', 'split', 'shadow', 'rounded', 12, 'pharmacie'),
    'pharmacie-garde' => $tpl('hopital', ['Garde', 'On Duty'], ['Pratique : dégradé bleu canard, cartes à plat, horaires et gardes bien visibles.', 'Practical: teal gradient, flat cards, hours and on-duty info up front.'], ['pharmacie', 'horaires'],
        ['#0e7490', '#0b2530', '#a3e635', '#effcfd'], 'Nunito', null, 'gradient', 'flat', 'pill', 16, 'pharmacie'),
    'dentiste-sourire' => $tpl('hopital', ['Sourire', 'Smile'], ['Frais et accueillant : turquoise, photo à côté du titre, cartes encadrées.', 'Fresh and welcoming: turquoise, photo beside the headline, outlined cards.'], ['dentiste', 'famille'],
        ['#0f766e', '#0a2724', '#fcd34d', '#f0fdfa'], 'DM Sans', null, 'split', 'border', 'pill', 20, 'dentiste'),
    'dentiste-studio' => $tpl('hopital', ['Studio dentaire', 'Dental Studio'], ['Moderne et précis : bleu roi et menthe, présentation centrée, cartes teintées.', 'Modern and precise: royal blue and mint, centered layout, tinted cards.'], ['dentiste', 'esthétique'],
        ['#1e40af', '#0b1533', '#5eead4', '#f5f8ff'], 'Plus Jakarta Sans', 'DM Serif Display', 'center', 'tinted', 'rounded', 10, 'dentiste'),

    // ─── Enseignement supérieur et formation ───────────────────────────────
    'universite-savoirs' => $tpl('ecole', ['Agora', 'Agora'], ['Académique : indigo et ambre, dégradé, cartes avec ombre, titres à empattements.', 'Academic: indigo and amber, gradient, shadowed cards, serif headings.'], ['université', 'recherche'],
        ['#3730a3', '#140f3d', '#fbbf24', '#f5f5ff'], 'Open Sans', 'Merriweather', 'gradient', 'shadow', 'rounded', 10, 'universite'),
    'universite-sciences' => $tpl('ecole', ['Sciences', 'Sciences'], ['Affirmé : rouge universitaire, photo à côté du titre, cartes encadrées, angles nets.', 'Bold: university crimson, photo beside the headline, outlined cards, sharp corners.'], ['grande école', 'moderne'],
        ['#9f1239', '#1a0710', '#fda4af', '#fff5f6', '#1c1917', '#57534e'], 'Inter', 'Fraunces', 'split', 'border', 'square', 4, 'universite'),
    'formation-competences' => $tpl('ecole', ['Compétences', 'Skills'], ['Moderne et motivant : indigo et ambre, photo à côté du titre, cartes ombrées, boutons arrondis.', 'Modern and motivating: indigo and amber, photo beside the headline, shadowed cards, pill buttons.'], ['formation', 'moderne'],
        ['#4338ca', '#1e1b4b', '#fbbf24', '#f5f7ff'], 'Plus Jakarta Sans', null, 'split', 'shadow', 'pill', 16, 'formation'),
    'formation-campus-pro' => $tpl('ecole', ['Campus Pro', 'Pro Campus'], ['Professionnel : bleu pétrole et corail, photo plein écran, cartes encadrées.', 'Professional: petrol blue and coral, full-screen photo, outlined cards.'], ['entreprises', 'certifiant'],
        ['#0e7490', '#082f3a', '#fb923c', '#ecfeff'], 'Inter', 'Montserrat', 'image', 'border', 'rounded', 10, 'formation'),

    // ─── Sport et culture ──────────────────────────────────────────────────
    'club-sport-maillot' => $tpl('sport-culture', ['Maillot', 'Jersey'], ['Vibrant : rouge vif et jaune, photo plein écran, cartes ombrées.', 'Vibrant: bright red and yellow, full-screen photo, shadowed cards.'], ['club', 'compétition'],
        ['#c8102e', '#111827', '#facc15', '#fef2f2'], 'Montserrat', null, 'image', 'shadow', 'rounded', 10, 'club-sport'),
    'club-sport-stade' => $tpl('sport-culture', ['Stade', 'Stadium'], ['Terrain : vert pelouse et jaune, présentation centrée, cartes teintées, boutons carrés.', 'On the pitch: grass green and yellow, centered layout, tinted cards, square buttons.'], ['association', 'jeunes'],
        ['#15803d', '#052e16', '#facc15', '#f0fdf4'], 'Poppins', null, 'center', 'tinted', 'square', 4, 'club-sport'),
    'fitness-energie' => $tpl('sport-culture', ['Énergie', 'Energy'], ['Intense : noir et vert citron, photo plein écran, cartes à plat, boutons carrés.', 'Intense: black and lime green, full-screen photo, flat cards, square buttons.'], ['salle de sport', 'cross-training'],
        ['#4d7c0f', '#0a0a0a', '#a3e635', '#f7fee7', '#0a0a0a', '#404040'], 'Montserrat', 'Poppins', 'image', 'flat', 'square', 6, 'fitness'),
    'fitness-zen' => $tpl('sport-culture', ['Zen', 'Zen'], ['Doux et lumineux : framboise et rose poudré, photo à côté du titre, titres à empattements, formes arrondies.', 'Soft and bright: raspberry and powder pink, photo beside the headline, serif headings, rounded shapes.'], ['studio', 'yoga'],
        ['#be185d', '#3b0a24', '#fbcfe8', '#fdf2f8'], 'DM Sans', 'Fraunces', 'split', 'shadow', 'pill', 20, 'fitness'),
    'culture-rideau' => $tpl('sport-culture', ['Rideau', 'Curtain'], ['Théâtre classique : bordeaux velours et or, photo plein écran, titres à empattements, cartes encadrées.', 'Classic theater: velvet burgundy and gold, full-screen photo, serif headings, outlined cards.'], ['théâtre', 'élégant'],
        ['#9f1239', '#1c0a10', '#fbbf24', '#fdf6f0', '#1c1917', '#57534e'], 'Lato', 'Playfair Display', 'image', 'border', 'rounded', 8, 'culture'),
    'culture-scene' => $tpl('sport-culture', ['Scène ouverte', 'Open Stage'], ['Contemporain : dégradé violet électrique, accent orange, cartes à plat, boutons arrondis.', 'Contemporary: electric purple gradient, orange accent, flat cards, pill buttons.'], ['centre culturel', 'festival'],
        ['#7c3aed', '#1e1033', '#fb923c', '#f5f3ff'], 'Inter', 'Instrument Serif', 'gradient', 'flat', 'pill', 18, 'culture'),

    // ─── Collectivités locales ─────────────────────────────────────────────
    'mairie-civique' => $tpl('gouvernement', ['Civique', 'Civic'], ['Officiel et clair : bleu marine et jaune, photo à côté du titre, titres à empattements, cartes encadrées.', 'Official and clear: navy blue and yellow, photo beside the headline, serif headings, outlined cards.'], ['mairie', 'officiel'],
        ['#1e40af', '#0b1739', '#fcd34d', '#eff4fb'], 'Open Sans', 'Merriweather', 'split', 'border', 'rounded', 8, 'mairie'),
    'mairie-proximite' => $tpl('gouvernement', ['Proximité', 'Neighborhood'], ['Accueillant : bleu océan et vert tendre, présentation centrée, cartes teintées, boutons arrondis.', 'Welcoming: ocean blue and soft green, centered layout, tinted cards, pill buttons.'], ['commune', 'citoyen'],
        ['#0369a1', '#0c2a3e', '#4ade80', '#f0f9ff'], 'Nunito', null, 'center', 'tinted', 'pill', 14, 'mairie'),

    // ─── Artisans et BTP ───────────────────────────────────────────────────
    'artisan-atelier' => $tpl('artisans', ['Établi', 'Workbench'], ['Chaleureux et artisanal : orange brique, photo à côté du titre, cartes avec ombre.', 'Warm and hands-on: brick orange, photo beside the headline, shadowed cards.'], ['multiservice', 'chaleureux'],
        ['#c2410c', '#1c1917', '#fbbf24', '#fff7ed', '#1c1917', '#57534e'], 'Open Sans', 'Montserrat', 'split', 'shadow', 'rounded', 10, 'artisan'),
    'artisan-depannage' => $tpl('artisans', ['Dépannage', 'Rapid Repair'], ['Réactif et rassurant : bleu franc et jaune, photo plein écran, cartes encadrées, angles nets.', 'Responsive and reassuring: bold blue and yellow, full-screen photo, outlined cards, sharp corners.'], ['urgence', 'plomberie'],
        ['#1d4ed8', '#0f172a', '#facc15', '#eff6ff'], 'Roboto', 'Montserrat', 'image', 'border', 'square', 4, 'artisan'),
    'btp-chantier' => $tpl('artisans', ['Chantier', 'Worksite'], ['Solide et institutionnel : bleu marine et orange sécurité, photo plein écran, cartes à plat.', 'Solid and institutional: navy and safety orange, full-screen photo, flat cards.'], ['construction', 'génie civil'],
        ['#1e3a5f', '#0b1726', '#f59e0b', '#f1f5f9'], 'Inter', 'Montserrat', 'image', 'flat', 'square', 2, 'btp'),
    'btp-beton' => $tpl('artisans', ['Béton armé', 'Reinforced'], ['Moderne et sobre : ardoise et orange, photo à côté du titre, cartes teintées.', 'Modern and understated: slate and orange, photo beside the headline, tinted cards.'], ['bâtiment', 'moderne'],
        ['#475569', '#1e293b', '#fb923c', '#f8fafc'], 'DM Sans', 'Plus Jakarta Sans', 'split', 'tinted', 'rounded', 8, 'btp'),

    // ─── Transport et automobile ───────────────────────────────────────────
    'logistique-fret' => $tpl('transport', ['Fret', 'Freight'], ['Dynamique et international : dégradé bleu océan, cartes avec ombre, boutons arrondis.', 'Dynamic and international: ocean-blue gradient, shadowed cards, pill buttons.'], ['logistique', 'international'],
        ['#0369a1', '#0c2340', '#f97316', '#f0f9ff'], 'Inter', 'Poppins', 'gradient', 'shadow', 'pill', 14, 'logistique'),
    'logistique-express' => $tpl('transport', ['Relais', 'Relay'], ['Rapide et percutant : rouge et jaune, présentation centrée sur fond foncé, cartes encadrées.', 'Fast and punchy: red and yellow, centered layout on a dark background, outlined cards.'], ['livraison', 'messagerie'],
        ['#b91c1c', '#18181b', '#fde047', '#fef2f2'], 'Roboto', 'Montserrat', 'center', 'border', 'rounded', 8, 'logistique'),
    'garage-atelier' => $tpl('transport', ['Mécanique', 'Garage'], ['Mécanique et énergique : rouge et anthracite, photo plein écran, cartes à plat, angles nets.', 'Mechanical and energetic: red and charcoal, full-screen photo, flat cards, sharp corners.'], ['garage', 'mécanique'],
        ['#dc2626', '#111827', '#fbbf24', '#f3f4f6'], 'Roboto', 'Montserrat', 'image', 'flat', 'square', 4, 'garage'),
    'garage-confiance' => $tpl('transport', ['Confiance', 'Trusted'], ['Accueillant et rassurant : sarcelle, photo à côté du titre, cartes avec ombre, boutons arrondis.', 'Welcoming and reassuring: teal, photo beside the headline, shadowed cards, pill buttons.'], ['entretien', 'familial'],
        ['#0f766e', '#042f2e', '#fcd34d', '#f0fdfa'], 'Nunito', 'Poppins', 'split', 'shadow', 'pill', 16, 'garage'),

    // ─── Agriculture et alimentation ───────────────────────────────────────
    'ferme-potager' => $tpl('agriculture', ['Potager', 'Garden'], ['Frais et naturel : vert olive et jaune, photo à côté du titre, cartes teintées, titres à empattements.', 'Fresh and natural: olive green and yellow, photo beside the headline, tinted cards, serif headings.'], ['ferme', 'paniers'],
        ['#3f6212', '#1a2e05', '#facc15', '#f7fee7', '#1a2e05', '#4d5a3a'], 'Nunito', 'Fraunces', 'split', 'tinted', 'pill', 18, 'ferme'),
    'ferme-terroir' => $tpl('agriculture', ['Terroir', 'Homestead'], ['Authentique et chaleureux : rouille et blé doré, photo plein écran, cartes encadrées.', 'Authentic and warm: rust and golden wheat, full-screen photo, outlined cards.'], ['producteur', 'authentique'],
        ['#9a3412', '#2b1a0e', '#fbbf24', '#fdf8f0', '#1c1917', '#57534e'], 'Lato', 'DM Serif Display', 'image', 'border', 'rounded', 10, 'ferme'),

    // ─── Beauté et bien-être ───────────────────────────────────────────────
    'salon-boucle' => $tpl('beaute', ['Boucle', 'Curl'], ['Chaleureux et féminin : framboise et rose poudré, photo à côté du titre, cartes avec ombre, titres élégants.', 'Warm and feminine: raspberry and powder pink, photo beside the headline, shadowed cards, elegant headings.'], ['coiffure', 'féminin'],
        ['#be185d', '#2a0f1f', '#f9a8d4', '#fdf2f8', '#1f1720', '#57534e'], 'Poppins', 'Playfair Display', 'split', 'shadow', 'pill', 18, 'salon'),
    'salon-atelier' => $tpl('beaute', ['Barbier', 'Barber'], ['Chic et contrasté : anthracite et or, photo plein écran, cartes sans bordure, angles nets ; idéal barbier ou salon mixte.', 'Chic and high-contrast: charcoal and gold, full-screen photo, flat cards, sharp corners; ideal for a barber or unisex salon.'], ['barbier', 'chic'],
        ['#44403c', '#0c0a09', '#d4a24c', '#faf7f2', '#1c1917', '#57534e'], 'Montserrat', 'DM Serif Display', 'image', 'flat', 'square', 2, 'salon'),
    'spa-lotus' => $tpl('beaute', ['Lotus', 'Lotus'], ['Apaisant et naturel : vert sauge et or doux, présentation centrée, cartes teintées, titres à empattements.', 'Calm and natural: sage green and soft gold, centered layout, tinted cards, serif headings.'], ['spa', 'nature'],
        ['#3f7a5c', '#1f2e27', '#e9c46a', '#f3f7f4', '#1c2620', '#4b5a52'], 'Nunito', 'Fraunces', 'center', 'tinted', 'pill', 20, 'spa'),
    'spa-sable' => $tpl('beaute', ['Sable', 'Sand'], ['Chaleureux et minéral : terre cuite et sable, photo plein écran, cartes encadrées.', 'Warm and earthy: terracotta and sand, full-screen photo, outlined cards.'], ['bien-être', 'chaleureux'],
        ['#9a5b3c', '#2b1d16', '#f2c9a0', '#fbf6f0', '#231a15', '#5c5048'], 'Lato', 'Instrument Serif', 'image', 'border', 'rounded', 12, 'spa'),

    // ─── Événementiel et photographie ──────────────────────────────────────
    'evenementiel-ceremonie' => $tpl('evenementiel', ['Cérémonie', 'Ceremony'], ['Romantique et festif : bordeaux et or, photo plein écran, cartes avec ombre.', 'Romantic and festive: burgundy and gold, full-screen photo, shadowed cards.'], ['mariage', 'élégant'],
        ['#9f1239', '#2a0a14', '#f5c86b', '#fff5f5', '#1c1917', '#57534e'], 'Lato', 'Playfair Display', 'image', 'shadow', 'rounded', 14, 'evenementiel'),
    'evenementiel-gala' => $tpl('evenementiel', ['Gala', 'Gala'], ['Prestige et entreprise : bleu nuit et champagne, dégradé, cartes encadrées.', 'Prestige and corporate: midnight blue and champagne, gradient, outlined cards.'], ['entreprise', 'prestige'],
        ['#3730a3', '#0f0d2e', '#e9d8a6', '#f5f5ff'], 'Plus Jakarta Sans', 'DM Serif Display', 'gradient', 'border', 'pill', 16, 'evenementiel'),
    'photographe-argentique' => $tpl('evenementiel', ['Argentique', 'Film'], ['Sobre et contrasté : noir profond et ambre, photo plein écran, cartes sans bordure, angles nets.', 'Bold and minimal: deep black and amber, full-screen photo, flat cards, sharp corners.'], ['portfolio', 'noir et blanc'],
        ['#27272a', '#09090b', '#f59e0b', '#f4f4f5', '#18181b', '#52525b'], 'Inter', 'Instrument Serif', 'image', 'flat', 'square', 2, 'photographe'),
    'photographe-lumiere' => $tpl('evenementiel', ['Heure dorée', 'Golden Hour'], ['Doux et lumineux : terracotta et pêche, photo à côté du titre, cartes avec ombre.', 'Soft and luminous: terracotta and peach, photo beside the headline, shadowed cards.'], ['mariage', 'famille'],
        ['#b0502a', '#2d1a12', '#fcd9a8', '#fdf8f3', '#231a15', '#5c5048'], 'DM Sans', 'Fraunces', 'split', 'shadow', 'rounded', 16, 'photographe'),

    // ─── Logiciels en ligne (SaaS) ─────────────────────────────────────────
    'saas-orbite' => $tpl('tech', ['Orbite', 'Orbital'], ['Startup moderne : indigo et cyan, dégradé, cartes avec ombre.', 'Modern startup: indigo and cyan, gradient, shadowed cards.'], ['saas', 'startup'],
        ['#4f46e5', '#0f172a', '#22d3ee', '#eef2ff'], 'Inter', 'Plus Jakarta Sans', 'gradient', 'shadow', 'rounded', 14, 'saas'),
    'saas-pixel' => $tpl('tech', ['Pixel', 'Pixel'], ['Frais et produit : émeraude et vert citron, capture du logiciel à côté du titre, cartes encadrées.', 'Fresh and product-led: emerald and lime, product shot beside the headline, outlined cards.'], ['logiciel', 'produit'],
        ['#047857', '#052e2b', '#a3e635', '#ecfdf5'], 'DM Sans', null, 'split', 'border', 'pill', 10, 'saas'),
];
