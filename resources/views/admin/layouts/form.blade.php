@extends('layouts.admin')
@section('title', __('Gabarit : :name', ['name' => $layout->name]))
@section('content')
    <a href="{{ route('admin.layouts.index') }}" class="text-sm text-slate-500 hover:text-slate-900">{{ __('← Thème') }}</a>
    <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ __('Gabarit : :name', ['name' => $layout->name]) }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ $types[$layout->type] ?? $layout->type }}</p>

    @php
        // Règles existantes au format de l'éditeur (« page:12 »), plus deux lignes vides.
        $rules = collect($layout->conditions ?? [])->map(fn ($r) => ['mode' => $r['mode'], 'what' => $r['target'].(isset($r['id']) ? ':'.$r['id'] : '')])->all();
        $rules = old('rules', [...$rules, ['mode' => 'include', 'what' => ''], ['mode' => 'include', 'what' => '']]);
    @endphp

    <form method="POST" action="{{ route('admin.layouts.update', $layout) }}" class="mt-6 max-w-3xl space-y-6 rounded-xl border border-slate-200 bg-white p-6">
        @csrf @method('PUT')
        <x-field name="name" :label="__('Nom')" :value="$layout->name" required />
        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label for="status" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Statut') }}</label>
                <select id="status" name="status" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="published" @selected(old('status', $layout->status) === 'published')>{{ __('Actif') }}</option>
                    <option value="draft" @selected(old('status', $layout->status) === 'draft')>{{ __('Inactif') }}</option>
                </select>
            </div>
            <div>
                <label for="locale" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Langue') }}</label>
                <select id="locale" name="locale" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">{{ __('Toutes les langues') }}</option>
                    @foreach ($locales as $loc)<option value="{{ $loc }}" @selected(old('locale', $layout->locale) === $loc)>{{ strtoupper($loc) }}</option>@endforeach
                </select>
            </div>
            <x-field name="priority" type="number" :label="__('Priorité')" :value="$layout->priority" :hint="__('La plus haute l’emporte.')" />
        </div>

        @if ($popup)
            @php
                $opt = fn ($key) => old('options.'.$key, $popup[$key]);
                $select = 'block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm';
            @endphp
            <fieldset class="space-y-5 border-t border-slate-100 pt-5">
                <legend class="text-sm font-semibold text-slate-900">{{ __('Ouverture de la popup') }}</legend>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="opt-trigger" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Déclencheur') }}</label>
                        <select id="opt-trigger" name="options[trigger]" class="{{ $select }}">
                            @foreach (['load' => __('Au chargement de la page'), 'delay' => __('Après un délai'), 'scroll' => __('Après un défilement'), 'exit' => __('Quand le visiteur s’apprête à partir'), 'click' => __('Au clic sur un lien seulement')] as $v => $l)
                                <option value="{{ $v }}" @selected($opt('trigger') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-field name="options[delay]" type="number" :label="__('Délai (secondes)')" :value="$opt('delay')" min="0" max="300" />
                    <x-field name="options[scroll]" type="number" :label="__('Défilement (% de la page)')" :value="$opt('scroll')" min="5" max="100" />
                </div>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="opt-frequency" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Fréquence pour un même visiteur') }}</label>
                        <select id="opt-frequency" name="options[frequency]" class="{{ $select }}">
                            @foreach (['always' => __('À chaque page vue'), 'session' => __('Une fois par visite'), 'days' => __('Tous les N jours'), 'once' => __('Une seule fois')] as $v => $l)
                                <option value="{{ $v }}" @selected($opt('frequency') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-field name="options[days]" type="number" :label="__('Nombre de jours')" :value="$opt('days')" min="1" max="365" />
                    <div>
                        <label for="opt-devices" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Appareils') }}</label>
                        <select id="opt-devices" name="options[devices]" class="{{ $select }}">
                            @foreach (['all' => __('Tous'), 'desktop' => __('Ordinateur et tablette'), 'mobile' => __('Mobile seulement')] as $v => $l)
                                <option value="{{ $v }}" @selected($opt('devices') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">
                    {!! __('Pour l’ouvrir depuis un bouton ou un lien, utilisez l’adresse :anchor. Un lien :close dans la popup la ferme.', ['anchor' => '<code class="font-mono text-brand-700">#popup-'.$layout->id.'</code>', 'close' => '<code class="font-mono text-brand-700">#fermer</code>']) !!}
                </p>
            </fieldset>

            <fieldset class="space-y-5 border-t border-slate-100 pt-5">
                <legend class="text-sm font-semibold text-slate-900">{{ __('Apparence de la popup') }}</legend>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="opt-position" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Position') }}</label>
                        <select id="opt-position" name="options[position]" class="{{ $select }}">
                            @foreach (['center' => __('Au centre'), 'top' => __('En haut'), 'bottom' => __('En bas'), 'bottom-left' => __('En bas à gauche'), 'bottom-right' => __('En bas à droite'), 'left' => __('Panneau à gauche'), 'right' => __('Panneau à droite')] as $v => $l)
                                <option value="{{ $v }}" @selected($opt('position') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="opt-animation" class="mb-1.5 block text-sm font-medium text-slate-700">{{ __('Animation') }}</label>
                        <select id="opt-animation" name="options[animation]" class="{{ $select }}">
                            @foreach (['fade' => __('Fondu'), 'zoom' => __('Zoom'), 'slide-up' => __('Glisse vers le haut'), 'slide-down' => __('Glisse vers le bas'), 'slide-left' => __('Glisse vers la gauche'), 'slide-right' => __('Glisse vers la droite')] as $v => $l)
                                <option value="{{ $v }}" @selected($opt('animation') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-field name="options[width]" type="number" :label="__('Largeur (px)')" :value="$opt('width')" min="260" max="1400" />
                </div>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-field name="options[radius]" type="number" :label="__('Arrondi des coins (px)')" :value="$opt('radius')" min="0" max="60" />
                    <x-field name="options[background]" :label="__('Couleur de fond')" :value="$opt('background')" />
                    <x-field name="options[overlay_color]" :label="__('Couleur du voile')" :value="$opt('overlay_color')" />
                </div>
                <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-700">
                    @foreach (['overlay' => __('Voile derrière la popup'), 'close_outside' => __('Fermer en cliquant sur le voile'), 'close_button' => __('Bouton de fermeture')] as $key => $l)
                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="options[{{ $key }}]" value="0">
                            <input type="checkbox" name="options[{{ $key }}]" value="1" @checked($opt($key)) class="rounded border-slate-300 text-brand-600">
                            {{ $l }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endif

        @if ($layout->type === 'mega')
            <p class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">{{ __('Ce panneau s’affiche dans les entrées du widget « Méga-menu » qui le choisissent ; il n’a pas de conditions d’affichage.') }}</p>
        @else
        <fieldset>
            <legend class="text-sm font-semibold text-slate-900">{{ __('Conditions d’affichage') }}</legend>
            <p class="mt-1 text-xs text-slate-500">{{ __('Le gabarit s’affiche là où au moins une règle « Afficher sur » correspond, sauf là où une règle « Sauf sur » correspond.') }}</p>
            <div id="rules" class="mt-3 space-y-2">
                @foreach ($rules as $i => $rule)
                    <div class="flex flex-wrap gap-2" data-rule>
                        <select name="rules[{{ $i }}][mode]" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="include" @selected(($rule['mode'] ?? '') === 'include')>{{ __('Afficher sur') }}</option>
                            <option value="exclude" @selected(($rule['mode'] ?? '') === 'exclude')>{{ __('Sauf sur') }}</option>
                        </select>
                        <select name="rules[{{ $i }}][what]" class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">—</option>
                            @foreach ($targets as $group => $options)
                                <optgroup label="{{ $group }}">
                                    @foreach ($options as $value => $label)<option value="{{ $value }}" @selected(($rule['what'] ?? '') === $value)>{{ $label }}</option>@endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
            <button type="button" class="mt-2 text-sm font-medium text-brand-600" onclick="(() => { const rows = document.querySelectorAll('[data-rule]'); const row = rows[rows.length - 1].cloneNode(true); const n = rows.length; row.querySelectorAll('select').forEach((s) => { s.name = s.name.replace(/rules\[\d+\]/, 'rules[' + n + ']'); s.selectedIndex = 0; }); document.getElementById('rules').append(row); })()">{{ __('+ Ajouter une règle') }}</button>
        </fieldset>
        @endif

        <div class="flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
            <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Enregistrer') }}</button>
            <a href="{{ route('admin.layouts.builder', $layout) }}" class="text-sm font-medium text-brand-600">{{ __('Ouvrir le builder') }}</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.layouts.destroy', $layout) }}" class="mt-6" onsubmit="return confirm(@js(__('Mettre ce gabarit à la corbeille ?')))">
        @csrf @method('DELETE')
        <button class="text-sm font-medium text-red-600 hover:text-red-500">{{ __('Mettre à la corbeille') }}</button>
    </form>
@endsection
