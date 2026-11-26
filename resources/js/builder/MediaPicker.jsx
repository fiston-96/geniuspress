import { useEffect, useRef, useState } from 'react';
import { api } from './api';
import { toast } from './store';
import { t } from './i18n';

export default function MediaPicker({ urls, onPick, onClose }) {
    const [items, setItems] = useState(null);
    const [uploading, setUploading] = useState(false);
    const input = useRef(null);

    useEffect(() => {
        api(urls.media).then((r) => setItems(r.data.filter((m) => m.mime_type.startsWith('image/')))).catch((e) => toast(e.message, 'error'));
    }, [urls.media]);

    const upload = async (files) => {
        setUploading(true);
        try {
            for (const file of files) {
                const form = new FormData();
                form.append('file', file);
                const m = await api(urls.media, { method: 'POST', form });
                setItems((prev) => [m, ...(prev ?? [])]);
            }
        } catch (e) {
            toast(e.message, 'error');
        } finally {
            setUploading(false);
        }
    };

    return (
        <div className="fixed inset-0 z-50 grid place-items-center bg-slate-900/50 p-4" onClick={onClose}>
            <div className="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" onClick={(e) => e.stopPropagation()}
                onDragOver={(e) => e.preventDefault()} onDrop={(e) => { e.preventDefault(); upload([...e.dataTransfer.files]); }}>
                <div className="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                    <h2 className="font-semibold">{t('Médiathèque')}</h2>
                    <div className="flex items-center gap-2">
                        <input ref={input} type="file" accept="image/*" multiple hidden onChange={(e) => upload([...e.target.files])} />
                        <button className="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-500 disabled:opacity-50" disabled={uploading} onClick={() => input.current.click()}>
                            {uploading ? t('Envoi…') : t('Envoyer des images')}
                        </button>
                        <button className="rounded-lg px-2 py-1.5 text-slate-500 hover:bg-slate-100" onClick={onClose} aria-label={t('Fermer')}>✕</button>
                    </div>
                </div>
                <div className="grid flex-1 grid-cols-3 gap-3 overflow-y-auto p-5 sm:grid-cols-4 md:grid-cols-5">
                    {items === null && <p className="col-span-full py-10 text-center text-sm text-slate-500">{t('Chargement…')}</p>}
                    {items?.length === 0 && <p className="col-span-full py-10 text-center text-sm text-slate-500">{t('Aucune image. Envoyez-en une ou glissez-la ici.')}</p>}
                    {items?.map((m) => (
                        <button key={m.id} className="group aspect-square overflow-hidden rounded-lg border border-slate-200 bg-slate-100 outline-none ring-brand-500 hover:ring-2 focus:ring-2"
                            onClick={() => onPick(m)} title={m.original_name}>
                            <img src={m.url} alt={m.alt || ''} className="h-full w-full object-cover" loading="lazy" />
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
