import { Component, createElement, useEffect, useLayoutEffect, useRef, useState } from 'react';
import DOMPurify from 'dompurify';
import { buildCss, containerDefaults } from './style';
import { Placeholder } from './widgets/core';
import { getState, setState, useStore } from './store';
import { contains, find, isContainer } from './tree';
import { getWidget } from './registry';
import { LAYOUTS } from './widgets';
import { addStructure, copyEl, dropAt, duplicateEl, labelOf, pasteAfter, pasteStyle, readClipboard, removeEl, select, wrapEl } from './actions';
import { t } from './i18n';
import { PathIcon, UiIcon } from './ui';
import { saveAsBlock } from './Blocks';

const EDITOR_CSS = `
body { min-height: 100vh; }
a { pointer-events: auto; }
.gpe-el { position: relative; }
.gp-text > .gpe-html > :first-child { margin-top: 0; }
.gp-text > .gpe-html > :last-child { margin-bottom: 0; }
.gpe-con { outline: 1px dashed rgba(148,163,184,.55); outline-offset: -1px; }
.gpe-con.is-hovered { outline: 1px solid #df9f7e; }
.gpe-con.is-selected { outline: 2px solid #aa4d26; outline-offset: -2px; }
.gpe-widget { cursor: grab; outline: 1px solid transparent; outline-offset: 2px; }
.gpe-widget.is-hovered { outline-color: #df9f7e; }
.gpe-widget.is-selected { outline: 2px solid #aa4d26; }
.gpe-el.is-dragging { opacity: .35; }
@media (min-width: 1025px) { .gpe-hide-desktop { opacity: .35; } }
@media (min-width: 768px) and (max-width: 1024px) { .gpe-hide-tablet { opacity: .35; } }
@media (max-width: 767px) { .gpe-hide-mobile { opacity: .35; } }
.gpe-widget a { cursor: grab; }
.gpe-empty { display: grid; place-items: center; flex: 1; min-height: 64px; border: 2px dashed #cbd5e1; border-radius: 8px;
  color: #94a3b8; font: 500 13px/1.3 system-ui, sans-serif; text-align: center; padding: 12px; }
.gpe-drop-line { position: fixed; z-index: 60; background: #aa4d26; border-radius: 2px; box-shadow: 0 0 0 3px rgba(170,77,38,.2); pointer-events: none; }
.gpe-toolbar { position: absolute; z-index: 20; display: none; gap: 1px; background: #aa4d26; border-radius: 6px; padding: 2px;
  font: 500 12px/1 system-ui, sans-serif; box-shadow: 0 4px 12px rgba(15,23,42,.25); white-space: nowrap; }
/* Les styles d'un widget visent parfois ses enfants directs (#gp-… > *), dont cette barre : elle garde sa propre typographie. */
.gpe-el > .gpe-toolbar { font: 500 12px/1 system-ui, sans-serif !important; letter-spacing: normal !important; text-transform: none !important;
  text-align: left !important; color: #fff !important; text-shadow: none !important; margin: 0 !important; width: auto !important; max-width: none !important; }
.gpe-toolbar button, .gpe-toolbar span { all: unset; cursor: pointer; color: #fff; padding: 5px 7px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; }
.gpe-toolbar button:hover, .gpe-toolbar span:hover { background: rgba(255,255,255,.18); }
.gpe-toolbar .gpe-handle { cursor: grab; }
.gpe-toolbar svg { width: 14px; height: 14px; }
.gpe-con > .gpe-toolbar { top: 0; left: 0; border-radius: 0 0 6px 0; }
.gpe-con.is-hovered > .gpe-toolbar, .gpe-con.is-selected > .gpe-toolbar { display: inline-flex; }
.gpe-widget > .gpe-toolbar { top: -26px; right: 0; background: #8e3f1f; }
.gpe-widget.is-selected > .gpe-toolbar { display: inline-flex; }
.gpe-layouts { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; width: 300px; margin: 0 auto; }
.gpe-layout-btn { all: unset; cursor: pointer; display: flex; gap: 3px; height: 34px; padding: 4px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; }
.gpe-layout-btn:hover { border-color: #aa4d26; }
.gpe-layout-btn span { background: #ecc6b0; border-radius: 3px; }
.gpe-add { margin: 32px auto; max-width: 720px; padding: 28px; border: 2px dashed #cbd5e1; border-radius: 14px; text-align: center;
  font: 14px/1.4 system-ui, sans-serif; color: #64748b; background: #f8fafc; cursor: pointer; }
.gpe-add h3 { font: 600 15px/1.3 system-ui, sans-serif; color: #0f172a; margin: 0 0 14px; }
.gpe-menu { position: fixed; z-index: 80; min-width: 200px; background: #fff; border-radius: 10px; padding: 5px; box-shadow: 0 12px 32px rgba(15,23,42,.28);
  font: 13px/1.2 system-ui, sans-serif; color: #0f172a; }
.gpe-menu button { all: unset; box-sizing: border-box; display: flex; align-items: center; gap: 10px; width: 100%; padding: 7px 10px; border-radius: 6px; cursor: pointer; }
.gpe-menu button > span { flex: 1; }
.gpe-menu svg { width: 15px; height: 15px; flex: none; color: #64748b; }
.gpe-menu button:hover svg { color: inherit; }
.gpe-menu .is-danger:hover { background: #fef2f2; color: #b91c1c; }
.gpe-add-btns { display: flex; justify-content: center; gap: 10px; }
.gpe-add-btn { all: unset; cursor: pointer; display: grid; place-items: center; width: 44px; height: 44px; border-radius: 999px; color: #fff; background: #aa4d26; box-shadow: 0 4px 10px rgba(170,77,38,.25); }
.gpe-add-btn:hover { background: #8e3f1f; }
.gpe-add-btn.is-alt { background: #1e1b4b; box-shadow: 0 4px 10px rgba(30,27,75,.25); }
.gpe-add-btn.is-alt:hover { background: #312e81; }
.gpe-add-btn svg { width: 20px; height: 20px; }
.gpe-add small { display: block; margin-top: 12px; color: #94a3b8; font-size: 12px; }
.gpe-menu button:hover { background: #fbf3ee; color: #72331a; }
.gpe-menu button[disabled] { opacity: .4; pointer-events: none; }
.gpe-menu kbd { color: #94a3b8; font: 11px system-ui, sans-serif; }
.gpe-menu hr { border: 0; border-top: 1px solid #e2e8f0; margin: 4px 0; }
`;

/** Bouton de la barre d'outils d'un élément (icône, infobulle et nom accessible). */
const Tool = ({ icon, d, label, onClick }) => (
    <button type="button" title={label} aria-label={label} onClick={onClick}>{d ? <PathIcon d={d} strokeWidth={2} /> : <UiIcon name={icon} strokeWidth={2} />}</button>
);

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

    return (
        <>
            {/* Polices choisies pour des éléments précis : chargées dans le document du canevas (l'iframe). */}
            {fonts.map((font) => <link key={font} rel="stylesheet" href={`https://fonts.bunny.net/css?family=${encodeURIComponent(font.toLowerCase())}:300,400,500,600,700,800,900&display=swap`} />)}
            <style>{EDITOR_CSS}</style>
            <style>{css}</style>
            <main
                id="contenu"
                className="gpe-root"
                // Comme <main id="contenu"> sur le site : un bloc (les marges verticales fusionnent).
                style={{ minHeight: '100vh' }}
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
                <>
                    <div className="gpe-add-btns">
                        <button type="button" className="gpe-add-btn" title={t('Ajouter un conteneur')} aria-label={t('Ajouter un conteneur')}
                            onClick={(e) => { e.stopPropagation(); setOpen(true); }}><UiIcon name="plus" strokeWidth={2.2} /></button>
                        <button type="button" className="gpe-add-btn is-alt" title={t('Mes blocs')} aria-label={t('Mes blocs')}
                            onClick={(e) => { e.stopPropagation(); setState({ panel: 'blocks', panelOpen: true }); }}><UiIcon name="library-big" strokeWidth={2} /></button>
                    </div>
                    <small>{t('ou glissez un élément ici')}</small>
                </>
            )}
        </div>
    );
}

function Element({ node, depth }) {
    return isContainer(node) ? <Container node={node} depth={depth} /> : <WidgetEl node={node} />;
}

/**
 * Conteneur vide décoratif (pastille de couleur, point, filet) : il a son propre fond, une bordure
 * ou une hauteur. La zone « Glissez un élément ici » le déformerait : on ne l'affiche pas.
 */
function decorative(s) {
    return !!(s.background_color || s.background_image || s.gradient_from || s.min_height
        || (s.border_style && s.border_style !== 'none'));
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
                ><UiIcon name="move" strokeWidth={2} /></span>
                <Tool icon="square-dashed" label={t('Modifier le conteneur')} onClick={act(() => select(node.id))} />
                <Tool icon="plus" label={t('Ajouter un conteneur à l’intérieur')} onClick={act(() => { select(node.id); addStructure([100]); })} />
                <Tool icon="copy-plus" label={t('Dupliquer')} onClick={act(() => duplicateEl(node.id))} />
                <Tool icon="bookmark-plus" label={t('Enregistrer dans Mes blocs')} onClick={act(() => saveAsBlock(node.id))} />
                <Tool icon="trash-2" label={t('Supprimer')} onClick={act(() => removeEl(node.id))} />
            </div>
            {node.settings.overlay_color && <div className="gp-overlay" />}
            <div
                className="gp-con-inner"
                onDragOver={(e) => onDragOverBox(e, node.id, e.currentTarget.parentElement)}
                onDrop={onDropBox}
            >
                {children.map((child) => <Element key={child.id} node={child} depth={depth + 1} />)}
                {children.length === 0 && !decorative(node.settings) && <div className="gpe-empty">{t('Glissez un élément ici')}</div>}
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
                <Tool d={getWidget(node.type)?.icon ?? 'M4 4h16v16H4z'} label={t('Modifier :label', { label })} onClick={act(() => select(node.id))} />
                <Tool icon="copy-plus" label={t('Dupliquer')} onClick={act(() => duplicateEl(node.id))} />
                <Tool icon="trash-2" label={t('Supprimer')} onClick={act(() => removeEl(node.id))} />
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
    // Le canevas vit dans une iframe : sa fenêtre (et non celle du builder) borne le menu.
    const ref = useRef(null);
    const [view, setView] = useState(null);
    useLayoutEffect(() => setView(ref.current?.ownerDocument.defaultView ?? null), []);
    useEffect(() => {
        if (!view) return undefined;
        const close = () => setState({ menu: null });
        const targets = view === window ? [window] : [window, view];
        for (const w of targets) {
            w.addEventListener('click', close);
            w.addEventListener('blur', close);
            w.addEventListener('scroll', close, true);
        }
        return () => targets.forEach((w) => { w.removeEventListener('click', close); w.removeEventListener('blur', close); w.removeEventListener('scroll', close, true); });
    }, [view]);
    const frame = view ?? window;
    const x = Math.max(0, Math.min(menu.x, frame.innerWidth - 220));
    const y = Math.max(0, Math.min(menu.y, frame.innerHeight - 260));
    const item = (icon, label, kbd, fn, disabled = false, danger = false) => (
        <button disabled={disabled} className={danger ? 'is-danger' : undefined} onClick={act(() => { setState({ menu: null }); fn(); })}>
            <UiIcon name={icon} /><span>{label}</span>{kbd && <kbd>{kbd}</kbd>}
        </button>
    );
    return (
        <div ref={ref} className="gpe-menu" style={{ left: x, top: y }} onClick={(e) => e.stopPropagation()} onContextMenu={(e) => e.preventDefault()}>
            {loc && item('pencil', t('Modifier :label', { label: labelOf(loc.node) }), null, () => select(loc.node.id))}
            {loc && <hr />}
            {loc && item('copy', t('Copier'), 'Ctrl+C', () => copyEl(loc.node.id))}
            {item('clipboard-paste', t('Coller après'), 'Ctrl+V', () => pasteAfter(loc?.node.id ?? null), !hasClip)}
            {loc && item('paintbrush', t('Coller le style'), 'Ctrl+Maj+V', () => pasteStyle(loc.node.id), !hasClip)}
            {loc && <hr />}
            {loc && item('copy-plus', t('Dupliquer'), 'Ctrl+D', () => duplicateEl(loc.node.id))}
            {loc && item('square-dashed', t('Envelopper dans un conteneur'), null, () => wrapEl(loc.node.id))}
            {loc && item('bookmark-plus', t('Enregistrer dans Mes blocs'), null, () => saveAsBlock(loc.node.id))}
            {loc && <hr />}
            {loc && item('trash-2', t('Supprimer'), 'Suppr', () => removeEl(loc.node.id), false, true)}
            {!loc && item('plus', t('Ajouter un conteneur'), null, () => { select(null); addStructure([100]); })}
            {!loc && item('library-big', t('Mes blocs'), null, () => setState({ panel: 'blocks', panelOpen: true }))}
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

    componentDidUpdate(prev) {
        // Un réglage corrigé : on retente l'aperçu.
        if (this.state.error && prev.settings !== this.props.settings) this.setState({ error: null });
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
        <SafePreview type={widget.type} settings={widget.settings}>
            {def.preview(widget.settings, { h: createElement, Placeholder, sanitize: (html) => DOMPurify.sanitize(html) })}
        </SafePreview>
    );
}

export { containerDefaults };
