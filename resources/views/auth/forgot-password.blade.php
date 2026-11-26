@extends('layouts.auth')
@section('title', __('Mot de passe oublié'))
@section('content')
    <h1 class="text-xl font-semibold">{{ __('Mot de passe oublié') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Indiquez l’adresse e-mail de votre compte : nous vous envoyons un lien pour choisir un nouveau mot de passe.') }}</p>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route($routes['email']) }}" class="mt-6 space-y-4">
        @csrf
        <x-field name="email" type="email" :label="__('Adresse e-mail')" required autofocus autocomplete="email" />
        <button class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Recevoir le lien') }}</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500"><a href="{{ route($routes['login']) }}" class="font-medium text-brand-600 hover:underline">{{ __('Retour à la connexion') }}</a></p>
@endsection
