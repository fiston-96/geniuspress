<?php

namespace App\Builder\Widgets;

use App\Builder\RenderContext;
use App\Models\Post;
use App\Site\Permalinks;

/**
 * Grille ou liste d'articles. Source « current » : les articles de l'archive,
 * de la recherche ou du blog affiché (paginés) ; ailleurs, les derniers articles.
 */
class PostsWidget extends ThemeWidget
{
    public function type(): string { return 'posts'; }

    public function label(): string { return __('Grille d’articles'); }

    public function rules(): array
    {
        return [
            'source' => 'enum:current,latest,category',
            'category' => 'int:0:2147483647',
            'count' => 'int:1:24',
            'layout' => 'enum:grid,list',
            'columns' => 'int:1:4@r',
            'gap' => 'int:0:80',
            'style' => 'enum:shadow,border,flat',
            'ratio' => 'enum:16:9,4:3,3:2,1:1',
            'show_image' => 'bool',
            'show_category' => 'bool',
            'show_date' => 'bool',
            'show_excerpt' => 'bool',
            'show_more' => 'bool',
            'more_text' => 'string:40',
            'pagination' => 'bool',
            'title_size' => 'int:10:60@r',
            'accent' => 'color',
        ];
    }

    public function translatable(): array { return ['more_text']; }

    public function aiHint(): ?string
    {
        return 'Grid of the latest blog posts (news, articles). Settings: source "latest", count 3, columns 3. Use it for a "Latest news" section.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $locale = $this->locale($ctx);
        $source = $s['source'] ?? 'current';
        $count = $s['count'] ?? 6;
        $paginator = $source === 'current' ? $ctx->site?->posts() : null;
        if ($paginator) {
            $posts = $paginator->getCollection();
        } elseif ($ctx->site?->library !== null) {
            // Démonstration d'un modèle : articles en mémoire.
            $posts = $ctx->site->library->where('locale', $locale)
                ->when($source === 'category' && ! empty($s['category']), fn ($c) => $c->filter(fn ($p) => $p->categories->contains('id', $s['category'])))
                ->sortByDesc(fn ($p) => $p->published_at?->timestamp ?? 0)->take($count)->values();
        } else {
            $posts = Post::query()->published()->where('locale', $locale)
                ->when($source === 'category' && ! empty($s['category']), fn ($q) => $q->whereHas('terms', fn ($t) => $t->whereKey($s['category'])))
                ->with(['categories', 'featuredMedia'])->orderByDesc('published_at')->orderByDesc('id')->limit($count)->get();
        }
        if ($posts->isEmpty()) {
            return '<p class="gp-plist__empty">'.e(__('Aucun article pour le moment.', [], $locale)).'</p>';
        }

        $layout = $s['layout'] ?? 'grid';
        $ctx->rule($id, [
            'grid-template-columns' => $layout === 'grid' ? 'repeat('.($s['columns'] ?? 3).', minmax(0, 1fr))' : null,
            'gap' => isset($s['gap']) ? $s['gap'].'px' : null,
            '--gp-posts-accent' => $s['accent'] ?? null,
        ], false, ' .gp-plist');
        if ($layout === 'grid') {
            foreach (['tablet', 'mobile'] as $device) {
                $ctx->rule($id, ['grid-template-columns' => 'repeat('.($s['columns_'.$device] ?? ($device === 'tablet' ? min(2, $s['columns'] ?? 3) : 1)).', minmax(0, 1fr))'], $device, ' .gp-plist');
            }
        }
        $ctx->rule($id, ['aspect-ratio' => str_replace(':', ' / ', $s['ratio'] ?? '16:9')], false, ' .gp-plist__media img');
        $ctx->responsive($id, $s, 'title_size', fn ($v) => ['font-size' => $v.'px'], ' .gp-plist__title');

        $more = $s['more_text'] ?? __('Lire la suite', [], $locale);
        $html = '<div class="gp-plist gp-plist--'.$layout.' gp-plist--'.e($s['style'] ?? 'shadow').'">';
        foreach ($posts as $post) {
            $media = ($s['show_image'] ?? true) ? $post->featuredMedia : null;
            $category = ($s['show_category'] ?? true) ? $post->categories->first() : null;
            $html .= '<article class="gp-plist__item">'
                .($media ? '<a class="gp-plist__media" href="'.e($post->url()).'" tabindex="-1" aria-hidden="true"><img src="'.e($media->url).'" alt="" loading="lazy"></a>' : '')
                .'<div class="gp-plist__body">'
                .($category ? '<a class="gp-plist__category" href="'.e($category->url()).'">'.e($category->name).'</a>' : '')
                .'<h3 class="gp-plist__title"><a href="'.e($post->url()).'">'.e($post->title).'</a></h3>'
                .(($s['show_date'] ?? true) ? '<p class="gp-plist__date">'.$this->date($post, $locale).'</p>' : '')
                .(($s['show_excerpt'] ?? true) ? '<p class="gp-plist__excerpt">'.e($post->summary()).'</p>' : '')
                .(($s['show_more'] ?? true) ? '<a class="gp-plist__more" href="'.e($post->url()).'">'.e($more).' <span aria-hidden="true">→</span></a>' : '')
                .'</div></article>';
        }
        $html .= '</div>';

        if ($paginator && ($s['pagination'] ?? true) && $paginator->hasPages()) {
            $html .= view('public.partials.pagination', ['paginator' => $paginator, 'locale' => $locale])->render();
        }

        return $html;
    }
}
