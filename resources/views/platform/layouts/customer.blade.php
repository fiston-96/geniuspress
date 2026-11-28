<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ __('Espace client GeniusPress') }}</title>
    @include('partials.brand-head')
    @vite(['resources/css/app.css'])
    @stack('head')
</head>
<body class="min-h-screen bg-cream text-slate-900 antialiased">
@php
    $me = auth('customer')->user();
    $hostingOn = (bool) config('hosting.enabled');
    $siteCount = $me && $hostingOn && config('hosting.unlimited') ? app(\App\Hosting\Trials::class)->sitesFor($me)->count() : null;
    $domainCount = $me ? $me->domains()->count() : 0;
    $unread = $me ? $me->unreadNotifications()->count() : 0;
    $initials = $me ? collect(preg_split('/\s+/', trim($me->name)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') : '?';

    // Menu groupé : [route, libellé, icône, pastille ?, motif de route actif ?]
    $groups = [
        ['', array_values(array_filter([
            ['platform.customer.dashboard', __('Mon espace'), 'layout-dashboard'],
            $hostingOn ? ['hosting.trial', __('Mes sites'), 'globe', $siteCount, 'hosting.*'] : null,
            ['platform.customer.domains', __('Domaines'), 'at-sign', $domainCount ?: null],
        ]))],
        [__('Boutique'), [
            ['platform.customer.plans', __('Offres et hébergement'), 'server'],
            ['platform.customer.templates', __('Modèles premium'), 'layout-template'],
            ['platform.customer.credits', __('Crédits IA'), 'sparkles'],
        ]],
        [__('Facturation'), [
            ['platform.customer.orders', __('Mes commandes'), 'receipt', null, 'platform.customer.orders*'],
            ['platform.customer.referrals', __('Affiliation'), 'gift'],
        ]],
        [__('Compte'), [
            ['platform.customer.account', __('Mon compte'), 'user'],
            ['platform.customer.notifications.index', __('Notifications'), 'bell', $unread ?: null, 'platform.customer.notifications.*'],
        ]],
    ];
@endphp

<div id="gp-nav-backdrop" class="fixed inset-0 z-40 hidden bg-slate-900/40 backdrop-blur-sm lg:hidden" data-nav-close></div>
<aside id="gp-nav" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r border-line bg-white transition-transform duration-200 lg:translate-x-0" aria-label="{{ __('Menu de l’espace client') }}">
    <div class="flex h-16 shrink-0 items-center justify-between px-5">
        <a href="{{ route('platform.customer.dashboard') }}" class="flex items-center gap-2"><x-logo :size="28" /></a>
        <button type="button" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 lg:hidden" data-nav-close title="{{ __('Fermer le menu') }}" aria-label="{{ __('Fermer le menu') }}"><x-icon name="x" /></button>
    </div>
    @if ($hostingOn)
        <div class="px-3 pb-3">
            <a href="{{ route('hosting.trial') }}#nouveau" class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                <x-icon name="plus" class="h-4 w-4" stroke="2.4" />{{ __('Créer un site') }}
            </a>
        </div>
    @endif
    <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-4 [scrollbar-width:thin]">
        @foreach ($groups as [$title, $items])
            <div>
                @if ($title)<p class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $title }}</p>@endif
                <ul class="space-y-0.5">
                    @foreach ($items as $item)
                        @php
                            [$route, $label, $icon] = $item;
                            $badge = $item[3] ?? null;
                            $active = request()->routeIs($item[4] ?? $route);
                        @endphp
                        <li>
                            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif @class(['group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition',
                                'bg-cream text-night' => $active, 'text-slate-600 hover:bg-cream hover:text-night' => ! $active])>
                                <x-icon :name="$icon" :class="'h-[18px] w-[18px] '.($active ? 'text-brand-600' : 'text-slate-400 group-hover:text-slate-600')" />
                                <span class="flex-1">{{ $label }}</span>
                                @if ($badge)<span @class(['rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none', 'bg-brand-600 text-white' => $route === 'platform.customer.notifications.index', 'bg-slate-100 text-slate-600' => $route !== 'platform.customer.notifications.index'])>{{ $badge > 99 ? '99+' : $badge }}</span>@endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
    <div class="shrink-0 border-t border-line p-3">
        <a href="{{ url('/documentation') }}" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"><x-icon name="circle-help" class="h-[18px] w-[18px] text-slate-400" />{{ __('Aide et documentation') }}</a>
    </div>
</aside>

<div class="flex min-h-screen flex-col lg:pl-64">
    <header class="sticky top-0 z-30 border-b border-line bg-cream/90 backdrop-blur">
        <div class="flex h-16 items-center gap-2 px-4 md:px-8">
            <button type="button" class="grid h-9 w-9 place-items-center rounded-lg text-slate-600 hover:bg-white lg:hidden" data-nav-open title="{{ __('Ouvrir le menu') }}" aria-label="{{ __('Ouvrir le menu') }}" aria-controls="gp-nav"><x-icon name="menu" /></button>
            <p class="truncate text-sm font-semibold text-slate-700">@yield('title')</p>
            <div class="ml-auto flex items-center gap-1">
                @if ($me)
                    @if ($me->ai_balance !== null)
                        <a href="{{ route('platform.customer.credits') }}" class="hidden items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-night ring-1 ring-line hover:ring-brand-300 sm:flex" title="{{ __('Crédits IA') }}">
                            <x-icon name="sparkles" class="h-3.5 w-3.5 text-brand-600" />{{ \App\Platform\Money::micros($me->ai_balance) }}
                        </a>
                    @endif
                    <x-notification-bell :href="route('platform.customer.notifications.index')" :count="$unread" class="hover:bg-white" />
                    <details class="relative" data-dropdown>
                        <summary class="ml-1 flex cursor-pointer list-none items-center gap-2 rounded-full bg-white py-1 pl-1 pr-3 ring-1 ring-line transition hover:ring-brand-300 [&::-webkit-details-marker]:hidden" aria-label="{{ __('Mon compte') }}">
                            <span class="grid h-7 w-7 place-items-center rounded-full bg-night text-[11px] font-bold text-gold-light">{{ $initials ?: '?' }}</span>
                            <span class="hidden max-w-[10rem] truncate text-sm font-medium text-slate-700 sm:block">{{ $me->name }}</span>
                            <x-icon name="chevron-down" class="h-3.5 w-3.5 text-slate-400" />
                        </summary>
                        <div class="absolute right-0 top-11 z-40 w-64 rounded-xl border border-line bg-white p-1.5 shadow-lg shadow-night/5">
                            <div class="px-3 py-2">
                                <p class="truncate text-sm font-semibold">{{ $me->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $me->email }}</p>
                            </div>
                            <a href="{{ route('platform.customer.account') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="user" class="h-4 w-4 text-slate-400" />{{ __('Mon compte') }}</a>
                            <a href="{{ route('platform.customer.orders') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="receipt" class="h-4 w-4 text-slate-400" />{{ __('Mes commandes') }}</a>
                            <div class="my-1 border-t border-slate-100"></div>
                            <form method="POST" action="{{ route('platform.logout') }}">@csrf
                                <button class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-red-50 hover:text-red-700"><x-icon name="log-out" class="h-4 w-4" />{{ __('Se déconnecter') }}</button>
                            </form>
                        </div>
                    </details>
                @endif
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6 md:px-8 md:py-8">
        @if (session('warnings'))
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <ul class="list-disc pl-5">@foreach (session('warnings') as $warning)<li>{{ $warning }}</li>@endforeach</ul>
            </div>
        @endif
        @if (session('status'))
            <div class="mb-6 flex items-start gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm text-emerald-800"><x-icon name="circle-check" class="mt-px h-4 w-4" />{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script>
    (function () {
        var nav = document.getElementById('gp-nav'), backdrop = document.getElementById('gp-nav-backdrop');
        function toggle(open) { nav.classList.toggle('-translate-x-full', !open); backdrop.classList.toggle('hidden', !open); document.documentElement.classList.toggle('overflow-hidden', open); }
        document.querySelectorAll('[data-nav-open]').forEach(function (b) { b.addEventListener('click', function () { toggle(true); }); });
        document.querySelectorAll('[data-nav-close]').forEach(function (b) { b.addEventListener('click', function () { toggle(false); }); });
        document.addEventListener('click', function (e) { document.querySelectorAll('details[data-dropdown][open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); }); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { toggle(false); document.querySelectorAll('details[data-dropdown][open]').forEach(function (d) { d.removeAttribute('open'); }); } });
    })();
</script>
</body>
</html>
