@extends('layouts.admin')
@section('title', __('Pages'))
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('Pages') }}</h1>
        <a href="{{ route('admin.pages.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('+ Nouvelle page') }}</a>
    </div>

    <form class="mt-6 flex flex-wrap gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('Rechercher une page…') }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">{{ __('Tous les statuts') }}</option>
            <option value="published" @selected(request('status') === 'published')>{{ __('Publiées') }}</option>
            <option value="draft" @selected(request('status') === 'draft')>{{ __('Brouillons') }}</option>
        </select>
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Titre') }}</th><th class="px-5 py-3">{{ __('Statut') }}</th><th class="px-5 py-3">{{ __('Langue') }}</th><th class="px-5 py-3">{{ __('Modifiée') }}</th><th class="px-5 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($pages as $page)
                <tr>
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.pages.builder', $page) }}" class="font-medium hover:text-indigo-600">{{ $page->title }}</a>
                        @if ($page->is_home)<span class="ml-2 rounded bg-indigo-50 px-1.5 py-0.5 text-xs font-medium text-indigo-700">{{ __('Accueil') }}</span>@endif
                        <p class="text-xs text-slate-500">/{{ $page->is_home ? '' : $page->slug }}</p>
                    </td>
                    <td class="px-5 py-3">
                        @if ($page->isPublished())
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Publiée') }}</span>
                        @else
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">{{ __('Brouillon') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 uppercase text-slate-500">{{ $page->locale }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $page->updated_at->diffForHumans() }}</td>
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.pages.builder', $page) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ __('Builder') }}</a>
                        <a href="{{ route('admin.pages.edit', $page) }}" class="ml-3 text-slate-600 hover:text-slate-900">{{ __('Réglages') }}</a>
                        <a href="{{ $page->url() }}" target="_blank" class="ml-3 text-slate-600 hover:text-slate-900">{{ __('Voir') }}</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">{{ __('Aucune page trouvée.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $pages->links() }}</div>
@endsection
