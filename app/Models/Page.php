<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'locale', 'translation_group', 'status', 'is_home', 'content', 'draft_content', 'meta_title', 'meta_description', 'author_id', 'published_at'])]
class Page extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'draft_content' => 'array',
            'is_home' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            $page->translation_group ??= (string) Str::uuid();
            // Langue du contenu = langue par défaut du site (indépendante de la langue de l'interface).
            $page->locale ??= ((array) Setting::read('locales', ['fr']))[0] ?? 'fr';
            $page->content ??= self::emptyContent();
        });
    }

    /** Premiers segments d'adresse réservés au fonctionnement de GeniusPress. */
    public const RESERVED_SLUGS = ['admin', 'install', 'storage', 'build', 'up', 'plugins', 'plugin-assets', 'brand'];

    /**
     * Normalise une adresse de page : « Documentation / Installation » → « documentation/installation ».
     * Les sous-niveaux sont permis (jusqu'à 4) ; chaque segment est mis en minuscules sans accents.
     */
    public static function normalizeSlug(?string $value): string
    {
        $segments = array_filter(array_map(fn ($s) => Str::slug($s), explode('/', (string) $value)));

        return implode('/', array_slice($segments, 0, 4));
    }

    public static function isReservedSlug(string $slug): bool
    {
        return in_array(explode('/', $slug)[0], self::RESERVED_SLUGS, true);
    }

    public static function emptyContent(): array
    {
        return ['version' => 1, 'sections' => []];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /** Contenu à charger dans le builder : le brouillon s'il existe, sinon la version publiée. */
    public function editableContent(): array
    {
        return $this->draft_content ?? $this->content ?? self::emptyContent();
    }

    public function hasUnpublishedChanges(): bool
    {
        return $this->draft_content !== null;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function url(): string
    {
        $default = ((array) Setting::read('locales', ['fr']))[0] ?? 'fr';
        $prefix = $this->locale && $this->locale !== $default ? $this->locale.'/' : '';

        return url($prefix.($this->is_home ? '' : $this->slug));
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest('id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Page::class, 'translation_group', 'translation_group')
            ->where('id', '!=', $this->id);
    }
}
