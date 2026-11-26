<?php

namespace App\Platform\Suppliers;

use App\Platform\Models\Order;
use App\Platform\Models\OrderItem;
use App\Platform\Models\Plan;
use App\Platform\Models\Service;
use App\Platform\Notifications\ServiceDelivered;
use App\Platform\Notifications\ServiceToDeliver;
use App\Platform\PlatformNotifier;
use App\Platform\Suppliers\Lws\LwsHosting;
use Illuminate\Support\Carbon;

/**
 * Livraison des offres partenaires payées (hébergement web, mail pro, stockage).
 *
 * - Hébergement web « lws » : acheté tout de suite par l'API LWS, au nom du client.
 * - Le reste (mail pro, stockage, offres « manual ») : l'API LWS ne les propose pas ;
 *   le service attend dans la file de livraison de la console (pending_action) et les
 *   exploitants sont prévenus. Le client voit « en préparation » jusqu'à la livraison.
 *
 * Un nouvel achat de la même offre pour le même domaine prolonge le service existant
 * (renouvellement chez le fournisseur fait par l'équipe, depuis la file de livraison).
 */
class ServiceDesk
{
    public function __construct(private LwsHosting $lws) {}

    public function deliver(Order $order, OrderItem $item, Plan $plan): Service
    {
        $customer = $order->customer;
        $domain = $item->meta['domain'] ?? null;
        $existing = Service::query()->where(['customer_id' => $customer->id, 'plan_id' => $plan->id, 'domain' => $domain])
            ->whereIn('status', ['pending', 'active'])->latest('id')->first();
        $automatic = $plan->provider === 'lws' && $plan->kind === 'web_hosting';

        if ($existing) {
            // Renouvellement toujours fait par l'équipe : chez LWS, renouveler un hébergement renouvelle
            // aussi son domaine s'il y a été acheté, ce que le client n'a pas forcément payé ici.
            $from = $existing->expires_at && $existing->expires_at->isFuture() ? $existing->expires_at : now();
            $existing->update(['expires_at' => $plan->periodEnd($from), 'order_id' => $order->id, 'pending_action' => $existing->pending_action ?? 'renew', 'reminded_days' => null]);
            PlatformNotifier::admins(new ServiceToDeliver($existing));

            return $existing;
        }

        $service = new Service([
            'customer_id' => $customer->id, 'plan_id' => $plan->id, 'order_id' => $order->id,
            'kind' => $plan->kind, 'provider' => $plan->provider, 'provider_ref' => $plan->provider_ref,
            'domain' => $domain,
            'request' => trim((($item->meta['quantity'] ?? 1) > 1 ? __('Nombre de boîtes : :count', ['count' => $item->meta['quantity']])."\n" : '').($item->meta['request'] ?? '')) ?: null,
            'expires_at' => $plan->periodEnd(now()),
        ]);
        $service->setRelation('customer', $customer);
        if ($automatic) {
            // Acheté avant d'enregistrer le service : un échec ne laisse rien derrière lui et la ligne de commande peut être relancée.
            $this->lws->buy($service, $plan->period === 'year' ? 12 : 1);
            $service->fill(['status' => 'active', 'delivered_at' => now()])->save();
            PlatformNotifier::send($customer, new ServiceDelivered($service));
        } else {
            $service->fill(['status' => 'pending', 'pending_action' => 'create'])->save();
            PlatformNotifier::admins(new ServiceToDeliver($service));
        }

        return $service;
    }

    /** L'équipe a fait le travail chez le fournisseur : service livré (ou renouvelé), client prévenu. */
    public function markDone(Service $service, ?string $note, ?Carbon $expiresAt = null): void
    {
        $first = $service->pending_action === 'create';
        $service->update(array_filter([
            'status' => 'active',
            'pending_action' => null,
            'error' => null,
            'note' => $note,
            'delivered_at' => $service->delivered_at ?? now(),
            'expires_at' => $expiresAt,
        ], fn ($v, $k) => $v !== null || in_array($k, ['pending_action', 'error'], true), ARRAY_FILTER_USE_BOTH));
        if ($first || $note) {
            PlatformNotifier::send($service->customer, new ServiceDelivered($service));
        }
    }

    /** Livraison impossible : le client est prévenu (le remboursement se fait à part). */
    public function markFailed(Service $service, string $reason): void
    {
        $service->update(['status' => 'failed', 'pending_action' => null, 'error' => mb_substr($reason, 0, 255), 'note' => $reason]);
        PlatformNotifier::send($service->customer, new ServiceDelivered($service));
    }
}
