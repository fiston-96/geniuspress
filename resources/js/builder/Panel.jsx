import { useState } from 'react';
import AiPanel from './AiPanel';
import AiTextTools from './AiTextTools';
import { api } from './api';
import { Control } from './controls';
import { commit, commitBatched, getState, setState, toast, useStore } from './store';
import { find, isContainer, updateSettings } from './tree';
import { allWidgets, getWidget } from './registry';
import { CONTAINER_CONTROLS, LAYOUTS, STYLE_CONTROLS } from './widgets';
import { DEVICES } from './style';
import { addStructure, addWidget, copyEl, dropAt, duplicateEl, labelOf, pasteStyle, readClipboard, removeEl, select, wrapEl } from './actions';
import { locale, t } from './i18n';

const TABS = [
    { id: 'content', label: t('Contenu') },
    { id: 'style', label: t('Style') },
    { id: 'advanced', label: t('Avancé') },
];

export const DEVICE_ICONS = {
    desktop: { label: t('Ordinateur'), icon: 'M3 4h18v12H3zM8 20h8M12 16v4' },
    tablet: { label: t('Tablette'), icon: 'M6 2h12v20H6zM11 18h2' },
    mobile: { label: t('Mobile'), icon: 'M8 2h8v20H8zM11 18h2' },
};

const Svg = ({ d, className = 'h-3.5 w-3.5' }) => (
    <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><path d={d} /></svg>
);

export default function Panel({ boot }) {
    const urls = boot.urls;
    const panel = useStore((s) => s.panel);
    const selected = useStore((s) => s.selected);
    const open = useStore((s) => s.panelOpen);

    const tabs = [
        { id: 'elements', label: t('Éléments') },
        { id: 'settings', label: t('Réglages'), disabled: !selected },
        { id: 'navigator', label: t('Structure') },
        { id: 'ai', label: t('✨ IA') },
        { id: 'history', label: t('Historique') },
    ];

    if (!open) return null;

    return (
        <aside className="flex w-80 shrink-0 flex-col border-r border-slate-200 bg-white">
            {/* Onglets : ils défilent au lieu d'être coupés quand la place manque. */}
            <div className="flex overflow-x-auto border-b border-slate-200 [scrollbar-width:none]">
                {tabs.map((tab) => (
                    <button key={tab.id} disabled={tab.disabled} onClick={() => setState({ panel: tab.id })}
                        className={`shrink-0 grow whitespace-nowrap border-b-2 px-2.5 py-2.5 text-[12px] font-medium transition disabled:opacity-40 ${panel === tab.id ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-800'}`}>
                        {tab.label}
                    </button>
                ))}
            </div>
            <div className="flex-1 overflow-y-auto">
                {panel === 'elements' && <Elements />}
                {panel === 'settings' && selected && <Settings key={selected} id={selected} boot={boot} />}
                {panel === 'navigator' && <Navigator />}
                {panel === 'ai' && <AiPanel boot={boot} />}
                {panel === 'history' && <History urls={urls} />}
            </div>
        </aside>
    );
}

function Elements() {
    const startDrag = (e, drag) => {
        e.dataTransfer.effectAllowed = 'copy';
        e.dataTransfer.setData('text/plain', drag.type ?? 'structure');
        setState({ drag });
    };
    const endDrag = () => setState({ drag: null, drop: null });

    return (
        <div className="space-y-5 p-4">
            <p className="text-xs text-slate-500">{t('Glissez un élément sur la page, ou cliquez pour l’ajouter dans le conteneur sélectionné.')}</p>
            <div>
                <h3 className="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{t('Structure')}</h3>
                <div className="grid grid-cols-3 gap-2">
                    {LAYOUTS.map((l) => (
                        <button key={l.label} title={l.label} draggable
                            onDragStart={(e) => startDrag(e, { kind: 'structure', widths: l.widths })} onDragEnd={endDrag}
                            onClick={() => addStructure(l.widths)}
                            className="flex h-10 cursor-grab gap-0.5 rounded-md border border-slate-200 p-1 hover:border-brand-400">
                            {l.widths.map((w, i) => <span key={i} className="rounded-sm bg-brand-200" style={{ flex: w }} />)}
                        </button>
                    ))}
                </div>
            </div>
            {Object.entries(allWidgets().reduce((groups, def) => {
                // Groupes de la palette : widgets de contenu, puis widgets dynamiques du site.
                (groups[def.group ?? t('Widgets')] ??= []).push(def);
                return groups;
            }, {})).map(([group, defs]) => (
                <div key={group}>
                    <h3 className="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{group}</h3>
                    <div className="grid grid-cols-2 gap-2">
                        {defs.map((def) => (
                            <button key={def.type} draggable
                                onDragStart={(e) => startDrag(e, { kind: 'new', type: def.type })} onDragEnd={endDrag}
                                onClick={() => addWidget(def.type)}
                                className="flex cursor-grab flex-col items-center gap-2 rounded-lg border border-slate-200 bg-white px-2 py-4 text-xs font-medium text-slate-700 transition hover:border-brand-400 hover:text-brand-700 hover:shadow-sm active:cursor-grabbing">
                                <svg className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.6" strokeLinecap="round" strokeLinejoin="round"><path d={def.icon} /></svg>
                                {def.label}
                            </button>
                        ))}
                    </div>
                </div>
            ))}
        </div>
    );
}

/** Valeur héritée d'un écran plus grand (tablette ← ordinateur, mobile ← tablette ← ordinateur). */
function inherited(settings, key, device) {
    if (device === 'tablet') return settings[key];
    if (device === 'mobile') return settings[`${key}_tablet`] ?? settings[key];
    return undefined;
}

function DeviceSwitch({ device }) {
    return (
        <span className="inline-flex items-center gap-0.5" title={t('Réglable pour chaque appareil')}>
            {Object.entries(DEVICE_ICONS).map(([id, d]) => (
                <button key={id} title={d.label} onClick={() => setState({ device: id })}
                    className={`rounded p-0.5 ${device === id ? 'bg-brand-100 text-brand-700' : 'text-slate-300 hover:text-slate-600'}`}>
                    <Svg d={d.icon} className="h-3 w-3" />
                </button>
            ))}
        </span>
    );
}

function Settings({ id, boot }) {
    const urls = boot.urls;
    const content = useStore((s) => s.content);
    const device = useStore((s) => s.device);
    const [tab, setTab] = useState('content');
    const [mode, setMode] = useState('normal');
    const loc = find(content, id);
    if (!loc) return <p className="p-4 text-sm text-slate-500">{t('Élément introuvable.')}</p>;

    const { node } = loc;
    const container = isContainer(node);
    const controls = container ? [...CONTAINER_CONTROLS, ...STYLE_CONTROLS] : (getWidget(node.type)?.controls ?? []);
    const inTab = controls.filter((c) => (c.tab ?? 'content') === tab);
    const hoverable = tab === 'style' && inTab.some((c) => c.hover);
    const hover = hoverable && mode === 'hover';

    const shown = inTab.filter((c) => {
        if (c.type === 'heading') return true;
        if (c.when && !c.when(node.settings, loc.depth)) return false;
        return hover ? !!c.hover : c.hover !== 'only';
    });
    // On retire les titres de groupe qui n'ont plus aucun réglage visible.
    const visible = shown.filter((c, i) => c.type !== 'heading' || (shown[i + 1] && shown[i + 1].type !== 'heading'));

    const keyOf = (c) => {
        if (hover && c.hover !== 'only') return `${c.key}_hover`;
        return c.responsive ? c.key + DEVICES[device] : c.key;
    };
    const placeholderOf = (c) => {
        if (hover && c.hover !== 'only') return node.settings[c.key];
        return c.responsive ? inherited(node.settings, c.key, device) : undefined;
    };
    const onChange = (key, value) => commitBatched(updateSettings(getState().content, id, { [key]: value }));

    const crumbs = [...loc.path, node];
    const hasClip = !!readClipboard();
    const tool = 'rounded p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-900 disabled:opacity-30';

    return (
        <div>
            <div className="border-b border-slate-200 px-4 py-3">
                <nav className="mb-1 flex flex-wrap items-center gap-1 text-[11px] text-slate-400">
                    {crumbs.map((c, i) => (
                        <span key={c.id} className="flex items-center gap-1">
                            {i > 0 && '›'}
                            <button className={c.id === id ? 'font-semibold text-slate-700' : 'hover:text-brand-600'} onClick={() => setState({ selected: c.id })}>{labelOf(c)}</button>
                        </span>
                    ))}
                </nav>
                <div className="flex items-center justify-between gap-2">
                    <h2 className="font-semibold">{labelOf(node)}</h2>
                    <div className="flex items-center">
                        <button className={tool} title={t('Copier (Ctrl+C)')} onClick={() => copyEl(id)}><Svg d="M9 9h11v11H9zM5 15H4V4h11v1" /></button>
                        <button className={tool} title={t('Coller le style (Ctrl+Maj+V)')} disabled={!hasClip} onClick={() => pasteStyle(id)}><Svg d="M4 20l7-7M14 4l6 6-8 8H6v-6z" /></button>
                        <button className={tool} title={t('Envelopper dans un conteneur')} onClick={() => wrapEl(id)}><Svg d="M3 3h18v18H3zM8 8h8v8H8z" /></button>
                        <button className={tool} title={t('Dupliquer (Ctrl+D)')} onClick={() => duplicateEl(id)}><Svg d="M8 8h12v12H8zM4 16V4h12" /></button>
                        <button className={tool} title={t('Supprimer (Suppr)')} onClick={() => removeEl(id)}><Svg d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" /></button>
                    </div>
                </div>
            </div>
            <div className="flex border-b border-slate-200 px-2">
                {TABS.map((tb) => (
                    <button key={tb.id} onClick={() => setTab(tb.id)}
                        className={`border-b-2 px-3 py-2 text-xs font-medium ${tab === tb.id ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:text-slate-800'}`}>
                        {tb.label}
                    </button>
                ))}
            </div>
            <div className="space-y-4 p-4">
                {hoverable && (
                    <div className="flex gap-1 rounded-md bg-slate-100 p-0.5">
                        {[['normal', t('Normal')], ['hover', t('Au survol')]].map(([m, label]) => (
                            <button key={m} onClick={() => setMode(m)}
                                className={`flex-1 rounded px-2 py-1 text-xs font-medium ${mode === m ? 'bg-white text-brand-700 shadow-sm' : 'text-slate-600'}`}>{label}</button>
                        ))}
                    </div>
                )}
                {tab === 'content' && !container && <AiTextTools boot={boot} widget={node} />}
                {visible.filter((c) => c.type !== 'heading').length === 0 && <p className="text-xs text-slate-500">{t('Aucun réglage dans cet onglet.')}</p>}
                {visible.map((c, i) => {
                    if (c.type === 'heading') return <Control key={`h${i}`} control={c} />;
                    const key = keyOf(c);
                    return (
                        <Control key={key} control={{ ...c, key }} value={node.settings[key]} placeholder={placeholderOf(c)} onChange={onChange} urls={urls}
                            extra={c.responsive && !hover ? <DeviceSwitch device={device} /> : null} />
                    );
                })}
            </div>
        </div>
    );
}

/** Arborescence de la page : sélectionner, survoler, réorganiser par glisser-déposer. */
function Navigator() {
    const content = useStore((s) => s.content);
    return (
        <div className="p-2" onMouseLeave={() => setState({ hovered: null })}>
            <p className="px-2 pb-2 text-xs text-slate-500">{t('Cliquez pour sélectionner, glissez pour déplacer. Déposez au milieu d’un conteneur pour y placer l’élément.')}</p>
            {content.elements.length === 0 && <p className="px-2 text-sm text-slate-400">{t('La page est vide.')}</p>}
            {content.elements.map((node) => <NavNode key={node.id} node={node} depth={0} />)}
        </div>
    );
}

function summary(node) {
    const s = node.settings;
    const text = s.text ?? (s.html ? s.html.replace(/<[^>]+>/g, ' ') : '') ?? '';
    return String(text).replace(/\s+/g, ' ').trim().slice(0, 40);
}

function NavNode({ node, depth }) {
    const selected = useStore((s) => s.selected === node.id);
    const hovered = useStore((s) => s.hovered === node.id);
    const [open, setOpen] = useState(true);
    const [zone, setZone] = useState(null);
    const container = isContainer(node);

    const onDragOver = (e) => {
        if (!getState().drag) return;
        e.preventDefault();
        e.stopPropagation();
        const r = e.currentTarget.getBoundingClientRect();
        const y = (e.clientY - r.top) / r.height;
        setZone(container ? (y < 0.25 ? 'before' : y > 0.75 ? 'after' : 'inside') : y < 0.5 ? 'before' : 'after');
    };
    const onDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        const where = zone;
        setZone(null);
        const loc = find(getState().content, node.id);
        if (!loc || !where) return;
        if (where === 'inside') dropAt(node.id, node.children.length);
        else dropAt(loc.parent?.id ?? null, loc.index + (where === 'after' ? 1 : 0));
    };

    return (
        <div>
            <div
                draggable
                onDragStart={(e) => { e.stopPropagation(); e.dataTransfer.setData('text/plain', node.id); setState({ drag: { kind: 'move', id: node.id } }); }}
                onDragEnd={() => { setZone(null); setState({ drag: null, drop: null }); }}
                onDragOver={onDragOver}
                onDragLeave={() => setZone(null)}
                onDrop={onDrop}
                onClick={() => select(node.id)}
                onMouseEnter={() => setState({ hovered: node.id })}
                className={`relative flex cursor-pointer items-center gap-1.5 rounded-md py-1.5 pr-2 text-[13px] ${selected ? 'bg-brand-600 text-white' : hovered ? 'bg-brand-50 text-brand-800' : 'text-slate-700 hover:bg-slate-50'} ${zone === 'inside' ? 'ring-2 ring-inset ring-brand-500' : ''}`}
                style={{ paddingLeft: 8 + depth * 14 }}
            >
                {zone === 'before' && <span className="absolute inset-x-1 -top-0.5 h-1 rounded bg-brand-500" />}
                {zone === 'after' && <span className="absolute inset-x-1 -bottom-0.5 h-1 rounded bg-brand-500" />}
                {container ? (
                    <button className="w-4 text-center text-[10px] opacity-70" onClick={(e) => { e.stopPropagation(); setOpen(!open); }} aria-label={open ? t('Replier') : t('Déplier')}>{open ? '▾' : '▸'}</button>
                ) : <span className="w-4" />}
                <Svg d={container ? 'M3 3h18v18H3z' : (getWidget(node.type)?.icon ?? 'M4 4h16v16H4z')} />
                <span className="font-medium">{labelOf(node)}</span>
                {container && node.settings.direction?.startsWith('row') && <span className="text-[10px] opacity-60">→</span>}
                {!container && <span className="truncate text-xs opacity-60">{summary(node)}</span>}
                {node.settings.css_id && <span className="ml-auto text-[10px] opacity-60">#{node.settings.css_id}</span>}
            </div>
            {container && open && (node.children ?? []).map((child) => <NavNode key={child.id} node={child} depth={depth + 1} />)}
        </div>
    );
}

function History({ urls }) {
    const revisions = useStore((s) => s.revisions);
    const [loading, setLoading] = useState(null);

    const restore = async (rev) => {
        if (!confirm(t('Remplacer le contenu actuel par cette version ? Vous pourrez annuler avec Ctrl+Z.'))) return;
        setLoading(rev.id);
        try {
            const { content } = await api(urls.restore.replace('__ID__', rev.id), { method: 'POST' });
            commit(content, { selected: null, panel: 'elements' });
            toast(t('Version restaurée. Enregistrez pour la conserver.'));
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setLoading(null);
        }
    };

    return (
        <div className="p-4">
            <p className="mb-3 text-xs text-slate-500">{t('Chaque enregistrement crée une version. Les 30 dernières sont conservées.')}</p>
            {revisions.length === 0 && <p className="text-sm text-slate-500">{t('Aucune version enregistrée.')}</p>}
            <ul className="space-y-1">
                {revisions.map((r) => (
                    <li key={r.id} className="flex items-center justify-between gap-2 rounded-md px-2 py-2 hover:bg-slate-50">
                        <div className="min-w-0">
                            <p className="text-sm font-medium">{r.note}</p>
                            <p className="truncate text-xs text-slate-500">{new Date(r.created_at).toLocaleString(locale())} · {r.user?.name ?? '—'}</p>
                        </div>
                        <button className="shrink-0 text-xs font-medium text-brand-600 hover:text-brand-500 disabled:opacity-50" disabled={loading === r.id} onClick={() => restore(r)}>
                            {t('Restaurer')}
                        </button>
                    </li>
                ))}
            </ul>
        </div>
    );
}
