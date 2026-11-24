// Construit resources/builder/icons.json : la sélection d'icônes livrée avec
// GeniusPress (widgets Icône, Boîte d'icône, Liste d'icônes, Réseaux sociaux…).
//
//   node scripts/icons/build-icons.mjs
//
// Sources (dépendances de développement, jamais livrées telles quelles) :
//   - lucide-static : icônes générales, licence ISC (https://lucide.dev)
//   - simple-icons  : logos des réseaux sociaux, licence CC0 (https://simpleicons.org)
// Les mentions de licence sont reprises dans resources/builder/ICONS-LICENSE.md.
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const root = new URL('../../', import.meta.url);

// Icônes générales, regroupées par usage (le nom est celui de Lucide).
const GENERAL = {
    base: ['check', 'circle-check', 'x', 'plus', 'minus', 'arrow-right', 'arrow-left', 'arrow-up-right', 'chevron-right', 'chevron-down', 'star', 'heart', 'info', 'triangle-alert', 'circle-help', 'quote', 'sparkles', 'zap', 'eye', 'search', 'link', 'external-link', 'download', 'upload', 'share-2', 'thumbs-up', 'smile', 'list', 'list-checks', 'sliders-horizontal', 'refresh-cw', 'log-in', 'bell', 'flag', 'target', 'lightbulb', 'rocket', 'award', 'trophy', 'medal', 'gem', 'crown'],
    contact: ['phone', 'smartphone', 'mail', 'map-pin', 'map', 'compass', 'globe', 'clock', 'calendar', 'calendar-days', 'calendar-check', 'message-circle', 'message-square', 'send', 'megaphone', 'newspaper', 'rss', 'mic', 'headphones'],
    people: ['user', 'users', 'user-round', 'user-plus', 'users-round', 'baby', 'accessibility', 'hand-heart', 'heart-handshake', 'handshake', 'hand-helping', 'hand-coins', 'person-standing', 'contact'],
    places: ['house', 'building', 'building-2', 'landmark', 'store', 'factory', 'hotel', 'church', 'school', 'hospital', 'warehouse', 'tent', 'castle'],
    business: ['briefcase', 'shopping-cart', 'shopping-bag', 'credit-card', 'wallet', 'banknote', 'receipt', 'tag', 'percent', 'gift', 'package', 'truck', 'box', 'piggy-bank', 'coins', 'chart-column', 'chart-line', 'chart-pie', 'trending-up', 'file-text', 'file', 'folder', 'clipboard', 'clipboard-check', 'scale', 'gavel', 'vote', 'id-card', 'shield', 'shield-check', 'lock', 'key', 'ticket'],
    tech: ['laptop', 'monitor', 'server', 'database', 'cloud', 'code', 'cpu', 'wifi', 'settings', 'wrench', 'hammer', 'layers', 'layout-grid', 'bot', 'brain', 'workflow', 'git-branch', 'terminal', 'smartphone-nfc', 'qr-code'],
    education: ['book', 'book-open', 'book-open-text', 'graduation-cap', 'pencil', 'library', 'notebook-pen', 'calculator', 'microscope', 'flask-conical', 'atom', 'languages', 'presentation', 'backpack'],
    health: ['stethoscope', 'ambulance', 'pill', 'syringe', 'activity', 'heart-pulse', 'thermometer', 'bandage', 'bed', 'cross', 'hospital', 'clipboard-plus', 'dna', 'tooth', 'eye', 'brain-circuit'],
    art: ['palette', 'brush', 'paintbrush', 'pen-tool', 'scissors', 'sofa', 'lamp', 'frame', 'image', 'camera', 'video', 'film', 'music', 'play', 'shirt', 'watch', 'glasses', 'flower-2', 'armchair', 'drama'],
    nature: ['leaf', 'tree-pine', 'sprout', 'wheat', 'tractor', 'sun', 'moon', 'droplet', 'flame', 'recycle', 'mountain', 'waves', 'cloud-rain'],
    life: ['utensils', 'coffee', 'chef-hat', 'cake', 'party-popper', 'car', 'bus', 'plane', 'bike', 'dumbbell', 'gamepad-2', 'hard-hat', 'construction', 'hourglass', 'timer', 'history', 'church', 'candlestick-chart'],
};

// Logos de réseaux sociaux et services (nom simple-icons → identifiant GeniusPress).
const BRANDS = {
    facebook: 'siFacebook', instagram: 'siInstagram', x: 'siX', youtube: 'siYoutube', whatsapp: 'siWhatsapp', tiktok: 'siTiktok',
    telegram: 'siTelegram', pinterest: 'siPinterest', snapchat: 'siSnapchat', github: 'siGithub', threads: 'siThreads',
    spotify: 'siSpotify', soundcloud: 'siSoundcloud', vimeo: 'siVimeo', mastodon: 'siMastodon', discord: 'siDiscord',
    messenger: 'siMessenger', signal: 'siSignal', wechat: 'siWechat', linkedin: 'siLinkedin', reddit: 'siReddit', bluesky: 'siBluesky',
};

/** Garde uniquement les formes SVG simples et leurs attributs géométriques. */
function inner(svg) {
    const body = svg.replace(/^[\s\S]*?<svg[^>]*>/, '').replace(/<\/svg>\s*$/, '');
    const allowed = /^(path|circle|rect|line|polyline|polygon|ellipse)$/;
    const attrs = /^(d|cx|cy|r|rx|ry|x|y|x1|y1|x2|y2|width|height|points)$/;
    const out = [];
    for (const m of body.matchAll(/<([a-z]+)([^>]*?)\/?>/g)) {
        if (!allowed.test(m[1])) continue;
        const kept = [...m[2].matchAll(/([a-z0-9-]+)="([^"]*)"/g)].filter(([, k]) => attrs.test(k)).map(([, k, v]) => `${k}="${v}"`);
        out.push(`<${m[1]} ${kept.join(' ')}/>`);
    }
    return out.join('');
}

const icons = { lucide: {}, brands: {}, groups: {} };
const missing = [];
for (const [group, names] of Object.entries(GENERAL)) {
    icons.groups[group] = [];
    for (const name of [...new Set(names)]) {
        const file = new URL(`node_modules/lucide-static/icons/${name}.svg`, root);
        if (!existsSync(file)) { missing.push(name); continue; }
        icons.lucide[name] ??= inner(readFileSync(file, 'utf8'));
        icons.groups[group].push(name);
    }
}
const si = require('simple-icons');
for (const [id, key] of Object.entries(BRANDS)) {
    const icon = si[key];
    if (!icon) { missing.push(`brand:${id}`); continue; }
    icons.brands[id] = { title: icon.title, hex: `#${icon.hex}`, path: icon.path };
}

writeFileSync(new URL('resources/builder/icons.json', root), JSON.stringify(icons));
console.log(`${Object.keys(icons.lucide).length} icônes, ${Object.keys(icons.brands).length} logos`);
if (missing.length) console.log('Introuvables :', missing.join(', '));
