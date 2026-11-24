@extends('install.layout', ['step' => 2])
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Base de données') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('GeniusPress enregistre vos pages, médias et réglages dans une base de données.') }}</p>

    @error('database')
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('install.database') }}" class="mt-6 space-y-5">
        @csrf
        <fieldset class="grid gap-3 sm:grid-cols-2">
            <legend class="sr-only">{{ __('Type de base de données') }}</legend>
            <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-4 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                <input type="radio" name="driver" value="mysql" class="mt-1" @checked($values['driver'] === 'mysql') @disabled(! $hasMysql) onchange="document.getElementById('mysql-fields').hidden = false">
                <span>
                    <span class="block text-sm font-semibold">MySQL / MariaDB</span>
                    <span class="block text-xs text-slate-500">{{ __('Recommandé pour un site en ligne. Fourni par la plupart des hébergeurs.') }}</span>
                </span>
            </label>
            <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-4 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                <input type="radio" name="driver" value="sqlite" class="mt-1" @checked($values['driver'] === 'sqlite') @disabled(! $hasSqlite) onchange="document.getElementById('mysql-fields').hidden = true">
                <span>
                    <span class="block text-sm font-semibold">SQLite</span>
                    <span class="block text-xs text-slate-500">{{ __('Un simple fichier, sans serveur. Idéal en local ou hors ligne.') }}</span>
                </span>
            </label>
        </fieldset>

        <div id="mysql-fields" class="space-y-4" @if ($values['driver'] === 'sqlite') hidden @endif>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2"><x-field name="host" :label="__('Serveur')" :value="$values['host']" /></div>
                <x-field name="port" :label="__('Port')" :value="$values['port']" inputmode="numeric" />
            </div>
            <x-field name="database" :label="__('Nom de la base')" :value="$values['database']" />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="username" :label="__('Utilisateur')" :value="$values['username']" autocomplete="off" />
                <x-field name="password" type="password" :label="__('Mot de passe')" autocomplete="off" :hint="$serverPassword ? __('Laissez vide pour utiliser le mot de passe fourni par le serveur.') : null" />
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="create" value="1" class="rounded border-slate-300" @checked(old('create', true))>
                {{ __('Créer la base si elle n’existe pas') }}
            </label>
        </div>
        <p class="text-xs text-slate-500">{{ __('SQLite enregistrera tout dans :path.', ['path' => $sqlitePath]) }}</p>

        <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Tester et créer les tables') }}</button>
    </form>
@endsection
