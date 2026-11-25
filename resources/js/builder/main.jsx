import * as React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import { allWidgets, getWidget, registerFromRules, registerWidget } from './registry';
import { t } from './i18n';
import { setState } from './store';
import { walk } from './tree';
import { registerCoreWidgets } from './widgets/core';
import { setThemeData } from './widgets/theme-preview';

registerCoreWidgets();

// API publique pour les scripts des plugins (chargés juste après ce fichier).
window.GeniusPress = Object.freeze({
    version: '0.1.0',
    React,
    h: React.createElement,
    registerWidget,
    getWidget,
    t,
    allWidgets,
    // Parcourir le contenu d'une page (conteneurs imbriqués) depuis un plugin.
    walk,
});

// Les scripts de plugins (type="module") s'exécutent avant DOMContentLoaded :
// on démarre l'éditeur une fois qu'ils ont tous déclaré leurs widgets.
document.addEventListener('DOMContentLoaded', () => {
    const boot = JSON.parse(document.getElementById('gp-boot').textContent);

    // Widgets déclarés uniquement côté PHP : contrôles générés automatiquement.
    for (const [type, w] of Object.entries(boot.widgets ?? {})) {
        registerFromRules(type, w.label, w.rules);
    }

    setState({
        // Le serveur envoie toujours le format actuel (converti si besoin).
        content: Array.isArray(boot.page.content?.elements) ? boot.page.content : { version: 2, elements: [] },
        design: boot.design ?? { colors: [], fonts: [] },
        status: boot.page.status,
        hasDraft: boot.page.has_draft,
        revisions: boot.revisions,
        // Articles, menu et catégories du site, pour l'aperçu des widgets dynamiques.
        sample: boot.sample,
    });
    setThemeData(boot.sample);

    createRoot(document.getElementById('builder')).render(<App boot={boot} />);
});
