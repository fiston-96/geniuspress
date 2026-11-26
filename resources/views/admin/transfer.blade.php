@extends('layouts.admin')
@section('title', __('Sauvegarde'))
@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Sauvegarde, export et import') }}</h1>
    <p class="mt-1 max-w-2xl text-sm text-slate-500">{{ __('Vos données vous appartiennent : téléchargez tout votre site dans un seul fichier, pour le sauvegarder ou le déplacer vers une autre installation de GeniusPress.') }}</p>

    @if (session('warnings'))
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <p class="font-semibold">{{ __('Points à vérifier') }}</p>
            <ul class="mt-1 list-disc pl-5">@foreach (session('warnings') as $warning)<li>{{ $warning }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Exporter le site') }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ __('Un fichier zip avec toutes les pages (traductions et brouillons compris), les médias et les réglages.') }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ __('Ne contient ni les comptes utilisateurs, ni la clé API, ni aucun mot de passe.') }}</p>
            <a href="{{ route('admin.transfer.export') }}" class="mt-4 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ __('Télécharger l’export') }}</a>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Importer un site') }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ __('Fichier zip exporté par GeniusPress. Taille maximale acceptée par ce serveur : :size Mo.', ['size' => number_format($maxUpload / 1048576, 0, ',', ' ')]) }}</p>

            <form method="POST" action="{{ route('admin.transfer.import') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <input type="file" name="archive" accept=".zip,application/zip" required class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium">
                @error('archive')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

                <fieldset class="space-y-2 text-sm">
                    <label class="flex gap-2"><input type="radio" name="mode" value="merge" class="mt-0.5" checked>
                        <span><span class="font-medium">{{ __('Ajouter au site actuel') }}</span><span class="block text-xs text-slate-500">{{ __('Les adresses déjà prises reçoivent un suffixe ; votre page d’accueil est conservée.') }}</span></span></label>
                    <label class="flex gap-2"><input type="radio" name="mode" value="replace" class="mt-0.5">
                        <span><span class="font-medium">{{ __('Remplacer les pages de même adresse') }}</span><span class="block text-xs text-slate-500">{{ __('Pour restaurer une sauvegarde : les pages existantes sont écrasées et la page d’accueil importée est reprise.') }}</span></span></label>
                </fieldset>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="settings" value="1" checked class="rounded border-slate-300"> {{ __('Importer aussi les réglages (nom, couleurs, police, langues)') }}</label>

                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ __('Importer') }}</button>
            </form>
        </section>
    </div>

    <p class="mt-6 text-xs text-slate-500">{{ __('Sur un serveur, les mêmes opérations existent en ligne de commande : :export et :import.', ['export' => 'php artisan geniuspress:export', 'import' => 'php artisan geniuspress:import fichier.zip']) }}</p>
@endsection
