<?php

namespace App\Platform\Suppliers\Lws;

use App\Platform\Domains\DomainRegistrar;
use App\Platform\Domains\ManagesDns;
use Illuminate\Support\Carbon;

/**
 * Noms de domaine achetés chez LWS (GP_DOMAIN_REGISTRAR=lws), au nom du client
 * (contact LWS titulaire), payés sur le compte prépayé de la plateforme.
 * Achat : POST /hosting avec l'offre « domaine » ; traité en différé par LWS.
 */
class LwsRegistrar implements DomainRegistrar, ManagesDns
{
    /** Types d'enregistrement qui contrediraient un A sur le même nom. */
    private const CONFLICTING = ['AAAA', 'CNAME', 'ANAME'];

    public function __construct(private LwsClient $client, private LwsContacts $contacts) {}

    public function name(): string
    {
        return 'lws';
    }

    public function contactFields(): array
    {
        return LwsContacts::FIELDS;
    }

    // LWS renouvelle une période à la fois : plusieurs appels pour plusieurs années feraient
    // payer deux fois en cas de reprise après un échec partiel.
    public function maxRenewYears(): int
    {
        return 1;
    }

    public function check(array $domains): array
    {
        $out = [];
        foreach ($domains as $domain) {
            try {
                $out[$domain] = $this->client->get('/domain/'.rawurlencode($domain).'/availability') === true;
            } catch (LwsException $e) {
                // Extension refusée ou nom invalide : indisponible. Accès refusé, LWS en panne : on remonte l'erreur.
                if (! in_array($e->getCode(), [400, 314, 404], true)) {
                    throw $e;
                }
                $out[$domain] = false;
            }
        }

        return $out;
    }

    public function register(string $domain, int $years, array $contact): array
    {
        $owner = $this->contacts->idFor($contact);
        $this->client->post('/hosting', [
            'package' => 'domaine',
            'domain' => $domain,
            'owner' => (int) $owner,
            'type' => 'buy',
            'period' => 12 * max(1, $years),
        ]);

        return ['reference' => 'lws:'.$owner, 'expires_at' => now()->addYears($years)];
    }

    public function renew(string $domain, int $years, \DateTimeInterface $currentExpiry, ?string $reference): array
    {
        $this->client->post('/hosting/'.rawurlencode($domain).'/renew');
        $from = Carbon::instance($currentExpiry)->isPast() ? now() : Carbon::instance($currentExpiry);

        return ['expires_at' => $from->copy()->addYear()];
    }

    public function pointTo(string $domain, string $ip): void
    {
        $zone = (array) $this->client->get('/domain/'.rawurlencode($domain).'/zdns');
        $path = '/domain/'.rawurlencode($domain).'/zdns';
        foreach (['@', 'www'] as $name) {
            $existing = null;
            foreach ($zone as $record) {
                if (($record['name'] ?? null) !== $name) {
                    continue;
                }
                $type = strtoupper((string) ($record['type'] ?? ''));
                if ($type === 'A' && $existing === null) {
                    $existing = $record;
                } elseif ($type === 'A' || in_array($type, self::CONFLICTING, true)) {
                    $this->client->delete($path, ['id' => (int) $record['id']]);
                }
            }
            $record = ['type' => 'A', 'name' => $name, 'value' => $ip, 'ttl' => 3600];
            if ($existing) {
                if (($existing['value'] ?? null) !== $ip) {
                    $this->client->put($path, ['id' => (int) $existing['id']] + $record);
                }
            } else {
                $this->client->post($path, $record);
            }
        }
    }

    public function sendAuthCode(string $domain): void
    {
        $this->client->get('/domain/'.rawurlencode($domain).'/authcode');
    }
}
