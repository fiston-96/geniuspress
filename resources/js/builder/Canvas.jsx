import { Component, createElement, useEffect, useState } from 'react';
import DOMPurify from 'dompurify';
import { buildCss, containerDefaults } from './style';
import { Placeholder } from './widgets/core';
import { getState, setState, useStore } from './store';
import { contains, find, isContainer } from './tree';
import { getWidget } from './registry';
import { LAYOUTS } from './widgets';
import { addStructure, copyEl, dropAt, duplicateEl, labelOf, pasteAfter, pasteStyle, readClipboard, removeEl, select, wrapEl } from './actions';
import { t } from './i18n';

const EDITOR_CSS = `
body { min-height: 100vh; }
a { pointer-events: auto; }
.gpe-el { position: relative; }
.gpe-con { outline: 1px dashed rgba(148,163,184,.55); outline-offset: -1px; }
.gpe-con.is-hovered { outline: 1px solid #818cf8; }
.gpe-con.is-selected { outline: 2px solid #4f46e5; outline-offset: -2px; }
.gpe-widget { cursor: grab; outline: 1px solid transparent; outline-offset: 2px; }
.gpe-widget.is-hovered { outline-color: #a5b4fc; }
.gpe-widget.is-selected { outline: 2px solid #4f46e5; }
.gpe-el.is-dragging { opacity: .35; }
@media (min-width: 1025px) { .gpe-hide-desktop { opacity: .35; } }
@media (min-width: 768px) and (max-width: 1024px) { .gpe-hide-tablet { opacity: .35; } }
@media (max-width: 767px) { .gpe-hide-mobile { opacity: .35; } }
.gpe-widget a { cursor: grab; }
.gpe-empty { display: grid; place-items: center; flex: 1; min-height: 64px; border: 2px dashed #cbd5e1; border-radius: 8px;
  color: #94a3b8; font: 500 13px/1.3 system-ui, sans-serif; text-align: center; padding: 12px; }
.gpe-drop-line { position: fixed; z-index: 60; background: #4f46e5; border-radius: 2px; box-shadow: 0 0 0 3px rgba(79,70,229,.2); pointer-events: none; }
.gpe-toolbar { position: absolute; z-index: 20; display: none; gap: 1px; background: #4f46e5; border-radius: 6px; padding: 2px;
  font: 500 12px/1 system-ui, sans-serif; box-shadow: 0 4px 12px rgba(15,23,42,.25); white-space: nowrap; }
.gpe-toolbar button, .gpe-toolbar span { all: unset; cursor: pointer; color: #fff; padding: 5px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; }
.gpe-toolbar button:hover, .gpe-toolbar span:hover { background: rgba(255,255,255,.18); }
.gpe-toolbar .gpe-handle { cursor: grab; }
.gpe-toolbar svg { width: 14px; height: 14px; }
.gpe-con > .gpe-toolbar { top: 0; left: 0; border-radius: 0 0 6px 0; }
.gpe-con.is-hovered > .gpe-toolbar, .gpe-con.is-selected > .gpe-toolbar { display: inline-flex; }
.gpe-widget > .gpe-toolbar { top: -26px; right: 0; background: #4338ca; }
.gpe-widget.is-selected > .gpe-toolbar { display: inline-flex; }
.gpe-layouts { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; width: 300px; margin: 0 auto; }
.gpe-layout-btn { all: unset; cursor: pointer; display: flex; gap: 3px; height: 34px; padding: 4px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; }
.gpe-layout-btn:hover { border-color: #4f46e5; }
.gpe-layout-btn span { background: #c7d2fe; border-radius: 3px; }
.gpe-add { margin: 32px auto; max-width: 720px; padding: 28px; border: 2px dashed #cbd5e1; border-radius: 14px; text-align: center;
  font: 14px/1.4 system-ui, sans-serif; color: #64748b; background: #f8fafc; cursor: pointer; }
.gpe-add h3 { font: 600 15px/1.3 system-ui, sans-serif; color: #0f172a; margin: 0 0 14px; }
.gpe-menu { position: fixed; z-index: 80; min-width: 200px; background: #fff; border-radius: 10px; padding: 5px; box-shadow: 0 12px 32px rgba(15,23,42,.28);
  font: 13px/1.2 system-ui, sans-serif; color: #0f172a; }
.gpe-menu button { all: unset; box-sizing: border-box; display: flex; justify-content: space-between; gap: 16px; width: 100%; padding: 7px 10px; border-radius: 6px; cursor: pointer; }
.gpe-menu button:hover { background: #eef2ff; color: #3730a3; }
.gpe-menu button[disabled] { opacity: .4; pointer-events: none; }
.gpe-menu kbd { color: #94a3b8; font: 11px system-ui, sans-serif; }
.gpe-menu hr { border: 0; border-top: 1px solid #e2e8f0; margin: 4px 0; }
`;

const Icon = ({ d }) => (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d={d} /></svg>
);
const I = {
    copy: 'M9 9h11v11H9zM5 15H4V4h11v1',
    trash: 'M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14',
    plus: 'M12 5v14M5 12h14',
    move: 'M5 9l-3 3 3 3M9 5l3-3 3 3M15 19l-3 3-3-3M19 9l3 3-3 3M2 12h20M12 2v20',
    edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z',
};

const act = (fn) => (e) => { e.stopPropagation(); e.preventDefault(); fn(); };

/**
 * Position d'insertion dans une boîte de conteneur selon le pointeur, et
 * repère visuel correspondant (ligne horizontale en colonne, verticale en ligne).
 */
function dropPoint(e, inner) {
    const items = [...inner.children].filter((el) => el.classList.contains('gpe-el') && !el.classList.contains('is-dragging'));
    const all = [...inner.children].filter((el) => el.classList.contains('gpe-el'));
    const dir = getComputedStyle(inner).flexDirection;
    const row = dir.startsWith('row');
    const reverse = dir.endsWith('reverse');
    const box = inner.getBoundingClientRect();
    if (!items.length) return { index: all.length, line: { x: box.left + 8, y: box.top + box.height / 2 - 2, w: Math.max(0, box.width - 16), h: 4 } };
    for (const el of items) {
        const r = el.getBoundingClientRect();
        const before = row ? (reverse ? e.clientX > r.left + r.width / 2 : e.clientX < r.left + r.width / 2) : (reverse ? e.clientY > r.top + r.height / 2 : e.clientY < r.top + r.height / 2);
        if (before) {
            const line = row ? { x: (reverse ? r.right : r.left) - 2, y: r.top, w: 4, h: r.height } : { x: r.left, y: (reverse ? r.bottom : r.top) - 2, w: r.width, h: 4 };
            return { index: all.indexOf(el), line };
        }
    }
    const last = items[items.length - 1].getBoundingClientRect();
    const line = row ? { x: (reverse ? last.left : last.right) - 2, y: last.top, w: 4, h: last.height } : { x: last.left, y: (reverse ? last.top : last.bottom) - 2, w: last.width, h: 4 };
    return { index: all.indexOf(items[items.length - 1]) + 1, line };
}

/** Gestion du survol pendant un glisser-déposer, pour la boîte d'un conteneur (ou la page). */
function onDragOverBox(e, parentId, container) {
    const { drag, content } = getState();
    if (!drag) return;
    // On ne dépose pas un conteneur dans lui-même : on laisse le parent gérer.
    if (drag.kind === 'move' && parentId) {
        const dragged = find(content, drag.id)?.node;
        if (dragged && contains(dragged, parentId)) return;
    }
    // Près du bord d'un conteneur imbriqué, on dépose à côté de lui plutôt que dedans.
    if (container) {
        const r = container.getBoundingClientRect();
        const edge = 10;
        if (e.clientX - r.left < edge || r.right - e.clientX < edge || e.clientY - r.top < edge || r.bottom - e.clientY < edge) return;
    }
    e.preventDefault();
    e.stopPropagation();
    const { index, line } = dropPoint(e, e.currentTarget);
    const cur = getState().drop;
    if (!cur || cur.parentId !== parentId || cur.index !== index || cur.line.x !== line.x || cur.line.y !== line.y) setState({ drop: { parentId, index, line } });
}

function onDropBox(e) {
    e.preventDefault();
    e.stopPropagation();
    const drop = getState().drop;
    if (drop) dropAt(drop.parentId, drop.index);
    else setState({ drag: null });
}

export default function Canvas() {
    const content = useStore((s) => s.content);
    const drop = useStore((s) => s.drop);
    const menu = useStore((s) => s.menu);
    const { css, fonts } = buildCss(content);

    // Polices choisies pour des éléments précis : chargées à la demande.
    useEffect(() => {
        for (const font of fonts) {
            const id = `gpe-font-${font.replace(/\W+/g, '-')}`;
            if (document.getElementById(id)) continue;
            const link = document.createElement('link');
            link.id = id;
            link.rel = 'stylesheet';
            link.href = `https://fonts.bunny.net/css?family=${encodeURIComponent(font.toLowerCase())}:400,500,600,700,800&display=swap`;
            document.head.appendChild(link);
        }
    }, [fonts.join('|')]); // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <>
            <style>{EDITOR_CSS}</style>
            <style>{css}</style>
            <main
                id="contenu"
                className="gpe-root"
                style={{ display: 'flex', flexDirection: 'column', minHeight: '100vh' }}
                onClick={() => select(null)}
                onDragOver={(e) => onDragOverBox(e, null, null)}
                onDragLeave={(e) => { if (!e.relatedTarget) setState({ drop: null }); }}
                onDrop={onDropBox}
                onContextMenu={(e) => { e.preventDefault(); setState({ menu: { x: e.clientX, y: e.clientY, id: null } }); }}
            >
                {content.elements.map((node) => <Element key={node.id} node={node} depth={0} />)}
                <AddArea empty={content.elements.length === 0} />
            </main>
            {drop && <div className="gpe-drop-line" style={{ left: drop.line.x, top: drop.line.y, width: drop.line.w, height: drop.line.h }} />}
            {menu && <ContextMenu menu={menu} />}
        </>
    );
}

function AddArea({ empty }) {
    const [open, setOpen] = useState(false);
    return (
        <div className="gpe-add" onClick={(e) => { e.stopPropagation(); setOpen(true); }}>
            {open || empty ? (
                <>
                    <h3>{t('Choisissez une structure pour le nouveau conteneur')}</h3>
                    <div className="gpe-layouts" onClick={(e) => e.stopPropagation()}>
                        {LAYOUTS.map((l) => (
                            <button key={l.label} className="gpe-layout-btn" title={l.label} onClick={() => { select(null); addStructure(l.widths); setOpen(false); }}>
                                {l.widths.map((w, i) => <span key={i} style={{ flex: w }} />)}
                            </button>
                        ))}
                    </div>
                    <p style={{ marginTop: 14 }}>{t('… ou glissez un élément ici depuis le panneau de gauche.')}</p>
                </>
            ) : (
                <>{t('＋ Ajouter un conteneur')} <br /><small>{t('ou glissez un élément ici')}</small></>
            )}
        </div>
    );
}

function Element({ node, depth }) {
    return isContainer(node) ? <Container node={node} depth={depth} /> : <WidgetEl node={node} />;
}

function stateClasses(node, selected, hovered, dragging) {
    const cls = [];
    for (const d of ['desktop', 'tablet', 'mobile']) if (node.settings[`hide_${d}`]) cls.push(`gpe-hide-${d}`);
    // Animations : comme sur le site (Renderer::classes) ; « is-in » joue l'apparition dans le canevas.
    if (node.settings.entrance) cls.push('gp-anim', `gp-anim--${node.settings.entrance}`, 'is-in');
    if (node.settings.hover_effect) cls.push('gp-hv', `gp-hv--${node.settings.hover_effect}`);
    // Défilement (Renderer::classes) : dans le canevas, les effets restent à leur position neutre.
    if (node.settings.sticky) cls.push('gp-sticky', `gp-sticky--${node.settings.sticky}`);
    if (node.settings.scroll_effect) cls.push('gp-sc', `gp-sc--${node.settings.scroll_effect}`);
    if (node.settings.background_parallax && node.settings.background_image && node.settings.background_type !== 'gradient') cls.push('gp-parallax');
    if (node.settings.css_classes) cls.push(node.settings.css_classes);
    if (selected) cls.push('is-selected');
    if (hovered) cls.push('is-hovered');
    if (dragging) cls.push('is-dragging');
    return cls.join(' ');
}

function Container({ node, depth }) {
    const selected = useStore((s) => s.selected === node.id);
    const hovered = useStore((s) => s.hovered === node.id);
    const dragging = useStore((s) => s.drag?.kind === 'move' && s.drag.id === node.id);
    const Tag = ['section', 'header', 'footer', 'article', 'aside', 'nav'].includes(node.settings.tag) ? node.settings.tag : 'div';
    const children = node.children ?? [];

    return (
        <Tag
            id={`gp-${node.id}`}
            className={`gp-el gp-con gpe-el gpe-con ${stateClasses(node, selected, hovered, dragging)}`}
            onClick={(e) => { e.stopPropagation(); select(node.id); }}
            onMouseOver={(e) => { e.stopPropagation(); if (getState().hovered !== node.id) setState({ hovered: node.id }); }}
            onContextMenu={(e) => { e.preventDefault(); e.stopPropagation(); select(node.id); setState({ menu: { x: e.clientX, y: e.clientY, id: node.id } }); }}
        >
            <div className="gpe-toolbar">
                <span className="gpe-handle" draggable title={t('Déplacer')}
                    onDragStart={(e) => {
                        e.stopPropagation();
                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/plain', node.id);
                        e.dataTransfer.setDragImage(e.currentTarget.closest('.gpe-con'), 20, 20);
                        setState({ drag: { kind: 'move', id: node.id } });
                    }}
                    onDragEnd={() => setState({ drag: null, drop: null })}
                ><Icon d={I.move} /></span>
                <button title={t('Modifier le conteneur')} onClick={act(() => select(node.id))}><Icon d={I.edit} /> {t('Conteneur')}</button>
                <button title={t('Ajouter un conteneur à l’intérieur')} onClick={act(() => { select(node.id); addStructure([100]); })}><Icon d={I.plus} /></button>
                <button title={t('Dupliquer')} onClick={act(() => duplicateEl(node.id))}><Icon d={I.copy} /></button>
                <button title={t('Supprimer')} onClick={act(() => removeEl(node.id))}><Icon d={I.trash} /></button>
            </div>
            {node.settings.overlay_color && <div className="gp-overlay" />}
            <div
                className="gp-con-inner"
                onDragOver={(e) => onDragOverBox(e, node.id, e.currentTarget.parentElement)}
                onDrop={onDropBox}
            >
                {children.map((child) => <Element key={child.id} node={child} depth={depth + 1} />)}
                {children.length === 0 && <div className="gpe-empty">{t('Glissez un élément ici')}</div>}
            </div>
        </Tag>
    );
}

function WidgetEl({ node }) {
    const selected = useStore((s) => s.selected === node.id);
    const hovered = useStore((s) => s.hovered === node.id);
    const dragging = useStore((s) => s.drag?.kind === 'move' && s.drag.id === node.id);
    const label = labelOf(node);

    return (
        <div
            id={`gp-${node.id}`}
            className={`gp-el gp-widget gp-${node.type} gpe-el gpe-widget ${stateClasses(node, selected, hovered, dragging)}`}
            draggable
            onDragStart={(e) => {
                e.stopPropagation();
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', node.id);
                setState({ drag: { kind: 'move', id: node.id } });
            }}
            onDragEnd={() => setState({ drag: null, drop: null })}
            onClick={(e) => { e.preventDefault(); e.stopPropagation(); select(node.id); }}
            onMouseOver={(e) => { e.stopPropagation(); if (getState().hovered !== node.id) setState({ hovered: node.id }); }}
            onContextMenu={(e) => { e.preventDefault(); e.stopPropagation(); select(node.id); setState({ menu: { x: e.clientX, y: e.clientY, id: node.id } }); }}
        >
            <div className="gpe-toolbar">
                <button title={label}><Icon d={I.edit} /> {label}</button>
                <button title={t('Dupliquer')} onClick={act(() => duplicateEl(node.id))}><Icon d={I.copy} /></button>
                <button title={t('Supprimer')} onClick={act(() => removeEl(node.id))}><Icon d={I.trash} /></button>
            </div>
            <WidgetBody widget={node} />
        </div>
    );
}

/** Menu du clic droit : copier, coller, coller le style, dupliquer, envelopper, supprimer. */
function ContextMenu({ menu }) {
    const content = useStore((s) => s.content);
    const loc = menu.id ? find(content, menu.id) : null;
    const hasClip = !!readClipboard();
    useEffect(() => {
        const close = () => setState({ menu: null });
        window.addEventListener('click', close);
        window.addEventListener('blur', close);
        window.addEventListener('scroll', close, true);
        return () => { window.removeEventListener('click', close); window.removeEventListener('blur', close); window.removeEventListener('scroll', close, true); };
    }, []);
    const x = Math.min(menu.x, window.innerWidth - 220);
    const y = Math.min(menu.y, window.innerHeight - 260);
    const item = (label, kbd, fn, disabled = false) => (
        <button disabled={disabled} onClick={act(() => { setState({ menu: null }); fn(); })}>{label}{kbd && <kbd>{kbd}</kbd>}</button>
    );
    return (
        <div className="gpe-menu" style={{ left: x, top: y }} onClick={(e) => e.stopPropagation()} onContextMenu={(e) => e.preventDefault()}>
            {loc && item(t('Modifier :label', { label: labelOf(loc.node) }), null, () => select(loc.node.id))}
            {loc && <hr />}
            {loc && item(t('Copier'), 'Ctrl+C', () => copyEl(loc.node.id))}
            {item(t('Coller après'), 'Ctrl+V', () => pasteAfter(loc?.node.id ?? null), !hasClip)}
            {loc && item(t('Coller le style'), 'Ctrl+Maj+V', () => pasteStyle(loc.node.id), !hasClip)}
            {loc && <hr />}
            {loc && item(t('Dupliquer'), 'Ctrl+D', () => duplicateEl(loc.node.id))}
            {loc && item(t('Envelopper dans un conteneur'), null, () => wrapEl(loc.node.id))}
            {loc && item(t('Supprimer'), 'Suppr', () => removeEl(loc.node.id))}
            {!loc && item(t('Ajouter un conteneur'), null, () => { select(null); addStructure([100]); })}
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
        return this.state.error ? <Placeholder>{t('Aperçu indisponible (erreur du widget « :type »)', { type: this.props.type })}</Placeholder> : this.props.children;
    }
}

function WidgetBody({ widget }) {
    const def = getWidget(widget.type);
    if (!def) {
        return <Placeholder>{t('Widget « :type » indisponible : le plugin qui le fournit est désactivé. Son contenu est conservé.', { type: widget.type })}</Placeholder>;
    }
    if (!def.preview) {
        return <Placeholder>{t(':label — aperçu visible sur le site publié', { label: def.label })}</Placeholder>;
    }
    return (
        <SafePreview type={widget.type}>
            {def.preview(widget.settings, { h: createElement, Placeholder, sanitize: (html) => DOMPurify.sanitize(html) })}
        </SafePreview>
    );
}

export { containerDefaults };
