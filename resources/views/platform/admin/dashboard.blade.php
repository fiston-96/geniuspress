@extends('platform.layouts.console')
@section('title', __('Plateforme commerciale'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Clients') }}</p>
            <p class="mt-1 text-2xl font-semibold">{{ $customers }}</p>
            <p class="text-xs text-slate-500">{{ __(':count ce mois-ci', ['count' => $newCustomers]) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Abonnements en cours') }}</p>
            <p class="mt-1 text-2xl font-semibold">{{ $subscriptions->count() }}</p>
            <p class="text-xs text-slate-500">{{ __(':count en essai', ['count' => $subscriptions->where('status', 'trialing')->count()]) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Encaissé ce mois-ci') }}</p>
            @forelse ($revenue as $row)
                <p class="mt-1 text-lg font-semibold">{{ Money::format((int) $row->total, $row->currency) }}</p>
            @empty
                <p class="mt-1 text-2xl font-semibold">—</p>
            @endforelse
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('IA ce mois-ci') }}</p>
            <p class="mt-1 text-lg font-semibold">{{ Money::micros((int) $ai->billed) }}</p>
            <p class="text-xs text-slate-500">{{ __('coût réel :cost · :calls appels', ['cost' => Money::micros((int) $ai->cost), 'calls' => (int) $ai->calls]) }}</p>
        </div>
    </div>

    @if ($failedItems->isNotEmpty())
        <section class="mt-8 rounded-xl border border-amber-300 bg-amber-50 p-6">
            <h2 class="font-semibold text-amber-900">{{ __('Achats payés non livrés') }}</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($failedItems as $item)
                    <li><a href="{{ route('console.orders.show', $item->order) }}" class="font-medium text-amber-900 hover:underline">{{ $item->order->number }}</a> — {{ $item->description }} ({{ $item->order->customer->email }}) : {{ $item->error }}</li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="mt-8">
        <h2 class="font-semibold">{{ __('Dernières commandes') }}</h2>
        @include('platform.admin.orders._table', ['orders' => $orders])
    </section>
@endsection
