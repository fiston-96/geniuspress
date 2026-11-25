<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * Listes de l'administration (pages, articles, gabarits) : toutes les colonnes sauf le
 * contenu du builder (arbres JSON volumineux). Moins de données lues, et MySQL peut
 * trier sans manquer de mémoire (« Out of sort memory » avec le tampon par défaut).
 */
trait ListsWithoutContent
{
    /** @var array<string, string[]> colonnes par table */
    private static array $listColumns = [];

    public function scopeWithoutContent(Builder $query): Builder
    {
        $table = $this->getTable();
        self::$listColumns[$table] ??= array_values(array_diff(Schema::getColumnListing($table), ['content', 'draft_content']));

        return $query->select(array_map(fn ($c) => $table.'.'.$c, self::$listColumns[$table]));
    }
}
