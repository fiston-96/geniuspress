{{-- Archive par défaut (blog, catégorie, étiquette, recherche), quand aucun gabarit du thème ne s'applique. --}}
@php($locale = $site->locale)
@php($posts = $site->posts())
<section class="gp-archive">
    <header class="gp-archive__header">
        @if ($site->kind === \App\Site\SiteContext::CATEGORY)
            <p class="gp-archive__eyebrow">{{ __('Catégorie', [], $locale) }}</p>
        @elseif ($site->kind === \App\Site\SiteContext::TAG)
            <p class="gp-archive__eyebrow">{{ __('Étiquette', [], $locale) }}</p>
        @endif
        <h1 class="gp-archive__title">{{ $site->title() }}</h1>
        @if ($site->term?->description)
            <p class="gp-archive__intro">{{ $site->term->description }}</p>
        @endif
        @if ($site->kind === \App\Site\SiteContext::SEARCH)
            @include('public.partials.search-form', ['value' => $site->search])
        @endif
    </header>
    @if ($posts && $posts->count())
        <div class="gp-post-grid">
            @foreach ($posts as $post)
                @include('public.partials.post-card')
            @endforeach
        </div>
        @include('public.partials.pagination', ['paginator' => $posts])
    @else
        <p class="gp-archive__empty">{{ $site->kind === \App\Site\SiteContext::SEARCH ? __('Aucun article ne correspond à votre recherche.', [], $locale) : __('Aucun article pour le moment.', [], $locale) }}</p>
    @endif
</section>
