// Icônes livrées avec GeniusPress (miroir de app/Builder/Icons.php).
// « lucide:{nom} » = icône générale, « brand:{nom} » = logo d'un réseau social.
import set from '../../builder/icons.json';

export const ICONS = set;

export function iconExists(ref) {
    const m = /^(lucide|brand):([a-z0-9-]{1,60})$/.exec(ref ?? '');
    if (!m) return false;
    return m[1] === 'lucide' ? !!set.lucide[m[2]] : !!set.brands[m[2]];
}

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

/** Même balisage SVG que Icons::svg() côté serveur. */
export function iconSvg(ref, cls = 'gp-ico') {
    if (!iconExists(ref)) return '';
    const [kind, name] = ref.split(':');
    if (kind === 'brand') {
        return `<svg class="${esc(cls)}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="${esc(set.brands[name].path)}"/></svg>`;
    }
    return `<svg class="${esc(cls)}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">${set.lucide[name]}</svg>`;
}

export const brandTitle = (name) => set.brands[name]?.title ?? null;
export const brandHex = (name) => set.brands[name]?.hex ?? null;

/** Icône pour un aperçu React (le balisage vient du fichier livré, jamais de l'utilisateur). */
export function Icon({ value, className }) {
    const html = iconSvg(value, className);
    return html ? <span style={{ display: 'contents' }} dangerouslySetInnerHTML={{ __html: html }} /> : null;
}
