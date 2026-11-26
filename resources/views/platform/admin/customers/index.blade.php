@extends('layouts.admin')
@section('title', __('Clients'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    @include('platform.admin._nav')
    <form class="flex gap-2">
        <input name="q" value="{{ $search }}" placeholder="{{ __('Nom ou adresse e-mail…') }}" aria-label="{{ __('Rechercher un client') }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50">{{ __('Rechercher') }}</button>
    </form>
    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Client') }}</th><th class="px-5 py-3">{{ __('Pays') }}</th><th class="px-5 py-3">{{ __('Crédits IA') }}</th><th class="px-5 py-3">{{ __('Commandes payées') }}</th><th class="px-5 py-3">{{ __('Clés actives') }}</th><th class="px-5 py-3">{{ __('État') }}</th><th class="px-5 py-3">{{ __('Inscription') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($customers as $customer)
                <tr>
                    <td class="px-5 py-2"><a href="{{ route('admin.platform.customers.show', $customer) }}" class="font-medium hover:text-brand-600">{{ $customer->name }}</a><p class="text-xs text-slate-500">{{ $customer->email }}</p></td>
                    <td class="px-5 py-2">{{ $customer->country ?? '—' }}</td>
                    <td class="px-5 py-2 whitespace-nowrap">{{ Money::micros($customer->ai_balance) }}</td>
                    <td class="px-5 py-2">{{ $customer->orders_count }}</td>
                    <td class="px-5 py-2">{{ $customer->licenses_count }}</td>
                    <td class="px-5 py-2">{{ Labels::status($customer->status) }}</td>
                    <td class="px-5 py-2 text-slate-500">{{ $customer->created_at->isoFormat('ll') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-6 text-center text-slate-500">{{ __('Aucun client.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
