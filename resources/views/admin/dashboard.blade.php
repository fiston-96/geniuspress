@extends('layouts.admin')
@section('title', __('Tableau de bord'))
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ __('Bonjour :name', ['name' => auth()->user()->name]) }}</h1>
            <p class="text-sm text-slate-500">{{ __('Voici l’état de votre site.') }}</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('+ Nouvelle page') }}</a>
    </div>

    <div class="mt-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([[__('Pages'), $stats['pages']], [__('Publiées'), $stats['published']], [__('Brouillons'), $stats['drafts']], [__('Médias'), $stats['media']]] as [$label, $value])
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-1 text-3xl font-semibold tabular-nums">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="mt-10 mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Modifiées récemment') }}</h2>
    <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
        @forelse ($recent as $page)
            <div class="flex items-center justify-between gap-4 px-5 py-3">
                <div class="min-w-0">
                    <p class="truncate font-medium">{{ $page->title }}</p>
                    <p class="text-xs text-slate-500">{{ __('Modifiée :date', ['date' => $page->updated_at->diffForHumans()]) }}</p>
                </div>
                <a href="{{ route('admin.pages.builder', $page) }}" class="shrink-0 text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ __('Ouvrir le builder') }}</a>
            </div>
        @empty
            <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('Aucune page pour l’instant.') }}</p>
        @endforelse
    </div>
@endsection
