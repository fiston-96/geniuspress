@extends('layouts.admin')
@section('title', __('Mon compte'))
@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Mon compte') }}</h1>
    <p class="mt-1 text-sm text-slate-500">{{ __('Votre nom, votre adresse e-mail de connexion et votre mot de passe.') }}</p>

    <div class="mt-6 max-w-2xl space-y-6">
        <form method="POST" action="{{ route('admin.account.update') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            @csrf @method('PUT')
            <h2 class="font-semibold">{{ __('Profil') }}</h2>
            <x-field name="name" :label="__('Nom')" :value="$user->name" required autocomplete="name" />
            <x-field name="email" type="email" :label="__('Adresse e-mail')" :value="$user->email" :hint="__('C’est l’identifiant pour vous connecter.')" required autocomplete="email" />
            <x-field name="profile_password" type="password" :label="__('Mot de passe actuel')" :hint="__('Pour confirmer que c’est bien vous.')" required autocomplete="current-password" />
            <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ __('Enregistrer le profil') }}</button>
        </form>

        <form method="POST" action="{{ route('admin.account.password') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            @csrf @method('PUT')
            <h2 class="font-semibold">{{ __('Mot de passe') }}</h2>
            <x-field name="current_password" type="password" :label="__('Mot de passe actuel')" required autocomplete="current-password" />
            <x-field name="password" type="password" :label="__('Nouveau mot de passe')" :hint="__('10 caractères minimum, avec des lettres et des chiffres.')" required autocomplete="new-password" />
            <x-field name="password_confirmation" type="password" :label="__('Confirmez le nouveau mot de passe')" required autocomplete="new-password" />
            <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ __('Changer le mot de passe') }}</button>
        </form>
    </div>
@endsection
