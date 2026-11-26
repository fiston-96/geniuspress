@extends('platform.layouts.console')
@section('title', __('Paiements'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Paiements') }}</h1>
    <p class="mt-1 max-w-3xl text-sm text-slate-500">{{ __('Prestataire qui encaisse les commandes des clients. Monerails : Mobile Money dans la devise du pays du client, converti au taux réglé dans Fournisseurs quand l’offre est dans une autre devise.') }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Prestataire et clés') }}</h2>
            <p class="mt-3 flex flex-wrap gap-2 text-sm">
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ __('Actif : :name', ['name' => $active === 'monerails' ? 'Monerails' : __('test (aucun argent)')]) }}</span>
                @if ($mode === 'live')<span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">{{ __('Clé de production') }}</span>
                @elseif ($mode === 'test')<span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ __('Clé de test') }}</span>@endif
                @if ($active === 'monerails' && ! $usable)<span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">{{ __('Paiements indisponibles : clé absente ou clé de test en production') }}</span>@endif
            </p>
            <form method="POST" action="{{ route('console.payments.settings') }}" class="mt-4 space-y-4">@csrf @method('PUT')
                <div>
                    <label for="gateway" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Prestataire actif') }}</label>
                    <select id="gateway" name="gateway" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="monerails" @selected($active === 'monerails')>Monerails (Mobile Money)</option>
                        <option value="sandbox" @selected($active === 'sandbox')>{{ __('Test (aucun argent, refusé en production)') }}</option>
                    </select>
                </div>
                <x-field name="monerails_key" type="password" :label="__('Clé API Monerails')" autocomplete="new-password"
                    :hint="$keyHint ? __('Clé enregistrée : :hint. Laissez vide pour la garder.', ['hint' => $keyHint]) : __('Tableau de bord Monerails → Développeurs → Clés API (mr_live_…).')" />
                @if ($keyHint)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="monerails_key_remove" value="1"> {{ __('Supprimer la clé enregistrée') }}</label>@endif
                <x-field name="monerails_webhook_secret" type="password" :label="__('Secret de notification (webhook)')" autocomplete="new-password"
                    :hint="$secretHint ? __('Secret enregistré : :hint. Laissez vide pour le garder.', ['hint' => $secretHint]) : __('Affiché une seule fois par Monerails à la création du point de notification (whsec_…).')" />
                @if ($secretHint)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="monerails_webhook_secret_remove" value="1"> {{ __('Supprimer le secret enregistré') }}</label>@endif
                <fieldset>
                    <legend class="mb-1 text-sm font-medium text-slate-700">{{ __('Paiement en dollars (USD), en plus de la devise du pays') }}</legend>
                    <p class="mb-2 text-xs text-slate-500">{{ __('Cochez un pays seulement quand Monerails y annonce l’ouverture de l’USD (en cours en RD Congo) : en attendant, il refuse l’USD et les clients paient dans la devise du pays.') }}</p>
                    <div class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                        @foreach ($countryNames as $code => $label)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="usd[]" value="{{ $code }}" @checked(in_array($code, $usdCountries, true))> {{ $label }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Enregistrer') }}</button>
            </form>
            @if ($mode)
                <form method="POST" action="{{ route('console.payments.test') }}" class="mt-4 border-t border-slate-100 pt-4">@csrf
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50">{{ __('Tester la connexion et lire les soldes') }}</button>
                </form>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Mise en service chez Monerails') }}</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-700">
                <li>{{ __('Créez une clé API de production (Développeurs → Clés API) et collez-la ici.') }}</li>
                <li>{{ __('Créez un point de notification (Développeurs → Webhooks, préréglage « Paiements ») avec cette adresse :') }}
                    <code class="mt-1 block break-all rounded bg-slate-100 px-2 py-1 text-xs">{{ $webhookUrl }}</code></li>
                <li>{{ __('Collez ici le secret « whsec_… » qu’il affiche une seule fois.') }}</li>
                <li>{{ __('Enregistrez un compte de reversement par devise (Paramètres → Reversements) pour récupérer l’argent encaissé.') }}</li>
                <li>{{ __('Choisissez Monerails comme prestataire actif, puis testez la connexion.') }}</li>
            </ol>
            @if ($missingRates || $missingLocal)
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                    <p class="font-medium">{{ __('Taux de change manquants') }}</p>
                    <p class="mt-1">{{ __('Une offre en :currencies ne peut être payée en Mobile Money que si le taux de sa devise et celui de la devise du client sont réglés.', ['currencies' => implode(', ', $missingRates ?: ['USD'])]) }}
                        @if ($missingLocal){{ __('Sans taux : :list.', ['list' => implode(', ', $missingLocal)]) }}@endif</p>
                    <a href="{{ route('console.suppliers.index') }}" class="mt-2 inline-block font-medium underline">{{ __('Régler les taux de change') }}</a>
                </div>
            @endif
        </section>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">{{ __('Derniers paiements') }}</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="py-2 pr-4">{{ __('Commande') }}</th><th class="py-2 pr-4">{{ __('Montant payé') }}</th><th class="py-2 pr-4">{{ __('Moyen') }}</th><th class="py-2 pr-4">{{ __('État') }}</th><th class="py-2">{{ __('Date') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recent as $p)
                        <tr>
                            <td class="py-2 pr-4">@if ($p->order)<a href="{{ route('console.orders.show', $p->order) }}" class="font-medium hover:text-brand-600">{{ $p->order->number }}</a>@endif</td>
                            <td class="py-2 pr-4">{{ Money::format($p->amount, $p->currency) }}@if ($p->original_currency)<span class="block text-xs text-slate-500">{{ __('pour :amount', ['amount' => Money::format((int) $p->original_amount, $p->original_currency)]) }}</span>@endif</td>
                            <td class="py-2 pr-4">{{ Labels::method($p->method) }}@if ($p->operator) · {{ $p->operator }}@endif @if ($p->country)· {{ $p->country }}@endif</td>
                            <td class="py-2 pr-4">{{ Labels::status($p->status) }}</td>
                            <td class="py-2 text-slate-500">{{ $p->created_at?->isoFormat('lll') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-slate-500">{{ __('Aucun paiement.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
