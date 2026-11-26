@php use App\Platform\Labels; use App\Platform\Money; @endphp
<div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
            <tr><th class="px-5 py-3">{{ __('Commande') }}</th><th class="px-5 py-3">{{ __('Client') }}</th><th class="px-5 py-3">{{ __('Montant') }}</th><th class="px-5 py-3">{{ __('État') }}</th><th class="px-5 py-3">{{ __('Date') }}</th></tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($orders as $order)
            <tr>
                <td class="px-5 py-2"><a href="{{ route('console.orders.show', $order) }}" class="font-medium hover:text-brand-600">{{ $order->number }}</a></td>
                <td class="px-5 py-2"><a href="{{ route('console.customers.show', $order->customer) }}" class="hover:text-brand-600">{{ $order->customer->name }}</a></td>
                <td class="px-5 py-2 whitespace-nowrap">{{ Money::format($order->total, $order->currency) }}</td>
                <td class="px-5 py-2">{{ Labels::status($order->status) }}</td>
                <td class="px-5 py-2 text-slate-500">{{ $order->created_at->isoFormat('lll') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">{{ __('Aucune commande.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
