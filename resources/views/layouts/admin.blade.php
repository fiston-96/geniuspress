<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ \App\Models\Setting::read('site_name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
@php
    $nav = [
        ['admin.dashboard', 'Tableau de bord', 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['admin.pages.index', 'Pages', 'M7 3h7l5 5v13H7zM14 3v5h5'],
        ['admin.media.index', 'Médias', 'M4 5h16v14H4zM4 15l4-4 5 5 3-3 4 4'],
    ];
    if (auth()->user()->isAdmin()) {
        $nav[] = ['admin.plugins.index', 'Plugins', 'M9 3v4M15 3v4M6 7h12v4a6 6 0 01-12 0zM12 17v4'];
        $nav[] = ['admin.settings.edit', 'Réglages', 'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.7 1.7 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.8-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1.1-1.5 1.7 1.7 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.8 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1.1 1.7 1.7 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.8.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.8V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z'];
    }
    // Les plugins peuvent ajouter des entrées : [nom de route, libellé, tracé SVG].
    $nav = \App\Plugins\Hooks::filter('admin.menu', $nav);
@endphp
<div class="flex min-h-screen">
    <aside class="hidden w-60 shrink-0 flex-col border-r border-slate-200 bg-white md:flex">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-5 py-5">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-600 text-sm font-bold text-white">G</span>
            <span class="font-semibold tracking-tight">GeniusPress</span>
        </a>
        <nav class="flex-1 space-y-0.5 px-3">
            @foreach ($nav as [$route, $label, $icon])
                @php $active = request()->routeIs(str_replace('.index', '.*', $route)); @endphp
                <a href="{{ route($route) }}" @class(['flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition', 'bg-indigo-50 text-indigo-700' => $active, 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' => ! $active])>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="border-t border-slate-200 p-4">
            <a href="{{ url('/') }}" target="_blank" class="mb-3 flex items-center gap-2 text-sm text-slate-600 hover:text-indigo-600">Voir le site ↗</a>
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500">{{ auth()->user()->isAdmin() ? 'Administrateur' : 'Éditeur' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="rounded-md p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-900" title="Se déconnecter">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M15 12H3m12 0l-4-4m4 4l-4 4M21 3v18"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3 md:hidden">
            <span class="font-semibold">GeniusPress</span>
            <nav class="flex gap-3 text-sm">
                @foreach ($nav as [$route, $label])<a href="{{ route($route) }}" class="text-slate-600">{{ $label }}</a>@endforeach
            </nav>
        </header>
        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 md:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
