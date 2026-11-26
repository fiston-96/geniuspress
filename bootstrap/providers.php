<?php

use App\Hosting\HostingServiceProvider;
use App\Platform\PlatformServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    PlatformServiceProvider::class,
    HostingServiceProvider::class,
];
