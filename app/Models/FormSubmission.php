<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['page_id', 'widget_id', 'data', 'ip_hash', 'read_at'])]
class FormSubmission extends Model
{
    protected function casts(): array
    {
        return ['data' => 'array', 'read_at' => 'datetime'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class)->withTrashed();
    }
}
