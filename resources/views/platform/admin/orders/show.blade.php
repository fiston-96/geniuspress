@extends('layouts.admin')
@section('title', __('Commande :number', ['number' => $order->number]))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    @include('platform.admin._nav')
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Commande :number', ['number' => $order->number]) }}</h1>
    <p class="mt-1 text-sm text-slate-500">
        <a href="{{ route('admin.platform.customers.show', $order->customer) }}" class="hover:text-brand-600">{{ $order->customer->name }} · {{ $order->customer->email }}</a>
        · {{ Labels::status($order->status) }} @if ($order->paid_at)· {{ __('payée le :date', ['date' => $order->paid_at->isoFormat('lll')]) }}@endif
    </p>

    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Type') }}</th><th class="px-5 py-3">{{ __('Libellé') }}</th><th class="px-5 py-3">{{ __('Montant') }}</th><th class="px-5 py-3">{{ __('Livraison') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($order->items as $item)
                <tr>
                    <td class="px-5 py-2">{{ Labels::kind($item->kind) }}</td>
                    <td class="px-5 py-2">{{ $item->description }}</td>
                    <td class="px-5 py-2 whitespace-nowrap">{{ Money::format($item->amount, $order->currency) }}</td>
                    <td class="px-5 py-2">
                        @if ($item->fulfilled_at){{ __('Livrée le :date', ['date' => $item->fulfilled_at->isoFormat('lll')]) }}
                        @elseif ($item->error)<span class="text-red-600">{{ $item->error }}</span>
                        @else —@endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @if ($order->isPaid() && $order->items->whereNull('fulfilled_at')->isNotEmpty())
        <form method="POST" action="{{ route('admin.platform.orders.deliver', $order) }}" class="mt-4">@csrf
            <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Relancer la livraison') }}</button>
        </form>
    @endif

    <h2 class="mt-8 font-semibold">{{ __('Paiements') }}</h2>
    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Référence') }}</th><th class="px-5 py-3">{{ __('Prestataire') }}</th><th class="px-5 py-3">{{ __('Moyen') }}</th><th class="px-5 py-3">{{ __('État') }}</th><th class="px-5 py-3">{{ __('Date') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($order->payments as $payment)
                <tr>
                    <td class="px-5 py-2 font-mono text-xs">{{ $payment->reference }}</td>
                    <td class="px-5 py-2">{{ $payment->gateway }}</td>
                    <td class="px-5 py-2">{{ Labels::method($payment->method) }} {{ $payment->operator }} {{ $payment->phone }}</td>
                    <td class="px-5 py-2">{{ Labels::status($payment->status) }} @if ($payment->failure)<span class="text-xs text-slate-500">({{ $payment->failure }})</span>@endif</td>
                    <td class="px-5 py-2 text-slate-500">{{ $payment->created_at->isoFormat('lll') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">{{ __('Aucun paiement.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
