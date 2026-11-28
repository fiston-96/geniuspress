{{--
    Carte d'un site GeniusPress hébergé (tableau de bord et « Mes sites ») : aperçu stylisé,
    état, accès au builder et au site. $site : App\Hosting\Site ; $status : état affiché
    (active, provisioning, securing, suspended, expired…).
--}}
@php
    $status ??= ($site->effectiveStatus() === 'active' && $site->awaitingCertificate()) ? 'securing' : $site->effectiveStatus();
    $ready = $status === 'active';
    $busy = in_array($status, ['provisioning', 'securing'], true);
@endphp
<article class="group flex flex-col overflow-hidden rounded-2xl border border-line bg-white transition hover:-translate-y-0.5 hover:border-brand-300">
    {{-- Aperçu : la page du site (bandeau terracotta, adresse) posée sur une page or clair, comme le symbole. --}}
    <div class="relative h-40 overflow-hidden border-b border-line bg-cream">
        @if ($ready)
            {{-- Aperçu en direct du site, réduit au quart : sans scripts ni interaction (sandbox), chargé à l'approche. --}}
            <iframe src="{{ $site->url() }}" title="{{ __('Aperçu de :name', ['name' => $site->name]) }}" loading="lazy" sandbox="" tabindex="-1" aria-hidden="true"
                class="pointer-events-none absolute left-0 top-0 h-[400%] w-[400%] origin-top-left scale-25 border-0 bg-white"></iframe>
            <a href="{{ $site->url() }}" target="_blank" rel="noopener" class="absolute inset-0" tabindex="-1" aria-hidden="true"></a>
        @else
        <div class="absolute bottom-0 left-4 h-24 w-3/5 rounded-t-lg bg-gold-light"></div>
        <div class="absolute inset-x-8 bottom-0 top-6 overflow-hidden rounded-t-lg bg-white ring-1 ring-line">
            <div class="flex items-center gap-1.5 bg-brand-600 px-3 py-2">
                <span class="h-1.5 w-1.5 rounded-full bg-white/70"></span><span class="h-1.5 w-1.5 rounded-full bg-white/50"></span><span class="h-1.5 w-1.5 rounded-full bg-white/30"></span>
                <span class="ml-1 truncate text-[11px] font-semibold text-white">{{ $site->name }}</span>
            </div>
            <div class="space-y-2 px-3 py-2.5">
                <p class="truncate font-mono text-[10px] text-slate-400">{{ $site->host }}</p>
                <div class="grid grid-cols-3 gap-1.5"><div class="col-span-2 h-6 rounded bg-cream"></div><div class="h-6 rounded bg-paper"></div></div>
                <div class="h-1.5 w-3/4 rounded-full bg-slate-100"></div>
            </div>
        </div>
        @endif
        <span @class(['absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[11px] font-semibold',
            'bg-white text-emerald-700 ring-1 ring-emerald-200' => $ready, 'bg-white text-brand-700 ring-1 ring-brand-200' => $busy, 'bg-red-600 text-white' => $status === 'suspended', 'bg-night text-white' => ! $ready && ! $busy && $status !== 'suspended'])>
            @if ($ready)<span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>@endif
            @if ($busy)<svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"/><path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>@endif
            {{ match (true) { $ready => __('En ligne'), $status === 'securing' => __('Sécurisation…'), $status === 'provisioning' => __('Préparation…'), $status === 'suspended' => __('Suspendu'), $status === 'expired' => __('Expiré'), default => __('Indisponible') } }}
        </span>
    </div>
    <div class="flex flex-1 flex-col p-4">
        <p class="truncate font-semibold text-night">{{ $site->name }}</p>
        <a href="{{ $site->url() }}" target="_blank" rel="noopener" class="truncate font-mono text-xs text-brand-700 hover:underline">{{ $site->host }}</a>
        @if ($status === 'suspended' && $site->suspend_reason)<p class="mt-1 text-xs text-red-700">{{ $site->suspend_reason }}</p>@endif
        <div class="mt-4 flex items-center gap-2">
            @if ($ready)
                <a href="{{ route('hosting.trial.open', ['site' => $site->slug]) }}" target="_blank" rel="noopener" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">
                    <x-icon name="wand-sparkles" class="h-4 w-4" />{{ __('Ouvrir le builder') }}
                </a>
                <a href="{{ $site->url() }}" target="_blank" rel="noopener" class="grid h-9 w-9 place-items-center rounded-lg border border-line text-slate-600 transition hover:bg-cream" title="{{ __('Voir le site') }}" aria-label="{{ __('Voir le site') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                @if (Route::has('platform.customer.domains'))<a href="{{ route('platform.customer.domains') }}" class="grid h-9 w-9 place-items-center rounded-lg border border-line text-slate-600 transition hover:bg-cream" title="{{ __('Brancher un nom de domaine') }}" aria-label="{{ __('Brancher un nom de domaine') }}"><x-icon name="at-sign" class="h-4 w-4" /></a>@endif
            @else
                <a href="{{ route('hosting.trial') }}" class="text-sm font-medium text-brand-700 hover:underline">{{ __('Voir l’état') }}</a>
            @endif
        </div>
    </div>
</article>
