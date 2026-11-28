@extends('layouts.admin')
@section('title', __('Rechercher'))
@section('content')
    <div class="flex items-center gap-3">
        <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700"><x-icon name="search" /></span>
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-night">{{ __('Rechercher') }}</h1>
            @if ($q !== '')<p class="text-sm text-slate-500">{{ trans_choice('{0} Aucun résultat pour « :q »|{1} 1 résultat pour « :q »|[2,*] :count résultats pour « :q »', $total, ['q' => $q]) }}</p>@endif
        </div>
    </div>

    <form method="GET" class="mt-6 flex gap-2" role="search">
        <input type="search" name="q" value="{{ $q }}" autofocus placeholder="{{ __('Rechercher pages, articles, médias…') }}" aria-label="{{ __('Rechercher') }}"
            class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-4 focus:ring-brand-500/10">
        <button class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-500">{{ __('Rechercher') }}</button>
    </form>

    @if ($q !== '' && mb_strlen($q) < 2)
        <p class="mt-6 text-sm text-slate-500">{{ __('Tapez au moins deux caractères.') }}</p>
    @endif

    @if ($total)
        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            @foreach ([
                'pages' => [__('Pages'), 'files', fn ($p) => [$p->title, '/'.($p->is_home ? '' : $p->slug).' · '.strtoupper($p->locale), route('admin.pages.builder', $p), route('admin.pages.edit', $p)]],
                'posts' => [__('Articles'), 'newspaper', fn ($p) => [$p->title, strtoupper($p->locale).' · '.($p->isPublished() ? __('Publié') : __('Brouillon')), route('admin.posts.builder', $p), route('admin.posts.edit', $p)]],
                'layouts' => [__('Gabarits du thème'), 'layers', fn ($l) => [$l->name, \App\Models\Layout::typeLabels()[$l->type] ?? $l->type, route('admin.layouts.builder', $l), route('admin.layouts.edit', $l)]],
            ] as $key => [$label, $icon, $row])
                @if (($results[$key] ?? collect())->isNotEmpty())
                    <section class="overflow-hidden rounded-2xl border border-line bg-white">
                        <h2 class="flex items-center gap-2 border-b border-line px-5 py-3 text-sm font-semibold"><x-icon :name="$icon" class="h-4 w-4 text-brand-600" />{{ $label }}</h2>
                        <ul class="divide-y divide-line">
                            @foreach ($results[$key] as $item)
                                @php [$title, $meta, $builder, $settings] = $row($item); @endphp
                                <li class="flex items-center gap-3 px-5 py-3">
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ $builder }}" class="block truncate font-medium hover:text-brand-700">{{ $title }}</a>
                                        <p class="truncate text-xs text-slate-500">{{ $meta }}</p>
                                    </div>
                                    <a href="{{ $builder }}" class="grid h-8 w-8 place-items-center rounded-lg text-brand-600 hover:bg-brand-50" title="{{ __('Ouvrir le builder') }}" aria-label="{{ __('Ouvrir le builder') }}"><x-icon name="pencil" class="h-4 w-4" /></a>
                                    <a href="{{ $settings }}" class="grid h-8 w-8 place-items-center rounded-lg text-slate-500 hover:bg-slate-100" title="{{ __('Réglages') }}" aria-label="{{ __('Réglages') }}"><x-icon name="settings" class="h-4 w-4" /></a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endforeach
            @if (($results['media'] ?? collect())->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-line bg-white lg:col-span-2">
                    <h2 class="flex items-center gap-2 border-b border-line px-5 py-3 text-sm font-semibold"><x-icon name="image" class="h-4 w-4 text-brand-600" />{{ __('Médias') }}</h2>
                    <div class="grid grid-cols-3 gap-3 p-5 sm:grid-cols-4 md:grid-cols-6">
                        @foreach ($results['media'] as $m)
                            <a href="{{ $m->url }}" target="_blank" rel="noopener" class="group block overflow-hidden rounded-lg border border-slate-200 bg-slate-50" title="{{ $m->original_name }}">
                                @if (str_starts_with($m->mime_type, 'image/'))<img src="{{ $m->url }}" alt="{{ $m->alt }}" loading="lazy" class="aspect-square w-full object-cover transition group-hover:scale-105">
                                @else<span class="grid aspect-square place-items-center text-xs font-semibold text-slate-500">{{ strtoupper(pathinfo($m->original_name, PATHINFO_EXTENSION)) }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    @elseif (mb_strlen($q) >= 2)
        <div class="mt-10 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <x-icon name="search" class="mx-auto h-8 w-8 text-slate-300" />
            <p class="mt-3 text-sm text-slate-500">{{ __('Aucun résultat. Essayez un autre mot.') }}</p>
        </div>
    @endif
@endsection
