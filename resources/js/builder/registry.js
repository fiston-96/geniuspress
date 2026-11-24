import { t } from './i18n';
import { STYLE_CONTROLS } from './widgets';
// Registre des widgets du builder (cœur + plugins).
//
// Un widget se déclare avec :
//   registerWidget('acme-citation', {
//     label: t('Citation'),
//     icon: 'M7 7h4v4H7z',                      // tracé SVG 24×24 (facultatif)
//     defaults: { text: '…' },                  // valeurs à l'ajout
//     controls: [{ key: 'text', label: t('Texte'), type: 'textarea' }, …],
//     preview: (settings, { h }) => h('blockquote', null, settings.text),
//     css: (settings, { rule, tablet, mobile, responsive, typography, px, sel }) => rule(sel, { color: settings.color }),
//   });
//
// Types de contrôles : text, textarea, richtext, url, select, segmented,
// slider (min, max, step), color, toggle, image, links, dim, spacing, shadow,
// font, heading (titre de groupe). Ajoutez tab: 'style' ou 'advanced' pour
// les ranger dans un autre onglet, responsive: true pour un réglage par
// appareil (clés « _tablet » et « _mobile »), hover: true pour un réglage au
// survol (clé « _hover »).
//
// Les réglages de style communs (fond, bordure, ombre, marges, largeur,
// masquage…) sont ajoutés automatiquement ; un contrôle du widget qui porte
// la même clé les remplace.

const DEFAULT_ICON = 'M4 4h16v16H4zM9 9h6v6H9z';
const widgets = new Map();

/** Contrôles du widget suivis des réglages communs qu'il ne redéfinit pas. */
function withCommon(controls) {
    const own = new Set(controls.filter((c) => c.key).map((c) => c.key));
    return [...controls, ...STYLE_CONTROLS.filter((c) => !c.key || !own.has(c.key))];
}

export function registerWidget(type, def) {
    if (!/^[a-z][a-z0-9-]{1,40}$/.test(type) || type === 'container') {
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
        controls: withCommon(def.controls ?? []),
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
    for (const [key, fullRule] of Object.entries(rules)) {
        if (key === 'media_id') continue;
        const [, rule, flags = ''] = fullRule.match(/^(.*?)(?:@([rh]{1,2}))?$/);
        const [kind, ...args] = rule.split(':');
        const name = key.replace(/_/g, ' ').replace(/^\w/, (c) => c.toUpperCase());
        const c = { key, label: name, responsive: flags.includes('r'), hover: flags.includes('h') };
        if (kind === 'string') Object.assign(c, { type: Number(args[0]) > 200 ? 'textarea' : 'text' });
        else if (kind === 'html') Object.assign(c, { type: 'richtext' });
        else if (kind === 'url') Object.assign(c, { type: 'url' });
        else if (kind === 'color') Object.assign(c, { type: 'color', tab: 'style' });
        else if (kind === 'bool') Object.assign(c, { type: 'toggle' });
        else if (kind === 'links') Object.assign(c, { type: 'links' });
        else if (kind === 'dim') Object.assign(c, { type: 'dim', tab: 'style' });
        else if (kind === 'spacing') Object.assign(c, { type: 'spacing', tab: 'style' });
        else if (kind === 'shadow') Object.assign(c, { type: 'shadow', tab: 'style' });
        else if (kind === 'font') Object.assign(c, { type: 'font', tab: 'style' });
        else if (kind === 'int') Object.assign(c, { type: 'slider', min: Number(args[0]), max: Number(args[1]) });
        else if (kind === 'enum') Object.assign(c, { type: 'select', options: [{ value: '', label: '—' }, ...args.join(':').split(',').map((v) => ({ value: v, label: v }))] });
        else continue;
        controls.push(c);
    }
    widgets.set(type, { type, label, icon: DEFAULT_ICON, defaults: {}, preview: null, css: null, controls: withCommon(controls), auto: true });
}

export { t };
