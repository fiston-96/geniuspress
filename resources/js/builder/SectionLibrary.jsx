// Bibliothèque de sections prêtes à l'emploi (App\Templates\SectionLibrary) : catégories,
// recherche, vignettes rendues par le serveur aux couleurs du site, insertion en un clic.
import { useEffect, useMemo, useState } from 'react';
import { api } from './api';
import { insertCopy } from './actions';
import { getState, setState, toast, useStore } from './store';
import { find } from './tree';
import { t } from './i18n';
import { IconButton, UiIcon } from './ui';

let cache = null;

/** Largeur de rendu des vignettes (px) et largeur affichée : la page est réduite à l'échelle. */
const RENDER_W = 1280;
const RENDER_H = 760;
const CARD_W = 300;

export function openLibrary() {
    setState({ libraryOpen: true });
}

const fold = (v) => String(v ?? '').normalize('NFD').replace(/\p{Diacritic}/gu, '').toLowerCase();

export default function SectionLibrary({ locale }) {
    const open = useStore((s) => s.libraryOpen);
    const [data, setData] = useState(cache);
    const [category, setCategory] = useState('all');
    const [query, setQuery] = useState('');
    const [busy, setBusy] = useState(null);

    useEffect(() => {
        if (!open || data) return;
        const url = `${getState().urls.sections}?locale=${encodeURIComponent(locale)}`;
        api(url).then((r) => { cache = r; setData(r); }).catch((e) => toast(e.message, 'error'));
    }, [open, data, locale]);

    useEffect(() => {
        if (!open) return undefined;
        const onKey = (e) => { if (e.key === 'Escape') setState({ libraryOpen: false }); };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [open]);

    const list = useMemo(() => {
        const words = fold(query).split(/\s+/).filter(Boolean);
        return (data?.sections ?? []).filter((s) => (category === 'all' || s.category === category)
            && words.every((w) => fold(`${s.name} ${data.categories[s.category]}`).includes(w)));
    }, [data, category, query]);

    if (!open) return null;

    const insert = async (section) => {
        setBusy(section.id);
        try {
            const { urls, content, selected } = getState();
            const { node } = await api(`${urls.sectionInsert.replace('__ID__', section.id)}?locale=${encodeURIComponent(locale)}`, { method: 'POST' });
            // Après la section de premier niveau qui contient la sélection, sinon à la fin de la page.
            const loc = selected ? find(content, selected) : null;
            const top = loc ? (loc.path[0]?.id ?? loc.node.id) : null;
            insertCopy(node, top);
            setState({ libraryOpen: false });
            toast(t('Section ajoutée : ses photos sont dans la médiathèque.'), 'success');
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setBusy(null);
        }
    };

    const counts = (data?.sections ?? []).reduce((acc, s) => ({ ...acc, [s.category]: (acc[s.category] ?? 0) + 1 }), {});

    return (
        <div className="fixed inset-0 z-50 flex bg-slate-900/60 p-4 backdrop-blur-sm" onClick={() => setState({ libraryOpen: false })} role="dialog" aria-modal="true" aria-label={t('Bibliothèque de sections')}>
            <div className="mx-auto flex h-full w-full max-w-7xl overflow-hidden rounded-2xl bg-white shadow-2xl" onClick={(e) => e.stopPropagation()}>
                <aside className="flex w-56 shrink-0 flex-col border-r border-slate-200 bg-slate-50">
                    <div className="flex items-center gap-2 px-4 py-4">
                        <UiIcon name="layout-template" className="h-5 w-5 text-brand-600" />
                        <h2 className="text-sm font-semibold">{t('Bibliothèque de sections')}</h2>
                    </div>
                    <nav className="flex-1 overflow-y-auto px-2 pb-3">
                        {[['all', t('Toutes les sections')], ...Object.entries(data?.categories ?? {})].map(([id, label]) => (
                            <button key={id} onClick={() => setCategory(id)} aria-pressed={category === id}
                                className={`flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm ${category === id ? 'bg-white font-semibold text-brand-700 shadow-sm' : 'text-slate-600 hover:bg-white/70'}`}>
                                <span>{label}</span>
                                <span className="text-xs text-slate-400">{id === 'all' ? data?.sections.length ?? '' : counts[id] ?? 0}</span>
                            </button>
                        ))}
                    </nav>
                </aside>
                <section className="flex min-w-0 flex-1 flex-col">
                    <header className="flex items-center gap-3 border-b border-slate-200 px-5 py-3">
                        <label className="relative block flex-1">
                            <span className="pointer-events-none absolute inset-y-0 left-2.5 grid place-items-center text-slate-400"><UiIcon name="search" /></span>
                            <input type="search" autoFocus value={query} onChange={(e) => setQuery(e.target.value)} placeholder={t('Rechercher une section')} aria-label={t('Rechercher une section')}
                                className="block w-full max-w-md rounded-lg border border-slate-300 bg-slate-50 py-2 pl-9 pr-3 text-sm outline-none focus:border-brand-500 focus:bg-white focus:ring-2 focus:ring-brand-500/20" />
                        </label>
                        <p className="hidden text-xs text-slate-500 lg:block">{t('Les sections prennent les couleurs et les polices de votre site.')}</p>
                        <IconButton icon="x" label={t('Fermer')} onClick={() => setState({ libraryOpen: false })} className="h-9 w-9 text-slate-500 hover:bg-slate-100" />
                    </header>
                    <div className="flex-1 overflow-y-auto p-5">
                        {!data && <p className="py-16 text-center text-sm text-slate-500">{t('Chargement…')}</p>}
                        {data && list.length === 0 && <p className="py-16 text-center text-sm text-slate-500">{t('Aucune section ne correspond.')}</p>}
                        <div className="grid gap-5" style={{ gridTemplateColumns: `repeat(auto-fill, minmax(${CARD_W}px, 1fr))` }}>
                            {list.map((s) => (
                                <button key={s.id} onClick={() => insert(s)} disabled={!!busy} title={t('Insérer cette section')}
                                    className="group overflow-hidden rounded-xl border border-slate-200 bg-white text-left transition hover:-translate-y-0.5 hover:border-brand-400 hover:shadow-lg disabled:opacity-60">
                                    <span className="relative block overflow-hidden bg-slate-100" style={{ height: Math.round(RENDER_H * (CARD_W / RENDER_W)) }}>
                                        <iframe title={s.name} loading="lazy" tabIndex={-1} aria-hidden="true"
                                            src={`${getState().urls.sectionPreview.replace('__ID__', s.id)}?locale=${encodeURIComponent(locale)}&v=${data.version}`}
                                            className="pointer-events-none absolute left-0 top-0 origin-top-left border-0"
                                            style={{ width: RENDER_W, height: RENDER_H, transform: `scale(${CARD_W / RENDER_W})` }} />
                                        <span className="absolute inset-0 grid place-items-center bg-brand-900/0 opacity-0 transition group-hover:bg-brand-900/35 group-hover:opacity-100">
                                            <span className="flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-brand-700 shadow">
                                                <UiIcon name={busy === s.id ? 'history' : 'plus'} className="h-3.5 w-3.5" />{busy === s.id ? t('Insertion…') : t('Insérer')}
                                            </span>
                                        </span>
                                    </span>
                                    <span className="block border-t border-slate-100 px-3 py-2">
                                        <span className="block truncate text-sm font-medium text-slate-800">{s.name}</span>
                                        <span className="block text-[11px] text-slate-400">{data.categories[s.category]}</span>
                                    </span>
                                </button>
                            ))}
                        </div>
                    </div>
                </section>
            </div>
        </div>
    );
}
