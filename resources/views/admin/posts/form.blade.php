@extends('layouts.admin')
@section('title', $post->exists ? __('Réglages de l’article') : __('Nouvel article'))
@section('content')
    <a href="{{ route('admin.posts.index') }}" class="text-sm text-slate-500 hover:text-slate-900">{{ __('← Articles') }}</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $post->exists ? __('Réglages : :title', ['title' => $post->title]) : __('Nouvel article') }}</h1>

    <form method="POST" action="{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
        @csrf
        @if ($post->exists) @method('PUT') @endif

        <div class="space-y-5 rounded-xl border border-slate-200 bg-white p-6">
            <x-field name="title" :label="__('Titre')" :value="$post->title" required autofocus />
            <x-field name="slug" :label="__('Adresse (slug)')" :value="$post->slug" :hint="__('Laissez vide pour la générer à partir du titre.')" />
            <div>
                <label for="excerpt" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Résumé') }}</label>
                <textarea id="excerpt" name="excerpt" rows="3" maxlength="500" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('excerpt', $post->excerpt) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">{{ __('Affiché dans les listes d’articles. Par défaut : le début de l’article.') }}</p>
            </div>
            <x-field name="tags" :label="__('Étiquettes')" :value="$post->exists ? $post->terms->where('taxonomy', 'tag')->pluck('name')->join(', ') : ''" :hint="__('Séparées par des virgules : santé, rentrée, bénévolat')" />

            <fieldset>
                <legend class="mb-2 text-sm font-medium text-slate-700">{{ __('Image à la une') }}</legend>
                @php($current = (int) old('featured_media_id', $post->featured_media_id))
                <div class="grid max-h-72 grid-cols-3 gap-2 overflow-y-auto rounded-lg border border-slate-200 p-2 sm:grid-cols-5">
                    <label class="flex aspect-[4/3] cursor-pointer items-center justify-center rounded-md border border-dashed border-slate-300 text-xs text-slate-500 has-[:checked]:border-brand-600 has-[:checked]:ring-2 has-[:checked]:ring-brand-200">
                        <input type="radio" name="featured_media_id" value="" class="sr-only" @checked(! $current)> {{ __('Aucune') }}
                    </label>
                    @foreach ($images as $image)
                        <label class="cursor-pointer overflow-hidden rounded-md border border-transparent has-[:checked]:border-brand-600 has-[:checked]:ring-2 has-[:checked]:ring-brand-200">
                            <input type="radio" name="featured_media_id" value="{{ $image->id }}" class="sr-only" @checked($current === $image->id)>
                            <img src="{{ $image->url }}" alt="{{ $image->alt }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-slate-500">{!! __('Ajoutez des images dans la <a href=":url" class="underline">médiathèque</a>.', ['url' => route('admin.media.index')]) !!}</p>
            </fieldset>

            <fieldset class="space-y-4 border-t border-slate-100 pt-5">
                <legend class="text-sm font-semibold text-slate-900">{{ __('Référencement (SEO)') }}</legend>
                <x-field name="meta_title" :label="__('Titre pour Google')" :value="$post->meta_title" maxlength="190" :hint="__('Par défaut : le titre de l’article.')" />
                <div>
                    <label for="meta_description" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Description pour Google') }}</label>
                    <textarea id="meta_description" name="meta_description" rows="2" maxlength="320" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('meta_description', $post->meta_description) }}</textarea>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Par défaut : le résumé.') }}</p>
                </div>
            </fieldset>
        </div>

        <aside class="space-y-5">
            <div class="space-y-4 rounded-xl border border-slate-200 bg-white p-5">
                <div>
                    <label for="locale" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Langue') }}</label>
                    <select id="locale" name="locale" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($locales as $loc)<option value="{{ $loc }}" @selected(old('locale', $post->locale) === $loc)>{{ strtoupper($loc) }}</option>@endforeach
                    </select>
                </div>
                @if ($post->exists)
                    <div>
                        <label for="status" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Statut') }}</label>
                        <select id="status" name="status" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="draft" @selected(old('status', $post->status) === 'draft')>{{ __('Brouillon') }}</option>
                            <option value="published" @selected(old('status', $post->status) === 'published')>{{ __('Publié') }}</option>
                        </select>
                    </div>
                @endif
                <div>
                    <label for="published_at" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Date de publication') }}</label>
                    <input type="datetime-local" id="published_at" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <p class="mt-1 text-xs text-slate-500">{{ __('Une date future programme l’article : il apparaît sur le site à cette date.') }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="mb-2 text-sm font-medium text-slate-700">{{ __('Catégories') }}</p>
                @php($checked = collect(old('categories', $post->exists ? $post->terms->where('taxonomy', 'category')->pluck('id')->all() : [])))
                @forelse ($categories as $locale => $list)
                    <p class="mt-2 text-xs font-semibold uppercase text-slate-400">{{ strtoupper($locale) }}</p>
                    @foreach ($list as $category)
                        <label class="flex items-center gap-2 py-0.5 text-sm"><input type="checkbox" name="categories[]" value="{{ $category->id }}" @checked($checked->contains($category->id)) class="rounded border-slate-300"> {{ $category->name }}</label>
                    @endforeach
                @empty
                    <p class="text-sm text-slate-500">{!! __('Aucune catégorie. <a href=":url" class="underline">Créer des catégories</a>.', ['url' => route('admin.terms.index')]) !!}</p>
                @endforelse
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ $post->exists ? __('Enregistrer') : __('Créer et ouvrir le builder') }}</button>
                @if ($post->exists)<a href="{{ route('admin.posts.builder', $post) }}" class="text-sm font-medium text-brand-600">{{ __('Ouvrir le builder') }}</a>@endif
            </div>
        </aside>
    </form>

    @if ($post->exists)
        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" class="mt-6" onsubmit="return confirm(@js(__('Mettre cet article à la corbeille ?')))">
            @csrf @method('DELETE')
            <button class="text-sm font-medium text-red-600 hover:text-red-500">{{ __('Mettre à la corbeille') }}</button>
        </form>
    @endif
@endsection
