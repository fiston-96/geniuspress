// Graphique du builder : même SVG que ChartWidget.php (parse, scale, number, axes, hbars, slices).
const W = 640;
const HEIGHTS = { '2:1': 320, '16:9': 360, '4:3': 480, '1:1': 640 };
const e = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
const r = (v) => String(Math.round(v * 10) / 10);
const ROUND = ['pie', 'doughnut'];

export function parseChart(data) {
    const rows = [];
    for (const line of String(data ?? '').trim().split(/\r\n|\r|\n/)) {
        if (line.trim() === '' || rows.length >= 60) continue;
        const cells = line.split('|').map((c) => c.trim());
        const values = cells.slice(1, 5).map((c) => {
            const n = c.replace(/[\s  ]/g, '').replace(/,/g, '.');
            return n !== '' && !Number.isNaN(Number(n)) ? Math.max(0, Number(n)) : 0;
        });
        rows.push({ label: cells[0], values: values.length ? values : [0] });
    }
    return rows;
}

function scale(max) {
    if (max <= 0) return [1, 4];
    const raw = max / 4;
    const mag = 10 ** Math.floor(Math.log10(raw));
    const norm = raw / mag;
    const step = (norm <= 1 ? 1 : norm <= 2 ? 2 : norm <= 2.5 ? 2.5 : norm <= 5 ? 5 : 10) * mag;
    return [step, Math.ceil(max / step - 1e-9) * step];
}

export function chartNumber(v, locale) {
    v = Math.round(v * 100) / 100;
    const d = v === Math.floor(v) ? 0 : Math.round(v * 10) / 10 === v ? 1 : 2;
    const [int, frac] = v.toFixed(d).split('.');
    const grouped = int.replace(/\B(?=(\d{3})+(?!\d))/g, locale === 'fr' ? ' ' : ',');
    return frac ? `${grouped}${locale === 'fr' ? ',' : '.'}${frac}` : grouped;
}

function axes(rows, count, kind, s, fmt) {
    const h = HEIGHTS[s.ratio ?? '16:9'];
    const [left, right, top, bottom] = [56, W - 12, 22, h - 34];
    const max = Math.max(...rows.map((row) => Math.max(...row.values.slice(0, count))));
    const [step, scaleTop] = scale(max);
    const y = (v) => bottom - (v / scaleTop) * (bottom - top);
    let out = '';
    for (let v = 0, i = 0; v <= scaleTop + 1e-9 && i < 12; v += step, i++) {
        out += (s.show_grid !== false || v === 0 ? `<line class="gp-chart__grid" x1="${left}" x2="${right}" y1="${r(y(v))}" y2="${r(y(v))}"/>` : '')
            + `<text class="gp-chart__axis" x="${left - 8}" y="${r(y(v) + 4)}" text-anchor="end">${e(fmt(v))}</text>`;
    }
    const n = rows.length;
    const band = (right - left) / n;
    rows.forEach((row, i) => {
        out += `<text class="gp-chart__axis" x="${r(left + band * (i + 0.5))}" y="${bottom + 22}" text-anchor="middle">${e(row.label)}</text>`;
    });
    if (kind === 'bar') {
        const inner = band * 0.72;
        const bw = inner / count;
        rows.forEach((row, i) => {
            for (let k = 0; k < count; k++) {
                const v = row.values[k] ?? 0;
                const x = left + band * i + (band - inner) / 2 + k * bw;
                out += `<rect class="gp-chart__c${k + 1}" x="${r(x + (count > 1 ? 1 : 0))}" y="${r(y(v))}" width="${r(bw - (count > 1 ? 2 : 0))}" height="${r(bottom - y(v))}" rx="3" fill="currentColor"/>`;
                if (s.show_values !== false) out += `<text class="gp-chart__value" x="${r(x + bw / 2)}" y="${r(y(v) - 6)}" text-anchor="middle">${e(fmt(v))}</text>`;
            }
        });
        return out;
    }
    for (let k = 0; k < count; k++) {
        const points = rows.map((row, i) => [r(left + band * (i + 0.5)), r(y(row.values[k] ?? 0)), row.values[k] ?? 0]);
        const line = `M${points.map((p) => `${p[0]} ${p[1]}`).join(' L')}`;
        if (kind === 'area') {
            out += `<path class="gp-chart__c${k + 1}" d="${line} L${points[points.length - 1][0]} ${bottom} L${points[0][0]} ${bottom} Z" fill="currentColor" fill-opacity=".14"/>`;
        }
        out += `<path class="gp-chart__c${k + 1}" d="${line}" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>`;
        for (const [px, py, v] of points) {
            out += `<circle class="gp-chart__c${k + 1}" cx="${px}" cy="${py}" r="4.5" fill="currentColor"/>`
                + (s.show_values !== false ? `<text class="gp-chart__value" x="${px}" y="${r(Number(py) - 10)}" text-anchor="middle">${e(fmt(v))}</text>` : '');
        }
    }
    return out;
}

function hbars(rows, count, s, fmt) {
    const h = HEIGHTS[s.ratio ?? '16:9'];
    const [left, right, top, bottom] = [150, W - 70, 6, h - 6];
    const max = Math.max(...rows.map((row) => Math.max(...row.values.slice(0, count))));
    const scaleTop = scale(max)[1];
    const band = (bottom - top) / rows.length;
    const inner = Math.min(band * 0.7, 26 * count);
    const bh = inner / count;
    let out = `<line class="gp-chart__grid" x1="${left}" x2="${left}" y1="${top}" y2="${bottom}"/>`;
    rows.forEach((row, i) => {
        const label = row.label.length > 22 ? `${row.label.slice(0, 21)}…` : row.label;
        out += `<text class="gp-chart__axis" x="${left - 10}" y="${r(top + band * (i + 0.5) + 4)}" text-anchor="end">${e(label)}</text>`;
        for (let k = 0; k < count; k++) {
            const v = row.values[k] ?? 0;
            const y = top + band * i + (band - inner) / 2 + k * bh;
            const w = (v / scaleTop) * (right - left);
            out += `<rect class="gp-chart__c${k + 1}" x="${left}" y="${r(y + (count > 1 ? 1 : 0))}" width="${r(w)}" height="${r(bh - (count > 1 ? 2 : 0))}" rx="3" fill="currentColor"/>`;
            if (s.show_values !== false) out += `<text class="gp-chart__value" x="${r(left + w + 6)}" y="${r(y + bh / 2 + 4)}">${e(fmt(v))}</text>`;
        }
    });
    return out;
}

function slices(rows, kind) {
    const total = rows.reduce((sum, row) => sum + row.values[0], 0);
    if (total <= 0) return '<circle class="gp-chart__grid" cx="160" cy="160" r="150" fill="none"/>';
    const [outer, inner] = [150, kind === 'doughnut' ? 90 : 0];
    const point = (turn, radius) => `${r(160 + radius * Math.sin(2 * Math.PI * turn))} ${r(160 - radius * Math.cos(2 * Math.PI * turn))}`;
    let out = '';
    let start = 0;
    rows.forEach((row, i) => {
        const part = row.values[0] / total;
        if (part <= 0) return;
        let d;
        if (part >= 0.9999) {
            d = `M10 160 A150 150 0 1 1 310 160 A150 150 0 1 1 10 160 Z${inner ? ' M70 160 A90 90 0 1 0 250 160 A90 90 0 1 0 70 160 Z' : ''}`;
        } else {
            const large = part > 0.5 ? 1 : 0;
            const end = start + part;
            d = inner
                ? `M${point(start, outer)} A${outer} ${outer} 0 ${large} 1 ${point(end, outer)} L${point(end, inner)} A${inner} ${inner} 0 ${large} 0 ${point(start, inner)} Z`
                : `M160 160 L${point(start, outer)} A${outer} ${outer} 0 ${large} 1 ${point(end, outer)} Z`;
        }
        out += `<path class="gp-chart__slice gp-chart__c${(i % 6) + 1}" d="${d}" fill="currentColor" fill-rule="evenodd"/>`;
        start += part;
    });
    return out;
}

/** ChartWidget::unit : « FC 500 », « $500 » ; « 45 % » et « 500 $ » en français, « 45% » sinon. */
export function chartUnit(v, prefix, suffix, locale) {
    const before = prefix !== '' && /\p{L}$/u.test(prefix) ? `${prefix} ` : prefix;
    const after = suffix !== '' && (locale === 'fr' || /^\p{L}/u.test(suffix)) ? ` ${suffix}` : suffix;
    return `${before}${chartNumber(v, locale)}${after}`;
}

/** Contenu de <figure class="gp-chart"> (ChartWidget::render), ou null sans données. */
export function chartHtml(s, locale) {
    const rows = parseChart(s.data);
    if (!rows.length) return null;
    const kind = s.kind ?? 'bar';
    const round = ROUND.includes(kind);
    const fmt = (v) => chartUnit(v, s.prefix ?? '', s.suffix ?? '', locale);
    const series = String(s.series ?? '').split('|').map((n) => n.trim()).filter((n) => n !== '');
    const count = round ? 1 : Math.min(4, Math.max(...rows.map((row) => row.values.length)));
    const svg = round ? slices(rows, kind) : kind === 'hbar' ? hbars(rows, count, s, fmt) : axes(rows, count, kind, s, fmt);

    let legend = '';
    if (s.show_legend !== false) {
        if (round) {
            const total = rows.reduce((sum, row) => sum + row.values[0], 0) || 1;
            rows.forEach((row, i) => {
                legend += `<li><span class="gp-chart__swatch gp-chart__c${(i % 6) + 1}"></span>${e(row.label)}`
                    + (s.show_values !== false ? ` <span class="gp-chart__legend-value">${e(fmt(row.values[0]))} · ${chartUnit((row.values[0] / total) * 100, '', '%', locale)}</span>` : '') + '</li>';
            });
        } else if (series.length) {
            series.slice(0, count).forEach((name, k) => { legend += `<li><span class="gp-chart__swatch gp-chart__c${k + 1}"></span>${e(name)}</li>`; });
        }
    }
    const [width, height] = round ? [320, 320] : [W, HEIGHTS[s.ratio ?? '16:9']];
    const title = s.title ?? '';
    return (title !== '' ? `<figcaption class="gp-chart__title">${e(title)}</figcaption>` : '')
        + `<svg class="gp-chart__svg" viewBox="0 0 ${width} ${height}" aria-hidden="true" focusable="false">${svg}</svg>`
        + (legend !== '' ? `<ul class="gp-chart__legend">${legend}</ul>` : '');
}
