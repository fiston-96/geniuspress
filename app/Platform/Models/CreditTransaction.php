<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Mouvement du grand livre des crédits IA (µ$). */
#[Fillable(['customer_id', 'amount', 'balance_after', 'kind', 'reference', 'description', 'user_id', 'operator_id'])]
class CreditTransaction extends Model
{
    protected $table = 'platform_credit_transactions';

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'amount' => 'integer', 'balance_after' => 'integer'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
