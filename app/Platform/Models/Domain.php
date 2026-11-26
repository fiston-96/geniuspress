<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_id', 'name', 'status', 'registrar', 'registrar_reference', 'registered_at', 'expires_at', 'site_slug', 'synced_at', 'reminded_days'])]
class Domain extends Model
{
    protected $table = 'platform_domains';

    protected function casts(): array
    {
        return ['registered_at' => 'datetime', 'expires_at' => 'datetime', 'synced_at' => 'datetime', 'reminded_days' => 'integer'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
