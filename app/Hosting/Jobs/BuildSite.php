<?php

namespace App\Hosting\Jobs;

use App\Hosting\HostingException;
use App\Hosting\Site;
use App\Hosting\SiteProvisioner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/** Crée un site hébergé enregistré (base, fichiers, compte administrateur). */
class BuildSite implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 600;

    public function __construct(public int $siteId) {}

    public function handle(SiteProvisioner $provisioner): void
    {
        $site = Site::query()->find($this->siteId);
        if (! $site) {
            return;
        }
        try {
            $provisioner->build($site);
        } catch (HostingException) {
            // Déjà enregistré sur le site (état « échec ») et signalé.
        }
    }
}
