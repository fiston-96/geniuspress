// Widgets du cœur : déclarés exactement comme le ferait un plugin.
// Le CSS ici est le miroir des méthodes render() de app/Builder/Widgets/*.php.
import DOMPurify from 'dompurify';
import { registerWidget } from '../registry';
import { CORE_WIDGETS } from '../widgets';
import { LIBRARY_WIDGETS } from './library';
import { LIBRARY_IMPL } from './library-preview';
import { t } from '../i18n';

export function embedUrl(url = '') {
    let m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,20})/);
    if (m) return `https://www.youtube-nocookie.com/embed/${m[1]}`;
    m = url.match(/vimeo\.com\/(?:video\/)?(\d{4,15})/);
    if (m) return `https://player.vimeo.com/video/${m[1]}`;
    return null;
}

export function Placeholder({ children }) {
    return <div className="gpe-empty" style={{ margin: 0 }}>{children}</div>;
}

const ALIGN_MARGIN = { left: '0 auto 0 0', right: '0 0 0 auto' };

const IMPL = {
    heading: {
        preview: (o) => {
            const Tag = o.tag || 'h2';
            return <Tag>{o.text}</Tag>;
        },
        css: (o, { rule, responsive, typography, sel }) => {
            rule(`${sel} > *`, { color: o.color, 'font-family': o.font_family ? null : 'var(--gp-heading-font)' });
            responsive('align', (v) => ({ 'text-align': v }), ' > *');
            responsive('font_size', (v) => ({ 'font-size': `${v}px` }), ' > *');
            typography(' > *');
        },
    },
    text: {
        preview: (o) => <div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(o.html || '') }} />,
        css: (o, { rule, responsive, typography, sel }) => {
            rule(sel, { color: o.color });
            responsive('align', (v) => ({ 'text-align': v }));
            responsive('font_size', (v) => ({ 'font-size': `${v}px` }));
            typography();
            rule(`${sel} a`, { color: o.link_color });
            rule(`${sel} a:hover`, { color: o.link_color_hover });
        },
    },
    image: {
        preview: (o) => (o.url ? <img src={o.url} alt={o.alt || ''} draggable={false} /> : <Placeholder>{o.alt ? t('Image à ajouter : :alt', { alt: o.alt }) : t('Choisissez une image dans le panneau')}</Placeholder>),
        css: (o, { rule, tablet, mobile, responsive, px, sel }) => {
            rule(sel, { 'text-align': o.align ?? 'center' });
            tablet(sel, { 'text-align': o.align_tablet });
            mobile(sel, { 'text-align': o.align_mobile });
            rule(`${sel} img`, { 'border-radius': px(o.border_radius) });
            responsive('width', (v) => ({ width: `${v}%` }), ' img');
        },
    },
    button: {
        preview: (o) => <a className={`gp-btn gp-btn-${o.size || 'md'}`} href={o.url || '#'} draggable={false}>{o.text || 'Bouton'}</a>,
        css: (o, { rule, tablet, mobile, responsive, typography, px, sel }) => {
            const variant = o.variant ?? 'filled';
            const bg = o.bg_color ?? 'var(--gp-primary)';
            rule(`${sel} .gp-btn`, {
                background: variant === 'filled' ? bg : 'transparent',
                'border-color': bg,
                color: o.text_color ?? (variant === 'filled' ? '#ffffff' : bg),
                'border-radius': px(o.border_radius),
            });
            // Alignement par appareil ; « full » = bouton sur toute la largeur (miroir de ButtonWidget.php).
            const byDevice = { desktop: [rule, o.align ?? 'left'], tablet: [tablet, o.align_tablet], mobile: [mobile, o.align_mobile] };
            for (const [add, align] of Object.values(byDevice)) {
                if (!align) continue;
                add(sel, { 'text-align': align === 'full' ? 'left' : align });
                add(`${sel} .gp-btn`, { display: align === 'full' ? 'block' : 'inline-block' });
            }
            responsive('font_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-btn');
            typography(' .gp-btn');
            rule(`${sel} .gp-btn:hover`, {
                background: variant === 'filled' ? o.bg_color_hover : null,
                'border-color': o.bg_color_hover,
                color: o.text_color_hover,
            });
        },
    },
    spacer: {
        preview: () => null,
        css: (o, { rule, tablet, mobile, px, sel }) => {
            rule(sel, { height: px(o.height ?? 40) });
            tablet(sel, { height: px(o.height_tablet) });
            mobile(sel, { height: px(o.height_mobile) });
        },
    },
    divider: {
        preview: () => <hr />,
        css: (o, { rule, sel }) => rule(`${sel} hr`, {
            'border-top': `${o.thickness ?? 1}px ${o.style ?? 'solid'} ${o.color ?? '#e2e8f0'}`,
            width: `${o.width ?? 100}%`,
            margin: ALIGN_MARGIN[o.align] ?? '0 auto',
        }),
    },
    links: {
        preview: (o) => {
            const items = o.items ?? [];
            if (!items.length) return <Placeholder>{t('Ajoutez des liens dans le panneau')}</Placeholder>;
            return (
                <nav className={`gp-links gp-links--${o.layout === 'horizontal' ? 'horizontal' : 'vertical'}${o.style === 'boxed' ? ' gp-links--boxed' : ''}`}>
                    {o.title && <p className="gp-links__title">{o.title}</p>}
                    <ul>{items.map((item, i) => <li key={i}><a href={item.url || '#'} draggable={false}>{item.label}</a></li>)}</ul>
                </nav>
            );
        },
        css: (o, { rule, sel }) => rule(sel, { '--gp-links-color': o.color }),
    },
    video: {
        preview: (o) => {
            const src = embedUrl(o.url);
            if (!src) return <Placeholder>{t('Collez un lien YouTube ou Vimeo')}</Placeholder>;
            return (
                <div className="gp-video" style={{ aspectRatio: (o.aspect || '16:9').replace(':', ' / ') }}>
                    <iframe src={src} title={t('Vidéo')} style={{ pointerEvents: 'none' }} />
                </div>
            );
        },
    },
};

export function registerCoreWidgets() {
    for (const [type, def] of Object.entries(CORE_WIDGETS)) {
        registerWidget(type, { ...def, ...IMPL[type] });
    }
    for (const [type, def] of Object.entries(LIBRARY_WIDGETS)) {
        registerWidget(type, { ...def, ...LIBRARY_IMPL[type] });
    }
}
