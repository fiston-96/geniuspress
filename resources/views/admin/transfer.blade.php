@extends('layouts.admin')
@section('title', __('Sauvegarde'))
@section('content')
@php
    $kindLabels = ['auto' => __('Automatique'), 'manuelle' => __('Manuelle'), 'avant-restauration' => __('Avant restauration')];
    $size = fn (int $b) => $b >= 1048576 ? number_format($b / 1048576, 1, ',', ' ').' Mo' : number_format($b / 1024, 0, ',', ' ').' Ko';
@endphp
    <h1 class="text-2xl font-bold tracking-tight text-night">{{ __('Sauvegarde et restauration') }}</h1>
    <p class="mt-1 max-w-3xl text-sm text-slate-600">{{ __('Vos données vous appartiennent. Les sauvegardes complètes remettent ce site exactement dans un état enregistré ; l’export sert à déplacer le contenu vers une autre installation.') }}</p>

    @if (session('warnings'))
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-semibold">{{ __('Points à vérifier') }}</p>
            <ul class="mt-1 list-disc pl-5">@foreach (session('warnings') as $warning)<li>{{ $warning }}</li>@endforeach</ul>
        </div>
    @endif
    @error('backup')<div class="mt-6 rounded-xl border border-red-200 bg-white px-4 py-3 text-sm text-red-700">{{ $message }}</div>@enderror

    {{-- Sauvegardes complètes --}}
    <section class="mt-6 rounded-2xl border border-line bg-white">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-line px-6 py-5">
            <div>
                <h2 class="flex items-center gap-2 text-lg font-bold tracking-tight text-night"><x-icon name="hard-drive-download" class="h-5 w-5 text-brand-600" />{{ __('Sauvegardes complètes') }}</h2>
                <p class="mt-0.5 text-sm text-slate-500">{{ __('Toute la base de données (pages, articles, comptes, messages, réglages) et tous les fichiers envoyés. Gardées sur ce serveur, jamais publiques.') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.backups.store') }}">@csrf
                <button class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700"><x-icon name="plus" class="h-4 w-4" stroke="2.4" />{{ __('Sauvegarder maintenant') }}</button>
            </form>
        </div>

        <ul class="divide-y divide-line">
            @forelse ($backups as $b)
                <li class="flex flex-wrap items-center gap-4 px-6 py-3.5">
                    <x-icon name="archive-restore" class="h-5 w-5 text-slate-400" />
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-night">{{ $b['date']->translatedFormat('j F Y à H:i') }}</p>
                        <p class="text-xs text-slate-500">{{ $kindLabels[$b['kind']] ?? $b['kind'] }} · {{ $size($b['size']) }} · {{ $b['date']->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('admin.backups.download', $b['name']) }}" class="grid h-9 w-9 place-items-center rounded-lg text-slate-600 hover:bg-cream" title="{{ __('Télécharger') }}" aria-label="{{ __('Télécharger') }}"><x-icon name="hard-drive-download" class="h-4 w-4" /></a>
                    <form method="POST" action="{{ route('admin.backups.restore', $b['name']) }}" onsubmit="return confirm(@js(__('Restaurer cette sauvegarde ? Tout le site revient à cet état (une copie de l’état actuel est gardée), puis vous devrez vous reconnecter.')))">@csrf
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-line px-3 py-2 text-sm font-semibold text-night hover:bg-cream" title="{{ __('Restaurer') }}"><x-icon name="archive-restore" class="h-4 w-4" />{{ __('Restaurer') }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.backups.destroy', $b['name']) }}" onsubmit="return confirm(@js(__('Supprimer définitivement cette sauvegarde ?')))">@csrf @method('DELETE')
                        <button class="grid h-9 w-9 place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" title="{{ __('Supprimer') }}" aria-label="{{ __('Supprimer') }}"><x-icon name="trash-2" class="h-4 w-4" /></button>
                    </form>
                </li>
            @empty
                <li class="px-6 py-10 text-center text-sm text-slate-500">{{ __('Aucune sauvegarde pour l’instant.') }}</li>
            @endforelse
        </ul>

        <div class="grid gap-6 border-t border-line px-6 py-5 md:grid-cols-2">
            <form method="POST" action="{{ route('admin.backups.settings') }}" class="space-y-3">@csrf @method('PUT')
                <h3 class="text-sm font-semibold text-night">{{ __('Sauvegarde automatique') }}</h3>
                <div class="flex flex-wrap items-end gap-3">
                    <label class="text-xs text-slate-600">{{ __('Fréquence') }}
                        <select name="backup_frequency" class="mt-1 block rounded-lg border border-line px-3 py-2 text-sm">
                            @foreach (['off' => __('Jamais'), 'daily' => __('Chaque jour'), 'weekly' => __('Chaque semaine')] as $v => $l)<option value="{{ $v }}" @selected($backupFrequency === $v)>{{ $l }}</option>@endforeach
                        </select>
                    </label>
                    <label class="text-xs text-slate-600">{{ __('Garder les') }}
                        <input type="number" name="backup_keep" min="1" max="60" value="{{ $backupKeep }}" class="mt-1 block w-20 rounded-lg border border-line px-3 py-2 text-sm">
                    </label>
                    <span class="pb-2 text-xs text-slate-500">{{ __('dernières') }}</span>
                    <button class="rounded-lg border border-line px-3 py-2 text-sm font-semibold text-night hover:bg-cream">{{ __('Enregistrer') }}</button>
                </div>
            </form>
            <form method="POST" action="{{ route('admin.backups.upload') }}" enctype="multipart/form-data" class="space-y-3" onsubmit="return confirm(@js(__('Restaurer cette sauvegarde ? Tout le site revient à cet état (une copie de l’état actuel est gardée), puis vous devrez vous reconnecter.')))">@csrf
                <h3 class="text-sm font-semibold text-night">{{ __('Restaurer depuis un fichier') }}</h3>
                <div class="flex flex-wrap items-center gap-3">
                    <input type="file" name="backup" accept=".zip,application/zip" required class="min-w-0 flex-1 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-cream file:px-3 file:py-2 file:text-sm file:font-medium">
                    <button class="rounded-lg border border-line px-3 py-2 text-sm font-semibold text-night hover:bg-cream">{{ __('Restaurer') }}</button>
                </div>
                <p class="text-xs text-slate-500">{{ __('Sauvegarde complète de ce site (fichier « geniuspress-sauvegarde-… .zip »). Taille maximale acceptée par ce serveur : :size Mo.', ['size' => number_format($maxUpload / 1048576, 0, ',', ' ')]) }}</p>
            </form>
        </div>
    </section>

    {{-- Tâches planifiées --}}
    <section class="mt-6 rounded-2xl border border-line bg-white">
        <div class="border-b border-line px-6 py-5">
            <h2 class="flex items-center gap-2 text-lg font-bold tracking-tight text-night"><x-icon name="clock" class="h-5 w-5 text-brand-600" />{{ __('Tâches planifiées') }}</h2>
            <p class="mt-0.5 text-sm text-slate-500">{{ __('Envoi de la newsletter, sauvegardes automatiques… Elles fonctionnent même chez un hébergeur mutualisé, sans cron.') }}</p>
        </div>
        <div class="grid gap-6 px-6 py-5 lg:grid-cols-2">
            <div class="space-y-3 text-sm">
                <p class="flex items-center gap-2">
                    <span @class(['h-2 w-2 rounded-full', 'bg-emerald-500' => $cron['last'] && $cron['last']->gt(now()->subMinutes(30)), 'bg-gold' => ! ($cron['last'] && $cron['last']->gt(now()->subMinutes(30)))])></span>
                    @if ($cron['last'])
                        {{ __('Dernier passage : :when', ['when' => $cron['last']->diffForHumans()]) }}
                        <span class="text-slate-500">· {{ ['cli' => __('cron du serveur'), 'visits' => __('visites du site'), 'url' => __('adresse secrète'), 'manual' => __('lancement manuel')][$cron['source']] ?? $cron['source'] }}</span>
                    @else
                        {{ __('Pas encore lancées.') }}
                    @endif
                </p>
                @if ($cron['cli'])
                    <p class="text-slate-600">{{ __('Un cron du serveur lance les tâches : les visites n’ont pas à le faire.') }}</p>
                @endif
                <form method="POST" action="{{ route('admin.backups.cron') }}" class="space-y-3">@csrf @method('PUT')
                    <label class="flex items-start gap-2">
                        <input type="hidden" name="cron_visits" value="0"><input type="checkbox" name="cron_visits" value="1" @checked($cron['visits']) class="mt-0.5 rounded border-slate-300">
                        <span>{{ __('Lancer les tâches grâce aux visites du site (au plus une fois par minute, après l’affichage de la page).') }}</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <button name="action" value="save" class="rounded-lg border border-line px-3 py-2 font-semibold text-night hover:bg-cream">{{ __('Enregistrer') }}</button>
                        <button name="action" value="run" class="inline-flex items-center gap-1.5 rounded-lg border border-line px-3 py-2 font-semibold text-night hover:bg-cream"><x-icon name="zap" class="h-4 w-4" />{{ __('Lancer maintenant') }}</button>
                    </div>
                </form>
            </div>
            <div class="space-y-3 rounded-xl bg-cream p-4 text-sm">
                <p class="font-semibold text-night">{{ __('Pour plus de régularité (sites peu visités)') }}</p>
                <p class="text-slate-600">{{ __('Faites appeler cette adresse secrète toutes les 5 minutes par le cron de votre hébergeur (cPanel, Plesk…) ou par un service gratuit comme cron-job.org :') }}</p>
                <input readonly value="{{ $cron['url'] }}" onclick="this.select()" aria-label="{{ __('Adresse secrète des tâches planifiées') }}" class="block w-full rounded-lg border border-line bg-white px-3 py-2 font-mono text-xs">
                <p class="text-xs text-slate-500">{{ __('Commande à coller dans le cron de l’hébergeur :') }}</p>
                <input readonly value="wget -q -O /dev/null &quot;{{ $cron['url'] }}&quot;" onclick="this.select()" aria-label="{{ __('Commande cron') }}" class="block w-full rounded-lg border border-line bg-white px-3 py-2 font-mono text-xs">
                <form method="POST" action="{{ route('admin.backups.cron') }}" onsubmit="return confirm(@js(__('Créer une nouvelle adresse ? L’ancienne cessera de fonctionner.')))">@csrf @method('PUT')
                    <button name="action" value="renew" class="text-xs font-medium text-brand-700 hover:underline">{{ __('Créer une nouvelle adresse secrète') }}</button>
                </form>
            </div>
        </div>
    </section>

    {{-- Export / import (déplacer le contenu) --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="font-bold tracking-tight text-night">{{ __('Exporter le site') }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ __('Un fichier zip avec toutes les pages (traductions et brouillons compris), les médias et les réglages.') }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ __('Ne contient ni les comptes utilisateurs, ni la clé API, ni aucun mot de passe.') }}</p>
            <a href="{{ route('admin.transfer.export') }}" class="mt-4 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Télécharger l’export') }}</a>
        </section>

        <section class="rounded-2xl border border-line bg-white p-6">
            <h2 class="font-bold tracking-tight text-night">{{ __('Importer un site') }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ __('Fichier zip exporté par GeniusPress. Taille maximale acceptée par ce serveur : :size Mo.', ['size' => number_format($maxUpload / 1048576, 0, ',', ' ')]) }}</p>

            <form method="POST" action="{{ route('admin.transfer.import') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <input type="file" name="archive" accept=".zip,application/zip" required class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-cream file:px-3 file:py-2 file:text-sm file:font-medium">
                @error('archive')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

                <fieldset class="space-y-2 text-sm">
                    <label class="flex gap-2"><input type="radio" name="mode" value="merge" class="mt-0.5" checked>
                        <span><span class="font-medium">{{ __('Ajouter au site actuel') }}</span><span class="block text-xs text-slate-500">{{ __('Les adresses déjà prises reçoivent un suffixe ; votre page d’accueil est conservée.') }}</span></span></label>
                    <label class="flex gap-2"><input type="radio" name="mode" value="replace" class="mt-0.5">
                        <span><span class="font-medium">{{ __('Remplacer les pages de même adresse') }}</span><span class="block text-xs text-slate-500">{{ __('Pour restaurer une sauvegarde : les pages existantes sont écrasées et la page d’accueil importée est reprise.') }}</span></span></label>
                </fieldset>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="settings" value="1" checked class="rounded border-slate-300"> {{ __('Importer aussi les réglages (nom, couleurs, police, langues)') }}</label>

                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Importer') }}</button>
            </form>
        </section>
    </div>
@endsection
