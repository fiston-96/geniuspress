@extends('install.layout', ['step' => 1])
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Bienvenue dans GeniusPress') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Vérifions d’abord que votre serveur peut faire fonctionner GeniusPress.') }}</p>

    <ul class="mt-6 divide-y divide-slate-100 rounded-lg border border-slate-200">
        @foreach ($checks as $check)
            <li class="flex items-start justify-between gap-4 px-4 py-2.5 text-sm">
                <div class="min-w-0">
                    <p class="font-medium">{{ $check['label'] }}</p>
                    @if ($check['detail'])<p class="truncate text-xs text-slate-500" title="{{ $check['detail'] }}">{{ $check['detail'] }}</p>@endif
                </div>
                @if ($check['ok'])
                    <span class="shrink-0 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ __('OK') }}</span>
                @elseif ($check['required'])
                    <span class="shrink-0 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700">{{ __('Manquant') }}</span>
                @else
                    <span class="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ __('Conseillé') }}</span>
                @endif
            </li>
        @endforeach
    </ul>

    @if ($passes)
        <a href="{{ $next }}" class="mt-6 block w-full rounded-lg bg-brand-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ __('Continuer') }}</a>
    @else
        <p class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ __('Corrigez les éléments marqués « Manquant », puis rechargez cette page. Votre hébergeur peut activer les extensions PHP et les droits d’écriture.') }}</p>
    @endif
@endsection
