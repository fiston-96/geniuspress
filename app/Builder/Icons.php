<?php

namespace App\Builder;

/**
 * Icônes livrées avec GeniusPress (resources/builder/icons.json, construit par
 * scripts/icons/build-icons.mjs ; licences dans resources/builder/ICONS-LICENSE.md).
 *
 * Une icône se désigne par « lucide:{nom} » (icône générale) ou
 * « brand:{nom} » (logo d'un réseau social).
 */
class Icons
{
    private static ?array $set = null;

    public static function set(): array
    {
        return self::$set ??= json_decode((string) file_get_contents(resource_path('builder/icons.json')), true) ?: ['lucide' => [], 'brands' => [], 'groups' => []];
    }

    public static function exists(mixed $ref): bool
    {
        if (! is_string($ref) || ! preg_match('/^(lucide|brand):([a-z0-9-]{1,60})$/', $ref, $m)) {
            return false;
        }

        return $m[1] === 'lucide' ? isset(self::set()['lucide'][$m[2]]) : isset(self::set()['brands'][$m[2]]);
    }

    /** SVG en ligne de l'icône (décoratif : masqué des lecteurs d'écran), ou chaîne vide. */
    public static function svg(?string $ref, string $class = 'gp-ico'): string
    {
        if (! self::exists($ref)) {
            return '';
        }
        [$kind, $name] = explode(':', $ref, 2);
        $class = e($class);
        if ($kind === 'brand') {
            $path = self::set()['brands'][$name]['path'];

            return "<svg class=\"{$class}\" viewBox=\"0 0 24 24\" fill=\"currentColor\" aria-hidden=\"true\" focusable=\"false\"><path d=\"".e($path).'"/></svg>';
        }

        return "<svg class=\"{$class}\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" aria-hidden=\"true\" focusable=\"false\">".self::set()['lucide'][$name].'</svg>';
    }

    /** Nom lisible d'un logo (« Facebook »), pour les libellés accessibles. */
    public static function brandTitle(string $name): ?string
    {
        return self::set()['brands'][$name]['title'] ?? null;
    }
}
