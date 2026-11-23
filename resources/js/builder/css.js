// Génère le CSS du canevas. Miroir de app/Builder/Renderer.php : toute
// modification ici doit être reportée côté PHP (et inversement).

const px = (v) => (v === undefined || v === null || v === '' ? null : `${v}px`);

function rule(out, selector, props) {
    const body = Object.entries(props)
        .filter(([, v]) => v !== null && v !== undefined && v !== '')
        .map(([k, v]) => `${k}:${v}`)
        .join(';');
    if (body) out.push(`${selector}{${body}}`);
}

const VALIGN = { start: 'flex-start', center: 'center', end: 'flex-end', stretch: 'stretch' };
const safeUrl = (u) => (typeof u === 'string' && /^(https?:\/\/|\/(?!\/))[^\s"'<>\\]*$/i.test(u) ? u : null);

export function buildCss(content) {
    const css = [];
    const mobile = [];

    for (const s of content.sections) {
        const o = s.settings;
        const sel = `#gp-${s.id}`;
        const bgImg = safeUrl(o.background_image);
        rule(css, sel, {
            'background-color': o.background_color,
            background: bgImg ? `url("${bgImg}") center/cover no-repeat${o.background_color ? ' ' + o.background_color : ''}` : null,
            color: o.text_color,
            'padding-top': px(o.padding_top ?? 64),
            'padding-bottom': px(o.padding_bottom ?? 64),
            'min-height': px(o.min_height),
        });
        rule(mobile, sel, { 'padding-top': px(o.padding_top_mobile), 'padding-bottom': px(o.padding_bottom_mobile) });

        const gap = o.gap ?? 24;
        rule(css, `${sel} > .gp-row`, {
            'max-width': o.content_width === 'full' ? 'none' : `${o.max_width ?? 1140}px`,
            gap: px(gap),
            'align-items': VALIGN[o.vertical_align ?? 'stretch'],
        });
        if (o.overlay_color) {
            rule(css, `${sel} > .gp-overlay`, { background: o.overlay_color, opacity: (o.overlay_opacity ?? 50) / 100 });
        }

        const n = s.columns.length;
        const share = Math.round(((gap * (n - 1)) / Math.max(1, n)) * 100) / 100;
        for (const c of s.columns) {
            const co = c.settings;
            const width = co.width ?? Math.round((100 / n) * 10000) / 10000;
            rule(css, `#gp-${c.id}`, {
                flex: `0 1 calc(${width}% - ${share}px)`,
                padding: px(co.padding),
                'background-color': co.background_color,
                'border-radius': px(co.border_radius),
                'justify-content': co.vertical_align ? VALIGN[co.vertical_align] : null,
            });

            for (const w of c.widgets) widgetCss(css, mobile, w);
        }
    }

    let out = css.join('\n');
    if (mobile.length) out += `\n@media (max-width: 767px){\n${mobile.join('\n')}\n}`;
    return out;
}

function widgetCss(css, mobile, w) {
    const o = w.settings;
    const sel = `#gp-${w.id}`;
    rule(css, sel, { 'margin-bottom': px(o.margin_bottom) });

    switch (w.type) {
        case 'heading':
            rule(css, `${sel} > *`, { 'text-align': o.align, color: o.color, 'font-size': px(o.font_size), 'font-weight': o.font_weight });
            rule(mobile, `${sel} > *`, { 'font-size': px(o.font_size_mobile) });
            break;
        case 'text':
            rule(css, sel, { 'text-align': o.align, color: o.color, 'font-size': px(o.font_size) });
            break;
        case 'image':
            rule(css, sel, { 'text-align': o.align ?? 'center' });
            rule(css, `${sel} img`, { width: o.width ? `${o.width}%` : null, 'border-radius': px(o.border_radius) });
            break;
        case 'button': {
            const variant = o.variant ?? 'filled';
            const bg = o.bg_color ?? 'var(--gp-primary)';
            const align = o.align ?? 'left';
            rule(css, sel, { 'text-align': align === 'full' ? null : align });
            rule(css, `${sel} .gp-btn`, {
                background: variant === 'filled' ? bg : 'transparent',
                'border-color': bg,
                color: o.text_color ?? (variant === 'filled' ? '#ffffff' : bg),
                'border-radius': px(o.border_radius),
                display: align === 'full' ? 'block' : null,
            });
            break;
        }
        case 'spacer':
            rule(css, sel, { height: px(o.height ?? 40) });
            rule(mobile, sel, { height: px(o.height_mobile) });
            break;
        case 'divider': {
            const margin = { left: '0 auto 0 0', right: '0 0 0 auto' }[o.align] ?? '0 auto';
            rule(css, `${sel} hr`, {
                'border-top': `${o.thickness ?? 1}px ${o.style ?? 'solid'} ${o.color ?? '#e2e8f0'}`,
                width: `${o.width ?? 100}%`,
                margin,
            });
            break;
        }
    }
}

export function embedUrl(url = '') {
    let m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,20})/);
    if (m) return `https://www.youtube-nocookie.com/embed/${m[1]}`;
    m = url.match(/vimeo\.com\/(?:video\/)?(\d{4,15})/);
    if (m) return `https://player.vimeo.com/video/${m[1]}`;
    return null;
}
