<?php

namespace App\Platform;

use App\Models\Setting;
use App\Platform\Ai\CreditAccount;
use App\Platform\Models\Customer;
use App\Platform\Models\Order;
use App\Platform\Models\Plan;
use App\Platform\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Comptes clients et abonnements. C'est aussi l'interface utilisée par
 * l'essai hébergé (app/Hosting) : voir la section 7 de
 * docs/developpeurs/plateforme-commerciale.md.
 */
class PlatformAccounts
{
    public function __construct(private CreditAccount $credits, private LicenseKeys $keys) {}

    public function register(string $name, string $email, string $password, ?string $phone = null, ?string $country = null, string $locale = 'fr'): Customer
    {
        $email = Str::lower(trim($email));
        if (Customer::query()->where('email', $email)->exists()) {
            throw new PlatformException(__('Un compte existe déjà avec cette adresse e-mail.'));
        }

        return Customer::create([
            'name' => $name, 'email' => $email, 'password' => $password,
            'phone' => $phone, 'country' => $country ? strtoupper($country) : null,
            'locale' => in_array($locale, array_keys(config('geniuspress.ui_locales')), true) ? $locale : 'fr',
            'status' => Customer::ACTIVE,
        ]);
    }

    public function findByEmail(string $email): ?Customer
    {
        return Customer::query()->where('email', Str::lower(trim($email)))->first();
    }

    /** Offre d'hébergement proposée à l'essai : celle choisie par l'exploitant, sinon la première avec des jours d'essai. */
    public function trialPlan(): ?Plan
    {
        $slug = Setting::read('platform_trial_plan');
        $query = Plan::query()->active()->where('kind', 'hosting')->where('trial_days', '>', 0);

        return ($slug ? (clone $query)->where('slug', $slug)->first() : null) ?? $query->orderBy('sort')->orderBy('id')->first();
    }

    public function canStartTrial(Customer $customer): bool
    {
        return $customer->isActive() && $customer->trial_used_at === null && $this->trialPlan() !== null;
    }

    public function startTrial(Customer $customer, ?string $siteReference = null): Subscription
    {
        $plan = $this->trialPlan() ?? throw new PlatformException(__('Aucune offre d’essai n’est disponible pour le moment.'));

        $subscription = DB::transaction(function () use ($customer, $plan, $siteReference) {
            $locked = Customer::query()->whereKey($customer->id)->lockForUpdate()->firstOrFail();
            if (! $locked->isActive()) {
                throw new PlatformException(__('Ce compte est suspendu. Contactez-nous.'));
            }
            if ($locked->trial_used_at !== null) {
                throw new PlatformException(__('Vous avez déjà utilisé votre essai gratuit.'));
            }
            $locked->forceFill(['trial_used_at' => now()])->save();
            $customer->trial_used_at = $locked->trial_used_at;

            return Subscription::create([
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays($plan->trial_days),
                'site_reference' => $siteReference,
            ]);
        });

        $trialCredits = (int) Setting::read('platform_trial_credits', 0);
        if ($trialCredits > 0) {
            $this->credits->credit($customer, $trialCredits, 'grant', __('Crédits IA offerts pour l’essai'), 'sub-'.$subscription->id);
        }

        return $subscription;
    }

    /** Abonnement en cours (essai compris) pour un type d'offre ; le plus récent s'il y en a plusieurs. */
    public function activeSubscription(Customer $customer, string $kind = 'hosting'): ?Subscription
    {
        return $customer->subscriptions()->with('plan')
            ->whereHas('plan', fn ($q) => $q->where('kind', $kind))
            ->whereIn('status', ['trialing', 'active', 'canceled'])
            ->latest('id')->get()
            ->first(fn (Subscription $s) => $s->isActive());
    }

    public function attachSite(Subscription $subscription, string $siteReference): void
    {
        $subscription->update(['site_reference' => mb_substr($siteReference, 0, 120)]);
    }

    /** @return array{license: \App\Platform\Models\License, key: string} clé en clair, à transmettre une seule fois */
    public function issueLicense(Customer $customer, string $name, ?string $siteUrl = null): array
    {
        return $this->keys->issue($customer, $name, $siteUrl);
    }

    /**
     * Abonnement payé : prolonge l'abonnement en cours à cette offre (essai
     * compris, la période payée commence à la fin de l'essai), sinon en crée un.
     */
    public function activatePlan(Customer $customer, Plan $plan, ?Order $order = null): ?Subscription
    {
        $subscription = null;
        if ($plan->period !== 'once') {
            $subscription = DB::transaction(function () use ($customer, $plan) {
                $current = $customer->subscriptions()->where('plan_id', $plan->id)
                    ->whereIn('status', ['trialing', 'active', 'canceled'])->latest('id')->lockForUpdate()->first();
                $from = now();
                if ($current && $current->isActive()) {
                    $end = $current->endsAt();
                    $from = $end && $end->isFuture() ? $end : now();
                }
                $values = ['status' => 'active', 'current_period_end' => $plan->periodEnd($from), 'canceled_at' => null];
                if ($current && $current->isActive()) {
                    $current->update($values);

                    return $current;
                }

                return Subscription::create($values + [
                    'customer_id' => $customer->id,
                    'plan_id' => $plan->id,
                    'site_reference' => $current?->site_reference,
                ]);
            });
        }

        if ($plan->ai_credits > 0) {
            $kind = $plan->kind === 'ai_credits' ? 'purchase' : 'grant';
            $this->credits->credit($customer, $plan->ai_credits, $kind, $plan->label(), $order?->number);
        }

        return $subscription;
    }

    /** Résiliation : l'abonnement reste actif jusqu'à la fin de la période payée. */
    public function cancel(Subscription $subscription): void
    {
        $subscription->update([
            'status' => $subscription->status === 'trialing' ? 'expired' : 'canceled',
            'canceled_at' => now(),
            'trial_ends_at' => $subscription->status === 'trialing' ? now() : $subscription->trial_ends_at,
        ]);
    }
}
