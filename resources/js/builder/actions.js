// Actions de l'éditeur partagées par le canevas, le panneau, l'arborescence
// et les raccourcis clavier.
import { commit, getState, setState, toast } from './store';
import { duplicate, find, fits, insert, isContainer, makeContainer, makeStructure, makeWidget, move, reId, remove, replaceSettings, wrap } from './tree';
import { CONTAINER_KEYS, STYLE_KEYS, expandKeys } from './style';
import { getWidget } from './registry';
import { t } from './i18n';

const CLIPBOARD = 'geniuspress:clipboard';

export function select(id) {
    setState({ selected: id, panel: id ? 'settings' : 'elements', menu: null });
}

export function labelOf(node) {
    if (!node) return '';
    if (isContainer(node)) return t('Conteneur');
    return getWidget(node.type)?.label ?? node.type;
}

export function duplicateEl(id) {
    const [next, copyId] = duplicate(getState().content, id);
    if (copyId) commit(next, { selected: copyId });
}

export function removeEl(id) {
    commit(remove(getState().content, id), { selected: null, panel: 'elements', menu: null });
}

export function wrapEl(id) {
    const [next, boxId] = wrap(getState().content, id);
    if (boxId) commit(next, { selected: boxId, panel: 'settings' });
    else toast(t('Impossible : profondeur maximale atteinte.'), 'error');
}

/** Copie un élément (et tout son contenu) ; le presse-papiers est partagé entre les pages. */
export function copyEl(id) {
    const loc = find(getState().content, id);
    if (!loc) return;
    try {
        localStorage.setItem(CLIPBOARD, JSON.stringify({ node: loc.node, at: Date.now() }));
        toast(t(':label copié', { label: labelOf(loc.node) }));
    } catch {
        toast(t('Copie impossible dans ce navigateur.'), 'error');
    }
}

export function readClipboard() {
    try {
        const data = JSON.parse(localStorage.getItem(CLIPBOARD) ?? 'null');
        return data?.node?.type ? data.node : null;
    } catch {
        return null;
    }
}

/** Colle l'élément copié juste après l'élément sélectionné (ou à la fin de la page). */
export function pasteAfter(id) {
    const node = readClipboard();
    if (!node) return toast(t('Rien à coller : copiez d’abord un élément.'));
    const content = getState().content;
    const copy = reId(structuredClone(node));
    const loc = id ? find(content, id) : null;
    let parentId = loc ? loc.parent?.id ?? null : null;
    let index = loc ? loc.index + 1 : content.elements.length;
    // Un conteneur sélectionné vide reçoit l'élément à l'intérieur.
    if (loc && isContainer(loc.node) && !loc.node.children.length) {
        parentId = loc.node.id;
        index = 0;
    }
    const item = parentId === null && !isContainer(copy) ? makeContainer(makeStructure([100]).settings, [copy]) : copy;
    if (!fits(content, item, parentId)) return toast(t('Impossible : profondeur maximale atteinte.'), 'error');
    commit(insert(content, parentId, index, item), { selected: copy.id, panel: 'settings', menu: null });
}

/** Clés de style d'un élément : réglages communs, disposition (conteneur), onglets Style et Avancé (widget). */
function styleKeysOf(node) {
    const keys = new Set(expandKeys(STYLE_KEYS));
    if (isContainer(node)) {
        expandKeys(CONTAINER_KEYS).forEach((k) => keys.add(k));
    } else {
        for (const c of getWidget(node.type)?.controls ?? []) {
            if (!c.key || (c.tab ?? 'content') === 'content') continue;
            keys.add(c.key);
            if (c.responsive) keys.add(`${c.key}_tablet`).add(`${c.key}_mobile`);
            if (c.hover) keys.add(`${c.key}_hover`);
        }
    }
    return keys;
}

/** Applique le style de l'élément copié à l'élément sélectionné (le contenu ne change pas). */
export function pasteStyle(id) {
    const source = readClipboard();
    const loc = id ? find(getState().content, id) : null;
    if (!source || !loc) return toast(t('Rien à coller : copiez d’abord un élément.'));
    const keys = styleKeysOf(source);
    // Entre deux types différents, seuls les réglages communs sont transmis.
    const target = styleKeysOf(loc.node);
    const shared = [...keys].filter((k) => target.has(k));
    const settings = Object.fromEntries(Object.entries(loc.node.settings).filter(([k]) => !shared.includes(k)));
    for (const k of shared) {
        if (source.settings[k] !== undefined) settings[k] = structuredClone(source.settings[k]);
    }
    commit(replaceSettings(getState().content, id, settings), { menu: null });
    toast(t('Style collé'));
}

/** Ajoute un widget : dans le conteneur sélectionné, après le widget sélectionné, ou dans un nouveau conteneur. */
export function addWidget(type) {
    const { content, selected } = getState();
    const w = makeWidget(type);
    const loc = selected ? find(content, selected) : null;
    if (loc && isContainer(loc.node)) {
        commit(insert(content, loc.node.id, undefined, w), { selected: w.id, panel: 'settings' });
    } else if (loc?.parent) {
        commit(insert(content, loc.parent.id, loc.index + 1, w), { selected: w.id, panel: 'settings' });
    } else {
        const box = makeStructure([100]);
        box.children.push(w);
        commit(insert(content, null, loc ? loc.index + 1 : undefined, box), { selected: w.id, panel: 'settings' });
    }
}

/** Ajoute une structure de conteneurs : dans le conteneur sélectionné, ou au premier niveau de la page. */
export function addStructure(widths) {
    const { content, selected } = getState();
    const loc = selected ? find(content, selected) : null;
    const parent = loc ? (isContainer(loc.node) ? loc.node : loc.parent) : null;
    const box = makeStructure(widths, !parent);
    const next = insert(content, parent?.id ?? null, parent ? undefined : loc ? loc.index + 1 : undefined, box);
    if (next === content) return toast(t('Impossible : profondeur maximale atteinte.'), 'error');
    commit(next, { selected: box.id, panel: 'settings' });
}

/** Dépose l'élément en cours de glisser à l'endroit indiqué. */
export function dropAt(parentId, index) {
    const { drag, content } = getState();
    setState({ drag: null, drop: null });
    if (!drag) return;
    if (drag.kind === 'new') {
        const w = makeWidget(drag.type);
        const item = parentId === null ? makeContainer(makeStructure([100]).settings, [w]) : w;
        commit(insert(content, parentId, index, item), { selected: w.id, panel: 'settings' });
    } else if (drag.kind === 'structure') {
        const box = makeStructure(drag.widths, parentId === null);
        const next = insert(content, parentId, index, box);
        if (next === content) return toast(t('Impossible : profondeur maximale atteinte.'), 'error');
        commit(next, { selected: box.id, panel: 'settings' });
    } else if (drag.kind === 'move') {
        const loc = find(content, drag.id);
        if (!loc) return;
        if (parentId === null && !isContainer(loc.node)) {
            // Un widget déposé au premier niveau est placé dans un nouveau conteneur.
            const box = makeContainer(makeStructure([100]).settings, []);
            let next = insert(content, null, index, box);
            next = move(next, drag.id, box.id, 0);
            commit(next);
            return;
        }
        const next = move(content, drag.id, parentId, index);
        if (next !== content) commit(next);
    }
}
