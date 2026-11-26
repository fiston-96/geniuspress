<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Journal du relais IA : un appel, sans le contenu de la demande. */
#[Fillable(['customer_id', 'license_id', 'action', 'model', 'input_tokens', 'output_tokens', 'cost', 'billed', 'status', 'error', 'ip', 'duration_ms'])]
class AiUsage extends Model
{
    protected $table = 'platform_ai_usages';

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'cost' => 'integer', 'billed' => 'integer'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
