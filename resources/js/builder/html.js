import DOMPurify from 'dompurify';

/**
 * Nettoyage du HTML des blocs texte, avec la même liste blanche que le serveur
 * (ContentSanitizer::html) : le canevas montre exactement ce qui sera enregistré.
 * Une balise non prévue est retirée mais son texte reste.
 */
const OPTIONS = {
    ALLOWED_TAGS: ['p', 'div', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'h2', 'h3', 'h4', 'h5', 'h6',
        'a', 'span', 'code', 'pre', 'kbd', 'hr', 'table', 'thead', 'tbody', 'tr', 'th', 'td'],
    ALLOWED_ATTR: ['href', 'title', 'target'],
    KEEP_CONTENT: true,
};

export function cleanHtml(html) {
    return DOMPurify.sanitize(html ?? '', OPTIONS);
}
