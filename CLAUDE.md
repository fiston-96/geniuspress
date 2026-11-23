# GeniusPress — notes pour les assistants de code

CMS open source (AGPL-3.0) : Laravel 13 comme fondation, builder React 19 (Vite), MySQL. Interface et commentaires en français.

## Commandes
- `php artisan serve` puis http://localhost:8000 (WAMP : MySQL sur 3306, root sans mot de passe)
- `npm run build` après toute modification de `resources/js` ou `resources/css`
- `php artisan test` — doit rester vert ; l'IA est testée avec `tests/Fakes/FakeLlm.php`, jamais avec l'API réelle

## Règles du projet
- Le contenu d'une page est un arbre JSON `sections → columns → widgets`. Tout contenu entrant (builder, IA, import) passe par `App\Builder\ContentSanitizer` avant d'être enregistré ou rendu.
- Les widgets passent tous par un registre, ceux du cœur comme ceux des plugins : classe PHP dans `app/Builder/Widgets/` (règles + `render()`), déclarée dans `AppServiceProvider::CORE_WIDGETS` ; côté builder, définition dans `resources/js/builder/widgets.js` + aperçu/CSS dans `resources/js/builder/widgets/core.jsx` (miroir de `render()`).
- Plugins : `app/Plugins/` (PluginManager, Plugin, Hooks). Guide : `docs/developpeurs/plugins.md`. Fixtures de test dans `tests/Fixtures/plugins/`.
- Ne jamais envoyer au navigateur les réglages privés : utiliser `Setting::publicValues()`, pas `allValues()`.
- Les appels IA passent par l'interface `App\Ai\LlmGateway` ; les messages d'erreur montrés à l'utilisateur sont en français (`AiException`).
- Une page publiée garde sa version en ligne dans `content` ; les modifications non publiées vont dans `draft_content`.
