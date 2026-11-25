@extends('platform.layouts.customer')
@section('title', __('Crédits IA'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('Crédits IA') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Chaque demande à l’assistant est débitée selon le modèle utilisé et le nombre de jetons lus et écrits.') }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-slate-500">{{ __('Solde') }}</p>
            <p @class(['text-2xl font-semibold', 'text-red-600' => $customer->ai_balance <= 0])>{{ Money::micros($customer->ai_balance) }}</p>
        </div>
    </div>

    <h2 class="mt-8 font-semibold">{{ __('Mouvements') }}</h2>
    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Date') }}</th><th class="px-5 py-3">{{ __('Type') }}</th><th class="px-5 py-3">{{ __('Libellé') }}</th><th class="px-5 py-3 text-right">{{ __('Montant') }}</th><th class="px-5 py-3 text-right">{{ __('Solde') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($transactions as $tx)
                <tr>
                    <td class="px-5 py-2 text-slate-500">{{ $tx->created_at->isoFormat('lll') }}</td>
                    <td class="px-5 py-2">{{ Labels::kind($tx->kind) }}</td>
                    <td class="px-5 py-2">{{ $tx->description }}</td>
                    <td @class(['px-5 py-2 text-right whitespace-nowrap', 'text-emerald-700' => $tx->amount > 0])>{{ $tx->amount > 0 ? '+' : '' }}{{ Money::micros($tx->amount) }}</td>
                    <td class="px-5 py-2 text-right whitespace-nowrap text-slate-500">{{ Money::micros($tx->balance_after) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">{{ __('Aucun mouvement.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>

    <h2 class="mt-8 font-semibold">{{ __('Derniers appels à l’IA') }}</h2>
    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Date') }}</th><th class="px-5 py-3">{{ __('Site') }}</th><th class="px-5 py-3">{{ __('Modèle d’IA') }}</th><th class="px-5 py-3 text-right">{{ __('Jetons (entrée / sortie)') }}</th><th class="px-5 py-3 text-right">{{ __('Débité') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($usages as $usage)
                <tr>
                    <td class="px-5 py-2 text-slate-500">{{ $usage->created_at->isoFormat('lll') }}</td>
                    <td class="px-5 py-2">{{ $usage->license?->name ?? '—' }}</td>
                    <td class="px-5 py-2">{{ $usage->model }} @if ($usage->status !== 'ok')<span class="text-xs text-slate-500">({{ Labels::status($usage->status) }})</span>@endif</td>
                    <td class="px-5 py-2 text-right text-slate-500">{{ number_format($usage->input_tokens, 0, ',', ' ') }} / {{ number_format($usage->output_tokens, 0, ',', ' ') }}</td>
                    <td class="px-5 py-2 text-right whitespace-nowrap">{{ Money::micros($usage->billed) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">{{ __('Aucun appel pour l’instant.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
