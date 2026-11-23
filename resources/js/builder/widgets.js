import { t } from './i18n';
// Définition des widgets du cœur côté éditeur : libellé, icône, valeurs par
// défaut et contrôles. Les clés correspondent aux classes de app/Builder/Widgets.
// L'aperçu et le CSS sont dans widgets/core.jsx ; l'enregistrement passe par registry.js.

const ALIGN = [
    { value: 'left', label: t('Gauche') },
    { value: 'center', label: t('Centre') },
    { value: 'right', label: t('Droite') },
];

export const CORE_WIDGETS = {
    heading: {
        label: t('Titre'),
        icon: 'M6 4v16M18 4v16M6 12h12',
        defaults: { text: t('Votre titre ici'), tag: 'h2' },
        controls: [
            { key: 'text', label: t('Texte'), type: 'textarea' },
            { key: 'tag', label: t('Niveau'), type: 'select', options: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'].map((v) => ({ value: v, label: v.toUpperCase() })) },
            { key: 'align', label: t('Alignement'), type: 'segmented', options: ALIGN, tab: 'style' },
            { key: 'color', label: t('Couleur'), type: 'color', tab: 'style' },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 12, max: 120, tab: 'style' },
            { key: 'font_size_mobile', label: t('Taille sur mobile (px)'), type: 'slider', min: 12, max: 80, tab: 'style' },
            { key: 'font_weight', label: t('Graisse'), type: 'select', tab: 'style', options: [
                { value: '', label: t('Par défaut') }, { value: '400', label: t('Normale') }, { value: '500', label: t('Moyenne') },
                { value: '600', label: t('Semi-grasse') }, { value: '700', label: t('Grasse') }, { value: '800', label: t('Très grasse') },
            ] },
        ],
    },
    text: {
        label: t('Texte'),
        icon: 'M4 6h16M4 10h16M4 14h10M4 18h12',
        defaults: { html: t('<p>Écrivez votre texte ici. Vous pouvez le mettre en <strong>gras</strong>, en <em>italique</em> ou ajouter des liens.</p>') },
        controls: [
            { key: 'html', label: t('Contenu'), type: 'richtext' },
            { key: 'align', label: t('Alignement'), type: 'segmented', options: [...ALIGN, { value: 'justify', label: t('Justifié') }], tab: 'style' },
            { key: 'color', label: t('Couleur'), type: 'color', tab: 'style' },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 12, max: 40, tab: 'style' },
        ],
    },
    image: {
        label: t('Image'),
        icon: 'M4 5h16v14H4zM4 15l4-4 5 5 3-3 4 4M15 9h.01',
        defaults: { width: 100, align: 'center' },
        controls: [
            { key: 'url', label: t('Image'), type: 'image' },
            { key: 'alt', label: t('Texte alternatif (accessibilité)'), type: 'text', placeholder: t('Décrivez l’image') },
            { key: 'link', label: t('Lien au clic'), type: 'url' },
            { key: 'width', label: t('Largeur (%)'), type: 'slider', min: 5, max: 100, tab: 'style' },
            { key: 'align', label: t('Alignement'), type: 'segmented', options: ALIGN, tab: 'style' },
            { key: 'border_radius', label: t('Arrondi (px)'), type: 'slider', min: 0, max: 200, tab: 'style' },
        ],
    },
    button: {
        label: t('Bouton'),
        icon: 'M3 8h18v8H3zM8 12h8',
        defaults: { text: t('Cliquez ici'), url: '#', size: 'md', variant: 'filled' },
        controls: [
            { key: 'text', label: t('Texte'), type: 'text' },
            { key: 'url', label: t('Lien'), type: 'url' },
            { key: 'new_tab', label: t('Ouvrir dans un nouvel onglet'), type: 'toggle' },
            { key: 'variant', label: t('Style'), type: 'segmented', tab: 'style', options: [{ value: 'filled', label: t('Plein') }, { value: 'outline', label: t('Contour') }] },
            { key: 'size', label: t('Taille'), type: 'segmented', tab: 'style', options: [{ value: 'sm', label: 'S' }, { value: 'md', label: 'M' }, { value: 'lg', label: 'L' }] },
            { key: 'align', label: t('Alignement'), type: 'segmented', tab: 'style', options: [...ALIGN, { value: 'full', label: t('Pleine largeur') }] },
            { key: 'bg_color', label: t('Couleur du bouton'), type: 'color', tab: 'style' },
            { key: 'text_color', label: t('Couleur du texte'), type: 'color', tab: 'style' },
            { key: 'border_radius', label: t('Arrondi (px)'), type: 'slider', min: 0, max: 50, tab: 'style' },
        ],
    },
    spacer: {
        label: t('Espace'),
        icon: 'M12 4v16M8 8l4-4 4 4M8 16l4 4 4-4',
        defaults: { height: 40 },
        controls: [
            { key: 'height', label: t('Hauteur (px)'), type: 'slider', min: 0, max: 400 },
            { key: 'height_mobile', label: t('Hauteur sur mobile (px)'), type: 'slider', min: 0, max: 400 },
        ],
    },
    divider: {
        label: t('Séparateur'),
        icon: 'M3 12h18',
        defaults: { thickness: 1, width: 100 },
        controls: [
            { key: 'style', label: t('Style'), type: 'segmented', options: [{ value: 'solid', label: t('Plein') }, { value: 'dashed', label: t('Tirets') }, { value: 'dotted', label: t('Points') }] },
            { key: 'color', label: t('Couleur'), type: 'color' },
            { key: 'thickness', label: t('Épaisseur (px)'), type: 'slider', min: 1, max: 20 },
            { key: 'width', label: t('Largeur (%)'), type: 'slider', min: 5, max: 100 },
            { key: 'align', label: t('Alignement'), type: 'segmented', options: ALIGN },
        ],
    },
    video: {
        label: t('Vidéo'),
        icon: 'M4 6h12v12H4zM16 10l4-2v8l-4-2',
        defaults: { aspect: '16:9' },
        controls: [
            { key: 'url', label: t('Lien YouTube ou Vimeo'), type: 'url', placeholder: t('https://www.youtube.com/watch?v=…') },
            { key: 'aspect', label: t('Format'), type: 'segmented', options: ['16:9', '4:3', '1:1', '9:16'].map((v) => ({ value: v, label: v })) },
        ],
    },
};


export const SECTION_CONTROLS = [
    { key: 'content_width', label: t('Largeur du contenu'), type: 'segmented', options: [{ value: 'boxed', label: t('Centrée') }, { value: 'full', label: t('Pleine largeur') }] },
    { key: 'max_width', label: t('Largeur maximale (px)'), type: 'slider', min: 600, max: 1600, step: 10, default: 1140 },
    { key: 'gap', label: t('Espace entre colonnes (px)'), type: 'slider', min: 0, max: 120, default: 24 },
    { key: 'vertical_align', label: t('Alignement vertical'), type: 'segmented', options: [{ value: 'start', label: t('Haut') }, { value: 'center', label: t('Milieu') }, { value: 'end', label: t('Bas') }, { value: 'stretch', label: t('Étiré') }] },
    { key: 'min_height', label: t('Hauteur minimale (px)'), type: 'slider', min: 0, max: 1000, step: 10 },
    { key: 'background_color', label: t('Couleur de fond'), type: 'color', tab: 'style' },
    { key: 'background_image', label: t('Image de fond'), type: 'image', tab: 'style' },
    { key: 'overlay_color', label: t('Voile de couleur'), type: 'color', tab: 'style' },
    { key: 'overlay_opacity', label: t('Opacité du voile (%)'), type: 'slider', min: 0, max: 100, default: 50, tab: 'style' },
    { key: 'text_color', label: t('Couleur du texte'), type: 'color', tab: 'style' },
    { key: 'padding_top', label: t('Marge intérieure haute (px)'), type: 'slider', min: 0, max: 300, default: 64, tab: 'style' },
    { key: 'padding_bottom', label: t('Marge intérieure basse (px)'), type: 'slider', min: 0, max: 300, default: 64, tab: 'style' },
    { key: 'padding_top_mobile', label: t('Marge haute sur mobile (px)'), type: 'slider', min: 0, max: 300, tab: 'style' },
    { key: 'padding_bottom_mobile', label: t('Marge basse sur mobile (px)'), type: 'slider', min: 0, max: 300, tab: 'style' },
    { key: 'anchor', label: t('Ancre (pour les liens #ancre)'), type: 'text', placeholder: t('contact'), tab: 'advanced' },
];

export const COLUMN_CONTROLS = [
    { key: 'width', label: t('Largeur (%)'), type: 'slider', min: 5, max: 100 },
    { key: 'vertical_align', label: t('Alignement vertical'), type: 'segmented', options: [{ value: 'start', label: t('Haut') }, { value: 'center', label: t('Milieu') }, { value: 'end', label: t('Bas') }] },
    { key: 'background_color', label: t('Couleur de fond'), type: 'color', tab: 'style' },
    { key: 'padding', label: t('Marge intérieure (px)'), type: 'slider', min: 0, max: 120, tab: 'style' },
    { key: 'border_radius', label: t('Arrondi (px)'), type: 'slider', min: 0, max: 100, tab: 'style' },
];

export const LAYOUTS = [
    { label: t('1 colonne'), widths: [100] },
    { label: t('2 colonnes'), widths: [50, 50] },
    { label: t('3 colonnes'), widths: [33.333, 33.333, 33.334] },
    { label: t('4 colonnes'), widths: [25, 25, 25, 25] },
    { label: '1/3 + 2/3', widths: [33.333, 66.667] },
    { label: '2/3 + 1/3', widths: [66.667, 33.333] },
];
