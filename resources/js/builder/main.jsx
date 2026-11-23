import * as React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import { allWidgets, getWidget, registerFromRules, registerWidget } from './registry';
import { setState } from './store';
import { registerCoreWidgets } from './widgets/core';

registerCoreWidgets();

// API publique pour les scripts des plugins (chargés juste après ce fichier).
window.GeniusPress = Object.freeze({
    version: '0.1.0',
    React,
    h: React.createElement,
    registerWidget,
    getWidget,
    allWidgets,
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
        content: boot.page.content?.sections ? boot.page.content : { version: 1, sections: [] },
        status: boot.page.status,
        hasDraft: boot.page.has_draft,
        revisions: boot.revisions,
    });

    createRoot(document.getElementById('builder')).render(<App boot={boot} />);
});
