<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** Client de la plateforme commerciale (distinct des utilisateurs d'un site GeniusPress). */
#[Fillable(['name', 'email', 'password', 'phone', 'country', 'company', 'locale', 'status'])]
#[Hidden(['password', 'remember_token'])]
class Customer extends Authenticatable
{
    protected $table = 'platform_customers';

    protected $attributes = ['status' => 'active', 'locale' => 'fr', 'ai_balance' => 0];

    public const ACTIVE = 'active';
    public const SUSPENDED = 'suspended';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'trial_used_at' => 'datetime',
            'ai_balance' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function aiUsages(): HasMany
    {
        return $this->hasMany(AiUsage::class);
    }

    /** Modèles de sites achetés (identifiants du catalogue). */
    public function templateIds(): array
    {
        return $this->entitlements()->where('kind', 'template')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->pluck('reference')->all();
    }
}
