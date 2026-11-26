import { useCallback, useEffect, useRef, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { api } from './api';
import Canvas from './Canvas';
import Panel from './Panel';
import { getState, redo, setState, toast, undo, useStore } from './store';
import { copyEl, duplicateEl, pasteAfter, pasteStyle, removeEl, togglePanel } from './actions';
import { t } from './i18n';

// L'ordinateur commence à 1025 px (tablette : 768 à 1024 px, mobile : 767 px et moins).
const DESKTOP_MIN = 1025;

const DEVICES = [
    { id: 'desktop', label: t('Ordinateur'), width: '100%', icon: 'M3 4h18v12H3zM8 20h8M12 16v4' },
    { id: 'tablet', label: t('Tablette'), width: '768px', icon: 'M6 2h12v20H6zM11 18h2' },
    { id: 'mobile', label: t('Mobile'), width: '390px', icon: 'M8 2h8v20H8zM11 18h2' },
];

const Svg = ({ d, className = 'h-4 w-4' }) => (
    <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"><path d={d} /></svg>
);

export default function App({ boot }) {
    const { urls } = boot;
    const device = useStore((s) => s.device);
    const panelOpen = useStore((s) => s.panelOpen);
    const dirty = useStore((s) => s.dirty);
    const saving = useStore((s) => s.saving);
    const status = useStore((s) => s.status);
    const hasDraft = useStore((s) => s.hasDraft);
    const canUndo = useStore((s) => s.past.length > 0);
    const canRedo = useStore((s) => s.future.length > 0);
    const toastMsg = useStore((s) => s.toast);

    const save = useCallback(async (publish = false) => {
        if (getState().saving) return;
        setState({ saving: true });
        try {
            const r = await api(urls.content, { method: 'PUT', body: { content: getState().content, publish } });
            // On garde le contenu local (déjà identique après nettoyage) pour ne pas perdre la sélection.
            setState({ dirty: false, status: r.status, hasDraft: r.has_draft, savedAt: r.saved_at, revisions: r.revisions });
            toast(publish ? t('Page publiée ✔') : r.has_draft ? t('Brouillon enregistré : publiez pour le mettre en ligne') : t('Modifications enregistrées ✔'), 'success');
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setState({ saving: false });
        }
    }, [urls.content]);

    const preview = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = urls.preview;
        form.target = '_blank';
        for (const [k, v] of Object.entries({ _token: boot.csrf, content: JSON.stringify(getState().content) })) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = k;
            input.value = v;
            form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
        form.remove();
    };

    // Raccourcis clavier (dans la page et dans l'iframe).
    const onKey = useCallback((e) => {
        const mod = e.ctrlKey || e.metaKey;
        const typing = /INPUT|TEXTAREA|SELECT/.test(e.target.tagName) || e.target.isContentEditable;
        if (mod && e.key.toLowerCase() === 's') { e.preventDefault(); save(); return; }
        if (typing) return;
        const key = e.key.toLowerCase();
        const selected = getState().selected;
        if (mod && key === 'z' && !e.shiftKey) { e.preventDefault(); undo(); }
        else if (mod && (key === 'y' || (key === 'z' && e.shiftKey))) { e.preventDefault(); redo(); }
        else if (mod && key === 'd' && selected) { e.preventDefault(); duplicateEl(selected); }
        else if (mod && key === 'c' && selected) {
            // Du texte sélectionné dans la page se copie normalement.
            const doc = e.target.ownerDocument;
            if (doc.getSelection()?.toString()) return;
            e.preventDefault();
            copyEl(selected);
        } else if (mod && key === 'v' && e.shiftKey && selected) { e.preventDefault(); pasteStyle(selected); }
        else if (mod && key === 'v') { e.preventDefault(); pasteAfter(selected); }
        else if ((e.key === 'Delete' || e.key === 'Backspace') && selected) { e.preventDefault(); removeEl(selected); }
        else if (e.key === 'Escape') setState({ selected: null, panel: 'elements', menu: null });
    }, [save]);

    useEffect(() => {
        document.addEventListener('keydown', onKey);
        const warn = (e) => { if (getState().dirty) { e.preventDefault(); e.returnValue = ''; } };
        window.addEventListener('beforeunload', warn);
        return () => { document.removeEventListener('keydown', onKey); window.removeEventListener('beforeunload', warn); };
    }, [onKey]);

    const current = DEVICES.find((d) => d.id === device);

    return (
        <div className="flex h-screen flex-col bg-slate-100 text-slate-900">
            <header className="flex h-14 shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-white px-3">
                <div className="flex min-w-0 items-center gap-3">
                    <a href={urls.back} className="grid h-9 w-9 shrink-0 place-items-center rounded-lg hover:bg-slate-100" title={t('Retour à l’administration')} aria-label={t('Retour à l’administration')}>
                        <svg viewBox="0 0 64 64" width="30" height="30" aria-hidden="true">
                            <rect x="5" y="25" width="25" height="33" rx="5" fill="#e3c48f" />
                            <rect x="17" y="16" width="25" height="34" rx="5" fill="#1e1b4b" />
                            <rect x="30" y="6" width="28" height="37" rx="5" fill="#aa4d26" />
                            <rect x="35" y="11" width="18" height="6" rx="2" fill="#ffffff" />
                            <rect x="35" y="20" width="8" height="10" rx="2" fill="#ffffff" />
                            <rect x="45" y="20" width="8" height="10" rx="2" fill="#ffffff" fillOpacity="0.6" />
                        </svg>
                    </a>
                    <div className="min-w-0">
                        <p className="truncate text-sm font-semibold">{boot.page.title}</p>
                        <p className="text-xs text-slate-500">
                            {status === 'published' ? <span className="text-emerald-600">{t('● Publiée')}</span> : <span className="text-amber-600">{t('● Brouillon')}</span>}
                            {dirty ? <span className="ml-2">{t('· modifications non enregistrées')}</span>
                                : hasDraft && status === 'published' && <span className="ml-2">{t('· brouillon non publié')}</span>}
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                <button onClick={togglePanel} aria-pressed={panelOpen}
                    title={panelOpen ? t('Masquer le panneau (plus de place pour la page)') : t('Afficher le panneau')}
                    aria-label={panelOpen ? t('Masquer le panneau (plus de place pour la page)') : t('Afficher le panneau')}
                    className={`rounded-md p-2 ${panelOpen ? 'text-slate-600 hover:bg-slate-100' : 'bg-indigo-50 text-indigo-700'}`}>
                    <Svg d="M4 4h16v16H4zM9 4v16" />
                </button>
                <div className="flex items-center gap-1 rounded-lg bg-slate-100 p-1">
                    {DEVICES.map((d) => (
                        <button key={d.id} title={d.label} onClick={() => setState({ device: d.id })}
                            className={`rounded-md p-1.5 ${device === d.id ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-900'}`}>
                            <Svg d={d.icon} />
                        </button>
                    ))}
                </div>
                </div>

                <div className="flex items-center gap-1.5">
                    <button className="rounded-md p-2 text-slate-600 hover:bg-slate-100 disabled:opacity-30" disabled={!canUndo} onClick={undo} title={t('Annuler (Ctrl+Z)')}><Svg d="M9 14L4 9l5-5M4 9h10.5a5.5 5.5 0 010 11H11" /></button>
                    <button className="rounded-md p-2 text-slate-600 hover:bg-slate-100 disabled:opacity-30" disabled={!canRedo} onClick={redo} title={t('Rétablir (Ctrl+Y)')}><Svg d="M15 14l5-5-5-5M20 9H9.5a5.5 5.5 0 000 11H13" /></button>
                    <a href={urls.settings} className="hidden rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 lg:block">{t('Réglages SEO')}</a>
                    <button className="rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100" onClick={preview}>{t('Aperçu')}</button>
                    <button className="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold hover:bg-slate-50 disabled:opacity-50" disabled={saving} onClick={() => save(false)}>
                        {saving ? t('Enregistrement…') : t('Enregistrer')}
                    </button>
                    <button className="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50" disabled={saving} onClick={() => save(true)}>
                        {status === 'published' ? t('Mettre à jour') : t('Publier')}
                    </button>
                </div>
            </header>

            <div className="flex min-h-0 flex-1">
                <Panel boot={boot} />
                <Stage device={current} boot={boot} onKey={onKey} />
            </div>

            {toastMsg && (
                <div className={`fixed bottom-5 left-1/2 z-50 -translate-x-1/2 rounded-lg px-4 py-2.5 text-sm font-medium text-white shadow-lg ${toastMsg.type === 'error' ? 'bg-red-600' : toastMsg.type === 'success' ? 'bg-emerald-600' : 'bg-slate-800'}`}>
                    {toastMsg.message}
                </div>
            )}
        </div>
    );
}

/**
 * Zone du canevas. En mode ordinateur, si l'écran est trop étroit pour
 * 1025 px, la page est affichée à 1025 px puis réduite à l'échelle : on voit
 * ainsi le vrai rendu ordinateur, et non celui de la tablette.
 */
function Stage({ device, boot, onKey }) {
    const ref = useRef(null);
    const [space, setSpace] = useState({ w: 0, h: 0 });
    useEffect(() => {
        const el = ref.current;
        const observer = new ResizeObserver(() => setSpace({ w: el.clientWidth - 32, h: el.clientHeight - 32 }));
        observer.observe(el);
        return () => observer.disconnect();
    }, []);
    const scale = device.id === 'desktop' && space.w > 0 && space.w < DESKTOP_MIN ? space.w / DESKTOP_MIN : 1;
    const style = scale < 1
        ? { width: DESKTOP_MIN, height: space.h / scale, transform: `scale(${scale})`, transformOrigin: 'top left' }
        : { width: device.width, maxWidth: '100%' };

    return (
        <div ref={ref} className="relative flex min-w-0 flex-1 justify-center overflow-auto p-4" onClick={() => setState({ selected: null, panel: 'elements' })}>
            <div className={scale < 1 ? 'absolute left-4 top-4' : 'h-full transition-[width] duration-300'} style={style} onClick={(e) => e.stopPropagation()}>
                <Frame boot={boot} onKey={onKey} />
            </div>
            {scale < 1 && <p className="pointer-events-none absolute bottom-1 right-3 text-[11px] text-slate-400">{t('Affichage ordinateur réduit à :percent %', { percent: Math.round(scale * 100) })}</p>}
        </div>
    );
}

/** Iframe qui contient le canevas : les media queries y réagissent à la largeur réelle. */
function Frame({ boot, onKey }) {
    const ref = useRef(null);
    const rootRef = useRef(null);

    useEffect(() => {
        const iframe = ref.current;
        const mount = () => {
            const doc = iframe.contentDocument;
            if (!doc || rootRef.current) return;
            doc.addEventListener('keydown', onKey);
            // Évite que le navigateur ouvre un fichier déposé hors d'une zone prévue.
            doc.addEventListener('dragover', (e) => e.preventDefault());
            doc.addEventListener('drop', (e) => e.preventDefault());
            rootRef.current = createRoot(doc.getElementById('root'));
            rootRef.current.render(<Canvas />);
        };
        if (iframe.contentDocument?.readyState === 'complete' && iframe.contentDocument.getElementById('root')) mount();
        iframe.addEventListener('load', mount);
        return () => iframe.removeEventListener('load', mount);
    }, [onKey]);

    // Mêmes couleurs et polices globales que sur le site publié (DesignTokens.php).
    const design = boot.design;
    const srcDoc = `<!DOCTYPE html><html lang="${boot.page.locale}"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="${design.fontsUrl}">
<link rel="stylesheet" href="${boot.siteCss}">
${(boot.pluginCss ?? []).map((href) => `<link rel="stylesheet" href="${href}">`).join('')}
<style>${design.css.replace(/</g, '')}</style>
</head><body><div id="root"></div></body></html>`;

    return <iframe ref={ref} title={t('Canevas')} srcDoc={srcDoc} className="h-full w-full rounded-md bg-white shadow-sm ring-1 ring-slate-200" />;
}
