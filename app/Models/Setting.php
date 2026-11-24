<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    public const DEFAULTS = [
        'site_name' => 'GeniusPress',
        'site_tagline' => '',
        'logo_url' => null,
        'primary_color' => '#2563eb',
        'secondary_color' => '#0f172a',
        'accent_color' => '#f59e0b',
        'font_family' => 'Inter',
        'heading_font' => null,
        'custom_colors' => [],
        'locales' => ['fr'],
        'organization_description' => '',
        'forms_email' => null,
        'anthropic_api_key' => null,
        'anthropic_api_key_hint' => null,
    ];

    /** Valeurs remplacées le temps d'une requête (démonstration d'un modèle du catalogue). */
    private static array $overrides = [];

    public static function override(array $values): void
    {
        self::$overrides = $values;
    }

    /** Réglages qui ne doivent jamais être envoyés au navigateur. */
    public const PRIVATE_KEYS = ['anthropic_api_key', 'anthropic_api_key_hint', 'forms_email', 'site_profile'];

    public static function publicValues(): array
    {
        return array_diff_key(static::allValues(), array_flip(self::PRIVATE_KEYS));
    }

    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    /** Tous les réglages, fusionnés avec les valeurs par défaut (et les remplacements de la requête). */
    public static function allValues(): array
    {
        $values = Cache::rememberForever('settings', function () {
            $stored = static::query()->pluck('value', 'key')->all();

            return array_merge(self::DEFAULTS, $stored);
        });

        return self::$overrides ? array_merge($values, self::$overrides) : $values;
    }

    public static function read(string $key, mixed $default = null): mixed
    {
        return static::allValues()[$key] ?? $default;
    }

    public static function write(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('settings');
    }
}
