@extends('layouts.admin')
@section('title', 'Réglages')
@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">Réglages du site</h1>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 max-w-2xl space-y-6">
        @csrf @method('PUT')
        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">Identité</h2>
            <x-field name="site_name" label="Nom du site" :value="$settings['site_name']" required />
            <x-field name="site_tagline" label="Slogan" :value="$settings['site_tagline']" />
            <x-field name="logo_url" label="Adresse du logo" :value="$settings['logo_url']" hint="Copiez l’adresse d’une image depuis la médiathèque." />
        </section>

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">Couleurs et police</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach (['primary_color' => 'Principale', 'secondary_color' => 'Secondaire', 'accent_color' => 'Accent'] as $key => $label)
                    <label class="block text-sm font-medium text-slate-700">{{ $label }}
                        <input type="color" name="{{ $key }}" value="{{ old($key, $settings[$key]) }}" class="mt-1.5 block h-10 w-full cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                    </label>
                @endforeach
            </div>
            <label class="block text-sm font-medium text-slate-700">Police
                <select name="font_family" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($fonts as $font)<option @selected($settings['font_family'] === $font)>{{ $font }}</option>@endforeach
                </select>
            </label>
        </section>

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">Langues</h2>
            <label class="block text-sm font-medium text-slate-700">Langue par défaut
                <select name="default_locale" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($availableLocales as $code => $name)<option value="{{ $code }}" @selected(($settings['locales'][0] ?? 'fr') === $code)>{{ $name }}</option>@endforeach
                </select>
            </label>
            <div>
                <p class="mb-2 text-sm font-medium text-slate-700">Langues du site</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ($availableLocales as $code => $name)
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="locales[]" value="{{ $code }}" @checked(in_array($code, $settings['locales'], true)) class="rounded border-slate-300"> {{ $name }}</label>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-semibold">Assistant IA</h2>
                @if ($aiConfigured)
                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Activé</span>
                @else
                    <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">Clé API manquante</span>
                @endif
            </div>
            <div>
                <label for="organization_description" class="mb-1.5 block text-sm font-medium text-slate-700">Présentez votre organisation</label>
                <textarea id="organization_description" name="organization_description" rows="5" maxlength="3000"
                    placeholder="Ex. : ONG basée à Goma depuis 2015. Nous scolarisons les enfants déplacés par la guerre au Nord-Kivu. Public : donateurs, partenaires internationaux et familles. Ton : chaleureux et sérieux."
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('organization_description', $settings['organization_description'] ?? '') }}</textarea>
                <p class="mt-1 text-xs text-slate-500">L’assistant s’en sert pour toutes ses rédactions : mission, lieu, public, ton, services. Plus c’est précis, meilleurs sont les résultats.</p>
            </div>
            <div>
                <label for="anthropic_api_key" class="mb-1.5 block text-sm font-medium text-slate-700">Clé API Anthropic (Claude)</label>
                <input id="anthropic_api_key" name="anthropic_api_key" type="password" autocomplete="off"
                    placeholder="{{ ! empty($settings['anthropic_api_key_hint']) && ! empty($settings['anthropic_api_key']) ? 'Clé enregistrée '.$settings['anthropic_api_key_hint'].' — laissez vide pour la garder' : 'sk-ant-…' }}"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-slate-500">Créez une clé sur console.anthropic.com. Elle est chiffrée et n’est jamais affichée.</p>
                @error('anthropic_api_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @if (! empty($settings['anthropic_api_key']))
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_api_key" value="1" class="rounded border-slate-300"> Supprimer la clé enregistrée</label>
                @endif
            </div>
            <p class="text-xs text-slate-500">Ce mois-ci : {{ number_format($aiUsage->requests ?? 0, 0, ',', ' ') }} demandes · {{ number_format(($aiUsage->input_tokens ?? 0) + ($aiUsage->output_tokens ?? 0), 0, ',', ' ') }} jetons utilisés.</p>
        </section>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Enregistrer les réglages</button>
    </form>
@endsection
