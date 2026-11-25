<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Models\Term;
use Illuminate\Support\Collection;

/** Liste des catégories ou des étiquettes du blog, avec le nombre d'articles (liste, pastilles ou nuage). */
class TermsListWidget extends ThemeWidget
{
    public function type(): string { return 'terms-list'; }

    public function label(): string { return __('Catégories et étiquettes'); }

    public function rules(): array
    {
        return [
            'taxonomy' => 'enum:category,tag',
            'layout' => 'enum:list,inline,cloud',
            'show_count' => 'bool',
            'hide_empty' => 'bool',
            'order' => 'enum:name,count',
            'limit' => 'int:1:100',
            'align' => 'enum:left,center,right@r',
            'font_size' => 'int:10:40@r',
            'color' => 'color',
            'accent' => 'color',
        ];
    }

    public function aiHint(): ?string
    {
        return 'List of blog categories (taxonomy "category") or tags (taxonomy "tag") with post counts. Use it in a blog sidebar.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['--gp-terms-accent' => $s['accent'] ?? null, 'color' => $s['color'] ?? null], false, ' .gp-terms');
        $ctx->responsive($id, $s, 'align', fn ($v) => ['justify-content' => ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'][$v], 'text-align' => $v], ' .gp-terms');
        $ctx->responsive($id, $s, 'font_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-terms');

        $locale = $this->locale($ctx);
        $taxonomy = $s['taxonomy'] ?? Term::CATEGORY;
        $items = $this->items($ctx, $taxonomy, $locale);
        if ($s['hide_empty'] ?? true) {
            $items = $items->filter(fn ($i) => $i['count'] > 0);
        }
        $items = (($s['order'] ?? 'name') === 'count'
            ? $items->sortBy([['count', 'desc'], fn ($a, $b) => strcasecmp($a['term']->name, $b['term']->name)])
            : $items->sortBy(fn ($i) => mb_strtolower($i['term']->name)))->take($s['limit'] ?? 30)->values();
        if ($items->isEmpty()) {
            return '<p class="gp-terms__empty">'.e($taxonomy === Term::TAG ? __('Aucune étiquette pour le moment.', [], $locale) : __('Aucune catégorie pour le moment.', [], $locale)).'</p>';
        }

        $layout = $s['layout'] ?? 'list';
        [$min, $max] = [$items->min('count'), $items->max('count')];
        $current = $ctx->site?->term;
        $html = '<ul class="gp-terms gp-terms--'.e($layout).'">';
        foreach ($items as ['term' => $term, 'count' => $count]) {
            // Nuage : la taille suit le nombre d'articles (0 à 1).
            $weight = $layout === 'cloud' ? ' style="--gp-w:'.($max > $min ? round(($count - $min) / ($max - $min), 2) : 0.5).'"' : '';
            $isCurrent = $current && $current->id !== null && $current->id === $term->id;
            $html .= '<li><a class="gp-terms__link" href="'.e($term->url()).'"'.($isCurrent ? ' aria-current="page"' : '').$weight.'>'
                .'<span class="gp-terms__name">'.e($term->name).'</span>'
                .(($s['show_count'] ?? true) ? '<span class="gp-terms__count">'.$count.'</span>' : '')
                .'</a></li>';
        }

        return $html.'</ul>';
    }

    /** @return Collection<int, array{term: Term, count: int}> */
    private function items(RenderContext $ctx, string $taxonomy, string $locale): Collection
    {
        if ($ctx->site?->library !== null) {
            // Démonstration d'un modèle : articles en mémoire.
            $relation = $taxonomy === Term::TAG ? 'tags' : 'categories';
            $items = [];
            foreach ($ctx->site->library->where('locale', $locale) as $post) {
                foreach ($post->{$relation} as $term) {
                    $key = $term->id ?? $term->slug;
                    $items[$key] ??= ['term' => $term, 'count' => 0];
                    $items[$key]['count']++;
                }
            }

            return collect(array_values($items));
        }

        return Term::query()->where('taxonomy', $taxonomy)->where('locale', $locale)
            ->withCount(['posts' => fn ($q) => $q->published()])->limit(500)->get()
            ->map(fn (Term $t) => ['term' => $t, 'count' => (int) $t->posts_count]);
    }
}
