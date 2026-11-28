<?php

namespace App\View;

/**
 * Icônes d'interface (Lucide) de l'administration, de l'espace client et du builder :
 * resources/js/builder/ui-icons.json, construit par scripts/icons/build-ui-icons.mjs.
 */
final class UiIcons
{
    private static ?array $set = null;

    /** Contenu SVG (formes) d'une icône, ou null si elle n'existe pas. */
    public static function body(string $name): ?string
    {
        self::$set ??= json_decode((string) file_get_contents(resource_path('js/builder/ui-icons.json')), true) ?: [];

        return self::$set[$name] ?? null;
    }
}
