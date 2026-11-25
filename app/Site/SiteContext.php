<?php

namespace App\Site;

use App\Models\Page;
use App\Models\Post;
use App\Models\Term;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Ce que le visiteur est en train de regarder : une page, un article, une archive
 * (blog, catégorie, étiquette), des résultats de recherche ou une page introuvable.
 *
 * Il sert à choisir les gabarits du thème (ThemeLayouts) et aux widgets dynamiques
 * (titre de l'article, grille d'articles…) pendant le rendu.
 */
final class SiteContext
{
    public const PAGE = 'page';
    public const POST = 'post';
    public const BLOG = 'blog';
    public const CATEGORY = 'category';
    public const TAG = 'tag';
    public const SEARCH = 'search';
    public const NOT_FOUND = 'not_found';

    /** Vrai pendant le rendu du contenu d'un article : empêche un widget « contenu » de s'inclure lui-même. */
    public bool $insideContent = false;

    public function __construct(
        public readonly string $kind,
        public readonly string $locale,
        public readonly ?Page $page = null,
        public readonly ?Post $post = null,
        public readonly ?Term $term = null,
        public readonly ?string $search = null,
        public readonly bool $preview = false,
    ) {}

    /** @var (\Closure(): LengthAwarePaginator)|null liste des articles, chargée seulement si on s'en sert */
    private ?\Closure $postsResolver = null;

    private ?LengthAwarePaginator $postsPage = null;

    /**
     * Tous les articles disponibles, en mémoire (démonstration d'un modèle) : les widgets
     * s'en servent au lieu d'interroger la base. Null sur un vrai site.
     *
     * @var \Illuminate\Support\Collection<int, Post>|null
     */
    public ?\Illuminate\Support\Collection $library = null;

    /** Article précédent et suivant (dans la bibliothèque en mémoire, sinon dans la base). */
    public function adjacent(Post $post): array
    {
        if ($this->library === null) {
            return PostNavigation::for($post);
        }
        $list = $this->library->where('locale', $post->locale)->sortBy(fn ($p) => [$p->published_at?->timestamp ?? 0, $p->id])->values();
        $i = $list->search(fn ($p) => $p->id === $post->id);

        return ['previous' => $i === false ? null : $list->get($i - 1), 'next' => $i === false ? null : $list->get($i + 1)];
    }

    public function postsUsing(\Closure $resolver): self
    {
        $this->postsResolver = $resolver;
        $this->postsPage = null;

        return $this;
    }

    /** Articles de l'archive, de la recherche ou du blog, paginés (null s'il n'y en a pas ici). */
    public function posts(): ?LengthAwarePaginator
    {
        return $this->postsPage ??= $this->postsResolver ? ($this->postsResolver)() : null;
    }

    public static function forPage(Page $page, bool $preview = false): self
    {
        return new self(self::PAGE, $page->locale, page: $page, preview: $preview);
    }

    public function isHome(): bool
    {
        return $this->kind === self::PAGE && $this->page?->is_home;
    }

    public function isArchive(): bool
    {
        return in_array($this->kind, [self::BLOG, self::CATEGORY, self::TAG, self::SEARCH], true);
    }

    /** Titre affiché (balise <title>, titre d'archive). */
    public function title(): string
    {
        return match ($this->kind) {
            self::PAGE => $this->page->title,
            self::POST => $this->post->title,
            self::CATEGORY, self::TAG => $this->term->name,
            self::BLOG => Permalinks::blogTitle($this->locale),
            self::SEARCH => __('Résultats pour « :q »', ['q' => $this->search], $this->locale),
            default => __('Page introuvable', [], $this->locale),
        };
    }

    public function metaTitle(string $siteName): string
    {
        $custom = $this->page?->meta_title ?? $this->post?->meta_title;

        return $custom ?: $this->title().' · '.$siteName;
    }

    public function metaDescription(): ?string
    {
        return match ($this->kind) {
            self::PAGE => $this->page->meta_description,
            self::POST => $this->post->meta_description ?: $this->post->summary(160),
            self::CATEGORY, self::TAG => $this->term->description ? \Illuminate\Support\Str::limit(strip_tags($this->term->description), 160) : null,
            default => null,
        };
    }

    /** Une copie pour le rendu du contenu d'un article (sans récursion). */
    public function forContent(): self
    {
        $copy = clone $this;
        $copy->insideContent = true;

        return $copy;
    }
}
