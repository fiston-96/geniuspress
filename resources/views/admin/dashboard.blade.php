@extends('layouts.admin')
@section('title', __('Tableau de bord'))
@section('content')
@php
    $hour = (int) now()->format('G');
    $greeting = $hour < 5 || $hour >= 18 ? __('Bonsoir :name', ['name' => auth()->user()->name]) : __('Bonjour :name', ['name' => auth()->user()->name]);
    $done = collect($checklist)->filter(fn ($c) => $c[3])->count();
    $progress = $checklist ? (int) round($done / count($checklist) * 100) : 100;
    $siteName = \App\Models\Setting::read('site_name');
@endphp

    {{-- En-tête : le site en construction (illustration tirée du symbole de la marque). --}}
    <section class="overflow-hidden rounded-2xl border border-line bg-white">
        <div class="flex flex-wrap items-center justify-between gap-8 px-6 py-8 sm:px-10">
            <div class="max-w-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gold">{{ now()->translatedFormat('l j F Y') }}</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-night sm:text-[2.6rem] sm:leading-tight">{{ $greeting }}</h1>
                <p class="mt-3 text-[15px] leading-relaxed text-slate-600">{{ __('Voici l’état de votre site.') }} {{ trans_choice('{0} Aucune page publiée pour l’instant.|{1} 1 page publiée,|[2,*] :count pages publiées,', $stats['published']) }} {{ $stats['published'] ? trans_choice('{0} aucun brouillon.|{1} 1 brouillon en cours.|[2,*] :count brouillons en cours.', $stats['drafts']) : '' }}</p>
                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ $home ? route('admin.pages.builder', $home) : route('admin.pages.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                        <x-icon name="wand-sparkles" class="h-4 w-4" />{{ $home ? __('Personnaliser l’accueil') : __('+ Nouvelle page') }}
                    </a>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg border border-night/15 px-4 py-2.5 text-sm font-semibold text-night transition hover:border-night/30 hover:bg-cream">
                        <x-icon name="eye" class="h-4 w-4" />{{ __('Voir le site') }}
                    </a>
                </div>
            </div>
            <x-brand-pages :title="$siteName" :subtitle="parse_url(url('/'), PHP_URL_HOST)" :progress="$checklist ? $progress : null" class="hidden md:block" />
        </div>

        {{-- Chiffres, séparés par des filets --}}
        <dl class="grid grid-cols-2 border-t border-line lg:grid-cols-4">
            @foreach (array_filter([
                [__('Pages'), $stats['pages'], route('admin.pages.index'), trans_choice('{0} aucune publiée|{1} 1 publiée|[2,*] :count publiées', $stats['published'])],
                [__('Articles'), $stats['posts'], route('admin.posts.index'), __('Blog et actualités')],
                [__('Médias'), $stats['media'], route('admin.media.index'), __('Images, vidéos, documents')],
                $stats['messages'] !== null ? [__('Messages non lus'), $stats['messages'], route('admin.messages.index'), trans_choice('{0} aucun abonné à la newsletter|{1} 1 abonné à la newsletter|[2,*] :count abonnés à la newsletter', $stats['subscribers'] ?? 0)] : null,
            ]) as $i => [$label, $value, $href, $hint])
                <a href="{{ $href }}" @class(['group border-line px-6 py-5 transition hover:bg-cream sm:px-8', 'border-l' => $i % 2 === 1, 'lg:border-l' => $i > 0, 'border-t lg:border-t-0' => $i >= 2])>
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $label }}</dt>
                    <dd class="mt-1 text-4xl font-bold tabular-nums tracking-tight text-night">{{ $value }}</dd>
                    <dd class="mt-1 flex items-center gap-1 text-xs text-slate-500 group-hover:text-brand-700">{{ $hint }}<x-icon name="arrow-right" class="h-3 w-3 opacity-0 transition group-hover:opacity-100" /></dd>
                </a>
            @endforeach
        </dl>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            {{-- Bien démarrer : étapes numérotées, progression en blocs (les pages qui se construisent). --}}
            @if ($checklist && $progress < 100)
                <section class="rounded-2xl border border-line bg-white">
                    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-line px-6 py-5">
                        <div>
                            <h2 class="text-lg font-bold tracking-tight text-night">{{ __('Bien démarrer') }}</h2>
                            <p class="mt-0.5 text-sm text-slate-500">{{ __(':done étapes sur :total : votre site est prêt à :percent %.', ['done' => $done, 'total' => count($checklist), 'percent' => $progress]) }}</p>
                        </div>
                        <div class="flex w-48 gap-1" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ __('Progression') }}">
                            @foreach ($checklist as $step)<span @class(['h-2 flex-1 rounded-sm', 'bg-brand-600' => $step[3], 'bg-line' => ! $step[3]])></span>@endforeach
                        </div>
                    </div>
                    <ol class="divide-y divide-line">
                        @foreach ($checklist as $n => [$key, $label, $help, $ok, $href])
                            <li>
                                <a href="{{ $href }}" class="group flex items-center gap-5 px-6 py-4 transition hover:bg-cream">
                                    @if ($ok)
                                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-gold/15 text-gold"><x-icon name="check" class="h-4 w-4" stroke="2.6" /></span>
                                    @else
                                        <span class="w-8 shrink-0 text-center text-lg font-bold tabular-nums text-gold">{{ str_pad($n + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    @endif
                                    <span class="min-w-0 flex-1">
                                        <span @class(['block text-sm font-semibold', 'text-slate-400' => $ok, 'text-night' => ! $ok])>{{ $label }}</span>
                                        <span class="block text-xs text-slate-500">{{ $help }}</span>
                                    </span>
                                    @unless ($ok)<x-icon name="arrow-right" class="h-4 w-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-brand-600" />@endunless
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- Contenus récents --}}
            <section class="rounded-2xl border border-line bg-white">
                <div class="flex items-center justify-between border-b border-line px-6 py-4">
                    <h2 class="text-lg font-bold tracking-tight text-night">{{ __('Modifiées récemment') }}</h2>
                    <a href="{{ route('admin.pages.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-700 hover:text-brand-600">{{ __('Toutes les pages') }}<x-icon name="arrow-right" class="h-4 w-4" /></a>
                </div>
                <ul class="divide-y divide-line">
                    @forelse ($recent as $item)
                        <li class="group flex items-center gap-4 px-6 py-3.5 transition hover:bg-cream">
                            <x-icon :name="$item['icon']" class="h-[18px] w-[18px] text-slate-400 group-hover:text-brand-600" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ $item['builder'] }}" class="block truncate font-semibold text-night hover:text-brand-700">{{ $item['title'] }}</a>
                                <p class="text-xs text-slate-500">{{ $item['kind'] }} · {{ __('Modifiée :date', ['date' => $item['updated_at']->diffForHumans()]) }}</p>
                            </div>
                            <span @class(['hidden items-center gap-1.5 text-xs font-medium sm:inline-flex', 'text-emerald-700' => $item['published'], 'text-gold' => ! $item['published']])>
                                <span @class(['h-1.5 w-1.5 rounded-full', 'bg-emerald-500' => $item['published'], 'bg-gold' => ! $item['published']])></span>{{ $item['published'] ? __('Publiée') : __('Brouillon') }}
                            </span>
                            <a href="{{ $item['builder'] }}" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-white hover:text-brand-700" title="{{ __('Ouvrir le builder') }}" aria-label="{{ __('Ouvrir le builder') }}"><x-icon name="pencil" class="h-4 w-4" /></a>
                            @if ($item['url'])<a href="{{ $item['url'] }}" target="_blank" rel="noopener" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-white hover:text-night" title="{{ __('Voir') }}" aria-label="{{ __('Voir') }}"><x-icon name="eye" class="h-4 w-4" /></a>@endif
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center">
                            <p class="text-sm text-slate-500">{{ __('Aucune page pour l’instant.') }}</p>
                            <a href="{{ route('admin.pages.create') }}" class="mt-3 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('+ Nouvelle page') }}</a>
                        </li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="space-y-6">
            {{-- Actions rapides --}}
            <section class="rounded-2xl border border-line bg-white">
                <h2 class="border-b border-line px-5 py-4 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('Actions rapides') }}</h2>
                <div class="p-2">
                    @foreach (array_filter([
                        [route('admin.pages.create'), __('Nouvelle page'), 'files'],
                        [route('admin.posts.create'), __('Nouvel article'), 'pen-line'],
                        [route('admin.media.index'), __('Ajouter des médias'), 'image'],
                        auth()->user()->isAdmin() ? [route('admin.templates.index'), __('Choisir un modèle'), 'layout-template'] : null,
                        auth()->user()->isAdmin() ? [route('admin.layouts.index'), __('Thème et popups'), 'layers'] : null,
                        auth()->user()->isAdmin() ? [route('admin.settings.edit'), __('Réglages du site'), 'settings'] : null,
                    ]) as [$href, $label, $icon])
                        <a href="{{ $href }}" class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-cream hover:text-night">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-cream text-night group-hover:bg-white"><x-icon :name="$icon" class="h-4 w-4" /></span>
                            <span class="flex-1">{{ $label }}</span>
                            <x-icon name="chevron-right" class="h-4 w-4 text-slate-300 group-hover:text-brand-600" />
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Derniers messages --}}
            @if (auth()->user()->isAdmin())
                <section class="rounded-2xl border border-line bg-white">
                    <div class="flex items-center justify-between border-b border-line px-5 py-4">
                        <h2 class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">{{ __('Derniers messages') }}</h2>
                        <a href="{{ route('admin.messages.index') }}" class="grid h-7 w-7 place-items-center rounded-lg text-slate-500 hover:bg-cream" title="{{ __('Messages') }}" aria-label="{{ __('Messages') }}"><x-icon name="arrow-right" class="h-4 w-4" /></a>
                    </div>
                    <ul class="divide-y divide-line">
                        @forelse ($messages as $m)
                            <li>
                                <a href="{{ route('admin.messages.show', $m) }}" class="flex gap-3 px-5 py-3 transition hover:bg-cream">
                                    <span @class(['mt-1.5 h-2 w-2 shrink-0 rounded-full', 'bg-brand-600' => ! $m->read_at, 'bg-line' => $m->read_at])></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-baseline justify-between gap-2"><span @class(['truncate text-sm text-night', 'font-semibold' => ! $m->read_at])>{{ $m->senderName() ?? $m->senderEmail() ?? $m->form_name ?? '—' }}</span><span class="shrink-0 text-xs text-slate-400">{{ $m->created_at->diffForHumans(null, true) }}</span></span>
                                        <span class="line-clamp-2 text-xs text-slate-500">{{ $m->excerpt(110) }}</span>
                                    </span>
                                </a>
                            </li>
                        @empty
                            <li class="px-5 py-8 text-center text-sm text-slate-500">{{ __('Aucun message pour l’instant.') }}</li>
                        @endforelse
                    </ul>
                </section>
            @endif

            {{-- Inspiration : symbole blanc sur terracotta (usage prévu par la charte). --}}
            <section class="rounded-2xl bg-brand-600 p-6 text-white">
                <img src="{{ asset('brand/geniuspress-mark-white.svg') }}" alt="" class="h-9 w-9">
                <h2 class="mt-4 text-lg font-bold tracking-tight">{{ __('Besoin d’inspiration ?') }}</h2>
                <p class="mt-1 text-sm leading-relaxed text-white/85">{{ __('Dans le builder, la bibliothèque propose 120 sections prêtes à l’emploi, aux couleurs de votre site.') }}</p>
                @if ($home)<a href="{{ route('admin.pages.builder', $home) }}" class="mt-5 inline-flex items-center gap-1.5 rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-brand-700 hover:bg-cream">{{ __('Ouvrir le builder') }}<x-icon name="arrow-right" class="h-4 w-4" /></a>@endif
            </section>
        </div>
    </div>
@endsection
