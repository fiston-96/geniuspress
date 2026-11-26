{{--
    Console d'exploitation de GeniusPress (/console) : module séparé du CMS (pas de pages, de
    builder ni de thème ici) et de l'espace client. Barre latérale nuit indigo pour ne jamais
    la confondre avec l'administration d'un site.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ __('Console GeniusPress') }}</title>
    @include('partials.brand-head')
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
@php
    $operator = auth('operator')->user();
    $nav = [
        ['console.dashboard', __('Vue d’ensemble'), 'M3 12l9-9 9 9M5 10v10h14V10'],
        ['console.customers.index', __('Clients'), 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0'],
        ['console.referrals.index', __('Affiliation'), 'M8 7a3 3 0 106 0 3 3 0 00-6 0zM3 20a6 6 0 0111 0M17 11l2 2 4-4'],
        ['console.orders.index', __('Commandes'), 'M3 7h18v10H3zM3 11h18M7 15h3'],
        ['console.plans.index', __('Offres'), 'M4 4h16v6H4zM4 14h7v6H4zM15 14h5v6h-5z'],
        ['console.pricing.index', __('Tarifs'), 'M12 3v18M17 7H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6'],
        ['console.payments.index', __('Paiements'), 'M3 6h15a3 3 0 013 3v9H3zM3 6l13-3v3M16 13h2'],
        ['console.suppliers.index', __('Fournisseurs'), 'M3 7l9-4 9 4-9 4-9-4zM3 12l9 4 9-4M3 17l9 4 9-4'],
        ['console.domains.index', __('Domaines'), 'M12 21a9 9 0 100-18 9 9 0 000 18zM3 12h18M12 3a14 14 0 010 18M12 3a14 14 0 000 18'],
        ['console.usage.index', __('Journal IA'), 'M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8z'],
    ];
    $unread = $operator?->unreadNotifications()->count() ?? 0;
@endphp
<div class="flex min-h-screen">
    <aside class="hidden w-60 shrink-0 flex-col bg-[#1e1b4b] text-indigo-100 md:flex">
        <div class="flex items-center justify-between gap-2 px-5 py-5">
            <a href="{{ route('console.dashboard') }}" class="flex flex-col">
                <x-logo :size="28" :dark="true" />
                <span class="mt-1 pl-[38px] text-[11px] font-semibold uppercase tracking-[0.16em] text-[#e3c48f]">{{ __('Console') }}</span>
            </a>
            <x-notification-bell :href="route('console.notifications.index')" :count="$unread" class="-mr-2 text-indigo-200 hover:bg-white/10 hover:text-white" />
        </div>
        <nav class="flex-1 space-y-0.5 px-3">
            @foreach ($nav as [$route, $label, $icon])
                @php $active = request()->routeIs(str_replace('.index', '.*', $route)); @endphp
                <a href="{{ route($route) }}" @class(['flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition', 'bg-white/12 text-white' => $active, 'text-indigo-200 hover:bg-white/8 hover:text-white' => ! $active])>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-4">
            <a href="{{ url('/') }}" target="_blank" class="mb-3 flex items-center gap-2 text-sm text-indigo-200 hover:text-white">{{ __('Voir le site ↗') }}</a>
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-white">{{ $operator?->name }}</p>
                    <p class="truncate text-xs text-indigo-300">{{ __('Exploitant') }}</p>
                </div>
                <form method="POST" action="{{ route('console.logout') }}">@csrf
                    <button class="rounded-md p-1.5 text-indigo-200 hover:bg-white/10 hover:text-white" title="{{ __('Se déconnecter') }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 12H3m12 0l-4-4m4 4l-4 4M21 3v18"/></svg>
                        <span class="sr-only">{{ __('Se déconnecter') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between gap-4 bg-[#1e1b4b] px-4 py-3 text-indigo-100 md:hidden">
            <div class="flex items-center gap-2">
                <x-logo :size="24" :dark="true" :wordmark="false" />
                <span class="text-xs font-semibold uppercase tracking-[0.16em] text-[#e3c48f]">{{ __('Console') }}</span>
                <x-notification-bell :href="route('console.notifications.index')" :count="$unread" class="text-indigo-200" />
            </div>
            <nav class="flex flex-wrap justify-end gap-3 text-sm">
                @foreach ($nav as [$route, $label])<a href="{{ route($route) }}" class="text-indigo-200 hover:text-white">{{ $label }}</a>@endforeach
            </nav>
        </header>
        <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 md:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            @if (session('warnings'))
                <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <ul class="list-disc pl-5">@foreach (session('warnings') as $warning)<li>{{ $warning }}</li>@endforeach</ul>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
