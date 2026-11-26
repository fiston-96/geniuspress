<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** SMS envoyé (ou non) par la plateforme : jamais le texte, seulement l'état et le coût. */
#[Fillable(['customer_id', 'provider', 'to', 'kind', 'status', 'provider_id', 'cost', 'error'])]
class SmsMessage extends Model
{
    protected $table = 'platform_sms_messages';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
