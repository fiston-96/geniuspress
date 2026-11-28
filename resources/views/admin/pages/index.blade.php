@extends('layouts.admin')
@section('title', __('Pages'))
@section('content')
@php
    $tab = request('status', '');
    $all = $counts->sum();
    $query = fn (array $change) => route('admin.pages.index', array_filter(array_merge(request()->only(['q', 'status', 'locale']), $change), fn ($v) => $v !== null && $v !== ''));
@endphp
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-night">{{ __('Pages') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ __('Chaque page se conçoit dans le builder ; les réglages gèrent l’adresse, la langue et le référencement.') }}</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700"><x-icon name="plus" class="h-4 w-4" stroke="2.4" />{{ __('Nouvelle page') }}</a>
    </div>

    {{-- Onglets de statut, langues, recherche --}}
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-b border-line">
        <nav class="-mb-px flex gap-1" aria-label="{{ __('Statut') }}">
            @foreach (['' => [__('Toutes'), $all], 'published' => [__('Publiées'), $counts['published'] ?? 0], 'draft' => [__('Brouillons'), $counts['draft'] ?? 0]] as $value => [$label, $n])
                <a href="{{ $query(['status' => $value ?: null, 'page' => null]) }}" @if ($tab === $value) aria-current="page" @endif
                    @class(['flex items-center gap-2 border-b-2 px-3 pb-3 pt-1 text-sm font-medium transition', 'border-brand-600 text-brand-700' => $tab === $value, 'border-transparent text-slate-500 hover:text-slate-800' => $tab !== $value])>
                    {{ $label }}<span @class(['rounded-full px-2 py-0.5 text-xs tabular-nums', 'bg-brand-50 text-brand-700' => $tab === $value, 'bg-slate-100 text-slate-500' => $tab !== $value])>{{ $n }}</span>
                </a>
            @endforeach
        </nav>
        <div class="mb-2 flex flex-wrap items-center gap-2">
            @if (count($locales) > 1)
                <div class="flex rounded-lg bg-slate-100 p-0.5 text-xs font-semibold" role="group" aria-label="{{ __('Langue') }}">
                    @foreach (['' => __('Toutes'), ...array_combine($locales, array_map('strtoupper', $locales))] as $code => $label)
                        <a href="{{ $query(['locale' => $code ?: null, 'page' => null]) }}" @class(['rounded-md px-2.5 py-1', 'bg-white text-slate-900 shadow-sm' => request('locale', '') === $code, 'text-slate-500 hover:text-slate-800' => request('locale', '') !== $code])>{{ $label }}</a>
                    @endforeach
                </div>
            @endif
            <form method="GET" class="relative">
                @foreach (request()->only(['status', 'locale']) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <span class="pointer-events-none absolute inset-y-0 left-2.5 grid place-items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span>
                <input name="q" value="{{ request('q') }}" placeholder="{{ __('Rechercher une page…') }}" aria-label="{{ __('Rechercher une page…') }}" class="w-56 rounded-lg border border-slate-300 bg-white py-1.5 pl-8 pr-3 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10">
            </form>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line bg-cream text-xs uppercase tracking-wide text-slate-500">
                <tr><th class="px-5 py-3 font-semibold">{{ __('Titre') }}</th><th class="hidden px-5 py-3 font-semibold md:table-cell">{{ __('Auteur') }}</th><th class="px-5 py-3 font-semibold">{{ __('Statut') }}</th><th class="hidden px-5 py-3 font-semibold sm:table-cell">{{ __('Langue') }}</th><th class="hidden px-5 py-3 font-semibold lg:table-cell">{{ __('Modifiée') }}</th><th class="px-5 py-3"><span class="sr-only">{{ __('Actions') }}</span></th></tr>
            </thead>
            <tbody class="divide-y divide-line">
            @forelse ($pages as $page)
                <tr class="group align-top hover:bg-cream/70">
                    <td class="px-5 py-3.5">
                        <div class="flex items-start gap-3">
                            <span @class(['mt-1 grid h-8 w-8 shrink-0 place-items-center rounded-lg', 'bg-brand-50 text-brand-700' => $page->is_home, 'bg-slate-100 text-slate-500' => ! $page->is_home])><x-icon :name="$page->is_home ? 'house' : 'file-text'" class="h-4 w-4" /></span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.pages.builder', $page) }}" class="font-semibold text-night hover:text-brand-700">{{ $page->title }}</a>
                                @if ($page->is_home)<span class="ml-1.5 rounded bg-brand-50 px-1.5 py-0.5 text-xs font-medium text-brand-700">{{ __('Accueil') }}</span>@endif
                                <p class="text-xs text-slate-500">/{{ $page->is_home ? '' : $page->slug }}</p>
                                {{-- Actions au survol, comme WordPress (toujours visibles au clavier et sur mobile). --}}
                                <p class="mt-1 flex flex-wrap gap-x-3 text-xs font-medium opacity-100 transition sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-within:opacity-100">
                                    <a href="{{ route('admin.pages.builder', $page) }}" class="text-brand-600 hover:underline">{{ __('Builder') }}</a>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="text-slate-600 hover:underline">{{ __('Réglages') }}</a>
                                    <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="text-slate-600 hover:underline">{{ __('Voir') }}</a>
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="hidden px-5 py-3.5 text-slate-600 md:table-cell">{{ $page->author?->name ?? '—' }}</td>
                    <td class="px-5 py-3.5">
                        @if ($page->isPublished())
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ __('Publiée') }}</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>{{ __('Brouillon') }}</span>
                        @endif
                    </td>
                    <td class="hidden px-5 py-3.5 sm:table-cell"><span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-semibold uppercase text-slate-600">{{ $page->locale }}</span></td>
                    <td class="hidden px-5 py-3.5 text-slate-500 lg:table-cell">{{ $page->updated_at->diffForHumans() }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('admin.pages.builder', $page) }}" class="grid h-8 w-8 place-items-center rounded-lg text-brand-600 hover:bg-brand-50" title="{{ __('Ouvrir le builder') }}" aria-label="{{ __('Ouvrir le builder') }}"><x-icon name="pencil" class="h-4 w-4" /></a>
                            <a href="{{ route('admin.pages.edit', $page) }}" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100" title="{{ __('Réglages') }}" aria-label="{{ __('Réglages') }}"><x-icon name="settings" class="h-4 w-4" /></a>
                            <a href="{{ $page->url() }}" target="_blank" rel="noopener" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100" title="{{ __('Voir') }}" aria-label="{{ __('Voir') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-14 text-center">
                    <x-icon name="files" class="mx-auto h-8 w-8 text-slate-300" />
                    <p class="mt-3 text-sm text-slate-500">{{ __('Aucune page trouvée.') }}</p>
                </td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $pages->links() }}</div>
@endsection
