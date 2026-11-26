<?php

namespace App\Platform;

use App\Platform\Models\Customer;
use App\Platform\Models\License;
use Illuminate\Support\Str;

/**
 * Clés de licence : « gpl_ » + 12 caractères publics + « _ » + 40 caractères secrets.
 * Seule l'empreinte SHA-256 de la clé complète est enregistrée ; la clé n'est
 * connue qu'à sa création.
 */
class LicenseKeys
{
    public const PATTERN = '/^(gpl_[A-Za-z0-9]{12})_[A-Za-z0-9]{40}$/';

    /** @return array{license: License, key: string} */
    public function issue(Customer $customer, string $name, ?string $siteUrl = null): array
    {
        do {
            $prefix = 'gpl_'.Str::random(12);
        } while (License::query()->where('key_prefix', $prefix)->exists());
        $key = $prefix.'_'.Str::random(40);

        $license = License::create([
            'customer_id' => $customer->id,
            'name' => mb_substr($name, 0, 120),
            'key_prefix' => $prefix,
            'key_hash' => hash('sha256', $key),
            'site_url' => $siteUrl ? mb_substr($siteUrl, 0, 255) : null,
            'status' => 'active',
        ]);

        return ['license' => $license, 'key' => $key];
    }

    /** Licence correspondant à la clé, qu'elle soit active ou non (null si la clé est inconnue). */
    public function find(string $key): ?License
    {
        if (! preg_match(self::PATTERN, $key, $m)) {
            return null;
        }
        $license = License::query()->where('key_prefix', $m[1])->first();

        return $license && hash_equals($license->key_hash, hash('sha256', $key)) ? $license : null;
    }

    public function revoke(License $license): void
    {
        $license->update(['status' => 'revoked', 'revoked_at' => now()]);
    }
}
