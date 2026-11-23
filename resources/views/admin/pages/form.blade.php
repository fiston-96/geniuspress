@extends('layouts.admin')
@section('title', $page->exists ? 'Réglages de la page' : 'Nouvelle page')
@section('content')
    <a href="{{ route('admin.pages.index') }}" class="text-sm text-slate-500 hover:text-slate-900">← Pages</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $page->exists ? 'Réglages : '.$page->title : 'Nouvelle page' }}</h1>

    <form method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="mt-6 max-w-2xl space-y-5 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @if ($page->exists) @method('PUT') @endif

        <x-field name="title" label="Titre" :value="$page->title" required autofocus />
        <x-field name="slug" label="Adresse (slug)" :value="$page->slug" hint="Laissez vide pour la générer à partir du titre. Exemple : a-propos" />

        <div>
            <label for="locale" class="mb-1.5 block text-sm font-medium text-slate-700">Langue</label>
            <select id="locale" name="locale" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($locales as $loc)<option value="{{ $loc }}" @selected(old('locale', $page->locale) === $loc)>{{ strtoupper($loc) }}</option>@endforeach
            </select>
        </div>

        @if ($page->exists)
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-slate-700">Statut</label>
                    <select id="status" name="status" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="draft" @selected(old('status', $page->status) === 'draft')>Brouillon</option>
                        <option value="published" @selected(old('status', $page->status) === 'published')>Publiée</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 self-end pb-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_home" value="1" @checked(old('is_home', $page->is_home)) class="rounded border-slate-300"> Page d’accueil du site
                </label>
            </div>

            <fieldset class="space-y-4 border-t border-slate-100 pt-5">
                <legend class="text-sm font-semibold text-slate-900">Référencement (SEO)</legend>
                <x-field name="meta_title" label="Titre pour Google" :value="$page->meta_title" maxlength="190" hint="Environ 60 caractères. Par défaut : le titre de la page." />
                <div>
                    <label for="meta_description" class="mb-1.5 block text-sm font-medium text-slate-700">Description pour Google</label>
                    <textarea id="meta_description" name="meta_description" rows="3" maxlength="320" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('meta_description', $page->meta_description) }}</textarea>
                    <p class="mt-1 text-xs text-slate-500">Entre 120 et 160 caractères.</p>
                </div>
            </fieldset>
        @endif

        <div class="flex items-center gap-3 pt-2">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ $page->exists ? 'Enregistrer' : 'Créer et ouvrir le builder' }}</button>
            @if ($page->exists)<a href="{{ route('admin.pages.builder', $page) }}" class="text-sm font-medium text-indigo-600">Ouvrir le builder</a>@endif
        </div>
    </form>

    @if ($page->exists && ! $page->is_home)
        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="mt-6" onsubmit="return confirm('Mettre cette page à la corbeille ?')">
            @csrf @method('DELETE')
            <button class="text-sm font-medium text-red-600 hover:text-red-500">Mettre à la corbeille</button>
        </form>
    @endif
@endsection
