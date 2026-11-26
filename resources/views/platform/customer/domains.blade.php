@extends('platform.layouts.customer')
@section('title', __('Domaines'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Noms de domaine') }}</h1>
    <form class="mt-6 flex flex-wrap gap-2">
        <input name="q" value="{{ $query }}" required maxlength="120" placeholder="monassociation.org" aria-label="{{ __('Nom de domaine') }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Vérifier la disponibilité') }}</button>
    </form>
    @if ($error)<p class="mt-3 text-sm text-red-600">{{ $error }}</p>@endif

    @if ($results && $contactFields)
        {{-- Coordonnées du titulaire, exigées par les registres : le domaine est enregistré à votre nom. --}}
        <details id="titulaire" class="mt-4 rounded-xl border border-slate-200 bg-white p-5 text-sm" @if ($errors->hasAny($contactFields) || collect($contactFields)->contains(fn ($f) => $f !== 'postal_code' && ! $customer->{$f})) open @endif>
            <summary class="cursor-pointer font-medium">{{ __('Titulaire du domaine : :name', ['name' => $customer->name]) }}</summary>
            <p class="mt-2 text-slate-500">{{ __('Le domaine est enregistré à votre nom : le registre exige vos vraies coordonnées. Elles sont gardées sur votre compte.') }}</p>
            <div class="mt-3 grid gap-3 md:grid-cols-2">
                @foreach ($contactFields as $field)
                    <label class="block text-xs font-medium text-slate-600">{{ $contactLabels[$field] }}
                        <input form="gp-buy-contact" name="{{ $field }}" value="{{ old($field, $customer->{$field}) }}" @if ($field !== 'postal_code') required @endif @if ($field === 'country') maxlength="2" @endif class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
                    </label>
                @endforeach
            </div>
        </details>
    @endif

    @if ($results)
        <div class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
            @foreach ($results as $result)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-sm">
                    <span class="font-medium">{{ $result['domain'] }}</span>
                    @if ($result['available'])
                        <form method="POST" action="{{ route('platform.customer.domains.buy') }}" class="flex items-center gap-2" data-gp-contact>@csrf
                            <input type="hidden" name="domain" value="{{ $result['domain'] }}">
                            <span class="text-slate-600">{{ __(':price par an', ['price' => Money::format($result['price'], $result['currency'])]) }}</span>
                            <select name="years" class="rounded-lg border border-slate-300 px-2 py-1 text-sm" aria-label="{{ __('Durée') }}">
                                @foreach ([1, 2, 3, 5] as $y)<option value="{{ $y }}">{{ trans_choice(':count an|:count ans', $y) }}</option>@endforeach
                            </select>
                            <button class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Acheter') }}</button>
                        </form>
                    @else
                        <span class="text-slate-500">{{ __('Déjà pris') }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($results && $contactFields)
        <form id="gp-buy-contact" hidden></form>
        <script>
            // Les coordonnées du titulaire sont saisies une fois et ajoutées au formulaire d'achat choisi.
            document.querySelectorAll('form[data-gp-contact]').forEach((form) => form.addEventListener('submit', () => {
                document.querySelectorAll('input[form="gp-buy-contact"]').forEach((input) => {
                    const copy = document.createElement('input');
                    copy.type = 'hidden'; copy.name = input.name; copy.value = input.value;
                    form.appendChild(copy);
                });
            }));
        </script>
    @endif

    <h2 class="mt-10 font-semibold">{{ __('Mes domaines') }}</h2>
    <div class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
        @forelse ($domains as $domain)
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-sm">
                <div>
                    <span class="font-medium">{{ $domain->name }}</span>
                    <span class="ml-2 text-slate-500">{{ Labels::status($domain->status) }}@if ($domain->expires_at) · {{ __('expire le :date', ['date' => $domain->expires_at->isoFormat('LL')]) }}@endif</span>
                    @if ($domain->site_slug)<span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">{{ __('Branché sur :site', ['site' => $sites->firstWhere('slug', $domain->site_slug)?->name ?? $domain->site_slug]) }}</span>@endif
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <form method="POST" action="{{ route('platform.customer.domains.renew', $domain) }}" class="flex items-center gap-2">@csrf
                        <select name="years" class="rounded-lg border border-slate-300 px-2 py-1 text-sm" aria-label="{{ __('Durée') }}">
                            @foreach (array_filter([1, 2, 3], fn ($y) => $y <= $maxRenew) as $y)<option value="{{ $y }}">{{ trans_choice(':count an|:count ans', $y) }}</option>@endforeach
                        </select>
                        <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium hover:bg-slate-50">{{ __('Renouveler') }}</button>
                    </form>
                    @if ($domain->status === 'active')
                        <form method="POST" action="{{ route('platform.customer.domains.authcode', $domain) }}">@csrf
                            <button class="rounded-lg px-2 py-1.5 text-xs text-slate-500 hover:text-slate-800 hover:underline" title="{{ __('Pour transférer ce domaine ailleurs : il vous appartient.') }}">{{ __('Code de transfert') }}</button>
                        </form>
                    @endif
                </div>
            </div>
            @if ($domain->status === 'active' && $sites->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 border-t border-dashed border-slate-100 bg-slate-50/60 px-5 py-2 text-sm">
                    <form method="POST" action="{{ route('platform.customer.domains.connect', $domain) }}" class="flex flex-wrap items-center gap-2">@csrf
                        <label for="site-{{ $domain->id }}" class="text-slate-600">{{ __('Afficher mon site à cette adresse :') }}</label>
                        <select id="site-{{ $domain->id }}" name="site" class="rounded-lg border border-slate-300 px-2 py-1 text-sm">
                            @foreach ($sites as $site)<option value="{{ $site->slug }}" @selected($domain->site_slug === $site->slug)>{{ $site->name }} ({{ $site->host }})</option>@endforeach
                        </select>
                        <button class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-500">{{ $domain->site_slug ? __('Changer') : __('Brancher') }}</button>
                    </form>
                    @if ($domain->site_slug)
                        <form method="POST" action="{{ route('platform.customer.domains.disconnect', $domain) }}">@csrf<button class="text-xs text-slate-500 hover:text-red-600 hover:underline">{{ __('Débrancher') }}</button></form>
                    @endif
                </div>
            @endif
        @empty
            <p class="px-5 py-4 text-sm text-slate-500">{{ __('Aucun domaine.') }}</p>
        @endforelse
    </div>
@endsection
