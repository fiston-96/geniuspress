// Icônes et boutons-icônes de l'interface du builder (jeu Lucide de ui-icons.json,
// construit par scripts/icons/build-ui-icons.mjs). Chaque bouton-icône porte une
// infobulle et un libellé accessible traduits : l'interface se lit par icônes.
import UI_ICONS from './ui-icons.json';

/** Icône d'interface ; le balisage vient du fichier livré, jamais de l'utilisateur. */
export function UiIcon({ name, className = 'h-4 w-4', strokeWidth = 1.8 }) {
    const body = UI_ICONS[name];
    if (!body) return null;
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round"
            aria-hidden="true" focusable="false" dangerouslySetInnerHTML={{ __html: body }} />
    );
}

/** Icône d'un chemin SVG (icônes des widgets, déclarées par leur définition). */
export function PathIcon({ d, className = 'h-4 w-4', strokeWidth = 1.8 }) {
    return (
        <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={strokeWidth} strokeLinecap="round" strokeLinejoin="round" aria-hidden="true" focusable="false">
            <path d={d} />
        </svg>
    );
}

/**
 * Bouton-icône. « label » sert d'infobulle et de nom accessible ; « shortcut » s'ajoute
 * à l'infobulle (« Annuler (Ctrl+Z) »).
 */
export function IconButton({ icon, label, shortcut, onClick, disabled, pressed, className = '', iconClass, ...rest }) {
    const title = shortcut ? `${label} (${shortcut})` : label;
    return (
        <button type="button" title={title} aria-label={label} aria-pressed={pressed} disabled={disabled} onClick={onClick}
            className={`grid place-items-center rounded-md transition disabled:pointer-events-none disabled:opacity-30 ${className}`} {...rest}>
            <UiIcon name={icon} className={iconClass} />
        </button>
    );
}
