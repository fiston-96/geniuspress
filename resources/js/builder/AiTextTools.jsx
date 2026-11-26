import { useState } from 'react';
import { api } from './api';
import { Spinner } from './AiPanel';
import { commit, getState, toast } from './store';
import { updateSettings, walk } from './tree';
import { t } from './i18n';

// Champ texte modifiable par l'IA pour chaque type de widget.
export const AI_FIELDS = {
    heading: { key: 'text', format: 'plain' },
    text: { key: 'html', format: 'html' },
    button: { key: 'text', format: 'plain' },
};

const ACTIONS = [
    ['improve', t('Améliorer')],
    ['shorten', t('Raccourcir')],
    ['expand', t('Développer')],
    ['fix', t('Corriger')],
    ['formal', t('Ton pro')],
    ['friendly', t('Ton chaleureux')],
];

/** Autres textes de la page, pour que l'IA reste cohérente. */
function pageContext(excludeId) {
    const parts = [];
    walk(getState().content.elements, (w) => {
        if (w.id === excludeId || w.type === 'container') return;
        const txt = w.settings.text ?? (w.settings.html ? w.settings.html.replace(/<[^>]+>/g, ' ') : null);
        if (txt) parts.push(txt.replace(/\s+/g, ' ').trim());
    });
    return parts.join('\n').slice(0, 3000);
}

export default function AiTextTools({ boot, widget }) {
    const field = AI_FIELDS[widget.type];
    const [busy, setBusy] = useState(null);
    const [instructions, setInstructions] = useState('');
    if (!field || !boot.ai.configured) return null;

    const current = widget.settings[field.key] ?? '';
    const hasText = (field.format === 'html' ? current.replace(/<[^>]+>/g, '') : current).trim().length > 0;

    const run = async (action) => {
        setBusy(action);
        try {
            const r = await api(boot.urls.aiText, {
                method: 'POST',
                body: { action, format: field.format, text: current, instructions, context: pageContext(widget.id), page_id: (boot.document ?? 'page') === 'page' ? boot.page.id : null },
            });
            commit(updateSettings(getState().content, widget.id, { [field.key]: r.text }));
            toast(t('Texte mis à jour. Ctrl+Z pour revenir en arrière.'), 'success');
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setBusy(null);
        }
    };

    return (
        <div className="rounded-lg border border-brand-100 bg-brand-50/60 p-3">
            <p className="mb-2 text-xs font-semibold text-brand-900">{t('✨ Assistant de rédaction')}</p>
            <div className="flex gap-1.5">
                <input className="min-w-0 flex-1 rounded-md border border-brand-200 bg-white px-2 py-1.5 text-xs outline-none focus:border-brand-500"
                    value={instructions} onChange={(e) => setInstructions(e.target.value)} maxLength={1000}
                    placeholder={hasText ? t('Consigne (facultatif)') : t('Que doit-on écrire ?')}
                    onKeyDown={(e) => { if (e.key === 'Enter' && instructions.trim()) run('write'); }} />
                <button className="rounded-md bg-brand-600 px-2.5 text-xs font-semibold text-white hover:bg-brand-500 disabled:opacity-50"
                    disabled={!!busy || (!instructions.trim() && !hasText)} onClick={() => run('write')}>
                    {busy === 'write' ? <Spinner /> : t('Écrire')}
                </button>
            </div>
            {hasText && (
                <div className="mt-2 flex flex-wrap gap-1">
                    {ACTIONS.map(([action, label]) => (
                        <button key={action} disabled={!!busy} onClick={() => run(action)}
                            className="rounded-full border border-brand-200 bg-white px-2 py-0.5 text-[11px] font-medium text-brand-700 hover:border-brand-400 disabled:opacity-50">
                            {busy === action ? <Spinner label={label} /> : label}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
