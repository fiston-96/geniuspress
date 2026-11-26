<?php

namespace App\Hosting;

/**
 * Crée et administre les sites hébergés (essais et offres payantes) : un code
 * partagé, une base et un dossier par site. Implémentation : LocalSiteProvisioner
 * (sites sur ce serveur).
 */
interface SiteProvisioner
{
    /** Enregistre un nouveau site (état « préparation ») ; build() le crée réellement. */
    public function create(SiteOwner $owner, string $name, string $slug, Plan $plan): Site;

    /** Crée dossier, base, .env, compte administrateur. En cas d'échec, tout est retiré (état « échec »). */
    public function build(Site $site): void;

    public function suspend(Site $site, ?string $reason = null): void;

    public function resume(Site $site): void;

    /** Repousse la date de fin (à partir d'aujourd'hui si elle est passée) ; un essai terminé redevient actif. */
    public function extend(Site $site, int $days): void;

    /** Termine l'essai : site public fermé, administration en lecture seule. */
    public function expire(Site $site): void;

    /** Passe le site à une offre (payante) : mêmes données, durée et quotas de l'offre. */
    public function convert(Site $site, Plan $plan): void;

    /** Supprime base, utilisateur MySQL et fichiers ; ne garde que l'historique. */
    public function delete(Site $site): void;

    /** Applique les migrations du code actuel à la base du site. */
    public function migrate(Site $site): void;

    /** Lien de connexion en un clic à l'administration du site. */
    public function loginUrl(Site $site): string;

    /** Fait servir ce site à une adresse de plus (domaine du client). Refusé si l'adresse sert déjà un autre site. */
    public function addHost(Site $site, string $host): void;

    /** Retire une adresse ajoutée par addHost (sans effet si elle sert un autre site). */
    public function removeHost(Site $site, string $host): void;
}
