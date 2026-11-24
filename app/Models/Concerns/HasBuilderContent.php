<?php

namespace App\Models\Concerns;

use App\Models\ContentRevision;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Contenu édité dans le builder (articles, gabarits) : la version en ligne dans
 * « content », les modifications non publiées dans « draft_content », et un
 * historique des versions dans content_revisions.
 */
trait HasBuilderContent
{
    public static function emptyContent(): array
    {
        return ['version' => 2, 'elements' => []];
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

    public function revisions(): MorphMany
    {
        return $this->morphMany(ContentRevision::class, 'revisable')->latest('id');
    }
}
