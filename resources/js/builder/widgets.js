// Définition des widgets du cœur côté éditeur : libellé, icône, valeurs par
// défaut et contrôles. Les clés correspondent aux classes de app/Builder/Widgets.
// L'aperçu et le CSS sont dans widgets/core.jsx ; l'enregistrement passe par registry.js.

const ALIGN = [
    { value: 'left', label: 'Gauche' },
    { value: 'center', label: 'Centre' },
    { value: 'right', label: 'Droite' },
];

export const CORE_WIDGETS = {
    heading: {
        label: 'Titre',
        icon: 'M6 4v16M18 4v16M6 12h12',
        defaults: { text: 'Votre titre ici', tag: 'h2' },
        controls: [
            { key: 'text', label: 'Texte', type: 'textarea' },
            { key: 'tag', label: 'Niveau', type: 'select', options: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'].map((v) => ({ value: v, label: v.toUpperCase() })) },
            { key: 'align', label: 'Alignement', type: 'segmented', options: ALIGN, tab: 'style' },
            { key: 'color', label: 'Couleur', type: 'color', tab: 'style' },
            { key: 'font_size', label: 'Taille (px)', type: 'slider', min: 12, max: 120, tab: 'style' },
            { key: 'font_size_mobile', label: 'Taille sur mobile (px)', type: 'slider', min: 12, max: 80, tab: 'style' },
            { key: 'font_weight', label: 'Graisse', type: 'select', tab: 'style', options: [
                { value: '', label: 'Par défaut' }, { value: '400', label: 'Normale' }, { value: '500', label: 'Moyenne' },
                { value: '600', label: 'Semi-grasse' }, { value: '700', label: 'Grasse' }, { value: '800', label: 'Très grasse' },
            ] },
        ],
    },
    text: {
        label: 'Texte',
        icon: 'M4 6h16M4 10h16M4 14h10M4 18h12',
        defaults: { html: '<p>Écrivez votre texte ici. Vous pouvez le mettre en <strong>gras</strong>, en <em>italique</em> ou ajouter des liens.</p>' },
        controls: [
            { key: 'html', label: 'Contenu', type: 'richtext' },
            { key: 'align', label: 'Alignement', type: 'segmented', options: [...ALIGN, { value: 'justify', label: 'Justifié' }], tab: 'style' },
            { key: 'color', label: 'Couleur', type: 'color', tab: 'style' },
            { key: 'font_size', label: 'Taille (px)', type: 'slider', min: 12, max: 40, tab: 'style' },
        ],
    },
    image: {
        label: 'Image',
        icon: 'M4 5h16v14H4zM4 15l4-4 5 5 3-3 4 4M15 9h.01',
        defaults: { width: 100, align: 'center' },
        controls: [
            { key: 'url', label: 'Image', type: 'image' },
            { key: 'alt', label: 'Texte alternatif (accessibilité)', type: 'text', placeholder: 'Décrivez l’image' },
            { key: 'link', label: 'Lien au clic', type: 'url' },
            { key: 'width', label: 'Largeur (%)', type: 'slider', min: 5, max: 100, tab: 'style' },
            { key: 'align', label: 'Alignement', type: 'segmented', options: ALIGN, tab: 'style' },
            { key: 'border_radius', label: 'Arrondi (px)', type: 'slider', min: 0, max: 200, tab: 'style' },
        ],
    },
    button: {
        label: 'Bouton',
        icon: 'M3 8h18v8H3zM8 12h8',
        defaults: { text: 'Cliquez ici', url: '#', size: 'md', variant: 'filled' },
        controls: [
            { key: 'text', label: 'Texte', type: 'text' },
            { key: 'url', label: 'Lien', type: 'url' },
            { key: 'new_tab', label: 'Ouvrir dans un nouvel onglet', type: 'toggle' },
            { key: 'variant', label: 'Style', type: 'segmented', tab: 'style', options: [{ value: 'filled', label: 'Plein' }, { value: 'outline', label: 'Contour' }] },
            { key: 'size', label: 'Taille', type: 'segmented', tab: 'style', options: [{ value: 'sm', label: 'S' }, { value: 'md', label: 'M' }, { value: 'lg', label: 'L' }] },
            { key: 'align', label: 'Alignement', type: 'segmented', tab: 'style', options: [...ALIGN, { value: 'full', label: 'Pleine largeur' }] },
            { key: 'bg_color', label: 'Couleur du bouton', type: 'color', tab: 'style' },
            { key: 'text_color', label: 'Couleur du texte', type: 'color', tab: 'style' },
            { key: 'border_radius', label: 'Arrondi (px)', type: 'slider', min: 0, max: 50, tab: 'style' },
        ],
    },
    spacer: {
        label: 'Espace',
        icon: 'M12 4v16M8 8l4-4 4 4M8 16l4 4 4-4',
        defaults: { height: 40 },
        controls: [
            { key: 'height', label: 'Hauteur (px)', type: 'slider', min: 0, max: 400 },
            { key: 'height_mobile', label: 'Hauteur sur mobile (px)', type: 'slider', min: 0, max: 400 },
        ],
    },
    divider: {
        label: 'Séparateur',
        icon: 'M3 12h18',
        defaults: { thickness: 1, width: 100 },
        controls: [
            { key: 'style', label: 'Style', type: 'segmented', options: [{ value: 'solid', label: 'Plein' }, { value: 'dashed', label: 'Tirets' }, { value: 'dotted', label: 'Points' }] },
            { key: 'color', label: 'Couleur', type: 'color' },
            { key: 'thickness', label: 'Épaisseur (px)', type: 'slider', min: 1, max: 20 },
            { key: 'width', label: 'Largeur (%)', type: 'slider', min: 5, max: 100 },
            { key: 'align', label: 'Alignement', type: 'segmented', options: ALIGN },
        ],
    },
    video: {
        label: 'Vidéo',
        icon: 'M4 6h12v12H4zM16 10l4-2v8l-4-2',
        defaults: { aspect: '16:9' },
        controls: [
            { key: 'url', label: 'Lien YouTube ou Vimeo', type: 'url', placeholder: 'https://www.youtube.com/watch?v=…' },
            { key: 'aspect', label: 'Format', type: 'segmented', options: ['16:9', '4:3', '1:1', '9:16'].map((v) => ({ value: v, label: v })) },
        ],
    },
};


export const SECTION_CONTROLS = [
    { key: 'content_width', label: 'Largeur du contenu', type: 'segmented', options: [{ value: 'boxed', label: 'Centrée' }, { value: 'full', label: 'Pleine largeur' }] },
    { key: 'max_width', label: 'Largeur maximale (px)', type: 'slider', min: 600, max: 1600, step: 10, default: 1140 },
    { key: 'gap', label: 'Espace entre colonnes (px)', type: 'slider', min: 0, max: 120, default: 24 },
    { key: 'vertical_align', label: 'Alignement vertical', type: 'segmented', options: [{ value: 'start', label: 'Haut' }, { value: 'center', label: 'Milieu' }, { value: 'end', label: 'Bas' }, { value: 'stretch', label: 'Étiré' }] },
    { key: 'min_height', label: 'Hauteur minimale (px)', type: 'slider', min: 0, max: 1000, step: 10 },
    { key: 'background_color', label: 'Couleur de fond', type: 'color', tab: 'style' },
    { key: 'background_image', label: 'Image de fond', type: 'image', tab: 'style' },
    { key: 'overlay_color', label: 'Voile de couleur', type: 'color', tab: 'style' },
    { key: 'overlay_opacity', label: 'Opacité du voile (%)', type: 'slider', min: 0, max: 100, default: 50, tab: 'style' },
    { key: 'text_color', label: 'Couleur du texte', type: 'color', tab: 'style' },
    { key: 'padding_top', label: 'Marge intérieure haute (px)', type: 'slider', min: 0, max: 300, default: 64, tab: 'style' },
    { key: 'padding_bottom', label: 'Marge intérieure basse (px)', type: 'slider', min: 0, max: 300, default: 64, tab: 'style' },
    { key: 'padding_top_mobile', label: 'Marge haute sur mobile (px)', type: 'slider', min: 0, max: 300, tab: 'style' },
    { key: 'padding_bottom_mobile', label: 'Marge basse sur mobile (px)', type: 'slider', min: 0, max: 300, tab: 'style' },
    { key: 'anchor', label: 'Ancre (pour les liens #ancre)', type: 'text', placeholder: 'contact', tab: 'advanced' },
];

export const COLUMN_CONTROLS = [
    { key: 'width', label: 'Largeur (%)', type: 'slider', min: 5, max: 100 },
    { key: 'vertical_align', label: 'Alignement vertical', type: 'segmented', options: [{ value: 'start', label: 'Haut' }, { value: 'center', label: 'Milieu' }, { value: 'end', label: 'Bas' }] },
    { key: 'background_color', label: 'Couleur de fond', type: 'color', tab: 'style' },
    { key: 'padding', label: 'Marge intérieure (px)', type: 'slider', min: 0, max: 120, tab: 'style' },
    { key: 'border_radius', label: 'Arrondi (px)', type: 'slider', min: 0, max: 100, tab: 'style' },
];

export const LAYOUTS = [
    { label: '1 colonne', widths: [100] },
    { label: '2 colonnes', widths: [50, 50] },
    { label: '3 colonnes', widths: [33.333, 33.333, 33.334] },
    { label: '4 colonnes', widths: [25, 25, 25, 25] },
    { label: '1/3 + 2/3', widths: [33.333, 66.667] },
    { label: '2/3 + 1/3', widths: [66.667, 33.333] },
];
