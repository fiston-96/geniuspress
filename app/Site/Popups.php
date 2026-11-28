<?php

namespace App\Site;

use App\Builder\ContentSanitizer;
use App\Builder\Renderer;
use App\Models\Layout;

/**
 * Popups : gabarits du thème de type « popup », conçus dans le builder.
 *
 * Ils s'affichent là où leurs conditions correspondent (comme un en-tête), plusieurs
 * à la fois si besoin, et s'ouvrent selon leur déclencheur : au chargement, après un
 * délai, après un défilement, à l'intention de quitter la page (ordinateur) ou au clic
 * sur un lien « #popup-{id} ». La fréquence (à chaque visite, une fois par session,
 * tous les N jours, une seule fois) est retenue dans le navigateur du visiteur
 * (resources/js/site.js). Un lien « #fermer » dans la popup la ferme.
 */
final class Popups
{
    public const MAX_PER_PAGE = 5;

    public const DEFAULTS = [
        'trigger' => 'delay',
        'delay' => 5,
        'scroll' => 50,
        'frequency' => 'days',
        'days' => 7,
        'devices' => 'all',
        'position' => 'center',
        'animation' => 'zoom',
        'width' => 560,
        'radius' => 16,
        'background' => '#ffffff',
        'overlay' => true,
        'overlay_color' => 'rgba(15,23,42,.6)',
        'close_outside' => true,
        'close_button' => true,
    ];

    public const TRIGGERS = ['load', 'delay', 'scroll', 'exit', 'click'];

    public const FREQUENCIES = ['always', 'session', 'days', 'once'];

    public const POSITIONS = ['center', 'top', 'bottom', 'bottom-left', 'bottom-right', 'left', 'right'];

    public const ANIMATIONS = ['fade', 'zoom', 'slide-up', 'slide-down', 'slide-left', 'slide-right'];

    public const DEVICES = ['all', 'desktop', 'mobile'];

    /** Réglages complets et valides (valeurs par défaut pour ce qui manque ou est invalide). */
    public static function sanitize(mixed $input): array
    {
        $in = is_array($input) ? $input : [];
        $pick = fn (string $key, array $allowed) => in_array($in[$key] ?? null, $allowed, true) ? $in[$key] : self::DEFAULTS[$key];
        $int = fn (string $key, int $min, int $max) => is_numeric($in[$key] ?? null) ? max($min, min($max, (int) $in[$key])) : self::DEFAULTS[$key];
        $bool = fn (string $key) => array_key_exists($key, $in) ? filter_var($in[$key], FILTER_VALIDATE_BOOLEAN) : self::DEFAULTS[$key];

        return [
            'trigger' => $pick('trigger', self::TRIGGERS),
            'delay' => $int('delay', 0, 300),
            'scroll' => $int('scroll', 5, 100),
            'frequency' => $pick('frequency', self::FREQUENCIES),
            'days' => $int('days', 1, 365),
            'devices' => $pick('devices', self::DEVICES),
            'position' => $pick('position', self::POSITIONS),
            'animation' => $pick('animation', self::ANIMATIONS),
            'width' => $int('width', 260, 1400),
            'radius' => $int('radius', 0, 60),
            'background' => ContentSanitizer::color($in['background'] ?? null) ?? self::DEFAULTS['background'],
            'overlay' => $bool('overlay'),
            'overlay_color' => ContentSanitizer::color($in['overlay_color'] ?? null) ?? self::DEFAULTS['overlay_color'],
            'close_outside' => $bool('close_outside'),
            'close_button' => $bool('close_button'),
        ];
    }

    /**
     * Popups qui s'appliquent à ce que regarde le visiteur, rendus (HTML, CSS, polices).
     * Dans l'aperçu d'une popup, celle-ci s'ouvre tout de suite, à chaque fois.
     *
     * @return list<array{id: int, name: string, options: array, html: string, css: string, fonts: string[], preview: bool}>
     */
    public static function forSite(SiteContext $site, ThemeLayouts $layouts, Renderer $renderer): array
    {
        $out = [];
        foreach ($layouts->all('popup', $site, self::MAX_PER_PAGE) as $layout) {
            /** @var Layout $layout */
            $forced = $layouts->isForced($layout);
            $options = self::sanitize($layout->options);
            if ($forced) {
                $options = ['trigger' => 'load', 'delay' => 0, 'frequency' => 'always', 'devices' => 'all'] + $options;
            }
            $out[] = ['id' => (int) $layout->id, 'name' => $layout->name, 'options' => $options, 'preview' => $forced]
                + $renderer->render($layout->content, null, $site);
        }

        return $out;
    }
}
