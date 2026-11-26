<?php

namespace App\Platform;

use App\Hosting\HostingAccounts;
use App\Hosting\Plan as HostingPlan;
use App\Hosting\Site;
use App\Hosting\SiteOwner;
use App\Platform\Models\Customer;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Comptes de l'essai hébergé = comptes clients de la plateforme (garde « customer »).
 * Remplace App\Hosting\LocalHostingAccounts quand la plateforme est active : l'essai
 * « Essayer GeniusPress » se lance depuis l'espace client /compte.
 */
class PlatformHostingAccounts implements HostingAccounts
{
    public const PREFIX = 'customer:';

    public function __construct(private PlatformAccounts $accounts) {}

    public function accountId(Authenticatable $user): string
    {
        return self::PREFIX.$user->getAuthIdentifier();
    }

    public function trialRefusal(Authenticatable $user): ?string
    {
        if (! $user instanceof Customer) {
            return __('Créez un compte GeniusPress ou connectez-vous pour lancer votre essai.');
        }
        if (! $user->isActive()) {
            return __('Ce compte est suspendu. Contactez-nous.');
        }
        if ($user->trial_used_at !== null) {
            return __('Vous avez déjà utilisé votre essai gratuit.');
        }
        if ($this->accounts->trialPlan() === null) {
            return __('Aucune offre d’essai n’est disponible pour le moment.');
        }

        return null;
    }

    /** Durée de l'offre d'essai de la plateforme ; quotas de config/hosting.php, sauf ceux précisés dans l'offre. */
    public function trialPlan(Authenticatable $user): HostingPlan
    {
        $plan = $this->accounts->trialPlan();
        $overrides = array_intersect_key((array) ($plan?->features ?? []), array_flip(HostingPlan::LIMITS));
        if ($plan?->trial_days) {
            $overrides['days'] = (int) $plan->trial_days;
        }

        return HostingPlan::trial($overrides);
    }

    public function owner(string $accountId): ?SiteOwner
    {
        $customer = self::customer($accountId);

        return $customer ? new SiteOwner($accountId, $customer->name, $customer->email, $customer->getAuthPassword(), $customer->locale) : null;
    }

    public function upgradeUrl(Site $site): ?string
    {
        return route('platform.customer.plans');
    }

    /** Client d'un identifiant de compte « customer:{id} ». */
    public static function customer(?string $accountId): ?Customer
    {
        if (! is_string($accountId) || ! str_starts_with($accountId, self::PREFIX)) {
            return null;
        }

        return Customer::query()->find((int) substr($accountId, strlen(self::PREFIX)));
    }
}
