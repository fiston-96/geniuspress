<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · GeniusPress</title>
    @include('partials.brand-head')
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center justify-center gap-2">
                <x-logo :size="40" />
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>
