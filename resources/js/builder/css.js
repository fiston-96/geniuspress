// Génère le CSS du canevas. Miroir de app/Builder/Renderer.php (sections,
// colonnes) ; le CSS de chaque widget vient de sa déclaration dans le registre.
import { getWidget } from './registry';

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

    const def = getWidget(w.type);
    if (!def?.css) return;
    try {
        def.css(o, {
            sel,
            px,
            rule: (selector, props) => rule(css, selector, props),
            mobile: (selector, props) => rule(mobile, selector, props),
        });
    } catch (e) {
        console.error(`[GeniusPress] CSS du widget « ${w.type} » :`, e);
    }
}
