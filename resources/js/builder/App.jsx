import { useCallback, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';
import { api } from './api';
import Canvas from './Canvas';
import Panel from './Panel';
import { commit, getState, redo, setState, toast, undo, useStore } from './store';
import { duplicateNode, removeNode } from './tree';
import { t } from './i18n';

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
        if (mod && e.key.toLowerCase() === 'z' && !e.shiftKey) { e.preventDefault(); undo(); }
        else if (mod && (e.key.toLowerCase() === 'y' || (e.key.toLowerCase() === 'z' && e.shiftKey))) { e.preventDefault(); redo(); }
        else if (mod && e.key.toLowerCase() === 'd' && getState().selected) {
            e.preventDefault();
            const [n, id] = duplicateNode(getState().content, getState().selected);
            commit(n, { selected: id });
        } else if ((e.key === 'Delete' || e.key === 'Backspace') && getState().selected) {
            e.preventDefault();
            commit(removeNode(getState().content, getState().selected), { selected: null, panel: 'elements' });
        } else if (e.key === 'Escape') setState({ selected: null, panel: 'elements' });
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
                    <a href={urls.back} className="grid h-9 w-9 place-items-center rounded-lg bg-indigo-600 font-bold text-white" title={t('Retour à l’administration')}>G</a>
                    <div className="min-w-0">
                        <p className="truncate text-sm font-semibold">{boot.page.title}</p>
                        <p className="text-xs text-slate-500">
                            {status === 'published' ? <span className="text-emerald-600">{t('● Publiée')}</span> : <span className="text-amber-600">{t('● Brouillon')}</span>}
                            {dirty ? <span className="ml-2">{t('· modifications non enregistrées')}</span>
                                : hasDraft && status === 'published' && <span className="ml-2">{t('· brouillon non publié')}</span>}
                        </p>
                    </div>
                </div>

                <div className="flex items-center gap-1 rounded-lg bg-slate-100 p-1">
                    {DEVICES.map((d) => (
                        <button key={d.id} title={d.label} onClick={() => setState({ device: d.id })}
                            className={`rounded-md p-1.5 ${device === d.id ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-900'}`}>
                            <Svg d={d.icon} />
                        </button>
                    ))}
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
                <div className="flex min-w-0 flex-1 justify-center overflow-auto p-4" onClick={() => setState({ selected: null, panel: 'elements' })}>
                    <div className="h-full transition-[width] duration-300" style={{ width: current.width, maxWidth: '100%' }} onClick={(e) => e.stopPropagation()}>
                        <Frame boot={boot} onKey={onKey} />
                    </div>
                </div>
            </div>

            {toastMsg && (
                <div className={`fixed bottom-5 left-1/2 z-50 -translate-x-1/2 rounded-lg px-4 py-2.5 text-sm font-medium text-white shadow-lg ${toastMsg.type === 'error' ? 'bg-red-600' : toastMsg.type === 'success' ? 'bg-emerald-600' : 'bg-slate-800'}`}>
                    {toastMsg.message}
                </div>
            )}
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

    const s = boot.settings;
    const font = s.font_family || 'Inter';
    const srcDoc = `<!DOCTYPE html><html lang="${boot.page.locale}"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://fonts.bunny.net/css?family=${encodeURIComponent(font.toLowerCase())}:400,500,600,700,800&display=swap">
<link rel="stylesheet" href="${boot.siteCss}">
${(boot.pluginCss ?? []).map((href) => `<link rel="stylesheet" href="${href}">`).join('')}
<style>:root{--gp-primary:${s.primary_color};--gp-secondary:${s.secondary_color};--gp-accent:${s.accent_color};--gp-font:'${font}',system-ui,sans-serif}</style>
</head><body><div id="root"></div></body></html>`;

    return <iframe ref={ref} title={t('Canevas')} srcDoc={srcDoc} className="h-full w-full rounded-md bg-white shadow-sm ring-1 ring-slate-200" />;
}
