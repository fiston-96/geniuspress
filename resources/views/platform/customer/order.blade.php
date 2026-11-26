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
            <span class="text-right font-semibold">{{ Money::format($order->total, $order->currency) }} <x-platform.local-price :amount="$order->total" :currency="$order->currency" class="block" /></span>
        </div>
        <p class="mt-2 text-sm">
            {{ __('État :') }}
            <span @class(['rounded-full px-2 py-0.5 text-xs font-medium', 'bg-emerald-50 text-emerald-700' => $order->isPaid(), 'bg-amber-50 text-amber-700' => ! $order->isPaid()])>{{ Labels::status($order->status) }}</span>
        </p>
    </div>

    @if ($order->status === 'pending')
        <div class="mt-6 max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
            @if ($payment && $payment->status === 'pending')
                <p class="text-sm text-slate-600">{{ __('Paiement en cours (:method).', ['method' => Labels::method($payment->method)]) }}
                    @if ($payment->currency !== $order->currency) {{ __('Montant : :amount.', ['amount' => Money::format($payment->amount, $payment->currency)]) }}@endif</p>
                @if ($payment->awaiting === 'otp')
                    {{-- Opérateur qui envoie un code (SMS) ou le fait obtenir par un code USSD. --}}
                    <form method="POST" action="{{ route('platform.customer.orders.authorize', $order) }}" class="mt-3 flex flex-wrap items-end gap-2">@csrf
                        <label class="text-sm font-medium text-slate-700">{{ __('Code de paiement') }}
                            <input name="otp" required inputmode="numeric" autocomplete="one-time-code" maxlength="12" class="mt-1 block w-40 rounded-lg border border-slate-300 px-3 py-2 text-sm tracking-widest">
                        </label>
                        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Valider le paiement') }}</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('platform.customer.orders.check', $order) }}" class="mt-3">@csrf
                        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">{{ __('J’ai payé : vérifier') }}</button>
                    </form>
                @endif
                <hr class="my-5 border-slate-100">
            @elseif ($payment && $payment->status === 'failed')
                <p class="mb-4 text-sm text-red-600">{{ __('Le dernier paiement a échoué. Vous pouvez réessayer.') }}</p>
            @endif
            @error('payment')<p class="mb-4 text-sm text-red-600">{{ $message }}</p>@enderror

            {{-- Solde d’affiliation suffisant dans la devise de la commande : paiement immédiat. --}}
            @if ($walletBalance >= $order->total)
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-brand-200 bg-brand-50 px-4 py-3">
                    <p class="text-sm text-brand-800">{{ __('Votre solde d’affiliation : :amount', ['amount' => Money::format($walletBalance, $order->currency)]) }}</p>
                    <form method="POST" action="{{ route('platform.customer.orders.pay', $order) }}">@csrf
                        <input type="hidden" name="method" value="wallet">
                        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Payer avec mon solde') }}</button>
                    </form>
                </div>
            @endif

            @if ($gateway)
                <h2 class="font-semibold">{{ __('Payer') }}</h2>
                <form method="POST" action="{{ route('platform.customer.orders.pay', $order) }}" class="mt-4 space-y-4">@csrf
                    @if (in_array('mobile_money', $gateway->methods(), true) && $countries)
                        {{-- Mobile Money par pays : montant dans la devise du pays, opérateurs ouverts dans ce pays. --}}
                        @php($customerCountry = auth('customer')->user()->country)
                        @php($selected = old('country', isset($countries[$customerCountry]) ? $customerCountry : array_key_first($countries)))
                        @php($selected = isset($countries[$selected]) ? $selected : array_key_first($countries))
                        @php($selectedCountry = $countries[$selected]['country'])
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="method" value="mobile_money" checked> {{ __('Mobile Money') }}</label>
                        <div class="grid gap-3 pl-6 md:grid-cols-3" data-gp-momo>
                            <select name="country" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="{{ __('Pays du compte Mobile Money') }}">
                                @foreach ($countries as $key => $c)<option value="{{ $key }}" data-country="{{ $c['country'] }}" data-amount="{{ $c['amount'] }}" data-converted="{{ $c['currency'] !== $order->currency ? 1 : 0 }}" data-note="{{ $c['note'] }}" @selected($selected === $key)>{{ $c['name'] }} ({{ $c['currency'] }})</option>@endforeach
                            </select>
                            <select name="operator" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="{{ __('Opérateur') }}">
                                @foreach (collect($countries)->unique('country') as $c)
                                    @foreach ($c['operators'] as $op => $o)<option value="{{ $op }}" data-country="{{ $c['country'] }}" @selected(old('operator') === $op && $selectedCountry === $c['country']) @disabled($selectedCountry !== $c['country']) @if ($selectedCountry !== $c['country']) hidden @endif>{{ $o['name'] }}</option>@endforeach
                                @endforeach
                            </select>
                            <input name="phone" type="tel" value="{{ old('phone', auth('customer')->user()->phone) }}" placeholder="{{ __('+ indicatif du pays') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="{{ __('Numéro Mobile Money') }}">
                        </div>
                        <p class="pl-6 text-sm text-slate-600">{{ __('Montant prélevé :') }} <strong data-gp-momo-amount>{{ $countries[$selected]['amount'] }}</strong>
                            <span class="text-xs text-slate-500" data-gp-momo-rate @if ($countries[$selected]['currency'] === $order->currency) hidden @endif>{{ __('(converti depuis :amount au taux du jour)', ['amount' => Money::format($order->total, $order->currency)]) }}</span></p>
                        <p class="ml-6 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900" data-gp-momo-note @unless ($countries[$selected]['note']) hidden @endunless>{{ $countries[$selected]['note'] }}</p>
                        <script>
                            (() => {
                                const box = document.querySelector('[data-gp-momo]');
                                const country = box.querySelector('select[name=country]');
                                const operator = box.querySelector('select[name=operator]');
                                const amount = document.querySelector('[data-gp-momo-amount]');
                                const note = document.querySelector('[data-gp-momo-note]');
                                country.addEventListener('change', () => {
                                    const chosen = country.selectedOptions[0];
                                    let first = null;
                                    operator.querySelectorAll('option').forEach((o) => {
                                        const show = o.dataset.country === chosen.dataset.country;
                                        o.hidden = !show; o.disabled = !show;
                                        if (show && !first) first = o;
                                    });
                                    // Même pays (francs ↔ dollars) : l'opérateur choisi est gardé.
                                    if (first && operator.selectedOptions[0]?.dataset.country !== chosen.dataset.country) operator.value = first.value;
                                    amount.textContent = country.selectedOptions[0].dataset.amount;
                                    document.querySelector('[data-gp-momo-rate]').hidden = chosen.dataset.converted !== '1';
                                    note.textContent = chosen.dataset.note || '';
                                    note.hidden = !chosen.dataset.note;
                                });
                            })();
                        </script>
                    @elseif (in_array('mobile_money', $gateway->methods(), true) && ! $gateway instanceof \App\Platform\Payments\MonerailsGateway)
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="method" value="mobile_money" checked> {{ __('Mobile Money') }}</label>
                        <div class="grid gap-3 pl-6 md:grid-cols-2">
                            <select name="operator" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="{{ __('Opérateur') }}">
                                @foreach ($gateway->operators() as $code => $name)<option value="{{ $code }}">{{ $name }}</option>@endforeach
                            </select>
                            <input name="phone" type="tel" value="{{ old('phone', auth('customer')->user()->phone) }}" placeholder="{{ __('+ indicatif du pays') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" aria-label="{{ __('Numéro Mobile Money') }}">
                        </div>
                    @endif
                    @if (in_array('card', $gateway->methods(), true))
                        <label class="flex items-center gap-2 text-sm"><input type="radio" name="method" value="card" @checked(! in_array('mobile_money', $gateway->methods(), true))> {{ __('Carte bancaire') }}</label>
                    @endif
                    @if ($gateway instanceof \App\Platform\Payments\MonerailsGateway && ! $countries)
                        <p class="text-sm text-slate-600">{{ __('Le paiement en ligne de cette commande n’est pas encore disponible. Contactez-nous pour la régler.') }}</p>
                    @else
                        <button class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-500">{{ $countries ? __('Payer') : __('Payer :amount', ['amount' => Money::format($order->total, $order->currency)]) }}</button>
                    @endif
                    @if ($gateway->name() === 'sandbox')<p class="text-xs text-amber-700">{{ __('Mode test : aucun argent n’est prélevé.') }}</p>@endif
                </form>
            @else
                <p class="text-sm text-slate-600">{{ __('Aucun moyen de paiement n’est configuré. Réessayez plus tard.') }}</p>
            @endif
        </div>
    @endif
@endsection
