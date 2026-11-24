<?php

namespace App\Builder;

use App\Models\Media;

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

    /** @param array<int, Media> $media */
    public function __construct(private array $media = []) {}

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
                $this->rule($id, $props($v), $device, $suffix);
            }
        }
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
        foreach ($this->css as $device => $lines) {
            $this->css[$device] = preg_replace($pattern, '#'.$htmlId, $lines);
        }
    }

    public function stylesheet(): string
    {
        $css = implode("\n", $this->css['desktop']);
        foreach (Schema::BREAKPOINTS as $device => $max) {
            if ($this->css[$device]) {
                $css .= "\n@media (max-width: {$max}px) {\n".implode("\n", $this->css[$device])."\n}";
            }
        }

        return $css;
    }
}
