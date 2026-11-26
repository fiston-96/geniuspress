<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Abonné à la newsletter du site (voir App\Newsletter\Newsletter). */
#[Fillable(['email', 'name', 'locale', 'status', 'token', 'source', 'ip_hash', 'confirmed_at', 'unsubscribed_at'])]
#[Hidden(['token', 'ip_hash'])]
class NewsletterSubscriber extends Model
{
    public const PENDING = 'pending';
    public const SUBSCRIBED = 'subscribed';
    public const UNSUBSCRIBED = 'unsubscribed';

    protected function casts(): array
    {
        return ['confirmed_at' => 'datetime', 'unsubscribed_at' => 'datetime'];
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class, 'subscriber_id');
    }

    public function scopeSubscribed($query)
    {
        return $query->where('status', self::SUBSCRIBED);
    }
}
