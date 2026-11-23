// Déclaration du widget dans le builder. Pas besoin d'outil de compilation :
// GeniusPress fournit React via window.GeniusPress.
const { registerWidget } = window.GeniusPress;

registerWidget('citation', {
    label: 'Citation',
    icon: 'M7 7h4v6H5M17 7h4v6h-6',
    defaults: { text: 'Votre citation ici.', author: 'Nom de l’auteur', align: 'left' },
    controls: [
        { key: 'text', label: 'Citation', type: 'textarea' },
        { key: 'author', label: 'Auteur', type: 'text' },
        { key: 'align', label: 'Alignement', type: 'segmented', tab: 'style', options: [{ value: 'left', label: 'Gauche' }, { value: 'center', label: 'Centre' }] },
        { key: 'accent', label: 'Couleur du trait', type: 'color', tab: 'style' },
    ],
    // Aperçu dans le canevas (même HTML que la méthode render() en PHP).
    preview: (o, { h }) => h('blockquote', { className: 'citation' },
        h('p', null, o.text),
        o.author ? h('footer', null, o.author) : null,
    ),
    // Styles dans le canevas (miroir de $ctx->rule() en PHP).
    css: (o, { rule, sel }) => rule(`${sel} blockquote`, { 'border-color': o.accent, 'text-align': o.align }),
});
