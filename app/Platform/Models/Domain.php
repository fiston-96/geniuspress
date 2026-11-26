<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_id', 'name', 'status', 'registrar', 'registrar_reference', 'registered_at', 'expires_at'])]
class Domain extends Model
{
    protected $table = 'platform_domains';

    protected function casts(): array
    {
        return ['registered_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
