<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'customer_id', 'status', 'total', 'currency', 'paid_at'])]
class Order extends Model
{
    protected $table = 'platform_orders';

    protected function casts(): array
    {
        return ['paid_at' => 'datetime', 'total' => 'integer'];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
