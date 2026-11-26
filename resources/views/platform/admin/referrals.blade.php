@extends('platform.layouts.console')
@section('title', __('Affiliation'))
@section('content')
@php
    use App\Platform\Money;
    $statusLabels = ['pending' => __('En attente'), 'available' => __('Disponible'), 'canceled' => __('Annulée')];
    $input = 'rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
@endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Affiliation') }}</h1>
    <p class="mt-1 max-w-2xl text-sm text-slate-500">{{ __('L’affilié gagne :rate % de la première commande payée de chaque client qu’il apporte, hors noms de domaine, disponible :days jours après le paiement sur son solde d’affiliation.', ['rate' => $rate, 'days' => $holdDays]) }}</p>

    {{-- Retraits à traiter --}}
    <h2 id="retraits" class="mt-8 text-lg font-semibold">{{ __('Retraits à traiter') }}</h2>
    @if ($requested->isEmpty())
        <p class="mt-3 rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">{{ __('Aucune demande de retrait en attente.') }}</p>
    @else
        <div class="mt-3 space-y-3">
            @foreach ($requested as $p)
                <div class="rounded-xl border border-brand-200 bg-white p-5">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <p class="text-lg font-semibold">{{ Money::format($p->amount, $p->currency) }}</p>
                        <p class="text-sm text-slate-500">{{ __('demandé le :date', ['date' => $p->created_at->isoFormat('LLL')]) }}</p>
                    </div>
                    <p class="mt-1 text-sm"><a href="{{ route('console.customers.show', $p->customer_id) }}" class="font-medium text-brand-700 hover:underline">{{ $p->customer?->name }}</a> · {{ $p->customer?->email }}</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $operators[$p->mobile_operator] ?? $p->mobile_operator }} · <span class="font-mono">{{ $p->phone }}</span></p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('console.referrals.payouts.paid', $p) }}" class="flex flex-wrap gap-2">@csrf
                            <label class="sr-only" for="ref-{{ $p->id }}">{{ __('Référence du transfert') }}</label>
                            <input id="ref-{{ $p->id }}" name="transfer_reference" required maxlength="120" placeholder="{{ __('Référence du transfert') }}" class="{{ $input }} w-56">
                            <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Marquer comme envoyé') }}</button>
                        </form>
                        <form method="POST" action="{{ route('console.referrals.payouts.reject', $p) }}" class="flex flex-wrap gap-2">@csrf
                            <label class="sr-only" for="note-{{ $p->id }}">{{ __('Motif du refus') }}</label>
                            <input id="note-{{ $p->id }}" name="note" required maxlength="255" placeholder="{{ __('Motif du refus') }}" class="{{ $input }} w-56">
                            <button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">{{ __('Refuser') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Chiffres et réglages --}}
    <div class="mt-10 grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Clients venus par affiliation') }}</p>
            <p class="mt-1 text-2xl font-semibold">{{ $referredCount }}</p>
            @if ($topSponsors->isNotEmpty())
                <ul class="mt-3 space-y-1 text-sm">@foreach ($topSponsors as $s)<li class="flex justify-between gap-2"><a class="truncate text-brand-700 hover:underline" href="{{ route('console.customers.show', $s) }}">{{ $s->name }}</a><span class="text-slate-500">{{ trans_choice('{1} 1 client apporté|[2,*] :count clients apportés', $s->referrals_count) }}</span></li>@endforeach</ul>
            @endif
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Commissions') }}</p>
            <ul class="mt-2 space-y-1 text-sm">
                @forelse ($totals as $t)
                    <li class="flex justify-between gap-2"><span>{{ $statusLabels[$t->status] ?? $t->status }} ({{ $t->n }})</span><span class="font-medium">{{ Money::format((int) $t->total, $t->currency) }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('Aucune commission pour l’instant.') }}</li>
                @endforelse
            </ul>
        </div>
        <form method="POST" action="{{ route('console.referrals.settings') }}" class="space-y-3 rounded-xl border border-slate-200 bg-white p-5">
            @csrf @method('PUT')
            <p class="text-sm font-medium text-slate-700">{{ __('Réglages') }}</p>
            <label class="flex items-center justify-between gap-3 text-sm">{{ __('Taux de commission (%)') }}
                <input name="platform_referral_rate" type="number" min="0" max="100" value="{{ $rate }}" class="{{ $input }} w-24"></label>
            <label class="flex items-center justify-between gap-3 text-sm">{{ __('Délai avant disponibilité (jours)') }}
                <input name="platform_referral_hold_days" type="number" min="0" max="365" value="{{ $holdDays }}" class="{{ $input }} w-24"></label>
            <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Enregistrer') }}</button>
        </form>
    </div>

    {{-- Commissions --}}
    <div class="mt-10 flex flex-wrap items-end justify-between gap-3">
        <h2 class="text-lg font-semibold">{{ __('Commissions') }}</h2>
        <nav class="flex gap-1 text-sm">
            <a href="{{ route('console.referrals.index') }}" @class(['rounded-lg px-3 py-1.5', 'bg-brand-50 font-medium text-brand-700' => ! $status, 'text-slate-600 hover:bg-slate-100' => $status])>{{ __('Toutes') }}</a>
            @foreach ($statusLabels as $key => $label)
                <a href="{{ route('console.referrals.index', ['statut' => $key]) }}" @class(['rounded-lg px-3 py-1.5', 'bg-brand-50 font-medium text-brand-700' => $status === $key, 'text-slate-600 hover:bg-slate-100' => $status !== $key])>{{ $label }}</a>
            @endforeach
        </nav>
    </div>
    @if ($commissions->isEmpty())
        <p class="mt-3 text-sm text-slate-500">{{ __('Aucune commission.') }}</p>
    @else
        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">{{ __('Affilié') }}</th><th class="px-4 py-3">{{ __('Client apporté') }}</th><th class="px-4 py-3">{{ __('Commande') }}</th><th class="px-4 py-3">{{ __('Commission') }}</th><th class="px-4 py-3">{{ __('État') }}</th><th class="px-4 py-3"><span class="sr-only">{{ __('Actions') }}</span></th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($commissions as $c)
                        <tr>
                            <td class="px-4 py-3"><a class="text-brand-700 hover:underline" href="{{ route('console.customers.show', $c->sponsor_id) }}">{{ $c->sponsor?->name }}</a></td>
                            <td class="px-4 py-3">{{ $c->referred?->name }}</td>
                            <td class="px-4 py-3"><a class="font-mono text-xs text-brand-700 hover:underline" href="{{ route('console.orders.show', $c->order_id) }}">{{ $c->order?->number }}</a></td>
                            <td class="px-4 py-3 font-medium">{{ Money::format($c->amount, $c->currency) }}</td>
                            <td class="px-4 py-3">{{ $statusLabels[$c->status] ?? $c->status }}@if ($c->status === 'pending')<br><span class="text-xs text-slate-500">{{ __('le :date', ['date' => $c->available_at->isoFormat('LL')]) }}</span>@elseif ($c->cancel_reason)<br><span class="text-xs text-slate-500">{{ $c->cancel_reason }}</span>@endif</td>
                            <td class="px-4 py-3 text-right">
                                @if ($c->status !== 'canceled')
                                    <form method="POST" action="{{ route('console.referrals.cancel', $c) }}" class="flex justify-end gap-2" onsubmit="return confirm(@js(__('Annuler cette commission ? Si elle est déjà disponible, elle sera reprise sur le solde de l’affilié.')))">@csrf
                                        <label class="sr-only" for="reason-{{ $c->id }}">{{ __('Motif') }}</label>
                                        <input id="reason-{{ $c->id }}" name="reason" required maxlength="255" placeholder="{{ __('Motif') }}" class="{{ $input }} w-32 py-1">
                                        <button class="text-sm text-red-600 hover:underline">{{ __('Annuler') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $commissions->links() }}</div>
    @endif

    @if ($processed->isNotEmpty())
        <h2 class="mt-10 text-lg font-semibold">{{ __('Retraits traités') }}</h2>
        <ul class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white text-sm">
            @foreach ($processed as $p)
                <li class="flex flex-wrap justify-between gap-2 px-4 py-3">
                    <span>{{ $p->customer?->name }} · {{ Money::format($p->amount, $p->currency) }}</span>
                    <span class="text-slate-500">{{ $p->status === 'paid' ? __('Envoyé, réf. :ref', ['ref' => $p->transfer_reference]) : __('Refusé : :note', ['note' => $p->note]) }} · {{ $p->operator?->name }} · {{ $p->processed_at?->isoFormat('LL') }}</span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
