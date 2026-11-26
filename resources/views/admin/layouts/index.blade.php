@extends('layouts.admin')
@section('title', __('Thème'))
@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('Thème') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-slate-600">{{ __('Concevez dans le builder l’en-tête, le pied de page, les articles, les archives, la recherche et la page 404. Chaque gabarit s’applique là où ses conditions le disent ; sans gabarit, GeniusPress utilise son affichage par défaut.') }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.layouts.store') }}" class="mt-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4">
        @csrf
        <label class="text-sm font-medium text-slate-700">{{ __('Type de gabarit') }}
            <select name="type" class="mt-1 block rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($types as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach
            </select>
        </label>
        <label class="min-w-56 flex-1 text-sm font-medium text-slate-700">{{ __('Nom') }}
            <input name="name" required maxlength="120" placeholder="{{ __('Par exemple : En-tête principal') }}" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </label>
        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Créer et ouvrir le builder') }}</button>
    </form>

    <div class="mt-6 grid gap-4 md:grid-cols-2">
        @foreach ($types as $type => $label)
            <section class="rounded-xl border border-slate-200 bg-white">
                <h2 class="border-b border-slate-100 px-5 py-3 text-sm font-semibold text-slate-900">{{ $label }}</h2>
                <ul class="divide-y divide-slate-100">
                    @forelse ($layouts[$type] ?? [] as $layout)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 text-sm">
                            <div class="min-w-0">
                                <a href="{{ route('admin.layouts.builder', $layout) }}" class="font-medium hover:text-brand-600">{{ $layout->name }}</a>
                                <p class="text-xs text-slate-500">
                                    @if ($layout->isPublished())<span class="font-medium text-emerald-700">{{ __('Actif') }}</span>@else<span class="font-medium text-amber-700">{{ __('Inactif') }}</span>@endif
                                    · {{ $layout->locale ? strtoupper($layout->locale) : __('Toutes les langues') }}
                                    · {{ trans_choice(':count condition|:count conditions', count($layout->conditions ?? []), ['count' => count($layout->conditions ?? [])]) }}
                                    @if ($layout->priority) · {{ __('priorité :n', ['n' => $layout->priority]) }}@endif
                                </p>
                            </div>
                            <div class="whitespace-nowrap">
                                <a href="{{ route('admin.layouts.builder', $layout) }}" class="font-medium text-brand-600 hover:text-brand-500">{{ __('Builder') }}</a>
                                <a href="{{ route('admin.layouts.edit', $layout) }}" class="ml-3 text-slate-600 hover:text-slate-900">{{ __('Conditions') }}</a>
                            </div>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-sm text-slate-500">{{ __('Aucun gabarit : affichage par défaut.') }}</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>
@endsection
