<?php

namespace App\Plugins;

use Throwable;

/**
 * Points d'accroche (hooks) pour les plugins.
 *
 *  - Événements : Hooks::listen('page.published', fn (Page $page) => …);
 *                 Hooks::dispatch('page.published', $page);
 *  - Filtres    : Hooks::addFilter('page.head', fn (string $html, ?Page $page, SiteContext $site) => $html.'…');
 *                 $html = Hooks::filter('page.head', '', $page, $site);
 *                 (« page.head » et « page.body_end » s'appliquent à tout le site public : $page est
 *                 null sur un article, une archive, la recherche ou la page introuvable.)
 *
 * Une fonction de plugin qui lève une exception est ignorée (et journalisée)
 * pour ne jamais casser le site.
 */
class Hooks
{
    /** @var array<string, array<int, list<callable>>> */
    private static array $listeners = [];

    /** @var array<string, array<int, list<callable>>> */
    private static array $filters = [];

    public static function listen(string $event, callable $callback, int $priority = 10): void
    {
        self::$listeners[$event][$priority][] = $callback;
    }

    public static function dispatch(string $event, mixed ...$args): void
    {
        foreach (self::sorted(self::$listeners[$event] ?? []) as $callback) {
            try {
                $callback(...$args);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    public static function addFilter(string $name, callable $callback, int $priority = 10): void
    {
        self::$filters[$name][$priority][] = $callback;
    }

    public static function filter(string $name, mixed $value, mixed ...$args): mixed
    {
        foreach (self::sorted(self::$filters[$name] ?? []) as $callback) {
            try {
                $value = $callback($value, ...$args);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $value;
    }

    public static function has(string $name): bool
    {
        return ! empty(self::$listeners[$name]) || ! empty(self::$filters[$name]);
    }

    /** Vide tous les hooks (utilisé par les tests). */
    public static function flush(): void
    {
        self::$listeners = self::$filters = [];
    }

    private static function sorted(array $byPriority): array
    {
        ksort($byPriority);

        return array_merge(...array_values($byPriority ?: [[]]));
    }
}
