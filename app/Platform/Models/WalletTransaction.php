<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Mouvement du solde d’affiliation d'un client (grand livre par devise, App\Platform\Referrals\Wallet). */
#[Fillable(['customer_id', 'currency', 'amount', 'balance_after', 'kind', 'reference', 'description', 'operator_id'])]
class WalletTransaction extends Model
{
    protected $table = 'platform_wallet_transactions';

    protected function casts(): array
    {
        return ['amount' => 'integer', 'balance_after' => 'integer'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
