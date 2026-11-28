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
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="max-w-2xl">
            <h1 class="text-2xl font-bold tracking-tight text-night">{{ __('Mes sites GeniusPress') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('Construisez vos sites avec le builder, les modèles et l’assistant IA, prévisualisez-les et publiez-les sur leur adresse :domain. Gratuit, sans limite de durée.', ['domain' => '.'.$domain]) }}</p>
        </div>
        @if ($sites->isNotEmpty())
            <a href="#nouveau" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700"><x-icon name="plus" class="h-4 w-4" stroke="2.4" />{{ __('Créer un nouveau site') }}</a>
        @endif
    </div>

    @if ($sites->isNotEmpty())
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($sites as ['site' => $site, 'status' => $status])
                @include('platform.customer.partials.site-card', ['site' => $site, 'status' => $status])
            @endforeach
        </div>
    @endif

    <section id="nouveau" class="mt-10 scroll-mt-24 overflow-hidden rounded-2xl border border-line bg-white">
        <div class="grid lg:grid-cols-5">
            <div class="bg-night p-8 text-white lg:col-span-2">
                <img src="{{ asset('brand/geniuspress-mark.svg') }}" alt="" class="h-10 w-10 rounded-lg bg-cream p-1">
                <h2 class="mt-4 text-2xl font-bold tracking-tight">{{ $sites->isEmpty() ? __('Créer mon premier site') : __('Créer un nouveau site') }}</h2>
                <ul class="mt-5 space-y-2.5 text-sm text-slate-200">
                    @foreach ([__('Plus de 100 modèles professionnels, en français et en anglais'), __('Le builder visuel et l’assistant IA pour vos textes'), __('Hébergé, sécurisé (HTTPS) et sauvegardé par GeniusPress'), __('Votre propre nom de domaine quand vous le souhaitez')] as $point)
                        <li class="flex items-start gap-2"><x-icon name="circle-check" class="mt-0.5 h-4 w-4 text-gold-light" />{{ $point }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="p-6 sm:p-8 lg:col-span-3">
                @if ($failed)
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ __('La création de votre site a échoué. Réessayez dans quelques minutes ; si le problème persiste, contactez-nous.') }}</div>
                @endif
                @if ($refusal)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $refusal }}</div>
                @else
                    <form method="POST" action="{{ route('hosting.trial.store') }}" class="space-y-5">
                        @csrf
                        <x-field name="name" :label="__('Nom du site')" :value="$sites->isEmpty() ? auth()->user()->name : ''" required maxlength="120" :hint="__('Le nom de votre organisation, de votre projet ou de votre boutique.')" />
                        <div>
                            <label for="slug" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Adresse') }}</label>
                            <div class="flex items-stretch overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20">
                                <span class="flex items-center bg-slate-50 px-3 text-sm text-slate-400">https://</span>
                                <input id="slug" name="slug" value="{{ old('slug', $sites->isEmpty() ? $suggested : '') }}" required maxlength="40" pattern="[a-z0-9][a-z0-9-]{1,38}[a-z0-9]" autocomplete="off"
                                    class="min-w-0 flex-1 px-3 py-2 font-mono text-sm outline-none">
                                <span class="flex items-center bg-slate-50 px-3 font-mono text-sm text-slate-500">.{{ $domain }}</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">{{ __('Lettres minuscules sans accents, chiffres et tirets.') }}</p>
                            @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700"><x-icon name="rocket" class="h-4 w-4" />{{ __('Créer le site') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
