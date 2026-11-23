<?php

namespace App\Plugins;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Throwable;

/**
 * Découvre les plugins du dossier plugins/, charge ceux qui sont actifs et
 * gère leur activation. Un plugin qui plante au chargement est désactivé
 * automatiquement et l'erreur est affichée dans l'admin.
 */
class PluginManager
{
    /** @var array<string, PluginManifest>|null */
    private ?array $discovered = null;

    /** @var array<string, Plugin> */
    private array $loaded = [];

    private array $autoload = [];

    public function __construct(private string $directory) {}

    public function directory(): string
    {
        return $this->directory;
    }

    /** @return array<string, PluginManifest> */
    public function discover(): array
    {
        if ($this->discovered !== null) {
            return $this->discovered;
        }

        $this->discovered = [];
        foreach (glob($this->directory.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR) ?: [] as $dir) {
            $manifest = PluginManifest::load($dir);
            $this->discovered[$manifest->slug] = $manifest;
        }
        ksort($this->discovered);

        return $this->discovered;
    }

    public function find(string $slug): ?PluginManifest
    {
        return $this->discover()[$slug] ?? null;
    }

    /** @return list<string> */
    public function activeSlugs(): array
    {
        return array_values((array) Setting::read('active_plugins', []));
    }

    public function isActive(string $slug): bool
    {
        return in_array($slug, $this->activeSlugs(), true);
    }

    /** @return array<string, Plugin> */
    public function loaded(): array
    {
        return $this->loaded;
    }

    /** @return array<string, string> erreurs de chargement par plugin */
    public function errors(): array
    {
        return (array) Setting::read('plugin_errors', []);
    }

    /** Charge les plugins actifs. Appelé une fois au démarrage de l'application. */
    public function bootActive(): void
    {
        try {
            $active = $this->activeSlugs();
        } catch (Throwable) {
            return; // Base pas encore installée.
        }

        foreach ($active as $slug) {
            try {
                $this->load($slug)->boot();
            } catch (Throwable $e) {
                report($e);
                $this->disableAfterFailure($slug, $e);
            }
        }
    }

    public function activate(string $slug): void
    {
        $plugin = $this->load($slug);

        $migrations = $plugin->manifest->path.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
        if (is_dir($migrations)) {
            Artisan::call('migrate', ['--path' => $migrations, '--realpath' => true, '--force' => true]);
        }

        $plugin->boot();
        $plugin->activate();

        $errors = $this->errors();
        unset($errors[$slug]);
        Setting::write([
            'active_plugins' => array_values(array_unique([...$this->activeSlugs(), $slug])),
            'plugin_errors' => $errors,
        ]);
    }

    public function deactivate(string $slug): void
    {
        if (isset($this->loaded[$slug])) {
            try {
                $this->loaded[$slug]->deactivate();
            } catch (Throwable $e) {
                report($e);
            }
        }
        Setting::write(['active_plugins' => array_values(array_diff($this->activeSlugs(), [$slug]))]);
    }

    /** Fichiers d'un plugin actif à charger dans le builder ou sur le site. */
    public function assets(string $kind): array
    {
        $urls = [];
        foreach ($this->loaded as $slug => $plugin) {
            $files = match ($kind) {
                'builder' => $plugin->manifest->builderScripts,
                'site_css' => $plugin->manifest->siteStyles,
                'site_js' => $plugin->manifest->siteScripts,
                default => [],
            };
            foreach ($files as $file) {
                $urls[] = route('plugin.asset', [$slug, $file]).'?v='.rawurlencode($plugin->manifest->version);
            }
        }

        return $urls;
    }

    private function load(string $slug): Plugin
    {
        if (isset($this->loaded[$slug])) {
            return $this->loaded[$slug];
        }

        $manifest = $this->find($slug) ?? throw new RuntimeException("Le plugin « {$slug} » est introuvable dans {$this->directory}.");
        if (! $manifest->isValid()) {
            throw new RuntimeException(implode(' ', $manifest->errors));
        }
        $version = (string) config('geniuspress.version');
        if (! $manifest->isCompatibleWith($version)) {
            throw new RuntimeException("Ce plugin demande GeniusPress {$manifest->requires} ; la version installée est {$version}.");
        }

        $this->registerAutoload($manifest);

        $class = $manifest->main;
        if ($class === null) {
            $plugin = new class($manifest) extends Plugin {};
        } else {
            if (! class_exists($class)) {
                throw new RuntimeException("La classe principale « {$class} » est introuvable. Vérifiez « main » et « autoload » dans plugin.json.");
            }
            if (! is_subclass_of($class, Plugin::class)) {
                throw new RuntimeException("La classe « {$class} » doit hériter de App\\Plugins\\Plugin.");
            }
            $plugin = new $class($manifest);
        }

        return $this->loaded[$slug] = $plugin;
    }

    private function registerAutoload(PluginManifest $manifest): void
    {
        foreach ($manifest->autoload as $namespace => $relative) {
            $this->autoload[rtrim($namespace, '\\').'\\'] = $manifest->path.DIRECTORY_SEPARATOR.trim($relative, '/\\');
        }

        static $registered = [];
        $key = spl_object_id($this);
        if (! isset($registered[$key])) {
            $registered[$key] = true;
            spl_autoload_register(function (string $class) {
                foreach ($this->autoload as $prefix => $dir) {
                    if (str_starts_with($class, $prefix)) {
                        $file = $dir.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix))).'.php';
                        if (is_file($file)) {
                            require_once $file;

                            return;
                        }
                    }
                }
            });
        }
    }

    private function disableAfterFailure(string $slug, Throwable $e): void
    {
        unset($this->loaded[$slug]);
        try {
            Setting::write([
                'active_plugins' => array_values(array_diff($this->activeSlugs(), [$slug])),
                'plugin_errors' => [...$this->errors(), $slug => mb_substr($e->getMessage(), 0, 500)],
            ]);
        } catch (Throwable) {
            // Impossible d'écrire en base : le plugin restera simplement non chargé.
        }
    }
}
