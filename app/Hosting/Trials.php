<?php

namespace App\Hosting;

use App\Hosting\Jobs\BuildSite;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

/**
 * Règles de l'essai hébergé : qui y a droit, adresse choisie, plafond du serveur.
 * Utilisé par le parcours « Essayer GeniusPress » et par les commandes.
 */
class Trials
{
    /** Premiers mots d'adresse réservés (services de la plateforme, confusion possible). */
    public const RESERVED = ['www', 'admin', 'api', 'app', 'mail', 'smtp', 'imap', 'ftp', 'ns1', 'ns2', 'cdn', 'static', 'assets', 'essai', 'demo', 'test', 'staging', 'dev', 'support', 'aide', 'help', 'blog', 'docs', 'status', 'compte', 'account', 'login', 'billing', 'paiement', 'geniuspress', 'genius', 'catalogue', 'console', 'plateforme', 'platform', 'affiliation', 'essayer', 'newsletter', 'webmail', 'autodiscover', 'cpanel'];

    public function __construct(private SiteProvisioner $provisioner, private HostingAccounts $accounts) {}

    /** Dernier site de ce compte (hors créations échouées). */
    public function siteFor(Authenticatable $user): ?Site
    {
        return Site::query()
            ->where('account_id', $this->accounts->accountId($user))
            ->where('status', '!=', Site::STATUS_FAILED)
            ->latest('id')->first();
    }

    /** Tous les sites de ce compte, le plus récent d'abord (hors créations échouées). */
    public function sitesFor(Authenticatable $user): \Illuminate\Support\Collection
    {
        return Site::query()
            ->where('account_id', $this->accounts->accountId($user))
            ->where('status', '!=', Site::STATUS_FAILED)
            ->latest('id')->get();
    }

    /** Un site de ce compte, d'après son identifiant (adresse) ; jamais celui d'un autre compte. */
    public function siteOf(Authenticatable $user, ?string $slug): ?Site
    {
        $sites = $this->sitesFor($user);

        return $slug ? $sites->firstWhere('slug', $slug) : $sites->first();
    }

    /** Raison pour laquelle ce compte ne peut pas créer de site maintenant, ou null. */
    public function refusal(Authenticatable $user): ?string
    {
        if ($reason = $this->accounts->trialRefusal($user)) {
            return $reason;
        }
        // Sites gratuits sans limite : seule la limite par compte (0 = aucune) et la capacité du serveur.
        if (config('hosting.unlimited')) {
            $max = (int) config('hosting.sites_per_account', 0);
            if ($max > 0 && $this->sitesFor($user)->whereNotIn('status', [Site::STATUS_DELETED])->count() >= $max) {
                return trans_choice('{1} Votre compte peut avoir 1 site.|[2,*] Votre compte peut avoir :count sites.', $max);
            }

            return $this->capacityRefusal();
        }
        $used = Site::query()
            ->where('account_id', $this->accounts->accountId($user))
            ->where('kind', 'trial')
            ->where('status', '!=', Site::STATUS_FAILED)
            ->count();
        if ($used >= (int) config('hosting.trials_per_account', 1)) {
            return __('Vous avez déjà utilisé votre essai gratuit.');
        }

        return $this->capacityRefusal();
    }

    public function capacityRefusal(): ?string
    {
        $max = (int) config('hosting.max_sites');
        if ($max > 0 && Site::query()->occupying()->count() >= $max) {
            return __('Tous nos sites d’essai sont occupés pour le moment. Revenez dans quelques jours.');
        }

        return null;
    }

    /** Message d'erreur si cette adresse ne peut pas être utilisée, sinon null. */
    public function slugError(string $slug): ?string
    {
        if (! preg_match(HostedBootstrap::SLUG, $slug)) {
            return __('L’adresse doit faire de 3 à 40 caractères : lettres minuscules sans accents, chiffres et tirets (pas au début ni à la fin).');
        }
        if (in_array($slug, self::RESERVED, true) || str_contains($slug, '--')) {
            return __('Cette adresse est réservée. Choisissez-en une autre.');
        }
        if (Site::query()->where('slug', $slug)->exists() || file_exists(HostedBootstrap::siteDir((string) config('hosting.path'), $slug))) {
            return __('Cette adresse est déjà prise. Choisissez-en une autre.');
        }

        return null;
    }

    /** Adresse proposée à partir du nom du site, libre si possible. */
    public function suggestSlug(string $name): string
    {
        $base = Str::limit(trim(preg_replace('/-+/', '-', Str::slug($name)), '-'), 30, '');
        $base = strlen($base) >= 3 ? rtrim($base, '-') : 'mon-site';
        $slug = $base;
        for ($i = 2; $this->slugError($slug) !== null && $i < 50; $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    /** Enregistre l'essai et lance sa création (après la réponse, ou dans la file configurée). */
    public function start(Authenticatable $user, string $name, string $slug): Site
    {
        if ($reason = $this->refusal($user)) {
            throw new HostingException($reason);
        }
        if ($error = $this->slugError($slug)) {
            throw new HostingException($error);
        }
        $owner = $this->accounts->owner($this->accounts->accountId($user)) ?? throw new HostingException(__('Compte introuvable.'));

        $site = $this->provisioner->create($owner, Str::limit(trim($name), 120, ''), $slug, $this->accounts->trialPlan($user));

        $queue = config('hosting.queue');
        $queue ? BuildSite::dispatch($site->id)->onConnection($queue) : BuildSite::dispatchAfterResponse($site->id);

        return $site;
    }
}
