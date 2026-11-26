// Aperçu et CSS des widgets avancés, lot 2 (widgets/pro2.js).
// Le CSS est le miroir exact des méthodes render() de app/Builder/Widgets/*Widget.php.
import { useId } from 'react';
import { cleanHtml } from '../html';
import { Icon } from '../icons';
import { t } from '../i18n';
import { embedUrl } from './core';
import { getState } from '../store';
import { chartHtml, parseChart } from './chart';

const JUSTIFY = { left: 'flex-start', center: 'center', right: 'flex-end' };

/** AnimatedHeadingWidget::SHAPES et ::words */
const SHAPES = {
    circle: 'M250,8C385,6 497,38 494,77C491,117 382,144 250,143C112,142 6,118 7,76C8,38 112,12 268,17',
    underline: 'M6,134C150,121 350,120 494,131',
    curly: 'M4,138C40,112 80,112 120,138C160,162 200,162 240,136C280,110 320,110 360,137C400,162 440,162 496,128',
    double: 'M6,126C170,116 330,116 494,124M32,146C190,136 320,136 468,144',
    strike: 'M6,82C170,72 330,72 494,78',
    diagonal: 'M10,140L490,14',
};
const animatedWords = (text) => String(text ?? '').split(/\r\n|\r|\n/).map((w) => w.trim()).filter((w) => w !== '').slice(0, 10);
const set = (v) => v !== undefined && v !== null && v !== '';
const px = (v) => (set(v) ? `${v}px` : null);
const html = (s) => ({ __html: cleanHtml(s ?? '') });
const Ph = ({ children }) => <div className="gpe-empty" style={{ margin: 0 }}>{children}</div>;

/** CarouselWidget::slidesCss */
function slidesCss(o, h, def) {
    h.rule(`${h.sel} .gp-slides`, { '--gp-per': String(o.per_view ?? def), '--gp-gap': px(o.gap) });
    h.tablet(`${h.sel} .gp-slides`, { '--gp-per': String(o.per_view_tablet ?? Math.min(2, o.per_view ?? def)) });
    h.mobile(`${h.sel} .gp-slides`, { '--gp-per': String(o.per_view_mobile ?? 1) });
}

/** Points d'aperçu (sur le site, ils sont créés par le script). */
function Dots({ o, count }) {
    if (o.dots === false || count < 2) return null;
    return (
        <div className="gp-slider__dots">
            {Array.from({ length: count }, (_, i) => <button key={i} type="button" tabIndex={-1} aria-current={i === 0 ? 'true' : undefined} />)}
        </div>
    );
}

function ContentToggle({ o }) {
    const name = `t-${useId().replace(/:/g, '')}`;
    return (
        <div className="gp-ctog">
            <input type="radio" name={name} id={`${name}-a`} className="gp-ctog__radio gp-ctog__radio--a" defaultChecked />
            <input type="radio" name={name} id={`${name}-b`} className="gp-ctog__radio gp-ctog__radio--b" />
            <div className="gp-ctog__switch"><label htmlFor={`${name}-a`}>{o.label_a ?? 'A'}</label><label htmlFor={`${name}-b`}>{o.label_b ?? 'B'}</label></div>
            <div className="gp-ctog__panel gp-ctog__panel--a" dangerouslySetInnerHTML={html(o.html_a)} />
            <div className="gp-ctog__panel gp-ctog__panel--b" dangerouslySetInnerHTML={html(o.html_b)} />
        </div>
    );
}

const SHARE = [
    ['whatsapp', 'brand:whatsapp'], ['facebook', 'brand:facebook'], ['x', 'brand:x'], ['telegram', 'brand:telegram'], ['email', 'lucide:mail'],
];

export const PRO2_IMPL = {
    slider: {
        preview: (o) => {
            const slides = o.slides ?? [];
            if (!slides.length) return <Ph>{t('Ajoutez des diapositives dans le panneau')}</Ph>;
            return (
                <div className="gp-slider gp-slider--full">
                    <div className="gp-slides gp-slides--full">
                        {slides.map((s, i) => (
                            <div key={i} className="gp-slide">
                                {s.url && <img className="gp-slide__img" src={s.url} alt="" draggable={false} />}
                                <span className="gp-slide__overlay" />
                                <div className="gp-slide__body">
                                    {s.eyebrow && <p className="gp-slide__eyebrow">{s.eyebrow}</p>}
                                    {s.title && <p className="gp-slide__title">{s.title}</p>}
                                    {s.text && <p className="gp-slide__text">{s.text}</p>}
                                    {s.button_text && <a className="gp-btn gp-slide__btn">{s.button_text}</a>}
                                </div>
                            </div>
                        ))}
                    </div>
                    <Dots o={o} count={slides.length} />
                </div>
            );
        },
        css: (o, h) => {
            if (!(o.slides ?? []).length) return;
            h.responsive('height', (v) => ({ height: `${v}px` }), ' .gp-slide');
            h.rule(`${h.sel} .gp-slider`, { 'border-radius': px(o.radius) });
            h.rule(`${h.sel} .gp-slide`, { color: o.color, 'text-align': o.align, '--gp-slide-align': set(o.align) ? JUSTIFY[o.align] : null });
            h.rule(`${h.sel} .gp-slide__overlay`, { background: o.overlay, opacity: set(o.overlay_opacity) ? o.overlay_opacity / 100 : null });
            h.responsive('title_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-slide__title');
        },
    },
    carousel: {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des éléments dans le panneau')}</Ph>;
            return (
                <div className="gp-slider">
                    <div className="gp-slides">
                        {items.map((item, i) => {
                            const Tag = item.link ? 'a' : 'div';
                            return (
                                <Tag key={i} className={`gp-card${o.card !== false ? ' gp-card--boxed' : ''}`}>
                                    {item.url && <img className="gp-card__img" src={item.url} alt="" draggable={false} />}
                                    {(item.title || item.text) && (
                                        <span className="gp-card__body">
                                            {item.title && <span className="gp-card__title">{item.title}</span>}
                                            {item.text && <span className="gp-card__text">{item.text}</span>}
                                        </span>
                                    )}
                                </Tag>
                            );
                        })}
                    </div>
                </div>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            slidesCss(o, h, 3);
            h.rule(`${h.sel} .gp-card__img`, { 'aspect-ratio': (o.ratio ?? '4:3').replace(':', ' / '), 'border-radius': px(o.radius) });
            h.rule(`${h.sel} .gp-card__title`, { color: o.title_color });
        },
    },
    'testimonial-carousel': {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Ph>{t('Ajoutez des témoignages dans le panneau')}</Ph>;
            return (
                <div className="gp-slider">
                    <div className="gp-slides">
                        {items.map((item, i) => {
                            const rating = Math.max(0, Math.min(5, parseInt(item.rating ?? 0, 10) || 0));
                            return (
                                <figure key={i} className="gp-tcard">
                                    {rating > 0 && <span className="gp-tcard__stars">{'★'.repeat(rating)}</span>}
                                    <blockquote className="gp-tcard__text">{item.text ?? ''}</blockquote>
                                    <figcaption className="gp-tcard__who">
                                        {item.url && <img src={item.url} alt="" draggable={false} />}
                                        <span><span className="gp-tcard__name">{item.name ?? ''}</span>{item.role && <span className="gp-tcard__role">{item.role}</span>}</span>
                                    </figcaption>
                                </figure>
                            );
                        })}
                    </div>
                </div>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).length) return;
            slidesCss(o, h, 2);
            h.rule(`${h.sel} .gp-slider`, { '--gp-tc-accent': o.accent });
            h.rule(`${h.sel} .gp-tcard`, { background: o.card_bg });
        },
    },
    'before-after': {
        preview: (o) => {
            if (!o.before_url || !o.after_url) return <Ph>{t('Choisissez les images « avant » et « après »')}</Ph>;
            const start = o.start ?? 50;
            return (
                <div className="gp-ba" style={{ '--gp-ba': `${start}%` }}>
                    <img className="gp-ba__after" src={o.after_url} alt="" draggable={false} />
                    <img className="gp-ba__before" src={o.before_url} alt="" draggable={false} />
                    <span className="gp-ba__label gp-ba__label--before">{o.before_label ?? t('Avant')}</span>
                    <span className="gp-ba__label gp-ba__label--after">{o.after_label ?? t('Après')}</span>
                    <span className="gp-ba__handle" aria-hidden="true" />
                </div>
            );
        },
        css: (o, h) => {
            if (!o.before_url || !o.after_url) return;
            h.rule(`${h.sel} .gp-ba`, { 'border-radius': px(o.radius), '--gp-ba-handle': o.handle_color });
        },
    },
    'content-toggle': {
        preview: (o) => <ContentToggle o={o} />,
        css: (o, h) => {
            h.rule(`${h.sel} .gp-ctog`, { '--gp-toggle': o.accent, '--gp-toggle-align': set(o.align) ? JUSTIFY[o.align] : null });
        },
    },
    'share-buttons': {
        preview: (o) => (
            <div className={`gp-share gp-share--${o.style ?? 'brand'} gp-share--${o.shape ?? 'circle'}`}>
                {o.label && <span className="gp-share__label">{o.label}</span>}
                {SHARE.filter(([key]) => o[key] !== false).map(([key, icon]) => (
                    <a key={key} className={`gp-share__btn gp-share__btn--${key}`}><Icon value={icon} /></a>
                ))}
                {o.copy !== false && <button type="button" className="gp-share__btn gp-share__btn--copy" tabIndex={-1}><Icon value="lucide:link" /></button>}
            </div>
        ),
        css: (o, h) => {
            h.rule(`${h.sel} .gp-share`, { '--gp-share-size': px(o.size) });
            h.responsive('align', (v) => ({ 'justify-content': JUSTIFY[v] }), ' .gp-share');
        },
    },
    'floating-button': {
        preview: (o) => {
            const kind = o.kind ?? 'whatsapp';
            const digits = String(o.number ?? '').replace(/[^0-9+]/g, '');
            if (kind === 'link' ? !o.link : digits === '') return <Ph>{kind === 'link' ? t('Indiquez le lien du bouton') : t('Indiquez le numéro du bouton')}</Ph>;
            const icon = { whatsapp: 'brand:whatsapp', phone: 'lucide:phone', link: 'lucide:message-circle' }[kind];
            return (
                <a className={`gp-float gp-float--${kind} gp-float--${o.position ?? 'right'} is-preview`}>
                    <Icon value={icon} />{o.label && <span className="gp-float__label">{o.label}</span>}
                </a>
            );
        },
        css: (o, h) => {
            const kind = o.kind ?? 'whatsapp';
            const digits = String(o.number ?? '').replace(/[^0-9+]/g, '');
            if (kind === 'link' ? !o.link : digits === '') return;
            h.rule(`${h.sel} .gp-float`, { background: o.bg_color, color: o.color });
        },
    },
    'back-to-top': {
        preview: (o) => (
            <a className={`gp-totop gp-totop--${o.position ?? 'right'} gp-totop--${o.shape ?? 'circle'} is-preview`}><Icon value="lucide:arrow-up-right" /></a>
        ),
        css: (o, h) => {
            h.rule(`${h.sel} .gp-totop`, { background: o.bg_color, color: o.color });
        },
    },
    'reading-progress': {
        preview: (o) => <div className={`gp-progress-read gp-progress-read--${o.position ?? 'top'} is-preview`} aria-hidden="true" />,
        css: (o, h) => {
            h.rule(`${h.sel} .gp-progress-read`, { background: o.color, height: px(o.height) });
        },
    },
    'video-playlist': {
        preview: (o) => {
            const videos = (o.items ?? []).map((i) => ({ ...i, embed: embedUrl(String(i.url ?? '')) })).filter((i) => i.embed);
            if (!videos.length) return <Ph>{t('Ajoutez des liens YouTube ou Vimeo dans le panneau')}</Ph>;
            return (
                <div className={`gp-vplay gp-vplay--${o.layout ?? 'side'}`}>
                    <div className="gp-vplay__player"><iframe src={videos[0].embed} title={videos[0].title ?? ''} style={{ pointerEvents: 'none' }} /></div>
                    <ol className="gp-vplay__list">
                        {videos.map((v, i) => (
                            <li key={i}>
                                <a aria-current={i === 0 ? 'true' : undefined}>
                                    <span className="gp-vplay__num">{i + 1}</span><span className="gp-vplay__title">{v.title ?? ''}</span>
                                    {v.duration && <span className="gp-vplay__time">{v.duration}</span>}
                                </a>
                            </li>
                        ))}
                    </ol>
                </div>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).some((i) => embedUrl(String(i.url ?? '')))) return;
            h.rule(`${h.sel} .gp-vplay`, { '--gp-vp-accent': o.accent, '--gp-vp-bg': o.list_bg });
        },
    },
    'page-nav': {
        preview: (o) => {
            const items = (o.items ?? []).filter((i) => set(i.anchor));
            if (!items.length) return <Ph>{t('Ajoutez des liens vers les sections')}</Ph>;
            return (
                <nav className={`gp-pnav gp-pnav--${o.position ?? 'right'} is-preview`}>
                    <ul>
                        {items.map((item, i) => (
                            <li key={i}><a aria-current={i === 0 ? 'true' : undefined}><span className="gp-pnav__dot" /><span className="gp-pnav__label">{item.label ?? item.anchor}</span></a></li>
                        ))}
                    </ul>
                </nav>
            );
        },
        css: (o, h) => {
            if (!(o.items ?? []).some((i) => set(i.anchor))) return;
            h.rule(`${h.sel} .gp-pnav`, { '--gp-pnav': o.color, '--gp-pnav-on': o.active_color });
        },
    },
    chart: {
        preview: (o) => {
            const inner = chartHtml(o, getState().sample?.locale ?? 'fr');
            if (inner === null) return <Ph>{t('Saisissez les données du graphique dans le panneau')}</Ph>;
            return <figure className={`gp-chart gp-chart--${o.kind ?? 'bar'}`} dangerouslySetInnerHTML={{ __html: inner }} />;
        },
        css: (o, h) => {
            if (!parseChart(o.data).length) return;
            h.rule(`${h.sel} .gp-chart`, { '--gp-c1': o.color_1, '--gp-c2': o.color_2, '--gp-c3': o.color_3, '--gp-c4': o.color_4, '--gp-chart-grid': o.grid_color, color: o.text_color });
            h.responsive('max_width', (v) => ({ 'max-width': `${v}px` }), ' .gp-chart');
        },
    },
    'animated-heading': {
        preview: (o) => {
            const Tag = o.tag || 'h2';
            const words = animatedWords(o.words);
            const effect = o.effect ?? 'typing';
            const first = words[0] ?? '';
            return (
                <Tag className={`gp-ah gp-ah--${effect}`}>
                    <span aria-hidden="true">
                        {o.before ? `${o.before} ` : ''}
                        <span className="gp-ah__words">
                            {effect === 'highlight'
                                ? <span className="gp-ah__hl">{first}<svg className="gp-ah__shape" viewBox="0 0 500 150" preserveAspectRatio="none"><path d={SHAPES[o.shape ?? 'circle']} pathLength="1" /></svg></span>
                                : (words.length ? words : ['']).map((w, i) => <span key={i} className={`gp-ah__word${i === 0 ? ' is-on' : ''}`}>{w}</span>)}
                        </span>
                        {o.after ? ` ${o.after}` : ''}
                    </span>
                </Tag>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} > *`, { color: o.color, 'font-family': o.font_family ? null : 'var(--gp-heading-font)' });
            h.responsive('align', (v) => ({ 'text-align': v }), ' > *');
            h.responsive('font_size', (v) => ({ 'font-size': `${v}px` }), ' > *');
            h.typography(' > *');
            h.rule(`${h.sel} .gp-ah__words`, { color: o.words_color });
            h.rule(`${h.sel} .gp-ah__shape`, { color: o.shape_color, 'stroke-width': o.shape_width ?? null });
        },
    },
};
