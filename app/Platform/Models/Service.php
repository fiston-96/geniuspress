<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Service partenaire d'un client (hébergement web, mail pro, stockage), acheté chez un
 * fournisseur. « pending_action » (create, renew) : travail à faire par l'équipe dans
 * l'espace du fournisseur, listé dans la console (file de livraison).
 */
#[Fillable(['customer_id', 'plan_id', 'order_id', 'kind', 'provider', 'provider_ref', 'domain', 'request', 'status', 'pending_action', 'error', 'note', 'delivered_at', 'expires_at', 'synced_at', 'reminded_days'])]
class Service extends Model
{
    protected $table = 'platform_services';

    protected function casts(): array
    {
        return ['delivered_at' => 'datetime', 'expires_at' => 'datetime', 'synced_at' => 'datetime', 'reminded_days' => 'integer'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function label(): string
    {
        return ($this->plan?->label() ?? \App\Platform\Labels::kind($this->kind)).($this->domain ? ' · '.$this->domain : '');
    }
}
