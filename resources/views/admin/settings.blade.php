@extends('layouts.admin')
@section('title', __('Réglages'))
@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Réglages du site') }}</h1>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 max-w-2xl space-y-6">
        @csrf @method('PUT')
        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Identité') }}</h2>
            <x-field name="site_name" :label="__('Nom du site')" :value="$settings['site_name']" required />
            <x-field name="site_tagline" :label="__('Slogan')" :value="$settings['site_tagline']" />
            <x-field name="logo_url" :label="__('Adresse du logo')" :value="$settings['logo_url']" :hint="__('Copiez l’adresse d’une image depuis la médiathèque.')" />
            <x-field name="forms_email" type="email" :label="__('E-mail qui reçoit les messages des formulaires')" :value="$settings['forms_email'] ?? ''" :hint="__('Laissez vide pour utiliser l’e-mail du premier administrateur. Les messages sont aussi conservés dans « Messages ».')" />
        </section>

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Couleurs et police') }}</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach (['primary_color' => __('Principale'), 'secondary_color' => __('Secondaire'), 'accent_color' => __('Accent')] as $key => $label)
                    <label class="block text-sm font-medium text-slate-700">{{ $label }}
                        <input type="color" name="{{ $key }}" value="{{ old($key, $settings[$key]) }}" class="mt-1.5 block h-10 w-full cursor-pointer rounded-lg border border-slate-300 bg-white p-1">
                    </label>
                @endforeach
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">{{ __('Police du texte') }}
                    <select name="font_family" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($fonts as $font)<option @selected($settings['font_family'] === $font)>{{ $font }}</option>@endforeach
                    </select>
                </label>
                <label class="block text-sm font-medium text-slate-700">{{ __('Police des titres') }}
                    <select name="heading_font" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">{{ __('Comme le texte') }}</option>
                        @foreach ($fonts as $font)<option @selected(($settings['heading_font'] ?? null) === $font)>{{ $font }}</option>@endforeach
                    </select>
                </label>
            </div>

            <div>
                <p class="text-sm font-medium text-slate-700">{{ __('Couleurs personnalisées') }}</p>
                <p class="mb-2 text-xs text-slate-500">{{ __('Proposées dans le builder à côté des trois couleurs du site. Modifier une couleur ici met à jour tous les éléments qui l’utilisent.') }}</p>
                <div class="space-y-2" id="gp-custom-colors">
                    @foreach (\App\Builder\DesignTokens::customColors($settings['custom_colors'] ?? []) as $i => $color)
                        <div class="flex items-center gap-2" data-color-row>
                            <input type="hidden" name="custom_colors[{{ $i }}][id]" value="{{ $color['id'] }}">
                            <input type="color" name="custom_colors[{{ $i }}][value]" value="{{ preg_match('/^#[0-9a-f]{6}$/', $color['value']) ? $color['value'] : '#000000' }}" class="h-9 w-12 cursor-pointer rounded border border-slate-300 bg-white p-0.5">
                            <input name="custom_colors[{{ $i }}][name]" value="{{ $color['name'] }}" maxlength="40" placeholder="{{ __('Nom de la couleur') }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <button type="button" class="shrink-0 rounded px-2 py-1 text-sm text-slate-500 hover:bg-slate-100" data-remove-color title="{{ __('Retirer') }}">✕</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="mt-2 text-sm font-medium text-brand-600" id="gp-add-color">{{ __('+ Ajouter une couleur') }}</button>
                <template id="gp-color-template">
                    <div class="flex items-center gap-2" data-color-row>
                        <input type="color" name="custom_colors[__I__][value]" value="#aa4d26" class="h-9 w-12 cursor-pointer rounded border border-slate-300 bg-white p-0.5">
                        <input name="custom_colors[__I__][name]" maxlength="40" placeholder="{{ __('Nom de la couleur') }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <button type="button" class="shrink-0 rounded px-2 py-1 text-sm text-slate-500 hover:bg-slate-100" data-remove-color title="{{ __('Retirer') }}">✕</button>
                    </div>
                </template>
                <script>
                    (() => {
                        const list = document.getElementById('gp-custom-colors');
                        let next = list.children.length;
                        document.getElementById('gp-add-color').addEventListener('click', () => {
                            const html = document.getElementById('gp-color-template').innerHTML.replaceAll('__I__', String(next++));
                            list.insertAdjacentHTML('beforeend', html);
                            list.lastElementChild.querySelector('input[name$="[name]"]').focus();
                        });
                        list.addEventListener('click', (e) => {
                            if (e.target.closest('[data-remove-color]')) e.target.closest('[data-color-row]').remove();
                        });
                    })();
                </script>
            </div>
        </section>

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Transition entre les pages') }}</h2>
            <p class="text-xs text-slate-500">{{ __('Quand un visiteur passe d’une page à l’autre, la nouvelle page apparaît en douceur et l’en-tête reste en place. Sans effet dans les navigateurs qui ne le permettent pas, et pour les visiteurs qui demandent moins d’animations.') }}</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">{{ __('Effet') }}
                    <select name="page_transition" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach (['' => __('Aucun'), 'fade' => __('Fondu'), 'slide' => __('Glissement vers le haut'), 'zoom' => __('Zoom léger')] as $value => $label)
                            <option value="{{ $value }}" @selected((old('page_transition', $settings['page_transition'] ?? '')) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <x-field name="page_transition_duration" type="number" :label="__('Durée (ms)')" :value="$settings['page_transition_duration'] ?? 400" min="150" max="1500" step="50" />
            </div>
        </section>

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Référencement') }}</h2>
            <label class="flex items-start gap-3 text-sm text-slate-700">
                <input type="hidden" name="search_engines" value="0">
                <input type="checkbox" name="search_engines" value="1" class="mt-0.5 rounded border-slate-300" @checked(old('search_engines', $settings['search_engines'] ?? true))>
                <span><span class="font-medium">{{ __('Autoriser les moteurs de recherche à indexer le site') }}</span><br>
                <span class="text-xs text-slate-500">{{ __('Décochez pendant la préparation du site : Google et les autres moteurs ne l’afficheront pas dans leurs résultats.') }}</span></span>
            </label>
            <x-field name="og_image" :label="__('Image de partage par défaut')" :value="$settings['og_image'] ?? ''" :hint="__('Affichée quand une page est partagée sur WhatsApp, Facebook ou LinkedIn et qu’elle n’a pas d’image. Format conseillé : 1200 × 630 pixels, en JPEG ou PNG.')" />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="google_site_verification" :label="__('Code de vérification Google Search Console')" :value="$settings['google_site_verification'] ?? ''" :hint="__('Seulement le code (contenu de la balise meta), pas la balise entière.')" />
                <x-field name="bing_site_verification" :label="__('Code de vérification Bing Webmaster Tools')" :value="$settings['bing_site_verification'] ?? ''" />
            </div>
            <p class="text-xs text-slate-500">{{ __('Plan du site à déclarer aux moteurs de recherche :') }} <a href="{{ url('/sitemap.xml') }}" class="text-brand-600 hover:underline" target="_blank">{{ url('/sitemap.xml') }}</a></p>
        </section>

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Langues') }}</h2>
            <label class="block text-sm font-medium text-slate-700">{{ __('Langue par défaut') }}
                <select name="default_locale" class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($availableLocales as $code => $name)<option value="{{ $code }}" @selected(($settings['locales'][0] ?? 'fr') === $code)>{{ $name }}</option>@endforeach
                </select>
            </label>
            <div>
                <p class="mb-2 text-sm font-medium text-slate-700">{{ __('Langues du site') }}</p>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ($availableLocales as $code => $name)
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="locales[]" value="{{ $code }}" @checked(in_array($code, $settings['locales'], true)) class="rounded border-slate-300"> {{ $name }}</label>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between gap-3">
                <h2 class="font-semibold">{{ __('Assistant IA') }}</h2>
                @if ($aiConfigured)
                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ __('Activé') }}</span>
                @else
                    <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">{{ __('Clé API manquante') }}</span>
                @endif
            </div>
            <div>
                <label for="organization_description" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Présentez votre organisation') }}</label>
                <textarea id="organization_description" name="organization_description" rows="5" maxlength="3000"
                    placeholder="{{ __('Ex. : ONG basée à Goma depuis 2015. Nous scolarisons les enfants déplacés par la guerre au Nord-Kivu. Public : donateurs, partenaires internationaux et familles. Ton : chaleureux et sérieux.') }}"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('organization_description', $settings['organization_description'] ?? '') }}</textarea>
                <p class="mt-1 text-xs text-slate-500">{{ __('L’assistant s’en sert pour toutes ses rédactions : mission, lieu, public, ton, services. Plus c’est précis, meilleurs sont les résultats.') }}</p>
            </div>
            <div>
                <label for="anthropic_api_key" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Clé API Anthropic (Claude)') }}</label>
                <input id="anthropic_api_key" name="anthropic_api_key" type="password" autocomplete="off"
                    placeholder="{{ ! empty($settings['anthropic_api_key_hint']) && ! empty($settings['anthropic_api_key']) ? __('Clé enregistrée :hint — laissez vide pour la garder', ['hint' => $settings['anthropic_api_key_hint']]) : 'sk-ant-…' }}"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-slate-500">{{ __('Créez une clé sur console.anthropic.com. Elle est chiffrée et n’est jamais affichée.') }}</p>
                @error('anthropic_api_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @if (! empty($settings['anthropic_api_key']))
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_api_key" value="1" class="rounded border-slate-300"> {{ __('Supprimer la clé enregistrée') }}</label>
                @endif
            </div>
            <div class="border-t border-slate-100 pt-4">
                <label for="gp_license_key" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Ou : clé de licence GeniusPress') }}</label>
                <input id="gp_license_key" name="gp_license_key" type="password" autocomplete="off"
                    placeholder="{{ ! empty($settings['gp_license_key_hint']) && ! empty($settings['gp_license_key']) ? __('Clé enregistrée :hint — laissez vide pour la garder', ['hint' => $settings['gp_license_key_hint']]) : 'gpl_…' }}"
                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-slate-500">{{ __('Sans clé Anthropic : l’IA passe par la plateforme GeniusPress et utilise les crédits IA de votre compte client, qui donne aussi accès aux modèles payants. Créez la clé dans votre espace client. Si elle est enregistrée, elle remplace la clé Anthropic.') }}
                    <a href="{{ rtrim(config('geniuspress.license.platform_url'), '/') }}/compte" target="_blank" rel="noopener" class="text-brand-600 hover:underline">{{ __('Espace client ↗') }}</a></p>
                @error('gp_license_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @if (! empty($settings['gp_license_key']))
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_license_key" value="1" class="rounded border-slate-300"> {{ __('Supprimer la clé de licence') }}</label>
                @endif
            </div>
            <p class="text-xs text-slate-500">{{ __('Ce mois-ci : :requests demandes · :tokens jetons utilisés.', ['requests' => number_format($aiUsage->requests ?? 0, 0, ',', ' '), 'tokens' => number_format(($aiUsage->input_tokens ?? 0) + ($aiUsage->output_tokens ?? 0), 0, ',', ' ')]) }}</p>
        </section>

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif
        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ __('Enregistrer les réglages') }}</button>
    </form>
@endsection
