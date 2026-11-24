@extends('install.layout', ['step' => 3])
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Votre site') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('La base de données est prête. Il ne reste qu’à créer votre site et votre compte administrateur.') }}</p>

    <form method="POST" action="{{ route('install.site') }}" class="mt-6 space-y-5">
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
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach (['welcome' => [__('Page d’accueil d’exemple'), __('Une section d’accueil à personnaliser.')], 'blank' => [__('Page vide'), __('Partez de zéro, ou laissez l’assistant IA créer la page.')]] as $value => [$title, $text])
                    <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-4 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                        <input type="radio" name="start" value="{{ $value }}" class="mt-1" @checked(old('start', 'welcome') === $value)>
                        <span><span class="block text-sm font-semibold">{{ $title }}</span><span class="block text-xs text-slate-500">{{ $text }}</span></span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('Créer mon site') }}</button>
    </form>
@endsection
