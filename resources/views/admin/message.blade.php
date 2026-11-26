@extends('layouts.admin')
@section('title', __('Message'))
@section('content')
    <a href="{{ route('admin.messages.index') }}" class="text-sm text-brand-600 hover:underline">← {{ __('Tous les messages') }}</a>
    <div class="mt-4 max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">{{ $message->data['subject'] ?? __('Message de :name', ['name' => $message->data['name'] ?? '—']) }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Reçu le :date depuis la page « :page »', ['date' => $message->created_at->translatedFormat('j F Y à H:i'), 'page' => $message->page?->title ?? '—']) }}</p>
            </div>
            <form method="post" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm(@js(__('Supprimer définitivement ce message ?')))">
                @csrf @method('delete')
                <button class="rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-50">{{ __('Supprimer') }}</button>
            </form>
        </div>
        <dl class="mt-6 grid gap-3 text-sm sm:grid-cols-[10rem_1fr]">
            @foreach (['name' => __('Nom'), 'email' => __('E-mail'), 'phone' => __('Téléphone')] as $key => $label)
                @isset($message->data[$key])
                    <dt class="font-medium text-slate-500">{{ $label }}</dt>
                    <dd>@if ($key === 'email')<a class="text-brand-700 hover:underline" href="mailto:{{ $message->data[$key] }}">{{ $message->data[$key] }}</a>@else{{ $message->data[$key] }}@endif</dd>
                @endisset
            @endforeach
        </dl>
        <div class="mt-6 whitespace-pre-line rounded-lg bg-slate-50 p-4 text-sm leading-relaxed">{{ $message->data['message'] ?? '' }}</div>
        @isset($message->data['email'])
            <a href="mailto:{{ $message->data['email'] }}?subject={{ rawurlencode(__('Re : :subject', ['subject' => $message->data['subject'] ?? \App\Models\Setting::read('site_name')])) }}" class="mt-4 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Répondre par e-mail') }}</a>
        @endisset
    </div>
@endsection
