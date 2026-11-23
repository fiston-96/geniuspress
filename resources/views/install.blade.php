@extends('layouts.auth')
@section('title', __('Installation'))
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Installer GeniusPress') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Créez votre site et le compte administrateur.') }}</p>
    <form method="POST" action="{{ url('/install') }}" class="mt-6 space-y-4">
        @csrf
        <x-field name="site_name" :label="__('Nom du site ou de l’organisation')" required autofocus />
        <x-field name="name" :label="__('Votre nom')" required />
        <x-field name="email" type="email" :label="__('Adresse e-mail')" required autocomplete="email" />
        <x-field name="password" type="password" :label="__('Mot de passe')" required autocomplete="new-password" :hint="__('10 caractères minimum, avec des lettres et des chiffres.')" />
        <x-field name="password_confirmation" type="password" :label="__('Confirmer le mot de passe')" required autocomplete="new-password" />
        <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">{{ __('Créer mon site') }}</button>
    </form>
@endsection
