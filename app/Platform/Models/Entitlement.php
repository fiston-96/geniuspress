<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Accès acheté par un client (aujourd'hui : un modèle de site payant). */
#[Fillable(['customer_id', 'kind', 'reference', 'order_id', 'expires_at'])]
class Entitlement extends Model
{
    protected $table = 'platform_entitlements';

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
