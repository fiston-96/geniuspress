// Opérations immuables sur l'arbre { version: 2, elements: [...] }.
// Un élément est un conteneur ({ type: 'container', children: [...] }) ou un widget.
import { getWidget } from './registry';

export const uid = () => Math.random().toString(36).slice(2, 12).padEnd(10, '0');

const clone = (v) => structuredClone(v);

export const isContainer = (node) => node?.type === 'container';

export const MAX_DEPTH = 8;

export function makeWidget(type) {
    return { id: uid(), type, settings: { ...(getWidget(type)?.defaults ?? {}) } };
}

export function makeContainer(settings = {}, children = []) {
    return { id: uid(), type: 'container', settings, children };
}

/**
 * Structure prête à l'emploi : [100] = un conteneur simple ; [50, 50] = un
 * conteneur en ligne (en colonne sur mobile) avec deux conteneurs enfants.
 * Au premier niveau de la page, le conteneur reçoit des marges confortables.
 */
export function makeStructure(widths = [100], topLevel = true) {
    const outer = topLevel ? { padding: { t: 40, r: 20, b: 40, l: 20 } } : {};
    if (widths.length === 1) return makeContainer(outer);
    return makeContainer(
        { ...outer, direction: 'row', direction_mobile: 'column' },
        widths.map((w) => makeContainer({ width: `${Math.round(w * 1000) / 1000}%` })),
    );
}

/**
 * Retrouve un élément : le nœud, son parent (null au premier niveau), sa
 * position, et la liste de ses ancêtres (du plus haut au plus proche).
 */
export function find(content, id) {
    const search = (nodes, parent, path) => {
        for (const [index, node] of nodes.entries()) {
            if (node.id === id) return { node, parent, index, path, depth: path.length };
            if (isContainer(node)) {
                const found = search(node.children ?? [], node, [...path, node]);
                if (found) return found;
            }
        }
        return null;
    };
    return search(content.elements ?? [], null, []);
}

/** Liste d'enfants d'un parent (null = premier niveau de la page). */
function listOf(content, parentId) {
    if (parentId === null || parentId === undefined) return content.elements;
    const loc = find(content, parentId);
    return loc && isContainer(loc.node) ? loc.node.children : null;
}

/** L'élément « id » contient-il (ou est-il) l'élément « other » ? */
export function contains(node, other) {
    if (node.id === other) return true;
    return isContainer(node) && (node.children ?? []).some((c) => contains(c, other));
}

/** Profondeur maximale de conteneurs sous un élément (lui compris). */
function containerDepth(node) {
    if (!isContainer(node)) return 0;
    return 1 + Math.max(0, ...(node.children ?? []).map(containerDepth));
}

/** Peut-on placer « node » dans le parent « parentId » sans dépasser la profondeur maximale ? */
export function fits(content, node, parentId) {
    const depth = parentId ? (find(content, parentId)?.depth ?? 0) + 1 : 0;
    return depth + containerDepth(node) <= MAX_DEPTH;
}

export function updateSettings(content, id, patch) {
    const next = clone(content);
    const loc = find(next, id);
    if (!loc) return content;
    const settings = { ...loc.node.settings, ...patch };
    for (const [k, v] of Object.entries(settings)) {
        if (v === '' || v === null || v === undefined) delete settings[k];
    }
    loc.node.settings = settings;
    return next;
}

/** Remplace tous les réglages d'un élément (coller le style). */
export function replaceSettings(content, id, settings) {
    const next = clone(content);
    const loc = find(next, id);
    if (!loc) return content;
    loc.node.settings = settings;
    return next;
}

export function insert(content, parentId, index, node) {
    const next = clone(content);
    const list = listOf(next, parentId);
    if (!list || !fits(next, node, parentId)) return content;
    list.splice(index ?? list.length, 0, node);
    return next;
}

export function move(content, id, parentId, index) {
    const from = find(content, id);
    if (!from) return content;
    // On ne peut pas déposer un conteneur dans lui-même ou dans un de ses enfants.
    if (parentId && contains(from.node, parentId)) return content;
    const fromParentId = from.parent?.id ?? null;
    if (fromParentId === (parentId ?? null) && (index === from.index || index === from.index + 1)) return content;

    const next = clone(content);
    const src = find(next, id);
    const srcList = src.parent ? src.parent.children : next.elements;
    const [node] = srcList.splice(src.index, 1);
    if (fromParentId === (parentId ?? null) && index > from.index) index -= 1;
    const dest = listOf(next, parentId);
    if (!dest || !fits(next, node, parentId)) return content;
    dest.splice(index, 0, node);
    return next;
}

export function remove(content, id) {
    const next = clone(content);
    const loc = find(next, id);
    if (!loc) return content;
    (loc.parent ? loc.parent.children : next.elements).splice(loc.index, 1);
    return next;
}

/** Donne de nouveaux identifiants à un élément et à tout ce qu'il contient. */
export function reId(node) {
    node.id = uid();
    if (isContainer(node)) (node.children ?? []).forEach(reId);
    return node;
}

export function duplicate(content, id) {
    const next = clone(content);
    const loc = find(next, id);
    if (!loc) return [content, null];
    const copy = reId(clone(loc.node));
    (loc.parent ? loc.parent.children : next.elements).splice(loc.index + 1, 0, copy);
    return [next, copy.id];
}

/** Enveloppe un élément dans un nouveau conteneur, à la même place. */
export function wrap(content, id) {
    const next = clone(content);
    const loc = find(next, id);
    if (!loc) return [content, null];
    const box = makeContainer({}, [loc.node]);
    if (!fits(next, box, loc.parent?.id ?? null)) return [content, null];
    (loc.parent ? loc.parent.children : next.elements).splice(loc.index, 1, box);
    return [next, box.id];
}

/** Parcourt tous les éléments (conteneurs compris). */
export function walk(nodes, fn, parent = null) {
    for (const node of nodes ?? []) {
        fn(node, parent);
        if (isContainer(node)) walk(node.children, fn, node);
    }
}
