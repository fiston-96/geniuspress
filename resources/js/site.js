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

// ─── Diaporamas et carrousels : flèches, points, défilement automatique ──
// Sans JavaScript, les diapositives défilent à la main (défilement aimanté).
for (const slider of document.querySelectorAll('[data-gp-slider]')) {
    const track = slider.querySelector('.gp-slides');
    const items = [...track.children];
    if (items.length < 2) continue;
    const prev = slider.querySelector('[data-gp-prev]');
    const next = slider.querySelector('[data-gp-next]');
    const dotsBox = slider.querySelector('[data-gp-dots]');
    const perView = () => Math.max(1, Math.round(track.clientWidth / (items[0].getBoundingClientRect().width || track.clientWidth)));
    const pages = () => Math.max(1, items.length - perView() + 1);
    const current = () => Math.round(track.scrollLeft / ((items[1]?.offsetLeft ?? 0) - items[0].offsetLeft || track.clientWidth));
    const go = (i) => {
        const n = pages();
        const target = items[((i % n) + n) % n];
        track.scrollTo({ left: target.offsetLeft - items[0].offsetLeft, behavior: reduceMotion ? 'auto' : 'smooth' });
    };
    let dots = [];
    const render = () => {
        const n = pages();
        if (dotsBox && dots.length !== n) {
            dotsBox.replaceChildren(...Array.from({ length: n }, (_, i) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.setAttribute('aria-label', String(i + 1));
                b.addEventListener('click', () => go(i));
                return b;
            }));
            dots = [...dotsBox.children];
        }
        const c = Math.min(current(), n - 1);
        dots.forEach((d, i) => d.setAttribute('aria-current', i === c ? 'true' : 'false'));
        const hide = n < 2;
        [prev, next, dotsBox].forEach((el) => el && (el.hidden = hide));
    };
    prev?.addEventListener('click', () => go(current() - 1));
    next?.addEventListener('click', () => go(current() + 1));
    track.addEventListener('scroll', () => requestAnimationFrame(render), { passive: true });
    window.addEventListener('resize', render);
    render();

    const delay = Number(slider.dataset.autoplay || 0) * 1000;
    if (delay && !reduceMotion) {
        let timer = null;
        const start = () => { stop(); timer = setInterval(() => go(current() + 1), delay); };
        const stop = () => timer && clearInterval(timer);
        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
        slider.addEventListener('focusin', stop);
        slider.addEventListener('focusout', start);
        start();
    }
}

// ─── Avant / après ───────────────────────────────────────────────────────
for (const box of document.querySelectorAll('[data-gp-ba]')) {
    const range = box.querySelector('.gp-ba__range');
    range?.addEventListener('input', () => box.style.setProperty('--gp-ba', `${range.value}%`));
}

// ─── Partage : copier le lien de la page ─────────────────────────────────
for (const btn of document.querySelectorAll('[data-gp-copy]')) {
    if (!navigator.clipboard) continue;
    btn.hidden = false;
    btn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(btn.dataset.gpCopy);
            btn.classList.add('is-done');
            btn.setAttribute('title', btn.dataset.done || '');
            setTimeout(() => btn.classList.remove('is-done'), 2000);
        } catch { /* copie refusée : rien à faire */ }
    });
}

// ─── Retour en haut et progression de la lecture ─────────────────────────
const toTop = [...document.querySelectorAll('[data-gp-totop]')];
const bars = [...document.querySelectorAll('[data-gp-reading]')];
bars.forEach((b) => { b.hidden = false; });
toTop.forEach((a) => a.addEventListener('click', (e) => { e.preventDefault(); window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }); }));
if (toTop.length || bars.length) {
    const onScroll = () => {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        const y = window.scrollY;
        toTop.forEach((a) => a.classList.toggle('is-hidden', y < 400));
        bars.forEach((b) => { b.style.width = `${max > 0 ? Math.min(100, (y / max) * 100) : 0}%`; });
    };
    window.addEventListener('scroll', () => requestAnimationFrame(onScroll), { passive: true });
    onScroll();
}

// ─── Playlist vidéo ──────────────────────────────────────────────────────
for (const box of document.querySelectorAll('[data-gp-playlist]')) {
    const frame = box.querySelector('iframe');
    for (const link of box.querySelectorAll('[data-embed]')) {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            frame.src = link.dataset.embed;
            frame.title = link.querySelector('.gp-vplay__title')?.textContent ?? '';
            box.querySelectorAll('[aria-current]').forEach((a) => a.removeAttribute('aria-current'));
            link.setAttribute('aria-current', 'true');
        });
    }
}

// ─── Navigation de page : section affichée mise en évidence ──────────────
for (const nav of document.querySelectorAll('[data-gp-pagenav]')) {
    const links = [...nav.querySelectorAll('a[href^="#"]')];
    const targets = links.map((a) => document.getElementById(decodeURIComponent(a.hash.slice(1)))).filter(Boolean);
    if (!targets.length || !('IntersectionObserver' in window)) continue;
    const io = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            links.forEach((a) => a.toggleAttribute('aria-current', a.hash === `#${entry.target.id}`));
        }
    }, { rootMargin: '-45% 0px -50% 0px' });
    targets.forEach((t) => io.observe(t));
}

// ─── Animations d'apparition ─────────────────────────────────────────────
// Les éléments « gp-anim » sont cachés tant que <html> porte gp-js (posée dans <head>) ;
// ils apparaissent en arrivant à l'écran. « gp-anim--repeat » : rejouée à chaque passage.
window.gpAnimReady = true;
const animated = [...document.querySelectorAll('.gp-anim')];
if (animated.length) {
    if (reduceMotion || !('IntersectionObserver' in window)) {
        animated.forEach((el) => el.classList.add('is-in'));
    } else {
        const io = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                const el = entry.target;
                const repeat = el.classList.contains('gp-anim--repeat');
                if (entry.isIntersecting) {
                    el.classList.add('is-in');
                    if (!repeat) io.unobserve(el);
                } else if (repeat) {
                    el.classList.remove('is-in');
                }
            }
        }, { rootMargin: '0px 0px -8% 0px' });
        animated.forEach((el) => io.observe(el));
    }
}

// ─── Défilement : parallaxe, effets liés à la position, éléments collants ─
// Position de chaque élément dans l'écran (variables CSS --gp-sp et --gp-spe, voir site.css)
// et classe « is-stuck » quand un élément collant est collé. Rien ne bouge si le visiteur
// demande moins d'animations ; les éléments collants restent collants.
const moving = reduceMotion ? [] : [...document.querySelectorAll('.gp-sc, .gp-parallax')];
const stickies = [...document.querySelectorAll('.gp-sticky')];
if (moving.length || stickies.length) {
    let ticking = false;
    const update = () => {
        ticking = false;
        const vh = window.innerHeight;
        for (const el of moving) {
            const box = el.getBoundingClientRect();
            // Position sans le décalage déjà appliqué par l'effet (propriété « translate »).
            const shift = parseFloat((getComputedStyle(el).translate || '').split(' ')[1]) || 0;
            const r = { top: box.top - shift, bottom: box.bottom - shift, height: box.height };
            if (r.bottom < -200 || r.top > vh + 200) continue;
            const position = (top) => (vh / 2 - (top + r.height / 2)) / ((vh + r.height) / 2);
            // Un élément déjà visible à l'ouverture de la page part de sa position d'alors :
            // il n'est ni estompé ni décalé avant que le visiteur fasse défiler la page.
            const p = Math.max(-1, Math.min(1, position(r.top) - Math.max(0, position(r.top + window.scrollY))));
            el.style.setProperty('--gp-sp', p.toFixed(3));
            el.style.setProperty('--gp-spe', Math.max(0, (Math.abs(p) - 0.25) / 0.75).toFixed(3));
        }
        for (const el of stickies) {
            const cs = getComputedStyle(el);
            if (cs.position !== 'sticky') {
                el.classList.remove('is-stuck');
                continue;
            }
            const r = el.getBoundingClientRect();
            const stuck = el.classList.contains('gp-sticky--bottom')
                ? Math.abs(r.bottom - (vh - (parseFloat(cs.bottom) || 0))) < 1.5
                :window.scrollY > 0 && Math.abs(r.top - (parseFloat(cs.top) || 0)) < 1.5;
            el.classList.toggle('is-stuck', stuck);
        }
    };
    const schedule = () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    };
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', schedule);
    update();
}

// ─── Titres animés ───────────────────────────────────────────────────────
// Machine à écrire, mots qui se succèdent, surlignage dessiné à l'arrivée à l'écran.
// Sans script (ou si le visiteur demande moins d'animations), le premier mot reste affiché.
for (const h of document.querySelectorAll('[data-gp-ah]')) {
    const pause = Number(h.dataset.pause) || 2500;
    if (h.classList.contains('gp-ah--highlight')) {
        const draw = () => {
            h.classList.add('is-drawn');
            if (h.hasAttribute('data-loop') && !reduceMotion) {
                setInterval(() => {
                    h.classList.remove('is-drawn');
                    setTimeout(() => h.classList.add('is-drawn'), 400);
                }, pause + 1600);
            }
        };
        if (!('IntersectionObserver' in window)) {
            draw();
            continue;
        }
        const io = new IntersectionObserver((entries) => {
            if (entries.some((e) => e.isIntersecting)) {
                io.disconnect();
                setTimeout(draw, 200);
            }
        }, { rootMargin: '0px 0px -10% 0px' });
        io.observe(h);
        continue;
    }

    const spans = [...h.querySelectorAll('.gp-ah__word')];
    if (spans.length < 2 || reduceMotion) continue;
    let i = 0;
    if (h.classList.contains('gp-ah--rotate')) {
        setInterval(() => {
            const prev = spans[i];
            prev.classList.replace('is-on', 'is-off');
            setTimeout(() => prev.classList.remove('is-off'), 500);
            i = (i + 1) % spans.length;
            spans[i].classList.add('is-on');
        }, pause);
        continue;
    }

    // Machine à écrire : le premier mot sert d'affichage, les autres donnent le texte.
    const texts = spans.map((s) => s.textContent);
    const shown = spans[0];
    spans.slice(1).forEach((s) => s.remove());
    const delay = 170 - (Number(h.dataset.speed) || 5) * 14;
    const type = (word, n) => {
        shown.textContent = word.slice(0, n);
        if (n < word.length) setTimeout(() => type(word, n + 1), delay);
        else setTimeout(() => erase(word, word.length), pause);
    };
    const erase = (word, n) => {
        shown.textContent = word.slice(0, n);
        if (n > 0) setTimeout(() => erase(word, n - 1), delay / 2);
        else {
            i = (i + 1) % texts.length;
            setTimeout(() => type(texts[i], 1), 300);
        }
    };
    setTimeout(() => erase(texts[0], texts[0].length), pause);
}

// ─── Menu des gabarits d'en-tête (widget « Menu ») ───────────────────────
// Sans JavaScript, la case à cocher ouvre et ferme le menu mobile. Ici : il se
// referme après un clic sur un lien (ancres d'une page unique), avec Échap ou
// par un clic à l'extérieur.
document.querySelectorAll('.gp-menu').forEach((menu) => {
    const toggle = menu.querySelector('.gp-menu__toggle');
    if (!toggle) return;
    const close = () => { toggle.checked = false; };
    menu.querySelectorAll('.gp-menu__panel a').forEach((a) => a.addEventListener('click', close));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    document.addEventListener('click', (e) => { if (!menu.contains(e.target)) close(); });
});
