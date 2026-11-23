// Traduction de l'interface du builder. Les textes source sont en français ;
// le dictionnaire de la langue de l'utilisateur est fourni par le serveur
// (lang/{locale}.json, plugins compris) dans le JSON #gp-boot.
//
//   t('Enregistrer')                       → « Save » en anglais
//   t('Bonjour :name', { name: 'Amani' })  → remplacement des :paramètres

let dictionary = null;

function load() {
    if (dictionary) return dictionary;
    try {
        dictionary = JSON.parse(document.getElementById('gp-boot')?.textContent ?? '{}').i18n ?? {};
    } catch {
        dictionary = {};
    }
    return dictionary;
}

export function t(key, params = {}) {
    let text = load()[key] ?? key;
    for (const [name, value] of Object.entries(params)) {
        text = text.replaceAll(`:${name}`, String(value));
    }
    return text;
}

export function locale() {
    return document.documentElement.lang || 'fr';
}
