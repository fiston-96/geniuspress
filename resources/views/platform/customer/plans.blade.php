@extends('platform.layouts.customer')
@section('title', __('Offres'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Offres') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Paiement par Mobile Money ou carte bancaire.') }}</p>

    @forelse ($plans as $kind => $group)
        <h2 id="{{ $kind }}" class="mt-8 font-semibold">{{ Labels::kind($kind) }}</h2>
        <div class="mt-3 grid gap-4 md:grid-cols-3">
            @foreach ($group as $plan)
                <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-6">
                    <h3 class="font-semibold">{{ $plan->label() }}</h3>
                    <p class="mt-2 text-2xl font-semibold">{{ Money::format($plan->price, $plan->currency) }} <span class="text-sm font-normal text-slate-500">{{ Labels::period($plan->period) }}</span></p>
                    @if ($plan->summary())<p class="mt-2 text-sm text-slate-600">{{ $plan->summary() }}</p>@endif
                    <ul class="mt-3 flex-1 space-y-1 text-sm text-slate-600">
                        @if ($plan->ai_credits > 0)<li>✓ {{ __(':amount de crédits IA', ['amount' => Money::micros($plan->ai_credits)]) }}</li>@endif
                        @if ($plan->trial_days > 0)<li>✓ {{ __(':days jours d’essai gratuit', ['days' => $plan->trial_days]) }}</li>@endif
                        @foreach ((array) $plan->features as $feature)<li>✓ {{ $feature }}</li>@endforeach
                    </ul>
                    <form method="POST" action="{{ route('platform.customer.plans.buy', $plan) }}" class="mt-4">@csrf
                        <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ $kind === 'ai_credits' ? __('Acheter') : __('Souscrire') }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    @empty
        <p class="mt-6 text-sm text-slate-500">{{ __('Aucune offre pour le moment.') }}</p>
    @endforelse
@endsection
