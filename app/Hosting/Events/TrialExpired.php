<?php

namespace App\Hosting\Events;

use App\Hosting\Site;
use Illuminate\Foundation\Events\Dispatchable;

/** Essai arrivé à sa date de fin (site public fermé, administration en lecture seule). */
class TrialExpired
{
    use Dispatchable;

    public function __construct(public readonly Site $site) {}
}
