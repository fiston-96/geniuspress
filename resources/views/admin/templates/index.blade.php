@extends('layouts.admin')
@section('title', __('Modèles'))
@section('content')
@php
    $field = 'mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm';
    $sectorName = fn ($key) => $sectors[$key]['name'][$lang] ?? $key;
    $hasProfile = ! empty($profile['name']);
@endphp

<h1 class="text-2xl font-semibold tracking-tight">{{ __('Modèles de sites') }}</h1>
<p class="mt-1 max-w-3xl text-sm text-slate-500">{{ __(':count modèles complets de 8 pages, en français et en anglais, pour tous les secteurs. Prévisualisez-les, installez-les, puis modifiez tout dans le builder — ou laissez l’assistant IA rédiger les textes pour votre organisation.', ['count' => count($templates)]) }}</p>

@if ($error)
    <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ $error }}</div>
@endif
@if ($errors->any())
    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-disc pl-5">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif

{{-- ─── Assistant IA : questionnaire ─────────────────────────────────── --}}
<details class="mt-6 rounded-xl border border-brand-200 bg-gradient-to-br from-brand-50 to-white p-6" @if (! $hasProfile || old('name')) open @endif>
    <summary class="cursor-pointer list-none">
        <span class="flex items-start gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand-600 text-lg text-white">✨</span>
            <span>
                <span class="block font-semibold">{{ __('Créer mon site avec l’assistant IA') }}</span>
                <span class="block text-sm text-slate-600">{{ __('Répondez à quelques questions : l’assistant vous propose les modèles les plus adaptés, puis rédige les textes à votre place.') }}</span>
            </span>
        </span>
    </summary>

    <form method="post" action="{{ route('admin.templates.recommend') }}" class="mt-6 space-y-5">
        @csrf
        @unless ($aiConfigured)
            <p class="rounded-lg bg-white px-4 py-3 text-sm text-slate-600 ring-1 ring-slate-200">{{ __('L’assistant IA n’est pas configuré : les modèles seront proposés selon votre secteur, et vous rédigerez les textes vous-même. Ajoutez une clé API dans Réglages pour profiter de la rédaction automatique.') }}</p>
        @endunless
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-medium text-slate-700">{{ __('Nom de votre organisation') }} *
                <input name="name" required maxlength="120" value="{{ old('name', $profile['name'] ?? '') }}" class="{{ $field }}" placeholder="{{ __('Ex. : ONG Espoir Kivu') }}">
            </label>
            <label class="block text-sm font-medium text-slate-700">{{ __('Secteur') }}
                <select name="sector" class="{{ $field }}">
                    <option value="">{{ __('Laisser l’assistant deviner') }}</option>
                    @foreach ($sectors as $key => $info)<option value="{{ $key }}" @selected(old('sector', $profile['sector'] ?? '') === $key)>{{ $info['name'][$lang] ?? $key }}</option>@endforeach
                    <option value="autre" @selected(old('sector', $profile['sector'] ?? '') === 'autre')>{{ __('Autre') }}</option>
                </select>
            </label>
        </div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Que fait votre organisation, pour qui, et depuis quand ?') }} *
            <textarea name="description" required rows="3" maxlength="3000" class="{{ $field }}" placeholder="{{ __('Ex. : Depuis 2015, nous scolarisons les enfants déplacés de Goma et formons les mères à de petits métiers.') }}">{{ old('description', $profile['description'] ?? '') }}</textarea>
        </label>
        <label class="block text-sm font-medium text-slate-700">{{ __('Vos services, programmes ou produits (un par ligne)') }}
            <textarea name="offerings" rows="3" maxlength="3000" class="{{ $field }}">{{ old('offerings', $profile['offerings'] ?? '') }}</textarea>
        </label>
        <div class="grid gap-4 sm:grid-cols-3">
            <label class="block text-sm font-medium text-slate-700">{{ __('Public visé') }}
                <input name="audience" maxlength="500" value="{{ old('audience', $profile['audience'] ?? '') }}" class="{{ $field }}" placeholder="{{ __('Donateurs, familles, partenaires…') }}">
            </label>
            <label class="block text-sm font-medium text-slate-700">{{ __('Ville') }}
                <input name="city" maxlength="120" value="{{ old('city', $profile['city'] ?? '') }}" class="{{ $field }}">
            </label>
            <label class="block text-sm font-medium text-slate-700">{{ __('Pays') }}
                <input name="country" maxlength="120" value="{{ old('country', $profile['country'] ?? '') }}" class="{{ $field }}">
            </label>
        </div>
        <div class="grid gap-4 sm:grid-cols-4">
            <label class="block text-sm font-medium text-slate-700">{{ __('Ton') }}
                <select name="tone" class="{{ $field }}">
                    @foreach (['chaleureux' => __('Chaleureux'), 'professionnel' => __('Professionnel'), 'institutionnel' => __('Institutionnel'), 'dynamique' => __('Dynamique'), 'elegant' => __('Élégant')] as $value => $label)
                        <option value="{{ $value }}" @selected(old('tone', $profile['tone'] ?? 'chaleureux') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block text-sm font-medium text-slate-700">{{ __('Téléphone') }}
                <input name="phone" maxlength="60" value="{{ old('phone', $profile['phone'] ?? '') }}" class="{{ $field }}">
            </label>
            <label class="block text-sm font-medium text-slate-700">{{ __('E-mail') }}
                <input name="email" type="email" maxlength="190" value="{{ old('email', $profile['email'] ?? '') }}" class="{{ $field }}">
            </label>
            <label class="block text-sm font-medium text-slate-700">{{ __('Adresse') }}
                <input name="address" maxlength="300" value="{{ old('address', $profile['address'] ?? '') }}" class="{{ $field }}">
            </label>
        </div>
        <label class="block text-sm font-medium text-slate-700">{{ __('Autres informations utiles (horaires, chiffres clés, prix…)') }}
            <textarea name="extra" rows="2" maxlength="2000" class="{{ $field }}">{{ old('extra', $profile['extra'] ?? '') }}</textarea>
        </label>
        <p class="text-xs text-slate-500">{{ __('L’assistant n’invente jamais de chiffres, de noms ni de contacts : ce qu’il ne sait pas reste entre crochets, à compléter.') }}</p>
        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ $aiConfigured ? __('Me proposer des modèles') : __('Voir les modèles de mon secteur') }}</button>
    </form>
</details>

{{-- ─── Propositions ─────────────────────────────────────────────────── --}}
@if (! empty($recommendations['items']))
    <section id="propositions" class="mt-8">
        <h2 class="text-lg font-semibold">{{ __('Nos propositions pour :name', ['name' => $profile['name'] ?? '']) }}</h2>
        @if (! empty($recommendations['sector']))<p class="text-sm text-slate-500">{{ __('Secteur : :sector', ['sector' => $sectorName($recommendations['sector'])]) }}</p>@endif
        <div class="mt-4 grid gap-5 lg:grid-cols-3">
            @foreach ($recommendations['items'] as $i => $item)
                @continue(! isset($byId[$item['id']]))
                @include('admin.templates.card', ['t' => $byId[$item['id']], 'reason' => $item['reason'], 'highlight' => $i === 0])
            @endforeach
        </div>
    </section>
@endif

{{-- ─── Catalogue ────────────────────────────────────────────────────── --}}
@if ($templates)
    <section class="mt-10">
        <div class="flex flex-wrap items-center gap-2" id="gp-sector-filter">
            <button type="button" data-sector="" class="rounded-full bg-slate-900 px-3 py-1 text-sm font-medium text-white">{{ __('Tous') }} ({{ count($templates) }})</button>
            @foreach ($sectors as $key => $info)
                <button type="button" data-sector="{{ $key }}" class="rounded-full bg-white px-3 py-1 text-sm font-medium text-slate-700 ring-1 ring-slate-200 hover:ring-slate-400">{{ $info['name'][$lang] ?? $key }} ({{ collect($templates)->where('sector', $key)->count() }})</button>
            @endforeach
        </div>
        <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-3" id="gp-templates">
            @foreach ($templates as $t)
                @include('admin.templates.card', ['t' => $t, 'reason' => null, 'highlight' => false])
            @endforeach
        </div>
    </section>
    <script>
        (() => {
            const buttons = document.querySelectorAll('#gp-sector-filter [data-sector]');
            buttons.forEach((button) => button.addEventListener('click', () => {
                buttons.forEach((b) => { const on = b === button; b.classList.toggle('bg-slate-900', on); b.classList.toggle('text-white', on); b.classList.toggle('bg-white', !on); b.classList.toggle('ring-1', !on); });
                document.querySelectorAll('#gp-templates [data-template]').forEach((card) => {
                    card.hidden = button.dataset.sector !== '' && card.dataset.sector !== button.dataset.sector;
                });
            }));
        })();
    </script>
@endif
@endsection
