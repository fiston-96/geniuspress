// Aperçu et CSS des widgets de widgets/library.js.
// Le CSS est le miroir exact des méthodes render() de app/Builder/Widgets/*.php.
import DOMPurify from 'dompurify';
import { Icon, brandHex, brandTitle } from '../icons';
import { t } from '../i18n';

const JUSTIFY = { left: 'flex-start', center: 'center', right: 'flex-end' };
const ratio = (r, fallback = 'auto') => ((r ?? fallback) === 'auto' ? null : (r ?? fallback).replace(':', ' / '));
const html = (s) => ({ __html: DOMPurify.sanitize(s ?? '') });
const Ph = ({ children }) => <div className="gpe-empty" style={{ margin: 0 }}>{children}</div>;

/** CSS d'une icône (IconWidget::iconCss). */
function iconCss(o, h, scope, prefix, defaultSize) {
    const sel = `${h.sel}${scope} .gp-icon-wrap`;
    const shape = o[`${prefix}shape`] ?? 'none';
    const hasBox = shape !== 'none';
    h.rule(sel, {
        color: o[`${prefix}color`] ?? (hasBox ? '#ffffff' : 'var(--gp-primary)'),
        background: hasBox ? (o[`${prefix}bg_color`] ?? 'var(--gp-primary)') : (o[`${prefix}bg_color`] ?? null),
        padding: hasBox ? `${o[`${prefix}box`] ?? 16}px` : null,
    });
    h.rule(`${sel}:hover`, { color: o[`${prefix}color_hover`], background: o[`${prefix}bg_color_hover`] });
    const size = o[`${prefix}size`] ?? defaultSize;
    h.rule(`${sel} .gp-ico`, { width: `${size}px`, height: `${size}px` });
    for (const device of ['tablet', 'mobile']) {
        const v = o[`${prefix}size_${device}`];
        if (v !== undefined && v !== null) h[device](`${sel} .gp-ico`, { width: `${v}px`, height: `${v}px` });
    }
}

/** Alignement sur tablette et mobile seulement (l'ordinateur a sa propre valeur par défaut). */
function smallAlign(o, h, selector, fn) {
    if (o.align_tablet) h.tablet(selector, fn(o.align_tablet));
    if (o.align_mobile) h.mobile(selector, fn(o.align_mobile));
}

const formatNumber = (n, sep) => Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, { space: ' ', comma: ',', dot: '.', none: '' }[sep ?? 'space'] ?? ' ');

export const LIBRARY_IMPL = {
    icon: {
        preview: (o) => {
            if (!o.icon) return <Ph>{t('Choisissez une icône')}</Ph>;
            return <span className={`gp-icon-wrap gp-icon--${o.shape ?? 'none'}`}><Icon value={o.icon} /></span>;
        },
        css: (o, h) => {
            if (!o.icon) return;
            iconCss(o, h, '', '', 48);
            h.rule(h.sel, { 'text-align': o.align ?? 'center' });
            smallAlign(o, h, h.sel, (v) => ({ 'text-align': v }));
        },
    },
    'icon-box': {
        preview: (o) => {
            const Tag = o.title_tag ?? 'h3';
            return (
                <div className={`gp-ibox gp-ibox--${o.position ?? 'top'}`}>
                    {o.icon && <span className={`gp-ibox__icon gp-icon-wrap gp-icon--${o.icon_shape ?? 'none'}`}><Icon value={o.icon} /></span>}
                    <div className="gp-ibox__body">
                        {o.title && <Tag className="gp-ibox__title">{o.title}</Tag>}
                        {o.text && <div className="gp-ibox__text" dangerouslySetInnerHTML={html(o.text)} />}
                        {o.link && <a className="gp-ibox__link" href={o.link} draggable={false}>{o.link_text ?? t('En savoir plus')} <span aria-hidden="true">→</span></a>}
                    </div>
                </div>
            );
        },
        css: (o, h) => {
            iconCss(o, h, '', 'icon_', 40);
            h.rule(h.sel, { 'text-align': o.align ?? ((o.position ?? 'top') === 'top' ? 'center' : null) });
            smallAlign(o, h, h.sel, (v) => ({ 'text-align': v }));
            h.rule(`${h.sel} .gp-ibox__title`, { color: o.title_color });
            h.responsive('title_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-ibox__title');
            h.rule(`${h.sel} .gp-ibox__text`, { color: o.text_color });
        },
    },
    'image-box': {
        preview: (o) => {
            const Tag = o.title_tag ?? 'h3';
            return (
                <div className="gp-imgbox">
                    {o.url ? <img className="gp-imgbox__img" src={o.url} alt={o.alt ?? ''} draggable={false} /> : <Ph>{o.alt ? t('Image à ajouter : :alt', { alt: o.alt }) : t('Choisissez une image dans le panneau')}</Ph>}
                    {o.title && <Tag className="gp-imgbox__title">{o.title}</Tag>}
                    {o.text && <div className="gp-imgbox__text" dangerouslySetInnerHTML={html(o.text)} />}
                    {o.link && o.link_text && <a className="gp-imgbox__link" href={o.link} draggable={false}>{o.link_text} <span aria-hidden="true">→</span></a>}
                </div>
            );
        },
        css: (o, h) => {
            h.responsive('align', (v) => ({ 'text-align': v }));
            h.rule(`${h.sel} .gp-imgbox__img`, { 'aspect-ratio': ratio(o.ratio), 'border-radius': o.image_radius !== undefined ? `${o.image_radius}px` : null });
            h.rule(`${h.sel} .gp-imgbox__title`, { color: o.title_color });
            h.responsive('title_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-imgbox__title');
            h.rule(`${h.sel} .gp-imgbox__text`, { color: o.text_color });
        },
    },
    'icon-list': {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des lignes dans le panneau')}</Ph>;
            return (
                <ul className={`gp-ilist gp-ilist--${o.layout ?? 'vertical'}`}>
                    {items.map((item, i) => (
                        <li key={i}><span className="gp-ilist__item"><Icon value={item.icon ?? 'lucide:check'} /><span>{item.text}</span></span></li>
                    ))}
                </ul>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            h.rule(`${h.sel} .gp-ilist`, { gap: o.gap !== undefined ? `${o.gap}px` : null });
            h.responsive('align', (v) => ({ 'justify-content': JUSTIFY[v], 'text-align': v }), ' .gp-ilist');
            h.rule(`${h.sel} .gp-ico`, { color: o.icon_color, width: o.icon_size !== undefined ? `${o.icon_size}px` : null, height: o.icon_size !== undefined ? `${o.icon_size}px` : null });
            h.rule(`${h.sel} .gp-ilist__item`, { color: o.text_color });
            h.rule(`${h.sel} a.gp-ilist__item:hover`, { color: o.text_color_hover });
        },
    },
    testimonial: {
        preview: (o) => {
            const rating = Number(o.rating ?? 0);
            return (
                <figure className={`gp-testi gp-testi--${o.style ?? 'card'}`}>
                    {rating > 0 && <div className="gp-testi__stars">{'★'.repeat(rating)}<span className="gp-testi__off">{'★'.repeat(5 - rating)}</span></div>}
                    <blockquote style={{ whiteSpace: 'pre-line' }}>{o.text}</blockquote>
                    <figcaption>
                        {o.url && <img src={o.url} alt="" draggable={false} />}
                        <span><span className="gp-testi__name">{o.name}</span>{o.role && <span className="gp-testi__role">{o.role}</span>}</span>
                    </figcaption>
                </figure>
            );
        },
        css: (o, h) => {
            h.responsive('align', (v) => ({ 'text-align': v }), ' .gp-testi');
            h.responsive('text_size', (v) => ({ 'font-size': `${v}px` }), ' blockquote');
            h.rule(`${h.sel} .gp-testi`, { '--gp-accent-local': o.accent });
        },
    },
    counter: {
        preview: (o) => (
            <div className="gp-counter__box">
                <span className="gp-counter__num">{o.prefix}<span>{formatNumber(Number(o.number ?? 0), o.separator)}</span>{o.suffix}</span>
                {o.label && <span className="gp-counter__label">{o.label}</span>}
            </div>
        ),
        css: (o, h) => {
            h.rule(h.sel, { 'text-align': o.align ?? 'center' });
            smallAlign(o, h, h.sel, (v) => ({ 'text-align': v }));
            h.rule(`${h.sel} .gp-counter__num`, { color: o.number_color });
            h.responsive('number_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-counter__num');
            h.rule(`${h.sel} .gp-counter__label`, { color: o.label_color });
        },
    },
    progress: {
        preview: (o) => {
            const show = o.show_percent ?? true;
            return (
                <div className="gp-progress">
                    {(o.label || show) && <div className="gp-progress__head"><span>{o.label}</span>{show && <span>{o.percent ?? 0}&nbsp;%</span>}</div>}
                    <div className="gp-progress__track"><div className="gp-progress__bar" /></div>
                </div>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-progress__track`, { background: o.track_color, height: o.height !== undefined ? `${o.height}px` : null, 'border-radius': o.radius !== undefined ? `${o.radius}px` : null });
            h.rule(`${h.sel} .gp-progress__bar`, { background: o.bar_color, width: `${parseInt(o.percent ?? 0, 10)}%` });
            h.rule(`${h.sel} .gp-progress__head`, { color: o.label_color });
        },
    },
    accordion: {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des questions dans le panneau')}</Ph>;
            return (
                <div className={`gp-acc gp-acc--${o.style ?? 'boxed'}`}>
                    {items.map((item, i) => (
                        <details key={i} open={i === 0 && !!o.open_first}>
                            <summary onClick={(e) => e.preventDefault()}>{item.title}</summary>
                            <div className="gp-acc__body" dangerouslySetInnerHTML={html(item.html)} />
                        </details>
                    ))}
                </div>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            h.rule(`${h.sel} summary`, { color: o.title_color });
            h.rule(`${h.sel} .gp-acc`, { '--gp-acc-accent': o.accent });
        },
    },
    tabs: {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des onglets dans le panneau')}</Ph>;
            return (
                <div className={`gp-tabs gp-tabs--${o.style ?? 'line'} is-ready`}>
                    <div className="gp-tabs__list">{items.map((item, i) => <span key={i} className="gp-tabs__tab" aria-selected={i === 0 ? 'true' : 'false'}>{item.title}</span>)}</div>
                    <section className="gp-tabs__panel"><div className="gp-tabs__body" dangerouslySetInnerHTML={html(items[0].html)} /></section>
                </div>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            h.rule(`${h.sel} .gp-tabs`, { '--gp-tabs-accent': o.accent });
        },
    },
    gallery: {
        preview: (o) => {
            const images = (o.images ?? []).filter((img) => img.url);
            if (!images.length) return <Ph>{t('Ajoutez des images dans le panneau')}</Ph>;
            return (
                <div className="gp-gallery__grid">
                    {images.map((img, i) => (
                        <figure key={i}><img src={img.url} alt={img.alt ?? ''} draggable={false} />{o.captions && img.caption && <figcaption>{img.caption}</figcaption>}</figure>
                    ))}
                </div>
            );
        },
        css: (o, h) => {
            if (!(o.images ?? []).some((img) => img.url)) return;
            h.rule(`${h.sel} .gp-gallery__grid`, { 'grid-template-columns': `repeat(${o.columns ?? 3}, minmax(0, 1fr))`, gap: `${o.gap ?? 12}px` });
            for (const device of ['tablet', 'mobile']) {
                const v = o[`columns_${device}`];
                if (v !== undefined && v !== null) h[device](`${h.sel} .gp-gallery__grid`, { 'grid-template-columns': `repeat(${v}, minmax(0, 1fr))` });
            }
            h.rule(`${h.sel} .gp-gallery__grid img`, { 'aspect-ratio': ratio(o.ratio, '1:1') ?? 'auto', 'border-radius': o.radius !== undefined ? `${o.radius}px` : null });
        },
    },
    pricing: {
        preview: (o) => (
            <div className={`gp-price${o.featured ? ' is-featured' : ''}`}>
                {o.badge && <span className="gp-price__badge">{o.badge}</span>}
                <h3 className="gp-price__title">{o.title}</h3>
                <p className="gp-price__amount"><span className="gp-price__currency">{o.currency}</span><span className="gp-price__value">{o.price}</span>{o.period && <span className="gp-price__period">{o.period}</span>}</p>
                {o.description && <p className="gp-price__desc">{o.description}</p>}
                {(o.features ?? []).length > 0 && (
                    <ul className="gp-price__features">
                        {o.features.map((f, i) => {
                            const included = f.included ?? true;
                            return <li key={i} className={included ? 'is-in' : 'is-out'}><Icon value={included ? 'lucide:check' : 'lucide:x'} /><span>{f.text}</span></li>;
                        })}
                    </ul>
                )}
                {o.button_text && <a className="gp-btn gp-btn-md gp-price__btn" href={o.button_url ?? '#'} draggable={false}>{o.button_text}</a>}
            </div>
        ),
        css: (o, h) => h.rule(`${h.sel} .gp-price`, { '--gp-price-accent': o.accent }),
    },
    'team-member': {
        preview: (o) => (
            <div className={`gp-team gp-team--${o.style ?? 'plain'}`}>
                {o.url ? <img className={`gp-team__photo gp-team__photo--${o.image_shape ?? 'rounded'}`} src={o.url} alt="" draggable={false} /> : <Ph>{t('Photo')}</Ph>}
                <h3 className="gp-team__name">{o.name}</h3>
                {o.role && <p className="gp-team__role">{o.role}</p>}
                {o.bio && <p className="gp-team__bio">{o.bio}</p>}
                {(o.socials ?? []).length > 0 && <ul className="gp-team__socials">{o.socials.filter((l) => l.icon && l.url).map((l, i) => <li key={i}><a href={l.url} draggable={false}><Icon value={l.icon} /></a></li>)}</ul>}
            </div>
        ),
        css: (o, h) => {
            h.rule(`${h.sel} .gp-team`, { 'text-align': o.align ?? 'center' });
            smallAlign(o, h, `${h.sel} .gp-team`, (v) => ({ 'text-align': v }));
        },
    },
    'social-icons': {
        preview: (o) => {
            const items = (o.items ?? []).filter((i) => i.icon && i.url);
            if (!items.length) return <Ph>{t('Ajoutez des réseaux dans le panneau')}</Ph>;
            const mode = o.color_mode ?? 'brand';
            return (
                <ul className={`gp-social gp-social--${o.shape ?? 'circle'} gp-social--${mode}`}>
                    {items.map((item, i) => {
                        const brand = item.icon.startsWith('brand:') ? item.icon.slice(6) : null;
                        return <li key={i}><a href={item.url} title={item.label ?? (brand ? brandTitle(brand) : '')} style={brand && mode === 'brand' ? { '--gp-brand': brandHex(brand) } : null} draggable={false}><Icon value={item.icon} /></a></li>;
                    })}
                </ul>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).some((i) => i.icon && i.url)) return;
            const size = o.size ?? 22;
            const shape = o.shape ?? 'circle';
            const mode = o.color_mode ?? 'brand';
            h.rule(`${h.sel} .gp-social`, { gap: `${o.gap ?? 10}px`, 'justify-content': JUSTIFY[o.align ?? 'left'] });
            smallAlign(o, h, `${h.sel} .gp-social`, (v) => ({ 'justify-content': JUSTIFY[v] }));
            h.rule(`${h.sel} .gp-ico`, { width: `${size}px`, height: `${size}px` });
            h.rule(`${h.sel} .gp-social a`, {
                padding: shape === 'none' ? '0' : `${Math.round(size * 0.45)}px`,
                background: shape === 'none' ? null : (o.bg_color ?? (mode === 'brand' ? null : 'rgba(15,23,42,.06)')),
                color: mode === 'custom' ? o.color : null,
            });
            h.rule(`${h.sel} .gp-social a:hover`, { color: mode === 'custom' ? o.color_hover : null });
        },
    },
    map: {
        preview: (o) => {
            if (!o.address) return <Ph>{t('Indiquez une adresse dans le panneau')}</Ph>;
            return <div className="gp-map"><iframe src={`https://maps.google.com/maps?q=${encodeURIComponent(o.address)}&z=${o.zoom ?? 15}&output=embed`} title={t('Carte')} style={{ pointerEvents: 'none' }} loading="lazy" /></div>;
        },
        css: (o, h) => {
            if (!o.address) return;
            h.rule(`${h.sel} iframe`, { height: `${o.height ?? 400}px`, 'border-radius': o.radius !== undefined ? `${o.radius}px` : null, filter: o.grayscale ? 'grayscale(1)' : null });
            for (const device of ['tablet', 'mobile']) {
                const v = o[`height_${device}`];
                if (v !== undefined && v !== null) h[device](`${h.sel} iframe`, { height: `${v}px` });
            }
        },
    },
    countdown: {
        preview: (o) => {
            if (!o.date) return <Ph>{t('Choisissez la date de l’événement dans le panneau')}</Ph>;
            const left = Math.max(0, Math.floor((Date.parse(o.date) - Date.now()) / 1000));
            const parts = [
                [Math.floor(left / 86400), o.days_label ?? t('jours')], [Math.floor((left % 86400) / 3600), o.hours_label ?? t('heures')],
                [Math.floor((left % 3600) / 60), o.minutes_label ?? t('minutes')], [left % 60, o.seconds_label ?? t('secondes')],
            ];
            return (
                <div className="gp-countdown__row">
                    {parts.map(([v, label], i) => <div key={i} className="gp-countdown__box"><span className="gp-countdown__num">{String(v).padStart(2, '0')}</span><span className="gp-countdown__label">{label}</span></div>)}
                </div>
            );
        },
        css: (o, h) => {
            if (!o.date) return;
            h.rule(`${h.sel} .gp-countdown__row`, { 'justify-content': JUSTIFY[o.align ?? 'center'] });
            h.rule(`${h.sel} .gp-countdown__box`, { background: o.box_color });
            h.rule(`${h.sel} .gp-countdown__num`, { color: o.number_color });
            h.rule(`${h.sel} .gp-countdown__label`, { color: o.label_color });
        },
    },
    'contact-form': {
        preview: (o) => {
            const field = (label, tag = 'input') => (
                <p className="gp-form__field"><label>{label}</label>{tag === 'textarea' ? <textarea rows={4} readOnly tabIndex={-1} /> : <input readOnly tabIndex={-1} />}</p>
            );
            return (
                <form className="gp-form" onSubmit={(e) => e.preventDefault()}>
                    <div className="gp-form__row">{field(o.name_label ?? t('Nom'))}{field(o.email_label ?? t('E-mail'))}</div>
                    {o.show_phone && field(o.phone_label ?? t('Téléphone'))}
                    {o.show_subject && field(o.subject_label ?? t('Sujet'))}
                    {field(o.message_label ?? t('Message'), 'textarea')}
                    <button type="button" className="gp-btn gp-btn-md" tabIndex={-1}>{o.button_text ?? t('Envoyer')}</button>
                </form>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-form .gp-btn`, { background: o.button_color, 'border-color': o.button_color });
            h.rule(`${h.sel} .gp-form input, ${h.sel} .gp-form textarea`, { 'border-radius': o.field_radius !== undefined ? `${o.field_radius}px` : null });
        },
    },
};
