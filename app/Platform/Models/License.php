<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Clé de licence d'un site : seule l'empreinte de la clé est enregistrée. */
#[Fillable(['customer_id', 'name', 'key_prefix', 'key_hash', 'site_url', 'status', 'monthly_cap', 'last_used_at', 'last_ip', 'revoked_at'])]
#[Hidden(['key_hash'])]
class License extends Model
{
    protected $table = 'platform_licenses';

    protected function casts(): array
    {
        return ['last_used_at' => 'datetime', 'revoked_at' => 'datetime', 'monthly_cap' => 'integer'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(AiUsage::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Début de la clé, affichable (la partie secrète n'est jamais connue après la création). */
    public function displayKey(): string
    {
        return $this->key_prefix.'_…';
    }
}
