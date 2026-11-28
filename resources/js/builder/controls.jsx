import { useEffect, useRef, useState } from 'react';
import { cleanHtml } from './html';
import MediaPicker from './MediaPicker';
import { t } from './i18n';
import { useStore } from './store';
import { ICONS, Icon, brandTitle } from './icons';
import { cleanCustomCss } from './style';
import { IconButton, UiIcon } from './ui';
import { TAGS } from './dynamic-tags';
import SHAPES from '../../builder/shapes.json';

/** Noms des séparateurs de formes (resources/builder/shapes.json). */
const SHAPE_LABELS = {
    tilt: t('Pente'), triangle: t('Triangle'), 'triangle-asym': t('Triangle décalé'), curve: t('Courbe'), 'curve-asym': t('Courbe décalée'),
    arrow: t('Flèche'), split: t('Fente'), wave: t('Vague'), waves: t('Vagues superposées'), zigzag: t('Zigzag'), mountains: t('Montagnes'),
};

const inputCls = 'block w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';

function Label({ children, onReset, hasValue, extra }) {
    return (
        <div className="mb-1 flex items-center justify-between gap-2">
            <span className="flex items-center gap-1.5 text-xs font-medium text-slate-600">{children}{extra}</span>
            {hasValue && onReset && (
                <button className="text-[11px] text-slate-400 hover:text-slate-700" onClick={onReset} title={t('Revenir à la valeur par défaut')}>{t('Réinitialiser')}</button>
            )}
        </div>
    );
}

const empty = (v) => v === undefined || v === null || v === '';

/**
 * Un réglage du panneau. « placeholder » est la valeur héritée (d'un écran plus
 * grand, ou de l'état normal pour le survol) ; « extra » s'affiche à côté du libellé.
 */
export function Control({ control, value, onChange, urls, placeholder, extra }) {
    const set = (v) => onChange(control.key, v);
    const reset = () => onChange(control.key, null);
    const has = !empty(value);
    const field = useRef(null);
    // Balises dynamiques : proposées dans les textes de contenu (pas les ancres, classes, noms techniques).
    const tags = ['text', 'textarea'].includes(control.type) && (control.tab ?? 'content') === 'content' && control.dynamic !== false && !NO_TAGS.includes(control.key);
    const insertTag = (token) => {
        const el = field.current;
        const text = String(value ?? '');
        if (!el || el.selectionStart == null) return set(text + token);
        const at = el.selectionStart + token.length;
        set(text.slice(0, el.selectionStart) + token + text.slice(el.selectionEnd));
        requestAnimationFrame(() => { el.focus(); el.setSelectionRange(at, at); });
    };
    const more = tags ? <>{extra}<TagPicker onPick={insertTag} /></> : extra;
    const label = (props = {}) => <Label extra={more} {...props}>{control.label}</Label>;

    switch (control.type) {
        case 'heading':
            return <h3 className="border-t border-slate-100 pt-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400 first:border-0 first:pt-0">{control.label}</h3>;
        case 'text':
        case 'url':
            return (
                <div>
                    {label()}
                    <input ref={field} className={inputCls} type="text" value={value ?? ''} placeholder={placeholder ?? control.placeholder ?? (control.type === 'url' ? t('https://… ou /page ou #ancre') : '')} onChange={(e) => set(e.target.value)} />
                </div>
            );
        case 'textarea':
            return (
                <div>
                    {label()}
                    <textarea ref={field} className={inputCls} rows={3} value={value ?? ''} onChange={(e) => set(e.target.value)} />
                </div>
            );
        case 'shape':
            // Séparateurs de formes : vignettes cliquables (forme dessinée telle qu'elle apparaîtra).
            return (
                <div>
                    {label({ hasValue: has, onReset: reset })}
                    <div className="grid grid-cols-4 gap-1.5" role="radiogroup" aria-label={control.label}>
                        <button type="button" role="radio" aria-checked={!has} title={t('Aucune')} aria-label={t('Aucune')} onClick={reset}
                            className={`grid h-10 place-items-center rounded-md border text-slate-400 ${!has ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-slate-200 hover:border-brand-300'}`}>
                            <UiIcon name="x" className="h-4 w-4" />
                        </button>
                        {Object.entries(SHAPES).map(([name, paths]) => (
                            <button key={name} type="button" role="radio" aria-checked={value === name} title={SHAPE_LABELS[name] ?? name} aria-label={SHAPE_LABELS[name] ?? name} onClick={() => set(name)}
                                className={`h-10 overflow-hidden rounded-md border bg-white p-1 ${value === name ? 'border-brand-500 text-brand-600 ring-2 ring-brand-500/20' : 'border-slate-200 text-slate-400 hover:border-brand-300 hover:text-slate-600'}`}>
                                <svg viewBox="0 0 1000 100" preserveAspectRatio="none" className="h-full w-full" fill="currentColor" aria-hidden="true">
                                    {paths.map((p, i) => <path key={i} d={p.d} opacity={p.opacity} />)}
                                </svg>
                            </button>
                        ))}
                    </div>
                </div>
            );
        case 'code': {
            // CSS personnalisé : refusé (et non appliqué) s'il contient une instruction interdite.
            const invalid = has && cleanCustomCss(value) === null;
            return (
                <div>
                    {label({ hasValue: has, onReset: reset })}
                    <textarea className={`${inputCls} font-mono text-[12px] leading-5 ${invalid ? '!border-red-400' : ''}`} rows={8} spellCheck={false}
                        value={value ?? ''} placeholder={control.placeholder} onChange={(e) => set(e.target.value)}
                        onKeyDown={(e) => {
                            // Tab insère une indentation au lieu de quitter le champ.
                            if (e.key !== 'Tab' || e.shiftKey) return;
                            e.preventDefault();
                            const el = e.currentTarget;
                            const next = `${el.value.slice(0, el.selectionStart)}    ${el.value.slice(el.selectionEnd)}`;
                            const at = el.selectionStart + 4;
                            set(next);
                            requestAnimationFrame(() => el.setSelectionRange(at, at));
                        }} />
                    {invalid
                        ? <p className="mt-1 text-[11px] text-red-600">{t('Ce CSS n’est pas appliqué : accolades déséquilibrées, ou instruction interdite (@import, url javascript:, expression…).')}</p>
                        : control.help && <p className="mt-1 text-[11px] text-slate-500">{control.help}</p>}
                </div>
            );
        }
        case 'select':
            return (
                <div>
                    {label({ hasValue: has, onReset: reset })}
                    <select className={inputCls} value={value ?? ''} onChange={(e) => set(e.target.value)}>
                        {(typeof control.options === 'function' ? control.options() : control.options).map((o) => (
                            <option key={o.value} value={o.value}>{o.value === '' && !empty(placeholder) ? `${o.label} (${control.options.find((x) => x.value === placeholder)?.label ?? placeholder})` : o.label}</option>
                        ))}
                    </select>
                </div>
            );
        case 'segmented':
            return (
                <div>
                    {label({ hasValue: has, onReset: reset })}
                    <div className="flex flex-wrap gap-1 rounded-md bg-slate-100 p-0.5">
                        {control.options.map((o) => {
                            const active = value === o.value;
                            const inherited = !has && placeholder === o.value;
                            return (
                                <button key={o.value} onClick={() => set(active ? null : o.value)}
                                    className={`flex-1 rounded px-2 py-1 text-xs font-medium transition ${active ? 'bg-white text-brand-700 shadow-sm' : inherited ? 'text-brand-500 ring-1 ring-inset ring-brand-200' : 'text-slate-600 hover:text-slate-900'}`}>
                                    {o.label}
                                </button>
                            );
                        })}
                    </div>
                </div>
            );
        case 'slider': {
            const shown = value ?? placeholder ?? control.default ?? control.min;
            return (
                <div>
                    {label({ hasValue: has, onReset: reset })}
                    <div className="flex items-center gap-2">
                        <input type="range" className={`flex-1 accent-brand-600 ${has ? '' : 'opacity-50'}`} min={control.min} max={control.max} step={control.step ?? 1}
                            value={shown} onChange={(e) => set(Number(e.target.value))} />
                        <input type="number" className={`${inputCls} !w-16 !px-1.5 text-right`} min={control.min} max={control.max}
                            value={value ?? ''} placeholder={String(placeholder ?? control.default ?? '—')}
                            // Mêmes bornes que le serveur (ContentSanitizer) : le maximum tout de suite, le minimum en quittant le champ.
                            onChange={(e) => set(e.target.value === '' ? null : Math.min(Number(e.target.value), control.max ?? Infinity))}
                            onBlur={() => { if (value != null && control.min != null && value < control.min) set(control.min); }} />
                    </div>
                </div>
            );
        }
        case 'color':
            return <ColorControl label={label({ hasValue: has, onReset: reset })} value={value} placeholder={placeholder} onChange={set} />;
        case 'toggle':
            return (
                <label className="flex cursor-pointer items-center justify-between gap-3 py-0.5">
                    <span className="flex items-center gap-1.5 text-xs font-medium text-slate-600">{control.label}{extra}</span>
                    <button role="switch" aria-checked={!!value} onClick={() => set(!value)}
                        className={`relative h-5 w-9 shrink-0 rounded-full transition ${value ? 'bg-brand-600' : 'bg-slate-300'}`}>
                        <span className={`absolute top-0.5 h-4 w-4 rounded-full bg-white shadow transition ${value ? 'left-4.5' : 'left-0.5'}`} />
                    </button>
                </label>
            );
        case 'image':
        case 'video':
            return <ImageControl control={control} value={value} onChange={onChange} urls={urls} />;
        case 'richtext':
            return <RichText label={control.label} value={value ?? ''} onChange={set} />;
        case 'links':
            return <LinksControl label={control.label} value={Array.isArray(value) ? value : []} onChange={set} />;
        case 'dim':
            return <DimControl label={label({ hasValue: has, onReset: reset })} control={control} value={value} placeholder={placeholder} onChange={set} />;
        case 'spacing':
            return <SpacingControl label={label({ hasValue: has, onReset: reset })} control={control} value={value} placeholder={placeholder} onChange={set} />;
        case 'shadow':
            return <ShadowControl label={label({ hasValue: has, onReset: reset })} value={value} onChange={set} />;
        case 'font':
            return <FontControl label={label({ hasValue: has, onReset: reset })} value={value} placeholder={placeholder} onChange={set} />;
        case 'icon':
            return <IconControl label={label({ hasValue: has, onReset: reset })} value={value} onChange={set} brands={control.brands} />;
        case 'repeater':
            return <RepeaterControl control={control} value={Array.isArray(value) ? value : []} onChange={set} urls={urls} />;
        case 'datetime':
            return (
                <div>
                    {label({ hasValue: has, onReset: reset })}
                    <input className={inputCls} type="datetime-local" value={value ?? ''} onChange={(e) => set(e.target.value || null)} />
                </div>
            );
        case 'number':
            return (
                <div>
                    {label({ hasValue: has, onReset: reset })}
                    <input className={inputCls} type="number" value={value ?? ''} placeholder={placeholder ?? ''} onChange={(e) => set(e.target.value === '' ? null : Number(e.target.value))} />
                </div>
            );
        default:
            return null;
    }
}

const hex6 = (v) => (/^#[0-9a-f]{6}$/i.test(v ?? '') ? v : /^#[0-9a-f]{3}$/i.test(v ?? '') ? `#${v.slice(1).split('').map((c) => c + c).join('')}` : '#000000');

/** Couleur : sélecteur, saisie libre (#hex, rgba…) et couleurs globales du site. */
function ColorControl({ label, value, placeholder, onChange }) {
    const colors = useStore((s) => s.design?.colors ?? []);
    const globalOf = (v) => (typeof v === 'string' && v.startsWith('global:') ? colors.find((c) => `global:${c.id}` === v) : null);
    const current = globalOf(value);
    const inherited = globalOf(placeholder);
    return (
        <div>
            {label}
            <div className="flex items-center gap-2">
                <input type="color" className="h-8 w-10 shrink-0 cursor-pointer rounded border border-slate-300 bg-white p-0.5" value={hex6(current ? current.value : value ?? (inherited ? inherited.value : placeholder))} onChange={(e) => onChange(e.target.value)} />
                {current ? (
                    <button className={`${inputCls} flex items-center justify-between text-left`} onClick={() => onChange(current.value)} title={t('Détacher de la couleur globale')}>
                        <span>🌐 {current.name}</span><span className="text-[11px] text-slate-400">{t('Détacher')}</span>
                    </button>
                ) : (
                    <input className={inputCls} value={value ?? ''} placeholder={inherited ? `🌐 ${inherited.name}` : placeholder ?? t('Par défaut')} onChange={(e) => onChange(e.target.value)} />
                )}
            </div>
            {colors.length > 0 && (
                <div className="mt-1.5 flex flex-wrap gap-1" aria-label={t('Couleurs du site')}>
                    {colors.map((c) => (
                        <button key={c.id} title={c.name} onClick={() => onChange(`global:${c.id}`)}
                            className={`h-5 w-5 rounded-full border border-slate-300 ${value === `global:${c.id}` ? 'ring-2 ring-brand-500 ring-offset-1' : ''}`} style={{ background: c.value }} />
                    ))}
                </div>
            )}
        </div>
    );
}

const parseDim = (v) => {
    if (v === 'auto') return { n: '', u: 'auto' };
    const m = String(v ?? '').match(/^(-?\d*\.?\d+)([a-z%]*)$/);
    return m ? { n: m[1], u: m[2] } : { n: '', u: null };
};

/** Dimension : nombre + unité (« 50% », « 320px », « 1.5 », « auto »). */
function DimControl({ label, control, value, placeholder, onChange }) {
    const units = control.units ?? ['px', '%', 'em', 'rem', 'vw', 'vh'];
    const cur = parseDim(value);
    const ph = parseDim(placeholder);
    const unit = cur.u ?? ph.u ?? units[0];
    const emit = (n, u) => onChange(u === 'auto' ? 'auto' : n === '' ? null : `${n}${u}`);
    return (
        <div>
            {label}
            <div className="flex gap-1.5">
                <input type="number" className={inputCls} disabled={unit === 'auto'} value={cur.n} placeholder={ph.n || '—'} step="any" onChange={(e) => emit(e.target.value, unit)} />
                <select className={`${inputCls} !w-20`} value={unit} onChange={(e) => emit(cur.n || ph.n, e.target.value)}>
                    {units.map((u) => <option key={u} value={u}>{u === '' ? t('sans unité') : u}</option>)}
                </select>
            </div>
        </div>
    );
}

/** Espacement ou arrondi des coins : quatre valeurs, liées ou non, et une unité. */
function SpacingControl({ label, control, value, placeholder, onChange }) {
    const [linked, setLinked] = useState(() => !value || ['t', 'r', 'b', 'l'].every((k) => value[k] === value.t));
    const v = value ?? {};
    const ph = placeholder ?? {};
    const units = control.units ?? ['px'];
    const names = control.corners
        ? [['t', t('Haut gauche')], ['r', t('Haut droit')], ['b', t('Bas droit')], ['l', t('Bas gauche')]]
        : [['t', t('Haut')], ['r', t('Droite')], ['b', t('Bas')], ['l', t('Gauche')]];
    const emit = (next) => {
        const clean = Object.fromEntries(Object.entries(next).filter(([k, x]) => k === 'u' || !empty(x)));
        onChange(Object.keys(clean).some((k) => k !== 'u') ? clean : null);
    };
    const setSide = (k, raw) => {
        const n = raw === '' ? null : Math.max(control.allowNegative ? -2000 : 0, Number(raw));
        emit(linked ? { t: n, r: n, b: n, l: n, u: v.u } : { ...v, [k]: n });
    };
    return (
        <div>
            {label}
            <div className="flex items-end gap-1">
                {names.map(([k, name]) => (
                    <label key={k} className="flex-1 text-center">
                        <input type="number" className={`${inputCls} !px-1 text-center`} value={v[k] ?? ''} placeholder={empty(ph[k]) ? '' : String(ph[k])} onChange={(e) => setSide(k, e.target.value)} />
                        <span className="mt-0.5 block text-[10px] text-slate-400">{name}</span>
                    </label>
                ))}
                <div className="mb-4 flex flex-col gap-1">
                    <button title={linked ? t('Valeurs liées') : t('Valeurs séparées')} onClick={() => setLinked(!linked)}
                        className={`rounded px-1.5 py-1 text-xs ${linked ? 'bg-brand-50 text-brand-700' : 'text-slate-400 hover:bg-slate-100'}`}>{linked ? '🔗' : '⛓️‍💥'}</button>
                </div>
            </div>
            {units.length > 1 && (
                <div className="mt-1 flex gap-1">
                    {units.map((u) => (
                        <button key={u} onClick={() => emit({ ...v, u: u === 'px' ? undefined : u })}
                            className={`rounded px-1.5 py-0.5 text-[11px] ${(v.u ?? 'px') === u ? 'bg-slate-800 text-white' : 'text-slate-500 hover:bg-slate-100'}`}>{u}</button>
                    ))}
                </div>
            )}
        </div>
    );
}

const SHADOWS = {
    [t('Légère')]: { x: 0, y: 1, blur: 3, spread: 0, color: 'rgba(15,23,42,0.12)' },
    [t('Moyenne')]: { x: 0, y: 6, blur: 18, spread: -4, color: 'rgba(15,23,42,0.18)' },
    [t('Forte')]: { x: 0, y: 18, blur: 40, spread: -8, color: 'rgba(15,23,42,0.28)' },
};

/** Ombre portée : modèles prêts à l'emploi ou réglage fin. */
function ShadowControl({ label, value, onChange }) {
    const v = value ?? null;
    const set = (patch) => onChange({ ...(v ?? SHADOWS[t('Moyenne')]), ...patch });
    const num = (k, name) => (
        <label className="flex-1 text-center">
            <input type="number" className={`${inputCls} !px-1 text-center`} value={v?.[k] ?? 0} onChange={(e) => set({ [k]: Number(e.target.value) })} />
            <span className="mt-0.5 block text-[10px] text-slate-400">{name}</span>
        </label>
    );
    return (
        <div>
            {label}
            <div className="flex flex-wrap gap-1">
                {Object.entries(SHADOWS).map(([name, preset]) => (
                    <button key={name} onClick={() => onChange(preset)} className="rounded border border-slate-200 px-2 py-1 text-xs text-slate-600 hover:border-brand-400">{name}</button>
                ))}
            </div>
            {v && (
                <div className="mt-2 space-y-2 rounded-md border border-slate-200 p-2">
                    <div className="flex gap-1">{num('x', 'X')}{num('y', 'Y')}{num('blur', t('Flou'))}{num('spread', t('Étendue'))}</div>
                    <input className={inputCls} value={v.color ?? ''} onChange={(e) => set({ color: e.target.value })} placeholder="rgba(0,0,0,0.2)" />
                    <label className="flex items-center gap-2 text-xs text-slate-600"><input type="checkbox" checked={!!v.inset} onChange={(e) => set({ inset: e.target.checked || undefined })} /> {t('Ombre intérieure')}</label>
                </div>
            )}
        </div>
    );
}

const GROUP_LABELS = {
    base: t('Essentielles'), contact: t('Contact'), people: t('Personnes'), places: t('Lieux'), business: t('Commerce'),
    tech: t('Technologie'), education: t('Éducation'), health: t('Santé'), art: t('Art et décoration'), nature: t('Nature'), life: t('Vie pratique'),
};

/** Choix d'une icône : recherche, catégories, logos des réseaux sociaux. */
function IconControl({ label, value, onChange, brands = true }) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [group, setGroup] = useState('base');
    const q = query.trim().toLowerCase();
    let refs;
    if (q) {
        refs = [...Object.keys(ICONS.lucide).filter((n) => n.includes(q)).map((n) => `lucide:${n}`),
            ...(brands ? Object.keys(ICONS.brands).filter((n) => n.includes(q) || ICONS.brands[n].title.toLowerCase().includes(q)).map((n) => `brand:${n}`) : [])];
    } else if (group === 'brands') {
        refs = Object.keys(ICONS.brands).map((n) => `brand:${n}`);
    } else {
        refs = (ICONS.groups[group] ?? []).map((n) => `lucide:${n}`);
    }
    const tab = (id, name) => (
        <button key={id} onClick={() => { setGroup(id); setQuery(''); }} className={`rounded px-1.5 py-0.5 text-[11px] ${group === id && !q ? 'bg-slate-800 text-white' : 'text-slate-500 hover:bg-slate-100'}`}>{name}</button>
    );
    return (
        <div>
            {label}
            <button onClick={() => setOpen(!open)} className={`${inputCls} flex items-center gap-2 text-left`}>
                {value ? <span className="h-5 w-5 text-slate-700 [&_svg]:h-5 [&_svg]:w-5"><Icon value={value} /></span> : null}
                <span className="truncate">{value ? (value.startsWith('brand:') ? brandTitle(value.slice(6)) : value.slice(7)) : t('Choisir une icône')}</span>
            </button>
            {open && (
                <div className="mt-1.5 rounded-md border border-slate-200 p-2">
                    <input className={inputCls} autoFocus placeholder={t('Rechercher (en anglais : heart, phone…)')} value={query} onChange={(e) => setQuery(e.target.value)} />
                    <div className="mt-1.5 flex flex-wrap gap-1">
                        {Object.entries(GROUP_LABELS).map(([id, name]) => tab(id, name))}
                        {brands && tab('brands', t('Réseaux sociaux'))}
                    </div>
                    <div className="mt-2 grid max-h-56 grid-cols-7 gap-1 overflow-y-auto">
                        {refs.slice(0, 280).map((ref) => (
                            <button key={ref} title={ref.replace(/^(lucide|brand):/, '')} onClick={() => { onChange(ref); setOpen(false); }}
                                className={`grid aspect-square place-items-center rounded text-slate-700 hover:bg-brand-50 hover:text-brand-700 [&_svg]:h-5 [&_svg]:w-5 ${value === ref ? 'bg-brand-100 text-brand-700' : ''}`}>
                                <Icon value={ref} />
                            </button>
                        ))}
                        {refs.length === 0 && <p className="col-span-7 p-2 text-xs text-slate-500">{t('Aucune icône trouvée.')}</p>}
                    </div>
                </div>
            )}
        </div>
    );
}

/**
 * Liste d'éléments (questions d'une FAQ, images d'une galerie, avantages d'une offre…).
 * control.fields décrit les champs d'un élément, avec les mêmes types que les autres contrôles.
 */
function RepeaterControl({ control, value, onChange, urls }) {
    const [openIndex, setOpenIndex] = useState(value.length === 1 ? 0 : null);
    // Plusieurs champs peuvent changer d'un coup (image : adresse, identifiant, texte alternatif) :
    // on part toujours de la dernière valeur envoyée, pas de celle du dernier affichage.
    const latest = useRef(value);
    latest.current = value;
    const update = (i, key, v) => {
        const next = latest.current.map((item, j) => {
            if (j !== i) return item;
            const changed = { ...item, [key]: v };
            if (v === null || v === '' || v === undefined) delete changed[key];
            return changed;
        });
        latest.current = next;
        onChange(next);
    };
    const move = (i, d) => {
        const next = [...value];
        [next[i], next[i + d]] = [next[i + d], next[i]];
        onChange(next);
        setOpenIndex(i + d);
    };
    const titleOf = (item, i) => {
        const first = control.fields.find((f) => ['text', 'textarea'].includes(f.type));
        const text = first ? String(item[first.key] ?? '').trim() : '';
        return text ? text.slice(0, 48) : `${control.itemLabel ?? t('Élément')} ${i + 1}`;
    };
    const small = 'h-6 w-6 text-slate-400 hover:bg-slate-100 hover:text-slate-700';
    const full = control.max && value.length >= control.max;
    return (
        <div>
            <Label>{control.label}</Label>
            <div className="space-y-1.5">
                {value.map((item, i) => (
                    <div key={i} className="rounded-md border border-slate-200">
                        <div className="flex items-center gap-1 px-2 py-1.5">
                            <button className="min-w-0 flex-1 truncate text-left text-xs font-medium text-slate-700" onClick={() => setOpenIndex(openIndex === i ? null : i)}>
                                <UiIcon name={openIndex === i ? 'chevron-down' : 'chevron-right'} className="mr-1 inline h-3 w-3" strokeWidth={2.2} />{titleOf(item, i)}
                            </button>
                            <IconButton icon="chevron-up" label={t('Monter')} className={small} iconClass="h-3.5 w-3.5" disabled={i === 0} onClick={() => move(i, -1)} />
                            <IconButton icon="chevron-down" label={t('Descendre')} className={small} iconClass="h-3.5 w-3.5" disabled={i === value.length - 1} onClick={() => move(i, 1)} />
                            <IconButton icon="copy-plus" label={t('Dupliquer')} className={small} iconClass="h-3.5 w-3.5" disabled={full} onClick={() => onChange([...value.slice(0, i + 1), structuredClone(item), ...value.slice(i + 1)])} />
                            <IconButton icon="trash-2" label={t('Retirer')} className={`${small} hover:!text-red-600`} iconClass="h-3.5 w-3.5" onClick={() => { onChange(value.filter((_, j) => j !== i)); setOpenIndex(null); }} />
                        </div>
                        {openIndex === i && (
                            <div className="space-y-3 border-t border-slate-100 p-2">
                                {control.fields.filter((f) => !f.when || f.when(item)).map((f) => (
                                    <Control key={f.key} control={f} value={item[f.key]} urls={urls} onChange={(key, v) => update(i, key, v)} />
                                ))}
                            </div>
                        )}
                    </div>
                ))}
            </div>
            <button className="mt-2 text-xs font-medium text-brand-600 hover:text-brand-500 disabled:opacity-40" disabled={full}
                onClick={() => { onChange([...value, structuredClone(control.newItem ?? {})]); setOpenIndex(value.length); }}>
                {control.addLabel ?? t('+ Ajouter un élément')}
            </button>
        </div>
    );
}

/** Police : celles du site (texte, titres) ou une police précise. */
function FontControl({ label, value, placeholder, onChange }) {
    const fonts = useStore((s) => s.design?.fonts ?? []);
    return (
        <div>
            {label}
            <select className={inputCls} value={value ?? ''} onChange={(e) => onChange(e.target.value)}>
                <option value="">{placeholder ? `${t('Par défaut')} (${placeholder})` : t('Par défaut')}</option>
                <option value="global:body">{t('Police du texte (site)')}</option>
                <option value="global:heading">{t('Police des titres (site)')}</option>
                {fonts.map((f) => <option key={f} value={f}>{f}</option>)}
            </select>
        </div>
    );
}

/** Liste de liens {label, url} : ajouter, modifier, monter, retirer. */
function LinksControl({ label, value, onChange }) {
    const update = (i, patch) => onChange(value.map((item, j) => (j === i ? { ...item, ...patch } : item)));
    const move = (i, d) => {
        const next = [...value];
        [next[i], next[i + d]] = [next[i + d], next[i]];
        onChange(next);
    };
    const small = 'rounded px-1.5 text-xs text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:opacity-30';
    return (
        <div>
            <Label>{label}</Label>
            <div className="space-y-2">
                {value.map((item, i) => (
                    <div key={i} className="rounded-md border border-slate-200 p-2">
                        <input className={inputCls} value={item.label ?? ''} placeholder={t('Libellé')} onChange={(e) => update(i, { label: e.target.value })} />
                        <input className={`${inputCls} mt-1.5`} value={item.url ?? ''} placeholder={t('https://… ou /page ou #ancre')} onChange={(e) => update(i, { url: e.target.value })} />
                        <div className="mt-1 flex justify-end gap-1">
                            <button className={small} disabled={i === 0} onClick={() => move(i, -1)} title={t('Monter')}>↑</button>
                            <button className={small} disabled={i === value.length - 1} onClick={() => move(i, 1)} title={t('Descendre')}>↓</button>
                            <button className={small} onClick={() => onChange(value.filter((_, j) => j !== i))} title={t('Retirer')}>✕</button>
                        </div>
                    </div>
                ))}
            </div>
            <button className="mt-2 text-xs font-medium text-brand-600 hover:text-brand-500" onClick={() => onChange([...value, { label: '', url: '' }])}>{t('+ Ajouter un lien')}</button>
        </div>
    );
}

function ImageControl({ control, value, onChange, urls }) {
    const [open, setOpen] = useState(false);
    // Numéro du média : « media_id » pour l'image principale, ou une clé propre (« before_media_id »…).
    const mediaKey = control.mediaKey ?? (control.key === 'url' ? 'media_id' : null);
    const setMedia = (id) => { if (mediaKey) onChange(mediaKey, id); };
    const video = control.type === 'video';
    return (
        <div>
            <Label hasValue={!!value} onReset={() => { onChange(control.key, null); setMedia(null); }}>{control.label}</Label>
            <button onClick={() => setOpen(true)} className="grid aspect-video w-full place-items-center overflow-hidden rounded-md border border-dashed border-slate-300 bg-slate-50 text-xs text-slate-500 hover:border-brand-400">
                {value ? (video ? <video src={value} muted autoPlay loop playsInline className="h-full w-full object-cover" /> : <img src={value} alt="" className="h-full w-full object-cover" />)
                    : video ? t('Choisir une vidéo') : t('Choisir une image')}
            </button>
            <input className={`${inputCls} mt-1.5`} value={value ?? ''} placeholder={video ? t('ou collez l’adresse d’une vidéo MP4') : t('ou collez l’adresse d’une image')} onChange={(e) => { onChange(control.key, e.target.value); setMedia(null); }} />
            {open && (
                <MediaPicker urls={urls} kind={video ? 'video' : 'image'} onClose={() => setOpen(false)} onPick={(m) => {
                    onChange(control.key, m.url);
                    setMedia(m.id);
                    if (control.key === 'url' && m.alt) onChange('alt', m.alt);
                    setOpen(false);
                }} />
            )}
        </div>
    );
}

/** Champs où les balises dynamiques n'ont pas de sens. */
const NO_TAGS = ['css_id', 'css_classes', 'name', 'form_name', 'email_to', 'custom_css'];

/** Bouton « { } » : choisit une balise dynamique ({{site.name}}…) et l'insère au curseur. */
function TagPicker({ onPick }) {
    const [open, setOpen] = useState(false);
    const box = useRef(null);
    useEffect(() => {
        if (!open) return undefined;
        const close = (e) => { if (!box.current?.contains(e.target)) setOpen(false); };
        document.addEventListener('mousedown', close);
        return () => document.removeEventListener('mousedown', close);
    }, [open]);
    return (
        <span ref={box} className="relative inline-flex">
            <IconButton icon="braces" label={t('Insérer une balise dynamique')} pressed={open} onMouseDown={(e) => e.preventDefault()} onClick={() => setOpen(!open)}
                className={`h-5 w-5 ${open ? 'bg-brand-100 text-brand-700' : 'text-slate-400 hover:text-brand-600'}`} iconClass="h-3.5 w-3.5" />
            {open && (
                <span className="absolute left-0 top-6 z-30 max-h-72 w-60 overflow-y-auto rounded-lg border border-slate-200 bg-white p-1.5 text-left shadow-xl">
                    {TAGS.map((g) => (
                        <span key={g.group} className="block">
                            <span className="block px-2 pb-0.5 pt-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{g.group}</span>
                            {g.tags.map(([key, name]) => (
                                <button key={key} type="button" onMouseDown={(e) => e.preventDefault()} onClick={() => { onPick(`{{${key}}}`); setOpen(false); }}
                                    className="flex w-full items-center justify-between gap-2 rounded px-2 py-1 text-xs text-slate-700 hover:bg-brand-50 hover:text-brand-800">
                                    <span>{name}</span><code className="text-[10px] text-slate-400">{key}</code>
                                </button>
                            ))}
                        </span>
                    ))}
                </span>
            )}
        </span>
    );
}

function RichText({ label, value, onChange }) {
    const ref = useRef(null);
    const last = useRef(value);

    useEffect(() => {
        // Ne réécrit le DOM que si la valeur vient d'ailleurs (annuler, autre widget…).
        if (ref.current && value !== last.current) {
            ref.current.innerHTML = cleanHtml(value);
            last.current = value;
        }
    }, [value]);

    useEffect(() => {
        if (ref.current) ref.current.innerHTML = cleanHtml(value);
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

    const btn = 'h-7 w-7 text-slate-600 hover:bg-white hover:text-slate-900';
    const tool = (icon, title, fn) => <IconButton icon={icon} label={title} className={btn} iconClass="h-3.5 w-3.5" onMouseDown={(e) => e.preventDefault()} onClick={fn} />;
    return (
        <div>
            <Label extra={<TagPicker onPick={(token) => cmd('insertText', token)} />}>{label}</Label>
            <div className="overflow-hidden rounded-md border border-slate-300 focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20">
                <div className="flex flex-wrap gap-0.5 border-b border-slate-200 bg-slate-50 p-1">
                    {tool('bold', t('Gras'), () => cmd('bold'))}
                    {tool('italic', t('Italique'), () => cmd('italic'))}
                    {tool('underline', t('Souligné'), () => cmd('underline'))}
                    {tool('list', t('Liste à puces'), () => cmd('insertUnorderedList'))}
                    {tool('list-ordered', t('Liste numérotée'), () => cmd('insertOrderedList'))}
                    {tool('link', t('Lien'), link)}
                    {tool('remove-formatting', t('Effacer la mise en forme'), () => cmd('removeFormat'))}
                </div>
                <div ref={ref} contentEditable suppressContentEditableWarning onInput={emit}
                    // Entrée crée un paragraphe (<p>) et non une <div>.
                    onFocus={() => document.execCommand('defaultParagraphSeparator', false, 'p')}
                    // Collage : on ne garde que les balises acceptées à l'enregistrement.
                    onPaste={(e) => {
                        const pasted = e.clipboardData.getData('text/html');
                        if (!pasted) return;
                        e.preventDefault();
                        document.execCommand('insertHTML', false, cleanHtml(pasted));
                        emit();
                    }}
                    className="prose-sm max-h-80 min-h-28 overflow-y-auto bg-white px-3 py-2 text-sm outline-none [&_a]:text-brand-600 [&_a]:underline [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:mb-2 [&_ul]:list-disc [&_ul]:pl-5" />
            </div>
        </div>
    );
}
