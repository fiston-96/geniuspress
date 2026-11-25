@extends('layouts.auth')
@section('title', __('Paiement de test'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-medium text-amber-800">{{ __('Mode test : aucun argent n’est prélevé.') }}</p>
    <h1 class="mt-4 text-xl font-semibold">{{ __('Paiement de test') }}</h1>
    <p class="mt-2 text-sm text-slate-600">{{ __('Commande :number', ['number' => $payment->order->number]) }} — <strong>{{ Money::format($payment->amount, $payment->currency) }}</strong></p>
    <p class="mt-1 text-sm text-slate-600">{{ Labels::method($payment->method) }}@if ($payment->phone) · {{ $payment->phone }}@endif</p>
    @if (session('status'))<p class="mt-3 text-sm text-slate-600">{{ session('status') }}</p>@endif
    @if ($payment->status === 'pending')
        <form method="POST" action="{{ route('platform.sandbox.complete', $payment) }}" class="mt-6 flex gap-3">@csrf
            <button name="result" value="success" class="flex-1 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500">{{ __('Simuler un paiement réussi') }}</button>
            <button name="result" value="failure" class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold hover:bg-slate-50">{{ __('Simuler un échec') }}</button>
        </form>
    @else
        <p class="mt-6 text-sm">{{ Labels::status($payment->status) }} — <a href="{{ route('platform.customer.orders.show', $payment->order) }}" class="text-indigo-600 hover:underline">{{ __('Retour à la commande') }}</a></p>
    @endif
@endsection
