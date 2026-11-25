@extends('layouts.admin')
@section('title', $customer->name)
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    @include('platform.admin._nav')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ $customer->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $customer->email }} @if ($customer->phone)· {{ $customer->phone }}@endif @if ($customer->country)· {{ $customer->country }}@endif · {{ __('inscrit le :date', ['date' => $customer->created_at->isoFormat('LL')]) }}</p>
            <p class="mt-1 text-sm">{{ Labels::status($customer->status) }} @if ($customer->trial_used_at)· {{ __('essai utilisé le :date', ['date' => $customer->trial_used_at->isoFormat('LL')]) }}@endif</p>
        </div>
        <form method="POST" action="{{ route('admin.platform.customers.status', $customer) }}">@csrf @method('PUT')
            @if ($customer->isActive())
                <input type="hidden" name="status" value="suspended">
                <button class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50" onclick="return confirm(@js(__('Suspendre ce compte ? Ses sites perdront l’accès à l’IA.')))">{{ __('Suspendre le compte') }}</button>
            @else
                <input type="hidden" name="status" value="active">
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">{{ __('Réactiver le compte') }}</button>
            @endif
        </form>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Crédits IA') }} : {{ Money::micros($customer->ai_balance) }}</h2>
            <form method="POST" action="{{ route('admin.platform.customers.credits', $customer) }}" class="mt-4 grid gap-2 md:grid-cols-3">@csrf
                <input name="amount" required placeholder="{{ __('Montant en $ (ex. 5 ou -2,5)') }}" aria-label="{{ __('Montant en $') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input name="reason" required maxlength="200" placeholder="{{ __('Motif') }}" aria-label="{{ __('Motif') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Ajuster') }}</button>
            </form>
            <div class="mt-4 max-h-80 divide-y divide-slate-100 overflow-y-auto text-sm">
                @foreach ($transactions as $tx)
                    <div class="flex justify-between gap-3 py-1.5">
                        <span><span class="text-slate-500">{{ $tx->created_at->isoFormat('lll') }}</span> · {{ Labels::kind($tx->kind) }} · {{ $tx->description }}</span>
                        <span @class(['whitespace-nowrap', 'text-emerald-700' => $tx->amount > 0])>{{ $tx->amount > 0 ? '+' : '' }}{{ Money::micros($tx->amount) }}</span>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('admin.platform.usage.index', ['customer' => $customer->id]) }}" class="mt-3 inline-block text-sm text-indigo-600 hover:underline">{{ __('Journal IA de ce client') }}</a>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Abonnements') }}</h2>
            <div class="mt-3 divide-y divide-slate-100 text-sm">
                @forelse ($subscriptions as $subscription)
                    <div class="py-1.5">
                        <span class="font-medium">{{ $subscription->plan->label() }}</span> · {{ Labels::status($subscription->status) }}
                        @if ($subscription->endsAt())· {{ __('jusqu’au :date', ['date' => $subscription->endsAt()->isoFormat('LL')]) }}@endif
                        @if ($subscription->site_reference)<span class="text-xs text-slate-500">· {{ __('site :ref', ['ref' => $subscription->site_reference]) }}</span>@endif
                    </div>
                @empty
                    <p class="py-1.5 text-slate-500">{{ __('Aucun abonnement.') }}</p>
                @endforelse
            </div>
            <h2 class="mt-6 font-semibold">{{ __('Modèles achetés') }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ $templates ? implode(', ', $templates) : '—' }}</p>
            <h2 class="mt-6 font-semibold">{{ __('Domaines') }}</h2>
            <p class="mt-2 text-sm text-slate-600">{{ $domains->isNotEmpty() ? $domains->pluck('name')->implode(', ') : '—' }}</p>
        </section>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">{{ __('Clés de licence') }}</h2>
        <div class="mt-3 divide-y divide-slate-100 text-sm">
            @forelse ($licenses as $license)
                <div class="flex flex-wrap items-center justify-between gap-3 py-2">
                    <div>
                        <span class="font-medium">{{ $license->name }}</span> <code class="text-xs text-slate-500">{{ $license->displayKey() }}</code> · {{ Labels::status($license->status) }}
                        @if ($license->site_url)<span class="text-xs text-slate-500">· {{ $license->site_url }}</span>@endif
                        @if ($license->last_used_at)<span class="text-xs text-slate-500">· {{ __('utilisée :when', ['when' => $license->last_used_at->diffForHumans()]) }} ({{ $license->last_ip }})</span>@endif
                    </div>
                    @if ($license->isActive())
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.platform.licenses.cap', $license) }}" class="flex items-center gap-1">@csrf @method('PUT')
                                <input name="monthly_cap" value="{{ $license->monthly_cap !== null ? Money::input($license->monthly_cap, 6) : '' }}" placeholder="{{ __('Plafond $/mois') }}" aria-label="{{ __('Plafond mensuel en $') }}" class="w-32 rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                <button class="rounded-lg border border-slate-300 px-2 py-1 text-xs hover:bg-slate-50">{{ __('OK') }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.platform.licenses.revoke', $license) }}" onsubmit="return confirm(@js(__('Révoquer cette clé ?')))">@csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">{{ __('Révoquer') }}</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="py-1.5 text-slate-500">{{ __('Aucune clé.') }}</p>
            @endforelse
        </div>
    </section>

    <section class="mt-6">
        <h2 class="font-semibold">{{ __('Commandes') }}</h2>
        @include('platform.admin.orders._table', ['orders' => $orders->each(fn ($o) => $o->setRelation('customer', $customer))])
    </section>
@endsection
