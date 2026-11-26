<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Campagne de la newsletter : brouillon, puis envoi par lots (App\Newsletter\Newsletter). */
#[Fillable(['subject', 'content', 'locale', 'status', 'recipients', 'sent_count', 'failed_count', 'user_id', 'started_at', 'sent_at'])]
class NewsletterCampaign extends Model
{
    public const DRAFT = 'draft';
    public const SENDING = 'sending';
    public const SENT = 'sent';

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'sent_at' => 'datetime', 'recipients' => 'integer', 'sent_count' => 'integer', 'failed_count' => 'integer'];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class, 'campaign_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }
}
