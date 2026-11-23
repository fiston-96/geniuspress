import { Component, createElement, useState } from 'react';
import DOMPurify from 'dompurify';
import { buildCss } from './css';
import { Placeholder } from './widgets/core';
import { commit, getState, setState, useStore } from './store';
import { addSection, duplicateNode, insertWidget, makeWidget, moveSection, moveWidget, removeNode, setLayout } from './tree';
import { getWidget } from './registry';
import { LAYOUTS } from './widgets';

const EDITOR_CSS = `
body { min-height: 100vh; }
a { pointer-events: auto; }
.gpe-section { position: relative; outline: 1px dashed transparent; outline-offset: -1px; }
.gpe-section:hover { outline-color: #818cf8; }
.gpe-section.is-selected { outline: 2px solid #4f46e5; outline-offset: -2px; }
.gpe-column { position: relative; min-height: 60px; outline: 1px dashed transparent; outline-offset: -1px; }
.gpe-section:hover .gpe-column { outline-color: #cbd5e1; }
.gpe-column.is-selected { outline: 2px solid #0ea5e9 !important; outline-offset: -2px; }
.gpe-widget { position: relative; cursor: grab; outline: 1px solid transparent; outline-offset: 2px; }
.gpe-widget:hover { outline-color: #a5b4fc; }
.gpe-widget.is-selected { outline: 2px solid #4f46e5; }
.gpe-widget.is-dragging { opacity: .35; }
@media (min-width: 768px) { .gpe-hide-desktop { opacity: .35; } }
@media (max-width: 767px) { .gpe-hide-mobile { opacity: .35; } }
.gpe-widget a { cursor: grab; }
.gpe-empty { display: grid; place-items: center; flex: 1; min-height: 80px; margin: 8px; border: 2px dashed #cbd5e1; border-radius: 8px;
  color: #94a3b8; font: 500 13px/1.3 system-ui, sans-serif; text-align: center; padding: 12px; }
.gpe-drop { height: 4px; margin: -2px 0; background: #4f46e5; border-radius: 2px; box-shadow: 0 0 0 3px rgba(79,70,229,.2); position: relative; z-index: 5; }
.gpe-toolbar { position: absolute; z-index: 20; display: none; gap: 1px; background: #4f46e5; border-radius: 6px; padding: 2px;
  font: 500 12px/1 system-ui, sans-serif; box-shadow: 0 4px 12px rgba(15,23,42,.25); }
.gpe-toolbar button { all: unset; cursor: pointer; color: #fff; padding: 5px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; }
.gpe-toolbar button:hover { background: rgba(255,255,255,.18); }
.gpe-toolbar svg { width: 14px; height: 14px; }
.gpe-section > .gpe-toolbar { top: 0; left: 50%; transform: translate(-50%, 0); border-radius: 0 0 8px 8px; }
.gpe-section:hover > .gpe-toolbar, .gpe-section.is-selected > .gpe-toolbar { display: inline-flex; }
.gpe-widget > .gpe-toolbar { top: -26px; right: 0; background: #4338ca; }
.gpe-widget.is-selected > .gpe-toolbar { display: inline-flex; }
.gpe-layouts { position: absolute; top: 30px; left: 50%; transform: translateX(-50%); z-index: 30; background: #fff; border-radius: 10px;
  box-shadow: 0 10px 30px rgba(15,23,42,.25); padding: 10px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; width: 300px; }
.gpe-layout-btn { all: unset; cursor: pointer; display: flex; gap: 3px; height: 34px; padding: 4px; border: 1px solid #e2e8f0; border-radius: 6px; }
.gpe-layout-btn:hover { border-color: #4f46e5; }
.gpe-layout-btn span { background: #c7d2fe; border-radius: 3px; }
.gpe-add { margin: 32px auto; max-width: 720px; padding: 28px; border: 2px dashed #cbd5e1; border-radius: 14px; text-align: center;
  font: 14px/1.4 system-ui, sans-serif; color: #64748b; background: #f8fafc; }
.gpe-add.is-over { border-color: #4f46e5; background: #eef2ff; }
.gpe-add h3 { font: 600 15px/1.3 system-ui, sans-serif; color: #0f172a; margin: 0 0 14px; }
.gpe-add .gpe-layouts { position: static; transform: none; box-shadow: none; padding: 0; margin: 0 auto; background: transparent; }
`;

const Icon = ({ d }) => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d={d} /></svg>
);
const I = {
    up: 'M12 19V5M5 12l7-7 7 7',
    down: 'M12 5v14M19 12l-7 7-7-7',
    copy: 'M9 9h11v11H9zM5 15H4V4h11v1',
    trash: 'M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14',
    cols: 'M4 4h16v16H4zM12 4v16',
    edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z',
};

function select(id) {
    setState({ selected: id, panel: id ? 'settings' : 'elements' });
}

function LayoutPicker({ onPick }) {
    return (
        <div className="gpe-layouts" onClick={(e) => e.stopPropagation()}>
            {LAYOUTS.map((l) => (
                <button key={l.label} className="gpe-layout-btn" title={l.label} onClick={() => onPick(l.widths)}>
                    {l.widths.map((w, i) => <span key={i} style={{ flex: w }} />)}
                </button>
            ))}
        </div>
    );
}

export default function Canvas() {
    const content = useStore((s) => s.content);
    const [over, setOver] = useState(false);
    const [picker, setPicker] = useState(false);

    const css = buildCss(content);

    const addFromPicker = (widths) => {
        const [next, section] = addSection(getState().content, undefined, widths);
        commit(next, { selected: section.id, panel: 'settings' });
        setPicker(false);
    };

    // Déposer un widget sous la dernière section crée une nouvelle section.
    const onDrop = (e) => {
        e.preventDefault();
        setOver(false);
        const drag = getState().drag;
        if (!drag) return;
        let [next, section] = addSection(getState().content, undefined, [100]);
        const colId = section.columns[0].id;
        if (drag.kind === 'new') {
            const w = makeWidget(drag.type);
            next = insertWidget(next, colId, 0, w);
            commit(next, { selected: w.id, panel: 'settings', drag: null });
        } else if (drag.kind === 'move') {
            commit(moveWidget(next, drag.id, colId, 0), { drag: null });
        }
    };

    return (
        <>
            <style>{EDITOR_CSS}</style>
            <style>{css}</style>
            <main onClick={() => select(null)}>
                {content.sections.map((s, i) => (
                    <Section key={s.id} section={s} index={i} total={content.sections.length} />
                ))}
                <div
                    className={`gpe-add${over ? ' is-over' : ''}`}
                    onClick={(e) => { e.stopPropagation(); setPicker(true); }}
                    onDragOver={(e) => { e.preventDefault(); setOver(true); }}
                    onDragLeave={() => setOver(false)}
                    onDrop={onDrop}
                >
                    {picker || content.sections.length === 0 ? (
                        <>
                            <h3>Choisissez une structure pour la nouvelle section</h3>
                            <LayoutPicker onPick={addFromPicker} />
                            <p style={{ marginTop: 14 }}>… ou glissez un élément ici depuis le panneau de gauche.</p>
                        </>
                    ) : (
                        <>＋ Ajouter une section <br /><small>ou glissez un élément ici</small></>
                    )}
                </div>
            </main>
        </>
    );
}

function Section({ section, index, total }) {
    const selected = useStore((s) => s.selected === section.id);
    const [layouts, setLayouts] = useState(false);
    const o = section.settings;
    const act = (fn) => (e) => { e.stopPropagation(); fn(); };

    return (
        <section
            id={`gp-${section.id}`}
            className={`gp-section gpe-section${selected ? ' is-selected' : ''}`}
            onClick={(e) => { e.stopPropagation(); select(section.id); setLayouts(false); }}
        >
            <div className="gpe-toolbar">
                <button title="Monter" disabled={index === 0} onClick={act(() => commit(moveSection(getState().content, section.id, -1)))}><Icon d={I.up} /></button>
                <button title="Descendre" disabled={index === total - 1} onClick={act(() => commit(moveSection(getState().content, section.id, 1)))}><Icon d={I.down} /></button>
                <button title="Structure des colonnes" onClick={act(() => setLayouts((v) => !v))}><Icon d={I.cols} /></button>
                <button title="Modifier la section" onClick={act(() => select(section.id))}><Icon d={I.edit} /> Section</button>
                <button title="Dupliquer" onClick={act(() => { const [n, id] = duplicateNode(getState().content, section.id); commit(n, { selected: id }); })}><Icon d={I.copy} /></button>
                <button title="Supprimer" onClick={act(() => commit(removeNode(getState().content, section.id), { selected: null, panel: 'elements' }))}><Icon d={I.trash} /></button>
            </div>
            {layouts && <LayoutPicker onPick={(w) => { commit(setLayout(getState().content, section.id, w)); setLayouts(false); }} />}
            {o.overlay_color && <div className="gp-overlay" />}
            <div className="gp-row">
                {section.columns.map((c) => <Column key={c.id} column={c} />)}
            </div>
        </section>
    );
}

function Column({ column }) {
    const selected = useStore((s) => s.selected === column.id);
    const [dropIndex, setDropIndex] = useState(null);

    const computeIndex = (e) => {
        const items = [...e.currentTarget.querySelectorAll(':scope > .gpe-widget')];
        for (const [i, el] of items.entries()) {
            const r = el.getBoundingClientRect();
            if (e.clientY < r.top + r.height / 2) return i;
        }
        return items.length;
    };

    const onDragOver = (e) => {
        if (!getState().drag) return;
        e.preventDefault();
        e.stopPropagation();
        const i = computeIndex(e);
        if (i !== dropIndex) setDropIndex(i);
    };

    const onDrop = (e) => {
        e.preventDefault();
        e.stopPropagation();
        const drag = getState().drag;
        const index = computeIndex(e);
        setDropIndex(null);
        if (!drag) return;
        if (drag.kind === 'new') {
            const w = makeWidget(drag.type);
            commit(insertWidget(getState().content, column.id, index, w), { selected: w.id, panel: 'settings', drag: null });
        } else {
            commit(moveWidget(getState().content, drag.id, column.id, index), { drag: null });
        }
    };

    const items = [];
    column.widgets.forEach((w, i) => {
        if (dropIndex === i) items.push(<div key="drop" className="gpe-drop" />);
        items.push(<Widget key={w.id} widget={w} />);
    });
    if (dropIndex === column.widgets.length && column.widgets.length) items.push(<div key="drop" className="gpe-drop" />);

    return (
        <div
            id={`gp-${column.id}`}
            className={`gp-column gpe-column${selected ? ' is-selected' : ''}`}
            onClick={(e) => { e.stopPropagation(); select(column.id); }}
            onDragOver={onDragOver}
            onDragLeave={(e) => { if (!e.currentTarget.contains(e.relatedTarget)) setDropIndex(null); }}
            onDrop={onDrop}
        >
            {items}
            {column.widgets.length === 0 && (
                <div className="gpe-empty" style={dropIndex !== null ? { borderColor: '#4f46e5', color: '#4f46e5' } : null}>
                    Glissez un élément ici
                </div>
            )}
        </div>
    );
}

function Widget({ widget }) {
    const selected = useStore((s) => s.selected === widget.id);
    const dragging = useStore((s) => s.drag?.kind === 'move' && s.drag.id === widget.id);
    const cls = ['gp-widget', `gp-${widget.type}`, 'gpe-widget'];
    if (widget.settings.hide_desktop) cls.push('gpe-hide-desktop');
    if (widget.settings.hide_mobile) cls.push('gpe-hide-mobile');
    if (selected) cls.push('is-selected');
    if (dragging) cls.push('is-dragging');
    const act = (fn) => (e) => { e.stopPropagation(); fn(); };

    return (
        <div
            id={`gp-${widget.id}`}
            className={cls.join(' ')}
            draggable
            onDragStart={(e) => {
                e.stopPropagation();
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', widget.id);
                setState({ drag: { kind: 'move', id: widget.id } });
            }}
            onDragEnd={() => setState({ drag: null })}
            onClick={(e) => { e.preventDefault(); e.stopPropagation(); select(widget.id); }}
        >
            <div className="gpe-toolbar">
                <button title={getWidget(widget.type)?.label ?? widget.type}><Icon d={I.edit} /> {getWidget(widget.type)?.label ?? widget.type}</button>
                <button title="Dupliquer" onClick={act(() => { const [n, id] = duplicateNode(getState().content, widget.id); commit(n, { selected: id }); })}><Icon d={I.copy} /></button>
                <button title="Supprimer" onClick={act(() => commit(removeNode(getState().content, widget.id), { selected: null, panel: 'elements' }))}><Icon d={I.trash} /></button>
            </div>
            <WidgetBody widget={widget} />
        </div>
    );
}

/** Isole l'aperçu d'un widget : un plugin qui plante n'empêche pas d'éditer la page. */
class SafePreview extends Component {
    state = { error: null };

    static getDerivedStateFromError(error) {
        return { error };
    }

    componentDidCatch(error) {
        console.error(`[GeniusPress] Aperçu du widget « ${this.props.type} » :`, error);
    }

    render() {
        return this.state.error ? <Placeholder>Aperçu indisponible (erreur du widget « {this.props.type} »)</Placeholder> : this.props.children;
    }
}

function WidgetBody({ widget }) {
    const def = getWidget(widget.type);
    if (!def) {
        return <Placeholder>Widget « {widget.type} » indisponible : le plugin qui le fournit est désactivé. Son contenu est conservé.</Placeholder>;
    }
    if (!def.preview) {
        return <Placeholder>{def.label} — aperçu visible sur le site publié</Placeholder>;
    }
    return (
        <SafePreview type={widget.type}>
            {def.preview(widget.settings, { h: createElement, Placeholder, sanitize: (html) => DOMPurify.sanitize(html) })}
        </SafePreview>
    );
}
