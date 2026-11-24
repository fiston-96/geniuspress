// Améliorations du site public, sans dépendance. Tout reste lisible et utilisable
// sans JavaScript : compteurs (chiffre final déjà écrit), onglets (panneaux
// visibles les uns sous les autres), galerie (liens vers les images).

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// ─── Compteurs : animés quand ils apparaissent à l'écran ─────────────────
const SEPARATORS = { space: ' ', comma: ',', dot: '.', none: '' };
const format = (n, sep) => Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, SEPARATORS[sep] ?? ' ');

function animateCounter(el) {
    const end = Number(el.dataset.gpCounter);
    const start = Number(el.dataset.start || 0);
    const duration = Number(el.dataset.duration || 2000);
    const sep = el.dataset.separator;
    if (reduceMotion || duration === 0 || end === start) return;
    const t0 = performance.now();
    const step = (now) => {
        const p = Math.min(1, (now - t0) / duration);
        const eased = 1 - (1 - p) ** 3;
        el.textContent = format(start + (end - start) * eased, sep);
        if (p < 1) requestAnimationFrame(step);
    };
    el.textContent = format(start, sep);
    requestAnimationFrame(step);
}

const counters = document.querySelectorAll('[data-gp-counter]');
if (counters.length && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                io.unobserve(entry.target);
                animateCounter(entry.target);
            }
        }
    }, { threshold: 0.4 });
    counters.forEach((el) => io.observe(el));
}

// ─── Comptes à rebours ───────────────────────────────────────────────────
for (const box of document.querySelectorAll('[data-gp-countdown]')) {
    const target = Date.parse(box.dataset.gpCountdown);
    const units = Object.fromEntries([...box.querySelectorAll('[data-unit]')].map((el) => [el.dataset.unit, el]));
    const tick = () => {
        const left = Math.max(0, Math.floor((target - Date.now()) / 1000));
        const values = { d: Math.floor(left / 86400), h: Math.floor((left % 86400) / 3600), m: Math.floor((left % 3600) / 60), s: left % 60 };
        for (const [unit, el] of Object.entries(units)) el.textContent = String(values[unit]).padStart(2, '0');
        if (left === 0) {
            clearInterval(timer);
            if (box.dataset.expired) {
                const p = document.createElement('p');
                p.className = 'gp-countdown__expired';
                p.textContent = box.dataset.expired;
                box.replaceChildren(p);
            }
        }
    };
    const timer = setInterval(tick, 1000);
    tick();
}

// ─── Onglets accessibles (rôles ARIA, flèches du clavier) ────────────────
document.querySelectorAll('[data-gp-tabs]').forEach((root, n) => {
    const panels = [...root.querySelectorAll(':scope > .gp-tabs__panel')];
    if (panels.length < 2) return;
    const list = document.createElement('div');
    list.className = 'gp-tabs__list';
    list.setAttribute('role', 'tablist');
    const tabs = panels.map((panel, i) => {
        const title = panel.querySelector('.gp-tabs__title');
        const tab = document.createElement('button');
        tab.type = 'button';
        tab.className = 'gp-tabs__tab';
        tab.id = `gp-tab-${n}-${i}`;
        tab.textContent = title?.textContent ?? String(i + 1);
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-controls', `gp-tabpanel-${n}-${i}`);
        panel.id = `gp-tabpanel-${n}-${i}`;
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', tab.id);
        panel.tabIndex = 0;
        list.appendChild(tab);
        return tab;
    });
    const select = (i, focus = false) => tabs.forEach((tab, j) => {
        tab.setAttribute('aria-selected', String(i === j));
        tab.tabIndex = i === j ? 0 : -1;
        panels[j].hidden = i !== j;
        if (i === j && focus) tab.focus();
    });
    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => select(i));
        tab.addEventListener('keydown', (e) => {
            const delta = { ArrowRight: 1, ArrowLeft: -1, Home: -i, End: tabs.length - 1 - i }[e.key];
            if (delta === undefined) return;
            e.preventDefault();
            select((i + delta + tabs.length) % tabs.length, true);
        });
    });
    root.prepend(list);
    root.classList.add('is-ready');
    select(0);
});

// ─── Galerie : visionneuse plein écran ───────────────────────────────────
document.querySelectorAll('[data-gp-lightbox]').forEach((gallery) => {
    const links = [...gallery.querySelectorAll('a')];
    links.forEach((link, start) => link.addEventListener('click', (e) => {
        e.preventDefault();
        let index = start;
        const opener = document.activeElement;
        const box = document.createElement('div');
        box.className = 'gp-lightbox';
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-modal', 'true');
        const img = document.createElement('img');
        const button = (cls, label, text) => {
            const b = document.createElement('button');
            b.className = cls;
            b.type = 'button';
            b.setAttribute('aria-label', label);
            b.textContent = text;
            return b;
        };
        const lang = document.documentElement.lang;
        const fr = lang.startsWith('fr');
        const close = button('gp-lightbox__close', fr ? 'Fermer' : 'Close', '×');
        const prev = button('gp-lightbox__prev', fr ? 'Image précédente' : 'Previous image', '‹');
        const next = button('gp-lightbox__next', fr ? 'Image suivante' : 'Next image', '›');
        const show = (i) => {
            index = (i + links.length) % links.length;
            img.src = links[index].href;
            img.alt = links[index].querySelector('img')?.alt ?? '';
        };
        const end = () => {
            box.remove();
            document.removeEventListener('keydown', onKey);
            opener?.focus?.();
        };
        const onKey = (ev) => {
            if (ev.key === 'Escape') end();
            if (ev.key === 'ArrowRight') show(index + 1);
            if (ev.key === 'ArrowLeft') show(index - 1);
        };
        close.addEventListener('click', end);
        prev.addEventListener('click', () => show(index - 1));
        next.addEventListener('click', () => show(index + 1));
        box.addEventListener('click', (ev) => { if (ev.target === box) end(); });
        document.addEventListener('keydown', onKey);
        box.append(img, close);
        if (links.length > 1) box.append(prev, next);
        document.body.appendChild(box);
        show(index);
        close.focus();
    }));
});
