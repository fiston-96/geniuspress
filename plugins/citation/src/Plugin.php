<?php

namespace Plugins\Citation;

use App\Plugins\Plugin as BasePlugin;

class Plugin extends BasePlugin
{
    public function boot(): void
    {
        // Déclare le widget dans le builder et sur le site.
        $this->widget(new Widget);

        // Exemples de hooks (décommentez pour essayer) :
        // $this->on('page.published', fn ($page) => logger("Page publiée : {$page->title}"));
        // $this->filter('page.head', fn (string $html) => $html.'<meta name="theme-color" content="#4f46e5">');
    }
}
