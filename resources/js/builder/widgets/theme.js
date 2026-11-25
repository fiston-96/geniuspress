// Widgets dynamiques du thème : réglages (miroir des rules() de app/Builder/Widgets/*Widget.php).
// Ils affichent ce que regarde le visiteur (article, archive) ; dans le builder,
// l'aperçu utilise les articles du site (boot.sample).
import { t } from '../i18n';
import { typographyControls } from '../widgets';

const ALIGN = [
    { value: 'left', label: t('Gauche') },
    { value: 'center', label: t('Centre') },
    { value: 'right', label: t('Droite') },
];
const heading = (label, tab = 'style') => ({ type: 'heading', label, tab });
export const THEME_GROUP = t('Site et articles');

/** Catégories du site pour la grille d'articles (remplies au démarrage, voir setThemeData). */
export const CATEGORY_OPTIONS = [{ value: '', label: t('Choisissez une catégorie') }];

const titleStyle = (max = 160) => [
    heading(t('Texte')),
    { key: 'align', label: t('Alignement'), type: 'segmented', options: ALIGN, tab: 'style', responsive: true },
    { key: 'color', label: t('Couleur'), type: 'color', tab: 'style' },
    { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 8, max, tab: 'style', responsive: true },
];

export const THEME_WIDGETS = {
    'post-title': {
        label: t('Titre de l’article'),
        icon: 'M6 4v16M18 4v16M6 12h12M3 20h18',
        defaults: { tag: 'h1' },
        controls: [
            { key: 'tag', label: t('Niveau'), type: 'select', options: ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p'].map((v) => ({ value: v, label: v.toUpperCase() })) },
            { key: 'link', label: t('Lien vers l’article'), type: 'toggle' },
            ...titleStyle(),
            heading(t('Typographie')),
            ...typographyControls(),
        ],
    },
    'post-content': {
        label: t('Contenu de l’article'),
        icon: 'M4 5h16M4 9h16M4 13h16M4 17h10',
        defaults: {},
        controls: [
            { key: 'color', label: t('Couleur du texte'), type: 'color', tab: 'style' },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 8, max: 60, tab: 'style', responsive: true },
        ],
    },
    'post-image': {
        label: t('Image à la une'),
        icon: 'M4 5h16v14H4zM4 15l4-4 5 5 3-3 4 4M15 9h.01',
        defaults: { ratio: '16:9' },
        controls: [
            { key: 'ratio', label: t('Proportions'), type: 'select', options: [
                { value: 'auto', label: t('Originales') }, { value: '16:9', label: '16:9' }, { value: '21:9', label: '21:9' }, { value: '4:3', label: '4:3' }, { value: '3:2', label: '3:2' }, { value: '1:1', label: '1:1' },
            ] },
            { key: 'link', label: t('Lien vers l’article'), type: 'toggle' },
            { key: 'radius', label: t('Arrondi (px)'), type: 'slider', min: 0, max: 60, tab: 'style' },
        ],
    },
    'post-meta': {
        label: t('Informations de l’article'),
        icon: 'M8 2v4M16 2v4M3 8h18M5 5h14v16H5z',
        defaults: { show_date: true, show_author: true, show_category: true, show_reading: true, show_tags: false, icons: true },
        controls: [
            { key: 'show_date', label: t('Date'), type: 'toggle' },
            { key: 'show_author', label: t('Auteur'), type: 'toggle' },
            { key: 'show_category', label: t('Catégories'), type: 'toggle' },
            { key: 'show_reading', label: t('Temps de lecture'), type: 'toggle' },
            { key: 'show_tags', label: t('Étiquettes'), type: 'toggle' },
            { key: 'icons', label: t('Icônes'), type: 'toggle' },
            { key: 'align', label: t('Alignement'), type: 'segmented', options: ALIGN, tab: 'style', responsive: true },
            { key: 'color', label: t('Couleur'), type: 'color', tab: 'style' },
            { key: 'link_color', label: t('Couleur des liens'), type: 'color', tab: 'style' },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 8, max: 40, tab: 'style', responsive: true },
        ],
    },
    'post-excerpt': {
        label: t('Résumé de l’article'),
        icon: 'M4 6h16M4 10h16M4 14h9',
        defaults: { length: 220 },
        controls: [
            { key: 'length', label: t('Longueur maximale (caractères)'), type: 'slider', min: 40, max: 600, default: 220 },
            ...titleStyle(60),
            heading(t('Typographie')),
            ...typographyControls(),
        ],
    },
    'post-nav': {
        label: t('Articles précédent et suivant'),
        icon: 'M9 6l-6 6 6 6M15 6l6 6-6 6',
        defaults: { show_titles: true },
        controls: [
            { key: 'show_titles', label: t('Titres des articles'), type: 'toggle' },
            { key: 'color', label: t('Couleur'), type: 'color', tab: 'style', hover: true },
        ],
    },
    'archive-title': {
        label: t('Titre de l’archive'),
        icon: 'M3 5h18v4H3zM6 13h12M8 17h8',
        defaults: { tag: 'h1', show_prefix: true, show_description: true },
        controls: [
            { key: 'tag', label: t('Niveau'), type: 'select', options: ['h1', 'h2', 'h3', 'p'].map((v) => ({ value: v, label: v.toUpperCase() })) },
            { key: 'show_prefix', label: t('« Catégorie » ou « Étiquette » au-dessus'), type: 'toggle' },
            { key: 'show_description', label: t('Description de la catégorie'), type: 'toggle' },
            ...titleStyle(),
            heading(t('Typographie')),
            ...typographyControls(),
        ],
    },
    posts: {
        label: t('Grille d’articles'),
        icon: 'M3 3h8v8H3zM13 3h8v8h-8zM3 13h8v8H3zM13 13h8v8h-8z',
        defaults: { source: 'current', count: 6, layout: 'grid', columns: 3, style: 'shadow', ratio: '16:9', show_image: true, show_category: true, show_date: true, show_excerpt: true, show_more: true, pagination: true },
        controls: [
            { key: 'source', label: t('Articles affichés'), type: 'select', options: [
                { value: 'current', label: t('Ceux de la page (archive, recherche), sinon les derniers') },
                { value: 'latest', label: t('Les derniers articles') },
                { value: 'category', label: t('Les derniers d’une catégorie') },
            ] },
            { key: 'category', label: t('Catégorie'), type: 'select', options: CATEGORY_OPTIONS, when: (o) => o.source === 'category' },
            { key: 'count', label: t('Nombre d’articles'), type: 'slider', min: 1, max: 24, default: 6, when: (o) => o.source !== 'current' },
            { key: 'pagination', label: t('Pagination'), type: 'toggle', when: (o) => (o.source ?? 'current') === 'current' && o.layout !== 'carousel' },
            { key: 'autoplay', label: t('Défilement automatique'), type: 'toggle', when: (o) => o.layout === 'carousel' },
            { key: 'interval', label: t('Durée de chaque diapositive (secondes)'), type: 'slider', min: 2, max: 20, default: 5, when: (o) => o.layout === 'carousel' && !!o.autoplay },
            { key: 'arrows', label: t('Flèches'), type: 'toggle', when: (o) => o.layout === 'carousel' },
            { key: 'dots', label: t('Points de navigation'), type: 'toggle', when: (o) => o.layout === 'carousel' },
            heading(t('Cartes'), 'content'),
            { key: 'show_image', label: t('Image à la une'), type: 'toggle' },
            { key: 'show_category', label: t('Catégorie'), type: 'toggle' },
            { key: 'show_date', label: t('Date'), type: 'toggle' },
            { key: 'show_excerpt', label: t('Résumé'), type: 'toggle' },
            { key: 'show_more', label: t('Lien « Lire la suite »'), type: 'toggle' },
            { key: 'more_text', label: t('Texte du lien'), type: 'text', placeholder: t('Lire la suite'), when: (o) => o.show_more !== false },
            { key: 'layout', label: t('Disposition'), type: 'segmented', tab: 'style', options: [{ value: 'grid', label: t('Grille') }, { value: 'list', label: t('Liste') }, { value: 'carousel', label: t('Carrousel') }] },
            { key: 'columns', label: t('Colonnes'), type: 'slider', min: 1, max: 4, default: 3, tab: 'style', responsive: true, when: (o) => (o.layout ?? 'grid') !== 'list' },
            { key: 'gap', label: t('Espace entre les cartes (px)'), type: 'slider', min: 0, max: 80, default: 28, tab: 'style' },
            { key: 'style', label: t('Style des cartes'), type: 'segmented', tab: 'style', options: [{ value: 'shadow', label: t('Ombre') }, { value: 'border', label: t('Bordure') }, { value: 'flat', label: t('Simple') }] },
            { key: 'ratio', label: t('Proportions de l’image'), type: 'select', tab: 'style', options: ['16:9', '4:3', '3:2', '1:1'].map((v) => ({ value: v, label: v })) },
            { key: 'title_size', label: t('Taille des titres (px)'), type: 'slider', min: 10, max: 60, tab: 'style', responsive: true },
            { key: 'accent', label: t('Couleur d’accent'), type: 'color', tab: 'style' },
        ],
    },
    breadcrumbs: {
        label: t('Fil d’Ariane'),
        icon: 'M3 12h4l2-3 2 3h4M17 9l3 3-3 3',
        defaults: { separator: '›' },
        controls: [
            { key: 'separator', label: t('Séparateur'), type: 'text' },
            { key: 'align', label: t('Alignement'), type: 'segmented', options: ALIGN, tab: 'style', responsive: true },
            { key: 'color', label: t('Couleur'), type: 'color', tab: 'style' },
            { key: 'link_color', label: t('Couleur des liens'), type: 'color', tab: 'style' },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 8, max: 40, tab: 'style', responsive: true },
        ],
    },
    'search-form': {
        label: t('Recherche'),
        icon: 'M11 4a7 7 0 100 14 7 7 0 000-14zM21 21l-5-5',
        defaults: {},
        controls: [
            { key: 'placeholder', label: t('Texte d’invite'), type: 'text', placeholder: t('Rechercher dans le site…') },
            { key: 'button_text', label: t('Texte du bouton'), type: 'text', placeholder: t('Rechercher') },
            { key: 'button_color', label: t('Couleur du bouton'), type: 'color', tab: 'style' },
            { key: 'radius', label: t('Arrondi (px)'), type: 'slider', min: 0, max: 40, tab: 'style' },
        ],
    },
    'site-logo': {
        label: t('Logo du site'),
        icon: 'M4 4h7v7H4zM14 7h6M14 11h4M4 15h16M4 19h10',
        defaults: { show_logo: true, show_name: true },
        controls: [
            { key: 'show_logo', label: t('Logo'), type: 'toggle' },
            { key: 'show_name', label: t('Nom du site'), type: 'toggle' },
            { key: 'height', label: t('Hauteur du logo (px)'), type: 'slider', min: 12, max: 200, default: 40, tab: 'style', responsive: true },
            { key: 'align', label: t('Alignement'), type: 'segmented', options: ALIGN, tab: 'style', responsive: true },
            { key: 'color', label: t('Couleur du nom'), type: 'color', tab: 'style' },
            { key: 'font_size', label: t('Taille du nom (px)'), type: 'slider', min: 8, max: 80, tab: 'style', responsive: true },
        ],
    },
    'nav-menu': {
        label: t('Menu'),
        icon: 'M4 6h16M4 12h16M4 18h16',
        defaults: { show_languages: true, show_cta: true, collapse: 'mobile', align: 'right' },
        controls: [
            { key: 'note', type: 'heading', label: t('Les liens se règlent dans Apparence, pour chaque langue.'), tab: 'content' },
            { key: 'show_languages', label: t('Choix de la langue'), type: 'toggle' },
            { key: 'show_cta', label: t('Bouton d’action'), type: 'toggle' },
            { key: 'collapse', label: t('Menu replié (bouton ☰)'), type: 'segmented', options: [{ value: 'mobile', label: t('Mobile') }, { value: 'tablet', label: t('Tablette') }, { value: 'never', label: t('Jamais') }] },
            { key: 'align', label: t('Alignement'), type: 'segmented', options: ALIGN, tab: 'style' },
            { key: 'color', label: t('Couleur des liens'), type: 'color', tab: 'style', hover: true },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 8, max: 40, tab: 'style' },
            { key: 'gap', label: t('Espace entre les liens (px)'), type: 'slider', min: 0, max: 80, default: 22, tab: 'style' },
            { key: 'cta_color', label: t('Couleur du bouton'), type: 'color', tab: 'style' },
        ],
    },
    'terms-list': {
        label: t('Catégories et étiquettes'),
        icon: 'M20.6 13.4l-7.2 7.2a2 2 0 01-2.8 0L3 13V3h10l7.6 7.6a2 2 0 010 2.8zM7.5 7.5h.01',
        defaults: { taxonomy: 'category', layout: 'list', show_count: true, hide_empty: true, order: 'name' },
        controls: [
            { key: 'taxonomy', label: t('Afficher'), type: 'segmented', options: [{ value: 'category', label: t('Catégories') }, { value: 'tag', label: t('Étiquettes') }] },
            { key: 'layout', label: t('Disposition'), type: 'segmented', options: [{ value: 'list', label: t('Liste') }, { value: 'inline', label: t('Pastilles') }, { value: 'cloud', label: t('Nuage') }] },
            { key: 'order', label: t('Ordre'), type: 'segmented', options: [{ value: 'name', label: t('Par nom') }, { value: 'count', label: t('Les plus utilisées') }] },
            { key: 'limit', label: t('Nombre maximum'), type: 'slider', min: 1, max: 100, default: 30 },
            { key: 'show_count', label: t('Nombre d’articles'), type: 'toggle' },
            { key: 'hide_empty', label: t('Masquer celles sans article'), type: 'toggle' },
            { key: 'align', label: t('Alignement'), type: 'segmented', tab: 'style', responsive: true, options: [{ value: 'left', label: t('Gauche') }, { value: 'center', label: t('Centre') }, { value: 'right', label: t('Droite') }] },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 10, max: 40, tab: 'style', responsive: true },
            { key: 'color', label: t('Couleur du texte'), type: 'color', tab: 'style' },
            { key: 'accent', label: t('Couleur d’accent'), type: 'color', tab: 'style' },
        ],
    },
};
