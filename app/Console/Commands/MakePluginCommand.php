<?php

namespace App\Console\Commands;

use App\Builder\WidgetRegistry;
use App\Plugins\PluginManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Crée un plugin fonctionnel avec un widget d'exemple, prêt à être modifié.
 *
 *   php artisan geniuspress:make-plugin citation --name="Citation" --author="Mon ONG"
 */
class MakePluginCommand extends Command
{
    protected $signature = 'geniuspress:make-plugin
        {slug : Nom du dossier (minuscules, chiffres et tirets), utilisé aussi comme type du widget}
        {--name= : Nom affiché du plugin}
        {--author= : Auteur}
        {--license=MIT : Licence du plugin}';

    protected $description = 'Crée un nouveau plugin GeniusPress avec un widget d’exemple';

    public function handle(PluginManager $plugins, WidgetRegistry $widgets): int
    {
        $slug = (string) $this->argument('slug');
        if (! preg_match('/^[a-z][a-z0-9-]{1,40}$/', $slug)) {
            $this->error('Le nom doit commencer par une lettre et ne contenir que des minuscules, des chiffres et des tirets.');

            return self::FAILURE;
        }
        if ($widgets->has($slug)) {
            $this->error("« {$slug} » est déjà un type de widget. Choisissez un autre nom.");

            return self::FAILURE;
        }

        $dir = $plugins->directory().DIRECTORY_SEPARATOR.$slug;
        if (File::exists($dir)) {
            $this->error("Le dossier {$dir} existe déjà.");

            return self::FAILURE;
        }

        $name = $this->option('name') ?: Str::headline($slug);
        $namespace = 'Plugins\\'.Str::studly($slug);
        $vars = [
            '{{slug}}' => $slug,
            '{{name}}' => $name,
            '{{author}}' => $this->option('author') ?: 'Votre nom',
            '{{license}}' => $this->option('license'),
            '{{namespace}}' => $namespace,
            '{{namespace_json}}' => str_replace('\\', '\\\\', $namespace),
            '{{version}}' => (string) config('geniuspress.version'),
        ];

        foreach (self::files() as $path => $content) {
            $target = $dir.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
            File::ensureDirectoryExists(dirname($target));
            File::put($target, strtr($content, $vars));
        }

        $this->info("Plugin créé dans {$dir}");
        $this->line('Activez-le dans Admin → Plugins, puis ajoutez le widget « '.$name.' » depuis le builder.');

        return self::SUCCESS;
    }

    private static function files(): array
    {
        return [
            'plugin.json' => <<<'JSON'
{
    "name": "{{name}}",
    "version": "0.1.0",
    "description": "Ajoute un widget « {{name}} » au builder.",
    "author": "{{author}}",
    "license": "{{license}}",
    "requires": { "geniuspress": ">={{version}}" },
    "main": "{{namespace_json}}\\Plugin",
    "autoload": { "psr-4": { "{{namespace_json}}\\": "src/" } },
    "assets": {
        "builder": ["builder.js"],
        "site_css": ["site.css"]
    }
}

JSON,
            'src/Plugin.php' => <<<'PHP'
<?php

namespace {{namespace}};

use App\Plugins\Plugin as BasePlugin;

class Plugin extends BasePlugin
{
    public function boot(): void
    {
        // Déclare le widget dans le builder et sur le site.
        $this->widget(new Widget);

        // Exemples de hooks (décommentez pour essayer) :
        // $this->on('page.published', fn ($page) => logger("Page publiée : {$page->title}"));
        // $this->filter('page.head', fn (string $html) => $html.'<meta name="theme-color" content="#4f46e5">');
    }
}

PHP,
            'src/Widget.php' => <<<'PHP'
<?php

namespace {{namespace}};

use App\Builder\RenderContext;
use App\Builder\Widgets\Widget as BaseWidget;

class Widget extends BaseWidget
{
    public function type(): string
    {
        return '{{slug}}';
    }

    public function label(): string
    {
        return __('{{name}}');
    }

    /** Réglages autorisés : tout le reste est supprimé avant l'enregistrement. */
    public function rules(): array
    {
        return [
            'text' => 'string:600',
            'author' => 'string:120',
            'accent' => 'color',
            'align' => 'enum:left,center',
        ];
    }

    /** Textes traduits par l'assistant IA. */
    public function translatable(): array
    {
        return ['text', 'author'];
    }

    /** Permet à l'assistant IA d'utiliser ce widget quand il crée une page. */
    public function aiHint(): ?string
    {
        return 'A short quote with its author. Never invent quotes attributed to real people.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['border-color' => $s['accent'] ?? null, 'text-align' => $s['align'] ?? null], false, ' blockquote');

        $author = isset($s['author']) ? '<footer>'.e($s['author']).'</footer>' : '';

        return '<blockquote class="{{slug}}"><p>'.e($s['text'] ?? '').'</p>'.$author.'</blockquote>';
    }
}

PHP,
            'public/builder.js' => <<<'JS'
// Déclaration du widget dans le builder. Pas besoin d'outil de compilation :
// GeniusPress fournit React via window.GeniusPress.
const { registerWidget, t } = window.GeniusPress;

registerWidget('{{slug}}', {
    label: t('{{name}}'),
    icon: 'M7 7h4v6H5M17 7h4v6h-6',
    defaults: { text: 'Votre citation ici.', author: 'Nom de l’auteur', align: 'left' },
    controls: [
        { key: 'text', label: t('Texte de la citation'), type: 'textarea' },
        { key: 'author', label: t('Auteur'), type: 'text' },
        { key: 'align', label: t('Alignement'), type: 'segmented', tab: 'style', options: [{ value: 'left', label: t('Gauche') }, { value: 'center', label: t('Centre') }] },
        { key: 'accent', label: t('Couleur du trait'), type: 'color', tab: 'style' },
    ],
    // Aperçu dans le canevas (même HTML que la méthode render() en PHP).
    preview: (o, { h }) => h('blockquote', { className: '{{slug}}' },
        h('p', null, o.text),
        o.author ? h('footer', null, o.author) : null,
    ),
    // Styles dans le canevas (miroir de $ctx->rule() en PHP).
    css: (o, { rule, sel }) => rule(`${sel} blockquote`, { 'border-color': o.accent, 'text-align': o.align }),
});

JS,
            'public/site.css' => <<<'CSS'
/* Styles du widget, chargés sur le site public et dans le builder. */
blockquote.{{slug}} { margin: 0; padding: 8px 0 8px 20px; border-left: 4px solid var(--gp-primary); }
blockquote.{{slug}} p { margin: 0 0 8px; font-size: 1.25rem; font-style: italic; }
blockquote.{{slug}} footer { font-size: .9rem; opacity: .75; }
blockquote.{{slug}} footer::before { content: "— "; }

CSS,
            'lang/en.json' => <<<'JSON'
{
    "{{name}}": "{{name}}",
    "Texte de la citation": "Quote text",
    "Auteur": "Author",
    "Couleur du trait": "Line color"
}

JSON,
            'README.md' => <<<'MD'
# {{name}}

Plugin GeniusPress.

## Structure

- `plugin.json` : nom, version, compatibilité, fichiers chargés.
- `src/Plugin.php` : point d'entrée ; déclare les widgets et les hooks.
- `src/Widget.php` : réglages autorisés et rendu HTML du widget sur le site.
- `public/builder.js` : contrôles et aperçu du widget dans le builder.
- `public/site.css` : styles du widget sur le site public.
- `lang/en.json` : traductions de l'interface (les textes source sont en français).

## Développement

1. Activez le plugin dans **Admin → Plugins**.
2. Modifiez les fichiers, puis rechargez le builder.
3. Si le plugin lève une erreur au chargement, il est désactivé automatiquement et l'erreur s'affiche dans **Admin → Plugins**.

MD,
        ];
    }
}
