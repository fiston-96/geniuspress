// Registre des widgets du builder (cœur + plugins).
//
// Un widget se déclare avec :
//   registerWidget('acme-citation', {
//     label: 'Citation',
//     icon: 'M7 7h4v4H7z',                      // tracé SVG 24×24 (facultatif)
//     defaults: { text: '…' },                  // valeurs à l'ajout
//     controls: [{ key: 'text', label: 'Texte', type: 'textarea' }, …],
//     preview: (settings, { h }) => h('blockquote', null, settings.text),
//     css: (settings, { rule, mobile, px, sel }) => rule(sel, { color: settings.color }),
//   });
//
// Types de contrôles : text, textarea, richtext, url, select, segmented,
// slider (min, max, step), color, toggle, image. Ajoutez tab: 'style' ou
// 'advanced' pour les ranger dans un autre onglet.

const ADVANCED = [
    { key: 'margin_bottom', label: 'Espace en dessous (px)', type: 'slider', min: 0, max: 200, tab: 'advanced' },
    { key: 'hide_desktop', label: 'Masquer sur ordinateur', type: 'toggle', tab: 'advanced' },
    { key: 'hide_mobile', label: 'Masquer sur mobile', type: 'toggle', tab: 'advanced' },
];

const DEFAULT_ICON = 'M4 4h16v16H4zM9 9h6v6H9z';
const widgets = new Map();

export function registerWidget(type, def) {
    if (!/^[a-z][a-z0-9-]{1,40}$/.test(type)) {
        console.error(`[GeniusPress] Type de widget invalide : « ${type} »`);
        return;
    }
    if (widgets.has(type) && !widgets.get(type).auto) {
        console.error(`[GeniusPress] Le widget « ${type} » est déjà déclaré.`);
        return;
    }
    widgets.set(type, {
        label: type,
        icon: DEFAULT_ICON,
        defaults: {},
        preview: null,
        css: null,
        ...def,
        type,
        controls: [...(def.controls ?? []), ...ADVANCED],
    });
}

export const getWidget = (type) => widgets.get(type) ?? null;

export const allWidgets = () => [...widgets.values()];

/**
 * Widget déclaré seulement côté PHP (plugin sans script pour le builder) :
 * on génère des contrôles à partir de ses règles de validation.
 */
export function registerFromRules(type, label, rules) {
    if (widgets.has(type)) return;
    const controls = [];
    for (const [key, rule] of Object.entries(rules)) {
        if (['margin_bottom', 'hide_desktop', 'hide_mobile', 'media_id'].includes(key)) continue;
        const [kind, ...args] = rule.split(':');
        const name = key.replace(/_/g, ' ').replace(/^\w/, (c) => c.toUpperCase());
        const c = { key, label: name };
        if (kind === 'string') Object.assign(c, { type: Number(args[0]) > 200 ? 'textarea' : 'text' });
        else if (kind === 'html') Object.assign(c, { type: 'richtext' });
        else if (kind === 'url') Object.assign(c, { type: 'url' });
        else if (kind === 'color') Object.assign(c, { type: 'color', tab: 'style' });
        else if (kind === 'bool') Object.assign(c, { type: 'toggle' });
        else if (kind === 'int') Object.assign(c, { type: 'slider', min: Number(args[0]), max: Number(args[1]) });
        else if (kind === 'enum') Object.assign(c, { type: 'select', options: [{ value: '', label: '—' }, ...args.join(':').split(',').map((v) => ({ value: v, label: v }))] });
        else continue;
        controls.push(c);
    }
    widgets.set(type, { type, label, icon: DEFAULT_ICON, defaults: {}, preview: null, css: null, controls: [...controls, ...ADVANCED], auto: true });
}
