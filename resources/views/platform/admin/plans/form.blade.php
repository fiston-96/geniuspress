@extends('platform.layouts.console')
@section('title', $plan->exists ? __('Modifier l’offre') : __('Nouvelle offre'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ $plan->exists ? __('Modifier l’offre') : __('Nouvelle offre') }}</h1>
    <form method="POST" action="{{ $plan->exists ? route('console.plans.update', $plan) : route('console.plans.store') }}" class="mt-6 max-w-3xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf @if ($plan->exists) @method('PUT') @endif
        <div class="grid gap-4 md:grid-cols-2">
            <x-field name="slug" :label="__('Identifiant')" :value="$plan->slug" :hint="__('Minuscules, chiffres et tirets : « essentiel », « credits-10 ».')" required />
            <div>
                <label for="kind" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Type') }}</label>
                <select id="kind" name="kind" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (\App\Platform\Models\Plan::KINDS as $kind)<option value="{{ $kind }}" @selected(old('kind', $plan->kind) === $kind)>{{ Labels::kind($kind) }}</option>@endforeach
                </select>
            </div>
            <x-field name="name_fr" :label="__('Nom (français)')" :value="$plan->name['fr'] ?? ''" required />
            <x-field name="name_en" :label="__('Nom (anglais)')" :value="$plan->name['en'] ?? ''" />
            <x-field name="description_fr" :label="__('Description (français)')" :value="$plan->description['fr'] ?? ''" />
            <x-field name="description_en" :label="__('Description (anglais)')" :value="$plan->description['en'] ?? ''" />
            <x-field name="price" :label="__('Prix')" :value="$plan->exists ? Money::input($plan->price, Money::decimals($plan->currency)) : ''" required />
            <div>
                <label for="currency" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Devise') }}</label>
                <select id="currency" name="currency" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (Money::CURRENCIES as $currency)<option value="{{ $currency }}" @selected(old('currency', $plan->currency) === $currency)>{{ $currency }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="period" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Période') }}</label>
                <select id="period" name="period" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (\App\Platform\Models\Plan::PERIODS as $period)<option value="{{ $period }}" @selected(old('period', $plan->period) === $period)>{{ Labels::period($period) }}</option>@endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">{{ __('Les packs de crédits IA sont toujours en paiement unique.') }}</p>
            </div>
            <x-field name="trial_days" type="number" min="0" max="90" :label="__('Jours d’essai gratuit (hébergement)')" :value="$plan->trial_days" />
            <x-field name="ai_credits" :label="__('Crédits IA inclus, en $')" :value="$plan->ai_credits ? Money::input($plan->ai_credits, 6) : ''" :hint="__('Valeur des crédits ajoutés au compte du client à chaque achat ou période payée.')" />
            <x-field name="sort" type="number" min="0" :label="__('Ordre d’affichage')" :value="$plan->sort" />
        </div>
        <fieldset class="rounded-lg border border-slate-200 p-4">
            <legend class="px-1 text-sm font-medium text-slate-700">{{ __('Offre partenaire (hébergement web, mail pro, stockage)') }}</legend>
            <p class="text-xs text-slate-500">{{ __('Seulement pour ces trois types. LWS : l’hébergement web est acheté automatiquement par l’API ; le mail pro et le stockage arrivent dans la file « À livrer » des Fournisseurs.') }}</p>
            <div class="mt-3 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="provider" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Livrée par') }}</label>
                    <select id="provider" name="provider" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">—</option>
                        <option value="lws" @selected(old('provider', $plan->provider) === 'lws')>{{ __('LWS') }}</option>
                        <option value="manual" @selected(old('provider', $plan->provider) === 'manual')>{{ __('L’équipe (livraison manuelle)') }}</option>
                    </select>
                    @error('provider')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <x-field name="provider_ref" :label="__('Nom de l’offre chez le fournisseur')" :value="$plan->provider_ref" :hint="__('Exactement comme chez LWS : « LWS Perso », « LWS Pro »…')" />
                <x-field name="cost" :label="__('Coût d’achat par période')" :value="$plan->cost !== null ? Money::input($plan->cost, Money::decimals($plan->cost_currency ?? 'EUR')) : ''" :hint="__('Mis à jour chaque nuit pour les offres LWS connues de l’API.')" />
                <div>
                    <label for="cost_currency" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Devise du coût') }}</label>
                    <select id="cost_currency" name="cost_currency" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach (Money::CURRENCIES as $currency)<option value="{{ $currency }}" @selected(old('cost_currency', $plan->cost_currency ?? 'EUR') === $currency)>{{ $currency }}</option>@endforeach
                    </select>
                </div>
            </div>
            <label class="mt-3 flex items-center gap-2 text-sm"><input type="hidden" name="needs_domain" value="0"><input type="checkbox" name="needs_domain" value="1" @checked(old('needs_domain', $plan->needs_domain))> {{ __('Le client indique un nom de domaine (toujours pour l’hébergement web)') }}</label>
        </fieldset>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="features" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Points forts (un par ligne, français)') }}</label>
                <textarea id="features" name="features" rows="6" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('features', implode("\n", $plan->featureList('fr'))) }}</textarea>
            </div>
            <div>
                <label for="features_en" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Points forts (un par ligne, anglais)') }}</label>
                <textarea id="features_en" name="features_en" rows="6" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('features_en', isset(((array) $plan->features)['en']) ? implode("\n", $plan->featureList('en')) : '') }}</textarea>
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked(old('active', $plan->active))> {{ __('En vente') }}</label>
        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Enregistrer') }}</button>
    </form>
@endsection
