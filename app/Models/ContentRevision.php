<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** Version enregistrée d'un article ou d'un gabarit du thème. */
#[Fillable(['user_id', 'content', 'note'])]
class ContentRevision extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['content' => 'array'];
    }

    public function revisable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
