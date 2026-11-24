import { useState } from 'react';
import { api } from './api';
import { commit, getState, setState, toast } from './store';
import { updateSettings } from './tree';
import { t } from './i18n';

const box = 'rounded-xl border border-slate-200 bg-white p-4';
const primary = 'inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50';
const secondary = 'rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-50';

export function Spinner({ label }) {
    return (
        <span className="inline-flex items-center gap-2">
            <svg className="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" strokeOpacity=".25" strokeWidth="4" /><path d="M22 12a10 10 0 00-10-10" stroke="currentColor" strokeWidth="4" strokeLinecap="round" /></svg>
            {label}
        </span>
    );
}

export function NotConfigured({ boot }) {
    return (
        <div className="m-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <p className="font-semibold">{t('L’assistant IA n’est pas encore activé.')}</p>
            <p className="mt-1">{t('Ajoutez une clé API Anthropic (Claude) et une présentation de votre organisation dans les réglages du site.')}</p>
            {boot.urls.aiSettings
                ? <a href={boot.urls.aiSettings} className="mt-3 inline-block font-semibold text-amber-900 underline">{t('Ouvrir les réglages')}</a>
                : <p className="mt-2">{t('Demandez à un administrateur de le faire.')}</p>}
        </div>
    );
}

export default function AiPanel({ boot }) {
    if (!boot.ai.configured) return <NotConfigured boot={boot} />;
    return (
        <div className="space-y-4 p-4">
            <Generate boot={boot} />
            <Audit boot={boot} />
            <Translate boot={boot} />
        </div>
    );
}

function Generate({ boot }) {
    const [brief, setBrief] = useState('');
    const [mode, setMode] = useState(getState().content.elements.length ? 'append' : 'replace');
    const [busy, setBusy] = useState(false);
    const [meta, setMeta] = useState(null);

    const run = async () => {
        setBusy(true);
        setMeta(null);
        try {
            const r = await api(boot.urls.aiGenerate, { method: 'POST', body: { brief } });
            const current = getState().content;
            const elements = mode === 'replace' ? r.content.elements : [...current.elements, ...r.content.elements];
            commit({ ...current, elements }, { selected: null });
            setMeta({ meta_title: r.meta_title, meta_description: r.meta_description });
            toast(t('Page générée : :count sections. Ctrl+Z pour annuler.', { count: r.content.elements.length }), 'success');
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setBusy(false);
        }
    };

    return (
        <section className={box}>
            <h3 className="font-semibold">{t('✨ Créer la page avec l’IA')}</h3>
            <p className="mt-1 text-xs text-slate-500">{t('Décrivez la page voulue : objectif, public, sections, appel à l’action.')}</p>
            <textarea className="mt-3 block w-full rounded-md border border-slate-300 px-2.5 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                rows={5} maxLength={4000} value={brief} onChange={(e) => setBrief(e.target.value)}
                placeholder={t('Ex. : Page d’accueil pour notre ONG d’éducation à Goma. Présenter notre mission, nos 3 programmes, nos chiffres clés, un appel aux dons et un formulaire de contact.')} />
            <div className="mt-2 flex gap-1 rounded-md bg-slate-100 p-0.5 text-xs">
                {[['replace', t('Remplacer la page')], ['append', t('Ajouter à la fin')]].map(([v, l]) => (
                    <button key={v} onClick={() => setMode(v)} className={`flex-1 rounded px-2 py-1 font-medium ${mode === v ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600'}`}>{l}</button>
                ))}
            </div>
            <button className={`${primary} mt-3 w-full`} disabled={busy || brief.trim().length < 10} onClick={run}>
                {busy ? <Spinner label={t('Construction de la page… (30 à 90 s)')} /> : 'Générer'}
            </button>
            {meta && (meta.meta_title || meta.meta_description) && <MetaSuggestion boot={boot} meta={meta} onDone={() => setMeta(null)} />}
        </section>
    );
}

function MetaSuggestion({ boot, meta, onDone }) {
    const [busy, setBusy] = useState(false);
    const apply = async () => {
        setBusy(true);
        try {
            await api(boot.urls.meta, { method: 'PUT', body: meta });
            toast(t('Titre et description SEO enregistrés.'), 'success');
            onDone();
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setBusy(false);
        }
    };
    return (
        <div className="mt-3 rounded-lg bg-slate-50 p-3 text-xs">
            <p className="font-semibold text-slate-700">{t('Référencement proposé')}</p>
            <p className="mt-1 font-medium text-blue-700">{meta.meta_title}</p>
            <p className="text-slate-600">{meta.meta_description}</p>
            <button className={`${secondary} mt-2`} disabled={busy} onClick={apply}>{t('Appliquer')}</button>
        </div>
    );
}

const SEVERITY = {
    high: [t('Important'), 'bg-red-50 text-red-700'],
    medium: [t('Moyen'), 'bg-amber-50 text-amber-700'],
    low: [t('Mineur'), 'bg-slate-100 text-slate-600'],
};
const CATEGORY = { seo: 'SEO', accessibility: t('Accessibilité'), content: t('Contenu') };

function Audit({ boot }) {
    const [busy, setBusy] = useState(false);
    const [report, setReport] = useState(null);

    const run = async () => {
        setBusy(true);
        try {
            setReport(await api(boot.urls.aiAudit, { method: 'POST', body: { content: getState().content } }));
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setBusy(false);
        }
    };

    const applyAlts = () => {
        let content = getState().content;
        for (const a of report.image_alts) content = updateSettings(content, a.widget_id, { alt: a.alt });
        commit(content);
        setReport({ ...report, image_alts: [] });
        toast(t('Textes alternatifs ajoutés aux images.'), 'success');
    };

    const color = report ? (report.score >= 80 ? 'text-emerald-600' : report.score >= 50 ? 'text-amber-600' : 'text-red-600') : '';

    return (
        <section className={box}>
            <h3 className="font-semibold">{t('🔎 SEO et accessibilité')}</h3>
            <p className="mt-1 text-xs text-slate-500">{t('L’IA vérifie le référencement, les titres, les contrastes et les images, puis propose des corrections.')}</p>
            <button className={`${primary} mt-3 w-full`} disabled={busy} onClick={run}>{busy ? <Spinner label={t('Analyse en cours…')} /> : report ? t('Relancer l’analyse') : t('Analyser la page')}</button>
            {report && (
                <div className="mt-4 space-y-3">
                    <div className="flex items-center gap-3">
                        <span className={`text-3xl font-bold tabular-nums ${color}`}>{report.score}</span>
                        <p className="text-xs text-slate-600">{report.summary}</p>
                    </div>
                    {(report.meta_title || report.meta_description) && <MetaSuggestion boot={boot} meta={{ meta_title: report.meta_title, meta_description: report.meta_description }} onDone={() => setReport({ ...report, meta_title: '', meta_description: '' })} />}
                    {report.image_alts.length > 0 && (
                        <div className="rounded-lg bg-slate-50 p-3 text-xs">
                            <p className="font-semibold text-slate-700">{t('Textes alternatifs proposés pour :count image(s)', { count: report.image_alts.length })}</p>
                            <ul className="mt-1 list-disc pl-4 text-slate-600">{report.image_alts.map((a) => <li key={a.widget_id}>{a.alt}</li>)}</ul>
                            <button className={`${secondary} mt-2`} onClick={applyAlts}>{t('Appliquer aux images')}</button>
                        </div>
                    )}
                    <ul className="space-y-2">
                        {report.issues.map((issue, i) => (
                            <li key={i} className="rounded-lg border border-slate-200 p-2.5 text-xs">
                                <div className="mb-1 flex gap-1.5">
                                    <span className={`rounded px-1.5 py-0.5 font-semibold ${SEVERITY[issue.severity]?.[1]}`}>{SEVERITY[issue.severity]?.[0]}</span>
                                    <span className="rounded bg-indigo-50 px-1.5 py-0.5 font-medium text-indigo-700">{CATEGORY[issue.category]}</span>
                                </div>
                                <p className="font-medium text-slate-800">{issue.message}</p>
                                <p className="mt-0.5 text-slate-600">→ {issue.fix}</p>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </section>
    );
}

function Translate({ boot }) {
    const others = Object.entries(boot.locales).filter(([code]) => code !== boot.page.locale);
    const [locale, setLocale] = useState(others[0]?.[0] ?? '');
    const [busy, setBusy] = useState(false);
    const [done, setDone] = useState(boot.translations ?? []);

    const run = async () => {
        if (getState().dirty && !confirm(t('La traduction part de la dernière version enregistrée. Continuer sans enregistrer vos modifications en cours ?'))) return;
        setBusy(true);
        try {
            const r = await api(boot.urls.aiTranslate, { method: 'POST', body: { locale } });
            setDone((prev) => [...prev.filter((tr) => tr.locale !== r.page.locale), { ...r.page, builder_url: r.builder_url }]);
            toast(t('Traduction « :title » créée en brouillon.', { title: r.page.title }), 'success');
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setBusy(false);
        }
    };

    return (
        <section className={box}>
            <h3 className="font-semibold">{t('🌍 Traduire la page')}</h3>
            {others.length === 0 ? (
                <p className="mt-1 text-xs text-slate-500">{t('Votre site n’a qu’une langue. Ajoutez-en d’autres dans les réglages (anglais, swahili, lingala…).')}</p>
            ) : (
                <>
                    <p className="mt-1 text-xs text-slate-500">{t('Crée une copie traduite de la page, en brouillon, avec la même mise en page.')}</p>
                    <div className="mt-3 flex gap-2">
                        <select className="min-w-0 flex-1 rounded-md border border-slate-300 px-2 py-1.5 text-sm" value={locale} onChange={(e) => setLocale(e.target.value)}>
                            {others.map(([code, name]) => <option key={code} value={code}>{name}</option>)}
                        </select>
                        <button className={primary} disabled={busy || !locale} onClick={run}>{busy ? <Spinner label="…" /> : t('Traduire')}</button>
                    </div>
                </>
            )}
            {done.length > 0 && (
                <ul className="mt-3 space-y-1 text-xs">
                    {done.map((tr) => (
                        <li key={tr.locale} className="flex items-center justify-between gap-2">
                            <span className="truncate"><b className="uppercase">{tr.locale}</b> · {tr.title}</span>
                            <a href={tr.builder_url} className="shrink-0 font-semibold text-indigo-600" onClick={(e) => { if (getState().dirty && !confirm(t('Quitter sans enregistrer ?'))) e.preventDefault(); else setState({ dirty: false }); }}>{t('Ouvrir')}</a>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}
