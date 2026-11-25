<?php

namespace App\Hosting;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Un site hébergé par la plateforme (essai ou offre payante), vu de la
 * plateforme. Le contenu du site est dans sa propre base ; ici, seulement de quoi
 * l'administrer. Une fois supprimé, il ne reste que l'historique.
 */
#[Fillable(['slug', 'name', 'account_id', 'owner_email', 'kind', 'plan', 'status', 'host', 'database', 'limits', 'expires_at', 'expired_at', 'suspended_at', 'suspend_reason', 'provisioned_at', 'removed_at', 'error'])]
class Site extends Model
{
    protected $table = 'hosting_sites';

    public const STATUS_PROVISIONING = 'provisioning';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DELETED = 'deleted';

    /** États où le site existe sur le disque. */
    public const LIVE = [self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_SUSPENDED];

    protected function casts(): array
    {
        return [
            'limits' => 'array',
            'expires_at' => 'datetime',
            'expired_at' => 'datetime',
            'suspended_at' => 'datetime',
            'provisioned_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->whereIn('status', self::LIVE);
    }

    /** Essais en cours ou en préparation (pour le plafond du serveur). */
    public function scopeOccupying(Builder $query): Builder
    {
        return $query->whereIn('status', [...self::LIVE, self::STATUS_PROVISIONING]);
    }

    public static function findLive(string $slug): ?self
    {
        return static::query()->where('slug', $slug)->whereIn('status', [...self::LIVE, self::STATUS_PROVISIONING, self::STATUS_FAILED])->first();
    }

    public function isTrial(): bool
    {
        return $this->kind === 'trial';
    }

    public function isLive(): bool
    {
        return in_array($this->status, self::LIVE, true);
    }

    /** État vu par la personne : un essai actif dont la date est passée est déjà terminé. */
    public function effectiveStatus(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->expires_at && $this->expires_at->isPast()) {
            return self::STATUS_EXPIRED;
        }

        return $this->status;
    }

    public function daysLeft(): ?int
    {
        return $this->expires_at ? max(0, (int) ceil(now()->diffInSeconds($this->expires_at, false) / 86400)) : null;
    }

    public function url(string $path = ''): string
    {
        $scheme = config('hosting.scheme', 'https');
        $port = config('hosting.port');
        $default = ($scheme === 'https' && (int) $port === 443) || ($scheme === 'http' && (int) $port === 80);

        return $scheme.'://'.$this->host.($port && ! $default ? ':'.$port : '').($path !== '' ? '/'.ltrim($path, '/') : '');
    }

    public function directory(): string
    {
        return HostedBootstrap::siteDir((string) config('hosting.path'), (string) $this->slug);
    }

    public function manifestPath(): string
    {
        return $this->directory().DIRECTORY_SEPARATOR.Manifest::FILE;
    }

    public function manifest(): ?Manifest
    {
        return $this->slug ? Manifest::load($this->manifestPath()) : null;
    }
}
