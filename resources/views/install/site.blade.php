@extends('install.layout', ['step' => 3])
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Votre site') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('La base de données est prête. Il ne reste qu’à créer votre site et votre compte administrateur.') }}</p>

    <form method="POST" action="{{ route('install.site') }}" class="mt-6 space-y-5" enctype="multipart/form-data">
        @csrf
        <x-field name="site_name" :label="__('Nom du site ou de l’organisation')" required autofocus />
        <div>
            <label for="locale" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Langue du site') }}</label>
            <select id="locale" name="locale" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($locales as $code => $name)
                    <option value="{{ $code }}" @selected(old('locale', app()->getLocale()) === $code)>{{ $name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">{{ __('Vous pourrez ajouter d’autres langues plus tard.') }}</p>
        </div>

        <fieldset class="space-y-4 border-t border-slate-100 pt-5">
            <legend class="text-sm font-semibold">{{ __('Compte administrateur') }}</legend>
            <x-field name="name" :label="__('Votre nom')" required />
            <x-field name="email" type="email" :label="__('Adresse e-mail')" required autocomplete="email" />
            <x-field name="password" type="password" :label="__('Mot de passe')" required autocomplete="new-password" :hint="__('10 caractères minimum, avec des lettres et des chiffres.')" />
            <x-field name="password_confirmation" type="password" :label="__('Confirmer le mot de passe')" required autocomplete="new-password" />
        </fieldset>

        <fieldset class="border-t border-slate-100 pt-5">
            <legend class="mb-3 text-sm font-semibold">{{ __('Point de départ') }}</legend>
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach (['welcome' => [__('Page d’accueil d’exemple'), __('Une section d’accueil à personnaliser.')], 'blank' => [__('Page vide'), __('Partez de zéro, ou laissez l’assistant IA créer la page.')], 'import' => [__('Importer un site'), __('Reprenez un site exporté depuis une autre installation de GeniusPress.')]] as $value => [$title, $text])
                    <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-4 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                        <input type="radio" name="start" value="{{ $value }}" class="mt-1" @checked(old('start', 'welcome') === $value) onchange="document.getElementById('import-file').hidden = this.value !== 'import'">
                        <span><span class="block text-sm font-semibold">{{ $title }}</span><span class="block text-xs text-slate-500">{{ $text }}</span></span>
                    </label>
                @endforeach
            </div>
            <div id="import-file" class="mt-3" @if (old('start') !== 'import') hidden @endif>
                <input type="file" name="archive" accept=".zip,application/zip" class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium">
                <p class="mt-1 text-xs text-slate-500">{{ __('Fichier zip créé avec « Sauvegarde → Exporter le site ».') }}</p>
                @error('archive')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </fieldset>

        <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Créer mon site') }}</button>
    </form>
@endsection
