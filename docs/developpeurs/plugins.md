# Créer un plugin GeniusPress

Un plugin ajoute des widgets au builder, réagit à des événements, modifie le HTML des pages ou déclare ses propres pages et routes. Les widgets du cœur de GeniusPress utilisent exactement la même API que les plugins.

> **Licence.** Grâce à l'exception de `LICENSE-EXCEPTION.md`, un plugin qui passe uniquement par l'API publique décrite ici peut avoir la licence de votre choix, y compris commerciale.

## Démarrer en 2 minutes

```bash
php artisan geniuspress:make-plugin temoignage --name="Témoignage" --author="Mon ONG"
```

La commande crée `plugins/temoignage/` avec un widget fonctionnel. Activez-le dans **Admin → Plugins**, ouvrez le builder : le widget « Témoignage » est dans la liste des éléments.

## Structure

```
plugins/temoignage/
├── plugin.json          Identité, compatibilité, fichiers chargés
├── src/Plugin.php       Point d'entrée : déclare widgets et hooks
├── src/Widget.php       Réglages autorisés et rendu HTML sur le site
├── public/builder.js    Contrôles et aperçu dans le builder
├── public/site.css      Styles sur le site (et dans le canevas du builder)
├── database/migrations/ (facultatif) tables du plugin, migrées à l'activation
├── resources/views/     (facultatif) vues Blade, accessibles via $this->views()
└── README.md
```

Le nom du dossier est l'identifiant du plugin : minuscules, chiffres et tirets.

## plugin.json

```json
{
    "name": "Témoignage",
    "version": "1.0.0",
    "description": "Ajoute un widget de témoignage.",
    "author": "Mon ONG",
    "license": "MIT",
    "homepage": "https://exemple.org",
    "requires": { "geniuspress": ">=0.1.0" },
    "main": "Plugins\\Temoignage\\Plugin",
    "autoload": { "psr-4": { "Plugins\\Temoignage\\": "src/" } },
    "assets": {
        "builder": ["builder.js"],
        "site_css": ["site.css"],
        "site_js": []
    }
}
```

| Champ | Rôle |
|---|---|
| `name`, `version` | Obligatoires. |
| `requires.geniuspress` | Version minimale : `>=0.1.0`, `^1.0` ou `*`. Un plugin incompatible ne peut pas être activé. |
| `main` | Classe qui hérite de `App\Plugins\Plugin`. Facultatif pour un plugin qui ne fournit que des fichiers. |
| `autoload.psr-4` | Espace de noms → dossier, comme dans Composer. |
| `assets` | Fichiers du dossier `public/` : `builder` (scripts du builder), `site_css` et `site_js` (site public). |

## La classe du plugin

```php
namespace Plugins\Temoignage;

use App\Plugins\Plugin as BasePlugin;
use Illuminate\Support\Facades\Route;

class Plugin extends BasePlugin
{
    public function boot(): void
    {
        $this->widget(new Widget);

        $this->on('page.published', fn ($page) => logger("Publiée : {$page->title}"));
        $this->filter('page.head', fn (string $html) => $html.'<meta name="theme-color" content="#0f172a">');

        $this->views(); // vues accessibles via view('temoignage::nom')
        $this->routes(function () {
            Route::get('/merci', fn () => view('temoignage::merci'))->name('merci');
        });
    }

    public function activate(): void {}   // une fois, à l'activation (après les migrations)
    public function deactivate(): void {} // une fois, à la désactivation : ne supprimez pas de données
}
```

| Méthode | Effet |
|---|---|
| `$this->widget(Widget)` | Déclare un widget. |
| `$this->on(événement, fonction)` | Réagit à un événement. |
| `$this->filter(nom, fonction)` | Modifie une valeur ; la fonction reçoit la valeur et la renvoie. |
| `$this->routes(fonction)` | Routes préfixées par `/plugins/{dossier}`, avec session et protection CSRF. Ajoutez `->middleware('auth')` pour l'admin. |
| `$this->views()` | Vues de `resources/views` sous l'espace `{dossier}::`. |
| `$this->path('chemin')`, `$this->asset('fichier')` | Chemin absolu d'un fichier du plugin, adresse publique d'un fichier de `public/`. |

## Créer un widget

### Côté serveur

```php
namespace Plugins\Temoignage;

use App\Builder\RenderContext;
use App\Builder\Widgets\Widget as BaseWidget;

class Widget extends BaseWidget
{
    public function type(): string { return 'temoignage'; }   // unique, minuscules et tirets
    public function label(): string { return 'Témoignage'; }

    public function rules(): array
    {
        return [
            'text'   => 'string:600',
            'author' => 'string:120',
            'photo'  => 'url',
            'accent' => 'color',
            'align'  => 'enum:left,center',
        ];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['border-color' => $s['accent'] ?? null], false, ' blockquote');

        return '<blockquote><p>'.e($s['text'] ?? '').'</p></blockquote>';
    }
}
```

**Règles de validation.** Tout réglage absent de `rules()` est supprimé avant l'enregistrement, et chaque valeur est contrôlée :

| Règle | Accepte |
|---|---|
| `string:MAX` | Texte brut, coupé à MAX caractères. À échapper avec `e()` dans `render()`. |
| `html` | HTML nettoyé par liste blanche (paragraphes, gras, italique, listes, liens). |
| `color` | `#rgb`, `#rrggbb`, `#rrggbbaa`, `rgb()`/`rgba()`, `transparent`, ou une couleur globale du site (`global:primary`, `global:{id}`). |
| `int:MIN:MAX` | Nombre entier, ramené entre MIN et MAX. |
| `enum:a,b,c` | Une des valeurs listées. |
| `url` | `https://`, `http://`, `mailto:`, `tel:`, `/chemin` ou `#ancre`. Jamais `javascript:`. |
| `slug:MAX` | Identifiant en minuscules (ancres). |
| `bool` | Vrai ou faux. |
| `dim` | Dimension CSS : `50%`, `320px`, `1.5em`, `1.4` (sans unité) ou `auto`. |
| `spacing` | Quatre côtés `{ t, r, b, l, u }` (marges, arrondis). |
| `shadow` | Ombre `{ x, y, blur, spread, color, inset }`. |
| `font` | Une police proposée par GeniusPress, ou `global:heading` / `global:body`. |

Ajoutez `@r` à une règle pour un réglage **par appareil** (`'font_size' => 'int:8:120@r'` accepte aussi `font_size_tablet` et `font_size_mobile`), `@h` pour un réglage **au survol** (`'color' => 'color@h'` accepte aussi `color_hover`), ou `@rh` pour les deux. Ajoutez `+ Schema::TYPOGRAPHY` à vos règles pour proposer la typographie complète.

Les réglages de style communs à tous les éléments (marges, largeur, fond, dégradé, bordure, ombre, opacité, masquage par appareil, ancre, classes CSS…) sont ajoutés automatiquement et appliqués à l'enveloppe du widget. Un réglage de votre widget qui porte le même nom les remplace.

**`RenderContext`**

- `$ctx->rule($id, [propriété => valeur], $appareil = false, $suffixe = '')` ajoute une règle CSS pour `#gp-{id}{suffixe}`. Les valeurs nulles sont ignorées. `$appareil` vaut `false` (tous les écrans), `'tablet'` (1024 px et moins) ou `'mobile'` / `true` (767 px et moins). Pour le survol, utilisez le suffixe `' blockquote:hover'`.
- `$ctx->responsive($id, $réglages, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' blockquote')` écrit la règle pour chaque appareil où le réglage est défini.
- `$ctx->typography($id, $réglages, ' blockquote')` applique les réglages de `Schema::TYPOGRAPHY` et charge la police choisie.
- Les couleurs globales (`global:primary`) sont remplacées automatiquement par leur variable CSS.
- `RenderContext::px($n)` renvoie `"{n}px"` ou `null`.
- `$ctx->media($id)` renvoie l'image de la médiathèque correspondant à un réglage `media_id`.

**Méthodes facultatives**

- `translatable()` : réglages traduits par l'assistant IA (`['text', 'author']`).
- `aiHint()` : une phrase, en anglais, qui explique à l'assistant IA quand utiliser le widget. Sans elle, l'IA ne l'utilise pas pour générer des pages.
- `aiExcluded()` : réglages que l'IA ne doit pas remplir (adresses d'images, liens).

### Côté builder (`public/builder.js`)

Aucun outil de compilation n'est nécessaire : GeniusPress fournit React via `window.GeniusPress`.

```js
const { registerWidget } = window.GeniusPress;

registerWidget('temoignage', {
    label: 'Témoignage',
    icon: 'M7 7h4v6H5M17 7h4v6h-6',          // tracé SVG 24×24
    defaults: { text: 'Votre témoignage', align: 'left' },
    controls: [
        { key: 'text', label: 'Texte', type: 'textarea' },
        { key: 'author', label: 'Auteur', type: 'text' },
        { key: 'photo', label: 'Photo', type: 'image' },
        { key: 'accent', label: 'Couleur', type: 'color', tab: 'style' },
        { key: 'align', label: 'Alignement', type: 'segmented', tab: 'style',
          options: [{ value: 'left', label: 'Gauche' }, { value: 'center', label: 'Centre' }] },
    ],
    preview: (o, { h }) => h('blockquote', null, h('p', null, o.text)),
    css: (o, { rule, tablet, mobile, responsive, typography, px, sel }) => rule(`${sel} blockquote`, { 'border-color': o.accent }),
});
```

Types de contrôles : `text`, `textarea`, `richtext`, `url`, `select`, `segmented`, `slider` (`min`, `max`, `step`, `default`), `color`, `toggle`, `image`, `links`, `dim` (`units`), `spacing` (`units`), `shadow`, `font`, et `heading` (titre de groupe). L'onglet se choisit avec `tab: 'content' | 'style' | 'advanced'`. Ajoutez `responsive: true` pour un réglage par appareil (règle `@r`) et `hover: true` pour un réglage au survol (règle `@h`).

Dans `css`, `rule`, `tablet` et `mobile` reçoivent un sélecteur complet ; `responsive(clé, fn, suffixe)` et `typography(suffixe)` font la même chose que leurs équivalents PHP.

`preview` doit produire le même HTML que `render()` en PHP, et `css` les mêmes règles. Si `preview` lève une erreur, seul ce widget affiche « aperçu indisponible » : le builder reste utilisable.

Un plugin sans `builder.js` fonctionne aussi. GeniusPress génère alors les contrôles à partir des règles PHP, et l'aperçu s'affiche sur le site publié.

## Hooks disponibles

| Nom | Type | Reçoit | Usage |
|---|---|---|---|
| `page.saved` | événement | `Page` | Après chaque enregistrement dans le builder. |
| `page.published` | événement | `Page` | Après une publication. |
| `page.content` | filtre | arbre JSON validé (`{ version: 2, elements: [...] }`, conteneurs imbriqués) | Modifier le contenu juste avant le rendu. `App\Builder\Renderer::walk()` parcourt tous les éléments. |
| `page.head` | filtre | `string $html, Page` | Ajouter des balises dans `<head>`. |
| `page.body_end` | filtre | `string $html, Page` | Ajouter du HTML avant `</body>`. |
| `admin.menu` | filtre | `array $nav` | Ajouter une entrée de menu : `[nom de route, libellé, tracé SVG]`. |

Une fonction de hook qui lève une exception est ignorée et journalisée : elle ne casse jamais la page.

## Sécurité et robustesse

- **Un plugin exécute du PHP avec tous les droits.** Seuls les administrateurs peuvent l'activer.
- **Plugin qui plante au chargement** : il est désactivé automatiquement, et l'erreur s'affiche dans **Admin → Plugins**.
- **Plugin désactivé** : ses widgets restent dans les pages, mais ne s'affichent plus. Ils réapparaissent à la réactivation, sans perte de contenu.
- **Fichiers publics** : seul le dossier `public/` du plugin est servi, et seulement pour les types de fichiers connus (js, css, images, polices, json).
- Échappez tout texte avec `e()` dans `render()`. Seuls les réglages `html` sont déjà nettoyés.

## Exemple complet

Le dossier `plugins/citation` contient un plugin complet généré par `make-plugin` : un widget, ses styles et son aperçu dans le builder.
