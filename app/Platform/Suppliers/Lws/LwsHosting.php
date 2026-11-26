<?php

namespace App\Platform\Suppliers\Lws;

use App\Platform\Models\Service;

/**
 * Hébergement web LWS acheté pour un client (offre « lws » de type web_hosting) :
 * le client en est propriétaire (son contact LWS), la plateforme paie sur son compte
 * prépayé. Le domaine est supposé déjà enregistré (type « host »).
 */
class LwsHosting
{
    public function __construct(private LwsClient $client, private LwsContacts $contacts) {}

    public function buy(Service $service, int $months): void
    {
        $customer = $service->customer;
        $owner = $this->contacts->idFor([
            'customer_id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email, 'phone' => $customer->phone,
            'country' => $customer->country, 'company' => $customer->company, 'address' => $customer->address,
            'postal_code' => $customer->postal_code, 'city' => $customer->city,
        ]);
        $this->client->post('/hosting', [
            'package' => (string) $service->provider_ref,
            'domain' => (string) $service->domain,
            'owner' => (int) $owner,
            'type' => 'host',
            'period' => max(1, $months),
        ]);
    }
}
