import { t } from './i18n';
// Définition des réglages côté éditeur : widgets du cœur, conteneurs, et
// réglages de style communs à tous les éléments. Les clés correspondent à
// app/Builder/Schema.php et aux classes de app/Builder/Widgets.
// L'aperçu et le CSS des widgets sont dans widgets/core.jsx.
//
// Un contrôle : { key, label, type, tab: 'content' | 'style' | 'advanced',
//   responsive: true (réglable par appareil), hover: true (réglable au survol),
//   when: (settings) => bool (affiché seulement si…) }.
// { type: 'heading', label } sépare les groupes de réglages.

const ALIGN = [
    { value: 'left', label: t('Gauche') },
    { value: 'center', label: t('Centre') },
    { value: 'right', label: t('Droite') },
];

const heading = (label, tab = 'style') => ({ type: 'heading', label, tab });

/** Typographie (Schema::TYPOGRAPHY). */
export const typographyControls = (tab = 'style') => [
    { key: 'font_family', label: t('Police'), type: 'font', tab },
    { key: 'font_weight', label: t('Graisse'), type: 'select', tab, options: [
        { value: '', label: t('Par défaut') }, { value: '300', label: t('Fine') }, { value: '400', label: t('Normale') }, { value: '500', label: t('Moyenne') },
        { value: '600', label: t('Semi-grasse') }, { value: '700', label: t('Grasse') }, { value: '800', label: t('Très grasse') }, { value: '900', label: t('Noire') },
    ] },
    { key: 'font_style', label: t('Style'), type: 'segmented', tab, options: [{ value: 'normal', label: t('Normal') }, { value: 'italic', label: t('Italique') }] },
    { key: 'text_transform', label: t('Casse'), type: 'select', tab, options: [
        { value: '', label: t('Par défaut') }, { value: 'none', label: t('Aucune') }, { value: 'uppercase', label: t('MAJUSCULES') },
        { value: 'lowercase', label: t('minuscules') }, { value: 'capitalize', label: t('Première Lettre') },
    ] },
    { key: 'line_height', label: t('Hauteur de ligne'), type: 'dim', tab, responsive: true, units: ['', 'px', 'em'] },
    { key: 'letter_spacing', label: t('Espacement des lettres'), type: 'dim', tab, responsive: true, units: ['px', 'em'] },
];

export const CORE_WIDGETS = {
    heading: {
        label: t('Titre'),
        icon: 'M6 4v16M18 4v16M6 12h12',
        defaults: { text: t('Votre titre ici'), tag: 'h2' },
        controls: [
            { key: 'text', label: t('Texte'), type: 'textarea' },
            { key: 'tag', label: t('Niveau'), type: 'select', options: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'].map((v) => ({ value: v, label: v.toUpperCase() })) },
            heading(t('Texte')),
            { key: 'align', label: t('Alignement'), type: 'segmented', options: [...ALIGN, { value: 'justify', label: t('Justifié') }], tab: 'style', responsive: true },
            { key: 'color', label: t('Couleur'), type: 'color', tab: 'style' },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 8, max: 160, tab: 'style', responsive: true },
            heading(t('Typographie')),
            ...typographyControls(),
        ],
    },
    text: {
        label: t('Texte'),
        icon: 'M4 6h16M4 10h16M4 14h10M4 18h12',
        defaults: { html: t('<p>Écrivez votre texte ici. Vous pouvez le mettre en <strong>gras</strong>, en <em>italique</em> ou ajouter des liens.</p>') },
        controls: [
            { key: 'html', label: t('Contenu'), type: 'richtext' },
            heading(t('Texte')),
            { key: 'align', label: t('Alignement'), type: 'segmented', options: [...ALIGN, { value: 'justify', label: t('Justifié') }], tab: 'style', responsive: true },
            { key: 'color', label: t('Couleur'), type: 'color', tab: 'style' },
            { key: 'link_color', label: t('Couleur des liens'), type: 'color', tab: 'style', hover: true },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 8, max: 80, tab: 'style', responsive: true },
            heading(t('Typographie')),
            ...typographyControls(),
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
            heading(t('Image')),
            { key: 'width', label: t('Largeur (%)'), type: 'slider', min: 5, max: 100, tab: 'style', responsive: true },
            { key: 'align', label: t('Alignement'), type: 'segmented', options: ALIGN, tab: 'style', responsive: true },
            { key: 'border_radius', label: t('Arrondi (px)'), type: 'slider', min: 0, max: 500, tab: 'style' },
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
            heading(t('Bouton')),
            { key: 'variant', label: t('Style'), type: 'segmented', tab: 'style', options: [{ value: 'filled', label: t('Plein') }, { value: 'outline', label: t('Contour') }] },
            { key: 'size', label: t('Taille'), type: 'segmented', tab: 'style', options: [{ value: 'sm', label: 'S' }, { value: 'md', label: 'M' }, { value: 'lg', label: 'L' }] },
            { key: 'align', label: t('Alignement'), type: 'segmented', tab: 'style', responsive: true, options: [...ALIGN, { value: 'full', label: t('Pleine largeur') }] },
            { key: 'bg_color', label: t('Couleur du bouton'), type: 'color', tab: 'style', hover: true },
            { key: 'text_color', label: t('Couleur du texte'), type: 'color', tab: 'style', hover: true },
            { key: 'border_radius', label: t('Arrondi (px)'), type: 'slider', min: 0, max: 100, tab: 'style' },
            { key: 'font_size', label: t('Taille du texte (px)'), type: 'slider', min: 8, max: 80, tab: 'style', responsive: true },
            heading(t('Typographie')),
            ...typographyControls(),
        ],
    },
    spacer: {
        label: t('Espace'),
        icon: 'M12 4v16M8 8l4-4 4 4M8 16l4 4 4-4',
        defaults: { height: 40 },
        controls: [
            { key: 'height', label: t('Hauteur (px)'), type: 'slider', min: 0, max: 600, responsive: true },
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
    links: {
        label: t('Liste de liens'),
        icon: 'M5 6h2M5 12h2M5 18h2M10 6h9M10 12h9M10 18h9',
        defaults: { title: t('Sur cette page'), items: [{ label: t('Premier lien'), url: '#' }], layout: 'vertical' },
        controls: [
            { key: 'title', label: t('Titre (facultatif)'), type: 'text' },
            { key: 'items', label: t('Liens'), type: 'links' },
            { key: 'layout', label: t('Disposition'), type: 'segmented', tab: 'style', options: [{ value: 'vertical', label: t('Verticale') }, { value: 'horizontal', label: t('Horizontale') }] },
            { key: 'style', label: t('Cadre'), type: 'segmented', tab: 'style', options: [{ value: 'plain', label: t('Sans') }, { value: 'boxed', label: t('Encadrée') }] },
            { key: 'color', label: t('Couleur du lien actif'), type: 'color', tab: 'style' },
        ],
    },
};

const FLEX_ALIGN = [
    { value: 'start', label: t('Début') },
    { value: 'center', label: t('Centre') },
    { value: 'end', label: t('Fin') },
    { value: 'stretch', label: t('Étiré') },
];

/** Disposition d'un conteneur (onglet Contenu). */
export const CONTAINER_CONTROLS = [
    heading(t('Disposition'), 'content'),
    { key: 'direction', label: t('Direction'), type: 'segmented', responsive: true, options: [
        { value: 'column', label: t('↓ Colonne') }, { value: 'row', label: t('→ Ligne') },
        { value: 'column-reverse', label: '↑' }, { value: 'row-reverse', label: '←' },
    ] },
    { key: 'justify', label: t('Répartition (axe principal)'), type: 'select', responsive: true, options: [
        { value: '', label: t('Par défaut') }, { value: 'start', label: t('Au début') }, { value: 'center', label: t('Au centre') }, { value: 'end', label: t('À la fin') },
        { value: 'between', label: t('Espacés (bords collés)') }, { value: 'around', label: t('Espacés autour') }, { value: 'evenly', label: t('Espacés également') },
    ] },
    { key: 'align', label: t('Alignement (axe secondaire)'), type: 'segmented', responsive: true, options: FLEX_ALIGN },
    { key: 'gap', label: t('Espace entre les éléments (px)'), type: 'slider', min: 0, max: 200, default: 20, responsive: true },
    { key: 'wrap', label: t('Retour à la ligne'), type: 'segmented', responsive: true, options: [{ value: 'nowrap', label: t('Non') }, { value: 'wrap', label: t('Oui') }] },
    heading(t('Taille'), 'content'),
    { key: 'content_width', label: t('Largeur du contenu'), type: 'segmented', options: [{ value: 'boxed', label: t('Centrée') }, { value: 'full', label: t('Pleine largeur') }] },
    { key: 'max_width', label: t('Largeur maximale du contenu (px)'), type: 'slider', min: 200, max: 2400, step: 10, default: 1140, responsive: true, when: (s, depth) => (s.content_width ?? (depth === 0 ? 'boxed' : 'full')) === 'boxed' },
    { key: 'tag', label: t('Balise HTML'), type: 'select', options: ['div', 'section', 'header', 'footer', 'article', 'aside', 'nav', 'main'].map((v) => ({ value: v, label: `<${v}>` })), tab: 'advanced' },
    heading(t('Voile de couleur')),
    { key: 'overlay_color', label: t('Couleur du voile'), type: 'color', tab: 'style' },
    { key: 'overlay_opacity', label: t('Opacité du voile (%)'), type: 'slider', min: 0, max: 100, default: 50, tab: 'style', when: (s) => !!s.overlay_color },
    heading(t('Fond vidéo'), 'style'),
    { key: 'background_video', label: t('Vidéo de fond (MP4 ou WebM, sans son)'), type: 'video', tab: 'style' },
    { key: 'background_video_mobile', label: t('Lire aussi sur mobile (sinon l’image de fond la remplace)'), type: 'toggle', tab: 'style', when: (s) => !!s.background_video },
    ...shapeControls('top', t('Séparateur du haut')),
    ...shapeControls('bottom', t('Séparateur du bas')),
];

/** Réglages d'un séparateur de formes (App\Builder\Shapes), en haut ou en bas du conteneur. */
function shapeControls(side, title) {
    const k = `shape_${side}`;
    const on = (s) => !!s[k];
    return [
        heading(title, 'style'),
        { key: k, label: t('Forme'), type: 'shape', tab: 'style' },
        { key: `${k}_color`, label: t('Couleur'), type: 'color', tab: 'style', when: on },
        { key: `${k}_height`, label: t('Hauteur (px)'), type: 'slider', min: 10, max: 600, default: 80, responsive: true, tab: 'style', when: on },
        { key: `${k}_width`, label: t('Largeur (%)'), type: 'slider', min: 100, max: 300, default: 100, tab: 'style', when: on },
        { key: `${k}_flip`, label: t('Retourner horizontalement'), type: 'toggle', tab: 'style', when: on },
        { key: `${k}_front`, label: t('Devant le contenu'), type: 'toggle', tab: 'style', when: on },
    ];
}

/** Courbes d'accélération (ElementStyle::EASING). */
const EASING_OPTIONS = [
    { value: '', label: t('Douce (par défaut)') }, { value: 'smooth', label: t('Très douce') }, { value: 'ease-out', label: t('Ralentit à la fin') },
    { value: 'ease-in', label: t('Accélère') }, { value: 'ease-in-out', label: t('Accélère puis ralentit') }, { value: 'linear', label: t('Constante') },
    { value: 'back', label: t('Avec rebond') },
];

/** Animations d'apparition (classes gp-anim--… de site.css). */
const ENTRANCES = [
    { value: '', label: t('Aucune') },
    { value: 'fade', label: t('Fondu') }, { value: 'fade-up', label: t('Fondu vers le haut') }, { value: 'fade-down', label: t('Fondu vers le bas') },
    { value: 'fade-left', label: t('Fondu depuis la gauche') }, { value: 'fade-right', label: t('Fondu depuis la droite') },
    { value: 'zoom-in', label: t('Zoom avant') }, { value: 'zoom-out', label: t('Zoom arrière') },
    { value: 'slide-up', label: t('Glissement vers le haut') }, { value: 'slide-down', label: t('Glissement vers le bas') },
    { value: 'slide-left', label: t('Glissement depuis la gauche') }, { value: 'slide-right', label: t('Glissement depuis la droite') },
    { value: 'flip-up', label: t('Retournement vertical') }, { value: 'flip-left', label: t('Retournement horizontal') },
    { value: 'rotate-in', label: t('Rotation') }, { value: 'bounce-in', label: t('Rebond') }, { value: 'blur-in', label: t('Net depuis un flou') },
];

/** Effets au survol (classes gp-hv--… de site.css). */
const HOVER_EFFECTS = [
    { value: '', label: t('Aucun') },
    { value: 'grow', label: t('Agrandir') }, { value: 'shrink', label: t('Réduire') }, { value: 'lift', label: t('Soulever') }, { value: 'sink', label: t('Enfoncer') },
    { value: 'rotate-left', label: t('Incliner à gauche') }, { value: 'rotate-right', label: t('Incliner à droite') },
    { value: 'float', label: t('Flotter') }, { value: 'pulse', label: t('Pulser') }, { value: 'wobble', label: t('Osciller') }, { value: 'buzz', label: t('Vibrer') },
];

/** Réglages communs à tous les éléments (Schema::STYLE), ajoutés automatiquement. */
export const STYLE_CONTROLS = [
    heading(t('Fond')),
    { key: 'background_type', label: t('Type de fond'), type: 'segmented', tab: 'style', options: [{ value: 'classic', label: t('Couleur / image') }, { value: 'gradient', label: t('Dégradé') }] },
    { key: 'background_color', label: t('Couleur de fond'), type: 'color', tab: 'style', hover: true, when: (s) => s.background_type !== 'gradient' },
    { key: 'background_image', label: t('Image de fond'), type: 'image', tab: 'style', when: (s) => s.background_type !== 'gradient' },
    { key: 'background_position', label: t('Position de l’image'), type: 'select', tab: 'style', when: (s) => s.background_type !== 'gradient' && !!s.background_image, options: [
        { value: '', label: t('Centre') }, { value: 'top', label: t('Haut') }, { value: 'bottom', label: t('Bas') }, { value: 'left', label: t('Gauche') }, { value: 'right', label: t('Droite') },
    ] },
    { key: 'background_size', label: t('Taille de l’image'), type: 'segmented', tab: 'style', when: (s) => s.background_type !== 'gradient' && !!s.background_image, options: [
        { value: 'cover', label: t('Couvrir') }, { value: 'contain', label: t('Contenir') }, { value: 'auto', label: t('Réelle') },
    ] },
    { key: 'background_fixed', label: t('Image fixe au défilement'), type: 'toggle', tab: 'style', when: (s) => s.background_type !== 'gradient' && !!s.background_image && !s.background_parallax },
    { key: 'background_parallax', label: t('Parallaxe de l’image (vitesse, vide = aucune)'), type: 'slider', min: 1, max: 10, tab: 'style', when: (s) => s.background_type !== 'gradient' && !!s.background_image },
    { key: 'gradient_type', label: t('Forme du dégradé'), type: 'segmented', tab: 'style', when: (s) => s.background_type === 'gradient', options: [{ value: 'linear', label: t('Linéaire') }, { value: 'radial', label: t('Circulaire') }] },
    { key: 'gradient_from', label: t('Première couleur'), type: 'color', tab: 'style', when: (s) => s.background_type === 'gradient' },
    { key: 'gradient_to', label: t('Seconde couleur'), type: 'color', tab: 'style', when: (s) => s.background_type === 'gradient' },
    { key: 'gradient_angle', label: t('Angle (degrés)'), type: 'slider', min: 0, max: 360, default: 180, tab: 'style', when: (s) => s.background_type === 'gradient' && s.gradient_type !== 'radial' },

    heading(t('Bordure')),
    { key: 'border_style', label: t('Type de bordure'), type: 'select', tab: 'style', options: [
        { value: '', label: t('Aucune') }, { value: 'solid', label: t('Pleine') }, { value: 'dashed', label: t('Tirets') }, { value: 'dotted', label: t('Points') }, { value: 'double', label: t('Double') },
    ] },
    { key: 'border_width', label: t('Épaisseur'), type: 'spacing', tab: 'style', units: ['px'], when: (s) => s.border_style && s.border_style !== 'none' },
    { key: 'border_color', label: t('Couleur de la bordure'), type: 'color', tab: 'style', hover: true, when: (s) => s.border_style && s.border_style !== 'none' },
    { key: 'border_radius', label: t('Arrondi des coins'), type: 'spacing', tab: 'style', responsive: true, corners: true, units: ['px', '%'] },
    { key: 'box_shadow', label: t('Ombre'), type: 'shadow', tab: 'style', hover: true },

    heading(t('Couleur et opacité')),
    { key: 'text_color', label: t('Couleur du texte'), type: 'color', tab: 'style', hover: true },
    { key: 'opacity', label: t('Opacité (%)'), type: 'slider', min: 0, max: 100, default: 100, tab: 'style', hover: true },
    { key: 'transition', label: t('Durée de l’effet au survol (ms)'), type: 'slider', min: 0, max: 3000, step: 50, default: 300, tab: 'style', hover: 'only' },
    { key: 'transition_easing', label: t('Courbe de l’effet au survol'), type: 'select', tab: 'style', hover: 'only', options: EASING_OPTIONS },

    heading(t('Animations'), 'advanced'),
    { key: 'entrance', label: t('Animation d’apparition'), type: 'select', tab: 'advanced', options: ENTRANCES },
    { key: 'entrance_duration', label: t('Durée (ms)'), type: 'slider', min: 100, max: 5000, step: 50, default: 700, tab: 'advanced', when: (s) => !!s.entrance },
    { key: 'entrance_delay', label: t('Délai avant l’animation (ms)'), type: 'slider', min: 0, max: 10000, step: 50, default: 0, tab: 'advanced', when: (s) => !!s.entrance },
    { key: 'entrance_easing', label: t('Courbe de l’animation'), type: 'select', tab: 'advanced', when: (s) => !!s.entrance, options: EASING_OPTIONS },
    { key: 'entrance_repeat', label: t('Rejouer à chaque passage à l’écran'), type: 'toggle', tab: 'advanced', when: (s) => !!s.entrance },
    { key: 'hover_effect', label: t('Effet au survol'), type: 'select', tab: 'advanced', options: HOVER_EFFECTS },
    { key: 'transition_easing', label: t('Courbe de l’effet au survol'), type: 'select', tab: 'advanced', when: (s) => !!s.hover_effect, options: EASING_OPTIONS },
    { key: 'transition', label: t('Durée de l’effet au survol (ms)'), type: 'slider', min: 0, max: 3000, step: 50, default: 300, tab: 'advanced', when: (s) => !!s.hover_effect },

    heading(t('Défilement'), 'advanced'),
    { key: 'sticky', label: t('Rester visible pendant le défilement'), type: 'segmented', tab: 'advanced', options: [{ value: '', label: t('Non') }, { value: 'top', label: t('En haut') }, { value: 'bottom', label: t('En bas') }] },
    { key: 'sticky_offset', label: t('Distance du bord de l’écran (px)'), type: 'slider', min: 0, max: 400, default: 0, tab: 'advanced', when: (s) => !!s.sticky },
    { key: 'sticky_devices', label: t('Sur quels écrans'), type: 'select', tab: 'advanced', when: (s) => !!s.sticky, options: [
        { value: '', label: t('Tous') }, { value: 'desktop', label: t('Ordinateur seulement') }, { value: 'desktop-tablet', label: t('Ordinateur et tablette') },
    ] },
    { key: 'sticky_bg', label: t('Fond une fois collé'), type: 'color', tab: 'advanced', when: (s) => !!s.sticky },
    { key: 'sticky_shadow', label: t('Ombre une fois collé'), type: 'toggle', tab: 'advanced', when: (s) => !!s.sticky },
    { key: 'scroll_effect', label: t('Effet au défilement'), type: 'select', tab: 'advanced', options: [
        { value: '', label: t('Aucun') }, { value: 'fade', label: t('Fondu à l’entrée et à la sortie') },
        { value: 'move-up', label: t('Monte plus vite que la page') }, { value: 'move-down', label: t('Descend avec la page') },
        { value: 'move-left', label: t('Glisse vers la gauche') }, { value: 'move-right', label: t('Glisse vers la droite') },
        { value: 'scale', label: t('Grandit au centre de l’écran') }, { value: 'blur', label: t('Net au centre, flou aux bords') }, { value: 'rotate', label: t('Tourne') },
    ] },
    { key: 'scroll_intensity', label: t('Intensité de l’effet'), type: 'slider', min: 1, max: 10, default: 5, tab: 'advanced', when: (s) => !!s.scroll_effect },

    heading(t('Espacements'), 'advanced'),
    { key: 'margin', label: t('Marge extérieure'), type: 'spacing', tab: 'advanced', responsive: true, units: ['px', '%', 'em', 'rem'], allowNegative: true },
    { key: 'padding', label: t('Marge intérieure'), type: 'spacing', tab: 'advanced', responsive: true, units: ['px', '%', 'em', 'rem'] },
    heading(t('Dimensions'), 'advanced'),
    { key: 'width', label: t('Largeur'), type: 'dim', tab: 'advanced', responsive: true, units: ['%', 'px', 'vw', 'auto'] },
    { key: 'min_height', label: t('Hauteur minimale'), type: 'dim', tab: 'advanced', responsive: true, units: ['px', 'vh', 'em'] },
    { key: 'align_self', label: t('Alignement dans le parent'), type: 'segmented', tab: 'advanced', responsive: true, options: FLEX_ALIGN },
    { key: 'grow', label: t('Remplir l’espace disponible'), type: 'toggle', tab: 'advanced', responsive: true },
    { key: 'order', label: t('Ordre d’affichage'), type: 'slider', min: -20, max: 20, tab: 'advanced', responsive: true },
    heading(t('Position'), 'advanced'),
    { key: 'z_index', label: t('Superposition (z-index)'), type: 'slider', min: -100, max: 100, tab: 'advanced' },
    { key: 'overflow', label: t('Contenu qui dépasse'), type: 'segmented', tab: 'advanced', options: [{ value: 'visible', label: t('Visible') }, { value: 'hidden', label: t('Masqué') }] },
    heading(t('Affichage'), 'advanced'),
    { key: 'hide_desktop', label: t('Masquer sur ordinateur'), type: 'toggle', tab: 'advanced' },
    { key: 'hide_tablet', label: t('Masquer sur tablette'), type: 'toggle', tab: 'advanced' },
    { key: 'hide_mobile', label: t('Masquer sur mobile'), type: 'toggle', tab: 'advanced' },
    heading(t('Attributs'), 'advanced'),
    { key: 'css_id', label: t('Ancre (pour les liens #ancre)'), type: 'text', placeholder: t('contact'), tab: 'advanced' },
    { key: 'css_classes', label: t('Classes CSS'), type: 'text', placeholder: 'ma-classe autre-classe', tab: 'advanced' },
    heading(t('CSS personnalisé'), 'advanced'),
    { key: 'custom_css', label: t('CSS de cet élément'), type: 'code', tab: 'advanced', placeholder: ['selector {', '    border-radius: 24px;', '}', 'selector:hover h3 {', '    color: global:primary;', '}'].join('\n'),
        help: t('« selector » désigne cet élément ; les autres règles s’appliquent à son contenu. Couleurs globales : global:primary.') },
].map((c) => ({ ...c, common: true }));

/** Structures proposées pour un nouveau conteneur. */
export const LAYOUTS = [
    { label: t('1 colonne'), widths: [100] },
    { label: t('2 colonnes'), widths: [50, 50] },
    { label: t('3 colonnes'), widths: [33.333, 33.333, 33.334] },
    { label: t('4 colonnes'), widths: [25, 25, 25, 25] },
    { label: '1/3 + 2/3', widths: [33.333, 66.667] },
    { label: '2/3 + 1/3', widths: [66.667, 33.333] },
];
