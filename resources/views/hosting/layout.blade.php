<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · GeniusPress</title>
    @include('partials.brand-head')
    @vite(['resources/css/app.css'])
    @stack('head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="mx-auto w-full max-w-2xl px-4 py-12">
        <div class="mb-8 flex items-center justify-center gap-2">
            <x-logo :size="40" />
        </div>
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if (session('warnings'))
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <ul class="list-disc pl-5">@foreach (session('warnings') as $warning)<li>{{ $warning }}</li>@endforeach</ul>
            </div>
        @endif
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            @yield('content')
        </div>
    </main>
</body>
</html>
