<?php

namespace App\Hosting\Database;

use App\Hosting\Site;

/** Un fichier SQLite dans le dossier du site : développement et tests. */
class SqliteSiteDatabase implements SiteDatabase
{
    public function create(Site $site): array
    {
        $path = $this->path($site);
        @mkdir(dirname($path), 0775, true);
        touch($path);

        return ['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $path];
    }

    public function drop(Site $site): void
    {
        @unlink($this->path($site));
    }

    private function path(Site $site): string
    {
        return $site->directory().DIRECTORY_SEPARATOR.'database.sqlite';
    }
}
