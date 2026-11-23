@extends('layouts.auth')
@section('title', 'Connexion')
@section('content')
    <h1 class="text-xl font-semibold">Connexion</h1>
    <p class="mt-1 text-sm text-slate-500">Accédez à l’administration de votre site.</p>
    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf
        <x-field name="email" type="email" label="Adresse e-mail" required autofocus autocomplete="email" />
        <x-field name="password" type="password" label="Mot de passe" required autocomplete="current-password" />
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="rounded border-slate-300"> Rester connecté
        </label>
        <button class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">Se connecter</button>
    </form>
@endsection
