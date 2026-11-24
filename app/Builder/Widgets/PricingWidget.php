<?php

namespace App\Builder\Widgets;

use App\Builder\Icons;
use App\Builder\RenderContext;

/** Offre ou tarif : nom, prix, période, liste de ce qui est inclus, bouton. */
class PricingWidget extends Widget
{
    public function type(): string { return 'pricing'; }

    public function label(): string { return __('Tarif'); }

    public function rules(): array
    {
        return [
            'title' => 'string:120',
            'price' => 'string:30',
            'currency' => 'string:12',
            'period' => 'string:60',
            'description' => 'string:300',
            'features' => 'list:30:text=string:200;included=bool',
            'button_text' => 'string:80',
            'button_url' => 'url',
            'featured' => 'bool',
            'badge' => 'string:40',
            'accent' => 'color',
        ];
    }

    public function translatable(): array { return ['title', 'period', 'description', 'features.*.text', 'button_text', 'badge']; }

    public function fillable(): array { return [...$this->translatable(), 'price', 'currency']; }

    public function aiHint(): ?string
    {
        return 'Price card (plan, membership, school fees, service package) with a features list ("features": [{text, included}]). Never invent prices: use "[à compléter]" as price when unknown.';
    }

    public function render(array $s, string $id, RenderContext $ctx): string
    {
        $ctx->rule($id, ['--gp-price-accent' => $s['accent'] ?? null], false, ' .gp-price');

        $html = '<div class="gp-price'.(! empty($s['featured']) ? ' is-featured' : '').'">';
        if (isset($s['badge'])) {
            $html .= '<span class="gp-price__badge">'.e($s['badge']).'</span>';
        }
        $html .= '<h3 class="gp-price__title">'.e($s['title'] ?? '').'</h3>';
        $html .= '<p class="gp-price__amount"><span class="gp-price__currency">'.e($s['currency'] ?? '').'</span><span class="gp-price__value">'.e($s['price'] ?? '').'</span>'
            .(isset($s['period']) ? '<span class="gp-price__period">'.e($s['period']).'</span>' : '').'</p>';
        if (isset($s['description'])) {
            $html .= '<p class="gp-price__desc">'.e($s['description']).'</p>';
        }
        if (! empty($s['features'])) {
            $html .= '<ul class="gp-price__features">';
            foreach ($s['features'] as $f) {
                $included = $f['included'] ?? true;
                $html .= '<li class="'.($included ? 'is-in' : 'is-out').'">'.Icons::svg($included ? 'lucide:check' : 'lucide:x')
                    .'<span>'.e($f['text'] ?? '').'</span>'.($included ? '' : '<span class="gp-sr">'.e(__('(non inclus)')).'</span>').'</li>';
            }
            $html .= '</ul>';
        }
        if (isset($s['button_text'])) {
            $html .= '<a class="gp-btn gp-btn-md gp-price__btn" href="'.e($s['button_url'] ?? '#').'">'.e($s['button_text']).'</a>';
        }

        return $html.'</div>';
    }
}
