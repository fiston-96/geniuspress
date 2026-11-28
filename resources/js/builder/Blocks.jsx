// « Mes blocs » : conteneurs et widgets enregistrés pour être réutilisés sur toutes
// les pages du site (bibliothèque partagée par l'équipe, gardée sur le serveur).
import { useEffect, useState } from 'react';
import { api } from './api';
import { insertCopy, labelOf } from './actions';
import { getState, setState, toast, useStore } from './store';
import { find, isContainer } from './tree';
import { getWidget } from './registry';
import { t } from './i18n';
import { IconButton, PathIcon, UiIcon } from './ui';

async function loadBlocks() {
    const { urls } = getState();
    if (!urls?.blocks) return;
    try {
        const { blocks } = await api(urls.blocks);
        setState({ blocks });
    } catch (e) {
        setState({ blocks: [] });
        toast(e.message, 'error');
    }
}

/** Enregistre l'élément (et tout son contenu) dans « Mes blocs ». */
export async function saveAsBlock(id) {
    const { urls, content } = getState();
    const loc = find(content, id);
    if (!loc || !urls?.blocks) return;
    const name = window.prompt(t('Nom du bloc'), labelOf(loc.node));
    if (name === null || !name.trim()) return;
    try {
        const { block } = await api(urls.blocks, { method: 'POST', body: { name: name.trim().slice(0, 120), node: loc.node } });
        setState((s) => ({ blocks: [block, ...(s.blocks ?? [])] }));
        toast(t('Bloc « :name » enregistré dans Mes blocs', { name: block.name }), 'success');
    } catch (e) {
        toast(e.message, 'error');
    }
}

function iconOf(node) {
    return isContainer(node) ? null : getWidget(node.type)?.icon ?? 'M4 4h16v16H4z';
}

/** Nombre d'éléments d'un bloc (lui compris). */
function countOf(node) {
    return 1 + (node.children ?? []).reduce((n, c) => n + countOf(c), 0);
}

export default function Blocks() {
    const blocks = useStore((s) => s.blocks);
    const selected = useStore((s) => s.selected);
    const [query, setQuery] = useState('');
    useEffect(() => { if (blocks === undefined || blocks === null) loadBlocks(); }, [blocks]);

    const remove = async (block) => {
        if (!confirm(t('Supprimer le bloc « :name » ? Les pages qui l’utilisent déjà ne changent pas.', { name: block.name }))) return;
        try {
            await api(getState().urls.blockDelete.replace('__ID__', block.id), { method: 'DELETE' });
            setState((s) => ({ blocks: (s.blocks ?? []).filter((b) => b.id !== block.id) }));
        } catch (e) {
            toast(e.message, 'error');
        }
    };

    const list = (blocks ?? []).filter((b) => !query || b.name.toLowerCase().includes(query.toLowerCase()));

    return (
        <div className="space-y-3 p-4">
            <p className="text-xs text-slate-500">{t('Enregistrez un conteneur ou un widget avec l’icône signet de ses réglages, puis glissez-le sur n’importe quelle page.')}</p>
            {(blocks?.length ?? 0) > 6 && (
                <label className="relative block">
                    <span className="pointer-events-none absolute inset-y-0 left-2.5 grid place-items-center text-slate-400"><UiIcon name="search" /></span>
                    <input type="search" value={query} onChange={(e) => setQuery(e.target.value)} placeholder={t('Rechercher un bloc')} aria-label={t('Rechercher un bloc')}
                        className="block w-full rounded-lg border border-slate-300 bg-slate-50 py-2 pl-9 pr-3 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20" />
                </label>
            )}
            {blocks === null || blocks === undefined ? <p className="text-sm text-slate-400">{t('Chargement…')}</p> : null}
            {blocks?.length === 0 && (
                <div className="grid place-items-center gap-2 rounded-xl border-2 border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400">
                    <UiIcon name="library-big" className="h-8 w-8" strokeWidth={1.4} />
                    {t('Aucun bloc pour l’instant.')}
                </div>
            )}
            <ul className="space-y-1.5">
                {list.map((b) => (
                    <li key={b.id} draggable
                        onDragStart={(e) => { e.dataTransfer.effectAllowed = 'copy'; e.dataTransfer.setData('text/plain', 'block'); setState({ drag: { kind: 'block', node: b.node } }); }}
                        onDragEnd={() => setState({ drag: null, drop: null })}
                        className="group flex cursor-grab items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-2 hover:border-brand-400 active:cursor-grabbing">
                        <span className="grid h-8 w-8 shrink-0 place-items-center rounded-md bg-brand-50 text-brand-700">
                            {iconOf(b.node) ? <PathIcon d={iconOf(b.node)} /> : <UiIcon name="square-dashed" />}
                        </span>
                        <span className="min-w-0 flex-1">
                            <span className="block truncate text-sm font-medium text-slate-800">{b.name}</span>
                            <span className="block truncate text-[11px] text-slate-400">{labelOf(b.node)} · {t(':count élément(s)', { count: countOf(b.node) })}{b.user ? ` · ${b.user}` : ''}</span>
                        </span>
                        <IconButton icon="plus" label={t('Insérer')} onClick={() => insertCopy(b.node, selected)} className="h-7 w-7 text-slate-500 hover:bg-brand-50 hover:text-brand-700" iconClass="h-3.5 w-3.5" />
                        <IconButton icon="trash-2" label={t('Supprimer')} onClick={() => remove(b)} className="h-7 w-7 text-slate-400 opacity-0 hover:bg-red-50 hover:text-red-600 focus:opacity-100 group-hover:opacity-100" iconClass="h-3.5 w-3.5" />
                    </li>
                ))}
            </ul>
        </div>
    );
}
