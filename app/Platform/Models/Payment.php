<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Tentative de paiement d'une commande auprès d'un prestataire. */
#[Fillable(['order_id', 'gateway', 'method', 'operator', 'phone', 'reference', 'gateway_reference', 'status', 'amount', 'currency', 'failure'])]
class Payment extends Model
{
    protected $table = 'platform_payments';

    protected function casts(): array
    {
        return ['amount' => 'integer'];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
