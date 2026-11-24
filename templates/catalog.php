<?php

/*
 * Catalogue des modèles de sites : 10 secteurs × 6 modèles = 60 modèles,
 * 8 pages chacun, en français et en anglais. Un modèle = un pack de secteur
 * (templates/sectors/{secteur}.php : pages et textes) + un thème. Construire avec :
 *
 *   php artisan geniuspress:templates
 *
 * Couleurs : [principale, foncée, accent, fond clair, texte, texte secondaire].
 * La principale porte du texte blanc (contraste ≥ 4,5) ; l'accent porte du
 * texte foncé (boutons d'appel). Polices disponibles : SettingController::FONTS.
 * Héros : split | center | image | gradient. Cartes : shadow | border | flat | tinted.
 */

use App\Templates\Theme;

$tpl = function (string $sector, array $name, array $description, array $tags, array $c, string $font, ?string $heading, string $hero, string $cards, string $buttons, int $radius): array {
    return [
        'sector' => $sector,
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
];
