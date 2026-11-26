<?php

namespace App\Builder;

use App\Models\Media;
use App\Site\SiteContext;

/**
 * Contexte passé aux widgets pendant le rendu : collecte du CSS (par appareil),
 * des polices utilisées, et accès aux médias de la page (préchargés en une
 * seule requête).
 */
class RenderContext
{
    /** @var array<string, string[]> règles CSS par appareil */
    private array $css = ['desktop' => [], 'tablet' => [], 'mobile' => []];

    /** @var array<string, true> */
    private array $fonts = [];

    /** @var string[] CSS déjà assemblé (contenu d'un article inclus dans un gabarit) */
    private array $embedded = [];

    /**
     * @param  array<int, Media>  $media
     * @param  int|null  $pageId  page rendue (pour les formulaires), null dans un aperçu sans page
     * @param  SiteContext|null  $site  ce que regarde le visiteur (article, archive…), pour les widgets dynamiques
     */
    public function __construct(private array $media = [], public readonly ?int $pageId = null, public readonly ?SiteContext $site = null) {}

    /** Ajoute le résultat d'un autre rendu (CSS et polices), par exemple le contenu d'un article. */
    public function embed(array $rendered): void
    {
        if (($rendered['css'] ?? '') !== '') {
            $this->embedded[] = $rendered['css'];
        }
        foreach ($rendered['fonts'] ?? [] as $font) {
            $this->fonts[$font] = true;
        }
    }

    /**
     * Ajoute une règle CSS pour l'élément #gp-{id}{suffixe}.
     * Les valeurs nulles ou vides sont ignorées.
     *
     * $device : false ou « desktop » (tous les écrans), « tablet » (1024 px et moins),
     * true ou « mobile » (767 px et moins). Pour le survol, utilisez le suffixe « :hover ».
     */
    public function rule(string $id, array $props, bool|string $device = false, string $suffix = ''): void
    {
        $device = match ($device) {
            true => 'mobile',
            false => 'desktop',
            default => $device,
        };
        $props = array_filter($props, fn ($v) => $v !== null && $v !== '');
        if (! $props || ! isset($this->css[$device])) {
            return;
        }
        $body = implode(';', array_map(fn ($k, $v) => $k.':'.self::value((string) $v), array_keys($props), $props));
        $this->css[$device][] = "#gp-{$id}{$suffix}{{$body}}";
    }

    /**
     * Réglage réglable par appareil (« font_size », « font_size_tablet », « font_size_mobile ») :
     * $props reçoit la valeur et renvoie les propriétés CSS.
     */
    public function responsive(string $id, array $settings, string $key, callable $props, string $suffix = ''): void
    {
        foreach (Schema::DEVICES as $device => $sfx) {
            $v = $settings[$key.$sfx] ?? null;
            if ($v !== null && $v !== '') {
                $this->rule($id, self::fluid($props($v), $settings, $key, $device, $v), $device, $suffix);
            }
        }
    }

    /** Largeur d'écran (px) à laquelle la taille réglée pour l'ordinateur est atteinte. */
    public const FLUID_WIDE = 1440;

    /**
     * Taille de texte fluide : quand une taille est réglée pour deux appareils (ordinateur et
     * tablette, ou tablette et mobile), elle glisse de l'une à l'autre selon la largeur réelle
     * de l'écran au lieu de sauter au seuil (écrans de 14 pouces, tablettes en paysage).
     * Miroir de fluid() dans resources/js/builder/style.js.
     */
    public static function fluid(array $props, array $settings, string $key, string $device, mixed $value): array
    {
        if (! isset($props['font-size']) || ! is_numeric($value) || $props['font-size'] !== $value.'px') {
            return $props;
        }
        $devices = array_keys(Schema::DEVICES);
        foreach (array_slice($devices, array_search($device, $devices, true) + 1) as $smaller) {
            $small = $settings[$key.Schema::DEVICES[$smaller]] ?? null;
            if ($small === null || $small === '') {
                continue;
            }
            if (is_numeric($small) && $small < $value) {
                $lo = Schema::BREAKPOINTS[$smaller];
                $hi = $device === 'desktop' ? self::FLUID_WIDE : Schema::BREAKPOINTS['tablet'];
                $props['font-size'] = "clamp({$small}px, calc({$small}px + ".($value - $small)." * (100vw - {$lo}px) / ".($hi - $lo)."), {$value}px)";
            }
            break;
        }

        return $props;
    }

    /** Réglages de Schema::TYPOGRAPHY appliqués à #gp-{id}{suffixe}. */
    public function typography(string $id, array $s, string $suffix = ''): void
    {
        $this->rule($id, [
            'font-family' => isset($s['font_family']) ? $this->fontFamily($s['font_family']) : null,
            'font-weight' => $s['font_weight'] ?? null,
            'font-style' => $s['font_style'] ?? null,
            'text-transform' => $s['text_transform'] ?? null,
        ], false, $suffix);
        $this->responsive($id, $s, 'line_height', fn ($v) => ['line-height' => $v], $suffix);
        $this->responsive($id, $s, 'letter_spacing', fn ($v) => ['letter-spacing' => $v], $suffix);
    }

    /** Valeur CSS d'une police (« global:heading », « Poppins »…), chargée automatiquement. */
    public function fontFamily(string $family): string
    {
        return match ($family) {
            'global:heading' => 'var(--gp-heading-font)',
            'global:body' => 'var(--gp-font)',
            default => $this->useFont($family),
        };
    }

    private function useFont(string $family): string
    {
        $this->fonts[$family] = true;

        return "'".str_replace("'", '', $family)."', system-ui, sans-serif";
    }

    /** @return string[] polices à charger en plus de celles du site */
    public function fonts(): array
    {
        return array_keys($this->fonts);
    }

    /** Remplace les couleurs globales (« global:primary ») par leur variable CSS. */
    public static function value(string $v): string
    {
        return str_contains($v, 'global:')
            ? preg_replace_callback('/global:([a-z0-9-]+)/', fn ($m) => self::globalColor($m[1]), $v)
            : $v;
    }

    public static function globalColor(string $name): string
    {
        return in_array($name, ['primary', 'secondary', 'accent'], true) ? "var(--gp-{$name})" : "var(--gp-color-{$name})";
    }

    public static function px(?int $v): ?string
    {
        return $v === null ? null : $v.'px';
    }

    public function media(?int $id): ?Media
    {
        return $id ? ($this->media[$id] ?? null) : null;
    }

    /** Remplace #gp-{id} par #{ancre} dans les règles déjà collectées. */
    public function renameId(string $id, string $htmlId): void
    {
        $pattern = '/#gp-'.preg_quote($id, '/').'(?![\w-])/';
        // Une ancre qui commence par un chiffre (« 2024 ») s'échappe en CSS : #\32 024.
        $selector = '#'.(preg_match('/^\d/', $htmlId) ? '\3'.$htmlId[0].' '.substr($htmlId, 1) : $htmlId);
        foreach ($this->css as $device => $lines) {
            $this->css[$device] = preg_replace_callback($pattern, fn () => $selector, $lines);
        }
    }

    public function stylesheet(): string
    {
        // Le CSS inclus (contenu d'un article) vient d'abord : celui du gabarit peut le compléter.
        $css = implode("\n", [...$this->embedded, ...$this->css['desktop']]);
        foreach (Schema::BREAKPOINTS as $device => $max) {
            if ($this->css[$device]) {
                $css .= "\n@media (max-width: {$max}px) {\n".implode("\n", $this->css[$device])."\n}";
            }
        }

        return $css;
    }
}
