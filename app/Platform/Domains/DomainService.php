<?php

namespace App\Platform\Domains;

use App\Platform\Models\Customer;
use App\Platform\Models\Domain;
use App\Platform\Models\TldPrice;
use App\Platform\PlatformException;

/** Recherche, enregistrement et renouvellement des domaines vendus par la plateforme. */
class DomainService
{
    public const REGISTRARS = [
        'sandbox' => SandboxRegistrar::class,
    ];

    public function registrar(): DomainRegistrar
    {
        $name = (string) config('geniuspress.platform.domain_registrar');
        $class = self::REGISTRARS[$name] ?? throw new PlatformException(__('La vente de noms de domaine n’est pas disponible pour le moment.'));
        if ($name === 'sandbox' && ! \App\Platform\Payments\PaymentManager::sandboxAllowed()) {
            throw new PlatformException(__('La vente de noms de domaine n’est pas disponible pour le moment.'));
        }

        return app($class);
    }

    /**
     * « monassociation » ou « monassociation.cd » → nom, extension demandée.
     *
     * @return array{0: string, 1: ?string}
     */
    public static function parse(string $query): array
    {
        $query = strtolower(trim($query));
        $query = preg_replace('#^(https?://)?(www\.)?#', '', $query);
        $query = rtrim(explode('/', $query)[0], '.');
        [$label, $tld] = array_pad(explode('.', $query, 2), 2, null);
        if (! preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', (string) $label) || str_contains((string) $label, '--')) {
            throw new PlatformException(__('Nom de domaine invalide : lettres, chiffres et tirets seulement (sans accents), 63 caractères au plus.'));
        }

        return [$label, $tld !== null && $tld !== '' ? $tld : null];
    }

    /**
     * Disponibilité et prix pour toutes les extensions vendues (l'extension demandée en premier).
     *
     * @return list<array{domain: string, tld: string, available: bool, price: int, renew_price: int, currency: string}>
     */
    public function search(string $query): array
    {
        [$label, $wanted] = self::parse($query);
        $prices = TldPrice::query()->where('active', true)->orderBy('tld')->get()->keyBy('tld');
        if ($prices->isEmpty()) {
            throw new PlatformException(__('La vente de noms de domaine n’est pas disponible pour le moment.'));
        }
        if ($wanted !== null && ! $prices->has($wanted)) {
            throw new PlatformException(__('L’extension « .:tld » n’est pas proposée.', ['tld' => $wanted]));
        }
        $tlds = $prices->keys()->sortBy(fn ($tld) => $tld === $wanted ? 0 : 1)->values();
        $domains = $tlds->map(fn ($tld) => $label.'.'.$tld)->all();
        $availability = $this->registrar()->check($domains);

        return $tlds->map(fn ($tld) => [
            'domain' => $label.'.'.$tld,
            'tld' => $tld,
            'available' => (bool) ($availability[$label.'.'.$tld] ?? false),
            'price' => $prices[$tld]->register_price,
            'renew_price' => $prices[$tld]->renew_price,
            'currency' => $prices[$tld]->currency,
        ])->all();
    }

    /** Prix de vente de l'extension d'un nom complet. */
    public function price(string $domain): TldPrice
    {
        $tld = substr($domain, strpos($domain, '.') + 1);

        return TldPrice::query()->where('tld', $tld)->where('active', true)->first()
            ?? throw new PlatformException(__('L’extension « .:tld » n’est pas proposée.', ['tld' => $tld]));
    }

    public function register(Customer $customer, string $domain, int $years): Domain
    {
        $result = $this->registrar()->register($domain, $years, [
            'name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone,
            'country' => $customer->country, 'company' => $customer->company,
        ]);

        return Domain::updateOrCreate(['name' => $domain], [
            'customer_id' => $customer->id,
            'status' => 'active',
            'registrar' => $this->registrar()->name(),
            'registrar_reference' => $result['reference'],
            'registered_at' => now(),
            'expires_at' => $result['expires_at'],
        ]);
    }

    public function renew(Domain $domain, int $years): Domain
    {
        $result = $this->registrar()->renew($domain->name, $years, $domain->expires_at ?? now(), $domain->registrar_reference);
        $domain->update(['status' => 'active', 'expires_at' => $result['expires_at']]);

        return $domain;
    }
}
