<?php

namespace App\Hosting;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Ce que l'hébergement demande à la plateforme commerciale (comptes, offres).
 * Implémentation provisoire : LocalHostingAccounts. La plateforme la remplace par
 * la sienne : $this->app->bind(HostingAccounts::class, …).
 */
interface HostingAccounts
{
    /** Identifiant stable du compte plateforme de cette personne. */
    public function accountId(Authenticatable $user): string;

    /** Raison (affichable, en français) pour laquelle ce compte n'a pas droit à un essai, ou null. */
    public function trialRefusal(Authenticatable $user): ?string;

    /** Offre d'essai de ce compte : durée et quotas. */
    public function trialPlan(Authenticatable $user): Plan;

    /** Propriétaire à recréer comme administrateur du site, ou null si le compte n'existe plus. */
    public function owner(string $accountId): ?SiteOwner;

    /** Page où choisir une offre payante pour ce site, ou null s'il n'y en a pas encore. */
    public function upgradeUrl(Site $site): ?string;
}
