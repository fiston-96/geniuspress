@extends('layouts.admin')
@section('title', __('Message'))
@section('content')
    <a href="{{ route('admin.messages.index') }}" class="text-sm text-brand-600 hover:underline">← {{ __('Tous les messages') }}</a>
    <div class="mt-4 max-w-3xl rounded-xl border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold">{{ $message->data['subject'] ?? $message->form_name ?? __('Message de :name', ['name' => $message->senderName() ?? $message->senderEmail() ?? '—']) }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Reçu le :date depuis la page « :page »', ['date' => $message->created_at->translatedFormat('j F Y à H:i'), 'page' => $message->source()]) }}</p>
            </div>
            <form method="post" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm(@js(__('Supprimer définitivement ce message ?')))">
                @csrf @method('delete')
                <button class="rounded-lg border border-red-200 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-50">{{ __('Supprimer') }}</button>
            </form>
        </div>
        <dl class="mt-6 grid gap-3 text-sm sm:grid-cols-[10rem_1fr]">
            {{-- Toutes les réponses, dans l'ordre du formulaire (le message du formulaire de contact est affiché à part). --}}
            @foreach ($message->fields() as [$label, $value])
                @continue(! $message->isCustom() && $label === __('Message'))
                <dt class="font-medium text-slate-500">{{ $label }}</dt>
                <dd class="whitespace-pre-line">@if (filter_var($value, FILTER_VALIDATE_EMAIL))<a class="text-brand-700 hover:underline" href="mailto:{{ $value }}">{{ $value }}</a>@else{{ $value }}@endif</dd>
            @endforeach
        </dl>
        @if (! $message->isCustom() && isset($message->data['message']))
            <div class="mt-6 whitespace-pre-line rounded-lg bg-slate-50 p-4 text-sm leading-relaxed">{{ $message->data['message'] }}</div>
        @endif
        @if ($message->senderEmail())
            <a href="mailto:{{ $message->senderEmail() }}?subject={{ rawurlencode(__('Re : :subject', ['subject' => $message->data['subject'] ?? \App\Models\Setting::read('site_name')])) }}" class="mt-4 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Répondre par e-mail') }}</a>
        @endif
    </div>
@endsection
