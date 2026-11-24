<?php

namespace App\Models;

use App\Site\Permalinks;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/** Catégorie ou étiquette d'articles, dans une langue du site. */
#[Fillable(['taxonomy', 'name', 'slug', 'locale', 'translation_group', 'description', 'parent_id'])]
class Term extends Model
{
    public const CATEGORY = 'category';
    public const TAG = 'tag';
    public const TAXONOMIES = [self::CATEGORY, self::TAG];

    protected static function booted(): void
    {
        static::creating(function (Term $term) {
            $term->translation_group ??= (string) Str::uuid();
            $term->locale ??= ((array) Setting::read('locales', ['fr']))[0] ?? 'fr';
            $term->slug = $term->slug ?: Str::limit(Str::slug($term->name), 120, '');
        });
    }

    public function url(): string
    {
        return Permalinks::term($this);
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'parent_id');
    }
}
