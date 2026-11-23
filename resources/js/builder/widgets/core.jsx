// Widgets du cœur : déclarés exactement comme le ferait un plugin.
// Le CSS ici est le miroir des méthodes render() de app/Builder/Widgets/*.php.
import DOMPurify from 'dompurify';
import { registerWidget } from '../registry';
import { CORE_WIDGETS } from '../widgets';

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
        css: (o, { rule, mobile, px, sel }) => {
            rule(`${sel} > *`, { 'text-align': o.align, color: o.color, 'font-size': px(o.font_size), 'font-weight': o.font_weight });
            mobile(`${sel} > *`, { 'font-size': px(o.font_size_mobile) });
        },
    },
    text: {
        preview: (o) => <div dangerouslySetInnerHTML={{ __html: DOMPurify.sanitize(o.html || '') }} />,
        css: (o, { rule, px, sel }) => rule(sel, { 'text-align': o.align, color: o.color, 'font-size': px(o.font_size) }),
    },
    image: {
        preview: (o) => (o.url ? <img src={o.url} alt={o.alt || ''} draggable={false} /> : <Placeholder>{o.alt ? `Image à ajouter : ${o.alt}` : 'Choisissez une image dans le panneau'}</Placeholder>),
        css: (o, { rule, px, sel }) => {
            rule(sel, { 'text-align': o.align ?? 'center' });
            rule(`${sel} img`, { width: o.width ? `${o.width}%` : null, 'border-radius': px(o.border_radius) });
        },
    },
    button: {
        preview: (o) => <a className={`gp-btn gp-btn-${o.size || 'md'}`} href={o.url || '#'} draggable={false}>{o.text || 'Bouton'}</a>,
        css: (o, { rule, px, sel }) => {
            const variant = o.variant ?? 'filled';
            const bg = o.bg_color ?? 'var(--gp-primary)';
            const align = o.align ?? 'left';
            rule(sel, { 'text-align': align === 'full' ? null : align });
            rule(`${sel} .gp-btn`, {
                background: variant === 'filled' ? bg : 'transparent',
                'border-color': bg,
                color: o.text_color ?? (variant === 'filled' ? '#ffffff' : bg),
                'border-radius': px(o.border_radius),
                display: align === 'full' ? 'block' : null,
            });
        },
    },
    spacer: {
        preview: () => null,
        css: (o, { rule, mobile, px, sel }) => {
            rule(sel, { height: px(o.height ?? 40) });
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
    video: {
        preview: (o) => {
            const src = embedUrl(o.url);
            if (!src) return <Placeholder>Collez un lien YouTube ou Vimeo</Placeholder>;
            return (
                <div className="gp-video" style={{ aspectRatio: (o.aspect || '16:9').replace(':', ' / ') }}>
                    <iframe src={src} title="Vidéo" style={{ pointerEvents: 'none' }} />
                </div>
            );
        },
    },
};

export function registerCoreWidgets() {
    for (const [type, def] of Object.entries(CORE_WIDGETS)) {
        registerWidget(type, { ...def, ...IMPL[type] });
    }
}
