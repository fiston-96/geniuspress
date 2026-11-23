// Petit store global partagé entre le panneau (document principal) et le
// canevas (rendu dans l'iframe par une seconde racine React).
import { useSyncExternalStore } from 'react';

const HISTORY_LIMIT = 100;

let state = {
    content: { version: 1, sections: [] },
    selected: null, // id du nœud sélectionné
    hovered: null,
    device: 'desktop',
    dirty: false,
    saving: false,
    status: 'draft',
    savedAt: null,
    revisions: [],
    drag: null, // { kind: 'new', type } | { kind: 'move', id }
    panel: 'elements',
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
