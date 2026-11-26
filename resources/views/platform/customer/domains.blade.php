@extends('platform.layouts.customer')
@section('title', __('Domaines'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Noms de domaine') }}</h1>
    <form class="mt-6 flex flex-wrap gap-2">
        <input name="q" value="{{ $query }}" required maxlength="120" placeholder="monassociation.org" aria-label="{{ __('Nom de domaine') }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Vérifier la disponibilité') }}</button>
    </form>
    @if ($error)<p class="mt-3 text-sm text-red-600">{{ $error }}</p>@endif

    @if ($results)
        <div class="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
            @foreach ($results as $result)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-sm">
                    <span class="font-medium">{{ $result['domain'] }}</span>
                    @if ($result['available'])
                        <form method="POST" action="{{ route('platform.customer.domains.buy') }}" class="flex items-center gap-2">@csrf
                            <input type="hidden" name="domain" value="{{ $result['domain'] }}">
                            <span class="text-slate-600">{{ __(':price par an', ['price' => Money::format($result['price'], $result['currency'])]) }}</span>
                            <select name="years" class="rounded-lg border border-slate-300 px-2 py-1 text-sm" aria-label="{{ __('Durée') }}">
                                @foreach ([1, 2, 3, 5] as $y)<option value="{{ $y }}">{{ trans_choice(':count an|:count ans', $y) }}</option>@endforeach
                            </select>
                            <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Acheter') }}</button>
                        </form>
                    @else
                        <span class="text-slate-500">{{ __('Déjà pris') }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <h2 class="mt-10 font-semibold">{{ __('Mes domaines') }}</h2>
    <div class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
        @forelse ($domains as $domain)
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-sm">
                <div>
                    <span class="font-medium">{{ $domain->name }}</span>
                    <span class="ml-2 text-slate-500">{{ Labels::status($domain->status) }}@if ($domain->expires_at) · {{ __('expire le :date', ['date' => $domain->expires_at->isoFormat('LL')]) }}@endif</span>
                </div>
                <form method="POST" action="{{ route('platform.customer.domains.renew', $domain) }}" class="flex items-center gap-2">@csrf
                    <select name="years" class="rounded-lg border border-slate-300 px-2 py-1 text-sm" aria-label="{{ __('Durée') }}">
                        @foreach ([1, 2, 3] as $y)<option value="{{ $y }}">{{ trans_choice(':count an|:count ans', $y) }}</option>@endforeach
                    </select>
                    <button class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium hover:bg-slate-50">{{ __('Renouveler') }}</button>
                </form>
            </div>
        @empty
            <p class="px-5 py-4 text-sm text-slate-500">{{ __('Aucun domaine.') }}</p>
        @endforelse
    </div>
@endsection
