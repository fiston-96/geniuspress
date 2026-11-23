// Opérations immuables sur l'arbre { sections: [{ columns: [{ widgets: [] }] }] }.
import { WIDGETS } from './widgets';

export const uid = () => Math.random().toString(36).slice(2, 12).padEnd(10, '0');

const clone = (v) => structuredClone(v);

export function makeWidget(type) {
    return { id: uid(), type, settings: { ...(WIDGETS[type]?.defaults ?? {}) } };
}

export function makeColumn(width) {
    return { id: uid(), type: 'column', settings: width ? { width: Math.round(width * 1000) / 1000 } : {}, widgets: [] };
}

export function makeSection(widths = [100]) {
    return { id: uid(), type: 'section', settings: {}, columns: widths.map((w) => makeColumn(widths.length > 1 ? w : null)) };
}

/** Retrouve un nœud (section, colonne ou widget) et ses parents. */
export function locate(content, id) {
    for (const [si, s] of content.sections.entries()) {
        if (s.id === id) return { kind: 'section', node: s, section: s, si };
        for (const [ci, c] of s.columns.entries()) {
            if (c.id === id) return { kind: 'column', node: c, section: s, column: c, si, ci };
            for (const [wi, w] of c.widgets.entries()) {
                if (w.id === id) return { kind: 'widget', node: w, section: s, column: c, widget: w, si, ci, wi };
            }
        }
    }
    return null;
}

export function updateSettings(content, id, patch) {
    const next = clone(content);
    const loc = locate(next, id);
    if (!loc) return content;
    const settings = { ...loc.node.settings, ...patch };
    for (const [k, v] of Object.entries(settings)) {
        if (v === '' || v === null || v === undefined) delete settings[k];
    }
    loc.node.settings = settings;
    return next;
}

export function insertWidget(content, columnId, index, widget) {
    const next = clone(content);
    const loc = locate(next, columnId);
    if (!loc || loc.kind !== 'column') return content;
    loc.column.widgets.splice(index ?? loc.column.widgets.length, 0, widget);
    return next;
}

export function moveWidget(content, widgetId, columnId, index) {
    const from = locate(content, widgetId);
    if (!from || from.kind !== 'widget') return content;
    let next = clone(content);
    const src = locate(next, widgetId);
    const [w] = src.column.widgets.splice(src.wi, 1);
    // Si on déplace dans la même colonne, plus bas, l'index se décale d'un cran.
    if (from.column.id === columnId && index > from.wi) index -= 1;
    const dest = locate(next, columnId);
    dest.column.widgets.splice(index, 0, w);
    return next;
}

export function removeNode(content, id) {
    const next = clone(content);
    const loc = locate(next, id);
    if (!loc) return content;
    if (loc.kind === 'section') next.sections.splice(loc.si, 1);
    if (loc.kind === 'column') {
        if (loc.section.columns.length === 1) return content;
        loc.section.columns.splice(loc.ci, 1);
        normalizeWidths(loc.section);
    }
    if (loc.kind === 'widget') loc.column.widgets.splice(loc.wi, 1);
    return next;
}

function reId(node) {
    node.id = uid();
    node.columns?.forEach(reId);
    node.widgets?.forEach(reId);
    return node;
}

export function duplicateNode(content, id) {
    const next = clone(content);
    const loc = locate(next, id);
    if (!loc) return [content, null];
    const copy = reId(clone(loc.node));
    if (loc.kind === 'section') next.sections.splice(loc.si + 1, 0, copy);
    if (loc.kind === 'column') {
        if (loc.section.columns.length >= 6) return [content, null];
        loc.section.columns.splice(loc.ci + 1, 0, copy);
        normalizeWidths(loc.section);
    }
    if (loc.kind === 'widget') loc.column.widgets.splice(loc.wi + 1, 0, copy);
    return [next, copy.id];
}

export function addSection(content, index, widths) {
    const next = clone(content);
    const section = makeSection(widths);
    next.sections.splice(index ?? next.sections.length, 0, section);
    return [next, section];
}

export function moveSection(content, id, delta) {
    const next = clone(content);
    const i = next.sections.findIndex((s) => s.id === id);
    const j = i + delta;
    if (i < 0 || j < 0 || j >= next.sections.length) return content;
    [next.sections[i], next.sections[j]] = [next.sections[j], next.sections[i]];
    return next;
}

/** Change la structure de colonnes d'une section en conservant les widgets. */
export function setLayout(content, sectionId, widths) {
    const next = clone(content);
    const loc = locate(next, sectionId);
    if (!loc || loc.kind !== 'section') return content;
    const old = loc.section.columns;
    const cols = widths.map((w, i) => {
        const c = old[i] ?? makeColumn();
        c.settings = { ...c.settings, width: widths.length > 1 ? w : undefined };
        if (widths.length === 1) delete c.settings.width;
        return c;
    });
    // Les widgets des colonnes supprimées vont dans la dernière colonne.
    for (const extra of old.slice(widths.length)) cols[cols.length - 1].widgets.push(...extra.widgets);
    loc.section.columns = cols;
    return next;
}

function normalizeWidths(section) {
    const n = section.columns.length;
    for (const c of section.columns) {
        if (n === 1) delete c.settings.width;
        else c.settings.width = Math.round((100 / n) * 1000) / 1000;
    }
}
