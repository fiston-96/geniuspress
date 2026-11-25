<?php

namespace App\Hosting\Events;

use App\Hosting\Site;
use Illuminate\Foundation\Events\Dispatchable;

/** Site hébergé créé et prêt. */
class SiteProvisioned
{
    use Dispatchable;

    public function __construct(public readonly Site $site) {}
}
