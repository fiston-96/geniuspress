<?php

namespace App\Platform\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Platform\Models\Plan;
use App\Platform\Money;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Offres : hébergement, options et packs de crédits IA, avec prix et périodes. */
class PlanController extends Controller
{
    public function index()
    {
        return view('platform.admin.plans.index', [
            'plans' => Plan::query()->withCount(['subscriptions' => fn ($q) => $q->whereIn('status', ['trialing', 'active'])])
                ->orderBy('kind')->orderBy('sort')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return view('platform.admin.plans.form', ['plan' => new Plan(['kind' => 'hosting', 'period' => 'month', 'currency' => config('geniuspress.platform.currency'), 'active' => true])]);
    }

    public function store(Request $request)
    {
        $plan = Plan::create($this->validated($request));

        return redirect()->route('console.plans.index')->with('status', __('Offre « :name » créée.', ['name' => $plan->label()]));
    }

    public function edit(Plan $plan)
    {
        return view('platform.admin.plans.form', ['plan' => $plan]);
    }

    public function update(Request $request, Plan $plan)
    {
        $plan->update($this->validated($request, $plan));

        return redirect()->route('console.plans.index')->with('status', __('Offre « :name » enregistrée.', ['name' => $plan->label()]));
    }

    private function validated(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'slug' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/', Rule::unique('platform_plans', 'slug')->ignore($plan?->id)],
            'kind' => ['required', Rule::in(Plan::KINDS)],
            'name_fr' => ['required', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'description_fr' => ['nullable', 'string', 'max:500'],
            'description_en' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'string', 'regex:/^\d{1,9}([.,]\d{1,2})?$/'],
            'currency' => ['required', Rule::in(Money::CURRENCIES)],
            'period' => ['required', Rule::in(Plan::PERIODS)],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'ai_credits' => ['nullable', 'string', 'regex:/^\d{1,6}([.,]\d{1,6})?$/'],
            'features' => ['nullable', 'string', 'max:2000'],
            'sort' => ['nullable', 'integer', 'min:0', 'max:999'],
            'active' => ['sometimes', 'boolean'],
            // Offres partenaires (hébergement web, mail pro, stockage).
            'provider' => ['nullable', Rule::in(Plan::PROVIDERS)],
            'provider_ref' => ['nullable', 'string', 'max:120'],
            'cost' => ['nullable', 'string', 'regex:/^\d{1,9}([.,]\d{1,2})?$/'],
            'cost_currency' => ['nullable', Rule::in(Money::CURRENCIES)],
            'needs_domain' => ['sometimes', 'boolean'],
        ]);
        $supplied = in_array($data['kind'], Plan::SUPPLIER_KINDS, true);
        if ($supplied && empty($data['provider'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['provider' => __('Choisissez qui livre cette offre : LWS ou l’équipe.')]);
        }
        // L'API LWS n'achète que l'hébergement web, et pour un domaine donné.
        if ($supplied && $data['provider'] === 'lws' && $data['kind'] === 'web_hosting' && trim((string) ($data['provider_ref'] ?? '')) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['provider_ref' => __('Indiquez le nom exact de l’offre chez LWS (ex. LWS Perso).')]);
        }
        $costCurrency = $data['cost_currency'] ?? 'EUR';
        // Un pack de crédits s'achète une fois ; un essai n'a de sens que pour l'hébergement.
        if ($data['kind'] === 'ai_credits') {
            $data['period'] = 'once';
            $data['trial_days'] = 0;
        }

        return [
            'slug' => $data['slug'],
            'kind' => $data['kind'],
            'name' => array_filter(['fr' => $data['name_fr'], 'en' => $data['name_en'] ?? null]),
            'description' => array_filter(['fr' => $data['description_fr'] ?? null, 'en' => $data['description_en'] ?? null]),
            'price' => Money::parse($data['price'], $data['currency']),
            'currency' => $data['currency'],
            'period' => $data['period'],
            'trial_days' => $data['kind'] === 'hosting' ? (int) ($data['trial_days'] ?? 0) : 0,
            'ai_credits' => ($data['ai_credits'] ?? '') === '' ? 0 : Money::usdToMicros($data['ai_credits']),
            'features' => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($data['features'] ?? ''))))),
            'sort' => (int) ($data['sort'] ?? 0),
            'active' => $request->boolean('active'),
            'provider' => $supplied ? $data['provider'] : null,
            'provider_ref' => $supplied ? (trim((string) ($data['provider_ref'] ?? '')) ?: null) : null,
            'cost' => $supplied && ($data['cost'] ?? '') !== '' ? Money::parse($data['cost'], $costCurrency) : null,
            'cost_currency' => $supplied && ($data['cost'] ?? '') !== '' ? $costCurrency : null,
            // Hébergement web : toujours pour un domaine.
            'needs_domain' => $supplied && ($data['kind'] === 'web_hosting' || $request->boolean('needs_domain')),
        ];
    }
}
