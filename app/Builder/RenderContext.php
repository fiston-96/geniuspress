<?php

namespace App\Builder;

use App\Models\Media;

/**
 * Contexte passé aux widgets pendant le rendu : collecte du CSS et accès
 * aux médias de la page (préchargés en une seule requête).
 */
class RenderContext
{
    private array $css = [];
    private array $cssMobile = [];

    /** @param array<int, Media> $media */
    public function __construct(private array $media = []) {}

    /**
     * Ajoute une règle CSS pour l'élément #gp-{id}{suffixe}.
     * Les valeurs nulles ou vides sont ignorées. $mobile : uniquement sous 768 px.
     */
    public function rule(string $id, array $props, bool $mobile = false, string $suffix = ''): void
    {
        $props = array_filter($props, fn ($v) => $v !== null && $v !== '');
        if (! $props) {
            return;
        }
        $body = implode(';', array_map(fn ($k, $v) => "$k:$v", array_keys($props), $props));
        $line = "#gp-{$id}{$suffix}{{$body}}";
        $mobile ? $this->cssMobile[] = $line : $this->css[] = $line;
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
        $swap = fn ($l) => str_replace("#gp-{$id}", "#{$htmlId}", $l);
        $this->css = array_map($swap, $this->css);
        $this->cssMobile = array_map($swap, $this->cssMobile);
    }

    public function stylesheet(): string
    {
        $css = implode("\n", $this->css);
        if ($this->cssMobile) {
            $css .= "\n@media (max-width: 767px) {\n".implode("\n", $this->cssMobile)."\n}";
        }

        return $css;
    }
}
