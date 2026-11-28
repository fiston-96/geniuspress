<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bloc réutilisable du builder (« Mes blocs ») : un élément (conteneur avec son
 * contenu, ou widget) au format 2, déjà passé par ContentSanitizer.
 */
class BuilderBlock extends Model
{
    public const MAX = 200;

    protected $fillable = ['name', 'node', 'user_id'];

    protected function casts(): array
    {
        return ['node' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Données envoyées au builder. */
    public function toBuilder(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'node' => $this->node,
            'user' => $this->user?->name,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
