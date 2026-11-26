@extends('platform.layouts.customer')
@section('title', __('Offres'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Offres') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Paiement en ligne sécurisé, dans votre devise.') }}</p>

    @if ($services->isNotEmpty())
        <section id="services" class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Vos services') }}</h2>
            <ul class="mt-3 divide-y divide-slate-100 text-sm">
                @foreach ($services as $service)
                    <li class="py-2">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <span class="font-medium">{{ $service->label() }}</span>
                            <span @class(['rounded-full px-2 py-0.5 text-xs font-semibold',
                                'bg-emerald-100 text-emerald-800' => $service->status === 'active' && ! $service->pending_action,
                                'bg-amber-100 text-amber-800' => $service->status === 'pending' || $service->pending_action,
                                'bg-red-100 text-red-700' => $service->status === 'failed'])>
                                {{ $service->status === 'pending' ? __('En préparation') : ($service->pending_action === 'renew' ? __('Renouvellement en cours') : Labels::status($service->status)) }}
                            </span>
                        </div>
                        @if ($service->expires_at && $service->status === 'active')<p class="text-xs text-slate-500">{{ __('Actif jusqu’au :date', ['date' => $service->expires_at->isoFormat('LL')]) }}</p>@endif
                        @if ($service->status === 'pending')<p class="text-xs text-slate-500">{{ __('Notre équipe prépare ce service : vous serez prévenu par e-mail dès qu’il est prêt.') }}</p>@endif
                        @if ($service->note)<p class="mt-1 whitespace-pre-line text-slate-600">{{ $service->note }}</p>@endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

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
                    <form method="POST" action="{{ route('platform.customer.plans.buy', $plan) }}" class="mt-4 space-y-2">@csrf
                        @if ($plan->isSupplied())
                            @if ($plan->needs_domain)
                                <label class="block text-xs font-medium text-slate-600">{{ __('Nom de domaine') }}
                                    <input name="domain" required list="gp-domains" placeholder="monassociation.org" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal">
                                </label>
                            @endif
                            @if (in_array($plan->kind, ['mail', 'storage'], true))
                                <label class="block text-xs font-medium text-slate-600">{{ $plan->kind === 'mail' ? __('Adresses souhaitées') : __('Précisions (facultatif)') }}
                                    <textarea name="request" rows="2" @if ($plan->kind === 'mail') placeholder="contact@…, direction@…" @endif class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-normal"></textarea>
                                </label>
                            @endif
                        @endif
                        <button class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ $kind === 'ai_credits' ? __('Acheter') : __('Souscrire') }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    @empty
        <p class="mt-6 text-sm text-slate-500">{{ __('Aucune offre pour le moment.') }}</p>
    @endforelse
    <datalist id="gp-domains">@foreach ($domainNames as $name)<option value="{{ $name }}">@endforeach</datalist>
@endsection
