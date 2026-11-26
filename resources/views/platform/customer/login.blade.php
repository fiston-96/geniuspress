@extends('layouts.auth')
@section('title', __('Espace client'))
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Espace client') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Connectez-vous à votre compte GeniusPress.') }}</p>
    <form method="POST" action="{{ route('platform.login') }}" class="mt-6 space-y-4">
        @csrf
        <x-field name="email" type="email" :label="__('Adresse e-mail')" required autofocus autocomplete="email" />
        <x-field name="password" type="password" :label="__('Mot de passe')" required autocomplete="current-password" />
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300"> {{ __('Rester connecté') }}
        </label>
        <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Se connecter') }}</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">{{ __('Pas encore de compte ?') }} <a href="{{ route('platform.register') }}" class="font-medium text-brand-600 hover:underline">{{ __('Créer un compte') }}</a></p>
@endsection
