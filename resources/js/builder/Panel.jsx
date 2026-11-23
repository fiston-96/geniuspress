import { useState } from 'react';
import AiPanel from './AiPanel';
import AiTextTools from './AiTextTools';
import { api } from './api';
import { Control } from './controls';
import { commit, commitBatched, getState, setState, toast, useStore } from './store';
import { addSection, insertWidget, locate, makeWidget, updateSettings } from './tree';
import { allWidgets, getWidget } from './registry';
import { COLUMN_CONTROLS, SECTION_CONTROLS } from './widgets';

const TABS = [
    { id: 'content', label: 'Contenu' },
    { id: 'style', label: 'Style' },
    { id: 'advanced', label: 'Avancé' },
];

export default function Panel({ boot }) {
    const urls = boot.urls;
    const panel = useStore((s) => s.panel);
    const selected = useStore((s) => s.selected);

    const tabs = [
        { id: 'elements', label: 'Éléments' },
        { id: 'settings', label: 'Réglages', disabled: !selected },
        { id: 'ai', label: '✨ IA' },
        { id: 'history', label: 'Historique' },
    ];

    return (
        <aside className="flex w-80 shrink-0 flex-col border-r border-slate-200 bg-white">
            <div className="flex border-b border-slate-200">
                {tabs.map((t) => (
                    <button key={t.id} disabled={t.disabled} onClick={() => setState({ panel: t.id })}
                        className={`flex-1 border-b-2 px-1 py-2.5 text-[13px] font-medium transition disabled:opacity-40 ${panel === t.id ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-800'}`}>
                        {t.label}
                    </button>
                ))}
            </div>
            <div className="flex-1 overflow-y-auto">
                {panel === 'elements' && <Elements />}
                {panel === 'settings' && selected && <Settings key={selected} id={selected} boot={boot} />}
                {panel === 'ai' && <AiPanel boot={boot} />}
                {panel === 'history' && <History urls={urls} />}
            </div>
        </aside>
    );
}

function Elements() {
    // Un clic ajoute l'élément dans la colonne sélectionnée (ou une nouvelle section).
    const add = (type) => {
        const { content, selected } = getState();
        const w = makeWidget(type);
        const loc = selected ? locate(content, selected) : null;
        if (loc?.column) {
            const index = loc.kind === 'widget' ? loc.wi + 1 : loc.column.widgets.length;
            commit(insertWidget(content, loc.column.id, index, w), { selected: w.id, panel: 'settings' });
        } else {
            const [next, section] = addSection(content, loc?.kind === 'section' ? loc.si + 1 : undefined, [100]);
            commit(insertWidget(next, section.columns[0].id, 0, w), { selected: w.id, panel: 'settings' });
        }
    };

    return (
        <div className="p-4">
            <p className="mb-3 text-xs text-slate-500">Glissez un élément sur la page, ou cliquez pour l’ajouter.</p>
            <div className="grid grid-cols-2 gap-2">
                {allWidgets().map((def) => ({ type: def.type, def })).map(({ type, def }) => (
                    <button key={type} draggable
                        onDragStart={(e) => { e.dataTransfer.effectAllowed = 'copy'; e.dataTransfer.setData('text/plain', type); setState({ drag: { kind: 'new', type } }); }}
                        onDragEnd={() => setState({ drag: null })}
                        onClick={() => add(type)}
                        className="flex cursor-grab flex-col items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-4 text-xs font-medium text-slate-700 transition hover:border-indigo-400 hover:text-indigo-700 hover:shadow-sm active:cursor-grabbing">
                        <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"><path d={def.icon} /></svg>
                        {def.label}
                    </button>
                ))}
            </div>
        </div>
    );
}

function Settings({ id, boot }) {
    const urls = boot.urls;
    const content = useStore((s) => s.content);
    const [tab, setTab] = useState('content');
    const loc = locate(content, id);
    if (!loc) return <p className="p-4 text-sm text-slate-500">Élément introuvable.</p>;

    const { node, kind } = loc;
    const controls = kind === 'section' ? SECTION_CONTROLS : kind === 'column' ? COLUMN_CONTROLS : (getWidget(node.type)?.controls ?? []);
    const visible = controls.filter((c) => (c.tab ?? 'content') === tab);
    const title = kind === 'section' ? 'Section' : kind === 'column' ? 'Colonne' : (getWidget(node.type)?.label ?? node.type);

    const onChange = (key, value) => commitBatched(updateSettings(getState().content, id, { [key]: value }));

    const crumbs = [
        { id: loc.section.id, label: 'Section' },
        loc.column && { id: loc.column.id, label: 'Colonne' },
        loc.widget && { id: loc.widget.id, label: getWidget(loc.widget.type)?.label ?? loc.widget.type },
    ].filter(Boolean);

    return (
        <div>
            <div className="border-b border-slate-200 px-4 py-3">
                <nav className="mb-1 flex flex-wrap items-center gap-1 text-[11px] text-slate-400">
                    {crumbs.map((c, i) => (
                        <span key={c.id} className="flex items-center gap-1">
                            {i > 0 && '›'}
                            <button className={c.id === id ? 'font-semibold text-slate-700' : 'hover:text-indigo-600'} onClick={() => setState({ selected: c.id })}>{c.label}</button>
                        </span>
                    ))}
                </nav>
                <h2 className="font-semibold">{title}</h2>
            </div>
            <div className="flex border-b border-slate-200 px-2">
                {TABS.map((t) => (
                    <button key={t.id} onClick={() => setTab(t.id)}
                        className={`border-b-2 px-3 py-2 text-xs font-medium ${tab === t.id ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-slate-500 hover:text-slate-800'}`}>
                        {t.label}
                    </button>
                ))}
            </div>
            <div className="space-y-4 p-4">
                {tab === 'content' && kind === 'widget' && <AiTextTools boot={boot} widget={node} />}
                {visible.length === 0 && <p className="text-xs text-slate-500">Aucun réglage dans cet onglet.</p>}
                {visible.map((c) => (
                    <Control key={c.key} control={c} value={node.settings[c.key]} onChange={onChange} urls={urls} />
                ))}
            </div>
        </div>
    );
}

function History({ urls }) {
    const revisions = useStore((s) => s.revisions);
    const [loading, setLoading] = useState(null);

    const restore = async (rev) => {
        if (!confirm('Remplacer le contenu actuel par cette version ? Vous pourrez annuler avec Ctrl+Z.')) return;
        setLoading(rev.id);
        try {
            const { content } = await api(urls.restore.replace('__ID__', rev.id), { method: 'POST' });
            commit(content, { selected: null, panel: 'elements' });
            toast('Version restaurée. Enregistrez pour la conserver.');
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setLoading(null);
        }
    };

    return (
        <div className="p-4">
            <p className="mb-3 text-xs text-slate-500">Chaque enregistrement crée une version. Les 30 dernières sont conservées.</p>
            {revisions.length === 0 && <p className="text-sm text-slate-500">Aucune version enregistrée.</p>}
            <ul className="space-y-1">
                {revisions.map((r) => (
                    <li key={r.id} className="flex items-center justify-between gap-2 rounded-md px-2 py-2 hover:bg-slate-50">
                        <div className="min-w-0">
                            <p className="text-sm font-medium">{r.note}</p>
                            <p className="truncate text-xs text-slate-500">{new Date(r.created_at).toLocaleString('fr-FR')} · {r.user?.name ?? '—'}</p>
                        </div>
                        <button className="shrink-0 text-xs font-medium text-indigo-600 hover:text-indigo-500 disabled:opacity-50" disabled={loading === r.id} onClick={() => restore(r)}>
                            Restaurer
                        </button>
                    </li>
                ))}
            </ul>
        </div>
    );
}
