@extends('platform.layouts.console')
@section('title', __('Tarifs'))
@section('content')
@php use App\Platform\Money; @endphp

    <section class="rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">{{ __('Tarifs de l’IA par modèle') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Prix réels d’Anthropic, en dollars par million de jetons (voir la page des tarifs de Claude). Le client paie le coût réel multiplié par le multiplicateur ci-dessous. Un modèle sans tarif actif est refusé.') }}</p>
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="py-2 pr-3">{{ __('Identifiant du modèle') }}</th><th class="py-2 pr-3">{{ __('Libellé') }}</th><th class="py-2 pr-3">{{ __('Entrée $/M') }}</th><th class="py-2 pr-3">{{ __('Sortie $/M') }}</th><th class="py-2 pr-3">{{ __('Facturé au client $/M') }}</th><th class="py-2 pr-3">{{ __('Actif') }}</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach ($rates as $rate)
                    <tr>
                        <td colspan="4" class="py-2 pr-3">
                            <form id="rate-{{ $rate->id }}" method="POST" action="{{ route('console.pricing.rates.update', $rate) }}" class="grid grid-cols-4 gap-2">@csrf @method('PUT')
                                <input name="model" value="{{ $rate->model }}" required aria-label="{{ __('Identifiant du modèle') }}" class="rounded-lg border border-slate-300 px-2 py-1 font-mono text-xs">
                                <input name="label" value="{{ $rate->label }}" required aria-label="{{ __('Libellé') }}" class="rounded-lg border border-slate-300 px-2 py-1 text-sm">
                                <input name="input_price" value="{{ Money::input(Money::scale((string) $rate->input_price, 6), 6) }}" required aria-label="{{ __('Entrée $/M') }}" class="rounded-lg border border-slate-300 px-2 py-1 text-sm">
                                <input name="output_price" value="{{ Money::input(Money::scale((string) $rate->output_price, 6), 6) }}" required aria-label="{{ __('Sortie $/M') }}" class="rounded-lg border border-slate-300 px-2 py-1 text-sm">
                            </form>
                        </td>
                        <td class="py-2 pr-3 whitespace-nowrap text-slate-600">{{ Money::micros($pricing->billedPerMillion((string) $rate->input_price)) }} / {{ Money::micros($pricing->billedPerMillion((string) $rate->output_price)) }}</td>
                        <td class="py-2 pr-3"><input type="hidden" name="active" value="0" form="rate-{{ $rate->id }}"><input type="checkbox" name="active" value="1" form="rate-{{ $rate->id }}" @checked($rate->active) aria-label="{{ __('Actif') }}"></td>
                        <td class="py-2 whitespace-nowrap text-right">
                            <button form="rate-{{ $rate->id }}" class="rounded-lg border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50">{{ __('Enregistrer') }}</button>
                            <form method="POST" action="{{ route('console.pricing.rates.destroy', $rate) }}" class="inline" onsubmit="return confirm(@js(__('Supprimer ce tarif ? Le modèle ne sera plus proposé.')))">@csrf @method('DELETE')
                                <button class="ml-1 text-xs text-red-600 hover:underline">{{ __('Supprimer') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <form method="POST" action="{{ route('console.pricing.rates.store') }}" class="mt-4 grid gap-2 md:grid-cols-5">@csrf
            <input name="model" required placeholder="claude-…" aria-label="{{ __('Identifiant du modèle') }}" class="rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm">
            <input name="label" required placeholder="{{ __('Libellé') }}" aria-label="{{ __('Libellé') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input name="input_price" required placeholder="{{ __('Entrée $/M') }}" aria-label="{{ __('Entrée $/M') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input name="output_price" required placeholder="{{ __('Sortie $/M') }}" aria-label="{{ __('Sortie $/M') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Ajouter le modèle') }}</button>
        </form>
    </section>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">{{ __('Réglages') }}</h2>
        <form method="POST" action="{{ route('console.pricing.settings') }}" class="mt-4 grid gap-4 md:grid-cols-3">@csrf @method('PUT')
            <x-field name="multiplier" :label="__('Multiplicateur du coût de l’IA')" :value="$multiplier" :hint="__('2 = le client paie deux fois le coût réel.')" required />
            <div>
                <label for="trial_plan" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Offre proposée à l’essai') }}</label>
                <select id="trial_plan" name="trial_plan" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">{{ __('Première offre d’hébergement avec essai') }}</option>
                    @foreach ($hostingPlans as $plan)<option value="{{ $plan->slug }}" @selected($trialPlan === $plan->slug)>{{ $plan->label() }} ({{ __(':days jours', ['days' => $plan->trial_days]) }})</option>@endforeach
                </select>
            </div>
            <x-field name="trial_credits" :label="__('Crédits IA offerts à l’essai, en $')" :value="$trialCredits ? Money::input($trialCredits, 6) : ''" />
            <div class="md:col-span-3"><button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Enregistrer les réglages') }}</button></div>
        </form>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Modèles de sites payants') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Les modèles absents de cette liste sont gratuits.') }}</p>
            <div class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($templatePrices as $price)
                    <div class="flex items-center justify-between gap-3 py-1.5">
                        <span><span class="font-medium">{{ $templates[$price->template_id] ?? $price->template_id }}</span> <span class="text-xs text-slate-500">{{ $price->template_id }}</span></span>
                        <span class="flex items-center gap-3">{{ Money::format($price->price, $price->currency) }}
                            <form method="POST" action="{{ route('console.pricing.templates.destroy', $price) }}">@csrf @method('DELETE')<button class="text-xs text-red-600 hover:underline">{{ __('Rendre gratuit') }}</button></form>
                        </span>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('console.pricing.templates.store') }}" class="mt-4 grid gap-2 md:grid-cols-4">@csrf
                @if ($templates)
                    <select name="template_id" required aria-label="{{ __('Modèle') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm md:col-span-2">
                        @foreach ($templates as $id => $name)<option value="{{ $id }}">{{ $name }} ({{ $id }})</option>@endforeach
                    </select>
                @else
                    <input name="template_id" required placeholder="ong-horizon" aria-label="{{ __('Modèle') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm md:col-span-2">
                @endif
                <input name="price" required placeholder="{{ __('Prix') }}" aria-label="{{ __('Prix') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="currency" aria-label="{{ __('Devise') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (Money::CURRENCIES as $currency)<option value="{{ $currency }}" @selected($currency === config('geniuspress.platform.currency'))>{{ $currency }}</option>@endforeach
                </select>
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500 md:col-span-4">{{ __('Mettre en vente') }}</button>
            </form>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Extensions de domaine vendues') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Prix de vente par an. Vérifiez le prix d’achat chez le registraire avant de fixer le vôtre.') }}</p>
            <div class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($tlds as $tld)
                    <div class="flex items-center justify-between gap-3 py-1.5">
                        <span class="font-medium">.{{ $tld->tld }}</span>
                        <span class="flex items-center gap-3">{{ Money::format($tld->register_price, $tld->currency) }} · {{ __('renouvellement :price', ['price' => Money::format($tld->renew_price, $tld->currency)]) }}
                            <form method="POST" action="{{ route('console.pricing.tlds.destroy', $tld) }}">@csrf @method('DELETE')<button class="text-xs text-red-600 hover:underline">{{ __('Retirer') }}</button></form>
                        </span>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('console.pricing.tlds.store') }}" class="mt-4 grid gap-2 md:grid-cols-4">@csrf
                <input name="tld" required placeholder="cd" aria-label="{{ __('Extension') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="register_price" required placeholder="{{ __('Achat') }}" aria-label="{{ __('Prix d’achat 1 an') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="renew_price" required placeholder="{{ __('Renouvellement') }}" aria-label="{{ __('Prix de renouvellement') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="currency" aria-label="{{ __('Devise') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (Money::CURRENCIES as $currency)<option value="{{ $currency }}" @selected($currency === config('geniuspress.platform.currency'))>{{ $currency }}</option>@endforeach
                </select>
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500 md:col-span-4">{{ __('Enregistrer l’extension') }}</button>
            </form>
        </section>
    </div>
@endsection
