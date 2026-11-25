<?php

namespace App\Site;

use App\Models\Layout;
use Illuminate\Support\Collection;

/**
 * Choisit le gabarit du thème à utiliser pour ce que regarde le visiteur.
 *
 * Conditions d'un gabarit : liste de règles { mode: include|exclude, target, id? } :
 *   all                tout le site
 *   front              page d'accueil
 *   page    [id]       toutes les pages, ou une page
 *   post    [id]       tous les articles, ou un article
 *   in_category id     les articles d'une catégorie
 *   blog               la liste des articles
 *   category [id]      toutes les catégories, ou une catégorie
 *   tag      [id]      toutes les étiquettes, ou une étiquette
 *   search             les résultats de recherche
 *   not_found          la page introuvable
 *
 * Un gabarit s'applique si au moins une règle « include » correspond et aucune
 * règle « exclude ». Entre plusieurs gabarits : la priorité la plus haute, puis
 * la règle la plus précise, puis le plus récent.
 */
final class ThemeLayouts
{
    public const TARGETS = ['all', 'front', 'page', 'post', 'in_category', 'blog', 'category', 'tag', 'search', 'not_found'];

    /** Cibles qui acceptent un identifiant (page, article, catégorie…). */
    public const TARGETS_WITH_ID = ['page', 'post', 'in_category', 'category', 'tag'];

    /** @var array<string, Collection> gabarits actifs chargés une fois par requête, par type */
    private array $cache = [];

    /** Gabarit imposé pour son type (aperçu dans le builder, même s'il n'est pas actif). */
    private ?Layout $forced = null;

    /** Gabarits fournis en mémoire (démonstration d'un modèle) au lieu de la base. */
    public function useLayouts(Collection $layouts): void
    {
        foreach (Layout::TYPES as $type) {
            $this->cache[$type] = $layouts->where('type', $type)->where('status', Layout::STATUS_PUBLISHED)->values();
        }
    }

    public function force(Layout $layout): void
    {
        $this->forced = $layout;
    }

    public function find(string $type, SiteContext $site): ?Layout
    {
        if ($this->forced?->type === $type) {
            return $this->forced;
        }
        $best = null;
        $bestScore = null;
        foreach ($this->active($type) as $layout) {
            if ($layout->locale && $layout->locale !== $site->locale) {
                continue;
            }
            $score = $this->match((array) $layout->conditions, $site);
            if ($score === null) {
                continue;
            }
            $rank = [$layout->priority, $score, $layout->id];
            if ($bestScore === null || $rank > $bestScore) {
                [$best, $bestScore] = [$layout, $rank];
            }
        }

        return $best;
    }

    private function active(string $type): Collection
    {
        return $this->cache[$type] ??= Layout::query()->where('type', $type)->where('status', Layout::STATUS_PUBLISHED)->get();
    }

    /** Précision de la meilleure règle « include » qui correspond, ou null si le gabarit ne s'applique pas. */
    public function match(array $conditions, SiteContext $site): ?int
    {
        $best = null;
        foreach ($conditions as $rule) {
            $score = $this->score((string) ($rule['target'] ?? ''), isset($rule['id']) ? (int) $rule['id'] : null, $site);
            if ($score === null) {
                continue;
            }
            if (($rule['mode'] ?? 'include') === 'exclude') {
                return null;
            }
            $best = max($best ?? 0, $score);
        }

        return $best;
    }

    /** 1 : tout le site ; 2 : un ensemble (toutes les pages…) ; 3 : un élément précis. Null : ne correspond pas. */
    private function score(string $target, ?int $id, SiteContext $site): ?int
    {
        $is = fn (bool $ok, int $score) => $ok ? $score : null;

        return match ($target) {
            'all' => 1,
            'front' => $is($site->isHome(), 3),
            'page' => $site->kind === SiteContext::PAGE ? ($id ? $is($site->page->id === $id, 3) : 2) : null,
            'post' => $site->kind === SiteContext::POST ? ($id ? $is($site->post->id === $id, 3) : 2) : null,
            'in_category' => $is($site->kind === SiteContext::POST && $id && $site->post->categories->contains('id', $id), 3),
            'blog' => $is($site->kind === SiteContext::BLOG, 2),
            'category' => $site->kind === SiteContext::CATEGORY ? ($id ? $is($site->term->id === $id, 3) : 2) : null,
            'tag' => $site->kind === SiteContext::TAG ? ($id ? $is($site->term->id === $id, 3) : 2) : null,
            'search' => $is($site->kind === SiteContext::SEARCH, 2),
            'not_found' => $is($site->kind === SiteContext::NOT_FOUND, 2),
            default => null,
        };
    }

    /** Nettoie des conditions reçues d'un formulaire ou d'un import. */
    public static function sanitizeConditions(mixed $input): array
    {
        $out = [];
        foreach (array_slice((array) $input, 0, 30) as $rule) {
            if (! is_array($rule) || ! in_array($rule['target'] ?? null, self::TARGETS, true)) {
                continue;
            }
            $clean = ['mode' => ($rule['mode'] ?? 'include') === 'exclude' ? 'exclude' : 'include', 'target' => $rule['target']];
            $id = (int) ($rule['id'] ?? 0);
            if (in_array($rule['target'], self::TARGETS_WITH_ID, true) && $id > 0) {
                $clean['id'] = $id;
            }
            if ($rule['target'] === 'in_category' && ! isset($clean['id'])) {
                continue;
            }
            $out[] = $clean;
        }

        return $out;
    }
}
