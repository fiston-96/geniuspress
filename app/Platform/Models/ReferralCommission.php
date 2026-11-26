<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Commission d’affiliation sur la première commande payée d'un client apporté (App\Platform\Referrals\Referrals). */
#[Fillable(['sponsor_id', 'referred_id', 'order_id', 'base_amount', 'amount', 'currency', 'rate_bps', 'status', 'available_at', 'released_at', 'canceled_at', 'cancel_reason'])]
class ReferralCommission extends Model
{
    protected $table = 'platform_referral_commissions';

    protected function casts(): array
    {
        return ['available_at' => 'datetime', 'released_at' => 'datetime', 'canceled_at' => 'datetime', 'base_amount' => 'integer', 'amount' => 'integer', 'rate_bps' => 'integer'];
    }

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'sponsor_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
