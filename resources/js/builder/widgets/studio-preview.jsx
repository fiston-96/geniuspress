// Aperçus et CSS des widgets de widgets/studio.js : miroir de FormWidget::render()
// et MegaMenuWidget::render() (app/Builder/Widgets).
import { t } from '../i18n';

const lines = (text) => String(text ?? '').split(/\r\n|\r|\n/).map((l) => l.trim()).filter(Boolean);
const ALIGN_SELF = { start: 'flex-start', center: 'center', end: 'flex-end', stretch: 'stretch' };
const JUSTIFY = { left: 'flex-start', center: 'center', right: 'flex-end' };

function Field({ f, i }) {
    const type = f.type ?? 'text';
    const label = String(f.label ?? '').trim() || t('Champ :n', { n: i + 1 });
    const width = ['100', '50', '33'].includes(String(f.width ?? '100')) ? String(f.width ?? '100') : '100';
    const star = f.required ? <> <span aria-hidden="true">*</span></> : null;
    const cls = `gp-form__field gp-form__field--${width}`;
    const options = lines(f.options).slice(0, 50);
    if (type === 'hidden') return null;
    if (type === 'textarea') return <p className={cls}><label>{label}{star}</label><textarea rows={5} readOnly tabIndex={-1} placeholder={f.placeholder} /></p>;
    if (type === 'select') {
        return (
            <p className={cls}><label>{label}{star}</label>
                <select tabIndex={-1} value="" onChange={() => {}}><option value="">{f.placeholder || '—'}</option>{options.map((o) => <option key={o}>{o}</option>)}</select>
            </p>
        );
    }
    if (type === 'radio' || type === 'checkboxes') {
        return (
            <fieldset className={cls}><legend>{label}{star}</legend>
                <div className="gp-form__choices">
                    {options.map((o) => <label key={o} className="gp-form__choice"><input type={type === 'radio' ? 'radio' : 'checkbox'} tabIndex={-1} readOnly checked={false} onChange={() => {}} /> <span>{o}</span></label>)}
                </div>
            </fieldset>
        );
    }
    if (type === 'acceptance') return <p className={cls}><label className="gp-form__choice"><input type="checkbox" tabIndex={-1} readOnly checked={false} onChange={() => {}} /> <span>{label}{star}</span></label></p>;
    return <p className={cls}><label>{label}{star}</label><input type={type} readOnly tabIndex={-1} placeholder={f.placeholder} /></p>;
}

export const STUDIO_IMPL = {
    form: {
        preview: (o) => {
            const fields = o.fields ?? [];
            if (!fields.length) return <p className="gp-form__note">{t('Ajoutez des champs au formulaire.')}</p>;
            return (
                <form className="gp-form gp-form--custom" onSubmit={(e) => e.preventDefault()}>
                    <div className="gp-form__grid">{fields.map((f, i) => <Field key={i} f={f} i={i} />)}</div>
                    <button type="button" className="gp-btn gp-btn-md" tabIndex={-1}>{o.button_text ?? t('Envoyer')}</button>
                </form>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-form .gp-btn`, { background: o.button_color, 'border-color': o.button_color });
            h.rule(`${h.sel} .gp-form .gp-btn`, { 'align-self': ALIGN_SELF[o.button_align ?? 'start'] });
            h.rule(`${h.sel} .gp-form :is(input, textarea, select)`, { 'border-radius': o.field_radius !== undefined ? `${o.field_radius}px` : null });
            h.rule(`${h.sel} .gp-form :is(label, legend)`, { color: o.label_color });
        },
    },
    'mega-menu': {
        preview: (o) => {
            const items = (o.items ?? []).filter((i) => String(i.label ?? '').trim());
            if (!items.length) return null;
            return (
                <nav className={`gp-mega gp-mega--collapse-${o.collapse ?? 'mobile'} gp-mega--${o.open_on ?? 'hover'} gp-mega--w-${o.panel_width ?? 'content'}`}>
                    <label className="gp-mega__burger"><span /></label>
                    <ul className="gp-mega__list">
                        {items.map((item, n) => {
                            const panel = !!item.mega || lines(item.links).some((l) => l.includes('|'));
                            return (
                                <li key={n} className={`gp-mega__item${panel ? ' has-panel' : ''}`}>
                                    <span className="gp-mega__top">{item.label}</span>
                                    {panel && (
                                        <span className="gp-mega__caret" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round"><path d="m6 9 6 6 6-6" /></svg>
                                        </span>
                                    )}
                                </li>
                            );
                        })}
                    </ul>
                </nav>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-mega__list`, { 'justify-content': JUSTIFY[o.align ?? 'right'] });
            h.rule(`${h.sel} .gp-mega__list`, { gap: o.gap !== undefined ? `${o.gap}px` : null });
            h.rule(`${h.sel} .gp-mega__top`, { color: o.color, 'font-size': o.font_size !== undefined ? `${o.font_size}px` : null });
            h.rule(`${h.sel} .gp-mega__item:hover > .gp-mega__top`, { color: o.color_hover });
            h.rule(`${h.sel} .gp-mega__burger`, { color: o.color });
            h.rule(`${h.sel} .gp-mega__panel`, { 'background-color': o.panel_bg });
        },
    },
};
