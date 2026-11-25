<?php

namespace App\Platform\Domains;

use App\Platform\Models\Domain;
use App\Platform\PlatformException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Registraire de test : aucun appel réseau. Règles déterministes : un nom est
 * pris s'il est déjà enregistré sur la plateforme, s'il fait partie des noms
 * réservés ou s'il contient « pris » ou « taken ». Un nom contenant « echec »
 * fait échouer l'enregistrement (pour tester la reprise).
 */
class SandboxRegistrar implements DomainRegistrar
{
    public const RESERVED = ['google', 'facebook', 'geniuspress', 'example', 'exemple'];

    public function name(): string
    {
        return 'sandbox';
    }

    public function check(array $domains): array
    {
        $taken = Domain::query()->whereIn('name', $domains)->where('status', 'active')->pluck('name')->all();
        $out = [];
        foreach ($domains as $domain) {
            $label = Str::before($domain, '.');
            $out[$domain] = ! in_array($domain, $taken, true)
                && ! in_array($label, self::RESERVED, true)
                && ! str_contains($label, 'pris') && ! str_contains($label, 'taken');
        }

        return $out;
    }

    public function register(string $domain, int $years, array $contact): array
    {
        if (str_contains($domain, 'echec')) {
            throw new PlatformException(__('Le registraire a refusé l’enregistrement du domaine.'));
        }

        return ['reference' => 'SBX-'.Str::upper(Str::random(10)), 'expires_at' => now()->addYears($years)];
    }

    public function renew(string $domain, int $years, \DateTimeInterface $currentExpiry, ?string $reference): array
    {
        $from = Carbon::instance($currentExpiry)->isPast() ? now() : Carbon::instance($currentExpiry);

        return ['expires_at' => $from->copy()->addYears($years)];
    }
}
