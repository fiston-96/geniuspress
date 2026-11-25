<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['customer_id', 'plan_id', 'status', 'trial_ends_at', 'current_period_end', 'canceled_at', 'site_reference'])]
class Subscription extends Model
{
    protected $table = 'platform_subscriptions';

    protected function casts(): array
    {
        return ['trial_ends_at' => 'datetime', 'current_period_end' => 'datetime', 'canceled_at' => 'datetime'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function onTrial(): bool
    {
        return $this->status === 'trialing' && $this->trial_ends_at?->isFuture();
    }

    /** Fin de l'essai ou de la période payée (null : sans échéance). */
    public function endsAt(): ?Carbon
    {
        return $this->status === 'trialing' ? $this->trial_ends_at : $this->current_period_end;
    }

    /** En cours : essai non terminé, ou période payée non échue (une résiliation prend effet à l'échéance). */
    public function isActive(): bool
    {
        return match ($this->status) {
            'trialing' => (bool) $this->trial_ends_at?->isFuture(),
            'active', 'canceled' => $this->current_period_end === null ? $this->status === 'active' : $this->current_period_end->isFuture(),
            default => false,
        };
    }
}
