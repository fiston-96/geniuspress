// Sections globales (gabarits « block », widget « global-block ») : conçues une fois, affichées
// sur plusieurs pages ; les modifier met à jour toutes les pages qui les utilisent.
import { useEffect, useState } from 'react';
import { api } from './api';
import { labelOf } from './actions';
import { commit, getState, setState, toast } from './store';
import { find, insert, makeContainer, makeStructure, makeWidget, remove } from './tree';
import { t } from './i18n';

const rendered = new Map();

/** Rendu serveur d'une section globale (version publiée), mis en cache pendant la session. */
function load(id) {
    if (!rendered.has(id)) {
        rendered.set(id, api(getState().urls.globalRender.replace('__ID__', id)).catch(() => null));
    }
    return rendered.get(id);
}

/** Oublie le rendu en cache (après modification dans un autre onglet, par exemple). */
export function refreshGlobals() {
    rendered.clear();
    setState((s) => ({ globalsVersion: (s.globalsVersion ?? 0) + 1 }));
}

/** Aperçu du widget dans le canevas : la section telle qu'elle est publiée, non modifiable ici. */
export function GlobalPreview({ id }) {
    const [data, setData] = useState(undefined);
    useEffect(() => {
        let alive = true;
        setData(undefined);
        if (id) load(id).then((d) => { if (alive) setData(d); });
        return () => { alive = false; };
    }, [id]);

    if (!id) return <div className="gpe-empty">{t('Choisissez une section globale dans l’onglet « Mes blocs ».')}</div>;
    if (data === undefined) return <div className="gpe-empty">{t('Chargement…')}</div>;
    if (!data) return <div className="gpe-empty">{t('Section globale introuvable (supprimée ?).')}</div>;
    return (
        <div className="gpe-global">
            <style>{data.css}</style>
            <span className="gpe-global__badge">{t('Section globale : :name', { name: data.name })}</span>
            {/* Rendu du serveur à partir du contenu déjà nettoyé (ContentSanitizer). */}
            <div dangerouslySetInnerHTML={{ __html: data.html }} />
        </div>
    );
}

/** Insère une section globale (liée) après l'élément de premier niveau sélectionné, ou à la fin. */
export function insertGlobal(global) {
    const { content, selected } = getState();
    const w = makeWidget('global-block');
    w.settings = { layout: global.id };
    const loc = selected ? find(content, selected) : null;
    const topIndex = loc ? content.elements.findIndex((n) => n.id === (loc.path[0]?.id ?? loc.node.id)) : -1;
    const box = makeContainer({ ...makeStructure([100]).settings, padding: { t: 0, r: 0, b: 0, l: 0 } }, [w]);
    commit(insert(content, null, topIndex >= 0 ? topIndex + 1 : content.elements.length, box), { selected: w.id, panel: 'settings', menu: null });
}

/** Transforme l'élément en section globale : il est enregistré à part et remplacé par le widget lié. */
export async function convertToGlobal(id) {
    const { urls, content } = getState();
    const loc = find(content, id);
    if (!loc || loc.node.type === 'global-block') return;
    const name = window.prompt(t('Nom de la section globale'), labelOf(loc.node));
    if (name === null || !name.trim()) return;
    try {
        const { global } = await api(urls.globalStore, { method: 'POST', body: { name: name.trim().slice(0, 120), node: loc.node } });
        const current = getState().content;
        const at = find(current, id);
        if (!at) return;
        const w = makeWidget('global-block');
        w.settings = { layout: global.id };
        const parentId = at.parent?.id ?? null;
        const item = parentId === null ? makeContainer({ ...makeStructure([100]).settings, padding: { t: 0, r: 0, b: 0, l: 0 } }, [w]) : w;
        commit(insert(remove(current, id), parentId, at.index, item), { selected: w.id, panel: 'settings', menu: null });
        setState((s) => ({ globals: [...(s.globals ?? []), global].sort((a, b) => a.name.localeCompare(b.name)) }));
        toast(t('Section globale « :name » créée : modifiez-la une fois, toutes les pages suivent.', { name: global.name }), 'success');
    } catch (e) {
        toast(e.message, 'error');
    }
}
