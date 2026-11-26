<?php

namespace App\Platform\Models;

use App\Notifications\ResetPasswordLink;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/** Client de la plateforme commerciale (distinct des utilisateurs d'un site GeniusPress). */
#[Fillable(['name', 'email', 'password', 'phone', 'country', 'company', 'address', 'postal_code', 'city', 'locale', 'status'])]
#[Hidden(['password', 'remember_token'])]
class Customer extends Authenticatable implements HasLocalePreference
{
    use Notifiable;

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
            'referred_at' => 'datetime',
            'ai_balance' => 'integer',
            'provider_ids' => 'array',
        ];
    }

    /** Services partenaires (hébergement web, mail pro, stockage). */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /** Identifiant de ce client chez un fournisseur (« lws »), ou null. */
    public function providerId(string $provider): ?string
    {
        $id = $this->provider_ids[$provider] ?? null;

        return $id === null ? null : (string) $id;
    }

    public function rememberProviderId(string $provider, string $id): void
    {
        $this->forceFill(['provider_ids' => [$provider => $id] + (array) $this->provider_ids])->save();
    }

    // ─── Affiliation (App\Platform\Referrals) ────────────────────────────

    /** Affilié de ce client (celui qui l'a fait venir), s'il y en a un. */
    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by');
    }

    /** Clients apportés de ce client. */
    public function referrals(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by');
    }

    public function referralCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'sponsor_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function preferredLocale(): ?string
    {
        return $this->locale ?: null;
    }

    /** Mot de passe oublié : lien vers l'espace client (config/auth.php, courtier customers). */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordLink(route('platform.password.reset', ['token' => $token, 'email' => $this->email])));
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
