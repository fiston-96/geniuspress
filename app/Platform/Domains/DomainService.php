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
        'lws' => \App\Platform\Suppliers\Lws\LwsRegistrar::class,
    ];

    /**
     * Appel au registraire depuis l'espace client : une erreur du fournisseur (LWS mal configuré,
     * en panne…) est signalée dans le journal mais jamais montrée telle quelle au client, pour que
     * le nom du fournisseur et les détails techniques restent internes.
     *
     * @template T
     *
     * @param  callable(): T  $call
     * @return T
     */
    public static function forCustomer(callable $call): mixed
    {
        try {
            return $call();
        } catch (\App\Platform\Suppliers\Lws\LwsException $e) {
            report($e);

            throw new PlatformException(__('Le service des noms de domaine est momentanément indisponible. Réessayez dans quelques minutes.'));
        }
    }

    /** Libellés des coordonnées du titulaire (DomainRegistrar::contactFields). */
    public static function contactLabels(): array
    {
        return [
            'phone' => __('Téléphone, avec l’indicatif du pays (+33, +1, +225…)'),
            'address' => __('Adresse'),
            'postal_code' => __('Code postal'),
            'city' => __('Ville'),
            'country' => __('Pays (code à deux lettres : FR, CA, CI…)'),
        ];
    }

    /**
     * Coordonnées du titulaire qui manquent au client pour acheter un domaine.
     *
     * @return list<string>
     */
    public function missingContact(Customer $customer): array
    {
        return array_values(array_filter($this->registrar()->contactFields(), fn ($field) => $field !== 'postal_code' && trim((string) $customer->{$field}) === ''));
    }

    /** Registraire choisi dans la console (Fournisseurs), sinon GP_DOMAIN_REGISTRAR. */
    public static function active(): string
    {
        $chosen = (string) \App\Models\Setting::read('platform_domain_registrar');

        return isset(self::REGISTRARS[$chosen]) ? $chosen : (string) config('geniuspress.platform.domain_registrar');
    }

    public function registrar(): DomainRegistrar
    {
        $name = self::active();
        $class = self::REGISTRARS[$name] ?? throw new PlatformException(__('La vente de noms de domaine n’est pas disponible pour le moment.'));
        $sandbox = \App\Platform\Payments\PaymentManager::sandboxAllowed();
        if ($name === 'sandbox' && ! $sandbox) {
            throw new PlatformException(__('La vente de noms de domaine n’est pas disponible pour le moment.'));
        }
        // LWS en mode test : achats simulés. En production, un client paierait pour un domaine qui n'existe pas.
        if ($name === 'lws') {
            $client = app(\App\Platform\Suppliers\Lws\LwsClient::class);
            if (! $client->configured() || ($client->testMode() && ! $sandbox)) {
                throw new PlatformException(__('La vente de noms de domaine n’est pas disponible pour le moment.'));
            }
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
        $availability = self::forCustomer(fn () => $this->registrar()->check($domains));

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
            'customer_id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone,
            'country' => $customer->country, 'company' => $customer->company, 'address' => $customer->address,
            'postal_code' => $customer->postal_code, 'city' => $customer->city,
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
        $domain->update(['status' => 'active', 'expires_at' => $result['expires_at'], 'reminded_days' => null]);

        return $domain;
    }
}
