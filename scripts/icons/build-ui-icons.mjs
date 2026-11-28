// Construit resources/js/builder/ui-icons.json : les icônes de l'interface du builder
// (barre d'outils, onglets, menus). Distinctes de resources/builder/icons.json, qui
// contient les icônes proposées dans les widgets du site.
//
//   node scripts/icons/build-ui-icons.mjs
//
// Source : lucide-static (licence ISC, https://lucide.dev), dépendance de développement.
import { readFileSync, writeFileSync } from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const root = new URL('../../', import.meta.url);

const UI = [
    // Barre du haut
    'undo-2', 'redo-2', 'eye', 'settings-2', 'monitor', 'tablet', 'smartphone', 'panel-left-close', 'panel-left-open', 'keyboard',
    // Onglets du panneau
    'square-plus', 'sliders-horizontal', 'list-tree', 'sparkles', 'library-big', 'history',
    // Onglets des réglages
    'pencil', 'palette', 'settings',
    // Actions sur un élément
    'copy', 'clipboard-paste', 'paintbrush', 'square-dashed', 'copy-plus', 'trash-2', 'bookmark-plus', 'move', 'plus', 'code',
    // Divers
    'search', 'x', 'chevron-right', 'chevron-down', 'chevron-up', 'braces', 'bold', 'italic', 'underline', 'list', 'list-ordered', 'link', 'remove-formatting', 'layout-template', 'refresh-cw', 'unlink', 'type', 'archive-restore', 'mouse-pointer-2', 'square', 'grip-vertical', 'box',
    // Administration et espace client (composant Blade <x-icon>)
    'layout-dashboard', 'file-text', 'newspaper', 'image', 'inbox', 'users', 'puzzle', 'hard-drive-download', 'globe', 'server',
    'credit-card', 'gift', 'bell', 'log-out', 'external-link', 'menu', 'user', 'circle-help', 'receipt', 'key-round', 'shopping-bag',
    'rocket', 'wand-sparkles', 'circle-check', 'circle', 'check', 'ellipsis', 'languages', 'house', 'layers', 'files', 'mail',
    'chart-no-axes-column', 'arrow-right', 'at-sign', 'shield-check', 'store', 'zap', 'clock', 'pen-line',
];

/** Contenu du SVG (formes Lucide), sans l'enveloppe ni le commentaire de licence. */
function inner(svg) {
    return svg.replace(/<!--[\s\S]*?-->/g, '').replace(/^[\s\S]*?<svg[^>]*>/, '').replace(/<\/svg>\s*$/, '')
        .replace(/\s*\n\s*/g, '').replace(/\s+\/>/g, '/>').trim();
}

const dir = require.resolve('lucide-static/package.json').replace(/package\.json$/, 'icons/');
const out = {};
for (const name of UI) out[name] = inner(readFileSync(`${dir}${name}.svg`, 'utf8'));

writeFileSync(new URL('resources/js/builder/ui-icons.json', root), `${JSON.stringify(out, null, 0)}\n`);
console.log(`${Object.keys(out).length} icônes d'interface écrites.`);
