@extends('platform.layouts.console')
@section('title', __('Journal IA'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp

    <h2 class="font-semibold">{{ __('Ce mois-ci, par modèle') }}</h2>
    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Modèle d’IA') }}</th><th class="px-5 py-3 text-right">{{ __('Appels') }}</th><th class="px-5 py-3 text-right">{{ __('Jetons (entrée / sortie)') }}</th><th class="px-5 py-3 text-right">{{ __('Coût réel') }}</th><th class="px-5 py-3 text-right">{{ __('Facturé') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($byModel as $row)
                <tr>
                    <td class="px-5 py-2">{{ $row->model }}</td>
                    <td class="px-5 py-2 text-right">{{ $row->calls }}</td>
                    <td class="px-5 py-2 text-right">{{ number_format((int) $row->input, 0, ',', ' ') }} / {{ number_format((int) $row->output, 0, ',', ' ') }}</td>
                    <td class="px-5 py-2 text-right">{{ Money::micros((int) $row->cost) }}</td>
                    <td class="px-5 py-2 text-right">{{ Money::micros((int) $row->billed) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">{{ __('Aucun appel ce mois-ci.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mt-8 font-semibold">{{ __('Appels') }}</h2>
    <p class="mt-1 text-sm text-slate-500">{{ __('Le contenu des demandes n’est jamais enregistré.') }}</p>
    <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Date') }}</th><th class="px-5 py-3">{{ __('Client') }}</th><th class="px-5 py-3">{{ __('Site') }}</th><th class="px-5 py-3">{{ __('Modèle d’IA') }}</th><th class="px-5 py-3">{{ __('État') }}</th><th class="px-5 py-3 text-right">{{ __('Jetons') }}</th><th class="px-5 py-3 text-right">{{ __('Coût / facturé') }}</th><th class="px-5 py-3">{{ __('IP') }}</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($usages as $usage)
                <tr>
                    <td class="px-5 py-2 whitespace-nowrap text-slate-500">{{ $usage->created_at->isoFormat('lll') }}</td>
                    <td class="px-5 py-2"><a href="{{ route('console.customers.show', $usage->customer) }}" class="hover:text-brand-600">{{ $usage->customer->name }}</a></td>
                    <td class="px-5 py-2">{{ $usage->license?->name ?? '—' }}</td>
                    <td class="px-5 py-2">{{ $usage->model }}<p class="text-xs text-slate-500">{{ $usage->action }}</p></td>
                    <td class="px-5 py-2">{{ Labels::status($usage->status) }} @if ($usage->error)<p class="text-xs text-slate-500">{{ $usage->error }}</p>@endif</td>
                    <td class="px-5 py-2 text-right whitespace-nowrap">{{ $usage->input_tokens }} / {{ $usage->output_tokens }}</td>
                    <td class="px-5 py-2 text-right whitespace-nowrap">{{ Money::micros($usage->cost) }} / {{ Money::micros($usage->billed) }}</td>
                    <td class="px-5 py-2 text-xs text-slate-500">{{ $usage->ip }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-6 text-center text-slate-500">{{ __('Aucun appel.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $usages->links() }}</div>
@endsection
