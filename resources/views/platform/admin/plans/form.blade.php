@extends('layouts.admin')
@section('title', $plan->exists ? __('Modifier l’offre') : __('Nouvelle offre'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    @include('platform.admin._nav')
    <h1 class="text-2xl font-semibold tracking-tight">{{ $plan->exists ? __('Modifier l’offre') : __('Nouvelle offre') }}</h1>
    <form method="POST" action="{{ $plan->exists ? route('admin.platform.plans.update', $plan) : route('admin.platform.plans.store') }}" class="mt-6 max-w-3xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
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
        <div>
            <label for="features" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Points forts (un par ligne)') }}</label>
            <textarea id="features" name="features" rows="4" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('features', implode("\n", (array) $plan->features)) }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked(old('active', $plan->active))> {{ __('En vente') }}</label>
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Enregistrer') }}</button>
    </form>
@endsection
