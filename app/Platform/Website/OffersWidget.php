<?php

namespace App\Platform\Website;

use App\Builder\Icons;
use App\Builder\RenderContext;
use App\Builder\Widgets\Widget;
use App\Platform\Labels;
use App\Platform\Models\Plan;
use App\Platform\Models\TldPrice;
use App\Platform\Money;

/**
 * Offres de la plateforme, lues en direct (vitrine geniuspress.site) : les offres en vente
 * des types choisis, avec leur prix réglé dans la console, et les prix des noms de domaine.
 * Déclaré seulement quand la plateforme est activée (PlatformServiceProvider).
 */
class OffersWidget extends Widget
{
    public const TYPE = 'platform-offers';

    public function type(): string
    {
        return self::TYPE;
    }

    public function label(): string
    {
        return __('Offres de la plateforme');
    }

    public function rules(): array
    {
        return [
            // Types d'offres affichés, séparés par des virgules (hosting, web_hosting, mail, storage, ai_credits, option).
            'kinds' => 'string:120',
            'show_domains' => 'bool',
            'button_text' => 'string:60',
            'empty_text' => 'string:200',
        ];
    }

    public function translatable(): array
    {
        return ['button_text', 'empty_text'];
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        // Textes, prix et noms des offres dans la langue de la page (pas celle de l'interface).
        $previous = app()->getLocale();
        app()->setLocale($ctx->site?->locale ?? $previous);
        try {
            return $this->offers($s);
        } finally {
            app()->setLocale($previous);
        }
    }

    private function offers(array $s): string
    {
        $kinds = array_values(array_intersect(array_map('trim', explode(',', (string) ($s['kinds'] ?? 'web_hosting,mail,storage'))), Plan::KINDS));
        $plans = $kinds ? Plan::query()->active()->whereIn('kind', $kinds)->orderByRaw($this->kindOrder($kinds))->orderBy('sort')->orderBy('price')->get() : collect();
        $button = (string) ($s['button_text'] ?? __('Commander'));
        $cards = $plans->map(fn (Plan $plan) => $this->planCard($plan, $button))->all();

        if (! empty($s['show_domains']) && ($domains = $this->domainsCard($button)) !== null) {
            array_unshift($cards, $domains);
        }
        if (! $cards) {
            return '<p class="gp-offers__empty">'.e((string) ($s['empty_text'] ?? __('Les offres arrivent très bientôt.'))).'</p>';
        }

        return '<div class="gp-offers">'.implode('', $cards).'</div>';
    }

    private function planCard(Plan $plan, string $button): string
    {
        $features = (array) $plan->features;
        if ($plan->trial_days > 0) {
            array_unshift($features, __(':days jours d’essai gratuit', ['days' => $plan->trial_days]));
        }
        if ($plan->ai_credits > 0) {
            array_unshift($features, __(':amount de crédits IA', ['amount' => Money::micros($plan->ai_credits)]));
        }

        return $this->card(Labels::kind($plan->kind), $plan->label(), Money::format($plan->price, $plan->currency), Labels::period($plan->period),
            $plan->summary(), $features, $button, '/compte/offres#'.$plan->kind);
    }

    private function domainsCard(string $button): ?string
    {
        $tlds = TldPrice::query()->where('active', true)->orderBy('register_price')->limit(8)->get();
        if ($tlds->isEmpty()) {
            return null;
        }
        $cheapest = $tlds->first();
        $lines = $tlds->sortBy('tld')->map(fn (TldPrice $tld) => '.'.$tld->tld.' · '.__(':price par an', ['price' => Money::format($tld->register_price, $tld->currency)]))->values()->all();

        return $this->card(__('Nom de domaine'), __('Votre adresse sur internet'), Money::format($cheapest->register_price, $cheapest->currency), __('par an'),
            __('Enregistré à votre nom, branché sur votre site en un clic.'), $lines, $button, '/compte/domaines', __('dès'));
    }

    /** @param  list<string>  $features */
    private function card(string $badge, string $title, string $price, string $period, string $summary, array $features, string $button, string $url, ?string $prefix = null): string
    {
        $html = '<div class="gp-price gp-offers__card">';
        $html .= '<p class="gp-offers__kind">'.e($badge).'</p>';
        $html .= '<h3 class="gp-price__title">'.e($title).'</h3>';
        $html .= '<p class="gp-price__amount">'.($prefix ? '<span class="gp-price__currency">'.e($prefix).'</span>' : '').'<span class="gp-price__value">'.e($price).'</span><span class="gp-price__period">'.e($period).'</span></p>';
        if ($summary !== '') {
            $html .= '<p class="gp-price__desc">'.e($summary).'</p>';
        }
        if ($features) {
            $html .= '<ul class="gp-price__features">';
            foreach ($features as $feature) {
                $html .= '<li class="is-in">'.Icons::svg('lucide:check').'<span>'.e((string) $feature).'</span></li>';
            }
            $html .= '</ul>';
        }

        return $html.'<a class="gp-btn gp-btn-md gp-price__btn" href="'.e($url).'">'.e($button).'</a></div>';
    }

    /** Ordre des types tel qu'indiqué dans le réglage « kinds » (valeurs vérifiées contre Plan::KINDS). */
    private function kindOrder(array $kinds): string
    {
        $cases = array_map(fn ($kind, $i) => "WHEN '{$kind}' THEN {$i}", $kinds, array_keys($kinds));

        return 'CASE kind '.implode(' ', $cases).' ELSE 99 END';
    }
}
