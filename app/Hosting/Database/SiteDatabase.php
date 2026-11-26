<?php

namespace App\Hosting\Database;

use App\Hosting\Site;

/** Base de données propre à chaque site hébergé. */
interface SiteDatabase
{
    /**
     * Crée la base (et son utilisateur) et renvoie les variables DB_* du .env du site.
     *
     * @return array<string, string>
     */
    public function create(Site $site): array;

    /** Supprime la base et son utilisateur. Sans erreur s'ils n'existent pas. */
    public function drop(Site $site): void;
}
