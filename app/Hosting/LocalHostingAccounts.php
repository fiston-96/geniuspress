<?php

namespace App\Hosting;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Implémentation provisoire, en attendant les comptes de la plateforme
 * commerciale : tout compte de ce site a droit à un essai, avec l'offre d'essai
 * de config/hosting.php.
 */
class LocalHostingAccounts implements HostingAccounts
{
    public function accountId(Authenticatable $user): string
    {
        return 'user:'.$user->getAuthIdentifier();
    }

    public function trialRefusal(Authenticatable $user): ?string
    {
        return null;
    }

    public function trialPlan(Authenticatable $user): Plan
    {
        return Plan::forNewSite();
    }

    public function owner(string $accountId): ?SiteOwner
    {
        if (! str_starts_with($accountId, 'user:')) {
            return null;
        }
        $user = User::query()->find((int) substr($accountId, 5));

        return $user ? new SiteOwner($accountId, $user->name, $user->email, $user->getAuthPassword(), $user->locale) : null;
    }

    public function upgradeUrl(Site $site): ?string
    {
        return config('hosting.upgrade_url') ?: null;
    }
}
