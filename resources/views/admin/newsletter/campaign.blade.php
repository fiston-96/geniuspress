@extends('layouts.admin')
@section('title', $campaign->exists ? $campaign->subject : __('Nouvelle campagne'))
@section('content')
@php
    $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 disabled:bg-slate-50';
    $editable = ! $campaign->exists || $campaign->isDraft();
@endphp
    <a href="{{ route('admin.newsletter.index') }}" class="text-sm text-slate-500 hover:underline">← {{ __('Newsletter') }}</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $campaign->exists ? $campaign->subject : __('Nouvelle campagne') }}</h1>

    @unless ($editable)
        <p class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            {{ $campaign->status === 'sent' ? __('Campagne envoyée le :date à :count abonnés.', ['date' => $campaign->sent_at?->translatedFormat('j M Y, H:i'), 'count' => $campaign->sent_count]) : __('Envoi en cours : :sent sur :total.', ['sent' => $campaign->sent_count, 'total' => $campaign->recipients]) }}
        </p>
    @endunless

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ $campaign->exists ? route('admin.newsletter.campaigns.update', $campaign) : route('admin.newsletter.campaigns.store') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-5">
            @csrf @if ($campaign->exists) @method('PUT') @endif
            <div>
                <label for="subject" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Objet de l’e-mail') }}</label>
                <input id="subject" name="subject" value="{{ old('subject', $campaign->subject) }}" required maxlength="190" @disabled(! $editable) class="{{ $input }}">
                @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="content" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Message') }}</label>
                <textarea id="content" name="content" rows="16" required @disabled(! $editable) class="{{ $input }} font-mono text-[13px] leading-relaxed">{{ old('content', $campaign->content) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">{{ __('Mise en forme simple : # Titre, **gras**, *italique*, - liste, [texte du lien](https://…). Laissez une ligne vide entre deux paragraphes.') }}</p>
                @error('content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="locale" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Destinataires') }}</label>
                <select id="locale" name="locale" @disabled(! $editable) class="{{ $input }}">
                    <option value="">{{ __('Tous les abonnés') }}</option>
                    @foreach (config('geniuspress.ui_locales') as $code => $name)
                        <option value="{{ $code }}" @selected(old('locale', $campaign->locale) === $code)>{{ __('Abonnés en :language', ['language' => $name]) }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">{{ trans_choice('{0} Aucun abonné inscrit pour l’instant.|{1} 1 abonné inscrit.|[2,*] :count abonnés inscrits.', $recipients) }}</p>
            </div>
            @if ($editable)
                <button class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Enregistrer le brouillon') }}</button>
            @endif
        </form>

        <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-[#fbf7f0] p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Aperçu') }}</p>
                <div class="mt-3 rounded-lg bg-white p-5 text-sm leading-relaxed text-slate-700 [&_a]:text-brand-700 [&_a]:underline [&_h1]:mb-2 [&_h1]:text-lg [&_h1]:font-bold [&_h2]:mb-2 [&_h2]:font-bold [&_li]:ml-5 [&_li]:list-disc [&_p]:mb-3">
                    @if ($campaign->content)
                        {!! \Illuminate\Support\Str::markdown(strip_tags($campaign->content), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                    @else
                        <p class="text-slate-400">{{ __('Enregistrez le brouillon pour voir l’aperçu.') }}</p>
                    @endif
                </div>
            </div>

            @if ($campaign->exists)
                <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.newsletter.campaigns.test', $campaign) }}">@csrf
                        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">{{ __('M’envoyer un essai') }}</button>
                    </form>
                    @if ($campaign->isDraft())
                        <form method="POST" action="{{ route('admin.newsletter.campaigns.send', $campaign) }}" onsubmit="return confirm(@js(trans_choice('{0} Aucun abonné ne recevra cette campagne.|{1} Envoyer cette campagne à 1 abonné ?|[2,*] Envoyer cette campagne à :count abonnés ? Elle ne pourra plus être modifiée.', $recipients)))">@csrf
                            <button class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500">{{ __('Envoyer maintenant') }}</button>
                        </form>
                    @endif
                    @if ($campaign->status !== 'sending')
                        <form method="POST" action="{{ route('admin.newsletter.campaigns.destroy', $campaign) }}" onsubmit="return confirm(@js(__('Supprimer cette campagne ?')))">@csrf @method('DELETE')
                            <button class="rounded-lg px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50">{{ __('Supprimer') }}</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
