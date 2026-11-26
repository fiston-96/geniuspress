@extends('layouts.auth')
@section('title', __('Nouveau mot de passe'))
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Nouveau mot de passe') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Au moins 10 caractères, avec des lettres et des chiffres.') }}</p>

    <form method="POST" action="{{ route($routes['update']) }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-field name="email" type="email" :label="__('Adresse e-mail')" :value="$email" required autocomplete="email" />
        <x-field name="password" type="password" :label="__('Nouveau mot de passe')" required autofocus autocomplete="new-password" minlength="10" />
        <x-field name="password_confirmation" type="password" :label="__('Confirmez le mot de passe')" required autocomplete="new-password" minlength="10" />
        <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Enregistrer le mot de passe') }}</button>
    </form>
@endsection
