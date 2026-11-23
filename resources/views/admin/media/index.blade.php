@extends('layouts.admin')
@section('title', 'Médias')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-semibold tracking-tight">Médiathèque</h1>
        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="flex items-center gap-2">
            @csrf
            <input type="file" name="file" required accept="image/*,.pdf" class="text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Envoyer</button>
        </form>
    </div>
    @error('file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        @forelse ($media as $m)
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div class="grid aspect-square place-items-center bg-slate-100">
                    @if (str_starts_with($m->mime_type, 'image/'))
                        <img src="{{ $m->url }}" alt="{{ $m->alt }}" class="h-full w-full object-cover" loading="lazy">
                    @else
                        <span class="text-sm font-semibold text-slate-500">PDF</span>
                    @endif
                </div>
                <div class="space-y-2 p-3">
                    <p class="truncate text-xs text-slate-500" title="{{ $m->original_name }}">{{ $m->original_name }}</p>
                    <form method="POST" action="{{ route('admin.media.update', $m) }}" class="flex gap-1">
                        @csrf @method('PATCH')
                        <input name="alt" value="{{ $m->alt }}" placeholder="Texte alternatif" class="min-w-0 flex-1 rounded border border-slate-300 px-2 py-1 text-xs">
                        <button class="rounded bg-slate-100 px-2 text-xs font-medium hover:bg-slate-200">OK</button>
                    </form>
                    <form method="POST" action="{{ route('admin.media.destroy', $m) }}" onsubmit="return confirm('Supprimer définitivement ce fichier ?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-600 hover:text-red-500">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="col-span-full py-10 text-center text-sm text-slate-500">Aucun fichier. Envoyez votre première image.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $media->links() }}</div>
@endsection
