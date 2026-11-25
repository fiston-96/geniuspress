// Aperçu et CSS des widgets dynamiques du thème (widgets/theme.js).
// Le CSS est le miroir exact des méthodes render() de app/Builder/Widgets/*Widget.php ;
// l'aperçu utilise les articles du site (boot.sample), ou des exemples.
import { Icon } from '../icons';
import { t } from '../i18n';
import { getState } from '../store';
import { CATEGORY_OPTIONS } from './theme';

const JUSTIFY = { left: 'flex-start', center: 'center', right: 'flex-end' };
const sample = () => getState().sample ?? { post: { title: t('Titre de l’article') }, posts: [], menu: [], languages: [] };
const on = (v) => v !== false && v !== 0 && v !== '0';

/** Remplit les choix qui dépendent du site (catégories) : appelé au démarrage du builder. */
export function setThemeData(data) {
    CATEGORY_OPTIONS.splice(1, CATEGORY_OPTIONS.length, ...(data?.categories ?? []));
}

/** ThemeWidget::text : couleur, police des titres, alignement, taille, typographie. */
function textCss(o, h, scope = ' > *', heading = true) {
    h.rule(`${h.sel}${scope}`, { color: o.color, 'font-family': heading && !o.font_family ? 'var(--gp-heading-font)' : null });
    h.responsive('align', (v) => ({ 'text-align': v }), scope);
    h.responsive('font_size', (v) => ({ 'font-size': `${v}px` }), scope);
    h.typography(scope);
}

function PostCard({ p, o }) {
    return (
        <article className="gp-posts__item">
            {on(o.show_image) && (p.image
                ? <a className="gp-posts__media"><img src={p.image} alt="" draggable={false} /></a>
                : <a className="gp-posts__media"><span style={{ display: 'block', aspectRatio: (o.ratio ?? '16:9').replace(':', ' / '), background: 'linear-gradient(135deg, var(--gp-primary), var(--gp-secondary))', opacity: 0.25 }} /></a>)}
            <div className="gp-posts__body">
                {on(o.show_category) && p.category && <a className="gp-posts__category">{p.category}</a>}
                <h3 className="gp-posts__title"><a>{p.title}</a></h3>
                {on(o.show_date) && <p className="gp-posts__date"><time>{p.date}</time></p>}
                {on(o.show_excerpt) && <p className="gp-posts__excerpt">{p.excerpt}</p>}
                {on(o.show_more) && <a className="gp-posts__more">{o.more_text || t('Lire la suite')} <span aria-hidden="true">→</span></a>}
            </div>
        </article>
    );
}

export const THEME_IMPL = {
    'post-title': {
        preview: (o) => {
            const Tag = o.tag || 'h1';
            const title = sample().post.title;
            return <Tag>{o.link ? <a>{title}</a> : title}</Tag>;
        },
        css: (o, h) => textCss(o, h),
    },
    'post-content': {
        preview: () => (
            <div className="gp-post-content">
                <div className="gpe-empty" style={{ margin: 0, textAlign: 'left' }}>
                    <strong>{t('Contenu de l’article')}</strong>
                    <p style={{ margin: '6px 0 0' }}>{t('Ici s’affiche le texte de chaque article, tel qu’il a été écrit dans le builder.')}</p>
                </div>
            </div>
        ),
        css: (o, h) => {
            h.rule(h.sel, { color: o.color });
            h.responsive('font_size', (v) => ({ 'font-size': `${v}px` }));
        },
    },
    'post-image': {
        preview: (o) => {
            const src = sample().post.image;
            return src
                ? <img src={src} alt="" draggable={false} />
                : <div className="gpe-empty" style={{ margin: 0, aspectRatio: (o.ratio ?? '16:9') === 'auto' ? '16 / 9' : (o.ratio ?? '16:9').replace(':', ' / '), display: 'grid', placeItems: 'center' }}>{t('Image à la une de l’article')}</div>;
        },
        css: (o, h) => {
            const ratio = o.ratio ?? '16:9';
            h.rule(`${h.sel} img`, { 'aspect-ratio': ratio === 'auto' ? null : ratio.replace(':', ' / '), 'border-radius': h.px(o.radius) });
        },
    },
    'post-meta': {
        preview: (o) => {
            const p = sample().post;
            const icon = (name) => (on(o.icons) ? <Icon value={`lucide:${name}`} /> : null);
            const items = [];
            if (on(o.show_date) && p.date) items.push(<>{icon('calendar')}<time>{p.date}</time></>);
            if (on(o.show_author) && p.author) items.push(<>{icon('user-round')}<span>{p.author}</span></>);
            if (on(o.show_category) && p.category) items.push(<>{icon('folder')}<span><a>{p.category}</a></span></>);
            if (on(o.show_reading)) items.push(<>{icon('clock')}<span>{t(':count min de lecture', { count: p.minutes ?? 3 })}</span></>);
            if (o.show_tags) items.push(<>{icon('tag')}<span><a>#{t('étiquette')}</a></span></>);
            return <div className="gp-pmeta">{items.map((item, i) => <span key={i} className="gp-pmeta__item">{item}</span>)}</div>;
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-pmeta`, { color: o.color });
            h.rule(`${h.sel} .gp-pmeta a`, { color: o.link_color });
            h.responsive('align', (v) => ({ 'justify-content': JUSTIFY[v] }), ' .gp-pmeta');
            h.responsive('font_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-pmeta');
        },
    },
    'post-excerpt': {
        preview: (o) => {
            const text = sample().post.excerpt ?? '';
            const max = o.length ?? 220;
            return <p>{text.length > max ? `${text.slice(0, max)}...` : text}</p>;
        },
        css: (o, h) => textCss(o, h, ' p', false),
    },
    'post-nav': {
        preview: (o) => {
            const [prev, next] = sample().posts.slice(1, 3);
            return (
                <nav className="gp-postnav">
                    <a className="gp-postnav__prev"><small>← {t('Article précédent')}</small>{on(o.show_titles) && <span>{prev?.title ?? t('Un deuxième article')}</span>}</a>
                    <a className="gp-postnav__next"><small>{t('Article suivant')} →</small>{on(o.show_titles) && <span>{next?.title ?? t('Un troisième article')}</span>}</a>
                </nav>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-postnav a`, { color: o.color });
            h.rule(`${h.sel} .gp-postnav a:hover`, { color: o.color_hover });
        },
    },
    'archive-title': {
        preview: (o) => {
            const Tag = o.tag || 'h1';
            return (
                <div className="gp-atitle">
                    {on(o.show_prefix) && <p className="gp-atitle__prefix">{t('Catégorie')}</p>}
                    <Tag className="gp-atitle__title">{CATEGORY_OPTIONS[1]?.label ?? sample().blog_title ?? 'Blog'}</Tag>
                    {on(o.show_description) && <p className="gp-atitle__desc">{t('La description de la catégorie s’affiche ici.')}</p>}
                </div>
            );
        },
        css: (o, h) => {
            textCss(o, h, ' .gp-atitle__title');
            h.responsive('align', (v) => ({ 'text-align': v }), ' .gp-atitle');
        },
    },
    posts: {
        preview: (o) => {
            const list = sample().posts;
            const count = (o.source ?? 'current') === 'current' ? list.length : Math.min(o.count ?? 6, list.length);
            if (!count) return <p className="gp-posts__empty">{t('Aucun article pour le moment.')}</p>;
            const layout = o.layout ?? 'grid';
            return (
                <div className={`gp-posts gp-posts--${layout} gp-posts--${o.style ?? 'shadow'}`}>
                    {list.slice(0, count).map((p, i) => <PostCard key={i} p={p} o={o} />)}
                </div>
            );
        },
        css: (o, h) => {
            const layout = o.layout ?? 'grid';
            const columns = o.columns ?? 3;
            h.rule(`${h.sel} .gp-posts`, {
                'grid-template-columns': layout === 'grid' ? `repeat(${columns}, minmax(0, 1fr))` : null,
                gap: o.gap !== undefined && o.gap !== null ? `${o.gap}px` : null,
                '--gp-posts-accent': o.accent,
            });
            if (layout === 'grid') {
                h.tablet(`${h.sel} .gp-posts`, { 'grid-template-columns': `repeat(${o.columns_tablet ?? Math.min(2, columns)}, minmax(0, 1fr))` });
                h.mobile(`${h.sel} .gp-posts`, { 'grid-template-columns': `repeat(${o.columns_mobile ?? 1}, minmax(0, 1fr))` });
            }
            h.rule(`${h.sel} .gp-posts__media img`, { 'aspect-ratio': (o.ratio ?? '16:9').replace(':', ' / ') });
            h.responsive('title_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-posts__title');
        },
    },
    breadcrumbs: {
        preview: (o) => {
            const s = sample();
            const crumbs = [t('Accueil'), s.blog_title ?? 'Blog', s.post.category ?? t('Actualités')];
            const sep = o.separator ?? '›';
            return (
                <nav className="gp-crumbs">
                    <ol>
                        {crumbs.flatMap((c, i) => [<li key={`c${i}`}><a>{c}</a></li>, <li key={`s${i}`} className="gp-crumbs__sep" aria-hidden="true">{sep}</li>])}
                        <li><span aria-current="page">{s.post.title}</span></li>
                    </ol>
                </nav>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-crumbs`, { color: o.color });
            h.rule(`${h.sel} .gp-crumbs a`, { color: o.link_color });
            h.responsive('align', (v) => ({ 'justify-content': JUSTIFY[v] }), ' .gp-crumbs ol');
            h.responsive('font_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-crumbs');
        },
    },
    'search-form': {
        preview: (o) => (
            <form className="gp-search-form" onSubmit={(e) => e.preventDefault()}>
                <input type="search" placeholder={o.placeholder || t('Rechercher dans le site…')} readOnly />
                <button type="button">{o.button_text || t('Rechercher')}</button>
            </form>
        ),
        css: (o, h) => {
            h.rule(`${h.sel} button`, { background: o.button_color });
            if (o.radius !== undefined && o.radius !== null) {
                h.rule(`${h.sel} input`, { 'border-radius': `${o.radius}px` });
                h.rule(`${h.sel} button`, { 'border-radius': `${o.radius}px` });
            }
        },
    },
    'site-logo': {
        preview: (o) => {
            const s = sample();
            const showLogo = on(o.show_logo) && s.logo_url;
            const showName = on(o.show_name) || !showLogo;
            return (
                <a className="gp-logo">
                    {showLogo && <img src={s.logo_url} alt="" draggable={false} />}
                    {showName && <span className="gp-logo__name">{s.site_name}</span>}
                </a>
            );
        },
        css: (o, h) => {
            h.responsive('height', (v) => ({ height: `${v}px` }), ' img');
            h.responsive('align', (v) => ({ 'justify-content': JUSTIFY[v] }), ' .gp-logo');
            h.rule(`${h.sel} .gp-logo`, { color: o.color });
            h.responsive('font_size', (v) => ({ 'font-size': `${v}px` }), ' .gp-logo__name');
        },
    },
    'nav-menu': {
        preview: (o) => {
            const s = sample();
            return (
                <nav className={`gp-menu gp-menu--collapse-${o.collapse ?? 'mobile'}`}>
                    <span className="gp-menu__burger"><span /></span>
                    <div className="gp-menu__panel">
                        {s.menu.length > 0 && <ul className="gp-menu__list">{s.menu.map((l, i) => <li key={i}><a aria-current={i === 0 ? 'page' : undefined}>{l.label}</a></li>)}</ul>}
                        {on(o.show_languages) && s.languages.length > 1 && (
                            <ul className="gp-menu__langs">{s.languages.map((code, i) => <li key={code}><a aria-current={i === 0 ? 'true' : undefined}>{code.toUpperCase()}</a></li>)}</ul>
                        )}
                        {on(o.show_cta) && s.cta_label && <a className="gp-btn gp-btn-sm gp-menu__cta">{s.cta_label}</a>}
                    </div>
                </nav>
            );
        },
        css: (o, h) => {
            h.rule(`${h.sel} .gp-menu__panel`, { 'justify-content': JUSTIFY[o.align ?? 'right'] });
            h.rule(`${h.sel} .gp-menu__list`, { gap: o.gap !== undefined && o.gap !== null ? `${o.gap}px` : null });
            h.rule(`${h.sel} .gp-menu__list a`, { color: o.color, 'font-size': o.font_size !== undefined && o.font_size !== null ? `${o.font_size}px` : null });
            h.rule(`${h.sel} .gp-menu__list a:hover`, { color: o.color_hover });
            h.rule(`${h.sel} .gp-menu__burger`, { color: o.color });
            h.rule(`${h.sel} .gp-menu__cta`, { background: o.cta_color, 'border-color': o.cta_color });
        },
    },
};
