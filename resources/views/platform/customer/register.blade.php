@extends('layouts.auth')
@section('title', __('Créer un compte'))
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Créer un compte') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Votre espace client GeniusPress : hébergement, modèles, domaines et crédits IA.') }}</p>
    <form method="POST" action="{{ route('platform.register') }}" class="mt-6 space-y-4">
        <x-google-button :href="route('platform.google.redirect')" />
        @csrf
        <x-field name="name" :label="__('Nom ou organisation')" required autofocus autocomplete="name" />
        <x-field name="email" type="email" :label="__('Adresse e-mail')" required autocomplete="email" />
        <x-field name="phone" type="tel" :label="__('Téléphone (facultatif)')" :hint="__('Pour le paiement Mobile Money, avec l’indicatif du pays.')" autocomplete="tel" />
        <x-field name="country" :label="__('Pays (code à 2 lettres, facultatif)')" maxlength="2" placeholder="CD" />
        <x-field name="password" type="password" :label="__('Mot de passe')" :hint="__('10 caractères minimum, avec des lettres et des chiffres.')" required autocomplete="new-password" />
        <x-field name="password_confirmation" type="password" :label="__('Confirmez le mot de passe')" required autocomplete="new-password" />
        <label class="flex items-start gap-2 text-sm text-slate-600">
            <input type="checkbox" name="newsletter" value="1" @checked(old('newsletter')) class="mt-0.5 rounded border-slate-300">
            <span>{{ __('Recevoir les nouvelles de GeniusPress (nouveaux modèles, conseils), une ou deux fois par mois.') }}</span>
        </label>
        <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Créer mon compte') }}</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">{{ __('Déjà un compte ?') }} <a href="{{ route('platform.login') }}" class="font-medium text-brand-600 hover:underline">{{ __('Se connecter') }}</a></p>
@endsection
