<?php

namespace App\Http\Controllers;

use App\Site\Seo;

/**
 * /robots.txt : indique le plan du site aux moteurs de recherche et écarte l'administration,
 * l'installation et les résultats de recherche. Si le site refuse les moteurs
 * (Réglages → Référencement), tout est interdit.
 */
class RobotsController extends Controller
{
    public function __invoke()
    {
        $lines = Seo::indexable()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /install', 'Disallow: /*?s=', '', 'Sitemap: '.url('/sitemap.xml')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
