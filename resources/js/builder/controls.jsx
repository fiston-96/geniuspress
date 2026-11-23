import { useEffect, useRef, useState } from 'react';
import DOMPurify from 'dompurify';
import MediaPicker from './MediaPicker';
import { t } from './i18n';

const inputCls = 'block w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20';

function Label({ children, onReset, hasValue }) {
    return (
        <div className="mb-1 flex items-center justify-between">
            <span className="text-xs font-medium text-slate-600">{children}</span>
            {hasValue && onReset && (
                <button className="text-[11px] text-slate-400 hover:text-slate-700" onClick={onReset} title={t('Revenir à la valeur par défaut')}>{t('Réinitialiser')}</button>
            )}
        </div>
    );
}

export function Control({ control, value, onChange, urls, onPickMedia }) {
    const set = (v) => onChange(control.key, v);
    const reset = () => onChange(control.key, null);
    const has = value !== undefined && value !== null && value !== '';

    switch (control.type) {
        case 'text':
        case 'url':
            return (
                <div>
                    <Label>{control.label}</Label>
                    <input className={inputCls} type="text" value={value ?? ''} placeholder={control.placeholder ?? (control.type === 'url' ? 'https://… ou /page ou #ancre' : '')} onChange={(e) => set(e.target.value)} />
                </div>
            );
        case 'textarea':
            return (
                <div>
                    <Label>{control.label}</Label>
                    <textarea className={inputCls} rows={3} value={value ?? ''} onChange={(e) => set(e.target.value)} />
                </div>
            );
        case 'select':
            return (
                <div>
                    <Label>{control.label}</Label>
                    <select className={inputCls} value={value ?? ''} onChange={(e) => set(e.target.value)}>
                        {control.options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
                    </select>
                </div>
            );
        case 'segmented':
            return (
                <div>
                    <Label hasValue={has} onReset={reset}>{control.label}</Label>
                    <div className="flex flex-wrap gap-1 rounded-md bg-slate-100 p-0.5">
                        {control.options.map((o) => (
                            <button key={o.value} onClick={() => set(o.value)}
                                className={`flex-1 rounded px-2 py-1 text-xs font-medium transition ${value === o.value ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'}`}>
                                {o.label}
                            </button>
                        ))}
                    </div>
                </div>
            );
        case 'slider':
            return (
                <div>
                    <Label hasValue={has} onReset={reset}>{control.label}</Label>
                    <div className="flex items-center gap-2">
                        <input type="range" className="flex-1 accent-indigo-600" min={control.min} max={control.max} step={control.step ?? 1}
                            value={value ?? control.default ?? control.min} onChange={(e) => set(Number(e.target.value))} />
                        <input type="number" className={`${inputCls} !w-16 !px-1.5 text-right`} min={control.min} max={control.max}
                            value={value ?? ''} placeholder={control.default ?? '—'} onChange={(e) => set(e.target.value === '' ? null : Number(e.target.value))} />
                    </div>
                </div>
            );
        case 'color':
            return (
                <div>
                    <Label hasValue={has} onReset={reset}>{control.label}</Label>
                    <div className="flex items-center gap-2">
                        <input type="color" className="h-8 w-10 cursor-pointer rounded border border-slate-300 bg-white p-0.5" value={/^#[0-9a-f]{6}$/i.test(value ?? '') ? value : '#000000'} onChange={(e) => set(e.target.value)} />
                        <input className={inputCls} value={value ?? ''} placeholder={t('Par défaut')} onChange={(e) => set(e.target.value)} />
                    </div>
                </div>
            );
        case 'toggle':
            return (
                <label className="flex cursor-pointer items-center justify-between gap-3 py-0.5">
                    <span className="text-xs font-medium text-slate-600">{control.label}</span>
                    <button role="switch" aria-checked={!!value} onClick={() => set(!value)}
                        className={`relative h-5 w-9 shrink-0 rounded-full transition ${value ? 'bg-indigo-600' : 'bg-slate-300'}`}>
                        <span className={`absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition ${value ? 'left-4.5' : 'left-0.5'}`} />
                    </button>
                </label>
            );
        case 'image':
            return <ImageControl control={control} value={value} onChange={onChange} urls={urls} />;
        case 'richtext':
            return <RichText label={control.label} value={value ?? ''} onChange={set} />;
        default:
            return null;
    }
}

function ImageControl({ control, value, onChange, urls }) {
    const [open, setOpen] = useState(false);
    return (
        <div>
            <Label hasValue={!!value} onReset={() => { onChange(control.key, null); onChange('media_id', null); }}>{control.label}</Label>
            <button onClick={() => setOpen(true)} className="grid aspect-video w-full place-items-center overflow-hidden rounded-md border border-dashed border-slate-300 bg-slate-50 text-xs text-slate-500 hover:border-indigo-400">
                {value ? <img src={value} alt="" className="h-full w-full object-cover" /> : t('Choisir une image')}
            </button>
            <input className={`${inputCls} mt-1.5`} value={value ?? ''} placeholder={t('ou collez l’adresse d’une image')} onChange={(e) => { onChange(control.key, e.target.value); onChange('media_id', null); }} />
            {open && (
                <MediaPicker urls={urls} onClose={() => setOpen(false)} onPick={(m) => {
                    onChange(control.key, m.url);
                    if (control.key === 'url') {
                        onChange('media_id', m.id);
                        if (m.alt) onChange('alt', m.alt);
                    }
                    setOpen(false);
                }} />
            )}
        </div>
    );
}

function RichText({ label, value, onChange }) {
    const ref = useRef(null);
    const last = useRef(value);

    useEffect(() => {
        // Ne réécrit le DOM que si la valeur vient d'ailleurs (annuler, autre widget…).
        if (ref.current && value !== last.current) {
            ref.current.innerHTML = DOMPurify.sanitize(value);
            last.current = value;
        }
    }, [value]);

    useEffect(() => {
        if (ref.current) ref.current.innerHTML = DOMPurify.sanitize(value);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const emit = () => {
        const html = ref.current.innerHTML;
        last.current = html;
        onChange(html);
    };
    const cmd = (name, arg) => {
        ref.current.focus();
        document.execCommand(name, false, arg);
        emit();
    };
    const link = () => {
        const url = prompt(t('Adresse du lien (https://…, /page, mailto:…)'));
        if (url) cmd('createLink', url);
    };

    const btn = 'rounded px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-white hover:text-slate-900';
    return (
        <div>
            <Label>{label}</Label>
            <div className="overflow-hidden rounded-md border border-slate-300 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20">
                <div className="flex flex-wrap gap-0.5 border-b border-slate-200 bg-slate-50 p-1">
                    <button className={btn} onMouseDown={(e) => e.preventDefault()} onClick={() => cmd('bold')}><b>G</b></button>
                    <button className={btn} onMouseDown={(e) => e.preventDefault()} onClick={() => cmd('italic')}><i>I</i></button>
                    <button className={btn} onMouseDown={(e) => e.preventDefault()} onClick={() => cmd('underline')}><u>S</u></button>
                    <button className={btn} onMouseDown={(e) => e.preventDefault()} onClick={() => cmd('insertUnorderedList')}>{t('• Liste')}</button>
                    <button className={btn} onMouseDown={(e) => e.preventDefault()} onClick={() => cmd('insertOrderedList')}>{t('1. Liste')}</button>
                    <button className={btn} onMouseDown={(e) => e.preventDefault()} onClick={link}>{t('Lien')}</button>
                    <button className={btn} onMouseDown={(e) => e.preventDefault()} onClick={() => cmd('removeFormat')}>{t('Effacer')}</button>
                </div>
                <div ref={ref} contentEditable suppressContentEditableWarning onInput={emit}
                    className="prose-sm max-h-80 min-h-28 overflow-y-auto bg-white px-3 py-2 text-sm outline-none [&_a]:text-indigo-600 [&_a]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-2 [&_ul]:list-disc [&_ul]:pl-5" />
            </div>
        </div>
    );
}
