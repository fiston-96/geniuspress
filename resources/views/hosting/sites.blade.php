{{-- Sites gratuits sans limite (config hosting.unlimited) : tous les sites du compte, et la création d'un nouveau. --}}
@extends(config('geniuspress.platform.enabled') ? 'platform.layouts.customer' : 'hosting.layout')
@section('title', __('Mes sites GeniusPress'))
@php($pending = $sites->first(fn ($s) => in_array($s['status'], ['provisioning', 'securing'], true)))
@if ($pending)
    @push('head')
        {{-- Préparation (ou certificat HTTPS) en cours : la page se recharge quand le site est prêt. --}}
        <script>
            (function poll() {
                setTimeout(function () {
                    fetch(@json(route('hosting.trial.status', ['site' => $pending['site']->slug])), { headers: { Accept: 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (d) { d.status === @json($pending['status']) ? poll() : location.reload(); })
                        .catch(poll);
                }, 2000);
            })();
        </script>
    @endpush
@endif
@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Mes sites GeniusPress') }}</h1>
    <p class="mt-2 text-sm text-slate-600">{{ __('Construisez vos sites avec le builder, les modèles et l’assistant IA, prévisualisez-les et publiez-les sur leur adresse :domain. Gratuit, sans limite de durée.', ['domain' => '.'.$domain]) }}</p>

    @if ($sites->isNotEmpty())
        <ul class="mt-6 space-y-3">
            @foreach ($sites as ['site' => $site, 'status' => $status])
                <li class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $site->name }}</p>
                            <a href="{{ $site->url() }}" target="_blank" rel="noopener" class="truncate font-mono text-xs text-brand-700 hover:underline">{{ $site->host }}</a>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if ($status === 'active')
                                <a href="{{ route('hosting.trial.open', ['site' => $site->slug]) }}" target="_blank" rel="noopener" class="rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Ouvrir le builder ↗') }}</a>
                                <a href="{{ $site->url() }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Voir le site ↗') }}</a>
                            @elseif (in_array($status, ['provisioning', 'securing'], true))
                                <span class="flex items-center gap-2 text-sm text-brand-700">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                                    {{ $status === 'securing' ? __('Sécurisation de l’adresse (HTTPS)…') : __('Préparation…') }}
                                </span>
                            @elseif ($status === 'suspended')
                                <span class="text-sm text-red-700">{{ __('Suspendu') }}@if ($site->suspend_reason) · {{ $site->suspend_reason }}@endif</span>
                            @else
                                <span class="text-sm text-slate-500">{{ __('Indisponible') }}</span>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <h2 class="mt-8 text-lg font-semibold">{{ $sites->isEmpty() ? __('Créer mon premier site') : __('Créer un nouveau site') }}</h2>
    @if ($failed)
        <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ __('La création de votre site a échoué. Réessayez dans quelques minutes ; si le problème persiste, contactez-nous.') }}</div>
    @endif
    @if ($refusal)
        <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $refusal }}</div>
    @else
        <form method="POST" action="{{ route('hosting.trial.store') }}" class="mt-3 space-y-4 rounded-xl border border-slate-200 bg-white p-5">
            @csrf
            <x-field name="name" :label="__('Nom du site')" :value="$sites->isEmpty() ? auth()->user()->name : ''" required maxlength="120" />
            <div>
                <label for="slug" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Adresse') }}</label>
                <div class="flex items-stretch overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20">
                    <input id="slug" name="slug" value="{{ old('slug', $sites->isEmpty() ? $suggested : '') }}" required maxlength="40" pattern="[a-z0-9][a-z0-9-]{1,38}[a-z0-9]" autocomplete="off"
                        class="min-w-0 flex-1 px-3 py-2 text-sm outline-none">
                    <span class="flex items-center bg-slate-50 px-3 text-sm text-slate-500">.{{ $domain }}</span>
                </div>
                <p class="mt-1 text-xs text-slate-500">{{ __('Lettres minuscules sans accents, chiffres et tirets.') }}</p>
                @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Créer le site') }}</button>
        </form>
    @endif
@endsection
