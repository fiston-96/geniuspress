<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'kind', 'reference', 'description', 'quantity', 'amount', 'meta', 'fulfilled_at', 'error'])]
class OrderItem extends Model
{
    protected $table = 'platform_order_items';

    public const KINDS = ['plan', 'template', 'ai_credits', 'domain_register', 'domain_renew'];

    protected function casts(): array
    {
        return ['meta' => 'array', 'fulfilled_at' => 'datetime', 'amount' => 'integer', 'quantity' => 'integer'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
