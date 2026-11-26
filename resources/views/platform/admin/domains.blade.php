@extends('platform.layouts.console')
@section('title', __('Domaines'))
@section('content')
@php use App\Platform\Labels; @endphp
    <p class="text-sm text-slate-500">{{ __('Registraire configuré : :name', ['name' => config('geniuspress.platform.domain_registrar')]) }}</p>
    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Domaine') }}</th><th class="px-5 py-3">{{ __('Client') }}</th><th class="px-5 py-3">{{ __('État') }}</th><th class="px-5 py-3">{{ __('Registraire') }}</th><th class="px-5 py-3">{{ __('Expiration') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($domains as $domain)
                <tr>
                    <td class="px-5 py-2 font-medium">{{ $domain->name }}</td>
                    <td class="px-5 py-2"><a href="{{ route('console.customers.show', $domain->customer) }}" class="hover:text-brand-600">{{ $domain->customer->name }}</a></td>
                    <td class="px-5 py-2">{{ Labels::status($domain->status) }}</td>
                    <td class="px-5 py-2">{{ $domain->registrar }} <span class="font-mono text-xs text-slate-500">{{ $domain->registrar_reference }}</span></td>
                    <td @class(['px-5 py-2', 'text-red-600' => $domain->expires_at?->lt(now()->addDays(30))])>{{ $domain->expires_at?->isoFormat('LL') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">{{ __('Aucun domaine vendu.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $domains->links() }}</div>
@endsection
