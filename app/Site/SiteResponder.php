<?php

namespace App\Site;

use App\Builder\Renderer;
use App\Models\Setting;
use Symfony\Component\HttpFoundation\Response;

/**
 * Assemble la page publique pour ce que regarde le visiteur (SiteContext) :
 * le contenu principal (page, article, archive, recherche, 404), puis l'en-tête
 * et le pied de page. Chaque partie vient d'un gabarit du thème s'il en existe un
 * qui s'applique, sinon de l'affichage par défaut de GeniusPress.
 */
final class SiteResponder
{
    public function __construct(private Renderer $renderer, private ThemeLayouts $layouts) {}

    public function respond(SiteContext $site, int $status = 200): Response
    {
        return response()->view('public.page', $this->viewData($site), $status);
    }

    /** Données de la vue public.page : contenu principal, gabarits d'en-tête et de pied de page. */
    public function viewData(SiteContext $site): array
    {
        $main = $this->main($site);
        $parts = [];
        foreach (['header', 'footer'] as $type) {
            $layout = $this->layouts->find($type, $site);
            $parts[$type] = $layout ? $this->renderer->render($layout->content, null, $site) : null;
        }

        return [
            'site' => $site,
            'page' => $site->page,
            'rendered' => $main,
            'themeHeader' => $parts['header'],
            'themeFooter' => $parts['footer'],
            // Popups du thème (App\Site\Popups) : rendues ici, ouvertes par le script du site.
            'themePopups' => Popups::forSite($site, $this->layouts, $this->renderer),
            'settings' => Setting::publicValues(),
            'preview' => $site->preview,
        ];
    }

    /** @return array{html: string, css: string, fonts: string[]} */
    private function main(SiteContext $site): array
    {
        $type = match ($site->kind) {
            SiteContext::POST => 'single',
            SiteContext::BLOG, SiteContext::CATEGORY, SiteContext::TAG => 'archive',
            SiteContext::SEARCH => 'search',
            SiteContext::NOT_FOUND => 'not_found',
            default => null,
        };

        if ($site->kind === SiteContext::PAGE) {
            return $this->renderer->render($site->page->content, $site->page->id, $site);
        }

        // Gabarit du thème ; la recherche se rabat sur le gabarit d'archive.
        $layout = $this->layouts->find($type, $site) ?? ($type === 'search' ? $this->layouts->find('archive', $site) : null);
        if ($layout) {
            return $this->renderer->render($layout->content, null, $site);
        }

        // Affichage par défaut.
        if ($site->kind === SiteContext::POST) {
            $content = $this->renderer->render($site->post->content, null, $site->forContent());

            return ['html' => view('public.partials.post', ['site' => $site, 'post' => $site->post, 'content' => $content['html']])->render()] + $content;
        }

        return ['html' => view($site->kind === SiteContext::NOT_FOUND ? 'public.partials.not-found' : 'public.partials.archive', ['site' => $site])->render(), 'css' => '', 'fonts' => []];
    }
}
