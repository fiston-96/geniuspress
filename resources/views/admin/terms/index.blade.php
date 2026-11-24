@extends('layouts.admin')
@section('title', $taxonomy === 'tag' ? __('Étiquettes') : __('Catégories'))
@section('content')
    <a href="{{ route('admin.posts.index') }}" class="text-sm text-slate-500 hover:text-slate-900">{{ __('← Articles') }}</a>
    <div class="mt-2 flex flex-wrap items-center gap-2">
        <h1 class="mr-4 text-2xl font-semibold tracking-tight">{{ __('Catégories et étiquettes') }}</h1>
        <a href="{{ route('admin.terms.index') }}" @class(['rounded-full px-3 py-1 text-sm font-medium', 'bg-slate-900 text-white' => $taxonomy === 'category', 'bg-white text-slate-600 ring-1 ring-slate-200' => $taxonomy !== 'category'])>{{ __('Catégories') }}</a>
        <a href="{{ route('admin.terms.index', ['taxonomy' => 'tag']) }}" @class(['rounded-full px-3 py-1 text-sm font-medium', 'bg-slate-900 text-white' => $taxonomy === 'tag', 'bg-white text-slate-600 ring-1 ring-slate-200' => $taxonomy !== 'tag'])>{{ __('Étiquettes') }}</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[340px_minmax(0,1fr)]">
        @php($term = $edit ?? new \App\Models\Term(['locale' => $locales[0]]))
        <form method="POST" action="{{ $edit ? route('admin.terms.update', $edit) : route('admin.terms.store') }}" class="h-fit space-y-4 rounded-xl border border-slate-200 bg-white p-5">
            @csrf
            @if ($edit) @method('PUT') @endif
            <input type="hidden" name="taxonomy" value="{{ $taxonomy }}">
            <h2 class="text-sm font-semibold text-slate-900">{{ $edit ? __('Modifier « :name »', ['name' => $edit->name]) : ($taxonomy === 'tag' ? __('Nouvelle étiquette') : __('Nouvelle catégorie')) }}</h2>
            <x-field name="name" :label="__('Nom')" :value="$term->name" required />
            <x-field name="slug" :label="__('Adresse (slug)')" :value="$term->slug" :hint="__('Laissez vide pour la générer à partir du nom.')" />
            <div>
                <label for="locale" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Langue') }}</label>
                <select id="locale" name="locale" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($locales as $loc)<option value="{{ $loc }}" @selected(old('locale', $term->locale) === $loc)>{{ strtoupper($loc) }}</option>@endforeach
                </select>
            </div>
            @if ($taxonomy === 'category' && $parents->isNotEmpty())
                <div>
                    <label for="parent_id" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Catégorie parente') }}</label>
                    <select id="parent_id" name="parent_id" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">{{ __('Aucune') }}</option>
                        @foreach ($parents as $parent)@if ($parent->id !== $term->id)<option value="{{ $parent->id }}" @selected((int) old('parent_id', $term->parent_id) === $parent->id)>{{ $parent->name }} ({{ strtoupper($parent->locale) }})</option>@endif @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label for="description" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                <textarea id="description" name="description" rows="3" maxlength="1000" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('description', $term->description) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">{{ __('Affichée en haut de la page de la catégorie.') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">{{ $edit ? __('Enregistrer') : __('Ajouter') }}</button>
                @if ($edit)<a href="{{ route('admin.terms.index', ['taxonomy' => $taxonomy]) }}" class="text-sm text-slate-600">{{ __('Annuler') }}</a>@endif
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-5 py-3">{{ __('Nom') }}</th><th class="px-5 py-3">{{ __('Langue') }}</th><th class="px-5 py-3">{{ __('Articles') }}</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($terms as $item)
                    <tr>
                        <td class="px-5 py-3">
                            <span class="font-medium">{{ $item->parent ? $item->parent->name.' › ' : '' }}{{ $item->name }}</span>
                            <p class="text-xs text-slate-500">{{ parse_url($item->url(), PHP_URL_PATH) }}</p>
                        </td>
                        <td class="px-5 py-3 uppercase text-slate-500">{{ $item->locale }}</td>
                        <td class="px-5 py-3 text-slate-600"><a href="{{ route('admin.posts.index', $taxonomy === 'category' ? ['category' => $item->id] : []) }}" class="hover:text-indigo-600">{{ $item->posts_count }}</a></td>
                        <td class="whitespace-nowrap px-5 py-3 text-right">
                            <a href="{{ route('admin.terms.index', ['taxonomy' => $taxonomy, 'edit' => $item->id]) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ __('Modifier') }}</a>
                            <a href="{{ $item->url() }}" target="_blank" class="ml-3 text-slate-600 hover:text-slate-900">{{ __('Voir') }}</a>
                            <form method="POST" action="{{ route('admin.terms.destroy', $item) }}" class="inline" onsubmit="return confirm(@js(__('Supprimer ? Les articles sont conservés.')))">
                                @csrf @method('DELETE')
                                <button class="ml-3 text-red-600 hover:text-red-500">{{ __('Supprimer') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-slate-500">{{ $taxonomy === 'tag' ? __('Aucune étiquette. Elles se créent aussi en écrivant un article.') : __('Aucune catégorie pour le moment.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
