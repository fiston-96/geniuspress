@extends('platform.layouts.console')
@section('title', __('Offres'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <div class="flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-slate-500">{{ __('Hébergement, options et packs de crédits IA. Une offre retirée de la vente reste valable pour les abonnés.') }}</p>
        <a href="{{ route('console.plans.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ __('+ Nouvelle offre') }}</a>
    </div>
    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Offre') }}</th><th class="px-5 py-3">{{ __('Type') }}</th><th class="px-5 py-3">{{ __('Prix') }}</th><th class="px-5 py-3">{{ __('Crédits IA inclus') }}</th><th class="px-5 py-3">{{ __('Essai') }}</th><th class="px-5 py-3">{{ __('Abonnés') }}</th><th class="px-5 py-3">{{ __('En vente') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($plans as $plan)
                <tr>
                    <td class="px-5 py-2"><a href="{{ route('console.plans.edit', $plan) }}" class="font-medium hover:text-brand-600">{{ $plan->label() }}</a><p class="text-xs text-slate-500">{{ $plan->slug }}</p></td>
                    <td class="px-5 py-2">{{ Labels::kind($plan->kind) }}</td>
                    <td class="px-5 py-2 whitespace-nowrap">{{ Money::format($plan->price, $plan->currency) }} <span class="text-xs text-slate-500">{{ Labels::period($plan->period) }}</span></td>
                    <td class="px-5 py-2">{{ $plan->ai_credits ? Money::micros($plan->ai_credits) : '—' }}</td>
                    <td class="px-5 py-2">{{ $plan->trial_days ? __(':days jours', ['days' => $plan->trial_days]) : '—' }}</td>
                    <td class="px-5 py-2">{{ $plan->subscriptions_count }}</td>
                    <td class="px-5 py-2">{{ $plan->active ? __('Oui') : __('Non') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-6 text-center text-slate-500">{{ __('Aucune offre. Créez la première.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
