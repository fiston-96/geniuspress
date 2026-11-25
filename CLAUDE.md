# GeniusPress — notes pour les assistants de code

CMS open source (AGPL-3.0) : Laravel 13 comme fondation, builder React 19 (Vite), MySQL. Interface et commentaires en français.

## Commandes
- `php artisan serve` puis http://localhost:8000 (WAMP : MySQL sur 3306, root sans mot de passe)
- `npm run build` après toute modification de `resources/js` ou `resources/css`
- `php artisan test` — doit rester vert ; l'IA est testée avec `tests/Fakes/FakeLlm.php`, jamais avec l'API réelle

## Règles du projet
- Le contenu d'une page (format 2) est un arbre JSON `{ version: 2, elements: [...] }` de conteneurs imbriqués (`type: container`, `children`) et de widgets ; format décrit dans `App\Builder\Schema`. Tout contenu entrant (builder, IA, import) passe par `App\Builder\ContentSanitizer` avant d'être enregistré ou rendu ; l'ancien format 1 (`sections → columns → widgets`, encore produit par l'IA et `website/site.php`) y est converti par `ContentMigrator`.
- Style : réglages communs `Schema::STYLE` (CSS dans `ElementStyle.php`), suffixes `@r` (par appareil : `_tablet`, `_mobile`) et `@h` (survol : `_hover`). Le CSS du canevas (`resources/js/builder/style.js` + `widgets/core.jsx`) doit rester identique à celui du serveur.
- Les widgets passent tous par un registre, ceux du cœur comme ceux des plugins : classe PHP dans `app/Builder/Widgets/` (règles + `render()`), déclarée dans `AppServiceProvider::CORE_WIDGETS` ; côté builder, définition dans `resources/js/builder/widgets.js` + aperçu/CSS dans `resources/js/builder/widgets/core.jsx` (miroir de `render()`).
- Interface traduisible : le texte source est en français. PHP/Blade : `__('Texte')` ; builder : `t('Texte')` (`resources/js/builder/i18n.js`). Toute nouvelle chaîne doit être ajoutée à `lang/en.json` (le test `InterfaceLanguageTest` échoue sinon). La langue de l'interface (utilisateur, `SetLocale`) est distincte de la langue du contenu des pages (`pages.locale`, réglage `locales` du site).
- Plugins : `app/Plugins/` (PluginManager, Plugin, Hooks). Guide : `docs/developpeurs/plugins.md`. Fixtures de test dans `tests/Fixtures/plugins/`.
- Installation : `app/Install/` (InstallState, Installer, EnvironmentFile). Le verrou `storage/app/installed.lock` ferme `/install`. Pendant l'installation, le `.env` n'est écrit qu'à la fin (base choisie gardée dans `storage/app/install-database.json`). Les tests utilisent un `.env`, un verrou et une base SQLite temporaires (`Tests\TestCase::$sandbox`) : ne jamais toucher au vrai `.env` dans un test.
- Export/import : `app/Transfer/` (SiteExporter, SiteImporter). Format zip versionné (`manifest.json` + pages/media/settings JSON + `media/`). L'import ne lit que les fichiers attendus, repasse tout par `ContentSanitizer` et réécrit les identifiants/adresses de médias. Jamais d'utilisateurs ni de réglages privés dans un export.
- Modèles de sites : `app/Templates/` (Theme, Kit, Blocks, Placeholder, TemplateBuilder), packs de secteur `templates/sectors/{secteur}.php` (8 pages, FR + EN, textes côte à côte), liste des modèles `templates/catalog.php`. `php artisan geniuspress:templates` construit le catalogue (`dist/catalog/v1` : index.json + un zip au format d'export par modèle). Démonstration sans installation sur `/catalogue/{modèle}` (en local, ou `GP_CATALOG_DEMOS=true`). Les blocs n'utilisent que des couleurs globales ; le test `TemplateCatalogTest` vérifie que chaque réglage de chaque page passe `ContentSanitizer`.
- Essai hébergé : `app/Hosting/` (conception : `docs/developpeurs/essai-heberge.md`). Un code partagé, une base + un dossier par site sous `storage/hosting/sites/{site}` ; `HostedBootstrap` (appelé dans `bootstrap/app.php`) choisit le `.env` et le storage du site d'après le nom d'hôte ou `GP_SITE=…`. Comptes et offres : interface `HostingAccounts` (branchée par `app/Platform/`). Dans les tests, `hosting.path` pointe vers `$this->sandbox`.
- Ne jamais envoyer au navigateur les réglages privés : utiliser `Setting::publicValues()`, pas `allValues()`.
- Les appels IA passent par l'interface `App\Ai\LlmGateway` ; les messages d'erreur montrés à l'utilisateur sont en français (`AiException`).
- Une page publiée garde sa version en ligne dans `content` ; les modifications non publiées vont dans `draft_content`.
