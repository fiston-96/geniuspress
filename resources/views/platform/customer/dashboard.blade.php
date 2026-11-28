@extends('platform.layouts.customer')
@section('title', __('Mon espace'))
@section('content')
@php
    use App\Platform\Labels;
    use App\Platform\Money;
    $countryName = \App\Platform\Countries::name($customer->country);
    $firstSite = $sites?->first(fn ($s) => $s->effectiveStatus() === 'active' && ! $s->awaitingCertificate());
@endphp

    @if ($newKey)
        <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-5">
            <p class="flex items-center gap-2 font-semibold text-amber-900"><x-icon name="key-round" class="h-5 w-5" />{{ __('Votre nouvelle clé de licence') }}</p>
            <p class="mt-1 text-sm text-amber-900">{{ __('Copiez-la maintenant et collez-la dans Réglages → Assistant IA de votre site. Elle ne sera plus jamais affichée.') }}</p>
            <input readonly value="{{ $newKey }}" onclick="this.select()" aria-label="{{ __('Votre nouvelle clé de licence') }}" class="mt-3 block w-full rounded-lg border border-amber-300 bg-white px-3 py-2 font-mono text-sm">
        </div>
    @endif

    {{-- Bienvenue : le site en construction (illustration tirée du symbole) et les chiffres du compte. --}}
    @php $showcase = $firstSite ?? $site ?? $sites?->first(); @endphp
    <section class="overflow-hidden rounded-2xl border border-line bg-white">
        <div class="flex flex-wrap items-center justify-between gap-8 px-6 py-8 sm:px-10">
            <div class="max-w-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold">{{ __('Espace client') }}</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-night sm:text-[2.6rem] sm:leading-tight">{{ __('Bonjour :name', ['name' => $customer->name]) }}</h1>
                <p class="mt-3 text-[15px] leading-relaxed text-slate-600">{{ __('Votre espace GeniusPress : vos sites, vos domaines, vos offres et vos crédits IA, au même endroit.') }}</p>
                @if ($countryName)
                    <a href="{{ route('platform.customer.account') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-brand-700">
                        <x-icon name="globe" class="h-3.5 w-3.5 text-gold" />{{ __('Votre pays : :country', ['country' => $countryName]) }}@if ($currency = \App\Platform\Countries::currency($customer->country)) · {{ __('Les prix sont aussi affichés en :currency, et le paiement s’adapte à votre pays.', ['currency' => $currency]) }}@endif
                    </a>
                @endif
                @if ($hosting && $sites !== null)
                    <div class="mt-6 flex flex-wrap gap-2">
                        @if ($firstSite)
                            <a href="{{ route('hosting.trial.open', ['site' => $firstSite->slug]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700"><x-icon name="wand-sparkles" class="h-4 w-4" />{{ __('Ouvrir le builder') }}</a>
                        @endif
                        <a href="{{ route('hosting.trial') }}#nouveau" @class(['inline-flex items-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition', 'border border-night/15 text-night hover:border-night/30 hover:bg-cream' => $firstSite, 'bg-brand-600 text-white hover:bg-brand-700' => ! $firstSite])><x-icon name="plus" class="h-4 w-4" stroke="2.4" />{{ $sites->isEmpty() ? __('Créer mon site') : __('+ Nouveau site') }}</a>
                    </div>
                @endif
            </div>
            @if ($showcase)
                <x-brand-pages :title="$showcase->name" :subtitle="$showcase->host" class="hidden md:block" />
            @else
                <x-brand-pages :title="__('Mon site')" :subtitle="'….geniuspress.site'" class="hidden md:block" />
            @endif
        </div>

        <dl class="grid grid-cols-2 border-t border-line lg:grid-cols-4">
            @foreach (array_values(array_filter([
                $hosting ? [__('Sites'), $sites !== null ? $sites->count() : ($site ? 1 : 0), route('hosting.trial'), __('Hébergés chez GeniusPress'), false] : null,
                [__('Domaines'), $domains->count(), route('platform.customer.domains'), __('Trouver un domaine'), false],
                [__('Crédits IA'), Money::micros($customer->ai_balance), route('platform.customer.plans').'#ai_credits', __('Recharger'), $customer->ai_balance <= 0],
                [__('Abonnements'), $subscriptions->count(), route('platform.customer.plans'), __('Voir les offres'), false],
            ])) as $i => [$label, $value, $href, $hint, $alert])
                <a href="{{ $href }}" @class(['group border-line px-6 py-5 transition hover:bg-cream sm:px-8', 'border-l' => $i % 2 === 1, 'lg:border-l' => $i > 0, 'border-t lg:border-t-0' => $i >= 2])>
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $label }}</dt>
                    <dd @class(['mt-1 text-3xl font-bold tabular-nums tracking-tight', 'text-red-600' => $alert, 'text-night' => ! $alert])>{{ $value }}</dd>
                    <dd class="mt-1 flex items-center gap-1 text-xs text-slate-500 group-hover:text-brand-700">{{ $hint }}<x-icon name="arrow-right" class="h-3 w-3 opacity-0 transition group-hover:opacity-100" /></dd>
                </a>
            @endforeach
        </dl>
    </section>

    {{-- Pays manquant : prix et paiement adaptés une fois indiqué. --}}
    @unless ($countryName)
        <a href="{{ route('platform.customer.account') }}" class="mt-4 flex items-center gap-3 rounded-xl border border-gold/40 bg-white p-4 text-sm text-night hover:bg-cream">
            <x-icon name="globe" class="h-5 w-5 shrink-0 text-gold" />
            <span class="flex-1"><strong>{{ __('Indiquez votre pays') }}</strong> — {{ __('GeniusPress affiche alors les prix dans votre devise et vous propose les moyens de paiement de votre pays.') }}</span>
            <x-icon name="arrow-right" class="h-4 w-4 text-brand-600" />
        </a>
    @endunless

    {{-- Les sites du client : le cœur de son espace de travail. --}}
    @if ($hosting && $sites !== null)
        {{-- Sites gratuits sans limite : tous les sites du client. --}}
        <section class="mt-8">
            <div class="mb-4 flex items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-night">{{ __('Mes sites GeniusPress') }}</h2>
                    <p class="text-sm text-slate-500">{{ $sites->isEmpty() ? __('Créez votre premier site : builder, modèles et assistant IA, publié sur votre adresse .geniuspress.site.') : trans_choice('{1} 1 site|[2,*] :count sites', $sites->count()) }}</p>
                </div>
                <a href="{{ route('hosting.trial') }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-700 hover:underline">{{ __('Tous mes sites') }}<x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($sites->take(5) as $s)
                    @include('platform.customer.partials.site-card', ['site' => $s, 'status' => null])
                @endforeach
                <a href="{{ route('hosting.trial') }}#nouveau" class="flex min-h-[15rem] flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-line bg-white/60 p-6 text-center text-slate-500 transition hover:border-brand-400 hover:bg-brand-50/40 hover:text-brand-700">
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-cream ring-1 ring-line"><x-icon name="plus" class="h-6 w-6" /></span>
                    <span class="font-semibold">{{ $sites->isEmpty() ? __('Créer mon site') : __('Créer un nouveau site') }}</span>
                    <span class="text-xs">{{ __('Un modèle, le builder et l’assistant IA : en ligne en quelques minutes.') }}</span>
                </a>
            </div>
        </section>
    @elseif ($hosting)
        {{-- Essai ou offre payante : un site par client. --}}
        <section class="mt-8 overflow-hidden rounded-2xl border border-line bg-white p-6">
            @if ($site)
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-brand-600 shadow-sm ring-1 ring-brand-100"><x-icon name="globe" class="h-6 w-6" /></span>
                        <div class="min-w-0">
                            <p class="text-sm text-slate-500">{{ __('Mon site GeniusPress') }}</p>
                            <p class="truncate text-lg font-semibold">{{ $site->name }}</p>
                            <p class="truncate font-mono text-xs text-slate-500">{{ $site->host ?? '—' }}</p>
                            @if ($site->kind === 'trial' && $site->daysLeft() !== null)
                                <p class="mt-1 text-sm font-medium text-brand-800">{{ trans_choice('{0} Essai terminé|{1} Essai : 1 jour restant|[2,*] Essai : :count jours restants', $site->daysLeft()) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if (in_array($site->status, ['active', 'expired'], true))
                            <a href="{{ route('hosting.trial.open') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700"><x-icon name="wand-sparkles" class="h-4 w-4" />{{ __('Ouvrir mon site et le builder') }}</a>
                        @else
                            <a href="{{ route('hosting.trial') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">{{ __('Voir l’état de mon site') }}</a>
                        @endif
                        @if ($site->kind === 'trial')
                            <a href="{{ route('platform.customer.plans') }}" class="rounded-xl border border-brand-300 bg-white px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ __('Passer à une offre') }}</a>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-white text-brand-600 shadow-sm ring-1 ring-brand-100"><x-icon name="rocket" class="h-6 w-6" /></span>
                        <div>
                            <p class="text-lg font-semibold">{{ __('Créez votre site GeniusPress') }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ __('Un site complet hébergé chez nous, avec le builder, les modèles et l’assistant IA. Rien à installer.') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('hosting.trial') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">{{ $canTrial ? __('Créer mon site gratuitement') : __('Créer mon site') }}</a>
                </div>
            @endif
        </section>
    @endif

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        {{-- Abonnements --}}
        <section class="rounded-2xl border border-line bg-white p-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="font-bold tracking-tight text-night">{{ __('Abonnements en cours') }}</h2>
                <a href="{{ route('platform.customer.plans') }}" class="text-sm font-medium text-brand-700 hover:underline">{{ __('Voir les offres') }}</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($subscriptions as $subscription)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-cream px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-white text-brand-600 ring-1 ring-slate-200"><x-icon name="server" class="h-4 w-4" /></span>
                            <div>
                                <p class="font-medium">{{ $subscription->plan->label() }}</p>
                                <p class="text-xs text-slate-500">{{ Labels::status($subscription->status) }}@if ($subscription->endsAt()) · {{ __('jusqu’au :date', ['date' => $subscription->endsAt()->isoFormat('LL')]) }}@endif</p>
                            </div>
                        </div>
                        @if (in_array($subscription->status, ['active', 'trialing'], true))
                            <form method="POST" action="{{ route('platform.customer.subscriptions.cancel', $subscription) }}" onsubmit="return confirm(@js(__('Résilier cet abonnement ?')))">@csrf
                                <button class="text-xs text-slate-500 hover:text-red-600">{{ __('Résilier') }}</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-cream px-4 py-4">
                        <p class="text-sm text-slate-700">{{ __('Aucun abonnement.') }} {{ __('Un nom de domaine, un hébergement, une boîte mail professionnelle : tout est dans les offres.') }}</p>
                        <a href="{{ route('platform.customer.plans') }}" class="rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Voir les offres') }}</a>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Raccourcis --}}
        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="font-bold tracking-tight text-night">{{ __('Raccourcis') }}</h2>
            <div class="mt-4 space-y-1">
                @foreach ([
                    [route('platform.customer.domains'), __('Trouver un domaine'), 'at-sign'],
                    [route('platform.customer.templates'), __('Modèles'), 'layout-template'],
                    [route('platform.customer.credits'), __('Crédits IA'), 'sparkles'],
                    [route('platform.customer.referrals'), __('Recommander GeniusPress'), 'gift'],
                    [route('platform.customer.account'), __('Mon compte'), 'user'],
                ] as [$href, $label, $icon])
                    <a href="{{ $href }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-cream">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-slate-100 text-slate-500 group-hover:bg-brand-50 group-hover:text-brand-700"><x-icon :name="$icon" class="h-4 w-4" /></span>
                        <span class="flex-1">{{ $label }}</span><x-icon name="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-brand-500" />
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-2">
        <section class="rounded-2xl border border-line bg-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-bold tracking-tight text-night">{{ __('Dernières commandes') }}</h2>
                <a href="{{ route('platform.customer.orders') }}" class="text-sm font-medium text-brand-700 hover:underline">{{ __('Toutes') }}</a>
            </div>
            <div class="mt-3 divide-y divide-line text-sm">
                @forelse ($orders->take(5) as $order)
                    <a href="{{ route('platform.customer.orders.show', $order) }}" class="flex items-center justify-between gap-3 py-2.5 hover:text-brand-700">
                        <span class="min-w-0 truncate">{{ $order->items->first()?->description }}</span>
                        <span class="shrink-0 text-right"><span class="block font-medium tabular-nums">{{ Money::format($order->total, $order->currency) }}</span><span class="block text-xs text-slate-500">{{ Labels::status($order->status) }}</span></span>
                    </a>
                @empty
                    <p class="py-3 text-slate-500">{{ __('Aucune commande.') }}</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-2xl border border-line bg-white p-6">
            <div class="flex items-center justify-between">
                <h2 class="font-bold tracking-tight text-night">{{ __('Mes domaines') }}</h2>
                <a href="{{ route('platform.customer.domains') }}" class="text-sm font-medium text-brand-700 hover:underline">{{ __('Gérer') }}</a>
            </div>
            <div class="mt-3 divide-y divide-line text-sm">
                @forelse ($domains->take(5) as $domain)
                    <div class="flex items-center justify-between gap-2 py-2.5">
                        <span class="flex items-center gap-2 font-medium"><x-icon name="globe" class="h-4 w-4 text-slate-400" />{{ $domain->name }}</span>
                        <span class="text-xs text-slate-500">{{ $domain->expires_at ? __('expire le :date', ['date' => $domain->expires_at->isoFormat('LL')]) : Labels::status($domain->status) }}</span>
                    </div>
                @empty
                    <p class="py-3 text-slate-500">{{ __('Aucun domaine.') }} <a href="{{ route('platform.customer.domains') }}" class="font-medium text-brand-600 hover:underline">{{ __('Trouver un domaine') }}</a></p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
