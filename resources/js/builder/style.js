// CSS du canevas. Miroir exact de app/Builder/RenderContext.php et
// app/Builder/ElementStyle.php : le même contenu doit donner le même CSS
// dans l'éditeur et sur le site publié.
import { getWidget } from './registry';

export const DEVICES = { desktop: '', tablet: '_tablet', mobile: '_mobile' };
export const DEVICE_LIST = Object.keys(DEVICES);
export const BREAKPOINTS = { tablet: 1024, mobile: 767 };

const FLEX = {
    start: 'flex-start', center: 'center', end: 'flex-end', stretch: 'stretch', baseline: 'baseline',
    between: 'space-between', around: 'space-around', evenly: 'space-evenly',
};

/** Réglages communs à tous les éléments (Schema::STYLE). r = par appareil, h = au survol. */
export const STYLE_KEYS = {
    margin: 'r', padding: 'r', width: 'r', min_height: 'r', align_self: 'r', grow: 'r', order: 'r',
    background_type: '', background_color: 'h', background_image: '', background_position: '', background_size: '', background_fixed: '', background_parallax: '',
    gradient_type: '', gradient_from: '', gradient_to: '', gradient_angle: '',
    border_style: '', border_width: '', border_color: 'h', border_radius: 'r', box_shadow: 'h',
    text_color: 'h', opacity: 'h', z_index: '', overflow: '', transition: '',
    transition_easing: '', hover_effect: '', entrance: '', entrance_duration: '', entrance_delay: '', entrance_easing: '', entrance_repeat: '',
    sticky: '', sticky_offset: '', sticky_devices: '', sticky_bg: '', sticky_shadow: '', scroll_effect: '', scroll_intensity: '',
    hide_desktop: '', hide_tablet: '', hide_mobile: '', css_id: '', css_classes: '', custom_css: '',
};

/** Disposition des conteneurs (Schema::CONTAINER). */
export const CONTAINER_KEYS = {
    tag: '', content_width: '', max_width: 'r', direction: 'r', wrap: 'r', justify: 'r', align: 'r', gap: 'r',
    overlay_color: '', overlay_opacity: '',
};

/** Toutes les clés d'un ensemble de réglages, suffixes compris. */
export function expandKeys(map) {
    const out = [];
    for (const [key, flags] of Object.entries(map)) {
        out.push(key);
        if (flags.includes('r')) out.push(`${key}_tablet`, `${key}_mobile`);
        if (flags.includes('h')) out.push(`${key}_hover`);
    }
    return out;
}

export const containerDefaults = (depth) => ({ content_width: depth === 0 ? 'boxed' : 'full', max_width: 1140, direction: 'column', gap: 20 });

export const globalColor = (name) => (['primary', 'secondary', 'accent'].includes(name) ? `var(--gp-${name})` : `var(--gp-color-${name})`);

export const cssValue = (v) => String(v).replace(/global:([a-z0-9-]+)/g, (_, n) => globalColor(n));

const empty = (v) => v === null || v === undefined || v === '';

/** Courbes d'accélération (ElementStyle::EASING). */
export const EASING = {
    ease: 'ease', 'ease-in': 'ease-in', 'ease-out': 'ease-out', 'ease-in-out': 'ease-in-out', linear: 'linear',
    smooth: 'cubic-bezier(.22,1,.36,1)', back: 'cubic-bezier(.34,1.56,.64,1)',
};

/** Valeur d'un réglage pour un appareil, en héritant des écrans plus grands. */
export function at(s, key, device) {
    let value = null;
    for (const [d, sfx] of Object.entries(DEVICES)) {
        if (!empty(s[key + sfx])) value = s[key + sfx];
        if (d === device) break;
    }
    return value;
}

/** Largeur d'écran (px) à laquelle la taille réglée pour l'ordinateur est atteinte (RenderContext::FLUID_WIDE). */
export const FLUID_WIDE = 1440;

/**
 * Taille de texte fluide entre deux appareils (RenderContext::fluid) : la taille glisse de celle
 * de l'appareil plus petit à celle-ci selon la largeur de l'écran, au lieu de sauter au seuil.
 */
export function fluid(props, s, key, device, value) {
    const num = (v) => !empty(v) && !Number.isNaN(Number(v));
    if (!props || props['font-size'] === undefined || !num(value) || props['font-size'] !== `${value}px`) return props;
    const devices = Object.keys(DEVICES);
    for (const smaller of devices.slice(devices.indexOf(device) + 1)) {
        const small = s[key + DEVICES[smaller]];
        if (empty(small)) continue;
        if (num(small) && Number(small) < Number(value)) {
            const lo = BREAKPOINTS[smaller];
            const hi = device === 'desktop' ? FLUID_WIDE : BREAKPOINTS.tablet;
            return { ...props, 'font-size': `clamp(${small}px, calc(${small}px + ${Number(value) - Number(small)} * (100vw - ${lo}px) / ${hi - lo}), ${value}px)` };
        }
        break;
    }
    return props;
}

/** Feuille de style collectée pendant le rendu, par appareil. */
export class Sheet {
    constructor() {
        this.css = { desktop: [], tablet: [], mobile: [] };
        this.fonts = new Set();
    }

    add(device, selector, props) {
        const body = Object.entries(props)
            .filter(([, v]) => !empty(v))
            .map(([k, v]) => `${k}:${cssValue(v)}`)
            .join(';');
        if (body && this.css[device]) this.css[device].push(`${selector}{${body}}`);
    }

    /** CSS déjà limité à un élément (CSS personnalisé), couleurs globales comprises (RenderContext::raw). */
    raw(css) {
        if (css !== '') this.css.desktop.push(cssValue(css));
    }

    rule(id, props, device = 'desktop', suffix = '') {
        this.add(device === true ? 'mobile' : device === false ? 'desktop' : device, `#gp-${id}${suffix}`, props);
    }

    responsive(id, s, key, fn, suffix = '') {
        for (const [device, sfx] of Object.entries(DEVICES)) {
            if (!empty(s[key + sfx])) this.rule(id, fluid(fn(s[key + sfx]), s, key, device, s[key + sfx]), device, suffix);
        }
    }

    typography(id, s, suffix = '') {
        this.rule(id, {
            'font-family': s.font_family ? this.fontFamily(s.font_family) : null,
            'font-weight': s.font_weight,
            'font-style': s.font_style,
            'text-transform': s.text_transform,
        }, 'desktop', suffix);
        this.responsive(id, s, 'line_height', (v) => ({ 'line-height': v }), suffix);
        this.responsive(id, s, 'letter_spacing', (v) => ({ 'letter-spacing': v }), suffix);
    }

    fontFamily(family) {
        if (family === 'global:heading') return 'var(--gp-heading-font)';
        if (family === 'global:body') return 'var(--gp-font)';
        this.fonts.add(family);
        return `'${family.replace(/'/g, '')}', system-ui, sans-serif`;
    }

    toString() {
        let out = this.css.desktop.join('\n');
        for (const [device, max] of Object.entries(BREAKPOINTS)) {
            if (this.css[device].length) out += `\n@media (max-width: ${max}px) {\n${this.css[device].join('\n')}\n}`;
        }
        return out;
    }
}

function sides(property, v, postfix = '') {
    if (!v) return {};
    const unit = v.u ?? 'px';
    const out = {};
    for (const [k, side] of [['t', 'top'], ['r', 'right'], ['b', 'bottom'], ['l', 'left']]) {
        if (!empty(v[k])) out[`${property}-${side}${postfix}`] = `${v[k]}${Number(v[k]) === 0 ? '' : unit}`;
    }
    return out;
}

function radius(v) {
    if (!v) return {};
    const unit = v.u ?? 'px';
    const out = {};
    for (const [k, corner] of [['t', 'top-left'], ['r', 'top-right'], ['b', 'bottom-right'], ['l', 'bottom-left']]) {
        if (!empty(v[k])) out[`border-${corner}-radius`] = `${v[k]}${Number(v[k]) === 0 ? '' : unit}`;
    }
    return out;
}

export const shadowCss = (v) => `${v.inset ? 'inset ' : ''}${v.x ?? 0}px ${v.y ?? 0}px ${v.blur ?? 0}px ${v.spread ?? 0}px ${v.color}`;

const round2 = (n) => Math.round(n * 100) / 100;

function basis(width, parent, device) {
    let count = parent.children?.length ?? 0;
    const gap = parseInt(at(parent.settings, 'gap', device) ?? 20, 10);
    if (!String(width).endsWith('%') || count < 2 || gap === 0) return width;
    // Grille sur plusieurs rangées (miroir de ElementStyle::basis).
    if ((at(parent.settings, 'wrap', device) ?? 'nowrap') === 'wrap') {
        count = Math.max(1, Math.round(100 / Math.max(1, parseFloat(width))));
        if (count < 2) return width;
    }
    return `calc(${width} - ${round2((gap * (count - 1)) / count)}px)`;
}

function sizing(s, parent, device, isContainer, useWidth) {
    const direction = (d) => (parent ? at(parent.settings, 'direction', d) ?? 'column' : 'column');
    const row = direction(device).startsWith('row');

    let width = null;
    if (useWidth) {
        const list = DEVICE_LIST.slice(0, DEVICE_LIST.indexOf(device) + 1).reverse();
        for (const d of list) {
            if (!empty(s[`width${DEVICES[d]}`])) {
                if (direction(d).startsWith('row') === row) width = s[`width${DEVICES[d]}`];
                break;
            }
        }
    }
    // « Remplir l'espace disponible » (miroir de ElementStyle::sizing).
    const grow = at(s, 'grow', device) ? '1' : '0';
    if (row) return { flex: width !== null ? `${grow} 1 ${basis(width, parent, device)}` : isContainer ? '1 1 0' : `${grow} 1 auto`, width: 'auto' };
    return { flex: `${grow} 1 auto`, width: width ?? 'auto' };
}

const sameSizing = (a, b) => a && b && a.flex === b.flex && a.width === b.width;

function background(s, has) {
    const p = {};
    if ((s.background_type ?? 'classic') === 'gradient' && has.has('gradient_from') && s.gradient_from) {
        const to = s.gradient_to ?? 'transparent';
        p['background-image'] = (s.gradient_type ?? 'linear') === 'radial'
            ? `radial-gradient(circle, ${s.gradient_from}, ${to})`
            : `linear-gradient(${s.gradient_angle ?? 180}deg, ${s.gradient_from}, ${to})`;
        return p;
    }
    if (has.has('background_color') && s.background_color) p['background-color'] = s.background_color;
    if (has.has('background_image') && safeUrl(s.background_image)) {
        p['background-image'] = `url("${safeUrl(s.background_image)}")`;
        p['background-position'] = s.background_position ?? 'center';
        p['background-size'] = s.background_size ?? 'cover';
        p['background-repeat'] = 'no-repeat';
        p['background-attachment'] = s.background_fixed ? 'fixed' : null;
        // Parallaxe : l'image passe dans un calque (::before, site.css) déplacé au défilement.
        if (has.has('background_parallax') && !empty(s.background_parallax)) {
            p['--gp-bg-img'] = p['background-image'];
            p['--gp-plx'] = String(s.background_parallax / 10);
            p['background-image'] = null;
            p['background-attachment'] = null;
        }
    }
    return p;
}

export const safeUrl = (u) => (typeof u === 'string' && /^(https?:\/\/|\/(?!\/))[^\s"'<>\\]*$/i.test(u) ? u : null);

/** Réglages communs d'un élément (ElementStyle::apply). */
export function applyStyle(sheet, node, parent, keys) {
    const s = node.settings;
    const id = node.id;
    const has = new Set(keys);
    const isContainer = node.type === 'container';
    const sticky = has.has('sticky') && !empty(s.sticky) ? s.sticky : null;

    let previous = null;
    for (const [device, sfx] of Object.entries(DEVICES)) {
        const p = {};
        if (has.has('margin')) Object.assign(p, sides('margin', s[`margin${sfx}`]));
        if (has.has('padding')) Object.assign(p, sides('padding', s[`padding${sfx}`]));
        if (has.has('border_radius')) Object.assign(p, radius(s[`border_radius${sfx}`]));
        if (has.has('min_height') && !empty(s[`min_height${sfx}`])) p['min-height'] = s[`min_height${sfx}`];
        if (has.has('align_self') && !empty(s[`align_self${sfx}`])) p['align-self'] = FLEX[s[`align_self${sfx}`]];
        if (has.has('order') && !empty(s[`order${sfx}`])) p.order = String(s[`order${sfx}`]);

        const size = sizing(s, parent, device, isContainer, has.has('width'));
        if (!sameSizing(size, previous) && !(device === 'desktop' && size.flex === '0 1 auto' && size.width === 'auto')) Object.assign(p, size);
        previous = size;

        // Élément collant seulement sur ordinateur (ou ordinateur et tablette) : il reprend sa place ailleurs.
        if (sticky && ((device === 'tablet' && (s.sticky_devices ?? 'all') === 'desktop') || (device === 'mobile' && (s.sticky_devices ?? 'all') === 'desktop-tablet'))) {
            Object.assign(p, { position: 'relative', top: 'auto', bottom: 'auto' });
        }

        sheet.rule(id, p, device);
    }

    const p = background(s, has);
    if (has.has('border_style') && s.border_style) {
        p['border-style'] = s.border_style;
        if (s.border_style !== 'none') {
            Object.assign(p, sides('border', s.border_width ?? { t: 1, r: 1, b: 1, l: 1 }, '-width'));
            p['border-color'] = s.border_color;
        }
    }
    if (has.has('box_shadow') && s.box_shadow?.color) p['box-shadow'] = shadowCss(s.box_shadow);
    if (has.has('text_color') && s.text_color) p.color = s.text_color;
    if (has.has('opacity') && !empty(s.opacity)) p.opacity = String(s.opacity / 100);
    if (has.has('z_index') && !empty(s.z_index)) {
        p['z-index'] = String(s.z_index);
        p.position = isContainer ? null : 'relative';
    }
    if (has.has('overflow') && s.overflow) p.overflow = s.overflow;
    // Collant en haut (en-tête) ou en bas de l'écran pendant le défilement.
    if (sticky) {
        p.position = 'sticky';
        p[sticky] = `${s.sticky_offset ?? 0}px`;
        p['z-index'] = String(s.z_index ?? 50);
    }
    // Effet au défilement : intensité (l'effet est dans site.css, la position vient du script du site).
    if (has.has('scroll_effect') && !empty(s.scroll_effect) && !empty(s.scroll_intensity)) p['--gp-sc-k'] = String(s.scroll_intensity / 10);

    const hover = {};
    if (has.has('background_color') && s.background_color_hover) hover['background-color'] = s.background_color_hover;
    if (has.has('border_color') && s.border_color_hover) hover['border-color'] = s.border_color_hover;
    if (has.has('box_shadow') && s.box_shadow_hover?.color) hover['box-shadow'] = shadowCss(s.box_shadow_hover);
    if (has.has('text_color') && s.text_color_hover) hover.color = s.text_color_hover;
    if (has.has('opacity') && !empty(s.opacity_hover)) hover.opacity = String(s.opacity_hover / 100);
    // Effet au survol (agrandir, soulever…) : la transformation suit la même transition.
    const effect = has.has('hover_effect') && !empty(s.hover_effect) ? s.hover_effect : null;
    if (Object.keys(hover).length || (has.has('transition') && !empty(s.transition)) || effect) {
        const ms = parseInt(s.transition ?? 300, 10);
        const ease = has.has('transition_easing') && !empty(s.transition_easing) ? ` ${EASING[s.transition_easing]}` : '';
        p.transition = ['background-color', 'color', 'border-color', 'box-shadow', 'opacity', ...(effect ? ['transform'] : [])].map((prop) => `${prop} ${ms}ms${ease}`).join(', ');
    }
    // Apparition à l'écran : durée, délai et courbe (l'animation elle-même est dans site.css).
    if (has.has('entrance') && !empty(s.entrance)) {
        p['--gp-anim-dur'] = !empty(s.entrance_duration) ? `${s.entrance_duration}ms` : null;
        p['--gp-anim-delay'] = !empty(s.entrance_delay) ? `${s.entrance_delay}ms` : null;
        p['--gp-anim-ease'] = !empty(s.entrance_easing) ? EASING[s.entrance_easing] : null;
    }

    sheet.rule(id, p);
    sheet.rule(id, hover, 'desktop', ':hover');
    // Apparence une fois collé (classe posée par le script du site).
    if (sticky) {
        sheet.rule(id, { 'background-color': s.sticky_bg || null, 'box-shadow': s.sticky_shadow ? '0 10px 30px -12px rgba(15,23,42,.35)' : null }, 'desktop', '.is-stuck');
    }
    // CSS personnalisé, après les réglages : il peut les compléter ou les remplacer.
    if (has.has('custom_css') && !empty(s.custom_css)) {
        const css = cleanCustomCss(s.custom_css);
        if (css !== null) sheet.raw(scopeCustomCss(css, `#gp-${id}`));
    }
}

/* CSS personnalisé d'un élément : miroir exact de App\Builder\CustomCss. */
export const CUSTOM_CSS_MAX = 10000;
const CSS_FORBIDDEN = /[<\\]|@import|@charset|@namespace|expression\s*\(|javascript:|vbscript:|behavior\s*:|-moz-binding/i;
const CSS_SELECTOR = /(?<![\w-])selector(?![\w-])/g;

/** CSS accepté (commentaires retirés), ou null s'il contient une instruction interdite ou des accolades déséquilibrées. */
export function cleanCustomCss(value) {
    if (typeof value !== 'string') return null;
    const css = value.replace(/\/\*[\s\S]*?\*\//g, '').trim();
    if (css === '' || new TextEncoder().encode(css).length > CUSTOM_CSS_MAX || CSS_FORBIDDEN.test(css)) return null;
    let depth = 0;
    for (const char of css) {
        depth += char === '{' ? 1 : char === '}' ? -1 : 0;
        if (depth < 0 || depth > 2) return null;
    }
    return depth === 0 ? css : null;
}

/** Règles limitées à l'élément désigné par « selector » (#gp-…). */
export function scopeCustomCss(css, selector, nested = false) {
    if (!nested && !css.includes('{')) return `${selector}{${css.trim()}}`;
    let out = '';
    let i = 0;
    while (i < css.length) {
        const open = css.indexOf('{', i);
        if (open < 0) break;
        // Ce qui précède la règle (déclarations sans sélecteur…) est ignoré.
        const prelude = css.slice(i, open).replace(/^[\s\S]*[;}]/, '').trim();
        let depth = 1;
        let j = open + 1;
        while (j < css.length && depth > 0) {
            depth += css[j] === '{' ? 1 : css[j] === '}' ? -1 : 0;
            j++;
        }
        const body = css.slice(open + 1, j - 1);
        i = j;
        if (!nested && /^@(media|supports)\b/i.test(prelude)) {
            const inner = scopeCustomCss(body, selector, true);
            if (inner !== '') out += `${prelude}{${inner}}`;
            continue;
        }
        if (prelude === '' || prelude.startsWith('@') || body.includes('{')) continue;
        const selectors = prelude.split(',').map((p) => p.trim()).filter(Boolean)
            .map((p) => (p.match(CSS_SELECTOR) ? p.replace(CSS_SELECTOR, selector) : `${selector} ${p}`));
        if (selectors.length) out += `${selectors.join(',')}{${body.trim()}}`;
    }
    return out;
}

/** Disposition flexbox d'un conteneur (ElementStyle::container). */
export function containerLayout(sheet, node, depth) {
    const s = node.settings;
    const boxed = (s.content_width ?? containerDefaults(depth).content_width) === 'boxed';
    let previousWrap = 'nowrap';
    for (const [device, sfx] of Object.entries(DEVICES)) {
        // Retour à la ligne seulement en ligne (miroir de ElementStyle::container).
        const wrap = String(at(s, 'direction', device) ?? 'column').startsWith('row') ? (at(s, 'wrap', device) ?? 'nowrap') : 'nowrap';
        const p = {
            'flex-direction': s[`direction${sfx}`],
            'flex-wrap': wrap !== previousWrap ? wrap : null,
            'justify-content': s[`justify${sfx}`] ? FLEX[s[`justify${sfx}`]] : null,
            'align-items': s[`align${sfx}`] ? FLEX[s[`align${sfx}`]] : null,
            gap: empty(s[`gap${sfx}`]) ? null : `${s[`gap${sfx}`]}px`,
            'max-width': boxed && !empty(s[`max_width${sfx}`]) ? `${s[`max_width${sfx}`]}px` : null,
        };
        if (device === 'desktop') p['max-width'] = boxed ? `${s.max_width ?? 1140}px` : 'none';
        previousWrap = wrap;
        sheet.rule(node.id, p, device, ' > .gp-con-inner');
    }
    if (s.overlay_color) {
        sheet.rule(node.id, { background: s.overlay_color, opacity: String((s.overlay_opacity ?? 50) / 100) }, 'desktop', ' > .gp-overlay');
    }
}

/** Réglages communs qu'un widget ne redéfinit pas (WidgetRegistry::styleKeys). */
export function widgetStyleKeys(type) {
    const own = new Set((getWidget(type)?.controls ?? []).filter((c) => !c.common).map((c) => c.key));
    return Object.keys(STYLE_KEYS).filter((k) => !own.has(k));
}

/** CSS complet du contenu (Renderer). */
export function buildCss(content) {
    const sheet = new Sheet();
    const walk = (nodes, parent, depth) => {
        for (const node of nodes) {
            if (node.type === 'container') {
                applyStyle(sheet, node, parent, Object.keys(STYLE_KEYS));
                containerLayout(sheet, node, depth);
                walk(node.children ?? [], node, depth + 1);
            } else {
                applyStyle(sheet, node, parent, widgetStyleKeys(node.type));
                widgetCss(sheet, node);
            }
        }
    };
    walk(content.elements ?? [], null, 0);
    return { css: sheet.toString(), fonts: [...sheet.fonts] };
}

const px = (v) => (empty(v) ? null : `${v}px`);

function widgetCss(sheet, w) {
    const def = getWidget(w.type);
    if (!def?.css) return;
    const sel = `#gp-${w.id}`;
    try {
        def.css(w.settings, {
            sel,
            px,
            rule: (selector, props) => sheet.add('desktop', selector, props),
            tablet: (selector, props) => sheet.add('tablet', selector, props),
            mobile: (selector, props) => sheet.add('mobile', selector, props),
            responsive: (key, fn, suffix = '') => sheet.responsive(w.id, w.settings, key, fn, suffix),
            typography: (suffix = '') => sheet.typography(w.id, w.settings, suffix),
        });
    } catch (e) {
        console.error(`[GeniusPress] CSS du widget « ${w.type} » :`, e);
    }
}
