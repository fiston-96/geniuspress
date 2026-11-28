// Balises dynamiques ({{site.name}}, {{page.title}}…) : même liste que App\Builder\DynamicTags.
// Dans le canevas, elles sont remplacées par des valeurs d'exemple ; sur le site, par les vraies.
import { getState } from './store';
import { t } from './i18n';

export const TAGS = [
    { group: t('Site'), tags: [['site.name', t('Nom du site')], ['site.tagline', t('Slogan du site')], ['site.url', t('Adresse du site')]] },
    { group: t('Page affichée'), tags: [['page.title', t('Titre de la page')], ['page.url', t('Adresse de la page')], ['archive.title', t('Titre de l’archive')], ['search.query', t('Texte recherché')]] },
    { group: t('Article'), tags: [['post.title', t('Titre de l’article')], ['post.excerpt', t('Résumé de l’article')], ['post.date', t('Date de l’article')], ['post.author', t('Auteur de l’article')], ['post.category', t('Catégorie de l’article')]] },
    { group: t('Date'), tags: [['date.year', t('Année en cours')], ['date.today', t('Date du jour')]] },
];

const KNOWN = new Set(TAGS.flatMap((g) => g.tags.map(([k]) => k)));
const PATTERN = /\{\{\s*([a-z]+\.[a-z_]+)\s*\}\}/g;

/** Valeurs d'exemple pour l'aperçu du builder. */
function sampleValues() {
    const { siteSettings = {}, pageTitle, sample } = getState();
    const post = sample?.posts?.[0] ?? {};
    const today = new Date();
    return {
        'site.name': siteSettings.site_name ?? '',
        'site.tagline': siteSettings.site_tagline ?? '',
        'site.url': window.location.origin,
        'page.title': pageTitle ?? '',
        'page.url': window.location.origin,
        'archive.title': t('Blog'),
        'search.query': t('recherche'),
        'post.title': post.title ?? t('Titre de l’article'),
        'post.excerpt': post.excerpt ?? '',
        'post.date': post.date ?? today.toLocaleDateString(),
        'post.author': post.author ?? '',
        'post.category': post.category ?? '',
        'date.year': String(today.getFullYear()),
        'date.today': today.toLocaleDateString(undefined, { day: 'numeric', month: 'long', year: 'numeric' }),
    };
}

/** Réglages d'un widget avec les balises remplacées (textes, listes), pour l'aperçu. */
export function resolveTags(settings) {
    let values = null;
    const walk = (v) => {
        if (typeof v === 'string') {
            if (!v.includes('{{')) return v;
            return v.replace(PATTERN, (all, key) => {
                if (!KNOWN.has(key)) return all;
                values ??= sampleValues();
                return values[key] ?? '';
            });
        }
        if (Array.isArray(v)) return v.map(walk);
        if (v && typeof v === 'object') return Object.fromEntries(Object.entries(v).map(([k, x]) => [k, walk(x)]));
        return v;
    };
    return walk(settings);
}

export const hasTags = (settings) => JSON.stringify(settings).includes('{{');
