<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('Installation') }} · GeniusPress</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
@php
    $steps = [1 => __('Serveur'), 2 => __('Base de données'), 3 => __('Votre site')];
@endphp
<main class="mx-auto w-full max-w-2xl px-4 py-10">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-indigo-600 text-lg font-bold text-white">G</span>
            <span class="text-xl font-semibold tracking-tight">GeniusPress</span>
        </div>
        <nav class="flex gap-2 text-xs">
            @foreach (config('geniuspress.ui_locales') as $code => $name)
                <a href="{{ route('install.language', $code) }}" @class(['rounded-md px-2 py-1', 'bg-slate-900 text-white' => app()->getLocale() === $code, 'text-slate-600 hover:bg-slate-200' => app()->getLocale() !== $code])>{{ $name }}</a>
            @endforeach
        </nav>
    </div>

    <ol class="mb-6 grid grid-cols-3 gap-2 text-xs font-medium">
        @foreach ($steps as $n => $label)
            <li @class([
                'rounded-lg border px-3 py-2',
                'border-indigo-600 bg-indigo-600 text-white' => $n === $step,
                'border-emerald-200 bg-emerald-50 text-emerald-800' => $n < $step,
                'border-slate-200 bg-white text-slate-500' => $n > $step,
            ])>
                <span class="opacity-75">{{ $n }}.</span> {{ $label }}@if ($n < $step) ✓@endif
            </li>
        @endforeach
    </ol>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        @yield('content')
    </div>
</main>
</body>
</html>
