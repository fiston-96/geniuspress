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

    <div class="mt-6 grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">{{ __('Crédits IA') }}</p>
            <p @class(['mt-1 text-2xl font-semibold', 'text-red-600' => $customer->ai_balance <= 0])>{{ Money::micros($customer->ai_balance) }}</p>
            <a href="{{ route('platform.customer.plans') }}#ai_credits" class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:underline">{{ __('Recharger') }}</a>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 md:col-span-2">
            <p class="text-sm text-slate-500">{{ __('Abonnements en cours') }}</p>
            @forelse ($subscriptions as $subscription)
                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <span class="font-medium">{{ $subscription->plan->label() }}</span>
                        <span class="ml-2 rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">{{ Labels::status($subscription->status) }}</span>
                        @if ($subscription->endsAt())<span class="ml-2 text-xs text-slate-500">{{ __('jusqu’au :date', ['date' => $subscription->endsAt()->isoFormat('LL')]) }}</span>@endif
                    </div>
                    @if (in_array($subscription->status, ['active', 'trialing'], true))
                        <form method="POST" action="{{ route('platform.customer.subscriptions.cancel', $subscription) }}" onsubmit="return confirm(@js(__('Résilier cet abonnement ?')))">@csrf
                            <button class="text-xs text-slate-500 hover:text-red-600">{{ __('Résilier') }}</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="mt-2 text-sm text-slate-600">{{ __('Aucun abonnement.') }} <a href="{{ route('platform.customer.plans') }}" class="font-medium text-indigo-600 hover:underline">{{ __('Voir les offres') }}</a></p>
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
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Créer une clé') }}</button>
        </form>
    </section>

    <div class="mt-8 grid gap-6 md:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Dernières commandes') }}</h2>
            <div class="mt-3 divide-y divide-slate-100 text-sm">
                @forelse ($orders as $order)
                    <a href="{{ route('platform.customer.orders.show', $order) }}" class="flex justify-between gap-2 py-2 hover:text-indigo-600">
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
                    <p class="py-2 text-slate-500">{{ __('Aucun domaine.') }} <a href="{{ route('platform.customer.domains') }}" class="font-medium text-indigo-600 hover:underline">{{ __('Trouver un domaine') }}</a></p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
