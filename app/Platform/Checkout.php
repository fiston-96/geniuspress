<?php

namespace App\Platform;

use App\Platform\Domains\DomainService;
use App\Platform\Models\Customer;
use App\Platform\Models\Domain;
use App\Platform\Models\Entitlement;
use App\Platform\Models\Order;
use App\Platform\Models\Payment;
use App\Platform\Models\Plan;
use App\Platform\Models\TemplatePrice;
use App\Platform\Payments\PaymentManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Commandes et paiements. Les prix sont toujours relus en base (jamais pris
 * dans le formulaire) ; un paiement n'est validé qu'après vérification auprès
 * du prestataire, puis la commande est livrée par OrderFulfiller.
 */
class Checkout
{
    public function __construct(private PaymentManager $payments, private DomainService $domains, private OrderFulfiller $fulfiller) {}

    /**
     * @param  array{domain?: ?string, request?: ?string}  $options  offres partenaires : domaine concerné, précisions du client
     */
    public function forPlan(Customer $customer, Plan $plan, array $options = []): Order
    {
        if (! $plan->active) {
            throw new PlatformException(__('Cette offre n’est plus disponible.'));
        }
        $meta = ['plan_id' => $plan->id];
        $description = $plan->label();
        if ($plan->isSupplied()) {
            $domain = trim((string) ($options['domain'] ?? ''));
            if ($domain !== '') {
                [$label, $tld] = DomainService::parse($domain);
                if ($tld === null) {
                    throw new PlatformException(__('Indiquez le nom de domaine complet, avec son extension (ex. monassociation.org).'));
                }
                $meta['domain'] = $label.'.'.$tld;
                $description .= ' · '.$meta['domain'];
            } elseif ($plan->needs_domain) {
                throw new PlatformException(__('Indiquez le nom de domaine de ce service.'));
            }
            $request = trim((string) ($options['request'] ?? ''));
            if ($request !== '') {
                $meta['request'] = mb_substr($request, 0, 1000);
            }
        }

        return $this->create($customer, 'plan', $plan->slug, mb_substr($description, 0, 255), $plan->price, $plan->currency, $meta);
    }

    public function forTemplate(Customer $customer, string $templateId, string $name): Order
    {
        $price = TemplatePrice::query()->where('template_id', $templateId)->where('active', true)->first()
            ?? throw new PlatformException(__('Ce modèle n’est pas en vente.'));
        if (Entitlement::query()->where(['customer_id' => $customer->id, 'kind' => 'template', 'reference' => $templateId])->exists()) {
            throw new PlatformException(__('Vous avez déjà acheté ce modèle.'));
        }

        return $this->create($customer, 'template', $templateId, __('Modèle de site « :name »', ['name' => $name]), $price->price, $price->currency);
    }

    public function forDomain(Customer $customer, string $domain, int $years = 1): Order
    {
        [$label, $tld] = DomainService::parse($domain);
        $domain = $label.'.'.$tld;
        $price = $this->domains->price($domain);
        if ($this->domains->missingContact($customer)) {
            throw new PlatformException(__('Complétez les coordonnées du titulaire du domaine.'));
        }
        if (! (DomainService::forCustomer(fn () => $this->domains->registrar()->check([$domain]))[$domain] ?? false)) {
            throw new PlatformException(__('Le domaine :domain n’est pas disponible.', ['domain' => $domain]));
        }

        return $this->create($customer, 'domain_register', $domain, __('Nom de domaine :domain (:years an(s))', ['domain' => $domain, 'years' => $years]),
            $price->register_price * $years, $price->currency, ['years' => $years], $years);
    }

    public function forDomainRenewal(Customer $customer, Domain $domain, int $years = 1): Order
    {
        if ($domain->customer_id !== $customer->id) {
            throw new PlatformException(__('Ce domaine n’appartient pas à votre compte.'));
        }
        $price = $this->domains->price($domain->name);
        if ($years > $this->domains->registrar()->maxRenewYears()) {
            throw new PlatformException(trans_choice('{1} Ce domaine se renouvelle un an à la fois.|[2,*] Ce domaine se renouvelle :count ans au plus à la fois.', $this->domains->registrar()->maxRenewYears()));
        }

        return $this->create($customer, 'domain_renew', $domain->name, __('Renouvellement de :domain (:years an(s))', ['domain' => $domain->name, 'years' => $years]),
            $price->renew_price * $years, $price->currency, ['years' => $years, 'domain_id' => $domain->id], $years);
    }

    /**
     * Démarre le paiement d'une commande.
     *
     * @param  array{method: string, phone?: ?string, operator?: ?string}  $details
     * @return array{payment: Payment, start: \App\Platform\Payments\PaymentStart}
     */
    public function pay(Order $order, array $details): array
    {
        if ($order->status !== 'pending') {
            throw new PlatformException(__('Cette commande n’est plus en attente de paiement.'));
        }
        $gateway = $this->payments->gateway();
        $method = $details['method'] ?? '';
        if (! in_array($method, $gateway->methods(), true)) {
            throw new PlatformException(__('Ce moyen de paiement n’est pas proposé.'));
        }
        if ($method === 'mobile_money' && ! array_key_exists($details['operator'] ?? '', $gateway->operators())) {
            throw new PlatformException(__('Choisissez votre opérateur Mobile Money.'));
        }

        $payment = Payment::create([
            'order_id' => $order->id,
            'gateway' => $gateway->name(),
            'method' => $method,
            'operator' => $method === 'mobile_money' ? $details['operator'] : null,
            'phone' => $method === 'mobile_money' ? $details['phone'] : null,
            'reference' => 'GPP-'.Str::upper(Str::random(16)),
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => $order->currency,
        ]);

        try {
            $start = $gateway->start($payment, $details + ['return_url' => route('platform.customer.orders.show', $order)]);
        } catch (PlatformException $e) {
            $payment->update(['status' => 'failed', 'failure' => mb_substr($e->getMessage(), 0, 255)]);
            throw $e;
        }

        return ['payment' => $payment, 'start' => $start];
    }

    /**
     * Paiement avec le solde d’affiliation (même devise, solde suffisant) : débit et paiement
     * enregistrés ensemble, puis livraison comme pour un paiement Mobile Money.
     */
    public function payWithWallet(Order $order): Payment
    {
        $payment = DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                throw new PlatformException(__('Cette commande n’est plus en attente de paiement.'));
            }
            $payment = Payment::create([
                'order_id' => $locked->id, 'gateway' => 'wallet', 'method' => 'wallet',
                'reference' => 'GPW-'.Str::upper(Str::random(16)), 'status' => 'succeeded',
                'amount' => $locked->total, 'currency' => $locked->currency,
            ]);
            app(Referrals\Wallet::class)->debit($locked->customer, $locked->total, $locked->currency, 'payment',
                __('Paiement de la commande :number', ['number' => $locked->number]), $locked->number);

            return $payment;
        });
        $this->fulfiller->paid($payment);

        return $payment->refresh();
    }

    /** Redemande l'état au prestataire et livre la commande si le paiement est confirmé. */
    public function confirm(Payment $payment): Payment
    {
        if ($payment->status !== 'pending') {
            return $payment;
        }
        $status = $this->payments->gateway($payment->gateway)->verify($payment);
        if ($status === 'succeeded') {
            $payment->update(['status' => 'succeeded']);
            $this->fulfiller->paid($payment);
        } elseif ($status === 'failed') {
            $payment->update(['status' => 'failed', 'failure' => __('Paiement refusé ou annulé.')]);
        }

        return $payment->refresh();
    }

    private function create(Customer $customer, string $kind, string $reference, string $description, int $amount, string $currency, array $meta = [], int $quantity = 1): Order
    {
        if (! $customer->isActive()) {
            throw new PlatformException(__('Ce compte est suspendu. Contactez-nous.'));
        }

        return DB::transaction(function () use ($customer, $kind, $reference, $description, $amount, $currency, $meta, $quantity) {
            $order = Order::create([
                'number' => 'GP-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                'customer_id' => $customer->id,
                'status' => 'pending',
                'total' => $amount,
                'currency' => $currency,
            ]);
            $order->items()->create(compact('kind', 'reference', 'description', 'quantity', 'amount', 'meta'));

            return $order;
        });
    }
}
