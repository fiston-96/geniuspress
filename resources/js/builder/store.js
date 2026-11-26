// Petit store global partagé entre le panneau (document principal) et le
// canevas (rendu dans l'iframe par une seconde racine React).
import { useSyncExternalStore } from 'react';

const HISTORY_LIMIT = 100;

let state = {
    content: { version: 2, elements: [] },
    selected: null, // id de l'élément sélectionné
    hovered: null, // id de l'élément survolé (canevas ou arborescence)
    design: { colors: [], fonts: [] }, // couleurs et polices globales du site
    drop: null, // { parentId, index, line: { x, y, w, h } } pendant un glisser-déposer
    menu: null, // menu contextuel { x, y, id }
    device: 'desktop',
    dirty: false,
    saving: false,
    status: 'draft',
    savedAt: null,
    revisions: [],
    drag: null, // { kind: 'new', type } | { kind: 'structure', widths } | { kind: 'move', id }
    panel: 'elements',
    // Panneau de gauche affiché ou masqué (choix mémorisé dans ce navigateur) : le canevas gagne toute la largeur.
    panelOpen: (() => { try { return localStorage.getItem('geniuspress:panel') !== 'closed'; } catch { return true; } })(),
    past: [],
    future: [],
    toast: null,
};

const listeners = new Set();

export const getState = () => state;

export function setState(patch) {
    state = { ...state, ...(typeof patch === 'function' ? patch(state) : patch) };
    listeners.forEach((l) => l());
}

export function useStore(selector) {
    return useSyncExternalStore(
        (l) => {
            listeners.add(l);
            return () => listeners.delete(l);
        },
        () => selector(state),
    );
}

/** Modifie le contenu en l'enregistrant dans l'historique (annuler / rétablir). */
export function commit(nextContent, extra = {}) {
    if (nextContent === state.content) return;
    // Une frappe en cours de regroupement devient d'abord sa propre étape.
    flushBatch();
    setState({
        content: nextContent,
        past: [...state.past.slice(-HISTORY_LIMIT + 1), state.content],
        future: [],
        dirty: true,
        ...extra,
    });
}

// Regroupe les modifications rapides (frappe au clavier, curseurs) en une seule étape d'historique.
let batchTimer = null;
let batchBase = null;
export function commitBatched(nextContent) {
    if (batchBase === null) batchBase = state.content;
    clearTimeout(batchTimer);
    batchTimer = setTimeout(() => {
        setState({ past: [...state.past.slice(-HISTORY_LIMIT + 1), batchBase], future: [] });
        batchBase = null;
    }, 600);
    setState({ content: nextContent, dirty: true });
}

function flushBatch() {
    if (batchBase !== null) {
        clearTimeout(batchTimer);
        setState({ past: [...state.past.slice(-HISTORY_LIMIT + 1), batchBase], future: [] });
        batchBase = null;
    }
}

export function undo() {
    flushBatch();
    if (!state.past.length) return;
    const prev = state.past[state.past.length - 1];
    setState({ content: prev, past: state.past.slice(0, -1), future: [state.content, ...state.future], dirty: true });
}

export function redo() {
    flushBatch();
    if (!state.future.length) return;
    const [next, ...rest] = state.future;
    setState({ content: next, past: [...state.past, state.content], future: rest, dirty: true });
}

let toastTimer;
export function toast(message, type = 'info') {
    clearTimeout(toastTimer);
    setState({ toast: { message, type } });
    toastTimer = setTimeout(() => setState({ toast: null }), 3500);
}
