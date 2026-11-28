@extends('platform.layouts.customer')
@section('title', __('Mes commandes'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-night">{{ __('Mes commandes') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Offres, domaines, modèles et crédits IA : toutes vos commandes et leur paiement.') }}</p>
        </div>
        <a href="{{ route('platform.customer.plans') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700"><x-icon name="shopping-bag" class="h-4 w-4" />{{ __('Voir les offres') }}</a>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-cream text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3 font-semibold">{{ __('Commande') }}</th><th class="hidden px-5 py-3 font-semibold sm:table-cell">{{ __('Date') }}</th><th class="px-5 py-3 font-semibold">{{ __('Statut') }}</th><th class="px-5 py-3 text-right font-semibold">{{ __('Montant') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($orders as $order)
                    <tr class="hover:bg-cream/70">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('platform.customer.orders.show', $order) }}" class="font-medium text-slate-900 hover:text-brand-700">{{ $order->items->first()?->description ?? $order->number }}</a>
                            <p class="font-mono text-xs text-slate-400">{{ $order->number }}@if ($order->items->count() > 1) · {{ trans_choice('{2} et 1 autre article|[3,*] et :count autres articles', $order->items->count(), ['count' => $order->items->count() - 1]) }}@endif</p>
                        </td>
                        <td class="hidden px-5 py-3.5 text-slate-500 sm:table-cell">{{ $order->created_at->isoFormat('LL') }}</td>
                        <td class="px-5 py-3.5">
                            <span @class(['rounded-full px-2 py-0.5 text-xs font-medium',
                                'bg-emerald-50 text-emerald-700' => in_array($order->status, ['paid', 'completed', 'fulfilled'], true),
                                'bg-amber-50 text-amber-700' => in_array($order->status, ['pending', 'awaiting_payment'], true),
                                'bg-slate-100 text-slate-600' => ! in_array($order->status, ['paid', 'completed', 'fulfilled', 'pending', 'awaiting_payment'], true)])>{{ Labels::status($order->status) }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-semibold tabular-nums">{{ Money::format($order->total, $order->currency) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-14 text-center">
                        <x-icon name="receipt" class="mx-auto h-8 w-8 text-slate-300" />
                        <p class="mt-3 text-sm text-slate-500">{{ __('Aucune commande.') }}</p>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
