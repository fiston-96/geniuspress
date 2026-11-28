@extends('platform.layouts.customer')
@section('title', __('Modèles'))
@section('content')
@php use App\Platform\Money; @endphp
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="max-w-2xl">
            <h1 class="text-2xl font-bold tracking-tight text-night">{{ __('Modèles') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ __('Plus de 100 sites complets, en français et en anglais. Les modèles gratuits s’installent depuis Modèles, dans l’administration de votre site ; les modèles premium s’achètent ici.') }}</p>
        </div>
        <span class="text-sm font-semibold text-gold">{{ trans_choice('{0} Aucun modèle|{1} 1 modèle|[2,*] :count modèles', count($templates)) }}</span>
    </div>

    @if ($templates)
        {{-- Filtre par secteur (sans rechargement). --}}
        <div class="mt-6 flex flex-wrap gap-2" role="group" aria-label="{{ __('Secteur') }}" data-filter>
            <button type="button" data-sector="" aria-pressed="true" class="rounded-full border border-night bg-night px-3 py-1.5 text-xs font-semibold text-white">{{ __('Tous') }}</button>
            @foreach ($sectors as $key => $sector)
                @if (collect($templates)->contains('sector', $key))
                    <button type="button" data-sector="{{ $key }}" aria-pressed="false" class="rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-night/30">{{ $sector['name'][$lang] ?? $key }}</button>
                @endif
            @endforeach
        </div>
    @endif

    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3" data-templates>
        @forelse ($templates as $id => $t)
            @php $price = $prices[$id] ?? null; @endphp
            <article data-sector="{{ $t['sector'] }}" class="group flex flex-col overflow-hidden rounded-2xl border border-line bg-white transition hover:-translate-y-0.5 hover:border-brand-300">
                <a href="{{ $t['demo'] }}" target="_blank" rel="noopener" class="relative block aspect-[16/10] overflow-hidden border-b border-line bg-cream" aria-label="{{ __('Aperçu de :name', ['name' => $t['name'][$lang] ?? $id]) }}">
                    @if ($t['image'])
                        <img src="{{ $t['image'] }}" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-[1.03]">
                    @else
                        <span class="absolute inset-0 grid place-items-center px-6 text-center text-xl font-bold text-night">{{ $t['name'][$lang] ?? $id }}</span>
                    @endif
                    <span @class(['absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold', 'bg-night text-gold-light' => $price, 'bg-white text-emerald-700 ring-1 ring-emerald-200' => ! $price])>{{ $price ? __('Premium') : __('Gratuit') }}</span>
                </a>
                <div class="flex flex-1 flex-col p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-gold">{{ $sectors[$t['sector']]['name'][$lang] ?? $t['sector'] }}</p>
                    <h2 class="mt-1 text-base font-bold tracking-tight text-night">{{ $t['name'][$lang] ?? $id }}</h2>
                    <p class="mt-1 line-clamp-2 flex-1 text-sm text-slate-600">{{ $t['description'][$lang] ?? '' }}</p>
                    <div class="mt-4 flex items-center gap-2">
                        @if ($price && in_array($id, $owned, true))
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700"><x-icon name="check" class="h-4 w-4" />{{ __('Acheté') }}</span>
                        @elseif ($price)
                            <form method="POST" action="{{ route('platform.customer.templates.buy', $id) }}">@csrf
                                <button class="rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-brand-700">{{ __('Acheter') }} · {{ Money::format($price->price, $price->currency) }}</button>
                            </form>
                        @endif
                        <a href="{{ $t['demo'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg border border-line px-3 py-2 text-sm font-semibold text-night hover:bg-cream"><x-icon name="eye" class="h-4 w-4" />{{ __('Voir la démo') }}</a>
                    </div>
                    @if ($price)<p class="mt-2 text-xs text-slate-500"><x-platform.local-price :amount="$price->price" :currency="$price->currency" /></p>@endif
                </div>
            </article>
        @empty
            @foreach ($prices as $price)
                <div class="rounded-2xl border border-line bg-white p-5">
                    <p class="font-semibold text-night">{{ $price->template_id }}</p>
                    <p class="mt-1 text-sm">{{ Money::format($price->price, $price->currency) }}</p>
                </div>
            @endforeach
            @if ($prices->isEmpty())<p class="text-sm text-slate-500">{{ __('Le catalogue des modèles est injoignable pour le moment.') }}</p>@endif
        @endforelse
    </div>

    <script>
        (function () {
            var buttons = document.querySelectorAll('[data-filter] [data-sector]');
            buttons.forEach(function (b) {
                b.addEventListener('click', function () {
                    var sector = b.getAttribute('data-sector');
                    buttons.forEach(function (o) {
                        var on = o === b;
                        o.setAttribute('aria-pressed', on ? 'true' : 'false');
                        o.className = on ? 'rounded-full border border-night bg-night px-3 py-1.5 text-xs font-semibold text-white' : 'rounded-full border border-line bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-night/30';
                    });
                    document.querySelectorAll('[data-templates] > article').forEach(function (card) {
                        card.hidden = sector !== '' && card.getAttribute('data-sector') !== sector;
                    });
                });
            });
        })();
    </script>
@endsection
