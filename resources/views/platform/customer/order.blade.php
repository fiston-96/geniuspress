@extends('platform.layouts.customer')
@section('title', __('Commande :number', ['number' => $order->number]))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <a href="{{ route('platform.customer.dashboard') }}" class="text-sm text-slate-500 hover:text-brand-600">← {{ __('Mon espace') }}</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ __('Commande :number', ['number' => $order->number]) }}</h1>

    <div class="mt-6 max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @foreach ($order->items as $item)
            <div class="flex justify-between gap-4 text-sm">
                <span>{{ $item->description }}</span>
                <span class="whitespace-nowrap font-medium">{{ Money::format($item->amount, $order->currency) }}</span>
            </div>
            @if ($order->isPaid() && ! $item->fulfilled_at)
                <p class="mt-1 text-xs text-amber-700">{{ __('Livraison en cours : nous finalisons cet achat et vous tiendrons informé.') }}</p>
            @endif
        @endforeach
        <div class="mt-4 flex justify-between border-t border-slate-100 pt-4">
            <span class="font-semibold">{{ __('Total') }}</span>
            <span class="font-semibold">{{ Money::format($order->total, $order->currency) }}</span>
        </div>
        <p class="mt-2 text-sm">
            {{ __('État :') }}
            <span @class(['rounded-full px-2 py-0.5 text-xs font-medium', 'bg-emerald-50 text-emerald-700' => $order->isPaid(), 'bg-amber-50 text-amber-700' => ! $order->isPaid()])>{{ Labels::status($order->status) }}</span>
        </p>
    </div>

    @if ($order->status === 'pending')
        <div class="mt-6 max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
            @if ($payment && $payment->status === 'pending')
                <p class="text-sm text-slate-600">{{ __('Paiement en cours (:method).', ['method' => Labels::method($payment->method)]) }}</p>
                <form method="POST" action="{{ route('platform.customer.orders.check', $order) }}" class="mt-3">@csrf
                    <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">{{ __('J’ai payé : vérifier') }}</button>
                </form>
                <hr class="my-5 border-slate-100">
            @elseif ($payment && $payment->status === 'failed')
                <p class="mb-4 text-sm text-red-600">{{ __('Le dernier paiement a échoué. Vous pouvez réessayer.') }}</p>
            @endif

            @if ($gateway)
                <h2 class="font-semibold">{{ __('Payer') }}</h2>
                <form method="POST" action="{{ route('platform.customer.orders.pay', $order) }}" class="mt-4 space-y-4">@csrf
                    @if (in_array('mobile_money', $gateway->methods(), true))
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="method" value="mobile_money" checked> {{ __('Mobile Money') }}</label>
                        <div class="grid gap-3 pl-6 md:grid-cols-2">
                            <select name="operator" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="{{ __('Opérateur') }}">
                                @foreach ($gateway->operators() as $code => $name)<option value="{{ $code }}">{{ $name }}</option>@endforeach
                            </select>
                            <input name="phone" type="tel" value="{{ old('phone', auth('customer')->user()->phone) }}" placeholder="+243 …" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="{{ __('Numéro Mobile Money') }}">
                        </div>
                    @endif
                    @if (in_array('card', $gateway->methods(), true))
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="method" value="card" @checked(! in_array('mobile_money', $gateway->methods(), true))> {{ __('Carte bancaire') }}</label>
                    @endif
                    <button class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Payer :amount', ['amount' => Money::format($order->total, $order->currency)]) }}</button>
                    @if ($gateway->name() === 'sandbox')<p class="text-xs text-amber-700">{{ __('Mode test : aucun argent n’est prélevé.') }}</p>@endif
                </form>
            @else
                <p class="text-sm text-slate-600">{{ __('Aucun moyen de paiement n’est configuré. Réessayez plus tard.') }}</p>
            @endif
        </div>
    @endif
@endsection
