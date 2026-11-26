@extends('platform.layouts.customer')
@section('title', __('Mon espace'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Bonjour :name', ['name' => $customer->name]) }}</h1>

    @if ($newKey)
        <div class="mt-6 rounded-xl border border-amber-300 bg-amber-50 p-5">
            <p class="font-semibold text-amber-900">{{ __('Votre nouvelle clé de licence') }}</p>
            <p class="mt-1 text-sm text-amber-900">{{ __('Copiez-la maintenant et collez-la dans Réglages → Assistant IA de votre site. Elle ne sera plus jamais affichée.') }}</p>
            <input readonly value="{{ $newKey }}" onclick="this.select()" aria-label="{{ __('Votre nouvelle clé de licence') }}" class="mt-3 block w-full rounded-lg border border-amber-300 bg-white px-3 py-2 font-mono text-sm">
        </div>
    @endif

    {{-- Le site GeniusPress du client : le cœur de son espace de travail. --}}
    @if ($hosting && $sites !== null)
        {{-- Sites gratuits sans limite : tous les sites du client. --}}
        <div class="mt-6 rounded-xl border border-brand-200 bg-brand-50/60 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-lg font-semibold">{{ __('Mes sites GeniusPress') }}</p>
                    <p class="text-sm text-slate-600">{{ $sites->isEmpty() ? __('Créez votre premier site : builder, modèles et assistant IA, publié sur votre adresse .geniuspress.site.') : trans_choice('{1} 1 site|[2,*] :count sites', $sites->count()) }}</p>
                </div>
                <a href="{{ route('hosting.trial') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ $sites->isEmpty() ? __('Créer mon site') : __('+ Nouveau site') }}</a>
            </div>
            @if ($sites->isNotEmpty())
                <ul class="mt-4 divide-y divide-brand-100 rounded-lg border border-brand-100 bg-white">
                    @foreach ($sites->take(8) as $s)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ $s->name }}</p>
                                <a href="{{ $s->url() }}" target="_blank" rel="noopener" class="truncate font-mono text-xs text-brand-700 hover:underline">{{ $s->host }}</a>
                            </div>
                            @if ($s->effectiveStatus() === 'active' && ! $s->awaitingCertificate())
                                <div class="flex gap-2">
                                    <a href="{{ route('hosting.trial.open', ['site' => $s->slug]) }}" target="_blank" rel="noopener" class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Ouvrir le builder ↗') }}</a>
                                    <a href="{{ $s->url() }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">{{ __('Voir ↗') }}</a>
                                </div>
                            @else
                                <a href="{{ route('hosting.trial') }}" class="text-sm text-brand-700 hover:underline">{{ __('Voir l’état') }}</a>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if ($sites->count() > 8)<a href="{{ route('hosting.trial') }}" class="mt-2 inline-block text-sm text-brand-700 hover:underline">{{ __('Tous mes sites →') }}</a>@endif
            @endif
        </div>
    @elseif ($hosting)
        <div class="mt-6 rounded-xl border border-brand-200 bg-brand-50/60 p-5 sm:p-6">
            @if ($site)
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm text-slate-500">{{ __('Mon site GeniusPress') }}</p>
                        <p class="mt-1 truncate text-lg font-semibold">{{ $site->name }}</p>
                        <p class="truncate font-mono text-xs text-slate-500">{{ $site->host ?? '—' }}</p>
                        @if ($site->kind === 'trial' && $site->daysLeft() !== null)
                            <p class="mt-1 text-sm text-brand-800">{{ trans_choice('{0} Essai terminé|{1} Essai : 1 jour restant|[2,*] Essai : :count jours restants', $site->daysLeft()) }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if (in_array($site->status, ['active', 'expired'], true))
                            <a href="{{ route('hosting.trial.open') }}" target="_blank" rel="noopener" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Ouvrir mon site et le builder') }}</a>
                        @else
                            <a href="{{ route('hosting.trial') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Voir l’état de mon site') }}</a>
                        @endif
                        @if ($site->kind === 'trial')
                            <a href="{{ route('platform.customer.plans') }}" class="rounded-lg border border-brand-300 bg-white px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ __('Passer à une offre') }}</a>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-lg font-semibold">{{ __('Créez votre site GeniusPress') }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ __('Un site complet hébergé chez nous, avec le builder, les modèles et l’assistant IA. Rien à installer.') }}</p>
                    </div>
                    <a href="{{ route('hosting.trial') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ $canTrial ? __('Créer mon site gratuitement') : __('Créer mon site') }}</a>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Crédits IA') }}</p>
            <p @class(['mt-1 text-2xl font-semibold', 'text-red-600' => $customer->ai_balance <= 0])>{{ Money::micros($customer->ai_balance) }}</p>
            <a href="{{ route('platform.customer.plans') }}#ai_credits" class="mt-2 inline-block text-sm font-medium text-brand-600 hover:underline">{{ __('Recharger') }}</a>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 md:col-span-2">
            <p class="text-sm text-slate-500">{{ __('Abonnements en cours') }}</p>
            @forelse ($subscriptions as $subscription)
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <span class="font-medium">{{ $subscription->plan->label() }}</span>
                        <span class="ml-2 rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">{{ Labels::status($subscription->status) }}</span>
                        @if ($subscription->endsAt())<span class="ml-2 text-xs text-slate-500">{{ __('jusqu’au :date', ['date' => $subscription->endsAt()->isoFormat('LL')]) }}</span>@endif
                    </div>
                    @if (in_array($subscription->status, ['active', 'trialing'], true))
                        <form method="POST" action="{{ route('platform.customer.subscriptions.cancel', $subscription) }}" onsubmit="return confirm(@js(__('Résilier cet abonnement ?')))">@csrf
                            <button class="text-xs text-slate-500 hover:text-red-600">{{ __('Résilier') }}</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="mt-2 text-sm text-slate-600">{{ __('Aucun abonnement.') }} <a href="{{ route('platform.customer.plans') }}" class="font-medium text-brand-600 hover:underline">{{ __('Voir les offres') }}</a></p>
            @endforelse
        </div>
    </div>

    <section class="mt-8 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">{{ __('Clés de licence') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Une clé par site GeniusPress (installé chez vous, sur votre serveur ou hébergé chez nous) : l’assistant IA utilise vos crédits et vos modèles achetés s’installent directement.') }}</p>
        <div class="mt-4 divide-y divide-slate-100">
            @forelse ($licenses as $license)
                <div class="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                    <div>
                        <span class="font-medium">{{ $license->name }}</span>
                        <code class="ml-2 text-xs text-slate-500">{{ $license->displayKey() }}</code>
                        @if (! $license->isActive())<span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ Labels::status($license->status) }}</span>@endif
                        @if ($license->last_used_at)<span class="ml-2 text-xs text-slate-500">{{ __('utilisée :when', ['when' => $license->last_used_at->diffForHumans()]) }}</span>@endif
                    </div>
                    @if ($license->isActive())
                        <form method="POST" action="{{ route('platform.customer.licenses.destroy', $license) }}" onsubmit="return confirm(@js(__('Révoquer cette clé ? Le site qui l’utilise perdra l’accès à l’IA.')))">@csrf @method('DELETE')
                            <button class="text-xs text-slate-500 hover:text-red-600">{{ __('Révoquer') }}</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="py-2 text-sm text-slate-500">{{ __('Aucune clé pour l’instant.') }}</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('platform.customer.licenses.store') }}" class="mt-4 flex flex-wrap gap-2">
            @csrf
            <input name="name" required maxlength="120" placeholder="{{ __('Nom du site') }}" aria-label="{{ __('Nom du site') }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input name="site_url" type="url" maxlength="255" placeholder="https://…" aria-label="{{ __('Adresse du site') }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Créer une clé') }}</button>
        </form>
    </section>

    <div class="mt-8 grid gap-6 md:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Dernières commandes') }}</h2>
            <div class="mt-3 divide-y divide-slate-100 text-sm">
                @forelse ($orders as $order)
                    <a href="{{ route('platform.customer.orders.show', $order) }}" class="flex justify-between gap-2 py-2 hover:text-brand-600">
                        <span>{{ $order->items->first()?->description }}</span>
                        <span class="whitespace-nowrap text-slate-500">{{ Money::format($order->total, $order->currency) }} · {{ Labels::status($order->status) }}</span>
                    </a>
                @empty
                    <p class="py-2 text-slate-500">{{ __('Aucune commande.') }}</p>
                @endforelse
            </div>
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Mes domaines') }}</h2>
            <div class="mt-3 divide-y divide-slate-100 text-sm">
                @forelse ($domains as $domain)
                    <div class="flex justify-between gap-2 py-2">
                        <span class="font-medium">{{ $domain->name }}</span>
                        <span class="text-slate-500">{{ $domain->expires_at ? __('expire le :date', ['date' => $domain->expires_at->isoFormat('LL')]) : Labels::status($domain->status) }}</span>
                    </div>
                @empty
                    <p class="py-2 text-slate-500">{{ __('Aucun domaine.') }} <a href="{{ route('platform.customer.domains') }}" class="font-medium text-brand-600 hover:underline">{{ __('Trouver un domaine') }}</a></p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
