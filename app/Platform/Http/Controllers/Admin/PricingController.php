<?php

namespace App\Platform\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Platform\Ai\Pricing;
use App\Platform\Models\AiRate;
use App\Platform\Models\Plan;
use App\Platform\Models\TemplatePrice;
use App\Platform\Models\TldPrice;
use App\Platform\Money;
use App\Templates\Catalog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Tarifs : prix réels des modèles Claude (renseignés par l'exploitant, jamais
 * codés en dur), multiplicateur, essai, modèles de sites payants, extensions de domaine.
 */
class PricingController extends Controller
{
    private const DECIMAL = 'regex:/^\d{1,6}([.,]\d{1,6})?$/';

    public function index(Pricing $pricing, Catalog $catalog)
    {
        try {
            $templates = collect($catalog->index()['templates'])->mapWithKeys(fn ($t) => [$t['id'] => $t['name'][app()->getLocale() === 'en' ? 'en' : 'fr'] ?? $t['id']])->all();
        } catch (Throwable) {
            $templates = [];
        }

        return view('platform.admin.pricing', [
            'rates' => AiRate::query()->orderBy('label')->get(),
            'multiplier' => $pricing->multiplier(),
            'pricing' => $pricing,
            'trialPlan' => Setting::read('platform_trial_plan'),
            'trialCredits' => (int) Setting::read('platform_trial_credits', 0),
            'hostingPlans' => Plan::query()->where('kind', 'hosting')->where('trial_days', '>', 0)->orderBy('sort')->get(),
            'templatePrices' => TemplatePrice::query()->orderBy('template_id')->get(),
            'templates' => $templates,
            'tlds' => TldPrice::query()->orderBy('tld')->get(),
        ]);
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'multiplier' => ['required', 'string', 'regex:/^\d{1,2}([.,]\d{1,3})?$/'],
            'trial_plan' => ['nullable', 'string', Rule::exists('platform_plans', 'slug')],
            'trial_credits' => ['nullable', 'string', self::DECIMAL],
        ]);
        $multiplier = str_replace(',', '.', $data['multiplier']);
        if ((float) $multiplier < 1) {
            return back()->withErrors(['multiplier' => __('Le multiplicateur doit être au moins 1 : en dessous, chaque appel coûte plus qu’il ne rapporte.')]);
        }
        Setting::write([
            'platform_ai_multiplier' => $multiplier,
            'platform_trial_plan' => $data['trial_plan'] ?? null,
            'platform_trial_credits' => ($data['trial_credits'] ?? '') === '' ? 0 : Money::usdToMicros($data['trial_credits']),
        ]);

        return back()->with('status', __('Réglages de la plateforme enregistrés.'));
    }

    public function storeRate(Request $request)
    {
        $data = $request->validate($this->rateRules());
        AiRate::create($this->rateValues($data, $request));

        return back()->with('status', __('Tarif enregistré.'));
    }

    public function updateRate(Request $request, AiRate $rate)
    {
        $data = $request->validate($this->rateRules($rate));
        $rate->update($this->rateValues($data, $request));

        return back()->with('status', __('Tarif enregistré.'));
    }

    public function destroyRate(AiRate $rate)
    {
        $rate->delete();

        return back()->with('status', __('Tarif supprimé : ce modèle n’est plus proposé.'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'template_id' => ['required', 'string', 'regex:/^[a-z0-9-]{2,60}$/'],
            'price' => ['required', 'string', 'regex:/^\d{1,9}([.,]\d{1,2})?$/'],
            'currency' => ['required', Rule::in(Money::CURRENCIES)],
        ]);
        TemplatePrice::updateOrCreate(['template_id' => $data['template_id']], [
            'price' => Money::parse($data['price'], $data['currency']), 'currency' => $data['currency'], 'active' => true,
        ]);

        return back()->with('status', __('Modèle payant enregistré.'));
    }

    public function destroyTemplate(TemplatePrice $price)
    {
        $price->delete();

        return back()->with('status', __('Ce modèle est de nouveau gratuit.'));
    }

    public function storeTld(Request $request)
    {
        $data = $request->validate([
            'tld' => ['required', 'string', 'max:30', 'regex:/^[a-z0-9]+(\.[a-z0-9]+)?$/'],
            'register_price' => ['required', 'string', 'regex:/^\d{1,9}([.,]\d{1,2})?$/'],
            'renew_price' => ['required', 'string', 'regex:/^\d{1,9}([.,]\d{1,2})?$/'],
            'currency' => ['required', Rule::in(Money::CURRENCIES)],
        ]);
        TldPrice::updateOrCreate(['tld' => ltrim(strtolower($data['tld']), '.')], [
            'register_price' => Money::parse($data['register_price'], $data['currency']),
            'renew_price' => Money::parse($data['renew_price'], $data['currency']),
            'currency' => $data['currency'],
            'active' => true,
        ]);

        return back()->with('status', __('Extension enregistrée.'));
    }

    public function destroyTld(TldPrice $tld)
    {
        $tld->delete();

        return back()->with('status', __('Extension retirée de la vente.'));
    }

    private function rateRules(?AiRate $rate = null): array
    {
        return [
            'model' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9][a-z0-9.\-]*$/', Rule::unique('platform_ai_rates', 'model')->ignore($rate?->id)],
            'label' => ['required', 'string', 'max:80'],
            'input_price' => ['required', 'string', self::DECIMAL],
            'output_price' => ['required', 'string', self::DECIMAL],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    private function rateValues(array $data, Request $request): array
    {
        return [
            'model' => $data['model'],
            'label' => $data['label'],
            'input_price' => str_replace(',', '.', $data['input_price']),
            'output_price' => str_replace(',', '.', $data['output_price']),
            'active' => $request->boolean('active', true),
        ];
    }
}
