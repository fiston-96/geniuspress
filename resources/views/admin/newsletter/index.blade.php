@extends('layouts.admin')
@section('title', __('Newsletter'))
@section('content')
@php
    $statusLabels = ['subscribed' => __('Inscrit'), 'pending' => __('À confirmer'), 'unsubscribed' => __('Désinscrit')];
    $campaignLabels = ['draft' => __('Brouillon'), 'sending' => __('Envoi en cours'), 'sent' => __('Envoyée')];
    $input = 'rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20';
@endphp
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('Newsletter') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">{{ __('Les visiteurs s’inscrivent avec le widget « Newsletter » et confirment leur adresse par e-mail. Chaque envoi contient un lien de désinscription.') }}</p>
        </div>
        <a href="{{ route('admin.newsletter.campaigns.create') }}" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('+ Nouvelle campagne') }}</a>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach (['subscribed' => __('Abonnés inscrits'), 'pending' => __('En attente de confirmation'), 'unsubscribed' => __('Désinscrits')] as $key => $label)
            <a href="{{ route('admin.newsletter.index', ['statut' => $key]) }}" @class(['rounded-xl border bg-white p-5 transition hover:border-brand-300', 'border-brand-400 ring-2 ring-brand-500/15' => $status === $key, 'border-slate-200' => $status !== $key])>
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-2xl font-semibold">{{ number_format((int) ($counts[$key] ?? 0), 0, ',', ' ') }}</p>
            </a>
        @endforeach
    </div>

    {{-- Campagnes --}}
    <h2 class="mt-10 text-lg font-semibold">{{ __('Campagnes') }}</h2>
    @if ($pending)
        <p class="mt-2 rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800">{{ trans_choice('{1} 1 e-mail reste à envoyer.|[2,*] :count e-mails restent à envoyer, au rythme de :rate par minute.', $pending, ['rate' => $perMinute]) }}</p>
    @endif
    @if ($campaigns->isEmpty())
        <p class="mt-3 rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">{{ __('Aucune campagne pour l’instant. Rédigez la première : quelques nouvelles, un lien vers votre dernier article…') }}</p>
    @else
        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-4 py-3">{{ __('Objet') }}</th><th class="px-4 py-3">{{ __('État') }}</th><th class="px-4 py-3">{{ __('Envoyés') }}</th><th class="px-4 py-3">{{ __('Date') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($campaigns as $c)
                        <tr>
                            <td class="px-4 py-3"><a class="font-medium text-brand-700 hover:underline" href="{{ route('admin.newsletter.campaigns.edit', $c) }}">{{ $c->subject }}</a></td>
                            <td class="px-4 py-3"><span @class(['rounded-full px-2 py-0.5 text-xs font-medium', 'bg-slate-100 text-slate-700' => $c->status === 'draft', 'bg-brand-50 text-brand-700' => $c->status === 'sending', 'bg-emerald-50 text-emerald-700' => $c->status === 'sent'])>{{ $campaignLabels[$c->status] ?? $c->status }}</span></td>
                            <td class="px-4 py-3 text-slate-600">@if ($c->status !== 'draft'){{ $c->sent_count }} / {{ $c->recipients }}@if ($c->failed_count) · <span class="text-red-600">{{ trans_choice('{1} 1 échec|[2,*] :count échecs', $c->failed_count) }}</span>@endif @else — @endif</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ ($c->sent_at ?? $c->started_at ?? $c->updated_at)->translatedFormat('j M Y, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Abonnés --}}
    <div class="mt-10 flex flex-wrap items-end justify-between gap-3">
        <h2 class="text-lg font-semibold">{{ __('Abonnés') }}</h2>
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="{{ route('admin.newsletter.index') }}" class="flex gap-2">
                @if ($status)<input type="hidden" name="statut" value="{{ $status }}">@endif
                <label for="q" class="sr-only">{{ __('Rechercher un abonné') }}</label>
                <input id="q" name="q" value="{{ $search }}" placeholder="{{ __('Rechercher un abonné…') }}" class="{{ $input }} w-56">
            </form>
            @if ($status || $search !== '')<a href="{{ route('admin.newsletter.index') }}" class="text-sm text-slate-500 hover:underline">{{ __('Tout afficher') }}</a>@endif
            <a href="{{ route('admin.newsletter.export') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">{{ __('Exporter (CSV)') }}</a>
        </div>
    </div>
    @if ($subscribers->isEmpty())
        <p class="mt-3 rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">{{ __('Aucun abonné. Ajoutez le widget « Newsletter » à une page du site pour recevoir des inscriptions.') }}</p>
    @else
        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-4 py-3">{{ __('E-mail') }}</th><th class="px-4 py-3">{{ __('État') }}</th><th class="px-4 py-3">{{ __('Source') }}</th><th class="px-4 py-3">{{ __('Date') }}</th><th class="px-4 py-3"><span class="sr-only">{{ __('Actions') }}</span></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($subscribers as $s)
                        <tr>
                            <td class="px-4 py-3">{{ $s->email }}@if ($s->name)<br><span class="text-xs text-slate-500">{{ $s->name }}</span>@endif</td>
                            <td class="px-4 py-3 text-slate-600">{{ $statusLabels[$s->status] ?? $s->status }} · {{ strtoupper($s->locale) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s->source ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ ($s->confirmed_at ?? $s->created_at)->translatedFormat('j M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.newsletter.subscribers.destroy', $s) }}" onsubmit="return confirm(@js(__('Supprimer définitivement cet abonné ?')))">@csrf @method('DELETE')
                                    <button class="text-sm text-red-600 hover:underline">{{ __('Supprimer') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $subscribers->links() }}</div>
    @endif

    {{-- Rythme d'envoi --}}
    <form method="POST" action="{{ route('admin.newsletter.settings') }}" class="mt-10 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-5">
        @csrf @method('PUT')
        <div>
            <label for="newsletter_per_minute" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('E-mails envoyés par minute') }}</label>
            <input id="newsletter_per_minute" name="newsletter_per_minute" type="number" min="1" max="600" value="{{ $perMinute }}" class="{{ $input }} w-28">
        </div>
        <p class="max-w-md flex-1 text-xs text-slate-500">{{ __('Restez sous la limite de votre fournisseur d’e-mails (souvent quelques centaines par heure pour une boîte mail classique), sinon les envois seront refusés.') }}</p>
        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">{{ __('Enregistrer') }}</button>
    </form>
@endsection
