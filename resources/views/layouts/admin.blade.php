<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ \App\Models\Setting::read('site_name') }}</title>
    @include('partials.brand-head')
    @vite(['resources/css/app.css'])
    @stack('head')
</head>
<body class="min-h-screen bg-cream text-slate-900 antialiased">
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin();
    $unreadNotices = $user->unreadNotifications()->count();
    $unreadMessages = $isAdmin ? \App\Models\FormSubmission::query()->whereNull('read_at')->count() : 0;
    $siteName = \App\Models\Setting::read('site_name');

    // Menu groupé : [nom de route, libellé, icône, motif de route actif ?, pastille ?]
    $groups = [
        ['' , [['admin.dashboard', __('Tableau de bord'), 'layout-dashboard']]],
        [__('Contenu'), array_values(array_filter([
            ['admin.pages.index', __('Pages'), 'files'],
            ['admin.posts.index', __('Articles'), 'newspaper', 'admin.posts.*|admin.terms.*'],
            ['admin.media.index', __('Médias'), 'image'],
            $isAdmin ? ['admin.messages.index', __('Messages'), 'inbox', null, $unreadMessages] : null,
        ]))],
    ];
    if ($isAdmin) {
        $groups[] = [__('Design'), [
            ['admin.templates.index', __('Modèles'), 'layout-template'],
            ['admin.layouts.index', __('Thème'), 'layers'],
            ['admin.appearance.edit', __('Apparence'), 'palette'],
        ]];
        $groups[] = [__('Marketing'), [
            ['admin.newsletter.index', __('Newsletter'), 'mail'],
        ]];
        $groups[] = [__('Système'), [
            ['admin.plugins.index', __('Plugins'), 'puzzle'],
            ['admin.transfer.index', __('Sauvegarde'), 'hard-drive-download'],
            ['admin.settings.edit', __('Réglages'), 'settings'],
        ]];
    }
    // L'exploitation de la plateforme (clients, offres, paiements) n'est pas ici : console /console, comptes à part.
    // Les plugins peuvent ajouter des entrées : [nom de route, libellé, tracé SVG].
    $plugins = \App\Plugins\Hooks::filter('admin.menu', []);
    $initials = collect(preg_split('/\s+/', trim($user->name)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

{{-- Menu latéral : fixe sur ordinateur, tiroir sur mobile (bouton ☰ de la barre du haut). --}}
<div id="gp-nav-backdrop" class="fixed inset-0 z-40 hidden bg-slate-900/50 backdrop-blur-sm lg:hidden" data-nav-close></div>
<aside id="gp-nav" class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-night text-slate-300 transition-transform duration-200 lg:translate-x-0" aria-label="{{ __('Menu de l’administration') }}">
    <div class="flex h-16 shrink-0 items-center justify-between gap-2 px-5">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2"><x-logo :size="28" dark /></a>
        <button type="button" class="grid h-8 w-8 place-items-center rounded-lg text-slate-400 hover:bg-white/10 hover:text-white lg:hidden" data-nav-close title="{{ __('Fermer le menu') }}" aria-label="{{ __('Fermer le menu') }}"><x-icon name="x" /></button>
    </div>

    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="group mx-3 mb-3 flex items-center gap-3 rounded-xl bg-white/5 px-3 py-2.5 ring-1 ring-white/10 transition hover:bg-white/10">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-600 text-sm font-bold text-white">{{ mb_strtoupper(mb_substr($siteName ?: 'G', 0, 1)) }}</span>
        <span class="min-w-0 flex-1">
            <span class="block truncate text-sm font-semibold text-white">{{ $siteName }}</span>
            <span class="flex items-center gap-1 text-xs text-slate-400 group-hover:text-gold-light">{{ __('Voir le site') }} <x-icon name="external-link" class="h-3 w-3" /></span>
        </span>
    </a>

    <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-4 [scrollbar-width:thin]">
        @foreach ($groups as [$title, $items])
            <div>
                @if ($title)<p class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $title }}</p>@endif
                <ul class="space-y-0.5">
                    @foreach ($items as $item)
                        @php
                            [$route, $label, $icon] = $item;
                            $pattern = $item[3] ?? str_replace('.index', '.*', $route);
                            $active = request()->routeIs(...explode('|', $pattern));
                            $badge = $item[4] ?? 0;
                        @endphp
                        <li>
                            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif @class(['group relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                'bg-white/10 text-white' => $active, 'text-slate-300 hover:bg-white/5 hover:text-white' => ! $active])>
                                @if ($active)<span class="absolute inset-y-1.5 left-0 w-1 rounded-r-full bg-brand-600" aria-hidden="true"></span>@endif
                                <x-icon :name="$icon" :class="'h-[18px] w-[18px] '.($active ? 'text-gold-light' : 'text-slate-400 group-hover:text-slate-200')" />
                                <span class="flex-1">{{ $label }}</span>
                                @if ($badge)<span class="rounded-full bg-brand-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white">{{ $badge > 99 ? '99+' : $badge }}</span>@endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
        @if ($plugins)
            <div>
                <p class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Extensions') }}</p>
                <ul class="space-y-0.5">
                    @foreach ($plugins as [$route, $label, $path])
                        @php $active = request()->routeIs(str_replace('.index', '.*', $route)); @endphp
                        <li><a href="{{ route($route) }}" @class(['flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition', 'bg-white/10 text-white' => $active, 'text-slate-300 hover:bg-white/5 hover:text-white' => ! $active])>
                            <svg class="h-[18px] w-[18px] text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $path }}"/></svg>{{ $label }}
                        </a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </nav>

    <div class="shrink-0 border-t border-white/10 p-3">
        <a href="{{ route('admin.account.edit') }}" @class(['flex items-center gap-3 rounded-lg p-2 transition hover:bg-white/5', 'bg-white/10' => request()->routeIs('admin.account.*')])>
            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-gold-light text-xs font-bold text-night">{{ $initials ?: '?' }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-medium text-white">{{ $user->name }}</span>
                <span class="block text-xs text-slate-400">{{ $isAdmin ? __('Administrateur') : __('Éditeur') }}</span>
            </span>
        </a>
    </div>
</aside>

<div class="flex min-h-screen flex-col lg:pl-64">
    {{-- Barre du haut : recherche, création rapide, site, notifications, compte. --}}
    <header class="sticky top-0 z-30 border-b border-line bg-cream/90 backdrop-blur">
        <div class="flex h-16 items-center gap-2 px-4 md:px-6">
            <button type="button" class="grid h-9 w-9 place-items-center rounded-lg text-slate-600 hover:bg-slate-100 lg:hidden" data-nav-open title="{{ __('Ouvrir le menu') }}" aria-label="{{ __('Ouvrir le menu') }}" aria-controls="gp-nav"><x-icon name="menu" /></button>
            <form method="GET" action="{{ route('admin.search') }}" role="search" class="relative hidden max-w-md flex-1 sm:block">
                <span class="pointer-events-none absolute inset-y-0 left-3 grid place-items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span>
                <input type="search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}" placeholder="{{ __('Rechercher pages, articles, médias…') }}" aria-label="{{ __('Rechercher') }}"
                    class="w-full rounded-lg border border-line bg-white py-2 pl-9 pr-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-500/10">
            </form>
            <div class="ml-auto flex items-center gap-1">
                <a href="{{ route('admin.search') }}" class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 sm:hidden" title="{{ __('Rechercher') }}" aria-label="{{ __('Rechercher') }}"><x-icon name="search" /></a>
                <details class="relative" data-dropdown>
                    <summary class="flex cursor-pointer list-none items-center gap-1.5 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-brand-700 [&::-webkit-details-marker]:hidden">
                        <x-icon name="plus" class="h-4 w-4" stroke="2.4" /><span class="hidden sm:inline">{{ __('Nouveau') }}</span>
                    </summary>
                    <div class="absolute right-0 top-11 z-40 w-60 rounded-xl border border-line bg-white p-1.5 shadow-lg shadow-night/5">
                        @foreach (array_filter([
                            ['admin.pages.create', __('Page'), 'files', __('Conçue dans le builder')],
                            ['admin.posts.create', __('Article'), 'newspaper', __('Pour le blog')],
                            ['admin.media.index', __('Média'), 'image', __('Image, document ou vidéo')],
                            $isAdmin ? ['admin.templates.index', __('Site à partir d’un modèle'), 'layout-template', __('Plus de 100 modèles')] : null,
                        ]) as [$route, $label, $icon, $hint])
                            <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-lg px-2.5 py-2 hover:bg-cream">
                                <span class="grid h-8 w-8 place-items-center rounded-lg bg-cream text-night"><x-icon :name="$icon" class="h-4 w-4" /></span>
                                <span><span class="block text-sm font-medium text-slate-800">{{ $label }}</span><span class="block text-xs text-slate-500">{{ $hint }}</span></span>
                            </a>
                        @endforeach
                    </div>
                </details>
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="hidden h-9 w-9 place-items-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 md:grid" title="{{ __('Voir le site') }}" aria-label="{{ __('Voir le site') }}"><x-icon name="eye" /></a>
                <x-notification-bell :href="route('admin.notifications.index')" :count="$unreadNotices" />
                <details class="relative" data-dropdown>
                    <summary class="ml-1 grid h-9 w-9 cursor-pointer list-none place-items-center rounded-full bg-night text-xs font-bold text-gold-light ring-2 ring-cream transition hover:ring-brand-200 [&::-webkit-details-marker]:hidden" title="{{ $user->name }}" aria-label="{{ __('Mon compte') }}">{{ $initials ?: '?' }}</summary>
                    <div class="absolute right-0 top-11 z-40 w-64 rounded-xl border border-line bg-white p-1.5 shadow-lg shadow-night/5">
                        <div class="px-3 py-2">
                            <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                        </div>
                        <a href="{{ route('admin.account.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="user" class="h-4 w-4 text-slate-400" />{{ __('Mon compte') }}</a>
                        <form method="POST" action="{{ route('admin.locale') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-1.5 text-sm text-slate-700">
                            @csrf @method('PUT')
                            <x-icon name="languages" class="h-4 w-4 text-slate-400" />
                            <label for="ui-locale" class="sr-only">{{ __('Langue de l’interface') }}</label>
                            <select id="ui-locale" name="locale" onchange="this.form.submit()" class="min-w-0 flex-1 rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-600">
                                @foreach (config('geniuspress.ui_locales') as $code => $name)
                                    <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </form>
                        <div class="my-1 border-t border-slate-100"></div>
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-red-50 hover:text-red-700"><x-icon name="log-out" class="h-4 w-4" />{{ __('Se déconnecter') }}</button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 md:px-8 md:py-8">
        @include('hosting.banner')
        @if (session('status'))
            <div class="mb-6 flex items-start gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm text-emerald-800"><x-icon name="circle-check" class="mt-px h-4 w-4" />{{ session('status') }}</div>
        @endif
        @if (session('warnings') && ! request()->routeIs('admin.transfer.*'))
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <ul class="list-disc pl-5">@foreach (session('warnings') as $warning)<li>{{ $warning }}</li>@endforeach</ul>
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script>
    // Tiroir du menu sur mobile ; menus déroulants (details) fermés au clic ailleurs ou avec Échap.
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
