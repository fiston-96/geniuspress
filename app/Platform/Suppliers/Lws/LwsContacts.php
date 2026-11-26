<?php

namespace App\Platform\Suppliers\Lws;

use App\Platform\Models\Customer;
use App\Platform\PlatformException;
use Illuminate\Support\Str;

/**
 * Contact LWS de chaque client : titulaire de ses domaines et propriétaire de ses
 * hébergements. Créé une fois avec ses vraies coordonnées (obligation des registres),
 * puis retenu dans platform_customers.provider_ids.
 */
class LwsContacts
{
    public const FIELDS = ['phone', 'address', 'postal_code', 'city', 'country'];

    public function __construct(private LwsClient $client) {}

    /** @param  array<string, mixed>  $contact  coordonnées (voir DomainRegistrar::register) */
    public function idFor(array $contact): string
    {
        $customer = isset($contact['customer_id']) ? Customer::query()->find($contact['customer_id']) : null;
        if ($customer && ($id = $customer->providerId('lws'))) {
            return $id;
        }

        $phone = self::phone((string) ($contact['phone'] ?? ''));
        if ($phone === null || trim((string) ($contact['address'] ?? '')) === '' || trim((string) ($contact['city'] ?? '')) === '' || strlen((string) ($contact['country'] ?? '')) !== 2) {
            throw new PlatformException(__('Coordonnées du titulaire incomplètes : téléphone au format international, adresse, ville et pays.'));
        }
        [$first, $last] = self::splitName((string) $contact['name']);
        $id = (string) $this->client->post('/contact', array_filter([
            'company' => $contact['company'] ?? null,
            'firstname' => $first,
            'lastname' => $last,
            'address' => $contact['address'],
            'postal' => ($contact['postal_code'] ?? '') !== '' ? $contact['postal_code'] : '00000',
            'city' => $contact['city'],
            'country' => strtoupper((string) $contact['country']),
            'phone' => $phone,
            'email' => $contact['email'],
            // Mot de passe d'accès à l'espace LWS : jamais utilisé ni communiqué (le client gère tout depuis GeniusPress).
            'password' => Str::password(24),
        ], fn ($v) => $v !== null && $v !== ''));
        if ($id === '' || ! ctype_digit($id)) {
            throw new PlatformException(__('LWS n’a pas créé le contact du titulaire.'));
        }
        $customer?->rememberProviderId('lws', $id);

        return $id;
    }

    /** « +243 81 234 5678 » → « 00243812345678 » (format exigé par LWS), ou null si invalide. */
    public static function phone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with(trim($phone), '+')) {
            $digits = '00'.$digits;
        }

        return preg_match('/^00\d{1,3}\d{6,12}$/', (string) $digits) ? $digits : null;
    }

    /** « Amani Kabila Mwamba » → [« Amani », « Kabila Mwamba »]. */
    public static function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = array_shift($parts) ?? '';

        return [$first, $parts ? implode(' ', $parts) : $first];
    }
}
