<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · {{ __('Espace client GeniusPress') }}</title>
    @include('partials.brand-head')
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
@php
    $links = [
        ['platform.customer.dashboard', __('Mon espace')],
        ['platform.customer.plans', __('Offres')],
        ['platform.customer.templates', __('Modèles payants')],
        ['platform.customer.domains', __('Domaines')],
        ['platform.customer.credits', __('Crédits IA')],
        ['platform.customer.referrals', __('Affiliation')],
    ];
@endphp
<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-3 md:px-8">
        <a href="{{ route('platform.customer.dashboard') }}" class="flex items-center gap-2"><x-logo :size="28" /></a>
        <nav class="flex flex-wrap items-center gap-1 text-sm">
            @foreach ($links as [$route, $label])
                <a href="{{ route($route) }}" @class(['rounded-lg px-3 py-2 font-medium transition', 'bg-brand-50 text-brand-700' => request()->routeIs($route), 'text-slate-600 hover:bg-slate-100' => ! request()->routeIs($route)])>{{ $label }}</a>
            @endforeach
            @if (auth('customer')->check())
                <x-notification-bell :href="route('platform.customer.notifications.index')" :count="auth('customer')->user()->unreadNotifications()->count()" />
            @endif
            <form method="POST" action="{{ route('platform.logout') }}">@csrf
                <button class="rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-100">{{ __('Se déconnecter') }}</button>
            </form>
        </nav>
    </div>
</header>
<main class="mx-auto w-full max-w-6xl px-4 py-8 md:px-8">
    @if (session('status'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
