<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Demande de retrait du solde de parrainage par Mobile Money, traitée dans la console. */
#[Fillable(['customer_id', 'amount', 'currency', 'method', 'mobile_operator', 'phone', 'status', 'transfer_reference', 'note', 'operator_id', 'processed_at'])]
class Payout extends Model
{
    protected $table = 'platform_payouts';

    protected function casts(): array
    {
        return ['amount' => 'integer', 'processed_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
