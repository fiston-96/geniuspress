@extends('layouts.admin')
@section('title', __('Articles'))
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold tracking-tight">{{ __('Articles') }}</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.terms.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">{{ __('Catégories et étiquettes') }}</a>
            <a href="{{ route('admin.posts.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ __('+ Nouvel article') }}</a>
        </div>
    </div>

    <form class="mt-6 flex flex-wrap gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="{{ __('Rechercher un article…') }}" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
            <option value="">{{ __('Tous les statuts') }}</option>
            <option value="published" @selected(request('status') === 'published')>{{ __('Publiés') }}</option>
            <option value="scheduled" @selected(request('status') === 'scheduled')>{{ __('Programmés') }}</option>
            <option value="draft" @selected(request('status') === 'draft')>{{ __('Brouillons') }}</option>
        </select>
        @if ($categories->isNotEmpty())
            <select name="category" class="rounded-lg border border-slate-300 px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="">{{ __('Toutes les catégories') }}</option>
                @foreach ($categories as $category)<option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }} ({{ strtoupper($category->locale) }})</option>@endforeach
            </select>
        @endif
    </form>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3">{{ __('Titre') }}</th><th class="px-5 py-3">{{ __('Statut') }}</th><th class="px-5 py-3">{{ __('Catégories') }}</th><th class="px-5 py-3">{{ __('Langue') }}</th><th class="px-5 py-3">{{ __('Date') }}</th><th class="px-5 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($posts as $post)
                <tr>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            @if ($post->featuredMedia)<img src="{{ $post->featuredMedia->url }}" alt="" class="h-10 w-14 shrink-0 rounded object-cover">@endif
                            <div class="min-w-0">
                                <a href="{{ route('admin.posts.builder', $post) }}" class="font-medium hover:text-brand-600">{{ $post->title }}</a>
                                <p class="truncate text-xs text-slate-500">{{ parse_url($post->url(), PHP_URL_PATH) }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        @if ($post->isScheduled())
                            <span class="rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-700">{{ __('Programmé') }}</span>
                        @elseif ($post->isPublished())
                            <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">{{ __('Publié') }}</span>
                        @else
                            <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700">{{ __('Brouillon') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $post->categories->pluck('name')->join(', ') ?: '—' }}</td>
                    <td class="px-5 py-3 uppercase text-slate-500">{{ $post->locale }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ ($post->published_at ?? $post->updated_at)->translatedFormat('j M Y') }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-right">
                        <a href="{{ route('admin.posts.builder', $post) }}" class="font-medium text-brand-600 hover:text-brand-500">{{ __('Builder') }}</a>
                        <a href="{{ route('admin.posts.edit', $post) }}" class="ml-3 text-slate-600 hover:text-slate-900">{{ __('Réglages') }}</a>
                        <a href="{{ $post->url() }}" target="_blank" class="ml-3 text-slate-600 hover:text-slate-900">{{ __('Voir') }}</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-slate-500">{{ __('Aucun article pour le moment. Écrivez le premier : actualités, événements, conseils…') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>

    @can('admin')
        <details class="mt-8 rounded-xl border border-slate-200 bg-white p-5">
            <summary class="cursor-pointer text-sm font-semibold text-slate-900">{{ __('Réglages du blog') }}</summary>
            <form method="POST" action="{{ route('admin.posts.settings') }}" class="mt-4 space-y-4">
                @csrf @method('PUT')
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($locales as $code)
                        <fieldset class="space-y-2 rounded-lg border border-slate-100 p-3">
                            <legend class="px-1 text-xs font-semibold uppercase text-slate-500">{{ strtoupper($code) }}</legend>
                            <label class="block text-sm">{{ __('Titre du blog') }}
                                <input name="blog[{{ $code }}][title]" value="{{ $blog['locales'][$code]['title'] ?? '' }}" placeholder="Blog" maxlength="60" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </label>
                            <label class="block text-sm">{{ __('Adresse du blog') }}
                                <input name="blog[{{ $code }}][base]" value="{{ $blog['locales'][$code]['base'] ?? '' }}" placeholder="blog" maxlength="40" class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </label>
                        </fieldset>
                    @endforeach
                </div>
                <label class="block text-sm">{{ __('Articles par page') }}
                    <input type="number" name="per_page" min="1" max="48" value="{{ $blog['per_page'] ?? 9 }}" class="mt-1 block w-32 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
                <p class="text-xs text-slate-500">{{ __('Une page qui porte la même adresse que le blog (par exemple « actualites ») le remplace : posez-y une grille d’articles.') }}</p>
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Enregistrer') }}</button>
            </form>
        </details>
    @endcan
@endsection
