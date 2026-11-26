@extends('platform.layouts.console')
@section('title', __('Fournisseurs'))
@section('content')
@php use App\Platform\Labels; use App\Platform\Money; @endphp
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Fournisseurs') }}</h1>
    <p class="mt-1 max-w-3xl text-sm text-slate-500">{{ __('Domaines, hébergement web, mail pro et stockage achetés chez LWS pour les clients, au prix LWS, revendus avec votre marge. Le nom du fournisseur n’apparaît jamais dans l’espace client.') }}</p>

    {{-- File de livraison : travail à faire à la main chez le fournisseur. --}}
    <section id="livraisons" class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">{{ __('À livrer') }} @if ($queue->isNotEmpty())<span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ $queue->count() }}</span>@endif</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Services payés que l’API ne sait pas créer (mail pro, stockage, offres manuelles) et renouvellements : faites-les dans l’espace LWS, puis marquez-les comme livrés. N’écrivez jamais de mot de passe dans le message au client.') }}</p>
        <div class="mt-4 space-y-4">
            @forelse ($queue as $service)
                <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4 text-sm">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <p class="font-medium">{{ $service->pending_action === 'renew' ? __('Renouveler') : __('Créer') }} · {{ $service->label() }}</p>
                        <p class="text-xs text-slate-500">{{ $service->provider === 'lws' ? 'LWS' : __('Manuel') }} @if ($service->provider_ref)· {{ $service->provider_ref }}@endif · {{ __('payé le :date', ['date' => $service->order?->paid_at?->isoFormat('LL') ?? '—']) }}</p>
                    </div>
                    <p class="mt-1 text-slate-600"><a href="{{ route('console.customers.show', $service->customer) }}" class="font-medium hover:text-brand-600">{{ $service->customer->name }}</a> · {{ $service->customer->email }} @if ($service->customer->phone)· {{ $service->customer->phone }}@endif</p>
                    @if ($service->request)<p class="mt-2 whitespace-pre-line rounded bg-white px-3 py-2 text-slate-700">{{ $service->request }}</p>@endif
                    <div class="mt-3 grid gap-3 lg:grid-cols-[1fr_auto]">
                        <form method="POST" action="{{ route('console.suppliers.services.done', $service) }}" class="flex flex-wrap items-end gap-2">@csrf
                            <input name="note" placeholder="{{ __('Message au client (facultatif)') }}" aria-label="{{ __('Message au client') }}" class="min-w-64 flex-1 rounded-lg border border-slate-300 px-3 py-2">
                            <label class="text-xs text-slate-500">{{ __('Fin chez le fournisseur') }}<input type="date" name="expires_at" value="{{ $service->expires_at?->format('Y-m-d') }}" class="mt-1 block rounded-lg border border-slate-300 px-3 py-1.5 text-sm"></label>
                            <button class="rounded-lg bg-emerald-600 px-3 py-2 font-semibold text-white hover:bg-emerald-500">{{ __('Marquer comme livré') }}</button>
                        </form>
                        <form method="POST" action="{{ route('console.suppliers.services.fail', $service) }}" class="flex items-end gap-2">@csrf
                            <input name="reason" required placeholder="{{ __('Raison de l’échec') }}" aria-label="{{ __('Raison de l’échec') }}" class="rounded-lg border border-slate-300 px-3 py-2">
                            <button class="rounded-lg border border-red-200 px-3 py-2 font-medium text-red-700 hover:bg-red-50">{{ __('Échec') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">{{ __('Rien à livrer pour le moment.') }}</p>
            @endforelse
        </div>
        @if ($recent->isNotEmpty())
            <details class="mt-4 text-sm"><summary class="cursor-pointer text-slate-600">{{ __('Services récents') }}</summary>
                <ul class="mt-2 divide-y divide-slate-100">
                    @foreach ($recent as $service)
                        <li class="flex flex-wrap justify-between gap-2 py-1.5"><span>{{ $service->label() }} · {{ $service->customer->name }}</span><span class="text-slate-500">{{ Labels::status($service->status) }} @if ($service->expires_at)· {{ __('jusqu’au :date', ['date' => $service->expires_at->isoFormat('LL')]) }}@endif</span></li>
                    @endforeach
                </ul>
            </details>
        @endif
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Connexion à LWS') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Clé API de l’onglet « Api LWS » de votre espace client LWS. Autorisez-y l’adresse IP du serveur. La clé est chiffrée et jamais réaffichée.') }}</p>
            <p class="mt-3 text-sm">
                @if ($client->configured())
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">{{ __('Configurée') }}</span>
                    @if ($client->testMode())<span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ __('Mode test : rien n’est acheté') }}</span>@endif
                @else
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ __('Non configurée') }}</span>
                @endif
            </p>
            <form method="POST" action="{{ route('console.suppliers.settings') }}" class="mt-4 space-y-4">@csrf @method('PUT')
                <div class="grid gap-4 md:grid-cols-2">
                    <x-field name="lws_login" :label="__('Identifiant LWS (le nombre seul)')" :value="$client->login()" inputmode="numeric" autocomplete="off" />
                    <x-field name="lws_key" type="password" :label="__('Clé API')" :hint="$keyHint ? __('Clé enregistrée : :hint. Laissez vide pour la garder.', ['hint' => $keyHint]) : null" autocomplete="new-password" />
                </div>
                @if ($keyHint)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="lws_key_remove" value="1"> {{ __('Supprimer la clé enregistrée') }}</label>@endif
                <label class="flex items-start gap-2 text-sm"><input type="hidden" name="lws_test" value="0"><input type="checkbox" name="lws_test" value="1" @checked($client->testMode()) class="mt-0.5"> <span>{{ __('Mode test (X-Test-Mode) : les demandes sont simulées par LWS, rien n’est créé ni facturé. Décochez seulement pour vendre réellement.') }}</span></label>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-field name="low_balance" :label="__('Alerte sous ce solde, en €')" :value="$lowBalance" required />
                    <x-field name="markup" :label="__('Marge conseillée, en %')" :value="$pricing->markup()" required />
                </div>
                <fieldset>
                    <legend class="mb-1.5 text-sm font-medium text-slate-700">{{ __('Taux de change : valeur de 1 € dans chaque devise (prix conseillés, et paiement Mobile Money d’une offre dans la devise du client)') }}</legend>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @foreach ($currencies as $currency)
                            <label class="text-xs text-slate-500">{{ $currency }}<input name="rates[{{ $currency }}]" value="{{ old('rates.'.$currency, $pricing->rates()[$currency] ?? '') }}" inputmode="decimal" class="mt-1 block w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm text-slate-900"></label>
                        @endforeach
                    </div>
                </fieldset>
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Enregistrer') }}</button>
            </form>
            @if ($client->configured())
                <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                    <form method="POST" action="{{ route('console.suppliers.test') }}">@csrf<button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50">{{ __('Tester la connexion et lire le solde') }}</button></form>
                    <form method="POST" action="{{ route('console.suppliers.sync') }}">@csrf<button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50">{{ __('Mettre à jour les coûts LWS') }}</button></form>
                </div>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Offres partenaires') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Hébergement web, mail pro et stockage : créez-les dans Offres (type et fournisseur), elles apparaissent dans l’espace client et sur la vitrine.') }}</p>
            <div class="mt-3 divide-y divide-slate-100 text-sm">
                @forelse ($plans as $plan)
                    @php($margin = $pricing->margin($plan->price, $plan->currency, $plan->cost, $plan->cost_currency))
                    <div class="flex flex-wrap items-center justify-between gap-2 py-2">
                        <span><a href="{{ route('console.plans.edit', $plan) }}" class="font-medium hover:text-brand-600">{{ $plan->label() }}</a> <span class="text-xs text-slate-500">{{ Labels::kind($plan->kind) }} · {{ $plan->provider === 'lws' ? 'LWS' : __('Manuel') }}@if (! $plan->active) · {{ __('hors vente') }}@endif</span></span>
                        <span class="text-right">{{ Money::format($plan->price, $plan->currency) }} <span class="text-xs text-slate-500">{{ Labels::period($plan->period) }}</span>
                            <span class="block text-xs {{ $margin !== null && $margin < 0 ? 'text-red-600' : 'text-slate-500' }}">{{ $plan->cost !== null ? __('coût :cost', ['cost' => Money::format($plan->cost, $plan->cost_currency)]) : __('coût inconnu') }}@if ($margin !== null) · {{ __('marge :margin', ['margin' => Money::format($margin, $plan->currency)]) }}@endif</span></span>
                    </div>
                @empty
                    <p class="py-2 text-slate-500">{{ __('Aucune offre partenaire.') }}</p>
                @endforelse
            </div>
            <a href="{{ route('console.plans.create') }}" class="mt-3 inline-block text-sm font-medium text-brand-600 hover:underline">{{ __('Nouvelle offre') }} →</a>
        </section>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="font-semibold">{{ __('Extensions de domaine : coûts et marges') }}</h2>
            <form method="POST" action="{{ route('console.suppliers.tlds.apply') }}">@csrf<button class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium hover:bg-slate-50">{{ __('Appliquer les prix conseillés') }}</button></form>
        </div>
        <p class="mt-1 text-sm text-slate-500">{{ __('Ajoutez ou retirez des extensions dans Tarifs ; leurs coûts viennent de LWS (euros HT).') }}</p>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="py-2 pr-4">{{ __('Extension') }}</th><th class="py-2 pr-4">{{ __('Coût achat / renouvellement') }}</th><th class="py-2 pr-4">{{ __('Prix de vente') }}</th><th class="py-2 pr-4">{{ __('Prix conseillé') }}</th><th class="py-2">{{ __('Marge à l’achat') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tlds as $tld)
                        @php($margin = $pricing->margin($tld->register_price, $tld->currency, $tld->cost_register, $tld->cost_currency))
                        @php($suggested = $pricing->suggested($tld->cost_register, $tld->cost_currency, $tld->currency))
                        <tr>
                            <td class="py-2 pr-4 font-medium">.{{ $tld->tld }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $tld->cost_register !== null ? Money::format($tld->cost_register, $tld->cost_currency).' / '.Money::format((int) $tld->cost_renew, $tld->cost_currency) : '—' }}</td>
                            <td class="py-2 pr-4">{{ Money::format($tld->register_price, $tld->currency) }} / {{ Money::format($tld->renew_price, $tld->currency) }}</td>
                            <td class="py-2 pr-4 text-slate-600">{{ $suggested !== null ? Money::format($suggested, $tld->currency) : '—' }}</td>
                            <td class="py-2 {{ $margin !== null && $margin < 0 ? 'font-semibold text-red-600' : '' }}">{{ $margin !== null ? Money::format($margin, $tld->currency) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-slate-500">{{ __('Aucune extension en vente.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
