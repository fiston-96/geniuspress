// Aperçu et CSS des widgets avancés (widgets/pro.js).
// Le CSS est le miroir exact des méthodes render() de app/Builder/Widgets/*Widget.php.
import DOMPurify from 'dompurify';
import { Icon } from '../icons';
import { t } from '../i18n';

const JUSTIFY = { left: 'flex-start', center: 'center', right: 'flex-end' };
const VJUSTIFY = { top: 'flex-start', center: 'center', bottom: 'flex-end' };
const set = (v) => v !== undefined && v !== null && v !== '';
const px = (v) => (set(v) ? `${v}px` : null);
const html = (s) => ({ __html: DOMPurify.sanitize(s ?? '') });
const Ph = ({ children }) => <div className="gpe-empty" style={{ margin: 0 }}>{children}</div>;
const ALERT_ICONS = { info: 'info', success: 'circle-check', warning: 'triangle-alert', danger: 'triangle-alert', note: 'lightbulb' };

/** DataTableWidget::rows */
function tableRows(data) {
    return String(data ?? '').trim().split(/\r\n|\r|\n/).slice(0, 200).filter((l) => l.trim() !== '').map((l) => l.split('|').map((c) => c.trim()));
}

export const PRO_IMPL = {
    'flip-box': {
        preview: (o) => (
            <div className={`gp-flip gp-flip--${o.direction ?? 'horizontal'}`} tabIndex={0}>
                <div className="gp-flip__inner">
                    <div className="gp-flip__face gp-flip__front">
                        {o.icon && <Icon value={o.icon} />}
                        <p className="gp-flip__title">{o.front_title}</p>
                        {o.front_text && <p className="gp-flip__text">{o.front_text}</p>}
                    </div>
                    <div className="gp-flip__face gp-flip__back">
                        <p className="gp-flip__title">{o.back_title}</p>
                        {o.back_text && <p className="gp-flip__text">{o.back_text}</p>}
                        {o.button_text && <a className="gp-btn gp-btn-sm gp-flip__btn">{o.button_text}</a>}
                    </div>
                </div>
            </div>
        ),
        css: (o, h) => {
            h.responsive('height', (v) => ({ height: `${v}px` }), ' .gp-flip');
            h.rule(`${h.sel} .gp-flip__face`, { 'border-radius': px(o.radius) });
            h.rule(`${h.sel} .gp-flip__front`, { background: o.front_bg, color: o.front_color });
            h.rule(`${h.sel} .gp-flip__back`, { background: o.back_bg, color: o.back_color });
        },
    },
    'promo-box': {
        preview: (o) => (
            <div className={`gp-promo${o.zoom ? ' gp-promo--zoom' : ''}`}>
                {o.url && <img className="gp-promo__img" src={o.url} alt="" draggable={false} />}
                <span className="gp-promo__overlay" />
                <div className="gp-promo__body">
                    {o.eyebrow && <p className="gp-promo__eyebrow">{o.eyebrow}</p>}
                    <p className="gp-promo__title">{o.title}</p>
                    {o.text && <p className="gp-promo__text">{o.text}</p>}
                    {o.button_text && <a className="gp-btn gp-promo__btn">{o.button_text}</a>}
                </div>
            </div>
        ),
        css: (o, h) => {
            h.responsive('height', (v) => ({ 'min-height': `${v}px` }), ' .gp-promo');
            h.rule(`${h.sel} .gp-promo`, {
                'border-radius': px(o.radius),
                color: o.color,
                'text-align': o.align,
                '--gp-promo-align': set(o.align) ? JUSTIFY[o.align] : null,
                'justify-content': VJUSTIFY[o.valign ?? 'bottom'],
            });
            h.rule(`${h.sel} .gp-promo__overlay`, { background: o.overlay, opacity: set(o.overlay_opacity) ? o.overlay_opacity / 100 : null });
        },
    },
    'dual-heading': {
        preview: (o) => {
            const Tag = o.tag || 'h2';
            const parts = [];
            if (set(o.before)) parts.push(o.before, ' ');
            parts.push(<span key="hl" className={`gp-dh__hl gp-dh__hl--${o.style ?? 'color'}`}>{o.highlight ?? ''}</span>);
            if (set(o.after)) parts.push(' ', o.after);
            return <Tag className="gp-dh">{parts}</Tag>;
        },
        css: (o, h) => {
            h.rule(`${h.sel} > *`, { color: o.color, 'font-family': o.font_family ? null : 'var(--gp-heading-font)' });
            h.responsive('align', (v) => ({ 'text-align': v }), ' > *');
            h.responsive('font_size', (v) => ({ 'font-size': `${v}px` }), ' > *');
            h.typography(' > *');
            h.rule(`${h.sel} .gp-dh__hl`, { '--gp-hl': o.highlight_color, '--gp-hl-2': o.highlight_color_2 });
        },
    },
    'price-list': {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des éléments dans le panneau')}</Ph>;
            return (
                <ul className={`gp-pricelist${o.leader !== false ? ' gp-pricelist--leader' : ''}`}>
                    {items.map((item, i) => (
                        <li key={i} className="gp-price">
                            {item.url && <img className="gp-price__img" src={item.url} alt="" draggable={false} />}
                            <div className="gp-price__body">
                                <p className="gp-price__head">
                                    <span className="gp-price__title">{item.title}</span>
                                    {item.badge && <span className="gp-price__badge">{item.badge}</span>}
                                    <span className="gp-price__dots" aria-hidden="true" />
                                    <span className="gp-price__price">{item.price}</span>
                                </p>
                                {item.description && <p className="gp-price__desc">{item.description}</p>}
                            </div>
                        </li>
                    ))}
                </ul>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            h.rule(`${h.sel} .gp-pricelist`, { gap: px(o.gap) });
            h.rule(`${h.sel} .gp-price__title`, { color: o.title_color });
            h.rule(`${h.sel} .gp-price__price`, { color: o.price_color });
            h.rule(`${h.sel} .gp-price__desc`, { color: o.text_color });
            if (set(o.image_size)) h.rule(`${h.sel} .gp-price__img`, { width: `${o.image_size}px`, height: `${o.image_size}px` });
        },
    },
    'business-hours': {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des lignes dans le panneau')}</Ph>;
            return (
                <ul className={`gp-hours${o.divider !== false ? ' gp-hours--divided' : ''}`}>
                    {items.map((item, i) => (
                        <li key={i} className={`gp-hours__row${item.highlight ? ' is-highlight' : ''}`}><span className="gp-hours__day">{item.day}</span><span className="gp-hours__time">{item.hours}</span></li>
                    ))}
                </ul>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            h.rule(`${h.sel} .gp-hours`, { color: o.color });
            h.rule(`${h.sel} .gp-hours__time`, { color: o.hours_color });
            h.rule(`${h.sel} .gp-hours__row.is-highlight`, { color: o.highlight_color });
            h.responsive('font_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-hours');
        },
    },
    'data-table': {
        preview: (o) => {
            const rows = tableRows(o.data);
            if (!rows.length) return <Ph>{t('Saisissez le contenu du tableau dans le panneau')}</Ph>;
            const header = o.header !== false ? rows.shift() : null;
            const cols = Math.max(...(header ? [header, ...rows] : rows).map((r) => r.length));
            const cells = (r, Tag) => Array.from({ length: cols }, (_, i) => <Tag key={i}>{r[i] ?? ''}</Tag>);
            return (
                <div className="gp-table-wrap">
                    <table className={`gp-table${o.striped !== false ? ' gp-table--striped' : ''}`}>
                        {o.caption && <caption>{o.caption}</caption>}
                        {header && <thead><tr>{cells(header, 'th')}</tr></thead>}
                        <tbody>{rows.map((r, i) => <tr key={i}>{cells(r, 'td')}</tr>)}</tbody>
                    </table>
                </div>
            );
        },
        css: (o, h) => {
            if (!tableRows(o.data).length) return;
            h.rule(`${h.sel} th`, { background: o.header_bg, color: o.header_color });
            h.rule(`${h.sel} td, ${h.sel} th`, { 'border-color': o.border_color });
            h.responsive('font_size', (v) => ({ 'font-size': `${v}px` }), ' table');
        },
    },
    timeline: {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des étapes dans le panneau')}</Ph>;
            return (
                <ol className={`gp-tl gp-tl--${o.layout ?? 'alternate'}${o.card !== false ? ' gp-tl--cards' : ''}`}>
                    {items.map((item, i) => (
                        <li key={i} className="gp-tl__item">
                            <span className="gp-tl__dot">{item.icon && <Icon value={item.icon} />}</span>
                            <div className="gp-tl__card">
                                {item.date && <p className="gp-tl__date">{item.date}</p>}
                                <p className="gp-tl__title">{item.title}</p>
                                {item.text && <p className="gp-tl__text">{item.text}</p>}
                            </div>
                        </li>
                    ))}
                </ol>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            h.rule(`${h.sel} .gp-tl`, { '--gp-tl-line': o.line_color, '--gp-tl-dot': o.dot_color });
            h.rule(`${h.sel} .gp-tl__date`, { color: o.date_color });
        },
    },
    'star-rating': {
        preview: (o) => {
            const rating = o.rating ?? 10;
            return (
                <div className="gp-rating">
                    <span className="gp-stars" style={{ '--gp-rating': `${rating * 10}%` }}>★★★★★</span>
                    {o.text && <span className="gp-rating__text">{o.text}</span>}
                </div>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-stars`, { '--gp-star': o.color, '--gp-star-off': o.empty_color, 'font-size': px(o.size) });
            h.rule(`${h.sel} .gp-rating__text`, { color: o.text_color });
            h.responsive('align', (v) => ({ 'justify-content': JUSTIFY[v] }), ' .gp-rating');
        },
    },
    alert: {
        preview: (o) => {
            const kind = o.kind ?? 'info';
            return (
                <div className={`gp-alert gp-alert--${kind}`} role="note">
                    {o.icon !== false && <Icon value={`lucide:${ALERT_ICONS[kind]}`} />}
                    <div className="gp-alert__body">
                        {o.title && <p className="gp-alert__title">{o.title}</p>}
                        <div className="gp-alert__text" dangerouslySetInnerHTML={html(o.text)} />
                    </div>
                </div>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-alert`, { background: o.bg_color, color: o.color, 'border-color': o.accent });
            h.rule(`${h.sel} .gp-alert .gp-ico`, { color: o.accent });
        },
    },
    hotspots: {
        preview: (o) => {
            if (!o.url) return <Ph>{t('Choisissez une image dans le panneau, puis placez les points')}</Ph>;
            return (
                <div className="gp-spots">
                    <img className="gp-spots__img" src={o.url} alt={o.alt ?? ''} draggable={false} />
                    {(o.spots ?? []).map((spot, i) => {
                        const x = spot.x ?? 50;
                        const y = spot.y ?? 50;
                        return (
                            <div key={i} className={`gp-spot${x > 60 ? ' gp-spot--left' : ''}`} style={{ left: `${x}%`, top: `${y}%` }} tabIndex={0}>
                                <span className="gp-spot__dot">{i + 1}</span>
                                <span className="gp-spot__tip">{spot.title && <strong>{spot.title}</strong>}{spot.text && <span>{spot.text}</span>}{spot.link && <a>→</a>}</span>
                            </div>
                        );
                    })}
                </div>
            );
        },
        css: (o, h) => {
            if (!o.url) return;
            h.rule(`${h.sel} .gp-spots`, { '--gp-spot': o.dot_color });
            h.rule(`${h.sel} .gp-spots__img`, { 'border-radius': px(o.radius) });
        },
    },
    'image-accordion': {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des panneaux dans le panneau de réglages')}</Ph>;
            return (
                <div className="gp-iacc">
                    {items.map((item, i) => (
                        <div key={i} className="gp-iacc__item" tabIndex={0} style={item.url ? undefined : { background: 'linear-gradient(135deg, var(--gp-primary), var(--gp-secondary))' }}>
                            {item.url && <img src={item.url} alt="" draggable={false} />}
                            <span className="gp-iacc__shade" />
                            <span className="gp-iacc__body"><span className="gp-iacc__title">{item.title}</span>{item.text && <span className="gp-iacc__text">{item.text}</span>}</span>
                        </div>
                    ))}
                </div>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            h.responsive('height', (v) => ({ height: `${v}px` }), ' .gp-iacc');
            h.rule(`${h.sel} .gp-iacc`, { gap: px(o.gap) });
            h.rule(`${h.sel} .gp-iacc__item`, { 'border-radius': px(o.radius) });
            h.rule(`${h.sel} .gp-iacc__shade`, { background: o.overlay });
        },
    },
    'dual-button': {
        preview: (o) => (
            <div className="gp-dbtn">
                <div className="gp-dbtn__group">
                    <a className="gp-dbtn__a">{o.text_1}</a>
                    {o.middle && <span className="gp-dbtn__mid">{o.middle}</span>}
                    <a className="gp-dbtn__b">{o.text_2}</a>
                </div>
            </div>
        ),
        css: (o, h) => {
            h.responsive('align', (v) => ({ 'justify-content': JUSTIFY[v] }), ' .gp-dbtn');
            h.rule(`${h.sel} .gp-dbtn__a`, { background: o.color_1, color: o.text_color_1 });
            h.rule(`${h.sel} .gp-dbtn__b`, { background: o.color_2, color: o.text_color_2 });
            h.rule(`${h.sel} .gp-dbtn`, { '--gp-dbtn-r': px(o.radius) });
        },
    },
    unfold: {
        preview: (o) => (
            <div className="gp-unfold">
                <div className="gp-unfold__content" dangerouslySetInnerHTML={html(o.html)} />
                <span className="gp-unfold__btn"><span className="gp-unfold__more">{o.more || t('Lire la suite')}</span></span>
            </div>
        ),
        css: (o, h) => {
            h.rule(`${h.sel} .gp-unfold`, { '--gp-unfold-h': px(o.height), '--gp-unfold-fade': o.fade, color: o.color });
            h.rule(`${h.sel} .gp-unfold__btn`, { color: o.button_color });
        },
    },
    ticker: {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des messages dans le panneau')}</Ph>;
            const list = items.map((item, i) => <li key={i}>{item.link ? <a>{item.text}</a> : item.text}</li>);
            return (
                <div className="gp-ticker">
                    {o.label && <span className="gp-ticker__label">{o.label}</span>}
                    <div className="gp-ticker__viewport"><div className="gp-ticker__track"><ul>{list}</ul><ul aria-hidden="true">{list}</ul></div></div>
                </div>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            h.rule(`${h.sel} .gp-ticker`, { background: o.bg_color, color: o.color, '--gp-ticker-speed': set(o.speed) ? `${o.speed}s` : null });
            h.rule(`${h.sel} .gp-ticker__label`, { background: o.label_bg, color: o.label_color });
        },
    },
    'image-scroll': {
        preview: (o) => {
            if (!o.url) return <Ph>{t('Choisissez une image longue dans le panneau')}</Ph>;
            return <div className={`gp-iscroll${o.frame ? ' gp-iscroll--frame' : ''}`} tabIndex={0}><img src={o.url} alt={o.alt ?? ''} draggable={false} /></div>;
        },
        css: (o, h) => {
            if (!o.url) return;
            h.responsive('height', (v) => ({ height: `${v}px`, '--gp-iscroll-h': `${v}px` }), ' .gp-iscroll');
            h.rule(`${h.sel} .gp-iscroll`, { '--gp-iscroll-time': set(o.duration) ? `${o.duration}s` : null, 'border-radius': px(o.radius) });
        },
    },
};
