{{-- Article par défaut, quand aucun gabarit « Article » du thème ne s'applique. --}}
@php($locale = $site->locale)
<article class="gp-single">
    <header class="gp-single__header">
        @if ($post->categories->isNotEmpty())
            <p class="gp-single__categories">
                @foreach ($post->categories as $category)<a href="{{ $category->url() }}">{{ $category->name }}</a>@endforeach
            </p>
        @endif
        <h1 class="gp-single__title">{{ $post->title }}</h1>
        <p class="gp-single__meta">
            @if ($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->locale($locale)->translatedFormat('j F Y') }}</time>@endif
            @if ($post->author)<span>{{ __('par :name', ['name' => $post->author->name], $locale) }}</span>@endif
            <span>{{ __(':count min de lecture', ['count' => $post->readingMinutes()], $locale) }}</span>
        </p>
    </header>
    @if ($post->featuredMedia)
        <figure class="gp-single__media"><img src="{{ $post->featuredMedia->url }}" alt="{{ $post->featuredMedia->alt ?? '' }}"></figure>
    @endif
    <div class="gp-single__content">{!! $content !!}</div>
    @if ($post->tags->isNotEmpty())
        <p class="gp-single__tags">
            @foreach ($post->tags as $tag)<a href="{{ $tag->url() }}">#{{ $tag->name }}</a>@endforeach
        </p>
    @endif
    @php($nav = \App\Site\PostNavigation::for($post))
    @if ($nav['previous'] || $nav['next'])
        <nav class="gp-single__nav" aria-label="{{ __('Articles voisins', [], $locale) }}">
            @if ($nav['previous'])<a href="{{ $nav['previous']->url() }}" rel="prev"><small>{{ __('Article précédent', [], $locale) }}</small>{{ $nav['previous']->title }}</a>@else<span></span>@endif
            @if ($nav['next'])<a href="{{ $nav['next']->url() }}" rel="next" class="is-next"><small>{{ __('Article suivant', [], $locale) }}</small>{{ $nav['next']->title }}</a>@endif
        </nav>
    @endif
</article>
