# Creating a GeniusPress plugin

A plugin adds widgets to the builder, reacts to events, modifies the HTML of pages, or declares its own pages and routes. GeniusPress core widgets use exactly the same API as plugins.

> **License.** Thanks to the exception in `LICENSE-EXCEPTION.md`, a plugin that only uses the public API described here can have the license of your choice, including a commercial one.

## Get started in 2 minutes

```bash
php artisan geniuspress:make-plugin temoignage --name="Testimonial" --author="My NGO"
```

The command creates `plugins/temoignage/` with a working widget. Activate it in **Admin → Plugins**, then open the builder: the "Testimonial" widget is in the list of elements.

## Structure

```
plugins/temoignage/
├── plugin.json          Identity, compatibility, loaded files
├── src/Plugin.php       Entry point: declares widgets and hooks
├── src/Widget.php       Allowed settings and HTML rendering on the site
├── public/builder.js    Controls and preview in the builder
├── public/site.css      Styles on the site (and in the builder canvas)
├── database/migrations/ (optional) plugin tables, migrated on activation
├── resources/views/     (optional) Blade views, available via $this->views()
└── README.md
```

The folder name is the plugin identifier: lowercase letters, digits and hyphens.

## plugin.json

```json
{
    "name": "Testimonial",
    "version": "1.0.0",
    "description": "Adds a testimonial widget.",
    "author": "My NGO",
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

| Field | Purpose |
|---|---|
| `name`, `version` | Required. |
| `requires.geniuspress` | Minimum version: `>=0.1.0`, `^1.0` or `*`. An incompatible plugin cannot be activated. |
| `main` | Class that extends `App\Plugins\Plugin`. Optional for a plugin that only provides files. |
| `autoload.psr-4` | Namespace → folder, as in Composer. |
| `assets` | Files from the `public/` folder: `builder` (builder scripts), `site_css` and `site_js` (public site). |

## The plugin class

```php
namespace Plugins\Temoignage;

use App\Plugins\Plugin as BasePlugin;
use Illuminate\Support\Facades\Route;

class Plugin extends BasePlugin
{
    public function boot(): void
    {
        $this->widget(new Widget);

        $this->on('page.published', fn ($page) => logger("Published: {$page->title}"));
        $this->filter('page.head', fn (string $html) => $html.'<meta name="theme-color" content="#0f172a">');

        $this->views(); // views available via view('temoignage::name')
        $this->routes(function () {
            Route::get('/merci', fn () => view('temoignage::merci'))->name('merci');
        });
    }

    public function activate(): void {}   // once, on activation (after migrations)
    public function deactivate(): void {} // once, on deactivation: do not delete data
}
```

| Method | Effect |
|---|---|
| `$this->widget(Widget)` | Declares a widget. |
| `$this->on(event, callback)` | Reacts to an event. |
| `$this->filter(name, callback)` | Modifies a value; the callback receives the value and returns it. |
| `$this->routes(callback)` | Routes prefixed with `/plugins/{folder}`, with session and CSRF protection. Add `->middleware('auth')` for the admin. |
| `$this->views()` | Views from `resources/views` under the `{folder}::` namespace. |
| `$this->path('path')`, `$this->asset('file')` | Absolute path of a plugin file, public URL of a file in `public/`. |

## Creating a widget

### Server side

```php
namespace Plugins\Temoignage;

use App\Builder\RenderContext;
use App\Builder\Widgets\Widget as BaseWidget;

class Widget extends BaseWidget
{
    public function type(): string { return 'temoignage'; }   // unique, lowercase and hyphens
    public function label(): string { return 'Testimonial'; }

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

**Validation rules.** Any setting missing from `rules()` is removed before saving, and every value is checked:

| Rule | Accepts |
|---|---|
| `string:MAX` | Plain text, truncated to MAX characters. Escape it with `e()` in `render()`. |
| `html` | HTML cleaned with an allowlist (paragraphs, bold, italic, lists, links). |
| `color` | `#rgb`, `#rrggbb` or `#rrggbbaa`. |
| `int:MIN:MAX` | Integer, clamped between MIN and MAX. |
| `enum:a,b,c` | One of the listed values. |
| `url` | `https://`, `http://`, `mailto:`, `tel:`, `/path` or `#anchor`. Never `javascript:`. |
| `slug:MAX` | Lowercase identifier (anchors). |
| `bool` | True or false. |

Common settings (`margin_bottom`, `hide_desktop`, `hide_mobile`) are added automatically.

**`RenderContext`**

- `$ctx->rule($id, [property => value], $mobile = false, $suffix = '')` adds a CSS rule for `#gp-{id}{suffix}`. Null values are ignored, and `$mobile = true` limits the rule to screens narrower than 768 px.
- `RenderContext::px($n)` returns `"{n}px"` or `null`.
- `$ctx->media($id)` returns the media library image matching a `media_id` setting.

**Optional methods**

- `translatable()`: settings translated by the AI assistant (`['text', 'author']`).
- `aiHint()`: one sentence, in English, that tells the AI assistant when to use the widget. Without it, the AI does not use the widget to generate pages.
- `aiExcluded()`: settings the AI must not fill in (image URLs, links).

### Builder side (`public/builder.js`)

No build tooling is needed: GeniusPress provides React through `window.GeniusPress`.

```js
const { registerWidget } = window.GeniusPress;

registerWidget('temoignage', {
    label: 'Testimonial',
    icon: 'M7 7h4v6H5M17 7h4v6h-6',          // 24×24 SVG path
    defaults: { text: 'Your testimonial', align: 'left' },
    controls: [
        { key: 'text', label: 'Text', type: 'textarea' },
        { key: 'author', label: 'Author', type: 'text' },
        { key: 'photo', label: 'Photo', type: 'image' },
        { key: 'accent', label: 'Color', type: 'color', tab: 'style' },
        { key: 'align', label: 'Alignment', type: 'segmented', tab: 'style',
          options: [{ value: 'left', label: 'Left' }, { value: 'center', label: 'Center' }] },
    ],
    preview: (o, { h }) => h('blockquote', null, h('p', null, o.text)),
    css: (o, { rule, mobile, px, sel }) => rule(`${sel} blockquote`, { 'border-color': o.accent }),
});
```

Control types: `text`, `textarea`, `richtext`, `url`, `select`, `segmented`, `slider` (`min`, `max`, `step`, `default`), `color`, `toggle`, `image`. Choose the tab with `tab: 'content' | 'style' | 'advanced'`.

`preview` must produce the same HTML as `render()` in PHP, and `css` the same rules. If `preview` throws an error, only that widget shows "Preview unavailable": the builder stays usable.

A plugin without `builder.js` works too. GeniusPress then generates the controls from the PHP rules, and the preview is shown on the published site.

## Available hooks

| Name | Type | Receives | Use |
|---|---|---|---|
| `page.saved` | event | `Page` | After every save in the builder. |
| `page.published` | event | `Page` | After a publication. |
| `page.content` | filter | validated JSON tree | Modify the content just before rendering. |
| `page.head` | filter | `string $html, Page` | Add tags to `<head>`. |
| `page.body_end` | filter | `string $html, Page` | Add HTML before `</body>`. |
| `admin.menu` | filter | `array $nav` | Add a menu entry: `[route name, label, SVG path]`. |

A hook function that throws an exception is ignored and logged: it never breaks the page.

## Security and robustness

- **A plugin runs PHP with full privileges.** Only administrators can activate one.
- **A plugin that crashes on load** is deactivated automatically, and the error is shown in **Admin → Plugins**.
- **Deactivated plugin**: its widgets stay in the pages but are no longer displayed. They reappear on reactivation, with no loss of content.
- **Public files**: only the plugin's `public/` folder is served, and only for known file types (js, css, images, fonts, json).
- Escape all text with `e()` in `render()`. Only `html` settings are already cleaned.

## Complete example

The `plugins/citation` folder contains a complete plugin generated by `make-plugin`: a widget, its styles and its preview in the builder.
