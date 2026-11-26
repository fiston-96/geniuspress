<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Offre de la plateforme : hébergement, option ou pack de crédits IA. */
#[Fillable(['slug', 'kind', 'name', 'description', 'price', 'currency', 'period', 'trial_days', 'ai_credits', 'features', 'active', 'sort'])]
class Plan extends Model
{
    protected $table = 'platform_plans';

    public const KINDS = ['hosting', 'option', 'ai_credits'];
    public const PERIODS = ['month', 'year', 'once'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'features' => 'array',
            'active' => 'boolean',
            'price' => 'integer',
            'ai_credits' => 'integer',
            'trial_days' => 'integer',
        ];
    }

    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return (string) ($this->name[$locale] ?? $this->name['fr'] ?? $this->slug);
    }

    public function summary(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return (string) ($this->description[$locale] ?? $this->description['fr'] ?? '');
    }

    /** Fin de la période commencée à $from. null pour un achat unique. */
    public function periodEnd(\DateTimeInterface $from): ?\Illuminate\Support\Carbon
    {
        $from = \Illuminate\Support\Carbon::instance($from);

        return match ($this->period) {
            'month' => $from->copy()->addMonthNoOverflow(),
            'year' => $from->copy()->addYearNoOverflow(),
            default => null,
        };
    }

    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
