<?php

namespace App\Platform;

use App\Platform\Domains\DomainService;
use App\Platform\Models\Domain;
use App\Platform\Models\Entitlement;
use App\Platform\Models\Order;
use App\Platform\Models\OrderItem;
use App\Platform\Models\Payment;
use App\Platform\Models\Plan;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Livraison d'une commande payée. Idempotente : la commande ne passe à « payée »
 * qu'une fois (verrou sur sa ligne) et chaque ligne n'est livrée qu'une fois
 * (fulfilled_at). Une ligne en échec (registraire indisponible…) garde son
 * erreur et peut être relancée depuis l'administration.
 */
class OrderFulfiller
{
    public function __construct(private PlatformAccounts $accounts, private DomainService $domains) {}

    public function paid(Payment $payment): void
    {
        $order = DB::transaction(function () use ($payment) {
            $order = Order::query()->whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            if ($order->status === 'paid') {
                return null;
            }
            if ($payment->amount !== $order->total || $payment->currency !== $order->currency) {
                report(new PlatformException("Paiement {$payment->reference} : montant différent de la commande {$order->number}."));

                return null;
            }
            $order->update(['status' => 'paid', 'paid_at' => now()]);

            return $order;
        });

        if ($order) {
            $this->deliver($order);
            PlatformNotifier::send($order->customer, new Notifications\OrderPaid($order));
            PlatformNotifier::admins(new Notifications\OrderPaid($order, forAdmin: true));
            // Affiliation : commission si c'est la première commande payée d'un client apporté.
            try {
                app(Referrals\Referrals::class)->onOrderPaid($order);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /** Livre les lignes pas encore livrées d'une commande payée. */
    public function deliver(Order $order): void
    {
        if (! $order->isPaid()) {
            return;
        }
        foreach ($order->items()->whereNull('fulfilled_at')->pluck('id') as $id) {
            DB::transaction(function () use ($id, $order) {
                $item = OrderItem::query()->whereKey($id)->lockForUpdate()->first();
                if (! $item || $item->fulfilled_at) {
                    return;
                }
                try {
                    $this->deliverItem($order, $item);
                    $item->update(['fulfilled_at' => now(), 'error' => null]);
                } catch (PlatformException $e) {
                    $item->update(['error' => mb_substr($e->getMessage(), 0, 255)]);
                } catch (Throwable $e) {
                    report($e);
                    $item->update(['error' => __('Livraison impossible pour le moment.')]);
                }
            });
        }
    }

    private function deliverItem(Order $order, OrderItem $item): void
    {
        $customer = $order->customer;
        match ($item->kind) {
            'plan' => $this->accounts->activatePlan($customer, Plan::query()->findOrFail($item->meta['plan_id'] ?? 0), $order),
            'template' => Entitlement::firstOrCreate(
                ['customer_id' => $customer->id, 'kind' => 'template', 'reference' => $item->reference],
                ['order_id' => $order->id],
            ),
            'domain_register' => $this->domains->register($customer, $item->reference, (int) ($item->meta['years'] ?? 1)),
            'domain_renew' => $this->domains->renew(Domain::query()->where('customer_id', $customer->id)->findOrFail($item->meta['domain_id'] ?? 0), (int) ($item->meta['years'] ?? 1)),
            default => throw new PlatformException(__('Type d’achat inconnu.')),
        };
    }
}
