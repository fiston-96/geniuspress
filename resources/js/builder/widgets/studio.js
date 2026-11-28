import { t } from '../i18n';
import { getState } from '../store';
// Formulaire avancé et méga-menu. Les clés correspondent à app/Builder/Widgets/FormWidget.php
// et MegaMenuWidget.php ; aperçus et CSS dans widgets/studio-preview.jsx.

const heading = (label, tab = 'style') => ({ type: 'heading', label, tab });

export const FIELD_TYPES = [
    { value: 'text', label: t('Texte court') }, { value: 'email', label: t('E-mail') }, { value: 'tel', label: t('Téléphone') },
    { value: 'url', label: t('Adresse web') }, { value: 'number', label: t('Nombre') }, { value: 'date', label: t('Date') },
    { value: 'textarea', label: t('Texte long') }, { value: 'select', label: t('Liste déroulante') }, { value: 'radio', label: t('Choix unique') },
    { value: 'checkboxes', label: t('Cases à cocher') }, { value: 'acceptance', label: t('Consentement') }, { value: 'hidden', label: t('Champ caché') },
];
const CHOICES = ['select', 'radio', 'checkboxes'];
const TYPING = ['text', 'email', 'tel', 'url', 'number', 'textarea', 'select'];

export const STUDIO_WIDGETS = {
    form: {
        label: t('Formulaire'),
        icon: 'M4 4h16v16H4zM8 8h8M8 12h3M13 12h3M8 16h8',
        keywords: [t('devis'), t('inscription'), t('questionnaire'), t('contact'), 'form'],
        defaults: {
            form_name: t('Demande de devis'),
            fields: [
                { type: 'text', label: t('Nom'), required: true, width: '50' },
                { type: 'email', label: t('E-mail'), required: true, width: '50' },
                { type: 'select', label: t('Service souhaité'), options: [t('Site vitrine'), t('Boutique en ligne'), t('Autre')].join('\n') },
                { type: 'textarea', label: t('Votre projet'), required: true },
                { type: 'acceptance', label: t('J’accepte que mes données soient utilisées pour répondre à ma demande.'), required: true },
            ],
        },
        controls: [
            { key: 'form_name', label: t('Nom du formulaire (dans Messages)'), type: 'text' },
            { key: 'fields', label: t('Champs'), type: 'repeater', max: 30, itemLabel: t('Champ'), newItem: { type: 'text', label: t('Nouveau champ') }, addLabel: t('+ Ajouter un champ'), fields: [
                { key: 'label', label: t('Libellé'), type: 'text' },
                { key: 'type', label: t('Type de champ'), type: 'select', options: FIELD_TYPES },
                { key: 'required', label: t('Obligatoire'), type: 'toggle', when: (f) => f.type !== 'hidden' },
                { key: 'placeholder', label: t('Texte indicatif'), type: 'text', when: (f) => TYPING.includes(f.type ?? 'text') },
                { key: 'options', label: t('Choix (un par ligne)'), type: 'textarea', when: (f) => CHOICES.includes(f.type) },
                { key: 'width', label: t('Largeur'), type: 'segmented', options: [{ value: '100', label: '1/1' }, { value: '50', label: '1/2' }, { value: '33', label: '1/3' }], when: (f) => f.type !== 'hidden' },
                { key: 'value', label: t('Valeur envoyée (balises dynamiques possibles)'), type: 'text', when: (f) => f.type === 'hidden' },
                { key: 'name', label: t('Nom technique (facultatif)'), type: 'text' },
            ] },
            { key: 'button_text', label: t('Texte du bouton'), type: 'text', placeholder: t('Envoyer') },
            heading(t('Après l’envoi'), 'content'),
            { key: 'success_message', label: t('Message de remerciement'), type: 'text', placeholder: t('Merci ! Votre message a bien été envoyé.') },
            { key: 'redirect', label: t('Ou rediriger vers'), type: 'url' },
            { key: 'email_to', label: t('Envoyer les réponses à (e-mail)'), type: 'text', placeholder: t('Adresse des réglages du site') },
            { key: 'email_subject', label: t('Objet de l’e-mail'), type: 'text' },
            { key: 'label_color', label: t('Couleur des libellés'), type: 'color', tab: 'style' },
            { key: 'field_radius', label: t('Arrondi des champs (px)'), type: 'slider', min: 0, max: 30, tab: 'style' },
            { key: 'button_color', label: t('Couleur du bouton'), type: 'color', tab: 'style' },
            { key: 'button_align', label: t('Position du bouton'), type: 'segmented', tab: 'style', options: [
                { value: 'start', label: t('Début') }, { value: 'center', label: t('Centre') }, { value: 'end', label: t('Fin') }, { value: 'stretch', label: t('Pleine largeur') },
            ] },
        ],
    },
};

export const STUDIO_THEME_WIDGETS = {
    'mega-menu': {
        label: t('Méga-menu'),
        icon: 'M3 5h18M3 5v4h18V5M6 13h5v6H6zM13 13h5M13 16h5M13 19h3',
        keywords: [t('menu'), t('navigation'), 'mega'],
        defaults: {
            open_on: 'hover', panel_width: 'content', collapse: 'mobile', align: 'right',
            items: [
                { label: t('Accueil'), url: '/' },
                { label: t('Services'), links: [t('Conseil'), t('Formation'), t('Accompagnement')].map((l) => `${l} | /`).join('\n') },
                { label: t('Contact'), url: '/contact' },
            ],
        },
        controls: [
            { key: 'items', label: t('Entrées'), type: 'repeater', max: 12, itemLabel: t('Entrée'), newItem: { label: t('Nouvelle entrée') }, addLabel: t('+ Ajouter une entrée'), fields: [
                { key: 'label', label: t('Libellé'), type: 'text' },
                { key: 'url', label: t('Lien (facultatif si l’entrée ouvre un panneau)'), type: 'url' },
                { key: 'mega', label: t('Panneau conçu dans le builder'), type: 'select', options: () => [{ value: '', label: t('Aucun') }, ...(getState().megaLayouts ?? []).map((l) => ({ value: String(l.id), label: l.name }))] },
                { key: 'links', label: t('Ou liste de liens (« Libellé | /adresse », un par ligne)'), type: 'textarea', when: (i) => !i.mega },
            ] },
            { key: 'mega_help', type: 'heading', label: t('Les panneaux se conçoivent dans Thème → Panneau de méga-menu.'), tab: 'content' },
            { key: 'open_on', label: t('Ouverture des panneaux'), type: 'segmented', options: [{ value: 'hover', label: t('Au survol') }, { value: 'click', label: t('Au clic') }] },
            { key: 'panel_width', label: t('Largeur des panneaux'), type: 'segmented', options: [{ value: 'auto', label: t('Ajustée') }, { value: 'content', label: t('Contenu') }, { value: 'full', label: t('Écran') }] },
            { key: 'collapse', label: t('Menu replié (bouton ☰)'), type: 'segmented', options: [{ value: 'mobile', label: t('Mobile') }, { value: 'tablet', label: t('Tablette') }, { value: 'never', label: t('Jamais') }] },
            { key: 'align', label: t('Alignement'), type: 'segmented', tab: 'style', options: [{ value: 'left', label: t('Gauche') }, { value: 'center', label: t('Centre') }, { value: 'right', label: t('Droite') }] },
            { key: 'color', label: t('Couleur des liens'), type: 'color', tab: 'style', hover: true },
            { key: 'font_size', label: t('Taille (px)'), type: 'slider', min: 8, max: 40, tab: 'style' },
            { key: 'gap', label: t('Espace entre les entrées (px)'), type: 'slider', min: 0, max: 80, default: 22, tab: 'style' },
            { key: 'panel_bg', label: t('Fond des panneaux'), type: 'color', tab: 'style' },
        ],
    },
};
