@extends('layouts.auth')
@section('title', __('Connexion'))
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Connexion') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Accédez à l’administration de votre site.') }}</p>
    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf
        <x-field name="email" type="email" :label="__('Adresse e-mail')" required autofocus autocomplete="email" />
        <x-field name="password" type="password" :label="__('Mot de passe')" required autocomplete="current-password" />
        <div class="flex items-center justify-between gap-3">
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300"> {{ __('Rester connecté') }}
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-brand-600 hover:underline">{{ __('Mot de passe oublié ?') }}</a>
        </div>
        <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Se connecter') }}</button>
    </form>
@endsection
