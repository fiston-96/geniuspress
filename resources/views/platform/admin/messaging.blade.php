@extends('platform.layouts.console')
@section('title', __('Messagerie'))
@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Messagerie') }}</h1>
    <p class="mt-1 max-w-3xl text-sm text-slate-500">{{ __('SMS envoyés aux clients pour les messages importants (paiement reçu, service prêt, site créé, fin d’essai, retrait envoyé), en plus de la cloche et de l’e-mail. Le client peut les refuser depuis son espace.') }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">Africa’s Talking</h2>
            <p class="mt-3 flex flex-wrap gap-2 text-sm">
                <span @class(['rounded-full px-2 py-0.5 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $enabled, 'bg-slate-100 text-slate-600' => ! $enabled])>{{ $enabled ? __('SMS activés') : __('SMS désactivés') }}</span>
                @if ($at->configured())<span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">{{ $at->sandbox() ? __('Bac à sable : aucun vrai SMS') : __('Production') }}</span>@endif
            </p>
            <form method="POST" action="{{ route('console.messaging.settings') }}" class="mt-4 space-y-4">@csrf @method('PUT')
                <label class="flex items-start gap-2 text-sm"><input type="hidden" name="enabled" value="0"><input type="checkbox" name="enabled" value="1" @checked($enabled) class="mt-0.5"> <span>{{ __('Envoyer les SMS de la plateforme') }}</span></label>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-field name="username" :label="__('Nom d’utilisateur de l’application')" :value="$at->username()" :hint="__('« sandbox » pour le bac à sable.')" autocomplete="off" />
                    <x-field name="sender" :label="__('Expéditeur (sender ID)')" :value="$at->senderId()" maxlength="11" :hint="__('Enregistré et validé dans votre compte Africa’s Talking (11 caractères au plus).')" />
                </div>
                <x-field name="api_key" type="password" :label="__('Clé API')" autocomplete="new-password" :hint="$keyHint ? __('Clé enregistrée : :hint. Laissez vide pour la garder.', ['hint' => $keyHint]) : __('Compte Africa’s Talking → Settings → API Key.')" />
                @if ($keyHint)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="api_key_remove" value="1"> {{ __('Supprimer la clé enregistrée') }}</label>@endif
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Enregistrer') }}</button>
            </form>
            @if ($at->configured())
                <form method="POST" action="{{ route('console.messaging.test') }}" class="mt-4 flex flex-wrap items-end gap-2 border-t border-slate-100 pt-4">@csrf
                    <x-field name="phone" type="tel" :label="__('Envoyer un SMS d’essai au')" placeholder="+221…" />
                    <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50">{{ __('Envoyer') }}</button>
                </form>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Pays couverts') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Africa’s Talking ne couvre pas encore la RD Congo ni l’Afrique centrale : ces clients gardent la cloche et l’e-mail, en attendant un second prestataire.') }}</p>
            <p class="mt-3 text-sm text-slate-700">{{ implode(' · ', $countries) }}</p>
            <h3 class="mt-5 text-sm font-semibold">{{ __('Ce mois-ci') }}</h3>
            <p class="mt-1 text-sm text-slate-700">{{ __(':sent envoyés · :failed en échec · :skipped hors couverture', ['sent' => $month['sent'] ?? 0, 'failed' => $month['failed'] ?? 0, 'skipped' => $month['skipped'] ?? 0]) }}</p>
        </section>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
        <h2 class="font-semibold">{{ __('Derniers SMS') }}</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="py-2 pr-4">{{ __('Date') }}</th><th class="py-2 pr-4">{{ __('Destinataire') }}</th><th class="py-2 pr-4">{{ __('Message') }}</th><th class="py-2 pr-4">{{ __('État') }}</th><th class="py-2">{{ __('Coût') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($messages as $m)
                        <tr>
                            <td class="py-2 pr-4 text-slate-500">{{ $m->created_at?->isoFormat('lll') }}</td>
                            <td class="py-2 pr-4">{{ $m->customer?->name ?? '—' }} <span class="font-mono text-xs text-slate-500">{{ $m->to }}</span></td>
                            <td class="py-2 pr-4 text-slate-600">{{ $m->kind }}</td>
                            <td class="py-2 pr-4">
                                <span @class(['rounded-full px-2 py-0.5 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $m->status === 'sent', 'bg-red-100 text-red-700' => $m->status === 'failed', 'bg-slate-100 text-slate-600' => $m->status === 'skipped'])>{{ ['sent' => __('Envoyé'), 'failed' => __('Échec'), 'skipped' => __('Non envoyé')][$m->status] ?? $m->status }}</span>
                                @if ($m->error)<span class="block text-xs text-slate-500">{{ $m->error }}</span>@endif
                            </td>
                            <td class="py-2 text-slate-600">{{ $m->cost ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-slate-500">{{ __('Aucun SMS pour le moment.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
