@extends('layouts.admin')
@section('title', __('Messages'))
@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Messages') }}</h1>
    <p class="mt-1 max-w-2xl text-sm text-slate-500">{{ __('Messages envoyés par les formulaires de contact du site. Ils sont aussi envoyés par e-mail à l’adresse choisie dans les réglages.') }}</p>

    @if ($messages->isEmpty())
        <p class="mt-8 rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500">{{ __('Aucun message pour l’instant. Ajoutez le widget « Formulaire de contact » à une page pour en recevoir.') }}</p>
    @else
        <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr><th class="px-4 py-3">{{ __('De') }}</th><th class="px-4 py-3">{{ __('Message') }}</th><th class="px-4 py-3">{{ __('Page') }}</th><th class="px-4 py-3">{{ __('Reçu le') }}</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($messages as $m)
                        <tr class="{{ $m->read_at ? '' : 'bg-brand-50/40 font-medium' }}">
                            <td class="px-4 py-3"><a class="text-brand-700 hover:underline" href="{{ route('admin.messages.show', $m) }}">{{ $m->data['name'] ?? '—' }}</a><br><span class="text-xs font-normal text-slate-500">{{ $m->data['email'] ?? '' }}</span></td>
                            <td class="max-w-md px-4 py-3"><span class="line-clamp-2">{{ $m->data['subject'] ?? \Illuminate\Support\Str::limit($m->data['message'] ?? '', 140) }}</span></td>
                            <td class="px-4 py-3 text-slate-600">{{ $m->page?->title ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $m->created_at->translatedFormat('j M Y, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $messages->links() }}</div>
    @endif
@endsection
