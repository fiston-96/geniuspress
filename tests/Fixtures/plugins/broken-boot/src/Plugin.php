<?php

namespace Fixtures\Broken;

class Plugin extends \App\Plugins\Plugin
{
    public function boot(): void
    {
        throw new \RuntimeException('Erreur volontaire du plugin');
    }
}
