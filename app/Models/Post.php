<?php

namespace App\Models;

use App\Builder\Renderer;
use App\Models\Concerns\HasBuilderContent;
use App\Site\Permalinks;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Article (blog, actualités). Son contenu est un arbre du builder, comme une page.
 * Un article « published » dont la date de publication est future est programmé :
 * il n'apparaît sur le site qu'à cette date.
 */
#[Fillable(['title', 'slug', 'locale', 'translation_group', 'status', 'content', 'draft_content', 'excerpt', 'featured_media_id', 'meta_title', 'meta_description', 'author_id', 'published_at'])]
class Post extends Model
{
    use HasBuilderContent, SoftDeletes, \App\Models\Concerns\ListsWithoutContent;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    /** Adresses impossibles pour un article : elles servent aux archives et au flux. */
    public const RESERVED_SLUGS = ['categorie', 'category', 'etiquette', 'tag', 'rss', 'page', 'feed'];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'draft_content' => 'array',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            $post->translation_group ??= (string) Str::uuid();
            $post->locale ??= ((array) Setting::read('locales', ['fr']))[0] ?? 'fr';
            $post->content ??= self::emptyContent();
        });
    }

    public static function normalizeSlug(?string $value): string
    {
        return Str::limit(Str::slug((string) $value), 120, '');
    }

    /** En ligne : publié, et sa date de publication est passée. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && (! $this->published_at || $this->published_at->lte(now()));
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at?->isFuture();
    }

    public function url(): string
    {
        return Permalinks::post($this);
    }

    /** Résumé : l'extrait s'il est rempli, sinon le début du texte de l'article. */
    public function summary(int $length = 180): string
    {
        if (filled($this->excerpt)) {
            return Str::limit(trim($this->excerpt), $length);
        }

        return Str::limit($this->plainText(), $length);
    }

    /** Texte brut de l'article (titres et paragraphes), pour le résumé et le temps de lecture. */
    public function plainText(): string
    {
        $parts = [];
        Renderer::walk((array) (($this->content ?? [])['elements'] ?? []), function ($node) use (&$parts) {
            $s = $node['settings'] ?? [];
            foreach (['html', 'text'] as $key) {
                if (isset($s[$key]) && is_string($s[$key]) && in_array($node['type'] ?? '', ['text', 'heading'], true)) {
                    $parts[] = html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '</li>'], ' ', $s[$key])), ENT_QUOTES);
                }
            }
        });

        return trim(preg_replace('/\s+/u', ' ', implode(' ', $parts)));
    }

    /** Temps de lecture en minutes (200 mots par minute, 1 au minimum). */
    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count($this->plainText()) / 200));
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(Term::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->terms()->where('taxonomy', Term::CATEGORY)->orderBy('name');
    }

    public function tags(): BelongsToMany
    {
        return $this->terms()->where('taxonomy', Term::TAG)->orderBy('name');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Post::class, 'translation_group', 'translation_group')
            ->where('id', '!=', $this->id);
    }
}
