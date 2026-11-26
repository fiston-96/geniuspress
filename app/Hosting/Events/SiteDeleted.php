<?php

namespace App\Hosting\Events;

use App\Hosting\Site;
use Illuminate\Foundation\Events\Dispatchable;

/** Site hébergé supprimé (base et fichiers effacés). */
class SiteDeleted
{
    use Dispatchable;

    public function __construct(public readonly Site $site) {}
}
