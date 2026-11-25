<?php

namespace App\Builder;

use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Str;

/**
 * Couleurs et polices globales du site, partagées par toutes les pages et par
 * l'éditeur : variables CSS (--gp-primary, --gp-color-…, --gp-heading-font) et
 * chargement des polices.
 *
 * Un élément peut utiliser une couleur globale (« global:primary »,
 * « global:{id} ») : la modifier dans les réglages met tout le site à jour.
 */
class DesignTokens
{
    public const MAX_CUSTOM_COLORS = 24;

    /** Couleurs du site : les trois couleurs de base puis les couleurs personnalisées. */
    public static function colors(array $settings): array
    {
        $colors = [
            ['id' => 'primary', 'name' => __('Principale'), 'value' => $settings['primary_color'] ?? '#2563eb'],
            ['id' => 'secondary', 'name' => __('Secondaire'), 'value' => $settings['secondary_color'] ?? '#0f172a'],
            ['id' => 'accent', 'name' => __('Accent'), 'value' => $settings['accent_color'] ?? '#f59e0b'],
        ];

        return [...$colors, ...self::customColors($settings['custom_colors'] ?? [])];
    }

    /**
     * Valide une liste de couleurs personnalisées [{id, name, value}].
     * Un identifiant manquant est créé à partir du nom ; il ne change plus ensuite,
     * pour que les éléments qui utilisent la couleur la retrouvent.
     */
    public static function customColors(mixed $rows): array
    {
        $out = [];
        $ids = ['primary' => true, 'secondary' => true, 'accent' => true];
        foreach (array_slice(is_array($rows) ? array_values($rows) : [], 0, self::MAX_CUSTOM_COLORS) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = Str::limit(trim(strip_tags((string) ($row['name'] ?? ''))), 40, '');
            $value = ContentSanitizer::color($row['value'] ?? null);
            if ($name === '' || ! $value || str_starts_with($value, 'global:')) {
                continue;
            }
            $id = is_string($row['id'] ?? null) && preg_match('/^[a-z0-9-]{1,40}$/', $row['id']) ? $row['id'] : (Str::slug($name) ?: 'couleur');
            $base = $id;
            for ($i = 2; isset($ids[$id]); $i++) {
                $id = $base.'-'.$i;
            }
            $ids[$id] = true;
            $out[] = ['id' => $id, 'name' => $name, 'value' => $value];
        }

        return $out;
    }

    public static function headingFont(array $settings): ?string
    {
        $font = $settings['heading_font'] ?? null;

        return in_array($font, SettingController::FONTS, true) ? $font : null;
    }

    /** Variables CSS à placer dans :root. */
    /** Transitions entre les pages proposées (images clés gp-pt-… dans site.css). */
    public const PAGE_TRANSITIONS = ['fade', 'slide', 'zoom'];

    /**
     * Transition entre les pages du site, sans script : le navigateur enchaîne l'ancienne et la
     * nouvelle page (View Transitions, ignoré sans effet par les navigateurs qui ne l'ont pas).
     * L'en-tête du thème reste en place pendant que le contenu change.
     */
    public static function pageTransition(array $settings): string
    {
        $type = $settings['page_transition'] ?? null;
        if (! in_array($type, self::PAGE_TRANSITIONS, true)) {
            return '';
        }
        $ms = max(150, min(1500, (int) ($settings['page_transition_duration'] ?? 400)));

        return '@media (prefers-reduced-motion: no-preference){@view-transition{navigation:auto}'
            .'.gp-theme-header{view-transition-name:gp-site-header}'
            ."::view-transition-old(root){animation:gp-pt-{$type}-out ".(int) round($ms * 0.6)."ms ease-in both}"
            ."::view-transition-new(root){animation:gp-pt-{$type}-in {$ms}ms cubic-bezier(.22,1,.36,1) both}}";
    }

    public static function css(array $settings): string
    {
        $vars = [
            '--gp-primary' => $settings['primary_color'] ?? '#2563eb',
            '--gp-secondary' => $settings['secondary_color'] ?? '#0f172a',
            '--gp-accent' => $settings['accent_color'] ?? '#f59e0b',
            '--gp-font' => self::stack($settings['font_family'] ?? 'Inter'),
        ];
        if ($heading = self::headingFont($settings)) {
            $vars['--gp-heading-font'] = self::stack($heading);
        }
        foreach (self::customColors($settings['custom_colors'] ?? []) as $c) {
            $vars['--gp-color-'.$c['id']] = $c['value'];
        }

        return ':root{'.implode(';', array_map(fn ($k, $v) => "{$k}:{$v}", array_keys($vars), $vars)).'}';
    }

    /** Adresse de la feuille de polices (police du texte, des titres et polices utilisées par les éléments). */
    public static function fontsUrl(array $settings, array $extra = []): string
    {
        $families = array_values(array_unique(array_filter([
            $settings['font_family'] ?? 'Inter',
            self::headingFont($settings),
            ...array_filter($extra, fn ($f) => in_array($f, SettingController::FONTS, true)),
        ])));

        return 'https://fonts.bunny.net/css?family='.implode('|', array_map(fn ($f) => urlencode(strtolower($f)).':400,500,600,700,800', $families)).'&display=swap';
    }

    private static function stack(string $font): string
    {
        return "'".str_replace("'", '', $font)."', system-ui, -apple-system, 'Segoe UI', sans-serif";
    }
}
