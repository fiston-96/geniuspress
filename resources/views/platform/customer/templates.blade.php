@extends('platform.layouts.customer')
@section('title', __('Modèles payants'))
@section('content')
@php use App\Platform\Money; @endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Modèles payants') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Un modèle acheté s’installe depuis Modèles, dans l’administration de chacun de vos sites reliés par une clé de licence.') }}</p>

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        @forelse ($prices as $price)
            @php $entry = $catalog[$price->template_id] ?? null; @endphp
            <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">{{ $entry['name'][$lang] ?? $price->template_id }}</h2>
                <p class="mt-1 flex-1 text-sm text-slate-600">{{ $entry['description'][$lang] ?? '' }}</p>
                <p class="mt-3 text-xl font-semibold">{{ Money::format($price->price, $price->currency) }}</p>
                <div class="mt-4 flex items-center gap-3">
                    @if (in_array($price->template_id, $owned, true))
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700">{{ __('Acheté') }}</span>
                    @else
                        <form method="POST" action="{{ route('platform.customer.templates.buy', $price->template_id) }}">@csrf
                            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Acheter') }}</button>
                        </form>
                    @endif
                    @if ($entry)<a href="{{ $entry['demo'] }}" target="_blank" rel="noopener" class="text-sm text-indigo-600 hover:underline">{{ __('Voir la démo ↗') }}</a>@endif
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ __('Tous les modèles sont gratuits pour le moment.') }}</p>
        @endforelse
    </div>
@endsection
