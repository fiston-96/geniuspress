{{-- Fiche d'un modèle : couleurs, description, aperçu et installation. --}}
@php
    $c = $t['colors'];
    $available = array_values(array_intersect($t['locales'] ?? ['fr'], ['fr', 'en']));
    $defaultLocales = array_values(array_intersect($available, $siteLocales)) ?: [$available[0] ?? 'fr'];
    $styleNames = ['split' => __('photo à côté du titre'), 'center' => __('présentation centrée'), 'image' => __('photo plein écran'), 'gradient' => __('dégradé')];
@endphp
<article data-template="{{ $t['id'] }}" data-sector="{{ $t['sector'] }}" class="flex flex-col overflow-hidden rounded-xl border bg-white {{ $highlight ? 'border-indigo-400 ring-2 ring-indigo-200' : 'border-slate-200' }}">
    <a href="{{ $t['demo'] }}" target="_blank" rel="noopener" class="relative block h-32" style="background: linear-gradient(135deg, {{ $c['primary'] }}, {{ $c['secondary'] }});" aria-label="{{ __('Aperçu de :name', ['name' => $t['name'][$lang] ?? $t['id']]) }}">
        <span class="absolute bottom-3 left-4 right-4 flex items-end justify-between">
            <span class="text-xl font-bold text-white drop-shadow" style="font-family: '{{ $t['fonts'][1] ?? $t['fonts'][0] ?? 'Inter' }}', system-ui">{{ $t['name'][$lang] ?? $t['id'] }}</span>
            <span class="flex gap-1">
                @foreach (['primary', 'secondary', 'accent', 'light'] as $k)
                    <span class="h-5 w-5 rounded-full ring-2 ring-white/70" style="background: {{ $c[$k] }}"></span>
                @endforeach
            </span>
        </span>
    </a>
    <div class="flex flex-1 flex-col gap-2 p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $sectors[$t['sector']]['name'][$lang] ?? $t['sector'] }}</p>
        @if ($reason)
            <p class="rounded-md bg-indigo-50 px-3 py-2 text-sm text-indigo-900">✨ {{ $reason }}</p>
        @endif
        <p class="text-sm text-slate-600">{{ $t['description'][$lang] ?? '' }}</p>
        <p class="text-xs text-slate-500">{{ implode(' · ', array_filter([$styleNames[$t['style']['hero'] ?? ''] ?? null, implode(' / ', $t['fonts'] ?? []), __(':count pages', ['count' => count($t['pages'] ?? [])])])) }}</p>
        <div class="mt-auto flex gap-2 pt-2">
            <a href="{{ $t['demo'] }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-medium hover:bg-slate-50">{{ __('Aperçu') }}</a>
            <details class="relative flex-1">
                <summary class="cursor-pointer list-none rounded-lg bg-indigo-600 px-3 py-1.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">{{ __('Installer…') }}</summary>
                <form method="post" action="{{ route('admin.templates.install', $t['id']) }}" class="absolute right-0 z-10 mt-2 w-80 space-y-3 rounded-xl border border-slate-200 bg-white p-4 text-sm shadow-xl"
                    onsubmit="return confirm(@js(__('Installer ce modèle ? Selon vos choix, les pages de même adresse et la page d’accueil seront remplacées.')))">
                    @csrf
                    <fieldset>
                        <legend class="font-medium text-slate-700">{{ __('Langues') }}</legend>
                        @foreach ($available as $code)
                            <label class="mr-3 inline-flex items-center gap-1.5"><input type="checkbox" name="locales[]" value="{{ $code }}" @checked(in_array($code, $defaultLocales, true)) class="rounded border-slate-300"> {{ $code === 'fr' ? 'Français' : 'English' }}</label>
                        @endforeach
                    </fieldset>
                    <fieldset class="space-y-1">
                        <legend class="font-medium text-slate-700">{{ __('Pages existantes') }}</legend>
                        <label class="flex gap-2"><input type="radio" name="mode" value="replace" checked> <span>{{ __('Remplacer celles de même adresse et la page d’accueil') }}</span></label>
                        <label class="flex gap-2"><input type="radio" name="mode" value="add"> <span>{{ __('Garder mes pages, ajouter celles du modèle à côté') }}</span></label>
                    </fieldset>
                    <label class="flex gap-2"><input type="hidden" name="design" value="0"><input type="checkbox" name="design" value="1" checked class="mt-0.5 rounded border-slate-300"> <span>{{ __('Appliquer les couleurs, polices et menus du modèle') }}</span></label>
                    <label class="flex gap-2 {{ $aiConfigured && $hasProfile ? '' : 'opacity-50' }}">
                        <input type="hidden" name="fill" value="0"><input type="checkbox" name="fill" value="1" @checked($aiConfigured && $hasProfile) @disabled(! $aiConfigured || ! $hasProfile) class="mt-0.5 rounded border-slate-300">
                        <span>{{ __('Laisser l’assistant IA rédiger les textes pour :name', ['name' => $profile['name'] ?? __('mon organisation')]) }}
                            @if (! $hasProfile)<span class="block text-xs text-slate-500">{{ __('Remplissez d’abord le questionnaire ci-dessus.') }}</span>@endif
                        </span>
                    </label>
                    <button class="w-full rounded-lg bg-indigo-600 px-3 py-2 font-semibold text-white hover:bg-indigo-500">{{ __('Installer ce modèle') }}</button>
                </form>
            </details>
        </div>
    </div>
</article>
