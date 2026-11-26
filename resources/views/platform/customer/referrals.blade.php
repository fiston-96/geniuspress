@extends('platform.layouts.customer')
@section('title', __('Parrainage'))
@section('content')
@php
    use App\Platform\Money;
    $statusLabels = ['pending' => __('En attente'), 'available' => __('Disponible'), 'canceled' => __('Annulée')];
    $payoutLabels = ['requested' => __('Demandé'), 'paid' => __('Envoyé'), 'rejected' => __('Refusé')];
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
    $whatsapp = 'https://wa.me/?text='.rawurlencode(__('Je crée mes sites avec GeniusPress : builder, modèles et assistant IA. Inscris-toi avec mon lien :').' '.$link);
@endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Parrainage') }}</h1>
    <p class="mt-1 max-w-2xl text-sm text-slate-600">{{ __('Invitez d’autres organisations ou développeurs : vous gagnez :rate % de la première commande payée de chaque filleul (hors noms de domaine).', ['rate' => $rate]) }}</p>

    {{-- Lien --}}
    <div class="mt-6 rounded-xl border border-brand-200 bg-brand-50/60 p-5">
        <label for="ref-link" class="text-sm font-medium text-slate-700">{{ __('Votre lien de parrainage') }}</label>
        <div class="mt-2 flex flex-wrap gap-2">
            <input id="ref-link" readonly value="{{ $link }}" onclick="this.select()" class="{{ $input }} min-w-0 flex-1 font-mono">
            <button type="button" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500"
                onclick="navigator.clipboard.writeText(document.getElementById('ref-link').value).then(() => { this.textContent = @js(__('Copié !')); setTimeout(() => this.textContent = @js(__('Copier')), 2000); })">{{ __('Copier') }}</button>
            <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="rounded-lg border border-brand-300 bg-white px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50">{{ __('Partager sur WhatsApp') }}</a>
        </div>
        <p class="mt-2 text-xs text-slate-500">{{ __('Code : :code · Le lien est retenu 30 jours, même si votre filleul s’inscrit plus tard. Commission disponible :days jours après son paiement.', ['code' => $customer->referral_code, 'days' => $holdDays]) }}</p>
    </div>

    {{-- Chiffres et solde --}}
    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Filleuls') }}</p>
            <p class="mt-1 text-2xl font-semibold">{{ $referrals->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Commissions en attente') }}</p>
            <p class="mt-1 text-2xl font-semibold">{{ $commissions->where('status', 'pending')->groupBy('currency')->map(fn ($c, $cur) => Money::format($c->sum('amount'), $cur))->implode(' · ') ?: '—' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Solde disponible') }}</p>
            <p class="mt-1 text-2xl font-semibold">{{ collect($balances)->map(fn ($v, $cur) => Money::format($v, $cur))->implode(' · ') ?: '—' }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ __('Utilisable pour payer vos commandes (bouton « Payer avec mon solde »), ou à retirer.') }}</p>
        </div>
    </div>

    {{-- Retrait --}}
    <h2 class="mt-10 text-lg font-semibold">{{ __('Retirer par Mobile Money') }}</h2>
    @if ($balances)
        <form method="POST" action="{{ route('platform.customer.referrals.payout') }}" class="mt-3 grid gap-3 rounded-xl border border-slate-200 bg-white p-5 md:grid-cols-5 md:items-end">
            @csrf
            <div>
                <label for="currency" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Devise') }}</label>
                <select id="currency" name="currency" class="{{ $input }}">@foreach ($balances as $cur => $v)<option value="{{ $cur }}">{{ $cur }}</option>@endforeach</select>
            </div>
            <div>
                <label for="amount" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Montant') }}</label>
                <input id="amount" name="amount" inputmode="decimal" value="{{ old('amount') }}" required class="{{ $input }}">
            </div>
            <div>
                <label for="mobile_operator" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Opérateur') }}</label>
                <select id="mobile_operator" name="mobile_operator" class="{{ $input }}">@foreach ($operators as $code => $name)<option value="{{ $code }}" @selected(old('mobile_operator') === $code)>{{ $name }}</option>@endforeach</select>
            </div>
            <div>
                <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Numéro') }}</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" placeholder="+243 …" required class="{{ $input }}">
            </div>
            <button class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Demander le retrait') }}</button>
            @error('amount')<p class="text-sm text-red-600 md:col-span-5">{{ $message }}</p>@enderror
        </form>
    @else
        <p class="mt-3 rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">{{ __('Votre solde est vide pour l’instant. Partagez votre lien : chaque première commande d’un filleul vous rapporte :rate %.', ['rate' => $rate]) }}</p>
    @endif

    {{-- Commissions --}}
    <h2 class="mt-10 text-lg font-semibold">{{ __('Commissions') }}</h2>
    @if ($commissions->isEmpty())
        <p class="mt-3 text-sm text-slate-500">{{ __('Aucune commission pour l’instant.') }}</p>
    @else
        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-4 py-3">{{ __('Filleul') }}</th><th class="px-4 py-3">{{ __('Commission') }}</th><th class="px-4 py-3">{{ __('État') }}</th><th class="px-4 py-3">{{ __('Disponible le') }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($commissions as $c)
                        <tr>
                            <td class="px-4 py-3">{{ $c->referred?->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium">{{ Money::format($c->amount, $c->currency) }} <span class="text-xs font-normal text-slate-500">({{ $c->rate_bps / 100 }} % · {{ Money::format($c->base_amount, $c->currency) }})</span></td>
                            <td class="px-4 py-3">{{ $statusLabels[$c->status] ?? $c->status }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $c->status === 'canceled' ? '—' : $c->available_at->isoFormat('LL') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Filleuls --}}
    @if ($referrals->isNotEmpty())
        <h2 class="mt-10 text-lg font-semibold">{{ __('Vos filleuls') }}</h2>
        <ul class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white text-sm">
            @foreach ($referrals as $r)
                <li class="flex justify-between gap-3 px-4 py-3"><span>{{ $r->name }}</span><span class="text-slate-500">{{ __('inscrit le :date', ['date' => $r->referred_at?->isoFormat('LL')]) }}</span></li>
            @endforeach
        </ul>
    @endif

    {{-- Retraits et mouvements --}}
    @if ($payouts->isNotEmpty() || $transactions->isNotEmpty())
        <h2 class="mt-10 text-lg font-semibold">{{ __('Historique du solde') }}</h2>
        <div class="mt-3 grid gap-4 lg:grid-cols-2">
            <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white text-sm">
                @foreach ($transactions as $tx)
                    <li class="flex justify-between gap-3 px-4 py-3">
                        <span class="min-w-0"><span class="block truncate">{{ $tx->description }}</span><span class="text-xs text-slate-500">{{ $tx->created_at->isoFormat('LL') }}</span></span>
                        <span @class(['shrink-0 font-medium', 'text-emerald-700' => $tx->amount > 0, 'text-slate-700' => $tx->amount < 0])>{{ $tx->amount > 0 ? '+' : '' }}{{ Money::format($tx->amount, $tx->currency) }}</span>
                    </li>
                @endforeach
            </ul>
            <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white text-sm">
                @forelse ($payouts as $p)
                    <li class="px-4 py-3">
                        <span class="flex justify-between gap-3"><span class="font-medium">{{ Money::format($p->amount, $p->currency) }}</span><span>{{ $payoutLabels[$p->status] ?? $p->status }}</span></span>
                        <span class="text-xs text-slate-500">{{ $operators[$p->mobile_operator] ?? $p->mobile_operator }} · {{ $p->phone }} · {{ $p->created_at->isoFormat('LL') }}@if ($p->transfer_reference) · {{ __('réf. :ref', ['ref' => $p->transfer_reference]) }}@endif @if ($p->note) · {{ $p->note }}@endif</span>
                    </li>
                @empty
                    <li class="px-4 py-3 text-slate-500">{{ __('Aucun retrait demandé.') }}</li>
                @endforelse
            </ul>
        </div>
    @endif
@endsection
