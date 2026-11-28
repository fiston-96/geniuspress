import { useEffect, useRef, useState } from 'react';
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
import { IconButton, PathIcon, UiIcon } from './ui';
import Blocks, { saveAsBlock } from './Blocks';
import { convertToGlobal } from './globals';

const TABS = [
    { id: 'content', label: t('Contenu'), icon: 'pencil' },
    { id: 'style', label: t('Style'), icon: 'palette' },
    { id: 'advanced', label: t('Avancé'), icon: 'settings' },
];

const DEVICE_ICONS = {
    desktop: { label: t('Ordinateur'), icon: 'monitor' },
    tablet: { label: t('Tablette'), icon: 'tablet' },
    mobile: { label: t('Mobile'), icon: 'smartphone' },
};

/** Texte comparable sans accents ni majuscules (recherche de widgets). */
const fold = (v) => String(v ?? '').normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();

export default function Panel({ boot }) {
    const urls = boot.urls;
    const panel = useStore((s) => s.panel);
    const selected = useStore((s) => s.selected);
    const open = useStore((s) => s.panelOpen);

    const tabs = [
        { id: 'elements', label: t('Éléments'), icon: 'square-plus' },
        { id: 'settings', label: t('Réglages'), icon: 'sliders-horizontal', disabled: !selected },
        { id: 'navigator', label: t('Structure'), icon: 'list-tree' },
        { id: 'blocks', label: t('Mes blocs'), icon: 'library-big' },
        { id: 'ai', label: t('Assistant IA'), icon: 'sparkles' },
        { id: 'history', label: t('Historique'), icon: 'history' },
    ];

    if (!open) return null;

    return (
        <aside className="flex w-80 shrink-0 flex-col border-r border-slate-200 bg-white">
            {/* Onglets en icônes (infobulle et nom accessible traduits). */}
            <div className="flex border-b border-slate-200" role="tablist" aria-label={t('Panneau')}>
                {tabs.map((tab) => (
                    <IconButton key={tab.id} icon={tab.icon} label={tab.label} disabled={tab.disabled} onClick={() => setState({ panel: tab.id })}
                        role="tab" aria-selected={panel === tab.id}
                        className={`h-11 flex-1 rounded-none border-b-2 ${panel === tab.id ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:bg-slate-50 hover:text-slate-800'}`}
                        iconClass="h-[18px] w-[18px]" />
                ))}
            </div>
            <div className="flex-1 overflow-y-auto">
                {panel === 'elements' && <Elements />}
                {panel === 'settings' && selected && <Settings key={selected} id={selected} boot={boot} />}
                {panel === 'navigator' && <Navigator />}
                {panel === 'blocks' && <Blocks />}
                {panel === 'ai' && <AiPanel boot={boot} />}
                {panel === 'history' && <History urls={urls} />}
            </div>
        </aside>
    );
}

function Elements() {
    const [query, setQuery] = useState('');
    const focus = useStore((s) => s.searchFocus);
    const input = useRef(null);
    // Ctrl+K : le champ de recherche prend le focus (et son texte est sélectionné).
    useEffect(() => { if (focus) { input.current?.focus(); input.current?.select(); } }, [focus]);

    const startDrag = (e, drag) => {
        e.dataTransfer.effectAllowed = 'copy';
        e.dataTransfer.setData('text/plain', drag.type ?? 'structure');
        setState({ drag });
    };
    const endDrag = () => setState({ drag: null, drop: null });

    const words = fold(query).split(/\s+/).filter(Boolean);
    const matches = (def) => {
        const haystack = fold([def.label, def.type, def.group, ...(def.keywords ?? [])].join(' '));
        return words.every((w) => haystack.includes(w));
    };
    const defs = allWidgets().filter((def) => !def.hidden && (!words.length || matches(def)));
    const groups = Object.entries(defs.reduce((acc, def) => {
        // Groupes de la palette : widgets de contenu, puis widgets dynamiques du site.
        (acc[def.group ?? t('Widgets')] ??= []).push(def);
        return acc;
    }, {}));
    // Entrée : ajoute le premier résultat.
    const onKeyDown = (e) => {
        if (e.key === 'Enter' && defs[0]) { e.preventDefault(); addWidget(defs[0].type); setQuery(''); }
        if (e.key === 'Escape') { e.stopPropagation(); setQuery(''); e.currentTarget.blur(); }
    };

    return (
        <div className="space-y-5 p-4">
            <label className="relative block">
                <span className="pointer-events-none absolute inset-y-0 left-2.5 grid place-items-center text-slate-400"><UiIcon name="search" /></span>
                <input ref={input} type="search" value={query} onChange={(e) => setQuery(e.target.value)} onKeyDown={onKeyDown}
                    placeholder={t('Rechercher un widget')} aria-label={t('Rechercher un widget')}
                    className="block w-full rounded-lg border border-slate-300 bg-slate-50 py-2 pl-9 pr-14 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20" />
                {!query && <kbd className="pointer-events-none absolute inset-y-0 right-2 my-auto h-5 rounded border border-slate-200 bg-white px-1.5 font-mono text-[10px] leading-5 text-slate-400">Ctrl K</kbd>}
            </label>
            {!words.length && (
                <div>
                    <h3 className="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{t('Structure')}</h3>
                    <div className="grid grid-cols-3 gap-2">
                        {LAYOUTS.map((l) => (
                            <button key={l.label} title={l.label} aria-label={l.label} draggable
                                onDragStart={(e) => startDrag(e, { kind: 'structure', widths: l.widths })} onDragEnd={endDrag}
                                onClick={() => addStructure(l.widths)}
                                className="flex h-10 cursor-grab gap-0.5 rounded-md border border-slate-200 p-1 hover:border-brand-400">
                                {l.widths.map((w, i) => <span key={i} className="rounded-sm bg-brand-200" style={{ flex: w }} />)}
                            </button>
                        ))}
                    </div>
                </div>
            )}
            {words.length > 0 && defs.length === 0 && <p className="text-center text-sm text-slate-500">{t('Aucun widget ne correspond à « :query ».', { query })}</p>}
            {groups.map(([group, list]) => (
                <div key={group}>
                    <h3 className="mb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{group}</h3>
                    <div className="grid grid-cols-3 gap-2">
                        {list.map((def) => (
                            <button key={def.type} draggable title={def.label}
                                onDragStart={(e) => startDrag(e, { kind: 'new', type: def.type })} onDragEnd={endDrag}
                                onClick={() => addWidget(def.type)}
                                className="flex cursor-grab flex-col items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-1 py-3 text-[11px] font-medium leading-tight text-slate-700 transition hover:border-brand-400 hover:text-brand-700 hover:shadow-sm active:cursor-grabbing">
                                <PathIcon d={def.icon} className="h-6 w-6" strokeWidth={1.6} />
                                <span className="line-clamp-2 text-center">{def.label}</span>
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
                <IconButton key={id} icon={d.icon} label={d.label} pressed={device === id} onClick={() => setState({ device: id })}
                    className={`rounded p-0.5 ${device === id ? 'bg-brand-100 text-brand-700' : 'text-slate-300 hover:text-slate-600'}`} iconClass="h-3 w-3" />
            ))}
        </span>
    );
}

function Settings({ id, boot }) {
    const urls = boot.urls;
    const content = useStore((s) => s.content);
    const device = useStore((s) => s.device);
    const isAdmin = useStore((s) => s.isAdmin);
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
    const tool = 'h-7 w-7 text-slate-500 hover:bg-slate-100 hover:text-slate-900';

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
                        <IconButton icon="copy" label={t('Copier')} shortcut="Ctrl+C" onClick={() => copyEl(id)} className={tool} iconClass="h-3.5 w-3.5" />
                        <IconButton icon="paintbrush" label={t('Coller le style')} shortcut="Ctrl+Maj+V" disabled={!hasClip} onClick={() => pasteStyle(id)} className={tool} iconClass="h-3.5 w-3.5" />
                        <IconButton icon="square-dashed" label={t('Envelopper dans un conteneur')} onClick={() => wrapEl(id)} className={tool} iconClass="h-3.5 w-3.5" />
                        <IconButton icon="bookmark-plus" label={t('Enregistrer dans Mes blocs')} onClick={() => saveAsBlock(id)} className={tool} iconClass="h-3.5 w-3.5" />
                        {isAdmin && node.type !== 'global-block' && <IconButton icon="refresh-cw" label={t('Transformer en section globale')} onClick={() => convertToGlobal(id)} className={tool} iconClass="h-3.5 w-3.5" />}
                        <IconButton icon="copy-plus" label={t('Dupliquer')} shortcut="Ctrl+D" onClick={() => duplicateEl(id)} className={tool} iconClass="h-3.5 w-3.5" />
                        <IconButton icon="trash-2" label={t('Supprimer')} shortcut="Suppr" onClick={() => removeEl(id)} className={`${tool} hover:!bg-red-50 hover:!text-red-600`} iconClass="h-3.5 w-3.5" />
                    </div>
                </div>
            </div>
            <div className="flex border-b border-slate-200" role="tablist" aria-label={t('Réglages')}>
                {TABS.map((tb) => (
                    <IconButton key={tb.id} icon={tb.icon} label={tb.label} onClick={() => setTab(tb.id)} role="tab" aria-selected={tab === tb.id}
                        className={`h-10 flex-1 rounded-none border-b-2 ${tab === tb.id ? 'border-brand-600 text-brand-700' : 'border-transparent text-slate-500 hover:bg-slate-50 hover:text-slate-800'}`} />
                ))}
            </div>
            <div className="space-y-4 p-4">
                {hoverable && (
                    <div className="flex gap-1 rounded-md bg-slate-100 p-0.5">
                        {[['normal', t('Normal'), 'square'], ['hover', t('Au survol'), 'mouse-pointer-2']].map(([m, label, icon]) => (
                            <button key={m} onClick={() => setMode(m)} aria-pressed={mode === m}
                                className={`flex flex-1 items-center justify-center gap-1.5 rounded px-2 py-1 text-xs font-medium ${mode === m ? 'bg-white text-brand-700 shadow-sm' : 'text-slate-600'}`}>
                                <UiIcon name={icon} className="h-3.5 w-3.5" />{label}
                            </button>
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
                    <button className="grid w-4 place-items-center opacity-70" onClick={(e) => { e.stopPropagation(); setOpen(!open); }} title={open ? t('Replier') : t('Déplier')} aria-label={open ? t('Replier') : t('Déplier')}>
                        <UiIcon name={open ? 'chevron-down' : 'chevron-right'} className="h-3 w-3" strokeWidth={2.2} />
                    </button>
                ) : <span className="w-4" />}
                {container ? <UiIcon name="square-dashed" className="h-3.5 w-3.5" /> : <PathIcon d={getWidget(node.type)?.icon ?? 'M4 4h16v16H4z'} className="h-3.5 w-3.5" />}
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
                            <p className="text-sm font-medium">{r.note === 'Publication' ? t('Publication') : r.note === 'Enregistrement' ? t('Enregistrement') : r.note}</p>
                            <p className="truncate text-xs text-slate-500">{new Date(r.created_at).toLocaleString(locale())} · {r.user?.name ?? '—'}</p>
                        </div>
                        <IconButton icon="archive-restore" label={t('Restaurer')} disabled={loading === r.id} onClick={() => restore(r)}
                            className="h-8 w-8 shrink-0 text-brand-600 hover:bg-brand-50" />
                    </li>
                ))}
            </ul>
        </div>
    );
}
