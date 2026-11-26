@extends('layouts.admin')
@section('title', __('Apparence'))
@section('content')
    <h1 class="text-2xl font-semibold tracking-tight">{{ __('Apparence du site') }}</h1>
    <p class="mt-1 max-w-2xl text-sm text-slate-500">{{ __('L’en-tête et le pied de page s’affichent sur toutes les pages publiques. Les menus se règlent pour chaque langue du site.') }}</p>

    @php
        $input = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm';
        $linkRows = function (string $name, array $links) use ($input) {
            $rows = array_pad($links, max(count($links) + 1, 3), ['label' => '', 'url' => '']);
            $html = '';
            foreach ($rows as $i => $link) {
                $html .= '<div class="grid grid-cols-[1fr_1.4fr_auto] gap-2" data-link-row>'
                    .'<input class="'.$input.'" name="'.$name.'['.$i.'][label]" value="'.e($link['label']).'" placeholder="'.e(__('Libellé')).'" maxlength="60">'
                    .'<input class="'.$input.'" name="'.$name.'['.$i.'][url]" value="'.e($link['url']).'" placeholder="/a-propos">'
                    .'<button type="button" class="rounded-lg px-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700" data-remove-row title="'.e(__('Retirer')).'">✕</button>'
                    .'</div>';
            }

            return $html;
        };
    @endphp

    <form method="POST" action="{{ route('admin.appearance.update') }}" class="mt-6 max-w-3xl space-y-6">
        @csrf @method('PUT')

        <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('En-tête') }}</h2>
            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" name="header[enabled]" value="1" @checked($nav['header']['enabled']) class="rounded border-slate-300"> {{ __('Afficher l’en-tête') }}</label>
                <label class="flex items-center gap-2"><input type="checkbox" name="header[sticky]" value="1" @checked($nav['header']['sticky']) class="rounded border-slate-300"> {{ __('Toujours visible en haut de l’écran') }}</label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">{{ __('Style') }}
                    <select name="header[style]" class="mt-1.5 {{ $input }}">
                        <option value="light" @selected($nav['header']['style'] === 'light')>{{ __('Clair') }}</option>
                        <option value="dark" @selected($nav['header']['style'] === 'dark')>{{ __('Sombre (couleur secondaire)') }}</option>
                    </select>
                </label>
            </div>
            <label class="block text-sm font-medium text-slate-700">{{ __('Icône du site (favicon)') }}
                <input name="favicon_url" value="{{ $faviconUrl }}" placeholder="{{ __('Adresse d’une image carrée depuis la médiathèque') }}" class="mt-1.5 {{ $input }}">
            </label>
        </section>

        @foreach ($locales as $code => $name)
            @php $l = $nav['locales'][$code] ?? []; @endphp
            <section class="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
                <h2 class="font-semibold">{{ __('Menus en :language', ['language' => $name]) }}</h2>

                <div>
                    <p class="mb-2 text-sm font-medium text-slate-700">{{ __('Menu principal') }}</p>
                    <div class="space-y-2" data-link-list="locales[{{ $code }}][menu]">{!! $linkRows("locales[{$code}][menu]", $l['menu'] ?? []) !!}</div>
                    <button type="button" class="mt-2 text-sm font-medium text-brand-600" data-add-row>{{ __('+ Ajouter un lien') }}</button>
                </div>

                <label class="block text-sm font-medium text-slate-700">{{ __('Texte du bouton d’action') }}
                    <input name="locales[{{ $code }}][cta_label]" value="{{ $l['cta_label'] ?? '' }}" maxlength="40" placeholder="{{ __('Faire un don') }}" class="mt-1.5 {{ $input }}">
                </label>

                <label class="block text-sm font-medium text-slate-700">{{ __('Lien du bouton d’action') }}
                    <input name="locales[{{ $code }}][cta_url]" value="{{ $l['cta_url'] ?? $nav['header']['cta_url'] }}" placeholder="/contact" class="mt-1.5 {{ $input }}">
                </label>

                <label class="block text-sm font-medium text-slate-700">{{ __('Texte du pied de page') }}
                    <textarea name="locales[{{ $code }}][footer_text]" rows="2" maxlength="500" class="mt-1.5 {{ $input }}">{{ $l['footer_text'] ?? '' }}</textarea>
                </label>

                <div>
                    <p class="mb-2 text-sm font-medium text-slate-700">{{ __('Liens du pied de page') }}</p>
                    <div class="space-y-2" data-link-list="locales[{{ $code }}][footer_links]">{!! $linkRows("locales[{$code}][footer_links]", $l['footer_links'] ?? []) !!}</div>
                    <button type="button" class="mt-2 text-sm font-medium text-brand-600" data-add-row>{{ __('+ Ajouter un lien') }}</button>
                </div>
            </section>
        @endforeach

        <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-semibold">{{ __('Pied de page') }}</h2>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="footer[enabled]" value="1" @checked($nav['footer']['enabled']) class="rounded border-slate-300"> {{ __('Afficher le pied de page') }}</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="footer[credit]" value="1" @checked($nav['footer']['credit']) class="rounded border-slate-300"> {{ __('Afficher « Propulsé par GeniusPress »') }}</label>
        </section>

        <button class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-500">{{ __('Enregistrer l’apparence') }}</button>
    </form>

    <script>
        // Ajout et retrait de lignes de liens (les lignes vides sont ignorées à l'enregistrement).
        document.addEventListener('click', (e) => {
            const add = e.target.closest('[data-add-row]');
            if (add) {
                const list = add.previousElementSibling;
                const rows = list.querySelectorAll('[data-link-row]');
                if (rows.length >= {{ \App\Site\Navigation::MAX_LINKS }}) return;
                const row = rows[rows.length - 1].cloneNode(true);
                const i = rows.length;
                row.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                    input.name = input.name.replace(/\[\d+\](\[(label|url)\])$/, `[${i}]$1`);
                });
                list.appendChild(row);
            }
            const remove = e.target.closest('[data-remove-row]');
            if (remove) {
                const row = remove.closest('[data-link-row]');
                row.querySelectorAll('input').forEach((input) => { input.value = ''; });
                if (row.parentElement.querySelectorAll('[data-link-row]').length > 1) row.remove();
            }
        });
    </script>
@endsection
